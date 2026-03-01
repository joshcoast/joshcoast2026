<template>
  <div class="ivyforms-search">
    <IvyTextInput
      ref="elInputRef"
      :model-value="modelValue"
      :clearable="true"
      :placeholder="placeholder"
      icon-start="search"
      icon-start-type="outline"
      :class="{ 'is-secondary': isSecondary }"
      @update:model-value="onInput"
    />
  </div>
</template>

<script setup lang="ts">
withDefaults(
  defineProps<{
    modelValue: string
    placeholder?: string
    isSecondary?: boolean
  }>(),
  {
    modelValue: '',
    placeholder: '',
    isSecondary: false,
  },
)

const emit = defineEmits(['update:modelValue'])

function onInput(value: string) {
  emit('update:modelValue', value)
}
</script>
<style lang="scss">
@use 'sass:list' as *;
@use 'sass:meta' as *;

.ivyforms-search {
  .ivyforms-input {
    width: 100%;
    &.el-input {
      .el-input__wrapper {
        .ivyforms-input__clear {
          cursor: pointer;
          width: 24px;
          height: 24px;
          transform: scale(1);
          margin: 0;
        }
      }
    }
  }
}
</style>
