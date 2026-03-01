<template>
  <div
    class="all-forms ivyforms-flex ivyforms-flex-1 ivyforms-flex-direction-column ivyforms-p-4 ivyforms-gap-24"
  >
    <div class="ivyforms-all-forms-option-bar">
      <div class="ivyforms-all-forms-option-bar__left">
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
          shortcuts
          secondary
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
        <IvyContextMenu @close="closeFilterIndicator"></IvyContextMenu>
      </div>
      <div class="ivyforms-all-forms-option-bar__right">
        <IvyButtonAction icon-start="plus" @click="openFormBuilder">
          {{ getLabel('form') }}
        </IvyButtonAction>
      </div>
    </div>
    <div class="ivyforms-all-forms-table">
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
          <AllFormsTableHeader
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
            :title="getLabel('empty_state_all_forms_title')"
            :subtitle="getLabel('empty_state_all_forms_subtitle')"
          >
            <template #actionButton>
              <IvyButtonAction icon-start="plus" @click="openFormBuilder">
                {{ getLabel('form') }}
              </IvyButtonAction>
            </template>
          </PageEmptyState>
          <PageEmptyState
            v-else
            image="filters"
            :title="getLabel('empty_state_all_forms_title')"
            :subtitle="getLabel('empty_state_all_forms_subtitle')"
          >
            <template #actionButton>
              <IvyButtonAction @click="resetFilters">{{
                getLabel('reset_filters')
              }}</IvyButtonAction>
            </template>
          </PageEmptyState>
        </template>
      </IvyTable>

      <IvyDialog
        v-model="showEditDialog"
        :title="getLabel('edit_table')"
        :subtitle="getLabel('edit_columns')"
        show-close
        align-center
        width="400px"
      >
        <IvyCheckbox
          v-model="allColumnsSelected"
          priority="secondary"
          size="s"
          type="checkmark"
          class="show-all-columns"
        >
          <slot>{{ getLabel('show_all_forms') }}</slot>
        </IvyCheckbox>
        <div v-for="column in tempColumns" :key="column.key">
          <IvyCheckbox
            v-if="column.showInModal"
            v-model="column.visible"
            :disabled="isCheckboxDisabled(column.key)"
            priority="secondary"
            size="s"
            type="checkmark"
          >
            {{ column.title }}
          </IvyCheckbox>
        </div>
        <template #footer>
          <IvyButtonAction
            :priority="'tertiary'"
            :size="'d'"
            :type="'fill'"
            @click="showEditDialog = false"
          >
            {{ getLabel('cancel') }}
          </IvyButtonAction>
          <IvyButtonAction
            :priority="'success'"
            :size="'d'"
            :type="'fill'"
            @click="applyColumnVisibility"
          >
            {{ getLabel('apply') }}
          </IvyButtonAction>
        </template>
      </IvyDialog>
    </div>

    <IvyShortDialog show-close align-center type="error" width="342px" />
    <div class="ivyforms-all-forms-pagination-container">
      <IvyPagination
        v-model:current-page="pagination.page"
        v-model:page-size="pagination.perPage"
        :total="total"
        secondary
        :aria-label="getLabel('pagination_navigation')"
      />
    </div>
    <IvyFormsTemplateSelection
      v-model:visible="showTemplateSelection"
      source-page-type="allForms"
      @close="handleTemplateSelectionClose"
      @form-created="handleFormCreated"
    />
  </div>
