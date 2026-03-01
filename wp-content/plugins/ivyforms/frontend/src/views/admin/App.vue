<template>
  <div>
    <component
      :is="BannerComponent"
      v-for="(BannerComponent, index) in bannerComponents"
      :key="index"
    />
    <RouterView />
    <IvyChangelogModal />
    <ProUpgradeDialog />
  </div>
</template>

<script setup lang="ts">
import { shallowRef, onMounted, markRaw, isReactive } from 'vue'
import type { Component } from 'vue'
import IvyChangelogModal from '@/views/_components/changelog/IvyChangelogModal.vue'
import ProUpgradeDialog from '@/views/_components/dialog/ProUpgradeDialog.vue'

// Allow Pro to inject banner components via hook
// Use shallowRef to prevent components from being made deeply reactive
const bannerComponents = shallowRef<Component[]>([])

onMounted(() => {
  // Apply filter initially
  if (window.IvyForms?.hooks?.applyFilters) {
    const components = window.IvyForms.hooks.applyFilters('ivyforms/app/banner_components', [])
    // Wrap each component with markRaw to prevent reactivity (only if not already raw)
    bannerComponents.value = components.map((comp: Component) =>
      isReactive(comp) ? markRaw(comp) : comp,
    )
  }

  // Listen for banner refresh events (e.g., when Pro plugin registers banners after mount)
  const refreshBanners = () => {
    if (window.IvyForms?.hooks?.applyFilters) {
      const components = window.IvyForms.hooks.applyFilters('ivyforms/app/banner_components', [])
      // Wrap each component with markRaw to prevent reactivity (only if not already raw)
      bannerComponents.value = components.map((comp: Component) =>
        isReactive(comp) ? markRaw(comp) : comp,
      )
    }
  }

  window.addEventListener('ivyforms:refresh_banners', refreshBanners)
})
</script>
