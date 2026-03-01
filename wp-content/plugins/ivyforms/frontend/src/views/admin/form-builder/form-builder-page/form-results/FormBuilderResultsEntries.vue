<template>
  <div
    class="form-builder-results-entries ivyforms-flex ivyforms-flex-1 ivyforms-flex-direction-column ivyforms-p-4 ivyforms-gap-24"
  >
    <div
      class="form-builder-results-entries__option-bar ivyforms-flex ivyforms-align-items-center ivyforms-justify-content-between ivyforms-gap-8"
    >
      <div
        class="form-builder-results-entries__option-bar-left ivyforms-flex ivyforms-align-items-center ivyforms-gap-8 ivyforms-overflow-visible"
      >
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
          popper-class="results-picker"
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
    <div class="form-builder-results-entries__table">
      <IvyTable
        ref="tableRef"
        :key="tableWidth"
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
          <div v-if="shouldShowSkeleton" class="form-builder-results-entries__table-skeleton">
            <IvySkeleton>
              <template #template>
                <IvySkeletonItem
                  v-for="n in pagination.perPage"
                  :key="n"
                  class="form-builder-results-entries__skeleton-row"
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

      <IvyDialog
        v-model="showEditDialog"
        :title="getLabel('edit_table')"
        :subtitle="getLabel('edit_columns')"
        show-close
        align-center
        width="400px"
      >
        <template #default>
          <IvyCheckbox
            v-model="allColumnsSelected"
            priority="secondary"
            size="s"
            type="checkmark"
            class="form-builder-results-entries__show-all-columns"
          >
            <slot>{{ getLabel('show_all_columns') }}</slot>
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
        </template>
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

    <div class="form-builder-results-entries__pagination-container">
      <IvyPagination
        v-model:current-page="pagination.page"
        v-model:page-size="pagination.perPage"
        :total="total"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, h, nextTick, onMounted, onUnmounted, reactive, ref, toRaw, watch } from 'vue'
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
import UtilDateFormatter from '@/utils/utilDateFormatter'
import { IVYFORMS_FORM_BUILDER_PAGE } from '@/constants/pages.ts'
import { useNavigation } from '@/composables/useNavigation'
import IvyTooltip from '@/views/_components/tooltip/IvyTooltip.vue'
import { useRoute } from 'vue-router'
import IvyDialog from '@/views/_components/dialog/IvyDialog.vue'
import IvyShortDialog from '@/views/_components/sub-dialog/IvyShortDialog.vue'
import { parsePhoneNumberFromString } from 'libphonenumber-js'
import { formatNumberForField } from '@/utils/utilNumberFormatter'
import type { NumberFormat } from '@/utils/utilNumberFormatter'
import { useGlobalState } from '@/stores/useGlobalState.ts'
import { useSettingsStore } from '@/stores/useSettingsStore'

const { getAdminPageUrl, navigateToAdminPage } = useNavigation()
const { getLabel } = useLabels()
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
const menuButtonRef = ref<InstanceType<typeof IvyIndicatorButton> | HTMLElement | null>(null)
const buttonRefs = ref<Record<number, HTMLElement | null>>({})
const datePickerPopperRef = ref<InstanceType<typeof IvyDatePicker> | HTMLElement | null>(null)
const showEditDialog = ref(false)
const tempColumns = ref([])
const tableData = ref([])
const tableWidth = ref(0)
const total = ref(0)
const selectedDate = ref(null)
const showDatePicker = ref(false)
const loading = ref(true)
const showFiltersIndicator = ref(false)
const showDatePickerIndicator = ref(false)
const hoveredRowId = ref<number | null>(null)
const dynamicEntryColumns = ref([])
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
  const formId = route.params.formId || route.params.id
  const entryId = rowData.id
  const hash = `/manage/${formId}/results/entries/details/${entryId}`
  const href = getAdminPageUrl(IVYFORMS_FORM_BUILDER_PAGE, hash)
  return h(
    IvyLink,
    {
      size: 's',
      href,
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

const renderActionsCell = ({ rowData }: { rowData }) => {
  if (!rowData) return null
  return h(
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
        iconStartType: 'fill',
        ref: (el) => {
          buttonRefs.value[rowData.id] =
            el && '$el' in el && el.$el instanceof HTMLElement ? el.$el : null
        },
        onClick: () => openActions(rowData),
      }),
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
    key: 'actions',
    title: '',
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
            class: 'ivyforms-ml-6',
            style: 'cursor: pointer;',
            role: 'button',
            ariaLabel: getLabel('column_visibility'),
          }),
        ],
      ),
    width: 45,
    minWidth: 45,
    cellRenderer: renderActionsCell,
    visible: true,
    showInModal: false,
    fixed: TableV2FixedDir.RIGHT,
  },
])
interface RowData {
  id: number
  [key: string]: unknown
}

