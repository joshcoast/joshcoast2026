<template>
  <button
    class="ivyforms-button-option"
    :disabled="props.disabled || props.loading"
    :class="[
      'ivyforms-button-option__priority__' + props.priority,
      'ivyforms-button-option__type__' + props.type,
      'ivyforms-button-option__size__' + props.size,
      {
        'is-active': props.active,
        'is-loading': props.loading,
        'is-icon-start': props.iconStart && $slots.default && !props.iconOnly,
        'is-icon-end': props.iconEnd && $slots.default && !props.iconOnly,
        'is-icon-only': ((props.iconStart || props.iconEnd) && !$slots.default) || props.iconOnly,
      },
    ]"
    :aria-label="props.ariaLabel || getLabel('option_button')"
    :style="{ width: props.fullWidth ? '100%' : undefined }"
    @click="handleClick"
    @mousedown="handleMouseDown"
  >
    <span v-if="props.loading" class="ivyforms-button-option__spinner-container">
      <svg viewBox="0 0 50 50" class="ivyforms-button-option__spinner">
        <circle class="ivyforms-button-option__spinner__line" cx="25" cy="25" r="22.5" />
      </svg>
    </span>
    <IvyIcon
      v-if="props.iconStart && !props.loading"
      :name="props.iconStart"
      :category="props.iconStartCategory"
      :type="props.iconStartType"
      :size="props.size"
      class="ivyforms-button-option__icon-start"
    />
    <span v-if="$slots.default && !props.iconOnly">
      <slot />
    </span>
    <IvyIcon
      v-if="props.iconEnd"
      :name="props.iconEnd"
      :category="props.iconEndCategory"
      :type="props.iconEndType"
      :size="props.size"
      class="ivyforms-button-option__icon-end"
    />
    <svg class="ivyforms-button-option__transition" width="100%" height="100%" fill="none">
      <g>
        <rect width="100%" height="100%" />
      </g>
    </svg>
  </button>
</template>

<script setup lang="ts">
import type { IconCategory } from '@/types/icons/icon-category'
import type { IconType } from '@/types/icons/icon-type'
import { useLabels } from '@/composables/useLabels'
const { getLabel } = useLabels()

interface Props {
  priority?:
    | 'primary'
    | 'secondary'
    | 'tertiary'
    | 'success'
    | 'warning'
    | 'danger'
    | 'white'
    | 'shadow-white'
  type?: 'fill' | 'border' | 'ghost'
  size?: 'l' | 'd' | 's' | 'xs'
  disabled?: boolean
  loading?: boolean
  active?: boolean
  iconStart?: string
  iconEnd?: string
  iconStartCategory?: IconCategory
  iconEndCategory?: IconCategory
  iconStartType?: IconType
  iconEndType?: IconType
  fullWidth?: boolean
  iconOnly?: boolean
  ariaLabel?: string
}

const props = withDefaults(defineProps<Props>(), {
  priority: 'primary',
  type: 'fill',
  size: 'd',
  disabled: false,
  loading: false,
  active: false,
  iconStart: '', // Default value for iconStart
  iconEnd: '', // Default value for iconEnd
  iconStartCategory: 'global',
  iconEndCategory: 'global',
  iconStartType: 'fill',
  iconEndType: 'fill',
  fullWidth: false,
  iconOnly: false,
  ariaLabel: '',
})

const handleClick = () => {
  ;(document?.activeElement as HTMLElement).blur()
}

const handleMouseDown = (event: Event) => {
  event.preventDefault()
}
</script>

<style lang="scss">
@use 'sass:list' as *;
@use 'sass:meta' as *;

$icon-start: '.ivyforms-button-option__icon-start';
$icon-end: '.ivyforms-button-option__icon-end';
$transition: '.ivyforms-button-option__transition';
$type-fill: '.ivyforms-button-option__type__fill';
$type-border: '.ivyforms-button-option__type__border';
$type-ghost: '.ivyforms-button-option__type__ghost';

