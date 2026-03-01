<template>
  <div class="ivyforms-default-value-options">
    <IvyFormItem :label="getLabel('default_value')" secondary>
      <IvyTextInput
        :model-value="field.defaultValue"
        type="textarea"
        @input="updateField('defaultValue', $event)"
      />
    </IvyFormItem>
  </div>
</template>

<script setup lang="ts">
import type { Field } from '@/types/field'
import IvyFormItem from '@/views/_components/form/IvyFormItem.vue'
import IvyTextInput from '@/views/_components/input/IvyTextInput.vue'

defineProps<{
  field: Field
  updateField: (key: string, value: unknown) => void
  getLabel: (key: string) => string
}>()
</script>
