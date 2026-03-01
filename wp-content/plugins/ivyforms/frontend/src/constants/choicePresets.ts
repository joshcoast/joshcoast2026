import { countryList } from '@/constants/countries'

export type ChoicePresetId =
  | 'countries'
  | 'states'
  | 'stateAbbreviations'
  | 'continents'
  | 'gender'
  | 'ageRanges'
  | 'weekdays'
  | 'rating'

export interface ChoicePresetOption {
  id: string
  label: string
}

export interface ChoicePreset {
  id: ChoicePresetId
  labelKey: string
  options: ChoicePresetOption[]
}

const normalizeLabel = (label: string) => label.trim()

const makePresetOptions = (options: string[]) =>
  options.map((label) => {
    const normalized = normalizeLabel(label)
    const value = normalized
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/(^-|-$)/g, '')
    return {
      id: value || normalized,
      label: normalized,
    }
  })

const usStates = [
  'Alabama',
  'Alaska',
  'Arizona',
  'Arkansas',
  'California',
  'Colorado',
  'Connecticut',
  'Delaware',
  'District of Columbia',
  'Florida',
  'Georgia',
  'Hawaii',
  'Idaho',
  'Illinois',
  'Indiana',
  'Iowa',
  'Kansas',
  'Kentucky',
  'Louisiana',
  'Maine',
  'Maryland',
  'Massachusetts',
  'Michigan',
  'Minnesota',
  'Mississippi',
  'Missouri',
  'Montana',
  'Nebraska',
  'Nevada',
  'New Hampshire',
  'New Jersey',
  'New Mexico',
  'New York',
  'North Carolina',
  'North Dakota',
  'Ohio',
  'Oklahoma',
  'Oregon',
  'Pennsylvania',
  'Rhode Island',
  'South Carolina',
  'South Dakota',
  'Tennessee',
  'Texas',
  'Utah',
  'Vermont',
  'Virginia',
  'Washington',
  'West Virginia',
  'Wisconsin',
  'Wyoming',
]

const usStateAbbreviations = [
  'AL',
  'AK',
  'AZ',
  'AR',
  'CA',
  'CO',
  'CT',
  'DE',
  'DC',
  'FL',
  'GA',
  'HI',
  'ID',
  'IL',
  'IN',
  'IA',
  'KS',
  'KY',
  'LA',
  'ME',
  'MD',
  'MA',
  'MI',
  'MN',
  'MS',
  'MO',
  'MT',
  'NE',
  'NV',
  'NH',
  'NJ',
  'NM',
  'NY',
  'NC',
  'ND',
  'OH',
  'OK',
  'OR',
  'PA',
  'RI',
  'SC',
  'SD',
  'TN',
  'TX',
  'UT',
  'VT',
  'VA',
  'WA',
  'WV',
  'WI',
  'WY',
]

const continents = [
  'Africa',
  'Antarctica',
  'Asia',
  'Europe',
  'North America',
  'Oceania',
  'South America',
]

const genderOptions = ['Female', 'Male', 'Non-binary', 'Prefer not to say']

const ageRangeOptions = [
  'Under 18',
  '18-24',
  '25-34',
  '35-44',
  '45-54',
  '55-64',
  '65 or Above',
  'Prefer Not to Answer',
]

const weekdayOptions = [
  'Monday',
  'Tuesday',
  'Wednesday',
  'Thursday',
  'Friday',
  'Saturday',
  'Sunday',
]

const ratingOptions = ['1', '2', '3', '4', '5']

export const choicePresets: ChoicePreset[] = [
  {
    id: 'countries',
    labelKey: 'bulk_edit_preset_countries',
    options: makePresetOptions(countryList.map((country) => country.name)),
  },
  {
    id: 'states',
    labelKey: 'bulk_edit_preset_states',
    options: makePresetOptions(usStates),
  },
  {
    id: 'stateAbbreviations',
    labelKey: 'bulk_edit_preset_state_abr',
    options: makePresetOptions(usStateAbbreviations),
  },
  {
    id: 'continents',
    labelKey: 'bulk_edit_preset_continents',
    options: makePresetOptions(continents),
  },
  {
    id: 'gender',
    labelKey: 'bulk_edit_preset_gender',
    options: makePresetOptions(genderOptions),
  },
  {
    id: 'ageRanges',
    labelKey: 'bulk_edit_preset_age',
    options: makePresetOptions(ageRangeOptions),
  },
  {
    id: 'weekdays',
    labelKey: 'bulk_edit_preset_days',
    options: makePresetOptions(weekdayOptions),
  },
  {
    id: 'rating',
    labelKey: 'bulk_edit_preset_rating',
    options: ratingOptions.map((num) => ({ id: num, label: num })),
  },
]
