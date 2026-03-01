<template>
  <div class="ivyforms-field__rating">
    <IvyFormItem
      :label="hideLabel ? '' : label"
      :required="required"
      :label-position="labelPosition"
    >
      <IvyRating
        :id="index"
        :aria-label="label"
        :model-value="defaultRatingValue"
        :rating-icon="ratingIcon"
        :options="fieldOptions"
        :show-label="showRatingText"
        readonly
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
import type { Field } from '@/types/field'

const props = defineProps<{ fieldIndex: number }>()
const formBuilderStore = useFormBuilder()

// Computed properties to dynamically get values from the store
const field = computed<Field | undefined>(() =>
  formBuilderStore.fields.find((f: Field) => f.fieldIndex === props.fieldIndex),
)

const label = computed(() => field.value?.label || '')
const required = computed(() => field.value?.required || false)
const hideLabel = computed(() => field.value?.hideLabel || false)
const index = computed(() => field.value?.fieldIndex ?? props.fieldIndex)
const fieldOptions = computed(() => field.value?.fieldOptions || [])
const defaultRatingValue = computed(() => {
  // Find the default option and return its value
  const options = fieldOptions.value
  const defaultOption = options.find((opt: { isDefault?: boolean }) => opt.isDefault)
  if (defaultOption) {
    return parseInt(defaultOption.value, 10)
  }
  return 0
})
const ratingIcon = computed(() => field.value?.ratingIcon || 'star')
const showRatingText = computed<boolean>(() => Boolean(field.value?.showRatingText ?? false))
const description = computed(() => field.value?.description || '')
const labelPosition = computed(() =>
  field.value?.labelPosition === 'default' ? 'top' : field.value?.labelPosition || 'top',
)
</script>

<style lang="scss" scoped>
.ivyforms-field__rating {
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

    // Adjust content width when label is on the right
    &:deep(.el-form-item__content) {
      flex: 0 1 auto;
      width: auto;
    }
  }

  .ivyforms-description-message {
    color: var(--map-base-text-0);
    display: block;
    width: 100%;
    white-space: normal;
    overflow-wrap: anywhere;
    word-break: break-word;
    margin-top: 8px;
  }
}
</style>
