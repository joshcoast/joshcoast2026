import { defineStore } from 'pinia'
import { ref, watch, nextTick } from 'vue'
import { useLabels } from '@/composables/useLabels'
import IvyMessage from '@/views/_components/message/ivyMessage.ts'
import { useApiClient } from '@/composables/useApiClient'
import { useUnsavedChangesStore } from '@/stores/useUnsavedChangesStore'
import { getErrorMessage } from '@/utils/errorHandling'

export const useConfirmationSettingBuilder = defineStore('confirmationSettingBuilder', () => {
  const allConfirmations = ref([])
  const id = ref<number | null>(null)
  const formId = ref<number | null>(null)
  const type = ref<string | null>('successMessage')
  const enabled = ref(true)
  const showForm = ref(false)
  const message = ref<string | null>('')
  const url = ref<string | null>('')
  const page = ref<string | null>('')
  const pageUrl = ref<string | null>('')
  const isEditing = ref(false)

  const { getLabel } = useLabels()

  // Use unsaved changes store for state management
  const unsavedChangesStore = useUnsavedChangesStore()

  // Flag to suppress change tracking during load/reset operations
  let suppressDirtyTracking = false

  /**
   * Mark the confirmation builder as having unsaved changes
   */
  const markDirty = () => {
    if (!suppressDirtyTracking && isEditing.value) {
      unsavedChangesStore.markDirty('confirmationBuilder')
    }
  }

  /**
   * Mark the confirmation builder as clean (no unsaved changes)
   */
  const markClean = () => {
    unsavedChangesStore.markClean('confirmationBuilder')
  }

  // Watch all tracked state for changes and mark dirty
  watch([type, enabled, showForm, message, url, page, pageUrl], () => markDirty())

  const resetEntityState = () => {
    markClean()
  }
  const { request } = useApiClient()

  // Load existing confirmation if confirmation id is provided
  const loadConfirmation = async (confirmationId: number) => {
    // Suppress dirty tracking during load
    suppressDirtyTracking = true

    try {
      const { data, error, status } = await request(`/confirmation/${confirmationId}`, {
        method: 'GET',
      })
      const confirmation = data?.data?.data
      if (!error && status === 200 && confirmation) {
        id.value = confirmationId
        formId.value = confirmation.formId
        type.value = confirmation.type || 'successMessage'
        enabled.value = confirmation.enabled || false
        showForm.value = confirmation.showForm || false
        message.value = confirmation.message || ''
        url.value = confirmation.url || ''
        page.value = confirmation.page || ''
        pageUrl.value = confirmation.pageUrl || ''
        isEditing.value = true

        // Start editing and mark clean (just loaded)
        unsavedChangesStore.startEditing('confirmationBuilder')
        markClean()
      } else {
        IvyMessage({
          message: `${getLabel('failed_to_load_confirmation')} ${(data?.message ?? error) || ''}`,
          type: 'error',
        })
      }
    } catch (error) {
      const errorMessage = getErrorMessage(error)
      IvyMessage({
        message: `${getLabel('failed_to_load_confirmation')} ${errorMessage}`,
        type: 'error',
      })
    } finally {
      // Wait for watchers to fire before re-enabling dirty tracking
      await nextTick()
      suppressDirtyTracking = false
    }
  }

  // Fetch all confirmations
  const fetchAllConfirmations = async (formIdValue: number) => {
    if (!formIdValue || isNaN(Number(formIdValue))) {
      return
    }
    // Suppress dirty tracking during fetch
    suppressDirtyTracking = true

    try {
      const { data, error, status } = await request(`/confirmations/${formIdValue}`, {
        method: 'GET',
      })
      if (!error && status === 200 && data?.data) {
        const responseData = data.data[0][0]
        id.value = responseData.id
        formId.value = responseData.formId
        type.value = responseData.type || 'successMessage'
        enabled.value = responseData.enabled || false
        showForm.value = responseData.showForm || false
        message.value = responseData.message || ''
        url.value = responseData.url || ''
        page.value = responseData.page || ''
        isEditing.value = true

        // Start editing and mark clean (just loaded)
        unsavedChangesStore.startEditing('confirmationBuilder')
        markClean()
      } else {
        IvyMessage({
          message: `${getLabel('failed_to_fetch_confirmation')} ${(data?.message ?? error) || ''}`,
          type: 'error',
        })
      }
    } catch (error) {
      const errorMessage = getErrorMessage(error)
      IvyMessage({
        message: `${getLabel('failed_to_fetch_confirmation')} ${errorMessage}`,
        type: 'error',
      })
    } finally {
      // Re-enable dirty tracking
      suppressDirtyTracking = false
    }
  }

  // Reset confirmation to default values
  const resetConfirmation = () => {
    // Suppress dirty tracking during reset
    suppressDirtyTracking = true

    id.value = null
    formId.value = null
    type.value = ''
    enabled.value = true
    showForm.value = false
    message.value = ''
    url.value = ''
    page.value = ''
    isEditing.value = false

    // Stop editing and clear dirty flag
    unsavedChangesStore.stopEditing('confirmationBuilder')

    // Re-enable dirty tracking
    suppressDirtyTracking = false
  }

  // Create a new confirmation
  const createConfirmation = async () => {
    const confirmationData = {
      formId: formId.value ?? null,
      type: type.value || '',
      enabled: enabled.value || false,
      showForm: showForm.value || false,
      message: message.value || '',
      url: url.value || '',
      page: page.value || '',
    }
    try {
      const { data, error, status } = await request('/confirmation/add/', {
        method: 'POST',
        data: confirmationData,
      })
      if (!error && status === 200 && data?.data?.data) {
        id.value = data.data.data.id
        allConfirmations.value.push(data.data)
        IvyMessage({
          message: getLabel('create_confirmation'),
          type: 'success',
        })
        markClean()
      } else {
        IvyMessage({
          message: `${getLabel('failed_to_create_confirmation')} ${(data?.message ?? error) || ''}`,
          type: 'error',
        })
      }
    } catch (error) {
      const errorMessage = getErrorMessage(error)
      IvyMessage({
        message: `${getLabel('error_creating_confirmation')} ${errorMessage}`,
        type: 'error',
      })
    }
  }

  // Update confirmation data
  const updateConfirmation = async (confirmationId, dataToUpdate) => {
    try {
      const { data, error, status } = await request(`/confirmation/update/${confirmationId}/`, {
        method: 'POST',
        data: dataToUpdate,
      })
      if (!error && status === 200) {
        IvyMessage({
          message: getLabel('update_confirmation'),
          type: 'success',
        })
        markClean()
      } else {
        IvyMessage({
          message: `${getLabel('error_creating_confirmation')}: ${(data?.message ?? error) || ''}`,
          type: 'error',
        })
      }
    } catch (error) {
      const errorMessage = getErrorMessage(error)
      IvyMessage({
        message: `${getLabel('error_to_update_confirmation')} ${errorMessage}`,
        type: 'error',
      })
    }
  }

  // Duplicate confirmation
  const duplicateConfirmation = async (confirmationId: number) => {
    try {
      const { data, error, status } = await request(`/confirmation/duplicate/${confirmationId}/`, {
        method: 'POST',
      })
      if (!error && status === 200 && data?.data) {
        IvyMessage({
          message: getLabel('updating_duplicated_confirmation'),
          type: 'success',
        })
        allConfirmations.value.push(data.data)
      } else {
        IvyMessage({
          message: `${getLabel('failed_to_duplicate_confirmation')} ${(data?.message ?? error) || ''}`,
          type: 'error',
        })
      }
    } catch (error) {
      const errorMessage = getErrorMessage(error)
      IvyMessage({
        message: `${getLabel('error_duplicate_confirmation')} ${errorMessage}`,
        type: 'error',
      })
    }
  }

  // Delete confirmation
  const deleteConfirmation = async (confirmationId) => {
    try {
      const { data, error, status } = await request(`/confirmation/delete/${confirmationId}/`, {
        method: 'POST',
        data: { id: confirmationId },
      })
      if (!error && status === 200) {
        IvyMessage({
          message: getLabel('delete_confirmation'),
          type: 'success',
        })
        allConfirmations.value = allConfirmations.value.filter((item) => item.id !== confirmationId)
      } else {
        IvyMessage({
          message: `${getLabel('failed_to_delete_confirmation')} ${(data?.message ?? error) || ''}`,
          type: 'error',
        })
      }
    } catch (error) {
      const errorMessage = getErrorMessage(error)
      IvyMessage({
        message: `${getLabel('error_delete_confirmation')} ${errorMessage}`,
        type: 'error',
      })
    }
  }

  return {
    allConfirmations,
    id,
    formId,
    type,
    enabled,
    showForm,
    resetEntityState,
    message,
    url,
    page,
    pageUrl,
    isEditing,
    markDirty,
    markClean,
    fetchAllConfirmations,
    resetConfirmation,
    createConfirmation,
    updateConfirmation,
    deleteConfirmation,
    loadConfirmation,
    duplicateConfirmation,
  }
})
