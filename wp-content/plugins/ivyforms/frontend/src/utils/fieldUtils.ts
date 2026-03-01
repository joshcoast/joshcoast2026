// Utility helpers for field operations used by the builder
import type { Field, NameSubField, AddressSubField } from '@/types/field'

export function duplicateFieldForStore(
  original: Field,
  newIndex: number,
  getLabel: (k: string) => string,
): Field {
  const origClone = JSON.parse(JSON.stringify(original)) as Field
  const copyBase: Field = {
    ...origClone,
    id: 0,
    fieldIndex: newIndex,
    label: `${origClone.label}${getLabel('copy_label')}`,
    limitMaxLength: origClone.limitMaxLength ?? false,
    maxLength: origClone.maxLength ?? 255,
    labelPosition: origClone.labelPosition ?? 'default',
    noDuplicates: origClone.noDuplicates ?? false,
    inputPrefix: origClone.inputPrefix || '',
    inputSuffix: origClone.inputSuffix || '',
  }

  // If it's name/address parent-child, ensure subfields are duplicated and ids reset
  if (origClone.type === 'name') {
    const sub = (origClone.nameFields ?? []) as NameSubField[]
    copyBase.nameFields = sub.map((nf) => ({ ...nf, id: '' }))
    // also ensure nameFieldTypes are recomputed to match
    copyBase.nameFieldTypes = (copyBase.nameFields || []).map((_, idx) => `nameField${idx + 1}`)
  } else if (origClone.type === 'address') {
    const sub = (origClone.addressFields ?? []) as AddressSubField[]
    copyBase.addressFields = sub.map((af) => ({ ...af, id: '' }))
    // recompute addressFieldTypes from addressFields
    copyBase.addressFieldTypes = (copyBase.addressFields || []).map((af) => af.type)
  }

  return copyBase
}
