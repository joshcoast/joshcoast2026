<template>
  <IvyFormItem :label="labelText" secondary>
    <IvySelectInput
      :model-value="field.ratingIcon || 'star'"
      :icon-start="selectedIcon"
      icon-start-type="line"
      @update:model-value="updateField('ratingIcon', $event)"
    >
      <IvySelectOption
        value="star"
        :label="getLabel('stars')"
        icon-start="star"
        icon-start-type="line"
        secondary
      >
        {{ getLabel('stars') }}
      </IvySelectOption>
      <IvySelectOption
        value="heart"
        :label="getLabel('hearts')"
        icon-start="heart"
        icon-start-type="line"
        secondary
      >
        {{ getLabel('hearts') }}
      </IvySelectOption>
      <IvySelectOption
        value="like"
        :label="getLabel('likes')"
        icon-start="like"
        icon-start-type="line"
        secondary
      >
        {{ getLabel('likes') }}
      </IvySelectOption>
    </IvySelectInput>
  </IvyFormItem>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { Field } from '@/types/field'
import IvyFormItem from '@/views/_components/form/IvyFormItem.vue'
import IvySelectInput from '@/views/_components/input/IvySelectInput.vue'
import IvySelectOption from '@/views/_components/input/IvySelectOption.vue'

const props = defineProps<{
  field: Field
  updateField: (key: string, value: unknown) => void
  getLabel?: (key: string) => string
}>()

const labelText = computed(() => props.getLabel?.('icon_type'))
const selectedIcon = computed(() => props.field.ratingIcon || 'star')
</script>

<style scoped lang="scss">
.ivyforms-form-item {
  margin-bottom: 0;
}
</style>
