<template>
  <div
    :class="[
      'ivyforms-field',
      'ivyforms-field__date',
      'ivyforms-field__date_' + field.id,
      field.cssClasses,
      {
        'ivyforms-field__date--is-readonly': !!field.readOnly,
      },
    ]"
  >
    <!-- Input Mode (manual entry) -->
    <IvyFormItem
      v-if="field.dateFieldType === 'input'"
      :label="field.hideLabel ? '' : field.label"
      :required="field.required"
      :error="error"
      :prop="field.type + '_' + field.fieldIndex"
      :show-info="!!field.description"
      :info-text="field.description || ''"
      :show-info-icon="false"
      :label-position="field.labelPosition"
    >
      <IvyTextInput
        :id="fieldID"
        v-model="textInputValue"
        :aria-label="field.label"
        class="ivyforms-field__date-input"
        type="text"
        :placeholder="displayFormat"
        :disabled="disabled || field.readOnly"
        :readonly="field.readOnly"
        @blur="onTextInputBlur"
        @update:model-value="onTextInputChange"
      />
    </IvyFormItem>

    <!-- Dropdown Mode -->
    <IvyFormItem
      v-else-if="field.dateFieldType === 'dropdown'"
      :label="field.label"
      :required="field.required"
      :error="error"
      :prop="field.type + '_' + field.fieldIndex"
      :show-info="!!field.description"
      :info-text="field.description || ''"
      :show-info-icon="false"
      :label-position="field.labelPosition"
      :hidden-label="field.hideLabel"
    >
      <div class="ivyforms-flex ivyforms-gap-12 ivyforms-width-100">
        <div
          v-for="fieldConfig in dateFieldsConfig"
          :key="fieldConfig.type"
          class="ivyforms-flex ivyforms-flex-1 ivyforms-flex-direction-column"
        >
          <label
            v-if="fieldConfig.type === 'day'"
            :for="`day-select-${field.fieldIndex}`"
            class="medium-14 ivyforms-mb-6 ivyforms-field__date__sublabel"
          >
            {{ getLabel('day') }}
          </label>
          <label
            v-else-if="fieldConfig.type === 'month'"
            :for="`month-select-${field.fieldIndex}`"
            class="medium-14 ivyforms-mb-6 ivyforms-field__date__sublabel"
          >
            {{ getLabel('month') }}
          </label>
          <label
            v-else-if="fieldConfig.type === 'year'"
            :for="`year-select-${field.fieldIndex}`"
            class="medium-14 ivyforms-mb-6 ivyforms-field__date__sublabel"
          >
            {{ getLabel('year') }}
          </label>

          <IvySelectInput
            v-if="fieldConfig.type === 'day'"
            :id="`day-select-${field.fieldIndex}`"
            v-model="selectedDay"
            class="ivyforms-field__date-day"
            :error="!!(error && !selectedDay)"
            placeholder="&#45;&#45;"
            :disabled="disabled || field.readOnly"
            :clearable="!field.readOnly"
            field-type="date"
            :aria-label="`${fieldConfig.type}-select-${field.fieldIndex}`"
            @update:model-value="onPartChange"
          >
            <IvySelectOption
              v-for="d in dayOptions"
              :key="d.value"
              :label="d.label"
              :value="d.value"
              field-type="date"
            />
          </IvySelectInput>
          <IvySelectInput
            v-else-if="fieldConfig.type === 'month'"
            :id="`month-select-${field.fieldIndex}`"
            v-model="selectedMonth"
            class="ivyforms-field__date-month"
            :error="!!(error && !selectedMonth)"
            placeholder="&#45;&#45;"
            :disabled="disabled || field.readOnly"
            :clearable="!field.readOnly"
            field-type="date"
            :aria-label="`${fieldConfig.type}-select-${field.fieldIndex}`"
            @update:model-value="onPartChange"
          >
            <IvySelectOption
              v-for="m in monthOptions"
              :key="m.value"
              :label="m.label"
              :value="m.value"
              field-type="date"
            />
          </IvySelectInput>
          <IvySelectInput
            v-else-if="fieldConfig.type === 'year'"
            :id="`year-select-${field.fieldIndex}`"
            v-model="selectedYear"
            class="ivyforms-field__date-year"
            :error="!!(error && !selectedYear)"
            placeholder="&#45;&#45;"
            :disabled="disabled || field.readOnly"
            :clearable="!field.readOnly"
            field-type="date"
            :aria-label="`${fieldConfig.type}-select-${field.fieldIndex}`"
            @update:model-value="onPartChange"
          >
            <IvySelectOption
              v-for="y in yearOptions"
              :key="y.value"
              :label="y.label"
              :value="y.value"
              field-type="date"
            />
          </IvySelectInput>
        </div>
      </div>
    </IvyFormItem>

    <!-- Date Picker Mode -->
    <IvyFormItem
      v-else
      :label="field.hideLabel ? '' : field.label"
      :required="field.required"
      :error="error"
      :prop="field.type + '_' + field.fieldIndex"
      :show-info="!!field.description"
      :info-text="field.description || ''"
      :show-info-icon="false"
      :label-position="field.labelPosition"
    >
      <IvyDatePicker
        :id="fieldID"
        v-model="localModelValue"
        :aria-label="field.label"
        class="ivyforms-field__date-picker"
        :format="displayFormat"
        value-format="YYYY-MM-DD"
        :placeholder="field.placeholder"
        :disabled="disabled || field.readOnly"
        :clearable="true"
        :disabled-date="disabledDatePickerDates"
      />
    </IvyFormItem>
  </div>
