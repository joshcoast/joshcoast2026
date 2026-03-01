<template>
  <div class="ivyforms-rating-options ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-16">
    <!-- Option List -->
    <IvyChoiceList
      :choices="field.fieldOptions"
      :show-values="field.showValues"
      type="rating"
      @update:choices="updateField('fieldOptions', $event)"
      @update:show-values="updateField('showValues', $event)"
    />

    <!-- Show text -->
    <IvyCheckbox
      :model-value="Boolean(field.showRatingText)"
      priority="secondary"
      type="checkmark"
      @update:model-value="updateField('showRatingText', $event)"
    >
      {{ getLabel('show_text') }}
    </IvyCheckbox>

    <IvyDivider />

    <!-- Required -->
    <IvyCheckbox
      :model-value="field.required"
      priority="secondary"
      type="checkmark"
      @update:model-value="updateField('required', $event)"
    >
      {{ getLabel('required') }}
    </IvyCheckbox>

    <!-- Required Message (shown when required is true) -->
    <IvyFormItem v-if="field.required" :label="getLabel('required_message')">
      <IvyTextInput
        secondary
        :model-value="field.requiredMessage || ''"
        :placeholder="getLabel('this_field_is_required')"
        @update:model-value="updateField('requiredMessage', $event)"
      />
    </IvyFormItem>
  </div>
</template>

<script setup lang="ts">
import { watch } from 'vue'
import type { Field } from '@/types/field'
import IvyFormItem from '@/views/_components/form/IvyFormItem.vue'
import IvyTextInput from '@/views/_components/input/IvyTextInput.vue'
import IvyCheckbox from '@/views/_components/checkbox/IvyCheckbox.vue'
import IvyDivider from '@/views/_components/divider/IvyDivider.vue'
import IvyChoiceList from '@/views/_components/choice/IvyChoiceList.vue'

interface Props {
  field: Field
  updateField: (key: string, value: unknown) => void
  getLabel: (key: string) => string
}

const props = defineProps<Props>()

// Watch for changes in fieldOptions to ensure values always match position (1, 2, 3, etc.)
watch(
  () => props.field.fieldOptions,
  (newOptions) => {
    if (!newOptions || newOptions.length === 0) return

    // Check if any value doesn't match its position
    const needsUpdate = newOptions.some((opt, index) => opt.value !== String(index + 1))

    if (needsUpdate) {
      // Create updated options with position-based values
      const updatedOptions = newOptions.map((opt, index) => ({
        ...opt,
        value: String(index + 1),
        position: index + 1,
      }))

      // Update without triggering infinite loop
      props.updateField('fieldOptions', updatedOptions)
    }
  },
  { deep: true },
)
</script>

<style scoped lang="scss">
.ivyforms-rating-options {
  :deep(.ivyforms-form-item),
  :deep(.el-form-item) {
    margin-bottom: 0;
    gap: var(--Spacing-xs, 6px);
  }

  :deep(.ivyforms-choice-list__items) {
    max-height: 225px;
  }
}
</style>
