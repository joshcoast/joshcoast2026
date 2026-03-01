<template>
  <div class="ivyforms-phone-options ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-16">
    <IvyToggle
      :model-value="field.phoneAutoDetect"
      priority="secondary"
      :title="getLabel('enable_auto_country_detection')"
      text-position="right"
      @change="updateField('phoneAutoDetect', $event)"
    />
  </div>
</template>

<script setup lang="ts">
import type { Field } from '@/types/field'
import IvyToggle from '@/views/_components/toggle/IvyToggle.vue'

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
