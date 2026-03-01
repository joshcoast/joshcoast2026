<template>
  <div class="ivyforms-field__website">
    <IvyFormItem
      :label="hideLabel ? '' : label"
      :required="required"
      :label-position="labelPosition"
    >
      <IvyTextInput
        :id="index"
        readonly
        :placeholder="placeholder"
        :model-value="modelValue"
        type="website"
      >
        <template v-if="inputPrefix" #prefix>
          <span class="ivyforms-field__website-prefix">{{ inputPrefix }}</span>
        </template>
        <template #suffix>
          <span class="ivyforms-field__website-suffix">{{ inputSuffix }}</span>
        </template>
      </IvyTextInput>
      <div v-if="description" class="ivyforms-description-message regular-14">
        {{ description }}
      </div>
    </IvyFormItem>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useFormBuilder } from '@/stores/useFormBuilder'

const props = defineProps<{ fieldIndex: number }>()
const formBuilderStore = useFormBuilder()

// Computed properties to dynamically get values from the store
const field = computed(() => formBuilderStore.fields.find((f) => f.fieldIndex === props.fieldIndex))

const label = computed(() => field.value?.label || '')
const placeholder = computed(() => field.value?.placeholder || '')
const required = computed(() => field.value?.required || false)
const hideLabel = computed(() => field.value?.hideLabel || false)
const index = computed(() => field.value?.fieldIndex ?? props.fieldIndex)
const modelValue = computed(() => field.value?.defaultValue || '')
const description = computed(() => field.value?.description || '')
const inputPrefix = computed(() => field.value?.inputPrefix || '')
const inputSuffix = computed(() => field.value?.inputSuffix || '')
const labelPosition = computed(() =>
  field.value?.labelPosition === 'default' ? 'top' : field.value?.labelPosition || 'top',
)
</script>
<style lang="scss" scoped>
.ivyforms-field__website {
  cursor: default;
  .ivyforms-form-item {
    cursor: default;
    margin-bottom: 0;
    :deep(.ivyforms-form-item__label) {
      display: flex;
      align-items: center;
      color: var(--map-base-text-0);
      /* Medium/Medium 14 */
      font-size: 14px;
      font-style: normal;
      font-weight: 500;
      line-height: 20px; /* 142.857% */
      cursor: default !important;
    }
    :deep(.el-form-item__label) {
      cursor: default;
    }
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
          border: 1px solid transparent; /* Ensure border does not change */
          background: none; /* Ensure background does not change */
          box-shadow: none;
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

  // Styling for website prefix and suffix
  :deep(.ivyforms-field__website-prefix),
  :deep(.ivyforms-field__website-suffix) {
    display: inline-block;
    padding: 0 8px;
    font-size: 14px;
    line-height: 40px;
    color: var(--map-base-text--2);
    cursor: default;
  }

  :deep(.ivyforms-field__website-prefix) {
    border-right: 1px solid var(--map-base-dusk-stroke--2, #ccc);
    border-radius: 8px 0 0 8px;
  }

  :deep(.ivyforms-field__website-suffix) {
    border-left: 1px solid var(--map-base-dusk-stroke--2, #ccc);
    border-radius: 0 8px 8px 0;
  }
}
</style>
