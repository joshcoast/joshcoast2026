import { watch } from 'vue'
import { useSettingsStore } from '@/stores/useSettingsStore'

/**
 * Composable for managing WCAG-compliant color switching
 * Dynamically updates CSS classes based on WCAG backend compliance setting
 */
export const useWcagColors = () => {
  const settingsStore = useSettingsStore()

  /**
   * Update CSS classes on document root based on WCAG compliance setting
   */
  const updateWcagClass = (useWcagColors: boolean) => {
    const root = document.documentElement

    if (useWcagColors) {
      // Add WCAG class to root element
      root.classList.add('wcag-compliant')
    } else {
      // Remove WCAG class from root element
      root.classList.remove('wcag-compliant')
    }
  }

  /**
   * Initialize WCAG color system
   */
  const initializeWcagColors = () => {
    // Apply initial class based on current setting
    updateWcagClass(settingsStore.wcagBackendCompliance)
  }

  /**
   * Watch for changes in WCAG compliance setting
   */
  const startWatching = () => {
    return watch(
      () => settingsStore.wcagBackendCompliance,
      (newValue) => {
        updateWcagClass(newValue)
      },
      { immediate: true },
    )
  }

  return {
    updateWcagClass,
    initializeWcagColors,
    startWatching,
  }
}
