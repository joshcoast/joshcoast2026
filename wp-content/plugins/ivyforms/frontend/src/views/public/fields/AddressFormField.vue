<template>
  <div
    :class="[
      'ivyforms-field',
      'ivyforms-field__address',
      'ivyforms-field__address--' + field.id,
      field.cssClasses,
      {
        'ivyforms-field__address--main-label-visible': !field.hideLabel,
        'ivyforms-field__address--readonly': !!field.readOnly,
      },
      `ivyforms-field__address--label-${effectiveLabelPosition}`,
    ]"
  >
    <div
      v-if="
        !field.hideLabel &&
        (effectiveLabelPosition === 'top' || effectiveLabelPosition === 'default')
      "
      class="ivyforms-field__address-label ivyforms-field__address-label-main ivyforms-gap-4 ivyforms-align-items-center ivyforms-mb-8 regular-16"
    >
      {{ field.label }}
    </div>
    <div
      class="ivyforms-field__address__container ivyforms-flex ivyforms-width-100"
      :class="{
        'ivyforms-flex-direction-row ivyforms-align-items-start':
          effectiveLabelPosition === 'left' || effectiveLabelPosition === 'right',
        'ivyforms-flex-direction-column':
          effectiveLabelPosition === 'top' || effectiveLabelPosition === 'default',
      }"
    >
      <div
        v-if="!field.hideLabel && effectiveLabelPosition === 'left'"
        class="ivyforms-field__address-label ivyforms-gap-4 regular-16 ivyforms-flex-shrink-0 ivyforms-mr-16 ivyforms-mb-0 ivyforms-width-fit-content ivyforms-flex ivyforms-align-items-center"
      >
        {{ field.label }}
      </div>
      <div
        class="ivyforms-field__address__layout ivyforms-flex-1 ivyforms-width-100 ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-16"
        :class="{
          'ivyforms-mb-0': effectiveLabelPosition === 'left' || effectiveLabelPosition === 'right',
        }"
      >
        <!-- Row 1: Street Address (full width) -->
        <div class="ivyforms-flex ivyforms-flex-direction-row">
          <div v-if="getSubVisible(0)" class="ivyforms-width-100">
            <IvyFormItem
              :required="isSubRequired(0)"
              :prop="`${field.type}_${field.fieldIndex}.${addressFields[0].type}`"
              :show-info="!!getSubDescription(0)"
              :info-text="getSubDescription(0) || ''"
              :error="getSubfieldError(addressFields[0].type)"
              :show-info-icon="false"
            >
              <template #label>
                <div
                  v-if="!addressFields[0].hide"
                  class="ivyforms-field__address-label medium-14 ivyforms-flex ivyforms-align-items-center"
                >
                  <span class="ivyforms-nowrap">{{ addressFields[0].label }}</span>
                </div>
                <div v-else class="ivyforms-placeholder-hidden" aria-hidden="true"></div>
              </template>
              <IvyTextInput
                :id="addressFields[0].fieldID"
                :model-value="getFieldValue(addressFields[0].type)"
                class="ivyforms-width-100"
                type="text"
                :placeholder="addressFields[0].placeholder"
                :disabled="isDisabled"
                :required="isSubRequired(0)"
                :aria-required="isSubRequired(0) ? 'true' : 'false'"
                @input="updateFieldValue(addressFields[0].type, $event)"
                @blur="(e) => syncFieldFromDom(addressFields[0].type, e)"
              />
            </IvyFormItem>
          </div>
        </div>
        <!-- Row 2: Address Line 2 (full width) -->
        <div class="ivyforms-flex ivyforms-flex-direction-row">
          <div v-if="getSubVisible(1)" class="ivyforms-width-100">
            <IvyFormItem
              :required="isSubRequired(1)"
              :prop="`${field.type}_${field.fieldIndex}.${addressFields[1].type}`"
              :show-info="!!getSubDescription(1)"
              :info-text="getSubDescription(1) || ''"
              :error="getSubfieldError(addressFields[1].type)"
              :show-info-icon="false"
            >
              <template #label>
                <div
                  v-if="!addressFields[1].hide"
                  class="ivyforms-field__address-label medium-14 ivyforms-flex ivyforms-align-items-center"
                >
                  <span class="ivyforms-nowrap">{{ addressFields[1].label }}</span>
                </div>
                <div v-else class="ivyforms-placeholder-hidden" aria-hidden="true"></div>
              </template>
              <IvyTextInput
                :id="addressFields[1].fieldID"
                :model-value="getFieldValue(addressFields[1].type)"
                class="ivyforms-width-100"
                type="text"
                :placeholder="addressFields[1].placeholder"
                :disabled="isDisabled"
                :required="isSubRequired(1)"
                :aria-required="isSubRequired(1) ? 'true' : 'false'"
                @input="updateFieldValue(addressFields[1].type, $event)"
                @blur="(e) => syncFieldFromDom(addressFields[1].type, e)"
              />
            </IvyFormItem>
          </div>
        </div>
        <!-- Row 3: State (full width) -->
        <div class="ivyforms-flex ivyforms-flex-direction-row">
          <div v-if="getSubVisible(3)" class="ivyforms-width-100">
            <IvyFormItem
              :required="isSubRequired(3)"
              :prop="`${field.type}_${field.fieldIndex}.${addressFields[3].type}`"
              :show-info="!!getSubDescription(3)"
              :info-text="getSubDescription(3) || ''"
              :error="getSubfieldError(addressFields[3].type)"
              :show-info-icon="false"
            >
              <template #label>
                <div
                  v-if="!addressFields[3].hide"
                  class="ivyforms-field__address-label medium-14 ivyforms-flex ivyforms-align-items-center"
                >
                  <span class="ivyforms-nowrap">{{ addressFields[3].label }}</span>
                </div>
                <div v-else class="ivyforms-placeholder-hidden" aria-hidden="true"></div>
              </template>
              <IvyTextInput
                :id="addressFields[3].fieldID"
                :model-value="getFieldValue(addressFields[3].type)"
                class="ivyforms-width-100"
                type="text"
                :placeholder="addressFields[3].placeholder"
                :disabled="isDisabled"
                :required="isSubRequired(3)"
                :aria-required="isSubRequired(3) ? 'true' : 'false'"
                @input="updateFieldValue(addressFields[3].type, $event)"
                @blur="(e) => syncFieldFromDom(addressFields[3].type, e)"
              />
            </IvyFormItem>
          </div>
        </div>
        <!-- Row 4: City, Zip, Country (three fields in one row) -->
        <div class="ivyforms-flex ivyforms-flex-direction-row ivyforms-gap-16">
          <template
            v-for="(fieldData, idx) in [addressFields[2], addressFields[4], addressFields[5]]"
            :key="fieldData.fieldID"
          >
            <div v-if="getSubVisible(idx === 0 ? 2 : idx === 1 ? 4 : 5)" class="ivyforms-width-100">
              <IvyFormItem
                :required="isSubRequired(idx === 0 ? 2 : idx === 1 ? 4 : 5)"
                :prop="`${field.type}_${field.fieldIndex}.${fieldData.type}`"
                :show-info="!!getSubDescription(idx === 0 ? 2 : idx === 1 ? 4 : 5)"
                :info-text="getSubDescription(idx === 0 ? 2 : idx === 1 ? 4 : 5) || ''"
                :error="getSubfieldError(fieldData.type)"
                :show-info-icon="false"
              >
                <template #label>
                  <div
                    v-if="!fieldData.hide"
                    class="ivyforms-field__address-label medium-14 ivyforms-flex ivyforms-align-items-center"
                  >
                    <span class="ivyforms-nowrap">{{ fieldData.label }}</span>
                  </div>
                  <div v-else class="ivyforms-placeholder-hidden" aria-hidden="true"></div>
                </template>
                <template v-if="fieldData.type === 'country'">
                  <IvySelectInput
                    :id="fieldData.fieldID"
                    v-model="countryValue"
                    :error="!!getSubfieldError('country')"
                    :placeholder="getLabel('select_country')"
                    :disabled="isDisabled"
                    :required="isSubRequired(idx === 0 ? 2 : idx === 1 ? 4 : 5)"
                    filterable
                    :aria-required="
                      isSubRequired(idx === 0 ? 2 : idx === 1 ? 4 : 5) ? 'true' : 'false'
                    "
                    @update:model-value="(val) => updateFieldValue('country', val)"
                  >
                    <IvySelectOption value="">{{ getLabel('select_country') }}</IvySelectOption>
                    <IvySelectOption
                      v-for="country in countryOptions"
                      :key="country"
                      :value="country"
                      >{{ country }}</IvySelectOption
                    >
                  </IvySelectInput>
                </template>
                <template v-else>
                  <IvyTextInput
                    :id="fieldData.fieldID"
                    :model-value="getFieldValue(fieldData.type)"
                    class="ivyforms-width-100"
                    type="text"
                    :placeholder="fieldData.placeholder"
                    :disabled="isDisabled"
                    :required="isSubRequired(idx === 0 ? 2 : idx === 1 ? 4 : 5)"
                    :aria-required="
                      isSubRequired(idx === 0 ? 2 : idx === 1 ? 4 : 5) ? 'true' : 'false'
                    "
                    @input="updateFieldValue(fieldData.type, $event)"
                    @blur="(e) => syncFieldFromDom(fieldData.type, e)"
                  />
                </template>
              </IvyFormItem>
            </div>
          </template>
        </div>
      </div>
      <div
        v-if="!field.hideLabel && effectiveLabelPosition === 'right'"
        class="ivyforms-field__address-label ivyforms-gap-4 regular-16 ivyforms-flex-shrink-0 ivyforms-ml-16 ivyforms-mb-0 ivyforms-width-fit-content ivyforms-flex ivyforms-align-items-center"
      >
        {{ field.label }}
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, inject, onMounted, watch } from 'vue'
import { useLabels } from '@/composables/useLabels'
import { useAutofillDetection, type AutofillField } from '@/composables/useAutofillDetection'
import { countryList } from '@/constants/countries'
import IvySelectInput from '@/views/_components/input/IvySelectInput.vue'
import IvySelectOption from '@/views/_components/input/IvySelectOption.vue'
import type { Field } from '@/types/field'
const { getLabel } = useLabels()

