<template>
  <div ref="formItem" class="ivyforms-form-item">
    <ElFormItem
      v-bind="{ ...$props, ...$attrs }"
      :label-position="elementLabelPosition"
      :label="props.label"
      :class="[
        {
          'has-value': hasValue,
          'is-active': isFormItemActive,
          'is-form-element-hovered': isContentHovered,
          'is-disabled': isFormItemDisabled,
          'is-warning': warning,
          'is-secondary': secondary,
          'has-info-message': showInfo,
          'has-custom-label': true,
        },
        getFormItemTypeClass(),
      ]"
    >
      <!-- Always use custom label template to maintain consistent styling -->
      <template #label>
        <slot name="label" />
        <!-- Conditionally use left or right label wrapper based on labelPosition -->
        <div
          v-if="props.labelPosition === 'left'"
          class="ivyforms-form-item__left-label ivyforms-flex ivyforms-align-items-center ivyforms-gap-4"
        >
          <!--Label-->
          <div class="ivyforms-form-item__label ivyforms-gap-2 ivyforms-p-0 medium-14">
            <span>{{ props.label }}</span>
          </div>

          <span
            v-if="props.required"
            class="ivyforms-form-item__asterisk ivyforms-flex-shrink-0 medium-14"
            >*</span
          >

          <!--Tooltip-->
          <IvyTooltip
            v-if="tooltip"
            :content="tooltip"
            raw-content
            placement="top"
            theme="inverted"
            class="ivyforms-form-item__tooltip ivyforms-flex ivyforms-align-items-center ivyforms-flex-shrink-0"
          >
            <IvyIcon
              name="question"
              type="outline"
              size="s"
              outer-size="16px"
              color="var(--map-base-dusk-symbol-2)"
              @click.prevent
            />
          </IvyTooltip>
        </div>

        <!-- For right positioned labels -->
        <div
          v-else-if="props.labelPosition === 'right'"
          class="ivyforms-form-item__right-label medium-14"
        >
          <!--        Label-->
          <div class="ivyforms-form-item__label">
            <span>{{ props.label }}</span>
          </div>
          <span v-if="props.required" class="ivyforms-form-item__asterisk medium-14">*</span>

          <!--        Tooltip-->
          <IvyTooltip
            v-if="tooltip"
            :content="tooltip"
            raw-content
            placement="top"
            theme="inverted"
            class="ivyforms-form-item__tooltip ivyforms-flex ivyforms-align-items-center ivyforms-flex-shrink-0"
          >
            <IvyIcon
              name="question"
              type="outline"
              size="s"
              outer-size="16px"
              color="var(--map-base-dusk-symbol-2)"
              @click.prevent
            />
          </IvyTooltip>
        </div>

        <!-- Default top positioning -->
        <div
          v-else
          class="ivyforms-form-item__left-label ivyforms-flex ivyforms-align-items-center ivyforms-gap-4"
        >
          <!--        Label-->
          <div class="ivyforms-form-item__label ivyforms-gap-2 ivyforms-p-0 medium-14">
            <span>{{ props.label }}</span>
          </div>
          <span
            v-if="props.required"
            class="ivyforms-form-item__asterisk ivyforms-flex-shrink-0 medium-14"
            >*</span
          >

          <!--        Tooltip-->
          <IvyTooltip
            v-if="tooltip"
            :content="tooltip"
            raw-content
            placement="top"
            theme="inverted"
            class="ivyforms-form-item__tooltip"
          >
            <IvyIcon
              name="question"
              type="outline"
              size="s"
              outer-size="16px"
              color="var(--map-base-dusk-symbol-2)"
              @click.prevent
            />
          </IvyTooltip>
        </div>

        <!--        Original Right Label (for additional content) -->
        <div v-if="rightLabel" class="ivyforms-form-item__right-label">
          <!--      TODO This should be a link component with an icon-->
          {{ props.rightLabel }}
        </div>
      </template>
      <template #error="{ error }">
        <slot name="error" />
        <div class="el-form-item__error">
          <IvyIcon
            name="danger"
            size="s"
            type="fill-duo"
            outer-size="16px"
            color="var(--map-status-error-symbol-0)"
          />
          {{ error }}
        </div>
      </template>
      <slot />
      <div
        v-if="showInfo"
        class="ivyforms-form-item__info ivyforms-flex ivyforms-align-items-center regular-14"
        :class="`ivyforms-${props.infoType}`"
      >
        <IvyIcon
          v-if="props.showInfoIcon"
          :name="infoIcon()"
          type="fill-duo"
          size="s"
          :color="infoIconColor()"
          outer-size="16px"
        />
        <div
          class="ivyforms-form-item__info-text"
          :style="{ '--ivyforms-info-color': infoIconColor() }"
        >
          {{ props.infoText }}
        </div>
      </div>
    </ElFormItem>
  </div>