</template>
<script setup lang="ts">
import { computed, h, nextTick, onMounted, onUnmounted, reactive, ref, toRaw, watch } from 'vue'
import { debounce } from 'lodash'
import type { TableColumn } from '@/views/_components/table/IvyTableColumn.ts'
import { useAllForms } from '@/stores/useAllForms.ts'
import { useContextMenuStore } from '@/stores/contextMenuStore.ts'
import { useActionEntityStore } from '@/stores/actionEntityStore'
import { useNavigation } from '@/composables/useNavigation'
import IvyMessage from '@/views/_components/message/ivyMessage.ts'
import IvyIcon from '@/views/_components/icon/IvyIcon.vue'
import IvyToggle from '@/views/_components/toggle/IvyToggle.vue'
import IvyCheckbox from '@/views/_components/checkbox/IvyCheckbox.vue'
import IvyButtonAction from '@/views/_components/button/IvyButtonAction.vue'
import IvyLink from '@/views/_components/link/IvyLink.vue'
import IvyContextMenu from '@/views/_components/context-menu/IvyContextMenu.vue'
import IvyDatePicker from '@/views/_components/datepicker/IvyDatePicker.vue'
import IvyDialog from '@/views/_components/dialog/IvyDialog.vue'
import IvyShortDialog from '@/views/_components/sub-dialog/IvyShortDialog.vue'
import IvyTooltip from '@/views/_components/tooltip/IvyTooltip.vue'
import {
  ContextMenuActionType,
  createContextMenuAction,
} from '@/views/_components/context-menu/kit/ContextMenuActionType.ts'
import UtilDateFormatter from '@/utils/utilDateFormatter'
import IvyFormsTemplateSelection from '../IvyFormsTemplateSelection.vue'
import { useLabels } from '@/composables/useLabels'
import type IvyIndicatorButton from '@/views/_components/button/IvyIndicatorButton.vue'
import { IVYFORMS_FORM_BUILDER_PAGE } from '@/constants/pages.ts'
import { TableV2FixedDir } from 'element-plus'
import { useWcagColors } from '@/composables/useWcagColors'
import { useSettingsStore } from '@/stores/useSettingsStore'
import { useGlobalState } from '@/stores/useGlobalState.ts'

// Initialize WCAG color system for AllForms page
const { startWatching } = useWcagColors()
startWatching()

// Initialize settings store to ensure WCAG compliance setting is loaded
const settingsStore = useSettingsStore()
const globalState = useGlobalState()
const { getLabel } = useLabels()
// State and Refs
const allFormsStore = useAllForms()
const contextMenuStore = useContextMenuStore()
const { getAdminPageUrl, navigateToAdminPage } = useNavigation()
const tableRef = ref()
const tableWidth = ref(0)
const resizeObserver = ref<ResizeObserver | null>(null)
const menuButtonRef = ref<InstanceType<typeof IvyIndicatorButton> | HTMLElement | null>(null)
const datePickerPopperRef = ref<InstanceType<typeof IvyDatePicker> | HTMLElement | null>(null)
const showEditDialog = ref(false)
const tempColumns = ref([])
const tableData = ref([])
const total = ref(0)
const selectedDate = ref(null)
const showDatePicker = ref(false)
const loading = ref(true)
const showFiltersIndicator = ref(false)
const showDatePickerIndicator = ref(false)
const buttonRefs = ref<Record<number, HTMLElement | null>>({})
const hoveredRowId = ref<number | null>(null)
const searchQuery = ref('')
const debouncedSearchQuery = ref('')
const showTemplateSelection = ref(false)

const updateDebouncedSearch = debounce((val) => {
  debouncedSearchQuery.value = val
}, 500)

const isDebouncing = computed(() => searchQuery.value !== debouncedSearchQuery.value)

// Combined loader state used both by the table and the empty-slot skeleton
const shouldShowSkeleton = computed(() => loading.value || isDebouncing.value)

const filters = reactive({
  starred: null,
  published: null,
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
  publishedTrueCount: 0,
  publishedFalseCount: 0,
  starredTrueCount: 0,
  starredFalseCount: 0,
})

const actionEntityStore = useActionEntityStore()

// Renderers
const renderSelectHeader = () => {
  return h(
    IvyCheckbox,
    {
      modelValue: allSelected.value,
      size: 's',
      type: 'checkmark',
      ariaLabel: getLabel('select_all_forms'),
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
      ariaLabel: getLabel('select_form_header'),
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
      href: getAdminPageUrl(IVYFORMS_FORM_BUILDER_PAGE, `/manage/${rowData.id}`),
    },
    () => [rowData.id],
  )
}

