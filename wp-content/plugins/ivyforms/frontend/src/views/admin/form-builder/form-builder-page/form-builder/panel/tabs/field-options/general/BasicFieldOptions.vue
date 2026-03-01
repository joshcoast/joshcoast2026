<template>
  <div
    class="ivyforms-basic-field-options ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-16"
  >
    <div class="ivyforms-basic-field-options__field-meta ivyforms-gap-4 ivyforms-pt-8">
      <div class="ivyforms-basic-field-options__field-meta-title medium-16">
        {{ formattedType }}
        <span class="ivyforms-basic-field-options__field-meta-id regular-16"
          >(ID {{ formattedId }})</span
        >
      </div>
      <IvyDivider />
    </div>
    <IvyFormItem :label="getLabel('label')" secondary>
      <IvyTextInput :model-value="field.label" @input="updateField('label', $event)" />
    </IvyFormItem>
    <IvyCheckbox
      :model-value="field.hideLabel"
      priority="secondary"
      :type="'checkmark'"
      @change="updateField('hideLabel', $event)"
    >
      {{ getLabel('hide_label') }}
    </IvyCheckbox>
    <IvyFormItem :label="getLabel('description')" secondary>
      <IvyTextInput
        :model-value="field.description || ''"
        :placeholder="getLabel('field_desc_placeholder')"
        @input="updateField('description', $event)"
      />
    </IvyFormItem>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { Field } from '@/types/field'
import IvyFormItem from '@/views/_components/form/IvyFormItem.vue'
import IvyTextInput from '@/views/_components/input/IvyTextInput.vue'
import IvyCheckbox from '@/views/_components/checkbox/IvyCheckbox.vue'

interface Props {
  field: Field
  updateField: (key: string, value: unknown) => void
  getLabel: (key: string) => string
}

const { field, updateField, getLabel } = defineProps<Props>()

// Human-friendly field type (e.g. "Text Field" from "text")
const formattedType = computed(() => {
  const t = field?.type ? String(field.type) : ''
  if (!t) return ''
  // Replace dashes/underscores, title-case words, and append 'Field' when appropriate
  const words = t.replace(/[-_]/g, ' ').replace(/\s+/g, ' ').trim()
  const title = words.replace(/\b\w/g, (c) => c.toUpperCase())
  // If the type already contains 'Field' or 'field' avoid doubling
  return /field$/i.test(title) ? title : `${title} Field`
})

// Padded ID like '01' for small numbers
const formattedId = computed(() => {
  const id = field?.id ?? ''
  if (typeof id === 'number' && id) return String(id).padStart(2, '0')
  return getLabel('not_set')
})
</script>
<style scoped lang="scss">
.ivyforms-basic-field-options {
  :deep(.ivyforms-form-item),
  :deep(.el-form-item) {
    margin-bottom: 0;
    gap: var(--Spacing-xs, 6px);
  }
}
</style>
