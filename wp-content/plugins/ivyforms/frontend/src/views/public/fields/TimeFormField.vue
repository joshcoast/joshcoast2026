<template>
  <div
    :class="[
      'ivyforms-field',
      'ivyforms-field__time',
      'ivyforms-field__time_' + field.id,
      field.cssClasses,
      {
        'ivyforms-field__time--is-readonly': !!field.readOnly,
      },
    ]"
  >
    <IvyFormItem
      v-if="field.timeFieldType === 'time-picker'"
      :label="field.hideLabel ? '' : field.label"
      :required="field.required"
      :error="error"
      :prop="field.type + '_' + field.fieldIndex"
      :show-info="!!field.description"
      :info-text="field.description || ''"
      :show-info-icon="false"
      :label-position="field.labelPosition"
    >
      <!-- time-picker mode -->
      <IvyTimePicker
        :id="fieldID"
        v-model="localModelValue"
        :aria-label="field.label"
        class="ivyforms-field__time-picker"
        :time-format="field.timeFormat"
        :placeholder="field.placeholder"
        :disabled="disabled || field.readOnly"
        :readonly="field.readOnly"
        :clearable="true"
      />
    </IvyFormItem>

    <!-- free text input mode -->
    <IvyFormItem
      v-if="field.timeFieldType === 'input'"
      :label="field.hideLabel ? '' : field.label"
      :required="field.required"
      :error="error"
      :prop="field.type + '_' + field.fieldIndex"
      :show-info="!!field.description"
      :info-text="field.description || ''"
      :show-info-icon="false"
      :label-position="field.labelPosition"
    >
      <div class="ivyforms-flex ivyforms-gap-12 ivyforms-width-100">
        <div :class="['ivyforms-flex', 'ivyforms-flex-1', 'ivyforms-flex-direction-column']">
          <IvyTextInput
            :id="fieldID"
            v-model="localModelValue"
            :aria-label="field.label"
            class="ivyforms-field__time-input"
            :class="{ 'has-error': error && !localModelValue }"
            type="text"
            :placeholder="field.placeholder"
            :disabled="disabled || field.readOnly"
            :readonly="field.readOnly"
            :required="field.required"
          />
        </div>
        <div
          v-if="field.timeFormat === 'ampm'"
          class="ivyforms-flex ivyforms-flex-1 ivyforms-flex-direction-column"
        >
          <IvySelectInput
            id="meridiem-input-select"
            v-model="selectedInputMeridiem"
            aria-label="getLabel('am_pm')"
            :error="!!(error && !selectedInputMeridiem)"
            :placeholder="getLabel('am_pm')"
            :disabled="disabled || field.readOnly"
            field-type="time"
            @update:model-value="onInputMeridiemChange"
          >
            <IvySelectOption value="" :label="getLabel('not_selected')" field-type="time">
              {{ getLabel('not_selected') }}
            </IvySelectOption>
            <IvySelectOption
              v-for="meridiem in meridiemOptions"
              :key="meridiem.value"
              :label="meridiem.label"
              :value="meridiem.value"
              field-type="time"
            >
              {{ meridiem.label }}
            </IvySelectOption>
          </IvySelectInput>
        </div>
      </div>
    </IvyFormItem>
    <IvyFormItem
      v-if="field.timeFieldType === 'dropdown'"
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
      <!-- dropdown mode -->
      <div class="ivyforms-flex ivyforms-gap-12 ivyforms-width-100">
        <div class="ivyforms-flex ivyforms-flex-1 ivyforms-flex-direction-column">
          <label for="hours-select" class="medium-14 ivyforms-mb-6 ivyforms-field__time__sublabel">
            {{ getLabel('hours') }}
          </label>
          <IvySelectInput
            id="hours-select"
            v-model="selectedHour"
            class="ivyforms-field__time-hour"
            :error="!!(error && !selectedHour)"
            placeholder="&#45;&#45;"
            :disabled="disabled || field.readOnly"
            field-type="time"
            @update:model-value="onPartChange"
          >
            <IvySelectOption
              v-for="h in hourOptions"
              :key="h.value"
              :label="h.label"
              :value="h.value"
              field-type="time"
            />
          </IvySelectInput>
        </div>

        <div class="ivyforms-flex ivyforms-flex-1 ivyforms-flex-direction-column">
          <label
            for="minutes-select"
            class="medium-14 ivyforms-mb-6 ivyforms-field__time__sublabel"
          >
            {{ getLabel('minutes') }}
          </label>
          <IvySelectInput
            id="minutes-select"
            v-model="selectedMinute"
            class="ivyforms-field__time-minute"
            :error="!!(error && !selectedMinute)"
            placeholder="&#45;&#45;"
            :disabled="disabled || field.readOnly"
            field-type="time"
            @update:model-value="onPartChange"
          >
            <IvySelectOption
              v-for="m in minuteOptions"
              :key="m.value"
              :label="m.label"
              :value="m.value"
              field-type="time"
            />
          </IvySelectInput>
        </div>

        <div
          v-if="field.timeFormat === 'ampm'"
          class="ivyforms-flex ivyforms-flex-1 ivyforms-flex-direction-column ivyforms-mt-26"
        >
          <IvySelectInput
            id="meridiem-select"
            v-model="selectedDropdownMeridiem"
            :error="!!(error && !selectedDropdownMeridiem)"
            aria-label="getLabel('am_pm')"
            :placeholder="getLabel('am_pm')"
            :disabled="disabled || field.readOnly"
            field-type="time"
            @update:model-value="onDropdownMeridiemChange"
          >
            <IvySelectOption value="" :label="getLabel('not_selected')" field-type="time">
              {{ getLabel('not_selected') }}
            </IvySelectOption>
            <IvySelectOption
              v-for="meridiem in meridiemOptions"
              :key="meridiem.value"
              :label="meridiem.label"
              :value="meridiem.value"
              field-type="time"
            >
              {{ meridiem.label }}
            </IvySelectOption>
          </IvySelectInput>
        </div>
      </div>
    </IvyFormItem>
  </div>
