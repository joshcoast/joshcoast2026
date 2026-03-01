import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { useApiClient } from '@/composables/useApiClient'
import { useLabels } from '@/composables/useLabels'
import IvyMessage from '@/views/_components/message/ivyMessage.ts'
import type { RecaptchaSettings } from '@/types/recaptcha/recaptcha-interface'
import type { RecaptchaType } from '@/types/recaptcha/recaptcha-type'
import type { TurnstileSettings } from '@/types/turnstile/turnstile-interface'
import type { TurnstileTheme } from '@/types/turnstile/turnstile-type'
import type { HCaptchaSettings } from '@/types/hcaptcha/hcaptcha-interface'
import type { HCaptchaType } from '@/types/hcaptcha/hcaptcha-type'
import type { AllSettings } from '@/types/settings'

/**
 * Settings store for managing application settings
 * This store handles reCAPTCHA settings and other security/general settings
 */
export const useSettingsStore = defineStore('settingsStore', () => {
  // State using refs (composition API style like useFormBuilder)

  // All settings state
  const allSettings = ref<AllSettings | null>(null)
  const isLoading = ref(false)

  // reCAPTCHA specific state (for backward compatibility)
  const recaptchaType = ref<RecaptchaType>('v2')
  const recaptchaSiteKey = ref('')
  const recaptchaSecretKey = ref('')
  const recaptchaLanguage = ref('')

  // Turnstile specific state
  const turnstileSiteKey = ref('')
  const turnstileSecretKey = ref('')
  const turnstileTheme = ref<TurnstileTheme>('auto')

  // hCaptcha specific state
  const hcaptchaType = ref<HCaptchaType>('checkbox')
  const hcaptchaSiteKey = ref('')
  const hcaptchaSecretKey = ref('')

  // WCAG Compliance state
  const wcagBackendCompliance = ref(false)

  // Request deduplication - prevent multiple simultaneous calls
  let loadPromise: Promise<boolean> | null = null

  // Template favorites state
  const favoriteTemplates = ref<string[]>([])

  // Delete on uninstall state
  const deleteOnUninstall = ref(false)

  const { getLabel } = useLabels()
  const { request } = useApiClient()

  // Computed values
  const recaptchaSettings = computed(() => ({
    type: recaptchaType.value,
    siteKey: recaptchaSiteKey.value,
    secretKey: recaptchaSecretKey.value,
    language: recaptchaLanguage.value,
  }))

  const turnstileSettings = computed(() => ({
    siteKey: turnstileSiteKey.value,
    secretKey: turnstileSecretKey.value,
    theme: turnstileTheme.value,
  }))

  const hcaptchaSettings = computed(() => ({
    type: hcaptchaType.value,
    siteKey: hcaptchaSiteKey.value,
    secretKey: hcaptchaSecretKey.value,
  }))

  // Computed getters for all settings
  const securitySettings = computed(() => allSettings.value?.security || null)
  const generalSettings = computed(() => allSettings.value?.general || null)
  const integrationsSettings = computed(() => allSettings.value?.integrations || null)

  // Specific settings getters
  const currentRecaptchaSettings = computed(() => allSettings.value?.security?.recaptcha || null)
  const currentTurnstileSettings = computed(() => allSettings.value?.security?.turnstile || null)
  const currentHCaptchaSettings = computed(() => allSettings.value?.security?.hcaptcha || null)

  // Integration settings getters
  const wpDataTablesIntegration = computed(
    () => allSettings.value?.integrations?.wpdatatables || null,
  )

  const isRecaptchaConfigured = computed(() => {
    // reCAPTCHA is configured only when both keys are present and valid
    const siteKeyPresent = recaptchaSiteKey.value && recaptchaSiteKey.value.trim() !== ''
    const secretKeyPresent = recaptchaSecretKey.value && recaptchaSecretKey.value.trim() !== ''

    return (
      siteKeyPresent &&
      secretKeyPresent &&
      validateCaptchaKeyFormat(recaptchaSiteKey.value) &&
      validateCaptchaKeyFormat(recaptchaSecretKey.value)
    )
  })

  const isTurnstileConfigured = computed(() => {
    // Turnstile is configured only when both keys are present and valid
    const siteKeyPresent = turnstileSiteKey.value && turnstileSiteKey.value.trim() !== ''
    const secretKeyPresent = turnstileSecretKey.value && turnstileSecretKey.value.trim() !== ''

    return (
      siteKeyPresent &&
      secretKeyPresent &&
      validateCaptchaKeyFormat(turnstileSiteKey.value) && // Turnstile uses similar format
      validateCaptchaKeyFormat(turnstileSecretKey.value)
    )
  })

  const isHCaptchaConfigured = computed(() => {
    // hCaptcha is configured only when both keys are present and valid
    const siteKeyPresent = hcaptchaSiteKey.value && hcaptchaSiteKey.value.trim() !== ''
    const secretKeyPresent = hcaptchaSecretKey.value && hcaptchaSecretKey.value.trim() !== ''

    return (
      siteKeyPresent &&
      secretKeyPresent &&
      validateCaptchaKeyFormat(hcaptchaSiteKey.value) && // hCaptcha uses similar format
      validateCaptchaKeyFormat(hcaptchaSecretKey.value)
    )
  })

  // Actions - following the same pattern as useFormBuilder
  const setRecaptchaType = (type: RecaptchaType) => {
    recaptchaType.value = type
  }

  const setRecaptchaSiteKey = (siteKey: string) => {
    recaptchaSiteKey.value = siteKey
  }

  const setRecaptchaSecretKey = (secretKey: string) => {
    recaptchaSecretKey.value = secretKey
  }

  const setRecaptchaLanguage = (language: string) => {
    recaptchaLanguage.value = language
  }

  const setTurnstileSiteKey = (siteKey: string) => {
    turnstileSiteKey.value = siteKey
  }

  const setTurnstileSecretKey = (secretKey: string) => {
    turnstileSecretKey.value = secretKey
  }

  const setTurnstileTheme = (theme: TurnstileTheme) => {
    turnstileTheme.value = theme
  }

  const setHCaptchaType = (type: HCaptchaType) => {
    hcaptchaType.value = type
  }

  const setHCaptchaSiteKey = (siteKey: string) => {
    hcaptchaSiteKey.value = siteKey
  }

  const setHCaptchaSecretKey = (secretKey: string) => {
    hcaptchaSecretKey.value = secretKey
  }

  const setWcagBackendCompliance = (value: boolean) => {
    wcagBackendCompliance.value = value
  }

  const setDeleteOnUninstall = (value: boolean) => {
    deleteOnUninstall.value = value
  }

  const setRecaptchaSettings = (settings: RecaptchaSettings) => {
    recaptchaType.value = settings.type
    recaptchaSiteKey.value = settings.siteKey
    recaptchaSecretKey.value = settings.secretKey
    // Set defaults for new fields if not provided
    recaptchaLanguage.value = settings.language || ''
  }

  const setTurnstileSettings = (settings: TurnstileSettings) => {
    turnstileSiteKey.value = settings.siteKey
    turnstileSecretKey.value = settings.secretKey
    // Set defaults for new fields if not provided
    turnstileTheme.value = settings.theme || 'auto'
  }

  const setHCaptchaSettings = (settings: HCaptchaSettings) => {
    hcaptchaType.value = settings.type
    hcaptchaSiteKey.value = settings.siteKey
    hcaptchaSecretKey.value = settings.secretKey
  }

  // Load all settings for admin page - single API call for better performance
  const loadAllSettings = async (): Promise<boolean> => {
    // If there's already a request in progress, return it
    if (loadPromise) {
      return loadPromise
    }

    loadPromise = (async () => {
      try {
        isLoading.value = true
        const { data, error, status } = await request('settings/all', {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
          },
        })

        if (status === 200 && !error && data?.data?.data) {
          allSettings.value = data.data.data

          // Update individual reCAPTCHA refs for backward compatibility
          const recaptcha = data.data.data.security?.recaptcha
          if (recaptcha) {
            recaptchaType.value = recaptcha.type || 'v2'
            recaptchaSiteKey.value = recaptcha.siteKey || ''
            recaptchaSecretKey.value = recaptcha.secretKey || ''
            recaptchaLanguage.value = recaptcha.language || ''
          }

          // Update individual Turnstile refs
          const turnstile = data.data.data.security?.turnstile
          if (turnstile) {
            turnstileSiteKey.value = turnstile.siteKey || ''
            turnstileSecretKey.value = turnstile.secretKey || ''
            turnstileTheme.value = turnstile.theme || 'auto'
          }

          // Update individual hCaptcha refs
          const hcaptcha = data.data.data.security?.hcaptcha
          if (hcaptcha) {
            hcaptchaType.value = hcaptcha.type || 'checkbox'
            hcaptchaSiteKey.value = hcaptcha.siteKey || ''
            hcaptchaSecretKey.value = hcaptcha.secretKey || ''
          }

          // Update WCAG compliance setting
          const general = data.data.data.general
          if (general && general.wcagBackend !== undefined) {
            wcagBackendCompliance.value = general.wcagBackend
          }

          // Update delete on uninstall setting
          if (general && general.delete_on_uninstall !== undefined) {
            deleteOnUninstall.value = general.delete_on_uninstall
          }

          // Update favorite templates
          if (general && general.favoriteTemplates) {
            favoriteTemplates.value = general.favoriteTemplates
          }

          return true
        } else {
          IvyMessage({
            message: getLabel('failed_to_load_settings'),
            type: 'error',
          })
          return false
        }
      } catch (error) {
        IvyMessage({
          message: getLabel('error_loading_settings'),
          type: 'error',
        })
        console.error(getLabel('error_loading_settings'), error)
        return false
      } finally {
        isLoading.value = false
        loadPromise = null
      }
    })()

    return loadPromise
  }

  // Generic settings loader - can be used for any category/option combination
  const loadSetting = async (category: string, option: string) => {
    try {
      isLoading.value = true
      const { data, error, status } = await request(`setting/${category}/${option}`, {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
        },
      })

      if (status === 200 && !error && data?.data?.value) {
        return data.data.value
      } else {
        IvyMessage({
          message: getLabel('failed_to_load_settings'),
          type: 'error',
        })
        return null
      }
    } catch (err) {
      console.error(getLabel('failed_to_load_settings'), err)
      IvyMessage({
        message: getLabel('failed_to_load_settings'),
        type: 'error',
      })
      return null
    } finally {
      isLoading.value = false
    }
  }

  // Generic settings saver - can be used for any category/option combination
  const saveSetting = async (
    category: string,
    option: string,
    value: unknown,
    showMessage: boolean = true,
  ): Promise<boolean> => {
    try {
      isLoading.value = true
      const { data, error, status } = await request('setting/update', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        data: {
          settingsCategory: category,
          settingsOption: option,
          settingsValue: value,
        },
      })

      if (status === 200 && !error) {
        if (showMessage) {
          IvyMessage({
            message: getLabel('settings_saved'),
            type: 'success',
          })
        }
        return true
      } else {
        if (showMessage) {
          IvyMessage({
            message: getLabel('failed_to_save_settings'),
            type: 'error',
          })
        }
        console.error(getLabel('failed_to_save_settings'), data?.message)
        return false
      }
    } catch (error) {
      if (showMessage) {
        IvyMessage({
          message: getLabel('error_saving_settings'),
          type: 'error',
        })
      }
      console.error(getLabel('error_saving_settings'), error)
      return false
    } finally {
      isLoading.value = false
    }
  }

  // Update specific setting in allSettings state (for real-time updates)
  const updateSettingInState = (category: keyof AllSettings, option: string, value: unknown) => {
    if (allSettings.value) {
      // Create a deep copy to trigger reactivity
      const updatedSettings = { ...allSettings.value }
      if (updatedSettings[category]) {
        ;(updatedSettings[category] as Record<string, unknown>)[option] = value
        allSettings.value = updatedSettings
      }
    }
  }

  // Load reCAPTCHA settings - following the async pattern from useFormBuilder
  const loadRecaptchaSettings = async () => {
    const settings = await loadSetting('security', 'recaptcha')

    if (settings) {
      recaptchaType.value = settings.type || 'v2'
      recaptchaSiteKey.value = settings.siteKey || ''
      recaptchaSecretKey.value = settings.secretKey || ''
      recaptchaLanguage.value = settings.language || ''
    }
  }

  // Save reCAPTCHA settings - following the async pattern from useFormBuilder
  const saveRecaptchaSettings = async (): Promise<boolean> => {
    // Basic frontend validation - only validate non-empty keys
    const siteKeyValue = recaptchaSiteKey.value.trim()
    const secretKeyValue = recaptchaSecretKey.value.trim()

    // Validate both keys at once if they are provided
    if (
      (siteKeyValue && !validateCaptchaKeyFormat(siteKeyValue)) ||
      (secretKeyValue && !validateCaptchaKeyFormat(secretKeyValue))
    ) {
      const errorMessage =
        siteKeyValue && !validateCaptchaKeyFormat(siteKeyValue)
          ? getLabel('site_key_invalid')
          : getLabel('secret_key_invalid')

      IvyMessage({
        message: errorMessage,
        type: 'error',
      })
      return false
    }

    const settingsValue = {
      type: recaptchaType.value,
      siteKey: recaptchaSiteKey.value,
      secretKey: recaptchaSecretKey.value,
      language: recaptchaLanguage.value,
    }

    const success = await saveSetting('security', 'recaptcha', settingsValue)

    if (success) {
      // Update the allSettings state with the new values
      updateSettingInState('security', 'recaptcha', settingsValue)
    }

    return success
  }

  // Load Turnstile settings - following the async pattern from useFormBuilder
  const loadTurnstileSettings = async () => {
    const settings = await loadSetting('security', 'turnstile')

    if (settings) {
      turnstileSiteKey.value = settings.siteKey || ''
      turnstileSecretKey.value = settings.secretKey || ''
      turnstileTheme.value = settings.theme || 'auto'
    }
  }

  // Load hCaptcha settings - following the async pattern from useFormBuilder
  const loadHCaptchaSettings = async () => {
    const settings = await loadSetting('security', 'hcaptcha')

    if (settings) {
      hcaptchaType.value = settings.type || 'checkbox'
      hcaptchaSiteKey.value = settings.siteKey || ''
      hcaptchaSecretKey.value = settings.secretKey || ''
    }
  }

  // Save Turnstile settings - following the async pattern from useFormBuilder
  const saveTurnstileSettings = async (): Promise<boolean> => {
    // Basic frontend validation - only validate non-empty keys
    const siteKeyValue = turnstileSiteKey.value.trim()
    const secretKeyValue = turnstileSecretKey.value.trim()

    // Validate both keys at once if they are provided
    if (
      (siteKeyValue && !validateCaptchaKeyFormat(siteKeyValue)) ||
      (secretKeyValue && !validateCaptchaKeyFormat(secretKeyValue))
    ) {
      const errorMessage =
        siteKeyValue && !validateCaptchaKeyFormat(siteKeyValue)
          ? getLabel('site_key_invalid')
          : getLabel('secret_key_invalid')

      IvyMessage({
        message: errorMessage,
        type: 'error',
      })
      return false
    }

    const settingsValue = {
      siteKey: turnstileSiteKey.value,
      secretKey: turnstileSecretKey.value,
      theme: turnstileTheme.value,
    }

    const success = await saveSetting('security', 'turnstile', settingsValue)

    if (success) {
      // Update the allSettings state with the new values
      updateSettingInState('security', 'turnstile', settingsValue)
    }

    return success
  }

  // Save hCaptcha settings - following the async pattern from useFormBuilder
  const saveHCaptchaSettings = async (): Promise<boolean> => {
    // Basic frontend validation - only validate non-empty keys
    const siteKeyValue = hcaptchaSiteKey.value.trim()
    const secretKeyValue = hcaptchaSecretKey.value.trim()

    // Validate both keys at once if they are provided
    if (
      (siteKeyValue && !validateCaptchaKeyFormat(siteKeyValue)) ||
      (secretKeyValue && !validateCaptchaKeyFormat(secretKeyValue))
    ) {
      const errorMessage =
        siteKeyValue && !validateCaptchaKeyFormat(siteKeyValue)
          ? getLabel('site_key_invalid')
          : getLabel('secret_key_invalid')

      IvyMessage({
        message: errorMessage,
        type: 'error',
      })
      return false
    }

    const settingsValue = {
      type: hcaptchaType.value,
      siteKey: hcaptchaSiteKey.value,
      secretKey: hcaptchaSecretKey.value,
    }

    const success = await saveSetting('security', 'hcaptcha', settingsValue)

    if (success) {
      // Update the allSettings state with the new values
      updateSettingInState('security', 'hcaptcha', settingsValue)
    }

    return success
  }

  // Save general settings
  const saveGeneralSettings = async (): Promise<boolean> => {
    const wcagSuccess = await saveSetting(
      'general',
      'wcagBackend',
      wcagBackendCompliance.value,
      false,
    )
    const deleteSuccess = await saveSetting(
      'general',
      'delete_on_uninstall',
      deleteOnUninstall.value,
    )

    const success = wcagSuccess && deleteSuccess

    if (success) {
      // Update the allSettings state with the new values
      updateSettingInState('general', 'wcagBackend', wcagBackendCompliance.value)
      updateSettingInState('general', 'delete_on_uninstall', deleteOnUninstall.value)
    }

    return success
  }

  // Basic captcha key format validation
  const validateCaptchaKeyFormat = (key: string): boolean => {
    // Allow empty keys (for clearing/resetting settings)
    if (!key || key.trim() === '') {
      return true
    }

    const trimmedKey = key.trim()

    // captcha keys are typically 40 characters long, but can vary
    if (trimmedKey.length < 20 || trimmedKey.length > 100) {
      return false
    }

    // Basic format validation - should contain alphanumeric characters and some special chars
    return /^[A-Za-z0-9_-]+$/.test(trimmedKey)
  }

  // Integration settings methods
  /**
   * Get integration settings dynamically by name
   */
  const getIntegrationSettings = (integrationName: string) => {
    return allSettings.value?.integrations?.[integrationName] || null
  }

  /**
   * Check if integration is enabled
   */
  const isIntegrationEnabled = (integrationName: string): boolean => {
    return allSettings.value?.integrations?.[integrationName]?.enabled || false
  }

  /**
   * Update integration enabled status (simple toggle)
   */
  const updateIntegrationEnabled = async (
    integrationName: string,
    enabled: boolean,
  ): Promise<boolean> => {
    const integrationValue = { enabled }
    const success = await saveSetting('integrations', integrationName, integrationValue)

    if (success) {
      updateSettingInState('integrations', integrationName, integrationValue)
    }

    return success
  }

  /**
   * Update integration settings (for complex integrations with multiple settings)
   */
  const updateIntegrationSettings = async (
    integrationName: string,
    settings: Record<string, unknown>,
  ): Promise<boolean> => {
    const success = await saveSetting('integrations', integrationName, settings)

    if (success) {
      updateSettingInState('integrations', integrationName, settings)
    }

    return success
  }

  /**
   * Toggle a template as favorite
   */
  const toggleFavoriteTemplate = async (templateId: string): Promise<boolean> => {
    const index = favoriteTemplates.value.indexOf(templateId)
    if (index > -1) {
      // Remove from favorites
      favoriteTemplates.value = favoriteTemplates.value.filter((id) => id !== templateId)
    } else {
      // Add to favorites
      favoriteTemplates.value = [...favoriteTemplates.value, templateId]
    }
    // Save to backend
    const success = await saveSetting('general', 'favoriteTemplates', favoriteTemplates.value)
    if (success) {
      updateSettingInState('general', 'favoriteTemplates', favoriteTemplates.value)
    }
    return success
  }

  /**
   * Check if a template is favorited
   */
  const isTemplateFavorited = (templateId: string): boolean => {
    return favoriteTemplates.value.includes(templateId)
  }

  /**
   * Load favorite templates
   */
  const loadFavoriteTemplates = async () => {
    // If allSettings is already loaded, use it
    if (allSettings.value?.general?.favoriteTemplates) {
      favoriteTemplates.value = allSettings.value.general.favoriteTemplates as string[]
      return
    }

    // Otherwise, load from individual endpoint
    const favorites = await loadSetting('general', 'favoriteTemplates')
    if (favorites && Array.isArray(favorites)) {
      favoriteTemplates.value = favorites as string[]
    } else if (favorites === null) {
      // If it returns null, try loading all settings
      await loadAllSettings()
      if (allSettings.value?.general?.favoriteTemplates) {
        favoriteTemplates.value = allSettings.value.general.favoriteTemplates as string[]
      }
    }
  }

  return {
    // State
    recaptchaType,
    recaptchaSiteKey,
    recaptchaSecretKey,
    recaptchaLanguage,
    turnstileSiteKey,
    turnstileSecretKey,
    turnstileTheme,
    hcaptchaType,
    hcaptchaSiteKey,
    hcaptchaSecretKey,
    wcagBackendCompliance,
    favoriteTemplates,
    deleteOnUninstall,
    isLoading,

    // All Settings State
    allSettings,

    // Computed
    recaptchaSettings,
    turnstileSettings,
    hcaptchaSettings,
    isRecaptchaConfigured,
    isTurnstileConfigured,
    isHCaptchaConfigured,

    // All Settings Computed
    securitySettings,
    generalSettings,
    integrationsSettings,
    currentRecaptchaSettings,
    currentTurnstileSettings,
    currentHCaptchaSettings,
    wpDataTablesIntegration,

    // Actions
    setRecaptchaType,
    setRecaptchaSiteKey,
    setRecaptchaSecretKey,
    setRecaptchaLanguage,
    setTurnstileSiteKey,
    setTurnstileSecretKey,
    setTurnstileTheme,
    setHCaptchaType,
    setHCaptchaSiteKey,
    setHCaptchaSecretKey,
    setWcagBackendCompliance,
    setDeleteOnUninstall,
    setRecaptchaSettings,
    setTurnstileSettings,
    setHCaptchaSettings,
    loadRecaptchaSettings,
    saveRecaptchaSettings,
    loadTurnstileSettings,
    saveTurnstileSettings,
    loadHCaptchaSettings,
    saveHCaptchaSettings,
    saveGeneralSettings,

    // All Settings API
    loadAllSettings,
    updateSettingInState,

    // Generic Settings API
    loadSetting,
    saveSetting,

    // Integration Settings API
    updateIntegrationEnabled,
    getIntegrationSettings,
    isIntegrationEnabled,
    updateIntegrationSettings,

    // Template Favorites API
    toggleFavoriteTemplate,
    isTemplateFavorited,
    loadFavoriteTemplates,

    // Validation utilities
    validateCaptchaKeyFormat,
  }
})
