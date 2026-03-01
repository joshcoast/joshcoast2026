<template>
  <ElInput
    ref="elInputRef"
    v-bind="{ ...$attrs, ...$props }"
    v-model="localModelValue"
    :class="[
      type === 'textarea' ? 'ivyforms-textarea' : 'ivyforms-input',
      { 'is-filled': props.filled, 'is-secondary': props.secondary },
    ]"
    :show-password="showPassword !== undefined ? showPassword : type === 'password'"
    :type="localInputType"
    @focus="isFocused = true"
    @blur="isFocused = false"
  >
    <template v-if="!!slots['prefix'] || iconStart" #prefix>
      <slot name="prefix">
        <IvyIcon
          v-if="iconStart"
          :name="iconStart"
          :category="iconStartCategory"
          :type="iconStartType"
          class="ivyforms-input__icon-start"
          size="l"
          color="var(--map-base-dusk-symbol-2)"
        />
      </slot>
    </template>
    <template v-if="!!slots['suffix'] || iconEnd || props.clearable" #suffix>
      <slot name="suffix">
        <IvyIcon
          v-if="props.iconEnd"
          :name="props.iconEnd"
          :category="props.iconEndCategory"
          :type="props.iconEndType"
          class="ivyforms-input__icon-end"
          size="l"
          color="var(--map-base-dusk-symbol-2)"
        />
        <IvyIcon
          v-if="props.clearable && localModelValue"
          name="close-circle"
          class="ivyforms-input__clear"
          size="l"
          color="var(--map-base-dusk-symbol-2)"
          type="fill-duo"
          @click.stop="clearInput"
        />
      </slot>
    </template>
  </ElInput>
</template>

<script setup lang="ts">
import { ref, watch, useSlots, onMounted, onUnmounted } from 'vue'
import type { IconCategory } from '@/types/icons/icon-category'
import type { IconType } from '@/types/icons/icon-type'

interface Props {
  type?:
    | 'text'
    | 'textarea'
    | 'number'
    | 'select'
    | 'multiple-select'
    | 'phone'
    | 'email'
    | 'website'
    | 'password'
  placeholder?: string
  iconStart?: string
  iconEnd?: string
  iconStartCategory?: IconCategory
  iconEndCategory?: IconCategory
  avatar?: string
  iconStartType?: IconType
  iconEndType?: IconType
  filled?: boolean
  secondary?: boolean
  prefix?: string
  suffix?: string
  clearable?: boolean
  showPassword?: boolean
  modelValue: string | number | null | undefined
}

const props = withDefaults(defineProps<Props>(), {
  type: 'text',
  placeholder: '',
  iconStart: '',
  iconEnd: '',
  iconStartCategory: 'global',
  iconEndCategory: 'global',
  iconStartType: 'outline',
  iconEndType: 'outline',
  avatar: '',
  prefix: '',
  suffix: '',
  showPassword: undefined,
})

const emit = defineEmits(['update:modelValue'])

const localModelValue = ref(props.modelValue)
const localInputType = ref(props.type)

const slots = useSlots()

const elInputRef = ref()
const isFocused = ref(false)

let cleanupAutofill: (() => void) | null = null

// Autofill detection - simplified without problematic animation events
const setupAutofillDetection = () => {
  if (!elInputRef.value) return

  // Skip autofill detection for textarea elements (paragraph fields)
  if (props.type === 'textarea') {
    return
  }

  const inputElement =
    elInputRef.value.input || elInputRef.value.$el?.querySelector('input') || elInputRef.value.$el
  if (!inputElement) return

  // Only apply autofill detection to actual input elements, not textareas
  if (inputElement.tagName?.toLowerCase() === 'textarea') {
    return
  }

  // Clean up previous detection if exists
  if (cleanupAutofill) {
    cleanupAutofill()
    cleanupAutofill = null
  }

  // Single check function that handles all autofill detection
  const syncInputValue = () => {
    if (inputElement.value !== localModelValue.value) {
      localModelValue.value = inputElement.value
    }
  }

  // Strategy 1: Polling for first 3 seconds with reasonable intervals
  const intervalId = setInterval(syncInputValue, 100)
  const timeoutId = setTimeout(() => clearInterval(intervalId), 3000)

  // Strategy 2: Basic event listeners (removed problematic animation events)
  const eventTypes = ['input', 'change', 'blur']
  eventTypes.forEach((eventType) => {
    inputElement.addEventListener(eventType, syncInputValue, { passive: true })
  })

  // Cleanup function
  cleanupAutofill = () => {
    clearInterval(intervalId)
    clearTimeout(timeoutId)
    eventTypes.forEach((eventType) => {
      inputElement.removeEventListener(eventType, syncInputValue)
    })
  }

  return cleanupAutofill
}

// Watch for modelValue changes (especially form resets)
watch(
  () => props.modelValue,
  (newValue, oldValue) => {
    // If input was reset (empty/null value) after being populated, restart autofill detection
    const isReset = !newValue || newValue === ''
    const wasPopulated = oldValue && oldValue !== ''

    if (isReset && wasPopulated) {
      // Restart autofill detection after a brief delay
      setTimeout(() => {
        setupAutofillDetection()
      }, 200)
    }
  },
)

onMounted(() => {
  // Set up autofill detection after component is mounted and Element Plus is ready
  setTimeout(() => {
    cleanupAutofill = setupAutofillDetection()
  }, 100)
})

onUnmounted(() => {
  if (cleanupAutofill) {
    cleanupAutofill()
  }
})

// Sync localModelValue with props.modelValue
watch(
  () => props.modelValue,
  (newValue) => {
    localModelValue.value = newValue
  },
)

