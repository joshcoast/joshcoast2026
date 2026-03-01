<template>
  <span
    class="ivyforms-circle-loader ivyforms-align-items-center ivyforms-justify-content-center"
    :class="[`ivyforms-circle-loader--${size}`, { 'ivyforms-circle-loader--completed': completed }]"
  >
    <svg viewBox="0 0 24 24" class="ivyforms-circle-loader__svg ivyforms-width-100">
      <circle class="ivyforms-circle-loader__bg" cx="12" cy="12" r="10" />
      <circle class="ivyforms-circle-loader__track" cx="12" cy="12" r="10" />
      <circle class="ivyforms-circle-loader__progress" cx="12" cy="12" r="10" />
    </svg>
    <span
      class="ivyforms-circle-loader__check ivyforms-flex ivyforms-align-items-center ivyforms-justify-content-center ivyforms-width-100"
    >
      <CheckLoaderIcon />
    </span>
  </span>
</template>

<script setup lang="ts">
import CheckLoaderIcon from '@/assets/icons/global/outline/check-loader.svg?component'

interface Props {
  size?: 's' | 'd' | 'l'
  completed?: boolean
}

withDefaults(defineProps<Props>(), {
  size: 'd',
  completed: false,
})
</script>

<style lang="scss" scoped>
.ivyforms-circle-loader {
  display: inline-flex;
  position: relative;

  // Size variants
  &--s {
    width: 16px;
    height: 16px;
  }

  &--d {
    width: 24px;
    height: 24px;
  }

  &--l {
    width: 32px;
    height: 32px;
  }

  &__svg {
    height: 100%;
    position: absolute;
    top: 0;
    left: 0;
  }

  &__bg {
    fill: transparent;
  }

  &__track {
    fill: none;
    stroke: var(--map-base-dusk-stroke-1, #707883);
    stroke-width: 2;
    opacity: 0;
  }

  &__progress {
    fill: none;
    stroke: var(--map-base-dusk-symbol-2, #394452);
    stroke-width: 2;
    stroke-linecap: round;
    stroke-dasharray: 62.83;
    stroke-dashoffset: 62.83;
    transform-origin: center;
    transform: rotate(-90deg);
    animation: ivyforms-circle-loader-spin 1.2s linear infinite;
  }

  &__check {
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    z-index: 1;

    :deep(path) {
      fill: var(--map-base-dusk-symbol-2, #394452);
    }
  }

  // Completed state - show final appearance
  &--completed {
    .ivyforms-circle-loader__bg {
      fill: var(--map-base-dusk-symbol-2, #394452);
      animation: ivyforms-circle-loader-bg-appear 0.3s ease-out forwards;
    }

    .ivyforms-circle-loader__track {
      opacity: 1;
      animation: ivyforms-circle-loader-track-appear 0.3s ease-out forwards;
    }

    .ivyforms-circle-loader__progress {
      stroke-dashoffset: 0;
      animation: none;
    }

    .ivyforms-circle-loader__check {
      :deep(path) {
        fill: #ffffff;
        animation: ivyforms-circle-loader-check-appear 0.3s ease-out forwards;
      }
    }
  }
}

// Loading animation - spinning circle
@keyframes ivyforms-circle-loader-spin {
  0% {
    stroke-dashoffset: 62.83;
    transform: rotate(-90deg);
  }
  50% {
    stroke-dashoffset: 15;
  }
  100% {
    stroke-dashoffset: 62.83;
    transform: rotate(270deg);
  }
}

// Completed state animations
@keyframes ivyforms-circle-loader-bg-appear {
  from {
    fill: transparent;
  }
  to {
    fill: var(--map-base-dusk-symbol-2, #394452);
  }
}

@keyframes ivyforms-circle-loader-track-appear {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes ivyforms-circle-loader-check-appear {
  from {
    fill: var(--map-base-dusk-symbol-2, #394452);
  }
  to {
    fill: #ffffff;
  }
}
</style>