const renderNameCell = ({ rowData }: { rowData }) => {
  if (!rowData) return null
  return h(
    IvyLink,
    {
      size: 's',
      href: getAdminPageUrl(IVYFORMS_FORM_BUILDER_PAGE, `/manage/${rowData.id}`),
      class: 'ivyforms-name-cell',
    },
    () => [rowData.name],
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
      await allFormsStore.updateStarred(rowData.id, rowData.starred)
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

const renderPublishedCell = ({ rowData }: { rowData }) => {
  if (!rowData) return null
  return h(IvyToggle, {
    modelValue: rowData.published,
    size: 's',
    ariaLabel: getLabel('status_switch'),
    'onUpdate:modelValue': async (value) => {
      rowData.published = value
      await allFormsStore.updateStatus(rowData.id, rowData.published)
      if (rowData.published) {
        filterCount.publishedTrueCount++
        filterCount.publishedFalseCount--
      } else {
        filterCount.publishedTrueCount--
        filterCount.publishedFalseCount++
      }
    },
  })
}
const renderEntriesCell = ({ rowData }: { rowData }) => {
  if (!rowData) return null
  const count = allFormsStore.entryCount[rowData.id]
  return h(
    IvyLink,
    {
      size: 's',
      href: getAdminPageUrl(IVYFORMS_FORM_BUILDER_PAGE, `/manage/${rowData.id}/results/entries`),
    },
    () => [count !== undefined ? count : '-'],
  )
}

const renderShortcodeCell = ({ rowData }: { rowData }) => {
  if (!rowData) return null
  const shortcode = `[ivyforms id=${rowData.id}]`
  return h('div', [
    h(
      IvyTooltip,
      {
        content: getLabel('copy_shortcode'),
        placement: 'top',
      },
      () => [
        h(IvyButtonAction, {
          iconOnly: true,
          iconStart: 'copy',
          size: 's',
          type: 'ghost',
          priority: 'tertiary',
          iconStartType: 'outline',
          ariaLabel: getLabel('copy_shorcode_table_form'),
          onClick: copyToClipboard(shortcode),
        }),
      ],
    ),
    h('span', shortcode),
  ])
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
          content: getLabel('edit'),
          placement: 'top',
        },
        () => [
          h(IvyButtonAction, {
            iconOnly: true,
            iconStart: 'edit',
            size: 's',
            type: 'ghost',
            priority: 'tertiary',
            iconStartType: 'outline',
            onClick: editForm(rowData.id),
          }),
        ],
      ),
      h(
        IvyTooltip,
        {
          content: getLabel('duplicate'),
          placement: 'top',
        },
        () => [
          h(IvyButtonAction, {
            iconOnly: true,
            iconStart: 'copy',
            size: 's',
            type: 'ghost',
            priority: 'tertiary',
            iconStartType: 'outline',
            onClick: duplicateForm(rowData.id),
          }),
        ],
      ),
      h(
        IvyTooltip,
        {
          content: getLabel('preview'),
          placement: 'top',
        },
        () => [
          h(IvyButtonAction, {
            iconOnly: true,
            iconStart: 'preview',
            size: 's',
            type: 'ghost',
            priority: 'tertiary',
            iconStartType: 'outline',
            onClick: previewForm(rowData.id),
          }),
        ],
      ),
      h(
        IvyTooltip,
        {
          content: getLabel('actions'),
          placement: 'top',
        },
        () => [
          h(IvyButtonAction, {
            iconOnly: true,
            iconStart: 'context-menu-dot',
            size: 's',
            type: 'ghost',
            priority: 'tertiary',
            iconStartType: 'outline',
            ref: (el) => {
              buttonRefs.value[rowData.id] =
                el && '$el' in el && el.$el instanceof HTMLElement ? el.$el : null
            },
            onClick: () => openActions(rowData.id),
          }),
        ],
      ),
    ],
  )
}

