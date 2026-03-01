<template>
  <div
    :class="[
      'ivyforms-field',
      'ivyforms-field__name',
      'ivyforms-field__name_' + field.id,
      field.cssClasses,
      {
        'main-label-visible': !field.hideLabel,
        'ivyforms-name-field--is-readonly': !!field.readOnly,
      },
      `ivyforms-name-field--label-${effectiveLabelPosition}`,
    ]"
  >
    <div
      v-if="
        !field.hideLabel &&
        (effectiveLabelPosition === 'top' || effectiveLabelPosition === 'default')
      "
      class="ivyforms-name-field__label ivyforms-gap-4 ivyforms-align-items-center ivyforms-mb-8 regular-16"
    >
      {{ field.label }}
    </div>

    <div
      class="ivyforms-name-field__container"
      :class="{
        'ivyforms-name-field__container--flex-row':
          effectiveLabelPosition === 'left' || effectiveLabelPosition === 'right',
        'ivyforms-name-field__container--flex-column':
          effectiveLabelPosition === 'top' || effectiveLabelPosition === 'default',
      }"
    >
      <div
        v-if="!field.hideLabel && effectiveLabelPosition === 'left'"
        class="ivyforms-name-field__label ivyforms-name-field__label--left ivyforms-gap-4 ivyforms-align-items-center regular-16"
      >
        {{ field.label }}
      </div>

      <!-- Fields layout -->
      <div
        class="ivyforms-name-field__layout ivyforms-width-100 ivyforms-gap-16"
        :class="{
          'ivyforms-name-field__layout--with-left-label': effectiveLabelPosition === 'left',
          'ivyforms-name-field__layout--with-right-label': effectiveLabelPosition === 'right',
        }"
        :style="gridStyle"
      >
        <div
          v-for="(fieldData, idx) in nameFields"
          :key="fieldData.fieldID"
          class="ivyforms-name-field__layout__field-wrapper"
        >
          <IvyFormItem
            :required="isSubRequired(idx)"
            :prop="`${field.type}_${field.fieldIndex}.${getTypeByIndex(idx)}`"
            :error="getSubfieldError(getTypeByIndex(idx))"
            :show-info="!!getSubDescription(idx)"
            :info-text="getSubDescription(idx) || ''"
            :show-info-icon="false"
          >
            <template #label>
              <div
                class="ivyforms-name-field__layout__field-wrapper__sublabel ivyforms-align-items-center ivyforms-gap-4 medium-14"
              >
                <span v-if="!fieldData.hide">{{ fieldData.label }}</span>
              </div>
            </template>
            <IvyTextInput
              :id="fieldData.fieldID"
              :model-value="getFieldValue(getTypeByIndex(idx))"
              class="ivyforms-name-field__name-input"
              type="text"
              :placeholder="fieldData.placeholder"
              :disabled="isDisabled"
              :required="isSubRequired(idx)"
              :aria-required="isSubRequired(idx) ? 'true' : 'false'"
              @input="updateFieldValue(getTypeByIndex(idx), $event)"
            />
          </IvyFormItem>
        </div>
      </div>

      <div
        v-if="!field.hideLabel && effectiveLabelPosition === 'right'"
        class="ivyforms-name-field__label ivyforms-name-field__label--right ivyforms-gap-4 ivyforms-align-items-center regular-16"
      >
        {{ field.label }}
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, inject, onMounted, onUnmounted, watch } from 'vue'
import { useAutofillDetection, type AutofillField } from '@/composables/useAutofillDetection'
import type { Field, NameFieldValue } from '@/types/field'

interface FieldProps {
  modelValue: NameFieldValue
  field: Field
  disabled?: boolean
  error?: string
  fieldErrors?: Record<string, string>
}

const props = withDefaults(defineProps<FieldProps>(), {
  modelValue: () => ({}),
  disabled: false,
  error: '',
  fieldErrors: () => ({}),
})
const emit = defineEmits(['update:modelValue'])

// Inject error clearer from parent form
const clearFieldError = inject<(fieldKey: string) => void>('clearFieldError', () => {})

// Disable input if component disabled OR field.readOnly is set
const isDisabled = computed(() => !!props.disabled || !!props.field.readOnly)

// Computed property for effective label position
const effectiveLabelPosition = computed(() => {
  const position = props.field?.labelPosition
  // If position is 'default' or not set, use 'top' as the actual position
  if (position === 'default' || !position) {
    return 'top'
  }
  return position
})

// Get specific error for a subfield
const getSubfieldError = (fieldType: string): string => {
  const subKey = `${props.field.type}_${props.field.fieldIndex}.${fieldType}`
  return props.fieldErrors?.[subKey] || ''
}

const nameFieldTypes = computed(() => props.field.nameFieldTypes || ['nameField1', 'nameField2'])

const nameFields = computed(() => {
  return nameFieldTypes.value.map((fieldType) => {
    // Raw label without i18n fallback, so cleared sublabels are treated as hidden
    const rawLabel = ((props.field as Record<string, string>)[`${fieldType}Label`] ?? '') as string
    const isCleared = rawLabel.trim() === ''
    const explicitHide = (props.field as Record<string, boolean>)[`${fieldType}HideLabel`] || false
    const hide = explicitHide || isCleared
    return {
      label: rawLabel, // no fallback; empty means hidden via `hide`
      placeholder: (props.field as Record<string, string>)[`${fieldType}Placeholder`] || '',
      fieldID: `ivyforms-field__name-${fieldType}_${(props.field.formId || '').toString()}_${(props.field.fieldIndex || '').toString()}`,
      hide,
    }
  })
})

