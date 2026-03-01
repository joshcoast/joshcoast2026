<template>
  <PagePanel :width="420" class="ivyforms-p-20">
    <div
      class="ivyforms-entry-page-section-title header-with-actions ivyforms-flex ivyforms-align-items-center ivyforms-justify-content-space-between ivyforms-gap-8"
    >
      <span class="medium-18">
        {{ getLabel('general_details') }}
      </span>
      <IvyTooltip :content="getLabel('actions')" placement="top">
        <IvyButtonAction
          ref="actionButtonRef"
          icon-only
          icon-start="context-menu-dot"
          size="s"
          type="ghost"
          priority="tertiary"
          icon-start-type="outline"
          @click="openActions"
        />
      </IvyTooltip>
    </div>

    <div class="ivyforms-entry-page-section-content">
      <table class="ivyforms-entry-details-table ivyforms-width-100">
        <tbody>
          <tr v-for="detail in formattedEntryDetails" :key="detail.key">
            <td class="ivyforms-key-column medium-14 ivyforms-px-8">{{ detail.label }}</td>
            <td class="ivyforms-value-column ivyforms-width-100 ivyforms-px-8 regular-14">
              <template v-if="detail.label === getLabel('status')">
                <div
                  class="ivyforms-flex ivyforms-align-items-center ivyforms-justify-content-between"
                >
                  <span>{{
                    entry[detail.key].charAt(0).toUpperCase() + entry[detail.key].slice(1)
                  }}</span>
                  <IvyButtonAction
                    icon-only
                    :icon-start="entry[detail.key] === 'read' ? 'read' : 'unread'"
                    size="s"
                    type="ghost"
                    priority="tertiary"
                    icon-start-type="outline"
                    @click="updateEntryStatus(entry[detail.key])"
                  />
                </div>
              </template>
              <template v-else-if="detail.label === getLabel('submitted_on')">
                {{ UtilDateFormatter.formatWPDate(entry[detail.key]) }}
              </template>
              <template v-else-if="detail.key === 'sourceURL'">
                <IvyLink :href="entry[detail.key]" target="_blank" size="s" rel="noreferrer">
                  {{ entry[detail.key] }}
                </IvyLink>
              </template>
              <template v-else-if="detail.key === 'author'">
                <template v-if="entry.userId === null || entry.userId === undefined">
                  {{ getLabel('guest') }}
                </template>
                <template v-else>
                  <IvyLink
                    :href="`${wordPressAdminUrl}user-edit.php?user_id=${entry.userId}`"
                    target="_blank"
                    size="s"
                    rel="noreferrer"
                  >
                    {{ entry[detail.key] }}
                  </IvyLink>
                </template>
              </template>
              <template v-else>
                {{ entry[detail.key] }}
              </template>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </PagePanel>
</template>

<script setup lang="ts">
import { computed, inject, type Ref, ref } from 'vue'
import { useAllEntries } from '@/stores/useAllEntries.ts'
import { useLabels } from '@/composables/useLabels.ts'
import { useFormBuilder } from '@/stores/useFormBuilder.ts'
import { useRoute, useRouter } from 'vue-router'
import { entryDetails } from '@/constants/generalEntryDetails.ts'
import {
  IVYFORMS_RESULTS_ENTRY_DETAILS,
  IVYFORMS_ENTRIES,
  IVYFORMS_ENTRY_DETAILS,
  IVYFORMS_FORM_RESULTS,
} from '@/constants/pages.ts'
import type { Entry } from '@/types/entry'
import UtilDateFormatter from '@/utils/utilDateFormatter.ts'
import { useContextMenuStore } from '@/stores/contextMenuStore.ts'
import {
  ContextMenuActionType,
  createContextMenuAction,
} from '@/views/_components/context-menu/kit/ContextMenuActionType.ts'
import IvyButtonAction from '@/views/_components/button/IvyButtonAction.vue'
import { useActionEntityStore } from '@/stores/actionEntityStore'

const route = useRoute()
const router = useRouter()
const allEntriesStore = useAllEntries()
const formBuilderStore = useFormBuilder()
const { getLabel } = useLabels()
const entry = inject<Ref<Entry>>('entry')
const contextMenuStore = useContextMenuStore()
const actionEntityStore = useActionEntityStore()
const deleteLoading = ref(false)
const actionButtonRef = ref<InstanceType<typeof IvyButtonAction> | HTMLElement | null>(null)

const formattedEntryDetails = computed(() => {
  return entryDetails.filter(
    (detail) => entry?.value && Object.prototype.hasOwnProperty.call(entry.value, detail.key),
  )
})

const wordPressAdminUrl = computed(() => {
  return window.IvyForms?.adminUrl || getAdminUrl()
})

