<template>
  <div
    class="ivyforms-field__address ivyforms-width-100"
    :class="[
      { 'ivyforms-field__address--readonly': !!field?.readOnly },
      `ivyforms-field__address--label-${effectiveLabelPosition}`,
    ]"
  >
    <div
      v-if="
        !field?.hideLabel &&
        (effectiveLabelPosition === 'top' || effectiveLabelPosition === 'default')
      "
      class="ivyforms-field__address-label ivyforms-mb-8 regular-16"
    >
      {{ field?.label || getLabel('address') }}
    </div>
    <div
      class="ivyforms-field__address-container ivyforms-width-100"
      :class="{
        'ivyforms-field__address-container--flex-row':
          effectiveLabelPosition === 'left' || effectiveLabelPosition === 'right',
        'ivyforms-field__address-container--flex-column':
          effectiveLabelPosition === 'top' || effectiveLabelPosition === 'default',
      }"
    >
      <template v-if="effectiveLabelPosition === 'left'">
        <div
          v-if="!field?.hideLabel"
          class="ivyforms-field__address-label ivyforms-field__address-label--left regular-16 ivyforms-mr-16 ivyforms-flex ivyforms-align-items-center"
        >
          {{ field?.label || getLabel('address') }}
        </div>
        <div
          class="ivyforms-field__address-fields ivyforms-width-100 ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-16"
        >
          <!-- First 3 fields: streetAddress, addressLine2, city -->
          <div
            v-for="fieldData in addressFields.slice(0, 3)"
            :key="fieldData.type"
            class="ivyforms-field__address-row ivyforms-field__address-row--full ivyforms-flex ivyforms-flex-direction-row ivyforms-width-100"
          >
            <div
              :class="[
                'ivyforms-field__address-field ivyforms-flex-1',
                fieldData.type.toLowerCase() + '-address',
              ]"
            >
              <IvyFormItem
                :label="fieldData.hideLabel ? '' : fieldData.label"
                :required="fieldData.required"
              >
                <IvyTextInput
                  :id="fieldData.id"
                  :aria-label="fieldData.label"
                  readonly
                  :model-value="fieldData.value"
                  type="text"
                  :placeholder="fieldData.placeholder"
                />
                <div
                  v-if="fieldData.description"
                  class="ivyforms-field__address-description regular-14"
                >
                  {{ fieldData.description }}
                </div>
              </IvyFormItem>
            </div>
          </div>
          <!-- Last 3 fields: state, zip, country -->
          <div
            class="ivyforms-field__address-row ivyforms-field__address-row--three-cols ivyforms-flex ivyforms-flex-direction-row ivyforms-gap-16 ivyforms-width-100"
          >
            <div
              v-for="fieldData in addressFields.slice(3, 6)"
              :key="fieldData.type"
              :class="[
                'ivyforms-field__address-field ivyforms-flex-1',
                fieldData.type.toLowerCase() + '-address',
              ]"
            >
              <IvyFormItem
                :label="fieldData.hideLabel ? '' : fieldData.label"
                :required="fieldData.required"
              >
                <template v-if="fieldData.type === 'country'">
                  <IvySelectInput
                    :id="fieldData.id"
                    v-model="countryValue"
                    :placeholder="getLabel('select_country')"
                    disabled
                    readonly
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
                    :id="fieldData.id"
                    :aria-label="fieldData.label"
                    readonly
                    :model-value="fieldData.value"
                    type="text"
                    :placeholder="fieldData.placeholder"
                  />
                </template>
                <div
                  v-if="fieldData.description"
                  class="ivyforms-field__address-description regular-14"
                >
                  {{ fieldData.description }}
                </div>
              </IvyFormItem>
            </div>
          </div>
        </div>
      </template>
      <template v-else-if="effectiveLabelPosition === 'right'">
        <div
          class="ivyforms-field__address-fields ivyforms-width-100 ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-16"
        >
          <!-- First 3 fields: streetAddress, addressLine2, city -->
          <div
            v-for="fieldData in addressFields.slice(0, 3)"
            :key="fieldData.type"
            class="ivyforms-field__address-row ivyforms-field__address-row--full ivyforms-flex ivyforms-flex-direction-row ivyforms-width-100"
          >
            <div
              :class="[
                'ivyforms-field__address-field ivyforms-flex-1',
                fieldData.type.toLowerCase() + '-address',
              ]"
            >
              <IvyFormItem
                :label="fieldData.hideLabel ? '' : fieldData.label"
                :required="fieldData.required"
              >
                <IvyTextInput
                  :id="fieldData.id"
                  :aria-label="fieldData.label"
                  readonly
                  :model-value="fieldData.value"
                  type="text"
                  :placeholder="fieldData.placeholder"
                />
                <div
                  v-if="fieldData.description"
                  class="ivyforms-field__address-description regular-14"
                >
                  {{ fieldData.description }}
                </div>
              </IvyFormItem>
            </div>
          </div>
          <!-- Last 3 fields: state, zip, country -->
          <div
            class="ivyforms-field__address-row ivyforms-field__address-row--three-cols ivyforms-flex ivyforms-flex-direction-row ivyforms-gap-16 ivyforms-width-100"
          >
            <div
              v-for="fieldData in addressFields.slice(3, 6)"
              :key="fieldData.type"
              :class="[
                'ivyforms-field__address-field ivyforms-flex-1',
                fieldData.type.toLowerCase() + '-address',
              ]"
            >
              <IvyFormItem
                :label="fieldData.hideLabel ? '' : fieldData.label"
                :required="fieldData.required"
              >
                <template v-if="fieldData.type === 'country'">
                  <IvySelectInput
                    :id="fieldData.id"
                    v-model="countryValue"
                    :placeholder="getLabel('select_country')"
                    disabled
                    readonly
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
                    :id="fieldData.id"
                    :aria-label="fieldData.label"
                    readonly
                    :model-value="fieldData.value"
                    type="text"
                    :placeholder="fieldData.placeholder"
                  />
                </template>
                <div
                  v-if="fieldData.description"
                  class="ivyforms-field__address-description regular-14"
                >
                  {{ fieldData.description }}
                </div>
              </IvyFormItem>
            </div>
          </div>
        </div>
        <div
          v-if="!field?.hideLabel"
          class="ivyforms-field__address-label ivyforms-field__address-label--right regular-16 ivyforms-ml-16 ivyforms-flex ivyforms-align-items-center"
        >
          {{ field?.label || getLabel('address') }}
        </div>
      </template>
      <template v-else>
        <div
          class="ivyforms-field__address-fields ivyforms-width-100 ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-16"
        >
          <!-- First 3 fields: streetAddress, addressLine2, city -->
          <div
            v-for="fieldData in addressFields.slice(0, 3)"
            :key="fieldData.type"
            class="ivyforms-field__address-row ivyforms-field__address-row--full ivyforms-flex ivyforms-flex-direction-row ivyforms-width-100"
          >
            <div
              :class="[
                'ivyforms-field__address-field ivyforms-flex-1',
                fieldData.type.toLowerCase() + '-address',
              ]"
            >
              <IvyFormItem
                :label="fieldData.hideLabel ? '' : fieldData.label"
                :required="fieldData.required"
              >
                <IvyTextInput
                  :id="fieldData.id"
                  :aria-label="fieldData.label"
                  readonly
                  :model-value="fieldData.value"
                  type="text"
                  :placeholder="fieldData.placeholder"
                />
                <div
                  v-if="fieldData.description"
                  class="ivyforms-field__address-description regular-14"
                >
                  {{ fieldData.description }}
                </div>
              </IvyFormItem>
            </div>
          </div>
          <!-- Last 3 fields: state, zip, country -->
          <div
            class="ivyforms-field__address-row ivyforms-field__address-row--three-cols ivyforms-flex ivyforms-flex-direction-row ivyforms-gap-16 ivyforms-width-100"
          >
            <div
              v-for="fieldData in addressFields.slice(3, 6)"
              :key="fieldData.type"
              :class="[
                'ivyforms-field__address-field ivyforms-flex-1',
                fieldData.type.toLowerCase() + '-address',
              ]"
            >
              <IvyFormItem
                :label="fieldData.hideLabel ? '' : fieldData.label"
                :required="fieldData.required"
              >
                <template v-if="fieldData.type === 'country'">
                  <IvySelectInput
                    :id="fieldData.id"
                    v-model="countryValue"
                    :placeholder="getLabel('select_country')"
                    disabled
                    readonly
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
                    :id="fieldData.id"
                    :aria-label="fieldData.label"
                    readonly
                    :model-value="fieldData.value"
                    type="text"
                    :placeholder="fieldData.placeholder"
                  />
                </template>
                <div
                  v-if="fieldData.description"
                  class="ivyforms-field__address-description regular-14"
                >
                  {{ fieldData.description }}
                </div>
              </IvyFormItem>
            </div>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useFormBuilder } from '@/stores/useFormBuilder'
