<template>
  <div ref="ivyDatePickerRef" class="ivyforms-date-picker" :class="{ 'is-secondary': isSecondary }">
    <ElConfigProvider>
      <ElDatePicker
        v-model="localModelValue"
        class="ivyforms-input ivyforms-input__size__default"
        :class="{
          'has-value': Array.isArray(localModelValue)
            ? localModelValue.length > 0
            : localModelValue,
          'is-clearable': props.clearable,
        }"
        v-bind="{ ...$props, ...$attrs }"
        :popper-class="`ivyforms-date-picker-popper ${popperUid} ${props.type === 'daterange' ? 'ivyforms-date-range-popper' : ''} ${props.popperBackground} ${isSecondary ? 'is-secondary' : ''} ${props.popperClass}`"
        :popper-options="{ placement: 'bottom-start' }"
        :prefix-icon="prefixIconComponent"
        :range-separator="props.rangeSeparator"
        :shortcuts="shortcutsToUse"
        :cell-class-name="
          (date: Date) => {
            const baseClass = props.cellClassName?.(date) || ''
            const loadingClass = props.loading ? 'is-loading' : ''

            return [baseClass, loadingClass].filter(Boolean).join(' ')
          }
        "
        @change="() => emit('change', localModelValue)"
        @visible-change="onDatePickerVisibleChange"
      />
    </ElConfigProvider>
  </div>
</template>

<script setup lang="ts">
import IvyIcon from '@/views/_components/icon/IvyIcon.vue'
import { dayjs } from 'element-plus'
import { computed, h, nextTick, ref, shallowRef, watch, inject } from 'vue'
import { useLabels } from '@/composables/useLabels'

const { getLabel } = useLabels()

interface Props {
  modelValue?: string | number | Date | [Date, Date] | [string, string] | null
  disabled?: boolean
  clearable?: boolean
  weekStart?: number
  prefixIcon?: string
  prefixIconColor?: string
  prefixIconColorWhenActive?: string
  clearIconType?: 'fill' | 'outline'
  type?:
    | 'year'
    | 'years'
    | 'month'
    | 'months'
    | 'date'
    | 'dates'
    | 'datetime'
    | 'week'
    | 'datetimerange'
    | 'daterange'
    | 'monthrange'
    | 'yearrange'
  popperBackground?: 'level-2-foreground' | 'level-1-foreground'
  popperClass?: string
  rangeSeparator?: string
  loading?: boolean
  cellClassName?: (date: Date) => string
  shortcuts?: boolean | Array<{ text: string; value: Date | (() => Date | [Date, Date]) }>
  format?: string
  valueFormat?: string
  secondary?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: null,
  weekStart: 1,
  prefixIconColorWhenActive: undefined,
  cellClassName: undefined,
  shortcuts: false,
  prefixIcon: 'calendar-dot',
  prefixIconColor: 'var(--map-base-brand-symbol-2)',
  clearIconType: 'outline',
  type: 'date',
  popperBackground: 'level-2-foreground',
  popperClass: '',
  rangeSeparator: '-',
  format: undefined,
  valueFormat: undefined,
  secondary: false,
})

// Prioritize prop over inject
const isSecondary = computed(() => props.secondary || inject('isSecondary', false))
const localShortcuts = [
  {
    text: getLabel('today'),
    value: () => [dayjs().startOf('day').toDate(), dayjs().endOf('day').toDate()],
  },
  {
    text: getLabel('yesterday'),
    value: () => [
      dayjs().subtract(1, 'day').startOf('day').toDate(),
      dayjs().subtract(1, 'day').endOf('day').toDate(),
    ],
  },
  {
    text: getLabel('last_7_days'),
    // Includes today and the previous 6 days
    value: () => [
      dayjs().subtract(6, 'day').startOf('day').toDate(),
      dayjs().endOf('day').toDate(),
    ],
  },
  {
    text: getLabel('last_30_days'),
    // Includes today and the previous 29 days
    value: () => [
      dayjs().subtract(29, 'day').startOf('day').toDate(),
      dayjs().endOf('day').toDate(),
    ],
  },
  {
    text: getLabel('this_month'),
    value: () => [dayjs().startOf('month').toDate(), dayjs().endOf('month').toDate()],
  },
  {
    text: getLabel('last_month'),
    value: () => [
      dayjs().subtract(1, 'month').startOf('month').toDate(),
      dayjs().subtract(1, 'month').endOf('month').toDate(),
    ],
  },
  {
    text: getLabel('last_3_months'),
    value: () => [
      dayjs().subtract(3, 'month').startOf('month').toDate(),
      dayjs().endOf('month').toDate(),
    ],
  },
  {
    text: getLabel('last_6_months'),
    value: () => [
      dayjs().subtract(6, 'month').startOf('month').toDate(),
      dayjs().endOf('month').toDate(),
    ],
  },
  {
    text: getLabel('this_year'),
    value: () => [dayjs().startOf('year').toDate(), dayjs().endOf('year').toDate()],
  },
]

