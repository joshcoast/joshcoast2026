<template>
  <IvyFormItem :label="getLabel('css_classes')" secondary>
    <IvyTextInput
      :model-value="field.cssClasses"
      :placeholder="getLabel('css_classes_placeholder')"
      @input="updateField('cssClasses', $event)"
    />
  </IvyFormItem>
</template>

<script setup lang="ts">
import type { Field } from '@/types/field'
import IvyFormItem from '@/views/_components/form/IvyFormItem.vue'
import IvyTextInput from '@/views/_components/input/IvyTextInput.vue'

interface Props {
  field: Field
  updateField: (key: string, value: unknown) => void
  getLabel: (key: string) => string
}

defineProps<Props>()
</script>
