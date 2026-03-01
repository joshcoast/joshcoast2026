<template>
  <div
    class="ivyforms-choice-list-options ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-16"
  >
    <IvyFormItem v-if="selectTypes.includes(field.type)">
      <IvyRadioGroup :model-value="field.type" @change="onChoiceTypeChange">
        <IvyRadio value="select" priority="secondary">{{ getLabel('single_choice') }}</IvyRadio>
        <IvyRadio value="multi-select" priority="secondary">{{
          getLabel('multiple_choice')
        }}</IvyRadio>
      </IvyRadioGroup>
    </IvyFormItem>
    <IvyChoiceList
      :choices="field.fieldOptions"
      :show-values="field.showValues"
      :shuffle-options="field.shuffleOptions"
      :enable-search="field.enableSearch"
      :type="field.type"
      @update:choices="updateField('fieldOptions', $event)"
      @update:show-values="updateField('showValues', $event)"
      @update:shuffle-options="updateField('shuffleOptions', $event)"
      @update:enable-search="updateField('enableSearch', $event)"
    />
  </div>
</template>

<script setup lang="ts">
import type { Field } from '@/types/field'
import IvyFormItem from '@/views/_components/form/IvyFormItem.vue'
import IvyRadioGroup from '@/views/_components/radio/IvyRadioGroup.vue'
import IvyRadio from '@/views/_components/radio/IvyRadio.vue'
import IvyChoiceList from '@/views/_components/choice/IvyChoiceList.vue'

interface Props {
  field: Field
  updateField: (key: string, value: unknown) => void
  getLabel: (key: string) => string
}

const props = defineProps<Props>()

const selectTypes = ['select', 'multi-select']

function onChoiceTypeChange(val: string) {
  props.updateField('type', val)
}
</script>

<style scoped lang="scss">
.ivyforms-choice-list-options {
  :deep(.ivyforms-form-item),
  :deep(.el-form-item) {
    margin-bottom: 0;
    gap: var(--Spacing-xs, 6px);
  }
  :deep(.el-radio-group.ivyforms-radio-group) {
    padding: 0;
  }
  :deep(.ivyforms-form-item .el-form-item .el-form-item__label) {
    display: none;
  }
}
</style>
