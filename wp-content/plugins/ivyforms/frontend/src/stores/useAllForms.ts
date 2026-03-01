import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useLabels } from '@/composables/useLabels'
import IvyMessage from '@/views/_components/message/ivyMessage.ts'
import { useApiClient } from '@/composables/useApiClient.ts'

export const useAllForms = defineStore('allForms', () => {
  const tableData = ref([])
  const paginationMeta = ref({
    page: 1,
    perPage: 10,
    total: 0,
  })
  const filterCount = ref({
    publishedTrueCount: 0,
    publishedFalseCount: 0,
    starredTrueCount: 0,
    starredFalseCount: 0,
  })
  const entryCount = ref<Record<number, number>>({})
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
  }: {
    page?: number
    perPage?: number
    search?: string
    sortBy?: string
    sortOrder?: string
    dateRange?: Array<string>
    filters?: Record<string, string | number>
    shouldGetCount?: boolean
  } = {}) => {
    try {
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
        filters,
        shouldGetCount,
      }
      const { data, error } = await request('forms/search', {
        method: 'GET',
        params,
      })
      if (error) {
        IvyMessage({
          message: `${getLabel('error_fetching_forms')} ${error}`,
          type: 'error',
        })
        return
      }

      const response = data.data
      tableData.value = normalizeData(response.data.data)
      paginationMeta.value = response.data.meta
      if (shouldGetCount) {
        filterCount.value = response.data.filterCount
      }
      // Update entryCount from response
      if (response.data.entryCounts) {
        Object.entries(response.data.entryCounts).forEach(([id, count]) => {
          entryCount.value[Number(id)] = count as number
        })
      }
    } catch (error) {
      IvyMessage({
        message: `${getLabel('error_fetching_forms')} ${error}`,
        type: 'error',
      })
    }
  }

  const updateStarred = async (id: number, starred: boolean) => {
    try {
      const { data, error, status } = await request(`form/update/starred/${id}/`, {
        method: 'POST',
        data: {
          attribute: 'starred',
          value: starred,
        },
      })

      if (error || status >= 400) {
        IvyMessage({
          message: starred
            ? `${getLabel('failed_to_star_form')}: ${data?.message || error}`
            : `${getLabel('failed_to_unstar_form')}: ${data?.message || error}`,
          type: 'error',
        })
      } else {
        IvyMessage({
          message: starred ? getLabel('form_starred') : getLabel('form_unstarred'),
          type: 'success',
        })
      }
    } catch (error) {
      IvyMessage({
        message: starred
          ? `${getLabel('failed_to_star_form')} ${error}`
          : `${getLabel('failed_to_unstar_form')} ${error}`,
        type: 'error',
      })
    }
  }

  const updateStatus = async (id: number, status: boolean) => {
    try {
      const {
        data,
        error,
        status: httpStatus,
      } = await request(`form/update/status/${id}/`, {
        method: 'POST',
        data: {
          value: status,
        },
      })

      if (error || httpStatus >= 400) {
        IvyMessage({
          message: `${getLabel('failed_to_update_form')} ${data?.message || error}`,
          type: 'error',
        })
      } else {
        IvyMessage({
          message: status ? getLabel('form_published') : getLabel('form_unpublished'),
          type: 'success',
        })
      }
    } catch (error) {
      IvyMessage({
        message: `${getLabel('error_updating_forms')} ${error}`,
        type: 'error',
      })
    }
  }

  const normalizeData = (data: Array<{ starred: string; published: string }>) => {
    return data.map((item) => ({
      ...item,
      starred: item.starred === '1',
      published: item.published === '1',
    }))
  }

  return {
    tableData,
    paginationMeta,
    filterCount,
    fetchData,
    updateStarred,
    updateStatus,
    entryCount,
  }
})
