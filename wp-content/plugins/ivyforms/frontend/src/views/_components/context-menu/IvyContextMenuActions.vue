<template>
  <div
    v-for="action in props.actions"
    :key="getLabel(action.label)"
    class="ivyforms-context-menu-action ivyforms-flex ivyforms-flex-direction-column regular-14 ivyforms-width-100"
    :class="{
      divided: isDivided(action.divided),
      secondary: isSecondary(action.secondary),
      hidden: action?.isHidden?.(),
      ghost: props.style === 'ghost',
      active: action?.isActive?.(),
      'coming-soon': isComingSoon(action.isComingSoon),
    }"
    @click="() => handleActionClick(action)"
  >
    <div
      class="ivyforms-context-menu-action__wrapper ivyforms-cursor-pointer ivyforms-overflow-hidden ivyforms-flex ivyforms-justify-content-between ivyforms-flex-grow ivyforms-gap-8 ivyforms-px-8 ivyforms-m-0 ivyforms-align-items-center"
      :class="{ danger: isDanger(action.danger) }"
    >
      <div
        class="ivyforms-context-menu-action__wrapper__content ivyforms-flex ivyforms-gap-6 ivyforms-justify-content-between ivyforms-flex-1 ivyforms-align-items-center"
      >
        <IvyIcon
          v-if="action.icon"
          :color="getIconColor(action)"
          :name="getIcon(action.icon)"
          :type="action.iconType || 'outline'"
          :category="action.iconCategory || 'global'"
          :size="action.iconSize || 'd'"
          class="ivyforms-context-menu-action__icon"
        />
        <div
          class="ivyforms-context-menu-action__label-wrapper ivyforms-flex-1 ivyforms-overflow-hidden"
        >
          <span class="ivyforms-context-menu-action__label ivyforms-mt-2">{{ action.label }}</span>
        </div>
      </div>
      <div
        v-if="action.rightText"
        class="ivyforms-context-menu-action__right-text regular-14 text-muted ivyforms-mt-2"
      >
        {{ action.rightText }}
      </div>
      <ComingSoonBadge v-if="isComingSoon(action.isComingSoon)" />
    </div>
    <IvyDivider v-if="isDividerDown(action.dividerDown)" />
  </div>
</template>

<script setup lang="ts">
import { defineEmits } from 'vue'
import type { ContextMenuAction } from './context-menu-action'
interface Props {
  actions: ContextMenuAction[]
  entityId?: number | null
  style?: 'ghost' | 'filled'
}

const props = withDefaults(defineProps<Props>(), {
  actions: () => [],
  entityId: null,
  style: 'filled',
})

const emit = defineEmits(['confirm-delete', 'action-executed', 'close-menu'])

const handleActionClick = (action: ContextMenuAction) => {
  if (isComingSoon(action.isComingSoon)) {
    return
  }

  if (action.handler) {
    action.handler(props.entityId)
  }

  emit('action-executed')
}

const getIconColor = (action: ContextMenuAction) => {
  if (isDanger(action.danger)) {
    return 'var(--map-status-error-symbol-0)'
  }

  if (action.iconColor) {
    return action.iconColor
  }

  return 'var(--map-base-dusk-symbol-2)'
}

const getLabel = (label: string | ((entityId: number | null) => string)) => {
  if (typeof label === 'function') {
    return label(props.entityId)
  }
  return label
}

const getIcon = (icon: string | ((entityId: number | null) => string)) => {
  if (typeof icon === 'function') {
    return icon(props.entityId)
  }
  return icon
}

const isDanger = (danger: (() => boolean) | boolean | undefined) => {
  if (typeof danger === 'function') {
    return danger()
  }
  return danger
}

const isDivided = (divided: (() => boolean) | boolean | undefined) => {
  if (typeof divided === 'function') {
    return divided()
  }
  return divided
}

const isDividerDown = (dividerDown: (() => boolean) | boolean | undefined) => {
  if (typeof dividerDown === 'function') {
    return dividerDown()
  }
  return dividerDown
}

const isSecondary = (secondary: (() => boolean) | boolean | undefined) => {
  if (typeof secondary === 'function') {
    return secondary()
  }
  return secondary
}

const isComingSoon = (isComingSoon: (() => boolean) | boolean | undefined) => {
  if (typeof isComingSoon === 'function') {
    return isComingSoon()
  }
  return isComingSoon
}
</script>

<style lang="scss">
// Context Menu Action
.ivyforms-context-menu-action {
  // Divided
  &.divided {
    border-top: 1px solid var(--map-divider);
    padding-top: 2px;
    margin-top: 2px;
  }

  // Hidden
  &.hidden {
    display: none;
  }

  // Secondary
  &.secondary:not(.coming-soon) {
    // Active
    &:active,
    &.active {
      .ivyforms-context-menu-action__wrapper {
        background-color: var(--map-base-purple-o05);
        color: var(--map-base-purple-symbol-0);
        font-weight: 400;

        .ivyforms-icon {
          svg {
            fill: var(--map-base-purple-symbol-0);
          }
        }
      }
    }
  }

  // Active
  &.active:not(.coming-soon) {
    .ivyforms-context-menu-action__wrapper {
      background-color: var(--map-base-brand-o05);
      color: var(--map-base-brand-symbol-0);
      font-weight: 400;

      .ivyforms-icon {
        svg {
          fill: var(--map-base-brand-symbol-0);
        }
      }
    }
  }
  &:active:not(.coming-soon) {
    > .ivyforms-context-menu-action__wrapper {
      background-color: var(--map-base-brand-o05);
      color: var(--map-base-brand-symbol-0);
      font-weight: 400;

      .ivyforms-icon {
        svg {
          fill: var(--map-base-brand-symbol-0);
        }
      }

      &.danger {
        color: var(--map-status-error-symbol-0);
        background-color: var(--map-status-error-o05);

        .ivyforms-icon {
          svg {
            fill: var(--map-status-error-symbol-0);
          }
        }
      }
    }
  }

  // Ghost
  &.style-ghost {
  }

  // Wrapper
  &__wrapper {
    height: 36px;
    color: var(--map-base-text-0);

    // Hover
    &:hover {
      background-color: var(--map-hover);
    }

    // Danger
    &.danger {
      color: var(--map-status-error-symbol-0);

      // Active
      &:active:not(.coming-soon),
      &.active:not(.coming-soon) {
        background: var(--map-status-error-o05);
        // Hover
        &:hover {
          background: var(--map-status-error-o10);
        }
      }
    }

    // Content
    &__content {
      min-width: 0;
    }
  }

  &__label-wrapper {
    min-width: 0;
  }

  &__label {
    line-height: 24px;
    text-overflow: ellipsis;
    white-space: nowrap;
    display: block;
  }

  &__right-text {
    line-height: 24px;
    flex-shrink: 0;
  }

  &.coming-soon {
    cursor: not-allowed;
    justify-content: space-between;

    .ivyforms-context-menu-action__wrapper {
      cursor: not-allowed;
      padding: 0 8px;

      &:hover {
        background-color: unset;
      }

      .ivyforms-context-menu-action__wrapper__content {
        opacity: 0.5;
      }
    }
  }
}
</style>
