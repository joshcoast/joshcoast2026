<template>
  <div class="ivyforms-field__radio">
    <IvyFormItem
      :label="hideLabel ? '' : label"
      :required="required"
      :label-position="labelPosition"
    >
      <IvyRadioGroup
        :id="index"
        v-model="selectedValue"
        :aria-label="label"
        direction="vertical"
        readonly
        class="ivyforms-field__radio-group ivyforms-flex ivyforms-align-items-start ivyforms-justify-content-start ivyforms-gap-8"
      >
        <IvyRadio
          v-for="option in radioOptions"
          :id="option.id"
          :key="option.id || option.value"
          :value="option.value"
          :label="option.label"
          priority="secondary"
          readonly
          class="ivyforms-field__radio-option"
        />
      </IvyRadioGroup>
      <div v-if="description" class="ivyforms-description-message regular-14">
        {{ description }}
      </div>
    </IvyFormItem>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useFormBuilder } from '@/stores/useFormBuilder'
import type { Choice } from '@/types/field'

const props = defineProps<{ fieldIndex: number }>()
const formBuilderStore = useFormBuilder()

// Computed properties to dynamically get values from the store
const field = computed(() => formBuilderStore.fields.find((f) => f.fieldIndex === props.fieldIndex))
const label = computed(() => field.value?.label || '')
const required = computed(() => field.value?.required || false)
const hideLabel = computed(() => field.value?.hideLabel || false)
const index = computed(() => field.value?.fieldIndex ?? props.fieldIndex)
const description = computed(() => field.value?.description || '')

const labelPosition = computed(() =>
  field.value?.labelPosition === 'default' ? 'top' : field.value?.labelPosition || 'top',
)

// Selection is derived solely from isDefault flag
const selectedValue = computed<string>({
  get() {
    const opts = field.value?.fieldOptions || []
    const def = opts.find((opt) => opt.isDefault)
    return def ? String(def.value) : ''
  },
  set() {
    /* no-op to block changes via UI */
  },
})

// Helper to shuffle options if enabled
function shuffle(array: Choice[]) {
  const arr = array.slice()
  for (let i = arr.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1))
    ;[arr[i], arr[j]] = [arr[j], arr[i]]
  }
  return arr
}

const radioOptions = computed(() => {
  let opts = field.value?.fieldOptions || []
  if (field.value?.shuffleOptions) {
    opts = shuffle(opts)
  }
  return opts.map((opt) => ({
    label: opt.label,
    value: opt.value,
    id: opt.id,
  }))
})
</script>

<style lang="scss" scoped>
.ivyforms-field__radio {
  &-option {
    color: var(--map-base-text-0);
  }
  :deep(.el-checkbox__inner) {
    background-color: transparent !important;
  }
  :deep(.ivyforms-form-item__label) {
    cursor: default !important;
  }
}
</style>
