<template>
  <div
    class="all-entries ivyforms-flex ivyforms-flex-1 ivyforms-flex-direction-column ivyforms-p-4 ivyforms-gap-24"
  >
    <div class="ivyforms-all-entries-option-bar">
      <div class="ivyforms-all-entries-option-bar__left">
        <IvySearch
          v-model="searchQuery"
          class="ivyforms-search__fixed-width"
          :placeholder="getLabel('search')"
        ></IvySearch>
        <IvyDatePicker
          v-if="showDatePicker"
          ref="datePickerPopperRef"
          v-model="selectedDate"
          type="datetimerange"
          :date-format="dateFormat"
          :aria-label="getLabel('date_filters')"
          clearable
          secondary
          shortcuts
          @close="closeDatePicker"
        />
        <IvyIndicatorButton
          color="tertiary"
          type="datepicker"
          :aria-label="getLabel('date_filters')"
          :indicator="showDatePickerIndicator"
          :custom-hide-indicator="hideDatePickerIndicator"
          @click="openDatePicker"
        />
        <IvyIndicatorButton
          ref="menuButtonRef"
          color="tertiary"
          type="filter"
          :aria-label="getLabel('filters')"
          :indicator="showFiltersIndicator"
          :custom-hide-indicator="hideFiltersIndicator"
          @click="openContextMenuFilters"
        />
        <IvyContextMenu></IvyContextMenu>
      </div>
    </div>
    <div class="ivyforms-all-entries-table">
      <IvyTable
        ref="tableRef"
        :data="shouldShowSkeleton ? [] : tableData"
        :loading="shouldShowSkeleton"
        :total="total"
        :row-key="'id'"
        :columns="dynamicWidthColumns"
        :min-table-width="getMinTableWidth"
        :is-custom-header="anyRowSelected"
        :sort="sort"
        pagination
        @sort-change="handleSortChange"
      >
        <template #header>
          <AllEntriesTableHeader
            v-model:loading="loading"
            :selected-ids="selectedRowIds"
            @unselect-all="unselectAllRows"
          />
        </template>

        <template #empty>
          <div v-if="shouldShowSkeleton" class="ivyforms-table-skeleton">
            <IvySkeleton>
              <template #template>
                <IvySkeletonItem
                  v-for="n in pagination.perPage"
                  :key="n"
                  class="ivyforms-skeleton-row"
                ></IvySkeletonItem>
              </template>
            </IvySkeleton>
          </div>
          <PageEmptyState
            v-else-if="!isFiltered"
            image="not-found"
            :title="getLabel('empty_state_all_entries_title')"
            :subtitle="getLabel('empty_state_all_entries_subtitle')"
          >
          </PageEmptyState>
          <PageEmptyState
            v-else
            image="filters"
            :title="getLabel('empty_state_filters_all_entries_title')"
            :subtitle="getLabel('empty_state_filters_all_entries_subtitle')"
          >
            <template #actionButton>
              <IvyButtonAction @click="resetFilters">{{
                getLabel('reset_filters')
              }}</IvyButtonAction>
            </template>
          </PageEmptyState>
        </template>
      </IvyTable>
    </div>

    <IvyShortDialog show-close align-center type="error" width="342px" />

    <div class="ivyforms-all-entries-pagination-container">
      <IvyPagination
        v-model:current-page="pagination.page"
        v-model:page-size="pagination.perPage"
        :total="total"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, h, nextTick, onMounted, onUnmounted, reactive, ref, watch } from 'vue'
