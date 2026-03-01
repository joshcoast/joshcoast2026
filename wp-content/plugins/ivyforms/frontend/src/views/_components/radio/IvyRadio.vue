<template>
  <ElRadio
    v-model="localModelValue"
    class="ivyforms-radio"
    :class="[
      `size-${props.size}`,
      `type-${props.type}`,
      `priority-${props.priority}`,
      props.labelPosition === 'left' ? 'label-left' : 'label-right',
      { 'with-description': props.description },
      { 'is-readonly': props.readonly },
    ]"
    v-bind="{ ...$props, ...$attrs, size: '', label: props.label }"
    @change="(eventValue: string | number | boolean) => $emit('change', eventValue)"
  >
    <span class="ivyforms-radio__label-with-tooltip">
      <slot>{{ props.label }}</slot>
      <IvyTooltip v-if="props.tooltip" :content="props.tooltip" raw-content placement="top">
        <IvyIcon
          name="question"
          type="outline"
          size="s"
          color="var(--map-base-dusk-symbol-2)"
          class="ivyforms-radio__tooltip-icon"
          @click.prevent
        />
      </IvyTooltip>
    </span>

    <span v-if="props.description" class="ivyforms-radio__description">
      {{ props.description }}
    </span>
  </ElRadio>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  modelValue?: string | number | boolean
  labelPosition?: 'right' | 'left'
  description?: string
  priority?: 'primary' | 'secondary' | 'amber'
  size?: 's' | 'd' | 'l' | 'xl'
  type?: 'checkmark' | 'withMarker'
  tooltip?: string
  label?: string
  readonly?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  labelPosition: 'right',
  priority: 'primary',
  size: 'd',
  type: 'withMarker',
  description: '',
  modelValue: '',
  tooltip: '',
  label: '',
  readonly: false,
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
// Radio
.ivyforms-radio {
  // Element Radio
  &.el-radio {
    display: flex;
    flex-direction: row;
    gap: 12px;
    margin-right: 0;
    height: unset;
    min-height: 24px;
    white-space: normal;
    &.is-checked {
      .el-radio__label {
        color: var(--map-base-text-0);
      }
    }
    // Input
    .el-radio__input {
      margin-right: 0;
      padding: 4px 0;

      // Inner
      .el-radio__inner {
        background-color: var(--map-base-dusk-o05);
        border-color: var(--map-base-dusk-stroke--2);
        @include transition();
      }
      &:disabled {
        opacity: 0.5;
      }

      // Checked
      &.is-checked {
        &:disabled {
          opacity: 0.5;
        }
      }
    }

    // Label
    .el-radio__label {
      padding: 0;
      font-size: 16px;
      font-weight: 400;
      display: flex;
      line-height: 22px;
      flex-direction: column;
      color: var(--map-base-text-0);
    }

    // Label Position
    &.label-left {
      // Input
      .el-radio__input {
        order: 2;
      }

      // Label
      .el-radio__label {
        order: 1;
      }
    }

    // Readonly state - remove cursor pointer
    &.is-readonly {
      cursor: default !important;

      .el-radio__input,
      .el-radio__inner,
      .el-radio__label {
        cursor: default;
      }
    }
  }

  // With Description
  &.with-description {
    align-items: flex-start;
  }

  // Description
  &__description {
    font-size: 14px;
    font-weight: 400;
    line-height: 20px;
    color: var(--map-base-text--1);
  }

  // Define sizes in a map
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
  // Function to process each size value if is array
  @each $size, $values in $sizes {
    &.size-#{$size} {
      .el-radio__inner {
        height: nth($values, 1);
        width: nth($values, 1);
        &::after {
          height: nth($values, 2);
          width: nth($values, 2);
        }
      }
      &.type-withMarker {
        .el-radio__inner {
          &::after {
            height: nth($values, 3);
            width: nth($values, 3);
          }
        }
      }
    }
  }

  // Define priorities in a map
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

  // Function to process each priority value if is array
  @each $priority, $values in $priorities {
    &.priority-#{$priority} {
      &.is-checked {
        &.type-checkmark {
          .el-radio__inner {
            background-color: nth($values, 1);
            border-color: nth($values, 1);
          }

          &:hover:not(.is-disabled):not(.is-readonly) {
            // Inner
            .el-radio__inner {
              background-color: nth($values, 2);
              border-color: nth($values, 2);
            }
          }
        }
        .el-radio__inner {
          &::after {
            background-color: nth($values, 1);
            border-color: nth($values, 1);
          }
        }

        &:hover:not(.is-disabled):not(.is-readonly) {
          // Inner
          .el-radio__inner {
            &::after {
              background-color: nth($values, 2);
              border-color: nth($values, 2);
            }
          }
        }
      }
      &:not(.is-checked) {
        // Hover
        &:hover:not(.is-disabled):not(.is-readonly) {
          // Inner
          .el-radio__inner {
            background-color: nth($values, 3);
            border-color: nth($values, 4);
          }
        }
      }
    }
  }
  // Type with checkmark
  &.type-checkmark {
    &.is-checked {
      .el-radio__inner {
        &::after {
          content: '';
          display: inline-block;
          background-image: url('@/assets/images/radioButton/radio-button-checkmark.svg');
          background-size: contain;
          background-repeat: no-repeat;
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
