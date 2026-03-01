<template>
  <ElCheckbox
    v-if="$slots.default"
    v-model="localModelValue"
    class="ivyforms-checkbox"
    :class="[
      `size-${props.size}`,
      `priority-${props.priority}`,
      `type-${props.type}`,
      props.labelPosition === 'left' ? 'label-left' : 'label-right',
      { 'with-description': props.description },
      {
        'is-checked': localModelValue,
        'is-indeterminate': props.indeterminate,
        'is-disabled': props.disabled,
        'with-marker': props.type === 'withMarker',
      },
    ]"
    :disabled="props.disabled"
    :aria-label="props.ariaLabel || getLabel('checkbox')"
    @change="(eventValue) => $emit('change', eventValue)"
  >
    <span class="ivyforms-checkbox__label-with-tooltip">
      <slot />
      <IvyTooltip v-if="props.tooltip" :content="props.tooltip" raw-content placement="top">
        <IvyIcon
          name="question"
          type="outline"
          size="s"
          color="var(--map-base-dusk-symbol-2)"
          class="ivyforms-checkbox__tooltip-icon"
          @click.prevent
        />
      </IvyTooltip>
    </span>

    <span v-if="props.description" class="ivyforms-checkbox__description">
      {{ props.description }}
    </span>
  </ElCheckbox>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useLabels } from '@/composables/useLabels'
const { getLabel } = useLabels()
interface Props {
  modelValue?: boolean
  indeterminate?: boolean
  labelPosition?: 'right' | 'left'
  description?: string
  priority?: 'primary' | 'secondary' | 'amber'
  size?: 's' | 'd' | 'l' | 'xl'
  type?: 'checkmark' | 'withMarker'
  disabled?: boolean
  ariaLabel?: string
  tooltip?: string
}

const props = withDefaults(defineProps<Props>(), {
  labelPosition: 'right',
  priority: 'primary',
  size: 'd',
  type: 'withMarker',
  disabled: false,
  indeterminate: false,
  description: '',
  ariaLabel: '',
  tooltip: '',
})

const emit = defineEmits(['update:modelValue', 'change'])

const localModelValue = computed({
  get() {
    return props.modelValue
  },
  set(value) {
    emit('update:modelValue', value)
  },
})
</script>

<style lang="scss">
@use 'sass:list' as *;
@use 'sass:meta' as *;
$el: '.el-checkbox';

