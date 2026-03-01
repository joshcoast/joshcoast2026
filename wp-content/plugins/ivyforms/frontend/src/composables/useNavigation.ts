import { useRouter, type RouteParamsRaw, type LocationQueryRaw } from 'vue-router'
import {
  IVYFORMS_ALLFORMS_PAGE,
  IVYFORMS_ENTRIES_PAGE,
  IVYFORMS_SETTINGS_PAGE,
  IVYFORMS_INTEGRATIONS_PAGE,
  IVYFORMS_DASHBOARD_PAGE,
  IVYFORMS_FORM_BUILDER_PAGE,
} from '@/constants/pages'

/**
 * Composable for cross-page navigation
 * Handles navigation between different WordPress admin pages and within the SPA
 */
export function useNavigation() {
  const router = useRouter()
  const currentPage = new URLSearchParams(window.location.search).get('page')

  /**
   * Navigate to a different WordPress admin page
   * @param page - The WordPress page identifier
   * @param hash - Optional hash for SPA routing within the page
   */
  const navigateToAdminPage = (page: string, hash?: string): void => {
    // If navigating to the same WordPress page, use router for SPA navigation
    if (page === currentPage && hash) {
      router.push({ path: hash.startsWith('/') ? hash : `/${hash}` })
      return
    }

    // Otherwise, navigate to different WordPress page
    const hashFragment = hash ? `#${hash}` : ''
    window.location.href = `admin.php?page=${page}${hashFragment}`
  }

  /**
   * Navigate within the current SPA using Vue Router
   * @param path - The route path or route object
   */
  const navigateWithinSpa = (path: string | object) => {
    return router.push(path)
  }

  /**
   * Navigate to All Forms page
   */
  const navigateToAllForms = () => {
    navigateToAdminPage(IVYFORMS_ALLFORMS_PAGE)
  }

  /**
   * Navigate to Entries page
   * @param formId - Optional form ID to filter entries
   */
  const navigateToEntries = (formId?: number) => {
    const hash = formId ? `/?formId=${formId}` : '/'
    navigateToAdminPage(IVYFORMS_ENTRIES_PAGE, hash)
  }

  /**
   * Navigate to Settings page
   * @param section - Optional settings section (general, security, etc.)
   */
  const navigateToSettings = (section?: string) => {
    const hash = section ? `/${section}` : '/'
    navigateToAdminPage(IVYFORMS_SETTINGS_PAGE, hash)
  }

  /**
   * Navigate to Integrations page
   */
  const navigateToIntegrations = () => {
    navigateToAdminPage(IVYFORMS_INTEGRATIONS_PAGE)
  }

  /**
   * Navigate to Dashboard page
   */
  const navigateToDashboard = () => {
    navigateToAdminPage(IVYFORMS_DASHBOARD_PAGE)
  }

  /**
   * Navigate to Form Builder page
   * @param formId - Optional form ID to edit
   * @param section - Optional section (build, settings, results)
   */
  const navigateToFormBuilder = (formId?: number, section?: string) => {
    if (!formId) {
      navigateToAdminPage(IVYFORMS_FORM_BUILDER_PAGE)
      return
    }

    let hash = `/manage/${formId}`
    if (section) {
      hash += `/${section}`
    }
    navigateToAdminPage(IVYFORMS_FORM_BUILDER_PAGE, hash)
  }

  /**
   * Navigate back in history
   */
  const navigateBack = () => {
    router.back()
  }

  /**
   * Get the URL for a specific admin page
   */
  const getAdminPageUrl = (page: string, hash?: string): string => {
    const hashFragment = hash ? `#${hash}` : ''
    return `admin.php?page=${page}${hashFragment}`
  }

  /**
   * Navigate to a specific route name with params
   */
  const navigateToRoute = (name: string, params?: RouteParamsRaw, query?: LocationQueryRaw) => {
    return router.push({ name, params, query })
  }

  return {
    navigateToAdminPage,
    navigateWithinSpa,
    navigateToAllForms,
    navigateToEntries,
    navigateToSettings,
    navigateToIntegrations,
    navigateToDashboard,
    navigateToFormBuilder,
    navigateBack,
    getAdminPageUrl,
    navigateToRoute,
    currentPage,
  }
}