const shortcutsToUse = computed(() => {
  if (!props.shortcuts) return undefined
  return Array.isArray(props.shortcuts) ? props.shortcuts : localShortcuts
})

const emit = defineEmits(['update:modelValue', 'change'])

const ivyDatePickerRef = ref<HTMLElement | null>(null)

// Unique popper class so we always scope DOM queries to this instance's popper
const popperUid = `ivy-date-popper-${Math.random().toString(36).slice(2, 8)}`

// Keep track of which shortcut index (if any) matches the current modelValue
const selectedShortcutIndex = ref<number | null>(null)

const prefixIconComponent = props.prefixIcon
  ? shallowRef({
      render() {
        return h(IvyIcon, {
          name: props.prefixIcon,
          color: props.prefixIconColor,
        })
      },
    })
  : null

dayjs['en'].weekStart = props.weekStart !== undefined ? props.weekStart : 1

const localModelValue = computed({
  get() {
    return props.modelValue
  },
  set(value) {
    emit('update:modelValue', value)
    nextTick(() => {
      const datePickerInput = ivyDatePickerRef.value?.querySelector(
        '.el-input__inner',
      ) as HTMLInputElement | null
      const dateRangePickerInputs = ivyDatePickerRef.value
        ? (Array.from(ivyDatePickerRef.value.querySelectorAll('.el-range-input')) as HTMLElement[])
        : []

      if (datePickerInput) {
        datePickerInput.blur()
      } else if (dateRangePickerInputs.length) {
        dateRangePickerInputs.forEach((input) => {
          const el = input as HTMLElement & { blur?: () => void }
          el.blur?.()
        })
      }
    })
  },
})

const prefixIconColorWhenActiveComputed = computed(() => {
  return props.prefixIconColorWhenActive || props.prefixIconColor
})

// Helpers to compute and highlight the selected shortcut when the picker opens
type RangeOrNull = { start: number; end: number } | null

function toDate(input: Date | string | number | null | undefined): Date | null {
  if (input == null) return null
  if (input instanceof Date) return input
  if (typeof input === 'number') return new Date(input)
  if (typeof input === 'string') return new Date(input)
  return null
}

function normalizeToRange(
  value: string | number | Date | [Date, Date] | [string, string] | null | undefined,
): RangeOrNull {
  if (value == null) return null
  if (Array.isArray(value)) {
    const [a, b] = value as Array<Date | string | number | null | undefined>
    const sa = toDate(a)
    const sb = toDate(b)
    if (!sa || !sb) return null
    return { start: sa.getTime(), end: sb.getTime() }
  }
  const d = toDate(value as Date | string | number)
  if (!d || isNaN(d.getTime())) return null
  return { start: d.getTime(), end: d.getTime() }
}

type ShortcutShape = { text?: string; value: Date | [Date, Date] | (() => Date | [Date, Date]) }
type ShortcutValue = Date | [Date, Date]

function getShortcutRange(shortcut: ShortcutShape | null | undefined): RangeOrNull {
  if (!shortcut) return null
  const val =
    typeof shortcut.value === 'function'
      ? (shortcut.value as () => ShortcutValue)()
      : (shortcut.value as ShortcutValue)
  return normalizeToRange(val)
}

function clearShortcutSelectionFromPopper(popper: Element) {
  const elems = popper.querySelectorAll('.el-picker-panel__shortcut')
  elems.forEach((el) => {
    el.classList.remove('selected')
    el.removeAttribute('aria-selected')
  })
}

