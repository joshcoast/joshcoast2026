import { useApiClient } from '@/composables/useApiClient'

/**
 * Backend integration configuration interface
 */
interface BackendIntegrationConfig {
  slug: string
  label: string
  component: string
  icon?: string
  description?: string
  category?: string
  requiresAuth?: boolean
  plan?: string
  settingsSchema?: Record<string, unknown>
  learnMoreUrl?: string
}

/**
 * Backend-registered integrations cache
 * Populated dynamically from the IntegrationRegistry via REST API
 */
let integrationsCache: Integration[] | null = null
let integrationsFetchPromise: Promise<Integration[]> | null = null

/**
 * Fetch integrations from backend registry
 * Uses caching to avoid multiple API calls
 */
async function fetchBackendIntegrations(): Promise<Integration[]> {
  // Return cached value if available
  if (integrationsCache) {
    return integrationsCache
  }

  // Return existing promise if fetch is in progress
  if (integrationsFetchPromise) {
    return integrationsFetchPromise
  }

  // Start new fetch
  integrationsFetchPromise = (async () => {
    try {
      const { request } = useApiClient()
      const response = await request<{
        success: boolean
        data: Record<string, BackendIntegrationConfig>
      }>('integrations')

      // The API controller returns {success: true, data: {wpdatatables: {...}, mailchimp: {...}}}
      // useApiClient wraps it in {message: 'OK', data: {success: true, data: {...}}}
      // So we need to unwrap twice: response.data.data.data
      const wrappedData = response.data?.data // {success: true, data: {...}}
      const backendData = wrappedData?.data as Record<string, BackendIntegrationConfig> // {wpdatatables: {...}, mailchimp: {...}}

      if (!backendData || typeof backendData !== 'object') {
        throw new Error('Invalid response format')
      }

      // Transform backend format to frontend format
      const transformed: Integration[] = Object.entries(backendData).map(([slug, config]) => ({
        name: config.label, // Translation key from backend (e.g., 'wpdatatables_label')
        value: slug, // Use slug as value
        image: config.icon || slug,
        description: config.description || '', // Translation key from backend (e.g., 'wpdatatables_description')
        category: config.category || 'Other',
        type: config.plan === 'lite' ? 'disabled' : 'soon', // Default type, will be updated by useIntegrations
        searchCategory: config.plan || 'lite',
        requiredPlan: config.plan !== 'lite' ? config.plan : undefined,
        component: config.component,
        learnMoreUrl: config.learnMoreUrl || '',
      }))

      integrationsCache = transformed
      return transformed
    } catch (error) {
      console.error('Failed to load integrations from backend:', error)

      // Fallback to hardcoded defaults
      const fallback: Integration[] = [
        {
          name: 'wpdatatables',
          value: 'wpdatatables',
          image: 'wpdatatables',
          description: 'wpdt_integration_description',
          category: 'Data Management',
          type: 'disabled',
          searchCategory: 'lite',
        },
      ]

      integrationsCache = fallback
      return fallback
    } finally {
      integrationsFetchPromise = null
    }
  })()

  return integrationsFetchPromise
}

/**
 * Get integrations with filters applied
 * This function applies filters at runtime, allowing Pro to modify integrations
 *
 * NOTE: This is now async and should be used with await or in a component setup
 */
export async function getIntegrations(): Promise<Integration[]> {
  let result = await fetchBackendIntegrations()

  // Allow Pro to extend/filter integrations via dedicated hooks
  if (typeof window !== 'undefined') {
    const w = window as Window & {
      IvyFormsPro?: {
        integrationsHooks?: Array<(integrations: Integration[]) => Integration[]>
      }
      IvyForms?: {
        hooks?: {
          applyFilters?: (hookName: string, value: Integration[]) => Integration[]
        }
      }
    }

    // Check Pro-specific hooks first (direct hooks from Pro plugin)
    const proHooks = w.IvyFormsPro?.integrationsHooks || []
    for (const hook of proHooks) {
      result = hook(result)
    }

    // Also support general filters if defined
    if (w.IvyForms?.hooks?.applyFilters) {
      result = w.IvyForms.hooks.applyFilters('ivyforms/integrations/list', result)
    }
  }

  return result
}

/**
 * Clear integrations cache
 * Useful for forcing a refresh after new integrations are registered
 */
export function clearIntegrationsCache(): void {
  integrationsCache = null
  integrationsFetchPromise = null
}

// Remove legacy export - integrations are now loaded dynamically

export type integrationType =
  | 'active'
  | 'disabled'
  | 'upgrade'
  | 'proActive'
  | 'proDisabled'
  | 'soon'

export interface Integration {
  name: string
  value: string
  image?: string
  description: string
  category: string
  type: integrationType
  searchCategory: string
  requiredPlan?: string // Optional: minimum plan required for Pro integrations
  component?: string // Component name for settings UI
  learnMoreUrl?: string // Optional: documentation/learn more URL
}
