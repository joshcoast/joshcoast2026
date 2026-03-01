<template>
  <div
    :class="[
      'field-resize-handle ivyforms-flex ivyforms-justify-content-center ivyforms-align-items-center',
      `field-resize-handle--${direction}`,
      {
        'field-resize-handle--active': isResizing,
        'field-resize-handle--disabled': isDisabled,
      },
    ]"
    @mousedown="onMouseDown"
  >
    <div
      class="field-resize-handle__grip ivyforms-flex ivyforms-gap-2 ivyforms-align-items-center ivyforms-justify-content-center"
    >
      <div class="field-resize-handle__line"></div>
      <div class="field-resize-handle__line"></div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { Field } from '@/types/field'
import { useFieldResize } from '@/composables/useFieldResize'

interface Props {
  field: Field
  direction: 'left' | 'right'
}

const props = defineProps<Props>()

const { startResize, resizingField } = useFieldResize()

const isResizing = computed(() => {
  return resizingField.value?.fieldIndex === props.field.fieldIndex
})

const isDisabled = computed(() => {
  // Only supports right direction
  if (props.direction !== 'right') return true

  // Never disable - just enforce minimum width in the resize logic
  return false
})

const onMouseDown = (event: MouseEvent) => {
  if (isDisabled.value) return
  startResize(event, props.field, props.direction)
}
</script>

<style scoped lang="scss">
.field-resize-handle {
  position: absolute;
  top: 8px;
  bottom: 8px;
  width: 16px;
  cursor: col-resize;
  z-index: 150;
  opacity: 0;
  transition: opacity 0.15s ease;
  pointer-events: auto;

  &--left {
    left: -12px;
  }

  &--right {
    right: -14px;
  }

  &:hover,
  &--active {
    opacity: 1 !important;
  }

  // Keep visible when parent is being hovered for resize
  .ivyforms-draggable__item-wrapper--resizable:hover &,
  .ivyforms-draggable__item-wrapper--resizing &,
  .ivyforms-draggable__field-container:hover & {
    opacity: 1 !important;
  }

  // Global class when any field is being resized - keeps ALL handles visible
  :global(body.ivyforms-resizing-field) & {
    opacity: 1 !important;
  }

  &--disabled {
    cursor: not-allowed;

    .field-resize-handle__grip {
      .field-resize-handle__line {
        background: var(--map-base-dusk-stroke--2);
      }
    }

    &:hover .field-resize-handle__grip .field-resize-handle__line,
    &.field-resize-handle--active .field-resize-handle__grip .field-resize-handle__line {
      background: var(--map-base-dusk-stroke--2);
    }
  }

  &__grip {
    height: 100%;
    padding: 0 4px;
  }

  &__line {
    width: 2px;
    height: 24px;
    background: var(--map-base-purple-stroke-0);
    border-radius: 1px;
    transition: all 0.2s ease;
  }

  &:hover &__line,
  &--active &__line {
    height: 32px;
    background: var(--map-base-purple-symbol-0);
  }
}
</style>
