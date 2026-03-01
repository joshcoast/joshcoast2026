<template>
  <div
    class="ivyforms-address-field-options ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-16"
  >
    <div class="ivyforms-basic-field-options__field-meta ivyforms-gap-4 ivyforms-pt-8">
      <div class="ivyforms-basic-field-options__field-meta-title medium-16">
        {{ getLabel('address_field') }}
        <span class="ivyforms-basic-field-options__field-meta-id regular-16"
          >(ID {{ formattedId }})</span
        >
      </div>
      <IvyDivider />
    </div>

    <!-- Main Field Label -->
    <IvyFormItem :label="getLabel('label')" secondary>
      <IvyTextInput :model-value="selectedField.label" @input="updateField('label', $event)" />
    </IvyFormItem>

    <!-- Hide Label -->
    <div class="ivyforms-address-field-options__checkbox-row ivyforms-flex ivyforms-gap-16">
      <IvyCheckbox
        v-model="hideLabel"
        priority="secondary"
        :type="'checkmark'"
        @change="updateField('hideLabel', $event)"
      >
        {{ getLabel('hide_label') }}
      </IvyCheckbox>
    </div>

    <IvyDivider />

    <!-- Options List Label -->
    <div class="ivyforms-address-field-options__options-list-label medium-14">
      {{ getLabel('address_fields') }}:
    </div>

    <!-- Address Field Options (fixed 6 inputs) -->
    <div
      class="ivyforms-address-field-options__address-options ivyforms-flex ivyforms-flex-direction-column"
    >
      <FieldOptionsItem
        v-for="(subfield, idx) in addressFields"
        :key="subfield.type"
        :title="subfield.label || getFieldLabel(subfield.type)"
        :collapsed="collapsedStates[idx]"
        :show-trash="false"
        :show-drag="false"
        :dropdown-icon="{
          collapsed: 'chevron-down',
          expanded: 'chevron-up',
          category: 'arrows',
          type: 'outline',
        }"
        @update:collapsed="(v) => (collapsedStates[idx] = v)"
      >
        <template #header-actions>
          <IvyCheckbox
            v-model="subfield.visible"
            class="ivyforms-address-field-options__option-item-visible-checkbox"
            :aria-label="getLabel('show_field')"
            priority="secondary"
            type="withMarker"
            @change="updateSubfield(idx, 'visible', $event)"
          >
            {{ getLabel('') }}
          </IvyCheckbox>
        </template>
        <!-- Sublabel -->
        <IvyFormItem :label="getLabel('sublabel')" secondary>
          <IvyTextInput v-model="subfield.label" @input="updateSubfield(idx, 'label', $event)" />
        </IvyFormItem>

        <!-- Description Message -->
        <IvyFormItem :label="getLabel('description_message')" secondary>
          <IvyTextInput
            v-model="subfield.description"
            :placeholder="getLabel('enter_description_here')"
            @input="updateSubfield(idx, 'description', $event)"
          />
        </IvyFormItem>

        <!-- Hide SubLabel -->
        <IvyCheckbox
          v-model="subfield.hideLabel"
          priority="secondary"
          :type="'checkmark'"
          @change="updateSubfield(idx, 'hideLabel', $event)"
        >
          {{ getLabel('hide_label') }}
        </IvyCheckbox>

        <IvyDivider />

        <!-- Required (per sublabel) -->
        <IvyCheckbox
          v-model="subfield.required"
          priority="secondary"
          :type="'checkmark'"
          @change="onSubRequiredChange(idx, $event)"
        >
          {{ getLabel('required') }}
        </IvyCheckbox>

        <!-- Required Message (per sublabel) -->
        <IvyFormItem :label="getLabel('required_message')" secondary>
          <IvyTextInput
            v-model="subfield.requiredMessage"
            :placeholder="getLabel('this_field_is_required')"
            @input="updateSubfield(idx, 'requiredMessage', $event)"
          />
        </IvyFormItem>

        <IvyDivider />

        <!-- Placeholder -->
        <IvyFormItem :label="getLabel('placeholder')" secondary>
          <IvyTextInput
            v-model="subfield.placeholder"
            :placeholder="getLabel('enter_text_here')"
            @input="updateSubfield(idx, 'placeholder', $event)"
          />
        </IvyFormItem>

        <!-- Default Value (only for country) -->
        <IvyFormItem
          v-if="subfield.type === 'country'"
          :label="getLabel('default_value')"
          secondary
        >
          <IvySelectInput
            v-model="countryDefaultValue"
            :placeholder="getLabel('select_country')"
            @change="onCountryDefaultChange(idx, $event)"
          >
            <IvySelectOption
              v-for="country in countryOptions"
              :key="country"
              :value="country"
              secondary
              >{{ country }}</IvySelectOption
            >
          </IvySelectInput>
        </IvyFormItem>
      </FieldOptionsItem>
    </div>

    <IvyDivider />

    <!-- CSS Classes -->
    <IvyFormItem :label="getLabel('css_classes')" secondary>
      <IvyTextInput
        :model-value="selectedField.cssClasses"
        @input="updateField('cssClasses', $event)"
      />
    </IvyFormItem>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { useFormBuilder } from '@/stores/useFormBuilder.ts'
