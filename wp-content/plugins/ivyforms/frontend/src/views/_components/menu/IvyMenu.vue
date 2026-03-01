<template>
  <div class="ivyforms-menu-wrapper">
    <ElMenu class="ivyforms-menu" v-bind="{ ...$props, ...$attrs }">
      <slot />
    </ElMenu>
  </div>
</template>

<script setup lang="ts"></script>
<style lang="scss">
// Menu Wrapper
.ivyforms-menu-wrapper {
  display: flex;
  //overflow-y: auto;
  width: 100%;

  // Menu
  .ivyforms-menu {
    // Element Menu
    &.el-menu {
      border-right: none;
      background-color: transparent;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 8px;
      flex-shrink: 0;
      flex-grow: 1;
    }

    // Horizontal Menu
    &.el-menu--horizontal {
      flex-direction: row;
      height: auto;
      border-bottom: none;
    }

    // Vertical Menu
    &.el-menu--vertical {
      .el-menu-item {
        width: 100%;
        margin: unset;
      }
    }
  }
}
</style>
