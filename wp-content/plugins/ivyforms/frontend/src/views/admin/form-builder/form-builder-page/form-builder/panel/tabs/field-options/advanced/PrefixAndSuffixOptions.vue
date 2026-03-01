<template>
  <div
    class="ivyforms-prefix-and-suffix-options ivyforms-flex ivyforms-gap-16 ivyforms-flex-direction-column"
  >
    <!-- Prefix -->
    <IvyFormItem :label="getLabel('prefix')" secondary>
      <IvyTextInput
        :model-value="field.inputPrefix"
        :placeholder="getLabel('prefix_placeholder')"
        @input="updateField('inputPrefix', $event)"
      />
    </IvyFormItem>

    <!-- Suffix -->
    <IvyFormItem :label="getLabel('suffix')" secondary>
      <IvyTextInput
        :model-value="field.inputSuffix"
        :placeholder="getLabel('suffix_placeholder')"
        @input="updateField('inputSuffix', $event)"
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