import { useLabels } from '@/composables/useLabels'
import { countryList } from '@/constants/countries'
import type { AddressSubField } from '@/types/field.d.ts'

const { getLabel } = useLabels()

const props = defineProps<{ fieldIndex: number }>()
const formBuilderStore = useFormBuilder()

const field = computed(() => formBuilderStore.fields.find((f) => f.fieldIndex === props.fieldIndex))

const addressFields = computed<AddressSubField[]>(() =>
  (field.value?.addressFields || []).filter((f: AddressSubField) => f.visible !== false),
)

const countryOptions = countryList.map((c) => c.name)

const countryValue = computed({
  get: () => addressFields.value.find((f) => f.type === 'country')?.value || '',
  set: () => {}, // No-op, readonly in builder
})

const effectiveLabelPosition = computed(() => {
  const position = field.value?.labelPosition
  if (position === 'default' || !position) {
    return 'top'
  }
  return position
})
</script>

<style lang="scss" scoped>
.ivyforms-field__address {
  cursor: default;
  .ivyforms-field__address-label {
    color: var(--map-base-text-0);
  }
  &--readonly {
    opacity: 0.6;
  }

  &--label-left,
  &--label-right {
    .ivyforms-field__address-container {
      display: flex;
      flex-direction: row;
      align-items: flex-start;
    }
    .ivyforms-field__address-label {
      &--left {
        display: flex;
        align-items: center;
        margin-right: 16px;
        min-width: fit-content;
      }
      &--right {
        display: flex;
        align-items: center;
        margin-left: 16px;
        min-width: fit-content;
      }
    }
  }

  &--label-top,
  &--label-default {
    .ivyforms-field__address {
      &-container {
        display: flex;
        flex-direction: column;
      }
    }
  }

  &-row {
    display: flex;
    flex-direction: row;
    width: 100%;
    &--full {
      width: 100%;
    }
    &--three-cols {
      gap: 16px;
      width: 100%;
    }
  }

  &-description {
    font-size: 14px;
    margin-top: 4px;
  }

  .ivyforms-form-item {
    margin-bottom: 0;
    cursor: default;

    :deep(.ivyforms-form-item__label) {
      display: flex;
      align-items: center;
      color: var(--map-base-text-0);
      font-size: 14px;
      font-style: normal;
      font-weight: 500;
      height: 20px !important;
      cursor: default !important;
    }

    :deep(.el-form-item__label) {
      cursor: default;
    }

    // Text inputs styling to match TextField behavior
    .ivyforms-input.el-input {
      :deep(.el-input__wrapper:hover),
      :deep(.el-input__wrapper.is-focus),
      :deep(.el-input__wrapper) {
        border-radius: var(--Radius-radius-md, 8px);
        border: 1px solid var(--map-base-dusk-stroke--2);
        background: transparent;
        box-shadow: none;
        padding: 0 12px;
        transition: none;
        cursor: default;
      }
      :deep(input) {
        border: none;
        background: transparent;
        cursor: default;
        box-shadow: none;

        &:focus {
          outline: none;
          border: 1px solid transparent;
          background: none;
          box-shadow: none;
        }
      }
    }

    // Select inputs styling to prevent focus
    .ivyforms-form-item-select.el-select {
      cursor: default;
      :deep(.el-select__wrapper:hover),
      :deep(.el-select__wrapper.is-focus),
      :deep(.el-select__wrapper) {
        border-radius: var(--Radius-radius-md, 8px);
        border: 1px solid var(--map-base-dusk-stroke--2);
        background: transparent !important;
        box-shadow: none;
        padding: 0 12px;
        transition: none;
        cursor: default;
      }
      :deep(.el-input) {
        cursor: default;
      }
      :deep(.el-input__wrapper) {
        cursor: default;
        box-shadow: none;
        background: transparent !important;
      }
      :deep(.el-input__inner) {
        cursor: default;
        &:focus {
          outline: none;
          border: 1px solid transparent;
          background: none;
          box-shadow: none;
        }
      }
      :deep(.el-select__caret) {
        cursor: default;
      }
    }
  }
}
</style>
