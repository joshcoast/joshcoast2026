<template>
  <div
    :class="[
      'ivyforms-field ivyforms-field__rating ivyforms-width-100',
      'ivyforms-field__rating_' + field.id,
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
      <IvyRating
        :id="fieldID"
        v-model="localModelValue"
        :aria-label="field.label"
        class="ivyforms-field__rating-input ivyforms-width-100"
        :rating-icon="field.ratingIcon || 'star'"
        :options="field.fieldOptions || []"
        :disabled="disabled"
        :show-label="field.showRatingText ?? false"
      />
    </IvyFormItem>
  </div>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue'
import type { Field } from '@/types/field'

interface FieldProps {
  modelValue?: number | null
  field: Field
  disabled?: boolean
  error?: string
}

const props = withDefaults(defineProps<FieldProps>(), {
  modelValue: undefined,
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

// Handle input event to clear errors when user rates
const handleInput = (value: number | null | undefined) => {
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
  return `ivyforms-field__rating-input_${props.field.formId || ''}_${props.field.fieldIndex || ''}`
})
</script>
