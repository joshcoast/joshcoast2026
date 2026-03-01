<template>
  <ElInputNumber
    ref="elInputNumberRef"
    v-bind="{ ...$attrs, ...$props }"
    v-model="localModelValue"
    value-on-clear="min"
    :placeholder="''"
    class="ivyforms-input-number"
    :class="[
      {
        'ivyforms-full-width': fullWidth,
        'is-filled': props.filled,
        'is-secondary': props.secondary,
      },
    ]"
  >
    <template #decrease-icon>
      <IvyIcon
        priority="tertiary"
        name="minus"
        size="s"
        type="outline"
        color="var(--map-base-dusk-symbol-2)"
      />
    </template>
    <template #increase-icon>
      <IvyIcon
        priority="tertiary"
        name="plus"
        size="s"
        type="outline"
        color="var(--map-base-dusk-symbol-2)"
      />
    </template>
  </ElInputNumber>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'

interface Props {
  modelValue: number | undefined | null
  min?: number
  max?: number
  fullWidth?: boolean
  filled?: boolean
  secondary?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  fullWidth: true,
  min: Number.MIN_SAFE_INTEGER,
  max: Number.MAX_SAFE_INTEGER,
})

const emit = defineEmits(['update:modelValue', 'input'])

const elInputNumberRef = ref()

const localModelValue = computed({
  get() {
    return props.modelValue === null ? undefined : props.modelValue
  },
  set(value) {
    emit('update:modelValue', value === undefined || value === null ? null : value)
  },
})

const focus = () => {
  elInputNumberRef.value?.focus()
}

defineExpose({
  focus,
})
</script>

<style lang="scss">
// Error and warning states (for use in form item context)
.el-form-item {
  &.is-error {
    .ivyforms-input-number {
      &.el-input-number {
        .el-input__wrapper {
          border: 1px solid var(--map-status-error-symbol-0);
        }
      }
    }
  }

  &.is-warning {
    .ivyforms-input-number {
      &.el-input-number {
        .el-input__wrapper {
          border: 1px solid var(--map-status-warning-stroke-0);
        }
      }
    }
  }
}

.ivyforms-input-number {
  display: flex;
  height: 40px;
  min-height: 40px;
  background: var(--map-base-dusk-o05);

  // Element Input Number
  &.el-input-number {
    // Decrease and Increase Buttons
    .el-input-number__decrease,
    .el-input-number__increase {
      border: 0;
      background: transparent;

      // Icon
      svg path {
        @include transition(fill);
      }

      // Hover
      &:hover {
        // Wrapper
        ~ .el-input:not(.is-disabled) .el-input__wrapper {
          border-color: var(--map-base-dusk-stroke-0);
        }
      }

      // Disabled
      &.is-disabled {
        opacity: 0.5;

        // Button
        button {
          cursor: not-allowed;

          // Transition
          .ivyforms-button-action__transition {
            display: none;
          }
        }
      }
    }

    // Input Wrapper
    .el-input__wrapper {
      height: 40px;
      min-height: 40px;
      border-radius: 8px;
      padding: 0 40px;
      box-shadow: none !important;
      border: 1px solid var(--map-base-dusk-stroke--2);
      background-color: transparent;

      // Hover
      &:hover {
        border-color: var(--map-base-dusk-stroke-0);
      }

      // Active
      &:active,
      &:focus,
      &.is-focus,
      &.is-active {
        border-color: var(--map-base-brand-fill-0);
      }

      // Native Input
      .el-input__inner {
        box-shadow: none !important;
        border: 0;
        padding: 0;
        font-size: 16px;
        color: var(--map-base-text-0);
        background-color: transparent;
      }
    }

    // Decrease Button
    .el-input-number__decrease {
      @include flipProperty(left, right, 12px);
    }

    // Increase Button
    .el-input-number__increase {
      @include flipProperty(right, left, 12px);
    }
  }

  // Full Width
  &.ivyforms-full-width {
    width: 100%;
  }

  // Secondary
  &.is-secondary {
    &.el-input-number {
      .el-input__wrapper {
        &:active,
        &:focus,
        &.is-focus,
        &.is-active {
          border: 1px solid var(--map-base-purple-stroke-0) !important;
        }
      }
    }
  }
}
</style>
