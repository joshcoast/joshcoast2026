<template>
  <div
    class="ivyforms-text-number has-stepper"
    :class="{
      'is-filled': filled,
      'is-disabled': disabled,
      'is-readonly': readOnly,
    }"
  >
    <IvyTextInput
      ref="textInputRef"
      v-model="displayValue"
      :type="'text'"
      :placeholder="placeholder"
      :secondary="secondary"
      class="ivyforms-text-number__input"
      :clearable="clearable"
      :disabled="disabled"
      :readonly="readOnly"
      :required="required"
      :aria-label="ariaLabel"
      :inputmode="inputMode"
      @focus="onFocus"
      @blur="onBlur"
      @input="onRawInput"
      @keydown.up.prevent="increment"
      @keydown.down.prevent="decrement"
      @keydown="onKeyDown"
      @paste.prevent="onPaste"
    >
      <template #prefix>
        <button
          type="button"
          class="ivyforms-text-number__btn ivyforms-text-number__btn--dec"
          :disabled="!canDecrement || disabled || readOnly"
          :aria-label="getLabel('decrease_value')"
          :title="getLabel('decrease_value')"
          @mousedown.prevent
          @click.prevent.stop="decrement"
        >
          <IvyIcon name="minus" size="s" type="outline" color="var(--map-base-dusk-symbol-2)" />
        </button>
      </template>
      <template #suffix>
        <button
          type="button"
          class="ivyforms-text-number__btn ivyforms-text-number__btn--inc"
          :disabled="!canIncrement || disabled || readOnly"
          :aria-label="getLabel('increase_value')"
          :title="getLabel('increase_value')"
          @mousedown.prevent
          @click.prevent.stop="increment"
        >
          <IvyIcon name="plus" size="s" type="outline" color="var(--map-base-dusk-symbol-2)" />
        </button>
      </template>
    </IvyTextInput>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import IvyTextInput from './IvyTextInput.vue'
import {
  formatNumber,
  sanitizeRaw,
  parseToNumber,
  formatNumberForField,
} from '@/utils/utilNumberFormatter'
import { useLabels } from '@/composables/useLabels'

/**
 * A text-based number input that supports locale-like formatting (US / EU) while
 * still exposing numeric modelValue. Accepts only digits, minus sign, dot, comma.
 */
interface Props {
  modelValue: number | null | undefined
  numberFormat?: 'us_decimal' | 'us_int' | 'eu_decimal' | 'eu_int' | ''
  min?: number
  max?: number
  step?: number // reserved for potential future use (manual step buttons)
  placeholder?: string
  filled?: boolean
  secondary?: boolean
  clearable?: boolean
  disabled?: boolean
  readOnly?: boolean
  required?: boolean
  ariaLabel?: string
}

const props = withDefaults(defineProps<Props>(), {
  numberFormat: '',
  min: Number.MIN_SAFE_INTEGER,
  max: Number.MAX_SAFE_INTEGER,
  step: 1,
  placeholder: '',
  filled: false,
  secondary: false,
  clearable: false,
  disabled: false,
  readOnly: false,
  required: false,
  ariaLabel: '',
})

const emit = defineEmits<{
  (e: 'update:modelValue', value: number | null): void
  (e: 'input', raw: string): void
  (e: 'invalid', reason: string): void
}>()

// Internal display string (formatted)
const displayValue = ref('')
// Flag: when focused we show unformatted raw (except thousand separators) to ease editing
const isFocused = ref(false)
// Stronger typing for the input ref: can be component instance or native input
const textInputRef = ref<null | {
  input?: HTMLInputElement
  $el?: HTMLElement
  focus?: () => void
}>(null)

const { getLabel } = useLabels()

// Determine inputmode for better mobile keyboard UX
const inputMode = computed(() => {
  if (props.numberFormat === 'us_int' || props.numberFormat === 'eu_int') return 'numeric'
  return 'decimal'
})

