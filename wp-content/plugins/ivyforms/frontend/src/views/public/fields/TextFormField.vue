<template>
  <div
    :class="[
      'ivyforms-field ivyforms-field__text',
      'ivyforms-field__text_' + field.id,
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
      :show-info-icon="false"
      :label-position="field.labelPosition"
    >
      <IvyTextInput
        :id="fieldID"
        v-model="localModelValue"
        :aria-label="field.label"
        class="ivyforms-field__text-input"
        type="text"
        :placeholder="field.placeholder"
        :disabled="disabled"
        :readonly="field.readOnly"
        :required="field.required"
        :maxlength="field.limitMaxLength ? field.maxLength : undefined"
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
    return props.modelValue
  },
  set(value) {
    handleInput(value)
  },
})

// Generate unique field ID as a string
const fieldID = computed(() => {
  return `ivyforms-field__text-input_${props.field.formId || ''}_${props.field.fieldIndex || ''}`
})
</script>

<style scoped lang="scss">
.ivyforms-field__text {
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
