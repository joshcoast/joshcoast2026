import type { Field } from '@/types/field'
import { parentChildFieldConfigs } from './parentChildFieldTemplates'

export function buildNewField(
  fieldData: Partial<Field> & { type: string },
  index: number,
  getLabel: (key: string) => string,
): Field {
  const config = parentChildFieldConfigs[fieldData.type]
  if (config) {
    // Parent-child field type
    const subFields = config.buildSubFields(getLabel)
    return {
      ...(fieldData as Field),
      id: fieldData.id ?? 0,
      formId: fieldData.formId ?? 0,
      fieldIndex: index,
      type: config.type,
      label: fieldData.label ?? getLabel(config.type),
      defaultValue: fieldData.defaultValue || '',
      value: fieldData.value || '',
      required: fieldData.required || false,
      readOnly: fieldData.readOnly || false,
      [config.subFieldKey]: subFields,
      limitMaxLength: fieldData.limitMaxLength || false,
      maxLength: fieldData.maxLength || 255,
      labelPosition: fieldData.labelPosition || 'default',
      noDuplicates: fieldData.noDuplicates || false,
      inputPrefix: fieldData.inputPrefix || '',
      inputSuffix: fieldData.inputSuffix || '',
    } as Field
  }
  // Regular field type
  return {
    ...(fieldData as Field),
    id: fieldData.id ?? 0,
    formId: fieldData.formId ?? 0,
    fieldIndex: index,
    defaultValue: fieldData.defaultValue || '',
    value: fieldData.value || '',
    required: fieldData.required || false,
    readOnly: fieldData.readOnly || false,
    limitMaxLength: fieldData.limitMaxLength || false,
    maxLength: fieldData.maxLength || 255,
    labelPosition: fieldData.labelPosition || 'default',
    noDuplicates: fieldData.noDuplicates || false,
    confirmFieldEnabled: fieldData.confirmFieldEnabled || false,
    confirmFieldLabel: fieldData.confirmFieldLabel || '',
    confirmFieldPlaceholder: fieldData.confirmFieldPlaceholder || '',
    confirmFieldHideLabel: fieldData.confirmFieldHideLabel || false,
    inputPrefix: fieldData.inputPrefix || '',
    inputSuffix: fieldData.inputSuffix || '',
  }
}
