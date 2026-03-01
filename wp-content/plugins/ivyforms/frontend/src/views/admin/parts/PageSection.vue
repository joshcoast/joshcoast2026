<template>
  <div class="ivyforms-page-section ivyforms-flex ivyforms-flex-1 ivyforms-gap-24">
    <div
      class="ivyforms-page-section__content ivyforms-p-20"
      :class="{ 'ivyforms-page-section__content__fullscreen': globalState.isFullScreenMode }"
    >
      <slot></slot>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useGlobalState } from '@/stores/useGlobalState.ts'
const globalState = useGlobalState()
</script>

<style scoped lang="scss">
.ivyforms-page-section {
  border-radius: 12px;
  background: var(--map-ground-level-1-foreground);
  flex-direction: column;
  overflow: hidden;
  position: relative;
  height: 100%;
  /* Shadow/Sheet */
  box-shadow:
    0 10px 10px -2px rgba(18, 26, 38, 0.05),
    0 4px 3px 0px rgba(18, 26, 38, 0.03);

  &__content {
    height: calc(100% - 30px);
    max-width: 100%;
    box-sizing: border-box;
    overflow: hidden;

    &__fullscreen {
      height: calc(100% - 30px);
    }
  }
}
</style>
