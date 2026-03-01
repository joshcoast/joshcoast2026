<template>
  <div>
    <h3>Message Component Controls</h3>
    <div class="message-controls">
      <!-- Message -->
      <label>
        Message:
        <input v-model="message" placeholder="Enter message" />
      </label>

      <!-- Message Type -->
      <label>
        Message Type:
        <select v-model="messageType">
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
    <div class="message-preview">
      <h3>Message Preview</h3>
      <IvyButtonAction @click="openMessage">Click for message</IvyButtonAction>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import IvyMessage from '@/views/_components/message/ivyMessage.ts'
const message = ref('This is a message')
const messageType = ref<'info' | 'success' | 'warning' | 'error' | 'default'>('info')

const openMessage = () => {
  const showMessage = {
    message: message.value,
    type: messageType.value,
  }
  IvyMessage(showMessage)
}
</script>

<style scoped>
.message-controls {
  margin-bottom: 20px;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 6px;
}

.message-controls label {
  display: block;
  margin-bottom: 10px;
}

.message-preview {
  margin-top: 20px;
}

.message-preview h3 {
  margin-bottom: 20px;
}

hr {
  margin: 20px 0;
  border: none;
  border-top: 1px solid #ddd;
}
</style>
