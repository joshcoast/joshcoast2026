<template>
  <!-- Date Picker Type -->
  <IvyFormItem v-if="isPickerType" :label="getLabel('default_value')">
    <IvyDatePicker
      v-model="datePickerValue"
      :format="displayFormat"
      value-format="YYYY-MM-DD"
      icon-start="calendar-dot"
      clearable
      secondary
    />
  </IvyFormItem>

  <!-- Input Type (manual entry) -->
  <IvyFormItem v-else-if="isInputType" :label="getLabel('default_value')">
    <IvyTextInput
      v-model="inputTextValue"
      :placeholder="displayFormat"
      type="text"
      @blur="handleInputTextBlur"
    />
  </IvyFormItem>

  <!-- Dropdown Type -->
  <IvyFormItem v-else-if="isDropdownType" :label="getLabel('default_value')">
    <div class="ivyforms-flex ivyforms-gap-12 ivyforms-width-100">
      <div
        v-for="fieldConfig in dateFieldsConfig"
        :key="fieldConfig.type"
        class="ivyforms-flex ivyforms-flex-1 ivyforms-flex-direction-column"
      >
        <div class="medium-14 ivyforms-mb-6 ivyforms-field-date__sublabel">
          {{ getLabel(fieldConfig.type) }}
        </div>
        <IvySelectInput
          v-if="fieldConfig.type === 'day'"
          v-model="dropdownDayValue"
          class="ivyforms-day-select"
          @update:model-value="handleDropdownChange"
        >
          <IvySelectOption
            v-for="dayOption in dayOptions"
            :key="dayOption.value"
            :label="dayOption.label"
            :value="dayOption.value"
          >
            {{ dayOption.label }}
          </IvySelectOption>
        </IvySelectInput>
        <IvySelectInput
          v-else-if="fieldConfig.type === 'month'"
          v-model="dropdownMonthValue"
          class="ivyforms-month-select"
          @update:model-value="handleDropdownChange"
        >
          <IvySelectOption
            v-for="monthOption in monthOptions"
            :key="monthOption.value"
            :label="monthOption.label"
            :value="monthOption.value"
          >
            {{ monthOption.label }}
          </IvySelectOption>
        </IvySelectInput>
        <IvySelectInput
          v-else-if="fieldConfig.type === 'year'"
          v-model="dropdownYearValue"
          class="ivyforms-year-select"
          @update:model-value="handleDropdownChange"
        >
          <IvySelectOption
            v-for="yearOption in yearOptions"
            :key="yearOption.value"
            :label="yearOption.label"
            :value="yearOption.value"
          >
            {{ yearOption.label }}
          </IvySelectOption>
        </IvySelectInput>
      </div>
    </div>
  </IvyFormItem>

  <IvyDivider />

  <div class="ivyforms-flex ivyforms-flex-direction-row ivyforms-gap-16">
    <!-- Min Value -->
    <IvyFormItem :label="getLabel('min_date')" class="ivyforms-flex-50">
      <IvyDatePicker
        v-model="minValueDate"
        :format="displayFormat"
        value-format="YYYY-MM-DD"
        :clearable="true"
        secondary
      />
    </IvyFormItem>

    <!-- Max Value -->
    <IvyFormItem :label="getLabel('max_date')" class="ivyforms-flex-50">
      <IvyDatePicker
        v-model="maxValueDate"
        :format="displayFormat"
        value-format="YYYY-MM-DD"
        :clearable="true"
        secondary
      />
    </IvyFormItem>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, watch, nextTick } from 'vue'
import IvyDatePicker from '@/views/_components/datepicker/IvyDatePicker.vue'
import IvyFormItem from '@/views/_components/form/IvyFormItem.vue'
import IvySelectInput from '@/views/_components/input/IvySelectInput.vue'
import IvySelectOption from '@/views/_components/input/IvySelectOption.vue'
import IvyTextInput from '@/views/_components/input/IvyTextInput.vue'
import IvyDivider from '@/views/_components/divider/IvyDivider.vue'
import { useDateField } from '@/composables/useDateField'
import type { Field } from '@/types'

interface Props {
  field: Field
  updateField: (key: string, value: unknown) => void
  getLabel: (key: string) => string
}

const props = defineProps<Props>()

const isPickerType = computed(() => props.field.dateFieldType === 'picker')
const isInputType = computed(() => props.field.dateFieldType === 'input')
const isDropdownType = computed(() => props.field.dateFieldType === 'dropdown')

const {
  parseDateString,
  formatDateString,
  getDaysArray,
  getMonthsArray,
  getYearsArray,
  toISOFormat,
  fromISOFormat,
} = useDateField()

// Display format (string) for the datepicker/input placeholders
const displayFormat = computed(() => String((props.field.dateFormat as string) || 'MM/DD/YYYY'))

