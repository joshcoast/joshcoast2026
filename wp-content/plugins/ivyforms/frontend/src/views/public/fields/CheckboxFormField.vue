<template>
  <div
    :class="[
      'ivyforms-field',
      'ivyforms-field__checkbox',
      'ivyforms-field__checkbox_' + field.id,
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
      <IvyCheckboxGroup
        :id="fieldID"
        v-model="localModelValue"
        :disabled="isDisabled"
        class="ivyforms-field__checkbox-group ivyforms-flex ivyforms-align-items-start ivyforms-justify-content-start ivyforms-flex-direction-column ivyforms-gap-8"
        role="group"
        :aria-required="field.required ? 'true' : undefined"
        :aria-readonly="field.readOnly ? 'true' : undefined"
      >
        <IvyCheckbox
          v-for="option in checkboxOptions"
          :id="String(option.id)"
          :key="option.id || option.value"
          :value="option.value"
          :aria-label="field.label"
          priority="secondary"
          type="checkmark"
          class="ivyforms-field__checkbox-option ivyforms-flex ivyforms-align-items-center ivyforms-gap-8 ivyforms-font-16 ivyforms-font-weight-500"
        >
          {{ option.label }}
        </IvyCheckbox>
      </IvyCheckboxGroup>
    </IvyFormItem>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, watch, ref, inject } from 'vue'
import type { Field } from '@/types/field'
import IvyCheckbox from '@/views/_components/checkbox/IvyCheckbox.vue'
import IvyCheckboxGroup from '@/views/_components/checkbox/IvyCheckboxGroup.vue'
import IvyFormItem from '@/views/_components/form/IvyFormItem.vue'

const props = defineProps<{
  modelValue: string[] | number[]
  field: Field
  disabled?: boolean
  error?: string
}>()

const emit = defineEmits(['update:modelValue'])
const fieldID = `field_${props.field.fieldIndex}`
const isDisabled = computed(() => !!props.disabled || !!props.field.readOnly)
const clearFieldError = inject<(fieldKey: string) => void>('clearFieldError', () => {})
const fieldKey = computed(() => props.field.type + '_' + props.field.fieldIndex)
const localModelValue = computed<string[]>({
  get() {
    return Array.isArray(props.modelValue) ? (props.modelValue as string[]) : []
  },
  set(val: string[]) {
    if (props.field.readOnly) return
    emit('update:modelValue', val)
    clearFieldError(fieldKey.value)
  },
})

const checkboxOptions = computed(() => {
  let opts = props.field.fieldOptions || []
  if (props.field.shuffleOptions) {
    opts = [...opts]
    for (let i = opts.length - 1; i > 0; i--) {
      const j = Math.floor(Math.random() * (i + 1))
      ;[opts[i], opts[j]] = [opts[j], opts[i]]
    }
  }
  return opts.map((opt) => ({
    label: opt.label,
    value: opt.value,
    id: opt.id,
    isDefault: opt.isDefault,
  }))
})

const hasSetDefaults = ref(false)

onMounted(() => {
  setDefaultsIfNeeded()
})

watch(
  () => props.modelValue,
  (newVal) => {
    if (!hasSetDefaults.value && (!Array.isArray(newVal) || newVal.length === 0)) {
      setDefaultsIfNeeded()
    }
  },
  { immediate: true },
)

function setDefaultsIfNeeded() {
  if (hasSetDefaults.value) return
  const optionDefaults = (props.field.fieldOptions || [])
    .filter((option) => option.isDefault)
    .map((option) => String(option.value))
  const fieldLevelDefaults =
    typeof props.field.defaultValue === 'string' && props.field.defaultValue.trim().length
      ? props.field.defaultValue
          .split(',')
          .map((val) => val.trim())
          .filter((val) => val.length)
      : []
  const defaultsSet = new Set<string>([...optionDefaults, ...fieldLevelDefaults])
  if (defaultsSet.size) {
    emit('update:modelValue', Array.from(defaultsSet))
    hasSetDefaults.value = true
  }
}
</script>

<style scoped lang="scss">
.ivyforms-field__checkbox-option {
  color: var(--map-base-text-0);
}
</style>