// Columns Configuration
const columns = ref<TableColumn[]>([
  {
    key: 'select',
    width: 56,
    minWidth: 56,
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
  },
  {
    key: 'name',
    title: getLabel('form_name'),
    width: 204,
    minWidth: 204,
    sortable: true,
    cellRenderer: renderNameCell,
    visible: true,
    showInModal: true,
  },
  {
    key: 'published',
    title: getLabel('published'),
    width: 48,
    minWidth: 48,
    cellRenderer: renderPublishedCell,
    visible: true,
    showInModal: true,
  },
  {
    key: 'entries',
    title: getLabel('entries'),
    width: 54,
    minWidth: 54,
    cellRenderer: renderEntriesCell,
    visible: true,
    showInModal: true,
  },
  {
    key: 'shortcode',
    title: getLabel('shortcode'),
    width: 160,
    minWidth: 160,
    cellRenderer: renderShortcodeCell,
    visible: true,
    showInModal: true,
  },
  {
    key: 'date-created',
    title: getLabel('date_created'),
    width: 105,
    minWidth: 105,
    cellRenderer: ({ rowData }) => {
      return h('span', UtilDateFormatter.formatWPDate(rowData.dateCreated))
    },
    visible: true,
    showInModal: true,
  },
  {
    key: 'date-edited',
    title: getLabel('date_edited'),
    width: 105,
    minWidth: 105,
    cellRenderer: ({ rowData }) => {
      return h('span', UtilDateFormatter.formatWPDate(rowData.dateEdited))
    },
    visible: true,
    showInModal: true,
  },
  {
    key: 'author',
    title: getLabel('author'),
    width: 204,
    minWidth: 204,
    cellRenderer: ({ rowData }) => h('span', rowData.author),
    visible: true,
    sortable: true,
    showInModal: true,
  },
  {
    key: 'actions',
    headerRenderer: () =>
      h(
        IvyTooltip,
        {
          content: getLabel('column_visibility'),
          placement: 'left',
          ariaLabel: getLabel('column_visibility'),
        },
        () => [
          h(IvyIcon, {
            name: 'eye-opened',
            type: 'outline',
            size: 'd',
            color: 'var(--map-base-dusk-symbol-2)',
            onClick: openColumnVisibility,
            style: 'cursor: pointer;',
            ariaLabel: getLabel('column_visibility'),
            role: 'button',
          }),
        ],
      ),
    class: 'ivyforms-cell-actions',
    width: 160,
    minWidth: 160,
    cellRenderer: renderActionsCell,
    visible: true,
    showInModal: false,
    fixed: TableV2FixedDir.RIGHT,
  },
])
const dateFormat = computed(() =>
  UtilDateFormatter.convertWPFormat(window.wpIvyDateFormat?.dateFormat || 'D/M/Y', 'dayjs'),
)

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
const allColumnsSelected = computed({
  get() {
    return tempColumns.value.every((column) => column.visible)
  },
  set(value) {
    tempColumns.value.forEach((column) => {
      if (column.key !== 'id' && column.showInModal) {
        column.visible = value
      }
    })
    // Ensure the 'id' column is always checked
    const idColumn = tempColumns.value.find((column) => column.key === 'id')
    if (idColumn) {
      idColumn.visible = true
    }
  },
})

const visibleColumnsCount = computed(() => {
  return tempColumns.value.filter((column) => column.visible).length
})

const isCheckboxDisabled = (columnKey: string) => {
  // Disable the checkbox if it's the last visible column
  return (
    visibleColumnsCount.value === 3 &&
    tempColumns.value.find((col) => col.key === columnKey)?.visible
  )
}

const isFiltered = computed(
  () =>
    searchQuery.value.trim() !== '' ||
    selectedDate.value ||
    filters.starred !== null ||
    filters.published !== null,
)

const buildFetchParams = (shouldGetCount: boolean) => ({
  page: pagination.page,
  perPage: pagination.perPage,
  search: debouncedSearchQuery.value,
  sortBy: sort.column,
  sortOrder: sort.order,
  dateRange: UtilDateFormatter.formatDateRangeForApi(selectedDate.value),
  shouldGetCount: shouldGetCount,
  filters: {
    starred: filters.starred,
    published: filters.published,
  },
})

const copyToClipboard = (text: string) => () => {
  if (navigator.clipboard) {
    navigator.clipboard.writeText(text).then(() => {
      IvyMessage({
        message: getLabel('copied_shortcode'),
        type: 'success',
      })
    })
  } else {
    // Fallback for insecure contexts
    const textarea = document.createElement('textarea')
    textarea.value = text
    textarea.style.position = 'fixed' // avoid scrolling
    textarea.style.opacity = '0'
    document.body.appendChild(textarea)
    textarea.select()
    try {
      document.execCommand('copy')
      IvyMessage({
        message: getLabel('copied_shortcode'),
        type: 'success',
      })
    } catch (err) {
      IvyMessage({
        message: `${getLabel('error_refreshing')} ${err}`,
        type: 'error',
      })
    }
    document.body.removeChild(textarea)
  }
}

// Watchers

watch(searchQuery, updateDebouncedSearch)

// Fetch forms on pagination, search, and filter change
watch(
  [
    () => pagination.page,
    () => pagination.perPage,
    () => debouncedSearchQuery.value,
    () => filters.starred,
    () => filters.published,
    () => selectedDate.value,
  ],
  async () => {
    loading.value = true
    await allFormsStore.fetchData(buildFetchParams(false))
    tableData.value = allFormsStore.tableData
    loading.value = false
  },
)
//Reset pagination when table loads
watch(
  () => allFormsStore.paginationMeta,
  (newMeta) => {
    if (pagination.page !== newMeta.page) {
      pagination.page = newMeta.page
    }
    if (pagination.perPage !== newMeta.perPage) {
      pagination.perPage = newMeta.perPage
    }
  },
  { deep: true, immediate: true },
)

