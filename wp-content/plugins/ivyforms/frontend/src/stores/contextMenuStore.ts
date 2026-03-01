import type { ShallowRef, DefineComponent, Ref } from 'vue'
import { defineStore } from 'pinia'
import { ref, shallowRef, markRaw } from 'vue'
import IvyContextMenuActions from '@/views/_components/context-menu/IvyContextMenuActions.vue'

export const useContextMenuStore = defineStore('contextMenu', () => {
  const isOpen: Ref<boolean> = ref(false)
  const contextMenuContent: ShallowRef<DefineComponent | null> = shallowRef(null)
  const contextMenuProps: Ref<{
    actions?: []
    menuTitle?: string
    contextMenuButtonRef?: HTMLElement | DefineComponent
    entityId?: number
    onClose?: () => void
    selectionType?: 'singleselect' | 'multiselect'
  }> = ref({})

  const openContextMenu = (props = {}) => {
    if (isOpen.value) {
      closeContextMenu() // Close any existing menu
    }

    // Use markRaw to prevent the component from being made reactive
    contextMenuContent.value = markRaw(IvyContextMenuActions as unknown as DefineComponent)
    contextMenuProps.value = props

    const buttonRef = contextMenuProps.value.contextMenuButtonRef
    let resolvedButtonRef: HTMLElement | null = null

    if (buttonRef && '$el' in buttonRef) {
      resolvedButtonRef = (buttonRef as DefineComponent).$el as HTMLElement
    } else if (buttonRef instanceof HTMLElement) {
      resolvedButtonRef = buttonRef
    }

    if (resolvedButtonRef) {
      resolvedButtonRef.classList.add('opened')
    }

    isOpen.value = true
  }

  const closeContextMenu = () => {
    isOpen.value = false
    const buttonRef = contextMenuProps.value.contextMenuButtonRef
    let resolvedButtonRef: HTMLElement | null = null

    if (buttonRef && '$el' in buttonRef) {
      resolvedButtonRef = (buttonRef as DefineComponent).$el as HTMLElement
    } else if (buttonRef instanceof HTMLElement) {
      resolvedButtonRef = buttonRef
    }

    if (resolvedButtonRef) {
      resolvedButtonRef.classList.remove('opened')
    }

    // Trigger the onClose callback if it exists
    if (typeof contextMenuProps.value.onClose === 'function') {
      contextMenuProps.value.onClose()
    }

    setTimeout(() => {
      if (!isOpen.value) {
        contextMenuProps.value = {}
        contextMenuContent.value = null
      }
    }, 200)
  }

  return {
    isOpen,
    contextMenuContent,
    contextMenuProps,
    openContextMenu,
    closeContextMenu,
  }
})
