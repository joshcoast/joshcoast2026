<template class="ivyforms-pr-20">
  <div
    class="ivyforms-entry-page-section-title ivyforms-flex ivyforms-align-items-center ivyforms-justify-content-between"
  >
    <span class="medium-18">
      {{ getLabel('user_information') }}
    </span>

    <IvyCheckbox v-model="showEmptyFields" priority="secondary" type="checkmark">
      {{ getLabel('show_empty_fields') }}
    </IvyCheckbox>
  </div>

  <div class="ivyforms-entry-page-section-content ivyforms-mt-24 ivyforms-width-100">
    <table class="ivyforms-entry-table ivyforms-width-100">
      <tbody>
        <tr v-for="field in filteredFields" :key="field.id">
          <td class="ivyforms-key-column medium-14 ivyforms-px-8">
            {{ getFieldLabel(field) || '' }}
          </td>
          <!-- Rating fields -->
          <td
            v-if="getFormField(field)?.type === 'rating'"
            class="ivyforms-value-column ivyforms-width-100 ivyforms-px-8 regular-14"
          >
            <IvyRating
              :model-value="Number(field.fieldValue) || 0"
              :rating-icon="getFormField(field)?.ratingIcon || 'star'"
              :options="getFormField(field)?.fieldOptions || []"
              :show-label="false"
              readonly
            />
          </td>
          <!-- HTML fields with DOMPurify sanitization -->
          <td
            v-else-if="shouldRenderAsHtml(field)"
            class="ivyforms-value-column ivyforms-width-100 ivyforms-px-8 regular-14"
          >
            <SafeHTML :html="getFieldDisplayValue(field)" :field-type="getFormField(field)?.type" />
          </td>
          <!-- Regular text fields -->
          <td v-else class="ivyforms-value-column ivyforms-width-100 ivyforms-px-8 regular-14">
            {{ getFieldDisplayValue(field) }}
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useEntry } from '@/stores/useEntry.ts'
import { useLabels } from '@/composables/useLabels.ts'
import { useFormBuilder } from '@/stores/useFormBuilder.ts'
import api from '@/composables/IvyFormAPI.ts'
import SafeHTML from '@/views/_components/SafeHTML.vue'
import IvyRating from '@/views/_components/input/IvyRating.vue'

const formBuilderStore = useFormBuilder()
const showEmptyFields = ref(false)
const entryStore = useEntry()
const { getLabel } = useLabels()

const fieldsLoading = ref(false)

const filteredFields = computed(() => {
  // Only include fields whose corresponding form field does not have a parentId
  return entryStore.entryFields.filter((field) => {
    const fieldId =
      typeof field === 'object' && field !== null ? (field.fieldId ?? field.id) : field
    const formField = formBuilderStore.fields?.find((f) => f.id == fieldId)

    if (!formField || formField.parentId) return false
    // Filter out security/CAPTCHA fields (recaptcha, turnstile, hcaptcha)
    if (
      formField.type === 'recaptcha' ||
      formField.type === 'turnstile' ||
      formField.type === 'hcaptcha'
    )
      return false
    // If showEmptyFields is off, omit empty values (null or empty string)
    return !(!showEmptyFields.value && (field.fieldValue === null || field.fieldValue === ''))
  })
})

// Watch for entry to load and load form fields
watch(
  () => entryStore.entry,
  async (entry) => {
    if (
      entry &&
      entry.formId &&
      (!formBuilderStore.fields || formBuilderStore.fields.length === 0)
    ) {
      fieldsLoading.value = true
      await formBuilderStore.loadForm(entry.formId.toString())
      fieldsLoading.value = false
    }
  },
  { immediate: true },
)

const getFieldLabel = (field) => {
  const fieldId = typeof field === 'object' && field !== null ? (field.fieldId ?? field.id) : field
  const formField = formBuilderStore.fields?.find((f) => f.id == fieldId)
  return formField ? formField.label : ''
}

const getFormField = (field) => {
  const fieldId = typeof field === 'object' && field !== null ? (field.fieldId ?? field.id) : field
  return formBuilderStore.fields?.find((f) => f.id == fieldId)
}

const shouldRenderAsHtml = (field) => {
  const fieldId = typeof field === 'object' && field !== null ? (field.fieldId ?? field.id) : field
  const formField = formBuilderStore.fields?.find((f) => f.id == fieldId)

  if (!formField) return false

  // Allow extensions to register field types that should render as HTML
  // This is useful for Pro fields like signature that return sanitized HTML
  const htmlFieldTypes = api.hooks.applyFilters<string[]>(
    'ivyforms/entry/html_field_types',
    [],
    formField,
  )

  return htmlFieldTypes.includes(formField.type)
}

const getFieldDisplayValue = (field) => {
  const fieldId = typeof field === 'object' && field !== null ? (field.fieldId ?? field.id) : field
  const formField = formBuilderStore.fields?.find((f) => f.id == fieldId)

  if (
    formField &&
    (formField.type === 'checkbox' || formField.type === 'multi-select') &&
    Array.isArray(formField.fieldOptions)
  ) {
    const values = (field.fieldValue || '')
      .split(',')
      .map((v) => v.trim())
      .filter((v) => v)
    const labels = values.map((val) => {
      const opt = formField.fieldOptions.find((opt) => opt.value == val)
      return opt ? opt.label : val
    })
    return labels.join(', ')
  }
  if (
    formField &&
    (formField.type === 'radio' || formField.type === 'select') &&
    Array.isArray(formField.fieldOptions)
  ) {
    const opt = formField.fieldOptions.find((opt) => opt.value == field.fieldValue)
    return opt ? opt.label : field.fieldValue
  }
  return field.fieldValue
}
</script>

<style lang="scss">
.ivyforms-entry-page-section-title {
  color: var(--map-base-text-0);
}

.ivyforms-entry-page-section-content {
  .ivyforms-entry-table {
    td {
      text-align: left;
      border-bottom: 1px solid var(--map-divider);
      height: 48px;
      color: var(--map-base-text-0);
    }

    .ivyforms-key-column {
      box-sizing: border-box;
      min-width: 104px;
    }

    .ivyforms-value-column {
      white-space: pre-wrap;
      word-break: break-all;
      overflow-wrap: anywhere;
    }
  }
}
</style>