</template>

<script setup lang="ts">
import { computed, inject, ref, watch, nextTick, onMounted } from 'vue'
import { useDateField } from '@/composables/useDateField'
import { useLabels } from '@/composables/useLabels'
import type { Field } from '@/types/field'
import IvyFormItem from '@/views/_components/form/IvyFormItem.vue'
import IvyDatePicker from '@/views/_components/datepicker/IvyDatePicker.vue'
import IvySelectInput from '@/views/_components/input/IvySelectInput.vue'
import IvySelectOption from '@/views/_components/input/IvySelectOption.vue'
import IvyTextInput from '@/views/_components/input/IvyTextInput.vue'

const { getLabel } = useLabels()

interface FieldProps {
  modelValue: string | null | undefined
  field: Field
  disabled?: boolean
  error?: string
}

const props = withDefaults(defineProps<FieldProps>(), {
  modelValue: '',
  disabled: false,
  error: '',
})

const emit = defineEmits(['update:modelValue'])
const clearFieldError = inject<(fieldKey: string) => void>('clearFieldError', () => {})
const fieldKey = computed(() => props.field.type + '_' + props.field.fieldIndex)

const {
  parseDateString,
  formatDateString,
  getDaysArray,
  getMonthsArray,
  getYearsArray,
  toISOFormat,
  fromISOFormat,
} = useDateField()

// Input mode ref - single text input for date
const textInputValue = ref<string>('')

// Dropdown mode refs - strictly typed as strings to prevent numeric values
const selectedDay = ref<string>('')
const selectedMonth = ref<string>('')
const selectedYear = ref<string>('')

// Create watchers to ensure dropdown values are always strings and never become numbers
watch(selectedDay, (newVal) => {
  if (typeof newVal !== 'string') {
    selectedDay.value = ''
  }
})
watch(selectedMonth, (newVal) => {
  if (typeof newVal !== 'string') {
    selectedMonth.value = ''
  }
})
watch(selectedYear, (newVal) => {
  if (typeof newVal !== 'string') {
    selectedYear.value = ''
  }
})

// Watch for year changes to clear invalid month/day selections
watch(selectedYear, () => {
  if (selectedYear.value && (minDate.value || maxDate.value)) {
    // Check if current month is still valid
    if (selectedMonth.value) {
      const isMonthValid = monthOptions.value.some((m) => m.value === selectedMonth.value)
      if (!isMonthValid) {
        selectedMonth.value = ''
        selectedDay.value = ''
      }
    }
  }
})

