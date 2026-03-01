<template>
  <h3>Menu Props Controls</h3>
  <div class="menu-controls">
    <label>
      Priority:
      <select>
        <!--      <select v-model="priority">-->
        <option value="primary">Primary</option>
        <option value="secondary">Secondary</option>
      </select>
    </label>

    <label>
      Style:
      <select>
        <!--      <select v-model="style">-->
        <option value="fill">Fill</option>
        <option value="ghost">Ghost</option>
      </select>
    </label>
  </div>

  <h3>Menu Component Preview</h3>
  <div class="menu-preview">
    <IvyButtonAction ref="menuButtonRef" @click="openMenu">Open Context Menu</IvyButtonAction>
    <IvyContextMenu />
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useContextMenuStore } from '@/stores/contextMenuStore.ts'
import {
  createContextMenuAction,
  ContextMenuActionType,
} from '@/views/_components/context-menu/kit/ContextMenuActionType'
const contextMenuStore = useContextMenuStore()
const menuButtonRef = ref(null)

const actions = [
  createContextMenuAction(ContextMenuActionType.Edit),
  createContextMenuAction(ContextMenuActionType.Delete, {
    handler: () => console.log('Custom delete handler'),
  }),
  createContextMenuAction(ContextMenuActionType.Duplicate),
]

const openMenu = () => {
  contextMenuStore.openContextMenu({
    actions: actions,
    contextMenuButtonRef: menuButtonRef.value,
  })
}
</script>

<style lang="scss">
.menu-preview {
  width: 600px;
  align-items: center;
}
</style>
