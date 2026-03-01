<template>
  <IvyCollapse :accordion="false">
    <template v-for="(item, fieldIndex) in reactiveCollapseItems" :key="fieldIndex">
      <IvyCollapseItem
        :class="['ivyforms-add-field__collapse-item']"
        :title="item.title"
        :name="item.name"
        :coming-soon="item.dragFields.length === 0"
      >
        <div class="ivyforms-add-field__drag-area ivyforms-gap-8">
          <!-- Native draggable fields (not disabled) -->
          <div
            class="ivyforms-add-field__draggable-wrapper ivyforms-flex ivyforms-gap-8 ivyforms-flex-wrap"
          >
            <template v-for="element in item.draggableFields" :key="element.type">
              <div
                :class="[
                  'ivyforms-add-field__wrapper ivforms-flex',
                  { 'non-draggable': element.comingSoon || element.pro },
                ]"
                :draggable="!element.comingSoon && !element.pro && !formBuilderStore.isFormLoading"
                @dragstart="onDragStart($event, element)"
                @dragend="onDragEnd($event)"
              >
                <IvyAddField
                  :field-type="element.type"
                  :field-name="element.name"
                  :icon-start="element.iconStart"
                  :icon-start-category="element.iconStartCategory"
                  :icon-start-type="element.iconStartType"
                  :icon-end="element.iconEnd"
                  :icon-end-category="element.iconEndCategory"
                  :icon-end-type="element.iconEndType"
                  :coming-soon="element.comingSoon"
                  :disabled="filterDisabled(element)"
                  :pro="element.pro"
                  :use-pro-badge="element.pro"
                  @click="addFieldFromClick(element.type)"
                />
              </div>
            </template>
          </div>
        </div>
      </IvyCollapseItem>
    </template>
  </IvyCollapse>
</template>

<script setup lang="ts">
import type { DragField } from '@/types/field'
import { useFormBuilder } from '@/stores/useFormBuilder.ts'
import { useSettingsStore } from '@/stores/useSettingsStore.ts'
import { useLabels } from '@/composables/useLabels'
import { computed, onMounted } from 'vue'
import { useProFeatures } from '@/composables/useProFeatures'
import { useProFeatureUpgrade } from '@/composables/useProFeatureUpgrade'
import api from '@/composables/IvyFormAPI'
import { useFormBuilderDraggable } from '@/composables/useFormBuilderDraggable'

const { getLabel } = useLabels()
const formBuilderStore = useFormBuilder()
const settingsStore = useSettingsStore()
const { onFieldDragEnd } = useFormBuilderDraggable()
const { checkFeatureAccess, getFeatureSlugForFieldType } = useProFeatureUpgrade()

// Pro features detection - only initialize if pro plugin bridge is available
const proInstance = window.IvyForms?.pro ? useProFeatures() : null

// Load settings on component mount to ensure reCAPTCHA configuration is available
onMounted(async () => {
  if (proInstance) {
    await proInstance.fetch() // REST-only load of pro features
  }
  if (!settingsStore.allSettings) {
    await settingsStore.loadAllSettings()
  }
})

type CollapseCategory = 'general' | 'security' | 'advanced'
interface CollapseItem {
  title: string
  name: string
  category: CollapseCategory
  dragFields: DragField[]
}

