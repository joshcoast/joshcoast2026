<template>
  <PageHeader>
    <template #left>
      <IvyFilter
        v-model="selectedFormId"
        size="s"
        type="border"
        placeholder="Select a form"
        :options="formOptions"
        :loading="loadingForms || loading"
        @change="onFormChange"
      />
    </template>
    <template #center>
      <IvyMenu :mode="mode" :ellipsis="false" router>
        <IvyHeaderButton
          v-for="item in menuItems"
          :key="item.index"
          :label="item.label"
          :name="item.index"
          type="tonal"
          :is-active="menuNavigation.isActive(item.routeName, item.index)"
          :route="menuNavigation.getMenuRoute(item, IVYFORMS_FORM_SETTING)"
          @click="menuNavigation.onMenuClick(item, menuNavigation.isActive)"
        />
      </IvyMenu>
    </template>
    <template #right>
      <!-- Preview Button -->
      <IvyTooltip :content="getLabel('preview_form')" placement="bottom" theme="inverted">
        <IvyButtonAction
          priority="tertiary"
          icon-start="eye-opened"
          icon-start-type="outline"
          @click="previewForm"
        />
      </IvyTooltip>
      <PageHeaderDivider />
    </template>
  </PageHeader>

  <div
    class="ivyforms-form-builder-settings-page ivyforms-flex ivyforms-gap-8 ivyforms-p-8"
    :class="{
      'ivyforms-fullscreen-mode': globalState.isFullScreenMode,
    }"
  >
    <FormSettingsPagePanel :key="selectedFormId" :active-index="settingsSubIndex" />
    <FormSettingsPageSection
      :active-index="settingsSubIndex"
      :is-adding-notification="isAddingNotification"
    />
  </div>

  <IvyShortDialog align-center width="342px" />
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, watch, computed } from 'vue'
import { useFormBuilder } from '@/stores/useFormBuilder.ts'
import { useGlobalState } from '@/stores/useGlobalState.ts'
import { useRoute, useRouter } from 'vue-router'
import IvyMessage from '@/views/_components/message/ivyMessage'
import { useAllForms } from '@/stores/useAllForms.ts'
import { useNotificationSettingBuilder } from '@/stores/useNotificationSettingBuilder.ts'
import { useConfirmationSettingBuilder } from '@/stores/useConfirmationSettingBuilder.ts'
import { useLabels } from '@/composables/useLabels'
import { useUnsavedChangesStore } from '@/stores/useUnsavedChangesStore'
import IvyShortDialog from '@/views/_components/sub-dialog/IvyShortDialog.vue'
import type { RouteLocationNormalizedLoaded } from 'vue-router'
import {
  IVYFORMS_ADD_FORM,
  IVYFORMS_FORM_SETTING,
  IVYFORMS_EDIT_FORM,
  IVYFORMS_FORM_RESULTS,
} from '@/constants/pages.ts'
import { useMenuNavigation } from '@/composables/useMenuNavigation'
import { updateFormDropdownOption } from '@/composables/useFormDropdownOptions'
import { useWcagColors } from '@/composables/useWcagColors'

const { getLabel } = useLabels()
const { startWatching } = useWcagColors()
startWatching()

const formBuilderStore = useFormBuilder()
const globalState = useGlobalState()
const allFormsStore = useAllForms()
const notificationStore = useNotificationSettingBuilder()
const confirmationStore = useConfirmationSettingBuilder()
const unsavedChangesStore = useUnsavedChangesStore()
const settingsSubIndex = ref('general')
const isAddingNotification = ref(false)
let adminMenuHandler: ((e: Event) => void) | null = null
const mode = ref('horizontal')
const route = useRoute()
const router = useRouter()
const menuNavigation = useMenuNavigation()

// Replace local selectedFormId with computed property
const selectedFormId = computed(() => {
  return formBuilderStore.formId || null
})

const formOptions = ref([])
const loadingForms = ref(false)
const loading = ref(false)
// Define menu items
const menuItems = computed(() => [
  {
    label: getLabel('build'),
    index: 'build',
    routeName: formBuilderStore.isEditing ? IVYFORMS_EDIT_FORM : IVYFORMS_ADD_FORM,
  },
  { label: getLabel('settings'), index: 'settings', routeName: IVYFORMS_FORM_SETTING },
  { label: getLabel('results'), index: 'results', routeName: IVYFORMS_FORM_RESULTS },
])

