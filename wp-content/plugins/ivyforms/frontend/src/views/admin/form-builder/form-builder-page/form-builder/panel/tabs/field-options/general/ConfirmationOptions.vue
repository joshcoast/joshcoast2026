<template>
  <div
    class="ivyforms-confirmation-options ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-16"
  >
    <IvyToggle
      :model-value="field.confirmFieldEnabled"
      priority="secondary"
      :title="getConfirmationToggleLabel()"
      text-position="right"
      :disabled="field.readOnly"
      @change="updateField('confirmFieldEnabled', $event)"
    />

    <template v-if="field.confirmFieldEnabled">
      <IvyFormItem :label="getLabel(getFieldTypeLabel('confirmation_label'))" secondary>
        <IvyTextInput
          :model-value="field.confirmFieldLabel"
          :placeholder="getLabel(getFieldTypeLabel('confirmation_label_placeholder'))"
          @input="updateField('confirmFieldLabel', $event)"
        />
      </IvyFormItem>
      <IvyCheckbox
        :model-value="field.confirmFieldHideLabel"
        priority="secondary"
        :type="'checkmark'"
        @change="updateField('confirmFieldHideLabel', $event)"
      >
        {{ getLabel('hide_confirmation_label') }}
      </IvyCheckbox>
      <IvyFormItem :label="getLabel(getFieldTypeLabel('confirmation_placeholder'))" secondary>
        <IvyTextInput
          :model-value="field.confirmFieldPlaceholder"
          :placeholder="getLabel(getFieldTypeLabel('confirmation_placeholder_placeholder'))"
          @input="updateField('confirmFieldPlaceholder', $event)"
        />
      </IvyFormItem>
    </template>
  </div>
</template>

<script setup lang="ts">
import type { Field } from '@/types/field'
import IvyFormItem from '@/views/_components/form/IvyFormItem.vue'
import IvyTextInput from '@/views/_components/input/IvyTextInput.vue'
import IvyCheckbox from '@/views/_components/checkbox/IvyCheckbox.vue'
import IvyToggle from '@/views/_components/toggle/IvyToggle.vue'

interface Props {
  field: Field
  updateField: (key: string, value: unknown) => void
  getLabel: (key: string) => string
}

const props = defineProps<Props>()

// Get the appropriate toggle label based on field type
const getConfirmationToggleLabel = () => {
  const fieldType = props.field.type

  // Try field-type-specific label first (e.g., 'enable_email_confirmation', 'enable_password_confirmation')
  const specificKey = `enable_${fieldType}_confirmation`
  const specificLabel = props.getLabel(specificKey)

  // If specific label exists (not same as key), use it
  if (specificLabel !== specificKey) {
    return specificLabel
  }

  // Default fallback
  return props.getLabel('enable_confirmation')
}

// Get field-type-specific label key
const getFieldTypeLabel = (baseKey: string) => {
  const fieldType = props.field.type

  // Try field-type-specific label first (e.g., 'email_confirmation_label', 'password_confirmation_label')
  const specificKey = `${fieldType}_${baseKey}`
  const specificLabel = props.getLabel(specificKey)

  // If specific label exists (not same as key), use it
  if (specificLabel !== specificKey) {
    return specificKey
  }

  // Default fallback to base key
  return baseKey
}
</script>

<style scoped lang="scss">
.ivyforms-confirmation-options {
  :deep(.ivyforms-form-item),
  :deep(.el-form-item) {
    margin-bottom: 0;
    gap: var(--Spacing-xs, 6px);
  }
}
</style>
