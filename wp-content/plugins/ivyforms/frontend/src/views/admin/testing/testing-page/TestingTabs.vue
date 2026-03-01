<template>
  <h3>Tabs Props Controls</h3>
  <div class="tabs-controls">
    <label>
      <input v-model="background" type="checkbox" />
      Background
    </label>

    <label>
      Priority:
      <select v-model="priority">
        <option value="primary">Primary</option>
        <option value="secondary">Secondary</option>
        <option value="tertiary">Tertiary</option>
        <option value="amber">Amber</option>
      </select>
    </label>

    <label>
      Size:
      <select v-model="size">
        <option value="s">Small</option>
        <option value="d">Default</option>
        <option value="l">Large</option>
      </select>
    </label>

    <label>
      Type:
      <select v-model="type">
        <option value="filled">Filled</option>
        <option value="tonal">Tonal</option>
        <option value="fab">FAB</option>
      </select>
    </label>

    <label>
      Position:
      <select v-model="position">
        <option value="top">Top</option>
        <option value="bottom">Bottom</option>
        <option value="left">Left</option>
        <option value="right">Right</option>
      </select>
    </label>

    <label>
      <input v-model="addTab" type="checkbox" />
      Enable Add/Remove Tabs
    </label>
  </div>

  <h3>Tabs Component Preview</h3>
  <div class="component-preview">
    <IvyTabs
      v-model="modelValue"
      :size="size"
      :type="type"
      :priority="priority"
      :position="position"
      :add-tab="addTab"
      :background="background"
      :tabs="[
        { name: 'tab1', label: 'Home', iconConfig: { name: 'info', type: 'outline', size: 'l' } },
        { name: 'tab2', label: 'User' },
        { name: 'tab3', label: 'Settings' },
      ]"
    />
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

// Props data
const background = ref(false)
const priority = ref<'primary' | 'secondary' | 'tertiary' | 'amber'>('primary')
const size = ref<'s' | 'd' | 'l'>('d')
const type = ref<'filled' | 'tonal' | 'fab'>('filled')
const position = ref<'top' | 'bottom' | 'left' | 'right'>('top')
const addTab = ref(false)
const modelValue = ref('tab1')
</script>
