import { ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import type { LocationQueryValue } from 'vue-router'
import { useLabels } from '@/composables/useLabels.ts'

export interface TableState {
  page: number
  perPage: number
  search: string
  sortBy: string
  sortOrder: 'asc' | 'desc'
  dateRange: string[]
  filters: Record<string, string | number | null>
}

/**
 * Composable to synchronize table state with URL query parameters
 * Enables table state persistence across navigation and browser back/forward
 */
export function useTableStateSync(defaultState: Partial<TableState> = {}) {
  const route = useRoute()
  const router = useRouter()
  const { getLabel } = useLabels()

  // Default values
  const defaults: TableState = {
    page: 1,
    perPage: 10,
    search: '',
    sortBy: '',
    sortOrder: 'desc',
    dateRange: [],
    filters: {},
    ...defaultState,
  }

  // Parse query params to state
  const parseQueryToState = (): TableState => {
    const query = route.query

    // Safely parse filters with try-catch to handle malformed JSON
    let filters = defaults.filters
    if (query.filters) {
      try {
        filters = JSON.parse(query.filters as string)
      } catch (error) {
        console.warn(getLabel('failed_to_parse_filter'), error)
        // Use default filters on error
      }
    }

    return {
      page: parseInt((query.page as string) || String(defaults.page)),
      perPage: parseInt((query.perPage as string) || String(defaults.perPage)),
      search: (query.search as string) || defaults.search,
      sortBy: (query.sortBy as string) || defaults.sortBy,
      sortOrder: ((query.sortOrder as string) || defaults.sortOrder) as 'asc' | 'desc',
      dateRange: query.dateRange
        ? Array.isArray(query.dateRange)
          ? query.dateRange
          : [query.dateRange]
        : defaults.dateRange,
      filters,
    }
  }

  // Initialize state from URL
  const tableState = ref<TableState>(parseQueryToState())

  // Convert state to query params
  const stateToQuery = (
    state: TableState,
  ): Record<string, LocationQueryValue | LocationQueryValue[]> => {
    const query: Record<string, LocationQueryValue | LocationQueryValue[]> = {}

    // Only include non-default values to keep URL clean
    if (state.page !== defaults.page) query.page = String(state.page)
    if (state.perPage !== defaults.perPage) query.perPage = String(state.perPage)
    if (state.search) query.search = state.search
    if (state.sortBy) query.sortBy = state.sortBy
    if (state.sortOrder !== defaults.sortOrder) query.sortOrder = state.sortOrder
    if (state.dateRange && state.dateRange.length > 0) query.dateRange = state.dateRange
    if (Object.keys(state.filters).length > 0) query.filters = JSON.stringify(state.filters)

    return query
  }

  // Update URL when state changes
  const syncStateToUrl = (state: TableState, replace = false) => {
    const query = stateToQuery(state)
    const method = replace ? router.replace : router.push

    method({
      query: {
        ...route.query,
        ...query,
      },
    }).catch((err) => {
      // Ignore navigation duplicated errors
      if (err.name !== 'NavigationDuplicated') {
        console.error('Navigation error:', err)
      }
    })
  }

  // Watch route query changes and update state
  watch(
    () => route.query,
    () => {
      tableState.value = parseQueryToState()
    },
    { deep: true },
  )

  // Update state and sync to URL
  const updateState = (updates: Partial<TableState>, replace = false) => {
    tableState.value = {
      ...tableState.value,
      ...updates,
    }
    syncStateToUrl(tableState.value, replace)
  }

  // Reset to default state
  const resetState = () => {
    tableState.value = { ...defaults }
    syncStateToUrl(defaults, true)
  }

  // Get current state as plain object
  const getState = (): TableState => {
    return { ...tableState.value }
  }

  return {
    tableState,
    updateState,
    resetState,
    getState,
    syncStateToUrl,
  }
}
