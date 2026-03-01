<template>
  <div class="field-width-indicator">
    {{ formattedWidth }}
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  width: number
}

const props = defineProps<Props>()

const formattedWidth = computed(() => {
  // Show exact numbers without decimals
  return `${Math.round(props.width)}%`
})
</script>

<style scoped lang="scss">
.field-width-indicator {
  position: absolute;
  top: -28px;
  left: 50%;
  transform: translateX(-50%);
  background: var(--map-ground-level-2-foreground);
  color: var(--map-base-text-0);
  padding: 4px 12px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
  line-height: 16px;
  white-space: nowrap;
  z-index: 200;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  pointer-events: none;
  user-select: none;
}
</style>
