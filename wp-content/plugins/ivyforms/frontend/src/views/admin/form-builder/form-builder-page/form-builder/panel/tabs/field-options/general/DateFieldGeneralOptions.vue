<template>
  <div
    class="ivyforms-general-date-options ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-16"
  >
    <IvyFormItem
      v-if="field.dateFieldType === 'input' || field.dateFieldType === 'picker'"
      :label="getLabel('placeholder')"
      secondary
    >
      <IvyTextInput :model-value="field.placeholder" @input="updateField('placeholder', $event)" />
    </IvyFormItem>

    <IvyDivider v-if="field.dateFieldType === 'input' || field.dateFieldType === 'picker'" />

    <IvyFormItem :label="getLabel('date_input_type')">
      <IvySelectInput
        :model-value="field.dateFieldType"
        :placeholder="getLabel('select')"
        class="ivyforms-date-select"
        secondary
        @update:model-value="handleDateFieldTypeChange"
      >
        <IvySelectOption value="input" :label="getLabel('date_input')" secondary>
          {{ getLabel('date_input') }}
        </IvySelectOption>
        <IvySelectOption value="picker" :label="getLabel('date_picker')" secondary>
          {{ getLabel('date_picker') }}
        </IvySelectOption>
        <IvySelectOption value="dropdown" :label="getLabel('date_dropdown')" secondary>
          {{ getLabel('date_dropdown') }}
        </IvySelectOption>
      </IvySelectInput>
    </IvyFormItem>

    <IvyDivider />

    <IvyFormItem :label="getLabel('date_format')">
      <IvySelectInput
        :model-value="field.dateFormat"
        :placeholder="getLabel('select')"
        secondary
        @update:model-value="handleDateFormatChange"
      >
        <IvySelectOption value="MM/DD/YYYY" label="MM/DD/YYYY" secondary>
          MM/DD/YYYY
        </IvySelectOption>
        <IvySelectOption value="DD/MM/YYYY" label="DD/MM/YYYY" secondary>
          DD/MM/YYYY
        </IvySelectOption>
        <IvySelectOption value="YYYY-MM-DD" label="YYYY-MM-DD" secondary>
          YYYY-MM-DD
        </IvySelectOption>
        <IvySelectOption value="DD.MM.YYYY" label="DD.MM.YYYY" secondary>
          DD.MM.YYYY
        </IvySelectOption>
      </IvySelectInput>
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
import { useDateField } from '@/composables/useDateField'

const { parseDateString, formatDateString } = useDateField()

interface Props {
  field: Field
  updateField: (key: string, value: unknown) => void
  getLabel: (key: string) => string
}

const props = defineProps<Props>()

const handleDateFieldTypeChange = (value: string) => {
  props.updateField('dateFieldType', value)

  // Update label based on the selected date field type
  if (value === 'picker') {
    const pickerLabelText = props.getLabel('date_picker')
    props.updateField('label', pickerLabelText)
  } else if (value === 'input') {
    const inputLabelText = props.getLabel('date_input')
    props.updateField('label', inputLabelText)
  } else if (value === 'dropdown') {
    props.updateField('label', props.getLabel('date_dropdown'))
  }
}

const handleDateFormatChange = (value: string) => {
  // Capture the old format BEFORE updating to the new one
  const oldFormat = (props.field.dateFormat as string) || 'MM/DD/YYYY'

  // Update to the new format
  props.updateField('dateFormat', value)

  // Convert existing default value to new format if it exists
  if (props.field.defaultValue) {
    const parsed = parseDateString(props.field.defaultValue as string, oldFormat)
    if (parsed.day && parsed.month && parsed.year) {
      const newFormatted = formatDateString(parsed.day, parsed.month, parsed.year, value)
      props.updateField('defaultValue', newFormatted)
    }
  }
}

// Watch for changes in date field type and update label accordingly
watch(
  () => props.field.dateFieldType,
  (newType) => {
    const currentLabel = props.field.label
    const defaultLabels = [
      props.getLabel('date_picker'),
      props.getLabel('date_input'),
      props.getLabel('date_dropdown'),
      props.getLabel('date'),
      '',
    ]
    if (defaultLabels.includes(currentLabel)) {
      if (newType === 'picker') {
        props.updateField('label', props.getLabel('date_picker'))
      } else if (newType === 'input') {
        props.updateField('label', props.getLabel('date_input'))
      } else if (newType === 'dropdown') {
        props.updateField('label', props.getLabel('date_dropdown'))
      }
    }
  },
  { immediate: true },
)
</script>

<style scoped lang="scss">
.ivyforms-general-date-options {
  :deep(.ivyforms-form-item),
  :deep(.el-form-item) {
    margin-bottom: 0;
    gap: var(--Spacing-xs, 6px);
  }
}
</style>