import { debounce } from 'lodash'
import type { TableColumn } from '@/views/_components/table/IvyTableColumn.ts'
import { useAllEntries } from '@/stores/useAllEntries.ts'
import { useContextMenuStore } from '@/stores/contextMenuStore.ts'
import { useActionEntityStore } from '@/stores/actionEntityStore'
import IvyIcon from '@/views/_components/icon/IvyIcon.vue'
import IvyCheckbox from '@/views/_components/checkbox/IvyCheckbox.vue'
import IvyButtonAction from '@/views/_components/button/IvyButtonAction.vue'
import IvyLink from '@/views/_components/link/IvyLink.vue'
import IvyContextMenu from '@/views/_components/context-menu/IvyContextMenu.vue'
import IvyDatePicker from '@/views/_components/datepicker/IvyDatePicker.vue'
import {
  ContextMenuActionType,
  createContextMenuAction,
} from '@/views/_components/context-menu/kit/ContextMenuActionType.ts'
import { useLabels } from '@/composables/useLabels'
import type IvyIndicatorButton from '@/views/_components/button/IvyIndicatorButton.vue'
import { useFormBuilder } from '@/stores/useFormBuilder.ts'
import { TableV2FixedDir } from 'element-plus'
import IvyShortDialog from '@/views/_components/sub-dialog/IvyShortDialog.vue'
import UtilDateFormatter from '@/utils/utilDateFormatter'
import {
  IVYFORMS_ENTRIES_PAGE,
  IVYFORMS_FORM_BUILDER_PAGE,
  IVYFORMS_FORM_RESULTS,
  IVYFORMS_RESULTS_ENTRY_DETAILS,
} from '@/constants/pages.ts'
import { useNavigation } from '@/composables/useNavigation'
import IvyTooltip from '@/views/_components/tooltip/IvyTooltip.vue'
import { useWcagColors } from '@/composables/useWcagColors'
import { useGlobalState } from '@/stores/useGlobalState.ts'
import { useSettingsStore } from '@/stores/useSettingsStore'
import { useRoute } from 'vue-router'

const { getLabel } = useLabels()

const { startWatching } = useWcagColors()
startWatching()

const { getAdminPageUrl, navigateToAdminPage } = useNavigation()
const formBuilderStore = useFormBuilder()
const globalState = useGlobalState()
const settingsStore = useSettingsStore()
const resizeObserver = ref<ResizeObserver | null>(null)
const route = useRoute()
// State and Refs
const allEntriesStore = useAllEntries()
const contextMenuStore = useContextMenuStore()
const actionEntityStore = useActionEntityStore()
const tableRef = ref()
const tableWidth = ref(0)
const menuButtonRef = ref<InstanceType<typeof IvyIndicatorButton> | HTMLElement | null>(null)
const datePickerPopperRef = ref<InstanceType<typeof IvyDatePicker> | HTMLElement | null>(null)
const tableData = ref([])
const total = ref(0)
const selectedDate = ref(null)
const showDatePicker = ref(false)
const loading = ref(true)
const showFiltersIndicator = ref(false)
const showDatePickerIndicator = ref(false)
const hoveredRowId = ref<number | null>(null)
const searchQuery = ref('')
const debouncedSearchQuery = ref('')
const updateDebouncedSearch = debounce((val) => {
  debouncedSearchQuery.value = val
}, 500)
const isDebouncing = computed(() => searchQuery.value !== debouncedSearchQuery.value)

// Combined loader state used both by the table and the empty-slot skeleton
const shouldShowSkeleton = computed(() => loading.value || isDebouncing.value)

const filters = reactive({
  starred: null,
  read: null,
})
const pagination = reactive({
  page: 1,
  perPage: 10,
})
const sort = reactive({
  column: 'id',
  order: 'desc',
})
const filterCount = reactive({
  readTrueCount: 0,
  readFalseCount: 0,
  starredTrueCount: 0,
  starredFalseCount: 0,
})

// Renderers
const renderSelectHeader = () => {
  return h(
    IvyCheckbox,
    {
      modelValue: allSelected.value,
      size: 's',
      type: 'checkmark',
      'onUpdate:modelValue': (value: boolean) => toggleSelectAll(value),
    },
    () => [],
  )
}

const renderSelectCell = ({ rowData }: { rowData }) => {
  if (!rowData) return null
  return h(
    IvyCheckbox,
    {
      modelValue: rowData.selected,
      type: 'checkmark',
      size: 's',
      'onUpdate:modelValue': (value: boolean) => (rowData.selected = value),
    },
    () => [],
  )
}
const renderIdCell = ({ rowData }) => {
  if (!rowData) return null
  return h(
    IvyLink,
    {
      size: 's',
      href: getAdminPageUrl(IVYFORMS_ENTRIES_PAGE, `/details/${rowData.id}`),
      class: rowData.status === 'unread' ? 'ivyforms-unread-entry' : '',
    },
    () => [rowData.id],
  )
}
const renderStarCell = ({ rowData }: { rowData }) => {
  if (!rowData) return null
  return h(IvyIcon, {
    name: rowData.starred ? 'star-2' : 'star',
    type: 'fill',
    size: 'd',
    color: rowData.starred ? 'var(--map-accent-amber-symbol-0)' : 'var(--map-base-dusk-symbol--1)',
    style: 'cursor: pointer;',
    onClick: async () => {
      rowData.starred = !rowData.starred
      await allEntriesStore.updateStarred(rowData.id, rowData.starred)
      if (rowData.starred) {
        filterCount.starredTrueCount++
        filterCount.starredFalseCount--
      } else {
        filterCount.starredTrueCount--
        filterCount.starredFalseCount++
      }
    },
  })
}