// Watch for month changes to clear invalid day selections
watch(selectedMonth, () => {
  if (selectedMonth.value && selectedYear.value && (minDate.value || maxDate.value)) {
    // Check if current day is still valid
    if (selectedDay.value) {
      const isDayValid = dayOptions.value.some((d) => d.value === selectedDay.value)
      if (!isDayValid) {
        selectedDay.value = ''
      }
    }
  }
})

// Flag to prevent watch from overwriting values during internal updates
const isInternalUpdate = ref(false)

// Determine field order and placeholders based on date format
const dateFieldsConfig = computed(() => {
  const format = ((props.field.dateFormat as string) || 'MM/DD/YYYY').toUpperCase()
  const fields: Array<{ type: 'day' | 'month' | 'year'; order: number; placeholder: string }> = []

  // Find position of each component in the format
  const dayIndex = format.indexOf('D')
  const monthIndex = format.indexOf('M')
  const yearIndex = format.indexOf('Y')

  if (dayIndex !== -1) {
    fields.push({ type: 'day', order: dayIndex, placeholder: format.includes('DD') ? 'DD' : 'D' })
  }
  if (monthIndex !== -1) {
    fields.push({
      type: 'month',
      order: monthIndex,
      placeholder: format.includes('MM') ? 'MM' : 'M',
    })
  }
  if (yearIndex !== -1) {
    fields.push({
      type: 'year',
      order: yearIndex,
      placeholder: format.includes('YYYY') ? 'YYYY' : 'YY',
    })
  }

  // Sort by position in format string
  return fields.sort((a, b) => a.order - b.order)
})

const handleInput = (value: string | null | undefined, shouldClearError: boolean = true) => {
  isInternalUpdate.value = true
  emit('update:modelValue', value ?? '')
  if (shouldClearError) {
    clearFieldError(fieldKey.value)
  }
  nextTick(() => {
    isInternalUpdate.value = false
  })
}

// Computed for date picker mode - convert to/from ISO string expected by DatePicker
const localModelValue = computed({
  get() {
    const val = props.modelValue != null ? String(props.modelValue) : ''
    return toISOFormat(val, (props.field.dateFormat as string) || 'MM/DD/YYYY')
  },
  set(dateValue: string) {
    const formatted = fromISOFormat(
      dateValue || '',
      (props.field.dateFormat as string) || 'MM/DD/YYYY',
    )
    handleInput(formatted, true)
  },
})

// Display format as string for props expecting string
const displayFormat = computed(() => String((props.field.dateFormat as string) || 'MM/DD/YYYY'))

// Parse minDateValue and maxDateValue to Date objects for validation
const minDate = computed(() => {
  if (!props.field.minDateValue) return null
  const parsed = parseDateString(
    String(props.field.minDateValue),
    (props.field.dateFormat as string) || 'MM/DD/YYYY',
  )
  if (parsed.day && parsed.month && parsed.year) {
    return new Date(Number(parsed.year), Number(parsed.month) - 1, Number(parsed.day))
  }
  return null
})

const maxDate = computed(() => {
  if (!props.field.maxDateValue) return null
  const parsed = parseDateString(
    String(props.field.maxDateValue),
    (props.field.dateFormat as string) || 'MM/DD/YYYY',
  )
  if (parsed.day && parsed.month && parsed.year) {
    return new Date(Number(parsed.year), Number(parsed.month) - 1, Number(parsed.day))
  }
  return null
})

// Function to disable dates outside the min/max range for date picker
const disabledDatePickerDates = (time: Date) => {
  if (minDate.value && time < minDate.value) {
    return true
  }
  if (maxDate.value && time > maxDate.value) {
    return true
  }
  return false
}

// Handle text input mode changes
function onTextInputChange(value: string | number | null | undefined) {
  // Just update the local value as user types
  textInputValue.value = value != null ? String(value) : ''
  emit('update:modelValue', value === undefined || value === null ? '' : value)
}

