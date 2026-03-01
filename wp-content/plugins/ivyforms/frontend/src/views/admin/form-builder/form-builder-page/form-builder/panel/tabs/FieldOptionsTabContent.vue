<template>
  <div v-if="field" class="ivyforms-secondary-tab-menu">
    <div class="ivyforms-secondary-tab-menu__tabs">
      <IvyTabs
        :model-value="activeTab"
        background
        type="tonal"
        priority="tertiary"
        :tabs="tabs"
        size="d"
        @update:model-value="handleTabChange"
      />
    </div>

    <div class="ivyforms-secondary-tab-menu__content">
      <!-- Loading State -->
      <div v-if="isLoading" class="ivyforms-py-16 ivyforms-pr-8">
        <IvyOptionsSkeleton />
      </div>

      <!-- Content - hidden during loading -->
      <div v-show="!isLoading">
        <!-- General Options Tab -->
        <div v-if="activeTab === 'general'">
          <GeneralFieldOptions :field="field" />
        </div>

        <!-- Advanced Options Tab -->
        <div v-if="activeTab === 'advanced'">
          <AdvancedFieldOptions />
        </div>

        <!-- Smart Logic Options Tab -->
        <div v-if="activeTab === 'smartLogic'">
          <!-- Show Pro component if available -->
          <component
            :is="SmartLogicOptions"
            v-if="isProAvailable && SmartLogicOptions"
            :field="field"
            :update-field="updateField"
            :get-label="getLabel"
          />
          <!-- Show upgrade message if Pro not available -->
          <SmartLogicFieldOptions v-else :field="field" />
        </div>
      </div>
    </div>
  </div>
  <div v-else>
    <PageEmptyState
      :title="getEmptyStateTitle()"
      :subtitle="getEmptyStateDescription()"
      image="fields"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, watch, computed, onMounted, type Component } from 'vue'
import { storeToRefs } from 'pinia'
import { useLabels } from '@/composables/useLabels'
import { useProFeatures } from '@/composables/useProFeatures'
import { useProFeatureUpgrade } from '@/composables/useProFeatureUpgrade'
import { useFormBuilder } from '@/stores/useFormBuilder'
import { useProUpgradeDialogStore } from '@/stores/proUpgradeDialogStore'
import type { Field } from '@/types/field'
import IvyOptionsSkeleton from '@/views/_components/skeleton/IvyOptionsSkeleton.vue'
import PageEmptyState from '@/views/admin/parts/PageEmptyState.vue'
import SmartLogicFieldOptions from './field-options/SmartLogicOptions.vue'

// Get SmartLogicOptions from Pro override if available
// Make it reactive by checking in a computed that re-evaluates
const SmartLogicOptions = computed(() => {
  const proComponent = window.IvyForms?.components?.['SmartLogicOptions']
  return proComponent as Component | undefined
})

const { getLabel } = useLabels()
const { updateFieldOptions, updateSelectedField } = useFormBuilder()
const { checkFeatureAccess, isProInstalled, isProLicenseActive } = useProFeatureUpgrade()
const proUpgradeDialogStore = useProUpgradeDialogStore()
const { dialogVisible } = storeToRefs(proUpgradeDialogStore)

// Update field values in the store
const updateField = (index: string, value: unknown) => {
  updateSelectedField(index, value)
  updateFieldOptions(index, value)
}

// Pro features detection - only initialize if pro plugin bridge is available
const proInstance = window.IvyForms?.pro ? useProFeatures() : null

const props = withDefaults(
  defineProps<{
    field: Field | null
  }>(),
  {
    field: null,
  },
)

const getEmptyStateTitle = () => {
  return getLabel('no_field_selected')
}

const getEmptyStateDescription = () => {
  return getLabel('please_select_field')
}

const activeTab = ref('general')
const isLoading = ref(false)
const previousTab = ref('general') // Store previous tab to return after dialog closes

const isProAvailable = computed(() => {
  if (!proInstance) return false
  if (!proInstance.loaded.value) return false
  return proInstance.hasFeature('smart_logic')
})

const tabs = computed(() => [
  { name: 'general', label: getLabel('general') },
  { name: 'advanced', label: getLabel('advanced') },
  {
    name: 'smartLogic',
    label: getLabel('smart_logic'),
    // Show Pro badge only if Pro is NOT installed (not when license is just inactive)
    pro: !isProInstalled(),
  },
])

// Handle tab change - show pro dialog if Pro tab is clicked but not available
const handleTabChange = (tabName: string | number) => {
  const tabNameStr = String(tabName)

  // Map tab names to Pro feature slugs for Pro-only tabs
  const tabToFeatureMap: Record<string, string> = {
    smartLogic: 'smart_logic',
  }

  // Check if this is a Pro tab
  const featureSlug = tabToFeatureMap[tabNameStr]
  if (featureSlug) {
    // Save current tab before attempting to access Pro feature
    previousTab.value = activeTab.value

    // If Pro is installed but license NOT active, show activation dialog
    if (isProInstalled() && !isProLicenseActive()) {
      checkFeatureAccess(featureSlug)
      return
    }

    // If Pro feature is not available (Pro not installed or feature not in plan), show upgrade dialog
    if (!isProAvailable.value) {
      checkFeatureAccess(featureSlug)
      return
    }
  }

  activeTab.value = tabNameStr
}

// Load Pro features on component mount
onMounted(async () => {
  if (proInstance && !proInstance.loaded.value) {
    await proInstance.fetch()
  }
})

// Watch for field changes and show loading state
watch(
  () => props.field,
  (newField, oldField) => {
    if (newField && oldField && newField.fieldIndex !== oldField.fieldIndex) {
      activeTab.value = 'general'
      isLoading.value = true
      setTimeout(() => {
        isLoading.value = false
      }, 200)
    }
  },
)

// Watch for dialog close and revert tab to previous if Pro dialog was closed without enabling feature
watch(
  () => dialogVisible.value,
  (newValue) => {
    // When dialog closes (becomes false), if we're still on a Pro tab that's not available, revert to previous
    if (!newValue && activeTab.value === 'smartLogic' && !isProAvailable.value) {
      activeTab.value = previousTab.value
    }
  },
)
</script>

<style scoped lang="scss">
.ivyforms-secondary-tab-menu {
  &__tabs {
    position: sticky;
    top: 0;
    z-index: 3;
    background: var(--map-ground-level-1-foreground);
  }
}
</style>
