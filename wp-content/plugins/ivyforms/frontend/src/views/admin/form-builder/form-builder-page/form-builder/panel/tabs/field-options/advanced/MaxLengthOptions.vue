<template>
  <div
    class="ivyforms-max-length-options ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-16"
  >
    <!-- Limit Maximum Length Toggle -->
    <IvyToggle
      :model-value="field.limitMaxLength"
      priority="secondary"
      size="d"
      :title="getLabel('limit_maximum_length')"
      text-position="right"
      @change="updateField('limitMaxLength', $event)"
    />

    <!-- Maximum Length Input (shown when limit is enabled) -->
    <IvyFormItem v-if="field.limitMaxLength">
      <IvyNumberInput
        :model-value="field.maxLength"
        :min="1"
        :max="65535"
        secondary
        @update:model-value="updateField('maxLength', $event)"
      />
    </IvyFormItem>
  </div>
</template>

<script setup lang="ts">
import type { Field } from '@/types/field'
import IvyToggle from '@/views/_components/toggle/IvyToggle.vue'
import IvyFormItem from '@/views/_components/form/IvyFormItem.vue'
import IvyNumberInput from '@/views/_components/input/IvyNumberInput.vue'

defineProps<{
  field: Field
  updateField: (key: string, value: unknown) => void
  getLabel: (key: string) => string
}>()
</script>
<style lang="scss" scoped>
.ivyforms-max-length-options {
  :deep(.el-form-item__label) {
    display: none !important;
  }
}
</style>
