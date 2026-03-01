<template>
  <div class="ivyforms-field-time" :class="{ 'ivyforms-field-time--readonly': readOnly }">
    <!-- Time Picker or Input types (existing behavior) -->
    <template v-if="!isDropdownType">
      <!-- Text input mode -->
      <template v-if="timeFieldType === 'input'">
        <IvyFormItem
          :label="hideLabel ? '' : label"
          :required="required"
          :label-position="labelPosition"
        >
          <div class="ivyforms-flex ivyforms-gap-12 ivyforms-width-100">
            <div class="ivyforms-flex ivyforms-flex-1 ivyforms-flex-direction-column">
              <IvyTextInput
                :id="'time_' + index"
                :aria-label="label"
                readonly
                :placeholder="placeholder"
                :model-value="inputDisplayValue"
                type="text"
              />
            </div>
            <div
              v-if="field.timeFormat === 'ampm'"
              class="ivyforms-flex ivyforms-flex-1 ivyforms-flex-direction-column"
            >
              <IvySelectInput
                :model-value="amPmValue"
                :aria-label="getLabel('am_pm')"
                readonly
                :placeholder="getLabel('am_pm')"
                @mousedown.prevent
                @click.prevent
              />
            </div>
          </div>
          <div
            v-if="description"
            class="ivyforms-field-time__description regular-14 ivyforms-width-100"
          >
            {{ description }}
          </div>
        </IvyFormItem>
      </template>
      <!-- Time picker mode -->
      <template v-else>
        <IvyFormItem
          :label="hideLabel ? '' : label"
          :required="required"
          :label-position="labelPosition"
        >
          <IvyTimePicker
            :id="'time_' + index"
            :aria-label="label"
            :editable="false"
            :format="timeFormat === 'ampm' ? 'hh:mm A' : 'HH:mm'"
            :model-value="modelValue24h"
            :clearable="false"
            :time-format="timeFormat"
            :placeholder="placeholder"
            @click.stop.prevent
          />
          <div
            v-if="description"
            class="ivyforms-field-time__description regular-14 ivyforms-width-100"
          >
            {{ description }}
          </div>
        </IvyFormItem>
      </template>
    </template>

    <!-- Dropdown type (use select components) -->
    <template v-else>
      <IvyFormItem
        :label="hideLabel ? '' : label"
        :required="required"
        :label-position="labelPosition"
      >
        <div class="ivyforms-flex ivyforms-width-100 ivyforms-gap-12">
          <div class="ivyforms-flex ivyforms-flex-1 ivyforms-flex-direction-column">
            <div class="medium-14 ivyforms-mb-6 ivyforms-field-time__sublabel">
              {{ getLabel('hours') }}
            </div>
            <IvySelectInput
              :model-value="dropdownHoursValue"
              placeholder="--"
              readonly
              @mousedown.prevent
              @click.prevent
            />
          </div>
          <div class="ivyforms-flex ivyforms-flex-1 ivyforms-flex-direction-column">
            <div class="medium-14 ivyforms-mb-6 ivyforms-field-time__sublabel">
              {{ getLabel('minutes') }}
            </div>
            <IvySelectInput
              :model-value="dropdownMinutesValue"
              placeholder="--"
              readonly
              @mousedown.prevent
              @click.prevent
            />
          </div>
          <div
            v-if="field.timeFormat === 'ampm'"
            class="ivyforms-flex ivyforms-flex-1 ivyforms-flex-direction-column ivyforms-justify-content-end"
          >
            <IvySelectInput
              :model-value="dropdownAmPmValue"
              :aria-label="getLabel('am_pm')"
              :placeholder="getLabel('am_pm')"
              readonly
              @mousedown.prevent
              @click.prevent
            />
          </div>
        </div>
        <div
          v-if="description"
          class="ivyforms-field-time__description regular-14 ivyforms-width-100"
        >
          {{ description }}
        </div>
      </IvyFormItem>
    </template>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useFormBuilder } from '@/stores/useFormBuilder'
import { useLabels } from '@/composables/useLabels'
import { useTimeField } from '@/composables/useTimeField'
import IvySelectInput from '@/views/_components/input/IvySelectInput.vue'
import IvyTimePicker from '@/views/_components/timepicker/IvyTimePicker.vue'
import IvyTextInput from '@/views/_components/input/IvyTextInput.vue'
import IvyFormItem from '@/views/_components/form/IvyFormItem.vue'

const { getLabel } = useLabels()
const props = defineProps<{ fieldIndex: number }>()
const formBuilderStore = useFormBuilder()
const field = computed(() => formBuilderStore.fields.find((f) => f.fieldIndex === props.fieldIndex))
const label = computed(() => field.value?.label || '')
const placeholder = computed(() => field.value?.placeholder || '')
const required = computed(() => field.value?.required || false)
const hideLabel = computed(() => field.value?.hideLabel || false)
const readOnly = computed(() => field.value?.readOnly || false)
const description = computed(() => field.value?.description || '')
const index = computed(() => field.value?.fieldIndex ?? props.fieldIndex)
const modelValue = computed(() => field.value?.defaultValue || '')
const timeFieldType = computed(() => field.value?.timeFieldType || 'time-picker')
const timeFormat = computed(() => field.value?.timeFormat)
const labelPosition = computed(() =>
  field.value?.labelPosition === 'default' ? 'top' : field.value?.labelPosition || 'top',
)

