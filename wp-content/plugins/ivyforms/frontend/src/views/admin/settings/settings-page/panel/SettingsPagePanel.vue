<template>
  <PagePanel>
    <!-- IvyMenuAccordion with enhanced styling -->
    <IvyMenuAccordion
      v-model="activeIndex"
      :menu-items="menuItems"
      :auto-expand-security="true"
      :use-router="false"
      class="ivyforms-settings__menu ivyforms-pr-12"
      @menu-select="onMenuSelect"
    />
  </PagePanel>
</template>

<script setup lang="ts">
import { useRouter, useRoute } from 'vue-router'
import { useLabels } from '@/composables/useLabels'
import { useSettingsStore } from '@/stores/useSettingsStore'
import { ref, onMounted, computed } from 'vue'
//import { IVYFORMS_GENERAL_SETTINGS, IVYFORMS_SECURITY_SETTINGS } from '@/constants/pages'
//import { useMenuNavigation } from '@/composables/useMenuNavigation'
import IvyMenuAccordion from '@/views/_components/menu/IvyMenuAccordion.vue'

interface MenuIconConfig {
  name: string
  type: 'fill' | 'outline'
  category: string
  size: 'd' | 's' | 'm' | 'l'
}

interface SubMenuItem {
  index: string
  label: string
  iconConfig?: MenuIconConfig
}

const { getLabel } = useLabels()
const settingsStore = useSettingsStore()

const props = defineProps<{
  activeIndex: string
}>()

const emit = defineEmits<{
  (e: 'update:activeIndex', value: string): void
}>()

const router = useRouter()
const route = useRoute()
//const menuNavigation = useMenuNavigation()

const activeIndex = ref(props.activeIndex || 'general') // Default to 'general' if no activeIndex

// Menu items with security and integrations sub-menus
// Make it reactive to settings changes and hook updates
const menuItems = computed(() => {
  // Force reactivity by accessing integrationsSettings
  // This ensures the computed re-runs when settings are loaded
  void settingsStore.integrationsSettings

  const items = [
    {
      label: getLabel('general'),
      index: 'general',
    },
    {
      label: getLabel('security'),
      index: 'security',
      autoNavigateToFirst: true, // Enable auto-navigation to first sub-item
      subItems: [
        {
          index: 'security/recaptcha',
          label: 'reCAPTCHA',
          iconConfig: {
            name: 'recaptcha-colored',
            type: 'fill' as const,
            category: 'security' as const,
            size: 'd' as const,
          },
        },
        {
          index: 'security/hcaptcha',
          label: 'hCaptcha',
          iconConfig: {
            name: 'hcaptcha',
            type: 'fill' as const,
            category: 'security' as const,
            size: 'd' as const,
          },
        },
        {
          index: 'security/turnstile',
          label: 'Turnstile',
          iconConfig: {
            name: 'turnstile',
            type: 'fill' as const,
            category: 'security' as const,
            size: 'd' as const,
          },
        },
        // {
        //   index: 'security/honeypot',
        //   label: 'Honeypot',
        //   iconConfig: {
        //     name: 'honeypot',
        //     type: 'fill' as const,
        //     category: 'security' as const,
        //     size: 'd' as const,
        //   }
        // }
      ],
    },
  ]

  // Allow Pro plugin to inject integration sub-items via hooks
  if (window.IvyForms?.hooks?.applyFilters) {
    const integrationSubItems = window.IvyForms.hooks.applyFilters(
      'ivyforms/settings/add_integration_subitem',
      [],
    )

    // Only create Integrations menu if there are enabled integrations
    if (Array.isArray(integrationSubItems) && integrationSubItems.length > 0) {
      // Filter sub-items based on enabled state from settings
      const enabledSubItems = integrationSubItems.filter((subItem: SubMenuItem) => {
        // Extract integration name from index (e.g., 'integrations/mailchimp' -> 'mailchimp')
        const integrationName = subItem.index.split('/')[1]
        // Check if integration is enabled in settings
        const integrationSettings = settingsStore.integrationsSettings?.[integrationName]
        return integrationSettings?.enabled === true
      })

      // Only add Integrations menu if at least one integration is enabled
      if (enabledSubItems.length > 0) {
        items.push({
          label: window.IvyForms?.getLabel?.('integrations') || 'Integrations',
          index: 'integrations',
          autoNavigateToFirst: true,
          subItems: enabledSubItems,
        })
      }
    }
  }

  // Allow Pro plugin to inject additional menu items via hooks
  if (window.IvyForms?.hooks?.applyFilters) {
    const additionalMenuItems = window.IvyForms.hooks.applyFilters(
      'ivyforms/settings/add_menu_item',
      [],
    )

    if (Array.isArray(additionalMenuItems) && additionalMenuItems.length > 0) {
      items.push(...additionalMenuItems)
    }
  }

  return items
})