</template>

<script setup lang="ts">
import { computed, inject, ref, watch } from 'vue'
import { useLabels } from '@/composables/useLabels'
import { useTimeField } from '@/composables/useTimeField'
import type { Field } from '@/types/field'
import IvyFormItem from '@/views/_components/form/IvyFormItem.vue'

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
const { parseTimeString } = useTimeField()
const pad2 = (number: number): string => (number < 10 ? '0' + number : '' + number)
const parsedDefaultValue = computed(() => parseTimeString(props.field.defaultValue || ''))
const selectedHour = ref<string>(parsedDefaultValue.value.hours || '')
const selectedMinute = ref<string>(parsedDefaultValue.value.minutes || '')
const handleInput = (value: string | null | undefined, shouldClearError: boolean = true) => {
  emit('update:modelValue', value ?? '')
  if (shouldClearError) {
    clearFieldError(fieldKey.value)
  }
}
const selectedDropdownMeridiem = ref<'AM' | 'PM' | ''>(
  props.field.timeFieldType === 'dropdown'
    ? (parsedDefaultValue.value.amPm as 'AM' | 'PM' | '')
    : '',
)
const selectedInputMeridiem = ref<'AM' | 'PM' | ''>(
  props.field.timeFieldType === 'input' ? (parsedDefaultValue.value.amPm as 'AM' | 'PM' | '') : '',
)
const inputTime12h = ref<string>('')
const meridiemOptions = computed(() => [
  { label: getLabel('am'), value: 'AM' },
  { label: getLabel('pm'), value: 'PM' },
])
const localModelValue = computed({
  get() {
    if (props.field.timeFieldType === 'input' && props.field.timeFormat === 'ampm') {
      return inputTime12h.value
    }
    if (
      props.field.timeFieldType === 'time-picker' &&
      props.field.timeFormat === 'ampm' &&
      (!props.modelValue || props.modelValue === '')
    ) {
      const parsed = parseTimeString(props.field.defaultValue || '')
      if (parsed.hours && parsed.minutes && parsed.amPm) {
        return `${pad2(Number(parsed.hours))}:${pad2(Number(parsed.minutes))} ${parsed.amPm}`
      }
    }
    return props.modelValue != null ? String(props.modelValue) : ''
  },
  set(timeValue: string) {
    if (props.field.timeFieldType === 'input' && props.field.timeFormat === 'ampm') {
      inputTime12h.value = timeValue
      if (!timeValue || !selectedInputMeridiem.value) {
        handleInput('', true)
        return
      }
      const timeMatch = timeValue.match(/^(\d{1,2}):(\d{2})$/)
      if (timeMatch && selectedInputMeridiem.value) {
        const hour12 = timeMatch[1]
        const minutes = timeMatch[2]
        const meridiem = selectedInputMeridiem.value
        const timeString = `${hour12}:${minutes} ${meridiem}`
        handleInput(timeString, true)
      } else {
        handleInput('__PARTIAL__', false)
      }
    } else if (props.field.timeFieldType === 'time-picker') {
      if (timeValue) {
        handleInput(timeValue, true)
      } else {
        handleInput('', true)
      }
    } else {
      handleInput(timeValue)
    }
  },
})

