<template>
  <div
    class="ivyforms-filter ivyforms-ml-8"
    :class="[
      `size-${props.size}`,
      {
        'is-clearable': showClearButton,
        'is-open': isOpen,
        'has-value': Array.isArray(localModelValue) ? localModelValue.length > 0 : localModelValue,
        'is-disabled': props.disabled,
        'is-icon-only': props.showOnlyIcon,
        'is-full-width': props.isFullWidth,
        'is-loading': props.loading,
      },
    ]"
  >
    <IvyButtonOption
      v-if="props.showOnlyIcon"
      ref="filterButtonRef"
      :icon-start="props.icon"
      :icon-start-type="props.iconType"
      :icon-start-category="props.iconCategory"
      :type="props.type"
      :priority="props.priority"
      :active="isWithValue"
      @click="openFilterOptions()"
    />
    <!-- Button -->
    <div
      v-else
      ref="filterButtonRef"
      class="ivyforms-filter__button"
      :class="{
        'ivyforms-filter__button--border': props.type === 'border',
        'ivyforms-filter__button--loading': props.loading,
      }"
      @click="openFilterOptions()"
    >
      <!-- Loading Spinner -->
      <div v-if="props.loading" class="ivyforms-filter__button__loading">
        <span class="ivyforms-filter__button__spinner-container">
          <svg viewBox="0 0 50 50" class="ivyforms-filter__button__spinner">
            <circle class="ivyforms-filter__button__spinner__line" cx="25" cy="25" r="22.5" />
          </svg>
        </span>
      </div>

      <!-- Placeholder or Selected Value - Hide when loading -->
      <template v-if="!props.loading">
        <span v-if="!isWithValue" class="ivyforms-filter__button__placeholder">
          {{ props.placeholder }}
        </span>
        <template v-if="isWithValue">
          <div class="ivyforms-filter__button__label">
            <span class="ivyforms-text-cut">{{ getTags() }}</span>
          </div>
        </template>
      </template>

      <!-- Clear -->
      <div
        v-if="showClearButton && !props.loading"
        class="ivyforms-filter__button__clear ivyforms-filter__icon"
      >
        <IvyIcon
          class="ivyforms-filter__icon"
          name="close"
          size="s"
          priority="tertiary"
          type="outline"
          color="var(--map-base-dusk-symbol-2)"
          @click.stop="clearSelection"
        />
      </div>
      <!-- /Clear -->

      <!-- Arrow -->
      <div v-else-if="!props.loading" class="ivyforms-filter__button__arrow ivyforms-filter__icon">
        <IvyIcon
          priority="tertiary"
          name="chevron-down"
          type="outline"
          category="arrows"
          size="s"
          color="var(--map-base-dusk-symbol-2)"
        />
      </div>
      <!-- /Arrow -->
    </div>
    <!-- /Button -->

    <!-- Tablet & Up Filter -->
    <IvyPopover
      ref="filterPopoverRef"
      trigger="click"
      placement="bottom-start"
      :virtual-ref="filterButtonRef"
      :show-arrow="false"
      :width="popoverWidth"
      virtual-triggering
      popper-class="ivyforms-filter-popover"
      :popper-background="props.popperBackground"
      :is-with-scrollbar="false"
      @before-enter="isOpen = true"
      @after-leave="closeFilterOptions()"
    >
      <!-- Search -->
      <div v-if="searchable" class="ivyforms-px-8 ivyforms-pt-8">
        <IvySearch
          v-model="filterText"
          :placeholder="getLabel('search')"
          :should-collapse-on-mobile="false"
          @update:model-value="executeRemoteMethod"
        />
      </div>
      <!-- /Search -->

      <!-- Items -->
      <IvyScrollbar>
        <IvyFilterItems
          class="ivyforms-filter-popover__items"
          :options="options"
          :selected-items="localModelValue"
          :filter-text="filterText"
          :loading="isLoading"
          @select="selectOption"
        />
      </IvyScrollbar>
      <!-- /Items -->
    </IvyPopover>
    <!-- /Tablet & Up Filter -->
  </div>