const renderFormNameCell = ({ rowData }: { rowData }) => {
  if (!rowData) return null
  const formId = rowData.formId
  const formName = formIdToName.value[formId] || ''
  return h(
    IvyLink,
    {
      size: 's',
      href: getAdminPageUrl(IVYFORMS_FORM_BUILDER_PAGE, `/manage/${formId}`),
      class: 'ivyforms-name-cell',
    },
    () => [formName],
  )
}

const renderActionsCell = ({ rowData }: { rowData }) => {
  if (!rowData) return null
  return h(
    'div',
    {
      class: ['ivyforms-column-actions', { hovered: rowData.id === hoveredRowId.value }],
    },
    [
      h(
        IvyTooltip,
        {
          content:
            rowData.status === 'read' ? getLabel('mark_as_unread') : getLabel('mark_as_read'),
          placement: 'top',
        },
        h(IvyButtonAction, {
          iconOnly: true,
          iconStart: rowData.status === 'read' ? 'read' : 'unread',
          size: 's',
          type: 'ghost',
          priority: 'tertiary',
          iconStartType: 'outline',
          onClick: async () => {
            rowData.status = rowData.status === 'read' ? 'unread' : 'read'
            await allEntriesStore.updateStatus(rowData.id, rowData.status)
            if (rowData.status === 'read') {
              filterCount.readTrueCount++
              filterCount.readFalseCount--
            } else {
              filterCount.readTrueCount--
              filterCount.readFalseCount++
            }
          },
        }),
      ),
      h(
        IvyTooltip,
        {
          content: getLabel('preview'),
          placement: 'top',
        },
        h(IvyButtonAction, {
          iconOnly: true,
          iconStart: 'preview',
          size: 's',
          type: 'ghost',
          priority: 'tertiary',
          iconStartType: 'outline',
          onClick: previewEntry(rowData.id),
        }),
      ),
      h(
        IvyTooltip,
        {
          content: getLabel('delete'),
          placement: 'top',
        },
        h(IvyButtonAction, {
          iconOnly: true,
          iconStart: 'trash',
          size: 's',
          type: 'ghost',
          priority: 'danger',
          iconStartType: 'outline',
          onClick: deleteEntry(rowData.id),
        }),
      ),
    ],
  )
}

// Columns Configuration
const columns = ref<TableColumn[]>([
  {
    key: 'select',
    width: 44,
    minWidth: 44,
    headerRenderer: renderSelectHeader,
    cellRenderer: renderSelectCell,
    visible: true,
    showInModal: false,
    fixed: TableV2FixedDir.LEFT,
  },
  {
    key: 'star',
    width: 32,
    minWidth: 32,
    title: getLabel('starred'),
    headerRenderer: () =>
      h(IvyIcon, {
        name: 'star',
        type: 'outline',
        size: 'd',
        color: 'var(--map-base-dusk-symbol-2)',
      }),
    cellRenderer: renderStarCell,
    visible: true,
    showInModal: true,
  },
  {
    key: 'id',
    title: getLabel('id'),
    width: 40,
    minWidth: 40,
    cellRenderer: renderIdCell,
    visible: true,
    showInModal: true,
    sortable: true,
    class: 'ivyforms-custom-link',
  },
  {
    key: 'formName',
    title: getLabel('form_name'),
    width: 204,
    minWidth: 204,
    sortable: true,
    cellRenderer: renderFormNameCell,
    visible: true,
    showInModal: true,
  },
  {
    key: 'date-created',
    title: getLabel('date_submitted'),
    width: 105,
    minWidth: 105,
    cellRenderer: ({ rowData }) => {
      return h('span', UtilDateFormatter.formatWPDate(rowData.dateCreated))
    },
    visible: true,
    showInModal: true,
  },
  {
    key: 'browser',
    title: getLabel('browser'),
    width: 100,
    minWidth: 100,
    cellRenderer: ({ rowData }) => {
      return h('span', rowData.userAgent || getLabel('unknown'))
    },
    visible: true,
    showInModal: true,
  },
  {
    key: 'ip',
    title: getLabel('ip_address'),
    width: 120,
    minWidth: 120,
    cellRenderer: ({ rowData }) => {
      return h('span', rowData.ipAddress || getLabel('unknown'))
    },
    visible: true,
    showInModal: true,
  },
  {
    key: 'actions',
    title: '',
    class: 'ivyforms-cell-actions',
    width: 124,
    minWidth: 124,
    cellRenderer: renderActionsCell,
    visible: true,
    showInModal: false,
    fixed: TableV2FixedDir.RIGHT,
  },
])

