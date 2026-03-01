<template>
  <ElSkeleton class="ivyforms-skeleton" v-bind="{ ...$props, ...$attrs }" animated>
    <template v-if="$slots.template" #template>
      <slot name="template" />
    </template>
    <template v-if="$slots.default" #default>
      <slot name="default" />
    </template>
  </ElSkeleton>
</template>

<script setup lang="ts"></script>
<style lang="scss">
// Skeleton
.el-skeleton {
  &.ivyforms-skeleton {
    display: flex;
    flex-direction: column;

    // Element Skeleton Item
    &.el-skeleton__item {
      background: var(--map-skeleton);
    }

    // Animated
    &.is-animated {
      // Skeleton Item
      .ivyforms-skeleton-item {
        background: linear-gradient(
          90deg,
          var(--map-skeleton) 25%,
          var(--map-skeleton-to) 37%,
          var(--map-skeleton) 63%
        );
        background-size: 400% 100%;
      }
    }
  }
}
</style>
