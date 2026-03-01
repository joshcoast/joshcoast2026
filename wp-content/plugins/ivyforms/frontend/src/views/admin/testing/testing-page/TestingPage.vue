<template>
  <div>
    <PageHeader />

    <!-- Tabs Navigation -->
    <IvyTabs
      v-model="activeTab"
      type="filled"
      priority="primary"
      :tabs="[
        { name: 'tabs', label: 'Tabs' },
        { name: 'menus', label: 'Menus' },
        { name: 'tooltips', label: 'Tooltips' },
        { name: 'radio', label: 'Radio' },
        { name: 'button', label: 'Button' },
        { name: 'alert', label: 'Alert' },
        { name: 'toggle', label: 'Toggle' },
        { name: 'checkbox', label: 'Checkbox' },
        { name: 'link', label: 'Link' },
        { name: 'search', label: 'Search' },
        { name: 'popover', label: 'Popover' },
        { name: 'dialog', label: 'Dialog' },
        { name: 'fab', label: 'FAB Options' },
        { name: 'context-menu', label: 'Context Menu' },
        { name: 'message', label: 'Message' },
        { name: 'subdialog', label: 'SubDialog' },
        { name: 'inputs', label: 'Inputs' },
        { name: 'editor', label: 'HTMLEditor' },
      ]"
    />

    <div class="tab-content">
      <!-- Tabs Section -->
      <div v-if="activeTab === 'tabs'">
        <TestingTabs />
      </div>

      <!-- Tooltips Section -->
      <div v-if="activeTab === 'tooltips'">
        <TestingTooltips />
      </div>

      <!-- Menus Section -->
      <div v-if="activeTab === 'menus'">
        <TestingMenus />
      </div>

      <!-- Radio Section -->
      <div v-if="activeTab === 'radio'">
        <TestingRadio />
      </div>

      <!-- Button Section -->
      <div v-if="activeTab === 'button'">
        <TestingButton />
      </div>

      <!-- Alert Section -->
      <div v-if="activeTab === 'alert'">
        <TestingAlert />
      </div>

      <!-- Toggle Section -->
      <div v-if="activeTab === 'toggle'">
        <TestingToggle />
      </div>

      <!-- Checkbox Section -->
      <div v-if="activeTab === 'checkbox'">
        <TestingCheckbox />
      </div>

      <!-- Link Section -->
      <div v-if="activeTab === 'link'">
        <TestingLink />
      </div>

      <!-- Search Section -->
      <div v-if="activeTab === 'search'">
        <TestingSearch />
      </div>

      <!-- Popover Section -->
      <div v-if="activeTab === 'popover'">
        <TestingPopover />
      </div>

      <!-- Dialog Section -->
      <div v-if="activeTab === 'dialog'">
        <TestingDialog />
      </div>

      <!-- FAB Options Section -->
      <div v-if="activeTab === 'fab'">
        <TestingFAB />
      </div>

      <!--       Context Menu Section -->
      <div v-if="activeTab === 'context-menu'">
        <TestingContextMenu />
      </div>

      <!-- Message Section -->
      <div v-if="activeTab === 'message'">
        <TestingMessage />
      </div>

      <!-- SubDialog Section -->
      <div v-if="activeTab === 'subdialog'">
        <TestingSubdialog />
      </div>

      <!-- Inputs Section -->
      <div v-if="activeTab === 'inputs'">
        <TestingInputs />
      </div>

      <!-- Editor Section -->
      <div v-if="activeTab === 'editor'">
        <TestingHtmlEditor />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, provide } from 'vue'
import TestingToggle from '@/views/admin/testing/testing-page/TestingToggle.vue'
import TestingSearch from '@/views/admin/testing/testing-page/TestingSearch.vue'
import TestingMessage from '@/views/admin/testing/testing-page/TestingMessage.vue'
import TestingSubdialog from '@/views/admin/testing/testing-page/TestingSubdialog.vue'

const activeTab = ref('message')

const title = 'Testing'
provide('pageTitle', title)
</script>

<style lang="scss">
[class$='-controls'] {
  margin-bottom: 20px;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 6px;
  display: flex;
  flex-direction: column;
  gap: 20px;
  flex-grow: 1;
}

h3 {
  justify-content: center;
  display: flex;
}

hr {
  margin: 20px 0;
  border: none;
  border-top: 1px solid #ddd;
}

.tab-content {
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