// Button Option
.ivyforms-button-option {
  position: relative;
  border-radius: 8px;
  display: inline-flex;
  align-items: center;
  white-space: nowrap;
  justify-content: center;
  flex-shrink: 0;
  -webkit-tap-highlight-color: transparent;
  user-select: none;
  outline: none;
  border: none;
  cursor: pointer;
  font-weight: 500;
  max-width: 100%;

  // Everything except transition
  > *:not(#{$transition}) {
    z-index: 1;
  }

  // Transition
  & #{$transition} {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border-radius: 8px;
    fill: transparent;
    opacity: 0;
  }

  // Hover
  @media (hover: hover) and (pointer: fine) {
    &:hover:not(:focus):not(:disabled):not(:active) {
      // Transition
      & #{$transition} {
        opacity: 0.05;
      }

      // Ghost and Border types for all priorities except secondary
      &:not(.ivyforms-button-option__priority__secondary) {
        &#{$type-ghost},
        &#{$type-border} {
          & #{$transition} {
            opacity: 0.08;
          }
        }
      }
    }
  }

  // Active
  &.is-active:not(:disabled),
  &:active:not(:disabled),
  &[aria-expanded='true'] {
    // Transition
    & #{$transition} {
      opacity: 0.16;
      fill: var(--map-ground-level-2-background);
    }
  }

  &.is-active {
    .ivyforms-icon {
      svg {
        fill: var(--map-base-purple-symbol-0);
        stroke: var(--map-base-purple-symbol-0);
      }
    }
  }

  // Focus
  &:focus:not(:active) {
    // Transition
    & #{$transition} {
      opacity: 0.4;
    }
  }

  // Disabled & Loading
  &:disabled,
  &.is-loading {
    opacity: 0.5;
  }

  // Priorities
  $priorities: (
    'primary': (
      var(--map-base-brand-fill-0),
      var(--primitive-white),
      var(--map-base-brand-stroke-0),
      var(--map-base-brand-fill-4),
      var(--map-base-brand-fill-0),
      var(--map-base-brand-symbol-0),
    ),
    'secondary': (
      var(--map-base-purple-fill-0),
      var(--primitive-white),
      var(--map-base-purple-stroke-0),
      var(--map-base-purple-fill-4),
      var(--map-base-purple-fill-0),
      var(--map-base-purple-symbol-0),
    ),
    'tertiary': (
      var(--map-base-dusk-fill--4),
      var(--map-base-dusk-symbol-2),
      var(--map-base-dusk-stroke-0),
      var(--map-base-dusk-fill-0),
      var(--map-base-dusk-fill-4),
      var(--map-base-dusk-symbol-2),
    ),
    'success': (
      var(--map-status-success-fill-0),
      var(--primitive-white),
      var(--map-status-success-stroke-0),
      var(--map-status-success-fill-4),
      var(--map-status-success-fill-0),
      var(--map-status-success-symbol-0),
    ),
    'warning': (
      var(--map-status-warning-fill-0),
      var(--primitive-white),
      var(--map-status-warning-stroke-0),
      var(--map-status-warning-fill-4),
      var(--map-status-warning-fill-0),
      var(--map-status-warning-symbol-0),
    ),
    'danger': (
      var(--map-status-error-fill-0),
      var(--primitive-white),
      var(--map-status-error-stroke-0),
      var(--map-status-error-fill-4),
      var(--map-status-error-fill-0),
      var(--map-status-error-symbol-0),
    ),
    'white': (
      var(--primitive-white),
      var(--primitive-black),
      var(--primitive-white),
      var(--primitive-white),
      var(--map-bw-o90),
      var(--map-bw),
    ),
    'shadow-white': (
      var(--map-ground-level-2-background),
      var(--map-base-dusk-symbol-2),
      var(--primitive-white),
      var(--primitive-white),
      var(--primitive-white),
      var(--map-base-dusk-symbol-2),
    ),
  );

  // Priority Styles
  @mixin priorityStyles($priority, $colors: ()) {
    $fill-color: nth($colors, 1);
    $text-color: nth($colors, 2);
    $border-focus-color: nth($colors, 3);
    $hover-fill-fill: nth($colors, 4);
    $hover-fill-border-ghost: null;
    @if length($colors) >= 5 {
      $hover-fill-border-ghost: nth($colors, 5);
    } @else {
      $hover-fill-border-ghost: nth($colors, 4);
    }
    $text-color-border-ghost: null;
    @if length($colors) >= 6 {
      $text-color-border-ghost: nth($colors, 6);
    } @else {
      $text-color-border-ghost: nth($colors, 2);
    }

    // Priority
    &__priority__#{$priority} {
      color: $text-color;
      --spinner-line-color: #{$text-color};
      @if $priority == 'shadow-white' {
        box-shadow: 0px 4px 15px 0px rgba(0, 0, 0, 0.05);
      }

      // Icon
      #{$icon-start},
      #{$icon-end} {
        fill: $text-color;
        stroke: $text-color;
      }

      // Focus
      &:focus:not(:active) {
        & .ivyforms-button-option__transition {
          box-shadow: 0 0 0 4px $border-focus-color;
        }
      }

      // Fill
      &.ivyforms-button-option__type__fill {
        background-color: $fill-color;

        // Hover
        @media (hover: hover) and (pointer: fine) {
          &:hover:not(:focus):not(:disabled):not(:active) {
            & .ivyforms-button-option__transition {
              fill: $hover-fill-fill;
            }
          }
        }
      }

      // Border & Ghost
      &.ivyforms-button-option__type__border,
      &.ivyforms-button-option__type__ghost {
        color: $text-color-border-ghost;
        --spinner-line-color: #{$text-color-border-ghost};

        @if $priority == 'shadow-white' {
          box-shadow: none;
        }

        // Hover
        @media (hover: hover) and (pointer: fine) {
          &:hover:not(:focus):not(:disabled):not(:active) {
            & .ivyforms-button-option__transition {
              fill: $hover-fill-border-ghost;
            }
          }
        }

        // Primary
        &.ivyforms-button-option__priority__primary {
          // Icon
          #{$icon-start},
          #{$icon-end} {
            fill: var(--map-base-brand-symbol-0);
          }
        }

        // Secondary
        &.ivyforms-button-option__priority__secondary {
          // Icon
          #{$icon-start},
          #{$icon-end} {
            fill: var(--map-base-purple-symbol-0);
          }
        }
        // Success
        &.ivyforms-button-option__priority__success {
          // Icon
          #{$icon-start},
          #{$icon-end} {
            fill: var(--map-base-brand-symbol-0);
          }
        }
        // Warning
        &.ivyforms-button-option__priority__warning {
          // Icon
          #{$icon-start},
          #{$icon-end} {
            fill: var(--map-status-warning-symbol-0);
          }
        }
        // Danger
        &.ivyforms-button-option__priority__danger {
          // Icon
          #{$icon-start},
          #{$icon-end} {
            fill: var(--map-status-error-symbol-0);
          }
        }
        // White
        &.ivyforms-button-option__priority__white {
          color: var(--primitive-white);
          // Icon
          #{$icon-start},
          #{$icon-end} {
            fill: var(--map-base-dusk-symbol-2);
          }
        }
        // Shadow White
        &.ivyforms-button-option__priority__shadow-white {
          color: var(--primitive-white);
          // Icon
          #{$icon-start},
          #{$icon-end} {
            fill: var(--primitive-white);
          }
        }

        // Border
        &.ivyforms-button-option__type__border {
          border: 1px solid $border-focus-color;

          // Focus
          &:focus:not(:active) {
            // Transition
            & #{$transition} {
              box-shadow: 0 0 0 5px $border-focus-color;
            }
          }
        }
      }
    }
  }

  @each $priority, $colors in $priorities {
    @include priorityStyles($priority, $colors);
  }

  // Type
  &__type {
    // Border
    &__border {
      background-color: transparent;
    }

    // Ghost
    &__ghost {
      background-color: transparent;
    }
  }

  // Define sizes in a map
  $sizes: (
    'l': (
      48px,
      16px,
      normal,
      (10px, 24px, 10px, 24px),
      // padding
      (10px, 24px, 10px, 18px),
      // padding with icon on start
      (10px, 18px, 10px, 24px),
      //padding with icon on end
      8px,
      2px,
    ),
    'd': (
      40px,
      16px,
      normal,
      (8px, 16px, 8px, 16px),
      // padding
      (8px, 16px, 8px, 10px),
      // padding with icon on start
      (8px, 10px, 8px, 16px),
      //padding with icon on end
      8px,
      2px,
    ),
    's': (
      36px,
      14px,
      normal,
      (4px, 12px, 4px, 12px),
      // padding
      (4px, 12px, 4px, 6px),
      // padding with icon on start
      (4px, 6px, 4px, 12px),
      //padding with icon on end
      8px,
      2px,
    ),
    'xs': (
      32px,
      12px,
      normal,
      (0, 8px, 0, 8px),
      // padding
      (0, 8px, 0, 4px),
      // padding with icon on start
      (0, 4px, 0, 8px),
      //padding with icon on end
      4px,
      4px,
    ),
  );

  // Function to process each size value if is array
  @function process-values($values) {
    $processed: ();
    @each $value in $values {
      // Check if the item is a list (this is where padding array could be)
      @if type-of($value) == 'list' {
        $processed: append($processed, join($value, ', ')); // Convert list to string
      } @else {
        $processed: append($processed, $value);
      }
    }
    @return $processed;
  }

  // Iterate over each size to apply styles
  @each $size, $values in $sizes {
    &__size__#{$size} {
      height: nth($values, 1);
      font-size: nth($values, 2);
      line-height: nth($values, 3);
      gap: nth($values, 7);
      padding: process-values(nth($values, 4));

      // For icon only buttons
      &.is-icon-start {
        padding: process-values(nth($values, 5));
      }

      // For icon only buttons
      &.is-icon-end {
        padding: process-values(nth($values, 6));
      }

      // For icon only buttons
      &.is-icon-only {
        width: nth($values, 1);
        padding: 0;

        // Adjust spinner container for icon only buttons
        .ivyforms-button-option__spinner-container {
          margin: 0;
        }
      }

      // Spinner
      .ivyforms-button-option__spinner {
        padding: nth($values, 8);
      }
    }
  }

  // Icon Containers
  #{$icon-start},
  #{$icon-end} {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }

  // Disabled & Loading
  &:disabled,
  &.is-loading {
    cursor: not-allowed;
  }

  // Text
  span {
    display: inline-block;
    text-overflow: ellipsis;
    overflow: hidden;
    white-space: nowrap;
  }

  // Icon
  i {
    display: inline-block;
  }

  // Spinner Container
  &__spinner-container {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  // Spinner
  &__spinner {
    --spinner-size: 5;
    --spinner-ring-size: 5;

    display: block;
    font-size: calc(var(--spinner-size) * 1em);
    border-radius: 50%;

    // Spinner Line
    &__line {
      fill: none;
      stroke: var(--spinner-line-color);
      stroke-width: var(--spinner-ring-size);
      stroke-linecap: round;
      transform-origin: 50% 50%;
      transform: rotate3d(0, 0, 1, 0deg);
      animation:
        2156ms spinner-arc ease-in-out infinite,
        1829ms spinner-rotate linear infinite;
    }
  }

  // Loader Animation Rotate
  @keyframes spinner-rotate {
    to {
      transform: rotate3d(0, 0, 1, 360deg);
    }
  }

  // Loader Animation Arc
  @keyframes spinner-arc {
    from {
      stroke-dasharray: 0 150;
      stroke-dashoffset: 0;
    }
    to {
      stroke-dasharray: 100 150;
      stroke-dashoffset: -140;
    }
  }
}
</style>