// Computed Properties

const dateFormat = computed(() =>
  UtilDateFormatter.convertWPFormat(window.wpIvyDateFormat?.dateFormat || 'D/M/Y', 'dayjs'),
)

// Build a map of form ID to form name
const formIdToName = computed(() => allEntriesStore.formIdToName)

// Columns with dynamic width
const dynamicWidthColumns = computed(() => {
  const visibleColumns = columns.value.filter((col) => col.visible)
  const modalColumns = visibleColumns.filter((col) => col.showInModal)
  const totalTableWidth = tableWidth.value || 0
  // Calculate total explicitly set widths for all columns
  const totalSetWidth = visibleColumns.reduce((sum, col) => {
    return sum + (typeof col.width === 'number' ? col.width : 0)
  }, 0)

  // Check if there is extra space
  const remainingWidth = totalTableWidth - totalSetWidth
  const dynamicWidth = remainingWidth > 0 ? totalTableWidth / modalColumns.length : null

  // Assign dynamic width to modal columns, keep fixed width for others
  return visibleColumns.map((col) => {
    if (col.showInModal && dynamicWidth) {
      return {
        ...col,
        width: Math.floor(dynamicWidth),
      }
    }
    return col
  })
})

const getMinTableWidth = computed(() => {
  return columns.value.reduce((sum, col) => {
    return sum + (typeof col.minWidth === 'number' ? col.minWidth : parseFloat(col.minWidth) || 0)
  }, 0)
})

const anyRowSelected = computed(() => tableData.value.some((row) => row.selected))
const selectedRowIds = computed(() =>
  tableData.value.filter((row) => row.selected).map((row) => row.id),
)
const allSelected = computed(
  () => tableData.value.length > 0 && tableData.value.every((row) => row.selected),
)

const isFiltered = computed(
  () =>
    searchQuery.value.trim() !== '' ||
    selectedDate.value ||
    filters.starred !== null ||
    filters.read !== null,
)

// Utility Functions
const buildFetchParams = (shouldGetCount: boolean) => {
  // Prefer an explicitly selected form from the form builder store
  const selectedFormId = formBuilderStore.formId ? Number(formBuilderStore.formId) : undefined

  const routeFormId =
    route.name === IVYFORMS_FORM_RESULTS || route.name === IVYFORMS_RESULTS_ENTRY_DETAILS
      ? parseRouteFormId()
      : undefined

  return {
    page: pagination.page,
    perPage: pagination.perPage,
    search: debouncedSearchQuery.value,
    sortBy: sort.column,
    sortOrder: sort.order,
    dateRange: UtilDateFormatter.formatDateRangeForApi(selectedDate.value),
    shouldGetCount: shouldGetCount,
    formId: selectedFormId ?? routeFormId,
    filters: {
      starred: filters.starred,
      read: filters.read,
    },
    searchFieldValue: false,
  }
}

const parseRouteFormId = (): number | undefined => {
  const val = (route?.params as Record<string, unknown>)?.formId
  if (val === undefined || val === null) return undefined
  if (Array.isArray(val)) return Number(val[0])
  return Number(val as string | number)
}

// Watchers

watch(searchQuery, updateDebouncedSearch)

