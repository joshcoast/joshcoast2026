<template>
  <h3>Menu Props Controls</h3>
  <div class="menu-controls">
    <label>
      Priority:
      <select v-model="priorityMenu">
        <option value="primary">Primary</option>
        <option value="secondary">Secondary</option>
        <option value="tertiary">Tertiary</option>
      </select>
    </label>

    <label>
      Size:
      <select v-model="sizeMenu">
        <option value="s">Small</option>
        <option value="d">Default</option>
        <option value="l">Large</option>
      </select>
    </label>

    <label>
      Type:
      <select v-model="typeMenu">
        <option value="filled">Filled</option>
        <option value="tonal">Tonal</option>
        <option value="white">White</option>
      </select>
    </label>

    <label>
      Mode:
      <select v-model="mode">
        <option value="horizontal">Horizontal</option>
        <option value="vertical">Vertical</option>
      </select>
    </label>
  </div>

  <h3>Menu Component Preview</h3>
  <div class="menu-preview">
    <IvyMenu :mode="mode" :ellipsis="false">
      <IvyHeaderButton
        v-for="item in menuItems"
        :key="item.index"
        :label="item.label"
        :is-active="activeIndex === item.index"
        :index="item.index"
        :priority="priorityMenu"
        :size="sizeMenu"
        :type="typeMenu"
        :icon-start-config="item.iconConfig as IconConfig"
        @click="setActiveMenuItem(item.index)"
      />
    </IvyMenu>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import type { IconCategory } from '@/types/icons/icon-category'
import type { IconType } from '@/types/icons/icon-type'

const priorityMenu = ref<'primary' | 'secondary' | 'tertiary'>('primary')
const sizeMenu = ref<'s' | 'd' | 'l'>('d')
const typeMenu = ref<'filled' | 'tonal' | 'white'>('filled')
const mode = ref<'horizontal' | 'vertical'>('horizontal')

// Active state logic
const activeIndex = ref('details')
const setActiveMenuItem = (index: string) => {
  activeIndex.value = index
}

// Menu Items
const menuItems = ref([
  { label: 'Details', index: 'details', iconConfig: { name: 'user' } },
  { label: 'Services', index: 'services', iconConfig: { name: 'info', type: 'outline' } },
  { label: 'Events', index: 'events', iconConfig: { name: 'clipboard', type: 'outline' } },
])

interface IconConfig {
  name: string
  type?: IconType
  category?: IconCategory
  size?: 'l' | 'd' | 's' | 'xs'
  outerSize?: string
}
</script>

<style lang="scss">
.menu-preview {
  width: 600px;
  align-items: center;
}
</style>
