<template>
  <div
    class="ivyforms-settings-notification ivyforms-flex ivyforms-flex-1 ivyforms-flex-direction-column ivyforms-gap-24 ivyforms-pr-20"
  >
    <div
      v-if="!isAddingNotification"
      class="ivyforms-flex-1 ivyforms-flex ivyforms-flex-direction-column"
    >
      <div class="ivyforms-settings-notification__option-bar ivyforms-flex ivyforms-gap-8">
        <div class="ivyforms-settings-notification__option-bar-left ivyforms-flex ivyforms-gap-8">
          <IvySearch
            v-model="searchQuery"
            :placeholder="getLabel('search_notifications')"
            clearable
          ></IvySearch>
        </div>
        <div class="ivyforms-settings-notification__option-bar-right ivyforms-flex ivyforms-gap-8">
          <IvyButtonAction icon-start="plus" @click="openNotificationBuilder">
            {{ getLabel('notifications') }}
          </IvyButtonAction>
        </div>
      </div>
      <div
        class="ivyforms-settings-notification__table ivyforms-gap-24 ivyforms-flex ivyforms-flex-1 ivyforms-flex-direction-column"
      >
        <IvyTable
          :columns="columns"
          :data="shouldShowSkeleton ? [] : tableData"
          :loading="shouldShowSkeleton"
          :sort="sort"
          :total="total"
          :row-key="'id'"
          pagination
          @sort-change="handleSortChange"
        >
          <template #empty>
            <div v-if="shouldShowSkeleton" class="ivyforms-table-skeleton">
              <IvySkeleton>
                <template #template>
                  <IvySkeletonItem
                    v-for="n in pagination.perPage"
                    :key="n"
                    class="ivyforms-skeleton-row"
                    style="min-height: 48px; margin-bottom: 8px"
                  ></IvySkeletonItem>
                </template>
              </IvySkeleton>
            </div>
            <PageEmptyState
              v-else-if="!isFiltered"
              image="not-found"
              :title="getLabel('empty_state_notification_title')"
              :subtitle="getLabel('empty_state_notification_subtitle')"
            >
              <template #actionButton>
                <IvyButtonAction icon-start="plus" @click="openNotificationBuilder">
                  {{ getLabel('notifications') }}
                </IvyButtonAction>
              </template>
            </PageEmptyState>
            <PageEmptyState
              v-else
              :title="getLabel('empty_search_title')"
              :subtitle="getLabel('empty_search_subtitle')"
            />
          </template>
        </IvyTable>

        <div class="ivyforms-all-forms-pagination-container">
          <IvyPagination
            v-model:current-page="pagination.page"
            v-model:page-size="pagination.perPage"
            :total="total"
          />
        </div>
      </div>
    </div>
    <div v-else>
      <FormBuilderManageNotification @close="closeNotificationBuilder" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch, h, reactive, toRaw, nextTick } from 'vue'
import { debounce } from 'lodash'
import { useNotificationSettingBuilder } from '@/stores/useNotificationSettingBuilder.ts'
import IvyButtonAction from '@/views/_components/button/IvyButtonAction.vue'
import IvySearch from '@/views/_components/search/IvySearch.vue'
import IvyTable from '@/views/_components/table/IvyTable.vue'
import IvyPagination from '@/views/_components/pagination/IvyPagination.vue'
import IvyToggle from '@/views/_components/toggle/IvyToggle.vue'
import IvySkeleton from '@/views/_components/skeleton/IvySkeleton.vue'
import IvySkeletonItem from '@/views/_components/skeleton/IvySkeletonItem.vue'
import PageEmptyState from '@/views/admin/parts/PageEmptyState.vue'
import { useFormBuilder } from '@/stores/useFormBuilder.ts'
import FormBuilderManageNotification from './FormBuilderManageNotification.vue'
import type { TableColumn } from '@/views/_components/table/IvyTableColumn.ts'
import { useRoute } from 'vue-router'
import { useRouter } from 'vue-router'
import { useLabels } from '@/composables/useLabels'
import { useActionEntityStore } from '@/stores/actionEntityStore'
import IvyLink from '@/views/_components/link/IvyLink.vue'
import IvyTooltip from '@/views/_components/tooltip/IvyTooltip.vue'
import { useWcagColors } from '@/composables/useWcagColors'
import { TableV2FixedDir } from 'element-plus'

const { getLabel } = useLabels()
const { startWatching } = useWcagColors()
startWatching()