function onTextInputBlur() {
  // Validate and format the date when user leaves the input
  const value = textInputValue.value.trim()

  if (!value) {
    handleInput('', true)
    return
  }

  // Try to parse the date according to the format
  const parsed = parseDateString(value, (props.field.dateFormat as string) || 'MM/DD/YYYY')

  if (parsed.day && parsed.month && parsed.year) {
    // Validate the date values
    const dayNum = Number(parsed.day)
    const monthNum = Number(parsed.month)
    const yearNum = Number(parsed.year)

    // Check if date is valid
    const isValidDate =
      dayNum >= 1 &&
      dayNum <= 31 &&
      monthNum >= 1 &&
      monthNum <= 12 &&
      yearNum >= 1900 &&
      yearNum <= 2100

    if (isValidDate) {
      const enteredDate = new Date(yearNum, monthNum - 1, dayNum)

      // Validate against min/max date if set
      if (minDate.value && enteredDate < minDate.value) {
        // Date is before minimum - keep the input as is to show error
        handleInput(value, false)
        return
      }
      if (maxDate.value && enteredDate > maxDate.value) {
        // Date is after maximum - keep the input as is to show error
        handleInput(value, false)
        return
      }

      // Format the date properly
      const formatted = formatDateString(
        String(dayNum),
        String(monthNum),
        String(yearNum),
        (props.field.dateFormat as string) || 'MM/DD/YYYY',
      )

      textInputValue.value = formatted
      handleInput(formatted, true)
    } else {
      // Invalid date - keep the input as is to show error
      handleInput(value, false)
    }
  } else {
    // Could not parse - keep the input as is to show error
    handleInput(value, false)
  }
}

// Handle dropdown mode changes
function onPartChange() {
  nextTick(() => {
    // Ensure values are strings and validate they're not invalid numeric strings
    const dayStr = selectedDay.value == null ? '' : String(selectedDay.value)
    const monthStr = selectedMonth.value == null ? '' : String(selectedMonth.value)
    const yearStr = selectedYear.value == null ? '' : String(selectedYear.value)

    // Check for invalid values that might contain MIN_SAFE_INTEGER or other invalid numbers
    const isValidDay = dayStr && !dayStr.includes('-') && dayStr !== '0' && !isNaN(Number(dayStr))
    const isValidMonth =
      monthStr && !monthStr.includes('-') && monthStr !== '0' && !isNaN(Number(monthStr))
    const isValidYear =
      yearStr && !yearStr.includes('-') && yearStr.length === 4 && !isNaN(Number(yearStr))

    // Only emit when all three values are valid or all are empty
    const hasAllValues = isValidDay && isValidMonth && isValidYear
    const hasNoValues = !dayStr && !monthStr && !yearStr

    if (hasAllValues) {
      const formatted = formatDateString(
        dayStr,
        monthStr,
        yearStr,
        (props.field.dateFormat as string) || 'MM/DD/YYYY',
      )

      // Validate against min/max date if set
      if (minDate.value || maxDate.value) {
        const selectedDate = new Date(Number(yearStr), Number(monthStr) - 1, Number(dayStr))
        if (minDate.value && selectedDate < minDate.value) {
          // Date is before minimum - don't update
          return
        }
        if (maxDate.value && selectedDate > maxDate.value) {
          // Date is after maximum - don't update
          return
        }
      }

      handleInput(formatted, true)
    } else if (hasNoValues) {
      handleInput('', true)
    }
    // Don't emit anything for partial or invalid states
  })
}