// Find and replace previous dynamicEntryColumnsRaw definition
const dynamicEntryColumnsRaw = computed(() => {
  const entryFields = formBuilderStore.fields || []
  // Filter out security/CAPTCHA fields (recaptcha, turnstile, hcaptcha) from table columns
  const filteredFields = entryFields.filter(
    (field) =>
      field.type !== 'recaptcha' && field.type !== 'turnstile' && field.type !== 'hcaptcha',
  )
  return filteredFields.map((field, index) => ({
    key: `field-${field.fieldIndex}`,
    title: field.label,
    width: 150,
    minWidth: 100,
    cellRenderer: ({ rowData }: { rowData: RowData }) => {
      const fieldValue = allEntriesStore.entryFields.find(
        (entryField) => entryField.entryId == rowData.id && entryField.fieldId == field.id,
      )?.fieldValue
      if (
        (field.type === 'checkbox' || field.type === 'multi-select') &&
        Array.isArray(field.fieldOptions)
      ) {
        const values = (fieldValue || '')
          .split(',')
          .map((v) => v.trim())
          .filter((v) => v)
        const labels = values.map((val) => {
          const opt = field.fieldOptions.find((opt) => opt.value == val)
          return opt ? opt.label : val
        })
        return h('span', labels.join(', '))
      }
      if (
        (field.type === 'radio' || field.type === 'select') &&
        Array.isArray(field.fieldOptions)
      ) {
        const opt = field.fieldOptions.find((opt) => opt.value == fieldValue)
        return h('span', opt ? opt.label : fieldValue || '')
      }
      let displayValue = fieldValue || ''
      if (displayValue && field.type === 'phone') {
        try {
          const pn = parsePhoneNumberFromString(displayValue)
          if (pn) {
            if (field.phoneFormat === 'international') displayValue = pn.formatInternational()
            else if (field.phoneFormat === 'e164') displayValue = pn.number
            else displayValue = pn.formatNational()
          }
        } catch {
          // leave raw value
        }
      }

      if (field.type === 'number') {
        return h(
          'span',
          formatNumberForField(fieldValue, (field.numberFormat || '') as NumberFormat),
        )
      }
      return h('span', (fieldValue as string) || '')
    },
    visible: index < 4,
    showInModal: true,
  }))
})

watch(
  dynamicEntryColumnsRaw,
  (newVal) => {
    // If dynamicEntryColumns is empty, initialize it
    if (dynamicEntryColumns.value.length === 0) {
      dynamicEntryColumns.value = newVal.map((col) => ({ ...col }))
    } else {
      // Update existing columns, preserving visibility state if possible
      dynamicEntryColumns.value = newVal.map((newCol) => {
        const existing = dynamicEntryColumns.value.find((col) => col.key === newCol.key)
        return existing ? { ...newCol, visible: existing.visible } : { ...newCol }
      })
    }
  },
  { immediate: true },
)

// Computed Properties

// Merge dynamic columns into the main columns array
const mergedColumns = ref([])

const updateMergedColumns = () => {
  const baseColumns = columns.value
  const idIndex = baseColumns.findIndex((col) => col.key === 'id')
  const beforeDateCreated = baseColumns.slice(0, idIndex + 1)
  const afterDateCreated = baseColumns.slice(idIndex + 1)
  mergedColumns.value = [...beforeDateCreated, ...dynamicEntryColumns.value, ...afterDateCreated]
}

// Keep mergedColumns in sync
watch([columns, dynamicEntryColumns], updateMergedColumns, { immediate: true })

const dateFormat = computed(() =>
  UtilDateFormatter.convertWPFormat(window.wpIvyDateFormat?.dateFormat || 'D/M/Y', 'dayjs'),
)