// Show datepicker indicator once a date range is selected
watch(selectedDate, () => (showDatePickerIndicator.value = !!selectedDate.value))

watch(
  () => allFormsStore.tableData,
  (newData) => {
    tableData.value = newData
    loading.value = false
  },
  { immediate: true },
)

watch(
  () => allFormsStore.paginationMeta,
  (newMeta) => {
    total.value = toRaw(newMeta).total || 0
  },
  { immediate: true },
)

watch(
  () => allFormsStore.filterCount,
  (newCount) => {
    filterCount.publishedTrueCount = newCount.publishedTrueCount
    filterCount.publishedFalseCount = newCount.publishedFalseCount
    filterCount.starredTrueCount = newCount.starredTrueCount
    filterCount.starredFalseCount = newCount.starredFalseCount
  },
  { immediate: true },
)

// Watcher to prevent unchecking the last column
watch(
  () => tempColumns.value.map((column) => column.visible),
  () => {
    const visibleColumns = tempColumns.value.filter((column) => column.visible)
    if (visibleColumns.length === 0) {
      const idColumn = tempColumns.value.find((column) => column.key === 'id')
      if (idColumn) {
        idColumn.visible = true
      }
    }
  },
)

// Reload table data when fullscreen mode changes
watch(
  () => globalState.isFullScreenMode,
  () => loadTableData(false),
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
    await allFormsStore.fetchData(buildFetchParams(shouldGetCount))
    tableData.value = allFormsStore.tableData
    const meta = allFormsStore.paginationMeta as { page: number; perPage: number; total: number }
    pagination.page = meta.page
    pagination.perPage = meta.perPage
    total.value = meta.total
    if (shouldGetCount) {
      filterCount.publishedTrueCount = allFormsStore.filterCount.publishedTrueCount
      filterCount.publishedFalseCount = allFormsStore.filterCount.publishedFalseCount
      filterCount.starredTrueCount = allFormsStore.filterCount.starredTrueCount
      filterCount.starredFalseCount = allFormsStore.filterCount.starredFalseCount
    }
  } finally {
    loading.value = false
    await syncHoverWithinTable()
  }
}

const handleSortChange = ({ column, order }: { column: string; order: 'asc' | 'desc' | null }) => {
  sort.column = column
  sort.order = order === 'asc' ? 'asc' : order === 'desc' ? 'desc' : ''
  pagination.page = 1
  loadTableData(false)
}

const openFormBuilder = () => {
  showTemplateSelection.value = true
}
// Template selection handlers
const handleTemplateSelectionClose = () => {
  showTemplateSelection.value = false
}

const handleFormCreated = () => {
  // Refresh table data when new form is created
  loadTableData(false)
  showTemplateSelection.value = false
}
const editForm = (id: number) => () => {
  navigateToAdminPage(IVYFORMS_FORM_BUILDER_PAGE, `/manage/${id}`)
}
const editFormSettings = (id: number) => () => {
  navigateToAdminPage(IVYFORMS_FORM_BUILDER_PAGE, `/manage/${id}/settings/general`)
}

const duplicateForm = (id: number) => async () => {
  await actionEntityStore.handleActionClick(id, null, 'form', 'duplicate', {
    setLoading: (isLoading: boolean) => {
      loading.value = isLoading
    },
  })
}

const previewForm = (id: number) => () => {
  window.open(
    `${window.wpIvyUrls.siteURL}?ivyforms_preview=${id}&_wpNonce=${window.wpIvyApiSettings.nonce}`,
    '_blank',
  )
}
const unselectAllRows = () => {
  tableData.value.forEach((row) => {
    row.selected = false
  })
}

const toggleSelectAll = (value: boolean) => {
  tableData.value.forEach((row) => (row.selected = value))
}

