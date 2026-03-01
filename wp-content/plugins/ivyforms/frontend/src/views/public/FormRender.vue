<template>
  <div
    ref="formWrapperRef"
    :class="['ivyforms-form-wrapper', 'ivyforms-form-wrapper-' + formData.id]"
  >
    <div v-if="showSuccessMessage" class="ivyforms-success-message">
      <SafeHTML :html="successMessage || getLabel('thank_you')" field-type="confirmation" />
    </div>
    <!-- Form title-->
    <template v-else>
      <div v-if="showSuccessMessageReset" class="ivyforms-success-message ivyforms-mb-24">
        <SafeHTML :html="successMessage || getLabel('thank_you')" field-type="confirmation" />
      </div>

      <h3
        v-if="formData.showTitle"
        :class="['ivyforms-form-title', 'ivyforms-form-title-' + formData.id]"
      >
        {{ formData.name }}
      </h3>
      <!-- Form description-->
      <p
        v-if="formData.showDescription"
        :class="['ivyforms-form-description', 'ivyforms-form-description-' + formData.id]"
      >
        {{ formData.description }}
      </p>

      <IvyForm
        :id="'ivyforms-form-' + formData.id"
        ref="formRef"
        :class="'ivyforms-form ivyforms-form-' + formData.id"
        :data-form-id="formData.id"
        :rules="formRules"
        :model="formValues"
        :label-position="getFormLabelPosition()"
        @submit.prevent="submitForm"
      >
        <template v-for="(row, rowIdx) in fieldsByRow" :key="'row-' + rowIdx">
          <div
            class="ivyforms-form-row ivyforms-flex ivyforms-gap-12 ivyforms-mb-16 ivyforms-width-100 ivyforms-flex-wrap-wrap"
          >
            <template v-for="field in row" :key="field.type + '_' + field.fieldIndex">
              <div
                class="ivyforms-form-field"
                :style="{
                  width:
                    row.length > 1
                      ? `calc(${field.width || 100}% - ${(12 * (row.length - 1)) / row.length}px)`
                      : `${field.width || 100}%`,
                  flex: `0 0 auto`,
                }"
              >
                <component
                  :is="formFieldsTemplates[field.type]"
                  ref="formFieldsCollectorRefs"
                  v-model="formValues[field.type + '_' + field.fieldIndex]"
                  :field="field"
                  :error="fieldErrors[field.type + '_' + field.fieldIndex]"
                  :field-errors="fieldErrors"
                  :label-position="getFormLabelPosition()"
                ></component>
              </div>
            </template>
          </div>
        </template>
        <!-- Add hidden inputs fields for referer, post id ... -->
        <div
          :class="[
            'ivyforms-form-submit',
            `ivyforms-form-submit--${formData.formActionButtons?.submitButtonSettings?.position || 'default'}`,
          ]"
        >
          <IvyButtonAction
            :id="'ivyforms-form-submit-button-' + formData.id"
            priority="tertiary"
            type="border"
            @click.prevent.stop="validateAndSubmit"
          >
            {{ formData.formActionButtons?.submitButtonSettings?.label || getLabel('submit') }}
            <IvyCircleLoader
              v-if="isSubmitting"
              :completed="isSubmitCompleted"
              class="ivyforms-ml-8"
            />
          </IvyButtonAction>
        </div>
      </IvyForm>
    </template>
  </div>
</template>

<script setup lang="ts">
import {
  type Component,
  type ComponentPublicInstance,
  computed,
  markRaw,
  nextTick,
  onMounted,
  provide,
  reactive,
  ref,
  watch,
} from 'vue'
import IvyForm from '@/views/_components/form/IvyForm.vue'
import IvyButtonAction from '@/views/_components/button/IvyButtonAction.vue'
import { useApiClient } from '@/composables/useApiClient'
import { useWcagColors } from '@/composables/useWcagColors'
// * Form Field templates
import IvyTextFormField from '../public/fields/TextFormField.vue'
import IvyParagraphFormField from '../public/fields/ParagraphFormField.vue'
import IvyEmailFormField from '../public/fields/EmailFormField.vue'
import IvyNumberFormField from '../public/fields/NumberFormField.vue'
import IvyPhoneFormField from '../public/fields/PhoneFormField.vue'
import IvyWebsiteFormField from '../public/fields/WebsiteFormField.vue'
import IvyRadioFormField from '../public/fields/RadioFormField.vue'
import NameFormField from '../public/fields/NameFormField.vue'
import RecaptchaFormField from '../public/fields/RecaptchaFormField.vue'
import TurnstileFormField from '../public/fields/TurnstileFormField.vue'
import HCaptchaFormField from '../public/fields/HCaptchaFormField.vue'
import IvyAddressFormField from '../public/fields/AddressFormField.vue'
import CheckboxFormField from '../public/fields/CheckboxFormField.vue'
import TimeFormField from '../public/fields/TimeFormField.vue'
import DateFormField from '../public/fields/DateFormField.vue'
import RatingFormField from '../public/fields/RatingFormField.vue'
import type { Field } from '@/types/field'
import type { Confirmation } from '@/types/confirmations'
import { useLabels } from '@/composables/useLabels'
import IvyMessage from '@/views/_components/message/ivyMessage.ts'
import SelectFormField from '@/views/public/fields/SelectFormField.vue'
import IvyCircleLoader from '@/views/_components/loader/IvyCircleLoader.vue'

// Initialize WCAG color system for public forms
const { startWatching } = useWcagColors()
startWatching()

// Global templates storage and hook setup
const globalTemplatesStore: Record<string, Component> = {}

// Hook to allow Pro plugin to register additional field components
const registerFieldTemplateGlobal = (fieldType: string, component: Component) => {
  if (component && fieldType) {
    const wrappedComponent = markRaw(component)
    globalTemplatesStore[fieldType] = wrappedComponent

    // Also update the reactive templates if available
    const w = window as Window & {
      _updateFormTemplates?: (fieldType: string, component: Component) => void
    }
    if (w._updateFormTemplates) {
      w._updateFormTemplates(fieldType, wrappedComponent)
    }

    // Trigger a global event to notify any listening components
    if (typeof window !== 'undefined') {
      window.dispatchEvent(
        new CustomEvent('ivyforms:template_registered', {
          detail: { fieldType, component: wrappedComponent },
        }),
      )
    }
  }
}

// Set up global hook immediately
if (typeof window !== 'undefined') {
  const w = window as Window & {
    IvyForms?: {
      api?: {
        registerFieldTemplate?: (fieldType: string, component: Component) => void
        fields?: {
          registerPublic?: (fieldType: string, component: Component) => void
        }
        hooks?: {
          registerFieldInit?: (
            callback: (
              field: Field,
              context: {
                formValues: Record<string, unknown>
                formRules: Record<string, unknown>
                getLabel: (key: string) => string
                fieldErrors: Record<string, unknown>
              },
            ) => void,
          ) => void
          registerProFieldType?: (fieldType: string) => void
        }
      }
      hooks?: {
        fieldInit?: Array<
          (
            field: Field,
            context: {
              formValues: Record<string, unknown>
              formRules: Record<string, unknown>
              getLabel: (key: string) => string
              fieldErrors: Record<string, unknown>
            },
          ) => void
        >
      }
      _publicFieldQueue?: Array<{ fieldType: string; component: Component }>
      _proFieldTypes?: Set<string>
    }
  }
  // Initialize IvyForms globals with proper structure (types defined in global.d.ts)
  // Note: Only minimal public API is initialized here, full API is in admin context
  // These objects are populated dynamically at runtime, so we cast through 'unknown'
  // which is type-safe since they will be fully initialized before use
  if (!w.IvyForms) {
    w.IvyForms = {
      api: {
        fields: { admin: {}, public: {} },
        hooks: { actions: {}, filters: {} },
        fieldOptions: { registry: {} },
      },
      hooks: {
        actions: {},
        filters: {},
        fieldInit: [],
      },
      fields: {},
    } as unknown as typeof w.IvyForms
  }
  if (!w.IvyForms.api) {
    w.IvyForms.api = {
      fields: { admin: {}, public: {} },
      hooks: { actions: {}, filters: {} },
      fieldOptions: { registry: {} },
    } as unknown as typeof w.IvyForms.api
  }
  if (!w.IvyForms.api.fields) {
    w.IvyForms.api.fields = {
      admin: {},
      public: {},
    } as unknown as typeof w.IvyForms.api.fields
  }

  // Make it available globally
  w.IvyForms.api.fields.registerPublic = registerFieldTemplateGlobal

  // Process any queued registrations
  const queue = w.IvyForms._publicFieldQueue || []
  queue.forEach(({ fieldType, component }) => registerFieldTemplateGlobal(fieldType, component))

  // Set up hooks system
  if (!w.IvyForms.hooks) {
    w.IvyForms.hooks = {
      actions: {},
      filters: {},
      fieldInit: [],
    } as unknown as typeof w.IvyForms.hooks
  }
  if (!w.IvyForms.hooks.fieldInit) {
    w.IvyForms.hooks.fieldInit = []
  }

  // Initialize Pro field types registry
  if (!w.IvyForms._proFieldTypes) {
    w.IvyForms._proFieldTypes = new Set<string>()
  }

  // Add API to register field initialization hooks
  if (!w.IvyForms.api.hooks) {
    w.IvyForms.api.hooks = {
      actions: {},
      filters: {},
    } as unknown as typeof w.IvyForms.api.hooks
  }
  w.IvyForms.api.hooks.registerFieldInit = (
    callback: (
      field: Field,
      context: {
        formValues: Record<string, unknown>
        formRules: Record<string, unknown>
        getLabel: (key: string) => string
        fieldErrors: Record<string, unknown>
      },
    ) => void,
  ) => {
    if (typeof callback === 'function') {
      w.IvyForms!.hooks!.fieldInit!.push(callback)
    }
  }

  // Add API to register Pro field types (fields that skip Lite validation)
  w.IvyForms.api.hooks.registerProFieldType = (fieldType: string) => {
    if (fieldType && typeof fieldType === 'string') {
      w.IvyForms!._proFieldTypes!.add(fieldType)
    }
  }
}

