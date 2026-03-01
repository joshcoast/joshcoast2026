<template>
  <div
    :class="['ivyforms-tabs-wrapper', `tab-style-${tabStyle}`, { 'bg-transparent': !background }]"
  >
    <ElTabs
      v-model="activeTab"
      type="card"
      :tab-position="position"
      :addable="addTab"
      :editable="addTab"
      :stretch="stretch"
      :class="[
        `size-${size}`,
        'ivyforms-tabs-group',
        `priority-${priority}`,
        `type-${type}`,
        `tab-style-${tabStyle}`,
        { 'bg-transparent': !background },
      ]"
      @tab-add="onTabAdd"
      @tab-remove="onTabRemove"
    >
      <ElTabPane
        v-for="tab in tabs"
        :id="`pane-${tab.name}`"
        :key="tab.name"
        :name="tab.name"
        :aria-labelledby="`tab-${tab.name}`"
        role="tabpanel"
        aria-live="polite"
        :disabled="tab.disabled"
        :type="{ minWidth: props.minWidth }"
        @click="!tab.disabled && (activeTab = tab.name)"
      >
        <template #label>
          <span
            :id="`tab-${tab.name}`"
            class="ivyforms-tab-label-wrapper ivyforms-flex ivyforms-align-items-center ivyforms-gap-6"
            @click="tab.pro && tab.disabled ? emit('pro-tab-click', tab.name) : null"
          >
            <IvyIcon v-if="tab.iconConfig?.name" v-bind="tab.iconConfig" aria-hidden="true" />
            {{ tab.label }}
            <ProBadge
              v-if="tab.pro"
              image="pro-bolt"
              size="s"
              class="ivyforms-tab-pro-badge ivyforms-flex-shrink-0"
            />
          </span>
        </template>
        <slot :name="tab.name"></slot>
      </ElTabPane>
    </ElTabs>
    <ComingSoonBadge v-if="props.comingSoon" />
  </div>
</template>

<script setup lang="ts">
import { nanoid } from 'nanoid'
import { ref, computed, onMounted, onUnmounted } from 'vue'
import IvyIcon from '@/views/_components/icon/IvyIcon.vue'
import ProBadge from '@/views/_components/badges/ProBadge.vue'
import type { IconType } from '@/types/icons/icon-type'
import type { IconCategory } from '@/types/icons/icon-category'

interface Tab {
  name: string
  label: string
  path?: string
  disabled?: boolean
  pro?: boolean
  iconConfig?: {
    name: string
    type?: IconType
    category?: IconCategory
    size?: 'l' | 'd' | 's' | 'xs'
    class?: string
  }
}

interface Props {
  background?: boolean
  comingSoon?: boolean
  priority?: 'primary' | 'secondary' | 'tertiary' | 'amber'
  size?: 's' | 'd' | 'l'
  type?: 'filled' | 'tonal' | 'fab'
  tabStyle?: 'stroke' | 'fill' | 'ghost'
  modelValue: string
  position?: 'left' | 'right' | 'top' | 'bottom'
  addTab?: boolean
  stretch?: boolean
  minWidth?: string
  tabs: Tab[]
}

const props = withDefaults(defineProps<Props>(), {
  background: false,
  priority: 'primary',
  size: 'd',
  type: 'filled',
  tabStyle: 'fill',
  position: 'top',
  addTab: false,
  stretch: true,
  minWidth: '50px',
})

// Reactive state for tabs and activeTab
const tabs = ref([...props.tabs])

const emit = defineEmits(['update:modelValue', 'pro-tab-click'])

const activeTab = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val),
})

// Add a new tab
const onTabAdd = () => {
  const newTabName = `tab-${nanoid(6)}`
  tabs.value.push({ name: newTabName, label: `Page ${tabs.value.length + 1}` })
  activeTab.value = newTabName
}

// Remove a tab
const onTabRemove = () => {
  tabs.value = tabs.value.filter((tab) => tab.name !== activeTab.value)
  if (tabs.value.length > 0) {
    activeTab.value = tabs.value[0]?.name || ''
  }
}

