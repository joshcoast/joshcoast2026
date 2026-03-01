<template>
  <div
    ref="tableContainerRef"
    class="ivyforms-table__container ivyforms-flex ivyforms-flex-direction-column ivyforms-width-100"
  >
    <!-- Slot for custom header (when row is selected) -->
    <div v-if="isCustomHeader" class="ivyforms-table__custom-header ivyforms-pt-2">
      <slot name="header" />
    </div>
    <IvyScrollbar ref="scrollbarRef">
      <ElAutoResizer :class="{ loading: props.loading }">
        <template #default="{ width }">
          <ElTableV2
            v-bind="$attrs"
            :columns="formattedColumns"
            :data="props.data"
            :width="Math.max(width, props.minTableWidth)"
            :height="tableHeight"
            class="ivyforms-table"
            :fixed="props.fixed"
            :row-class="getRowClass"
          >
            <!-- Slot for custom column headers with sorting -->
            <template #header-cell="{ column }">
              <div
                class="ivyforms-flex ivyforms-align-items-center"
                :style="{
                  cursor: column.sortable ? 'pointer' : 'default',
                }"
                @click="emitSortChange(column)"
              >
                <component :is="column.headerRenderer()" v-if="column.headerRenderer" />

                <div
                  v-else-if="column.comingSoon"
                  class="ivyforms-flex ivyforms-align-items-center ivyforms-gap-8"
                >
                  <div class="ivyforms-table__container__coming-soon-title">{{ column.title }}</div>
                  <ComingSoonBadge size="s" />
                </div>
                <span v-else>{{ column.title }}</span>

                <!-- Sorting icons -->
                <span v-if="column.sortable">
                  <span v-if="sortColumn === column.key">
                    <span v-if="sortOrder === 'asc'">
                      <IvyIcon
                        size="d"
                        outer-size="20px"
                        :name="isDarkMode ? 'sort-asc-dark' : 'sort-asc'"
                        category="table"
                      />
                    </span>
                    <span v-else-if="sortOrder === 'desc'">
                      <IvyIcon
                        size="d"
                        outer-size="20px"
                        :name="isDarkMode ? 'sort-desc-dark' : 'sort-desc'"
                        category="table"
                      />
                    </span>
                  </span>
                  <span v-else>
                    <IvyIcon
                      size="d"
                      outer-size="20px"
                      name="arrow-up-down-2"
                      category="arrows"
                      type="outline"
                      color="var(--map-base-dusk-symbol-2)"
                    />
                  </span>
                </span>
              </div>
            </template>

            <!-- Slot for empty table/no filters match -->
            <template #empty>
              <slot name="empty" />
            </template>
          </ElTableV2>
        </template>
      </ElAutoResizer>
    </IvyScrollbar>
  </div>
</template>

<script lang="ts" setup>
import { computed, ref, h, onMounted, onUnmounted, watch, nextTick } from 'vue'
import type { TableColumn } from '@/views/_components/table/IvyTableColumn.ts'
import type { CellRenderProps } from '@/views/_components/table/IvyCellRendererProps.ts'
import IvyScrollbar from '@/views/_components/scrollbar/IvyScrollbar.vue'

interface Props {
  data: Array<{ [key: string]: string | number | boolean }>
  columns?: TableColumn[]
  loading?: boolean
  pagination?: boolean
  groupBy?: string
  fixed?: boolean
  isCustomHeader?: boolean
  minTableWidth?: number
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
  pagination: false,
  isCustomHeader: false,
  columns: () => [],
  groupBy: '',
  minTableWidth: 240,
})

const emit = defineEmits<{
  (e: 'sort-change', sortEvent: { column: string | null; order: 'asc' | 'desc' | null }): void
}>()

// Sort icons based on theme
const isDarkMode = ref(document.body.classList.contains('ivyforms-theme-dark'))

const observer = new MutationObserver(() => {
  isDarkMode.value = document.body.classList.contains('ivyforms-theme-dark')
})

const tableContainerRef = ref<HTMLElement | null>(null)
const scrollbarRef = ref<InstanceType<typeof IvyScrollbar> | null>(null)
const flooredWidth = ref<number>(0)
const tableHeight = ref<number>(550)