// Date picker value (store uses display format; datepicker uses ISO)
const datePickerValue = computed({
  get: () =>
    toISOFormat(
      (props.field.defaultValue as string) || '',
      (props.field.dateFormat as string) || 'MM/DD/YYYY',
    ),
  set: (value: string) => {
    props.updateField(
      'defaultValue',
      fromISOFormat(value || '', (props.field.dateFormat as string) || 'MM/DD/YYYY'),
    )
  },
})

// Input mode value - single text input for formatted date
const inputTextValue = ref<string>('')

// Dropdown mode values
const dropdownDayValue = ref('')
const dropdownMonthValue = ref('')
const dropdownYearValue = ref('')

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

// Initialize values from default value
watch(
  () => props.field.defaultValue,
  (newValue) => {
    // Skip if this is an internal update (from user input)
    if (isInternalUpdate.value) {
      return
    }

    if (newValue && typeof newValue === 'string') {
      inputTextValue.value = newValue
      const parsed = parseDateString(newValue, (props.field.dateFormat as string) || 'MM/DD/YYYY')
      dropdownDayValue.value = parsed.day
      dropdownMonthValue.value = parsed.month
      dropdownYearValue.value = parsed.year
    } else {
      inputTextValue.value = ''
      dropdownDayValue.value = ''
      dropdownMonthValue.value = ''
      dropdownYearValue.value = ''
    }
  },
  { immediate: true },
)

// Handle input text blur - validate and format the date
const handleInputTextBlur = () => {
  const value = inputTextValue.value.trim()

  if (!value) {
    isInternalUpdate.value = true
    props.updateField('defaultValue', '')
    nextTick(() => {
      isInternalUpdate.value = false
    })
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
      // Format the date properly
      const formatted = formatDateString(
        String(dayNum),
        String(monthNum),
        String(yearNum),
        (props.field.dateFormat as string) || 'MM/DD/YYYY',
      )

      isInternalUpdate.value = true
      inputTextValue.value = formatted
      props.updateField('defaultValue', formatted)
      nextTick(() => {
        isInternalUpdate.value = false
      })
    } else {
      // Invalid date - keep the input as is or clear it
      inputTextValue.value = value
    }
  } else {
    // Could not parse - keep the input as is
    inputTextValue.value = value
  }
}

// Handle dropdown change
const handleDropdownChange = () => {
  nextTick(() => {
    // Only update when all three values are selected or all are empty
    const hasAllValues =
      dropdownDayValue.value && dropdownMonthValue.value && dropdownYearValue.value
    const hasNoValues =
      !dropdownDayValue.value && !dropdownMonthValue.value && !dropdownYearValue.value

    if (hasAllValues) {
      isInternalUpdate.value = true
      const formatted = formatDateString(
        dropdownDayValue.value,
        dropdownMonthValue.value,
        dropdownYearValue.value,
        (props.field.dateFormat as string) || 'MM/DD/YYYY',
      )
      props.updateField('defaultValue', formatted)
      nextTick(() => {
        isInternalUpdate.value = false
      })
    } else if (hasNoValues) {
      isInternalUpdate.value = true
      props.updateField('defaultValue', '')
      nextTick(() => {
        isInternalUpdate.value = false
      })
    }
    // Don't update for partial states - just keep the values locally
  })
}

// Options for dropdowns
const dayOptions = computed(() => [{ label: '--', value: '' }, ...getDaysArray()])

const monthOptions = computed(() => [{ label: '--', value: '' }, ...getMonthsArray()])

const yearOptions = computed(() => {
  const minYear = (props.field.minYear as number) || new Date().getFullYear() - 100
  const maxYear = (props.field.maxYear as number) || new Date().getFullYear() + 100
  return [{ label: '--', value: '' }, ...getYearsArray(minYear, maxYear)]
})

// Min and max date values
const minValueDate = computed({
  get: () => {
    const value = props.field.minDateValue
    return toISOFormat(
      value ? String(value) : '',
      (props.field.dateFormat as string) || 'MM/DD/YYYY',
    )
  },
  set: (value: string) => {
    props.updateField(
      'minDateValue',
      fromISOFormat(value || '', (props.field.dateFormat as string) || 'MM/DD/YYYY'),
    )
  },
})

const maxValueDate = computed({
  get: () => {
    const value = props.field.maxDateValue
    return toISOFormat(
      value ? String(value) : '',
      (props.field.dateFormat as string) || 'MM/DD/YYYY',
    )
  },
  set: (value: string) => {
    props.updateField(
      'maxDateValue',
      fromISOFormat(value || '', (props.field.dateFormat as string) || 'MM/DD/YYYY'),
    )
  },
})
</script>

<style scoped lang="scss">
.ivyforms-field-date__sublabel {
  color: var(--map-base-text-0);
}

.ivyforms-flex-50 {
  flex: 1 1 50%;
}
</style>
