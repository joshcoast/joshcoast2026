import IvyMessage from '@/views/_components/message/ivyMessage.ts'
import { defineStore } from 'pinia'
import { ref, watch } from 'vue'
import type { Ref } from 'vue'
import { useLabels } from '@/composables/useLabels'
import { useAllForms } from '@/stores/useAllForms'
import { useAllEntries } from '@/stores/useAllEntries'
import { useNotificationSettingBuilder } from '@/stores/useNotificationSettingBuilder.ts'
import { useRoute } from 'vue-router'
import { useFormBuilder } from '@/stores/useFormBuilder'
import { useApiClient } from '@/composables/useApiClient'

type DeleteEntityOptions = {
  entityType: string
  ids: number[]
  successMessage?: string
  failedMessage?: string
  errorMessage?: string
}

type DeleteSingleEntityOptions = {
  entityType: string
  id: number
  successMessage?: string
  failedMessage?: string
  errorMessage?: string
}

type DuplicateEntityOptions = {
  entityType: string
  id: number
  successMessage?: string
  failedMessage?: string
  errorMessage?: string
}

/* TODO: Redesign - Internationalization */
export const useActionEntityStore = defineStore('actionEntity', () => {
  type EntityType = 'form' | 'forms' | 'notification' | 'entry' | 'entries'
  type ActionType = 'delete' | 'cancel' | 'duplicate' | 'unsaved_changes'
  type ButtonType = 'primary' | 'secondary' | 'success' | 'warning' | 'danger' | 'tertiary'

  type DialogData = {
    title: string
    subtitle: string
    dialogType?: 'info' | 'success' | 'warning' | 'error' | 'default' | 'pro' | 'upcoming'
    buttons?: {
      close: { type?: ButtonType; text: string; function?: () => void }
      confirm: {
        type: ButtonType
        text: string
        function?: () => void
      }
    } | null
  }

  type LoadingCallbacks = {
    setLoading?: (isLoading: boolean) => void
    onSuccess?: () => void
  }

  const defaultDialogData: DialogData = {
    title: '',
    subtitle: '',
    dialogType: 'default',
  }

  const dialogVisible: Ref<boolean> = ref(false)
  const dialogLoading: Ref<boolean> = ref(false)
  const actionAllowed: Ref<boolean> = ref(true)
  const dialogData: Ref<DialogData> = ref({ ...defaultDialogData })
  const entityId: Ref<number | number[]> = ref(0)
  const entityType: Ref<EntityType> = ref('forms')
  const actionType: Ref<ActionType> = ref('delete')
  const successCallback: Ref<(() => void) | null> = ref(null)
  const loadingCallbacks: Ref<LoadingCallbacks> = ref({})

  // For unsaved changes dialog
  const pendingNavigationCallback: Ref<(() => void) | null> = ref(null)

  watch(dialogVisible, (value) => {
    if (!value) {
      setTimeout(onClose, 150)
    }
  })

  const handleActionClick = async (
    id: number | null,
    ids: number[] | null = null,
    type: EntityType,
    action: ActionType,
    callbacks: LoadingCallbacks = {},
    navigationCallback?: () => void,
  ) => {
    entityId.value = ids || id
    entityType.value = type
    actionType.value = action
    loadingCallbacks.value = callbacks

    // Store navigation callback for unsaved changes
    if (action === 'unsaved_changes' && navigationCallback) {
      pendingNavigationCallback.value = navigationCallback
    }

    generateDialogMessage()
    dialogVisible.value = true
  }

  const generateDialogMessage = () => {
    dialogData.value.title = getDialogText()['title']
    dialogData.value.subtitle = getDialogText()['subtitle']

    dialogData.value.buttons = dialogData.value.buttons
      ? dialogData.value.buttons
      : {
          close: { text: getLabel('close') },
          confirm: { type: 'primary', text: getDialogText()['confirmText'] },
        }
    if (actionType.value === 'delete') {
      dialogData.value.buttons.confirm.type = 'danger'
      dialogData.value.dialogType = 'error'
    }

    if (actionType.value === 'duplicate') {
      dialogData.value.buttons.confirm.type = 'primary'
      dialogData.value.dialogType = 'info'
    }

    if (actionType.value === 'unsaved_changes') {
      dialogData.value.buttons.confirm.type = 'warning'
      dialogData.value.dialogType = 'warning'
    }
  }

  const getDialogText = () => {
    const titles = {
      delete: {
        forms: getLabel('are_you_sure_delete_forms'),
        form: getLabel('are_you_sure_delete_form'),
        notification: getLabel('are_you_sure_delete_notification'),
        entry: getLabel('are_you_sure_delete_entry'),
        entries: getLabel('are_you_sure_delete_entries'),
      },
      duplicate: {
        form: getLabel('duplicate_form'),
        notification: getLabel('duplicate_notification'),
      },
      unsaved_changes: {
        forms: getLabel('unsaved_changes'),
        form: getLabel('unsaved_changes'),
        notification: getLabel('unsaved_changes'),
      },
    }

    const subtitles = {
      delete: {
        forms: getLabel('action_cannot_be_undone'),
        form: getLabel('action_cannot_be_undone'),
        notification: getLabel('action_cannot_be_undone'),
        entry: getLabel('action_cannot_be_undone'),
        entries: getLabel('action_cannot_be_undone'),
      },
      duplicate: {
        forms: getLabel('duplicate_form_subtitle'),
        form: getLabel('duplicate_form_subtitle'),
        notification: getLabel('duplicate_notification_subtitle'),
      },
    }

    let titleKey: string = entityType.value
    if (
      entityType.value === 'forms' &&
      Array.isArray(entityId.value) &&
      entityId.value.length === 1
    ) {
      titleKey = 'form'
    }
    if (
      entityType.value === 'entries' &&
      Array.isArray(entityId.value) &&
      entityId.value.length === 1
    ) {
      titleKey = 'entry'
    }

    const text = {
      delete: {
        title: titles.delete[titleKey],
        subtitle: subtitles.delete[titleKey],
        confirmText: getLabel('delete'),
      },
      duplicate: {
        title: titles.duplicate[titleKey],
        subtitle: subtitles.duplicate[titleKey],
        confirmText: getLabel('duplicate'),
      },
      unsaved_changes: {
        title: titles.unsaved_changes[titleKey],
        confirmText: getLabel('leave'),
      },
    }

    type MessageActions = keyof typeof text
    return text[actionType.value as MessageActions]
  }

  const refreshData = async (entityType: string, id?: number) => {
    const stores = {
      forms: useAllForms,
      notification: useNotificationSettingBuilder,
      entry: useAllEntries,
      entries: useAllEntries,
    }

    const store = stores[entityType]?.()

    if (!store) {
      IvyMessage({ message: `Invalid entity type: ${entityType}`, type: 'error' })
      return
    }

    try {
      // Special handling for entries to preserve any selected form filter
      if (entityType === 'entries' || entityType === 'entry') {
        const formBuilderStore = useFormBuilder()
        const route = useRoute()

        let routeFormId: number | undefined = undefined
        const paramVal =
          (route?.params as Record<string, unknown>)?.formId ??
          (route?.params as Record<string, unknown>)?.id
        if (paramVal !== undefined && paramVal !== null) {
          routeFormId = Array.isArray(paramVal)
            ? Number(paramVal[0])
            : Number(paramVal as string | number)
        }

        const formIdToPass =
          id ?? (formBuilderStore.formId ? Number(formBuilderStore.formId) : routeFormId)

        await store.fetchData?.({ formId: formIdToPass, shouldGetCount: true })
      } else {
        if (id !== undefined) {
          await store.fetchData?.(id)
        } else {
          await store.fetchData?.()
        }
      }

      if (entityType === 'forms') {
        const allFormsStore = useAllForms()
        allFormsStore.tableData = [...store.tableData]
      }
      if (entityType === 'entries' || entityType === 'entry') {
        const allEntriesStore = useAllEntries()
        allEntriesStore.tableData = [...store.tableData]
      }
    } catch (error) {
      IvyMessage({
        message: `${getLabel('error_refreshing')} ${entityType}: ${error}`,
        type: 'error',
      })
    }
  }

  const { getLabel } = useLabels()

  const deleteEntities = async (options: DeleteEntityOptions): Promise<boolean> => {
    const { entityType, ids } = options

    const messageEntityType =
      entityType === 'forms' && ids.length === 1
        ? 'form'
        : entityType === 'entries' && ids.length === 1
          ? 'entry'
          : entityType

    if (loadingCallbacks.value.setLoading) {
      loadingCallbacks.value.setLoading(true)
    }

    try {
      dialogVisible.value = false

      const { request } = useApiClient()
      const { error } = await request(`${entityType}/delete`, {
        method: 'POST',
        data: { ids },
      })

      if (!error) {
        IvyMessage({
          message: options.successMessage || getLabel(`${messageEntityType}_deleted`),
          type: 'success',
        })

        await refreshData(entityType)

        // Call onSuccess callback if provided
        if (loadingCallbacks.value.onSuccess) {
          loadingCallbacks.value.onSuccess()
        }

        return true
      } else {
        IvyMessage({
          message:
            options.failedMessage ||
            `${getLabel(`failed_to_delete_${messageEntityType}`)} ${error.message || ''}`,
          type: 'error',
        })
        return false
      }
    } catch (error) {
      IvyMessage({
        message:
          options.errorMessage || `${getLabel(`error_deleting_${messageEntityType}`)} ${error}`,
        type: 'error',
      })
      return false
    } finally {
      if (loadingCallbacks.value.setLoading) {
        loadingCallbacks.value.setLoading(false)
      }
      loadingCallbacks.value = {}
    }
  }

  const route = useRoute()
  const deleteSingleEntity = async (options: DeleteSingleEntityOptions): Promise<boolean> => {
    const { entityType, id } = options

    if (loadingCallbacks.value.setLoading) {
      loadingCallbacks.value.setLoading(true)
    }

    try {
      dialogVisible.value = false

      const { request } = useApiClient()
      const { error } = await request(`${entityType}/delete/${id}/`, {
        method: 'POST',
        data: { id: id },
      })

      if (!error) {
        IvyMessage({
          message: options.successMessage || getLabel(`${entityType}_deleted`),
          type: 'success',
        })

        if (entityType === 'notification') {
          if (route.params.id) {
            const formIdParam = Array.isArray(route.params.id)
              ? route.params.id[0]
              : route.params.id

            const formId = parseInt(formIdParam, 10)

            await refreshData(entityType, formId)
          }
        } else {
          await refreshData(entityType)
        }

        // Call onSuccess callback if provided
        if (loadingCallbacks.value.onSuccess) {
          loadingCallbacks.value.onSuccess()
        }

        return true
      } else {
        IvyMessage({
          message:
            options.failedMessage ||
            `${getLabel(`failed_to_delete_${entityType}`)} ${error.message || ''}`,
          type: 'error',
        })
        return false
      }
    } catch (error) {
      IvyMessage({
        message: options.errorMessage || `${getLabel(`error_deleting_${entityType}`)} ${error}`,
        type: 'error',
      })
      dialogVisible.value = false
      return false
    } finally {
      if (loadingCallbacks.value.setLoading) {
        loadingCallbacks.value.setLoading(false)
      }
      loadingCallbacks.value = {}
    }
  }

  const duplicateSingleEntity = async (options: DuplicateEntityOptions): Promise<boolean> => {
    const { entityType, id } = options

    if (loadingCallbacks.value.setLoading) {
      loadingCallbacks.value.setLoading(true)
    }

    try {
      dialogVisible.value = false

      const { request } = useApiClient()
      const { error } = await request(`${entityType}/duplicate/${id}/`, {
        method: 'POST',
      })

      if (!error) {
        IvyMessage({
          message: options.successMessage || getLabel(`${entityType}_duplicated`),
          type: 'success',
        })

        const refreshEntityType =
          entityType === 'form' ? 'forms' : entityType === 'entry' ? 'entries' : entityType

        if (entityType === 'notification') {
          if (route.params.id) {
            const formIdParam = Array.isArray(route.params.id)
              ? route.params.id[0]
              : route.params.id

            const formId = parseInt(formIdParam, 10)

            await refreshData(refreshEntityType, formId)
          }
        } else {
          await refreshData(refreshEntityType)
        }

        if (successCallback.value) {
          successCallback.value()
        }

        return true
      } else {
        IvyMessage({
          message:
            options.failedMessage ||
            `${getLabel(`failed_to_duplicate_${entityType}`)} ${error.message || ''}`,
          type: 'error',
        })
        return false
      }
    } catch (error) {
      IvyMessage({
        message: options.errorMessage || `${getLabel(`error_duplicating_${entityType}`)} ${error}`,
        type: 'error',
      })
      dialogVisible.value = false
      return false
    } finally {
      if (loadingCallbacks.value.setLoading) {
        loadingCallbacks.value.setLoading(false)
      }
      loadingCallbacks.value = {}
    }
  }

  const confirmAction = async () => {
    switch (actionType.value) {
      case 'delete':
        if (Array.isArray(entityId.value)) {
          await deleteEntities({
            entityType: entityType.value,
            ids: entityId.value,
          })
        } else {
          await deleteSingleEntity({
            entityType: entityType.value,
            id: entityId.value as number,
          })
        }
        break
      case 'duplicate':
        await duplicateSingleEntity({
          entityType: entityType.value,
          id: entityId.value as number,
        })
        break
      case 'unsaved_changes':
        if (pendingNavigationCallback.value) {
          pendingNavigationCallback.value()
          pendingNavigationCallback.value = null
        }
        dialogVisible.value = false
        break
    }
  }

  const onClose = () => {
    actionAllowed.value = true
    dialogData.value = { ...defaultDialogData }
    pendingNavigationCallback.value = null
  }

  const showUnsavedChangesDialog = (navigationCallback: () => void) => {
    handleActionClick(null, null, 'form', 'unsaved_changes', {}, navigationCallback)
  }

  /**
   * Show a custom dialog with specific configuration
   * Used for Pro feature upgrade dialogs and other custom dialogs
   */
  const showDialog = (config: DialogData) => {
    dialogData.value = {
      ...defaultDialogData,
      ...config,
    }
    dialogVisible.value = true
  }

  return {
    dialogVisible,
    dialogLoading,
    actionAllowed,
    dialogData,
    actionType,
    handleActionClick,
    confirmAction,
    refreshData,
    showUnsavedChangesDialog,
    showDialog,
  }
})
