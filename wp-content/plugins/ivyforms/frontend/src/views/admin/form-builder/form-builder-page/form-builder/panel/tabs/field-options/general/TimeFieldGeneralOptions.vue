<template>
  <div
    class="ivyforms-general-time-options ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-16"
  >
    <IvyFormItem
      v-if="field.timeFieldType === 'input' || field.timeFieldType === 'time-picker'"
      :label="getLabel('placeholder')"
      secondary
    >
      <IvyTextInput :model-value="field.placeholder" @input="updateField('placeholder', $event)" />
    </IvyFormItem>

    <IvyDivider v-if="field.timeFieldType === 'input' || field.timeFieldType === 'time-picker'" />

    <IvyFormItem :label="getLabel('time_field_type')">
      <IvySelectInput
        :model-value="field.timeFieldType"
        :placeholder="getLabel('select')"
        class="ivyforms-time-select"
        secondary
        @update:model-value="handleTimeFieldTypeChange"
      >
        <IvySelectOption value="time-picker" :label="getLabel('time_picker')" secondary>
          {{ getLabel('time_picker') }}
        </IvySelectOption>
        <IvySelectOption value="dropdown" :label="getLabel('dropdown')" secondary>
          {{ getLabel('time_dropdown') }}
        </IvySelectOption>
        <IvySelectOption value="input" :label="getLabel('input')" secondary>
          {{ getLabel('time_input') }}
        </IvySelectOption>
      </IvySelectInput>
    </IvyFormItem>

    <IvyDivider />

    <IvyFormItem :label="getLabel('time_format')">
      <div class="ivyforms-radio-group ivyforms-flex ivyforms-gap-24">
        <IvyRadio
          :model-value="field.timeFormat"
          value="24h"
          @change="updateField('timeFormat', '24h')"
        >
          {{ getLabel('24_hour') }}
        </IvyRadio>
        <IvyRadio
          :model-value="field.timeFormat"
          value="ampm"
          @change="updateField('timeFormat', 'ampm')"
        >
          {{ getLabel('am_pm') }}
        </IvyRadio>
      </div>
    </IvyFormItem>
  </div>
</template>

<script setup lang="ts">
import type { Field } from '@/types/field'
import { watch } from 'vue'
import IvyFormItem from '@/views/_components/form/IvyFormItem.vue'
import IvySelectInput from '@/views/_components/input/IvySelectInput.vue'
import IvySelectOption from '@/views/_components/input/IvySelectOption.vue'
import IvyTextInput from '@/views/_components/input/IvyTextInput.vue'
import IvyRadio from '@/views/_components/radio/IvyRadio.vue'
import { useTimeField } from '@/composables/useTimeField'

const { parseTimeString } = useTimeField()

interface Props {
  field: Field
  updateField: (key: string, value: unknown) => void
  getLabel: (key: string) => string
}

const props = defineProps<Props>()

const handleTimeFieldTypeChange = (value: string) => {
  props.updateField('timeFieldType', value)

  if (value === 'time-picker') {
    const parsed = parseTimeString(props.field.defaultValue as string)
    if (!parsed.hours || !parsed.minutes) {
      props.updateField('defaultValue', '')
    }
  }

  // Update label based on the selected time field type
  if (value === 'time-picker') {
    const pickerLabelText = props.getLabel('time_picker')
    props.updateField('label', pickerLabelText)
    props.updateField('pickerLabel', pickerLabelText)
  } else if (value === 'input') {
    const inputLabelText = props.getLabel('time_input')
    props.updateField('label', inputLabelText)
    props.updateField('inputLabel', inputLabelText)
  } else if (value === 'dropdown') {
    props.updateField('label', props.getLabel('time_dropdown'))
  }
}

// Watch for changes in time field type and update label accordingly
watch(
  () => props.field.timeFieldType,
  (newType) => {
    const currentLabel = props.field.label
    const defaultLabels = [
      props.getLabel('time_picker'),
      props.getLabel('time_input'),
      props.getLabel('time_dropdown'),
      '',
    ]
    if (defaultLabels.includes(currentLabel)) {
      if (newType === 'time-picker') {
        const pickerLabelText = props.getLabel('time_picker')
        props.updateField('label', pickerLabelText)
        props.updateField('pickerLabel', pickerLabelText)
      } else if (newType === 'input') {
        const inputLabelText = props.getLabel('time_input')
        props.updateField('label', inputLabelText)
        props.updateField('inputLabel', inputLabelText)
      } else if (newType === 'dropdown') {
        props.updateField('label', props.getLabel('time_dropdown'))
      }
    }
  },
  { immediate: true },
)

const { formatTimeString, getAmPmFromHours, to24hFormat } = useTimeField()

watch(
  () => props.field.timeFormat,
  (newFormat) => {
    if (newFormat === 'ampm') {
      const parsed = parseTimeString(props.field.defaultValue as string)
      if (!parsed.amPm && parsed.hours) {
        const hoursNum = parseInt(parsed.hours, 10)
        const amPm = getAmPmFromHours(parsed.hours)
        const hours12 = hoursNum % 12 || 12
        const formatted = formatTimeString(String(hours12).padStart(2, '0'), parsed.minutes, amPm)
        props.updateField('defaultValue', formatted)
      }
    } else if (newFormat === '24h') {
      const formatted = to24hFormat(props.field.defaultValue as string)
      props.updateField('defaultValue', formatted)
    }
  },
)
</script>

<style scoped lang="scss">
.ivyforms-general-time-options {
  :deep(.ivyforms-form-item),
  :deep(.el-form-item) {
    margin-bottom: 0;
    gap: var(--Spacing-xs, 6px);
  }
}
</style>
