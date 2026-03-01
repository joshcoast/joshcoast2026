<template>
  <div class="ivyforms-integrations-section-content">
    <div
      class="ivyforms-integrations-section-content__header ivyforms-flex ivyforms-flex-direction-column ivyforms-align-items-start ivyforms-gap-8"
    >
      <div class="ivyforms-integrations-section-content__title medium-18">
        {{ getLabel('integrations_section_title') }}
      </div>
      <div class="ivyforms-integrations-section-content__body regular-16">
        {{ getLabel('integrations_section_body') }}
      </div>
      <div
        class="ivyforms-integrations-section-content__option-bar ivyforms-flex ivyforms-justify-content-between ivyforms-align-items-start"
      >
        <IvySearch
          v-model="searchQuery"
          :placeholder="getLabel('search')"
          class="ivyforms-integrations-section-content__search-fixed-width"
        />
        <IvyTabs
          v-model="activeCategory"
          :tabs="categoryTabs"
          tab-style="fill"
          priority="tertiary"
          background
        />
      </div>
    </div>
    <div
      class="ivyforms-integrations-section-content__list ivyforms-flex ivyforms-flex-direction-row ivyforms-align-items-start ivyforms-gap-16 ivyforms-pt-24"
    >
      <!-- Skeleton loaders while loading -->
      <template v-if="isLoading">
        <el-skeleton
          v-for="n in skeletonCount"
          :key="n"
          :rows="3"
          animated
          class="ivyforms-integration-skeleton"
        />
      </template>

      <!-- Actual integration cards -->
      <IvyIntegrationCard
        v-for="integration in filteredIntegrations"
        v-else
        :key="integration.value"
        :title="getLabel(integration.name)"
        :description="getLabel(integration.description)"
        :image="integration.image"
        :type="getIntegrationType(integration)"
        :toggle-value="getIntegrationToggleValue(integration.value)"
        :toggle-disabled="isIntegrationToggleDisabled(integration.value)"
        :is-loading="isInstalling === integration.value"
        :button-text="getButtonText(integration.value)"
        :on-button-click="() => handleButtonClick(integration.value)"
        :on-toggle-change="(val) => handleToggleChange(integration.value, val)"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import IvyMessage from '@/views/_components/message/ivyMessage'
import { useLabels } from '@/composables/useLabels'
import { useIntegrations } from '@/composables/useIntegrations'
import { useProFeatures } from '@/composables/useProFeatures'
import { useProFeatureUpgrade } from '@/composables/useProFeatureUpgrade'
import { useSettingsStore } from '@/stores/useSettingsStore'
import { useApiClient } from '@/composables/useApiClient'
import IvySearch from '@/views/_components/search/IvySearch.vue'
import IvyTabs from '@/views/_components/tabs/IvyTabs.vue'

const { getLabel } = useLabels()
const settingsStore = useSettingsStore()
const pro = useProFeatures()
const {
  checkFeatureAccess,
  getProIntegrationFeatures,
  showUpgradeDialog,
  isProInstalled,
  isProLicenseActive,
} = useProFeatureUpgrade()
const { integrations, getIntegrationType, loadIntegrationStates, loadIntegrations } =
  useIntegrations()
const apiClient = useApiClient()
const searchQuery = ref('')
const activeCategory = ref('all')
const isLoading = ref(true)
const isInstalling = ref<string | null>(null) // Track which plugin is being installed
const skeletonCount = ref(2) // Default skeleton count, will be updated after loading

// Get all available Pro integrations (mailchimp, wpdatatables, etc.)
const proIntegrations = computed(() => getProIntegrationFeatures())

const categoryTabs = [
  { name: 'all', label: getLabel('all') },
  { name: 'lite', label: getLabel('lite') },
  { name: 'pro', label: getLabel('pro') },
  { name: 'soon', label: getLabel('soon') },
]

// Load all settings on mount
onMounted(async () => {
  isLoading.value = true
  await pro.fetch() // Fetch Pro features first to determine plan
  await settingsStore.loadAllSettings()
  await loadIntegrations() // Load integrations from backend registry
  await loadIntegrationStates()

  // Update skeleton count based on actual integrations loaded
  skeletonCount.value = integrations.value.length || 2

  isLoading.value = false
})

