<template>
  <div
    :class="[
      'ivyforms-field ivyforms-field__phone',
      'ivyforms-field__phone_' + field.id,
      field.cssClasses,
    ]"
  >
    <IvyFormItem
      :label="field.hideLabel ? '' : field.label"
      :required="field.required"
      :error="error"
      :prop="field.type + '_' + field.fieldIndex"
      :show-info="!!field.description"
      :info-text="field.description || ''"
      :label-position="field.labelPosition"
      :show-info-icon="false"
    >
      <IvyPhoneInput
        :id="fieldID"
        v-model="localModelValue"
        class="ivyforms-field__phone-input"
        :placeholder="field.placeholder"
        :disabled="disabled || field.readOnly"
        :readonly="field.readOnly"
        :required="field.required"
        :phone-format="field.phoneFormat || 'national'"
        :auto-detect-country="field.phoneAutoDetect !== false"
        :aria-label="field.hideLabel ? field.label || 'Phone number' : ''"
        :aria-labelledby="field.hideLabel ? '' : `ivyforms-label-${fieldID}`"
      />
    </IvyFormItem>
  </div>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue'

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
  phoneFormat?: 'international' | 'national' | 'e164'
  phoneAutoDetect?: boolean
  defaultValue?: string
  requiredMessage?: string
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

// Inject clearFieldError function from parent
const clearFieldError = inject<(fieldKey: string) => void>('clearFieldError', () => {})

// Generate field key for clearing errors
const fieldKey = computed(() => {
  return props.field.type + '_' + props.field.fieldIndex
})

// Handle input event to clear errors when user starts typing
const handleInput = (value: string | number | null | undefined) => {
  emit('update:modelValue', value)
  clearFieldError(fieldKey.value)
}

const localModelValue = computed({
  get() {
    return props.modelValue != null ? String(props.modelValue) : ''
  },
  set(value) {
    handleInput(value)
  },
})
// Generate unique field ID as a string
const fieldID = computed(() => {
  return `ivyforms-field__phone-input_${props.field.formId || ''}_${props.field.fieldIndex || ''}`
})
</script>

<style scoped lang="scss">
.ivyforms-field__phone {
  &.is-readonly {
    :deep(.ivyforms-phone-input),
    :deep(.ivyforms-phone-input:hover),
    :deep(.ivyforms-phone-input.is-focus) {
      border-color: var(--map-base-dusk-stroke--2) !important;
      box-shadow: none !important;
    }
  }
  :deep(.ivyforms-phone-input__arrow) {
    .ivyforms-icon__svg {
      width: auto;
    }
  }
}
</style>