// Inject clearFieldError function from parent
const clearFieldError = inject<(fieldKey: string) => void>('clearFieldError', () => {})

interface AddressFieldValue {
  [key: string]: string | undefined
  fullAddress?: string
}
interface FieldProps {
  modelValue: AddressFieldValue
  field: Field
  disabled?: boolean
  error?: string
  fieldErrors?: Record<string, string>
}

const props = withDefaults(defineProps<FieldProps>(), {
  modelValue: () => ({}),
  disabled: false,
  error: '',
  fieldErrors: () => ({}),
})
const emit = defineEmits(['update:modelValue'])

const isDisabled = computed(() => !!props.disabled || !!props.field.readOnly)

// Always use the correct order for address fields
const addressFieldOrder = ['streetAddress', 'addressLine2', 'city', 'state', 'zip', 'country']
const addressFieldTypes = computed(() => {
  // If the field has addressFieldTypes, reorder to match the required order
  const types = props.field.addressFieldTypes || [
    'streetAddress',
    'addressLine2',
    'city',
    'state',
    'zip',
    'country',
  ]
  // Map to required order, filter out missing, and fill with empty if needed
  return addressFieldOrder.map((t) => types.find((x) => x === t) || t)
})

const countryOptions = countryList.map((c) => c.name)

const addressFields = computed(() => {
  return addressFieldTypes.value.map((fieldType) => {
    const rawLabel = ((props.field as Record<string, string>)[`${fieldType}Label`] ?? '') as string
    const isCleared = rawLabel.trim() === ''
    const explicitHide = (props.field as Record<string, boolean>)[`${fieldType}HideLabel`] || false
    const hide = explicitHide || isCleared
    return {
      type: fieldType,
      label: rawLabel,
      placeholder: (props.field as Record<string, string>)[`${fieldType}Placeholder`] || '',
      fieldID: `ivyforms-field__address-${fieldType}_${(props.field.formId || '').toString()}_${(props.field.fieldIndex || '').toString()}`,
      hide,
    }
  })
})