const openActions = debounce((id) => {
  const buttonElement = buttonRefs.value[id]
  if (!buttonElement) {
    IvyMessage({
      message: getLabel('menu_button_element_not_found'),
      type: 'error',
    })
    return
  }

  hoveredRowId.value = id

  // Add hovered class to all rows with matching rowkey
  const table = document.querySelector('.ivyforms-table')
  if (table) {
    const rows = table.querySelectorAll('.el-table-v2__row')
    rows.forEach((row) => {
      if (row.getAttribute('rowkey') === id.toString()) {
        row.classList.add('highlighted')
      }
    })
  }

  contextMenuStore.openContextMenu({
    actions: [
      createContextMenuAction(ContextMenuActionType.Entries, {
        handler: () => {
          navigateToAdminPage(IVYFORMS_FORM_BUILDER_PAGE, `/manage/${id}/results/entries`)
        },
      }),
      createContextMenuAction(ContextMenuActionType.Settings, { handler: editFormSettings(id) }),
      createContextMenuAction(ContextMenuActionType.Delete, {
        handler: async (entityId) => {
          await actionEntityStore.handleActionClick(null, [entityId], 'forms', 'delete', {
            setLoading: (isLoading) => {
              loading.value = isLoading
            },
          })
        },
      }),
    ],
    entityId: id,
    contextMenuButtonRef: buttonElement,
    onClose: () => {
      const highlightedRows = document.querySelectorAll('.el-table-v2__row.highlighted')
      highlightedRows.forEach((row) => row.classList.remove('highlighted'))
      const hoveredRows = document.querySelectorAll('.el-table-v2__row.hovered')
      hoveredRows.forEach((row) => row.classList.remove('hovered'))
      hoveredRowId.value = null
    },
  })
}, 100)

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

const openColumnVisibility = () => {
  tempColumns.value = columns.value.map((column) => ({
    key: column.key,
    visible: column.visible,
    showInModal: column.showInModal ?? true,
    title: column.title,
  }))
  showEditDialog.value = true
}
const applyColumnVisibility = () => {
  tempColumns.value.forEach((tempColumn) => {
    const column = columns.value.find((col) => col.key === tempColumn.key)
    if (column) {
      column.visible = tempColumn.visible
    }
  })

  columns.value = [...columns.value]

  // Recalculate and apply dynamic widths
  const visibleColumns = columns.value.filter((col) => col.visible && col.showInModal)
  const dynamicWidthValue = visibleColumns.length > 0 ? `${100 / visibleColumns.length}%` : 'auto'

  columns.value.forEach((column) => {
    if (column.showInModal) {
      column.width = column.visible ? dynamicWidthValue : null
    }
  })

  // Close the dialog
  showEditDialog.value = false
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
const closeFilterIndicator = () => {
  showFiltersIndicator.value = false
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
const updateFilter = (filterType: 'starred' | 'published', value: boolean | null) => {
  filters.starred = filterType === 'starred' ? value : null
  filters.published = filterType === 'published' ? value : null
  showFiltersIndicator.value = value !== null
}

const openContextMenuFilters = async () => {
  if (contextMenuStore.isOpen) {
    contextMenuStore.closeContextMenu()
    return
  }

  contextMenuStore.openContextMenu({
    actions: [
      createContextMenuAction(ContextMenuActionType.Published, {
        handler: withDelayedClose(() => updateFilter('published', true)),
        rightText: filterCount.publishedTrueCount.toString(),
        isActive: () => filters.published === true,
        secondary: true,
      }),
      createContextMenuAction(ContextMenuActionType.Unpublished, {
        handler: withDelayedClose(() => updateFilter('published', false)),
        isActive: () => filters.published === false,
        secondary: true,
        rightText: filterCount.publishedFalseCount.toString(),
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
      createContextMenuAction(ContextMenuActionType.Reset, {
        handler: withDelayedClose(() => {
          updateFilter('starred', null)
          updateFilter('published', null)
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
  filters.published = null
  showFiltersIndicator.value = false
}

const resetFilters = () => {
  filters.starred = null
  filters.published = null
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
</script>

<style lang="scss">
.all-forms {
  display: flex;
  flex-direction: column;
  height: 100%;
  overflow: hidden;

  .ivyforms-all-forms-option-bar {
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

    // Search
    .ivyforms-search__fixed-width {
      width: 309px;
      min-width: 309px;
      max-width: 309px;
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
    &.highlighted {
      background: var(--map-hover) !important;
    }

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
        &.hovered {
          display: flex;
        }
      }
    }
  }

  .el-table-v2__header-cell[data-key='actions'] {
    justify-content: flex-end;
    padding-right: 14px;
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