function normalizeForComparison(r: RangeOrNull): RangeOrNull {
  if (!r) return null
  // Normalize to day boundaries to avoid millisecond/timezone mismatches
  const start = dayjs(r.start).startOf('day').valueOf()
  const end = dayjs(r.end).endOf('day').valueOf()
  return { start, end }
}

// Recompute selectedShortcutIndex whenever the model value or shortcuts change
watch(
  [() => localModelValue.value, () => shortcutsToUse.value],
  () => {
    selectedShortcutIndex.value = null
    const current = normalizeForComparison(normalizeToRange(localModelValue.value))
    if (!current || !shortcutsToUse.value) return

    for (const [i, scRaw] of shortcutsToUse.value.entries()) {
      const sc = scRaw as ShortcutShape
      const scRange = normalizeForComparison(getShortcutRange(sc))
      if (!scRange) continue
      if (scRange.start === current.start && scRange.end === current.end) {
        selectedShortcutIndex.value = i
        break
      }
    }
  },
  { immediate: true },
)

function onDatePickerVisibleChange(visible: boolean) {
  // Only act when opening
  if (!visible) return
  // Query the popper using the unique UID for this instance
  const popper = document.querySelector(`.${popperUid}`)
  if (!popper) return

  // Clear any previous selection marks
  clearShortcutSelectionFromPopper(popper)

  if (selectedShortcutIndex.value == null) return

  const shortcutElems = Array.from(popper.querySelectorAll('.el-picker-panel__shortcut'))
  const el = shortcutElems[selectedShortcutIndex.value]
  if (el) {
    el.classList.add('selected')
    el.setAttribute('aria-selected', 'true')
  }
}
</script>

<style lang="scss">
@use 'sass:list' as *;
@use 'sass:meta' as *;
// Date Picker
.ivyforms-date-picker {
  width: 100%;
  height: 40px;

  // Date Editor
  .el-date-editor {
    // Input
    &.el-input {
      width: 100%;
      height: 40px;
    }
  }

  // Input
  .ivyforms-input {
    width: 100%;
    height: 40px;

    // Input Wrapper
    .el-input__wrapper,
    &.el-input__wrapper {
      display: flex;
      border-radius: 8px;
      box-shadow: none;
      padding: 0 12px;
      overflow: hidden;
      background-color: transparent;
      outline: none;
      gap: 4px;
      height: 40px;
      box-sizing: border-box;
      width: 100%;

      // Hover
      &:hover {
        box-shadow: none;
      }

      // Input
      input {
        padding: 0;
        font-size: 16px;
        font-style: normal;
        font-weight: 400;
        line-height: 22px;
        border: none;
        box-shadow: none;
        background-color: transparent;
      }

      // Range Input
      .el-range-input {
        text-overflow: ellipsis;
        overflow: hidden;
        white-space: nowrap;
      }

      // Icon
      .el-input__icon {
        width: 24px;
      }
    }

    // Prefix
    .el-input__prefix {
      display: none;
    }

    // Range Separator
    .el-range-separator {
      display: none;
    }
  }

  // Has Value
  .has-value {
    // Input Wrapper
    .el-input__wrapper,
    &.el-input__wrapper {
      // Range Icon
      .el-range__icon svg {
        fill: v-bind(prefixIconColorWhenActiveComputed);
      }
    }

    // Clearable
    &.is-clearable {
      // Input Wrapper
      .el-input__wrapper,
      &.el-input__wrapper {
        // Close Icon
        .el-range__close-icon--hidden {
          opacity: 1;
          visibility: visible;

          // Tablet & Up
          @include tablet-and-up {
            opacity: 0;
            visibility: hidden;
          }
        }
      }
    }
  }
}