// Get toggle value based on integration state
const getIntegrationToggleValue = (integrationValue: string): boolean => {
  const integrationSettings = settingsStore.integrationsSettings?.[integrationValue]
  return integrationSettings?.enabled || false
}

// Check if toggle should be disabled
const isIntegrationToggleDisabled = (integrationValue: string): boolean => {
  const integration = integrations.value.find((i) => i.value === integrationValue)
  if (!integration) return false

  // If Pro is installed but license is NOT active, disable the toggle
  if (isProInstalled() && !isProLicenseActive()) {
    return true
  }

  return false
}

// Get button text for integrations
const getButtonText = (integrationValue: string): string | undefined => {
  const integration = integrations.value.find((i) => i.value === integrationValue)
  if (!integration) return undefined

  const currentType = getIntegrationType(integration)

  // For 'upgrade' type, show "Upgrade" (Pro not installed)
  if (currentType === 'upgrade') {
    return getLabel('upgrade')
  }

  // For 'proDisabled' type, check if license is active
  // If Pro installed but license NOT active, show "Activate License"
  if (currentType === 'proDisabled') {
    // If Pro is installed but license NOT active, show "Activate License"
    if (isProInstalled() && !isProLicenseActive()) {
      return getLabel('activate_license')
    }
    // Otherwise, let the default logic handle it (Learn More / Go to Settings)
  }

  // Special handling for wpDataTables
  if (integrationValue === 'wpdatatables') {
    const integrationSettings = settingsStore.integrationsSettings?.[integrationValue]
    const isEnabled = integrationSettings?.enabled || false
    const isConnected = integrationSettings?.connected || false

    // If currently installing, show "Installing..."
    if (isInstalling.value === integrationValue) {
      return getLabel('installing')
    }

    // If installed and toggle is ON, show "Create Tables"
    if (isConnected && isEnabled) {
      return getLabel('create_tables')
    }
  }
  return undefined // Use default button text
}

// Handle button click (Install/Learn More/Go to Settings/Upgrade/Create Tables)
const handleButtonClick = async (integrationValue: string) => {
  const integration = integrations.value.find((i) => i.value === integrationValue)
  if (!integration) return

  const currentType = getIntegrationType(integration)

  // Handle Upgrade button for Pro integrations when Pro is not installed or plan is insufficient
  if (currentType === 'upgrade') {
    // Show upgrade dialog based on Pro installation status
    showUpgradeDialog()
    return
  }

  // Handle Install button for disabled integrations
  if (currentType === 'disabled') {
    // For wpDataTables, install and activate the plugin
    if (integrationValue === 'wpdatatables') {
      try {
        isInstalling.value = integrationValue

        const response = await apiClient.request('/integrations/install-plugin', {
          method: 'POST',
          data: {
            plugin_slug: 'wpdatatables',
          },
        })

        if (response.data?.success) {
          // Show success message
          IvyMessage({
            message: getLabel('wpdatatables_installed_successfully'),
            type: 'success',
            duration: 3000,
          })

          // Reload settings to update the connected status
          await settingsStore.loadAllSettings()
          await loadIntegrationStates()
        } else {
          IvyMessage({
            message: response.data?.message || getLabel('installation_failed'),
            type: 'error',
            duration: 3000,
          })
        }
      } catch (error) {
        console.error('Error installing plugin:', error)
        IvyMessage({
          message: `${getLabel('installation_failed')} ${error}`,
          type: 'error',
          duration: 3000,
        })
      } finally {
        isInstalling.value = null
      }
      return
    }

    // For other integrations, just enable them
    await settingsStore.updateIntegrationEnabled(integrationValue, true)
    await loadIntegrationStates() // Reload to update UI
    return
  }

  // Handle active Lite integrations
  if (currentType === 'active') {
    // For wpDataTables with toggle ON, navigate to constructor
    if (integrationValue === 'wpdatatables') {
      const integrationSettings = settingsStore.integrationsSettings?.[integrationValue]
      const isEnabled = integrationSettings?.enabled || false

      if (isEnabled) {
        // Toggle ON: Open wpDataTables constructor in new tab
        window.open('admin.php?page=wpdatatables-constructor&source=ivyforms', '_blank')
        return
      } else {
        // Toggle OFF: Open documentation in new tab
        window.open('https://ivyforms.com/documentation/integration-with-wpdatatables/', '_blank')
        return
      }
    }

    // For other integrations, open Learn More URL if available
    if (integration.learnMoreUrl) {
      window.open(integration.learnMoreUrl, '_blank')
    }
    return
  }

  // Handle Go to Settings for Pro integrations (proActive/proDisabled)
  if (currentType === 'proActive' || currentType === 'proDisabled') {
    // If Pro is installed but license NOT active, show activation dialog
    if (isProInstalled() && !isProLicenseActive()) {
      showUpgradeDialog()
      return
    }

    // Check if this integration is a Pro feature that requires access verification
    const integrationFeatureSlug = `${integrationValue}_integration`
    if (Object.values(proIntegrations.value).includes(integrationFeatureSlug)) {
      if (!checkFeatureAccess(integrationFeatureSlug)) {
        // checkFeatureAccess will show the appropriate upgrade dialog
        return
      }
    }

    // Check if toggle is ON - only navigate if enabled
    const integrationSettings = settingsStore.integrationsSettings?.[integrationValue]
    if (integrationSettings?.enabled) {
      // Navigate to settings page with integration route
      // Use relative path which works better in WordPress admin
      const currentUrl = new URL(window.location.href)
      currentUrl.searchParams.set('page', 'ivyforms-settings')
      currentUrl.hash = `/integrations/${integrationValue}`
      window.location.href = currentUrl.toString()
    } else {
      // Toggle OFF: Open Learn More URL if available
      if (integration.learnMoreUrl) {
        window.open(integration.learnMoreUrl, '_blank')
      }
    }
    return
  }
}

