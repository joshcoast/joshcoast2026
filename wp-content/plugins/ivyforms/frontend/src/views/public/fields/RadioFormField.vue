<template>
  <div
    :class="[
      'ivyforms-field',
      'ivyforms-field__radio',
      'ivyforms-field__radio_' + field.id,
      field.cssClasses,
    ]"
  >
    <IvyFormItem
      :label="field.hideLabel ? '' : field.label"
      :required="field.required"
      :error="error"
      :prop="field.type + '_' + field.fieldIndex"
      :show-info="!!field.description"
      :info-text="String(field.description || '')"
      :show-info-icon="false"
      :label-position="field.labelPosition === 'default' ? 'top' : field.labelPosition || 'top'"
    >
      <IvyRadioGroup
        :id="fieldID"
        v-model="localModelValue"
        :aria-label="field.label"
        :disabled="disabled"
        :readonly="field.readOnly"
        :required="field.required"
        direction="vertical"
        class="ivyforms-field__radio-group ivyforms-flex ivyforms-gap-8"
      >
        <IvyRadio
          v-for="option in radioOptions"
          :id="option.id"
          :key="option.id || option.value"
          :value="option.value"
          priority="secondary"
          class="ivyforms-field__radio-option"
        >
          {{ option.label }}
        </IvyRadio>
      </IvyRadioGroup>
    </IvyFormItem>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, watch, onMounted, inject } from 'vue'
import type { Field } from '@/types/field'
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

const fieldID = `field_${props.field.fieldIndex}`
const clearFieldError = inject<(fieldKey: string) => void>('clearFieldError', () => {})
const fieldKey = computed(() => props.field.type + '_' + props.field.fieldIndex)
const localModelValue = ref(props.modelValue)

watch(
  () => props.modelValue,
  (newVal) => {
    localModelValue.value = newVal
  },
)
watch(localModelValue, (newVal) => {
  emit('update:modelValue', newVal)
  clearFieldError(fieldKey.value)
})
const disabled = computed(() => props.disabled || props.field.readOnly)
const error = computed(() => props.error || '')
const field = computed(() => props.field)

// Shuffle options only once on mount
const shuffledOptions = ref([])
function shuffle<T>(array: T[]): T[] {
  const arr = array.slice()
  for (let i = arr.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1))
    ;[arr[i], arr[j]] = [arr[j], arr[i]]
  }
  return arr
}
onMounted(() => {
  let opts = field.value.fieldOptions || []
  if (field.value.shuffleOptions) {
    opts = shuffle(opts)
  }
  shuffledOptions.value = opts.map((opt) => ({
    label: opt.label,
    value: opt.value,
    id: opt.id,
  }))
})
const radioOptions = computed(() =>
  shuffledOptions.value.length
    ? shuffledOptions.value
    : (field.value.fieldOptions || []).map((opt) => ({
        label: opt.label,
        value: opt.value,
        id: opt.id,
      })),
)
</script>
