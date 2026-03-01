import type { IconType } from '@/types/icons/icon-type'
import { IconCategory } from '@/types/icons/icon-category'
import type { TimeFieldType, TimeFormat } from '@/types/time/time-type'
import type { DateFieldType, DateFormat } from '@/types/date/date-type'

export interface DragField {
  type: string
  name: string
  iconStart: string
  iconStartType: IconType
  iconStartCategory: IconCategory
  iconEnd: string
  iconEndCategory: IconCategory
  iconEndType: IconType
  comingSoon?: boolean
  featureSlug?: string
  disabled?: boolean
  pro?: boolean
}

export type Choice = {
  id: number
  label: string
  value: string
  isDefault?: boolean
  position: number
}

// Name subfield for Name fields (canonical representation)
export interface NameSubField {
  type: string
  id: string
  modelValue: string
  label: string
  required: boolean
  optionHide: boolean
  description: string
  placeholder: string
  requiredMessage?: string
}

// Address subfield for Address fields (canonical representation)
export interface AddressSubField {
  type: string
  id: string
  label: string
  value: string
  placeholder: string
  required: boolean
  hideLabel: boolean
  description: string
  requiredMessage: string
  visible: boolean
}

export interface Field {
  id: number
  formId: number
  fieldIndex: number
  type: string
  label: string
  defaultValue: string
  required: boolean
  placeholder: string
  position?: number
  rowIndex?: number
  columnIndex?: number
  width?: number
  hideLabel?: boolean
  readOnly?: boolean
  fieldOptions?: Choice[]
  shuffleOptions?: boolean
  showValues?: boolean
  enableSearch?: boolean
  rows?: number
  confirmFieldEnabled?: boolean
  confirmFieldLabel?: string
  confirmFieldPlaceholder?: string
  confirmFieldHideLabel?: boolean
  // For parent-child fields:
  parentId?: number
  // Canonical subfield arrays:
  nameFields?: NameSubField[]
  addressFields?: AddressSubField[]
  // Helper arrays for subfield order:
  nameFieldTypes?: string[]
  addressFieldTypes?: string[]
  countryDefaultValue?: string
  addressType?: string
  settings?: string
  description?: string
  requiredMessage?: string
  cssClasses?: string
  value?: string
  limitMaxLength?: boolean
  maxLength?: number
  labelPosition?: string
  noDuplicates?: boolean
  // Phone specific
  phoneFormat?: 'e164' | 'international' | 'national'
  phoneAutoDetect?: boolean
  minValue?: number
  maxValue?: number
  step?: number
  numberFormat?: string
  subFieldIndex?: number
  timeFieldType?: TimeFieldType
  timeFormat?: TimeFormat
  dateFieldType?: DateFieldType
  dateFormat?: DateFormat
  minYear?: number
  maxYear?: number
  minDateValue?: string
  maxDateValue?: string
  inputPrefix?: string
  inputSuffix?: string
  visible?: boolean
  ratingIcon?: 'star' | 'heart' | 'like'
  showRatingText?: boolean
  // Allow dynamic access for other properties
  [key: string]: unknown
}

export interface NameFieldValue {
  [key: string]: string | undefined
}

// Address field value type (similar to NameFieldValue)
export interface AddressFieldValue {
  [key: string]: string | undefined
  fullAddress?: string
}
