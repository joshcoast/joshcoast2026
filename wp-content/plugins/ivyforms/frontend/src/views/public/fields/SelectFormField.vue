<template>
  <div
    :class="[
      'ivyforms-field',
      'ivyforms-field__select',
      'ivyforms-field__select_' + field.id,
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
      <IvySelectInput
        :id="fieldID"
        v-model="localModelValue"
        :aria-label="field.label"
        :disabled="disabled"
        :readonly="field.readOnly"
        :required="field.required"
        class="ivyforms-field__select-input"
        :filterable="field.enableSearch"
        :multiple="field.type === 'multi-select'"
        :placeholder="field.placeholder"
      >
        <IvySelectOption
          v-for="option in selectOptions"
          :id="option.id"
          :key="option.id || option.value"
          :value="option.value"
          :label="option.label"
        />
      </IvySelectInput>
    </IvyFormItem>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, watch, onMounted, inject } from 'vue'
import type { Field } from '@/types/field'
import IvyFormItem from '@/views/_components/form/IvyFormItem.vue'
import IvySelectInput from '@/views/_components/input/IvySelectInput.vue'
import IvySelectOption from '@/views/_components/input/IvySelectOption.vue'

interface FieldProps {
  modelValue: string | string[] | null | undefined
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
    value: opt.value !== null && opt.value !== undefined ? String(opt.value) : '',
    id: opt.id,
    isDefault: opt.isDefault,
  }))

  // Default value logic for multi-select
  const isMulti = field.value.type === 'multi-select'
  if (isMulti) {
    const optionDefaults = shuffledOptions.value.filter((o) => o.isDefault).map((o) => o.value)
    const defaultsSet = new Set([...optionDefaults])
    if (defaultsSet.size) {
      localModelValue.value = Array.from(defaultsSet)
      emit('update:modelValue', localModelValue.value)
    }
  }
})
const selectOptions = computed(() =>
  shuffledOptions.value.length
    ? shuffledOptions.value
    : (field.value.fieldOptions || []).map((opt) => ({
        label: opt.label,
        value: opt.value !== null && opt.value !== undefined ? String(opt.value) : '',
        id: opt.id,
      })),
)
</script>

<style scoped lang="scss">
.ivyforms-field__select-input {
  width: 100%;
}
</style>
