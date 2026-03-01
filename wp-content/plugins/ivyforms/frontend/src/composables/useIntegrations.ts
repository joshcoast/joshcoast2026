import { computed, ref } from 'vue'
import { getIntegrations, type Integration, type integrationType } from '@/constants/integrations'
import { useProFeatures } from '@/composables/useProFeatures'
import { useProFeatureUpgrade } from '@/composables/useProFeatureUpgrade'
import { useSettingsStore } from '@/stores/useSettingsStore'

/**
 * Integrations Composable
 *
 * Manages integration status and dynamically updates types based on:
 * - Pro installation status
 * - Current license plan
 * - Connection/activation state
 */

interface IntegrationState {
  [key: string]: {
    enabled: boolean
    connected: boolean
  }
}

export function useIntegrations() {
  const pro = useProFeatures()
  const settingsStore = useSettingsStore()
  const { isProInstalled: checkProInstalled, isProLicenseActive: checkProLicenseActive } =
    useProFeatureUpgrade()
  const integrationState = ref<IntegrationState>({})

  /**
   * Load integration states from settings store
   */
  const loadIntegrationStates = async () => {
    // Load from the settings store's integrationsSettings
    const storeSettings = settingsStore.integrationsSettings || {}

    // Transform to match our IntegrationState interface
    const transformed: IntegrationState = {}
    for (const [key, value] of Object.entries(storeSettings)) {
      if (value && typeof value === 'object') {
        transformed[key] = {
          enabled: (value as { enabled?: boolean }).enabled || false,
          connected: (value as { connected?: boolean }).connected || false,
        }
      }
    }

    integrationState.value = transformed
  }

  /**
   * Check if Pro is installed and meets minimum plan requirement
   * Also checks if license is active
   */
  const meetsRequirement = (requiredPlan?: string): boolean => {
    if (!requiredPlan) return true

    // First check: Is Pro installed and license active?
    if (!checkProInstalled() || !checkProLicenseActive()) {
      return false
    }

    const currentPlan = pro.plan.value
    const isProInstalledCheck = currentPlan !== 'lite' && currentPlan !== 'error'

    if (!isProInstalledCheck) return false

    const planHierarchy = ['essentials', 'growth', 'agency']
    const currentIndex = planHierarchy.indexOf(currentPlan)
    const requiredIndex = planHierarchy.indexOf(requiredPlan)

    return currentIndex >= requiredIndex && currentIndex !== -1
  }

  /**
   * Determine integration type dynamically
   */
  const getIntegrationType = (integration: Integration): integrationType => {
    const state = integrationState.value[integration.value] || { enabled: false, connected: false }

    // Check if this is a Pro integration (plan is not 'lite')
    const isProIntegration = integration.searchCategory !== 'lite'

    if (isProIntegration) {
      if (!meetsRequirement(integration.requiredPlan)) {
        // If Pro is NOT installed at all, show 'upgrade' type (with Pro banner)
        // If Pro IS installed but license not active, show 'proDisabled' type (no banner)
        const proInstalled = checkProInstalled()
        return proInstalled ? 'proDisabled' : 'upgrade'
      }

      // Pro is installed and plan is sufficient
      // If not enabled, return 'proDisabled' to show "Learn More" button
      if (!state.enabled) {
        return 'proDisabled'
      }

      // If enabled but not connected, return 'proDisabled' to show "Go to Settings"
      if (!state.connected) {
        return 'proDisabled'
      }

      return 'proActive'
    }

    // Lite integrations
    // PRIORITY 1: Check if plugin is installed (connected)
    // If installed, ALWAYS show "Learn More" regardless of toggle state
    if (state.connected) {
      return 'active'
    }

    // PRIORITY 2: Plugin is NOT installed
    return 'disabled'
  }

  /**
   * Get all integrations with dynamic types
   * Now async - loads from backend registry
   */
  const integrations = ref<Integration[]>([])
  const integrationsLoaded = ref(false)

  const loadIntegrations = async () => {
    if (integrationsLoaded.value) {
      return integrations.value
    }

    // Call getIntegrations() to get filtered list from backend at runtime
    const loaded = await getIntegrations()
    integrations.value = loaded.map((integration) => ({
      ...integration,
      type: getIntegrationType(integration),
    }))

    integrationsLoaded.value = true
    return integrations.value
  }

  /**
   * Update integration state
   */
  const updateIntegrationState = (name: string, enabled: boolean, connected = false) => {
    integrationState.value[name] = { enabled, connected }
  }

  return {
    integrations: computed(() => integrations.value),
    integrationsLoaded: computed(() => integrationsLoaded.value),
    integrationState,
    loadIntegrations,
    loadIntegrationStates,
    updateIntegrationState,
    getIntegrationType,
  }
}
