<template>
  <div
    class="ivyforms-page-panel ivyforms-p-12"
    :style="{ width: panelWidth, minWidth: panelWidth }"
  >
    <slot></slot>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps({
  width: {
    type: [String, Number],
    default: '240px',
  },
  minWidth: {
    type: [String, Number],
    default: '240px',
  },
})

// Ensures width has px suffix if it's a number
const panelWidth = computed(() => {
  if (typeof props.width === 'number') {
    return `${props.width}px`
  }
  return props.width
})
</script>

<style scoped lang="scss">
.ivyforms-page-panel {
  align-items: flex-start;
  align-self: stretch;
  border-radius: 12px;
  background: var(--map-ground-level-1-foreground);

  /* Shadow/Sheet */
  box-shadow:
    0 10px 10px -2px rgba(18, 26, 38, 0.05),
    0 4px 3px 0px rgba(18, 26, 38, 0.03);
}
</style>