// Date Picker Popper
.ivyforms-date-picker-popper.el-popper {
  display: flex;
  flex-direction: column;
  align-items: center;
  border: none !important;
  box-shadow:
    0px 12px 32px 4px rgba(0, 0, 0, 0.04),
    0px 8px 20px 0px rgba(0, 0, 0, 0.08) !important;
  border-radius: 4px;
  background: var(--map-ground-level-2-foreground);

  // Level 1 Foreground
  &.level-1-foreground {
    background: var(--map-ground-level-1-foreground);

    // Date Picker Panel
    .el-picker-panel {
      background: var(--map-ground-level-1-foreground);
    }
  }

  // Level 2 Foreground
  &.level-2-foreground {
    background: var(--map-ground-level-2-foreground);

    // Date Picker Panel
    .el-picker-panel {
      background: var(--map-ground-level-2-foreground);
      &__footer {
        background: var(--map-ground-level-2-foreground);
      }
    }
  }

  // Date Picker Panel
  .el-picker-panel {
    border-radius: 4px;

    // Panel Body
    .el-picker-panel__body {
      // Content
      .el-picker-panel__content {
        // Date Table
        .el-date-table {
          // Table Body
          tbody {
            display: flex;
            flex-direction: column;

            // Table Row
            tr {
              display: flex;

              // Header
              th {
                font-size: 12px;
                font-style: normal;
                font-weight: 400;
                color: var(--map-base-text--1);
                border-bottom: solid 1px var(--map-divider);
                padding: 3px 8px;
                box-sizing: border-box;
                flex: 1;
              }

              // Days
              td {
                width: 40px;
                height: 36px;
                padding: 0;
                flex: 1;
                color: var(--map-base-text-0);
                border-radius: 2px;
                @include transition(0.1s);

                // Hover
                &:hover {
                  color: var(--map-base-brand-symbol-0) !important;
                }

                // Prev & Next Month
                &.prev-month,
                &.next-month {
                  color: var(--map-base-text-0);
                  opacity: 0.5;
                }

                // Loading
                &.is-loading {
                  opacity: 1 !important;

                  // Date Table Cell
                  .el-date-table-cell {
                    background-color: unset !important;

                    // Date Table Cell Text
                    .el-date-table-cell__text {
                      background: linear-gradient(
                        90deg,
                        var(--map-skeleton) 25%,
                        var(--map-skeleton-to) 37%,
                        var(--map-skeleton) 63%
                      );
                      background-color: unset !important;
                      background-size: 400% 100%;
                      cursor: not-allowed;
                      content-visibility: hidden;
                      border-radius: 4px;
                      animation: el-skeleton-loading 1.4s ease infinite;
                      color: var(--map-base-text-0);
                    }
                  }
                }

                // Disabled
                &.disabled {
                  // Cell
                  .el-date-table-cell {
                    background: var(--map-base-dusk-o20);

                    // Cell Text
                    .el-date-table-cell__text {
                      color: var(--map-base-text-0);
                    }
                  }
                }
              }
            }

            // Date Row
            .el-date-table__row {
              display: flex;

              // TD
              td {
                height: 36px;
                display: flex;
                justify-content: center;
                align-items: center;

                // Today
                &.today {
                  // Cell
                  .el-date-table-cell {
                    // Cell Text
                    .el-date-table-cell__text {
                      font-weight: 500;
                      color: var(--map-base-brand-symbol-0);
                    }
                  }
                }

                // Current
                &.current {
                  color: $primitive-white;
                  // Cell
                  .el-date-table-cell {
                    // Cell Text
                    .el-date-table-cell__text {
                      color: $primitive-white;
                      background-color: var(--map-base-brand-symbol-0);
                    }
                  }
                }

                // Date Cell
                .el-date-table-cell {
                  padding: 0;
                  height: 28px;
                  display: flex;
                  align-items: center;
                  justify-content: center;
                  line-height: 36px;
                  width: 100%;

                  // Cell Text
                  .el-date-table-cell__text {
                    font-size: 12px;
                    font-style: normal;
                    font-weight: 400;
                  }
                }

                // In Range
                &.in-range {
                  // Cell
                  .el-date-table-cell {
                    background-color: var(--map-base-brand-o10);
                  }
                }

                // Start & End Dates
                &.start-date,
                &.end-date {
                  // Cell
                  .el-date-table-cell__text {
                    background-color: var(--map-base-brand-symbol-0);
                    color: $primitive-white;
                  }

                  // Today
                  &.today {
                    // Cell
                    .el-date-table-cell {
                      // Cell Text
                      .el-date-table-cell__text {
                        color: $primitive-white;
                      }
                    }
                  }
                }
              }
            }
          }
        }

        // Moth & Year Tables
        .el-month-table,
        .el-year-table {
          display: flex;
          padding-top: 4px;

          // Body
          tbody {
            flex-direction: column;
            display: flex;
            flex: 1;
            gap: 8px;

            // Table Row
            tr {
              display: flex;

              // Data Cell
              td {
                padding: 0;
                border-radius: 4px;
                display: flex;
                cursor: default;
                width: 70px;
                @include transition(0.1s);

                // Today
                &.today {
                  // Cell Text
                  .el-date-table-cell__text {
                    color: var(--map-base-brand-symbol-0);
                    font-weight: 500;
                  }
                }

                // Current
                &.current {
                  background: var(--map-base-brand-fill-0);

                  // Cell Text
                  .el-date-table-cell__text {
                    color: $primitive-white;
                    background: var(--map-base-brand-symbol-0);
                  }
                }

                // Div
                div {
                  padding: 0;
                  height: 28px;
                }

                // Span
                span {
                  line-height: 28px;
                  height: 28px;
                  color: var(--map-base-text-0);
                  padding: 0;
                  display: flex;
                  align-items: center;
                  font-size: 12px;
                  font-style: normal;
                  font-weight: 400;
                  margin: 0;
                  justify-content: center;
                  flex: 1;
                  border-radius: 8px;
                }
              }
            }
          }
        }

        // Moth Table Cell
        .el-month-table td {
          // Hover
          &:hover {
            background: var(--map-hover);
            cursor: pointer;
          }
        }

        // Year Table Cell
        .el-year-table td {
          // Hover
          &.available:not(.current):hover {
            cursor: pointer;
            background: var(--map-hover);
          }

          // Hover
          &.available:hover {
            cursor: pointer;
          }
        }
      }
      .el-date-range-picker__header-label,
      .el-date-picker__header-label {
        font-size: 16px;
        font-style: normal;
        font-weight: 500;
        padding: 0 2px;
        color: var(--map-base-text-0);
      }
      .el-date-range-picker__time-header {
        border-bottom: 1px solid var(--map-divider);

        .el-input.el-date-range-picker__editor {
          .el-input__wrapper {
            background: var(--map-ground-level-2-foreground);
          }
        }
      }
    }

    .el-picker-panel__footer {
      border-top: 1px solid var(--map-divider);
    }

    &__shortcut {
      color: var(--map-base-text-0);
      &:hover,
      &.selected {
        color: var(--map-base-brand-symbol-0);
      }
    }
  }

  // Date Picker
  .el-date-picker {
    width: 280px;
    padding: 16px;
    box-sizing: content-box;

    // Panel Body
    .el-picker-panel__body {
      display: flex;
      flex-direction: column;

      // Header
      .el-date-picker__header {
        height: 24px;
        box-sizing: content-box;
        margin: 0;
        padding: 0 0 16px 0;
        display: flex;
        justify-content: center;
        align-items: center;

        // Label
        .el-date-range-picker__header-label,
        .el-date-picker__header-label {
          font-size: 16px;
          font-style: normal;
          font-weight: 500;
          padding: 0 2px;
          color: var(--map-base-text-0);

          // Hover
          &:hover {
            color: var(--map-base-brand-symbol-0);
          }
        }

        // Prev & Next Button
        .el-date-picker__prev-btn,
        .el-date-picker__next-btn {
          display: flex;
          line-height: 24px;
          height: 24px;

          // Icon
          i {
            display: none;
          }

          // Button
          button {
            width: 24px;
            height: 24px;
            padding: 0;
            margin: 0;
            display: flex;
            border-radius: 4px;
            text-align: center;
            align-items: center;
            justify-content: center;

            // Hover & Active
            &:hover,
            &:active {
              // Before
              &:before {
                color: var(--map-base-brand-symbol-0);
              }
            }

            // Before
            &:before {
              font-family: 'icomoon', serif;
              font-size: 20px;
              color: var(--map-base-brand-symbol-0);
            }
          }

          // Previous Year
          .d-arrow-left:before {
            content: '';
            display: inline-block;
            width: 20px;
            height: 20px;
            background-image: url('@/assets/icons/arrows/outline/arrow-double-left.svg');
            background-size: contain;
            background-repeat: no-repeat;
          }

          // Previous Month
          .arrow-left:before {
            content: '';
            display: inline-block;
            width: 20px;
            height: 20px;
            background-image: url('@/assets/icons/arrows/outline/chevron-left.svg');
            background-size: contain;
            background-repeat: no-repeat;
          }

          // Next Year
          .d-arrow-right:before {
            content: '';
            display: inline-block;
            width: 20px;
            height: 20px;
            background-image: url('@/assets/icons/arrows/outline/arrow-double-right.svg');
            background-size: contain;
            background-repeat: no-repeat;
          }

          // Next Month
          .arrow-right:before {
            content: '';
            display: inline-block;
            width: 20px;
            height: 20px;
            background-image: url('@/assets/icons/arrows/outline/chevron-right.svg');
            background-size: contain;
            background-repeat: no-repeat;
          }
        }

        // Prev Button
        .el-date-picker__prev-btn {
          @include flipProperty('margin-right', 'margin-left', auto);
        }

        // Next Button
        .el-date-picker__next-btn {
          @include flipProperty('margin-left', 'margin-right', auto);
        }
      }

      // Content
      .el-picker-panel__content {
        margin: 0;
        width: 100%;
      }
    }
  }

  // Date Range Popper
  &.ivyforms-date-range-popper {
    @media only screen and (max-width: 640px) {
      width: calc(100% - 12px);
    }
  }

  // Date Range
  .el-date-range-picker.has-sidebar {
    width: 100%;
  }
  .el-date-range-picker {
    width: 625px;

    @media only screen and (max-width: 640px) {
      width: 100%;
      display: flex;
      flex-direction: column;
    }

    // Panel Body
    .el-picker-panel__body {
      @media only screen and (max-width: 640px) {
        width: 100%;
        min-width: unset;
        display: flex;
        flex-direction: column;
      }

      // Content
      .el-picker-panel__content {
        width: 280px;
        box-sizing: content-box;

        @media only screen and (max-width: 640px) {
          width: 100%;
          box-sizing: border-box;
        }

        // Left
        &.is-left {
          border-right: 1px solid var(--map-divider);
          @media only screen and (max-width: 640px) {
            border: none;
          }
        }

        // Header
        .el-date-range-picker__header {
          height: 24px;
          box-sizing: content-box;
          padding-bottom: 16px;

          // Label
          div {
            font-size: 16px;
            font-style: normal;
            font-weight: 500;
            padding: 0 2px;
            line-height: 24px;
            color: var(--map-base-text-0) !important;
          }

          // Prev & Next Button
          .el-picker-panel__icon-btn {
            line-height: 24px;
            width: 24px;
            height: 24px;
            padding: 0;
            margin: 0;
            display: flex;
            border-radius: 4px;
            text-align: center;
            align-items: center;
            justify-content: center;

            // Icon
            i {
              display: none;
            }

            // Hover & Active
            &:hover,
            &:active {
              // Before
              &:before {
                color: var(--map-base-brand-symbol-0);
              }
            }

            // Before
            &:before {
              font-family: 'icomoon', serif;
              font-size: 20px;
              color: var(--map-base-brand-symbol-0);
            }

            /// Previous Year
            &.d-arrow-left:before {
              content: '';
              display: inline-block;
              width: 20px;
              height: 20px;
              background-image: url('@/assets/icons/arrows/outline/arrow-double-left.svg');
              background-size: contain;
              background-repeat: no-repeat;
            }

            // Previous Month
            &.arrow-left:before {
              content: '';
              display: inline-block;
              width: 20px;
              height: 20px;
              background-image: url('@/assets/icons/arrows/outline/chevron-left.svg');
              background-size: contain;
              background-repeat: no-repeat;
            }

            // Next Year
            &.d-arrow-right:before {
              content: '';
              display: inline-block;
              width: 20px;
              height: 20px;
              background-image: url('@/assets/icons/arrows/outline/arrow-double-right.svg');
              background-size: contain;
              background-repeat: no-repeat;
            }

            // Next Month
            &.arrow-right:before {
              content: '';
              display: inline-block;
              width: 20px;
              height: 20px;
              background-image: url('@/assets/icons/arrows/outline/chevron-right.svg');
              background-size: contain;
              background-repeat: no-repeat;
            }
          }
        }

        // Table cell
        .el-date-table-cell {
          &__text {
            @media only screen and (max-width: 640px) {
              width: 100%;
              border-radius: 12px;
            }
          }
        }
      }
    }
  }

  // Popper Arrow
  .el-popper__arrow:before {
    content: none;
    border: none;
  }
  .el-icon svg {
    display: contents;
  }
  .el-button.is-plain {
    border: 1px solid var(--map-base-brand-fill-0);
    span {
      color: var(--map-base-brand-fill-0);
    }
  }

  .el-button.is-text {
    color: var(--map-base-text-0);
    &:hover {
      background: none;
    }
  }
  .el-date-range-picker__editors-wrap {
    > .el-date-range-picker__time-picker-wrap:first-child {
      width: 100%;
    }

    > .el-date-range-picker__time-picker-wrap:nth-child(2) {
      display: none;
    }

    &.is-right {
      position: relative;
      left: -8px;
    }
    .el-input {
      .el-input__wrapper {
        height: 20px;
        border: 1px solid var(--map-base-dusk-stroke-1);
        box-shadow: none;
        display: flex;
        border-radius: var(--Radius-border-radius-base, 4px);

        .el-input__inner {
          justify-content: center;
          align-items: center;
          flex: 1 0 0;
          color: var(--map-base-text--2);

          font-family: Roboto;
          font-size: 12px;
          font-style: normal;
          font-weight: 400;
          line-height: 16px; /* 133.333% */

          &::placeholder {
            text-align: center;
          }
        }

        &:hover,
        &.is-hover {
          border: 1px solid var(--map-base-brand-stroke-0);
          background: transparent;
        }

        input {
          color: var(--map-base-text-0);
          border: none;
          box-shadow: none;
          background-color: transparent;
          display: flex;
        }

        &:active,
        &:focus,
        &.is-focus,
        &.is-active {
          border: 1px solid var(--map-base-brand-stroke-0);
          background: transparent;
          box-shadow: none;
        }
      }
    }
    .el-date-range-picker__time-picker-wrap {
      display: flex;
    }

    .el-input.el-input--small.el-date-range-picker__editor {
      flex-grow: 1;
    }
  }
}

