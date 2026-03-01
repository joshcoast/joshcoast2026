<template>
  <div class="ivyforms-field__email">
    <IvyFormItem
      :label="hideLabel ? '' : label"
      class="ivyforms-field__email--first-form-item"
      :class="{ 'ivyforms-field__email--confirm-first-form-item': field?.confirmFieldEnabled }"
      :required="required"
      :label-position="labelPosition"
    >
      <IvyTextInput
        :id="index"
        :aria-label="label"
        readonly
        :placeholder="placeholder"
        :model-value="modelValue"
        type="email"
      />
      <div v-if="description" class="ivyforms-description-message regular-14">
        {{ description }}
      </div>
    </IvyFormItem>
    <!-- Confirmation preview -->
    <IvyFormItem
      v-if="field?.confirmFieldEnabled"
      class="ivyforms-field__email--second-form-item"
      :label="field?.confirmFieldHideLabel ? '' : confirmLabel"
      :required="required"
    >
      <IvyTextInput
        :id="index + '-confirm'"
        readonly
        :placeholder="confirmPlaceholder"
        :model-value="''"
        type="email"
      />
    </IvyFormItem>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useFormBuilder } from '@/stores/useFormBuilder'
import { useLabels } from '@/composables/useLabels'

const { getLabel } = useLabels()
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
const labelPosition = computed(() =>
  field.value?.labelPosition === 'default' ? 'top' : field.value?.labelPosition || 'top',
)
const description = computed(() => field.value?.description || '')
const confirmLabel = computed(
  () => field.value?.confirmFieldLabel || getLabel('confirmation_label_placeholder'),
)
const confirmPlaceholder = computed(() => field.value?.confirmFieldPlaceholder || '')
</script>
<style lang="scss" scoped>
.ivyforms-field__email {
  cursor: default;
  &--first-form-item {
    margin-bottom: 0;
  }
  &--confirm-first-form-item {
    margin-bottom: 16px !important;
  }
  &--second-form-item {
    margin-bottom: 0;
  }
  .ivyforms-form-item {
    cursor: default;
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
}
</style>
