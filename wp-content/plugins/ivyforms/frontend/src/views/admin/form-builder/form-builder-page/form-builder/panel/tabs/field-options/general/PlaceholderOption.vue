<template>
  <IvyFormItem :label="getLabel('placeholder')" secondary>
    <IvyTextInput :model-value="field.placeholder" @input="updateField('placeholder', $event)" />
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
