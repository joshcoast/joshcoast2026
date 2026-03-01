<template>
  <div>
    <h3>IvySearch Props Controls</h3>
    <div class="search-controls">
      <!-- Placeholder -->
      <label>
        Placeholder:
        <input v-model="placeholder" placeholder="Enter placeholder text" />
      </label>

      <!-- Priority -->
      <label>
        Priority:
        <select v-model="priority">
          <option value="primary">Primary</option>
          <option value="secondary">Secondary</option>
        </select>
      </label>

      <label>
        Disabled:
        <input v-model="disabled" type="checkbox" />
      </label>
    </div>
    <hr />

    <!-- Search Input Preview -->
    <div class="search-preview">
      <h3>Search Input Preview</h3>
      <IvySearch
        v-model="searchValue"
        :placeholder="placeholder"
        :priority="priority"
        :disabled="disabled ? '' : undefined"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import IvySearch from '@/views/_components/search/IvySearch.vue'

const searchValue = ref('')
const placeholder = ref('Search')
const priority = ref<'primary' | 'secondary'>('primary')
const disabled = ref<boolean>(false)
</script>

<style scoped>
.search-controls {
  margin-bottom: 20px;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 6px;
}
.search-controls label {
  display: block;
  margin-bottom: 10px;
}
.search-preview {
  margin-top: 20px;
}
.search-preview h3 {
  margin-bottom: 20px;
}
hr {
  margin: 20px 0;
  border: none;
  border-top: 1px solid #ddd;
}
</style>
