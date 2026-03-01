import { useRouter } from 'vue-router'
import { useUnsavedChangesStore } from '@/stores/useUnsavedChangesStore'
import { useActionEntityStore } from '@/stores/actionEntityStore.ts'
import { useFormBuilder } from '@/stores/useFormBuilder.ts'
import { useConfirmationSettingBuilder } from '@/stores/useConfirmationSettingBuilder.ts'
import {
  IVYFORMS_EDIT_NOTIFICATIONS,
  IVYFORMS_NEW_NOTIFICATIONS,
  IVYFORMS_SETTINGS,
  IVYFORMS_ADD_FORM,
  IVYFORMS_EDIT_FORM,
} from '@/constants/pages.ts'

export function useMenuNavigation() {
  const router = useRouter()
  const unsavedChangesStore = useUnsavedChangesStore()
  const actionEntityStore = useActionEntityStore()
  const formBuilderStore = useFormBuilder()
  const confirmationStore = useConfirmationSettingBuilder()

  // Helper to check if any entity has unsaved changes
  function hasAnyUnsavedChanges() {
    return (
      unsavedChangesStore.isDirty('formBuilder') ||
      unsavedChangesStore.isDirty('confirmationBuilder') ||
      unsavedChangesStore.isDirty('notificationBuilder')
    )
  }

  // Helper to clear all dirty flags
  function clearAllDirtyFlags() {
    unsavedChangesStore.markClean('formBuilder')
    unsavedChangesStore.markClean('confirmationBuilder')
    unsavedChangesStore.markClean('notificationBuilder')
  }

  async function getConfirmationIdForForm(formId) {
    // Try to get confirmationId from store
    if (confirmationStore.id && confirmationStore.formId == formId) {
      return confirmationStore.id
    }
    // Try to fetch confirmation for this form
    await confirmationStore.fetchAllConfirmations(formId)
    if (confirmationStore.id) {
      return confirmationStore.id
    }
    // If still not found, create one
    confirmationStore.formId = formId
    await confirmationStore.createConfirmation()
    return confirmationStore.id
  }

  async function getMenuRoute(item, fallbackRouteName = null) {
    if (hasAnyUnsavedChanges()) {
      return item.routeName
        ? { name: fallbackRouteName, params: { id: formBuilderStore.formId } }
        : undefined
    }

    if (item.routeName === 'form-confirmation') {
      const formId = formBuilderStore.formId
      const confirmationId = await getConfirmationIdForForm(formId)
      return { name: item.routeName, params: { formId, confirmationId } }
    }
    // Notifications table only needs formId
    if (item.routeName === 'form-notifications') {
      return { name: item.routeName, params: { formId: formBuilderStore.formId } }
    }

    // Default: use id for other routes
    return item.routeName
      ? { name: item.routeName, params: { id: formBuilderStore.formId } }
      : undefined
  }

  async function onMenuClick(item, isActive) {
    if (isActive(item.routeName, item.index)) return

    const doNavigate = async () => {
      // Handle items with sub-items (like Integrations)
      if (item.subItems && item.subItems.length > 0 && item.autoNavigateToFirst) {
        const firstSubItem = item.subItems[0]
        const path = `/manage/${formBuilderStore.formId}/settings/${item.index}${firstSubItem.index}`
        await router.push(path)
        return
      }

      if (item.routeName === 'form-confirmation') {
        const formId = formBuilderStore.formId
        const confirmationId = await getConfirmationIdForForm(formId)
        await router.push({ name: item.routeName, params: { formId, confirmationId } })
        return
      }
      if (item.routeName === 'form-notifications') {
        await router.push({ name: item.routeName, params: { formId: formBuilderStore.formId } })
        return
      }
      // TODO integrations route
      if (item.routeName === 'form-integrations') {
        await router.push({
          name: item.routeName,
          params: { formId: formBuilderStore.formId, integration: 'wpdatatables' },
        })
        return
      }
      // Default: use id for other routes
      await router.push({ name: item.routeName, params: { formId: formBuilderStore.formId } })
    }

    if (hasAnyUnsavedChanges()) {
      actionEntityStore.showUnsavedChangesDialog(async () => {
        clearAllDirtyFlags()
        await doNavigate()
      })
    } else {
      await doNavigate()
    }
  }

  function isActive(routeName, index) {
    const matched = router.currentRoute.value.matched
    const currentPath = router.currentRoute.value.path

    // Check if we're on the "build" tab (either add or edit form)
    if (index === 'build') {
      return matched.some(({ name }) => name === IVYFORMS_ADD_FORM || name === IVYFORMS_EDIT_FORM)
    }

    // Check if we're on an integrations sub-route (must include the full path)
    if (index === 'integrations') {
      return currentPath.includes('/settings/integrations/')
    }

    if (routeName && matched.some(({ name }) => name === routeName)) {
      return true
    }
    if (matched.length > 1 && index === 'settings') {
      const parent = matched[matched.length - 2]
      if (parent && parent.name === IVYFORMS_SETTINGS) {
        return true
      }
    }
    if (
      index === 'notifications' &&
      matched.some(
        ({ name }) => name === IVYFORMS_NEW_NOTIFICATIONS || name === IVYFORMS_EDIT_NOTIFICATIONS,
      )
    ) {
      return true
    }
    return false
  }

  return {
    getMenuRoute,
    onMenuClick,
    isActive,
  }
}
