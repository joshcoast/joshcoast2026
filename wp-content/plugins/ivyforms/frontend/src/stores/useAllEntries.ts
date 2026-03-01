import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useLabels } from '@/composables/useLabels'
import IvyMessage from '@/views/_components/message/ivyMessage.ts'
import { useApiClient } from '@/composables/useApiClient.ts'

export const useAllEntries = defineStore('allEntries', () => {
  const tableData = ref([])
  const entryFields = ref([])
  const paginationMeta = ref({
    page: 1,
    perPage: 10,
    total: 0,
  })
  const filterCount = ref({
    readTrueCount: 0,
    readFalseCount: 0,
    starredTrueCount: 0,
    starredFalseCount: 0,
  })
  const formIdToName = ref<Record<number, string>>({})

  const { getLabel } = useLabels()
  const { request } = useApiClient()
  const fetchData = async ({
    page = 1,
    perPage = 10,
    search = '',
    sortBy = '',
    sortOrder = 'desc',
    dateRange = [],
    filters = {},
    shouldGetCount = true,
    formId = null,
    searchFieldValue = false,
  }: {
    page?: number
    perPage?: number
    search?: string
    sortBy?: string
    sortOrder?: string
    dateRange?: Array<string>
    filters?: Record<string, string | number | null>
    shouldGetCount?: boolean
    formId?: number | null
    searchFieldValue?: boolean
  } = {}) => {
    try {
      // Always include formId in filters if present
      const mergedFilters = { ...filters }
      if (formId !== null && formId !== undefined) {
        mergedFilters.formId = formId
      }
      const params: Record<
        string,
        string | number | boolean | null | string[] | Record<string, string | number>
      > = {
        page,
        perPage,
        search,
        orderBy: sortBy,
        order: sortOrder,
        dateRange,
        filters: mergedFilters,
        shouldGetCount,
        searchFieldValue,
      }
      const { data, error } = await request('entries/search', {
        method: 'GET',
        params,
      })
      if (error) {
        IvyMessage({
          message: `${getLabel('error_fetching_entries')} ${error}`,
          type: 'error',
        })
        return
      }

      const response = data.data
      tableData.value = response.data.data
      paginationMeta.value = response.data.meta
      if (shouldGetCount) {
        filterCount.value = response.data.filterCount
      }
      entryFields.value = response.data.entryFields
      formIdToName.value = response.data.formIdToName || {}
    } catch (error) {
      IvyMessage({
        message: `${getLabel('error_fetching_entries')} ${error}`,
        type: 'error',
      })
    }
  }

  const updateStarred = async (id: number, starred: boolean) => {
    try {
      const { data, error, status } = await request(`entry/update/starred/${id}/`, {
        method: 'POST',
        data: {
          attribute: 'starred',
          value: starred,
        },
      })

      if (error || status >= 400) {
        IvyMessage({
          message: starred
            ? `${getLabel('failed_to_star_entry')} ${data?.message || error}`
            : `${getLabel('failed_to_unstar_entry')} ${data?.message || error}`,
          type: 'error',
        })
      } else {
        IvyMessage({
          message: starred ? getLabel('entry_starred') : getLabel('entry_unstarred'),
          type: 'success',
        })
      }
    } catch (error) {
      IvyMessage({
        message: starred
          ? `${getLabel('failed_to_star_entry')} ${error}`
          : `${getLabel('failed_to_unstar_entry')} ${error}`,
        type: 'error',
      })
    }
  }

  const updateStatus = async (id: number, status: string) => {
    try {
      const {
        data,
        error,
        status: httpStatus,
      } = await request(`entry/update/status/${id}/`, {
        method: 'POST',
        data: {
          value: status,
        },
      })

      if (error || httpStatus >= 400) {
        IvyMessage({
          message: `${getLabel('failed_to_update_entry_status')} ${data?.message || error}`,
          type: 'error',
        })
      } else {
        IvyMessage({
          message: status === 'read' ? getLabel('entry_read') : getLabel('entry_unread'),
          type: 'success',
        })
      }
    } catch (error) {
      IvyMessage({
        message: `${getLabel('failed_to_update_entry_status')} ${error}`,
        type: 'error',
      })
    }
  }

  const reset = () => {
    tableData.value = []
    entryFields.value = []
    paginationMeta.value = { page: 1, perPage: 10, total: 0 }
    filterCount.value = {
      readTrueCount: 0,
      readFalseCount: 0,
      starredTrueCount: 0,
      starredFalseCount: 0,
    }
  }

  return {
    tableData,
    entryFields,
    paginationMeta,
    filterCount,
    formIdToName,
    fetchData,
    updateStarred,
    updateStatus,
    reset,
  }
})