// Keyboard navigation
onMounted(() => {
  document.addEventListener('keydown', handleKeyDown)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeyDown)
})

const handleKeyDown = (event: KeyboardEvent) => {
  if (!tabs.value.length) return

  // Ignore arrow keys when focus is in an input, textarea, or contenteditable element
  const target = event.target as HTMLElement
  if (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA' || target.isContentEditable) {
    return
  }

  const currentIndex = tabs.value.findIndex((tab) => tab.name === activeTab.value)
  if (currentIndex === -1) return

  if (event.key === 'ArrowRight') {
    // Find the next available tab that's not disabled
    let nextIndex = (currentIndex + 1) % tabs.value.length
    while (tabs.value[nextIndex].disabled && nextIndex !== currentIndex) {
      nextIndex = (nextIndex + 1) % tabs.value.length
    }
    if (nextIndex !== currentIndex) {
      activeTab.value = tabs.value[nextIndex].name
    }
  } else if (event.key === 'ArrowLeft') {
    // Find the previous available tab that's not disabled
    let prevIndex = (currentIndex - 1 + tabs.value.length) % tabs.value.length
    while (tabs.value[prevIndex].disabled && prevIndex !== currentIndex) {
      prevIndex = (prevIndex - 1 + tabs.value.length) % tabs.value.length
    }
    if (prevIndex !== currentIndex) {
      activeTab.value = tabs.value[prevIndex].name
    }
  }
}
</script>

<style lang="scss">
@use 'sass:list' as *;
@use 'sass:meta' as *;

