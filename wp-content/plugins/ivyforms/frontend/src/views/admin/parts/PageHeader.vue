<template>
  <header
    class="ivyforms-header ivyforms-flex ivyforms-gap-8 ivyforms-align-items-center ivyforms-justify-content-between ivyforms-py-12 ivyforms-px-24"
  >
    <div class="ivyforms-header__left">
      <IvyButtonAction
        v-if="shouldShowBackButton"
        priority="tertiary"
        icon-start="chevron-left"
        icon-start-category="arrows"
        icon-start-type="outline"
        @click="goBack"
      >
        {{ backButtonLabel }}
      </IvyButtonAction>
      <IvyLogo v-else class="ivyforms-header__logo" orientation="logoOnly" />
      <PageHeaderDivider v-if="!isEntryDetailsPage" class="ivyforms-px-4" />
      <div
        v-if="!isFormBuilder"
        class="ivyforms-header__title ivyforms-flex ivyforms-flex-1 ivyforms-align-items-center ivyforms-gap-12 medium-18"
      >
        <span class="ivyforms-header__title-text ivyforms-text-cut">{{ pageTitle }}</span>
      </div>
      <slot name="left"></slot>
    </div>
    <div class="ivyforms-header__center">
      <slot name="center"></slot>
    </div>
    <div class="ivyforms-header__right">
      <IvyTooltip
        :content="theme === 'dark' ? getLabel('light_theme') : getLabel('dark_theme')"
        theme="inverted"
        placement="bottom"
      >
        <IvyThemeSwitch v-model="theme" :aria-label="getLabel('theme_switch')" />
      </IvyTooltip>
      <IvyTooltip :content="getLabel('support')" placement="bottom" theme="inverted">
        <IvyButtonAction
          priority="tertiary"
          icon-start="chat-dots"
          icon-start-type="outline"
          @click="openSupport"
        />
      </IvyTooltip>
      <PageHeaderDivider v-if="showUpgradeButton" class="ivyforms-px-4" />
      <IvyButtonAction
        v-if="showUpgradeButton"
        priority="pro"
        icon-start="bolt-lightning"
        icon-start-type="fill"
        @click="openPricing"
      >
        {{ getLabel('upgrade') }}
      </IvyButtonAction>
      <PageHeaderDivider class="ivyforms-px-4" />
      <slot name="right"></slot>
      <IvyTooltip
        :content="isExpanded ? getLabel('collapse') : getLabel('full_screen')"
        theme="inverted"
        :disabled="closeTooltip"
      >
        <IvyButtonAction
          priority="tertiary"
          icon-start-category="arrows"
          :icon-start="isExpanded ? 'collapse' : 'expand'"
          @click="toggleFullScreen"
        />
      </IvyTooltip>
    </div>
  </header>
</template>

<script setup lang="ts">
import { useCookies } from '@vueuse/integrations/useCookies'
import { useGlobalState } from '@/stores/useGlobalState.ts'
import { inject, onBeforeMount, onMounted, onUnmounted, ref, computed, watch } from 'vue'
import { useTheme } from '@/composables/useTheme'
import type { ThemeType } from '@/types/theme'
import { useLabels } from '@/composables/useLabels'
import { useApiClient } from '@/composables/useApiClient'
import {
  IVYFORMS_ENTRY_DETAILS,
  IVYFORMS_FORM_BUILDER_PAGE,
  IVYFORMS_ADD_FORM,
  IVYFORMS_EDIT_FORM,
  IVYFORMS_SETTINGS,
  IVYFORMS_FORM_SETTING,
  IVYFORMS_FORM_NOTIFICATIONS,
  IVYFORMS_NEW_NOTIFICATIONS,
  IVYFORMS_EDIT_NOTIFICATIONS,
  IVYFORMS_FORM_CONFIRMATION,
  IVYFORMS_FORM_INTEGRATIONS,
  IVYFORMS_FORM_RESULTS,
  IVYFORMS_RESULTS_ENTRY_DETAILS,
  IVYFORMS_RESULTS,
  IVYFORMS_ENTRIES_PAGE,
  IVYFORMS_ALLFORMS_PAGE,
} from '@/constants/pages.ts'
import IvyButtonAction from '@/views/_components/button/IvyButtonAction.vue'
import { useRoute } from 'vue-router'
import { useNavigation } from '@/composables/useNavigation'
import { useUnsavedChangesStore } from '@/stores/useUnsavedChangesStore'
import { useProFeatures } from '@/composables/useProFeatures'
import { openPricingPage } from '@/composables/useProFeatureUpgrade'
import { IVYFORMS_CONTACT_URL, IVYFORMS_WORDPRESS_SUPPORT_URL } from '@/constants/links'
const { getLabel } = useLabels()

