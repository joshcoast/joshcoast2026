<template>
  <PageHeader>
    <template #left>
      <IvyMenu v-if="isFullScreenMode" mode="horizontal" :ellipsis="false">
        <IvyHeaderButton
          v-for="item in menuItems"
          :key="item.index"
          :label="item.label"
          :name="item.index"
          :is-active="activeIndex === item.index"
          type="tonal"
          @click="redirectTo(item.index)"
        />
      </IvyMenu>
    </template>
    <!--    <template #right>-->
    <!--      <IvyButtonAction priority="tertiary" icon-start="bell" icon-start-type="fill-duo" />-->
    <!--    </template>-->
    <PageHeaderDivider />
  </PageHeader>

  <div
    class="ivyforms-settings-page ivyforms-flex ivyforms-gap-8 ivyforms-p-8"
    :class="{
      'ivyforms-fullscreen-mode': isFullScreenMode,
    }"
  >
    <SettingsPagePanel
      :active-index="settingsSubIndex"
      @update:active-index="setSettingsSubIndex"
    />
    <SettingsPageSection :active-index="settingsSubIndex" />
  </div>
</template>

<script setup lang="ts">
import { provide, ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useLabels } from '@/composables/useLabels'
import { useGlobalState } from '@/stores/useGlobalState.ts'
import { useNavigation } from '@/composables/useNavigation'
import IvyMenu from '@/views/_components/menu/IvyMenu.vue'
import IvyHeaderButton from '@/views/_components/menu/IvyHeaderButton.vue'
import SettingsPagePanel from '@/views/admin/settings/settings-page/panel/SettingsPagePanel.vue'
import SettingsPageSection from '@/views/admin/settings/settings-page/section/SettingsPageSection.vue'
import {
  IVYFORMS_ALL_FORMS,
  IVYFORMS_ENTRIES,
  IVYFORMS_INTEGRATIONS,
  IVYFORMS_SETTINGS,
} from '@/constants/pages.ts'
import { useWcagColors } from '@/composables/useWcagColors'

// Get global state to check if we're in fullscreen mode
const globalState = useGlobalState()
const isFullScreenMode = computed(() => globalState.isFullScreenMode)
const { getLabel } = useLabels()
const router = useRouter()
const { startWatching } = useWcagColors()
startWatching()

const { navigateToAllForms, navigateToEntries, navigateToSettings, navigateToIntegrations } =
  useNavigation()
const activeIndex = ref('settings')
const settingsSubIndex = ref('general')
const title: string = getLabel('settings')
provide('pageTitle', title)

const menuItems = [
  { label: getLabel('all_forms'), index: IVYFORMS_ALL_FORMS },
  { label: getLabel('entries'), index: IVYFORMS_ENTRIES },
  { label: getLabel('integrations'), index: IVYFORMS_INTEGRATIONS },
  { label: getLabel('settings'), index: IVYFORMS_SETTINGS },
]

// Function to update sub-index when changing sections
const setSettingsSubIndex = (index: string) => {
  settingsSubIndex.value = index
  router.push({ path: `/${index}/` })
}

const redirectTo = (index: string) => {
  if (index === activeIndex.value) return

  activeIndex.value = index
  // Redirect based on the selected menu item
  if (index === IVYFORMS_ALL_FORMS) {
    navigateToAllForms()
  } else if (index === IVYFORMS_ENTRIES) {
    navigateToEntries()
  } else if (index === IVYFORMS_SETTINGS) {
    navigateToSettings()
  } else if (index === IVYFORMS_INTEGRATIONS) {
    navigateToIntegrations()
  }
}

// When the component mounts, ensure we're on the general settings page
onMounted(() => {
  // Initialize to general settings and update the route if we are on the root path
  if (router.currentRoute.value.path === '/' || router.currentRoute.value.path === '') {
    settingsSubIndex.value = 'general'
    router.push({ path: '/general/' })
  }
})
</script>
<style scoped lang="scss">
.ivyforms-settings-page {
  height: calc(100vh - 116px);
  overflow: hidden;
  display: flex;
  transition: height 0.3s ease-in-out;

  &.ivyforms-fullscreen-mode {
    height: calc(100vh - 80px);
  }
}
</style>
