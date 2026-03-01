<template>
  <ElDropdownMenu class="ivyforms-dropdown-menu" :visible-arrow="false" v-bind="$props">
    <slot />
  </ElDropdownMenu>
</template>

<style lang="scss">
@use 'sass:list' as *;
@use 'sass:meta' as *;
// Dropdown Menu
.ivyforms-dropdown-menu {
  border: none;
  background: var(--map-ground-level-2-foreground);
  padding: 8px 0px;
  gap: 0;
  display: flex;
  flex-direction: column;
}
</style>