// Helper: produce the raw editable string from a numeric model value
const rawFromModel = (val: number | null | undefined) => {
  if (val === null || val === undefined || Number.isNaN(val as number)) return ''
  let raw = String(val)
  if (props.numberFormat === 'eu_decimal') raw = raw.replace('.', ',')
  if (props.numberFormat === 'eu_int' || props.numberFormat === 'us_int')
    raw = String(Math.trunc(val))
  return raw
}

// Helper: allowed characters depending on number format
const allowedChars = (key: string) => {
  // If it's a single printable character, validate directly
  if (key.length === 1) {
    // digits always allowed; allow minus, dot, comma
    return /^[0-9]$/.test(key) || key === '-' || key === '.' || key === ','
  }
  // Allow common non-character key names produced by numpad / different layouts
  const extra = ['NumpadSubtract', 'NumpadDecimal', 'Subtract', 'Decimal', 'Minus']
  return extra.includes(key)
}

// Keydown handler to prevent disallowed input characters while preserving navigation/editing keys
const onKeyDown = (e: KeyboardEvent) => {
  // Allow control combos (copy/paste/cut/select all)
  if (e.ctrlKey || e.metaKey || e.altKey) return

  const key = e.key
  // Always allow navigation / editing keys
  const allowedControlKeys = [
    'Backspace',
    'Delete',
    'ArrowLeft',
    'ArrowRight',
    'Home',
    'End',
    'Tab',
    'Enter',
  ]
  if (allowedControlKeys.includes(key)) return

  if (!allowedChars(key)) {
    e.preventDefault()
    return
  }

  // Only allow one decimal separator and only one minus at start
  const inputEl = (textInputRef.value &&
    (textInputRef.value.input ||
      textInputRef.value.$el?.querySelector('input'))) as HTMLInputElement | null
  const cur = inputEl?.value ?? displayValue.value

  // If current is empty, allow minus or decimal (so user can start with those)
  // Determine decimal char based on format
  const decimalChar = props.numberFormat === 'eu_decimal' ? ',' : '.'

  if (key === decimalChar) {
    // If already contains a decimal separator (either one), block
    if ((cur ?? '').includes('.') || (cur ?? '').includes(',')) {
      e.preventDefault()
    }
    return
  }

  if (key === '-' || key === 'Minus' || key === 'Subtract') {
    const selectionStart = inputEl?.selectionStart ?? 0
    if ((cur ?? '').includes('-') || selectionStart !== 0) {
      e.preventDefault()
    }
    return
  }
}

// Handle paste: sanitize pasted text before inserting
const onPaste = (e: ClipboardEvent) => {
  const text = e.clipboardData?.getData('text') ?? ''
  if (!text) return
  const sanitized = sanitizeRaw(text, props.numberFormat)

  // Insert sanitized text at cursor position
  const inputEl = (textInputRef.value &&
    (textInputRef.value.input ||
      textInputRef.value.$el?.querySelector('input'))) as HTMLInputElement | null
  if (!inputEl) return
  const start = inputEl.selectionStart ?? 0
  const end = inputEl.selectionEnd ?? 0
  const before = inputEl.value.slice(0, start)
  const after = inputEl.value.slice(end)
  const next = before + sanitized + after
  displayValue.value = next

  // Set caret after the inserted sanitized text
  const caret = start + sanitized.length
  requestAnimationFrame(() => {
    try {
      inputEl.setSelectionRange(caret, caret)
    } catch {
      // ignore if not supported
    }
  })

  // Manually trigger parsing/emit
  const parsed = parseToNumber(next, props.numberFormat, props.min, props.max)
  if (parsed === null) emit('update:modelValue', null)
  else emit('update:modelValue', parsed)
}

// Update display when external model changes
watch(
  () => props.modelValue,
  (val) => {
    if (isFocused.value) {
      displayValue.value = rawFromModel(val)
    } else {
      displayValue.value = formatNumberForField(val ?? null, props.numberFormat)
    }
  },
  { immediate: true },
)

