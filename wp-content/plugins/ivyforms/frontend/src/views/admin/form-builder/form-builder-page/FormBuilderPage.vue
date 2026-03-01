<template>
  <PageHeader>
    <template #left>
      <IvyFilter
        v-model="selectedFormId"
        size="s"
        type="border"
        :placeholder="getLabel('select_form')"
        :options="formOptions"
        :loading="loadingForms || loading"
        @change="onFormChange"
      />
    </template>
    <template #center>
      <IvyMenu mode="horizontal" :ellipsis="false" router>
        <IvyHeaderButton
          v-for="item in menuItems"
          :key="item.index"
          :label="item.label"
          :name="item.index"
          type="tonal"
          :is-active="menuNavigation.isActive(item.routeName, item.index)"
          :route="menuNavigation.getMenuRoute(item, IVYFORMS_ADD_FORM)"
          @click="menuNavigation.onMenuClick(item, menuNavigation.isActive)"
        />
      </IvyMenu>
    </template>
    <template #right>
      <!--    Preview Button -->

      <IvyTooltip :content="getLabel('preview_form')" placement="bottom" theme="inverted">
        <IvyButtonAction
          priority="tertiary"
          icon-start="eye-opened"
          icon-start-type="outline"
          @click="previewForm"
        />
      </IvyTooltip>
      <!-- Save Button -->

      <IvyButtonAction
        v-if="formBuilderStore.isEditing"
        :class="['ivyforms-button__action ivyforms-button__action--update']"
        priority="primary"
        :loading="loading"
        @click="updateForm"
      >
        <template v-if="!loading">{{ getLabel('update') }}</template>
      </IvyButtonAction>
      <IvyButtonAction
        v-else
        :class="['ivyforms-button__action ivyforms-button__action--save']"
        priority="primary"
        :loading="loading"
        @click="saveForm"
      >
        <template v-if="!loading">{{ getLabel('save') }}</template>
      </IvyButtonAction>
      <PageHeaderDivider />
    </template>
  </PageHeader>
  <div
    class="ivyforms-form-builder-page ivyforms-flex ivyforms-gap-8 ivyforms-p-8"
    :class="{
      'ivyforms-form-builder-page--fullscreen-mode': globalState.isFullScreenMode,
      'ivyforms-form-builder-page--template-open': isTemplateSelectionVisible,
    }"
  >
    <FormBuilderPagePanel />
    <FormBuilderPageSection />
    <!-- Template Selection Popover -->
    <IvyFormsTemplateSelection
      v-model:visible="isTemplateSelectionVisible"
      @form-created="handleFormCreated"
      @close="handleCloseTemplateSelection"
    />
  </div>

  <ProUpgradeDialog />
  <IvyShortDialog align-center width="342px" />
</template>

<script setup lang="ts">
import ProUpgradeDialog from '@/views/_components/dialog/ProUpgradeDialog.vue'
import IvyShortDialog from '@/views/_components/sub-dialog/IvyShortDialog.vue'
import { ref, onMounted, computed, watch, onBeforeUnmount } from 'vue'
import { useFormBuilder } from '@/stores/useFormBuilder.ts'
import IvyMessage from '@/views/_components/message/ivyMessage.ts'
import { useGlobalState } from '@/stores/useGlobalState.ts'
import IvyButtonAction from '@/views/_components/button/IvyButtonAction.vue'
import { useAllForms } from '@/stores/useAllForms.ts'
import { useNotificationSettingBuilder } from '@/stores/useNotificationSettingBuilder.ts'
import { useConfirmationSettingBuilder } from '@/stores/useConfirmationSettingBuilder.ts'
import { useRouter } from 'vue-router'
import { useLabels } from '@/composables/useLabels'
import { useUnsavedChangesStore } from '@/stores/useUnsavedChangesStore'
import {
  IVYFORMS_ADD_FORM,
  IVYFORMS_FORM_SETTING,
  IVYFORMS_EDIT_FORM,
  IVYFORMS_FORM_RESULTS,
} from '@/constants/pages.ts'
import { useMenuNavigation } from '@/composables/useMenuNavigation'
import IvyFormsTemplateSelection from '@/views/admin/forms/forms-page/IvyFormsTemplateSelection.vue'
import { updateFormDropdownOption } from '@/composables/useFormDropdownOptions'
import { useWcagColors } from '@/composables/useWcagColors'
import { useNavigation } from '@/composables/useNavigation'

const { getLabel } = useLabels()
const { navigateToAllForms } = useNavigation()
const { startWatching } = useWcagColors()
startWatching()

// Initialize router
const router = useRouter()
// Template selection state
const isTemplateSelectionVisible = ref(false)

const unsavedChangesStore = useUnsavedChangesStore()

const menuItems = computed(() => [
  {
    label: getLabel('build'),
    index: 'build',
    routeName: formBuilderStore.isEditing ? IVYFORMS_EDIT_FORM : IVYFORMS_ADD_FORM,
  },
  { label: getLabel('settings'), index: 'settings', routeName: IVYFORMS_FORM_SETTING },
  { label: getLabel('results'), index: 'results', routeName: IVYFORMS_FORM_RESULTS },
])

const formOptions = ref([])
const loadingForms = ref(false)
const loading = ref(false)
const formBuilderStore = useFormBuilder()
const globalState = useGlobalState()
const allFormsStore = useAllForms()
const notificationStore = useNotificationSettingBuilder()
const confirmationStore = useConfirmationSettingBuilder()
const menuNavigation = useMenuNavigation()

