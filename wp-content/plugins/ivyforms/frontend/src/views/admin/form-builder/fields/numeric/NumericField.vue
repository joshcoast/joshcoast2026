<template>
  <div class="ivyforms-field__numeric">
    <IvyFormItem
      :label="hideLabel ? '' : label"
      :required="required"
      :label-position="labelPosition"
    >
      <IvyTextNumberInput
        :id="index"
        read-only
        :placeholder="placeholder"
        :model-value="numericModelValue"
        :number-format="numberFormat"
      />
      <div v-if="description" class="ivyforms-description-message regular-14">
        {{ description }}
      </div>
    </IvyFormItem>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useFormBuilder } from '@/stores/useFormBuilder'
import IvyTextNumberInput from '@/views/_components/input/IvyTextNumberInput.vue'

type NumberFormat = 'us_decimal' | 'us_int' | 'eu_decimal' | 'eu_int' | ''

const VALID_NUMBER_FORMATS: readonly string[] = [
  'us_decimal',
  'us_int',
  'eu_decimal',
  'eu_int',
  '',
] as const

function isNumberFormat(value: string | undefined | null): value is NumberFormat {
  if (typeof value !== 'string') return false
  return VALID_NUMBER_FORMATS.includes(value)
}

const props = defineProps<{ fieldIndex: number }>()
const formBuilderStore = useFormBuilder()

// Computed properties to dynamically get values from the store
const field = computed(() => formBuilderStore.fields.find((f) => f.fieldIndex === props.fieldIndex))

const label = computed(() => field.value?.label || '')
const placeholder = computed(() => field.value?.placeholder || '')
const required = computed(() => field.value?.required || false)
const hideLabel = computed(() => field.value?.hideLabel || false)
const index = computed(() => field.value?.fieldIndex ?? props.fieldIndex)
const numericModelValue = computed(() => {
  const defaultVal = field.value?.defaultValue
  if (defaultVal === '' || defaultVal === null || defaultVal === undefined) return null
  const parsed = Number(defaultVal)
  return isNaN(parsed) ? null : parsed
})
const description = computed(() => field.value?.description || '')
const labelPosition = computed(() =>
  field.value?.labelPosition === 'default' ? 'top' : field.value?.labelPosition || 'top',
)
const numberFormat = computed(() => {
  const format = field.value?.numberFormat || ''
  return isNumberFormat(format) ? format : ''
})
</script>

<style lang="scss" scoped>
.ivyforms-field__numeric {
  cursor: default;
  .ivyforms-form-item {
    margin-bottom: 0;
    :deep(.ivyforms-form-item__label) {
      display: flex;
      align-items: center;
      color: var(--map-base-text-0);
      font-size: 14px;
      font-style: normal;
      font-weight: 500;
      line-height: 20px;
      cursor: default !important;
    }
    :deep(.el-form-item__label) {
      cursor: default;
    }
    :deep(.ivyforms-text-number) {
      cursor: default;

      .ivyforms-text-number__btn {
        cursor: default;
        pointer-events: none;
      }

      .ivyforms-input.el-input {
        .el-input__wrapper:hover,
        .el-input__wrapper.is-focus,
        .el-input__wrapper {
          border-radius: var(--Radius-radius-md, 8px);
          border: 1px solid var(--map-base-dusk-stroke--2);
          background: transparent;
          box-shadow: none;
          padding: 0 44px;
          transition: none;
          cursor: default;
        }
        input {
          border: none;
          background: transparent;
          cursor: default;
          box-shadow: none;
          text-align: center;

          &:focus {
            outline: none;
            border: 1px solid transparent;
            background: none;
            box-shadow: none;
          }
        }
      }
    }
  }
  .ivyforms-description-message {
    color: var(--map-base-text-0);
    display: block;
    width: 100%;
    white-space: normal;
    overflow-wrap: anywhere;
    word-break: break-word;
  }
}
</style>
