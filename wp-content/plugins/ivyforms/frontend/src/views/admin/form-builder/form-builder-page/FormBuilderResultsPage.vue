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
          :route="menuNavigation.getMenuRoute(item, IVYFORMS_FORM_RESULTS)"
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
    class="ivyforms-form-builder-results-page ivyforms-flex ivyforms-gap-8 ivyforms-p-8"
    :class="{
      'ivyforms-fullscreen-mode': globalState.isFullScreenMode,
    }"
  >
    <FormResultsPagePanel
      :active-index="resultsSubIndex"
      @update:active-index="setResultsSubIndex"
    />
    <FormResultsPageSection :active-index="resultsSubIndex" />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue'
import { useFormBuilder } from '@/stores/useFormBuilder.ts'
import { useGlobalState } from '@/stores/useGlobalState.ts'
import { useRoute, useRouter } from 'vue-router'
import IvyMessage from '@/views/_components/message/ivyMessage'
import { useAllForms } from '@/stores/useAllForms.ts'
import { useLabels } from '@/composables/useLabels'
import {
  IVYFORMS_ADD_FORM,
  IVYFORMS_FORM_SETTING,
  IVYFORMS_EDIT_FORM,
  IVYFORMS_FORM_RESULTS,
} from '@/constants/pages.ts'
import { useMenuNavigation } from '@/composables/useMenuNavigation'
import { useWcagColors } from '@/composables/useWcagColors'

const formBuilderStore = useFormBuilder()
const globalState = useGlobalState()
const allFormsStore = useAllForms()
const resultsSubIndex = ref('entries')
const formOptions = ref([])
const loadingForms = ref(false)
const loading = ref(false)
const route = useRoute()
const router = useRouter()
const menuNavigation = useMenuNavigation()
const { getLabel } = useLabels()

// Computed Properties

// Replace local selectedFormId with computed property
const selectedFormId = computed(() => {
  return formBuilderStore.formId || null
})

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

// Methods

// Function to update sub-index when changing sections
const setResultsSubIndex = (index: string) => {
  resultsSubIndex.value = index
}

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
  if (!formId) return
  await router.push({ path: `/manage/${formId}/results/entries` })
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

    // Only load a default form if there's no formId in the store AND no formId in the route params
    if (!formBuilderStore.formId && !route.params.formId && allFormsStore.tableData.length > 0) {
      const firstFormId = allFormsStore.tableData[0].id.toString()
      formBuilderStore.formId = firstFormId
      await formBuilderStore.loadForm(firstFormId)
      if (route.params.formId !== firstFormId) {
        await router.push({ path: `/manage/${firstFormId}/results/entries` })
      }
    }
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

// Load form data if an ID is provided
onMounted(async () => {
  await fetchAllForms() // Fetch all forms for the dropdown
  if (!formBuilderStore.formId) {
    formBuilderStore.counterFields = 0
  }
})

// Initialize WCAG colors
const { startWatching } = useWcagColors()
startWatching()

// Watchers

// Watch for changes in the route params to update formId in formBuilderStore
watch(
  () => route.params.formId,
  async (newFormId) => {
    if (newFormId && newFormId !== formBuilderStore.formId) {
      formBuilderStore.formId = String(newFormId)
      await formBuilderStore.loadForm(String(newFormId))
    }
  },
  { immediate: true },
)

// Watch for changes in formBuilderStore.name to update options
watch(
  () => formBuilderStore.name,
  (newName) => {
    if (formBuilderStore.formId && newName !== null && newName !== undefined) {
      // Update the form name in the options list when it changes
      const formIndex = formOptions.value.findIndex(
        (option) => option.value === formBuilderStore.formId,
      )

      if (formIndex !== -1) {
        formOptions.value[formIndex].label = newName
      }
    }
  },
)

// Watch for changes in the route hash to update the results sub-index
watch(
  () => route.hash,
  (newHash) => {
    if (newHash.includes('/entries')) {
      resultsSubIndex.value = 'entries'
    } else if (newHash.includes('/insights')) {
      resultsSubIndex.value = 'insights'
    } else if (newHash.includes('/report')) {
      resultsSubIndex.value = 'report'
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
.ivyforms-form-builder-results-page {
  height: calc(100vh - 116px);
  overflow: hidden;
  transition: height 0.3s ease-in-out;

  &.ivyforms-fullscreen-mode {
    height: calc(100vh - 80px);
  }
}
</style>
