/**
 * Register Lite Field Options
 *
 * This file registers all built-in field option components for Lite fields.
 */

import api from '@/composables/IvyFormAPI'

// General tab options
import BasicFieldOptions from './general/BasicFieldOptions.vue'
import ValidationOptions from './general/ValidationOptions.vue'
import PlaceholderOption from './general/PlaceholderOption.vue'
import CssClassesOption from './general/CssClassesOption.vue'
import NumberRangeOptions from './general/NumberRangeOptions.vue'
import PhoneFormatOptions from './general/PhoneFormatOptions.vue'
import PhoneCountryDetectionOptions from './general/PhoneCountryDetectionOptions.vue'
import TimeFieldOptions from './general/TimeFieldGeneralOptions.vue'
import DateFieldOptions from './general/DateFieldGeneralOptions.vue'
import TextAreaOptions from './general/TextAreaOptions.vue'
import ConfirmationOptions from './general/ConfirmationOptions.vue'
import ChoiceListOptions from './general/ChoiceListOptions.vue'
import NameFieldOptions from './general/NameFieldOptions.vue'
import AddressFieldOptions from './general/AddressFieldOptions.vue'
import RatingFieldOptions from './general/RatingFieldOptions.vue'

// Advanced tab options
import MaxLengthOptions from './advanced/MaxLengthOptions.vue'
import TextDefaultValueOptions from './advanced/TextDefaultValueOptions.vue'
import TextareaDefaultValueOptions from './advanced/TextareaDefaultValueOptions.vue'
import LabelPositionOptions from './advanced/LabelPositionOptions.vue'
import NoDuplicatesOption from './advanced/NoDuplicatesOption.vue'
import TimeFieldAdvancedOptions from './advanced/TimeFieldAdvancedOptions.vue'
import DateFieldAdvancedOptions from './advanced/DateFieldAdvancedOptions.vue'
import PrefixAndSuffixOptions from '@/views/admin/form-builder/form-builder-page/form-builder/panel/tabs/field-options/advanced/PrefixAndSuffixOptions.vue'
import RatingIconOptions from './advanced/RatingIconOptions.vue'

// All field types that can have basic options (label, description, hideLabel)
const ALL_FIELD_TYPES = [
  'text',
  'email',
  'number',
  'textarea',
  'phone',
  'website',
  'time',
  'date',
  'radio',
  'checkbox',
  'select',
  'multi-select',
  'name',
  'address',
  'recaptcha',
  'turnstile',
  'hcaptcha',
  'rating',
]

// Fields that can have validation options
const VALIDATABLE_FIELDS = [
  'text',
  'email',
  'number',
  'textarea',
  'phone',
  'website',
  'time',
  'date',
  'radio',
  'checkbox',
  'select',
  'multi-select',
  'name',
  'address',
]

// Fields that have placeholder
const PLACEHOLDER_FIELDS = [
  'text',
  'email',
  'number',
  'textarea',
  'phone',
  'website',
  'select',
  'multi-select',
]

// Option-based fields
const OPTION_FIELD_TYPES = ['radio', 'checkbox', 'select', 'multi-select']

// Fields that can have CSS classes
const CSS_CLASS_FIELDS = ALL_FIELD_TYPES