// Build immutable field view models to prevent mutation and reduce redundant calls
const buildDraggableFields = (fields: DragField[]) => {
  const recaptchaAdded = formBuilderStore.fields.some((f) => f.type === 'recaptcha')
  const turnstileAdded = formBuilderStore.fields.some((f) => f.type === 'turnstile')
  const hcaptchaAdded = formBuilderStore.fields.some((f) => f.type === 'hcaptcha')
  const captchaFieldAdded = recaptchaAdded || turnstileAdded || hcaptchaAdded

  return fields.map((field) => {
    // For fields with featureSlug, respect Pro plugin's decision but add debugging
    if (field.featureSlug) {
      //const available = hasFeature(field.featureSlug)
      // Use the field's current pro flag (already set by Pro plugin filter) rather than overriding
      const finalPro = field.pro // Pro plugin already decided this
      const isDisabled =
        field.comingSoon ||
        ((field.type === 'recaptcha' || field.type === 'turnstile' || field.type === 'hcaptcha') &&
          captchaFieldAdded)

      return {
        ...field,
        pro: finalPro,
        _precomputedDisabled: isDisabled,
      }
    }

    // For fields without featureSlug, use original pro flag
    const isDisabled =
      field.pro ||
      field.comingSoon ||
      ((field.type === 'recaptcha' || field.type === 'turnstile' || field.type === 'hcaptcha') &&
        captchaFieldAdded)

    return {
      ...field,
      _precomputedDisabled: isDisabled,
    }
  })
  // Keep all fields visible - disabled state is handled by _precomputedDisabled
}
const filterDisabled = (field: DragField & { _precomputedDisabled?: boolean }) => {
  // Use precomputed value if available to reduce redundant calculations
  if (field._precomputedDisabled !== undefined) {
    return field._precomputedDisabled
  }

  // Fallback for non-precomputed fields
  if (field.comingSoon || field.pro) return true
  if (field.type === 'recaptcha' || field.type === 'turnstile' || field.type === 'hcaptcha') {
    return (
      formBuilderStore.fields.some((f) => f.type === 'recaptcha') ||
      formBuilderStore.fields.some((f) => f.type === 'turnstile') ||
      formBuilderStore.fields.some((f) => f.type === 'hcaptcha')
    )
  }
  return false
}

// Create reactive computed properties for each section's draggable fields
const reactiveCollapseItems = computed(() => {
  // Force reactivity by watching both collapseItems and pro loading state

  return collapseItems.value.map((item) => ({
    ...item,
    draggableFields: buildDraggableFields(item.dragFields),
  }))
})

const addFieldFromClick = (fieldType: string) => {
  // Block if form is loading
  if (formBuilderStore.isFormLoading) return

  // Check if this field requires Pro and show dialog if license not active
  const sourceItem = collapseItems.value
    .flatMap((i) => i.dragFields)
    .find((f) => f.type === fieldType)

  // If field is marked as pro OR has a feature slug, check access
  if (sourceItem?.pro || sourceItem?.featureSlug) {
    const featureSlug = sourceItem.featureSlug || getFeatureSlugForFieldType(fieldType)
    if (featureSlug && !checkFeatureAccess(featureSlug)) {
      // checkFeatureAccess will show the appropriate upgrade dialog
      return
    }
  }

  // Check if it's reCAPTCHA and if it's already added
  if (fieldType === 'recaptcha') {
    const hasRecaptcha = formBuilderStore.fields.some((field) => field.type === 'recaptcha')
    if (hasRecaptcha) {
      // TODO: Show notification that reCAPTCHA is already added
      return
    }
  }

  // Check if it's Turnstile and if it's already added
  if (fieldType === 'turnstile') {
    const hasTurnstile = formBuilderStore.fields.some((field) => field.type === 'turnstile')
    if (hasTurnstile) {
      // TODO: Show notification that Turnstile is already added
      return
    }
  }

  // Check if it's hCaptcha and if it's already added
  if (fieldType === 'hcaptcha') {
    const hasHCaptcha = formBuilderStore.fields.some((field) => field.type === 'hcaptcha')
    if (hasHCaptcha) {
      // TODO: Show notification that hCaptcha is already added
      return
    }
  }

  // Ensure counterFields is a valid number before incrementing
  if (!Number.isFinite(formBuilderStore.counterFields) || formBuilderStore.counterFields < 0) {
    formBuilderStore.counterFields = 0
  }

  // Only increase counter if we're actually going to add the field
  const counterFields = formBuilderStore.increaseCounter()
  const newField = transformField({ type: fieldType }, counterFields)

  // Clear all dragging states to prevent drop zones from blocking clicks
  formBuilderStore.isDraggingFromPanel = false

  // Add field to a new row at the end (click always creates new row)
  const maxRow =
    formBuilderStore.fields.length > 0
      ? Math.max(...formBuilderStore.fields.map((f) => f.rowIndex ?? 0))
      : -1

  const newRowIndex = maxRow + 1

  formBuilderStore.addField({
    ...newField,
    position: formBuilderStore.fields.length + 1,
    rowIndex: newRowIndex,
    columnIndex: 0,
    width: 100,
  })

  updateFieldPositions()
}

