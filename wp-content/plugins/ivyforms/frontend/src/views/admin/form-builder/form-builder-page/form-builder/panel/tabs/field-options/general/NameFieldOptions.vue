<template>
  <div
    class="ivyforms-name-field-options ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-16"
  >
    <div class="ivyforms-basic-field-options__field-meta ivyforms-gap-4 ivyforms-pt-8">
      <div class="ivyforms-basic-field-options__field-meta-title medium-16">
        {{ getLabel('name_field') }}
        <span class="ivyforms-basic-field-options__field-meta-id regular-16"
          >(ID {{ formattedId }})</span
        >
      </div>
      <IvyDivider />
    </div>

    <!-- Main Field Label -->
    <IvyFormItem :label="getLabel('label')" secondary>
      <IvyTextInput v-model="selectedField.label" @input="updateField('label', $event)" />
    </IvyFormItem>

    <!-- Hide Label and Read Only Checkboxes -->
    <div class="ivyforms-name-field-options__checkbox-row ivyforms-flex ivyforms-gap-16">
      <IvyCheckbox
        v-model="hideLabel"
        priority="secondary"
        :type="'checkmark'"
        @change="updateField('hideLabel', $event)"
      >
        {{ getLabel('hide_label') }}
      </IvyCheckbox>

      <IvyCheckbox
        v-model="readOnly"
        priority="secondary"
        :type="'checkmark'"
        :disabled="hasAnyRequiredSubfield"
        @change="updateField('readOnly', $event)"
      >
        {{ getLabel('read_only') }}
      </IvyCheckbox>
    </div>

    <IvyDivider />

    <!-- Options List Label -->
    <div class="ivyforms-name-options-list-label medium-14">{{ getLabel('options_list') }}:</div>

    <!-- Name Field Options with Drag & Drop -->
    <div class="ivyforms-flex ivyforms-flex-direction-column">
      <draggable
        v-model="nameFieldsList"
        handle=".ivyforms-drag-handle"
        item-key="id"
        ghost-class="ivyforms-drag-ghost"
        chosen-class="ivyforms-drag-chosen"
        @start="onDragStart"
        @end="onNameFieldsReorder"
      >
        <template #item="{ element: subfield, index }">
          <FieldOptionsItem
            class="ivyforms-name-option-item"
            :title="subfield.label || `${getLabel('name_field')} ${index + 1}`"
            :collapsed="collapsedStates[index]"
            :disable-delete="nameFields.length <= 1"
            :drag-icon="{ name: 'drag', category: 'builder', type: 'outline' }"
            :trash-icon="{ name: 'trash', category: 'global', type: 'outline' }"
            :dropdown-icon="{
              collapsed: 'chevron-down',
              expanded: 'chevron-up',
              category: 'arrows',
              type: 'outline',
            }"
            @update:collapsed="(v) => (collapsedStates[index] = v)"
            @delete="nameFields.length > 1 && removeField(index)"
            @dragover="onDragOverItem"
            @dragleave="onDragLeaveItem"
            @drop="onDropItem"
          >
            <!-- Sublabel -->
            <IvyFormItem :label="getLabel('sublabel')" secondary>
              <IvyTextInput
                v-model="subfield.label"
                @input="updateSubfield(index, 'label', $event)"
              />
            </IvyFormItem>

            <!-- Description Message -->
            <IvyFormItem :label="getLabel('description_message')" secondary>
              <IvyTextInput
                v-model="subfield.description"
                :placeholder="getLabel('enter_description_here')"
                @input="updateSubfield(index, 'description', $event)"
              />
            </IvyFormItem>

            <!-- Hide Label -->
            <IvyCheckbox
              v-model="subfield.optionHide"
              priority="secondary"
              :type="'checkmark'"
              @change="updateSubfield(index, 'optionHide', $event)"
            >
              {{ getLabel('hide_label') }}
            </IvyCheckbox>

            <IvyDivider />

            <!-- Required (per sublabel) -->
            <IvyCheckbox
              v-model="subfield.required"
              priority="secondary"
              :type="'checkmark'"
              :disabled="isMainReadOnly"
              @change="onSubRequiredChange(index, $event)"
            >
              {{ getLabel('required') }}
            </IvyCheckbox>

            <!-- Required Message (per sublabel) -->
            <IvyFormItem :label="getLabel('required_message')" secondary>
              <IvyTextInput
                v-model="subfield.requiredMessage"
                :placeholder="getLabel('this_field_is_required')"
                :disabled="isMainReadOnly || !subfield.required"
                @input="updateSubfield(index, 'requiredMessage', $event)"
              />
            </IvyFormItem>

            <IvyDivider />

            <!-- Placeholder -->
            <IvyFormItem :label="getLabel('placeholder')" secondary>
              <IvyTextInput
                v-model="subfield.placeholder"
                :placeholder="getLabel('enter_text_here')"
                @input="updateSubfield(index, 'placeholder', $event)"
              />
            </IvyFormItem>

            <!-- Default Value -->
            <IvyFormItem :label="getLabel('default_value')" secondary>
              <IvyTextInput
                v-model="subfield.modelValue"
                @input="updateSubfield(index, 'modelValue', $event)"
              />
            </IvyFormItem>
          </FieldOptionsItem>
        </template>
      </draggable>
    </div>

    <!-- Add Option Button -->
    <IvyButtonAction
      priority="secondary"
      type="fill"
      size="d"
      icon-start-category="global"
      icon-start-name="plus"
      :disabled="isAddDisabled"
      @click="addOption"
    >
      {{ getLabel('add_option') }}
    </IvyButtonAction>

    <IvyDivider />

    <!-- CSS Classes -->
    <IvyFormItem :label="getLabel('css_classes')" secondary>
      <IvyTextInput v-model="cssClasses" @input="updateField('cssClasses', $event)" />
    </IvyFormItem>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { useFormBuilder } from '@/stores/useFormBuilder.ts'
