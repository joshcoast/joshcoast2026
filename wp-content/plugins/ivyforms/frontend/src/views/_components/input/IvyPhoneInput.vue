<template>
  <div ref="phoneInputRef" :class="['ivyforms-phone-input', { 'is-secondary': isSecondary }]">
    <VueTelInput
      ref="vueTelRef"
      v-model="localModelValue"
      :class="['ivyforms-phone-input__control', { 'is-focused': isInputFocused }]"
      :input-options="{ placeholder: isInputFocused ? examplePhoneNumber : placeholder || '' }"
      :ignored-countries="ignoredCountries"
      :mode="vueTelMode"
      :valid-characters-only="true"
      :disabled="disabled"
      :default-country="computedDefaultCountry"
      @focus="onInputFocus"
      @blur="handleBlur"
      @country-changed="onCountryChanged"
      @open="onDropdownOpen"
      @close="onDropdownClose"
      @on-input="onInput"
    >
      <template #arrow-icon>
        <IvyIcon
          class="ivyforms-phone-input__arrow"
          category="arrows"
          name="chevron-down"
          size="s"
          color="var(--map-base-dusk-symbol-2)"
        />
      </template>
    </VueTelInput>
  </div>
</template>

<script setup lang="ts">
import { VueTelInput } from 'vue-tel-input'
import 'vue-tel-input/vue-tel-input.css'
import { computed, ref, nextTick, onBeforeMount, onMounted, inject, watch } from 'vue'
import { parsePhoneNumberFromString, getExampleNumber, type CountryCode } from 'libphonenumber-js'
import examples from 'libphonenumber-js/mobile/examples'
import IvyIcon from '@/views/_components/icon/IvyIcon.vue'
import { ignoredCountries } from '@/constants/countries'
import { useLabels } from '@/composables/useLabels'

interface Props {
  modelValue?: string | undefined
  placeholder?: string
  disabled?: boolean
  phoneFormat?: 'international' | 'national' | 'e164'
  autoDetectCountry?: boolean
  ariaLabel?: string
  ariaLabelledby?: string
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: '',
  placeholder: '',
  disabled: false,
  phoneFormat: 'national',
  autoDetectCountry: true,
  ariaLabel: '',
  ariaLabelledby: '',
})

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
  (e: 'blur'): void
  (e: 'update', payload: { countryCode: string; isValid: boolean }): void
}>()

const { getLabel } = useLabels()

const vueTelRef = ref<InstanceType<typeof VueTelInput> | null>(null)
const phoneInputRef = ref<HTMLElement | null>(null)
const internalValue = ref<string>('')
const selectedCountryCode = ref<string>('')
const manuallySelectedCountry = ref<string>('')
const isInputFocused = ref(false)

const localModelValue = computed({
  get() {
    return internalValue.value
  },
  set(v: string) {
    internalValue.value = v
  },
})

// Determine vue-tel-input mode (it supports 'international' and 'national'). For e164 we'll leverage international.
const vueTelMode = computed(() =>
  props.phoneFormat === 'international' ? 'international' : 'national',
)

const examplePhoneNumber = computed(() => {
  if (!selectedCountryCode.value) return ''
  try {
    const ex = getExampleNumber(selectedCountryCode.value as CountryCode, examples)
    if (!ex) return ''
    if (props.phoneFormat === 'international') return ex.formatInternational()
    if (props.phoneFormat === 'e164') return ex.number
    return ex.formatNational()
  } catch {
    return ''
  }
})

const onCountryChanged = (country: { iso2: string }) => {
  selectedCountryCode.value = country.iso2
  manuallySelectedCountry.value = country.iso2
}

const onDropdownOpen = () => {
  // Adjust dropdown width dynamically (ensures full country name visibility)
  nextTick(() => {
    const dropdown = phoneInputRef.value?.querySelector('.vti__dropdown-list') as HTMLElement | null
    if (dropdown) {
      dropdown.style.minWidth = '280px'
      // Add WCAG accessibility label for screen readers
      dropdown.setAttribute('aria-label', getLabel('select_country'))
      dropdown.setAttribute('role', 'listbox')
      // Make dropdown list visible to assistive technologies
      dropdown.removeAttribute('aria-hidden')

      // Ensure dropdown items are keyboard accessible when open
      const items = dropdown.querySelectorAll('.vti__dropdown-item')
      items.forEach((item) => {
        item.setAttribute('role', 'option')
        item.setAttribute('tabindex', '0')
      })
    }
  })
}

