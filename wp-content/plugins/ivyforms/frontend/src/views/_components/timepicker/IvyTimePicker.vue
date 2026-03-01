<template>
  <div class="ivyforms-time-picker" :class="{ 'is-secondary': isSecondary }">
    <ElTimePicker
      v-model="localModelValue"
      class="ivyforms-input"
      :class="{ 'has-value': !!localModelValue }"
      :editable="editable"
      :clearable="clearable"
      :format="displayFormat"
      :value-format="valueFormat"
      :popper-class="`ivyforms-time-popper ivyforms-time-popper--${popperBackground}`"
      :popper-options="{ placement: 'bottom-start' }"
      v-bind="restAttrs"
      @change="onChange"
    />
  </div>
</template>

<script setup lang="ts">
import { computed, useAttrs, inject } from 'vue'
import type { TimeFormat } from '@/types/time/time-type'

interface Props {
  modelValue?: string | null
  format?: string
  timeFormat?: TimeFormat
  valueFormat?: string
  clearable?: boolean
  editable?: boolean
  popperBackground?: 'level-2-foreground' | 'level-1-foreground'
  secondary?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: null,
  timeFormat: '24h',
  valueFormat: 'HH:mm',
  clearable: true,
  editable: false,
  popperBackground: 'level-2-foreground',
  format: undefined,
  secondary: false,
})

// Prioritize prop over inject
const isSecondary = computed(() => props.secondary || inject('isSecondary', false))

const emit = defineEmits<{
  (e: 'update:modelValue', value: string | null): void
  (e: 'change', value: string | null): void
}>()

const attrs = useAttrs()

// Separate attrs so we keep explicit control of a few props while forwarding the rest
const restAttrs = computed(() => {
  // Only exclude keys, do not assign them to variables
  const excludedKeys = [
    'modelValue',
    'format',
    'timeFormat',
    'valueFormat',
    'clearable',
    'editable',
    'popperBackground',
    'secondary',
  ]
  return Object.fromEntries(Object.entries(attrs).filter(([key]) => !excludedKeys.includes(key)))
})

const onChange = (val: string | null) => {
  emit('change', val)
}

const localModelValue = computed({
  get: () => props.modelValue ?? null,
  set: (val: string | null) => emit('update:modelValue', val),
})

const displayFormat = computed(() => {
  if (props.format) return props.format
  return props.timeFormat === 'ampm' ? 'hh:mm A' : 'HH:mm'
})
</script>

<style lang="scss">
// Root wrapper
.ivyforms-time-picker {
  width: 100%;
  height: 40px;

  input::selection {
    background-color: var(--map-base-dusk-o20);
    color: var(--map-base-text-0);
  }

  // El time editor behaves like date editor under the hood
  .el-date-editor {
    &.el-input {
      width: 100%;
      height: 40px;
    }
  }

  .ivyforms-input {
    width: 100%;
    height: 40px;

    .el-input__prefix {
      display: none;
    }

    .el-input__wrapper,
    &.el-input__wrapper {
      display: flex;
      align-items: center;
      gap: 4px;
      padding: 0 12px;
      border-radius: var(--Radius-radius-md, 8px);
      border: 1px solid var(--map-base-dusk-stroke--2);
      background: var(--map-base-dusk-o05);
      box-shadow: none;

      &:hover {
        border: 1px solid var(--map-base-dusk-stroke-0);
      }
      &:focus,
      &.is-focus,
      &:active,
      &.is-active {
        border: 1px solid var(--map-base-brand-stroke-0);
        box-shadow: none;
        background: var(--map-base-dusk-o05);
      }

      input.el-input__inner {
        padding: 0;
        border: none;
        background: transparent;
        box-shadow: none;
        font-size: 16px;
        line-height: 22px;
        color: var(--map-base-text-0);

        &:focus {
          outline: none;
        }
      }
    }
  }

  // Secondary styling
  &.is-secondary {
    .ivyforms-input {
      .el-input__wrapper,
      &.el-input__wrapper {
        &:focus,
        &.is-focus,
        &:active,
        &.is-active {
          border: 1px solid var(--map-base-purple-stroke-0);
        }
      }
    }
  }
}

// Popper styling
.ivyforms-time-popper {
  --el-color-primary: var(--map-base-brand-0);

  &.el-popper {
    display: flex;
    flex-direction: column;
    align-items: center;
    border: none !important;
    box-shadow:
      0px 12px 32px 4px rgba(0, 0, 0, 0.04),
      0px 8px 20px 0px rgba(0, 0, 0, 0.08) !important;
    border-radius: 4px;
    background: var(--map-ground-level-2-foreground);

    &.ivyforms-time-popper--level-1-foreground {
      background: var(--map-ground-level-1-foreground);
    }

    &.ivyforms-time-popper--level-2-foreground {
      background: var(--map-ground-level-2-foreground);
    }

    .el-time-spinner__item {
      margin-bottom: 0 !important;
    }
  }

  .el-time-spinner__item {
    color: var(--map-base-text-0) !important;

    &:hover:not(.is-disabled):not(.is-active) {
      background: var(--map-hover) !important;
    }
  }

  .el-popper__arrow {
    left: unset !important;

    &::before {
      background: var(--map-ground-level-2-foreground) !important;
      border: 1px solid var(--map-ground-level-2-foreground) !important;
    }
  }

  .el-time-panel {
    width: 165px;

    &__footer {
      padding: 8px 12px;
      height: 46px;
    }

    &__btn.cancel {
      font-weight: 500;
      padding: 0 8px;
      margin: 0;
      color: var(--map-base-text-0);
    }

    &__btn.confirm {
      background: var(--map-base-dusk-fill-1);
      border-radius: 4px;
      color: $primitive-white;
      text-transform: lowercase;
      padding: 0 8px;
      margin: 0 0 0 8px;

      &:first-letter {
        text-transform: uppercase;
      }
    }
  }
}
</style>