const isSubRequired = (idx: number): boolean => {
  const t = addressFields.value[idx]?.type
  return !!(props.field as Record<string, unknown>)[`${t}Required`]
}
const getSubDescription = (idx: number): string => {
  const t = addressFields.value[idx]?.type
  return ((props.field as Record<string, string>)[`${t}Description`] || '') as string
}
const getSubfieldError = (fieldType: string): string => {
  const subKey = `${props.field.type}_${props.field.fieldIndex}.${fieldType}`
  return props.fieldErrors?.[subKey] || ''
}
const getFieldValue = (fieldType: string) =>
  props.modelValue?.[fieldType] ||
  (fieldType === 'country' ? props.field.countryDefaultValue || '' : '')
function updateFieldValue(fieldType: string, value: string, autofill = false) {
  if (autofill && fieldType === 'country' && props.modelValue?.country) {
    return
  }
  const newModelValue: AddressFieldValue = { ...props.modelValue, [fieldType]: value }
  emit('update:modelValue', newModelValue)

  const subKey = `${props.field.type}_${props.field.fieldIndex}.${fieldType}`
  clearFieldError(subKey)
}

const countryValue = computed({
  get: () => getFieldValue('country'),
  set: (val: string) => updateFieldValue('country', val),
})

const getSubVisible = (idx: number): boolean => {
  const t = addressFields.value[idx]?.type
  return (props.field as Record<string, boolean>)[`${t}Visible`] !== false
}