const isDropdownType = computed(() => timeFieldType.value === 'dropdown')

const { parseTimeString, to24hFormat } = useTimeField()

const modelValue24h = computed(() => to24hFormat(modelValue.value))

// Parse default value to extract hours, minutes, and AM/PM
const parsedTime = computed(() => parseTimeString(modelValue.value))

const inputValue = computed(() => {
  if (!modelValue.value) return ''
  if (/^(AM|PM)$/i.test(modelValue.value.trim())) return ''
  const match = modelValue.value.match(/^(.*?)(?:\s*(AM|PM))?$/)
  return match ? match[1].trim() : modelValue.value
})

// For input mode - display value in 12h format when AM/PM is enabled
const inputDisplayValue = computed(() => {
  return inputValue.value
})

const previousAmPm = ref('')

const amPmValue = computed(() => {
  if (!modelValue.value) {
    return ''
  }
  const match = modelValue.value.match(/\b(AM|PM)\b/i)
  if (match && match[1]) {
    return match[1].toUpperCase()
  }
  return ''
})

watch(amPmValue, (newVal) => {
  previousAmPm.value = newVal
})

// For dropdown mode - extract hours based on format, including only-hours case
const dropdownHoursValue = computed(() => {
  if (!modelValue.value) return ''
  const hoursMatch = modelValue.value.match(/^(\d{2})$/)
  if (hoursMatch) {
    return hoursMatch[1]
  }
  const timeMatch = modelValue.value.match(/^(\d{2}):/)
  if (timeMatch) {
    return timeMatch[1]
  }
  return ''
})

const dropdownMinutesValue = computed(() => {
  if (!modelValue.value) return ''
  if (/^:([0-9]{2})$/.test(modelValue.value)) {
    const match = modelValue.value.match(/^:([0-9]{2})$/)
    return match ? match[1] : ''
  }
  return parsedTime.value.minutes
})

const dropdownAmPmValue = computed(() => {
  if (!modelValue.value) return ''
  if (/^(AM|PM)$/.test(modelValue.value)) {
    return modelValue.value
  }
  if (parsedTime.value.amPm) {
    return parsedTime.value.amPm
  }
  return ''
})
</script>

<style lang="scss" scoped>
.ivyforms-field-time {
  cursor: default;

  &--readonly {
    opacity: 0.6;
  }

  &__sublabel {
    color: var(--map-base-text-0);
  }

  &__description {
    color: var(--map-base-text-0);
    display: block;
    white-space: normal;
    overflow-wrap: anywhere;
    word-break: break-word;
    margin-top: 6px;
  }

  .ivyforms-form-item {
    cursor: default;
    margin-bottom: 0;
    :deep(.ivyforms-form-item__label) {
      display: flex;
      align-items: center;
      color: var(--map-base-text-0);
      font-size: 14px;
      font-style: normal;
      font-weight: 500;
      line-height: 20px;
      cursor: default !important;
    }
    :deep(.el-form-item__label) {
      cursor: default;
    }
    .ivyforms-input.el-input {
      :deep(.el-input__wrapper),
      :deep(.el-input__wrapper:hover),
      :deep(.el-input__wrapper.is-focus) {
        border-radius: var(--Radius-radius-md, 8px);
        border: 1px solid var(--map-base-dusk-stroke--2);
        background: transparent;
        box-shadow: none;
        padding: 0 12px;
        transition: none;
        cursor: default;
      }
      :deep(input) {
        border: none;
        background: transparent;
        cursor: default;
        box-shadow: none;
        &:focus {
          outline: none;
          border: 1px solid transparent;
          background: none;
          box-shadow: none;
        }
      }
    }
    :deep(.ivyforms-time-picker) {
      pointer-events: none;
    }
    :deep(.el-date-editor.el-input) {
      width: 100%;
      pointer-events: none;
      .el-input__prefix {
        display: none !important;
      }
      .el-input__wrapper {
        border-radius: var(--Radius-radius-md, 8px);
        border: 1px solid var(--map-base-dusk-stroke--2);
        background: transparent;
        box-shadow: none;
        padding: 0 12px;
        transition: none;
        cursor: default;
        height: 40px;
      }
      input.el-input__inner {
        border: none;
        background: transparent;
        cursor: default;
        box-shadow: none;
        padding: 0;
        font-size: 16px;
        line-height: 22px;
        pointer-events: none;
        &:focus {
          outline: none;
          border: 1px solid transparent;
          background: none;
          box-shadow: none;
        }
      }
    }
  }

  :deep(.ivyforms-form-item-select .el-select__wrapper),
  :deep(.ivyforms-form-item-select .el-select__wrapper:hover),
  :deep(.ivyforms-form-item-select .el-select__wrapper.is-hovering),
  :deep(.ivyforms-form-item-select .el-select__wrapper:focus),
  :deep(.ivyforms-form-item-select .el-select__wrapper.is-focused),
  :deep(.ivyforms-form-item-select .el-select__wrapper:active),
  :deep(.ivyforms-form-item-select .el-select__wrapper.is-active) {
    border: 1px solid var(--map-base-dusk-stroke--2);
  }
}
</style>