export function registerLiteFieldOptions() {
  // ============================
  // GENERAL TAB OPTIONS
  // ============================

  // Register basic field options (label, description, hideLabel)
  // Note: name and address fields use their own dedicated components
  // (NameFieldOptions, AddressFieldOptions)
  api.fieldOptions.register({
    id: 'lite-basic-options',
    fieldTypes: ALL_FIELD_TYPES,
    tab: 'general',
    order: 10,
    component: BasicFieldOptions,
    showDividerAfter: true,
    condition: (field) => {
      // Don't show for name/address (they have their own dedicated components)
      // recaptcha also has no label/description options
      return (
        field.type !== 'name' &&
        field.type !== 'address' &&
        field.type !== 'recaptcha' &&
        field.type !== 'turnstile' &&
        field.type !== 'hcaptcha'
      )
    },
  })

  // Register validation options (required, readOnly, requiredMessage)
  // Note: name and address fields have these options built into their dedicated components
  api.fieldOptions.register({
    id: 'lite-validation-options',
    fieldTypes: VALIDATABLE_FIELDS,
    tab: 'general',
    order: 20,
    component: ValidationOptions,
    showDividerAfter: true,
    condition: (field) => {
      // Don't show for name/address (they have their own dedicated components)
      return field.type !== 'name' && field.type !== 'address'
    },
  })

  // Register choice list options (for radio, checkbox, select, multi-select)
  api.fieldOptions.register({
    id: 'lite-choice-list-options',
    fieldTypes: OPTION_FIELD_TYPES,
    tab: 'general',
    order: 30,
    component: ChoiceListOptions,
    showDividerAfter: true,
  })

  // Register number range options (only for number fields)
  api.fieldOptions.register({
    id: 'lite-number-range-options',
    fieldTypes: ['number'],
    tab: 'general',
    order: 35,
    component: NumberRangeOptions,
    showDividerAfter: true,
  })

  // Register placeholder option (for text-based fields, but NOT option fields)
  api.fieldOptions.register({
    id: 'lite-placeholder-option',
    fieldTypes: PLACEHOLDER_FIELDS,
    tab: 'general',
    order: 40,
    component: PlaceholderOption,
    showDividerAfter: true,
  })

  // Register name field basic options (for name fields)
  api.fieldOptions.register({
    id: 'lite-name-field-basic-options',
    fieldTypes: ['name'],
    tab: 'general',
    order: 45,
    component: NameFieldOptions,
    showDividerAfter: true,
  })

  // Register address field basic options (for address fields)
  api.fieldOptions.register({
    id: 'lite-address-field-basic-options',
    fieldTypes: ['address'],
    tab: 'general',
    order: 50,
    component: AddressFieldOptions,
    showDividerAfter: true,
  })

  // Register rating field options (only for rating fields)
  api.fieldOptions.register({
    id: 'lite-rating-field-options',
    fieldTypes: ['rating'],
    tab: 'general',
    order: 52,
    component: RatingFieldOptions,
    showDividerAfter: true,
  })

  // Register phone format options with auto-detect (only for phone fields)
  api.fieldOptions.register({
    id: 'lite-phone-format-options',
    fieldTypes: ['phone'],
    tab: 'general',
    order: 55,
    component: PhoneFormatOptions,
    showDividerAfter: true,
  })

  // Register time field options (only for time fields)
  api.fieldOptions.register({
    id: 'lite-time-field-options',
    fieldTypes: ['time'],
    tab: 'general',
    order: 60,
    component: TimeFieldOptions,
    showDividerAfter: true,
  })

  // Register date field options (only for date fields)
  api.fieldOptions.register({
    id: 'lite-date-field-options',
    fieldTypes: ['date'],
    tab: 'general',
    order: 60,
    component: DateFieldOptions,
    showDividerAfter: true,
  })

  // Register CSS classes option (before the bottom toggles/options)
  api.fieldOptions.register({
    id: 'lite-css-classes-option',
    fieldTypes: CSS_CLASS_FIELDS,
    tab: 'general',
    order: 90,
    component: CssClassesOption,
    showDividerAfter: true,
    condition: (field) => {
      // Don't show for name field (it has CSS classes in NameFieldOptions)
      return field.type !== 'name' && field.type !== 'address'
    },
  })

  // Register phone format options with auto-detect (only for phone fields)
  api.fieldOptions.register({
    id: 'lite-phone-country-detection',
    fieldTypes: ['phone'],
    tab: 'general',
    order: 91,
    component: PhoneCountryDetectionOptions,
    showDividerAfter: false,
  })

  // Register textarea options (only for textarea fields)
  api.fieldOptions.register({
    id: 'lite-textarea-options',
    fieldTypes: ['textarea'],
    tab: 'general',
    order: 96,
    component: TextAreaOptions,
    showDividerAfter: false,
  })

  // Register confirmation options
  api.fieldOptions.register({
    id: 'lite-confirmation-options',
    fieldTypes: ['email'],
    tab: 'general',
    order: 97,
    component: ConfirmationOptions,
    showDividerAfter: false,
  })

  // ============================
  // ADVANCED TAB OPTIONS
  // ============================

  // Field types for advanced options
  // Text-based fields default value
  const TEXT_DEFAULT_VALUE_FIELDS = ['text', 'email', 'number', 'website']
  const MAX_LENGTH_FIELDS = ['text', 'email', 'textarea', 'website', 'phone']
  const LABEL_POSITION_FIELDS = [
    'text',
    'email',
    'number',
    'textarea',
    'website',
    'phone',
    'time',
    'date',
    'name',
    'select',
    'multi-select',
    'radio',
    'checkbox',
    'address',
    'rating',
  ]
  const NO_DUPLICATES_FIELDS = ['text', 'email', 'number', 'website', 'phone']
  const PREFIX_SUFFIX_FIELDS = ['website']
  const RATING_ICON_FIELDS = ['rating']

  // Register time advanced options (only for time fields)
  api.fieldOptions.register({
    id: 'lite-time-advanced-options',
    fieldTypes: ['time'],
    tab: 'advanced',
    order: 10,
    component: TimeFieldAdvancedOptions,
    showDividerAfter: true,
  })
  // Register date advanced options (only for date fields)
  api.fieldOptions.register({
    id: 'lite-date-advanced-options',
    fieldTypes: ['date'],
    tab: 'advanced',
    order: 10,
    component: DateFieldAdvancedOptions,
    showDividerAfter: true,
  })

  // Register max length options
  api.fieldOptions.register({
    id: 'lite-max-length-options',
    fieldTypes: MAX_LENGTH_FIELDS,
    tab: 'advanced',
    order: 20,
    component: MaxLengthOptions,
    showDividerAfter: true,
  })

  // Register default value options
  api.fieldOptions.register({
    id: 'lite-text-default-value-options',
    fieldTypes: TEXT_DEFAULT_VALUE_FIELDS,
    tab: 'advanced',
    order: 30,
    component: TextDefaultValueOptions,
    showDividerAfter: true,
  })
  // Register default value options
  api.fieldOptions.register({
    id: 'lite-textarea-default-value-options',
    fieldTypes: ['textarea'],
    tab: 'advanced',
    order: 31,
    component: TextareaDefaultValueOptions,
    showDividerAfter: true,
  })

  // Register label position options
  api.fieldOptions.register({
    id: 'lite-label-position-options',
    fieldTypes: LABEL_POSITION_FIELDS,
    tab: 'advanced',
    order: 40,
    component: LabelPositionOptions,
    showDividerAfter: true,
  })

  // Register rating icon options
  api.fieldOptions.register({
    id: 'lite-rating-icon-options',
    fieldTypes: RATING_ICON_FIELDS,
    tab: 'advanced',
    order: 41,
    component: RatingIconOptions,
    showDividerAfter: true,
  })

  // Register no duplicates option
  api.fieldOptions.register({
    id: 'lite-no-duplicates-option',
    fieldTypes: NO_DUPLICATES_FIELDS,
    tab: 'advanced',
    order: 50,
    component: NoDuplicatesOption,
    showDividerAfter: true,
  })

  // Register prefix/suffix options
  api.fieldOptions.register({
    id: 'lite-prefix-suffix-options',
    fieldTypes: PREFIX_SUFFIX_FIELDS,
    tab: 'advanced',
    order: 50,
    component: PrefixAndSuffixOptions,
    showDividerAfter: false,
  })
}