// Sync field from DOM on blur (includes autofill)
function syncFieldFromDom(fieldType: string, e: FocusEvent) {
  const el = e.target as HTMLInputElement | null
  if (!el) return
  if (el.value && el.value !== getFieldValue(fieldType)) {
    updateFieldValue(fieldType, el.value)
  }
}

// Setup autofill detection using the composable
const autofillFields = computed<AutofillField[]>(() => {
  return addressFields.value.map((fieldData) => ({
    id: fieldData.fieldID,
    getValue: () => getFieldValue(fieldData.type),
    setValue: (value: string) => updateFieldValue(fieldData.type, value, true),
    onClearError: () => {
      const subKey = `${props.field.type}_${props.field.fieldIndex}.${fieldData.type}`
      clearFieldError(subKey)
    },
  }))
})

const { restartDetection, checkForInitialAutofill } = useAutofillDetection({
  fields: autofillFields.value,
  pollingDuration: 7000, // Increased for slow autofill
  pollingInterval: 100,
  initialCheckDelay: 100, // Faster initial check
})

// Manual check on mount and when fields become available
onMounted(() => {
  // Initial check immediately after mount
  setTimeout(checkForInitialAutofill, 50)
  // Additional checks for delayed autofill
  setTimeout(checkForInitialAutofill, 300)
  setTimeout(checkForInitialAutofill, 1000)
})

// Watch for field changes and restart detection
watch(
  () => addressFields.value.map((f) => f.fieldID),
  () => {
    setTimeout(restartDetection, 100)
  },
  { flush: 'post' },
)

const effectiveLabelPosition = computed(() => {
  const position = props.field?.labelPosition
  if (position === 'default' || !position) {
    return 'top'
  }
  return position
})
</script>

<style lang="scss" scoped>
.ivyforms-placeholder-hidden {
  height: 20px;
  visibility: hidden;
  display: block;
}
.ivyforms-field__address {
  :deep(.el-form-item__label) {
    justify-content: flex-start;
  }
  &--readonly {
    opacity: 0.7;
    :deep(.el-input__wrapper),
    :deep(.el-input__wrapper:hover),
    :deep(.el-input__wrapper.is-focus) {
      border-color: var(--map-base-dusk-stroke--2) !important;
      box-shadow: none !important;
    }
  }
  &-label {
    color: var(--map-base-text-0);
    display: inline-flex;
    // Default and top positioning
    &:not(&--left):not(&--right) {
      //@extend .ivyforms-mb-8;
    }
  }
  &__container {
    display: flex;
    width: 100%;
    &--flex-row {
      flex-direction: row;
      align-items: flex-start;
      gap: 0;
    }
    &--flex-column {
      flex-direction: column;
    }
  }
  &__layout {
    flex: 1;
    width: 100%;
    &--with-left-label,
    &--with-right-label {
      @extend .ivyforms-mb-0;
    }
  }
}
</style>
