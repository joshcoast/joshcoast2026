<template>
  <div class="toggle-test-page">
    <h3>IvyToggle Props Controls</h3>
    <div class="props-controls">
      <!-- Model Value -->
      <label>
        <input v-model="modelValue" type="checkbox" />
        Checked
      </label>

      <!-- Disabled -->
      <label>
        <input v-model="disabled" type="checkbox" />
        Disabled
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
          <option value="d">Default</option>
          <option value="l">Large</option>
          <option value="xl">Extra Large</option>
        </select>
      </label>

      <!-- Type -->
      <label>
        Type:
        <select v-model="type">
          <option value="fill">Fill</option>
          <option value="stroke">Stroke</option>
          <option value="stroke-with-text">Stroke with Text</option>
        </select>
      </label>
    </div>

    <hr />

    <!-- IvyToggle Preview -->
    <div class="toggle-preview">
      <h3>IvyToggle Preview</h3>
      <IvyToggle
        v-model="modelValue"
        :disabled="disabled"
        :priority="priority"
        :size="size"
        :type="type"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import IvyToggle from '@/views/_components/toggle/IvyToggle.vue'

const modelValue = ref(false)
const disabled = ref(false)
const priority = ref<'primary' | 'secondary' | 'amber'>('primary')
const size = ref<'s' | 'd' | 'l' | 'xl'>('d')
const type = ref<'fill' | 'stroke' | 'stroke-with-text'>('fill')
</script>

<style scoped>
.toggle-test-page {
  padding: 20px;
}

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

.toggle-preview {
  margin-top: 20px;
}

.toggle-preview h3 {
  margin-bottom: 20px;
}

hr {
  margin: 20px 0;
  border: none;
  border-top: 1px solid #ddd;
}
</style>
