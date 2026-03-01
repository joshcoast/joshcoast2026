<template>
  <PageHeader>
    <template #left>
      <IvyFilter
        v-model="selectedFormId"
        size="s"
        type="border"
        clearable
        :placeholder="getLabel('select_form')"
        :options="formOptions"
        :loading="loadingForms || loading"
        @change="onFormChange"
      />
      <IvyMenu v-if="isFullScreenMode" mode="horizontal" :ellipsis="false">
        <IvyHeaderButton
          v-for="item in menuItems"
          :key="item.index"
          :label="item.label"
          :name="item.index"
          :is-active="activeIndex === item.index"
          type="tonal"
          @click="handleMenuClick(item.index)"
        />
      </IvyMenu>
    </template>
    <PageHeaderDivider />
  </PageHeader>

  <div class="ivyforms-all-entries-page ivyforms-flex ivyforms-gap-8 ivyforms-p-8">
    <AllEntriesPageSection />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, provide, computed } from 'vue'
import { useLabels } from '@/composables/useLabels'
import { useAllForms } from '@/stores/useAllForms.ts'
import { useFormBuilder } from '@/stores/useFormBuilder.ts'
import IvyMessage from '@/views/_components/message/ivyMessage.ts'
import { useGlobalState } from '@/stores/useGlobalState.ts'
import { useNavigation } from '@/composables/useNavigation'
import {
  IVYFORMS_ALL_FORMS,
  IVYFORMS_ENTRIES,
  IVYFORMS_GLOBAL_SETTINGS,
  IVYFORMS_INTEGRATIONS,
} from '@/constants/pages.ts'
import { useWcagColors } from '@/composables/useWcagColors'

const { getLabel } = useLabels()
const { startWatching } = useWcagColors()
startWatching()

const selectedFormId = ref<number | null>()
const formOptions = ref<Array<{ label: string; value: number }>>([])
const loadingForms = ref<boolean>(false)
const loading = ref<boolean>(false)
const allFormsStore = useAllForms()
const formBuilderStore = useFormBuilder()
const globalState = useGlobalState()
const isFullScreenMode = computed(() => globalState.isFullScreenMode)
const { navigateToAllForms, navigateToEntries, navigateToSettings, navigateToIntegrations } =
  useNavigation()

// Menu related variables
const activeIndex = ref(IVYFORMS_ENTRIES)

const menuItems = computed(() => [
  {
    label: getLabel('all_forms'),
    index: IVYFORMS_ALL_FORMS,
    routeName: IVYFORMS_ALL_FORMS,
  },
  {
    label: getLabel('entries'),
    index: IVYFORMS_ENTRIES,
    routeName: IVYFORMS_ENTRIES,
  },
  {
    label: getLabel('integrations'),
    index: IVYFORMS_INTEGRATIONS,
  },
  { label: getLabel('settings'), index: IVYFORMS_GLOBAL_SETTINGS },
])
const title: string = getLabel('all_entries')
provide('pageTitle', title)

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
const onFormChange = (formId: number) => {
  selectedFormId.value = formId
  formBuilderStore.formId = String(formId)
}

const handleMenuClick = (index: string) => {
  if (index === activeIndex.value) return
  activeIndex.value = index
  if (index === IVYFORMS_ALL_FORMS) {
    navigateToAllForms()
  } else if (index === IVYFORMS_ENTRIES) {
    navigateToEntries()
  } else if (index === IVYFORMS_GLOBAL_SETTINGS) {
    navigateToSettings()
  } else if (index === IVYFORMS_INTEGRATIONS) {
    navigateToIntegrations()
  }
}

onMounted(() => {
  fetchAllForms()
})
</script>

<style lang="scss">
.ivyforms-all-entries-page {
  height: calc(100vh - 116px);
  overflow: hidden;
  transition: height 0.3s ease-in-out;

  &.ivyforms-fullscreen-mode {
    height: calc(100vh - 80px);
  }
}
</style>
