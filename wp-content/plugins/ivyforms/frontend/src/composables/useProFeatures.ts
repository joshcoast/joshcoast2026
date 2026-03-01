import { ref, computed } from 'vue'
import { useApiClient } from '@/composables/useApiClient'

/**
 * Pro Features Composable
 * Responsibilities:
 *  - Provides reactive access to plan, feature flags, license info, upgrade map & groups
 *  - Offers refresh() to revalidate after license activation/upgrade without page reload
 *
 * Usage:
 *  const pro = useProFeatures();
 *  await pro.fetch(); // load once per page view
 *  if (pro.hasFeature('password_field')) { // enable feature-specific UI }
 *
 * Staleness Strategy:
 *  - Current implementation treats first successful load as final until refresh() is called.
 *  - You can add TTL logic: if(Date.now() - fetchedAt > X) then refetch lazily.
 */
interface ProFeaturePayload {
  plan: string
  features: Record<string, boolean>
  upgrade: Record<string, string>
  groups?: Record<string, string[]>
  meta?: Record<string, { label?: string; group?: string }>
  license?: Record<string, unknown>
}

interface Window {
  ivyFormsProScripts?: Record<string, string>
}

const _state = {
  loading: ref(false),
  loaded: ref(false),
  error: ref<unknown | null>(null),
  data: ref<ProFeaturePayload | null>(null),
  fetchedAt: ref<number | null>(null),
}

export function useProFeatures() {
  const { request } = useApiClient()

  const hasFeature = (slug: string) => {
    if (!_state.loaded.value) return false
    return !!_state.data.value?.features?.[slug]
  }

  const plan = computed(() => _state.data.value?.plan || 'lite')
  const license = computed(() => _state.data.value?.license || null)
  const upgradeMap = computed(() => _state.data.value?.upgrade || {})
  const groups = computed(() => _state.data.value?.groups || {})

  const fetch = async (options: { force?: boolean } = {}) => {
    if (_state.loading.value) return
    if (!options.force && _state.loaded.value) return
    const w = window as Window

    // If Pro scripts are not loaded, mark as loaded with lite plan to avoid infinite pending
    if (w.ivyFormsProScripts === undefined) {
      _state.data.value = {
        plan: 'lite',
        features: {},
        upgrade: {},
      }
      _state.loaded.value = true
      _state.fetchedAt.value = Date.now()
      return
    }

    _state.loading.value = true
    // Preserve previous successful state unless force refresh
    if (options.force) {
      _state.error.value = null
    }

    const { data, error, status } = await request<{ message: string; data: ProFeaturePayload }>(
      'pro/features',
    )

    if (data && data.data) {
      _state.data.value = data.data as ProFeaturePayload
      _state.loaded.value = true
      _state.fetchedAt.value = Date.now()
      _state.error.value = null
    } else if (error) {
      // Graceful fallback scenarios when Pro is not installed / inaccessible.
      // We always flip loaded=true so UI logic can settle and show locked tiles instead of staying pending forever.
      if (status === 404) {
        // Route not found => Pro not installed. Treat as Lite + empty features.
        // The 404 error above is expected and can be safely ignored.
        console.info(
          '%c🌿 IvyForms Lite %cis in use',
          'font-weight: bold; color: #06a192;',
          'font-weight: normal; color: inherit;',
        )
        _state.data.value = _state.data.value || {
          plan: 'lite',
          features: {},
          upgrade: {},
        }
        _state.error.value = null
      } else if (status === 401 || status === 403) {
        // Unauthorized: maybe nonce expired or user lacks capability. Still expose empty dataset.
        _state.data.value = _state.data.value || {
          plan: 'unauthorized',
          features: {},
          upgrade: {},
        }
        _state.error.value = { message: 'pro_features_unauthorized', status }
      } else {
        // Other server / network errors. Provide safe empty dataset if none yet so gating can proceed.
        if (!_state.data.value) {
          _state.data.value = {
            plan: 'error',
            features: {},
            upgrade: {},
          }
        }
        _state.error.value = error
      }
      _state.loaded.value = true
      if (!_state.fetchedAt.value) _state.fetchedAt.value = Date.now()
    }
    _state.loading.value = false
  }

  const refresh = async () => fetch({ force: true })

  return {
    ..._state,
    plan,
    license,
    groups,
    upgradeMap,
    hasFeature,
    fetch,
    refresh,
  }
}
