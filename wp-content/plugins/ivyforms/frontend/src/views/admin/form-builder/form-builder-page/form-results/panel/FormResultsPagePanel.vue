<template>
  <PagePanel>
    <!-- Menu -->
    <IvyMenu :mode="mode" :ellipsis="false" class="ivyforms-form-builder-results__menu">
      <IvyHeaderButton
        v-for="item in menuItems"
        :key="item.index"
        :label="item.label"
        :name="item.index"
        type="tonal"
        :is-active="menuNavigation.isActive(item.routeName, item.index)"
        :coming-soon="item.disabled"
        @click="setActiveMenuItem(item)"
      />
    </IvyMenu>
  </PagePanel>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useLabels } from '@/composables/useLabels'
import { IVYFORMS_ENTRIES, IVYFORMS_FORM_RESULTS } from '@/constants/pages.ts'
import { useMenuNavigation } from '@/composables/useMenuNavigation'
const { getLabel } = useLabels()
const mode = ref('vertical')
defineProps<{
  activeIndex: string
}>()

const emit = defineEmits<{
  (e: 'update:activeIndex', value: string): void
}>()

const menuNavigation = useMenuNavigation()

// Menu items
const menuItems = [
  {
    label: getLabel('entries'),
    index: IVYFORMS_ENTRIES,
    routeName: IVYFORMS_FORM_RESULTS,
    disabled: false,
  },
  // { label: getLabel('insights'), index: 'insights', disabled: true },
  // { label: getLabel('report'), index: 'report', disabled: true },
]

// Function to set the active index and navigate using shared logic
const setActiveMenuItem = async (item: { index: string; routeName?: string }) => {
  emit('update:activeIndex', item.index)
  // Delegate navigation and unsaved-changes handling to shared composable
  await menuNavigation.onMenuClick(item, menuNavigation.isActive)
}
</script>

<style lang="scss">
.ivyforms-form-builder-results {
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