.ivyforms-date-picker {
  &.is-secondary {
    .el-input {
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

// Secondary styling for Date Picker Popper
.ivyforms-date-picker-popper.el-popper.is-secondary {
  .el-button.is-plain,
  .el-picker-panel__link-btn.is-plain {
    border: 1px solid var(--map-base-purple-fill-0) !important;
    span {
      color: var(--map-base-purple-fill-0) !important;
    }
  }
  .el-date-range-picker__editors-wrap .el-input .el-input__wrapper {
    &:hover,
    &.is-hover,
    &:active,
    &:focus,
    &.is-focus,
    &.is-active {
      border: 1px solid var(--map-base-purple-stroke-0);
    }
  }
  .el-date-range-picker__header-label,
  .el-date-picker__header-label {
    &:hover {
      color: var(--map-base-purple-symbol-0);
    }
  }
  .el-date-picker__prev-btn,
  .el-date-picker__next-btn,
  .el-picker-panel__icon-btn {
    &:before {
      color: var(--map-base-purple-symbol-0);
    }
    &:hover,
    &:active {
      &:before {
        color: var(--map-base-purple-symbol-0);
      }
    }
  }

  .el-picker-panel {
    &__shortcut {
      &:hover,
      &.selected {
        color: var(--map-base-purple-symbol-0);
      }
    }
    .el-picker-panel__body .el-picker-panel__content .el-date-table tbody tr td:hover {
      color: var(--map-base-purple-symbol-0) !important;
    }
    .el-picker-panel__body .el-picker-panel__content .el-date-table tbody .el-date-table__row {
      td.today {
        .el-date-table-cell .el-date-table-cell__text {
          color: var(--map-base-purple-symbol-0);
        }
      }
      td.current {
        .el-date-table-cell .el-date-table-cell__text {
          background-color: var(--map-base-purple-symbol-0);
        }
      }
      td.in-range {
        .el-date-table-cell {
          background-color: var(--map-accent-sky-blue-fill--4);
        }
      }
      td.start-date,
      td.end-date {
        .el-date-table-cell__text {
          background-color: var(--map-base-purple-symbol-0);
        }
      }
    }
    .el-picker-panel__body .el-picker-panel__content {
      .el-month-table,
      .el-year-table {
        tbody tr td {
          &.today {
            .el-date-table-cell__text {
              color: var(--map-base-purple-symbol-0);
            }
          }
          &.current {
            background: var(--map-base-purple-fill-0);
            .el-date-table-cell__text {
              background: var(--map-base-purple-symbol-0);
            }
          }
        }
      }
    }
  }
}
</style>