interface RecaptchaFieldComponent extends ComponentPublicInstance {
  $props: {
    field: {
      type: string
      fieldIndex: number
    }
  }
  executeRecaptcha?: () => Promise<string>
}

interface TurnstileFieldComponent extends ComponentPublicInstance {
  $props: {
    field: {
      type: string
      fieldIndex: number
    }
    modelValue?: string
  }
}

interface HCaptchaFieldComponent extends ComponentPublicInstance {
  $props: {
    field: {
      type: string
      fieldIndex: number
    }
    modelValue?: string
  }
}

const { getLabel } = useLabels()
const { request } = useApiClient()
const skipValidation = ref(false)
const isSubmitting = ref(false)
const isSubmitCompleted = ref(false)

// Helper: normalize backend fields for preview (merge Name children into parent)
function normalizePreviewFields(rawFields: Field[]): Field[] {
  if (!Array.isArray(rawFields) || rawFields.length === 0) return []

  // Preserve original order index to stably order children without subFieldIndex
  const apiOrder = new Map<Field, number>()
  rawFields.forEach((f, i) => apiOrder.set(f, i))

  const isChild = (f: Field) => f.parentId !== undefined && f.parentId !== null
  const parents = rawFields.filter((f) => !isChild(f))

  const result: Field[] = []

  for (const parent of parents) {
    // Collect children by matching parentId to parent.id or parent.fieldIndex
    const children = rawFields.filter(
      (c) =>
        isChild(c) &&
        (c.parentId === (parent as Field).id || c.parentId === (parent as Field).fieldIndex),
    )

    // Propagate common settings to top-level field if provided
    const pSettings = (parent.settings || {}) as Record<string, unknown>
    if (pSettings) {
      if (parent.hideLabel === undefined && typeof pSettings.hideLabel !== 'undefined') {
        ;(parent as Record<string, unknown>).hideLabel = !!pSettings.hideLabel
      }
      if (parent.readOnly === undefined && typeof pSettings.readOnly !== 'undefined') {
        ;(parent as Record<string, unknown>).readOnly = !!(pSettings.readOnly as boolean)
      }
      if (
        (parent as Record<string, unknown>).description === undefined &&
        typeof pSettings.description !== 'undefined'
      ) {
        ;(parent as Record<string, unknown>).description = String(pSettings.description || '')
      }
      if (
        (parent as Record<string, unknown>).cssClasses === undefined &&
        typeof pSettings.cssClasses !== 'undefined'
      ) {
        ;(parent as Record<string, unknown>).cssClasses = String(pSettings.cssClasses || '')
      }
    }

    // Apply Pro filters to parse field (e.g., conditional logic parser)
    let processedParent = parent
    if (window.IvyForms?.hooks?.applyFilters) {
      processedParent = window.IvyForms.hooks.applyFilters(
        'ivyforms/form-render/filter_parse_field',
        parent,
      ) as Field
    }

    // Use processedParent for rest of function
    const parentField = processedParent

    // Merge children for name and address fields
    if (children.length >= 2 || parentField.type === 'name' || parentField.type === 'address') {
      // Sort children: first those with explicit subFieldIndex, then the rest by API order
      const ordered = [...children].sort((a, b) => {
        const as = ((a.settings || {}) as Record<string, unknown>).subFieldIndex
        const bs = ((b.settings || {}) as Record<string, unknown>).subFieldIndex
        const aHas = typeof as === 'number'
        const bHas = typeof bs === 'number'
        if (aHas && bHas) return (as as number) - (bs as number)
        if (aHas) return -1
        if (bHas) return 1
        return (apiOrder.get(a) || 0) - (apiOrder.get(b) || 0)
      })

      if (parentField.type === 'name') {
        // Merge as name field
        const nameFieldTypes: string[] = []
        ordered.forEach((child, idx) => {
          const key = `nameField${idx + 1}`
          nameFieldTypes.push(key)
          const cs = (child.settings || {}) as Record<string, unknown>
          const prec = parentField as unknown as Record<string, unknown>
          prec[`${key}Label`] = (child.label as unknown) || ''
          prec[`${key}Placeholder`] = (child.placeholder as unknown) || ''
          prec[`${key}Required`] = !!child.required
          // Required message: prefer settings.requiredMessage, fallback to legacy top-level
          if (typeof cs.requiredMessage !== 'undefined') {
            prec[`${key}RequiredMessage`] = cs.requiredMessage
          } else if (typeof (child as Record<string, unknown>).requiredMessage !== 'undefined') {
            prec[`${key}RequiredMessage`] = (child as Record<string, unknown>).requiredMessage
          }
          // Default value
          if (typeof child.defaultValue !== 'undefined') {
            prec[`${key}Value`] = child.defaultValue as unknown
          }
          // Hide sublabel (support both settings.hideLabel and legacy child.hideLabel)
          const childHasSettingsHide = typeof cs.hideLabel !== 'undefined'
          const childHasTopLevelHide =
            typeof (child as Record<string, unknown>).hideLabel !== 'undefined'
          if (childHasSettingsHide || childHasTopLevelHide) {
            const childHideLabel = childHasSettingsHide
              ? !!(cs.hideLabel as boolean)
              : !!((child as Record<string, unknown>).hideLabel as boolean)
            prec[`${key}HideLabel`] = childHideLabel
          }
          // Subfield description
          if (typeof (child as Record<string, unknown>).description !== 'undefined') {
            prec[`${key}Description`] = String(
              ((child as Record<string, unknown>).description as string) || '',
            )
          }
          // Visibility
          if (typeof (child as Record<string, unknown>).description !== 'undefined') {
            prec[`${key}Visible`] = String(
              ((child as Record<string, unknown>).visible as boolean) || true,
            )
          }
        })
        ;(parentField as Record<string, unknown>).nameFieldTypes = nameFieldTypes
        parentField.type = 'name'
      } else if (parentField.type === 'address') {
        // Merge as address field
        // Use canonical order for address fields (must match IvyAddressFormField.vue and builder)
        const canonicalOrder = ['streetAddress', 'addressLine2', 'city', 'state', 'zip', 'country']
        const addressFieldTypes = []
        // If there are NO children, still populate all 6 subfields on the parent
        if (ordered.length === 0) {
          canonicalOrder.forEach((fieldType) => {
            addressFieldTypes.push(fieldType)
            const prec = parentField as unknown as Record<string, unknown>
            prec[`${fieldType}Label`] =
              (parentField as Record<string, unknown>)[`${fieldType}Label`] || ''
            prec[`${fieldType}Placeholder`] =
              (parentField as Record<string, unknown>)[`${fieldType}Placeholder`] || ''
            prec[`${fieldType}Required`] =
              (parentField as Record<string, unknown>)[`${fieldType}Required`] || false
            prec[`${fieldType}RequiredMessage`] =
              (parentField as Record<string, unknown>)[`${fieldType}RequiredMessage`] || ''
            prec[`${fieldType}Value`] =
              (parentField as Record<string, unknown>)[`${fieldType}Value`] || ''
            prec[`${fieldType}HideLabel`] =
              (parentField as Record<string, unknown>)[`${fieldType}HideLabel`] || false
            prec[`${fieldType}Description`] =
              (parentField as Record<string, unknown>)[`${fieldType}Description`] || ''
            prec[`${fieldType}Visible`] =
              (parentField as Record<string, unknown>)[`${fieldType}Visible`] || true
          })
          ;(parentField as Record<string, unknown>).addressFieldTypes = canonicalOrder
          parentField.type = 'address'
        } else {
          canonicalOrder.forEach((fieldType, idx) => {
            // Find child by settings.fieldType, or by label (case-insensitive, spaces removed), or fallback to ordered[idx] if exists
            let child =
              ordered.find((c) => {
                const settings = parseSettings((c as unknown as Record<string, unknown>).settings)
                return (
                  (settings && settings.fieldType === fieldType) ||
                  (c.label && c.label.toLowerCase().replace(/\s/g, '') === fieldType.toLowerCase())
                )
              }) || ordered.find((c) => c.type === fieldType)
            // Only fallback to ordered[idx] if it exists and is not undefined
            if (!child && Array.isArray(ordered) && ordered.length > idx && ordered[idx]) {
              child = ordered[idx]
            }
            addressFieldTypes.push(fieldType)
            const cs = child && child.settings ? parseSettings(child.settings) : {}
            const prec = parentField as unknown as Record<string, unknown>
            prec[`${fieldType}Label`] = child ? (child.label as unknown) || '' : ''
            prec[`${fieldType}Placeholder`] = child ? (child.placeholder as unknown) || '' : ''
            prec[`${fieldType}Required`] = child ? !!child.required : false
            if (child && typeof cs.requiredMessage !== 'undefined') {
              prec[`${fieldType}RequiredMessage`] = cs.requiredMessage
            } else if (
              child &&
              typeof (child as Record<string, unknown>).requiredMessage !== 'undefined'
            ) {
              prec[`${fieldType}RequiredMessage`] = (
                child as Record<string, unknown>
              ).requiredMessage
            } else {
              prec[`${fieldType}RequiredMessage`] = ''
            }
            if (child && typeof child.defaultValue !== 'undefined') {
              prec[`${fieldType}Value`] = child.defaultValue as unknown
            } else {
              prec[`${fieldType}Value`] = ''
            }
            const childHasSettingsHide = child && typeof cs.hideLabel !== 'undefined'
            const childHasTopLevelHide =
              child && typeof (child as Record<string, unknown>).hideLabel !== 'undefined'
            if (childHasSettingsHide || childHasTopLevelHide) {
              const childHideLabel = childHasSettingsHide
                ? !!(cs.hideLabel as boolean)
                : !!((child as Record<string, unknown>).hideLabel as boolean)
              prec[`${fieldType}HideLabel`] = childHideLabel
            } else {
              prec[`${fieldType}HideLabel`] = false
            }
            if (child && typeof (child as Record<string, unknown>).description !== 'undefined') {
              prec[`${fieldType}Description`] = String(
                ((child as Record<string, unknown>).description as string) || '',
              )
            } else {
              prec[`${fieldType}Description`] = ''
            }
            if (child && typeof (child as Record<string, unknown>).visible !== 'undefined') {
              prec[`${fieldType}Visible`] = !!((child as Record<string, unknown>)
                .visible as boolean)
            } else {
              prec[`${fieldType}Visible`] = true
            }
          })
          ;(parentField as Record<string, unknown>).addressFieldTypes = canonicalOrder
          parentField.type = 'address'
        }
      }
    }

    result.push(parentField)
  }

  return result
}