import { useLabels } from '@/composables/useLabels'
import IvyFormItem from '@/views/_components/form/IvyFormItem.vue'
import IvyTextInput from '@/views/_components/input/IvyTextInput.vue'
import IvyCheckbox from '@/views/_components/checkbox/IvyCheckbox.vue'
import IvyDivider from '@/views/_components/divider/IvyDivider.vue'
import FieldOptionsItem from '../../_components/FieldOptionsItem.vue'
import IvySelectInput from '@/views/_components/input/IvySelectInput.vue'
import IvySelectOption from '@/views/_components/input/IvySelectOption.vue'
import { countryList } from '@/constants/countries'
import type { AddressSubField } from '@/types/field'

const { getLabel } = useLabels()
const { selectedField } = storeToRefs(useFormBuilder())
const { updateSelectedField, updateFieldOptions } = useFormBuilder()

// Padded ID like '01' for small numbers
const formattedId = computed(() => {
  const id = selectedField.value?.id ?? ''
  if (typeof id === 'number' && id) return String(id).padStart(2, '0')
  return getLabel('not_set')
})

const addressFields = computed<AddressSubField[]>({
  get: () => selectedField.value?.addressFields || [],
  set: (val) => updateField('addressFields', val),
})

const collapsedStates = ref<boolean[]>([])
watch(
  addressFields,
  (newFields, oldFields) => {
    const newLength = newFields.length
    if (!oldFields || (oldFields as AddressSubField[]).length === 0) {
      collapsedStates.value = new Array(newLength).fill(true)
      if (newLength > 0) collapsedStates.value[0] = false
      return
    }
    const prevStates = collapsedStates.value
    collapsedStates.value = new Array(newLength).fill(true)
    for (let i = 0; i < newLength; i++) {
      collapsedStates.value[i] = prevStates[i] ?? false
    }
  },
  { immediate: true },
)

const hideLabel = computed({
  get: () => selectedField.value?.hideLabel || false,
  set: (value) => updateField('hideLabel', value),
})

const countryOptions = countryList.map((c) => c.name)
const countryDefaultValue = computed({
  get() {
    const countryField = addressFields.value.find((f) => f.type === 'country')
    return countryField?.value || ''
  },
  set(val) {
    const countryField = addressFields.value.find((f) => f.type === 'country')
    if (countryField) countryField.value = val
  },
})
function onCountryDefaultChange(idx, value) {
  updateSubfield(idx, 'value', value)
}

const getFieldLabel = (fieldType: string): string => {
  switch (fieldType) {
    case 'streetAddress':
      return getLabel('street_address')
    case 'addressLine2':
      return getLabel('address_line_2')
    case 'city':
      return getLabel('city')
    case 'state':
      return getLabel('state')
    case 'zip':
      return getLabel('zip')
    case 'country':
      return getLabel('country')
    default:
      return fieldType
  }
}

const updateField = (index: string, value: unknown) => {
  updateSelectedField(index, value)
  updateFieldOptions(index, value)
}

const updateSubfield = (idx: number, key: keyof AddressSubField, value: unknown) => {
  const arr = [...addressFields.value]
  arr[idx] = { ...arr[idx], [key]: value }
  updateField('addressFields', arr)
}

const onSubRequiredChange = (idx: number, val: boolean) => {
  updateSubfield(idx, 'required', !!val)
}
</script>

<style scoped lang="scss">
.ivyforms-address-field-options {
  :deep(.ivyforms-form-item),
  :deep(.el-form-item) {
    margin-bottom: 0;
    gap: var(--Spacing-xs, 6px);
  }

  &__options-list-label {
    font-size: 14px;
    font-weight: 500;
    color: var(--map-base-text-0);
    line-height: 20px;
  }
}
</style>