function onInputMeridiemChange(value: 'AM' | 'PM' | '') {
  selectedInputMeridiem.value = value
  if (props.field.timeFieldType === 'input' && inputTime12h.value) {
    const timeMatch = inputTime12h.value.match(/^(\d{1,2}):(\d{2})$/)
    if (timeMatch && value) {
      const hour12 = timeMatch[1]
      const minutes = timeMatch[2]
      const timeString = `${hour12}:${minutes} ${value}`
      handleInput(timeString, true)
    } else {
      handleInput('__PARTIAL__', false)
    }
  }
}
function onDropdownMeridiemChange(value: 'AM' | 'PM' | '') {
  selectedDropdownMeridiem.value = value
  emitFromParts()
}
const hourOptions = computed(() => {
  if (props.field.timeFormat === 'ampm') {
    return Array.from({ length: 12 }, (_, i) => {
      const value = pad2(i + 1)
      return { value, label: value }
    })
  } else {
    return Array.from({ length: 24 }, (_, i) => {
      const value = pad2(i)
      return { value, label: value }
    })
  }
})
const minuteOptions = computed(() =>
  Array.from({ length: 60 }, (_, i) => {
    const value = pad2(i)
    return { value, label: value }
  }),
)
function emitFromParts() {
  const hasAnyValue = !!(
    selectedHour.value ||
    selectedMinute.value ||
    (props.field.timeFormat === 'ampm' && selectedDropdownMeridiem.value)
  )
  const hasAllValues =
    selectedHour.value &&
    selectedMinute.value &&
    (props.field.timeFormat === '24h' || selectedDropdownMeridiem.value)
  if (!hasAnyValue) {
    handleInput('', true)
    return
  }
  if (!hasAllValues) {
    handleInput('__PARTIAL__', false)
    return
  }
  if (props.field.timeFormat === 'ampm') {
    const hour12 = parseInt(selectedHour.value, 10)
    const minute = selectedMinute.value
    const meridiem = selectedDropdownMeridiem.value
    const timeValue = pad2(hour12) + ':' + minute + ' ' + meridiem
    handleInput(timeValue, true)
    return
  }
  const timeValue = selectedHour.value + ':' + selectedMinute.value
  handleInput(timeValue, true)
}
function onPartChange() {
  emitFromParts()
}
watch(
  () => [props.modelValue, props.field.timeFormat],
  () => {
    const val = props.modelValue != null ? String(props.modelValue) : ''
    if (props.field.timeFieldType === 'input' && props.field.timeFormat === 'ampm') {
      if (val && val !== '__PARTIAL__') {
        const match = val.match(/^(.*?)(?:\s*(AM|PM|am|pm))?$/)
        inputTime12h.value = match ? match[1].trim() : ''
        selectedInputMeridiem.value =
          match && match[2] ? (match[2].toUpperCase() as 'AM' | 'PM') : ''
      } else if (!val || val.trim() === '') {
        inputTime12h.value = ''
        selectedInputMeridiem.value = ''
      }
    } else if (props.field.timeFieldType === 'time-picker') {
      const ampmRegex = /^(?:0?[1-9]|1[0-2]):[0-5]\d\s?(AM|PM|am|pm)$/
      const twentyFourRegex = /^(?:[01]\d|2[0-3]):[0-5]\d$/
      if (
        val &&
        val !== '__PARTIAL__' &&
        !twentyFourRegex.test(val) &&
        !(props.field.timeFormat === 'ampm' && ampmRegex.test(val))
      ) {
        handleInput('', true)
      }
    } else if (props.field.timeFieldType === 'dropdown') {
      if (val !== '__PARTIAL__') {
        const parsed = parseTimeString(val)
        selectedHour.value = parsed.hours || ''
        selectedMinute.value = parsed.minutes || ''
        selectedDropdownMeridiem.value = parsed.amPm as 'AM' | 'PM' | ''
        emitFromParts()
      }
    }
  },
  { immediate: true },
)
const fieldID = computed(() => {
  return `ivyforms-field__time-input_${props.field.formId || ''}_${props.field.fieldIndex || ''}`
})
</script>

<style lang="scss">
.ivyforms-field__time {
  &__asterisk {
    color: var(--map-status-error-fill-0);
  }

  &--is-readonly {
    opacity: 0.7;
    .el-select__wrapper,
    .el-select__wrapper:hover,
    .el-select__wrapper.is-focus,
    .el-input__wrapper,
    .el-input__wrapper:hover,
    .el-input__wrapper.is-focus {
      border-color: var(--map-base-dusk-stroke--2) !important;
      box-shadow: none !important;
      border: 1px solid var(--map-base-dusk-stroke--2) !important;
    }
    .el-select:has(> .is-disabled),
    input[disabled] {
      cursor: not-allowed;
    }
  }
  &__sublabel {
    color: var(--map-base-text-0);
  }
}
</style>
