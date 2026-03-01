import { defineStore } from 'pinia'
import { ref, watch, nextTick } from 'vue'
import { useLabels } from '@/composables/useLabels'
import IvyMessage from '@/views/_components/message/ivyMessage.ts'
import { useApiClient } from '@/composables/useApiClient'
import { useUnsavedChangesStore } from '@/stores/useUnsavedChangesStore'
import { getErrorMessage } from '@/utils/errorHandling'

export const useNotificationSettingBuilder = defineStore('notificationSettingBuilder', () => {
  const allNotifications = ref([])
  const { getLabel } = useLabels()
  const tableData = ref([])
  const paginationMeta = ref({
    page: 1,
    perPage: 10,
    total: 0,
  })
  const name = ref<string | null>(getLabel('new_notification'))
  const sender = ref<string | null>('')
  const replyTo = ref<string | null>('')
  const receiver = ref<string | null>('')
  const enabled = ref(false)
  const subject = ref<string | null>('')
  const message = ref<string | null>('')
  const smartLogic = ref(false)
  const formId = ref<string | null>(null)
  const id = ref<number | null>(null)
  const isEditing = ref(false) // Make `isEditing` reactive

  // Use unsaved changes store for state management
  const unsavedChangesStore = useUnsavedChangesStore()

  // Flag to suppress change tracking during load/reset operations
  let suppressDirtyTracking = false

  /**
   * Mark the notification builder as having unsaved changes
   */
  const markDirty = () => {
    if (!suppressDirtyTracking && isEditing.value) {
      unsavedChangesStore.markDirty('notificationBuilder')
    }
  }

  /**
   * Mark the notification builder as clean (no unsaved changes)
   */
  const markClean = () => {
    unsavedChangesStore.markClean('notificationBuilder')
  }

  // Watch all tracked state for changes and mark dirty
  watch([name, sender, replyTo, receiver, enabled, subject, message], () => markDirty())

  const { request } = useApiClient()

  // Load existing notification if notification id is provided
  const loadNotification = async (NotifId: number) => {
    // Suppress dirty tracking during load
    suppressDirtyTracking = true

    try {
      const { data, error, status } = await request(`/notification/${NotifId}`, {
        method: 'GET',
      })
      if (!error && status === 200 && data?.data?.data) {
        const responseData = data.data.data
        formId.value = responseData.formId
        id.value = NotifId
        name.value = responseData.name || getLabel('new_notification')
        sender.value = responseData.sender || ''
        replyTo.value = responseData.replyTo || ''
        receiver.value = responseData.receiver || ''
        enabled.value = responseData.enabled || false
        subject.value = responseData.subject || getLabel('new_notification')
        message.value = responseData.message || ''
        smartLogic.value = false
        isEditing.value = true

        // Start editing and mark clean (just loaded)
        unsavedChangesStore.startEditing('notificationBuilder')
        markClean()
      } else {
        IvyMessage({
          message: `${getLabel('failed_to_load_notification')} ${data?.message || error || ''}`,
          type: 'error',
        })
      }
    } catch (error) {
      const errorMessage = getErrorMessage(error)
      IvyMessage({
        message: `${getLabel('failed_to_load_notification')} ${errorMessage}`,
        type: 'error',
      })
      // Reset editing state on error
      isEditing.value = false
      unsavedChangesStore.stopEditing('notificationBuilder')
    } finally {
      // Wait for watchers to fire before re-enabling dirty tracking
      await nextTick()
      suppressDirtyTracking = false
    }
  }

  // Fetch all form notifications
  const fetchData = async (formId?: number) => {
    try {
      const url = formId ? `/notifications/${formId}` : `/notifications`
      const { data, error } = await request(url, { method: 'GET' })
      if (data && data.data) {
        allNotifications.value = data.data.data
      } else {
        allNotifications.value = []
        IvyMessage({
          message: `${getLabel('unexpected_response_structure')} ${error || data || ''}`,
          type: 'error',
        })
      }
    } catch (error) {
      const errorMessage = getErrorMessage(error)
      IvyMessage({
        message: `${getLabel('failed_to_fetch_notification')} ${errorMessage}`,
        type: 'error',
      })
    }
  }

  // Fetch all notifications
  const fetchAllNotifications = async () => {
    try {
      const url = `/notifications`
      const { data, error } = await request(url, { method: 'GET' })
      if (data && data.data) {
        allNotifications.value = data.data.data
      } else {
        allNotifications.value = []
        IvyMessage({
          message: `${getLabel('unexpected_response_structure')} ${error || data || ''}`,
          type: 'error',
        })
      }
    } catch (error) {
      const errorMessage = getErrorMessage(error)
      IvyMessage({
        message: `${getLabel('failed_to_fetch_notification')} ${errorMessage}`,
        type: 'error',
      })
    }
  }

  // Reset notification to default values
  const resetNotification = (formIdRoute: string) => {
    // Suppress dirty tracking during reset
    suppressDirtyTracking = true

    name.value = getLabel('new_notification')
    sender.value = ''
    replyTo.value = ''
    receiver.value = ''
    enabled.value = true
    subject.value = getLabel('new_notification')
    message.value = '{{all_data}}'
    smartLogic.value = false
    formId.value = formIdRoute
    id.value = null
    isEditing.value = false // Reset to false when resetting notification

    // Stop editing and clear dirty flag
    unsavedChangesStore.stopEditing('notificationBuilder')

    // Re-enable dirty tracking
    suppressDirtyTracking = false
  }

  // Create a new notification
  const createNotification = async (formId) => {
    const notificationData = {
      name: name.value || getLabel('new_notification'),
      sender: sender.value || '',
      replyTo: replyTo.value || '',
      receiver: receiver.value || '',
      enabled: enabled.value || false,
      subject: subject.value || getLabel('new_notification'),
      message: message.value || '',
      smartLogic: smartLogic.value || false,
      formId: formId || null,
    }

    try {
      const { data, error, status } = await request('/notification/add/', {
        method: 'POST',
        data: notificationData,
      })
      if (!error && status === 200) {
        id.value = data.data.data.id
        allNotifications.value.push(data.data)
        IvyMessage({
          message: getLabel('create_notification'),
          type: 'success',
        })
        markClean()
      } else {
        IvyMessage({
          message: `${getLabel('failed_to_create_notification')}: ${data?.message || error || ''}`,
          type: 'error',
        })
      }
    } catch (error) {
      const errorMessage = getErrorMessage(error)
      IvyMessage({
        message: `${getLabel('error_creating_notification')} ${errorMessage}`,
        type: 'error',
      })
    }
  }

  // Update notification data
  const updateNotification = async (NotifId, dataObj) => {
    try {
      const { data, error, status } = await request(`/notification/update/${NotifId}/`, {
        method: 'POST',
        data: dataObj,
      })
      if (!error && status === 200) {
        IvyMessage({
          message: getLabel('update_notification'),
          type: 'success',
        })
        markClean()
      } else {
        IvyMessage({
          message: `${getLabel('failed_to_update_notification')}: ${data?.message || error || ''}`,
          type: 'error',
        })
      }
    } catch (error) {
      const errorMessage = getErrorMessage(error)
      IvyMessage({
        message: `${getLabel('error_updating_notification')} ${errorMessage}`,
        type: 'error',
      })
    }
  }

  // Search notifications with pagination and sorting
  const searchNotifications = async ({
    page = 1,
    perPage = 10,
    search = '',
    sortBy = '',
    sortOrder = 'asc',
    filters = {},
    formId = null,
  }: {
    page?: number
    perPage?: number
    search?: string
    sortBy?: string
    sortOrder?: string
    filters?: Record<string, string | number | null>
    formId?: number | null
  } = {}) => {
    try {
      const mergedFilters = { ...filters }
      if (formId !== null && formId !== undefined) {
        mergedFilters.formId = formId
      }
      const params: Record<
        string,
        string | number | boolean | null | Record<string, string | number | null>
      > = {
        page,
        perPage,
        search,
        orderBy: sortBy,
        order: sortOrder,
        filters: mergedFilters,
      }

      const { data, error } = await request('notifications/search', {
        method: 'GET',
        params,
      })

      if (error) {
        const errorMessage = getErrorMessage(error)
        IvyMessage({
          message: `${getLabel('error_fetching_notifications')} ${errorMessage}`,
          type: 'error',
        })
        return
      }

      const response = data.data
      tableData.value = normalizeNotificationData(response.data.data || [])
      paginationMeta.value = response.data.meta || { page: 1, perPage: 10, total: 0 }
    } catch (error) {
      const errorMessage = getErrorMessage(error)
      IvyMessage({
        message: `${getLabel('error_fetching_notifications')} ${errorMessage}`,
        type: 'error',
      })
    }
  }

  // Define proper types for notification data
  interface RawNotificationItem {
    id?: number
    name?: string
    sender?: string
    receiver?: string
    subject?: string
    message?: string
    enabled?: string | number | boolean
    formId?: number
    [key: string]: unknown // For any wrapped notification properties
  }

  interface NormalizedNotification {
    id: number
    name: string
    sender: string
    receiver: string
    subject: string
    message: string
    enabled: boolean
    formId: number
  }

  // Normalize notification data structure
  const normalizeNotificationData = (data: RawNotificationItem[]): NormalizedNotification[] => {
    return data.map((item) => {
      if (item && typeof item === 'object') {
        const notificationKey = Object.keys(item).find(
          (key) => key.includes('notification') || key.includes('Notification'),
        )

        if (notificationKey && item[notificationKey]) {
          const notification = item[notificationKey] as RawNotificationItem
          return {
            id: notification.id || 0,
            name: notification.name || '',
            sender: notification.sender || '',
            replyTo: notification.replyTo || '',
            receiver: notification.receiver || '',
            subject: notification.subject || '',
            message: notification.message || '',
            enabled: notification.enabled === '1',
            formId: notification.formId || 0,
          }
        }

        // If it's a direct notification object
        if (item.id && item.name !== undefined) {
          return {
            id: item.id,
            name: item.name,
            sender: item.sender || '',
            replyTo: item.replyTo || '',
            receiver: item.receiver || '',
            subject: item.subject || '',
            message: item.message || '',
            enabled: item.enabled === '1',
            formId: item.formId || null,
          }
        }
      }

      // Return a fallback object for items that couldn't be normalized
      return {
        id: 0,
        name: 'Unknown',
        sender: '',
        replyTo: '',
        receiver: '',
        subject: '',
        message: '',
        enabled: false,
        formId: null,
      }
    })
  }

  return {
    allNotifications,
    tableData,
    paginationMeta,
    name,
    sender,
    replyTo,
    receiver,
    enabled,
    subject,
    message,
    smartLogic,
    formId,
    id,
    isEditing,
    markDirty,
    markClean,
    fetchData,
    fetchAllNotifications,
    resetNotification,
    createNotification,
    updateNotification,
    loadNotification,
    searchNotifications,
  }
})
