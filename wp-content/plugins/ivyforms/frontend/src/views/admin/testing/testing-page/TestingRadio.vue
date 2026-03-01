<template>
  <div>
    <h3>Radio Button Props Controls</h3>
    <div class="radio-controls">
      <!-- Label Position -->
      <label>
        Label Position:
        <select v-model="labelPosition">
          <option value="left">Left</option>
          <option value="right">Right</option>
        </select>
      </label>

      <!-- Description -->
      <label>
        Description:
        <input v-model="description" placeholder="Enter description" />
      </label>

      <!-- Background -->
      <label>
        <input v-model="background" type="checkbox" />
        Background
      </label>

      <!-- Priority -->
      <label>
        Priority:
        <select v-model="priority">
          <option value="primary">Primary</option>
          <option value="secondary">Secondary</option>
          <option value="amber">Amber</option>
        </select>
      </label>

      <!-- Size -->
      <label>
        Size:
        <select v-model="size">
          <option value="s">Small</option>
          <option value="d">Default (medium)</option>
          <option value="l">Large</option>
          <option value="xl">Extra Large</option>
        </select>
      </label>

      <!-- Style -->
      <label>
        Style:
        <select v-model="type">
          <option value="checkmark">Checkmark</option>
          <option value="withMarker">With Marker</option>
        </select>
      </label>

      <!-- Direction -->
      <label>
        Direction:
        <select v-model="direction">
          <option value="horizontal">Horizontal</option>
          <option value="vertical">Vertical</option>
        </select>
      </label>
    </div>
    <hr />

    <!-- Radio Button Preview -->
    <div class="radio-preview">
      <h3>Radio Button Preview</h3>
      <IvyRadioGroup v-model="radioValue" :direction="direction">
        <IvyRadio
          :label-position="labelPosition"
          :description="description"
          :background="background"
          :priority="priority"
          :size="size"
          :type="type"
          value="radio1"
        >
          Test Radio Button 1
        </IvyRadio>
        <IvyRadio
          :label-position="labelPosition"
          :description="description"
          :background="background"
          :priority="priority"
          :size="size"
          :type="type"
          value="radio2"
        >
          Test Radio Button 2
        </IvyRadio>
        <IvyRadio
          :label-position="labelPosition"
          :description="description"
          :background="background"
          :priority="priority"
          :size="size"
          :type="type"
          value="radio3"
          disabled
        >
          Test Radio Button 3
        </IvyRadio>
      </IvyRadioGroup>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import IvyRadioGroup from '@/views/_components/radio/IvyRadioGroup.vue'
const radioValue = ref('') // Set an initial value

const labelPosition = ref<'left' | 'right'>('left')
const description = ref('')
const background = ref(false)
const priority = ref<'primary' | 'secondary' | 'amber'>('primary')
const size = ref<'s' | 'd' | 'l' | 'xl'>('d')
const type = ref<'checkmark' | 'withMarker'>('checkmark')
const direction = ref<'horizontal' | 'vertical'>('horizontal')
</script>

<style scoped>
.props-controls {
  margin-bottom: 20px;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 6px;
}
.props-controls label {
  display: block;
  margin-bottom: 10px;
}
.radio-preview {
  margin-top: 20px;
}
.radio-preview h3 {
  margin-bottom: 20px;
}
hr {
  margin: 20px 0;
  border: none;
  border-top: 1px solid #ddd;
}
</style>
