<template>
  <ElOption
    :class="[
      'ivyforms-select-option',
      {
        'is-secondary': isSecondary,
      },
    ]"
    :value="props.value"
    :label="props.label"
    :disabled="disabled"
  >
    <div
      v-if="props.iconStart"
      class="ivyforms-flex ivyforms-align-items-center ivyforms-flex-1 ivyforms-gap-8"
    >
      <IvyIcon
        :name="props.iconStart"
        :category="props.iconStartCategory"
        :type="props.iconStartType"
        class="ivyforms-select-option__icon-start"
        size="d"
      />
      <slot />
    </div>
    <slot v-else />
    <IvyIcon
      v-if="props.iconConfig?.name"
      v-bind="{ ...props.iconConfig, name: props.iconConfig.name }"
    />
  </ElOption>
</template>

<script setup lang="ts">
import { inject, computed } from 'vue'
import IvyIcon from '@/views/_components/icon/IvyIcon.vue'
import type { IconCategory } from '@/types/icons/icon-category'
import type { IconType } from '@/types/icons/icon-type'

const props = defineProps<{
  value: string | number | boolean | object
  label?: string | number
  disabled?: boolean
  fieldType?: string
  secondary?: boolean
  iconStart?: string
  iconStartCategory?: IconCategory
  iconStartType?: IconType
  iconConfig?: {
    name: string
    type?: IconType
    category?: IconCategory
    size?: 'l' | 'd' | 's' | 'xs'
    class?: string
  }
}>()

const isSecondary = computed(() => props.secondary || inject('isSecondary', false))
</script>

<style lang="scss">
// Select Option
.ivyforms-select-option {
  &.el-select-dropdown__item {
    white-space: normal;
    min-height: 36px !important;
    height: auto !important;
    padding: 8px;
    color: var(--map-base-dusk-symbol-2);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin: 0;
    font-size: 14px;
    font-style: normal;
    font-weight: 400;
    line-height: 20px;

    h1,
    h2,
    h3,
    h4,
    h5,
    h6,
    p {
      color: var(--map-base-dusk-symbol-2);
    }

    .ivyforms-icon__svg.type-line.name-star {
      path {
        stroke: var(--map-base-text-0);
      }
    }

    &.is-selected {
      .ivyforms-icon__svg.type-line.name-star {
        path {
          stroke: var(--map-base-purple-symbol-0);
        }
      }

      background: var(--map-base-brand-o10);
      color: var(--map-base-dusk-symbol-2);
      font-size: 14px;
      font-style: normal;
      font-weight: 400;

      &.is-secondary {
        background: var(--map-base-purple-o10);
        &:after {
          background-color: var(--map-base-purple-symbol-0) !important;
        }
      }
    }

    &.is-secondary.is-selected {
      color: var(--map-base-purple-symbol-0) !important;
      background: var(--map-base-purple-o05) !important;
    }

    &:hover,
    &.is-hovering {
      color: var(--map-base-dusk-symbol-2);
      background: var(--map-hover);
    }

    &:first-child {
      margin-top: 8px !important;
    }

    &:last-child {
      margin-bottom: 8px !important;
    }

    .ivyforms-icon {
      display: contents;
    }

    .ivyforms-icon__svg {
      width: auto;
      justify-content: start;
    }
  }
}
</style>
