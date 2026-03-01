<template>
  <div>
    <h3>Link Component Props Controls</h3>
    <div class="link-controls">
      <!-- Href -->
      <label>
        Href:
        <input v-model="href" placeholder="Enter the URL" />
      </label>

      <!-- Target -->
      <label>
        Target:
        <select v-model="target">
          <option value="_blank">_blank</option>
          <option value="_self">_self</option>
        </select>
      </label>

      <!-- Rel (visible only if target is not _self) -->
      <label v-if="target !== '_self'">
        Rel:
        <select v-model="rel">
          <option value="noopener">noopener</option>
          <option value="noreferrer">noreferrer</option>
        </select>
      </label>

      <!-- Priority -->
      <label>
        Priority:
        <select v-model="priority">
          <option value="primary">Primary</option>
          <option value="secondary">Secondary</option>
        </select>
      </label>

      <!-- Size -->
      <label>
        Size:
        <select v-model="size">
          <option value="d">Default</option>
          <option value="s">Small</option>
        </select>
      </label>
    </div>
    <hr />

    <!-- Link Component Preview -->
    <div class="link-preview">
      <h3>Link Component Preview</h3>
      <IvyLink :href="href" :target="target" :rel="rel" :priority="priority" :size="size">
        Test Link
      </IvyLink>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import IvyLink from '@/views/_components/link/IvyLink.vue'

const href = ref('https://www.example.com')
const target = ref<'_blank' | '_self'>('_blank')
const rel = ref<'noopener' | 'noreferrer'>('noopener')
const priority = ref<'primary' | 'secondary'>('primary')
const size = ref<'d' | 's'>('d')
</script>

<style scoped>
.link-controls {
  margin-bottom: 20px;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 6px;
}

.link-controls label {
  display: block;
  margin-bottom: 10px;
}

.link-preview {
  margin-top: 20px;
}

.link-preview h3 {
  margin-bottom: 20px;
}

hr {
  margin: 20px 0;
  border: none;
  border-top: 1px solid #ddd;
}
</style>
