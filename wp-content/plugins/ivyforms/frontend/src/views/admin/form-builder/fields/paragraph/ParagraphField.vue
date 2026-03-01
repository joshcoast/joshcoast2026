<template>
  <div class="ivyforms-field__paragraph">
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
        type="textarea"
        :rows="rows"
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
const rows = computed(() => (field.value?.rows <= 0 ? 3 : field.value?.rows))
const description = computed(() => field.value?.description || '')
const labelPosition = computed(() =>
  field.value?.labelPosition === 'default' ? 'top' : field.value?.labelPosition || 'top',
)
</script>
<style lang="scss" scoped>
.ivyforms-field__paragraph {
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
    .ivyforms-textarea.el-textarea {
      :deep(.el-textarea__inner:hover),
      :deep(.el-textarea__inner.is-focus),
      :deep(.el-textarea__inner) {
        border-radius: var(--Radius-radius-md, 8px);
        border: 1px solid var(--map-base-dusk-stroke--2);
        background: transparent;
        box-shadow: none;
        padding: 0 12px;
        transition: none;
        cursor: default;
      }
      :deep(textarea) {
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
}
</style>
