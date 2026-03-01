<template>
  <div
    class="ivyforms-general-field-options-dynamic ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-16 ivyforms-pt-16"
  >
    <template v-for="(optionConfig, index) in fieldOptions" :key="optionConfig.id">
      <!-- Divider before -->
      <IvyDivider v-if="optionConfig.showDividerBefore && index > 0" />

      <!-- Render the option component -->
      <component
        :is="optionConfig.component"
        :field="selectedField"
        :update-field="updateField"
        :get-label="getLabel"
      />

      <!-- Divider after -->
      <IvyDivider v-if="optionConfig.showDividerAfter && index < fieldOptions.length - 1" />
    </template>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { storeToRefs } from 'pinia'
import { useFormBuilder } from '@/stores/useFormBuilder.ts'
import { useLabels } from '@/composables/useLabels'
import api from '@/composables/IvyFormAPI'

const { getLabel } = useLabels()
const { selectedField } = storeToRefs(useFormBuilder())
const { updateFieldOptions, updateSelectedField } = useFormBuilder()

// Update field values in the store
const updateField = (index: string, value: unknown) => {
  updateSelectedField(index, value)
  updateFieldOptions(index, value)
}

// Get field options for the currently selected field type
const fieldOptions = computed(() => {
  // Include hookCounter in dependency tracking for reactivity
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  const _updateCount = api.hooks.updateCounter.value

  if (!selectedField.value) return []

  // Get all registered options for this field type
  const options = api.fieldOptions.getForFieldType(selectedField.value.type, 'general')

  // Ensure the last option does not have a divider after it, and all others do
  options.forEach((option, index) => {
    option.showDividerAfter = index < options.length - 1
    if (index === options.length - 1) {
      option.showDividerAfter = false
    }
  })

  // Filter by condition if provided
  return options.filter((config) => {
    if (!config.condition) return true
    return config.condition(selectedField.value)
  })
})
</script>

<style scoped lang="scss">
.ivyforms-general-field-options-dynamic {
  :deep(.ivyforms-form-item),
  :deep(.el-form-item) {
    margin-bottom: 0;
    gap: var(--Spacing-xs, 6px);
  }
}
</style>
