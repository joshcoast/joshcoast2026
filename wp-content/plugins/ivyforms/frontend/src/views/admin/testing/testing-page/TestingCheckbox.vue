<template>
  <div>
    <h3>Checkbox Props Controls</h3>
    <div class="checkbox-controls">
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

      <!-- Indeterminate -->
      <label>
        Indeterminate:
        <input v-model="indeterminate" type="checkbox" />
      </label>
      <!-- Checkbox Group -->
      <label>
        Use Checkbox Group:
        <input v-model="useCheckboxGroup" type="checkbox" />
      </label>
    </div>
    <hr />

    <!-- Checkbox Preview -->
    <div class="checkbox-preview">
      <h3>Checkbox Preview</h3>

      <!-- Checkbox or Checkbox Group based on the toggle -->
      <div v-if="!useCheckboxGroup">
        <IvyCheckbox
          v-model="checkboxValue"
          :label-position="labelPosition"
          :description="description"
          :priority="priority"
          :size="size"
          :type="type"
          :indeterminate="indeterminate"
        >
          Sample Checkbox
        </IvyCheckbox>
      </div>

      <div v-else>
        <IvyCheckboxGroup v-model="checkboxGroupValue">
          <IvyCheckbox
            :label-position="labelPosition"
            :description="description"
            :priority="priority"
            :size="size"
            :type="type"
            value="checkbox1"
            :indeterminate="indeterminate"
          >
            Checkbox 1
          </IvyCheckbox>
          <IvyCheckbox
            :label-position="labelPosition"
            :description="description"
            :priority="priority"
            :size="size"
            :type="type"
            value="checkbox2"
            :indeterminate="indeterminate"
          >
            Checkbox 2
          </IvyCheckbox>
          <IvyCheckbox
            :label-position="labelPosition"
            :description="description"
            :priority="priority"
            :size="size"
            :type="type"
            value="checkbox3"
            disabled
            :indeterminate="indeterminate"
          >
            Checkbox 3
          </IvyCheckbox>
        </IvyCheckboxGroup>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import IvyCheckbox from '@/views/_components/checkbox/IvyCheckbox.vue'
import IvyCheckboxGroup from '@/views/_components/checkbox/IvyCheckboxGroup.vue'

const checkboxValue = ref(false)
const checkboxGroupValue = ref<string[]>([])

const labelPosition = ref<'left' | 'right'>('left')
const description = ref('')
const priority = ref<'primary' | 'secondary' | 'amber'>('primary')
const size = ref<'s' | 'd' | 'l' | 'xl'>('d')
const type = ref<'checkmark' | 'withMarker'>('checkmark')
const useCheckboxGroup = ref(false)
const indeterminate = ref(false)
</script>

<style scoped>
.checkbox-controls {
  margin-bottom: 20px;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 6px;
}
.checkbox-controls label {
  display: block;
  margin-bottom: 10px;
}

.checkbox-preview {
  margin-top: 20px;
}
.checkbox-preview h3 {
  margin-bottom: 20px;
}
hr {
  margin: 20px 0;
  border: none;
  border-top: 1px solid #ddd;
}
</style>
