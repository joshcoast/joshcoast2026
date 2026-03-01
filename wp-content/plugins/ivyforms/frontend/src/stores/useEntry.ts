import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useApiClient } from '@/composables/useApiClient.ts'
import IvyMessage from '@/views/_components/message/ivyMessage.ts'
import { useLabels } from '@/composables/useLabels.ts'
import { useAllEntries } from '@/stores/useAllEntries.ts'

export const useEntry = defineStore('entry', () => {
  const entry = ref(null)
  const entryFields = ref([])
  const loading = ref(false)
  const error = ref(null)
  const { getLabel } = useLabels()
  const { request } = useApiClient()
  const allEntriesStore = useAllEntries()

  const fetchEntry = async (id: number) => {
    try {
      const { data, error, status } = await request(`entry/${id}`, {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
        },
      })
      if (!error && status === 200 && data?.data) {
        entry.value = data.data.entry
        entryFields.value = data.data.fields || []
        loading.value = false

        // Mark the entry as read
        if (entry.value.status === 'unread') {
          await allEntriesStore.updateStatus(id, 'read')
          entry.value.status = 'read'
        }
      } else {
        IvyMessage({
          message: `${getLabel('failed_to_get_entry')} ${data?.message || ''}`,
          type: 'error',
        })
        console.error(getLabel('failed_to_get_entry'), data?.message)
      }
    } catch (error) {
      IvyMessage({
        message: `${getLabel('failed_to_get_entry')} ${error}`,
        type: 'error',
      })
    }
  }

  return {
    entry,
    entryFields,
    loading,
    error,
    fetchEntry,
  }
})