const onDropdownClose = () => {
  // WCAG 4.1.2: Prevent nested interactive controls by hiding dropdown from assistive technologies when closed
  nextTick(() => {
    const dropdown = phoneInputRef.value?.querySelector('.vti__dropdown-list') as HTMLElement | null
    if (dropdown) {
      // Hide from assistive technologies when closed
      dropdown.setAttribute('aria-hidden', 'true')

      // Remove focusability from items when closed to prevent nested interactive issues
      const items = dropdown.querySelectorAll('.vti__dropdown-item')
      items.forEach((item) => {
        item.setAttribute('tabindex', '-1')
      })
    }
  })
}

const onInputFocus = () => {
  isInputFocused.value = true
}
const handleBlur = () => {
  isInputFocused.value = false
  emit('blur')
}

const formatAndEmit = (rawNumber: string, phoneObject: { country: string; valid: boolean }) => {
  let formatted = rawNumber
  try {
    const pn = parsePhoneNumberFromString(rawNumber || '', selectedCountryCode.value as CountryCode)
    if (pn) {
      if (props.phoneFormat === 'international') formatted = pn.formatInternational()
      else if (props.phoneFormat === 'e164') formatted = pn.number
      else formatted = pn.formatNational()
    }
  } catch {
    // ignore formatting errors, emit raw
  }
  emit('update:modelValue', formatted)
  emit('update', { countryCode: selectedCountryCode.value, isValid: phoneObject.valid })
}

const onInput = (
  value: string,
  phoneObject: { country: string; valid: boolean; number: string },
) => {
  if (manuallySelectedCountry.value) {
    if (
      phoneObject.country &&
      phoneObject.country !== manuallySelectedCountry.value &&
      value.replace(/\D/g, '').length > 6
    ) {
      selectedCountryCode.value = phoneObject.country
      manuallySelectedCountry.value = ''
    } else {
      selectedCountryCode.value = manuallySelectedCountry.value
    }
  } else if (phoneObject.country) {
    selectedCountryCode.value = phoneObject.country
  }
  formatAndEmit(phoneObject.number, phoneObject)
}

const convertIncomingValue = (incoming: string) => {
  if (!incoming) return ''
  try {
    const pn = parsePhoneNumberFromString(incoming)
    if (pn?.country) {
      selectedCountryCode.value = pn.country
      if (props.phoneFormat === 'international') return pn.formatInternational()
      if (props.phoneFormat === 'e164') return pn.number
      return pn.formatNational()
    }
  } catch {
    return incoming
  }
  return incoming
}

onBeforeMount(() => {
  if (props.modelValue) {
    internalValue.value = convertIncomingValue(props.modelValue)
  }
})

// Update internal value if external model changes
watch(
  () => props.modelValue,
  (nv) => {
    if (nv && nv !== internalValue.value) {
      internalValue.value = convertIncomingValue(nv)
    }
  },
)

// When format changes dynamically (rare in public form) reformat current value
watch(
  () => props.phoneFormat,
  () => {
    if (internalValue.value) {
      internalValue.value = convertIncomingValue(internalValue.value)
    }
  },
)

// Determine default country when auto detection is disabled
interface TelCountry {
  iso2: string
  // other props from vue-tel-input country objects are ignored for now
}

const computedDefaultCountry = computed(() => {
  if (selectedCountryCode.value) return selectedCountryCode.value
  if (!props.autoDetectCountry) {
    const telInstance = vueTelRef.value as unknown as { allCountries?: TelCountry[] }
    const list: TelCountry[] = telInstance?.allCountries ?? []
    const first = list.find((c) => c.iso2 && !ignoredCountries.includes(c.iso2.toUpperCase()))
    return first ? first.iso2 : 'us'
  }
  return undefined
})

onMounted(() => {
  if (!props.autoDetectCountry && !selectedCountryCode.value) {
    nextTick(() => {
      const telInstance = vueTelRef.value as unknown as { allCountries?: TelCountry[] }
      const list: TelCountry[] = telInstance?.allCountries ?? []
      const first = list.find((c) => c.iso2 && !ignoredCountries.includes(c.iso2.toUpperCase()))
      if (first) selectedCountryCode.value = first.iso2
    })
  }

  // Add WCAG accessibility attributes to the input element
  nextTick(() => {
    const input = vueTelRef.value?.$el?.querySelector('.vti__input') as HTMLInputElement | null
    if (input) {
      // Ensure accessibility: only set aria-label for screen readers
      input.setAttribute('aria-label', props.ariaLabel || getLabel('phone_number'))
    }

    // WCAG 4.1.2: Initialize dropdown as hidden and items as non-focusable to prevent nested interactive controls
    const dropdown = phoneInputRef.value?.querySelector('.vti__dropdown-list') as HTMLElement | null
    if (dropdown) {
      dropdown.setAttribute('aria-hidden', 'true')
      const items = dropdown.querySelectorAll('.vti__dropdown-item')
      items.forEach((item) => {
        item.setAttribute('tabindex', '-1')
      })
    }
  })
})