// Options for dropdowns
const dayOptions = computed(() => {
  const allDays = getDaysArray()

  // If min/max dates are set, filter days accordingly
  if (minDate.value || maxDate.value) {
    // If we have a selected month and year, filter days for that specific month/year
    if (selectedMonth.value && selectedYear.value) {
      const month = Number(selectedMonth.value)
      const year = Number(selectedYear.value)

      return allDays.filter((day) => {
        if (!day.value) return true // Keep the empty option
        const dayNum = Number(day.value)
        const testDate = new Date(year, month - 1, dayNum)

        if (minDate.value && testDate < minDate.value) return false
        if (maxDate.value && testDate > maxDate.value) return false
        return true
      })
    }

    // If only year is selected, show days that could be valid in any month of that year
    if (selectedYear.value) {
      const year = Number(selectedYear.value)

      return allDays.filter((day) => {
        if (!day.value) return true // Keep the empty option
        const dayNum = Number(day.value)

        // Check if this day is valid in any month of the selected year
        for (let month = 1; month <= 12; month++) {
          const testDate = new Date(year, month - 1, dayNum)

          // Skip invalid dates (e.g., Feb 30)
          if (testDate.getDate() !== dayNum) continue

          const isValid =
            (!minDate.value || testDate >= minDate.value) &&
            (!maxDate.value || testDate <= maxDate.value)
          if (isValid) return true
        }
        return false
      })
    }

    // No year/month selected yet - show days that could be valid in the allowed date range
    return allDays.filter((day) => {
      if (!day.value) return true // Keep the empty option
      const dayNum = Number(day.value)

      // Get the range of years to check
      const minYear = minDate.value ? minDate.value.getFullYear() : new Date().getFullYear() - 100
      const maxYear = maxDate.value ? maxDate.value.getFullYear() : new Date().getFullYear() + 100

      // Check if this day number appears in any valid month/year combination
      for (let year = minYear; year <= maxYear; year++) {
        for (let month = 1; month <= 12; month++) {
          const testDate = new Date(year, month - 1, dayNum)

          // Skip invalid dates (e.g., Feb 30)
          if (testDate.getDate() !== dayNum) continue

          const isValid =
            (!minDate.value || testDate >= minDate.value) &&
            (!maxDate.value || testDate <= maxDate.value)
          if (isValid) return true
        }
      }
      return false
    })
  }

  return allDays
})

const monthOptions = computed(() => {
  const allMonths = getMonthsArray()

  // If min/max dates are set, filter months accordingly
  if (minDate.value || maxDate.value) {
    // If we have a selected year, filter months for that specific year
    if (selectedYear.value) {
      const year = Number(selectedYear.value)

      return allMonths.filter((month) => {
        if (!month.value) return true // Keep the empty option
        const monthNum = Number(month.value)

        // Check first and last day of the month
        const firstDay = new Date(year, monthNum - 1, 1)
        const lastDay = new Date(year, monthNum, 0)

        // Include month if any day in it falls within the valid range
        if (minDate.value && lastDay < minDate.value) return false
        if (maxDate.value && firstDay > maxDate.value) return false
        return true
      })
    }

    // No year selected yet - show months that could be valid in the allowed date range
    return allMonths.filter((month) => {
      if (!month.value) return true // Keep the empty option
      const monthNum = Number(month.value)

      // Get the range of years to check
      const minYear = minDate.value ? minDate.value.getFullYear() : new Date().getFullYear() - 100
      const maxYear = maxDate.value ? maxDate.value.getFullYear() : new Date().getFullYear() + 100

      // Check if this month appears in any valid year
      for (let year = minYear; year <= maxYear; year++) {
        const firstDay = new Date(year, monthNum - 1, 1)
        const lastDay = new Date(year, monthNum, 0)

        // Include month if any day in it falls within the valid range
        const isValid =
          (!minDate.value || lastDay >= minDate.value) &&
          (!maxDate.value || firstDay <= maxDate.value)
        if (isValid) return true
      }
      return false
    })
  }

  return allMonths
})

const yearOptions = computed(() => {
  let minYear = new Date().getFullYear() - 100
  let maxYear = new Date().getFullYear() + 100

  // If minDateValue is set, use its year as the minimum
  if (minDate.value) {
    minYear = minDate.value.getFullYear()
  }

  // If maxDateValue is set, use its year as the maximum
  if (maxDate.value) {
    maxYear = maxDate.value.getFullYear()
  }

  return getYearsArray(minYear, maxYear)
})