// Safely parse settings which may be stored as JSON string or as an object
const parseSettings = (s: unknown): Record<string, unknown> => {
  if (!s) return {}
  if (typeof s === 'string') {
    try {
      return JSON.parse(s) as Record<string, unknown>
    } catch {
      return {}
    }
  }
  if (typeof s === 'object') {
    return s as Record<string, unknown>
  }
  return {}
}

// Form data
const formFields = ref([])
const formValues = ref({})
const formRules = ref({})
const fieldErrors = ref({})
const isLoading = ref(true)
const formRef = ref(null)
const formWrapperRef = ref(null)
const formFieldsCollectorRefs = ref([])
const formNonce = ref(null)
const formConfirmation = ref([])
const showSuccessMessage = ref(false)
const showSuccessMessageReset = ref(false)
const successMessage = ref('')
const postId = ref('')
const refererUrl = ref('')

// Computed property to filter visible fields
// Hook: ivyforms_fields_visible can be used by Pro to add conditional logic filtering
const visibleFields = computed(() => {
  if (!formFields.value || formFields.value.length === 0) {
    return []
  }

  // Allow Pro plugin to filter fields (e.g., conditional logic)
  let fields = [...formFields.value]

  if (window.IvyForms?.hooks?.applyFilters) {
    fields = window.IvyForms.hooks.applyFilters(
      'ivyforms/form-render/filter_fields_visible',
      fields,
      formValues.value,
    )
  }

  return fields
})

// Organize fields into rows based on rowIndex and columnIndex
const fieldsByRow = computed(() => {
  const fields = visibleFields.value
  if (!fields || fields.length === 0) {
    return []
  }

  // Group fields by rowIndex
  const rowsMap: Map<number, Field[]> = new Map()

  fields.forEach((field) => {
    const rowIdx = field.rowIndex ?? 0
    if (!rowsMap.has(rowIdx)) {
      rowsMap.set(rowIdx, [])
    }
    rowsMap.get(rowIdx)!.push(field)
  })

  // Sort rows by rowIndex and sort fields within each row by columnIndex
  return Array.from(rowsMap.entries())
    .sort(([aIdx], [bIdx]) => aIdx - bIdx)
    .map(([, fields]) => {
      return fields.sort((a, b) => (a.columnIndex ?? 0) - (b.columnIndex ?? 0))
    })
})

interface FormData {
  id: string | number
  name?: string
  description?: string
  showTitle?: boolean
  showDescription?: boolean
  storeEntries?: boolean
  defaultValue?: string
  counter?: number
  fields?: Field[]
  confirmations?: Confirmation[]
  formActionButtons?: {
    submitButtonSettings?: {
      label: string
      position: 'default' | 'left' | 'center' | 'right'
    }
  }
}

// Define the shape of entries provided by the global window variable
interface GlobalFormEntry {
  id: string | number
  form: FormData
  nonce: string
  fields: Field[]
  confirmations: Confirmation[]
}

// Update your refs with proper typing
const formData = ref<FormData>({
  id: '',
  name: '',
  description: '',
  showTitle: true,
  showDescription: false,
  storeEntries: true,
  defaultValue: '',
  counter: 0,
  fields: [],
  confirmations: [],
  formActionButtons: {
    submitButtonSettings: {
      label: getLabel('submit'),
      position: 'default',
    },
  },
})
// * Form Fields Object - start with base templates then add global ones
const baseTemplates = {
  text: markRaw(IvyTextFormField),
  email: markRaw(IvyEmailFormField),
  textarea: markRaw(IvyParagraphFormField),
  number: markRaw(IvyNumberFormField),
  phone: markRaw(IvyPhoneFormField),
  website: markRaw(IvyWebsiteFormField),
  time: markRaw(TimeFormField),
  date: markRaw(DateFormField),
  recaptcha: markRaw(RecaptchaFormField),
  turnstile: markRaw(TurnstileFormField),
  hcaptcha: markRaw(HCaptchaFormField),
  radio: markRaw(IvyRadioFormField),
  select: markRaw(SelectFormField),
  name: markRaw(NameFormField),
  address: markRaw(IvyAddressFormField),
  checkbox: markRaw(CheckboxFormField),
  'multi-select': markRaw(SelectFormField),
  rating: markRaw(RatingFormField),
}