// Handle toggle change
const handleToggleChange = async (integrationValue: string, enabled: boolean) => {
  // Check if this integration is a Pro feature and user is trying to enable it
  const integrationFeatureSlug = `${integrationValue}_integration`
  if (enabled && Object.values(proIntegrations.value).includes(integrationFeatureSlug)) {
    // Check if user has access to this Pro integration
    if (!checkFeatureAccess(integrationFeatureSlug)) {
      // checkFeatureAccess will show the appropriate upgrade dialog
      return
    }
  }

  await settingsStore.updateIntegrationEnabled(integrationValue, enabled)
  // Reload ALL settings from server to get fresh 'connected' status
  await settingsStore.loadAllSettings()
  await loadIntegrationStates() // Reload to update UI
}

const filteredIntegrations = computed(() => {
  let result = integrations.value
  if (activeCategory.value !== 'all') {
    result = result.filter((integration) => {
      // Map Pro plan levels to 'pro' category for filtering
      const isPro = ['essentials', 'growth', 'agency'].includes(integration.searchCategory)

      if (activeCategory.value === 'pro') {
        return isPro
      } else if (activeCategory.value === 'lite') {
        return integration.searchCategory === 'lite'
      } else if (activeCategory.value === 'soon') {
        return integration.type === 'soon'
      }

      return true
    })
  }
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase()
    result = result.filter(
      (integration) =>
        getLabel(integration.name).toLowerCase().includes(q) ||
        getLabel(integration.description).toLowerCase().includes(q),
    )
  }
  return result
})
</script>

<style scoped lang="scss">
.ivyforms-integrations-section-content {
  &__title {
    color: var(--map-base-text-0);
  }
  &__body {
    color: var(--map-base-text--1);
  }
  &__option-bar {
    align-self: stretch;
  }
  // Search
  &__search-fixed-width {
    width: 309px;
    min-width: 309px;
    max-width: 309px;
  }
}

.ivyforms-integration-skeleton {
  width: 394px;
  height: 172px;
  padding: 24px;
  border-radius: 8px;
  background: var(--map-ground-level-1-foreground);
  border: 1px solid var(--map-base-dusk-stroke--2);
}
</style>
