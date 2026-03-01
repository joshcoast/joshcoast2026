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
          @click="handleMenuClick(item.index)"
        />
      </IvyMenu>
    </template>
    <!--    <template #right>-->
    <!--      <IvyButtonAction priority="tertiary" icon-start="bell" icon-start-type="fill-duo" />-->
    <!--    </template>-->
    <PageHeaderDivider />
  </PageHeader>

  <div
    class="ivyforms-all-forms-page ivyforms-flex ivyforms-gap-8 ivyforms-p-8"
    :class="{
      'ivyforms-fullscreen-mode': isFullScreenMode,
    }"
  >
    <!--    <FormsFolderPageSection />-->
    <AllFormsPageSection />
  </div>
</template>

<script setup lang="ts">
import { provide, ref, computed } from 'vue'
import { useGlobalState } from '@/stores/useGlobalState.ts'
import { useLabels } from '@/composables/useLabels'
import { useNavigation } from '@/composables/useNavigation'
import {
  IVYFORMS_ALL_FORMS,
  IVYFORMS_ENTRIES,
  IVYFORMS_GLOBAL_SETTINGS,
  IVYFORMS_INTEGRATIONS,
} from '@/constants/pages.ts'

const { getLabel } = useLabels()
const { navigateToAllForms, navigateToEntries, navigateToSettings, navigateToIntegrations } =
  useNavigation()

const title: string = getLabel('all_forms')
provide('pageTitle', title)

// Get global state to check if we're in fullscreen mode
const globalState = useGlobalState()
const isFullScreenMode = computed(() => globalState.isFullScreenMode)
// Menu related variables
const activeIndex = ref(IVYFORMS_ALL_FORMS)

const menuItems = [
  { label: getLabel('all_forms'), index: IVYFORMS_ALL_FORMS },
  { label: getLabel('entries'), index: IVYFORMS_ENTRIES },
  { label: getLabel('integrations'), index: IVYFORMS_INTEGRATIONS },
  { label: getLabel('settings'), index: IVYFORMS_GLOBAL_SETTINGS },
]

const handleMenuClick = (index: string) => {
  if (index === activeIndex.value) return
  activeIndex.value = index
  if (index === IVYFORMS_ALL_FORMS) {
    navigateToAllForms()
  } else if (index === IVYFORMS_ENTRIES) {
    navigateToEntries()
  } else if (index === IVYFORMS_GLOBAL_SETTINGS) {
    navigateToSettings()
  } else if (index === IVYFORMS_INTEGRATIONS) {
    navigateToIntegrations()
  }
}
</script>

<style lang="scss">
.ivyforms-banners-container {
  padding: 8px;
}

.ivyforms-all-forms-page {
  height: calc(100vh - 116px);
  overflow: hidden;
  display: flex;
  transition: height 0.3s ease-in-out;

  &.ivyforms-fullscreen-mode {
    height: calc(100vh - 80px);
  }
}
</style>
