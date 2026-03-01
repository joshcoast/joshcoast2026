<template>
  <div
    class="ivyforms-validation-options ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-16"
  >
    <div class="ivyforms-checkbox-row ivyforms-flex ivyforms-gap-16">
      <IvyCheckbox
        :model-value="field.required"
        priority="secondary"
        :type="'checkmark'"
        :disabled="field.readOnly"
        @change="updateField('required', $event)"
      >
        {{ getLabel('required') }}
      </IvyCheckbox>
      <IvyCheckbox
        :model-value="field.readOnly"
        priority="secondary"
        :type="'checkmark'"
        :disabled="field.required || field.confirmFieldEnabled"
        @change="updateField('readOnly', $event)"
      >
        {{ getLabel('read_only') }}
      </IvyCheckbox>
    </div>
    <IvyFormItem v-if="field.required" :label="getLabel('required_message')">
      <IvyTextInput
        :model-value="field.requiredMessage || ''"
        secondary
        :placeholder="getLabel('required_message_placeholder')"
        @input="updateField('requiredMessage', $event)"
      />
    </IvyFormItem>
  </div>
</template>

<script setup lang="ts">
import type { Field } from '@/types/field'
import IvyFormItem from '@/views/_components/form/IvyFormItem.vue'
import IvyTextInput from '@/views/_components/input/IvyTextInput.vue'
import IvyCheckbox from '@/views/_components/checkbox/IvyCheckbox.vue'

interface Props {
  field: Field
  updateField: (key: string, value: unknown) => void
  getLabel: (key: string) => string
}

defineProps<Props>()
</script>
<style lang="scss" scoped>
.ivyforms-validation-options {
  .ivyforms-checkbox-row {
    justify-content: flex-start;
  }
}
</style>