const updateFieldPositions = () => {
  formBuilderStore.fields.forEach((field, fieldIndex) => {
    field.position = fieldIndex + 1
  })
}
const onDragStart = (event: DragEvent, element: DragField) => {
  // Prevent drag when loading or disabled
  if (formBuilderStore.isFormLoading || element.comingSoon || element.pro) {
    event.preventDefault()
    return
  }

  formBuilderStore.isDraggingFromPanel = true

  // Clone and transform the field
  const fieldData = cloneItem(element)

  // Store the field data to the drag event's dataTransfer
  if (event.dataTransfer) {
    event.dataTransfer.effectAllowed = 'move' // Use 'move' instead of 'copy' to avoid green plus circle
    event.dataTransfer.setData('fieldData', JSON.stringify(fieldData))

    // Create a visible drag preview showing the field being dragged
    const target = event.target as HTMLElement
    const fieldElement = target.closest('.ivyforms-add-field__wrapper') as HTMLElement
    if (fieldElement) {
      // Clone the element for drag preview
      const clone = fieldElement.cloneNode(true) as HTMLElement
      clone.style.position = 'absolute'
      clone.style.top = '-9999px'
      clone.style.width = fieldElement.offsetWidth + 'px'
      clone.style.opacity = '0.8'
      document.body.appendChild(clone)

      // Use the clone as drag image
      event.dataTransfer.setDragImage(
        clone,
        fieldElement.offsetWidth / 2,
        fieldElement.offsetHeight / 2,
      )

      setTimeout(() => {
        if (clone.parentNode === document.body) {
          document.body.removeChild(clone)
        }
      }, 0)
    }
  }
}

const onDragEnd = (event?: DragEvent) => {
  // Reset only our own drag state; let the draggable composable handle builder drag state
  formBuilderStore.isDraggingFromPanel = false
  onFieldDragEnd()

  // Clear any dataTransfer payload to avoid stale data on some browsers
  if (event?.dataTransfer) {
    event.dataTransfer.clearData()
  }
}