import type { NameSubField } from '@/types/field'
import { useLabels } from '@/composables/useLabels.ts'
import IvyFormItem from '@/views/_components/form/IvyFormItem.vue'
import IvyTextInput from '@/views/_components/input/IvyTextInput.vue'
import IvyCheckbox from '@/views/_components/checkbox/IvyCheckbox.vue'
import IvyDivider from '@/views/_components/divider/IvyDivider.vue'
import IvyButtonAction from '@/views/_components/button/IvyButtonAction.vue'
import draggable from 'vuedraggable'
import FieldOptionsItem from '@/views/admin/form-builder/form-builder-page/form-builder/panel/tabs/_components/FieldOptionsItem.vue'

const { getLabel } = useLabels()
const { selectedField } = storeToRefs(useFormBuilder())
const { updateSelectedField, updateFieldOptions } = useFormBuilder()

// Padded ID like '01' for small numbers
const formattedId = computed(() => {
  const id = selectedField.value?.id ?? ''
  if (typeof id === 'number' && id) return String(id).padStart(2, '0')
  return getLabel('not_set')
})

const nameFields = computed<NameSubField[]>({
  get: () => selectedField.value?.nameFields || [],
  set: (val) => updateField('nameFields', val),
})

const nameFieldsList = ref<NameSubField[]>([])
let isDragging = false
watch(
  nameFields,
  (newValue) => {
    if (!isDragging) {
      nameFieldsList.value = [...newValue]
    }
  },
  { immediate: true, deep: true },
)

watch(
  nameFieldsList,
  (newList) => {
    if (!isDragging) return
    updateField('nameFields', newList)
  },
  { deep: true },
)

const collapsedStates = ref<boolean[]>([])
watch(
  nameFields,
  (newTypes, oldTypes) => {
    const newLength = newTypes.length
    if (!oldTypes || (oldTypes as NameSubField[]).length === 0) {
      collapsedStates.value = new Array(newLength).fill(true)
      if (newLength > 0) collapsedStates.value[0] = false
      return
    }
    const prevStates = collapsedStates.value
    collapsedStates.value = new Array(newLength).fill(true)
    for (let i = 0; i < newLength; i++) {
      collapsedStates.value[i] = prevStates[i] ?? false
    }
  },
  { immediate: true },
)

const hideLabel = computed({
  get: () => selectedField.value?.hideLabel || false,
  set: (value) => updateField('hideLabel', value),
})

const readOnly = computed({
  get: () => selectedField.value?.readOnly || false,
  set: (value) => {
    updateField('readOnly', value)
    if (value) {
      // Clear all required flags from subfields
      const arr = nameFields.value.map((sf) => ({ ...sf, required: false, requiredMessage: '' }))
      updateField('nameFields', arr)
    }
  },
})