.ivyforms-tabs-wrapper {
  position: relative;
  display: flex;
  box-sizing: border-box;
  height: fit-content;
  border-radius: var(--radius-l, 8px);
  background: var(--map-base-dusk-o10);

  &.bg-transparent {
    background-color: transparent;
  }

  &.tab-style-stroke {
    border: 1px solid var(--map-base-dusk-stroke--2);
  }

  .ivyforms-tabs-group {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    overflow-x: visible;
    overflow-y: visible;
    white-space: nowrap;
    width: 100%;
    box-sizing: border-box;
    padding: var(--spacing-01, 4px);

    // Left and right position
    &.el-tabs--left.el-tabs--card {
      .el-tabs__item.is-left,
      .el-tabs__item.is-left:first-child {
        border: none;
      }
    }

    &.el-tabs--right.el-tabs--card {
      .el-tabs__item.is-right,
      .el-tabs__item.is-right:first-child {
        border: none;
      }
    }

    // Define priorities and their styles
    $priorities: (
      'primary': (
        transparent,
        var(--map-base-text-0),
        var(--map-base-dusk-o10),
        var(--map-base-brand-fill-0),
        $primitive-white,
        var(--map-base-brand-o05),
        var(--map-base-brand-symbol-0),
      ),
      'secondary': (
        transparent,
        var(--map-base-text-0),
        var(--map-base-dusk-o10),
        var(--map-base-purple-fill-0),
        $primitive-white,
        var(--map-base-purple-o05),
        var(--map-base-purple-symbol-0),
      ),
      'tertiary': (
        transparent,
        var(--map-base-text-0),
        var(--map-base-dusk-o10),
        var(--map-base-dusk-o10),
        var(--map-base-text-0),
        var(--map-ground-level-3),
        var(--map-base-text-0),
      ),
      'amber': (
        transparent,
        var(--map-base-text-0),
        var(--map-base-dusk-o10),
        var(--map-accent-amber-fill-0),
        $primitive-white,
        var(--map-accent-amber-o05),
        var(--map-accent-amber-symbol-0),
      ),
    );

    // Mixin for priority styles
    @mixin tabStyles($priority, $colors: ()) {
      $default-bg: nth($colors, 1);
      $default-text: nth($colors, 2);
      $hover-bg: nth($colors, 3);
      $active-bg: nth($colors, 4);
      $active-text: nth($colors, 5);
      $tonal-bg: nth($colors, 6);
      $tonal-text: nth($colors, 7);
      $icon-color: var(--map-base-text-2);

      &.type-tonal.priority-#{$priority} .el-tabs__item {
        &.is-active {
          background-color: $tonal-bg;
          color: $tonal-text;

          .ivyforms-icon {
            svg {
              fill: $icon-color;
            }
          }
        }
      }
      &.type-fab.priority-#{$priority} .el-tabs__item {
        &.is-active {
          background: var(--map-ground-level-3);
          color: $tonal-text;
          box-shadow: 0 4px 15px 0 rgba(0, 0, 0, 0.05);

          .ivyforms-icon {
            svg {
              fill: $icon-color;
            }
          }
        }
      }

      &.type-tonal.priority-tertiary .el-tabs__item {
        &.is-active {
          box-shadow: 0 4px 15px 0 rgba(0, 0, 0, 0.05);
        }
      }

      // Add tab button
      .el-tabs__new-tab {
        border: none;
        background-color: $default-bg;
        color: $default-text;

        &:hover {
          color: $default-text;
        }
      }

      &.priority-#{$priority} .el-tabs__item {
        background-color: $default-bg;
        color: $default-text;
        border-radius: 8px;
        display: inline-flex;
        flex-shrink: 0;
        min-width: 44px;
        padding: 10px var(--spacing-04, 16px) !important;
        justify-content: center;
        align-items: center;

        &:hover {
          background-color: $hover-bg;
        }

        &.is-active {
          background-color: $active-bg;
          color: $active-text;

          .ivyforms-icon {
            svg {
              fill: $active-text;
            }
          }
        }

        .ivyforms-icon {
          svg {
            fill: $default-text;
          }
        }
      }

      .el-tab-pane {
        &.priority-#{$priority} {
          background-color: $default-bg;
          color: $default-text;

          &.is-active {
            background-color: $active-bg;
          }
        }
      }
    }

    // Generate styles for all priorities
    @each $priority, $colors in $priorities {
      @include tabStyles($priority, $colors);
    }

    // Active PRO tab
    .el-tabs__header {
      .el-tabs__item.is-active#tab-pro {
        background-color: var(--map-accent-amber-fill-0) !important;
        color: $primitive-white !important;
      }
    }

    // Card style adjustments
    &.el-tabs--card {
      .el-tabs__header {
        border: none;
        min-width: 100%;
        margin: 0;
        height: auto;

        .el-tabs__nav,
        .el-tabs__item {
          border: none;
          gap: var(--spacing-01, 4px);
          flex: 1;
          text-align: center;
          min-width: var(--tab-min-width, 32px);
        }

        // Disabled tab styles
        .el-tabs__item.is-disabled {
          cursor: not-allowed;
          opacity: 0.5;

          &:hover {
            background-color: transparent;
          }
        }

        .el-tabs__nav {
          display: flex;
          min-width: 100%;
        }
      }
    }

    // Transparent background for tabs
    &.bg-transparent {
      .el-tabs__header {
        background-color: transparent;
        .el-tabs__nav-wrap {
          margin-bottom: 0;
        }
      }
    }

    // Size-specific styles
    &.size-s {
      height: 32px;
      .el-tabs__header {
        height: 32px;
        .el-tabs__item {
          height: 24px;
          .ivyforms-tab-label-wrapper {
            margin-top: 1px;
          }
        }
      }
    }

    &.size-d {
      height: 40px;
      .el-tabs__header {
        height: 40px;
        .el-tabs__item {
          height: 32px;
        }
      }
    }

    &.size-l {
      height: 48px;
      .el-tabs__header {
        height: 48px;
        .el-tabs__item {
          height: 40px;
        }
      }
    }
  }

  .ivyforms-coming-soon-badge {
    position: absolute;
    top: -9px;
    right: -8px;
  }

  .ivyforms-tab-label-wrapper {
    position: relative;
  }

  .ivyforms-tab-pro-badge {
    margin-left: auto;
  }
}
</style>