const cloneItem = (item: DragField) => {
  const counterFields = formBuilderStore.increaseCounter()
  return transformField(item, counterFields)
}
const transformField = (item, counterFields) => {
  // Allow external code to transform the field being added
  // This hook allows you to modify the field object before it is added to the form.
  // You can change properties like label, required, defaultValue, etc.
  // If you return a non-null value, it will be used as the new field object
  // instead of the default transformations below.
  // Note: counterFields is incremented before calling this function, so it's
  // safe to use it for fieldIndex or IDs.
  const external = api.hooks.applyFilters(
    'ivyforms/builder/add/transform',
    null,
    item,
    counterFields,
    { getLabel },
  )
  if (external) {
    return external
  }

  switch (item.type) {
    case 'text':
      return {
        id: 0,
        fieldIndex: counterFields,
        type: 'text',
        label: getLabel('text'),
        required: false,
        defaultValue: '',
        placeholder: '',
        position: 0,
        labelPosition: 'default',
      }
    case 'email':
      return {
        id: 0,
        fieldIndex: counterFields,
        type: 'email',
        label: getLabel('email'),
        required: false,
        defaultValue: '',
        placeholder: '',
        position: 0,
        labelPosition: 'default',
      }
    case 'number':
      return {
        id: 0,
        fieldIndex: counterFields,
        type: 'number',
        label: getLabel('number'),
        required: false,
        defaultValue: '',
        placeholder: '',
        position: 0,
        labelPosition: 'default',
      }
    case 'textarea':
      return {
        id: 0,
        fieldIndex: counterFields,
        type: 'textarea',
        label: getLabel('paragraph'),
        required: false,
        defaultValue: '',
        placeholder: '',
        position: 0,
        labelPosition: 'default',
      }
    case 'phone':
      return {
        id: 0,
        fieldIndex: counterFields,
        type: 'phone',
        label: getLabel('phone'),
        required: false,
        defaultValue: '',
        placeholder: '',
        position: 0,
        labelPosition: 'default',
        phoneAutoDetect: true,
      }
    case 'website':
      return {
        id: 0,
        fieldIndex: counterFields,
        type: 'website',
        label: getLabel('website'),
        required: false,
        defaultValue: '',
        placeholder: '',
        position: 0,
        labelPosition: 'default',
      }
    case 'date':
      return {
        id: 0,
        fieldIndex: counterFields,
        type: 'date',
        label: getLabel('date_picker'),
        required: false,
        defaultValue: '',
        placeholder: '',
        position: 0,
        dateFieldType: 'picker' as const,
        dateFormat: 'MM/DD/YYYY' as const,
        labelPosition: 'default',
      }
    case 'time':
      return {
        id: 0,
        fieldIndex: counterFields,
        type: 'time',
        label: getLabel('time_picker'),
        required: false,
        defaultValue: '',
        placeholder: '',
        position: 0,
        timeFieldType: 'time-picker' as const,
        timeFormat: '24h' as const,
        labelPosition: 'default',
      }
    case 'recaptcha':
      return {
        id: 0,
        fieldIndex: counterFields,
        type: 'recaptcha',
        label: getLabel('recaptcha'),
        required: false,
        defaultValue: '',
        placeholder: '',
        position: 0,
        labelPosition: 'default',
      }
    case 'turnstile':
      return {
        id: 0,
        fieldIndex: counterFields,
        type: 'turnstile',
        label: getLabel('turnstile'),
        required: false,
        defaultValue: '',
        placeholder: '',
        position: 0,
        labelPosition: 'default',
      }
    case 'hcaptcha':
      return {
        id: 0,
        fieldIndex: counterFields,
        type: 'hcaptcha',
        label: getLabel('hcaptcha'),
        required: false,
        defaultValue: '',
        placeholder: '',
        position: 0,
        labelPosition: 'default',
      }
    case 'radio':
      return {
        id: 0,
        fieldIndex: counterFields,
        type: 'radio',
        label: getLabel('radio_button'),
        required: false,
        defaultValue: getLabel('option_1'),
        placeholder: '',
        position: 0,
        fieldOptions: [
          {
            id: 0,
            label: getLabel('option_1'),
            value: 'option1',
            isDefault: true,
            position: 1,
          },
          {
            id: 1,
            label: getLabel('option_2'),
            value: 'option2',
            isDefault: false,
            position: 2,
          },
        ],
        shuffleOptions: false,
        showValues: true,
      }
    case 'select':
      return {
        id: 0,
        fieldIndex: counterFields,
        type: 'select',
        label: getLabel('dropdown'),
        required: false,
        defaultValue: getLabel('option_1'),
        placeholder: '',
        position: 0,
        fieldOptions: [
          {
            id: 0,
            label: getLabel('option_1'),
            value: 'option1',
            isDefault: true,
            position: 1,
          },
          {
            id: 1,
            label: getLabel('option_2'),
            value: 'option2',
            isDefault: false,
            position: 2,
          },
        ],
        shuffleOptions: false,
        showValues: true,
        enableSearch: false,
      }
    case 'rating':
      return {
        id: 0,
        fieldIndex: counterFields,
        type: 'rating',
        label: getLabel('rating'),
        required: false,
        defaultValue: '',
        placeholder: '',
        position: 0,
        labelPosition: 'default',
        ratingIcon: 'star',
        fieldOptions: [
          {
            id: 1,
            label: getLabel('good'),
            value: '1',
            isDefault: false,
          },
          {
            id: 2,
            label: getLabel('nice'),
            value: '2',
            isDefault: false,
          },
          {
            id: 3,
            label: getLabel('very_good'),
            value: '3',
            isDefault: false,
          },
          {
            id: 4,
            label: getLabel('awesome'),
            value: '4',
            isDefault: false,
          },
          {
            id: 5,
            label: getLabel('amazing'),
            value: '5',
            isDefault: false,
          },
        ],
        showValues: false,
      }
    case 'address': {
      const addressFieldTypes = ['streetAddress', 'addressLine2', 'city', 'state', 'zip', 'country']
      const addressFields = addressFieldTypes.map((type) => ({
        type,
        id: '',
        label: getLabel(type === 'addressLine2' ? 'address_line_2' : type),
        value: '',
        placeholder: '',
        required: false,
        hideLabel: false,
        description: '',
        requiredMessage: '',
        visible: true,
      }))
      return {
        id: 0,
        fieldIndex: counterFields,
        type: 'address',
        label: getLabel('address'),
        hideLabel: false,
        cssClasses: '',
        labelPosition: 'top',
        addressFieldTypes,
        addressFields,
        countryDefaultValue: '',
        position: 0,
      }
    }
    case 'name': {
      const nameFieldTypes = ['nameField1', 'nameField2']
      const nameFields = nameFieldTypes.map((type, idx) => ({
        type,
        id: '',
        modelValue: '',
        label: getLabel(idx === 0 ? 'first_name' : 'last_name'),
        required: false,
        optionHide: false,
        description: '',
        placeholder: '',
      }))
      return {
        id: 0,
        fieldIndex: counterFields,
        type: 'name',
        label: getLabel('name'),
        required: false,
        defaultValue: '',
        placeholder: '',
        position: 0,
        nameFieldTypes,
        nameFields,
      }
    }
    case 'checkbox':
      return {
        id: 0,
        fieldIndex: counterFields,
        type: 'checkbox',
        label: getLabel('checkbox'),
        required: false,
        placeholder: '',
        position: 0,
        fieldOptions: [
          {
            id: 0,
            label: getLabel('choice_1'),
            value: 'choice1',
            isDefault: false,
            position: 1,
          },
          {
            id: 1,
            label: getLabel('choice_2'),
            value: 'choice2',
            isDefault: false,
            position: 2,
          },
          {
            id: 2,
            label: getLabel('choice_3'),
            value: 'choice3',
            isDefault: false,
            position: 3,
          },
        ],
        shuffleOptions: false,
        showValues: true,
      }
  }
}