// Emit update:modelValue when localModelValue changes
watch(localModelValue, (newValue) => {
  emit('update:modelValue', newValue)
})

const clearInput = () => {
  localModelValue.value = ''
}

const focus = () => {
  elInputRef.value?.focus()
}

defineExpose({
  focus,
})
</script>

<style lang="scss">
// Input
.ivyforms-input {
  &.el-input {
    height: 40px;

    .el-input__wrapper {
      display: flex;
      align-items: center;
      gap: var(--Spacing-sm, 8px);
      padding: var(--Spacing-sm, 4px) var(--Spacing-lg, 12px);
      align-self: stretch;
      border-radius: var(--Radius-radius-md, 8px);
      border: 1px solid var(--map-base-dusk-stroke--2);
      background: var(--map-base-dusk-o05);
      box-shadow: none !important;
      outline: none !important;
      transition: none !important;
      box-sizing: border-box;

      .el-input__prefix-inner > :last-child {
        margin-right: 0;
      }

      // Hover
      &:hover,
      &.is-hover {
        border: 1px solid var(--map-base-dusk-stroke-0);
        background: var(--map-base-dusk-o05);
      }

      // Inner input
      input {
        padding: 0 !important;
        margin: 0 !important;
        font-size: 16px;
        font-style: normal;
        font-weight: 400;
        line-height: 22px;
        color: var(--map-base-text-0);
        border: none !important;
        box-shadow: none !important;
        background-color: transparent;
        display: flex;
        align-items: center;
        gap: 4px;
        flex: 1 0 0;
        outline: none !important;
        box-sizing: border-box;
      }

      // Active & Focus
      &:active,
      &:focus,
      &.is-focus,
      &.is-active {
        border: 1px solid var(--map-base-brand-stroke-0);
        background: var(--map-base-dusk-o05);
        box-shadow: none !important;
        outline: none !important;
        padding: var(--Spacing-sm, 4px) var(--Spacing-lg, 12px) !important;
      }

      // Clear Icon
      .ivyforms-input__clear {
        cursor: pointer;
        width: 20px;
        height: 20px;
        transform: scale(1);
        margin: 0;
      }

      .el-input__clear {
        display: none !important;

        &::before {
          display: none !important;
        }
      }
    }

    // Disabled state
    &.is-disabled {
      .el-input__wrapper {
        background: var(--map-base-dusk-o05);
        border-color: var(--map-base-dusk-stroke--2);
        box-shadow: none;

        input {
          color: var(--map-base-text-0);
          opacity: 0.5;
        }
      }
    }

    // Secondary
    &.is-secondary {
      .el-input__wrapper {
        // Active & Focus
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

// Form Item
.el-form-item {
  &.ivyforms-form-item-input {
    // Secondary
    &.is-secondary {
      .ivyforms-input {
        &.el-input {
          .el-input__wrapper {
            // Active & Focus
            &:active,
            &:focus,
            &.is-focus,
            &.is-active {
              border: 1px solid var(--map-base-purple-stroke-0);
            }
          }
        }
      }
    }
  }

  // Error and warning states
  &.is-error {
    .ivyforms-input {
      &.el-input {
        .el-input__wrapper {
          border: 1px solid var(--map-status-error-symbol-0) !important;
          box-shadow: none !important;
        }

        .el-input__wrapper:hover,
        .el-input__wrapper:focus,
        .el-input__wrapper:active,
        .el-input__wrapper.is-focus,
        .el-input__wrapper.is-active {
          border: 1px solid var(--map-status-error-symbol-0) !important;
          box-shadow: none !important;
        }
      }
    }
  }

  &.is-warning {
    .ivyforms-input {
      &.el-input {
        .el-input__wrapper {
          border: 1px solid var(--map-status-warning-stroke-0);
        }
      }
    }
  }

  // Textarea
  &.ivyforms-form-item-textarea {
    .ivyforms-textarea {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      font-size: 16px;
      font-style: normal;
      font-weight: 400;
      line-height: 22px;

      // Element Textarea
      .el-textarea__inner {
        border-radius: var(--Radius-radius-md, 8px);
        border: 1px solid var(--map-base-dusk-stroke--2);
        background-color: var(--map-base-dusk-o05);
        display: flex;
        align-items: center;
        gap: var(--Spacing-sm, 8px);
        align-self: stretch;
        box-shadow: none;
        color: var(--map-base-text-0);

        // Hover
        &:hover {
          border: 1px solid var(--map-base-dusk-stroke-0);
          background: var(--map-base-dusk-o05);
        }

        // Active & Focus
        &:active,
        &:focus,
        &.is-focus,
        &.is-active {
          border: 1px solid var(--map-base-brand-stroke-0);
          background: var(--map-base-dusk-o05);
          box-shadow: none;
        }
      }
    }

    // Secondary
    &.is-secondary {
      .ivyforms-textarea {
        .el-textarea__inner {
          // Active & Focus
          &:active,
          &:focus,
          &.is-focus,
          &.is-active {
            border: 1px solid var(--map-base-purple-stroke-0);
          }
        }
      }
    }
  }

  // Error and warning states
  &.is-error {
    .ivyforms-textarea {
      .el-textarea__inner {
        border: 1px solid var(--map-status-error-symbol-0) !important;
        box-shadow: 0 0 0 1px var(--el-color-danger) inset !important;
      }
    }
  }

  &.is-warning {
    .ivyforms-textarea {
      .el-textarea__inner {
        border: 1px solid var(--map-status-warning-stroke-0);
      }
    }
  }
}
</style>
