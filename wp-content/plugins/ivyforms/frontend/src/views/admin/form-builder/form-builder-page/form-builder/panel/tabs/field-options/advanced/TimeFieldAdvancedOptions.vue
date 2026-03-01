<template>
  <template v-if="field.type === 'time'">
    <!-- Time Picker Type -->
    <IvyFormItem v-if="isTimePicker" :label="getLabel('default_value')">
      <IvyTimePicker
        v-model="timePickerValue"
        :time-format="field.timeFormat"
        :editable="false"
        :clearable="true"
        secondary
      />
    </IvyFormItem>

    <!-- Input Type -->

    <div v-else-if="isInput" class="ivyforms-flex ivyforms-gap-12 ivyforms-width-100">
      <div class="ivyforms-flex ivyforms-flex-1 ivyforms-flex-direction-column">
        <IvyFormItem :label="getLabel('default_value')">
          <IvyTextInput v-model="timeValue" @input="handleTimeChange" />
        </IvyFormItem>
      </div>
      <div
        v-if="showAmPmSelect"
        class="ivyforms-flex ivyforms-flex-1 ivyforms-flex-direction-column ivyforms-mt-26"
      >
        <IvySelectInput
          v-model="amPmValueInput"
          class="ivyforms-ampm-select"
          @update:model-value="handleAmPmChangeInput"
        >
          <IvySelectOption value="" :label="getLabel('not_selected')">
            {{ getLabel('not_selected') }}
          </IvySelectOption>
          <IvySelectOption value="AM" :label="getLabel('am')">
            {{ getLabel('am') }}
          </IvySelectOption>
          <IvySelectOption value="PM" :label="getLabel('pm')">
            {{ getLabel('pm') }}
          </IvySelectOption>
        </IvySelectInput>
      </div>
    </div>

    <!-- Dropdown Type -->
    <IvyFormItem v-else-if="isDropdown" :label="getLabel('default_value')">
      <div class="ivyforms-flex ivyforms-gap-12 ivyforms-width-100">
        <div class="ivyforms-flex ivyforms-flex-1 ivyforms-flex-direction-column">
          <div class="medium-14 ivyforms-mb-6 ivyforms-field-time__sublabel">
            {{ getLabel('hours') }}
          </div>
          <IvySelectInput
            v-model="dropdownHoursValue"
            class="ivyforms-hours-select"
            @update:model-value="handleDropdownChange"
          >
            <IvySelectOption
              v-for="hourOption in hourOptions"
              :key="hourOption.value"
              :label="hourOption.label"
              :value="hourOption.value"
            >
              {{ hourOption.label }}
            </IvySelectOption>
          </IvySelectInput>
        </div>
        <div class="ivyforms-flex ivyforms-flex-1 ivyforms-flex-direction-column">
          <div class="medium-14 ivyforms-mb-6 ivyforms-field-time__sublabel">
            {{ getLabel('minutes') }}
          </div>
          <IvySelectInput
            v-model="dropdownMinutesValue"
            class="ivyforms-minutes-select"
            @update:model-value="handleDropdownChange"
          >
            <IvySelectOption
              v-for="minuteOption in minuteOptions"
              :key="minuteOption.value"
              :label="minuteOption.label"
              :value="minuteOption.value"
            >
              {{ minuteOption.label }}
            </IvySelectOption>
          </IvySelectInput>
        </div>
        <div
          v-if="showAmPmSelect"
          class="ivyforms-flex ivyforms-flex-1 ivyforms-flex-direction-column"
        >
          <div class="medium-14 ivyforms-mb-6 ivyforms-field-time__sublabel">
            {{ getLabel('am_pm') }}
          </div>
          <IvySelectInput
            v-model="amPmValueDropdown"
            class="ivyforms-ampm-select"
            @update:model-value="handleAmPmChangeDropdown"
          >
            <IvySelectOption value="" :label="getLabel('not_selected')">
              {{ getLabel('not_selected') }}
            </IvySelectOption>
            <IvySelectOption value="AM" :label="getLabel('am')">
              {{ getLabel('am') }}
            </IvySelectOption>
            <IvySelectOption value="PM" :label="getLabel('pm')">
              {{ getLabel('pm') }}
            </IvySelectOption>
          </IvySelectInput>
        </div>
      </div>
    </IvyFormItem>
  </template>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import IvyTimePicker from '@/views/_components/timepicker/IvyTimePicker.vue'
import IvyFormItem from '@/views/_components/form/IvyFormItem.vue'
import { useTimeField } from '@/composables/useTimeField'
import type { Field } from '@/types'

interface Props {
  field: Field
  updateField: (key: string, value: unknown) => void
  getLabel: (key: string) => string
}

const props = defineProps<Props>()

// Constants
const TIME_FIELD_TYPES = {
  TIME_PICKER: 'time-picker',
  INPUT: 'input',
  DROPDOWN: 'dropdown',
} as const

