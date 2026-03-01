<template>
  <div>
    <h3>IvyPopover Props Controls</h3>
    <div class="popover-controls">
      <!-- Popper Background -->
      <label>
        Popper Background:
        <select v-model="popperBackground">
          <option value="level-1-foreground">Level 1 Foreground</option>
          <option value="level-2-foreground">Level 2 Foreground</option>
          <option value="level-3-foreground">Level 3 Foreground</option>
        </select>
      </label>

      <!-- Scrollbar -->
      <label>
        <input v-model="isWithScrollbar" type="checkbox" disabled />
        Enable Scrollbar
      </label>

      <!-- Arrow -->
      <label>
        <select v-model="popperArrow">
          <option value="with-arrow">With Arrow</option>
          <option value="without-arrow">Without Arrow</option>
        </select>
      </label>
    </div>
    <hr />

    <!-- Popover Preview -->
    <div class="popover-preview">
      <h3>Popover Preview</h3>
      <IvyPopover
        :popper-background="popperBackground"
        :popper-arrow="popperArrow"
        :is-with-scrollbar="isWithScrollbar"
        :popper-class="popperClass"
        width="212"
      >
        <template #reference>
          <button>Click to trigger Popover</button>
        </template>
        <template #default>
          <p>This is a popover content preview.</p>
        </template>
      </IvyPopover>
      <br />
      <hr />
      <br />
      <IvyPopover
        :popper-background="popperBackground"
        :popper-arrow="popperArrow"
        :is-with-scrollbar="isWithScrollbar"
        :popper-class="popperClass"
      >
        <template #reference>
          <button>Click to trigger Popover</button>
        </template>
        <template #default>
          <p>Why do we use it? It is a long established fact that a reader will bes shing p.</p>
        </template>
      </IvyPopover>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import IvyPopover from '@/views/_components/popover/IvyPopover.vue'

const popperBackground = ref<'level-1-foreground' | 'level-2-foreground' | 'level-3-foreground'>(
  'level-2-foreground',
)
const popperArrow = ref<'with-arrow' | 'without-arrow'>('without-arrow')
const isWithScrollbar = ref(false)
const popperClass = ref('')
</script>

<style scoped>
.popover-controls {
  margin-bottom: 20px;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 6px;
}

.popover-controls label {
  display: block;
  margin-bottom: 10px;
}

.popover-preview {
  margin-top: 20px;
}

.popover-preview h3 {
  margin-bottom: 20px;
}

hr {
  margin: 20px 0;
  border: none;
  border-top: 1px solid #ddd;
}
</style>
