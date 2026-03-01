<template>
  <ElDropdown ref="elDropdown" v-bind="$props" popper-class="ivyforms-dropdown">
    <slot />
    <template #dropdown>
      <slot name="dropdown" />
    </template>
  </ElDropdown>
</template>

<style lang="scss">
@use 'sass:list' as *;
@use 'sass:meta' as *;
// Dropdown
.ivyforms-dropdown {
  transform: translate(0, -4px) !important;
  border: 1px var(--map-base-brand-stroke-0) !important;
  border-radius: 8px !important;
  overflow: hidden !important;
  box-shadow: var(--shadow-100) !important;
  background: var(--map-base-dusk-o05) !important;

  // Placement Top
  &[data-popper-placement='top-start'] {
    transform: translate(0, 4px) !important;
  }

  // Arrow
  .el-popper__arrow {
    &:before {
      content: none;
    }
  }
}
</style>