const formBuilderStore = useFormBuilder()
const notificationStore = useNotificationSettingBuilder()
const actionEntityStore = useActionEntityStore()

// State and refs
const searchQuery = ref('')
const debouncedSearchQuery = ref('')
const loading = ref(false)
const isAddingNotification = ref(false)
const isEditingNotification = ref(false)
const tableData = ref([])
const total = ref(0)
const hoveredRowId = ref<number | null>(null)

const route = useRoute()
const router = useRouter()

// Debounced search
const updateDebouncedSearch = debounce((val) => {
  debouncedSearchQuery.value = val
}, 500)
const isDebouncing = computed(() => searchQuery.value !== debouncedSearchQuery.value)

// Combined loader state used both by the table and the empty-slot skeleton
const shouldShowSkeleton = computed(() => loading.value || isDebouncing.value)

// Pagination and sorting
const pagination = reactive({
  page: 1,
  perPage: 10,
})

const sort = reactive({
  column: 'id',
  order: 'desc',
})

// Computed properties
const isFiltered = computed(() => searchQuery.value.trim().length > 0)

// Utility functions
const buildFetchParams = () => ({
  page: pagination.page,
  perPage: pagination.perPage,
  search: debouncedSearchQuery.value,
  sortBy: sort.column,
  sortOrder: sort.order,
  formId: route.params.formId
    ? parseInt(
        Array.isArray(route.params.formId) ? route.params.formId[0] : route.params.formId,
        10,
      )
    : formBuilderStore.formId
      ? parseInt(formBuilderStore.formId, 10)
      : null,
})

// Load table data using server-side search
const loadTableData = async () => {
  loading.value = true
  try {
    await notificationStore.searchNotifications(buildFetchParams())
    tableData.value = notificationStore.tableData
    const meta = notificationStore.paginationMeta
    pagination.page = meta.page
    pagination.perPage = meta.perPage
    total.value = meta.total
  } catch (error) {
    console.error(getLabel('error_loading_notification'), error)
  } finally {
    loading.value = false
    await syncHoverWithinTable()
  }
}

// Watchers
watch(searchQuery, updateDebouncedSearch)

// Watch for action completion by monitoring dialog visibility
watch(
  () => actionEntityStore.dialogVisible,
  (isVisible, wasVisible) => {
    // When dialog closes (was visible, now not visible), refresh data only for delete/duplicate actions
    if (
      wasVisible &&
      !isVisible &&
      (actionEntityStore.actionType === 'delete' || actionEntityStore.actionType === 'duplicate')
    ) {
      // Small delay to ensure backend operation completed
      setTimeout(loadTableData, 300)
    }
  },
)

// Fetch notifications on pagination, search, and filter change
watch(
  [() => pagination.page, () => pagination.perPage, () => debouncedSearchQuery.value],
  async () => {
    loading.value = true
    await notificationStore.searchNotifications(buildFetchParams())
    tableData.value = notificationStore.tableData
    loading.value = false
  },
)

watch(
  () => notificationStore.tableData,
  (newData) => {
    tableData.value = newData
    loading.value = false
  },
  { immediate: true },
)

watch(
  () => notificationStore.paginationMeta,
  (newMeta) => {
    total.value = toRaw(newMeta).total || 0
  },
  { immediate: true },
)

// Methods
const handleSortChange = ({ column, order }: { column: string; order: 'asc' | 'desc' | null }) => {
  sort.column = column
  sort.order = order === 'asc' ? 'asc' : order === 'desc' ? 'desc' : 'asc'
  pagination.page = 1
  loadTableData()
}

// Redirect to New Notification page
const openNotificationBuilder = () => {
  const formIdParam = Array.isArray(route.params.formId)
    ? route.params.formId[0]
    : route.params.formId || formBuilderStore.formId
  notificationStore.resetNotification(formIdParam)
  isAddingNotification.value = true
  isEditingNotification.value = false
  router.replace(`/manage/${formIdParam}/settings/notification/manage`)
}

const closeNotificationBuilder = () => {
  isAddingNotification.value = false
  isEditingNotification.value = false
  loadTableData()
  router.push({ name: 'form-notifications', params: { formId: formBuilderStore.formId } })
}

// Redirect to Edit Notification page
const editNotification = (id: number) => async () => {
  loading.value = true
  try {
    await notificationStore.loadNotification(id)
    isAddingNotification.value = true
    isEditingNotification.value = true
  } catch (error) {
    console.error(getLabel('error_loading_notification'), error)
  } finally {
    loading.value = false
    const formIdParam = Array.isArray(route.params.formId)
      ? route.params.formId[0]
      : route.params.formId || formBuilderStore.formId
    await router.push(`/manage/${formIdParam}/settings/notification/manage/${id}`)
  }
}

