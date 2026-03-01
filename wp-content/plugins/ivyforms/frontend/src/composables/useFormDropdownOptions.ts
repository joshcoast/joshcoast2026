import type { Ref } from 'vue'
import { useFormBuilder } from '@/stores/useFormBuilder'
import { useLabels } from '@/composables/useLabels'

export interface FormDropdownOption {
  value: string | number
  label: string
  labelId?: string
}

export function updateFormDropdownOption(formOptions: Ref<FormDropdownOption[]>) {
  const formBuilderStore = useFormBuilder()
  const { getLabel } = useLabels()
  if (formBuilderStore.formId && formBuilderStore.name) {
    const existingFormIndex = formOptions.value.findIndex(
      (option) => option.value === formBuilderStore.formId,
    )
    if (existingFormIndex === -1) {
      formOptions.value.push({
        value: formBuilderStore.formId,
        labelId: `${getLabel('id')}: ${formBuilderStore.formId}`,
        label: formBuilderStore.name,
      })
    } else {
      formOptions.value[existingFormIndex].label = formBuilderStore.name
    }
  }
}
