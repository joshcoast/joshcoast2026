<template>
  <div
    class="ivyforms-settings-general ivyforms-flex ivyforms-flex-1 ivyforms-flex-direction-column ivyforms-pr-20"
  >
    <div class="ivyforms-settings-general__option-bar ivyforms-pb-8 ivyforms-flex">
      <!-- Enable Toggle -->
      <div class="ivyforms-settings-general__option-bar__left ivyforms-flex">
        <span class="ivyforms-settings-general__title medium-18">{{ getLabel('general') }}</span>
      </div>

      <!-- Save Button -->
      <div class="ivyforms-settings-general__option-bar__right ivyforms-flex">
        <IvyButtonAction
          :class="['ivyforms-button__action--save']"
          priority="primary"
          :loading="isLoading"
          @click="saveSettings"
        >
          <template v-if="!isLoading">{{ getLabel('save') }}</template>
        </IvyButtonAction>
      </div>
    </div>
    <div
      class="ivyforms-settings-general__items ivyforms-pb-8 ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-16"
    >
      <IvyToggle
        v-model="settingsStore.wcagBackendCompliance"
        priority="secondary"
        size="d"
        text-position="right"
        :title="getLabel('wcag_compliance_settings')"
      ></IvyToggle>
      <IvyToggle
        v-model="settingsStore.deleteOnUninstall"
        priority="secondary"
        size="d"
        text-position="right"
        :title="getLabel('delete_on_uninstall')"
      ></IvyToggle>
    </div>
  </div>
</template>
<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useLabels } from '@/composables/useLabels.ts'
import { useSettingsStore } from '@/stores/useSettingsStore.ts'

const settingsStore = useSettingsStore()
const { getLabel } = useLabels()

// Load settings when component is mounted
onMounted(async () => {
  await settingsStore.loadAllSettings()
})

const isLoading = computed(() => settingsStore.isLoading)

const saveSettings = async () => {
  // Save general settings including WCAG backend compliance
  await settingsStore.saveGeneralSettings()
}
</script>
<style lang="scss" scoped>
@use 'sass:list' as *;
@use 'sass:meta' as *;
.ivyforms-settings-general {
  flex-direction: column;
  height: 100%;
  gap: 24px;
  &__title {
    color: var(--map-base-text-0);
  }
  &__option-bar {
    border-bottom: 1px solid var(--map-divider);
    background: var(--map-ground-level-1-foreground);

    &__left,
    &__right {
      gap: 8px;
      flex: 1 1 50%; // Make both sections flex-grow and flex-shrink with 50% base width
      min-width: 0; // Allow sections to shrink below their content size if needed
    }

    &__left {
      align-items: center;
      justify-content: flex-start;
    }

    &__right {
      align-items: center;
      justify-content: flex-end;

      .ivyforms-button__action--save {
        min-width: 67px;
        gap: 0;

        :deep(.ivyforms-button-action),
        :deep(.ivyforms-button-action.is-loading) {
          min-width: 67px;
          gap: 0;
          width: 100%;
        }
      }
    }
  }
}
</style>
