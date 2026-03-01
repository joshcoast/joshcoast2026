<template>
  <div>
    <h3>Alert Component Controls</h3>
    <div class="alert-controls">
      <!-- Title -->
      <label>
        Title:
        <input v-model="title" placeholder="Enter title" />
      </label>

      <!-- Description -->
      <label>
        Description:
        <input v-model="description" placeholder="Enter description" />
      </label>

      <!-- Alert Type -->
      <label>
        Alert Type:
        <select v-model="alertType">
          <option value="info">Info</option>
          <option value="success">Success</option>
          <option value="warning">Warning</option>
          <option value="error">Error</option>
          <option value="default">Default</option>
        </select>
      </label>
    </div>
    <hr />

    <!-- Alert Preview -->
    <div class="alert-preview">
      <h3>Alert Preview</h3>
      <IvyAlert :title="title" :description="description" :type="alertType" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import IvyAlert from '@/views/_components/alert/IvyAlert.vue'

const title = ref('')
const description = ref('')
const alertType = ref<'info' | 'success' | 'warning' | 'error' | 'default'>('info')
</script>

<style scoped>
.alert-controls {
  margin-bottom: 20px;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 6px;
}

.alert-controls label {
  display: block;
  margin-bottom: 10px;
}

.alert-preview {
  margin-top: 20px;
}

.alert-preview h3 {
  margin-bottom: 20px;
}

hr {
  margin: 20px 0;
  border: none;
  border-top: 1px solid #ddd;
}
</style>