// Fetch forms on pagination, search, filter, and formId change
watch(
  [
    () => pagination.page,
    () => pagination.perPage,
    () => debouncedSearchQuery.value,
    () => filters.starred,
    () => filters.read,
    () => selectedDate.value,
  ],
  async () => {
    loading.value = true
    await allEntriesStore.fetchData(buildFetchParams(false))
    tableData.value = allEntriesStore.tableData
    loading.value = false
  },
)

watch([() => formBuilderStore.formId, () => formBuilderStore.loadedForEntry], async () => {
  loading.value = true
  await allEntriesStore.fetchData(buildFetchParams(true))
  tableData.value = allEntriesStore.tableData
  loading.value = false
})

// Show the datepicker indicator once a date range is selected
watch(selectedDate, () => (showDatePickerIndicator.value = !!selectedDate.value))

watch(
  () => allEntriesStore.tableData,
  (newData) => {
    tableData.value = newData
    loading.value = false
  },
  { immediate: true },
)

//Reset pagination when table loads
watch(
  () => allEntriesStore.paginationMeta,
  (newMeta) => {
    if (pagination.page !== newMeta.page) {
      pagination.page = newMeta.page
    }
    if (pagination.perPage !== newMeta.perPage) {
      pagination.perPage = newMeta.perPage
    }
    total.value = newMeta.total
  },
  { deep: true, immediate: true },
)

watch(
  () => allEntriesStore.filterCount,
  (newCount) => {
    filterCount.readTrueCount = newCount.readTrueCount
    filterCount.readFalseCount = newCount.readFalseCount
    filterCount.starredTrueCount = newCount.starredTrueCount
    filterCount.starredFalseCount = newCount.starredFalseCount
  },
  { immediate: true },
)

watch(
  () => tableData.value,
  async () => {
    await syncHoverWithinTable()
  },
)

// Methods
const loadTableData = async (shouldGetCount) => {
  loading.value = true
  try {
    await allEntriesStore.fetchData(buildFetchParams(shouldGetCount))
    tableData.value = allEntriesStore.tableData
    const meta = allEntriesStore.paginationMeta as { page: number; perPage: number; total: number }
    pagination.page = meta.page
    pagination.perPage = meta.perPage
    total.value = meta.total
    if (shouldGetCount) {
      filterCount.readTrueCount = allEntriesStore.filterCount.readTrueCount
      filterCount.readFalseCount = allEntriesStore.filterCount.readFalseCount
      filterCount.starredTrueCount = allEntriesStore.filterCount.starredTrueCount
      filterCount.starredFalseCount = allEntriesStore.filterCount.starredFalseCount
    }
  } finally {
    loading.value = false
    await syncHoverWithinTable()
  }
}

const syncHoverWithinTable = async () => {
  await nextTick()

  const table = document.querySelector('.ivyforms-table')
  if (!table) {
    return
  }

  const rows = table.querySelectorAll('.el-table-v2__row')
  if (!rows.length) {
    return
  }

  rows.forEach((row) => {
    const rowKey = row.getAttribute('rowkey')
    if (!rowKey) {
      return
    }

    row.addEventListener('mouseenter', () => {
      hoveredRowId.value = Number(rowKey)

      rows.forEach((matchingRow) => {
        if (matchingRow.getAttribute('rowkey') === rowKey) {
          matchingRow.classList.add('hovered')
        }
      })
    })

    row.addEventListener('mouseleave', () => {
      hoveredRowId.value = null

      rows.forEach((matchingRow) => {
        if (matchingRow.getAttribute('rowkey') === rowKey) {
          matchingRow.classList.remove('hovered')
        }
      })
    })
  })
}

const handleSortChange = ({ column, order }: { column: string; order: 'asc' | 'desc' | null }) => {
  sort.column = column
  sort.order = order === 'asc' ? 'asc' : order === 'desc' ? 'desc' : ''
  pagination.page = 1
  loadTableData(false)
}

const previewEntry = (id: number) => () => {
  navigateToAdminPage(IVYFORMS_ENTRIES_PAGE, `/details/${id}`)
}
const unselectAllRows = () => {
  tableData.value.forEach((row) => {
    row.selected = false
  })
}

const toggleSelectAll = (value: boolean) => {
  tableData.value.forEach((row) => (row.selected = value))
}

const deleteEntry = (id: number) => async () => {
  await actionEntityStore.handleActionClick(id, null, 'entry', 'delete', {
    setLoading: (isLoading) => {
      loading.value = isLoading
    },
  })
}