const baseCollapseItems: CollapseItem[] = [
  {
    title: getLabel('general'),
    name: '1',
    category: 'general',
    dragFields: [
      {
        type: 'text',
        name: getLabel('text'),
        iconStart: 'field',
        iconStartType: 'outline',
        iconStartCategory: 'global',
        iconEnd: '',
        iconEndType: 'fill',
        iconEndCategory: 'global',
      },
      {
        type: 'email',
        name: getLabel('email'),
        iconStart: 'email',
        iconStartType: 'outline',
        iconStartCategory: 'global',
        iconEnd: '',
        iconEndType: 'fill',
        iconEndCategory: 'global',
      },
      {
        type: 'number',
        name: getLabel('number'),
        iconStart: 'hashtag',
        iconStartType: 'outline',
        iconStartCategory: 'global',
        iconEnd: '',
        iconEndType: 'fill',
        iconEndCategory: 'global',
      },
      {
        type: 'textarea',
        name: getLabel('paragraph'),
        iconStart: 'paragraph',
        iconStartType: 'outline',
        iconStartCategory: 'global',
        iconEnd: '',
        iconEndType: 'fill',
        iconEndCategory: 'global',
      },
      {
        type: 'phone',
        name: getLabel('phone'),
        iconStart: 'phone',
        iconStartType: 'outline',
        iconStartCategory: 'global',
        iconEnd: '',
        iconEndType: 'fill',
        iconEndCategory: 'global',
      },
      {
        type: 'website',
        name: getLabel('website'),
        iconStart: 'globe',
        iconStartType: 'outline',
        iconStartCategory: 'global',
        iconEnd: '',
        iconEndType: 'fill',
        iconEndCategory: 'global',
      },
      {
        type: 'name',
        name: getLabel('name'),
        iconStart: 'user',
        iconStartType: 'outline',
        iconStartCategory: 'global',
        iconEnd: '',
        iconEndType: 'fill',
        iconEndCategory: 'global',
      },
      {
        type: 'address',
        name: getLabel('address'),
        iconStart: 'location',
        iconStartType: 'outline',
        iconStartCategory: 'global',
        iconEnd: '',
        iconEndType: 'fill',
        iconEndCategory: 'global',
      },
      {
        type: 'radio',
        name: getLabel('radio_button'),
        iconStart: 'radio',
        iconStartType: 'outline',
        iconStartCategory: 'global',
        iconEnd: '',
        iconEndType: 'fill',
        iconEndCategory: 'global',
      },
      {
        type: 'checkbox',
        name: getLabel('checkbox'),
        iconStart: 'checkbox',
        iconStartType: 'outline',
        iconStartCategory: 'global',
        iconEnd: '',
        iconEndType: 'fill',
        iconEndCategory: 'global',
      },
      {
        type: 'select',
        name: getLabel('dropdown'),
        iconStart: 'chevron-square-down',
        iconStartType: 'outline',
        iconStartCategory: 'arrows',
        iconEnd: 'none',
        iconEndType: 'fill',
        iconEndCategory: 'global',
      },
      {
        type: 'rating',
        name: getLabel('rating'),
        iconStart: 'star',
        iconStartType: 'outline',
        iconStartCategory: 'global',
        iconEnd: '',
        iconEndType: 'fill',
        iconEndCategory: 'global',
      },
      {
        type: 'slider',
        name: 'Slider',
        iconStart: 'slider',
        iconStartType: 'outline',
        iconStartCategory: 'global',
        iconEnd: '',
        iconEndType: 'fill',
        iconEndCategory: 'global',
        comingSoon: true,
      },
      {
        type: 'html',
        name: 'HTML Code',
        iconStart: 'code',
        iconStartType: 'outline',
        iconStartCategory: 'global',
        iconEnd: '',
        iconEndType: 'fill',
        iconEndCategory: 'global',
        comingSoon: true,
      },
    ],
  },
  {
    title: getLabel('security'),
    name: '2',
    category: 'security',
    dragFields: [
      {
        type: 'recaptcha',
        name: getLabel('recaptcha'),
        iconStart: 'recaptcha',
        iconStartType: 'fill',
        iconStartCategory: 'security',
        iconEnd: '',
        iconEndType: 'fill',
        iconEndCategory: 'global',
      },
      {
        type: 'turnstile',
        name: getLabel('turnstile'),
        iconStart: 'turnstile',
        iconStartType: 'fill',
        iconStartCategory: 'security',
        iconEnd: '',
        iconEndType: 'fill',
        iconEndCategory: 'global',
      },
      {
        type: 'hcaptcha',
        name: getLabel('hcaptcha'),
        iconStart: 'hcaptcha',
        iconStartType: 'fill',
        iconStartCategory: 'security',
        iconEnd: '',
        iconEndType: 'fill',
        iconEndCategory: 'global',
      },
    ],
  },
  {
    title: getLabel('advanced'),
    name: '3',
    category: 'advanced',
    dragFields: [
      {
        type: 'date',
        name: getLabel('date'),
        iconStart: 'calendar-dot',
        iconStartType: 'outline',
        iconStartCategory: 'global',
        iconEnd: '',
        iconEndType: 'fill',
        iconEndCategory: 'global',
      },
      {
        type: 'time',
        name: getLabel('time'),
        iconStart: 'clock',
        iconStartType: 'outline',
        iconStartCategory: 'global',
        iconEnd: '',
        iconEndType: 'fill',
        iconEndCategory: 'global',
      },
      {
        type: 'password',
        name: getLabel('password'),
        iconStart: 'key',
        iconStartType: 'outline',
        iconStartCategory: 'global',
        iconEnd: '',
        iconEndType: 'fill',
        iconEndCategory: 'global',
        featureSlug: 'password_field',
        pro: true,
      },
      {
        type: 'signature',
        name: getLabel('signature'),
        iconStart: 'signature2',
        iconStartType: 'outline',
        iconStartCategory: 'global',
        iconEnd: '',
        iconEndType: 'fill',
        iconEndCategory: 'global',
        featureSlug: 'signature_field',
        pro: true,
      },
    ],
  },
]