// Function to handle menu selection
const onMenuSelect = (index: string) => {
  activeIndex.value = index
  emit('update:activeIndex', index)

  // Navigate based on the selected menu item
  if (index === 'general') {
    router.push({ path: '/general' })
  } else if (index === 'security') {
    // Default to reCAPTCHA when clicking main Security menu
    router.push({ path: '/security/recaptcha' })
  } else if (index.startsWith('security/')) {
    // Handle sub-menu clicks for specific captcha providers
    const provider = index.split('security/')[1]
    router.push({ path: `/security/${provider}` })
  } else if (index === 'integrations') {
    // Default to first enabled integration when clicking main Integrations menu
    // Extension will add integrations via filters, so we redirect to /integrations
    // and let the router handle it
    router.push({ path: '/integrations' })
  } else if (index.startsWith('integrations/')) {
    // Handle sub-menu clicks for specific integrations
    const integration = index.split('integrations/')[1]
    router.push({ path: `/integrations/${integration}` })
  } else {
    // Allow Pro plugin to handle custom routes via hooks
    if (window.IvyForms?.hooks?.applyFilters) {
      const handled = window.IvyForms.hooks.applyFilters(
        'ivyforms/settings/handle_menu_route',
        false,
        index,
        router,
      )

      // If Pro didn't handle it, do nothing (avoid navigation)
      if (!handled) {
        console.warn(`[Settings] No route handler for menu item: ${index}`)
      }
    }
  }
}

// On mount, check the current route and set the active index accordingly
onMounted(async () => {
  // Load settings to ensure integration states are available
  await settingsStore.loadAllSettings()

  const path = route.path
  if (path.includes('/security/')) {
    const provider = path.split('/security/')[1]
    if (provider) {
      activeIndex.value = `security/${provider}`
      emit('update:activeIndex', `security/${provider}`)
    } else {
      activeIndex.value = 'security/recaptcha'
      emit('update:activeIndex', 'security/recaptcha')
    }
  } else if (path.includes('/security')) {
    activeIndex.value = 'security/recaptcha'
    emit('update:activeIndex', 'security/recaptcha')
  } else if (path.includes('/integrations/')) {
    const integration = path.split('/integrations/')[1]
    if (integration) {
      activeIndex.value = `integrations/${integration}`
      emit('update:activeIndex', `integrations/${integration}`)
    } else {
      // No specific integration, stay on integrations parent
      activeIndex.value = 'integrations'
      emit('update:activeIndex', 'integrations')
    }
  } else if (path.includes('/integrations')) {
    activeIndex.value = 'integrations'
    emit('update:activeIndex', 'integrations')
  } else {
    // Allow Pro plugin to handle custom route detection via hooks
    let handled = false
    if (window.IvyForms?.hooks?.applyFilters) {
      const result = window.IvyForms.hooks.applyFilters(
        'ivyforms/settings/detect_active_route',
        null,
        path,
      )

      if (result) {
        activeIndex.value = result
        emit('update:activeIndex', result)
        handled = true
      }
    }

    if (!handled) {
      // Default to general settings
      activeIndex.value = 'general'
      emit('update:activeIndex', 'general')
    }
  }
})
</script>

<style lang="scss">
// IvyMenuAccordion component handles all styling internally
</style>
