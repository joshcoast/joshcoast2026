<template>
  <div
    class="ivyforms-textarea-options ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-16"
  >
    <IvyFormItem :label="getLabel('text_area_size')">
      <IvyNumberInput
        :model-value="rowsModel"
        secondary
        :min="1"
        :placeholder="getLabel('rows')"
        @update:model-value="onRowsChange"
      />
    </IvyFormItem>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { Field } from '@/types/field'
import IvyFormItem from '@/views/_components/form/IvyFormItem.vue'
import IvyNumberInput from '@/views/_components/input/IvyNumberInput.vue'

interface Props {
  field: Field
  updateField: (key: string, value: unknown) => void
  getLabel: (key: string) => string
}

const props = defineProps<Props>()

// Proxy for rows so that value 0 shows placeholder (null in the input)
const rowsModel = computed<number | null>({
  get() {
    return !props.field.rows || props.field.rows === 0 ? null : props.field.rows
  },
  set(val: number | null) {
    props.updateField('rows', val == null ? 0 : Number(val))
  },
})

const onRowsChange = (val: number | null) => {
  // Ensure minimum value of 1 when value is provided
  if (val !== null && val < 1) {
    rowsModel.value = 1
  } else {
    rowsModel.value = val
  }
}
</script>

<style scoped lang="scss">
.ivyforms-textarea-options {
  :deep(.ivyforms-form-item),
  :deep(.el-form-item) {
    margin-bottom: 0;
    gap: var(--Spacing-xs, 6px);
  }
}
</style>