const focus = () => {
  const input = vueTelRef.value?.$el?.querySelector('.vti__input') as HTMLInputElement | null
  input?.focus()
}

const isSecondary = inject('isSecondary', false)

defineExpose({ focus })
</script>

<style lang="scss">
.ivyforms-phone-input {
  position: relative;
  display: flex;
  align-items: center;
  width: 100%;

  .vue-tel-input {
    height: 40px;
    border-radius: 8px;
    box-shadow: none !important;
    border: 1px solid var(--map-base-dusk-stroke--2);
    flex: 1;
    display: flex;
    background: var(--map-base-dusk-o05);
    transition: border-color 0.15s ease;

    &:hover,
    &:focus-within,
    &.is-focused {
      border-color: var(--map-base-dusk-stroke-0);
    }
    &.is-secondary:hover,
    &.is-secondary:focus-within {
      border-color: var(--map-base-purple-stroke-0);
    }
    &.disabled,
    &.disabled:hover {
      background: var(--map-base-dusk-o05);
      opacity: 0.6;
      cursor: not-allowed;
    }

    .vti__dropdown {
      width: 64px;
      min-width: 64px;
      padding-left: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: transparent;
    }
    .vti__dropdown.open .ivyforms-phone-input__arrow {
      transform: rotate(180deg);
    }

    .vti__dropdown-list {
      left: 0;
      padding: 8px 0;
      margin: 0;
      list-style: none;
      max-height: 224px;
      position: absolute;
      background: var(--primitive-white);
      width: 280px !important;
      min-width: 280px;
      box-shadow: var(--shadow-100);
      border-radius: 4px;
      border: none;
      overflow-y: auto;
      z-index: 10;
      @include scrollbar();
      &.below {
        top: 45px;
      }
      .vti__dropdown-item {
        height: auto;
        display: flex;
        align-items: flex-start;
        gap: 8px;
        font-size: 14px;
        font-weight: 400;
        padding: 4px 8px;
        cursor: pointer;
        transition: background-color 0.15s;
        white-space: normal; /* allow wrapping */
        line-height: 18px;
        color: var(--map-base-text--2);

        .vti__flag {
          display: inline-block;
          width: 20px;
          height: 15px;
          margin-top: 2px;
          margin-right: 8px;
          visibility: visible !important;
          flex-shrink: 0;
        }

        strong {
          font-weight: 400;
          color: var(--map-base-text-0);
        }
        span {
          color: var(--map-base-text--2);
        }

        &.highlighted {
          background: var(--map-base-purple-o05);
        }
        &[aria-selected='true'] {
          background: var(--map-base-purple-o05);
          strong,
          span {
            color: var(--map-base-purple-symbol-0);
          }
        }
      }
    }

    .vti__input {
      font-size: 16px;
      font-weight: 400;
      padding: 0 12px 0 4px;
      color: var(--map-base-text-0);
      background: transparent;
      border: none;
      width: 100%;
      outline: none;
      border-top-right-radius: 8px;
      border-bottom-right-radius: 8px;
      &::placeholder {
        color: var(--map-base-text--1);
      }
    }
  }
  &__arrow {
    transition: transform 0.15s ease;
  }
}
</style>

<style lang="scss">
.el-form-item.is-error {
  .ivyforms-phone-input.m-phone-number-input {
    border-radius: 8px;
    box-shadow: 0 0 0 1px var(--map-status-error-symbol-1) inset;
    border: 1px solid var(--map-status-error-symbol-0);
    .vue-tel-input {
      border-color: transparent !important;
    }
    &:hover,
    &:focus-within {
      box-shadow: 0 0 0 1px var(--map-status-error-symbol-1) inset;
      border: 1px solid var(--map-status-error-symbol-0);
      .vue-tel-input {
        border-color: transparent !important;
      }
    }
  }
}
</style>
