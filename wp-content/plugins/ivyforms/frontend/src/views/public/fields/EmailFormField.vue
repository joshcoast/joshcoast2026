<template>
  <div
    :class="[
      'ivyforms-field ivyforms-field__email',
      'ivyforms-field__email_' + field.id,
      field.cssClasses,
    ]"
  >
    <IvyFormItem
      :label="field.hideLabel ? '' : field.label"
      :required="field.required"
      :error="error"
      :prop="field.type + '_' + field.fieldIndex"
      :show-info="!!field.description && !error"
      :info-text="field.description || ''"
      :show-info-icon="false"
      :label-position="field.labelPosition"
    >
      <IvyTextInput
        :id="fieldID"
        v-model="localModelValue"
        class="ivyforms-field__email-input"
        type="email"
        :aria-label="field.label"
        :placeholder="field.placeholder"
        :disabled="disabled"
        :readonly="field.readOnly"
        :required="field.required"
        :maxlength="field.limitMaxLength ? field.maxLength : undefined"
        @blur="syncMainFromDom"
      />
    </IvyFormItem>

    <!-- Confirmation field: appears only when enabled -->
    <IvyFormItem
      v-if="field.confirmFieldEnabled"
      :label="field.confirmFieldHideLabel ? '' : confirmLabel"
      :required="!!localModelValue && localModelValue.toString().trim() !== ''"
      :prop="confirmKey"
    >
      <IvyTextInput
        :id="confirmFieldID"
        v-model="confirmModelValue"
        :aria-label="confirmLabel"
        class="ivyforms-field__email-input ivyforms-field__email-input--confirm"
        type="email"
        :placeholder="confirmPlaceholder"
        :disabled="disabled"
        :readonly="field.readOnly"
        :required="!!localModelValue && localModelValue.toString().trim() !== ''"
        @blur="syncConfirmFromDom"
      />
    </IvyFormItem>
  </div>
</template>

<script setup lang="ts">
import { computed, inject, watch, type Ref, onMounted } from 'vue'
import { useAutofillDetection, type AutofillField } from '@/composables/useAutofillDetection'
import { useLabels } from '@/composables/useLabels'

const { getLabel } = useLabels()

interface Field {
  id: number
  formId: number
  fieldIndex: number
  type: string
  label: string
  placeholder?: string
  required?: boolean
  hideLabel?: boolean
  readOnly?: boolean
  description?: string
  cssClasses?: string
  confirmFieldEnabled?: boolean
  confirmFieldLabel?: string
  confirmFieldPlaceholder?: string
  confirmFieldHideLabel?: boolean
  limitMaxLength?: boolean
  maxLength?: number
  labelPosition?: string
  noDuplicates?: boolean
  // Any other field properties from your data
}

interface FieldProps {
  modelValue: string | number | null | undefined
  field: Field
  disabled?: boolean
  error?: string
}

const props = withDefaults(defineProps<FieldProps>(), {
  modelValue: '',
  disabled: false,
  error: '',
})

const emit = defineEmits(['update:modelValue'])

// Define acceptable form values shape
type FormValues = Record<string, string | number | null | undefined>
type InjectedFormValues = Ref<FormValues> | FormValues | null

// Inject global formValues (may be provided as a ref) and error clearer
const injectedFormValues = inject<InjectedFormValues>('formValues', null)
const formValues = computed(() => {
  if (!injectedFormValues) return {} as FormValues
  return (
    typeof injectedFormValues === 'object' && 'value' in injectedFormValues
      ? (injectedFormValues as Ref<FormValues>).value
      : injectedFormValues
  ) as FormValues
})
const clearFieldError = inject<(fieldKey: string) => void>('clearFieldError', () => {})

// Keys
const mainKey = computed(() => props.field.type + '_' + props.field.fieldIndex)
const confirmKey = computed(() => 'emailConfirm_' + props.field.fieldIndex)