// Create reactive templates and manually add global store items
const formFieldsTemplates = reactive({ ...baseTemplates })
// Add any existing global templates
Object.entries(globalTemplatesStore).forEach(([fieldType, component]) => {
  formFieldsTemplates[fieldType] = component
})
// Force reactivity trigger
const templateUpdateTrigger = ref(0)
// Set up update function for late registrations
if (typeof window !== 'undefined') {
  ;(
    window as Window & { _updateFormTemplates?: (fieldType: string, component: Component) => void }
  )._updateFormTemplates = (fieldType: string, component: Component) => {
    formFieldsTemplates[fieldType] = component
    templateUpdateTrigger.value++ // Force Vue reactivity
  }

  // Listen for template registration events
  window.addEventListener('ivyforms:template_registered', (event: Event) => {
    const customEvent = event as CustomEvent<{ fieldType: string; component: Component }>
    const { fieldType, component } = customEvent.detail
    formFieldsTemplates[fieldType] = component
    templateUpdateTrigger.value++ // Force Vue reactivity
  })

  // Also check if there are already registered templates in global store
  Object.entries(globalTemplatesStore).forEach(([fieldType, component]) => {
    formFieldsTemplates[fieldType] = component
  })
}

// Initialize from global variable
onMounted(async () => {
  const w = window as unknown as { wpIvyFormDataList?: Record<string, GlobalFormEntry> }
  if (typeof window !== 'undefined' && w.wpIvyFormDataList) {
    const counter = (formWrapperRef.value as HTMLElement)
      .closest('.ivyforms-frontend')!
      .getAttribute('data-counter') as string
    const formsData = w.wpIvyFormDataList[counter]
    // Check if formData is an object and has the required properties
    if (typeof formsData === 'object' && formsData !== null && formsData.id) {
      // Assign the form data to the reactive variable
      formData.value = formsData.form
      formNonce.value = formsData.nonce
      // Normalize fields for preview
      formFields.value = normalizePreviewFields(formsData.fields || [])
      formConfirmation.value = formsData.confirmations || []

      // Wait for Pro plugins to register their hooks before initializing fields
      // This ensures all extensions can add field-specific initialization logic
      await new Promise<void>((resolve) => {
        if (typeof window !== 'undefined') {
          // Check if Pro is ready
          const wExt = window as Window & { IvyFormsProReady?: boolean }
          if (wExt.IvyFormsProReady) {
            resolve()
            return
          }

          // Wait for Pro ready event
          const handler = () => {
            resolve()
            window.removeEventListener('ivyforms:pro_ready', handler)
          }
          window.addEventListener('ivyforms:pro_ready', handler, { once: true })

          // Fallback timeout - initialize after 100ms even if Pro doesn't signal ready
          setTimeout(() => {
            window.removeEventListener('ivyforms:pro_ready', handler)
            resolve()
          }, 100)
        } else {
          resolve()
        }
      })

      // ✅ NOW initialize fields AFTER Pro is ready
      // This ensures visibleFields computed will apply conditional logic hooks
      formFields.value = normalizePreviewFields(formsData.fields || [])

      // Initialize form values with defaults or empty strings
      formFields.value.forEach((field) => {
        const key = field.type + '_' + field.fieldIndex

        // Special handling for name field type
        if (field.type === 'name') {
          const nameValue: Record<string, string> = {}
          const nameFieldTypes = field.nameFieldTypes || ['nameField1', 'nameField2']
          // Initialize each dynamic name field
          nameFieldTypes.forEach((fieldType) => {
            nameValue[fieldType] = (field as Record<string, string>)[`${fieldType}Value`] || ''
          })
          formValues.value[key] = nameValue
        } else if (field.type === 'address') {
          // initialize all Address field subfields
          const addressValue: Record<string, string> = {}
          const addressFieldTypes = field.addressFieldTypes || [
            'streetAddress',
            'addressLine2',
            'city',
            'state',
            'zip',
            'country',
          ]
          addressFieldTypes.forEach((fieldType) => {
            addressValue[fieldType] = (field as Record<string, string>)[`${fieldType}Value`] || ''
          })
          formValues.value[key] = addressValue
        } else if (field.type === 'checkbox' && Array.isArray(field.fieldOptions)) {
          const defaultOpts = field.fieldOptions
            .filter((opt) => opt.isDefault)
            .map((opt) => opt.value)
          formValues.value[key] = defaultOpts.length ? defaultOpts : undefined
        } else if (
          (field.type === 'radio' || field.type === 'select') &&
          Array.isArray(field.fieldOptions)
        ) {
          const defaultOpt = field.fieldOptions.find((opt) => opt.isDefault)
          formValues.value[key] = defaultOpt ? defaultOpt.value : ''
        } else if (field.type === 'rating' && Array.isArray(field.fieldOptions)) {
          const defaultOpt = field.fieldOptions.find((opt) => opt.isDefault)
          formValues.value[key] = defaultOpt ? Number(defaultOpt.value) : undefined
        } else if (field.type === 'multi-select' && Array.isArray(field.fieldOptions)) {
          const defaultOpts = field.fieldOptions
            .filter((opt) => opt.isDefault)
            .map((opt) => opt.value)
          formValues.value[key] = defaultOpts.length ? defaultOpts : undefined
        } else if (field.type === 'website') {
          // Handle website fields with prefix and suffix
          let websiteValue = field.defaultValue || ''
          const prefix = field.inputPrefix || ''
          const suffix = field.inputSuffix || ''

          // If there's a defaultValue, strip any existing prefix/suffix first
          if (websiteValue && (prefix || suffix)) {
            // Remove prefix if it exists
            if (prefix && websiteValue.startsWith(prefix)) {
              websiteValue = websiteValue.substring(prefix.length)
            }
            // Remove suffix if it exists
            if (suffix && websiteValue.endsWith(suffix)) {
              websiteValue = websiteValue.substring(0, websiteValue.length - suffix.length)
            }
          }

          formValues.value[key] = websiteValue
        } else if (field.type === 'date') {
          // Sanitize date default values - filter out invalid dates with MIN_SAFE_INTEGER
          let dateValue = field.defaultValue || ''
          if (
            typeof dateValue === 'string' &&
            (dateValue.includes('-9007199254740991') || dateValue.includes('MIN_SAFE_INTEGER'))
          ) {
            dateValue = ''
          }
          formValues.value[key] = dateValue
        } else {
          formValues.value[key] = field.defaultValue || ''
        }

        if (field.type === 'name') {
          const nameFieldTypes = field.nameFieldTypes || ['nameField1', 'nameField2']
          nameFieldTypes.forEach((nfType: string) => {
            const subKey = `${field.type}_${field.fieldIndex}.${nfType}`
            const label =
              (field as Record<string, string>)[`${nfType}Label`] ||
              getLabel(`name_field_${nfType.replace('nameField', '')}`)
            const rulesForSub: unknown[] = []
            if ((field as Record<string, unknown>)[`${nfType}Required`]) {
              const customMsg = (field as Record<string, unknown>)[`${nfType}RequiredMessage`]
              const message =
                typeof customMsg === 'string' && (customMsg as string).trim() !== ''
                  ? (customMsg as string)
                  : `${label} ${getLabel('required')}`
              rulesForSub.push({
                required: true,
                message,
                trigger: ['submit'],
              })
            }
            ;(formRules.value as Record<string, unknown>)[subKey] = rulesForSub
          })
        } else if (field.type === 'address') {
          const addressFieldTypes = field.addressFieldTypes || [
            'streetAddress',
            'addressLine2',
            'city',
            'state',
            'zip',
            'country',
          ]
          addressFieldTypes.forEach((afType: string) => {
            const subKey = `${field.type}_${field.fieldIndex}.${afType}`
            const label =
              (field as Record<string, string>)[`${afType}Label`] ||
              getLabel(`address_field_${afType.replace('addressField', '')}`)
            const rulesForSub: unknown[] = []
            if ((field as Record<string, unknown>)[`${afType}Required`]) {
              const customMsg = (field as Record<string, unknown>)[`${afType}RequiredMessage`]
              const message =
                typeof customMsg === 'string' && (customMsg as string).trim() !== ''
                  ? (customMsg as string)
                  : `${label} ${getLabel('required')}`
              rulesForSub.push({
                required: true,
                message,
                trigger: ['submit'],
              })
            }
            ;(formRules.value as Record<string, unknown>)[subKey] = rulesForSub
          })
        } else {
          const rules = []

          // Check if this field type is registered as a Pro field
          // Pro fields handle their own validation via fieldInit hooks
          const w = window as Window & { IvyForms?: { _proFieldTypes?: Set<string> } }
          const isProField = w.IvyForms?._proFieldTypes?.has(field.type) || false

          // Required rule (skip Pro fields - Pro handles them via fieldInit hooks)
          if (field.required && !isProField) {
            rules.push({
              required: true,
              message: getRequiredMessage(field),
              trigger: ['submit'],
            })
          }

          // Format validations
          if (field.type === 'email') {
            rules.push({
              validator: (_rule: unknown, value: string, callback: (err?: Error) => void) => {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
                if (skipValidation.value) {
                  callback()
                  return
                }
                if (!value) {
                  if (field.required) {
                    callback(new Error(getLabel('enter_email_address')))
                  } else {
                    callback()
                  }
                  return
                }
                if (!emailRegex.test(value)) {
                  callback(new Error(getLabel('enter_email_address')))
                  return
                }
                callback()
              },
              trigger: ['submit'],
            })
          }

          if (field.type === 'phone') {
            rules.push({
              pattern: /^[+\d]?(?:[\d\s.-]{7,15})$/,
              message: getLabel('enter_phone_number'),
              trigger: ['submit'],
            })
          }

          if (field.type === 'website') {
            rules.push({
              validator: (_rule: unknown, value: unknown, callback: (err?: Error) => void) => {
                // Check if field is required and empty
                if (field.required && (value === '' || value === null || value === undefined)) {
                  return callback(new Error(getRequiredMessage(field)))
                }
                if (value === '' || value === null || value === undefined) {
                  return callback()
                }

                let urlToValidate = String(value)

                // If field has prefix and/or suffix, construct the full URL for validation
                const prefix = field.inputPrefix || ''
                const suffix = field.inputSuffix || ''

                if (prefix || suffix) {
                  // User entered only the middle part, construct full URL
                  urlToValidate = prefix + urlToValidate + suffix
                }

                // Validate the complete URL
                const urlPattern = /^(https?:\/\/)?([\da-z.-]+)\.([a-z.]{2,6})([/\w .-]*)*\/?$/i
                if (!urlPattern.test(urlToValidate)) {
                  return callback(new Error(getLabel('enter_valid_website')))
                }

                callback()
              },
              trigger: ['submit'],
            })
          }

          if (field.type === 'time') {
            rules.push({
              validator: (_: unknown, value: string, callback: (e?: Error) => void) => {
                // Trim spaces before validation
                const trimmedValue = typeof value === 'string' ? value.trim() : value
                const isEmpty = trimmedValue == null || trimmedValue === ''
                const isPartial = trimmedValue === '__PARTIAL__'
                let validPattern = false
                let errorMsg = ''

                // Special handling for dropdown mode - check if all parts are selected
                if (field.timeFieldType === 'dropdown') {
                  if (field.required && (isEmpty || isPartial)) {
                    return callback(new Error(getRequiredMessage(field)))
                  }
                  if (isPartial) {
                    return callback(new Error(getLabel('enter_valid_time')))
                  }
                  if (!isEmpty) {
                    let hasValidFormat = false
                    if (field.timeFormat === 'ampm') {
                      hasValidFormat = /^(0?[1-9]|1[0-2]):[0-5]\d ?([AP]M|[ap]m)$/.test(
                        trimmedValue,
                      )
                    } else {
                      hasValidFormat = /^(?:[01]?\d|2[0-3]):[0-5]\d$/.test(trimmedValue)
                    }
                    if (!hasValidFormat) {
                      return callback(new Error(getLabel('enter_valid_time')))
                    }
                  }
                  return callback()
                }

                // For time-picker and input modes, use pattern validation
                if (field.timeFieldType === 'time-picker') {
                  validPattern =
                    isEmpty ||
                    /^(?:[01]\d|2[0-3]):[0-5]\d(?:\s?(?:AM|PM|am|pm))?$/.test(trimmedValue)
                  errorMsg = getLabel('enter_valid_time')
                } else if (field.timeFormat === 'ampm') {
                  validPattern = /^(0?[1-9]|1[0-2]):[0-5]\d (AM|PM|am|pm)$/.test(trimmedValue)
                  errorMsg = getLabel('enter_valid_time')
                } else {
                  validPattern = /^(?:[01]\d|2[0-3]):[0-5]\d$/.test(trimmedValue)
                  errorMsg = getLabel('enter_valid_time')
                }
                if (field.required && isEmpty) {
                  return callback(new Error(getRequiredMessage(field)))
                }
                if (!isEmpty && !validPattern) {
                  return callback(new Error(errorMsg))
                }
                return callback()
              },
              trigger: ['submit'],
            })
          }

          if (field.type === 'date') {
            rules.push({
              validator: (_: unknown, value: string, callback: (e?: Error) => void) => {
                // Trim spaces before validation
                const trimmedValue = typeof value === 'string' ? value.trim() : value
                const isEmpty = trimmedValue == null || trimmedValue === ''
                const isPartial = trimmedValue === '__PARTIAL__'

                // Special handling for dropdown mode - check if all parts are selected
                if (field.dateFieldType === 'dropdown') {
                  if (field.required && (isEmpty || isPartial)) {
                    return callback(new Error(getRequiredMessage(field)))
                  }
                  if (isPartial) {
                    return callback(new Error(getLabel('enter_valid_date')))
                  }
                  // If not empty and not partial in dropdown mode, it's valid (dropdown ensures correct format)
                  if (!isEmpty) {
                    return callback()
                  }
                  return callback()
                }

                // Check required
                if (field.required && isEmpty) {
                  return callback(new Error(getRequiredMessage(field)))
                }

                // If empty and not required, allow it
                if (isEmpty) {
                  return callback()
                }

                // Validate date format based on field.dateFormat (Day.js format)
                const dateFormat = (field.dateFormat as string) || 'MM/DD/YYYY'
                let isValidDate = false

                if (dateFormat === 'MM/DD/YYYY' || dateFormat === 'M/D/YYYY') {
                  isValidDate = /^(0?[1-9]|1[0-2])\/(0?[1-9]|[12]\d|3[01])\/\d{4}$/.test(
                    trimmedValue,
                  )
                } else if (dateFormat === 'DD/MM/YYYY' || dateFormat === 'D/M/YYYY') {
                  isValidDate = /^(0?[1-9]|[12]\d|3[01])\/(0?[1-9]|1[0-2])\/\d{4}$/.test(
                    trimmedValue,
                  )
                } else if (dateFormat === 'YYYY-MM-DD' || dateFormat === 'YYYY-M-D') {
                  isValidDate = /^\d{4}-(0?[1-9]|1[0-2])-(0?[1-9]|[12]\d|3[01])$/.test(trimmedValue)
                } else if (dateFormat === 'DD.MM.YYYY' || dateFormat === 'D.M.YYYY') {
                  isValidDate = /^(0?[1-9]|[12]\d|3[01])\.(0?[1-9]|1[0-2])\.\d{4}$/.test(
                    trimmedValue,
                  )
                } else {
                  // Default to MM/DD/YYYY
                  isValidDate = /^(0?[1-9]|1[0-2])\/(0?[1-9]|[12]\d|3[01])\/\d{4}$/.test(
                    trimmedValue,
                  )
                }

                if (!isValidDate) {
                  return callback(new Error(getLabel('enter_date_valid_format')))
                }

                // Validate min/max date range
                const hasMin =
                  field.minDateValue !== null &&
                  field.minDateValue !== undefined &&
                  field.minDateValue !== ''
                const hasMax =
                  field.maxDateValue !== null &&
                  field.maxDateValue !== undefined &&
                  field.maxDateValue !== ''

                if (hasMin || hasMax) {
                  const currentDate = parseDateToObject(trimmedValue, dateFormat)
                  if (!currentDate) {
                    return callback(new Error(getLabel('enter_valid_date')))
                  }

                  const minDate = hasMin
                    ? parseDateToObject(String(field.minDateValue), dateFormat)
                    : null
                  const maxDate = hasMax
                    ? parseDateToObject(String(field.maxDateValue), dateFormat)
                    : null

                  // Check if date is before minimum
                  if (minDate && currentDate < minDate) {
                    const message = getLabel('date_must_be_after')
                    return callback(new Error(message.replace('{min}', String(field.minDateValue))))
                  }

                  // Check if date is after maximum
                  if (maxDate && currentDate > maxDate) {
                    const message = getLabel('date_must_be_before')
                    return callback(new Error(message.replace('{max}', String(field.maxDateValue))))
                  }
                }

                return callback()
              },
              trigger: ['submit'],
            })
          }

          if (field.type === 'number' && (field.minValue !== null || field.maxValue !== null)) {
            rules.push({
              validator: (_rule: unknown, value: unknown, callback: (err?: Error) => void) => {
                if (value === '' || value === null || value === undefined) {
                  return callback()
                }
                const num = Number(value)
                if (isNaN(num)) {
                  return callback(new Error(getLabel('enter_valid_number')))
                }
                const hasMin = field.minValue !== null && field.minValue !== undefined
                const hasMax = field.maxValue !== null && field.maxValue !== undefined
                if (hasMin && num < field.minValue) {
                  if (hasMax) {
                    const tpl = getLabel('number_out_of_range')
                    return callback(
                      new Error(
                        tpl
                          .replace('{min}', String(field.minValue))
                          .replace('{max}', String(field.maxValue)),
                      ),
                    )
                  }
                  const tpl = getLabel('number_cannot_be_smaller_than')
                  return callback(new Error(tpl.replace('{min}', String(field.minValue))))
                }
                if (hasMax && num > field.maxValue && field.minValue !== field.maxValue) {
                  if (hasMin) {
                    const tpl = getLabel('number_out_of_range')
                    return callback(
                      new Error(
                        tpl
                          .replace('{min}', String(field.minValue))
                          .replace('{max}', String(field.maxValue)),
                      ),
                    )
                  }
                  const tpl = getLabel('number_cannot_be_greater_than')
                  return callback(new Error(tpl.replace('{max}', String(field.maxValue))))
                }
                callback()
              },
              trigger: ['change', 'blur', 'submit'],
            })

            // Real-time watcher for immediate feedback when exceeding limits
            watch(
              () => formValues.value[key],
              (val) => {
                const hasMin = field.minValue !== null && field.minValue !== undefined
                const hasMax = field.maxValue !== null && field.maxValue !== undefined
                if (val === '' || val === null || val === undefined) {
                  if (fieldErrors.value[key]) delete fieldErrors.value[key]
                  return
                }
                const num = Number(val)
                if (isNaN(num)) {
                  fieldErrors.value[key] = getLabel('enter_valid_number')
                  return
                }
                if (
                  ((hasMin && num < field.minValue) || (hasMax && num > field.maxValue)) &&
                  field.minValue !== field.maxValue
                ) {
                  if (hasMin && hasMax) {
                    const tpl = getLabel('number_out_of_range')
                    fieldErrors.value[key] = tpl
                      .replace('{min}', String(field.minValue))
                      .replace('{max}', String(field.maxValue))
                  } else if (hasMin && num < field.minValue) {
                    const tpl = getLabel('number_cannot_be_smaller_than')
                    fieldErrors.value[key] = tpl.replace('{min}', String(field.minValue))
                  } else if (hasMax && num > field.maxValue) {
                    const tpl = getLabel('number_cannot_be_greater_than')
                    fieldErrors.value[key] = tpl.replace('{max}', String(field.maxValue))
                  }
                  return
                }
                if (fieldErrors.value[key]) delete fieldErrors.value[key]
              },
            )
          }

          formRules.value[key] = rules
        }

        // Real-time watcher for date min/max validation (only for input mode)
        if (
          field.type === 'date' &&
          field.dateFieldType === 'input' &&
          (field.minDateValue !== null || field.maxDateValue !== null)
        ) {
          const hasMin =
            field.minDateValue !== null &&
            field.minDateValue !== undefined &&
            field.minDateValue !== ''
          const hasMax =
            field.maxDateValue !== null &&
            field.maxDateValue !== undefined &&
            field.maxDateValue !== ''

          if (hasMin || hasMax) {
            watch(
              () => formValues.value[key],
              (val) => {
                // Skip validation if empty or partial
                if (!val || val === '' || val === '__PARTIAL__') {
                  if (fieldErrors.value[key]) delete fieldErrors.value[key]
                  return
                }

                const dateFormat = (field.dateFormat as string) || 'MM/DD/YYYY'

                const isDateComplete = (dateStr: string, format: string): boolean => {
                  let expectedLength = format.length

                  if (format.includes('M/D') || format.includes('D/M')) {
                    expectedLength = 8
                  } else if (format.includes('MM/DD') || format.includes('DD/MM')) {
                    expectedLength = 10
                  } else if (format.includes('YYYY-MM-DD') || format.includes('YYYY-M-D')) {
                    expectedLength = 10
                  } else if (format.includes('DD.MM.YYYY') || format.includes('D.M.YYYY')) {
                    expectedLength = 10
                  }

                  if (dateStr.length < expectedLength) {
                    return false
                  }

                  let parts: string[] = []
                  if (dateStr.indexOf('/') !== -1) {
                    parts = dateStr.split('/')
                  } else if (dateStr.indexOf('-') !== -1) {
                    parts = dateStr.split('-')
                  } else if (dateStr.indexOf('.') !== -1) {
                    parts = dateStr.split('.')
                  }

                  if (parts.length === 3) {
                    let yearPart = ''
                    if (format.startsWith('YYYY')) {
                      yearPart = parts[0]
                    } else {
                      yearPart = parts[2]
                    }

                    if (yearPart.length !== 4 || isNaN(Number(yearPart))) {
                      return false
                    }
                  } else {
                    return false
                  }

                  return true
                }

                if (!isDateComplete(String(val), dateFormat)) {
                  if (fieldErrors.value[key]) delete fieldErrors.value[key]
                  return
                }

                const currentDate = parseDateToObject(String(val), dateFormat)
                if (!currentDate) {
                  if (fieldErrors.value[key]) delete fieldErrors.value[key]
                  return
                }

                const minDate = hasMin
                  ? parseDateToObject(String(field.minDateValue), dateFormat)
                  : null
                const maxDate = hasMax
                  ? parseDateToObject(String(field.maxDateValue), dateFormat)
                  : null

                // Use nextTick to ensure error is set AFTER clearFieldError is called
                nextTick(() => {
                  if (minDate && currentDate < minDate) {
                    const message = getLabel('date_must_be_after')
                    fieldErrors.value[key] = message.replace('{min}', String(field.minDateValue))
                    return
                  }

                  if (maxDate && currentDate > maxDate) {
                    const message = getLabel('date_must_be_before')
                    fieldErrors.value[key] = message.replace('{max}', String(field.maxDateValue))
                    return
                  }

                  if (fieldErrors.value[key]) delete fieldErrors.value[key]
                })
              },
            )
          }
        }

        // Email confirmation extension
        if (field.type === 'email' && field.confirmFieldEnabled) {
          const confirmKey = 'emailConfirm_' + field.fieldIndex
          formValues.value[confirmKey] = ''
          const confirmRules = []
          confirmRules.push({
            validator: (_rule: unknown, value: string, callback: (err?: Error) => void) => {
              const mainVal = formValues.value[key]
              const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
              if (!mainVal) {
                callback()
                return
              }
              if (!value) {
                callback(new Error(getLabel('enter_email_address')))
                return
              }
              if (!emailRegex.test(value)) {
                callback(new Error(getLabel('enter_email_address')))
                return
              }
              if (mainVal && value !== mainVal) {
                callback(new Error(getLabel('emails_do_not_match')))
                return
              }
              callback()
            },
            trigger: ['submit'],
          })
          formRules.value[confirmKey] = confirmRules
        }

        // Allow Pro or other extensions to add field-specific initialization
        // Hook: ivyforms:field_init
        if (typeof window !== 'undefined') {
          const w = window as Window & {
            IvyForms?: {
              hooks?: {
                fieldInit?: Array<
                  (
                    field: Field,
                    context: {
                      formValues: typeof formValues.value
                      formRules: typeof formRules.value
                      getLabel: typeof getLabel
                      fieldErrors: typeof fieldErrors.value
                    },
                  ) => void
                >
              }
            }
          }

          const hooks = w.IvyForms?.hooks?.fieldInit || []
          hooks.forEach((hook) => {
            try {
              hook(field, {
                formValues: formValues.value,
                formRules: formRules.value,
                getLabel,
                fieldErrors: fieldErrors.value,
              })
            } catch (error) {
              console.error(getLabel('error_in_field_init_hook'), error)
            }
          })
        }
      })

      // Form is loaded, remove loader
      isLoading.value = false

      // Clear any validation errors that may have been triggered during initialization
      // This prevents fields from showing errors on page load
      // Use setTimeout to ensure this runs after all DOM updates and isLoading changes
      setTimeout(() => {
        if (formRef.value) {
          formRef.value.clearValidate()
        }
      }, 50)
      // add vanila js remove class from loader
      const counter = (formWrapperRef.value as HTMLElement)
        .closest('.ivyforms-frontend')
        ?.getAttribute('data-counter')
      const loader = document.querySelector(`.ivyforms-skeleton-${counter}`)
      if (loader) {
        loader.remove()
      }
    } else {
      console.error(getLabel('invalid_form_data'), formData)
    }
  } else {
    console.error(getLabel('form_data_not_found'))
    // Even with error, don't show loader anymore
    isLoading.value = false
  }

  // After loading form data, get the outer wrapper by ID and read data attribute
  let counter = formData.value.counter || ''
  if (!counter && formWrapperRef.value) {
    const closest = formWrapperRef.value.closest('.ivyforms-frontend')
    if (closest && closest.dataset.counter) {
      counter = closest.dataset.counter
    }
  }
  if (counter) {
    const outerWrapper = document.getElementById('ivyforms-app-' + counter)
    if (outerWrapper && outerWrapper.dataset) {
      postId.value = outerWrapper.dataset.postId || ''
      refererUrl.value = outerWrapper.dataset.referer || document.referrer || ''
    }
  }
})

