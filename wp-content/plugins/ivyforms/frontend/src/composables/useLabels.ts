import { useLabelsStore } from '@/stores/labels'
import { getActivePinia } from 'pinia'

/**
 * Composable function to provide easy access to labels throughout the application
 * @returns Object with getLabel function and allLabels getter
 */
export function useLabels() {
  // Check if Pinia is available
  if (!getActivePinia()) {
    //console.warn('Pinia is not initialized. Labels will not be available.')
    return {
      getLabel: (key: string, fallback: string = '') => fallback || key,
      allLabels: {},
    }
  }

  const labelsStore = useLabelsStore()

  /**
   * Get a label by key with optional fallback value
   * @param key The label key to retrieve
   * @param fallback Optional fallback text if label doesn't exist
   * @returns The label string or fallback value
   */
  const getLabel = (key: string, fallback: string = ''): string => {
    return labelsStore.getLabel(key, fallback)
  }

  return {
    getLabel,
    allLabels: labelsStore.allLabels,
  }
}
