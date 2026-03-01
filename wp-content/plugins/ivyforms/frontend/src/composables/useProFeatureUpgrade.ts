/**
 * Pro Feature Upgrade Dialog Composable
 *
 * Shows appropriate upgrade dialogs based on Pro plugin status:
 * 1. Pro NOT installed/active -> Notification Pop-up (simple)
 * 2. Pro installed but license not activated -> Activation Pop-up (detailed)
 */

import { useProUpgradeDialogStore } from '@/stores/proUpgradeDialogStore'
import { useLabels } from '@/composables/useLabels'
import { useSettingsStore } from '@/stores/useSettingsStore'
import { IVYFORMS_WEBSITE_PRICING, IVYFORMS_WEBSITE } from '@/constants/links'

type ProFeatureType = string

/**
 * Open pricing page in a new tab with proper security attributes
 * Centralized function to handle all pricing page navigation
 */
export function openPricingPage() {
  const link = document.createElement('a')
  link.href = IVYFORMS_WEBSITE_PRICING
  link.target = '_blank'
  link.rel = 'noopener noreferrer nofollow'
  link.click()
}

export function useProFeatureUpgrade() {
  const proUpgradeDialog = useProUpgradeDialogStore()
  const { getLabel } = useLabels()
  const settingsStore = useSettingsStore()

  /**
   * Get all available Pro field features from the plan
   * These are loaded from the backend plan configuration
   */
  const getProFieldFeatures = (): Record<string, ProFeatureType> => {
    const proFeatures = window.IvyForms?.pro?.features?.fields || {}
    return proFeatures as Record<string, ProFeatureType>
  }

  /**
   * Check if a field type is a Pro feature field
   * Dynamically checks against available Pro features from the plan
   */
  const isProFieldFeature = (fieldType: string): boolean => {
    const proFieldFeatures = getProFieldFeatures()
    return Object.values(proFieldFeatures).includes(fieldType)
  }

  /**
   * Get the feature slug for a given field type
   * Maps field type to its corresponding feature slug from the plan
   */
  const getFeatureSlugForFieldType = (fieldType: string): ProFeatureType | null => {
    const proFieldFeatures = getProFieldFeatures()
    for (const [, featureSlug] of Object.entries(proFieldFeatures)) {
      if (featureSlug === fieldType) {
        return featureSlug
      }
    }
    return null
  }

  /**
   * Get all available Pro field option features from the plan
   * These are loaded from the backend plan configuration
   */
  const getProFieldOptionFeatures = (): Record<string, ProFeatureType> => {
    const proFeatures = window.IvyForms?.pro?.features?.field_options || {}
    return proFeatures as Record<string, ProFeatureType>
  }

  /**
   * Check if a field option slug is a Pro feature
   * Dynamically checks against available Pro features from the plan
   */
  const isProFieldOption = (slug: string): boolean => {
    const proFieldOptions = getProFieldOptionFeatures()
    return Object.values(proFieldOptions).includes(slug)
  }

  /**
   * Get all available Pro integration features from the plan
   * These are loaded from the backend plan configuration
   */
  const getProIntegrationFeatures = (): Record<string, ProFeatureType> => {
    const proFeatures = window.IvyForms?.pro?.features?.integrations || {}
    return proFeatures as Record<string, ProFeatureType>
  }

  /**
   * Check if an integration slug is a Pro feature
   * Dynamically checks against available Pro features from the plan
   */
  const isProIntegration = (slug: string): boolean => {
    const proIntegrations = getProIntegrationFeatures()
    return Object.values(proIntegrations).includes(slug)
  }

  /**
   * Check if Pro plugin is installed and activated
   * Pro plugin must be both installed AND activated for this to return true
   */
  const isProInstalled = (): boolean => {
    // First check: Pro scripts must be loaded by WordPress
    // This is the most reliable indicator that Pro plugin is ACTIVE
    const proScriptsLoaded =
      (window as { ivyFormsProScripts?: Record<string, string> }).ivyFormsProScripts !== undefined

    if (!proScriptsLoaded) {
      return false
    }

    // Second check: Pro object must exist
    if (!window.IvyForms?.pro) {
      return false
    }

    // Third check: Pro must be loaded or have features
    const isLoaded = window.IvyForms.pro.loaded === true
    const hasFeatures =
      window.IvyForms.pro.features !== undefined &&
      window.IvyForms.pro.features !== null &&
      Object.keys(window.IvyForms.pro.features).length > 0

    return isLoaded || hasFeatures
  }

  /**
   * Check if Pro license is active
   * Checks if license settings exist and are valid in the settings store
   */
  const isProLicenseActive = (): boolean => {
    if (!isProInstalled()) return false

    // Check if allSettings has been loaded
    const settings = settingsStore.allSettings
    if (!settings) return false

    // Check if Pro license settings exist
    const proSettings = (settings as Record<string, unknown>).pro as
      | Record<string, unknown>
      | undefined
    if (!proSettings || !proSettings.license) return false

    const licenseInfo = proSettings.license as { status?: string; plan?: string }

    // License is active if status is 'valid' and plan is not 'lite'
    return licenseInfo.status === 'valid' && licenseInfo.plan !== 'lite'
  }

  /**
   * Show upgrade dialog based on current Pro status
   */
  const showUpgradeDialog = () => {
    const proInstalled = isProInstalled()
    const licenseActive = isProLicenseActive()

    // Scenario 1: Pro plugin NOT installed/active
    // Show simple "Notification Pop-up" from Figma
    if (!proInstalled) {
      proUpgradeDialog.showDialog({
        iconName: 'pro-lightning-alt',
        title: getLabel('pro_feature_available'),
        subtitle: '',
        buttons: {
          close: {
            type: 'tertiary',
            text: getLabel('learn_more'),
            //TODO: Update URL to specific feature page if available
            function: () => {
              const link = document.createElement('a')
              link.href = IVYFORMS_WEBSITE
              link.target = '_blank'
              link.rel = 'noopener noreferrer nofollow'
              link.click()
            },
          },
          confirm: {
            type: 'primary',
            text: getLabel('upgrade_to_pro'),
            function: () => {
              window.open(IVYFORMS_WEBSITE_PRICING, '_blank')
            },
          },
        },
      })
    }
    // Scenario 2: Pro installed but license NOT activated
    // Show detailed "Pop-up" with activation message from Figma
    else if (!licenseActive) {
      proUpgradeDialog.showDialog({
        iconName: 'pro-lightning',
        title: getLabel('pro_feature_requires_activation'),
        subtitle: getLabel('pro_feature_activation_subtitle'),
        buttons: {
          confirm: {
            type: 'primary',
            text: getLabel('activate_license'),
            function: () => {
              //TODO: Update URL to specific license activation page if available
              const currentUrl = new URL(window.location.href)
              currentUrl.searchParams.set('page', 'ivyforms-settings')
              currentUrl.hash = '/license'
              window.location.href = currentUrl.toString()
            },
          },
        },
      })
    }
    // Scenario 3: Pro active but feature not in current plan - Later for different plan tiers
    else {
      proUpgradeDialog.showDialog({
        iconName: 'pro-lightning-alt',
        title: getLabel('pro_feature_available'),
        subtitle: '',
        buttons: {
          close: {
            type: 'tertiary',
            text: getLabel('learn_more'),
            function: () => {
              openPricingPage()
            },
          },
          confirm: {
            type: 'primary',
            text: getLabel('upgrade_plan'),
            function: () => {
              openPricingPage()
            },
          },
        },
      })
    }
  }

  /**
   * Check if a feature is available and show upgrade dialog if not
   * @returns true if feature is available, false if upgrade dialog was shown
   */
  const checkFeatureAccess = (featureType: ProFeatureType): boolean => {
    // If Pro is not installed or license is not active, show upgrade dialog
    if (!isProInstalled() || !isProLicenseActive()) {
      showUpgradeDialog()
      return false
    }

    // Check if specific feature is available (for different plan tiers)
    const hasFeature = window.IvyForms?.pro?.features?.[featureType]
    if (!hasFeature) {
      showUpgradeDialog()
      return false
    }

    return true
  }

  return {
    isProInstalled,
    isProLicenseActive,
    showUpgradeDialog,
    checkFeatureAccess,
    getProFieldFeatures,
    isProFieldFeature,
    getFeatureSlugForFieldType,
    getProFieldOptionFeatures,
    isProFieldOption,
    getProIntegrationFeatures,
    isProIntegration,
  }
}
