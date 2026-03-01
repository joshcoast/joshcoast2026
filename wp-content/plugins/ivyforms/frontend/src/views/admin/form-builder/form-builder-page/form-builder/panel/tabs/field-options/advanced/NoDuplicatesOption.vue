<template>
  <div class="ivyforms-no-duplicates-option">
    <IvyCheckbox
      :model-value="field.noDuplicates"
      priority="secondary"
      type="checkmark"
      @change="updateField('noDuplicates', $event)"
    >
      {{ getLabel('no_duplicates') }}
    </IvyCheckbox>
  </div>
</template>

<script setup lang="ts">
import type { Field } from '@/types/field'
import IvyCheckbox from '@/views/_components/checkbox/IvyCheckbox.vue'

defineProps<{
  field: Field
  updateField: (key: string, value: unknown) => void
  getLabel: (key: string) => string
}>()
</script>
