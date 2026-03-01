import type { Field, NameSubField, AddressSubField } from '@/types/field'

function safeParse(json: string | null | undefined): Record<string, unknown> {
  if (!json) return {}
  try {
    return JSON.parse(json) as Record<string, unknown>
  } catch {
    return {}
  }
}

export function normalizeFieldsForBuilder(apiFields: Field[]): Field[] {
  if (!Array.isArray(apiFields) || apiFields.length === 0) return []
  const parents: Field[] = []
  const childrenByParent: Record<number, Field[]> = {}

  for (const f of apiFields) {
    if (f.parentId && typeof f.parentId === 'number' && f.parentId > 0) {
      if (!childrenByParent[f.parentId]) childrenByParent[f.parentId] = []
      childrenByParent[f.parentId].push(f)
    } else {
      parents.push(f)
    }
  }

  // Ensure fields have proper row/column defaults
  parents.forEach((field, index) => {
    // Only set defaults if ALL layout properties are missing (backward compatibility)
    const hasLayoutInfo =
      (field.rowIndex !== undefined && field.rowIndex !== null) ||
      (field.columnIndex !== undefined && field.columnIndex !== null) ||
      (field.width !== undefined && field.width !== null)

    if (!hasLayoutInfo) {
      // Old form without layout info - each field in its own row
      field.rowIndex = index
      field.columnIndex = 0
      field.width = 100
    } else {
      // Form with layout info - preserve existing values or use sensible defaults
      if (field.rowIndex === undefined || field.rowIndex === null) {
        field.rowIndex = 0
      }
      if (field.columnIndex === undefined || field.columnIndex === null) {
        field.columnIndex = 0
      }
      if (field.width === undefined || field.width === null) {
        field.width = 100
      }
    }
  })

  return parents.map((parent) => {
    if (parent.type === 'name') {
      const children = childrenByParent[parent.id] ?? []
      const nameFields: NameSubField[] = children.map((child) => {
        const s = safeParse((child as Field & { settings?: string }).settings)
        const label = (child as Partial<Field>).label ?? String(s['label'] ?? '')
        return {
          type: 'text',
          id: String(child.id ?? ''),
          modelValue: (child as Partial<Field>).defaultValue ?? '',
          label,
          required: !!(child as Partial<Field>).required || Boolean(s['required']),
          optionHide: !!(child as Partial<Field>).hideLabel || Boolean(s['hideLabel']),
          description: String((child as Partial<Field>).description ?? s['description'] ?? ''),
          placeholder: String((child as Partial<Field>).placeholder ?? s['placeholder'] ?? ''),
        }
      })
      const nameFieldTypes = nameFields.map((_, idx) => `nameField${idx + 1}`)
      return {
        ...parent,
        nameFields,
        nameFieldTypes,
        // Preserve grid layout properties
        rowIndex: parent.rowIndex ?? 0,
        columnIndex: parent.columnIndex ?? 0,
        width: parent.width ?? 100,
      } as Field
    }
    if (parent.type === 'address') {
      const children = childrenByParent[parent.id] ?? []
      const defaultAddressOrder = [
        'streetAddress',
        'addressLine2',
        'city',
        'state',
        'zip',
        'country',
      ]
      const addressFields: AddressSubField[] = children.map((child, idx) => {
        const s = safeParse((child as Field & { settings?: string }).settings)
        const explicitType = (child as Partial<Field>).addressType || String(s['type'] ?? '')
        const type =
          explicitType && explicitType.trim() !== ''
            ? explicitType
            : (defaultAddressOrder[idx] ?? `addressPart${idx + 1}`)
        return {
          type,
          id: String(child.id ?? ''),
          label: (child as Partial<Field>).label ?? String(s['label'] ?? ''),
          value: (child as Partial<Field>).defaultValue ?? String(s['value'] ?? ''),
          placeholder: (child as Partial<Field>).placeholder ?? String(s['placeholder'] ?? ''),
          required: !!(child as Partial<Field>).required || Boolean(s['required']),
          hideLabel: !!(child as Partial<Field>).hideLabel || Boolean(s['hideLabel']),
          description: String((child as Partial<Field>).description ?? s['description'] ?? ''),
          requiredMessage: String(
            (child as Partial<Field>).requiredMessage ?? s['requiredMessage'] ?? '',
          ),
          visible: !!(child as Partial<Field>).visible || Boolean(s['visible'] ?? true),
        }
      })
      const addressFieldTypes = addressFields.map((af) => af.type)
      return {
        ...parent,
        addressFields,
        addressFieldTypes,
        // Preserve grid layout properties
        rowIndex: parent.rowIndex ?? 0,
        columnIndex: parent.columnIndex ?? 0,
        width: parent.width ?? 100,
      } as Field
    }
    // Regular field - preserve grid layout properties
    return {
      ...parent,
      rowIndex: parent.rowIndex ?? 0,
      columnIndex: parent.columnIndex ?? 0,
      width: parent.width ?? 100,
    }
  })
}

export function denormalizeFieldsForApi(fields: Field[]): Field[] {
  const result: Field[] = []
  for (const field of fields) {
    if (field.type === 'name') {
      if (Array.isArray(field.nameFields)) {
        const { nameFields, ...parentRest } = field
        // Preserve row/column/width layout for name fields
        result.push({
          ...parentRest,
          type: 'name',
          rowIndex: field.rowIndex,
          columnIndex: field.columnIndex,
          width: field.width,
        } as Field)
        nameFields.forEach((sub, idx) => {
          result.push({
            id: sub.id ? parseInt(sub.id, 10) || 0 : 0,
            formId: field.formId,
            fieldIndex: field.fieldIndex,
            type: 'text',
            label: sub.label,
            defaultValue: sub.modelValue,
            required: sub.required,
            placeholder: sub.placeholder,
            position: field.position,
            value: sub.modelValue,
            hideLabel: sub.optionHide,
            description: sub.description,
            requiredMessage: '',
            parentId: field.id,
            subFieldIndex: idx,
          } as Field)
        })
        continue
      }
    }
    if (field.type === 'address') {
      if (Array.isArray(field.addressFields)) {
        const { addressFields, ...parentRest } = field
        // Preserve row/column/width layout for address fields
        result.push({
          ...parentRest,
          rowIndex: field.rowIndex,
          columnIndex: field.columnIndex,
          width: field.width,
        } as Field)
        addressFields.forEach((sub, idx) => {
          const settings = JSON.stringify({
            hideLabel: sub.hideLabel,
            description: sub.description,
            placeholder: sub.placeholder,
            type: sub.type,
            requiredMessage: sub.requiredMessage,
            visible: typeof sub.visible === 'boolean' ? sub.visible : true,
          })
          result.push({
            id: sub.id ? parseInt(sub.id, 10) || 0 : 0,
            formId: field.formId,
            fieldIndex: field.fieldIndex,
            type: 'text',
            label: sub.label,
            defaultValue: sub.value,
            required: sub.required,
            placeholder: sub.placeholder,
            position: field.position,
            value: sub.value,
            hideLabel: sub.hideLabel,
            description: sub.description,
            requiredMessage: sub.requiredMessage,
            parentId: field.id,
            addressType: sub.type,
            settings,
            subFieldIndex: idx,
            visible: sub.visible,
          } as Field)
        })
        continue
      }
    }
    // Regular field - preserve row/column/width layout
    result.push({
      ...field,
      id: typeof field.id === 'string' ? parseInt(field.id, 10) || 0 : field.id || 0,
      rowIndex: field.rowIndex,
      columnIndex: field.columnIndex,
      width: field.width,
    } as Field)
  }
  return result
}