/**
 * Get WordPress admin URL
 * Uses window.IvyForms.adminUrl if available, otherwise constructs from window.location
 */
const getAdminUrl = (): string => {
  try {
    // Try to get from admin_url endpoint via REST API root
    if (window.IvyForms?.useApiClient) {
      const apiClient = window.IvyForms.useApiClient()
      if (apiClient?.root) {
        // Extract admin URL from REST API root
        const match = apiClient.root.match(/(.+?)\/wp-json\/?/)
        if (match) {
          return match[1] + '/wp-admin/'
        }
      }
    }
    // Fallback: construct from current location pathname
    // Extract base path from current URL (handles subdirectory installations)
    const pathname = window.location.pathname
    const wpAdminIndex = pathname.indexOf('/wp-admin/')
    if (wpAdminIndex !== -1) {
      const basePath = pathname.substring(0, wpAdminIndex)
      return window.location.origin + basePath + '/wp-admin/'
    }
    // Final fallback if wp-admin not found in path
    return window.location.origin + '/wp-admin/'
  } catch {
    // Absolute final fallback
    return '/wp-admin/'
  }
}

const getCurrentEntryId = () => {
  if (route.name === IVYFORMS_RESULTS_ENTRY_DETAILS) {
    return route.params.entryId
  }
  return route.params.id
}

const updateEntryStatus = async (oldStatus: string) => {
  try {
    const newStatus = oldStatus === 'read' ? 'unread' : 'read'
    const currentEntryId = getCurrentEntryId()
    await allEntriesStore.updateStatus(Number(currentEntryId), newStatus)
    if (entry?.value) {
      entry.value.status = newStatus
    }
    // Redirect if status changed from read to unread
    if (oldStatus === 'read' && newStatus === 'unread') {
      if (route.name === IVYFORMS_RESULTS_ENTRY_DETAILS) {
        await router.push({ name: IVYFORMS_FORM_RESULTS, params: { id: route.params.formId } })
      } else if (route.name === IVYFORMS_ENTRY_DETAILS || route.name === IVYFORMS_ENTRIES) {
        formBuilderStore.formId = null
        allEntriesStore.reset()
        await router.push({ name: IVYFORMS_ENTRIES })
      }
    }
  } catch (error) {
    console.error(getLabel('failed_to_update_entry_status'), error)
  }
}

const openActions = () => {
  if (contextMenuStore.isOpen) {
    contextMenuStore.closeContextMenu()
    return
  }

  const id = Number(getCurrentEntryId())
  if (!id) return

  const btnRef = actionButtonRef.value
  if (!btnRef) return

  // Extract HTMLElement from component instance
  const buttonElement =
    btnRef && '$el' in btnRef && btnRef.$el instanceof HTMLElement
      ? btnRef.$el
      : btnRef instanceof HTMLElement
        ? btnRef
        : null

  if (!buttonElement) return

  contextMenuStore.openContextMenu({
    actions: [
      createContextMenuAction(ContextMenuActionType.Delete, {
        handler: async () => {
          contextMenuStore.closeContextMenu()
          await actionEntityStore.handleActionClick(id, null, 'entry', 'delete', {
            setLoading: (isLoading) => {
              deleteLoading.value = isLoading
            },
            onSuccess: async () => {
              if (route.name === IVYFORMS_RESULTS_ENTRY_DETAILS) {
                await router.push({
                  name: IVYFORMS_FORM_RESULTS,
                  params: { id: route.params.formId },
                })
              } else {
                formBuilderStore.formId = null
                await router.push({ name: IVYFORMS_ENTRIES })
              }
            },
          })
        },
      }),
    ],
    contextMenuButtonRef: buttonElement,
  })
}
</script>

<style scoped lang="scss">
.ivyforms-page-panel {
  display: flex;
  padding: 20px;
  flex-direction: column;
  align-items: flex-start;
  gap: 24px;

  .ivyforms-entry-page-section-content {
    width: 100%;

    .ivyforms-entry-details-table {
      td {
        text-align: left;
        border-bottom: 1px solid var(--map-divider);
        height: 48px;
        color: var(--map-base-text-0);
      }

      .ivyforms-key-column {
        min-width: 104px;
        box-sizing: border-box;
      }

      .ivyforms-value-column {
        max-width: none;
        overflow-wrap: break-word;
        word-break: break-all;
        white-space: normal;
        display: table-cell;

        // Override IvyLink centering
        ::v-deep(.ivyforms-link) {
          text-align: left;
          display: block;
        }
        ::v-deep(.ivyforms-link .el-link__inner) {
          text-align: left;
          display: block;
        }
      }
    }
  }
}
</style>