// Handle main input
const handleInput = (value: string | number | null | undefined) => {
  emit('update:modelValue', value)
  clearFieldError(mainKey.value)
}

const localModelValue = computed({
  get() {
    return props.modelValue
  },
  set(value) {
    handleInput(value)
  },
})

// Confirmation input model (stored directly in injected formValues)
const confirmModelValue = computed<string | number | null | undefined>({
  get() {
    return formValues.value[confirmKey.value]
  },
  set(value) {
    formValues.value[confirmKey.value] = value
    clearFieldError(confirmKey.value)
  },
})

// Labels / placeholders
const confirmLabel = computed(
  () => props.field.confirmFieldLabel || getLabel('confirmation_label_placeholder'),
)
const confirmPlaceholder = computed(() => props.field.confirmFieldPlaceholder)

// IDs
const fieldID = computed(
  () => `ivyforms-field__email-input_${props.field.formId || ''}_${props.field.fieldIndex || ''}`,
)
const confirmFieldID = computed(
  () =>
    `ivyforms-field__email-confirm-input_${props.field.formId || ''}_${props.field.fieldIndex || ''}`,
)
// Sync main input from DOM on blur (includes autofill)
function syncMainFromDom(e: FocusEvent) {
  const el = e.target as HTMLInputElement | null
  if (!el) return
  if (el.value && el.value !== localModelValue.value) {
    localModelValue.value = el.value
  }
}

// Sync confirm input from DOM
function syncConfirmFromDom(e: FocusEvent) {
  const el = e.target as HTMLInputElement | null
  if (!el) return
  if (el.value !== formValues.value[confirmKey.value]) {
    formValues.value[confirmKey.value] = el.value
    clearFieldError(confirmKey.value)
  }
}
// Auto clear mismatch error when emails become equal
watch(
  () => [formValues.value[confirmKey.value], localModelValue.value],
  ([confirmVal, mainVal]) => {
    if (confirmVal && mainVal && confirmVal === mainVal) {
      clearFieldError(confirmKey.value)
    }
  },
)

// Setup autofill detection using the composable
const autofillFields = computed<AutofillField[]>(() => {
  const fields: AutofillField[] = [
    {
      id: fieldID.value,
      getValue: () => localModelValue.value?.toString() || '',
      setValue: (value: string) => {
        localModelValue.value = value
      },
    },
  ]

  // Add confirmation field if enabled and visible
  if (props.field.confirmFieldEnabled && localModelValue.value) {
    fields.push({
      id: confirmFieldID.value,
      getValue: () => formValues.value[confirmKey.value]?.toString() || '',
      setValue: (value: string) => {
        formValues.value[confirmKey.value] = value
      },
      onClearError: () => clearFieldError(confirmKey.value),
    })
  }

  return fields
})

const { restartDetection, checkForInitialAutofill } = useAutofillDetection({
  fields: autofillFields.value,
  pollingDuration: 7000, // Increased for slow autofill
  pollingInterval: 100,
  initialCheckDelay: 100, // Faster initial check
})

// Manual check on mount and when fields become available
onMounted(() => {
  // Initial check immediately after mount
  setTimeout(checkForInitialAutofill, 50)
  // Additional checks for delayed autofill
  setTimeout(checkForInitialAutofill, 300)
  setTimeout(checkForInitialAutofill, 1000)
})

// Re-setup detection when confirmation field becomes available
watch(
  () => props.field.confirmFieldEnabled && !!localModelValue.value,
  (showConfirm) => {
    if (showConfirm) {
      setTimeout(restartDetection, 100)
    }
  },
)
</script>

<style scoped lang="scss">
.ivyforms-field__email-input--confirm {
  margin-top: 4px;
}

.ivyforms-field__email {
  &.is-readonly {
    :deep(.el-input__wrapper),
    :deep(.el-input__wrapper:hover),
    :deep(.el-input__wrapper.is-focus) {
      border-color: var(--map-base-dusk-stroke--2) !important;
      box-shadow: none !important;
    }
  }
}
</style>
