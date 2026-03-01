<template>
  <div
    class="ivyforms-toggle"
    :class="[
      `text-position-${props.textPosition}`,
      `size-${props.size}`,
      `priority-${props.priority}`,
      `type-${props.type}`,
      { 'is-disabled': props.disabled },
    ]"
  >
    <ElSwitch
      v-model="localModelValue"
      v-bind="{ ...$props, ...$attrs, size: '' }"
      :inline-prompt="props.type === 'stroke-with-text'"
      :active-text="computedActiveText"
      :inactive-text="computedInactiveText"
      :aria-label="props.ariaLabel || getLabel('toggle_switch')"
      @change="(eventValue: boolean | string | number) => $emit('change', eventValue)"
    />
    <div
      v-if="props.title || props.tooltip || props.description"
      class="ivyforms-toogle__text-block"
    >
      <div
        v-if="props.title || props.tooltip"
        class="ivyforms-toggle__text-block__title ivyforms-flex ivyforms-align-items-center"
      >
        <div v-if="props.title" class="regular-16">{{ props.title }}</div>
        <IvyTooltip
          v-if="props.tooltip"
          :content="props.tooltip"
          raw-content
          placement="top"
          theme="inverted"
          :aria-label="props.title"
        >
          <IvyIcon
            name="question"
            type="outline"
            size="s"
            color="var(--map-base-dusk-symbol-2)"
            @click.prevent
          />
        </IvyTooltip>
      </div>

      <div v-if="props.description" class="ivyforms-toogle__text-block__description">
        {{ props.description }}
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useLabels } from '@/composables/useLabels'
const { getLabel } = useLabels()

interface Props {
  modelValue?: boolean | string | number
  label?: string
  disabled?: boolean
  size?: 's' | 'd' | 'l' | 'xl'
  priority?: 'primary' | 'secondary' | 'amber'
  type?: 'fill' | 'stroke' | 'stroke-with-text'
  textPosition?: 'left' | 'right'
  title?: string
  description?: string
  tooltip?: string
  ariaLabel?: string
}

const props = withDefaults(defineProps<Props>(), {
  disabled: false,
  size: 'd',
  priority: 'primary',
  type: 'fill',
  label: '',
  modelValue: '',
  textPosition: 'right',
  title: '',
  description: '',
  tooltip: '',
  ariaLabel: '',
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

const computedActiveText = computed(() =>
  props.type === 'stroke-with-text' ? (props.label ? props.label : getLabel('on')) : '',
)
const computedInactiveText = computed(() =>
  props.type === 'stroke-with-text' ? (props.label ? props.label : getLabel('off')) : '',
)
</script>

<style lang="scss">
@use 'sass:list' as *;

.ivyforms-toggle {
  display: flex;
  align-items: center;
  gap: 12px;
  min-height: 24px;
  border-radius: 49%;

  &.text-position-left {
    flex-direction: row-reverse;
  }
  // Text Block
  &__text-block {
    // Title
    &__title {
      height: 24px;
      color: var(--map-base-text-0);
    }

    // Switch Description
    & &__description {
      color: var(--map-base-text--1);
    }
  }

  .el-switch__input {
    background-color: transparent;
    box-shadow: none;
    color: transparent;
    border: none;
  }
  .el-switch__inner {
    align-items: flex-start;
    justify-content: center;
    .is-text {
      font-weight: 400;
      font-size: 12px;
      line-height: 16px;
      text-align: center;
      position: absolute;
      left: 50%;
    }
  }

  &.is-disabled {
    opacity: 0.5;
    pointer-events: none;
  }

  .el-switch {
    display: flex;

    &__core {
      background-color: var(--map-base-dusk-o05);
      border-color: var(--map-base-dusk-fill--2);
      border-radius: 49px;
      min-width: 34px;

      .el-switch__action {
        background-color: var(--map-base-dusk-fill-1);
        box-shadow: 0 0 5px 0 rgba(0, 0, 0, 0.04);
      }

      &:not(.is-checked):hover .el-switch__action {
        background-color: var(--map-base-dusk-fill-2);
      }

      .el-switch__inner {
        .is-text {
          color: var(--map-base-text--1);
        }
      }
    }
  }

  &__label {
    font-size: 14px;
    font-weight: 500;
  }

  $priorities: (
    'primary': (
      var(--map-base-brand-fill-0),
      var(--map-base-brand-fill-1),
    ),
    'secondary': (
      var(--map-base-purple-fill-0),
      var(--map-base-purple-fill-1),
    ),
    'amber': (
      var(--map-accent-amber-fill-0),
      var(--map-accent-amber-fill-1),
    ),
  );

  // Process each priority value if is array
  @each $priority, $values in $priorities {
    &.priority-#{$priority} {
      .el-switch {
        &.is-checked {
          .el-switch__core {
            background-color: nth($values, 1);
            border-color: nth($values, 1);

            .el-switch__action {
              background-color: $primitive-white;
            }

            .el-switch__inner .is-text {
              color: $primitive-white;
              left: 20%;
            }
          }

          // Hover state when checked
          &:hover .el-switch__core {
            background-color: nth($values, 2);
          }
        }
      }
    }
  }

  // Define sizes in a map
  $sizes: (
    'xl': (
      70px,
      36px,
      32px,
      28px,
    ),
    'l': (
      64px,
      32px,
      28px,
      24px,
    ),
    'd': (
      46px,
      24px,
      20px,
      16px,
    ),
    's': (
      34px,
      16px,
      14px,
      12px,
    ),
  );

  // Process each size value if is array
  @each $size, $values in $sizes {
    &.size-#{$size} {
      .el-switch {
        .el-switch__core {
          height: nth($values, 2);
          width: nth($values, 1);

          .el-switch__action {
            height: nth($values, 3);
            width: nth($values, 3);
            left: calc(100% - nth($values, 3));
          }
        }

        &:not(.is-checked) .el-switch__action {
          left: 1px;
        }
      }
      &.type-stroke-with-text {
        .el-switch {
          .el-switch__core {
            .el-switch__action {
              height: nth($values, 4);
              width: nth($values, 4);
            }
          }
        }
      }
      &.type-stroke {
        .el-switch {
          .el-switch__core {
            height: nth($values, 4);
            width: nth($values, 1);

            .el-switch__action {
              height: nth($values, 2);
              width: nth($values, 2);
              left: calc(100% - nth($values, 2) + 3px);
            }
          }

          &:not(.is-checked) .el-switch__action {
            left: -2px;
          }
        }
      }
    }
  }
}
</style>