onMounted(() => {
  if (tableContainerRef.value) {
    const updateHeight = () => {
      const rowHeight = 50
      const numRows = props.data.length + 1
      const maxTableHeight = numRows * rowHeight

      // Get the parent of the ElResizer element
      const tableContainer = tableContainerRef.value
      const parentOfParent = tableContainer?.parentElement?.parentElement as HTMLElement | null
      const parentOfParentHeight = parentOfParent ? parentOfParent.offsetHeight : 0
      // If pagination is enabled, subtract the height of the pagination component
      const finalMaxHeight = props.pagination ? parentOfParentHeight - 160 : parentOfParentHeight

      // Set a minimum height for the table when there is no data - if there is only one row, it should be at least 550px
      const minHeight = numRows > 1 ? 0 : 550
      const newHeight = Math.max(minHeight, Math.min(maxTableHeight, finalMaxHeight))

      // Only update if the difference is significant to prevent micro-adjustments
      if (Math.abs(newHeight - tableHeight.value) > 1) {
        tableHeight.value = newHeight
      }
    }

    // Keep a small stable-read check so we only emit when width is stable
    let lastMeasured = 0
    let stableCount = 0
    const STABLE_REQUIRED = 2
    const HYSTERESIS = 3 // px

    const updateDimensions = (entry?: ResizeObserverEntry) => {
      window.requestAnimationFrame(() => {
        // Measure width
        const measured =
          entry?.contentRect?.width || tableContainerRef.value?.clientWidth || props.minTableWidth
        const newWidth = Math.max(props.minTableWidth, Math.floor(measured))

        if (Math.abs(newWidth - lastMeasured) <= HYSTERESIS) {
          stableCount += 1
        } else {
          stableCount = 0
        }

        lastMeasured = newWidth

        // Only update flooredWidth when measurement has been stable
        if (
          stableCount >= STABLE_REQUIRED &&
          Math.abs(newWidth - flooredWidth.value) > HYSTERESIS
        ) {
          flooredWidth.value = newWidth
        }

        // Update height
        updateHeight()
      })
    }

    // Run once initially
    updateDimensions()

    const onWindowResize = () => updateDimensions()
    window.addEventListener('resize', onWindowResize)
    const resizeObserver = new window.ResizeObserver((entries) => {
      // Use the first entry (the container) to measure contentRect.width
      if (entries && entries.length > 0) {
        updateDimensions(entries[0])
      } else {
        updateDimensions()
      }
    })
    if (tableContainerRef.value) resizeObserver.observe(tableContainerRef.value)
    onUnmounted(() => {
      window.removeEventListener('resize', onWindowResize)
      resizeObserver.disconnect()
    })
  }
})

observer.observe(document.body, { attributes: true, attributeFilter: ['class'] })

onUnmounted(() => {
  observer.disconnect()
})

// Watch for data changes to update table height
watch(
  () => props.data.length,
  () => {
    const rowHeight = 50
    const numRows = props.data.length + 1
    const maxTableHeight = numRows * rowHeight

    const tableContainer = tableContainerRef.value
    const parentOfParent = tableContainer?.parentElement?.parentElement as HTMLElement | null
    const parentOfParentHeight = parentOfParent ? parentOfParent.offsetHeight : 0
    const finalMaxHeight = props.pagination ? parentOfParentHeight - 160 : parentOfParentHeight

    const minHeight = numRows > 1 ? 0 : 550
    const newHeight = Math.max(minHeight, Math.min(maxTableHeight, finalMaxHeight))

    if (Math.abs(newHeight - tableHeight.value) > 1) {
      tableHeight.value = newHeight
    }
  },
)

// Watch for column changes to update scrollbar (fixes horizontal scrollbar not appearing after column visibility change)
watch(
  () => props.columns?.filter((col) => col.visible).length,
  () => {
    nextTick(() => {
      scrollbarRef.value?.update()
      window.dispatchEvent(new Event('resize'))
    })
  },
)

// Format columns to use custom components inside headers and cells
const formattedColumns = computed(
  () =>
    props.columns
      ?.filter((col) => col.visible)
      .map((col) => ({
        ...col,
        sortable: col.sortable || false,
        headerRenderer: col.headerRenderer || undefined,
        cellRenderer:
          col.cellRenderer || ((props: CellRenderProps) => h('span', {}, props.rowData[col.key])),
        minWidth:
          typeof col.minWidth === 'string' ? parseInt(col.minWidth, 10) || 100 : col.minWidth, // Ensure minWidth is a number
        width: typeof col.width === 'string' ? parseInt(col.width, 10) || 100 : col.width, // Ensure width is a number
      })) || [],
)