const duplicateNotification = (id: number) => async () => {
  await actionEntityStore.handleActionClick(id, null, 'notification', 'duplicate', {
    setLoading: (isLoading: boolean) => {
      loading.value = isLoading
    },
  })
}

const deleteNotification = (id: number) => async () => {
  await actionEntityStore.handleActionClick(id, null, 'notification', 'delete', {
    setLoading: (isLoading) => {
      loading.value = isLoading
    },
  })
}

// Cell renderers
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
            onClick: editNotification(rowData.id),
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
            onClick: duplicateNotification(rowData.id),
          }),
        ],
      ),
      h(
        IvyTooltip,
        {
          content: getLabel('delete'),
          placement: 'top',
        },
        () => [
          h(IvyButtonAction, {
            iconOnly: true,
            iconStart: 'trash',
            size: 's',
            type: 'ghost',
            priority: 'danger',
            iconStartType: 'outline',
            onClick: deleteNotification(rowData.id),
          }),
        ],
      ),
    ],
  )
}

const renderPublishedCell = ({ rowData }) => {
  if (!rowData) return null
  return h(IvyToggle, {
    modelValue: rowData.enabled,
    size: 's',
    'onUpdate:modelValue': async (value) => {
      rowData.enabled = value
      await notificationStore.updateNotification(rowData.id, rowData)
      await loadTableData()
    },
  })
}

const renderNameCell = ({ rowData }: { rowData }) => {
  if (!rowData) return null

  return h(
    IvyLink,
    {
      size: 's',
      href: '#',
      onClick: editNotification(rowData.id),
      class: 'ivyforms-name-cell',
    },
    () => [rowData.name || 'No Name'],
  )
}

const renderReceiverCell = ({ rowData }: { rowData }) => {
  if (!rowData) return null
  return h('span', rowData.receiver || 'No Receiver')
}

const renderReplyToCell = ({ rowData }: { rowData }) => {
  if (!rowData) return null
  return h('span', rowData.replyTo || 'No Reply-To')
}

const renderSubjectCell = ({ rowData }: { rowData }) => {
  if (!rowData) return null
  return h('span', rowData.subject || 'No Subject')
}

const columns = ref<TableColumn[]>([
  {
    key: 'enabled',
    title: getLabel('active'),
    cellRenderer: renderPublishedCell,
    width: 100,
    visible: true,
    class: 'status-column',
  },
  {
    key: 'name',
    title: getLabel('name'),
    cellRenderer: renderNameCell,
    width: 300,
    visible: true,
    class: 'name-column',
    sortable: true,
  },
  {
    key: 'receiver',
    title: getLabel('receiver'),
    cellRenderer: renderReceiverCell,
    width: 250,
    visible: true,
    class: 'receiver-column',
    sortable: true,
  },
  {
    key: 'replyTo',
    title: getLabel('reply_to'),
    cellRenderer: renderReplyToCell,
    width: 250,
    visible: true,
    class: 'replyto-column',
    sortable: true,
  },
  {
    key: 'subject',
    title: getLabel('subject'),
    cellRenderer: renderSubjectCell,
    width: 300,
    visible: true,
    class: 'subject-column',
    sortable: true,
  },
  {
    key: 'actions',
    title: '',
    cellRenderer: renderActionsCell,
    width: 124,
    minWidth: 124,
    visible: true,
    class: 'ivyforms-cell-actions',
    fixed: TableV2FixedDir.RIGHT,
  },
])
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
// Lifecycle hooks
onMounted(() => {
  notificationStore.isEditing = false
  loadTableData()
})
watch(
  () => tableData.value,
  async () => {
    await syncHoverWithinTable()
  },
)
</script>

<style lang="scss">
.ivyforms-settings-notification {
  height: 100%;
  overflow: hidden;

  &__option-bar {
    align-items: center;
    justify-content: space-between;
    background: var(--map-ground-level-1-foreground);

    &-left,
    &-right {
      position: relative;
    }

    &-left {
      align-items: center;
    }

    &-right {
      align-items: flex-end;
    }
  }

  &__table {
    padding-top: 24px;

    .ivyforms-table-skeleton {
      .ivyforms-skeleton-row {
        height: 48px;
        margin-bottom: 8px;
        border-radius: 4px;
        animation: pulse 1.5s ease-in-out infinite;
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
}
</style>