</template>

<script setup lang="ts">
import { ref, provide, nextTick, computed } from 'vue'
import type { Editor } from '@tiptap/core'

interface Props {
  tooltip?: string
  label?: string
  rightLabel?: string
  inputRef?: { focus?: () => void; editor?: Editor; hasContent?: () => boolean }
  showInfo?: boolean
  infoType?: 'default' | 'success' | 'warning' | 'info' | 'error'
  infoText?: string
  staticLabel?: boolean
  warning?: boolean
  secondary?: boolean
  showInfoIcon?: boolean
  labelPosition?: string
  required?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  tooltip: '',
  label: '',
  rightLabel: '',
  inputRef: () => ({}),
  showInfo: false,
  infoType: 'default',
  infoText: '',
  staticLabel: false,
  warning: false,
  secondary: false,
  showInfoIcon: true,
  labelPosition: 'default',
  required: false,
})

// Convert custom label position to Element Plus format
const elementLabelPosition = computed(() => {
  switch (props.labelPosition) {
    case 'left':
      return 'left'
    case 'right':
      return 'right'
    case 'top':
      return 'top'
    default:
      return 'top' // Changed default to 'top'
  }
})

const formItem = ref<HTMLElement | null>(null)
const hasValue = ref(false)
const isFormItemActive = ref(false)
const isContentHovered = ref(false)
const isFormItemDisabled = ref(false)

const getFormItemType = () => {
  if (formItem.value?.querySelector('.el-date-editor')) {
    return 'datepicker'
  } else if (formItem.value?.querySelector('.el-select')) {
    return 'select'
  } else if (formItem.value?.querySelector('.ivyforms-phone-input')) {
    return 'phone'
  } else if (formItem.value?.querySelector('.el-textarea')) {
    return 'textarea'
  } else if (formItem.value?.querySelector('.el-input')) {
    return 'input'
  } else if (formItem.value?.querySelector('.ivyforms-editor')) {
    return 'editor'
  } else {
    return ''
  }
}

const getFormItemTypeClass = () => {
  return `ivyforms-form-item-${getFormItemType()}`
}

const infoIcon = () => {
  switch (props.infoType) {
    case 'success':
      return 'check-circle'
    default:
      return 'info'
  }
}

const infoIconColor = () => {
  switch (props.infoType) {
    case 'success':
      return 'var(--map-status-success-symbol-0)'
    case 'warning':
      return 'var(--map-status-warning-symbol-0)'
    case 'error':
      return 'var(--map-status-error-symbol-0)'
    case 'info':
      return 'var(--map-base-text-0)'
    default:
      return 'var(--map-base-text-0)'
  }
}

const updateHasValue = () => {
  nextTick(() => {
    const input = formItem.value?.querySelector('.el-input__inner') as HTMLInputElement | null
    const textarea = formItem.value?.querySelector('.el-textarea__inner') as HTMLInputElement | null
    const select = formItem.value?.querySelector('.el-select') as HTMLElement | null

    if (input) {
      hasValue.value = input.value !== ''
    } else if (textarea) {
      hasValue.value = textarea.value !== ''
    } else if (select) {
      checkSelectHasValue()
    }
  })
}

const checkSelectHasValue = () => {
  const parent = formItem.value?.querySelector(
    '.ivyforms-select, .ivyforms-time-select, .ivyforms-multi-select',
  )

  if (parent) {
    hasValue.value = parent.classList.contains('has-value')
  }
}
defineExpose({
  updateHasValue,
})
provide('isSecondary', props.secondary)
</script>

