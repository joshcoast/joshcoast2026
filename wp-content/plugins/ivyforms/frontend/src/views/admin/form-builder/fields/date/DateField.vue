<template>
  <div class="ivyforms-field-date" :class="{ 'ivyforms-field-date--readonly': readOnly }">
    <!-- Date Picker Type -->
    <template v-if="isPickerType">
      <IvyFormItem
        :label="hideLabel ? '' : label"
        :required="required"
        :label-position="labelPosition"
      >
        <IvyTextInput
          :id="'date_' + index"
          :aria-label="label"
          :placeholder="placeholder"
          :model-value="datePickerValue"
          type="text"
          readonly=""
        />
        <div
          v-if="description"
          class="ivyforms-field-date__description regular-14 ivyforms-width-100 ivyforms-mt-6"
        >
          {{ description }}
        </div>
      </IvyFormItem>
    </template>

    <!-- Input Type (manual entry) -->
    <template v-else-if="isInputType">
      <IvyFormItem
        :label="hideLabel ? '' : label"
        :required="required"
        :label-position="labelPosition"
      >
        <IvyTextInput
          :id="'date_' + index"
          :aria-label="label"
          readonly
          :placeholder="placeholder"
          :model-value="datePickerValue"
          type="text"
        />
        <div
          v-if="description"
          class="ivyforms-field-date__description regular-14 ivyforms-width-100 ivyforms-mt-6"
        >
          {{ description }}
        </div>
      </IvyFormItem>
    </template>

    <!-- Dropdown Type -->
    <template v-else-if="isDropdownType">
      <IvyFormItem
        :label="hideLabel ? '' : label"
        :required="required"
        :label-position="labelPosition"
      >
        <div class="ivyforms-flex ivyforms-width-100 ivyforms-gap-12">
          <div class="ivyforms-flex ivyforms-flex-1 ivyforms-flex-direction-column">
            <div class="medium-14 ivyforms-mb-6 ivyforms-field-date__sublabel">
              {{ getDayLabel }}
            </div>
            <IvySelectInput
              :model-value="dropdownDayValue"
              placeholder="--"
              readonly
              @mousedown.prevent
              @click.prevent
            />
          </div>
          <div class="ivyforms-flex ivyforms-flex-1 ivyforms-flex-direction-column">
            <div class="medium-14 ivyforms-mb-6 ivyforms-field-date__sublabel">
              {{ getMonthLabel }}
            </div>
            <IvySelectInput
              :model-value="dropdownMonthValue"
              placeholder="--"
              readonly
              @mousedown.prevent
              @click.prevent
            />
          </div>
          <div class="ivyforms-flex ivyforms-flex-1 ivyforms-flex-direction-column">
            <div class="medium-14 ivyforms-mb-6 ivyforms-field-date__sublabel">
              {{ getYearLabel }}
            </div>
            <IvySelectInput
              :model-value="dropdownYearValue"
              placeholder="--"
              readonly
              @mousedown.prevent
              @click.prevent
            />
          </div>
        </div>
        <div
          v-if="description"
          class="ivyforms-field-date__description regular-14 ivyforms-width-100 ivyforms-mt-6"
        >
          {{ description }}
        </div>
      </IvyFormItem>
    </template>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useFormBuilder } from '@/stores/useFormBuilder'
import { useLabels } from '@/composables/useLabels'
import { useDateField } from '@/composables/useDateField'
import IvySelectInput from '@/views/_components/input/IvySelectInput.vue'
import IvyTextInput from '@/views/_components/input/IvyTextInput.vue'
import IvyFormItem from '@/views/_components/form/IvyFormItem.vue'

const { getLabel } = useLabels()
const props = defineProps<{ fieldIndex: number }>()
const formBuilderStore = useFormBuilder()
const field = computed(() => formBuilderStore.fields.find((f) => f.fieldIndex === props.fieldIndex))
const label = computed(() => field.value?.label || '')
const placeholder = computed(() => field.value?.placeholder || '')
const required = computed(() => field.value?.required || false)
const hideLabel = computed(() => field.value?.hideLabel || false)
const readOnly = computed(() => field.value?.readOnly || false)
const description = computed(() => field.value?.description || '')
const index = computed(() => field.value?.fieldIndex ?? props.fieldIndex)
const modelValue = computed(() => field.value?.defaultValue || '')

const dateFieldType = computed(() => field.value?.dateFieldType || 'picker')
const dateFormat = computed(() => field.value?.dateFormat || 'MM/DD/YYYY')
const labelPosition = computed(() =>
  field.value?.labelPosition === 'default' ? 'top' : field.value?.labelPosition || 'top',
)

const isPickerType = computed(() => dateFieldType.value === 'picker')
const isInputType = computed(() => dateFieldType.value === 'input')
const isDropdownType = computed(() => dateFieldType.value === 'dropdown')

const { parseDateString } = useDateField()

// Parse default value
const parsedDate = computed(() =>
  parseDateString(String(modelValue.value || ''), String(dateFormat.value as unknown as string)),
)

// For date picker and input mode - both use the same formatted value
const datePickerValue = computed(() => modelValue.value || '')

// For dropdown mode
const dropdownDayValue = computed(() => parsedDate.value.day)
const dropdownMonthValue = computed(() => parsedDate.value.month)
const dropdownYearValue = computed(() => parsedDate.value.year)

// Labels based on format
const getDayLabel = computed(() => getLabel('day'))
const getMonthLabel = computed(() => getLabel('month'))
const getYearLabel = computed(() => getLabel('year'))
</script>

<style lang="scss" scoped>
.ivyforms-field-date {
  cursor: default;

  &--readonly {
    opacity: 0.6;
  }

  &__sublabel {
    color: var(--map-base-text-0);
  }

  &__description {
    color: var(--map-base-text-0);
    display: block;
    white-space: normal;
    overflow-wrap: anywhere;
    word-break: break-word;
  }

  :deep(.el-input__inner),
  :deep(.el-input__wrapper) {
    cursor: default;
  }

  :deep(.ivyforms-input.el-input .el-input__wrapper),
  :deep(.ivyforms-input.el-input .el-input__wrapper:hover),
  :deep(.ivyforms-input.el-input .el-input__wrapper.is-hovering),
  :deep(.ivyforms-input.el-input .el-input__wrapper:focus),
  :deep(.ivyforms-input.el-input .el-input__wrapper.is-focused),
  :deep(.ivyforms-input.el-input .el-input__wrapper:active),
  :deep(.ivyforms-input.el-input .el-input__wrapper.is-active) {
    border: 1px solid var(--map-base-dusk-stroke--2);
  }
}
</style>
