<template>
  <PagePanel>
    <template v-if="!loading">
      <IvyMenuAccordion
        v-model="activeIndex"
        :menu-items="menuItems"
        :auto-expand-security="true"
        :use-router="false"
        class="ivyforms-settings__menu ivyforms-pr-12"
        @menu-select="onMenuSelect"
      />
    </template>
    <template v-else>
      <div>Loading settings...</div>
    </template>
  </PagePanel>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useFormBuilder } from '@/stores/useFormBuilder.ts'
import { useConfirmationSettingBuilder } from '@/stores/useConfirmationSettingBuilder.ts'
import { useLabels } from '@/composables/useLabels'
import { useUnsavedChangesStore } from '@/stores/useUnsavedChangesStore'
import IvyMenuAccordion from '@/views/_components/menu/IvyMenuAccordion.vue'
import type { MenuItem } from '@/views/_components/menu/IvyMenuAccordion.vue'
import { useSettingsStore } from '@/stores/useSettingsStore.ts'
const { getLabel } = useLabels()
const router = useRouter()
const route = useRoute()
const unsavedChangesStore = useUnsavedChangesStore()
const formBuilderStore = useFormBuilder()
const confirmationStore = useConfirmationSettingBuilder()
const settingsStore = useSettingsStore()
let adminMenuHandler: ((e: Event) => void) | null = null

const props = defineProps<{
  activeIndex: string
}>()

const emit = defineEmits<{
  (e: 'update:activeIndex', value: string): void
}>()

// Track active index based on current route
const activeIndex = ref(props.activeIndex || 'wpdatatables')

// Check if there is any enabled integration
const hasEnabledIntegration = computed(() => {
  const integrations = settingsStore.allSettings?.integrations
  if (!integrations) return false
  return Object.values(integrations).some(
    (integration: { enabled: boolean }) => integration && integration.enabled === true,
  )
})

// Menu items with integrations sub-menu
const menuItems = computed<MenuItem[]>(() => {
  const items: MenuItem[] = [
    { label: getLabel('general_settings'), index: 'general' },
    { label: getLabel('notifications'), index: 'notifications' },
    { label: getLabel('confirmations'), index: 'confirmations' },
  ]
  if (hasEnabledIntegration.value) {
    // Dynamically build subItems for enabled integrations
    const integrations = settingsStore.allSettings?.integrations || {}
    type IntegrationValue = { enabled: boolean; [key: string]: unknown }
    const subItems: MenuItem[] = Object.entries(integrations)
      .filter(([, value]: [string, IntegrationValue]) => value && value.enabled === true)
      .map(([key]: [string, IntegrationValue]) => ({
        index: `integrations/${key}`,
        label: getLabel(key),
        iconConfig: {
          name: key,
          type: 'fill',
          category: 'integrations',
          size: 'd',
        },
      }))
    items.push({
      label: getLabel('integrations'),
      index: 'integrations',
      autoNavigateToFirst: true,
      subItems,
    })
  }
  return items
})

// Helper function to get confirmation ID
const getConfirmationIdForForm = async (formId: number) => {
  // Try to get confirmationId from store
  if (confirmationStore.id && confirmationStore.formId == formId) {
    return confirmationStore.id
  }
  // Try to fetch confirmation for this form
  await confirmationStore.fetchAllConfirmations(formId)
  if (confirmationStore.id) {
    return confirmationStore.id
  }
  // If still not found, create one
  confirmationStore.formId = formId
  await confirmationStore.createConfirmation()
  return confirmationStore.id
}

// Handle menu selection
const onMenuSelect = async (index: string) => {
  activeIndex.value = index
  emit('update:activeIndex', index)
  const formId = formBuilderStore.formId

  if (index === 'general') {
    await router.push({ path: `/manage/${formId}/settings/general` })
  } else if (index === 'notifications') {
    await router.push({ path: `/manage/${formId}/settings/notification` })
  } else if (index === 'confirmations') {
    // Get or create confirmationId first
    const confirmationId = await getConfirmationIdForForm(Number(formId))
    await router.push({ path: `/manage/${formId}/settings/confirmation/manage/${confirmationId}` })
  } else if (index.startsWith('integrations/')) {
    // Handle integration sub-items
    const integration = index.split('integrations/')[1]
    await router.push({ path: `/manage/${formId}/settings/integrations/${integration}` })
  } else if (index === 'integrations') {
    // Default to wpDataTables when clicking main Integrations menu
    await router.push({ path: `/manage/${formId}/settings/integrations/wpdatatables` })
  }
}

// Update active index based on current route
const updateActiveIndex = () => {
  const path = route.path

  if (path.includes('/settings/general')) {
    activeIndex.value = 'general'
  } else if (path.includes('/settings/notification')) {
    activeIndex.value = 'notifications'
  } else if (path.includes('/settings/confirmation')) {
    activeIndex.value = 'confirmations'
  } else if (path.includes('/settings/integrations/')) {
    // Extract integration name from path
    const match = path.match(/\/integrations\/(\w+)/)
    if (match) {
      activeIndex.value = `integrations/${match[1]}`
    } else {
      activeIndex.value = 'integrations/wpdatatables'
    }
  }
}

const loading = ref(true)

onMounted(async () => {
  // Set initial active index based on current route
  updateActiveIndex()

  // Watch for route changes
  router.afterEach(() => {
    updateActiveIndex()
  })

  // Ensure settings are loaded before rendering menu
  if (!settingsStore.allSettings?.integrations) {
    await settingsStore.loadAllSettings()
  }
  loading.value = false

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
</script>

<style lang="scss">
.ivyforms-form-builder-settings {
  &__menu {
    &.ivyforms-menu-wrapper {
      .ivyforms-menu {
        &.el-menu {
          .el-menu-item {
            &.ivyforms-menu-item {
              justify-content: flex-start;
              align-content: flex-start;
            }
          }
        }
      }
    }
  }
}
</style>
