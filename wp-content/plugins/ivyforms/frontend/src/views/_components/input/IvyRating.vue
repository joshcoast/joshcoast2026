<template>
  <div
    class="ivyforms-rating ivyforms-flex ivyforms-flex-direction-row ivyforms-align-items-center"
    :class="{
      'is-secondary': props.secondary,
      'is-disabled': props.disabled,
      'is-readonly': props.readonly,
    }"
  >
    <div ref="starsRef" class="ivyforms-rating__stars ivyforms-flex ivyforms-flex-wrap-wrap">
      <button
        v-for="(option, index) in displayOptions"
        :key="index + 1"
        type="button"
        class="ivyforms-rating__star ivyforms-p-0 ivyforms-pr-8 ivyforms-cursor-pointer ivyforms-align-items-center ivyforms-justify-content-center"
        :class="[
          {
            'is-filled': index + 1 <= (hoveredIndex ?? ratingPosition),
            'is-hovered': hoveredIndex !== null && index + 1 <= hoveredIndex,
          },
          `ivyforms-rating__star--${iconType}`,
        ]"
        :aria-label="`Rate ${index + 1} out of ${displayOptions.length}`"
        :disabled="disabled || readonly"
        @mouseenter="!disabled && !readonly && (hoveredIndex = index + 1)"
        @click="!disabled && !readonly && (ratingPosition = index + 1)"
      >
        <IvyIcon
          :name="iconType"
          :type="index + 1 <= (hoveredIndex ?? ratingPosition) ? 'fill' : 'line'"
          :size="iconSize"
          :color="
            index + 1 <= (hoveredIndex ?? ratingPosition)
              ? activeColor
              : 'var(--map-base-dusk-symbol-2)'
          "
        />
      </button>
    </div>
    <p v-if="ratingLabel" class="ivyforms-rating__label regular-14 ivyforms-m-0 ivyforms-p-0">
      {{ ratingLabel }}
    </p>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import IvyIcon from '@/views/_components/icon/IvyIcon.vue'
import type { Choice } from '@/types/field'
interface Props {
  modelValue: number | undefined | null
  ratingIcon?: 'star' | 'heart' | 'like'
  showLabel?: boolean
  secondary?: boolean
  disabled?: boolean
  readonly?: boolean
  options?: Choice[]
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: 0,
  ratingIcon: 'star',
  showLabel: true,
  secondary: false,
  disabled: false,
  readonly: false,
  options: () => [],
})

const emit = defineEmits(['update:modelValue'])

const hoveredIndex = ref<number | null>(null)
const starsRef = ref<HTMLElement | null>(null)

function handleMouseMove(e: MouseEvent) {
  if (!starsRef.value) return
  const rect = starsRef.value.getBoundingClientRect()
  if (
    e.clientX < rect.left ||
    e.clientX > rect.right ||
    e.clientY < rect.top ||
    e.clientY > rect.bottom
  ) {
    hoveredIndex.value = null
  }
}

onMounted(() => {
  window.addEventListener('mousemove', handleMouseMove)
})
onBeforeUnmount(() => {
  window.removeEventListener('mousemove', handleMouseMove)
})

const iconType = computed(() => props.ratingIcon || 'star')
const iconSize = computed(() => 'l' as const)
const activeColor = computed(() => {
  if (iconType.value === 'heart') {
    return 'var(--map-accent-raspberry-fill-0)'
  }
  return 'var(--map-accent-amber-symbol-0)'
})

const normalizeValue = (value: unknown): number | null => {
  if (value === null || value === undefined) return null
  const numeric = Number(String(value).trim())
  return Number.isNaN(numeric) ? null : numeric
}

// Display options - use options prop (required for rating fields)
const displayOptions = computed(() => {
  if (Array.isArray(props.options) && props.options.length > 0) {
    return props.options
  }
  // Fallback to 5 options if none provided
  return Array.from({ length: 5 }, (_, i) => ({
    id: i + 1,
    label: '',
    value: String(i + 1),
    isDefault: false,
    position: i + 1,
  }))
})

// Rating position (1-based index) - represents which star/option is selected
const ratingPosition = computed({
  get() {
    const currentValue = normalizeValue(props.modelValue)
    // Clamp value to valid range (1 to displayOptions.length)
    if (currentValue === null || currentValue <= 0) return 0
    return Math.min(currentValue, displayOptions.value.length)
  },
  set(position) {
    // Emit the position (1-5) as the modelValue
    emit('update:modelValue', position === 0 ? 0 : (position ?? null))
  },
})

const isInteractive = computed(() => !props.readonly && !props.disabled)

const effectiveIndex = computed(() => {
  // When interactive and showing label, prefer hovered index
  if (isInteractive.value && props.showLabel && hoveredIndex.value !== null) {
    return hoveredIndex.value
  }
  return ratingPosition.value
})

const ratingLabel = computed(() => {
  const position = effectiveIndex.value
  if (!props.showLabel || position === null || position <= 0) {
    return ''
  }

  const option = displayOptions.value[position - 1]
  if (option && option.label) {
    return option.label
  }
  return String(position)
})
</script>

<style scoped lang="scss">
.ivyforms-rating {
  &.is-secondary {
    opacity: 0.6;
  }

  &.is-disabled {
    opacity: 0.5;
    pointer-events: none;
  }

  &.is-readonly {
    cursor: default;

    .ivyforms-rating__star {
      cursor: default;
      pointer-events: none;
    }
  }

  &__star {
    background: none;
    border: none;
    display: inline-flex;
    transition: all 0.2s ease-in-out;

    &:disabled {
      cursor: not-allowed;
    }

    &:hover:not(:disabled) {
      transform: scale(1.1);
    }

    &:focus-visible {
      outline: 2px solid var(--map-base-primary-symbol-0);
      outline-offset: 2px;
    }

    &.is-hovered {
      transform: scale(1.15);
    }

    &.is-filled {
      :deep(.ivyforms-icon) {
        color: var(--map-accent-amber-symbol-0);
      }
    }

    &:not(.is-filled) {
      :deep(.ivyforms-icon__svg.type-line) {
        path {
          stroke: var(--map-base-dusk-symbol-2);
        }
      }
    }
  }

  &__label {
    color: var(--map-base-text-0, #212832);
  }
}
</style>
