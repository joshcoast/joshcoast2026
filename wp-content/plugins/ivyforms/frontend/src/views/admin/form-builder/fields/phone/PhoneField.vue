<template>
  <div class="ivyforms-field__phone">
    <IvyFormItem
      :label="hideLabel ? '' : label"
      :required="required"
      :label-position="labelPosition"
    >
      <IvyPhoneInput
        :id="index"
        disabled
        :placeholder="placeholder"
        :model-value="modelValue"
        type="number"
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
import IvyPhoneInput from '@/views/_components/input/IvyPhoneInput.vue'

const props = defineProps<{ fieldIndex: number }>()
const formBuilderStore = useFormBuilder()

// Computed properties to dynamically get values from the store
const field = computed(() => formBuilderStore.fields.find((f) => f.fieldIndex === props.fieldIndex))

const label = computed(() => field.value?.label || '')
const placeholder = computed(() => field.value?.placeholder || '(___) __ - __ - __')
const required = computed(() => field.value?.required || false)
const hideLabel = computed(() => field.value?.hideLabel || false)
const index = computed(() => field.value?.fieldIndex ?? props.fieldIndex)
const modelValue = computed(() => field.value?.defaultValue || '')
const description = computed(() => field.value?.description || '')
const labelPosition = computed(() =>
  field.value?.labelPosition === 'default' ? 'top' : field.value?.labelPosition || 'top',
)
</script>
<style lang="scss" scoped>
.ivyforms-field__phone {
  cursor: default;
  .ivyforms-form-item {
    cursor: default;
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
    .ivyforms-phone-input {
      cursor: default;
      pointer-events: none; // Disable all pointer events on the component

      :deep(.vue-tel-input) {
        cursor: default !important;
        pointer-events: none !important;
        border: 1px solid var(--map-base-dusk-stroke--2);
        background: transparent;
        border-radius: var(--Radius-radius-md, 8px);

        &:hover,
        &:focus-within,
        &.is-focused {
          border-color: var(--map-base-dusk-stroke--2) !important;
        }
      }

      :deep(.vti__dropdown) {
        cursor: default !important;
        pointer-events: none !important;
      }

      :deep(.vti__flag) {
        cursor: default !important;
        pointer-events: none !important;
      }

      :deep(.vti__input) {
        cursor: default !important;
        pointer-events: none !important;
        border: none;
        background: transparent;
        box-shadow: none;

        &:focus {
          outline: none !important;
          border: none !important;
          background: transparent !important;
          box-shadow: none !important;
        }

        &:hover {
          cursor: default !important;
        }
      }

      :deep(.ivyforms-phone-input__arrow) {
        cursor: default !important;
        pointer-events: none !important;

        .ivyforms-icon__svg {
          width: auto;
        }
      }
    }
  }

  // Re-enable pointer events on the entire field container to allow drag and click
  pointer-events: auto;

  .ivyforms-description-message {
    color: var(--map-base-text-0);
    display: block;
    width: 100%;
    white-space: normal;
    overflow-wrap: anywhere;
    word-break: break-word;
    cursor: default;
    pointer-events: none;
  }
}
</style>
