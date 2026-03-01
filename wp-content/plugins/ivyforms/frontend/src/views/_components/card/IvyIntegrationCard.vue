<template>
  <div
    class="ivyforms-integration-card ivyforms-flex ivyforms-flex-direction-column ivyforms-align-items-start ivyforms-p-24 ivyforms-flex-1 ivyforms-border-radius-8"
    :class="cardClass"
  >
    <div v-if="isPro" class="ivyforms-integration-card__pro-line"></div>
    <div
      v-if="isPro"
      class="ivyforms-integration-card__pro-badge-wrapper ivyforms-flex ivyforms-align-items-end"
    >
      <ProBadge class="ivyforms-integration-card__pro-badge ivyforms-shadow-100" />
    </div>
    <div
      class="ivyforms-integration-card__content ivyforms-flex ivyforms-flex-direction-column ivyforms-align-items-start ivyforms-gap-20 ivyforms-width-100"
    >
      <div
        class="ivyforms-integration-card__top-row ivyforms-flex ivyforms-align-items-center ivyforms-gap-16 ivyforms-width-100"
      >
        <div
          class="ivyforms-integration-card__image-wrapper ivyforms-flex ivyforms-justify-content-center ivyforms-align-items-center"
        >
          <IvyIcon category="integrations" :name="image || 'default'" size="l" />
        </div>
        <div class="ivyforms-integration-card__title medium-18">{{ title }}</div>
        <div
          v-if="isSoon"
          class="ivyforms-integration-card__badge ivyforms-integration-card__badge--soon"
        >
          <ComingSoonBadge></ComingSoonBadge>
        </div>
        <IvyToggle
          v-else
          class="ivyforms-integration-card__toggle ivyforms-ml-auto"
          :model-value="toggleValue"
          :disabled="isToggleDisabled"
          @update:model-value="onToggleChange"
        />
      </div>
      <div class="ivyforms-integration-card__desc regular-16 text-secondary">{{ description }}</div>
      <IvyButtonAction
        class="ivyforms-integration-card__button"
        :priority="buttonProps.priority"
        :type="buttonProps.type"
        :loading="isLoading"
        @click="onButtonClick"
      >
        {{ buttonText }}
      </IvyButtonAction>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useLabels } from '@/composables/useLabels.ts'
import type { integrationType } from '@/constants/integrations.ts'
const { getLabel } = useLabels()

const props = defineProps<{
  title: string
  description: string
  image?: string
  type: integrationType
  buttonText?: string
  buttonType?: string
  toggleValue?: boolean
  toggleDisabled?: boolean
  isLoading?: boolean
  onButtonClick?: () => void
  onToggleChange?: (val: boolean) => void
}>()

// Show Pro badge only when user needs to upgrade (doesn't have Pro or doesn't have required plan)
const isPro = computed(() => props.type === 'upgrade')
const isSoon = computed(() => props.type === 'soon')

// Disable toggle for: upgrade (needs Pro), soon (coming soon), disabled (plugin not installed), or explicitly disabled via prop
const isToggleDisabled = computed(
  () =>
    props.type === 'upgrade' ||
    props.type === 'soon' ||
    props.type === 'disabled' ||
    props.toggleDisabled === true,
)

const cardClass = computed(() => {
  return {
    'ivyforms-integration-card--active': props.type === 'active',
    'ivyforms-integration-card--disabled': props.type === 'disabled',
    'ivyforms-integration-card--upgrade': props.type === 'upgrade',
    'ivyforms-integration-card--pro-active': props.type === 'proActive',
    'ivyforms-integration-card--pro-disabled': props.type === 'proDisabled',
    'ivyforms-integration-card--soon': props.type === 'soon',
  }
})

const buttonText = computed(() => {
  switch (props.type) {
    case 'upgrade':
      return getLabel('upgrade')
    case 'disabled':
      return getLabel('install')
    case 'active':
      // For wpDataTables: if toggle is ON, show "Create Tables", otherwise "Learn More"
      // For other Lite integrations: always show "Learn More"
      return props.toggleValue ? props.buttonText || getLabel('learn_more') : getLabel('learn_more')
    case 'soon':
      return getLabel('learn_more')
    case 'proActive':
    case 'proDisabled':
      // If custom button text is provided, use it (e.g., "Activate License")
      if (props.buttonText) {
        return props.buttonText
      }
      // If toggle is on (enabled), show "Go to Settings", otherwise "Learn More"
      return props.toggleValue ? getLabel('go_to_settings') : getLabel('learn_more')
    default:
      return ''
  }
})

const buttonProps = computed(
  (): {
    priority:
      | 'primary'
      | 'secondary'
      | 'tertiary'
      | 'success'
      | 'warning'
      | 'danger'
      | 'white'
      | 'shadow-white'
      | 'pro'
    type: 'fill' | 'border' | 'ghost'
  } => {
    switch (props.type) {
      // case 'active':
      //   return { priority: 'primary', type: 'border' }
      case 'upgrade':
        return { priority: 'pro', type: 'fill' }
      case 'disabled':
        return { priority: 'primary', type: 'fill' }
      case 'soon':
      case 'active':
        return { priority: 'tertiary', type: 'fill' }
      case 'proActive':
      case 'proDisabled':
        return { priority: 'tertiary', type: 'fill' }
      default:
        return { priority: 'primary', type: 'fill' }
    }
  },
)
</script>

<style scoped lang="scss">
.ivyforms-integration-card {
  position: relative;
  max-width: 394px;
  border: 1px solid var(--map-base-dusk-stroke--2);
  background: var(--map-ground-level-1-foreground);
  flex: 1 0 0;
  &__pro-line {
    position: absolute;
    top: -2px;
    left: 0;
    width: 100%;
    height: 4px;
    border-radius: 8px 8px 0 0;
    border-top: 4px solid var(--map-accent-amber-stroke-0);
    z-index: 2;
  }
  &__pro-badge-wrapper {
    position: absolute;
    top: -12px;
    left: 16px;
    z-index: 3;
    pointer-events: none;

    :deep(.ivyforms-pro-badge svg) {
      border-radius: 0 !important;
    }
  }
  &__title {
    color: var(--map-base-text-0);
  }
  &__image-wrapper {
    background-color: var(--map-base-dusk-o10);
    border-radius: 50%;
    width: 48px;
    height: 48px;
  }
}
</style>