function getRequiredMessage(field: Field): string {
  const rm = field.requiredMessage
  if (typeof rm === 'string' && rm.trim() !== '') {
    return rm
  }
  let fieldMessage: string
  fieldMessage = ucFirst(field.label || field.type)
  fieldMessage += ` ${getLabel('required')}`
  return fieldMessage
}

const ucFirst = (str: string) => (str ? str[0]?.toLocaleUpperCase() + str.slice(1) : '')

// Helper function to parse date string to Date object with validation
const parseDateToObject = (dateStr: string, format: string): Date | null => {
  let day = 0,
    month = 0,
    year = 0
  let parts: string[] = []
  if (dateStr.indexOf('/') !== -1) {
    parts = dateStr.split('/')
  } else if (dateStr.indexOf('-') !== -1) {
    parts = dateStr.split('-')
  } else if (dateStr.indexOf('.') !== -1) {
    parts = dateStr.split('.')
  } else {
    parts = [dateStr]
  }

  if (format.startsWith('MM') || format.startsWith('M')) {
    // MM/DD/YYYY or M/D/YYYY
    month = parseInt(parts[0], 10)
    day = parseInt(parts[1], 10)
    year = parseInt(parts[2], 10)
  } else if (format.startsWith('DD') || format.startsWith('D')) {
    // DD/MM/YYYY or D/M/YYYY or DD.MM.YYYY
    day = parseInt(parts[0], 10)
    month = parseInt(parts[1], 10)
    year = parseInt(parts[2], 10)
  } else if (format.startsWith('YYYY')) {
    // YYYY-MM-DD or YYYY-M-D
    year = parseInt(parts[0], 10)
    month = parseInt(parts[1], 10)
    day = parseInt(parts[2], 10)
  }

  if (day && month && year) {
    const date = new Date(year, month - 1, day)
    // Validate that the date is actually valid (e.g., not Feb 31)
    if (date.getFullYear() === year && date.getMonth() === month - 1 && date.getDate() === day) {
      return date
    }
  }
  return null
}