<style lang="scss">
//Form Item
.ivyforms-form-item {
  position: relative;

  &__asterisk {
    color: var(--map-status-error-fill-0);
  }

  // Element Form Item
  .el-form-item {
    margin-bottom: 0; // Default margin
    // When labelPosition is 'left' - use left label class for positioning
    &[class*='el-form-item--label-left'] {
      flex-direction: row;
      align-items: flex-start;

      .el-form-item__label {
        flex-shrink: 0;
        margin-right: 16px; // Add spacing between label and input
        margin-bottom: 0; // Remove bottom margin for side positioning
        min-width: 50px; // Ensure label has minimum width

        // Style the left label when positioned on the left
        .ivyforms-form-item__left-label {
          align-items: center;
          display: flex;
          gap: 4px;
        }
      }

      .el-form-item__content {
        flex: 1;
      }
    }

    // When labelPosition is 'right' - use right label class for positioning
    &[class*='el-form-item--label-right'] {
      flex-direction: row;
      align-items: flex-start;

      .el-form-item__label {
        flex-shrink: 0;
        margin-left: 16px; // Add spacing for right-positioned labels
        margin-right: 0;
        margin-bottom: 0;
        min-width: 50px;
        order: 2; // Move label to the right side

        // Style the right label when positioned on the right
        .ivyforms-form-item__right-label {
          align-items: center;
          display: flex;
          gap: 4px;
          // Override the underline styling for positional right labels
          color: var(--map-base-text-0);
          text-decoration: none;
          margin-left: 0;
        }
      }

      .el-form-item__content {
        flex: 1;
        order: 1; // Move content to the left side
      }
    }

    // Remove margin when info message is present using class selector
    &.has-info-message {
      margin-bottom: 0;
    }

    // Content
    .el-form-item__content {
      display: flex;
      flex-direction: column;
      gap: var(--Spacing-xs, 6px);
      align-items: flex-start;
      justify-content: flex-start;
      text-align: left;

      // Error message
      .el-form-item__error {
        position: relative;
        display: flex;
        align-items: center;
        padding: 0 var(--Spacing-4xs, 1px);
        gap: var(--Spacing-2xs, 4px);
        align-self: stretch;
        font-size: 14px;
        font-style: normal;
        font-weight: 400;
        line-height: 20px;

        .ivyforms-icon {
          .ivyforms-icon__svg {
            width: auto;
          }
        }
      }
      // Force left alignment for radio groups
      > .ivyforms-radio-group {
        justify-content: flex-start !important;
        margin-left: 0 !important;
      }
    }

    // Label
    .el-form-item__label {
      display: flex;
      align-items: center;
      justify-content: flex-start;
      padding: 0;
      margin-bottom: 6px;
      height: auto;

      // Mandatory astrix
      &::before {
        display: none !important;
      }

      // Left label
      .ivyforms-form-item__left-label {
        // Label
        .ivyforms-form-item__label {
          cursor: text;
          background-color: transparent;
          color: var(--map-base-text-0);
          order: 0;
          height: auto;
        }

        // Asterisk
        .ivyforms-form-item__asterisk {
          order: 1;
        }

        // Tooltip
        .ivyforms-form-item__tooltip {
          order: 2;

          .el-tooltip__trigger {
            height: 20px;
            display: flex;
            align-items: center;
          }
        }
      }

      // Right label
      .ivyforms-form-item__right-label {
        order: 3;
        margin-left: auto;
        color: var(--map-base-dusk-symbol-2);
        text-align: center;
        text-decoration-line: none;
        text-decoration-style: solid;
        text-decoration-skip-ink: auto;
        text-decoration-color: var(--map-base-dusk-symbol-2);
        text-decoration-thickness: 10%;
        text-underline-offset: 15.5%;
        text-underline-position: from-font;
      }
    }

    // Icons
    .ivyforms-icon:not(.ivyforms-input__icon-end):not(.ivyforms-input__icon-start):not(
        .ivyforms-input__clear
      ):not(.selector__country-flag):not(.ivyforms-button-option__icon-start) {
      display: contents;
    }

    // Disabled
    &.is-disabled {
      opacity: 0.5;
      cursor: not-allowed;

      // Label
      .el-form-item__label {
        cursor: not-allowed !important;
      }
    }

    // Filled
    &.is-filled {
      .el-select__wrapper {
        border: 1px solid var(--map-base-dusk-stroke--2);
        background: var(--map-base-dusk-o05);

        // Hover
        &:hover {
          border: 1px solid var(--map-base-dusk-stroke-0);
          background: var(--map-base-dusk-o05);
        }

        // Active & Focus
        &:active,
        &.is-active,
        &:focus,
        &.is-focus,
        &:focus-within {
          border: 1px solid var(--map-base-brand-stroke-0);
          background: var(--map-base-dusk-o05);
        }
      }
    }

    // Hint info message
    .ivyforms-form-item__info {
      align-self: stretch;
      font-style: normal;
      margin: 0;

      .ivyforms-form-item__info-text {
        color: var(--ivyforms-info-color);
        padding: 0 var(--Spacing-4xs, 1px);
        word-break: break-word;
      }
    }
  }

  .el-form-item.is-error,
  .ivyforms-form-item__info {
    svg {
      min-width: 24px;
    }
  }
}
</style>