const cookies = useCookies()
const { request } = useApiClient()
const pro = useProFeatures()
const { navigateToAdminPage } = useNavigation()

const formBuilderRoutes = [
  IVYFORMS_ADD_FORM,
  IVYFORMS_EDIT_FORM,
  IVYFORMS_SETTINGS,
  IVYFORMS_FORM_SETTING,
  IVYFORMS_FORM_NOTIFICATIONS,
  IVYFORMS_NEW_NOTIFICATIONS,
  IVYFORMS_EDIT_NOTIFICATIONS,
  IVYFORMS_FORM_CONFIRMATION,
  IVYFORMS_FORM_INTEGRATIONS,
  IVYFORMS_FORM_RESULTS,
]

const isExpanded = ref(false)
const globalState = useGlobalState()

const injectedPageTitle = inject<string>('pageTitle', '')

// Use injected page title if available, otherwise fall back to global state
const pageTitle = computed(() => injectedPageTitle || globalState.pageTitle)

const closeTooltip = ref(false)

const route = useRoute()

const fullscreenRouteNames = [
  IVYFORMS_ENTRY_DETAILS,
  IVYFORMS_FORM_BUILDER_PAGE,
  IVYFORMS_ADD_FORM,
  IVYFORMS_EDIT_FORM,
  IVYFORMS_SETTINGS,
  IVYFORMS_FORM_SETTING,
  IVYFORMS_FORM_NOTIFICATIONS,
  IVYFORMS_NEW_NOTIFICATIONS,
  IVYFORMS_EDIT_NOTIFICATIONS,
  IVYFORMS_FORM_CONFIRMATION,
  IVYFORMS_FORM_INTEGRATIONS,
  IVYFORMS_FORM_RESULTS,
  IVYFORMS_RESULTS_ENTRY_DETAILS,
]
const isFullScreenWithBackPage = computed(() => {
  return fullscreenRouteNames.includes(route.name as string)
})

const isEntryDetailsPage = computed(() => {
  return route.name === IVYFORMS_ENTRY_DETAILS || route.name === IVYFORMS_RESULTS_ENTRY_DETAILS
})

const shouldShowBackButton = computed(() => {
  return isEntryDetailsPage.value || (isExpanded.value && isFullScreenWithBackPage.value)
})

// Get theme from our composable
const { theme, setTheme } = useTheme()

onBeforeMount(() => {
  setTheme((cookies.get('ivyforms_theme') as ThemeType) || 'light')
})

const toggleFullScreen = async () => {
  // Store the intended new state
  const newExpandedState = !isExpanded.value

  // Update UI immediately for better UX
  updateFullScreenUI(newExpandedState)

  closeTooltip.value = true

  try {
    const { data, error, status } = await request('setting/update', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      data: {
        settingsCategory: 'general',
        settingsOption: 'fullscreen',
        settingsValue: newExpandedState,
      },
    })

    // Handle response
    if (status !== 200 || error) {
      console.error(getLabel('error_update_settings'), error)
      // Revert UI state on error
      updateFullScreenUI(isExpanded.value)
      return
    }

    if (data && data.success) {
      // Update local state with the confirmed value from backend
      isExpanded.value = data.data.value
      globalState.setFullScreenMode(data.data.value)

      // Update global wpIvySettings to keep it in sync
      if (typeof window.wpIvySettings !== 'undefined') {
        window.wpIvySettings.general.fullscreen = data.data.value
      }
    } else {
      // If no success flag, revert the UI
      updateFullScreenUI(isExpanded.value)
    }
  } catch (error) {
    console.error(getLabel('error_update_settings'), error)
    // Revert UI state on error
    updateFullScreenUI(isExpanded.value)
  }

  setTimeout(() => {
    closeTooltip.value = false
  }, 1000)
}

// Helper function to update fullscreen UI
const updateFullScreenUI = (expanded: boolean) => {
  const adminBody = document.body
  const adminMenuMain = document.getElementById('adminmenumain')
  const wpContent = document.getElementById('wpcontent')
  const adminBar = document.getElementById('wpadminbar')
  const wpToolbar = document.getElementsByClassName('wp-toolbar')[0]

  if (expanded) {
    adminBody.classList.add('ivyforms-fullscreen-mode')
    if (adminMenuMain) adminMenuMain.style.display = 'none'
    if (wpContent) {
      wpContent.style.marginLeft = '0'
      wpContent.style.width = '100vw'
      wpContent.style.height = '100vh'
    }
    if (adminBar) adminBar.style.display = 'none'
    if (wpToolbar instanceof HTMLElement) {
      wpToolbar.style.padding = '0'
    }
  } else {
    adminBody.classList.remove('ivyforms-fullscreen-mode')
    if (adminMenuMain) adminMenuMain.style.display = ''
    if (wpContent) {
      wpContent.style.marginLeft = ''
      wpContent.style.width = ''
      wpContent.style.height = ''
    }
    if (adminBar) adminBar.style.display = ''
    if (wpToolbar instanceof HTMLElement) {
      wpToolbar.style.padding = ''
    }
  }

  // Update local state and global state
  isExpanded.value = expanded
  globalState.setFullScreenMode(expanded)
}

