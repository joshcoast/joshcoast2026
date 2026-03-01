<template>
  <ElCollapseItem
    class="ivyforms-collapse-item"
    :class="{ 'is-disabled': isDisabled }"
    v-bind="$attrs"
    :disabled="isDisabled"
  >
    <template #icon="{ isActive }">
      <ComingSoonBadge v-if="props.comingSoon" class="ivyforms-collapse-item__coming-soon" />
      <i class="el-icon el-collapse-item__arrow">
        <component
          :is="isActive ? ArrowsOutlineChevronUp : ArrowsOutlineChevronDown"
          class="icon"
        />
      </i>
    </template>
    <template #title>
      <span class="ivyforms-collapse-item__title">
        <slot name="title">{{ props.title }}</slot>
      </span>
    </template>
    <slot />
  </ElCollapseItem>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import ArrowsOutlineChevronDown from '@/assets/icons/arrows/outline/chevron-down.svg?component'
import ArrowsOutlineChevronUp from '@/assets/icons/arrows/outline/chevron-up.svg?component'

const props = defineProps({
  disabled: {
    type: Boolean,
    default: false,
  },
  comingSoon: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: '',
  },
})

const isDisabled = computed(() => props.disabled || props.comingSoon)
</script>

<style scoped lang="scss">
.ivyforms-collapse-item {
  &.el-collapse-item {
    border-bottom: 1px solid var(--map-divider);

    :deep(.el-collapse-item__header) {
      font-size: 14px;
      font-style: normal;
      font-weight: 500;
      line-height: 20px; /* 142.857% */
      color: var(--map-base-text-0);
      padding: 8px 0;
      background: var(--map-ground-level-1-foreground);
      border: none;
      min-height: auto;
    }

    :deep(.el-icon) {
      width: 24px;
      height: 24px;
      margin: 0px 8px 0 auto;

      svg {
        width: 24px;
        height: 24px;
      }
    }

    :deep(.el-collapse-item__wrap) {
      padding-bottom: 8px;
      padding-top: 4px;
      background: var(--map-ground-level-1-foreground);
      border: none;
    }

    :deep(.el-collapse-item__content) {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      padding-bottom: 0;
    }
  }

  &.is-disabled {
    cursor: not-allowed;

    :deep(.el-collapse-item__header span) {
      opacity: 0.5;
    }

    :deep(.el-icon) {
      margin: 4px 8px 0 16px;
      opacity: 0.5;
    }
  }

  &__coming-soon {
    margin-left: auto;
  }
}
</style>
