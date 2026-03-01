<template>
  <IvyPopover
    :visible="isOpen"
    popper-class="ivyforms-popper-context-menu"
    :popper-background="'level-1-foreground'"
    :width="props.width"
    placement="bottom-start"
    :virtual-ref="resolveContextMenuButtonRef"
    :show-arrow="false"
    :offset="props.offset"
    virtual-triggering
  >
    <component
      :is="contextMenuStore.contextMenuContent"
      :actions="contextMenuStore.contextMenuProps.actions"
      :entity-id="contextMenuStore.contextMenuProps.entityId"
      :selection-type="contextMenuStore.contextMenuProps.selectionType"
      @action-executed="handleActionExecuted"
      @close-menu="contextMenuStore.closeContextMenu"
    />
  </IvyPopover>
</template>

<script setup lang="ts">
import { ref, computed, type ComponentPublicInstance } from 'vue'
import { storeToRefs } from 'pinia'
import { useContextMenuStore } from '@/stores/contextMenuStore.ts'
import { watch } from 'vue'
import { onClickOutside } from '@vueuse/core'
import IvyPopover from '@/views/_components/popover/IvyPopover.vue'

interface Props {
  width?: number
  offset?: number
}

const props = withDefaults(defineProps<Props>(), {
  width: 205,
  offset: undefined,
})

const contextMenuStore = useContextMenuStore()
const { isOpen } = storeToRefs(contextMenuStore)

const stopClickOutsideListener = ref<null | (() => void)>(null)

const handleActionExecuted = () => {
  if (!contextMenuStore.contextMenuProps.selectionType) {
    contextMenuStore.closeContextMenu()
  }
}

const menuProps = computed(() => contextMenuStore.contextMenuProps)

type MaybeHTMLElement = HTMLElement | (ComponentPublicInstance & { $el: HTMLElement })

watch(
  () => menuProps.value.contextMenuButtonRef,
  (newRef) => {
    if (stopClickOutsideListener.value) {
      // Cleanup old listener
      stopClickOutsideListener.value()
    }
    if (newRef instanceof HTMLElement) {
      stopClickOutsideListener.value = onClickOutside(newRef, (event) => {
        // Don't close if selectionType is present and click is on a menu item
        if (contextMenuStore.contextMenuProps.selectionType) {
          const target = event.target as HTMLElement
          if (target.closest('.ivyforms-popper-context-menu')) {
            return
          }
        }
        contextMenuStore.closeContextMenu()
      })
    } else if (newRef && newRef.$el && newRef.$el instanceof HTMLElement) {
      stopClickOutsideListener.value = onClickOutside(newRef.$el, (event) => {
        // Don't close if selectionType is present and click is on a menu item
        if (contextMenuStore.contextMenuProps.selectionType) {
          const target = event.target as HTMLElement
          if (target.closest('.ivyforms-popper-context-menu')) {
            return
          }
        }
        contextMenuStore.closeContextMenu()
      })
    } else {
      stopClickOutsideListener.value = null
    }
  },
)

const resolveContextMenuButtonRef = computed(() => {
  const ref = contextMenuStore.contextMenuProps.contextMenuButtonRef as
    | MaybeHTMLElement
    | null
    | undefined
  if (ref && '$el' in ref && ref.$el instanceof HTMLElement) {
    return ref.$el
  }
  if (ref instanceof HTMLElement) {
    return ref
  }
  return null
})

// Watch for when the context menu closes and remove the click outside listener
watch(isOpen, (newVal) => {
  if (!newVal && stopClickOutsideListener.value) {
    stopClickOutsideListener.value()
    stopClickOutsideListener.value = null
  }
})
</script>

<style lang="scss">
// Popper Context Menu
.ivyforms-popper-context-menu {
  // Element Popper
  &.el-popper.el-popover {
    padding: 0;
    border-radius: 2px;
    background: var(--map-ground-level-2-foreground);
    box-shadow:
      0 1px 2px 0 rgba(18, 26, 38, 0.3),
      0 1px 3px 1px rgba(18, 26, 38, 0.15);
  }
}
</style>