// Watch for modelValue changes to update inputs
watch(
  () => [props.modelValue, props.field.dateFormat],
  () => {
    // Skip if this is an internal update (from user input)
    if (isInternalUpdate.value) {
      return
    }

    // Use modelValue if available, otherwise use defaultValue
    let val = props.modelValue != null ? String(props.modelValue) : ''

    // If no modelValue, check for defaultValue
    if (!val && props.field.defaultValue) {
      val = String(props.field.defaultValue)
    }

    // Sanitize: reject any invalid values containing MIN_SAFE_INTEGER or other invalid data
    if (val && (val.includes('-9007199254740991') || val.includes('MIN_SAFE_INTEGER'))) {
      val = ''
      // Emit empty value to clean up the corrupted data
      handleInput('', false)
    }

    if (val && val.trim() !== '') {
      if (props.field.dateFieldType === 'input') {
        // For text input mode, just set the formatted value
        textInputValue.value = val
      } else if (props.field.dateFieldType === 'dropdown') {
        const parsed = parseDateString(val, (props.field.dateFormat as string) || 'MM/DD/YYYY')
        // Only set if parsed values are valid strings (not empty and not invalid)
        selectedDay.value = parsed.day && parsed.day !== '' && parsed.day !== '0' ? parsed.day : ''
        selectedMonth.value =
          parsed.month && parsed.month !== '' && parsed.month !== '0' ? parsed.month : ''
        selectedYear.value =
          parsed.year && parsed.year !== '' && !parsed.year.includes('-') ? parsed.year : ''
      }
    } else {
      if (props.field.dateFieldType === 'input') {
        textInputValue.value = ''
      } else if (props.field.dateFieldType === 'dropdown') {
        selectedDay.value = ''
        selectedMonth.value = ''
        selectedYear.value = ''
      }
    }
  },
  { immediate: true },
)

// Watch for default value changes to update inputs when modelValue is empty
watch(
  () => props.field.defaultValue,
  (newDefaultValue) => {
    // Only apply default value if modelValue is empty or undefined
    const currentValue = props.modelValue != null ? String(props.modelValue) : ''
    if (currentValue) {
      return // Don't override user's current value
    }

    if (newDefaultValue && typeof newDefaultValue === 'string') {
      if (props.field.dateFieldType === 'input') {
        // For text input mode, just set the formatted value
        textInputValue.value = newDefaultValue
      } else if (props.field.dateFieldType === 'dropdown') {
        const parsed = parseDateString(
          newDefaultValue,
          (props.field.dateFormat as string) || 'MM/DD/YYYY',
        )
        selectedDay.value = parsed.day || ''
        selectedMonth.value = parsed.month || ''
        selectedYear.value = parsed.year || ''
      }
    } else {
      // Clear values if no default value
      if (props.field.dateFieldType === 'input') {
        textInputValue.value = ''
      } else if (props.field.dateFieldType === 'dropdown') {
        selectedDay.value = ''
        selectedMonth.value = ''
        selectedYear.value = ''
      }
    }
  },
  { immediate: false },
)

const fieldID = computed(() => {
  return `ivyforms-field__date-input_${props.field.formId || ''}_${props.field.fieldIndex || ''}`
})

// Ensure fields start with empty value if no modelValue or defaultValue
onMounted(() => {
  const hasModelValue = props.modelValue != null && String(props.modelValue) !== ''
  const hasDefaultValue =
    props.field.defaultValue != null && String(props.field.defaultValue) !== ''

  // If no value exists, emit empty string to ensure form doesn't try to validate placeholders or undefined values
  if (!hasModelValue && !hasDefaultValue) {
    if (props.field.dateFieldType === 'input' || props.field.dateFieldType === 'dropdown') {
      handleInput('', false)
    }
  }
})
</script>

<style lang="scss">
.ivyforms-field__date {
  &__sublabel {
    color: var(--map-base-text-0);
  }

  &--is-readonly {
    opacity: 0.6;
    pointer-events: none;
  }

  .ivyforms-field__date-picker,
  .ivyforms-field__date-input,
  .ivyforms-field__date-day,
  .ivyforms-field__date-month,
  .ivyforms-field__date-year {
    width: 100%;
  }
}
</style>
