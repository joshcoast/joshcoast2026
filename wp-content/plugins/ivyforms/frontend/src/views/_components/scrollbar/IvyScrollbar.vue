<template>
  <ElScrollbar
    ref="scrollbar"
    v-bind="{ ...$props, ...$attrs }"
    :class="[
      'ivyforms-scrollbar',
      `priority-${props.priority}`,
      props.modifier ? `ivyforms-scrollbar--${props.modifier}` : null,
    ]"
  >
    <slot />
  </ElScrollbar>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import type { ElScrollbar } from 'element-plus'
import { useLabels } from '@/composables/useLabels'

const { getLabel } = useLabels()
const scrollbar = ref<typeof ElScrollbar>(null)

type ScrollBehavior = 'auto' | 'instant' | 'smooth'

interface ScrollOptions {
  behavior?: ScrollBehavior
}

interface ScrollToOptions extends ScrollOptions {
  left?: number
  top?: number
}

interface Props {
  priority?: 'primary' | 'secondary' | 'amber' | 'dusk' | 'mix'
  modifier?: string
}

const props = withDefaults(defineProps<Props>(), {
  priority: 'mix',
  modifier: undefined,
})

const handleScroll = () => {
  scrollbar.value?.handleScroll()
}

const scrollTo = (options: ScrollToOptions | number, yCoord?: number) => {
  scrollbar.value?.scrollTo(options, yCoord)
}

const setScrollTop = (scrollTop: number) => {
  scrollbar.value?.setScrollTop(scrollTop)
}

const setScrollLeft = (scrollLeft: number) => {
  scrollbar.value?.setScrollLeft(scrollLeft)
}

const update = () => {
  scrollbar.value?.update()
}

const getWrapRef = () => {
  return scrollbar.value.wrapRef
}

// Add keyboard accessibility to scrollbar wrap element
onMounted(() => {
  if (scrollbar.value?.wrapRef) {
    const wrapElement = scrollbar.value.wrapRef as HTMLElement
    // Make scrollbar focusable with keyboard
    wrapElement.setAttribute('tabindex', '0')
    // Add ARIA role for better screen reader support
    wrapElement.setAttribute('role', 'region')
    wrapElement.setAttribute('aria-label', getLabel('scrollable_content'))
  }
})

defineExpose({
  handleScroll,
  scrollTo,
  setScrollTop,
  setScrollLeft,
  update,
  getWrapRef,
})
</script>

<style lang="scss">
@use 'sass:list' as *;

// Scrollbar
.ivyforms-scrollbar {
  // Element Scrollbar
  &.el-scrollbar {
    overflow: visible;

    // Priorities
    $priorities: (
      'primary': (
        var(--map-base-brand-fill-0),
        var(--map-base-brand-fill-1),
      ),
      'secondary': (
        var(--map-base-purple-fill-0),
        var(--map-base-purple-fill-1),
      ),
      'amber': (
        var(--map-accent-amber-fill-0),
        var(--map-accent-amber-fill-1),
      ),
      'dusk': (
        var(--map-base-dusk-fill-0),
        var(--map-base-dusk-fill-1),
      ),
      'mix': (
        var(--map-base-dusk-fill-0),
        var(--map-base-purple-fill-0),
      ),
    );

    // Priority Styles
    @mixin priorityStyles($priority, $colors: ()) {
      $background-color: nth($colors, 1);
      $hover-color: nth($colors, 2);

      // Priority
      &.priority-#{$priority} {
        // Bar
        .el-scrollbar__bar {
          // Thumb
          .el-scrollbar__thumb {
            opacity: 1;
            background-color: $background-color;

            &:hover {
              background-color: $hover-color;
            }
          }
        }
      }
    }

    @each $priority, $colors in $priorities {
      @include priorityStyles($priority, $colors);
    }

    .el-scrollbar__wrap {
      // Make sure the wrap uses native scrolling and picks up any max-height passed in via style
      overflow: auto !important;
      max-height: inherit !important;

      // Enable momentum/native scrolling on iOS / macOS WebKit browsers
      -webkit-overflow-scrolling: touch;

      // Prevent scroll chaining so the modal content scrolls independently of the page
      overscroll-behavior: contain;

      // Improve touch handling for trackpad / touchpad devices
      touch-action: pan-y;
    }

    // Target only direct .el-scrollbar__bar children, not nested scrollbars
    > .el-scrollbar__bar.is-vertical {
      right: 0;
      padding-left: 0;
    }
  }

  // Modifier: outside-vertical - only affects THIS scrollbar's bar, not children
  &.ivyforms-scrollbar--outside-vertical.el-scrollbar {
    > .el-scrollbar__bar.is-vertical {
      right: -10px;
      padding-left: 4px;
    }
  }
}
</style>