// Handle raw input event while focused
const onRawInput = (e: Event | string) => {
  const original =
    typeof e === 'string' ? e : ((e.target as HTMLInputElement | null)?.value ?? displayValue.value)
  const sanitized = sanitizeRaw(original, props.numberFormat)
  if (sanitized !== original) {
    displayValue.value = sanitized
  }
  emit('input', sanitized)
  const parsed = parseToNumber(sanitized, props.numberFormat, props.min, props.max)
  if (parsed === null) {
    emit('update:modelValue', null)
  } else {
    emit('update:modelValue', parsed)
  }
}

const onFocus = () => {
  isFocused.value = true
  displayValue.value = rawFromModel(props.modelValue)
}

const onBlur = () => {
  isFocused.value = false
  // Reformat display value using formatter
  const num = parseToNumber(displayValue.value, props.numberFormat, props.min, props.max)
  if (num === null) {
    displayValue.value = ''
    emit('update:modelValue', null)
  } else {
    displayValue.value = formatNumberForField(num, props.numberFormat)
    emit('update:modelValue', num)
  }
}

// Add step computed
const stepValue = computed(() => (props.step && props.step > 0 ? props.step : 1))

// Capability flags (updated to consider disabled/readOnly in template only)
const canIncrement = computed(() => {
  if (props.modelValue === null || props.modelValue === undefined) return true
  return props.modelValue + stepValue.value <= props.max
})
const canDecrement = computed(() => {
  if (props.modelValue === null || props.modelValue === undefined) return true
  return props.modelValue - stepValue.value >= props.min
})

const applyIntConstraint = (val: number) => {
  if (props.numberFormat === 'us_int' || props.numberFormat === 'eu_int') return Math.trunc(val)
  return val
}

const clamp = (val: number) => Math.min(Math.max(val, props.min), props.max)

const increment = () => {
  let current = props.modelValue
  if (current === null || current === undefined || typeof current !== 'number' || isNaN(current)) {
    current = 0
  }
  const next = clamp(applyIntConstraint(Number(current) + stepValue.value))
  emit('update:modelValue', next)
  if (!isFocused.value) displayValue.value = formatNumber(next, props.numberFormat)
}

const decrement = () => {
  let current = props.modelValue
  if (current === null || current === undefined || typeof current !== 'number' || isNaN(current)) {
    current = 0
  }
  const next = clamp(applyIntConstraint(Number(current) - stepValue.value))
  emit('update:modelValue', next)
  if (!isFocused.value) displayValue.value = formatNumber(next, props.numberFormat)
}

// Expose focus method
const focus = () => {
  ;(textInputRef.value as { focus?: () => void } | null)?.focus?.()
}

defineExpose({ focus })
</script>

<style lang="scss">
.ivyforms-text-number {
  position: relative;
  display: flex;
  width: 100%;

  &.is-disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }

  &__input {
    &.el-input {
      .el-input__wrapper {
        padding: 0 44px; // space for buttons
        text-align: center;
      }
      .el-input__inner {
        text-align: center;
      }
      // Make prefix/suffix overlay and not affect centering
      .el-input__prefix,
      .el-input__suffix {
        position: absolute;
        top: 0;
        height: 100%;
        display: flex;
        align-items: center;
        pointer-events: none; // buttons inside will re-enable
      }
      .el-input__prefix {
        left: 0;
        width: 44px;
        justify-content: center;
      }
      .el-input__suffix {
        right: 0;
        width: 44px;
        justify-content: center;
      }

      &__btn {
        pointer-events: auto;
      }
    }
  }
  &__btn {
    border: 0;
    background: transparent;
    cursor: pointer;
    padding: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    transition: background 0.15s ease;

    &:hover:not(:disabled):not(.is-readonly) {
      background: var(--map-base-dusk-o10, rgba(0, 0, 0, 0.05));
    }

    &:active:not(:disabled):not(.is-readonly) {
      background: var(--map-base-dusk-o15, rgba(0, 0, 0, 0.08));
    }

    &:disabled {
      opacity: 0.35;
      cursor: not-allowed;
    }
  }
}
</style>