// Methods for DatePicker
const openDatePicker = () => {
  showDatePicker.value = true
  nextTick(() => {
    // Position then open the datepicker by clicking on the input
    positionDatePicker()
    const datePickerInput = document.querySelector('.el-date-editor') as HTMLInputElement
    datePickerInput?.click()
  })
}
const positionDatePicker = () => {
  nextTick(() => {
    const menuButton =
      menuButtonRef.value && '$el' in menuButtonRef.value
        ? menuButtonRef.value.$el
        : menuButtonRef.value

    const popper =
      datePickerPopperRef.value && '$el' in datePickerPopperRef.value
        ? datePickerPopperRef.value.$el
        : datePickerPopperRef.value

    if (menuButton instanceof HTMLElement && popper instanceof HTMLElement) {
      const buttonRect = menuButton.getBoundingClientRect()

      // Override the default positioning
      popper.style.position = 'absolute'
      popper.style.top = `${buttonRect.bottom + 8}px`
      popper.style.left = `${buttonRect.left}px`
      popper.style.inset = 'unset'
    }
  })
}

const closeDatePicker = () => {
  showDatePicker.value = false
}

const hideDatePickerIndicator = () => {
  selectedDate.value = null
  showDatePickerIndicator.value = false
  showDatePicker.value = false
}

// Helper function for delayed close
const withDelayedClose =
  (fn: () => void, delay = 500) =>
  () => {
    fn()
    setTimeout(() => {
      contextMenuStore.closeContextMenu()
    }, delay)
  }

// Methods for Filters
const updateFilter = (filterType: 'starred' | 'read', value: boolean | string | null) => {
  filters.starred = filterType === 'starred' ? value : null
  filters.read = filterType === 'read' ? value : null
  showFiltersIndicator.value = value !== null
}

const openContextMenuFilters = async () => {
  if (contextMenuStore.isOpen) {
    contextMenuStore.closeContextMenu()
    return
  }

  contextMenuStore.openContextMenu({
    actions: [
      createContextMenuAction(ContextMenuActionType.Read, {
        handler: withDelayedClose(() => updateFilter('read', 'read')),
        rightText: filterCount.readTrueCount.toString(),
        isActive: () => filters.read === 'read',
        secondary: true,
      }),
      createContextMenuAction(ContextMenuActionType.Unread, {
        handler: withDelayedClose(() => updateFilter('read', 'unread')),
        isActive: () => filters.read === 'unread',
        secondary: true,
        rightText: filterCount.readFalseCount.toString(),
      }),
      createContextMenuAction(ContextMenuActionType.Starred, {
        handler: withDelayedClose(() => updateFilter('starred', true)),
        isActive: () => filters.starred === true,
        secondary: true,
        rightText: filterCount.starredTrueCount.toString(),
      }),
      createContextMenuAction(ContextMenuActionType.Unstarred, {
        handler: withDelayedClose(() => updateFilter('starred', false)),
        isActive: () => filters.starred === false,
        rightText: filterCount.starredFalseCount.toString(),
        secondary: true,
      }),
      createContextMenuAction(ContextMenuActionType.ResetFilters, {
        handler: withDelayedClose(() => {
          updateFilter('starred', null)
          updateFilter('read', null)
        }),
        secondary: true,
        divided: true,
      }),
    ],
    contextMenuButtonRef: menuButtonRef.value,
  })
}

const hideFiltersIndicator = () => {
  filters.starred = null
  filters.read = null
  showFiltersIndicator.value = false
}

const resetFilters = () => {
  filters.starred = null
  filters.read = null
  selectedDate.value = null
  searchQuery.value = ''
  pagination.page = 1
  showFiltersIndicator.value = false
  showDatePickerIndicator.value = false
}

// Click outside logic for context menu filters
function handleClickOutside(event: MouseEvent) {
  const menuButton =
    menuButtonRef.value &&
    ('$el' in menuButtonRef.value ? menuButtonRef.value.$el : menuButtonRef.value)
  const contextMenu = document.querySelector(
    '.ivyforms-context-menu, .ivyforms-popper-context-menu',
  )

  if (
    contextMenuStore.isOpen &&
    !menuButton?.contains(event.target as Node) &&
    !contextMenu?.contains(event.target as Node)
  ) {
    contextMenuStore.closeContextMenu()
  }
}

