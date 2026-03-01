<template>
  <div
    class="ivyforms-name-fields"
    :class="[
      { 'ivyforms-name-fields--readonly': !!field?.readOnly },
      `ivyforms-name-fields--label-${effectiveLabelPosition}`,
    ]"
  >
    <div
      v-if="
        !field?.hideLabel &&
        (effectiveLabelPosition === 'top' || effectiveLabelPosition === 'default')
      "
      class="ivyforms-name-fields__main-label ivyforms-mb-8 regular-16"
    >
      {{ field?.label || getLabel('name') }}
    </div>

    <div
      class="ivyforms-name-fields__container"
      :class="{
        'ivyforms-name-fields__container--flex-row':
          effectiveLabelPosition === 'left' || effectiveLabelPosition === 'right',
        'ivyforms-name-fields__container--flex-column':
          effectiveLabelPosition === 'top' || effectiveLabelPosition === 'default',
      }"
    >
      <div
        v-if="!field?.hideLabel && effectiveLabelPosition === 'left'"
        class="ivyforms-name-fields__main-label ivyforms-name-fields__main-label--left regular-16"
      >
        {{ field?.label || getLabel('name') }}
      </div>

      <div
        class="ivyforms-name-fields__layout ivyforms-flex ivyforms-flex-direction-row ivyforms-flex-wrap-wrap ivyforms-gap-16"
        :class="{
          'ivyforms-name-fields__layout--with-left-label': effectiveLabelPosition === 'left',
          'ivyforms-name-fields__layout--with-right-label': effectiveLabelPosition === 'right',
        }"
      >
        <div
          v-for="fieldData in nameFields"
          :key="fieldData.id"
          :class="[
            'ivyforms-name-fields__layout-field ivyforms-flex-1',
            fieldData.type.toLowerCase() + '-name',
          ]"
        >
          <IvyFormItem
            :label="fieldData.optionHide ? '' : fieldData.label"
            :required="fieldData.required"
          >
            <IvyTextInput
              :id="fieldData.id"
              readonly
              :aria-label="fieldData.label"
              :model-value="fieldData.modelValue"
              type="text"
              :placeholder="fieldData.placeholder"
            />
            <div
              v-if="fieldData.description"
              class="ivyforms-name-fields__layout-field-description regular-14 ivyforms-width-100"
            >
              {{ fieldData.description }}
            </div>
          </IvyFormItem>
        </div>
      </div>

      <div
        v-if="!field?.hideLabel && effectiveLabelPosition === 'right'"
        class="ivyforms-name-fields__main-label ivyforms-name-fields__main-label--right regular-16"
      >
        {{ field?.label || getLabel('name') }}
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useFormBuilder } from '@/stores/useFormBuilder'
import { useLabels } from '@/composables/useLabels'
import type { NameSubField } from '@/types/field'

const { getLabel } = useLabels()

const props = defineProps<{ fieldIndex: number }>()
const formBuilderStore = useFormBuilder()

const field = computed(() => formBuilderStore.fields.find((f) => f.fieldIndex === props.fieldIndex))

const nameFields = computed<NameSubField[]>(() => field.value?.nameFields || [])

// Computed property for effective label position
const effectiveLabelPosition = computed(() => {
  const position = field.value?.labelPosition
  // If position is 'default' or not set, use 'top' as the actual position
  if (position === 'default' || !position) {
    return 'top'
  }
  return position
})
</script>

<style lang="scss" scoped>
.ivyforms-name-fields {
  cursor: default;

  &--readonly {
    opacity: 0.6;
  }

  &__main-label {
    color: var(--map-base-text-0);

    // Left positioning
    &--left {
      flex-shrink: 0;
      margin-right: 8px;
      margin-bottom: 0;
      min-width: 100px;
      display: flex;
      align-items: center;
    }

    // Right positioning
    &--right {
      flex-shrink: 0;
      margin-left: 8px;
      margin-bottom: 0;
      min-width: 100px;
      display: flex;
      align-items: center;
    }
  }

  &__container {
    width: 100%;

    &--flex-row {
      display: flex;
      flex-direction: row;
      align-items: flex-start;
      gap: 0;
    }

    &--flex-column {
      display: flex;
      flex-direction: column;
    }
  }

  &__layout {
    flex: 1;

    &--with-left-label,
    &--with-right-label {
      margin-bottom: 0;
    }

    &-field {
      &-description {
        color: var(--map-base-text-0);
        display: block;
        white-space: normal;
        overflow-wrap: anywhere;
        word-break: break-word;
      }
    }
  }

  // Specific positioning styles
  &--label-left {
    .ivyforms-name-fields__container {
      align-items: flex-start;
    }

    .ivyforms-name-fields__layout {
      flex: 1;
    }
  }

  &--label-right {
    .ivyforms-name-fields__container {
      align-items: flex-start;
    }

    .ivyforms-name-fields__layout {
      flex: 1;
    }
  }

  &--label-top,
  &--label-default {
    .ivyforms-name-fields__layout {
      width: 100%;
    }
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
  }
}
</style>