// Columns with dynamic width
const dynamicWidthColumns = computed(() => {
  const visibleColumns = mergedColumns.value.filter((col) => col.visible)
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
  return mergedColumns.value.reduce((sum, col) => {
    if (!col.visible) return sum
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
    visibleColumnsCount.value === 1 &&
    tempColumns.value.find((col) => col.key === columnKey)?.visible
  )
}

const isFiltered = computed(
  () =>
    searchQuery.value.trim() !== '' ||
    selectedDate.value ||
    filters.starred !== null ||
    filters.read !== null,
)

// Helper to get formId from route params
function getFormIdFromRoute() {
  // Use formBuilderStore.formId if available, fallback to route params
  let formId = formBuilderStore.formId || route.params.formId || route.params.formId
  if (Array.isArray(formId)) {
    formId = formId[0]
  }
  return formId
}

// Utility Functions
const buildFetchParams = (shouldGetCount: boolean) => ({
  page: pagination.page,
  perPage: pagination.perPage,
  search: debouncedSearchQuery.value,
  sortBy: sort.column,
  sortOrder: sort.order,
  dateRange: UtilDateFormatter.formatDateRangeForApi(selectedDate.value),
  shouldGetCount: shouldGetCount,
  formId: Number(getFormIdFromRoute()),
  filters: {
    starred: filters.starred,
    read: filters.read,
  },
  searchFieldValue: true,
})

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

// Watcher for form ID change: clear tableData and fetch
watch(
  () => getFormIdFromRoute(),
  async (newFormId, oldFormId) => {
    if (newFormId && newFormId !== oldFormId) {
      tableData.value = []
      loading.value = true
      await allEntriesStore.fetchData(buildFetchParams(false))
      tableData.value = allEntriesStore.tableData
      loading.value = false
    }
  },
)

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

watch(
  () => allEntriesStore.paginationMeta,
  (newMeta) => {
    total.value = toRaw(newMeta).total || 0
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

// Watch for action completion by monitoring dialog visibility
watch(
  () => actionEntityStore.dialogVisible,
  (isVisible, wasVisible) => {
    // When dialog closes (was visible, now not visible), refresh data only for delete actions
    if (wasVisible && !isVisible && actionEntityStore.actionType === 'delete') {
      // Small delay to ensure backend operation completed
      setTimeout(() => loadTableData(false), 300)
    }
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

const openColumnVisibility = () => {
  tempColumns.value = mergedColumns.value.map((column) => ({
    key: column.key,
    visible: column.visible,
    showInModal: column.showInModal ?? true,
    title: column.title,
  }))
  showEditDialog.value = true
}
const applyColumnVisibility = () => {
  // Update visibility for mergedColumns and dynamicEntryColumns
  tempColumns.value.forEach((tempColumn) => {
    // Update mergedColumns
    const mergedCol = mergedColumns.value.find((col) => col.key === tempColumn.key)
    if (mergedCol) mergedCol.visible = tempColumn.visible

    // Update dynamicEntryColumns
    const dynamicCol = dynamicEntryColumns.value.find((col) => col.key === tempColumn.key)
    if (dynamicCol) dynamicCol.visible = tempColumn.visible
  })

  showEditDialog.value = false
}

const handleSortChange = ({ column, order }: { column: string; order: 'asc' | 'desc' | null }) => {
  sort.column = column
  sort.order = order === 'asc' ? 'asc' : order === 'desc' ? 'desc' : ''
  pagination.page = 1
  loadTableData(false)
}

const previewEntry = (id: number) => () => {
  const formId = route.params.formId || route.params.id
  const hash = `/manage/${formId}/results/entries/details/${id}`
  navigateToAdminPage(IVYFORMS_FORM_BUILDER_PAGE, hash)
}
const unselectAllRows = () => {
  tableData.value.forEach((row) => {
    row.selected = false
  })
}

const toggleSelectAll = (value: boolean) => {
  tableData.value.forEach((row) => (row.selected = value))
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

const openActions = debounce((rowData) => {
  const id = rowData.id
  const status = rowData.status
  const buttonElement = buttonRefs.value[id]
  if (!buttonElement) {
    console.warn(getLabel('menu_button_element_not_found'))
    return
  }

  hoveredRowId.value = id

  // Add highlighted class to all rows with matching rowkey
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
      createContextMenuAction(
        status === 'unread' ? ContextMenuActionType.MarkAsRead : ContextMenuActionType.MarkAsUnread,
        {
          iconSize: 's',
          handler: async (id) => {
            const newStatus = rowData.status === 'unread' ? 'read' : 'unread'
            await allEntriesStore.updateStatus(id, newStatus)
            rowData.status = newStatus
            if (rowData.status === 'read') {
              filterCount.readTrueCount++
              filterCount.readFalseCount--
            } else {
              filterCount.readTrueCount--
              filterCount.readFalseCount++
            }
          },
        },
      ),
      createContextMenuAction(ContextMenuActionType.Preview, {
        iconSize: 's',
        handler: previewEntry(id),
      }),
      createContextMenuAction(ContextMenuActionType.Delete, {
        iconSize: 's',
        handler: async (entityId) => {
          await actionEntityStore.handleActionClick(null, [entityId], 'entries', 'delete', {
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

// Lifecycle Hooks
onMounted(() => {
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
.ivyforms-date-picker-popper.el-popper.results-picker {
  inset: 180px auto auto 779px !important;
}

.form-builder-results-entries {
  height: 100%;
  overflow: hidden;

  &__option-bar {
    background: var(--map-ground-level-1-foreground);

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

  &__table-skeleton {
    .form-builder-results-entries__skeleton-row {
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

    &-cell {
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;

      div {
        display: flex;
        align-items: center;
        align-content: center;
        justify-content: center;
        flex-wrap: wrap;
      }

      &.ivyforms-custom-link {
        .ivyforms-unread-entry {
          .el-link {
            color: var(--map-base-brand-symbol-0) !important;
          }
        }
      }
    }

    &-cell > span {
      display: block;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      max-width: 100%;
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

    &.highlighted {
      background: var(--map-hover) !important;
    }
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
}
</style>
