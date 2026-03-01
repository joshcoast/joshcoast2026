<template>
  <div class="ivyforms-field__checkbox">
    <IvyFormItem
      :label="hideLabel ? '' : label"
      :required="required"
      :label-position="labelPosition"
    >
      <IvyCheckboxGroup
        :id="index"
        v-model="selectedValues"
        readonly
        class="ivyforms-field__checkbox-group ivyforms-flex ivyforms-align-items-start ivyforms-justify-content-start ivyforms-flex-direction-column ivyforms-gap-8"
      >
        <IvyCheckbox
          v-for="option in checkboxOptions"
          :id="option.id"
          :key="option.id || option.value"
          :value="option.value"
          priority="secondary"
          type="checkmark"
          :class="['ivyforms-field__checkbox-option', { 'readonly-option': true }]"
        >
          {{ option.label }}
        </IvyCheckbox>
      </IvyCheckboxGroup>
      <div v-if="description" class="ivyforms-description-message regular-14">
        {{ description }}
      </div>
    </IvyFormItem>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useFormBuilder } from '@/stores/useFormBuilder'
import IvyCheckbox from '@/views/_components/checkbox/IvyCheckbox.vue'
import IvyCheckboxGroup from '@/views/_components/checkbox/IvyCheckboxGroup.vue'
import IvyFormItem from '@/views/_components/form/IvyFormItem.vue'
import type { Choice } from '@/types/field'

const props = defineProps<{ fieldIndex: number }>()
const formBuilderStore = useFormBuilder()
const field = computed(() => formBuilderStore.fields.find((f) => f.fieldIndex === props.fieldIndex))
const label = computed(() => field.value?.label || '')
const required = computed(() => field.value?.required || false)
const hideLabel = computed(() => field.value?.hideLabel || false)
const index = computed(() => field.value?.fieldIndex ?? props.fieldIndex)
const description = computed(() => field.value?.description || '')

const labelPosition = computed(() =>
  field.value?.labelPosition === 'default' ? 'top' : field.value?.labelPosition || 'top',
)

// Selected values derived only from isDefault flags
const selectedValues = computed<string[]>({
  get() {
    const opts = field.value?.fieldOptions || []
    return opts.filter((opt) => opt.isDefault).map((opt) => opt.value)
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

const checkboxOptions = computed(() => {
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
.ivyforms-field__checkbox {
  &-option {
    color: var(--map-base-text-0);

    &.readonly-option {
      pointer-events: none;
      cursor: default;
      background: none;
    }

    &:hover {
      background: none;
    }
  }
  :deep(.el-checkbox__inner) {
    background-color: transparent !important;
  }
  :deep(.ivyforms-form-item__label) {
    cursor: default !important;
  }
}
</style>
