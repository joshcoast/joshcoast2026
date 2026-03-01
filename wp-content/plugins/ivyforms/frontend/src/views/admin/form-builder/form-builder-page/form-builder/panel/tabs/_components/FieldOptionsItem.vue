<template>
  <div
    class="ivyforms-option-item ivyforms-mb-8 ivyforms-border-radius-8"
    :class="{ collapsed }"
    v-bind="$attrs"
  >
    <div
      class="ivyforms-option-item__header ivyforms-flex ivyforms-align-items-center ivyforms-justify-content-between ivyforms-border-radius-8 ivyforms-px-8"
      @click="toggle"
    >
      <div
        class="ivyforms-option-item__header__left ivyforms-flex ivyforms-align-items-center ivyforms-gap-8"
      >
        <IvyIcon
          v-if="showDrag"
          :class="[
            'ivyforms-option-item__header__left__drag-icon',
            collapsed
              ? 'ivyforms-option-item__header__left__drag-icon--handle'
              : 'ivyforms-option-item__header__left__drag-icon--disabled',
            collapsed ? 'ivyforms-drag-handle' : '',
          ]"
          :name="dragIcon.name"
          :category="dragIcon.category"
          :type="dragIcon.type"
          @click.stop
        />
        <slot name="header-actions" />
        <span class="ivyforms-option-item__header__left__title medium-14">{{ title }}</span>
      </div>
      <div
        class="ivyforms-option-actions ivyforms-flex ivyforms-align-items-center ivyforms-gap-4"
        @click.stop
      >
        <IvyButtonAction
          v-if="showTrash"
          priority="danger"
          type="ghost"
          size="d"
          icon-start="trash"
          icon-start-category="global"
          icon-start-type="outline"
          icon-only
          :disabled="disableDelete"
          aria-label="Delete"
          class="ivyforms-trash-action"
          @click="onDelete"
        />
        <!-- Dropdown toggle as IvyButtonAction with rotating arrow -->
        <IvyButtonAction
          v-if="showDropdown"
          priority="tertiary"
          type="ghost"
          size="d"
          icon-start="chevron-down"
          icon-start-category="arrows"
          icon-start-type="outline"
          icon-only
          :class="['ivyforms-dropdown-toggle', { expanded: !collapsed }]"
          aria-label="Toggle section"
          @click="toggle"
        />
      </div>
    </div>

    <div
      v-if="!collapsed"
      class="ivyforms-option-item__content ivyforms-pt-8 ivyforms-pb-16 ivyforms-px-16 ivyforms-flex ivyforms-flex-direction-column ivyforms-border-radius-8 ivyforms-gap-16"
    >
      <slot />
    </div>
  </div>
</template>

<script setup lang="ts">
import IvyIcon from '@/views/_components/icon/IvyIcon.vue'
import IvyButtonAction from '@/views/_components/button/IvyButtonAction.vue'
import { computed } from 'vue'
import type { IconCategory } from '@/types/icons/icon-category'
import type { IconType } from '@/types/icons/icon-type'

defineOptions({ inheritAttrs: false })

interface IconProps {
  name: string
  category?: IconCategory
  type?: IconType
}

const props = withDefaults(
  defineProps<{
    title: string
    collapsed: boolean
    showDrag?: boolean
    showTrash?: boolean
    showDropdown?: boolean
    disableDelete?: boolean
    dragIcon?: IconProps
    trashIcon?: IconProps
  }>(),
  {
    showDrag: true,
    showTrash: true,
    showDropdown: true,
    disableDelete: false,
    dragIcon: () => ({ name: 'drag', category: 'builder', type: 'outline' }),
    trashIcon: () => ({ name: 'trash', category: 'global', type: 'outline' }),
  },
)

const emit = defineEmits<{
  (e: 'update:collapsed', value: boolean): void
  (e: 'delete'): void
}>()

const showDrag = computed(() => props.showDrag !== false)
const showTrash = computed(() => props.showTrash !== false)
const showDropdown = computed(() => props.showDropdown !== false)

const dragIcon = computed<Required<IconProps>>(() => ({
  name: props.dragIcon?.name ?? 'drag',
  category: props.dragIcon?.category ?? 'builder',
  type: props.dragIcon?.type ?? 'outline',
}))

const toggle = () => emit('update:collapsed', !props.collapsed)
const onDelete = () => {
  if (props.disableDelete) return
  emit('delete')
}
</script>

<style scoped lang="scss">
.ivyforms-option-item {
  border: 1px solid var(--map-divider);
  position: relative;

  &:last-child {
    margin-bottom: 0;
  }

  &.dragging-over-top::before {
    content: '';
    position: absolute;
    height: 2px;
    background-color: var(--map-base-purple-stroke-0);
    left: 0;
    right: 0;
    z-index: 10;
    top: -6px;
  }

  &.dragging-over-bottom::after {
    content: '';
    position: absolute;
    height: 2px;
    background-color: var(--map-base-purple-stroke-0);
    left: 0;
    right: 0;
    z-index: 10;
    bottom: -6px;
  }

  &__header {
    cursor: pointer;
    border-bottom: 1px solid transparent;

    &__left {
      &__drag-icon {
        color: var(--map-base-text-2);

        &--handle {
          cursor: grab;
          transition: transform 0.2s ease;

          &:hover {
            color: var(--map-base-text-0);
            transform: scale(1.1);
          }

          &:active {
            cursor: grabbing;
          }
        }

        &--disabled {
          color: var(--map-base-text-3);
          cursor: not-allowed;
          opacity: 0.5;
        }
      }

      &__title {
        color: var(--map-base-text-0);
      }
    }

    .ivyforms-option-actions {
      :deep(
        .ivyforms-button-action.ivyforms-button-action__size__d.is-icon-only.ivyforms-dropdown-toggle
      ),
      :deep(
        .ivyforms-button-action.ivyforms-button-action__size__d.is-icon-only.ivyforms-trash-action
      ) {
        width: 24px;
        height: 24px;
        padding: 0;
        border-radius: 4px;
      }

      // Rotation for dropdown arrow
      :deep(.ivyforms-dropdown-toggle .ivyforms-button-action__icon-start) {
        transition: transform 0.18s ease;
      }
      :deep(.ivyforms-dropdown-toggle.expanded .ivyforms-button-action__icon-start) {
        transform: rotate(180deg);
      }

      // Remove hover background/overlay for any IvyButtonAction inside this component
      :deep(
        .ivyforms-button-action:hover:not(:active):not(:disabled)
          .ivyforms-button-action__transition
      ) {
        opacity: 0 !important;
        fill: transparent !important;
      }
    }
  }

  &__content {
    background: var(--map-ground-level-1-foreground);
  }

  &.collapsed {
    .ivyforms-option-item__header {
      border-bottom: none;
    }

    .ivyforms-option-item__content {
      display: none;
    }
  }

  &:not(.collapsed) {
    .ivyforms-option-item__header {
      border-bottom: 1px solid var(--map-divider);
    }
  }
}
</style>