// Get the most common label position for the form
const getFormLabelPosition = (): 'left' | 'right' | 'top' => {
  if (!formFields.value || formFields.value.length === 0) {
    return 'top'
  }

  // Count label positions
  const positionCounts = formFields.value.reduce((counts, field) => {
    const position = field.labelPosition || 'top'
    counts[position] = (counts[position] || 0) + 1
    return counts
  }, {})

  // Find the most common position
  const mostCommonPosition = Object.keys(positionCounts).reduce((a, b) =>
    positionCounts[a] > positionCounts[b] ? a : b,
  )

  // Convert to Element Plus format
  switch (mostCommonPosition) {
    case 'left':
      return 'left'
    case 'right':
      return 'right'
    case 'top':
    case 'inside':
    default:
      return 'top'
  }
}

// Validate form and submit
const validateAndSubmit = async () => {
  if (!formRef.value) return

  await formRef.value.validate(
    async (valid: boolean, errors: Record<string, { message: string }[]>) => {
      if (valid) {
        // Handle reCAPTCHA v3 execution before form submission
        const hasRecaptchaField = formFields.value.some((field) => field.type === 'recaptcha')

        if (hasRecaptchaField) {
          // Find reCAPTCHA field component ref
          const recaptchaFieldRef = formFieldsCollectorRefs.value?.find(
            (ref: ComponentPublicInstance): ref is RecaptchaFieldComponent =>
              ref &&
              (ref as RecaptchaFieldComponent).$props &&
              (ref as RecaptchaFieldComponent).$props.field &&
              (ref as RecaptchaFieldComponent).$props.field.type === 'recaptcha',
          )
          if (recaptchaFieldRef && recaptchaFieldRef.executeRecaptcha) {
            try {
              // For v3, this will execute the reCAPTCHA and get a token
              // For v2, this will return the current token
              const token = await recaptchaFieldRef.executeRecaptcha()

              // Update the form value with the token
              const fieldKey = `recaptcha_${recaptchaFieldRef.$props.field.fieldIndex}`
              formValues.value[fieldKey] = token
            } catch (error) {
              console.error('reCAPTCHA execution failed:', error)
              // You might want to show an error message to the user here
              return
            }
          }
        }

        // Handle Turnstile validation before form submission
        const hasTurnstileField = formFields.value.some((field) => field.type === 'turnstile')

        if (hasTurnstileField) {
          // Find Turnstile field component ref
          const turnstileFieldRef = formFieldsCollectorRefs.value?.find(
            (ref: ComponentPublicInstance): ref is TurnstileFieldComponent =>
              ref &&
              (ref as TurnstileFieldComponent).$props &&
              (ref as TurnstileFieldComponent).$props.field &&
              (ref as TurnstileFieldComponent).$props.field.type === 'turnstile',
          )

          if (turnstileFieldRef) {
            const fieldKey = `turnstile_${turnstileFieldRef.$props.field.fieldIndex}`
            const turnstileToken = formValues.value[fieldKey]

            // Check if Turnstile token exists
            if (!turnstileToken || turnstileToken.toString().trim() === '') {
              // Set error for the Turnstile field
              fieldErrors.value = {
                ...fieldErrors.value,
                [fieldKey]: getLabel('turnstile_complete_verification'),
              }
              scrollToFirstInvalidField()
              return
            }
          }
        }

        // Handle hCaptcha validation before form submission
        const hasHCaptchaField = formFields.value.some((field) => field.type === 'hcaptcha')

        if (hasHCaptchaField) {
          // Find hCaptcha field component ref
          const hcaptchaFieldRef = formFieldsCollectorRefs.value?.find(
            (ref: ComponentPublicInstance): ref is HCaptchaFieldComponent =>
              ref &&
              (ref as HCaptchaFieldComponent).$props &&
              (ref as HCaptchaFieldComponent).$props.field &&
              (ref as HCaptchaFieldComponent).$props.field.type === 'hcaptcha',
          )

          if (hcaptchaFieldRef) {
            const fieldKey = `hcaptcha_${hcaptchaFieldRef.$props.field.fieldIndex}`
            const hcaptchaToken = formValues.value[fieldKey]

            // Check if hCaptcha token exists
            if (!hcaptchaToken || hcaptchaToken.toString().trim() === '') {
              // Set error for the hCaptcha field
              fieldErrors.value = {
                ...fieldErrors.value,
                [fieldKey]: getLabel('hcaptcha_complete_verification'),
              }
              scrollToFirstInvalidField()
              return
            }
          }
        }

        await submitForm()
      } else {
        fieldErrors.value = {} // Clear previous
        Object.keys(errors).forEach((fieldKey) => {
          const rawMsg = errors[fieldKey][0]?.message || getLabel('invalid_input')
          const displayMsg = rawMsg
          ;(fieldErrors.value as Record<string, string>)[fieldKey] = displayMsg
        })
        scrollToFirstInvalidField()
      }
    },
  )
}

