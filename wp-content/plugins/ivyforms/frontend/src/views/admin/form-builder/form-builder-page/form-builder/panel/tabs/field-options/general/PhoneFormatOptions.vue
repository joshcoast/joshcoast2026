<template>
  <div class="ivyforms-phone-options ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-16">
    <IvyFormItem :label="getLabel('phone_format')" secondary>
      <IvySelectInput
        :model-value="field.phoneFormat"
        :placeholder="getLabel('phone_format')"
        @change="updateField('phoneFormat', $event)"
      >
        <IvySelectOption
          value="international"
          :label="getLabel('phone_format_international')"
          secondary
          >{{ getLabel('phone_format_international') }}</IvySelectOption
        >
        <IvySelectOption value="national" :label="getLabel('phone_format_national')" secondary>{{
          getLabel('phone_format_national')
        }}</IvySelectOption>
        <IvySelectOption value="e164" :label="getLabel('phone_format_e164')" secondary>{{
          getLabel('phone_format_e164')
        }}</IvySelectOption>
      </IvySelectInput>
    </IvyFormItem>
  </div>
</template>

<script setup lang="ts">
import type { Field } from '@/types/field'
import IvyFormItem from '@/views/_components/form/IvyFormItem.vue'
import IvySelectInput from '@/views/_components/input/IvySelectInput.vue'
import IvySelectOption from '@/views/_components/input/IvySelectOption.vue'

interface Props {
  field: Field
  updateField: (key: string, value: unknown) => void
  getLabel: (key: string) => string
}

defineProps<Props>()
</script>

<style scoped lang="scss">
.ivyforms-phone-options {
  :deep(.ivyforms-form-item),
  :deep(.el-form-item) {
    margin-bottom: 0;
    gap: var(--Spacing-xs, 6px);
  }
}
</style>