// Make collapseItems reactive so it updates when pro filters are registered
const collapseItems = computed<CollapseItem[]>(() => {
  // Watch for changes in the hooks system - this triggers when filters are added
  api.hooks.updateCounter.value // eslint-disable-line @typescript-eslint/no-unused-expressions

  // Also watch pro loading state to re-compute when pro becomes available
  if (proInstance) {
    proInstance.loaded.value // eslint-disable-line @typescript-eslint/no-unused-expressions
  }

  // This hook allows you to modify the collapse items in the form builder.
  // You can add, remove, or change the fields that are
  // displayed in the collapsible sections.
  const out = api.hooks.applyFilters<CollapseItem[]>(
    'ivyforms/builder/filter/collapse_items',
    baseCollapseItems,
    { getLabel },
  )

  return Array.isArray(out) ? out : baseCollapseItems
})
</script>
<style lang="scss">
.ivyforms-add-field {
  &__collapse-item {
    &.ivyforms-collapse-item {
      &.el-collapse-item {
        .el-collapse-item__content {
          display: block !important;
        }
      }
    }
  }

  &__draggable-wrapper {
    /* Two-column responsive flex layout for field tiles */
    display: flex;
    flex-wrap: wrap;
    gap: 8px; /* horizontal & vertical spacing */
    width: 100%;

    /* Each draggable item wrapper - default two-column layout */
    .ivyforms-add-field__wrapper {
      flex: 0 0 calc(50% - 4px) !important;
      max-width: calc(50% - 4px) !important;
      min-width: calc(50% - 4px) !important;
      box-sizing: border-box !important;

      &.sortable-chosen {
        display: block;
      }
    }

    .non-draggable {
      cursor: not-allowed;
    }
  }
}
</style>