</template>

<script setup lang="ts">
import { onClickOutside } from '@vueuse/core'
import { ref, unref, computed, type Ref } from 'vue'
import type { IconCategory } from '@/types/icons/icon-category'
import type { IconType } from '@/types/icons/icon-type'
import { useLabels } from '@/composables/useLabels'
const { getLabel } = useLabels()

interface Props {
  modelValue: (string | number)[] | string | number | null
  size?: 'l' | 'd' | 's' | 'xs'
  priority?:
    | 'primary'
    | 'secondary'
    | 'tertiary'
    | 'success'
    | 'warning'
    | 'danger'
    | 'white'
    | 'shadow-white'
  popperClass?: string
  icon?: string
  iconCategory?: IconCategory
  iconColor?: string
  iconType?: IconType
  clearable?: boolean
  searchable?: boolean
  disabled?: boolean
  placeholder?: string
  loading?: boolean
  // eslint-disable-next-line vue/require-default-prop
  options?: Array<{
    value: string | number
    label: string
    icon?: string
    iconColor?: string
    iconType?: string
    iconCategory?: IconCategory
    subtitle?: string
    children?: Array<{
      value: string | number
      label: string
      icon?: string
      iconColor?: string
      iconCategory?: IconCategory
    }>
  }>
  popoverWidth?: string
  showOnlyIcon?: boolean
  type?: 'fill' | 'border'
  popperBackground?: 'level-2-foreground' | 'level-1-foreground'
  isFullWidth?: boolean
  // eslint-disable-next-line vue/require-default-prop
  remoteMethod?: (search: string) => Promise<void>
}

const props = withDefaults(defineProps<Props>(), {
  size: 'd',
  placeholder: '',
  priority: 'primary',
  popoverWidth: '272px',
  popperClass: '',
  icon: 'filter',
  iconCategory: 'global',
  iconType: 'outline',
  iconColor: '',
  searchable: true,
  type: 'fill',
  isFullWidth: false,
  popperBackground: 'level-2-foreground',
})

const emit = defineEmits(['update:modelValue', 'visibleChange', 'change'])

const isOpen: Ref<boolean> = ref(false)
const isLoading: Ref<boolean> = ref(false)
const filterButtonRef = ref(null)
const filterPopoverRef = ref(null)
const filterText: Ref<string> = ref('')

onClickOutside(filterButtonRef, () => {
  unref(filterPopoverRef)
})

const localModelValue = computed({
  get() {
    return props.modelValue
  },
  set(value) {
    emit('update:modelValue', value)
    emit('change', value)
  },
})

const showClearButton = computed(() => {
  const value = localModelValue.value

  return props.clearable && (Array.isArray(value) ? value.length : value)
})

const isWithValue = computed((): boolean => {
  const value = localModelValue.value

  return Array.isArray(value) ? value.length > 0 : !!value
})

const tempModelValue = ref(localModelValue.value)

const getTags = () => {
  for (const option of props.options) {
    if (option.children) {
      for (const child of option.children) {
        if (child.value === localModelValue.value[0]) {
          return child.label
        }
      }
    }

    if (
      option.value ===
      (Array.isArray(localModelValue.value) ? localModelValue.value[0] : localModelValue.value)
    ) {
      return option.label
    }
  }
}

const clearSelection = () => {
  if (props.clearable) {
    if (Array.isArray(localModelValue.value)) {
      localModelValue.value = []
    } else {
      localModelValue.value = null
    }

    emit('update:modelValue', Array.isArray(localModelValue.value) ? [] : null)
  }
}

const openFilterOptions = () => {
  if (Array.isArray(localModelValue.value)) {
    tempModelValue.value = [...localModelValue.value]
  } else {
    tempModelValue.value = localModelValue.value
  }
  isOpen.value = true

  executeRemoteMethod('')
}

const closeFilterOptions = () => {
  isOpen.value = false
  filterText.value = ''
}

