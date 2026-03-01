<template>
  <div id="app">
    <h1>Testing IvyEditor</h1>

    <IvyEditor v-model="editorContent" />
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import IvyEditor from '@/views/_components/html-editor/IvyEditor.vue'

const editorContent = ref('<p>Start typing...</p>')
</script>

<style scoped>
#app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  padding: 20px;
}

h1 {
  font-size: 2rem;
  margin-bottom: 20px;
}

h2 {
  font-size: 1.5rem;
  margin-top: 20px;
}
</style>
