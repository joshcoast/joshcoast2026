<template>
  <div>
    <IvyTabs
      v-model="activeButtonTab"
      type="tonal"
      priority="primary"
      :tabs="[
        { name: 'button-controls', label: 'Button controls' },
        { name: 'button-views', label: 'Button views' },
      ]"
    />
    <div v-if="activeButtonTab === 'button-controls'">
      <h3>Button Props Controls</h3>
      <div class="button-controls">
        <label>
          Priority:
          <select v-model="priority">
            <option value="primary">Primary</option>
            <option value="secondary">Secondary</option>
            <option value="tertiary">Tertiary</option>
            <option value="success">Success</option>
            <option value="warning">Warning</option>
            <option value="danger">Danger</option>
            <option value="white">White</option>
            <option value="shadow-white">Shadow White</option>
          </select>
        </label>

        <label>
          Size:
          <select v-model="size">
            <option value="l">Large</option>
            <option value="d">Default</option>
            <option value="s">Small</option>
            <option value="xs">Extra Small</option>
          </select>
        </label>

        <label>
          Type:
          <select v-model="type">
            <option value="fill">Fill</option>
            <option value="border">Border</option>
            <option value="ghost">Ghost</option>
          </select>
        </label>

        <label>
          Icon Start:
          <select v-model="iconStart">
            <option v-for="icon in iconNames.global" :key="icon" :value="icon">{{ icon }}</option>
          </select>
        </label>

        <label>
          Icon Start Type:
          <select v-model="iconStartType">
            <option value="fill">Fill</option>
            <option value="fill-duo">Fill Duo</option>
            <option value="line">Line</option>
            <option value="broken">Broken</option>
            <option value="outline">Outline</option>
          </select>
        </label>

        <label>
          Icon End:
          <select v-model="iconEnd">
            <option v-for="icon in iconNames.global" :key="icon" :value="icon">{{ icon }}</option>
          </select>
        </label>

        <label>
          Icon End Type:
          <select v-model="iconEndType">
            <option value="fill">Fill</option>
            <option value="fill-duo">Fill Duo</option>
            <option value="line">Line</option>
            <option value="broken">Broken</option>
            <option value="outline">Outline</option>
          </select>
        </label>

        <label>
          Full Width:
          <input v-model="fullWidth" type="checkbox" />
        </label>

        <label>
          Icon Only:
          <input v-model="iconOnly" type="checkbox" />
        </label>

        <label>
          Disabled:
          <input v-model="disabled" type="checkbox" />
        </label>

        <label>
          Loading:
          <input v-model="loading" type="checkbox" />
        </label>

        <IvyButtonAction priority="danger" @click="resetInputs">Reset</IvyButtonAction>
      </div>

      <h3>Button Component Preview</h3>
      <div class="button-preview">
        <IvyButtonAction
          :priority="priority"
          :size="size"
          :type="type"
          :icon-start="iconStart"
          :icon-start-type="iconStartType"
          :icon-end="iconEnd"
          :icon-end-type="iconEndType"
          :full-width="fullWidth"
          :icon-only="iconOnly"
          :disabled="disabled"
          :loading="loading"
        >
          Test Button
        </IvyButtonAction>
      </div>
    </div>

    <div v-if="activeButtonTab === 'button-views'">
      <TestingbuttonViews />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import IvyButtonAction from '@/views/_components/button/IvyButtonAction.vue'

const priority = ref<
  'primary' | 'secondary' | 'tertiary' | 'success' | 'warning' | 'danger' | 'white' | 'shadow-white'
>('primary')
const size = ref<'l' | 'd' | 's' | 'xs'>('d')
const type = ref<'fill' | 'border' | 'ghost'>('fill')
const iconStart = ref<string>('')
const iconStartType = ref<'fill' | 'outline'>('fill')
const iconEnd = ref<string>('')
const iconEndType = ref<'fill' | 'outline'>('fill')
const fullWidth = ref<boolean>(false)
const iconOnly = ref<boolean>(false)
const disabled = ref<boolean>(false)
const loading = ref<boolean>(false)

const activeButtonTab = ref('button-controls')

// Manually list some common icons for testing
// This is a testing page, so we just need a few examples
const iconNames = {
  global: [
    'archive',
    'bell',
    'calendar-dot',
    'cart',
    'check-circle',
    'clock',
    'close-circle',
    'copy',
    'edit',
    'email',
    'export',
    'filter',
    'image',
    'info',
    'link',
    'search',
    'settings',
    'trash',
    'user',
    'warning',
  ],
}
const resetInputs = () => {
  priority.value = 'primary'
  size.value = 'd'
  type.value = 'fill'
  iconStart.value = ''
  iconStartType.value = 'fill'
  iconEnd.value = ''
  iconEndType.value = 'fill'
  fullWidth.value = false
  iconOnly.value = false
  disabled.value = false
  loading.value = false
}
</script>

<style lang="scss">
.button-controls {
  margin-bottom: 20px;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 6px;
  display: flex;
  flex-direction: column;
  gap: 20px;
  flex-grow: 1;
}

.button-preview {
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
