<template>
  <ElSelect
    ref="elInputRef"
    v-bind="{ ...$attrs, ...$props }"
    v-model="model"
    :placeholder="props.placeholder"
    :disabled="props.disabled"
    :readonly="props.readonly"
    :multiple="props.multiple"
    :collapse-tags="props.multiple"
    :clearable="props.clearable && !props.readonly"
    :class="[
      {
        'is-clearable': props.clearable && !props.readonly,
        'is-filled': props.filled,
        'is-secondary': isSecondary,
        'is-error': props.error,
        'is-readonly': props.readonly,
      },
      'ivyforms-form-item-select',
    ]"
    :max-collapse-tags="props.maxTags"
    :aria-label="computedAriaLabel"
    :fit-input-width="props.fitInputWidth"
  >
    <template v-if="!!slots['prefix'] || iconStart" #prefix>
      <slot name="prefix">
        <IvyIcon
          v-if="iconStart"
          :name="iconStart"
          :category="iconStartCategory"
          :type="iconStartType"
          class="ivyforms-input__icon-start"
          size="l"
          color="var(--map-base-dusk-symbol-2)"
        />

        <!--            TODO Add the avatar component-->
        <IvyIcon v-if="props.avatar" class="ivyforms-input__avatar" name="user" size="l" />
        <!--            <IvyAvatar-->
        <!--                v-if="props.avatar"-->
        <!--                class="ivyforms-input__avatar"-->
        <!--            />-->
      </slot>
    </template>
    <slot />
  </ElSelect>
</template>

<script setup lang="ts">
import { useSlots, inject, computed, ref, onMounted, nextTick } from 'vue'
import type { IconCategory } from '@/types/icons/icon-category'
import type { IconType } from '@/types/icons/icon-type'
import { useLabels } from '@/composables/useLabels'
const { getLabel } = useLabels()

interface Props {
  modelValue: string | string[] | null | undefined
  label?: string
  placeholder?: string
  iconStart?: string
  iconEnd?: string
  iconStartCategory?: IconCategory
  iconEndCategory?: IconCategory
  avatar?: string
  iconStartType?: IconType
  iconEndType?: IconType
  filled?: boolean
  prefix?: string
  suffix?: string
  clearable?: boolean
  disabled?: boolean
  readonly?: boolean
  multiple?: boolean
  maxTags?: number
  ariaLabel?: string
  error?: boolean
  fieldType?: string
  fitInputWidth?: boolean
  secondary?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  placeholder: '',
  iconStartCategory: 'global',
  iconEndCategory: 'global',
  iconStartType: 'outline',
  iconEndType: 'outline',
  label: '',
  iconStart: '',
  iconEnd: '',
  avatar: '',
  prefix: '',
  suffix: '',
  maxTags: 3,
  ariaLabel: '',
  error: false,
  readonly: false,
  fieldType: '',
  fitInputWidth: true,
  secondary: false,
})

const isSecondary = computed(() => props.secondary || inject('isSecondary', false))

const slots = useSlots()

const emit = defineEmits(['update:modelValue'])

const elInputRef = ref(null)

const model = computed({
  get: () => props.modelValue,
  set: (value) => {
    if (!props.readonly) {
      emit('update:modelValue', value)
    }
  },
})

const computedAriaLabel = computed(() => props.ariaLabel || getLabel('select'))

// Add keyboard accessibility to select dropdown wrap element
onMounted(() => {
  nextTick(() => {
    // Find all el-select-dropdown__wrap elements and make them focusable
    const dropdownWraps = document.querySelectorAll('.el-select-dropdown__wrap')
    dropdownWraps.forEach((wrap) => {
      const wrapElement = wrap as HTMLElement
      // Make dropdown wrap focusable with keyboard
      if (!wrapElement.hasAttribute('tabindex')) {
        wrapElement.setAttribute('tabindex', '0')
      }
      // Add ARIA role for better screen reader support
      if (!wrapElement.hasAttribute('role')) {
        wrapElement.setAttribute('role', 'region')
        wrapElement.setAttribute('aria-label', getLabel('select_options'))
      }
    })
  })
})
</script>

