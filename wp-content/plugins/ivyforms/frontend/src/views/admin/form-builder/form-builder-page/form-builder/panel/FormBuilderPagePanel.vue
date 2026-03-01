<template>
  <PagePanel :width="520">
    <!-- Tabs Navigation - Fixed at top -->
    <div class="ivyforms-tabs-container ivyforms-pb-12">
      <IvyTabs
        v-model="activeTab"
        background
        type="filled"
        size="d"
        priority="primary"
        :tabs="tabs"
        :coming-soon="true"
      />
    </div>

    <!-- Scrollable Tab Content -->
    <div class="ivyforms-tab-content ivyforms-width-100">
      <!-- Add Field Tab -->
      <IvyScrollbar modifier="outside-vertical">
        <div v-if="activeTab === 'addField'">
          <AddFieldTabContent />
        </div>

        <!-- Options Tab -->
        <div v-if="activeTab === 'options'">
          <SubmitButtonSettings v-if="formBuilderStore.isSubmitButtonSelected" />
          <FieldOptionsTabContent v-else :field="formBuilderStore.selectedField" />
        </div>

        <!-- Style Tab -->
        <div v-if="activeTab === 'style'">
          <FieldStyleTabContent />
        </div>
      </IvyScrollbar>
    </div>
  </PagePanel>
</template>

<script setup lang="ts">
import { computed, watch } from 'vue'
import { useFormBuilder } from '@/stores/useFormBuilder.ts'
import { useLabels } from '@/composables/useLabels'
import { useWcagColors } from '@/composables/useWcagColors'
import SubmitButtonSettings from './tabs/SubmitButtonSettings.vue'

const { getLabel } = useLabels()
const { startWatching } = useWcagColors()
startWatching()

const formBuilderStore = useFormBuilder()

// Use computed to sync with store's activeTab
const activeTab = computed({
  get: () => formBuilderStore.activeTab,
  set: (value) => {
    formBuilderStore.activeTab = value
  },
})

const tabs = [
  { name: 'addField', label: getLabel('add_field') },
  { name: 'options', label: getLabel('options') },
  { name: 'style', label: getLabel('style'), disabled: true },
]

// Watch for field selection and change tab when a field is selected
watch(
  () => formBuilderStore.selectedField,
  (newField) => {
    if (newField) {
      formBuilderStore.activeTab = 'options'
    }
  },
)
</script>

<style scoped lang="scss">
.ivyforms-tabs-container {
  z-index: 10;
  background-color: var(--map-ground-level-1-foreground, #ffffff);
  /* Make IvyTabs take full width */
  :deep(.ivyforms-tabs) {
    width: 100%;
  }
}

.ivyforms-tab-content {
  height: calc(100% - 50px); /* Adjust based on IvyTabs height */
}
</style>