const scrollToFirstInvalidField = () => {
  // Try to find the first field with error
  // Look for error classes or aria-invalid, or use the error keys
  nextTick(() => {
    // Try by error class (Element Plus/ivyforms)
    const invalidField = document.querySelector(
      '.ivyforms-form-field .is-error input, .ivyforms-form-field .is-error textarea, .ivyforms-form-field .is-error select',
    )
    if (invalidField && typeof invalidField.scrollIntoView === 'function') {
      invalidField.scrollIntoView({ behavior: 'smooth', block: 'center' })
      if (invalidField instanceof HTMLElement && typeof invalidField.focus === 'function') {
        invalidField.focus({ preventScroll: true })
      }
    }
  })
}

// Clear error when user starts typing
const clearFieldError = (fieldKey: string) => {
  if ((fieldErrors.value as Record<string, unknown>)[fieldKey]) {
    delete (fieldErrors.value as Record<string, unknown>)[fieldKey]
  }
  // Also clear parent key for name subfields like name_<index>.nameFieldX
  if (fieldKey.includes('.')) {
    const parentKey = fieldKey.split('.')[0]
    if ((fieldErrors.value as Record<string, unknown>)[parentKey]) {
      delete (fieldErrors.value as Record<string, unknown>)[parentKey]
    }
  }
}