const openSupport = () => {
  const currentPlan = pro.plan.value
  const isProActivated = currentPlan !== 'lite' && currentPlan !== 'error'
  const supportUrl = isProActivated ? IVYFORMS_CONTACT_URL : IVYFORMS_WORDPRESS_SUPPORT_URL
  window.open(supportUrl, '_blank')
}

const openPricing = () => {
  openPricingPage()
}

const showUpgradeButton = computed(() => {
  // Only show button if Pro features are loaded and plan is actually 'lite'
  // This prevents flashing when Pro is installed
  if (!pro.loaded.value) return false
  return pro.plan.value === 'lite'
})

const unsavedChangesStore = useUnsavedChangesStore()

function goBack() {
  // No need to check for unsaved changes
  if (route.name === IVYFORMS_ENTRY_DETAILS) {
    navigateToAdminPage(IVYFORMS_ENTRIES_PAGE)
    return
  }
  if (route.name === IVYFORMS_RESULTS_ENTRY_DETAILS) {
    const formId = route.params.formId
    if (formId) {
      navigateToAdminPage(IVYFORMS_FORM_BUILDER_PAGE, `manage/${formId}/results/entries`)
    } else {
      navigateToAdminPage(IVYFORMS_RESULTS)
    }
    return
  }

  // Only check for unsaved changes when navigating from form builder pages (which have editors)
  if (
    route.name === IVYFORMS_FORM_BUILDER_PAGE ||
    formBuilderRoutes.includes(route.name as string)
  ) {
    unsavedChangesStore.confirmIfDirty(() => {
      navigateToAdminPage(IVYFORMS_ALLFORMS_PAGE)
    })
    return
  }
}

const backButtonLabel = computed(() => {
  if (route.name === IVYFORMS_ENTRY_DETAILS) {
    return getLabel('all_entries')
  }
  if (route.name === IVYFORMS_RESULTS_ENTRY_DETAILS) {
    return getLabel('results')
  }
  return getLabel('all_forms')
})

// Check if we're on any form builder related page
const isFormBuilder = computed(() => {
  return (
    route.name === IVYFORMS_FORM_BUILDER_PAGE || formBuilderRoutes.includes(route.name as string)
  )
})

onMounted(() => {
  // Initialize fullscreen state from global state first, then fallback to backend settings
  let initialFullscreenState = globalState.isFullScreenMode

  // Only read from wpIvySettings if global state has never been initialized
  if (!globalState.isFullScreenModeInitialized) {
    try {
      if (
        typeof window.wpIvySettings !== 'undefined' &&
        window.wpIvySettings.general?.fullscreen !== undefined
      ) {
        initialFullscreenState = Boolean(window.wpIvySettings.general.fullscreen)
      }
    } catch (error) {
      console.warn('Could not read wpIvySettings.general.fullscreen:', error)
    }
  }

  // Set the initial state
  isExpanded.value = initialFullscreenState
  globalState.setFullScreenMode(initialFullscreenState)

  // Apply fullscreen UI if needed
  if (initialFullscreenState) {
    updateFullScreenUI(true)
  }
})

// Watch for changes in global fullscreen state to sync local state
watch(
  () => globalState.isFullScreenMode,
  (newFullScreenMode) => {
    if (newFullScreenMode !== isExpanded.value) {
      isExpanded.value = newFullScreenMode
      updateFullScreenUI(newFullScreenMode)
    }
  },
)

onUnmounted(() => {
  if (!isExpanded.value) return
  document.body.classList.remove('ivyforms-fullscreen-mode')
})
</script>

<style scoped lang="scss">
// Header
.ivyforms-header {
  background: var(--map-ground-level-1-foreground);
  box-shadow: 0 4px 15px 0 rgba(0, 0, 0, 0.05);
  position: relative;

  // Title
  &__title {
    white-space: nowrap;
    color: var(--map-base-text-0);

    &-text {
      display: inline-block;
      max-width: 60ch;
    }
  }

  // Logo
  &__logo {
    height: 32px;
    width: 32px;
    align-items: center;
    justify-content: center;
  }

  &__center {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    align-items: center;
    justify-content: center;
  }

  &__left,
  &__right {
    display: flex;
    gap: 8px;
    position: relative;
    flex: 0 0 auto;
  }

  &__left {
    align-items: center;
  }

  &__right {
    align-items: center;
  }

  :deep(.ivyforms-menu.el-menu .ivyforms-menu-item) {
    transition: none !important;
  }
}
</style>
