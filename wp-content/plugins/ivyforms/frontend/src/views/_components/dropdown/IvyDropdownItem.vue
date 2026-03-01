<template>
  <!-- Dropdown Item -->
  <div class="ivyforms-dropdown-item-wrapper">
    <ElDropdownItem
      class="ivyforms-dropdown-item"
      :command="props.command"
      :disabled="props.disabled"
      :divided="props.divided"
      :class="[
        {
          'is-danger': props.danger,
        },
      ]"
    >
      <div
        class="ivyforms-dropdown-item__wrapper"
        :class="[
          {
            'is-disabled': props.disabled,
            'is-with-icon': Boolean(props.icon),
          },
        ]"
      >
        <IvyIcon
          v-if="props.icon"
          :name="props.icon"
          :category="props.iconCategory"
          size="d"
          class="ivyforms-dropdown-item__icon"
        />
        <span class="ivyforms-dropdown-item__label regular-14">
          <slot />
        </span>
      </div>
    </ElDropdownItem>
  </div>
  <!-- /Dropdown Item -->
</template>

<script setup lang="ts">
import type { IconCategory } from '@/types/icons/icon-category'

interface Props {
  command: string | number | object
  icon?: string
  iconCategory?: IconCategory
  disabled?: boolean
  danger?: boolean
  divided?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  command: undefined,
  danger: false,
  iconCategory: 'global',
  icon: undefined,
})
</script>

<style lang="scss">
@use 'sass:list' as *;
@use 'sass:meta' as *;
// Dropdown Item Wrapper
.ivyforms-dropdown-item-wrapper {
  // Dropdown Item
  .ivyforms-dropdown-item {
    // Not Divider
    &:not(.el-dropdown-menu__item--divided) {
      padding: 0 8px;
      height: 36px;
      overflow: hidden;
      border-radius: 0;
      cursor: pointer;
      -webkit-tap-highlight-color: transparent;
      color: var(--map-base-text-0);
    }

    // Divided
    &.el-dropdown-menu__item--divided {
      border-top: 1px solid var(--map-divider);
      margin: 2px 4px;
    }

    // Hover & Focus
    &:hover:not(.is-disabled),
    &:focus {
      background: var(--map-hover);
      color: initial;
    }

    // Active
    &:active:not(.is-disabled) {
      background: var(--map-base-brand-o10);

      // Label
      .ivyforms-dropdown-item__label {
        color: var(--map-base-brand-fill-0);
      }

      // Icon
      .ivyforms-dropdown-item__icon {
        fill: var(--map-base-brand-fill-0);
      }
    }

    // Disabled
    &.is-disabled {
      cursor: not-allowed;
    }

    // Danger
    &.is-danger {
      // Text
      span {
        color: var(--map-status-error-symbol-2);
      }

      // Hover
      &:hover:not(.is-disabled) {
        background: var(--map-status-error-o05);
      }

      // Active
      &:active:not(.is-disabled) {
        background: var(--map-status-error-o10);

        // Text
        span {
          color: var(--map-status-error-symbol-1);
        }

        // Icon
        .ivyforms-dropdown-item__icon {
          fill: var(--map-status-error-symbol-2);
        }
      }

      // Icon
      .ivyforms-dropdown-item__icon {
        fill: var(--map-status-error-symbol-2);
      }
    }

    // Wrapper
    &__wrapper {
      width: 100%;
      height: 100%;
      border-radius: 5px;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 4px;

      // Disabled
      &.is-disabled {
        color: var(--map-base-text-0);
        pointer-events: none;
        opacity: 0.5;
      }
    }

    // Icon
    &__icon {
      fill: var(--map-base-brand-symbol-2);
      @include flipProperty('margin-left', 'margin-right', -4px);
    }

    // Label
    &__label {
      display: inline-flex;
      align-items: center;
      flex-grow: 1;
    }
  }
}
</style>