const previewForm = () => {
  if (!formBuilderStore.formId) {
    IvyMessage({
      message: getLabel('first_save_form'),
      type: 'error',
    })
  }
  window.open(
    `${window.wpIvyUrls.siteURL}?ivyforms_preview=${formBuilderStore.formId}&_wpNonce=${window.wpIvyApiSettings.nonce}`,
    '_blank',
  )
}

const onFormChange = async (formId: string) => {
  if (!formId) {
    formBuilderStore.resetForm()

    // Also clear the notifications and confirmations
    notificationStore.allNotifications = []
    notificationStore.formId = null
    confirmationStore.allConfirmations = []
    confirmationStore.formId = null

    // Update router to general settings page when form is cleared
    await router.push({ path: '/' })
    return
  }

  loading.value = true
  try {
    // Load the selected form
    await formBuilderStore.loadForm(formId)
    // Extract the current subtab from the route
    const currentPath = router.currentRoute.value.path + router.currentRoute.value.hash
    const settingsMatch = currentPath.match(/\/settings\/(\w+)/)
    const subtab = settingsMatch ? settingsMatch[1] : null
    if (subtab) {
      await router.push({ path: `/manage/${formId}/settings/${subtab}` })
      settingsSubIndex.value = subtab
      IvyMessage({
        message: getLabel('form_loaded'),
        type: 'success',
      })
    } else {
      await router.push({ path: `/manage/${formId}` })
      IvyMessage({
        message: getLabel('form_loaded'),
        type: 'success',
      })
    }
  } catch (error) {
    console.error(getLabel('failed_to_load_form'), error)
    IvyMessage({
      message: `${getLabel('failed_to_load_form')} ${error}`,
      type: 'error',
    })
  } finally {
    loading.value = false
  }
}

// Function to fetch all forms
const fetchAllForms = async () => {
  loadingForms.value = true
  try {
    await allFormsStore.fetchData({
      page: 1,
      perPage: 100, // Get a reasonable number of forms
    })
    // Transform form data into options format for IvyFilter
    formOptions.value = allFormsStore.tableData.map((form) => ({
      value: form.id,
      labelId: `${getLabel('id')}: ${form.id}`,
      label: form.name || `Form #${form.id}`,
    }))

    // No need to set selectedFormId since it's now a computed property
  } catch (error) {
    console.error(getLabel('failed_to_load_form'), error)
    IvyMessage({
      message: `${getLabel('failed_to_fetch_form')} ${error}`,
      type: 'error',
    })
  } finally {
    loadingForms.value = false
  }
}

function resolveSettingsSubIndexFromRoute(route: RouteLocationNormalizedLoaded) {
  // Check for confirmationId and notificationId in params first
  if (route.params.confirmationId) {
    settingsSubIndex.value = 'confirmations'
    isAddingNotification.value = false
    return
  }
  if (route.params.notificationId) {
    settingsSubIndex.value = 'notifications'
    isAddingNotification.value = true
    return
  }
  // Fallback to route name and path checks
  const routeName = route.name ? route.name.toString() : ''
  if (routeName.includes('notification')) {
    settingsSubIndex.value = 'notifications'
    isAddingNotification.value = true
  } else if (routeName.includes('confirmation')) {
    settingsSubIndex.value = 'confirmations'
    isAddingNotification.value = false
  } else if (routeName.includes('general') || routeName.includes('settings')) {
    settingsSubIndex.value = 'general'
    isAddingNotification.value = false
  } else {
    const fullPath = route.path + route.hash
    if (fullPath.includes('/notification')) {
      settingsSubIndex.value = 'notifications'
      isAddingNotification.value = true
    } else if (fullPath.includes('/confirmation')) {
      settingsSubIndex.value = 'confirmations'
      isAddingNotification.value = false
    } else {
      settingsSubIndex.value = 'general'
      isAddingNotification.value = false
    }
  }
}

