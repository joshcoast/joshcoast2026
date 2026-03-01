<template>
  <PageHeader>
    <template #center>
      <div
        class="ivyforms-header__navigation ivyforms-flex ivyforms-align-items-center ivyforms-justify-content-center ivyforms-gap-8"
      >
        <IvyButtonAction
          icon-only
          icon-start="chevron-left"
          icon-start-category="arrows"
          icon-start-type="outline"
          priority="tertiary"
          type="fill"
          size="s"
          :disabled="currentEntryIndex <= 1"
          @click="navigateToPreviousEntry"
        />
        <IvyButtonAction
          icon-only
          icon-start="chevron-right"
          icon-start-category="arrows"
          icon-start-type="outline"
          priority="tertiary"
          type="fill"
          size="s"
          :disabled="currentEntryIndex >= totalEntries"
          @click="navigateToNextEntry"
        />
        <span
          class="ivyforms-header__navigation-text ivyforms-flex ivyforms-align-items-center ivyforms-justify-content-center regular-14"
        >
          <template v-if="loading">
            <div class="ivyforms-count-loader"></div>
          </template>
          <template v-else>
            {{ currentEntryIndex }}/{{ totalEntries }} {{ getLabel('entries') }}
          </template>
        </span>
      </div>
    </template>
  </PageHeader>

  <div class="ivyforms-entry-page ivyforms-flex ivyforms-gap-8 ivyforms-p-8">
    <EntryPageSection />
    <EntryDetailsPagePanel />
    <IvyContextMenu />
    <IvyShortDialog show-close align-center type="error" width="342px" />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, provide, watch } from 'vue'
import { useRoute, useRouter, onBeforeRouteLeave } from 'vue-router'
import { useEntry } from '@/stores/useEntry.ts'
import { useFormBuilder } from '@/stores/useFormBuilder.ts'
import { useAllEntries } from '@/stores/useAllEntries.ts'
import { useLabels } from '@/composables/useLabels.ts'
import { IVYFORMS_RESULTS_ENTRY_DETAILS, IVYFORMS_ENTRY_DETAILS } from '@/constants/pages.ts'
import { useWcagColors } from '@/composables/useWcagColors'

const { getLabel } = useLabels()
const { startWatching } = useWcagColors()
startWatching()
const route = useRoute()
const router = useRouter()
const entryStore = useEntry()
const formBuilderStore = useFormBuilder()
const allEntriesStore = useAllEntries()
const isResultsEntryRoute = computed(() => {
  return route.path.includes('/results/entries') && route.params.formId
})

// Entry and form IDs from route
const entryId = computed(() => {
  return isResultsEntryRoute.value ? route.params.entryId : route.params.id
})
const formId = computed(() => {
  return isResultsEntryRoute.value
    ? route.params.formId
    : entryStore.entry?.formId || formBuilderStore.formId
})
const entry = ref(null)
const entryFields = ref(null)
const entriesWithSameFormId = ref([])
const currentEntryIndex = ref(1)
const totalEntries = computed(() => entriesWithSameFormId.value.length)
const loading = ref(false)
const formName = computed(() => {
  return formBuilderStore.name ? formBuilderStore.name : ''
})

const updateEntryData = async (newEntryId: number) => {
  loading.value = true
  try {
    await entryStore.fetchEntry(newEntryId)
    entry.value = entryStore.entry
    entryFields.value = entryStore.entryFields
    currentEntryIndex.value =
      entriesWithSameFormId.value.findIndex((entry) => Number(entry.id) === Number(newEntryId)) + 1
  } finally {
    loading.value = false
  }
}

const navigateToPreviousEntry = async () => {
  if (currentEntryIndex.value > 1) {
    currentEntryIndex.value--
    const previousEntryId = entriesWithSameFormId.value[currentEntryIndex.value - 1].id
    if (isResultsEntryRoute.value) {
      await router.push({
        name: IVYFORMS_RESULTS_ENTRY_DETAILS,
        params: { formId: formId.value, entryId: previousEntryId },
      })
    } else {
      await router.push({ name: IVYFORMS_ENTRY_DETAILS, params: { id: previousEntryId } })
    }
    await updateEntryData(Number(previousEntryId))
  }
}