const cssClasses = computed({
  get: () => selectedField.value?.cssClasses || '',
  set: (value) => updateField('cssClasses', value),
})

const isAddDisabled = computed(() => nameFields.value.length >= 5)
const hasAnyRequiredSubfield = computed(() => nameFields.value.some((sf) => !!sf.required))
const isMainReadOnly = computed(() => readOnly.value)

const updateField = (index: string, value: unknown) => {
  updateSelectedField(index, value)
  updateFieldOptions(index, value)
}

const escapeRegex = (s: string) => s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')

const getNextNameFieldIndex = () => {
  const baseLabel = getLabel('name_field')
  let maxIdx = nameFields.value.length
  const labelRe = new RegExp(`^${escapeRegex(baseLabel)}\\s+(\\d+)$`, 'i')

  for (const subfield of nameFields.value) {
    const typeMatch = /^nameField(\d+)$/i.exec(subfield.type || '')
    if (typeMatch) {
      const n = parseInt(typeMatch[1], 10)
      if (n > maxIdx) maxIdx = n
    }
    const labelMatch = labelRe.exec(subfield.label || '')
    if (labelMatch) {
      const n = parseInt(labelMatch[1], 10)
      if (n > maxIdx) maxIdx = n
    }
  }
  return maxIdx + 1
}

const updateSubfield = (idx: number, key: keyof NameSubField, value: unknown) => {
  const arr = [...nameFields.value]
  arr[idx] = { ...arr[idx], [key]: value }
  updateField('nameFields', arr)
}

const removeField = (idx: number) => {
  if (nameFields.value.length <= 1) return
  const arr = [...nameFields.value]
  arr.splice(idx, 1)
  updateField('nameFields', arr)
}

const addOption = () => {
  if (isAddDisabled.value) return
  const arr = [...nameFields.value]
  const nextIdx = getNextNameFieldIndex()
  arr.push({
    type: `nameField${nextIdx}`,
    id: '',
    modelValue: '',
    label: `${getLabel('name_field')} ${nextIdx}`,
    required: false,
    optionHide: false,
    description: '',
    placeholder: '',
  })
  updateField('nameFields', arr)
}

const onNameFieldsReorder = () => {
  isDragging = false
  clearDragIndicators()
}

// let originalOrder: NameSubField[] = []
const onDragStart = () => {
  isDragging = true
  clearDragIndicators()
  // originalOrder = [...nameFieldsList.value]
}

const onDragOverItem = (event: DragEvent) => {
  event.preventDefault()
  document.querySelectorAll('.ivyforms-name-option-item').forEach((el) => {
    el.classList.remove('dragging-over-top', 'dragging-over-bottom')
  })
  const item = event.currentTarget as HTMLElement
  const rect = item.getBoundingClientRect()
  const offsetY = event.clientY - rect.top
  if (offsetY < rect.height / 2) {
    item.classList.add('dragging-over-top')
  } else {
    item.classList.add('dragging-over-bottom')
  }
}

const onDragLeaveItem = (event: DragEvent) => {
  event.preventDefault()
  const isStillInside = [...document.querySelectorAll('.ivyforms-name-option-item')].some((el) =>
    el.matches(':hover'),
  )
  if (!isStillInside) {
    document.querySelectorAll('.ivyforms-name-option-item').forEach((el) => {
      el.classList.remove('dragging-over-top', 'dragging-over-bottom')
    })
  }
}

const onDropItem = (event: DragEvent) => {
  event.preventDefault()
  clearDragIndicators()
}
const clearDragIndicators = () => {
  document.querySelectorAll('.ivyforms-name-option-item').forEach((el) => {
    el.classList.remove('dragging-over-top', 'dragging-over-bottom')
  })
}

const onSubRequiredChange = (idx: number, val: boolean) => {
  updateSubfield(idx, 'required', !!val)
  if (val && readOnly.value) {
    updateField('readOnly', false)
  }
}
</script>

<style scoped lang="scss">
.ivyforms-name-field-options {
  :deep(.ivyforms-form-item),
  :deep(.el-form-item) {
    margin-bottom: 0;
    gap: var(--Spacing-xs, 6px);
  }

  :deep(.ivyforms-form-item-input) {
    margin-bottom: 0;
  }

  .ivyforms-options-list-label,
  .ivyforms-name-options-list-label {
    font-size: 14px;
    font-weight: 500;
    color: var(--map-base-text-0);
    line-height: 20px;
  }
}
</style>