// Load form data if an ID is provided
onMounted(async () => {
  await fetchAllForms() // Fetch all forms for the dropdown

  // Extract form ID from route parameters
  const routeFormId = route.params.formId
  const formIdParam = Array.isArray(routeFormId) ? routeFormId[0] : routeFormId

  if (formIdParam && !formBuilderStore.formId) {
    loading.value = true
    try {
      await formBuilderStore.loadForm(formIdParam)
      updateFormDropdownOption(formOptions)
    } catch (error) {
      console.error(getLabel('failed_to_load_form'), error)
      IvyMessage({
        message: `${getLabel('failed_to_load_form')} ${error}`,
        type: 'error',
      })
    } finally {
      loading.value = false
    }
  } else {
    updateFormDropdownOption(formOptions)
  }

  if (!formBuilderStore.formId) {
    formBuilderStore.counterFields = 0
  }

  // Set formId from route params on mount
  if (route.params.formId) {
    formBuilderStore.formId = Array.isArray(route.params.formId)
      ? route.params.formId[0]
      : route.params.formId
  }
  // Set confirmationId and notificationId if present
  if (route.params.confirmationId) {
    confirmationStore.id = route.params.confirmationId
      ? Number(
          Array.isArray(route.params.confirmationId)
            ? route.params.confirmationId[0]
            : route.params.confirmationId,
        )
      : null
  }
  if (route.params.notificationId) {
    notificationStore.id = route.params.notificationId
      ? Number(
          Array.isArray(route.params.notificationId)
            ? route.params.notificationId[0]
            : route.params.notificationId,
        )
      : null
  }

  // Set the correct settings tab based on the current route on initial load
  resolveSettingsSubIndexFromRoute(route)

  // Intercept WP admin menu (all entities)
  const adminMenu = document.getElementById('adminmenu')
  if (adminMenu) {
    adminMenuHandler = (e: Event) => {
      unsavedChangesStore.handleAdminMenuClick(e)
    }
    adminMenu.addEventListener('click', adminMenuHandler, true)
  }
})

// Cleanup admin menu listener - must be registered synchronously in setup
onBeforeUnmount(() => {
  const adminMenu = document.getElementById('adminmenu')
  if (adminMenu && adminMenuHandler) {
    adminMenu.removeEventListener('click', adminMenuHandler, true)
  }
})

// Watch for changes in formBuilderStore.name to update options and ensure current form is in options
watch(
  () => formBuilderStore.name,
  (newName) => {
    if (formBuilderStore.formId && newName !== null && newName !== undefined) {
      const formIndex = formOptions.value.findIndex(
        (option) => option.value === formBuilderStore.formId,
      )
      if (formIndex !== -1) {
        formOptions.value[formIndex].label = newName
      } else {
        updateFormDropdownOption(formOptions)
      }
    }
  },
)

// Watch for changes in the route path and hash to update the settings sub-index
watch(
  () => [route.path, route.hash, route.name],
  () => {
    resolveSettingsSubIndexFromRoute(route)
  },
  { immediate: true },
)

// Watch for changes in route params to update formId, confirmationId, and notificationId
watch(
  () => route.params,
  (params) => {
    // Update formId when route params change
    if (params.formId) {
      formBuilderStore.formId = Array.isArray(params.formId) ? params.formId[0] : params.formId
    }
    // Update confirmationId and notificationId when route params change
    if (params.confirmationId) {
      confirmationStore.id = params.confirmationId
        ? Number(
            Array.isArray(params.confirmationId) ? params.confirmationId[0] : params.confirmationId,
          )
        : null
    }
    if (params.notificationId) {
      notificationStore.id = params.notificationId
        ? Number(
            Array.isArray(params.notificationId) ? params.notificationId[0] : params.notificationId,
          )
        : null
    }
  },
  { immediate: true },
)
</script>

<style lang="scss" scoped>
.ivyforms-button__action {
  &--update,
  &--save {
    min-width: 83px;
    gap: 0;
  }
}
.ivyforms-form-builder-settings-page {
  height: calc(100vh - 116px);
  overflow: hidden;
  transition: height 0.3s ease-in-out;

  &.ivyforms-fullscreen-mode {
    height: calc(100vh - 80px);
  }
}
</style>
