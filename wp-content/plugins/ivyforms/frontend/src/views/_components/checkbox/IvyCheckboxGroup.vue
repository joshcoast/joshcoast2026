<template>
  <ElCheckboxGroup
    v-model="localModelValue"
    class="ivyforms-checkbox-group"
    v-bind="{ ...$props, ...$attrs }"
    @change="(eventValue: CheckboxValueType[]) => $emit('change', eventValue)"
  >
    <slot />
  </ElCheckboxGroup>
</template>

<script setup lang="ts">
import type { CheckboxGroupValueType, CheckboxValueType } from 'element-plus'
import { computed } from 'vue'

const props = defineProps<{
  modelValue?: CheckboxGroupValueType | undefined
}>()

const emit = defineEmits(['update:modelValue', 'change'])

const localModelValue = computed({
  get() {
    return props.modelValue
  },
  set(value) {
    emit('update:modelValue', value)
  },
})
</script>

<style lang="scss">
@use 'sass:list' as *;
@use 'sass:meta' as *;
// Checkbox Group
.ivyforms-checkbox-group {
  display: flex;
  gap: 8px;
  flex-direction: column;
}
</style>
