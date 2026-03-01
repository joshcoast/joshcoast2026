<template>
  <div class="ivyforms-menu-accordion ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-4">
    <!-- Create separate ElMenu for each IvyHeaderButton to provide proper context -->
    <template v-for="item in menuItems" :key="item.index">
      <!-- Menu item without sub-items -->
      <ElMenu v-if="!item.subItems" class="ivyforms-menu" mode="vertical">
        <IvyHeaderButton
          :name="item.index"
          :label="item.label"
          :is-active="activeIndex === item.index"
          :icon-start-config="item.iconConfig"
          size="d"
          type="tonal"
          :priority="priority"
          @click="onItemClick(item.index)"
        />
      </ElMenu>

      <!-- Menu item with sub-items (accordion) -->
      <div
        v-else
        class="ivyforms-menu-accordion__group ivyforms-flex ivyforms-flex-direction-column"
      >
        <!-- Header with separate ElMenu -->
        <ElMenu class="ivyforms-menu" mode="vertical">
          <IvyHeaderButton
            :name="item.index"
            :label="item.label"
            :is-active="isParentActive(item.index)"
            :icon-start-config="item.iconConfig"
            :icon-end-config="{
              name: expandedItems.includes(item.index) ? 'chevron-up' : 'chevron-down',
              type: 'outline',
              category: 'arrows',
              size: 's',
            }"
            size="d"
            type="tonal"
            :priority="priority"
            @click="onHeaderClick(item)"
          />
        </ElMenu>

        <!-- Sub-items with custom styling -->
        <div
          v-if="expandedItems.includes(item.index)"
          class="ivyforms-menu-accordion__content ivyforms-mt-0"
        >
          <div
            class="ivyforms-menu-accordion__submenu ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-0 ivyforms-mx-0 ivyforms-my-8"
          >
            <div
              v-for="(subItem, subIndex) in item.subItems"
              :key="subItem.index"
              :class="[
                'ivyforms-submenu-item ivyforms-ml-32',
                { 'ivyforms-submenu-item--last': subIndex === item.subItems.length - 1 },
                { 'ivyforms-submenu-item--active': activeIndex === subItem.index },
              ]"
              @click="onSubItemSelect(subItem.index)"
            >
              <div class="ivyforms-submenu-item-content ivyforms-flex">
                <div class="ivyforms-tree-line ivyforms-flex ivyforms-align-items-start">
                  <TreeEndIcon
                    v-if="subIndex === item.subItems.length - 1"
                    class="ivyforms-tree-connector"
                  />
                  <TreeDefaultIcon v-else class="ivyforms-tree-connector" />
                </div>

                <IvyIcon v-if="subItem.iconConfig" v-bind="subItem.iconConfig" />

                <span class="medium-14">{{ subItem.label }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import IvyHeaderButton from '@/views/_components/menu/IvyHeaderButton.vue'
import IvyIcon from '@/views/_components/icon/IvyIcon.vue'
import TreeDefaultIcon from '@/assets/images/sub-menu-tree/default.svg?component'
import TreeEndIcon from '@/assets/images/sub-menu-tree/end.svg?component'
import type { IconType } from '@/types/icons/icon-type'
import type { IconCategory } from '@/types/icons/icon-category'
import type { IconSize } from '@/types/icons/icon-size'

interface SubMenuItem {
  index: string
  label: string
  iconConfig?: {
    name: string
    type?: IconType
    category?: IconCategory
    size?: IconSize
    class?: string
  }
}

export interface MenuItem {
  index: string
  label: string
  iconConfig?: {
    name: string
    type?: IconType
    category?: IconCategory
    size?: IconSize
    class?: string
  }
  subItems?: SubMenuItem[]
  autoNavigateToFirst?: boolean // Generic flag for auto-navigation
}

interface Props {
  menuItems: MenuItem[]
  modelValue?: string
  priority?: 'primary' | 'secondary' | 'tertiary'
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: '',
  priority: 'primary',
})

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
  (e: 'menuSelect', index: string): void
}>()

const activeIndex = ref(
  props.modelValue || (props.menuItems.length > 0 ? props.menuItems[0].index : ''),
)
const expandedItems = ref<string[]>([])

// Computed property to check which parent menus should be active
const activeParents = computed(() => {
  const parents: string[] = []

  props.menuItems.forEach((item) => {
    if (item.subItems?.some((subItem) => subItem.index === activeIndex.value)) {
      parents.push(item.index)
    }
  })

  return parents
})

// Function to check if a parent menu should be active
const isParentActive = (parentIndex: string) => {
  return activeParents.value.includes(parentIndex)
}