// Lifecycle Hooks
onMounted(() => {
  document.addEventListener('mousedown', handleClickOutside)
  loadTableData(true)

  // Load settings to ensure WCAG compliance is initialized
  settingsStore.loadAllSettings()

  // Set up ResizeObserver for table width changes
  resizeObserver.value = new ResizeObserver((entries) => {
    for (const entry of entries) {
      tableWidth.value = entry.contentRect.width
    }
  })
  const tableContainer = tableRef.value?.$el?.parentElement
  if (tableContainer) {
    resizeObserver.value.observe(tableContainer)
  }
})

onUnmounted(() => {
  document.removeEventListener('mousedown', handleClickOutside)
  if (resizeObserver.value) {
    resizeObserver.value.disconnect()
  }
})

watch(
  () => globalState.isFullScreenMode,
  () => loadTableData(false),
)
</script>

<style lang="scss">
.all-entries {
  display: flex;
  flex-direction: column;
  height: 100%;
  overflow: hidden;

  .ivyforms-all-entries-option-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: var(--map-ground-level-1-foreground);
    gap: 8px;

    &__right,
    &__left {
      display: flex;
      gap: 8px;
      position: relative;
    }

    // Left
    &__left {
      align-items: center;
    }

    // Right
    &__right {
      align-items: flex-end;
    }

    // Hide datepicker input to only show the inner picker
    .el-date-editor.el-date-editor--datetimerange,
    .ivyforms-date-picker {
      width: 0;
      height: 0;
      opacity: 0;
      cursor: default;
      left: 315px;
      top: 20px;

      input {
        cursor: default;
      }
    }

    // Search
    .ivyforms-search__fixed-width {
      width: 309px;
      min-width: 309px;
      max-width: 309px;
    }
  }

  .el-table-v2__row-cell {
    div {
      display: flex;
      align-items: center;
      align-content: center;
      justify-content: center;
      flex-wrap: wrap;

      &.ivyforms-column-actions {
        display: none;
      }
    }
  }

  .el-table-v2__row {
    &.hovered {
      .el-table-v2__row-cell {
        .ivyforms-column-actions {
          display: flex;
        }
      }
    }

    &:hover,
    &.hovered {
      .el-table-v2__row-cell {
        .ivyforms-column-actions {
          display: flex;
        }
      }
    }

    .el-table-v2__row-cell {
      .ivyforms-column-actions {
        justify-content: flex-end;

        &.hovered {
          display: flex;
        }
      }

      &.ivyforms-custom-link {
        .ivyforms-unread-entry {
          .el-link {
            color: var(--map-base-brand-symbol-0) !important;
          }
        }
      }
    }

    &.ivyforms-unread-entry {
      background: var(--map-base-brand-o05) !important;

      &:hover,
      &.hovered {
        background: var(--map-hover) !important;
      }

      &.is-selected {
        background:
          linear-gradient(0deg, var(--map-accent-amber-o05) 0%, var(--map-accent-amber-o05) 100%),
          var(--map-ground-level-1-foreground) !important;
      }
    }
  }

  .ivyforms-table-skeleton {
    .ivyforms-skeleton-row {
      height: 48px;
      margin-bottom: 8px;
      border-radius: 4px;
      animation: pulse 1.5s ease-in-out infinite;
    }
  }
  .el-table-v2__row {
    opacity: 0;
    animation: fadeIn 0.3s ease-in-out forwards;
    animation-delay: calc(var(--row-index, 0) * 0.05s);
  }
  @keyframes fadeIn {
    from {
      opacity: 0;
      transform: translateY(-10px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
  @keyframes pulse {
    0% {
      opacity: 0.6;
    }
    50% {
      opacity: 0.3;
    }
    100% {
      opacity: 0.6;
    }
  }

  .ivyforms-name-cell .el-link__inner {
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
    display: -moz-box;
    -moz-box-orient: vertical;
    line-clamp: 2;
    display: block;
    max-height: 40px;
    line-height: 20px;
    text-align: start;
    word-break: break-word;
    overflow: hidden;
    text-overflow: ellipsis;

    @supports (-webkit-line-clamp: 2) {
      display: -webkit-box;
    }
  }
}
</style>
