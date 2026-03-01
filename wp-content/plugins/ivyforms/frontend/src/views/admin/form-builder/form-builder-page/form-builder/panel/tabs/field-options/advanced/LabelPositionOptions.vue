<template>
  <div class="ivyforms-label-position-options">
    <IvyFormItem :label="getLabel('label_position')">
      <IvyRadioGroup
        :model-value="field.labelPosition"
        :priority="'secondary'"
        @change="updateField('labelPosition', $event)"
      >
        <IvyRadio value="default" priority="secondary">{{
          getLabel('label_position_default')
        }}</IvyRadio>
        <IvyRadio value="top" priority="secondary">{{ getLabel('label_position_top') }}</IvyRadio>
        <IvyRadio value="left" priority="secondary">{{ getLabel('label_position_left') }}</IvyRadio>
        <IvyRadio value="right" priority="secondary">{{
          getLabel('label_position_right')
        }}</IvyRadio>
      </IvyRadioGroup>
    </IvyFormItem>
  </div>
</template>

<script setup lang="ts">
import type { Field } from '@/types/field'
import IvyFormItem from '@/views/_components/form/IvyFormItem.vue'
import IvyRadioGroup from '@/views/_components/radio/IvyRadioGroup.vue'
import IvyRadio from '@/views/_components/radio/IvyRadio.vue'

defineProps<{
  field: Field
  updateField: (key: string, value: unknown) => void
  getLabel: (key: string) => string
}>()
</script>
<style scoped lang="scss">
.ivyforms-label-position-options {
  :deep(.el-radio-group.ivyforms-radio-group) {
    padding: 0;
  }
}
</style>
