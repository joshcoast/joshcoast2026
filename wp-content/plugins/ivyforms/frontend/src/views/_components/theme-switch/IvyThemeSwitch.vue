<template>
  <div
    class="ivyforms-theme-switch"
    :class="{ 'is-dark': localModelValue === 'dark' }"
    :aria-label="props.ariaLabel || getLabel('theme_switch')"
    role="button"
    @click="toggleTheme"
  >
    <BackgroundLightImage
      class="ivyforms-theme-switch__background ivyforms-theme-switch__background--light"
    />
    <BackgroundDarkImage
      class="ivyforms-theme-switch__background ivyforms-theme-switch__background--dark"
    />
    <div class="ivyforms-theme-switch__images">
      <div class="ivyforms-theme-switch__images__item">
        <SunImage />
      </div>
      <div class="ivyforms-theme-switch__images__item">
        <MoonImage />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import BackgroundLightImage from '@/assets/images/theme-switch/background-light.svg?component'
import BackgroundDarkImage from '@/assets/images/theme-switch/background-dark.svg?component'
import SunImage from '@/assets/images/theme-switch/sun.svg?component'
import MoonImage from '@/assets/images/theme-switch/moon.svg?component'
import { computed } from 'vue'
import { useLabels } from '@/composables/useLabels'
const { getLabel } = useLabels()

interface Props {
  modelValue: 'light' | 'dark'
  ariaLabel?: string
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: 'light',
  ariaLabel: '',
})

const emit = defineEmits(['update:modelValue'])

const localModelValue = computed({
  get() {
    return props.modelValue
  },
  set(value) {
    emit('update:modelValue', value)
  },
})

function toggleTheme() {
  localModelValue.value = localModelValue.value === 'light' ? 'dark' : 'light'
}
</script>

<style lang="scss">
// Theme Switch
.ivyforms-theme-switch {
  position: relative;
  overflow: hidden;
  height: 40px;
  width: 40px;
  border-radius: 50%;
  display: flex;
  justify-content: center;
  align-items: center;
  cursor: pointer;

  // Background
  &__background {
    height: 40px;
    width: 40px;
    border-radius: 50%;
    position: absolute;
    z-index: 1;
    @include transition(opacity, 0.3s);

    // Light
    &--light {
      opacity: 1;
    }

    // Dark
    &--dark {
      opacity: 0;
    }
  }

  // Images
  &__images {
    position: absolute;
    top: 0;
    z-index: 2;
    @include transition(transform, 0.6s);

    // Item
    &__item {
      height: 40px;
      width: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
  }

  // SVG
  g {
    @include transition(fill-opacity, 0.3s);
  }

  // Path
  path {
    @include transition(fill, 0.3s);
  }

  // Light
  &:not(.is-dark) {
    // Hover
    &:hover {
      // Background
      .ivyforms-theme-switch__background {
        // SVG
        g {
          fill-opacity: 0.5;
        }
      }

      // Item
      .ivyforms-theme-switch__images__item {
        // SVG
        path {
          fill: var(--map-base-brand-symbol-1);
        }
      }
    }
  }

  // Dark
  &.is-dark {
    // Hover
    &:hover {
      // Background
      .ivyforms-theme-switch__background {
        // SVG
        g {
          fill-opacity: 0.15;
        }
      }
    }

    // Background
    .ivyforms-theme-switch__background {
      // Light
      &--light {
        opacity: 0;
      }

      // Dark
      &--dark {
        opacity: 1;
      }
    }

    // Images
    .ivyforms-theme-switch__images {
      transform: rotate(-180deg);
    }
  }
}
</style>
