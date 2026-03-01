<template>
  <div>
    <el-link
      v-bind="linkAttrs"
      :underline="false"
      class="ivyforms-link"
      :class="`priority-${props.priority} size-${props.size} ${isPressed ? 'pressed' : ''} ${isUsed ? 'used' : ''}`"
      @click="handleClick"
      @mousedown="handleMouseDown"
      @mouseup="handleMouseUp"
      @mouseleave="handleMouseLeave"
    >
      <slot></slot>
    </el-link>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'

interface Props {
  href: string
  priority?: 'primary' | 'secondary' | 'amber'
  target?: '_blank' | '_self'
  rel?: 'noopener' | 'noreferrer'
  size?: 's' | 'd'
  onClick?: (event: Event) => void
}

const props = withDefaults(defineProps<Props>(), {
  priority: 'primary',
  target: '_self',
  rel: 'noreferrer',
  size: 'd',
  href: '#',
  onClick: () => {},
})

const isPressed = ref(false)
const isUsed = ref(false)

// Computed properties for dynamic attributes
const linkAttrs = computed(() => {
  return {
    href: props.href === '#' ? '' : props.href, // Prevent navigation if href is invalid
    target: props.target,
    rel: props.target === '_blank' ? props.rel : '',
  }
})

// Handle click events
const handleClick = (event: Event) => {
  if (props.href === '#' || !props.href) {
    event.preventDefault() // Prevent default navigation if href is invalid
  } else {
    isUsed.value = true // Mark as used after navigation
  }
  if (props.onClick) {
    props.onClick(event)
  }
}

// Handle mouse down event to detect pressed state
const handleMouseDown = () => {
  isPressed.value = true
}

// Handle mouse up event to reset pressed state
const handleMouseUp = () => {
  isPressed.value = false
}

// Handle mouse leave event to reset pressed state
const handleMouseLeave = () => {
  isPressed.value = false
}
</script>
<style lang="scss">
@use 'sass:list' as *;
@use 'sass:meta' as *;
// Radio
.ivyforms-link {
  color: var(--map-base-dusk-symbol-2);
  text-align: center;

  font-family: Roboto;
  font-style: normal;
  font-weight: 500;

  &.size-s {
    font-size: 14px;
    line-height: 20px;
  }
  &.size-d {
    font-size: 16px;
    line-height: 22px;
  }

  &.el-link {
    border: none;
    color: var(--map-base-dusk-symbol-2);
    .el-link__inner {
      border: none;
    }
  }

  $priorities: (
    'primary': (
      var(--map-base-brand-symbol-0),
      var(--map-base-brand-symbol-1),
    ),
    'secondary': (
      var(--map-base-purple-symbol-0),
      var(--map-base-purple-symbol-1),
    ),
  );

  // Function to process each priority value if is array
  @each $priority, $values in $priorities {
    &.priority-#{$priority} {
      &:hover {
        color: nth($values, 1);
        border: none;
        box-shadow: none;
        outline: none;
      }
      &.pressed {
        color: nth($values, 2); // For pressed state
        border: none;
        box-shadow: none;
        outline: none;
      }

      &.used {
        color: var(--map-base-dusk-symbol-0); // For used state
        border: none;
        box-shadow: none;
        outline: none;
      }
    }
  }
}
</style>
