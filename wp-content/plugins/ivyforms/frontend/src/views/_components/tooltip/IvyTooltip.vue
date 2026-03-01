<template>
  <div
    class="ivyforms-tooltip"
    role="tooltip"
    :aria-label="props.ariaLabel || props.content || getLabel('tooltip')"
  >
    <ElTooltip
      v-model="localModelValue"
      :popper-class="['ivyforms-tooltip-popper', themeClass]"
      effect="customized"
      v-bind="$attrs"
      :hide-after="validatedHideAfter"
      :placement="placement"
      :popper-options="{
        modifiers: [
          { name: 'preventOverflow', options: { boundary: 'viewport' } },
          { name: 'flip', options: { fallbackPlacements: ['top', 'bottom', 'right', 'left'] } },
          {
            name: 'maxWidth',
            enabled: true,
            fn: ({ state }) => {
              state.styles.popper.maxWidth = validatedMaxWidth
            },
            phase: 'beforeWrite',
            requires: ['computeStyles'],
          },
        ],
      }"
      role="tooltip"
      :aria-hidden="!localModelValue"
      :aria-label="props.ariaLabel || getLabel('tooltip')"
    >
      <template #content>
        <div :id="tooltipId">
          <slot name="content">{{ props.content }}</slot>
        </div>
      </template>
      <div ref="tooltipTrigger" :aria-describedby="tooltipId">
        <slot />
      </div>
    </ElTooltip>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { nanoid } from 'nanoid'
import { useLabels } from '@/composables/useLabels'
const { getLabel } = useLabels()

interface Props {
  modelValue?: boolean
  content?: string | undefined
  maxWidth?: string
  hideAfter?: number
  placement?:
    | 'top'
    | 'top-start'
    | 'top-end'
    | 'bottom'
    | 'bottom-start'
    | 'bottom-end'
    | 'left'
    | 'left-start'
    | 'left-end'
    | 'right'
    | 'right-start'
    | 'right-end'
  theme?: 'light' | 'inverted' | 'dusk'
  ariaLabel?: string
}

const props = withDefaults(defineProps<Props>(), {
  content: '',
  maxWidth: '320px',
  hideAfter: 0,
  placement: 'top',
  theme: 'inverted',
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

// Generate a unique tooltip ID
const tooltipId = ref(`ivyforms-tooltip-${nanoid(6)}`)

// Validation
const validatedHideAfter = computed(() =>
  props.hideAfter && props.hideAfter > 0 ? props.hideAfter : 0,
)
const validatedMaxWidth = computed(() =>
  /^(\d+(\.\d+)?(px|em|rem|%)|auto)$/.test(props.maxWidth ?? '320px') ? props.maxWidth : '320px',
)

const themeClass = computed(() => `ivyforms-tooltip-${props.theme}`)
</script>

<style lang="scss">
// Tooltip Popper
.ivyforms-tooltip-popper {
  // Element Popper
  &.el-popper {
    border-radius: 8px;
    box-shadow:
      0 1px 2px 0 rgba(18, 26, 38, 0.3),
      0 1px 3px 1px rgba(18, 26, 38, 0.15);
    text-align: center;
    font-size: 12px;
    font-style: normal;
    font-weight: 500;
    line-height: 16px;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 4px;
    color: var(--map-base-text-0);
    background: var(--map-ground-level-2-foreground);

    // Arrow
    .el-popper__arrow {
      &::before {
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        position: absolute;
        border: none;
        background: var(--map-ground-level-2-foreground);
      }
    }

    // Themes
    &.is-customized {
      // Inverted
      &.ivyforms-tooltip-inverted {
        color: var(--map-base-dusk-fill--4);
        background: var(--map-base-dusk-fill-4);
        // Arrow
        .el-popper__arrow::before {
          background: var(--map-base-dusk-fill-4);
        }
      }

      // Dusk
      &.ivyforms-tooltip-dusk {
        color: var(--map-base-text-0);
        background: var(--map-base-dusk-fill--4);
        // Arrow
        .el-popper__arrow::before {
          background: var(--map-base-dusk-fill--4) !important;
        }
      }

      // Light
      &.ivyforms-tooltip-light {
        color: var(--map-base-text-0);
        background: var(--map-ground-level-2-foreground);
        // Arrow
        .el-popper__arrow::before {
          background: var(--map-ground-level-2-foreground);
        }
      }
    }
  }
}
</style>