// Handle direct item clicks (no sub-items)
const onItemClick = (index: string) => {
  activeIndex.value = index
  emit('update:modelValue', index)
  emit('menuSelect', index)

  // Close all expanded items when selecting a main item
  expandedItems.value = []
}

// Handle header clicks for items with sub-items
const onHeaderClick = (item: MenuItem) => {
  // Check if the item is currently expanded
  const isCurrentlyExpanded = expandedItems.value.includes(item.index)

  // Close all expanded items first
  expandedItems.value = []

  // If it wasn't expanded, expand it now (toggle behavior)
  if (!isCurrentlyExpanded) {
    expandedItems.value.push(item.index)

    // Auto-navigate to first sub-item (always for items with sub-items)
    if (item.subItems && item.subItems.length > 0) {
      const defaultSubItem = item.subItems[0]
      activeIndex.value = defaultSubItem.index
      emit('update:modelValue', defaultSubItem.index)
      emit('menuSelect', defaultSubItem.index)
    }
  }
}

// Handle sub-item selection
const onSubItemSelect = (index: string) => {
  activeIndex.value = index
  emit('update:modelValue', index)
  emit('menuSelect', index)
}

// Watch for prop changes
watch(
  () => props.modelValue,
  (newValue) => {
    activeIndex.value = newValue

    // Check if the new value is a sub-item
    const isSubItem = props.menuItems.some((item) =>
      item.subItems?.some((subItem) => subItem.index === newValue),
    )

    if (isSubItem) {
      // Auto-expand parent menu if active item is a sub-item
      props.menuItems.forEach((item) => {
        if (item.subItems?.some((subItem) => subItem.index === newValue)) {
          if (!expandedItems.value.includes(item.index)) {
            expandedItems.value.push(item.index)
          }
        }
      })
    } else {
      // This is a main menu item, close all expanded items
      expandedItems.value = []
    }
  },
)

// Initialize expanded items on mount
onMounted(() => {
  // If no modelValue is provided, emit the default active index
  if (!props.modelValue && activeIndex.value) {
    emit('update:modelValue', activeIndex.value)
    emit('menuSelect', activeIndex.value)
  }

  // Initialize expanded items
  if (props.modelValue || activeIndex.value) {
    const currentValue = props.modelValue || activeIndex.value
    props.menuItems.forEach((item) => {
      if (item.subItems?.some((subItem) => subItem.index === currentValue)) {
        expandedItems.value.push(item.index)
      }
    })
  }
})
</script>

<style lang="scss">
.ivyforms-menu-accordion {
  border: none;

  .ivyforms-menu {
    &.el-menu {
      border: none;
      background-color: transparent;
      width: 100%;

      .ivyforms-menu-item.el-menu-item {
        width: 100% !important;
        justify-content: space-between;

        .ivyforms-icon {
          .ivyforms-icon__svg {
            width: auto;
          }
        }
      }
    }
  }

  &__item {
    width: 100%;
  }

  &__group {
    width: 100%;
  }

  &__header {
    width: 100%;
  }

  // Icon transitions for smooth changes
  :deep(.ivyforms-icon),
  :deep(svg) {
    transition: all 0.3s ease !important;
  }

  // Position chevron icon to the right for accordion headers
  &__group {
    :deep(.el-menu-item) {
      display: flex !important;
      justify-content: space-between !important;
      align-items: center !important;
      position: relative !important;

      // Push the chevron to the far right
      .ivyforms-icon:last-child {
        position: absolute !important;
        right: 12px !important;
        margin-left: auto !important;
      }

      // Ensure the label has space for the icon
      span {
        flex: 1;
        margin-right: 32px;
      }
    }
  }

  &__submenu {
    transition: all 0.3s ease;

    .ivyforms-submenu-item {
      min-height: 40px;
      cursor: pointer;
      border-radius: var(--radius-l, 8px);

      &:hover {
        background-color: var(--map-base-dusk-fill--4);
      }

      &.ivyforms-submenu-item--active {
        background-color: var(--map-base-dusk-fill--4);
      }

      .ivyforms-submenu-item-content {
        width: 100%;
        color: var(--map-base-text-0);
        max-height: 40px;
        position: relative;
        padding: 8px var(--spacing-04, 16px);
        justify-content: flex-start;
        align-items: center;
        gap: var(--spacing-02, 8px);

        .ivyforms-tree-line {
          position: absolute;
          width: 12px;
          height: 40px;
          flex-shrink: 0;
          justify-content: flex-start;
          left: -16px;
          top: 0;

          .ivyforms-tree-connector {
            width: 12px;
            height: 40px;
            display: block;
            object-fit: contain;
          }
        }

        span {
          color: var(--map-base-text-0);
        }
      }
    }
  }
}
</style>