const getTypeByIndex = (idx: number): string => nameFieldTypes.value[idx] || ''
const isSubRequired = (idx: number): boolean => {
  const t = getTypeByIndex(idx)
  return !!(props.field as Record<string, unknown>)[`${t}Required`]
}

// Subfield description helper (per sub)
const getSubDescription = (idx: number): string => {
  const t = getTypeByIndex(idx)
  const val = (props.field as Record<string, unknown>)[`${t}Description`]
  return typeof val === 'string' ? val : ''
}

// Single row grid with equal columns for 1–5 subfields
const gridStyle = computed(() => {
  const count = Math.max(1, Math.min(5, nameFields.value.length || 0))
  return { gridTemplateColumns: `repeat(${count}, minmax(0, 1fr))` }
})

const getFieldValue = (fieldType: string) => props.modelValue?.[fieldType] || ''

const updateFieldValue = (fieldType: string, value: string) => {
  const newModelValue: NameFieldValue = { ...props.modelValue, [fieldType]: value }
  emit('update:modelValue', newModelValue)

  // Clear specific subfield error immediately on input (e.g., after submit validation)
  const subKey = `${props.field.type}_${props.field.fieldIndex}.${fieldType}`
  clearFieldError(subKey)
}

// Setup autofill detection using the composable
const autofillFields = computed<AutofillField[]>(() => {
  return nameFields.value.map((fieldData, idx) => ({
    id: fieldData.fieldID,
    getValue: () => getFieldValue(getTypeByIndex(idx)),
    setValue: (value: string) => updateFieldValue(getTypeByIndex(idx), value),
    onClearError: () => {
      const subKey = `${props.field.type}_${props.field.fieldIndex}.${getTypeByIndex(idx)}`
      clearFieldError(subKey)
    },
  }))
})

const { restartDetection, checkForInitialAutofill } = useAutofillDetection({
  fields: autofillFields.value,
  pollingDuration: 7000, // Increased for slow autofill
  pollingInterval: 100,
  initialCheckDelay: 100, // Faster initial check
})

// Manual check on mount and when fields become available
onMounted(() => {
  // Initial check immediately after mount
  setTimeout(checkForInitialAutofill, 50)
  // Additional checks for delayed autofill
  setTimeout(checkForInitialAutofill, 300)
  setTimeout(checkForInitialAutofill, 1000)
})

// Watch for field changes and restart detection
watch(
  () => nameFields.value.map((f) => f.fieldID),
  () => {
    setTimeout(restartDetection, 100)
  },
  { flush: 'post' },
)

// Watch for modelValue changes (especially form resets)
watch(
  () => props.modelValue,
  (newValue, oldValue) => {
    // If form was reset (empty object) or significant change, restart autofill detection
    const isReset = Object.keys(newValue || {}).length === 0
    const wasPopulated = oldValue && Object.keys(oldValue).length > 0

    if (isReset && wasPopulated) {
      setTimeout(restartDetection, 200)
    }
  },
  { deep: true },
)

onUnmounted(() => {})
</script>

<style lang="scss" scoped>
:deep(.ivyforms-form-item .el-form-item .el-form-item__label) {
  justify-content: flex-start !important;
}

.ivyforms-name-field {
  &--is-readonly {
    opacity: 0.7;
    // Prevent hover/focus border changes when read-only
    :deep(.el-input__wrapper),
    :deep(.el-input__wrapper:hover),
    :deep(.el-input__wrapper.is-focus) {
      border-color: var(--map-base-dusk-stroke--2) !important;
      box-shadow: none !important;
    }
    :deep(input[disabled]) {
      cursor: not-allowed;
    }
  }

  &__label {
    color: var(--map-base-text-0);
    display: inline-flex;

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
    display: flex;
    width: 100%;

    &--flex-row {
      flex-direction: row;
      align-items: flex-start;
      gap: 0;
    }

    &--flex-column {
      flex-direction: column;
    }
  }

  &__layout {
    display: grid;
    flex: 1;

    &--with-left-label,
    &--with-right-label {
      margin-bottom: 0;
    }

    &__field-wrapper {
      min-width: 0;

      &__sublabel {
        display: inline-flex;
        color: var(--map-base-text-0);
        height: 20px;
        span {
          white-space: nowrap;
        }

        &__asterisk {
          color: var(--map-status-error-fill-0);
        }
      }
    }
  }

  // Specific positioning styles
  &--label-left {
    .ivyforms-name-field__container {
      align-items: flex-start;
    }

    .ivyforms-name-field__layout {
      flex: 1;
    }
  }

  &--label-right {
    .ivyforms-name-field__container {
      align-items: flex-start;
    }

    .ivyforms-name-field__layout {
      flex: 1;
    }
  }

  &--label-top,
  &--label-default {
    .ivyforms-name-field__layout {
      width: 100%;
    }
  }
}
</style>
