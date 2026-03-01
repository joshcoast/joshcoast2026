<template>
  <button
    class="ivyforms-indicator-button__wrapper"
    :class="{ 'is-active': isActive, 'is-fab': props.color === 'fab' }"
    :style="{ background: getBackgroundColor() }"
    :aria-label="props.ariaLabel || getLabel('indicator_button')"
    @click="toggleActiveState"
    @mouseenter="isHovered = true"
    @mouseleave="isHovered = false"
  >
    <IvyIcon :name="currentIcon" :color="getIconColor()" type="outline" size="l"> </IvyIcon>
    <span
      v-if="showIndicator"
      class="ivyforms-indicator-button__indicator"
      :class="`priority-${props.indicatorPriority}`"
      @mouseenter="indicatorHovered = true"
      @mouseleave="indicatorHovered = false"
      @click.stop="hideIndicator"
    >
      <span v-if="showIndicatorX" class="ivyforms-indicator-button__indicator-x">×</span>
    </span>
  </button>
</template>

<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import { useLabels } from '@/composables/useLabels'
const { getLabel } = useLabels()

interface Props {
  type: 'sort' | 'filter' | 'folder' | 'datepicker' | 'import' | 'export'
  color?: 'fab' | 'tertiary'
  indicator?: boolean
  indicatorPriority?: 'primary' | 'secondary' | 'amber'
  customHideIndicator?: () => void
  ariaLabel?: string
}

const props = withDefaults(defineProps<Props>(), {
  type: 'sort',
  color: 'tertiary',
  indicator: false,
  indicatorPriority: 'secondary',
  customHideIndicator: undefined,
  ariaLabel: '',
})

const showIndicator = ref(props.indicator)
const isActive = ref(false)
const isHovered = ref(false)
const indicatorHovered = ref(false)

// Only show the "x" when the indicator is on and hovered
const showIndicatorX = computed(
  () => showIndicator.value && (isHovered.value || indicatorHovered.value),
)

// Toggle active state
const toggleActiveState = () => {
  isActive.value = !isActive.value
}

// Logic for the indicator
const hideIndicator = () => {
  if (props.customHideIndicator) {
    props.customHideIndicator()
  } else {
    showIndicator.value = false
  }
  isActive.value = false
  // Reset hover states when the indicator is hidden
  isHovered.value = false
  indicatorHovered.value = false
}

watch(
  () => props.indicator,
  (newValue) => {
    showIndicator.value = newValue
  },
)

// Reset hover state when indicator is turned off
watch(showIndicator, (val) => {
  if (!val) {
    isHovered.value = false
    indicatorHovered.value = false
  }
})

const getIconName = (type: Props['type']) => {
  const icons = {
    sort: { default: 'sort-default', hover: 'sort-from-top', active: 'sort-from-top' },
    filter: { default: 'filter', hover: 'filter', active: 'filter' },
    folder: { default: 'folder', hover: 'folder', active: 'folder' },
    datepicker: { default: 'calendar-dot', hover: 'calendar-dot', active: 'calendar-dot' },
    import: { default: 'import', hover: 'import', active: 'import' },
    export: { default: 'export', hover: 'export', active: 'export' },
  }

  return isActive.value
    ? icons[type].active
    : isHovered.value
      ? icons[type].hover
      : icons[type].default
}

const getBackgroundColor = () => {
  if (props.color === 'tertiary') {
    return 'var(--map-base-dusk-fill--4)'
  }
  return 'var(--map-ground-level-3)'
}

const getIconColor = () => {
  if (props.color !== 'tertiary') {
    return isHovered.value || isActive.value
      ? 'var(--map-base-purple-symbol-0)'
      : 'var(--map-base-dusk-symbol-2)'
  }
  return 'var(--map-base-dusk-symbol-2)'
}

// Computed property for icon name
const currentIcon = computed(() => getIconName(props.type))
</script>

<style lang="scss">
.ivyforms-indicator-button__wrapper {
  position: relative;
  display: flex;
  height: 40px;
  width: 40px;
  align-items: center;
  padding: 0;
  align-self: stretch;
  border-radius: 8px;
  cursor: pointer;
  border: none;
  outline: none;

  &.is-fab {
    box-shadow: 0 4px 15px 0 rgba(0, 0, 0, 0.05);

    &:hover {
      .ivyforms-icon__svg {
        // Global & Arrows
        &.category-global,
        &.category-arrows {
          fill: var(--map-base-purple-fill--1);
        }
      }
    }
  }

  &:not(.is-fab) {
    &:hover {
      position: relative;

      &::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: var(--map-bw-o90);
        opacity: 0.05;
        border-radius: inherit;
        pointer-events: none;
      }
    }
  }

  // Icon
  .ivyforms-icon {
    padding: var(--Spacing-sm, 8px) 8px;
  }

  // Indicator
  .ivyforms-indicator-button__indicator {
    width: 12px;
    height: 12px;
    position: absolute;
    top: 1px;
    right: 1px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--map-status-success-fill-0);
    cursor: pointer;
    font-size: 0;

    &.priority-secondary {
      background: var(--map-base-purple-fill--1);
    }

    &.priority-amber {
      background: var(--map-status-warning-fill-0);
    }

    .ivyforms-indicator-button__indicator-x {
      font-size: 10px;
      font-weight: bold;
      color: $primitive-white;
      line-height: 10px;
      pointer-events: none;
      user-select: none;
      font-family: inherit;
      display: flex;
      align-items: center;
      justify-content: center;
    }
  }
}
</style>