const selectOption = (value: number | string) => {
  if (Array.isArray(localModelValue.value)) {
    const valueIndex = localModelValue.value.findIndex((entity) => entity === value)
    if (valueIndex === -1) {
      localModelValue.value.push(value)
    } else {
      localModelValue.value.splice(valueIndex, 1)
    }
  } else {
    localModelValue.value = value
    if (filterPopoverRef.value) {
      filterPopoverRef.value.hide()
    }
  }
}

const executeRemoteMethod = async (value: string) => {
  if (props.remoteMethod) {
    isLoading.value = true
    await props.remoteMethod(value)
    isLoading.value = false
  }
}
</script>

<style lang="scss">
@use 'sass:list' as *;
@use 'sass:meta' as *;
// Controls display properties of clear and arrow icons in the filter component
@mixin displayIcons($clearDisplay: none, $arrowDisplay: block) {
  .ivyforms-filter__suffix--clear {
    display: $clearDisplay;
  }

  .ivyforms-filter__suffix--arrow {
    display: $arrowDisplay;
  }
}

// Filter
.ivyforms-filter {
  --placeholder-font-size: 16px;
  --placeholder-line-height: 24px;
  position: relative;
  -webkit-tap-highlight-color: transparent;
  max-width: 100%;
  border-radius: var(--Radius-radius-md, 8px);
  background: var(--map-base-dusk-o05);
  width: 185px;
  // Button
  &__button {
    display: flex;
    border-radius: var(--Radius-radius-md, 8px);
    align-items: center;
    padding: var(--Spacing-sm, 8px) var(--Spacing-lg, 12px);
    gap: var(--Spacing-sm, 8px);
    cursor: pointer;
    color: var(--map-base-text-0);
    height: var(--button-height);
    box-sizing: border-box;
    @include transition(all, 0.3s);

    // Placeholder
    &__placeholder {
      font-size: var(--placeholder-font-size);
      line-height: var(--placeholder-line-height);
      white-space: nowrap;

      &.with-loading {
        margin-left: 8px;
      }
    }
    // Label
    &__label {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 4px;
      border-radius: 4px;
      font-size: 16px;
      font-style: normal;
      font-weight: 400;
      line-height: 20px;
      color: var(--map-base-text-0);
      overflow: hidden;
    }
    // Tag
    //&__tag {
    //  display: flex;
    //  padding: 0 8px;
    //  justify-content: center;
    //  align-items: center;
    //  gap: 4px;
    //  border-radius: 4px;
    //  font-size: 16px;
    //  font-style: normal;
    //  font-weight: 400;
    //  line-height: 20px;
    //  color: var(--map-base-text-0);
    //
    //  // Tag With Label
    //  &--with-label {
    //    overflow: auto;
    //  }
    //}

    // Loading spinner
    &__loading {
      display: flex;
      align-items: center;
      justify-content: center;
    }

    &__spinner-container {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 18px;
      height: 18px;
    }

    &__spinner {
      width: 100%;
      height: 100%;
      animation: ivyforms-filter-spin 1s linear infinite;

      &__line {
        fill: none;
        stroke: var(--map-base-dusk-symbol-0);
        stroke-width: 4;
        stroke-linecap: round;
        stroke-dasharray: 150;
        stroke-dashoffset: 0;
        animation: ivyforms-filter-dash 1.5s ease-in-out infinite;
      }
    }

    // Clear & Arrow
    &__clear,
    &__arrow {
      @include flipProperty('margin-left', 'margin-right', auto);
    }

    // Border
    &--border {
      border: 1px solid var(--map-base-dusk-stroke-0);

      // Hover
      &:hover {
        border: 1px solid var(--map-base-brand-stroke-0);
      }
    }

    // Loading state
    &--loading {
      justify-content: center;

      .ivyforms-filter__button__loading {
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
      }
    }
  }

  // Arrow
  &__suffix--arrow {
    z-index: 2;
    position: absolute;
    top: var(--icon-top, 8px);
    pointer-events: none;
    @include transition(transform, 0.3s);
    @include flipProperty('right', 'left', 12px);
  }

  // Clear
  &__suffix--clear {
    display: none;
    position: absolute;
    top: var(--icon-top, 8px);
    pointer-events: none;
    cursor: pointer;
    @include flipProperty('right', 'left', 12px);
  }

  // Icon
  .ivyforms-filter__icon {
    @include transition();
  }

  // Hover (Only Desktop)
  @media (hover: hover) and (pointer: fine) {
    &:hover:not(.is-disabled) {
      // Wrapper
      .el-select__wrapper {
        background: var(--map-base-brand-o10);
      }
    }
  }

  // Clearable
  &.is-clearable {
    // Initially display the clear icon and hide the arrow
    @include displayIcons(block, none);

    // Desktop Small & Up
    @include desktop-small-and-up {
      // Reverse the display properties on larger screens
      @include displayIcons(none, block);

      // Change back on hover
      &:hover:not(.is-disabled) {
        @include displayIcons(block, none);
      }
    }
  }

  // Focus
  &.is-keyboard-focused:focus-within:not(.is-open):not(.is-disabled) {
    // Wrapper
    .el-select__wrapper {
      background: var(--map-base-brand-o10);

      // Focused
      &.is-focused {
        border: 1px solid var(--map-base-brand-stroke-0);

        // Before
        &::before {
          content: '';
          box-shadow: 0 0 0 5px var(--map-base-brand-stroke-0);
          opacity: 0.4;
          position: absolute;
          top: 0;
          width: 100%;
          height: 100%;
          border-radius: 24px;
          z-index: 0;
          @include transition();
          @include flipProperty('left', 'right', 0);
        }
      }
    }
  }

  // Open
  &.is-open {
    // Filter Button
    .ivyforms-filter__button:not(.is-icon-only) {
      background: var(--map-base-brand-o10);
      border: 1px solid var(--map-base-brand-stroke-0);

      // Arrow
      .ivyforms-filter__button__arrow {
        transform: rotate(180deg);
        transition: var(--el-transition-duration);
      }

      // Border Button
      &.ivyforms-filter__button--border {
        background-color: transparent;
      }
    }

    // Element Select
    .el-select {
      // Wrapper
      .el-select__wrapper {
        background: var(--map-base-brand-o10);
        border: 1px solid var(--map-base-brand-stroke-0);
      }
    }

    // Arrow
    .ivyforms-filter__suffix--arrow {
      transform: rotate(180deg);
    }
  }

  // Disabled
  &.is-disabled {
    opacity: 0.5;
  }

  // Size
  &.size {
    // XS
    &-xs {
      --button-height: 28px;
      --icon-top: calc((28px - 24px) / 2);
      --placeholder-font-size: 14px;
      --placeholder-line-height: 20px;
    }

    // S
    &-s {
      --button-height: 36px;
      --icon-top: calc((36px - 24px) / 2);
    }

    // L
    &-l {
      --button-height: 48px;
      --icon-top: calc((48px - 24px) / 2);
    }

    // D
    &-d {
      --button-height: 40px;
      --icon-top: calc((40px - 24px) / 2);
    }
  }

  // Full Width
  &.is-full-width {
    width: 100%;
  }
}

// Filter Popover
.ivyforms-filter-popover {
  // Element Popper
  &.el-popper {
    gap: 0;
    border-radius: 4px;
    &.level-2-foreground {
      padding: 8px 0;
    }
  }

  // Items
  &__items {
    max-height: 400px;
  }
}

@keyframes ivyforms-filter-spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

@keyframes ivyforms-filter-dash {
  0% {
    stroke-dasharray: 1, 150;
    stroke-dashoffset: 0;
  }
  50% {
    stroke-dasharray: 90, 150;
    stroke-dashoffset: -35;
  }
  100% {
    stroke-dasharray: 90, 150;
    stroke-dashoffset: -124;
  }
}
</style>