<style lang="scss">
.ivyforms-form-item-select {
  .el-select__wrapper {
    border: 1px solid var(--map-base-dusk-stroke--2);
    background: var(--map-base-dusk-o05);
    border-radius: var(--Radius-radius-md, 8px);
    display: flex;
    align-items: center;
    gap: var(--Spacing-sm, 8px);
    padding: var(--Spacing-sm, 8px) var(--Spacing-lg, 12px);
    height: 40px;
    box-shadow: none !important;

    &.is-disabled {
      background: var(--map-base-dusk-o05);
    }

    &:hover:not(.is-disabled),
    &.is-hovering:not(.is-focused):not(.is-disabled) {
      border: 1px solid var(--map-base-dusk-stroke-0);
    }

    &:focus,
    &:active,
    &.is-active,
    &.is-focused {
      border: 1px solid var(--map-base-brand-stroke-0) !important;

      .el-tag {
        background: var(--map-base-brand-o10);
        color: var(--map-base-brand-symbol-0);

        .el-tag__close svg {
          color: var(--map-base-brand-symbol-0);
          width: 14px;
          height: 14px;
        }
      }
    }

    &:hover:focus,
    &:hover.is-focused,
    &.is-hovering.is-focused {
      border: 1px solid var(--map-base-brand-stroke-0) !important;
    }

    .el-select__selection {
      padding: 0;
      font-size: 16px;
      font-weight: 400;
      line-height: 22px;
      color: var(--map-base-text-0);
      background-color: transparent;
      display: flex;
      align-items: center;
      gap: 4px;
      flex: 1 0 0;
    }

    .el-select__suffix .el-icon.el-select__caret.el-select__icon {
      background: url('../../../assets/icons/arrows/line/chevron-down.svg') no-repeat center;
      background-size: contain;
      width: 20px;
      height: 20px;
      cursor: pointer;
      opacity: 1 !important;

      &.el-select__clear {
        background: url('../../../assets/icons/global/fill-duo/close-circle.svg') no-repeat center;
      }
    }

    .el-select__placeholder {
      color: var(--map-base-text-0);
    }
  }

  &.is-secondary .el-select__wrapper {
    &:active,
    &:focus,
    &.is-focused,
    &.is-active {
      border: 1px solid var(--map-base-purple-stroke-0) !important;

      .el-tag {
        background: var(--map-base-purple-o10);
        color: var(--map-base-purple-symbol-0);

        .el-tag__close svg {
          color: var(--map-base-purple-symbol-0);
          width: 14px;
          height: 14px;
        }
      }
    }
  }

  // Readonly
  &.is-readonly {
    pointer-events: none;

    .el-select__wrapper {
      cursor: default;

      // Keep normal opacity unlike disabled state
      opacity: 1;
      background: var(--map-base-dusk-o05);

      &:hover,
      &.is-hovering,
      &:focus,
      &:active,
      &.is-active,
      &.is-focused {
        border: 1px solid var(--map-base-dusk-stroke--2);
        cursor: default;
      }

      .el-select__suffix .el-icon.el-select__caret.el-select__icon {
        display: none;
      }

      // Maintain normal text color
      .el-select__selection {
        color: var(--map-base-text-0);
        cursor: default;
      }
    }

    // Hide dropdown options
    .el-select-dropdown {
      display: none !important;
    }
  }

  // Filled
  &.is-filled {
    .el-select__wrapper {
      border: 1px solid var(--map-base-dusk-stroke--2);
      background: var(--map-base-dusk-o05);

      &:hover,
      &.is-hovering {
        border: 1px solid var(--map-base-dusk-stroke-0);
      }

      &:active,
      &.is-active,
      &:focus,
      &.is-focused {
        border: 1px solid var(--map-base-brand-stroke-0);

        .el-tag {
          background: var(--map-base-brand-o10);
          color: var(--map-base-brand-symbol-0);

          .el-tag__close svg {
            color: var(--map-base-brand-symbol-0);
            width: 14px;
            height: 14px;
          }
        }
      }

      .el-tag {
        background: var(--map-base-dusk-o20);
        color: var (--map-base-text-0);

        .el-tag__close svg {
          color: var(--map-base-text-0);
          width: 14px;
          height: 14px;
        }
      }
    }

    &.is-error .el-select__wrapper {
      border: 1px solid var(--map-status-error-symbol-0);
    }

    &.is-warning .el-select__wrapper {
      border: 1px solid var(--map-status-warning-stroke-0);
    }

    .el-tag {
      font-size: 14px;
      font-weight: 400;
      line-height: 20px;
      display: flex;
      padding: 2px 4px 2px 8px;
      align-items: center;
      gap: 4px;
      cursor: pointer;
      background: var(--map-base-dusk-o20);
      color: var(--map-base-text-0);

      .el-tag__close {
        background: url('../../../assets/icons/global/outline/close.svg') no-repeat center;
        background-size: contain;
        width: 16px;
        height: 16px;
        cursor: pointer;
        margin: 0;

        svg {
          color: var(--map-base-text-0);
          width: 14px;
          height: 14px;
        }

        &:hover {
          background: url('../../../assets/icons/global/outline/close.svg') no-repeat center;
          width: 16px;
          height: 16px;
        }
      }
    }
  }

  // Option Tag
  .el-tag--info {
    background: var(--map-base-dusk-o10);
    .el-select__tags-text {
      color: var(--map-base-text-0);
    }
  }
}

.el-select__popper {
  .el-scrollbar {
    border-radius: 3px;
    max-height: 161px;

    .el-select-dropdown__wrap {
      max-height: 161px;
    }
  }
}

.el-form-item {
  &.is-error {
    .ivyforms-form-item-select.is-error {
      .el-select__wrapper {
        border: 1px solid var(--map-status-error-symbol-0);
      }
    }
  }
}

// Rating icon colors for IvySelectInput prefix
.ivyforms-form-item-select {
  .el-select__wrapper {
    .el-select__selection {
      color: var(--map-base-text-0);
    }
  }

  .ivyforms-input__icon-start {
    // All line icons use dusk color (same as IvyRating unfilled state)
    .ivyforms-icon__svg.type-line {
      path {
        stroke: var(--map-base-dusk-symbol-2);
      }
    }
  }
}
</style>
