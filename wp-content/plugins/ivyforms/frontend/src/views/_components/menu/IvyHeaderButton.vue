<template>
  <ElMenuItem
    :index="name"
    :route="route"
    :class="[
      `size-${size}`,
      'ivyforms-menu-item',
      `priority-${priority}`,
      `type-${type}`,
      { 'is-active-item': isActive },
      { 'has-coming-soon': comingSoon },
    ]"
    :disabled="disabled || comingSoon"
    :aria-label="props.ariaLabel || getLabel('header_button')"
    @click="onClick"
  >
    <IvyIcon v-if="iconStart?.name" v-bind="iconStart" />

    <span>
      {{ label }}
    </span>

    <IvyIcon v-if="iconEnd?.name" v-bind="iconEnd" />

    <ComingSoonBadge
      v-if="props.comingSoon"
      class="ivyforms-header-button-wrapper__coming-soon"
      :size="'s'"
    />
  </ElMenuItem>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { IconCategory } from '@/types/icons/icon-category'
import type { IconType } from '@/types/icons/icon-type'
import type { IconSize } from '@/types/icons/icon-size'
import { useLabels } from '@/composables/useLabels'
const { getLabel } = useLabels()

interface IconConfig {
  name: string
  type?: IconType
  category?: IconCategory
  size?: IconSize
  outerSize?: string
}

interface Props {
  name?: string
  label: string
  priority?: 'primary' | 'secondary' | 'tertiary'
  size?: 's' | 'd' | 'l'
  type?: 'filled' | 'tonal' | 'white'
  disabled?: boolean
  isActive?: boolean
  iconStartConfig?: IconConfig
  iconEndConfig?: IconConfig
  comingSoon?: boolean
  ariaLabel?: string
  route?: object
}

// Default icon configuration
const defaultIconConfig: IconConfig = {
  name: 'page',
  size: 'l',
  outerSize: '20px',
}

// Merge icon configuration only if the prop is passed
const getIconConfig = (iconConfig?: IconConfig) =>
  iconConfig ? { ...defaultIconConfig, ...iconConfig } : undefined

const props = withDefaults(defineProps<Props>(), {
  priority: 'primary',
  size: 'd',
  type: 'filled',
  isActive: false,
  disabled: false,
  iconStartConfig: undefined,
  iconEndConfig: undefined,
  name: '',
  comingSoon: false,
  ariaLabel: '',
  route: undefined,
})

// Apply defaults only if the config exists
const iconStart = computed(() => getIconConfig(props.iconStartConfig))
const iconEnd = computed(() => getIconConfig(props.iconEndConfig))

const emit = defineEmits(['click'])

const onClick = () => {
  if (!props.disabled) {
    emit('click', props.name)
  }
}
</script>

<style lang="scss">
@use 'sass:list' as *;
@use 'sass:meta' as *;

// Position the Coming Soon badge correctly
.ivyforms-header-button-wrapper__coming-soon {
  position: absolute;
  top: -7px;
  right: -4px;
}

.ivyforms-menu {
  &.el-menu {
    // Menu Item
    .ivyforms-menu-item {
      position: relative;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 10px var(--spacing-04, 16px);
      border-radius: 8px;
      border: none;
      min-width: 44px;
      height: 40px;
      font-size: 14px;
      margin: 0;
      font-style: normal;
      font-weight: 500;
      gap: var(--spacing-01, 4px);
      flex: 1;

      // Define priorities and their styles
      $priorities: (
        'primary': (
          transparent,
          var(--map-base-text-0),
          var(--map-base-brand-o05),
          var(--map-base-brand-symbol-0),
          var(--map-base-brand-fill-0),
          $primitive-white,
        ),
        'secondary': (
          transparent,
          var(--map-base-text-0),
          var(--map-base-purple-o05),
          var(--map-base-purple-symbol-0),
          var(--map-base-purple-fill-0),
          $primitive-white,
        ),
        'tertiary': (
          transparent,
          var(--map-base-text-0),
          var(--map-base-dusk-o10),
          var(--map-base-text-0),
          var(--map-base-dusk-o10),
          var(--map-base-text-0),
        ),
      );

      // Mixin for priority styles
      @mixin menuStyles($priority, $colors: ()) {
        $default-bg: nth($colors, 1);
        $default-text: nth($colors, 2);
        $hover-bg: nth($colors, 3);
        $hover-text: nth($colors, 4);
        $active-bg: nth($colors, 5);
        $active-text: nth($colors, 6);

        &.priority-#{$priority} {
          background-color: $default-bg;
          color: $default-text;

          // Icon
          .ivyforms-icon {
            display: contents;
            fill: $default-text;
            width: 20px !important;
            height: 20px;
          }

          // Hover
          &:hover,
          &.is-hover {
            background-color: $hover-bg;
            color: $hover-text;

            .ivyforms-icon {
              fill: $hover-text;
            }
          }

          // Active
          &:active,
          &.is-active-item {
            border: none;
            background-color: $active-bg;
            color: $active-text !important;

            .ivyforms-icon {
              fill: $active-text;
            }
          }

          &.is-active {
            color: $active-bg !important;
          }

          // Focus
          // Hover
          &:focus,
          &.is-focus {
            background-color: $hover-bg;
            color: $hover-text;

            .ivyforms-icon {
              fill: $hover-text;
            }
          }

          // Default state when not active
          &:not(.is-active-item) {
            background-color: $default-bg;
            color: $default-text !important;
            border: none;

            .ivyforms-icon {
              fill: $default-text;
            }
          }

          &.type-tonal {
            &:active,
            &.is-active-item {
              background-color: $hover-bg !important;
              color: $hover-text !important;

              .ivyforms-icon {
                fill: $hover-text;
              }
            }
          }

          &.is-disabled {
            &:active,
            &.is-active-item {
              color: $default-text !important;
            }

            &:hover,
            &.is-hover,
            &:focus,
            &.is-focus {
              background-color: $default-bg !important;
              color: $default-text !important;
            }
          }

          &.has-coming-soon {
            margin: 0;
          }
        }

        &.priority-tertiary {
          &.is-active-item,
          &:active {
            border-radius: var(--radius-l, 8px);
            background: var(--map-ground-level-3);
            box-shadow: 0 4 15 0 rgba(0, 0, 0, 0.05);
          }
        }
      }

      // Generate styles for all priorities
      @each $priority, $colors in $priorities {
        @include menuStyles($priority, $colors);
      }

      // Size specific styles
      &.size-s {
        height: 32px;
        max-height: 32px;
      }

      &.size-d {
        height: 40px;
        max-height: 40px;
      }

      &.size-l {
        height: 48px;
        max-height: 48px;
      }
      span {
        line-height: 20px;
      }
    }
  }
}
</style>