const navigateToNextEntry = async () => {
  if (currentEntryIndex.value < totalEntries.value) {
    currentEntryIndex.value++
    const nextEntryId = entriesWithSameFormId.value[currentEntryIndex.value - 1].id
    if (isResultsEntryRoute.value) {
      await router.push({
        name: IVYFORMS_RESULTS_ENTRY_DETAILS,
        params: { formId: formId.value, entryId: nextEntryId },
      })
    } else {
      await router.push({ name: IVYFORMS_ENTRY_DETAILS, params: { id: nextEntryId } })
    }
    await updateEntryData(Number(nextEntryId))
  }
}

onMounted(async () => {
  loading.value = true
  // Always fetch entry by entryId
  try {
    await entryStore.fetchEntry(Number(entryId.value))
    entry.value = entryStore.entry
    entryFields.value = entryStore.entryFields
    // For Results tab, load form by formId from route
    if (isResultsEntryRoute.value && formId.value) {
      // Indicate the form was loaded only for entry viewing so other components
      // don't treat it as a user-selected filter.
      try {
        formBuilderStore.loadedForEntry = true
        // eslint-disable-next-line @typescript-eslint/no-unused-vars
      } catch (e) {
        // ignore if flag not available
      }
      await formBuilderStore.loadForm(Array.isArray(formId.value) ? formId.value[0] : formId.value)
    } else if (entry.value?.formId) {
      try {
        formBuilderStore.loadedForEntry = true
      } finally {
        await formBuilderStore.loadForm(entry.value.formId)
      }
    }
    const meta = allEntriesStore.paginationMeta as { page: number; perPage: number; total: number }
    const formIdVal = entry.value?.formId
    await allEntriesStore.fetchData({ formId: formIdVal, perPage: meta.total })
    entriesWithSameFormId.value = allEntriesStore.tableData
      .filter((entry) => Number(entry.formId) === Number(formIdVal))
      .sort((a, b) => Number(a.id) - Number(b.id))
    currentEntryIndex.value =
      entriesWithSameFormId.value.findIndex((entry) => Number(entry.id) === Number(entryId.value)) +
      1
    // Update current entry index on page reload
    watch(
      () => (isResultsEntryRoute.value ? route.params.entryId : route.params.id),
      (newId) => {
        currentEntryIndex.value =
          entriesWithSameFormId.value.findIndex((entry) => Number(entry.id) === Number(newId)) + 1
      },
      { immediate: true },
    )
  } catch (error) {
    console.error(getLabel('failed_to_get_entry'), error)
  } finally {
    loading.value = false
  }
})

// Clear selected form before navigation (runs before leaving the route)
onBeforeRouteLeave((to, from, next) => {
  try {
    formBuilderStore.formId = null
    formBuilderStore.loadedForEntry = false
  } finally {
    next()
  }
})

onUnmounted(() => {
  // Clear the formId stored in the form builder when leaving the entry details page.
  try {
    formBuilderStore.formId = null
    formBuilderStore.loadedForEntry = false
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
  } catch (e) {
    // ignore if store is unavailable for any reason
  }
})

// Provide entry data to child components
provide('entry', entry)

provide('pageTitle', formName)
</script>

<style scoped lang="scss">
.ivyforms-entry-page {
  height: calc(100vh - 116px);
  overflow: hidden;
  display: flex;
  transition: height 0.3s ease-in-out;
}

.ivyforms-header {
  :deep(.ivyforms-header__left) {
    gap: 16px !important;
  }

  &__navigation {
    width: 200px;
    min-width: 200px;
    max-width: 200px;

    &-text {
      color: var(--map-base-text-0);
      width: 90px;
      max-width: 90px;
      text-align: center;
    }

    .ivyforms-count-loader {
      display: inline-block;
      width: 14px;
      height: 14px;
      border: 2px solid var(--map-base-brand-fill-0);
      border-top: 2px solid transparent;
      border-radius: 50%;
      animation: ivyforms-spin 1.2s linear infinite;
      vertical-align: middle;
    }
  }
}

@keyframes ivyforms-spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
