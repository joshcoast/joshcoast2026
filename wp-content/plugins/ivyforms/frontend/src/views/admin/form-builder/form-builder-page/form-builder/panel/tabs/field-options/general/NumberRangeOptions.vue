<template>
  <div class="ivyforms-number-options ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-16">
    <div class="ivyforms-number-range ivyforms-flex ivyforms-gap-16">
      <IvyFormItem :label="getLabel('range_from')" secondary>
        <IvyTextNumberInput
          v-model="minValueModel"
          :max="field.maxValue ?? null"
          :min="Number.MIN_SAFE_INTEGER"
        />
      </IvyFormItem>
      <IvyFormItem :label="getLabel('range_to')" secondary>
        <IvyTextNumberInput
          v-model="maxValueModel"
          :min="field.minValue ?? null"
          :max="Number.MAX_SAFE_INTEGER"
        />
      </IvyFormItem>
    </div>

    <IvyDivider />

    <IvyFormItem :label="getLabel('step')" secondary>
      <IvyTextNumberInput v-model="stepValueModel" :min="1" :step="1" number-format="us_int" />
    </IvyFormItem>
    <IvyFormItem :label="getLabel('number_format')" secondary>
      <IvySelectInput
        :model-value="field.numberFormat || ''"
        class="ivyforms-number-format-select"
        @update:model-value="(val) => updateField('numberFormat', val)"
      >
        <IvySelectOption :label="getLabel('number_format_none')" value="" secondary />
        <IvySelectOption
          :label="getLabel('number_format_us_decimal')"
          value="us_decimal"
          secondary
        />
        <IvySelectOption :label="getLabel('number_format_us_int')" value="us_int" secondary />
        <IvySelectOption
          :label="getLabel('number_format_eu_decimal')"
          value="eu_decimal"
          secondary
        />
        <IvySelectOption :label="getLabel('number_format_eu_int')" value="eu_int" secondary />
      </IvySelectInput>
    </IvyFormItem>
  </div>
</template>

<script setup lang="ts">
import type { Field } from '@/types/field'
import IvyFormItem from '@/views/_components/form/IvyFormItem.vue'
import IvyTextNumberInput from '@/views/_components/input/IvyTextNumberInput.vue'
import IvySelectInput from '@/views/_components/input/IvySelectInput.vue'
import IvySelectOption from '@/views/_components/input/IvySelectOption.vue'
import IvyDivider from '@/views/_components/divider/IvyDivider.vue'
import { computed } from 'vue'

interface Props {
  field: Field
  updateField: (key: string, value: unknown) => void
  getLabel: (key: string) => string
}

const props = defineProps<Props>()

const minValueModel = computed<number | null>({
  get() {
    if (!props.field) return null
    return props.field.minValue === null || props.field.minValue === undefined
      ? null
      : props.field.minValue
  },
  set(val: number | null) {
    props.updateField('minValue', val === null || val === undefined ? null : Number(val))
  },
})

const maxValueModel = computed<number | null>({
  get() {
    if (!props.field) return null
    return props.field.maxValue === null || props.field.maxValue === undefined
      ? null
      : props.field.maxValue
  },
  set(val: number | null) {
    props.updateField('maxValue', val === null || val === undefined ? null : Number(val))
  },
})

const stepValueModel = computed<number>({
  get() {
    if (!props.field) return 1
    const current = props.field.step
    if (current === null || current === undefined || isNaN(Number(current))) return 1
    return Number(current) < 1 ? 1 : Number(current)
  },
  set(val: number | null) {
    const normalized = val === null || val === undefined || isNaN(Number(val)) ? 1 : val
    props.updateField('step', normalized < 1 ? 1 : normalized)
  },
})
</script>

<style scoped lang="scss">
.ivyforms-number-options {
  :deep(.ivyforms-form-item),
  :deep(.el-form-item) {
    margin-bottom: 0;
    gap: var(--Spacing-xs, 6px);
  }

  .ivyforms-number-range {
    width: 100%;

    > * {
      flex: 1;
    }
  }
}
</style>
