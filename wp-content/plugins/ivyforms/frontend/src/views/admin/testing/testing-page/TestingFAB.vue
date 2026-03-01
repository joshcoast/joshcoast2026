<template>
  <h3>FAB Props Controls</h3>
  <div class="fab-controls">
    <label>
      <select v-model="color">
        <option value="fab">FAB</option>
        <option value="tertiary">Tertiary</option>
      </select>
    </label>

    <label>
      <input v-model="indicator" type="checkbox" />
      Indicator
    </label>

    <label>
      Indicator Priority:
      <select v-model="priority">
        <option value="primary">Primary</option>
        <option value="secondary">Secondary</option>
        <option value="amber">Amber</option>
      </select>
    </label>

    <label>
      Option:
      <select v-model="option">
        <option value="sort">Sort</option>
        <option value="filter">Filter</option>
        <option value="folder">Folder</option>
        <option value="datepicker">Date Picker</option>
        <option value="import">Import</option>
        <option value="export">Export</option>
      </select>
    </label>
  </div>

  <h3>FAB Component Preview</h3>
  <div class="fab-preview">
    <IvyIndicatorButton
      :color="color"
      :type="option"
      :indicator-priority="priority"
      :indicator="indicator"
    />
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

const color = ref<'fab' | 'tertiary'>('fab')
const priority = ref<'primary' | 'secondary' | 'amber'>('secondary')
const option = ref<'sort' | 'filter' | 'folder' | 'datepicker' | 'import' | 'export'>('sort')
const indicator = ref(true)
</script>

<style lang="scss">
.fab-preview {
  width: 600px;
  align-items: center;
}
</style>