// Provide the clearFieldError function to child components
provide('clearFieldError', clearFieldError)
// Provide reactive form values so nested fields (e.g., email confirmation) can mutate the same model
provide('formValues', formValues)

const resetForm = () => {
  skipValidation.value = true
  formValues.value = {}
  nextTick(() => {
    skipValidation.value = false
  })
}
// Submit form
const submitForm = async () => {
  // Set submitting state
  isSubmitting.value = true

  // Clear previous errors before each submit
  fieldErrors.value = {}

  // Process form values to handle website fields with prefix/suffix
  const processedValues = { ...formValues.value }

  formFields.value.forEach((field) => {
    if (field.type === 'website') {
      const key = field.type + '_' + field.fieldIndex
      const fullValue = processedValues[key] || ''
      const prefix = field.inputPrefix || ''
      const suffix = field.inputSuffix || ''

      let userInput = fullValue

      // Only add prefix/suffix if the value doesn't already have them
      if (fullValue && (prefix || suffix)) {
        // Check if prefix is already present before adding
        if (prefix && !userInput.startsWith(prefix)) {
          userInput = prefix + userInput
        }

        // Check if suffix is already present before adding
        if (suffix && !userInput.endsWith(suffix)) {
          userInput = userInput + suffix
        }
      }

      processedValues[key] = userInput
    }
  })

  try {
    const { data, error, status } = await request('form/submission/', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      data: {
        formId: formData.value.id,
        values: processedValues,
        nonce: formNonce.value,
        postId: postId.value,
        referer: refererUrl.value,
      },
    })

    // Check for duplicate value error from backend
    if (data.data.data.is_duplicate) {
      const duplicateFields = data.data.data.duplicate_errors

      for (const field of formFields.value) {
        const fieldKey = field.type + '_' + field.fieldIndex

        if (duplicateFields[field.id]) {
          const fieldValue = formValues.value[fieldKey]
          if (fieldValue && fieldValue.toString().trim() !== '') {
            ;(fieldErrors.value as Record<string, string>)[fieldKey] = getLabel(
              'duplicate_value_error_label',
            ).replace('{label}', field.label?.toLowerCase() || 'value')
          }
        }
      }
      isSubmitting.value = false
      return
    }

    // Handle response
    if (status !== 200 || error) {
      IvyMessage({
        type: 'error',
        message: getLabel('error_submitting_form') + (error ? error.message : data),
      })
      isSubmitting.value = false
      return
    }

    // Show completed state briefly before hiding loader
    isSubmitCompleted.value = true
    await new Promise((resolve) => setTimeout(resolve, 600))

    // Reset form or show success message
    resetForm()

    isSubmitting.value = false
    isSubmitCompleted.value = false

    const type = formConfirmation.value[0].type
    const enabled = formConfirmation.value[0].enabled
    const showForm = formConfirmation.value[0].showForm

    if (!enabled) return

    if (type === 'successMessage') {
      if (!showForm) {
        showSuccessMessage.value = true
        successMessage.value =
          data.data.data.confirmation || formConfirmation.value[0].message || getLabel('thank_you')
        return
      } else {
        showSuccessMessageReset.value = true
        successMessage.value =
          data.data.data.confirmation || formConfirmation.value[0].message || getLabel('thank_you')

        // Hide the success message after 5 seconds
        setTimeout(() => {
          const messageElement = document.querySelector('.ivyforms-success-message')
          if (messageElement) {
            messageElement.classList.add('ivyforms-fade-out')
          }
          setTimeout(() => {
            showSuccessMessageReset.value = false
          }, 500) // Match the CSS transition duration
        }, 5000)
      }
    } else if (type === 'redirectToPage' && formConfirmation.value[0].pageUrl) {
      // Use pageUrl that was generated on the backend
      window.location.href = formConfirmation.value[0].pageUrl
    } else if (type === 'redirectToCustomUrl' && formConfirmation.value[0].url) {
      window.location.href = formConfirmation.value[0].url
    }
  } catch (error) {
    console.error(getLabel('error_submitting_form'), error)
    isSubmitting.value = false
    // Show error message
  }
}
</script>

<style lang="scss" scoped>
.ivyforms-form-wrapper {
  width: 100%;
  max-width: 720px;
  margin: 0 auto;
  padding: 0 16px;
  box-sizing: border-box;

  @media (max-width: 768px) {
    padding: 0 12px;
    max-width: 100%;
  }

  .ivyforms-form-title {
    font-size: 24px;
    font-weight: 600;
    margin-bottom: 12px;
    color: var(--map-base-text-0);
  }

  .ivyforms-form-description {
    font-size: 16px;
    margin-bottom: 24px;
    color: var(--map-base-text-1);
  }

  .ivyforms-form-row {
    max-width: 100%;
    box-sizing: border-box;

    // Mobile responsive - stack fields vertically on small screens
    @media (max-width: 768px) {
      flex-direction: column;
      gap: 8px;

      .ivyforms-form-field {
        width: 100% !important;
        max-width: 100% !important;
        flex: 1 1 100% !important;
      }
    }

    // Tablet - allow 2 columns
    @media (min-width: 769px) and (max-width: 1024px) {
      flex-wrap: wrap;

      .ivyforms-form-field {
        min-width: calc(50% - 8px);

        // If field has width > 50%, make it full width
        &[style*='flex: 100'],
        &[style*='flex: 75'],
        &[style*='flex: 66'] {
          flex: 1 1 100% !important;
        }
      }
    }
  }

  .ivyforms-form-field {
    min-width: 0;
    max-width: 100%;
    box-sizing: border-box;

    :deep(.el-input),
    :deep(.el-select),
    :deep(.el-textarea),
    :deep(.el-date-picker),
    :deep(.ivyforms-input) {
      max-width: 100%;
      box-sizing: border-box;
    }

    :deep(.el-input__wrapper) {
      max-width: 100%;
      box-sizing: border-box;
    }
  }

  .ivyforms-field {
    padding: 12px 0;
  }

  .ivyforms-form-submit {
    margin-top: 12px;
    display: flex;

    // Position modifiers
    &--default,
    &--left {
      justify-content: flex-start;
    }

    &--center {
      justify-content: center;
    }

    &--right {
      justify-content: flex-end;
    }
  }

  .ivyforms-form-item {
    .el-form-item {
      // When labelPosition is 'left' - use left label class for positioning
      &[class*='el-form-item--label-left'] {
        flex-direction: row;
        align-items: flex-start;

        .el-form-item__label {
          flex-shrink: 0;
          margin-right: 16px; // Add spacing between label and input
          margin-bottom: 0; // Remove bottom margin for side positioning
          min-width: 50px; // Ensure label has minimum width

          // Style the left label when positioned on the left
          .ivyforms-form-item__left-label {
            align-items: center;
            display: flex;
            gap: 4px;
          }
        }

        .el-form-item__content {
          flex: 1;
        }
      }

      // When labelPosition is 'right' - use right label class for positioning
      &[class*='el-form-item--label-right'] {
        flex-direction: row;
        align-items: flex-start;

        .el-form-item__label {
          flex-shrink: 0;
          margin-left: 16px; // Add spacing for right-positioned labels
          margin-right: 0;
          margin-bottom: 0;
          min-width: 50px;
          order: 2; // Move label to the right side

          // Style the right label when positioned on the right
          .ivyforms-form-item__right-label {
            align-items: center;
            display: flex;
            gap: 4px;
            // Override the underline styling for positional right labels
            color: var(--map-base-text-0);
            text-decoration: none;
            margin-left: 0;
          }
        }

        .el-form-item__content {
          flex: 1;
          order: 1; // Move content to the left side
        }
      }
    }
  }

  ::v-deep(.ivyforms-success-message) {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    padding: 20px;
    border: 1px solid var(--map-base-brand-symbol-0);
    border-radius: 8px;
    text-align: center;
    transition: opacity 0.5s ease-in-out;
    opacity: 1;

    &.ivyforms-fade-out {
      opacity: 0;
    }

    .ivyforms-safe-html {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;

      img {
        max-width: 100%;
        height: auto;
        margin: 10px 0;
      }
    }
  }
}
</style>
