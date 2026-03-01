<template>
  <div class="ivyforms-field__select">
    <IvyFormItem
      :label="hideLabel ? '' : label"
      :required="required"
      :label-position="labelPosition"
    >
      <IvySelectInput
        :id="String(index)"
        v-model="selectedValue"
        class="ivyforms-field__select-dropdown"
        :multiple="isMultiSelect"
        :placeholder="placeholder"
        readonly
      >
        <IvySelectOption
          v-for="option in selectOptions"
          :key="option.id || option.value"
          :value="option.value"
          :label="option.label"
        >
        </IvySelectOption>
      </IvySelectInput>
      <div v-if="description" class="ivyforms-description-message regular-14">
        {{ description }}
      </div>
    </IvyFormItem>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useFormBuilder } from '@/stores/useFormBuilder'
import type { Choice, Field } from '@/types/field'

const props = defineProps<{ fieldIndex: number }>()
const formBuilderStore = useFormBuilder()

const field = computed<Field | undefined>(() =>
  formBuilderStore.fields.find((f: Field) => f.fieldIndex === props.fieldIndex),
)
const label = computed(() => field.value?.label || '')
const required = computed(() => field.value?.required || false)
const hideLabel = computed(() => field.value?.hideLabel || false)
const index = computed(() => field.value?.fieldIndex ?? props.fieldIndex)
const description = computed(() => field.value?.description || '')

const selectOptions = computed<Choice[]>(() => field.value?.fieldOptions || [])

const isMultiSelect = computed(() => {
  const value = field.value
  return value && typeof value === 'object' && value.type === 'multi-select'
})

const placeholder = computed(() => field.value?.placeholder || '')

const selectedValue = computed({
  get() {
    const opts = field.value?.fieldOptions || []
    if (isMultiSelect.value) {
      // Return array of default values for multi-select
      return opts.filter((opt) => opt.isDefault).map((opt) => opt.value)
    } else {
      // Return single default value for select
      const def = opts.find((opt) => opt.isDefault)
      return def ? String(def.value) : ''
    }
  },
  set(newValue) {
    // Update isDefault on options when value changes
    if (!field.value) return
    if (isMultiSelect.value && Array.isArray(newValue)) {
      field.value.fieldOptions.forEach((opt) => {
        opt.isDefault = newValue.includes(opt.value)
      })
    } else {
      field.value.fieldOptions.forEach((opt) => {
        opt.isDefault = String(opt.value) === String(newValue)
      })
    }
  },
})

const labelPosition = computed(() =>
  field.value?.labelPosition === 'default' ? 'top' : field.value?.labelPosition || 'top',
)
</script>

<style lang="scss" scoped>
.ivyforms-field__select {
  .ivyforms-form-item {
    :deep(.ivyforms-form-item__label) {
      cursor: default !important;
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