const TIME_FORMATS = {
  AMPM: 'ampm',
  HOURS_24: '24h',
} as const

const AM_PM_VALUES = {
  AM: 'AM',
  PM: 'PM',
  EMPTY: '',
} as const

type AmPmValue = (typeof AM_PM_VALUES)[keyof typeof AM_PM_VALUES]

const isTimePicker = computed(() => props.field.timeFieldType === TIME_FIELD_TYPES.TIME_PICKER)
const isInput = computed(() => props.field.timeFieldType === TIME_FIELD_TYPES.INPUT)
const isDropdown = computed(() => props.field.timeFieldType === TIME_FIELD_TYPES.DROPDOWN)

// Use normalizedTimeFormat for AM/PM logic
const showAmPmSelect = computed(() => {
  return props.field.timeFormat === TIME_FORMATS.AMPM
})

const amPmValueInput = ref<AmPmValue>(AM_PM_VALUES.EMPTY)
const amPmValueDropdown = ref<AmPmValue>(AM_PM_VALUES.EMPTY)
const timeValue = ref<string>('')
const dropdownHoursValue = ref('')
const dropdownMinutesValue = ref('')

const hourOptions = computed(() => {
  const notSelected = { value: '', label: props.getLabel('not_selected') }
  if (props.field.timeFormat === TIME_FORMATS.AMPM) {
    const options = Array.from({ length: 12 }, (_, i) => {
      const value = String(i + 1).padStart(2, '0')
      return { value, label: value }
    })
    return [notSelected, ...options]
  } else {
    const options = Array.from({ length: 24 }, (_, i) => {
      const value = String(i).padStart(2, '0')
      return { value, label: value }
    })
    return [notSelected, ...options]
  }
})

const minuteOptions = [
  { value: '', label: props.getLabel('not_selected') },
  ...Array.from({ length: 60 }, (_, i) => {
    const value = String(i).padStart(2, '0')
    return { value, label: value }
  }),
]

const { parseTimeString, formatTimeString, getAmPmFromHours } = useTimeField()

watch(
  () => props.field.defaultValue,
  (newVal) => {
    if (isDropdown.value && typeof newVal === 'string') {
      const parsed = parseTimeString(newVal)
      dropdownHoursValue.value = parsed.hours
      dropdownMinutesValue.value = parsed.minutes
      amPmValueDropdown.value = parsed.amPm as AmPmValue
    }
    if (isInput.value && typeof newVal === 'string') {
      const match = newVal.match(/\b(AM|PM)\b/i)
      timeValue.value = newVal.replace(/\b(AM|PM)\b/i, '').trim()
      if (match && match[1]) {
        amPmValueInput.value = match[1].toUpperCase() as AmPmValue
      }
    }
  },
  { immediate: true },
)

// Update defaultValue when input changes
const handleTimeChange = (val: string) => {
  const ampm = amPmValueInput.value
  const newValue = ampm ? `${val.trim()} ${ampm}` : val.trim()
  props.updateField('defaultValue', newValue)
}

// Update defaultValue when AM/PM changes
const handleAmPmChangeInput = (value: 'AM' | 'PM' | '') => {
  const timePart = timeValue.value.trim()
  const newValue = value ? `${timePart} ${value}` : timePart
  props.updateField('defaultValue', newValue)
}

const handleAmPmChangeDropdown = (value: 'AM' | 'PM' | '') => {
  amPmValueDropdown.value = value
  const formatted = formatTimeString(
    dropdownHoursValue.value,
    dropdownMinutesValue.value || '',
    value,
  )
  props.updateField('defaultValue', formatted)
}

// Dropdown mode - update defaultValue on change
const handleDropdownChange = () => {
  const formatted = formatTimeString(
    dropdownHoursValue.value,
    dropdownMinutesValue.value,
    amPmValueDropdown.value,
  )
  props.updateField('defaultValue', formatted)
}

const timePickerValue = ref(props.field.defaultValue || '')

watch(timePickerValue, (newVal) => {
  if (props.field.timeFormat === TIME_FORMATS.AMPM && newVal) {
    const [hoursStr, minutes] = newVal.split(':')
    const hours = parseInt(hoursStr, 10)
    const ampm = getAmPmFromHours(hoursStr)
    const hours12 = hours % 12 || 12
    const formatted = formatTimeString(String(hours12).padStart(2, '0'), minutes, ampm)
    props.updateField('defaultValue', formatted)
  } else {
    props.updateField('defaultValue', newVal)
  }
})
</script>
<style scoped lang="scss">
.ivyforms-time-advanced-options {
  :deep(.ivyforms-form-item),
  :deep(.el-form-item) {
    margin-bottom: 0;
    gap: var(--Spacing-xs, 6px);
  }
}

.ivyforms-field-time__sublabel {
  color: var(--map-base-text-0);
}
</style>
