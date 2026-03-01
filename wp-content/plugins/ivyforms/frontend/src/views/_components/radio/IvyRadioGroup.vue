<template>
  <ElRadioGroup
    v-model="localModelValue"
    class="ivyforms-radio-group ivyforms-pt-8 ivyforms-pb-8 ivyforms-flex ivyforms-justify-content-start ivyforms-gap-16"
    :class="[`direction-${props.direction}`]"
    v-bind="{ ...$props, ...$attrs }"
    @change="(eventValue: string | number | boolean) => $emit('change', eventValue)"
  >
    <slot />
  </ElRadioGroup>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  modelValue?: string | number | boolean
  direction?: 'vertical' | 'horizontal'
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: undefined,
  direction: 'horizontal',
})

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
// Radio Group
.ivyforms-radio-group {
  // Vertical
  &.direction-vertical {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
}
</style>