// Checkbox
.ivyforms-checkbox {
  display: flex;
  flex-direction: row;
  gap: 12px;
  margin-right: 0;
  height: unset;
  min-height: 24px;
  white-space: normal;

  // Override Element UI styles for disabled checkbox in dark theme
  .el-checkbox__input.is-disabled .el-checkbox__inner {
    background-color: var(--map-base-dusk-o05);
    border-color: var(--map-base-dusk-stroke--2);
    cursor: not-allowed;
  }

  &.show-all-columns {
    border-bottom: 1px solid var(--map-divider);
    padding-bottom: 6px;
    margin-top: 0 !important;
  }

  &.el-checkbox {
    white-space: normal;
    display: flex;
    align-items: center;
    gap: 12px;
    margin-right: 0;
    -webkit-tap-highlight-color: transparent;
    min-height: 24px;
    height: unset;
    color: var(--map-base-text-0);
    &.is-checked {
      color: var(--map-base-text-0);
      .el-checkbox__label {
        color: var(--map-base-text-0);
      }
      &.is-indeterminate {
        .el-checkbox__inner {
          background-color: var(--map-base-dusk-o05);
          border-color: var(--map-base-dusk-stroke--2);
          &::after {
            content: '';
            width: 10px;
            height: 2px;
            background-color: white;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
          }
        }
      }
    }
  }
  .el-checkbox__input {
    margin-right: 0;
    padding: 0;

    .el-checkbox__inner {
      background-color: var(--map-base-dusk-o05);
      border-color: var(--map-base-dusk-stroke--2);
      border-radius: 4px;
    }

    &:disabled {
      opacity: 0.5;
    }

    &.is-checked {
      color: var(--map-base-text-0);
      .el-checkbox__inner {
        background-color: var(--map-base-dusk-o05);
        border-color: var(--map-base-dusk-stroke--2);
        &::after {
          content: '';
          display: inline-block;
          position: absolute;
          left: 50%;
          top: 50%;
          transform: translate(-50%, -50%);
          width: 100%;
          height: 100%;
        }
      }
      .el-checkbox__label {
        color: var(--map-base-text-0);
      }
    }
    &.is-checked {
      &:disabled {
        opacity: 0.5;
      }
    }
  }
  .el-checkbox__label {
    padding: 0;
    font-size: 16px;
    font-weight: 400;
    display: flex;
    line-height: 22px;
    flex-direction: column;
    color: var(--map-base-text-0);
  }

  &.label-left {
    .el-checkbox__input {
      order: 2;
    }
    .el-checkbox__label {
      order: 1;
    }
  }

  &.with-description {
    align-items: flex-start;
  }

  &__description {
    font-size: 14px;
    font-weight: 400;
    line-height: 20px;
    color: var(--map-base-text--1);
  }

  // Sizes
  $sizes: (
    'xl': (
      32px,
      24px,
      16px,
    ),
    'l': (
      24px,
      16px,
      12px,
    ),
    'd': (
      20px,
      16px,
      12px,
    ),
    's': (
      16px,
      12px,
      8px,
    ),
  );

  @each $size, $values in $sizes {
    &.size-#{$size} {
      .el-checkbox__inner {
        height: nth($values, 1);
        width: nth($values, 1);
        &::after {
          height: nth($values, 2);
          width: nth($values, 2);
        }
      }
      &.type-withMarker {
        .el-checkbox__inner {
          &::after {
            height: nth($values, 3);
            width: nth($values, 3);
          }
        }
      }
    }
  }

  // Priorities
  $priorities: (
    'primary': (
      var(--map-base-brand-fill-0),
      var(--map-base-brand-fill-1),
      var(--map-base-brand-o05),
      var(--map-base-brand-stroke-0),
    ),
    'secondary': (
      var(--map-base-purple-fill-0),
      var(--map-base-purple-fill-1),
      var(--map-base-purple-o05),
      var(--map-base-purple-stroke-0),
    ),
    'amber': (
      var(--map-accent-amber-fill-0),
      var(--map-accent-amber-fill-1),
      var(--map-accent-amber-o05),
      var(--map-accent-amber-stroke-0),
    ),
  );

  @each $priority, $values in $priorities {
    &.priority-#{$priority} {
      &.is-checked {
        &.type-checkmark {
          .el-checkbox__inner {
            background-color: nth($values, 1);
            border-color: nth($values, 1);
          }
          &:hover:not(.is-disabled) {
            .el-checkbox__inner {
              &::after {
                background-color: nth($values, 2);
                border-color: nth($values, 2);
              }
            }
          }
        }

        .el-checkbox__inner {
          &::after {
            background-color: nth($values, 1);
            border-color: nth($values, 1);
          }
        }

        &:hover:not(.is-disabled) {
          .el-checkbox__inner {
            &::after {
              background-color: nth($values, 2);
              border-color: nth($values, 2);
            }
          }
        }
        &.is-indeterminate {
          .el-checkbox__inner {
            background-color: nth($values, 1);
            border-color: nth($values, 1);
          }
          &:hover:not(.is-disabled) {
            .el-checkbox__inner {
              background-color: nth($values, 2);
              border-color: nth($values, 2);
              &::after {
                background-color: white;
              }
            }
          }
        }
      }

      &:not(.is-checked) {
        &:hover:not(.is-disabled) {
          .el-checkbox__inner {
            background-color: nth($values, 3);
            border-color: nth($values, 4);
          }
        }
      }
    }
  }

  &.type-checkmark {
    &.is-checked {
      .el-checkbox__inner {
        &::after {
          content: '';
          display: inline-block;
          background-image: url('@/assets/images/radioButton/radio-button-checkmark.svg');
          background-size: contain;
          background-repeat: no-repeat;
          position: absolute;
          left: 50%;
          top: 50%;
          transform: translate(-50%, -50%);
          border-radius: 4px;
        }
      }
    }
  }
  &__label-with-tooltip {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
  }

  &__tooltip-icon {
    cursor: pointer;
    flex-shrink: 0;
  }
}
</style>
