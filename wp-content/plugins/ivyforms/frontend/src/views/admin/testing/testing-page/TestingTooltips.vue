<template>
  <h3>Tooltip Props Controls</h3>
  <div class="tooltip-controls">
    <label>
      Theme:
      <select v-model="theme">
        <option value="light">Light</option>
        <option value="inverted">Inverted</option>
        <option value="dusk">Dusk</option>
      </select>
    </label>

    <label>
      Placement:
      <select v-model="placement">
        <option value="top">Top</option>
        <option value="bottom">Bottom</option>
        <option value="left">Left</option>
        <option value="right">Right</option>
      </select>
    </label>

    <label>
      Tooltip Content:
      <input v-model="content" type="text" />
    </label>

    <label>
      Max Width:
      <input v-model="maxWidth" type="text" placeholder="e.g. 320px" />
    </label>

    <label>
      Hide After (ms):
      <input v-model="hideAfter" type="number" />
    </label>
  </div>

  <h3>Tooltip Component Preview</h3>
  <div class="tooltip-preview">
    <IvyTooltip
      :content="content"
      :theme="theme"
      :placement="placement"
      :max-width="maxWidth"
      :hide-after="hideAfter"
      :model-value="modelValueTT"
      @update:model-value="modelValueTT = $event"
    >
      <IvyIcon name="question" type="outline" size="l" />
    </IvyTooltip>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

const theme = ref<'light' | 'inverted' | 'dusk'>('light')
const placement = ref<
  | 'top'
  | 'top-start'
  | 'top-end'
  | 'bottom'
  | 'bottom-start'
  | 'bottom-end'
  | 'left'
  | 'left-start'
  | 'left-end'
  | 'right'
  | 'right-start'
  | 'right-end'
>('top')
const content = ref('This is a tooltip')
const maxWidth = ref('320px')
const hideAfter = ref(0)
const modelValueTT = ref(true)
</script>

<style lang="scss">
.tooltip-preview {
  display: flex;
  justify-content: center;
  margin-top: 60px;
}
</style>