// Sorting state (only for UI purposes)
const sortColumn = ref<string | null>(null)
const sortOrder = ref<'asc' | 'desc' | null>(null)

// Emit sorting changes
const emitSortChange = (column) => {
  if (!column.sortable) return

  if (sortColumn.value === column.key) {
    // Toggle order
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    // New column selected, set ascending order
    sortColumn.value = column.key
    sortOrder.value = 'asc'
  }

  // Emit the sorting change event
  const sortEvent = {
    column: sortColumn.value,
    order: sortOrder.value,
  }
  emit('sort-change', sortEvent)
}

// Custom row classes
const getRowClass = ({ rowData }: { rowData: Record<string, unknown> }) => {
  const classes: string[] = []
  if (rowData.status === 'unread') classes.push('ivyforms-unread-entry')
  if (rowData.selected) classes.push('is-selected')
  return classes.join(' ')
}
</script>

<style lang="scss">
.ivyforms-table__container {
  position: relative;

  &__coming-soon-title {
    opacity: 0.5;
    cursor: not-allowed;
  }

  // Horizontal scrollbar opacity to match vertical
  .el-virtual-scrollbar {
    bottom: unset !important;
  }

  .el-scrollbar__thumb {
    opacity: var(--el-scrollbar-opacity, 0.3) !important;

    &:hover {
      background-color: var(
        --el-scrollbar-hover-bg-color,
        var(--el-text-color-secondary)
      ) !important;
      opacity: var(--el-scrollbar-hover-opacity, 0.5) !important;
    }
  }

  .ivyforms-table__custom-header {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    z-index: 10;
    background: var(--map-ground-level-1-foreground);
    border-bottom: 1px solid var(--map-divider);
  }

  .el-auto-resizer {
    &.loading {
      width: 100%;
      height: 100%;
      min-width: 0;
      min-height: 0;
      overflow: hidden;
    }
  }

  .ivyforms-table {
    // Header
    .el-table-v2__header {
      border-bottom: 1px solid var(--map-divider);
      background: var(--map-ground-level-1-foreground);
      transition: none;

      // Columns
      .el-table-v2__header-cell {
        display: flex;
        background: var(--map-ground-level-1-foreground);
        color: var(--map-base-text-0);
        text-align: center;
        font-size: 14px;
        font-style: normal;
        font-weight: 400;
        line-height: 20px;

        &:has(.el-checkbox) {
          justify-content: center;
        }

        // Sortable
        &.is-sortable {
          span {
            margin-left: 0 !important;
          }
          .el-table-v2__sort-icon {
            svg {
              display: none;
            }
          }
        }
      }
    }

    // Hide all checkbox labels
    .el-checkbox__label {
      display: none;
    }

    // Body
    .el-table-v2__body {
      background: var(--map-ground-level-1-foreground);
      transition: none;

      // Row cells with checkbox - modern browsers support
      .el-table-v2__row-cell {
        &:has(.el-checkbox) {
          justify-content: center;
        }
      }

      // Alternative approach for older browsers
      .el-table-v2__row-cell.el-checkbox-cell {
        justify-content: center;
      }

      // Rows
      .el-table-v2__row {
        display: flex;
        height: 48px;
        align-items: center;
        flex-shrink: 0;
        border-bottom: 1px solid var(--map-divider);
        background: var(--map-ground-level-1-foreground);
        color: var(--map-base-dusk-symbol-2);
        transition: none;

        &:hover,
        &.hovered {
          background: var(--map-hover);
        }

        // Selected
        &.is-selected {
          background:
            linear-gradient(0deg, var(--map-accent-amber-o05) 0%, var(--map-accent-amber-o05) 100%),
            var(--map-ground-level-1-foreground);
        }
      }
    }
  }
}

.el-skeleton {
  &.ivyforms-skeleton {
    margin: 1px;

    .ivyforms-skeleton-item {
      height: 48px;
    }
  }
}
</style>
