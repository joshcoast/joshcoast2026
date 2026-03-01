<template>
  <div
    class="ivyforms-advanced-field-options-dynamic ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-16 ivyforms-pt-16"
  >
    <template v-for="optionConfig in advancedFieldOptions" :key="optionConfig.id">
      <!-- Render divider before if configured -->
      <IvyDivider v-if="optionConfig.showDividerBefore" />

      <!-- Render the dynamic field option component -->
      <component
        :is="optionConfig.component"
        :field="field"
        :update-field="updateField"
        :get-label="getLabel"
      />

      <!-- Render divider after if configured -->
      <IvyDivider v-if="optionConfig.showDividerAfter" />
    </template>
    <PageEmptyState
      v-if="!advancedFieldOptions.length"
      :title="getLabel('coming_soon')"
      :subtitle="getLabel('we_are_working_on_it')"
      image="fields-options"
    />
  </div>
</template>

<script setup lang="ts">
import { computed, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { useFormBuilder } from '@/stores/useFormBuilder.ts'
import { useLabels } from '@/composables/useLabels'
import api from '@/composables/IvyFormAPI'
import IvyDivider from '@/views/_components/divider/IvyDivider.vue'
import PageEmptyState from '@/views/admin/parts/PageEmptyState.vue'

const { getLabel } = useLabels()
const { selectedField } = storeToRefs(useFormBuilder())
const { updateSelectedField, updateFieldOptions } = useFormBuilder()

// Get all advanced field options for the current field type
const advancedFieldOptions = computed(() => {
  if (!selectedField.value) return []

  // Get options for this field type and 'advanced' tab
  const options = api.fieldOptions.getForFieldType(selectedField.value.type, 'advanced')

  // Ensure the last option does not have a divider after it, and all others do
  options.forEach((option, index) => {
    option.showDividerAfter = index < options.length - 1
    if (index === options.length - 1) {
      option.showDividerAfter = false
    }
  })

  // Filter by condition if present
  return options.filter((option) => {
    if (option.condition) {
      return option.condition(selectedField.value)
    }
    return true
  })
})

// Watch for updates from Pro plugin or other extensions
watch(
  () => api.hooks.updateCounter.value,
  () => {
    // This will trigger re-computation of advancedFieldOptions
  },
)

const field = computed(() => selectedField.value)

const updateField = (index: string, value: unknown) => {
  updateSelectedField(index, value)
  updateFieldOptions(index, value)
}
</script>

<style scoped lang="scss">
.ivyforms-advanced-field-options-dynamic {
  :deep(.ivyforms-form-item),
  :deep(.el-form-item) {
    margin-bottom: 0;
    gap: var(--Spacing-xs, 6px);
  }

  :deep(.el-textarea__inner) {
    border-radius: var(--Radius-radius-md, 8px);
    border: 1px solid var(--map-base-dusk-stroke--2);
    background-color: var(--map-base-dusk-o05);
    box-shadow: none;
  }
}
</style>
