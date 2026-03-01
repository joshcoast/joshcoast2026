import type { Field, NameSubField, AddressSubField } from '@/types/field'

export type ParentFieldTemplate = Omit<
  Field,
  'id' | 'formId' | 'fieldIndex' | 'position' | 'value'
> & {
  nameFields?: NameSubField[]
  addressFields?: AddressSubField[]
}

type SubFieldsBuilder = (getLabel: (key: string) => string) => unknown[]

export interface ParentChildFieldConfig {
  type: string
  buildSubFields: SubFieldsBuilder
  subFieldKey: string
}

export const parentChildFieldConfigs: Record<string, ParentChildFieldConfig> = {
  name: {
    type: 'name',
    buildSubFields: (getLabel) => [
      {
        type: 'text',
        id: '',
        modelValue: '',
        label: getLabel('first_name'),
        required: false,
        optionHide: false,
        description: '',
        placeholder: '',
        labelPosition: 'default',
      },
      {
        type: 'text',
        id: '',
        modelValue: '',
        label: getLabel('last_name'),
        required: false,
        optionHide: false,
        description: '',
        placeholder: '',
        labelPosition: 'default',
      },
    ],
    subFieldKey: 'nameFields',
  },
  address: {
    type: 'address',
    buildSubFields: (getLabel) => [
      {
        type: 'streetAddress',
        id: '',
        label: getLabel('street_address'),
        value: '',
        placeholder: '',
        required: false,
        hideLabel: false,
        description: '',
        requiredMessage: '',
        visible: true,
      },
      {
        type: 'addressLine2',
        id: '',
        label: getLabel('address_line_2'),
        value: '',
        placeholder: '',
        required: false,
        hideLabel: false,
        description: '',
        requiredMessage: '',
        visible: true,
      },
      {
        type: 'city',
        id: '',
        label: getLabel('city'),
        value: '',
        placeholder: '',
        required: false,
        hideLabel: false,
        description: '',
        requiredMessage: '',
        visible: true,
      },
      {
        type: 'state',
        id: '',
        label: getLabel('state'),
        value: '',
        placeholder: '',
        required: false,
        hideLabel: false,
        description: '',
        requiredMessage: '',
        visible: true,
      },
      {
        type: 'zip',
        id: '',
        label: getLabel('zip'),
        value: '',
        placeholder: '',
        required: false,
        hideLabel: false,
        description: '',
        requiredMessage: '',
        visible: true,
      },
      {
        type: 'country',
        id: '',
        label: getLabel('country'),
        value: '',
        placeholder: '',
        required: false,
        hideLabel: false,
        description: '',
        requiredMessage: '',
        visible: true,
      },
    ],
    subFieldKey: 'addressFields',
  },
}