// Replace local selectedFormId with computed property
const selectedFormId = computed(() => {
  return formBuilderStore.formId
})

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
    console.error(getLabel('failed_to_fetch_form'), error)
    IvyMessage({
      message: `${getLabel('failed_to_fetch_form')} ${error}`,
      type: 'error',
    })
  } finally {
    loadingForms.value = false
  }
}

// Function to handle form selection change
const proceedFormChange = async (formId: string) => {
  if (!formId) {
    formBuilderStore.resetForm()

    // Also clear the notifications and confirmations
    notificationStore.allNotifications = []
    notificationStore.formId = null
    confirmationStore.allConfirmations = []
    confirmationStore.formId = null

    // Reset URL to base path
    await router.push({ path: '/' })
    return
  }
  loading.value = true
  try {
    // Load the selected form
    await formBuilderStore.loadForm(formId)
    // Update the route to include this form's ID based on current route
    if (
      router.currentRoute.value.path.includes('/settings/general/') ||
      router.currentRoute.value.path.includes('/settings/notification') ||
      router.currentRoute.value.path.includes('/settings/confirmation')
    ) {
      await router.push({ path: `/manage/${formId}/settings/general` })
      IvyMessage({
        message: getLabel('form_loaded'),
        type: 'success',
      })
    } else if (router.currentRoute.value.path.includes('/settings/notification/')) {
      await notificationStore.fetchData(parseInt(formId))
      await router.push({ path: `/settings/notification/${formId}` })
      IvyMessage({
        message: getLabel('notification_loaded'),
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

const onFormChange = async (formId: string) => {
  unsavedChangesStore.confirmIfDirty(() => {
    proceedFormChange(formId)
  })
}

// Function to Save (New Form)
const saveForm = async () => {
  loading.value = true
  try {
    await formBuilderStore.saveForm()
    await fetchAllForms()
    updateFormDropdownOption(formOptions)
  } finally {
    loading.value = false
  }
}

// Function to Update (Existing Form)
const updateForm = async () => {
  loading.value = true
  try {
    await formBuilderStore.updateForm()
    await fetchAllForms()
    updateFormDropdownOption(formOptions)
  } finally {
    loading.value = false
  }
}

// Function to preview the form
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

// Store reference to admin menu handler for cleanup
let adminMenuHandler: ((e: Event) => void) | null = null

// Load form data if an ID is provided
onMounted(async () => {
  await fetchAllForms() // Fetch all forms for the dropdown
  const route = router.currentRoute.value
  let formIdParam = route.params.formId
  if (Array.isArray(formIdParam)) {
    formIdParam = formIdParam[0]
  }
  if (formIdParam) {
    isTemplateSelectionVisible.value = false
    formBuilderStore.formId = formIdParam
    // loadForm accepts a single argument (form id) in the store types — call with one argument
    await formBuilderStore.loadForm(formIdParam)
    updateFormDropdownOption(formOptions)
  } else {
    isTemplateSelectionVisible.value = true
    updateFormDropdownOption(formOptions)
  }
  globalState.setCurrentPage('FormBuilderPage')

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

// Watch for route changes to handle browser back/forward navigation
watch(
  () => router.currentRoute.value.params.formId,
  (newFormId) => {
    if (!newFormId) {
      // If we navigate back to base URL without form ID, show template selection modal
      isTemplateSelectionVisible.value = true
      formBuilderStore.resetForm()
    }
  },
  { immediate: false },
)

// Watch for changes in formBuilderStore.name to update options
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

const handleFormCreated = async (formId?: number) => {
  try {
    await fetchAllForms()
    updateFormDropdownOption(formOptions)

    if (formId && formBuilderStore.formId && formBuilderStore.name) {
      const existingFormIndex = formOptions.value.findIndex(
        (option) => option.value === formBuilderStore.formId,
      )

      if (existingFormIndex === -1) {
        formOptions.value.push({
          value: formBuilderStore.formId,
          labelId: `${getLabel('id')}: ${formBuilderStore.formId}`,
          label: formBuilderStore.name,
        })
      } else {
        formOptions.value[existingFormIndex].label = formBuilderStore.name
      }

      // Update the global page title
      globalState.setPageTitle(formBuilderStore.name)
    }

    isTemplateSelectionVisible.value = false
  } catch (error) {
    console.error(getLabel('failed_to_load_form'), error)
    isTemplateSelectionVisible.value = false
  }
}

const handleCloseTemplateSelection = () => {
  isTemplateSelectionVisible.value = false
  navigateToAllForms()
}
</script>
<style lang="scss" scoped>
.ivyforms-button__action {
  &--update,
  &--save {
    min-width: 83px;
    gap: 0;

    :deep(.ivyforms-button-action),
    :deep(.ivyforms-button-action.is-loading) {
      min-width: 83px;
      gap: 0;
      width: 100%;
    }
  }
}

.ivyforms-form-builder-page {
  height: calc(100vh - 116px);
  overflow: hidden;
  display: flex;
  transition: height 0.3s ease-in-out;

  &--fullscreen-mode {
    height: calc(100vh - 80px);
  }

  &.ivyforms-form-builder-page--template-open {
    overflow: visible;
  }
}
</style>
