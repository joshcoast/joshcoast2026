<template>
  <div
    :class="[
      'ivyforms-field ivyforms-field__website',
      'ivyforms-field__website_' + field.id,
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
        class="ivyforms-field__website-input"
        type="text"
        :aria-label="field.label"
        :placeholder="field.placeholder"
        :disabled="disabled"
        :readonly="field.readOnly"
        :required="field.required"
        :maxlength="field.limitMaxLength ? field.maxLength : undefined"
      >
        <template v-if="field.inputPrefix" #prefix>
          <span class="ivyforms-field__website-prefix">{{ field.inputPrefix }}</span>
        </template>
        <template v-if="field.inputSuffix" #suffix>
          <span class="ivyforms-field__website-suffix">{{ field.inputSuffix }}</span>
        </template>
      </IvyTextInput>
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
  inputPrefix?: string
  inputSuffix?: string
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
  return `ivyforms-field__website-input_${props.field.formId || ''}_${props.field.fieldIndex || ''}`
})
</script>

<style scoped>
.ivyforms-field__website-prefix,
.ivyforms-field__website-suffix {
  display: inline-block;
  padding: 0 8px;
  font-size: 14px;
  line-height: 40px;
  color: var(--map-base-text-0);
}

.ivyforms-field__website-prefix {
  border-right: 1px solid var(--map-base-dusk-stroke--2);
}

.ivyforms-field__website-suffix {
  border-left: 1px solid var(--map-base-dusk-stroke--2);
}
</style>
