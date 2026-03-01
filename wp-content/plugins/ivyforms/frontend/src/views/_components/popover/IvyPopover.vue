<template>
  <div class="ivyforms-popover">
    <ElPopover
      ref="elPopoverRef"
      v-bind="{ ...$props, ...$attrs }"
      :popper-class="
        `ivyforms-popover-popper ${props.popperBackground} ` +
        `ivyforms-popover-arrow ${props.popperArrow}` +
        (props.isWithScrollbar ? ' is-with-scrollbar' : '') +
        (props.popperClass ? ' ' + props.popperClass : '')
      "
      :teleported="false"
      @show="handleShow"
      @hide="handleHide"
    >
      <template #reference>
        <slot name="reference" />
      </template>
      <template v-if="$slots.default && props.isWithScrollbar">
        <!--            TODO Add the IvyScrollbar component-->
      </template>
      <slot v-else />
    </ElPopover>
  </div>
</template>

<script setup lang="ts">
import { ref, type Ref, onBeforeUnmount } from 'vue'
import { ElPopover } from 'element-plus'

interface Props {
  popperClass?: string
  popperBackground?: 'level-1-foreground' | 'level-2-foreground' | 'level-3-foreground'
  popperArrow?: 'with-arrow' | 'without-arrow'
  isWithScrollbar?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  popperClass: undefined,
  popperBackground: 'level-2-foreground',
  popperArrow: 'without-arrow',
  isWithScrollbar: false,
})

const emit = defineEmits(['show'])

const elPopoverRef: Ref<typeof ElPopover> = ref()

function handleEscape(event: KeyboardEvent) {
  if (event.key === 'Escape' && elPopoverRef.value) {
    elPopoverRef.value.hide?.()
  }
}

function handleShow() {
  window.addEventListener('keydown', handleEscape)
  emit('show')
}

function handleHide() {
  window.removeEventListener('keydown', handleEscape)
}

onBeforeUnmount(() => {
  window.removeEventListener('keydown', handleEscape)
})

const hide = () => {
  elPopoverRef.value?.hide()
}

defineExpose({
  hide,
})
</script>

<style lang="scss">
@use 'sass:list' as *;
@use 'sass:meta' as *;
// Popover Popper
.ivyforms-popover-popper {
  // Element Popper
  &.el-popper {
    border-radius: 8px;
    border: none;
    font-size: 12px;
    font-style: normal;
    font-weight: 400;
    line-height: 16px;
    background: var(--map-wb);
    display: flex;
    flex-direction: column;
    color: var(--map-base-text-0);

    &.is-light {
      border: none;
    }
    p {
      color: var(--map-base-text-0);
    }

    &.without-arrow {
      // Arrow
      .el-popper__arrow::before {
        display: none;
      }
    }
    &.with-arrow {
      // Arrow
      .el-popper__arrow::before {
        display: flex;
      }
    }
    &.level-1-foreground {
      align-items: flex-start;
    }
    // Level 1,2,3 Background
    &.level-1-foreground,
    &.level-2-foreground,
    &.level-3-foreground {
      background: var(--map-ground-level-2-foreground);
      box-shadow: var(--shadow-300);
      // Arrow
      .el-popper__arrow::before {
        background: var(--map-ground-level-2-foreground);
        border-color: var(--map-ground-level-2-foreground);
      }
    }

    // Define levels in a map
    $levels: (
      '1': (
        var(--shadow-100),
        4px,
        0px,
        2px,
      ),
      '2': (
        var(--shadow-300),
        16px 16px 16px 16px,
        16px,
        12px,
      ),
      '3': (
        var(--shadow-500),
        8px,
        8px,
        16px,
      ),
    );
    // Function to process each level value if is array
    @each $level, $values in $levels {
      &.level-#{$level}-foreground {
        box-shadow: nth($values, 1);
        padding: nth($values, 2) !important;
        gap: nth($values, 3);
        border-radius: nth($values, 4);
        min-width: 50px;
        transform: translate(0px);
      }
    }
    // Title
    .el-popover__title {
      font-size: 12px;
      font-style: normal;
      font-weight: 500;
      line-height: 16px;
      margin-bottom: 0;
      color: var(--map-base-text-0);
    }
  }
}
</style>
