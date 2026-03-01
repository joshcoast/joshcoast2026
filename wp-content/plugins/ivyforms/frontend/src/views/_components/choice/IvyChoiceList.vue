<template>
  <div class="ivyforms-choice-list">
    <div
      class="ivyforms-choice-list__header ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-16"
    >
      <div class="ivyforms-choice-list__title ivyforms-flex ivyforms-align-items-center medium-14">
        {{ getLabel('option_list') }}
        <IvyTooltip>
          <template #content>
            {{ getLabel('configure_options_field') }}
          </template>
          <IvyIcon name="question" type="outline" size="xs" color="var(--map-base-text-0)" />
        </IvyTooltip>
      </div>

      <!--      TODO Implement options with icon and image-->
      <!--      <div class="ivyforms-choice-list__tabs">-->
      <!--        <IvyTabs-->
      <!--          v-model="activeTab"-->
      <!--          priority="secondary"-->
      <!--          tab-style="stroke"-->
      <!--          type="tonal"-->
      <!--          :tabs="tabs"-->
      <!--        >-->
      <!--        </IvyTabs>-->
      <!--      </div>-->

      <div class="ivyforms-choice-list__toggles ivyforms-flex ivyforms-justify-content-between">
        <!--      <IvyCheckboxGroup>-->
        <IvyCheckbox v-model="localShowValues" priority="secondary" type="checkmark">
          {{ getLabel('show_values') }}
        </IvyCheckbox>
        <!--        TODO Add Calc Values-->
        <!--        <IvyCheckbox v-model="localCalcValueEnabled" priority="secondary" type="checkmark"-->
        <!--          >Calc Values</IvyCheckbox -->
        <!--        >-->
        <!--      </IvyCheckboxGroup>-->
      </div>
    </div>
    <IvyScrollbar>
      <div class="ivyforms-choice-list__items">
        <draggable
          v-model="internalChoices"
          item-key="id"
          handle=".ivyforms-choice-item__drag-handle"
          :animation="150"
          class="ivyforms-choice-list__draggable"
          @end="onDragEnd"
        >
          <template #item="{ element, index }">
            <div>
              <IvyChoiceItem
                :id="element.id"
                v-bind="element.attrs || {}"
                :label="element.label"
                :value="element.value"
                :show-value="localShowValues"
                :enable-search="localEnableSearch"
                :calc-value-enabled="localCalcValueEnabled"
                :type="type"
                :disabled-delete="internalChoices.length === 1"
                :is-default="element.isDefault"
                :radio-model-value="
                  type === 'radio' || type === 'select' || type === 'rating'
                    ? defaultChoiceId
                    : null
                "
                :radio-value="element.id"
                @set-default="(id) => setDefault(id)"
                @delete="() => removeOptionAt(index)"
                @add="() => addOptionAt(index)"
                @update:label="(val) => onUpdateLabel(element.id, val)"
                @update:value="(val) => onUpdateValue(element.id, val)"
                @blur-label="(val) => onUpdateLabel(element.id, val)"
                @blur-value="(val) => onUpdateValue(element.id, val)"
                @update:model-value="setDefault"
              />
            </div>
          </template>
        </draggable>
      </div>
    </IvyScrollbar>
    <div
      class="ivyforms-choice-list__footer ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-16 ivyforms-pt-16"
    >
      <div class="ivyforms-choice-list__actions ivyforms-flex ivyforms-justify-content-between">
        <IvyButtonAction
          v-if="type === 'select' || type === 'radio' || type === 'rating'"
          priority="secondary"
          @click="clearDefaultSelection"
        >
          {{ getLabel('clear_selection') }}
        </IvyButtonAction>

        <IvyButtonAction priority="tertiary" @click="openBulkDialog">
          {{ getLabel('bulk_edit') }}
        </IvyButtonAction>
      </div>
    </div>

    <IvyDialog
      v-model="isBulkDialogVisible"
      class="ivyforms-choice-list__bulk-dialog"
      :width="dialogWidth"
      show-close
      align-center
    >
      <template #header>
        <div class="ivyforms-choice-list__bulk-dialog-header medium-18">
          {{ getLabel('bulk_edit_dialog_title') }}
        </div>
      </template>

      <div class="ivyforms-choice-list__bulk-dialog-body ivyforms-flex ivyforms-gap-16">
        <div
          v-if="showPresetsPanel && type !== 'rating'"
          class="ivyforms-choice-list__bulk-dialog-presets ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-08"
        >
          <div class="ivyforms-choice-list__bulk-dialog-label regular-14">
            {{ getLabel('bulk_edit_presets_label') }}
          </div>
          <IvyMenuAccordion
            v-model="selectedPresetId"
            :menu-items="presetMenuItems"
            priority="secondary"
            @menu-select="handlePresetSelect"
          />
        </div>
        <div
          class="ivyforms-choice-list__bulk-dialog-options ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-08"
        >
          <div class="ivyforms-choice-list__bulk-dialog-label regular-14">
            {{ getLabel('bulk_edit_options_label') }}
          </div>
          <IvyTextInput
            v-model="bulkOptionsText"
            class="ivyforms-choice-list__bulk-dialog-textarea"
            type="textarea"
            :rows="14"
            :placeholder="getLabel('bulk_edit_options_placeholder')"
          />
        </div>
      </div>

      <template #footer>
        <div
          class="ivyforms-choice-list__bulk-dialog-footer ivyforms-flex ivyforms-justify-content-end ivyforms-gap-12"
        >
          <IvyButtonAction priority="tertiary" @click="closeBulkDialog">
            {{ getLabel('bulk_edit_cancel') }}
          </IvyButtonAction>
          <IvyButtonAction priority="success" @click="applyBulkOptions">
            {{ getLabel('bulk_edit_add_options') }}
          </IvyButtonAction>
        </div>
      </template>
    </IvyDialog>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import draggable from 'vuedraggable'
import { useLabels } from '@/composables/useLabels.ts'
import IvyDialog from '@/views/_components/dialog/IvyDialog.vue'
import IvyButtonAction from '@/views/_components/button/IvyButtonAction.vue'
import IvyMenuAccordion, { type MenuItem } from '@/views/_components/menu/IvyMenuAccordion.vue'
import { choicePresets, type ChoicePresetId } from '@/constants/choicePresets'
import IvyTextInput from '@/views/_components/input/IvyTextInput.vue'
const { getLabel } = useLabels()

type Choice = {
  id: number
  label: string
  value: string
  number?: number
  isDefault?: boolean
  position?: number
}

const props = defineProps({
  choices: {
    type: Array as () => Choice[],
    required: true,
    default: () => [],
  },
  showValues: {
    type: Boolean,
    default: true,
  },
  enableSearch: {
    type: Boolean,
    default: false,
  },
  shuffleOptions: {
    type: Boolean,
    default: false,
  },
  type: {
    type: String,
    default: 'single',
  },
  presetIds: {
    type: Array as () => ChoicePresetId[],
    default: () => [],
  },
})

const emit = defineEmits([
  'update:choices',
  'update:showValues',
  'update:shuffleOptions',
  'update:defaultValue',
  'update:enableSearch',
])

const localShowValues = ref(props.showValues)
const localCalcValueEnabled = ref(false)
const localShuffleOptions = ref(props.shuffleOptions)
const localEnableSearch = ref(props.enableSearch)

// Make type reactive (computed wrapper so template can still use `type` plainly)
const type = computed(() => props.type)

// Use a shallow copy for internal drag, but always emit to parent on drag end or add/remove
const internalChoices = ref<Choice[]>([...props.choices])
const nextId = ref(
  internalChoices.value.length > 0 ? Math.max(...internalChoices.value.map((c) => c.id)) + 1 : 1,
)
const defaultChoiceId = ref(
  internalChoices.value.find((c) => c.isDefault)?.id || internalChoices.value[0]?.id,
)

function updatePositions() {
  internalChoices.value.forEach((choice, idx) => {
    choice.position = idx + 1
  })
}

watch(
  () => props.choices,
  (newChoices) => {
    internalChoices.value = [...newChoices]
    nextId.value =
      internalChoices.value.length > 0 ? Math.max(...internalChoices.value.map((c) => c.id)) + 1 : 1
    defaultChoiceId.value =
      internalChoices.value.find((c) => c.isDefault)?.id || internalChoices.value[0]?.id
    updatePositions()
  },
  { deep: true, immediate: true },
)

watch(localShowValues, (val) => emit('update:showValues', val))
watch(localShuffleOptions, (val) => emit('update:shuffleOptions', val))
watch(localEnableSearch, (val) => emit('update:enableSearch', val))

// Keep localShuffleOptions in sync with prop
watch(
  () => props.shuffleOptions,
  (val) => {
    localShuffleOptions.value = val
  },
)

// React when field type changes
watch(
  () => props.type,
  (newType, oldType) => {
    if (newType === oldType) return
    if (newType === 'radio' || newType === 'select' || newType === 'rating') {
      // Ensure exactly one default remains
      const existingDefault = internalChoices.value.find((c) => c.isDefault)
      if (existingDefault) {
        defaultChoiceId.value = existingDefault.id
        // Clear others
        internalChoices.value.forEach((c) => (c.isDefault = c.id === existingDefault.id))
      } else if (internalChoices.value[0]) {
        // Pick first as default if none
        internalChoices.value.forEach((c, idx) => (c.isDefault = idx === 0))
        defaultChoiceId.value = internalChoices.value[0].id
      }
      const def = internalChoices.value.find((c) => c.isDefault)
      if (def) emit('update:defaultValue', def.value)
    } else if (newType === 'checkbox' || newType === 'multi-select') {
      // Multiple defaults allowed; emit current list
      const defaults = internalChoices.value.filter((c) => c.isDefault).map((c) => c.value)
      emit('update:defaultValue', defaults.join(','))
    }
    emitChoices()
  },
)

function setDefault(id: number) {
  if (type.value === 'radio' || type.value === 'select' || type.value === 'rating') {
    // Single default only
    defaultChoiceId.value = id
    internalChoices.value.forEach((choice) => {
      choice.isDefault = choice.id === id
    })
    const defaultOption = internalChoices.value.find((c) => c.id === id)
    if (defaultOption) {
      emit('update:defaultValue', defaultOption.value)
    }
  } else if (type.value === 'checkbox' || type.value === 'multi-select') {
    // Toggle default state allowing multiple defaults (or none)
    const target = internalChoices.value.find((c) => c.id === id)
    if (target) {
      target.isDefault = !target.isDefault
      const defaults = internalChoices.value.filter((c) => c.isDefault).map((c) => c.value)
      emit('update:defaultValue', defaults.join(','))
    }
  }
  emitChoices()
}

function getNextOptionNumber() {
  // Find the highest number used in current choices for this field/component
  const numbers = internalChoices.value.map((c) => {
    const match = c.value.match(/(\d+)$/)
    return match ? parseInt(match[1], 10) : 0
  })
  return numbers.length > 0 ? Math.max(...numbers) + 1 : 1
}

function addOptionAt(index: number) {
  const nextNumber = getNextOptionNumber()
  let label = `Option ${nextNumber}`
  if (type.value === 'checkbox') {
    label = `Choice ${nextNumber}`
  }
  const newOption = {
    id: nextId.value++,
    label,
    value: `${type.value === 'checkbox' ? 'choice' : 'option'}${nextNumber}`,
    number: nextNumber,
    isDefault: false,
    position: nextNumber,
  }
  internalChoices.value.splice(index + 1, 0, newOption)
  updatePositions()
  emitChoices()
}

function removeOptionAt(index: number) {
  if (internalChoices.value.length === 1) return
  onDelete(index)
}

function clearDefaultSelection() {
  internalChoices.value.forEach((c) => (c.isDefault = false))
  defaultChoiceId.value = null
  emit('update:defaultValue', '')
  emitChoices()
}

const presets = computed(() => {
  if (type.value === 'rating') {
    // Remove rating from sidebar completely
    return choicePresets.filter((preset) => preset.id !== 'rating')
  }
  if (props.presetIds && props.presetIds.length > 0) {
    return choicePresets.filter((preset) => props.presetIds!.includes(preset.id))
  }
  return choicePresets
})

const showPresetsPanel = computed(() => presets.value.length > 1)

const dialogWidth = computed(() =>
  type.value === 'rating' ? '480px' : showPresetsPanel.value ? '720px' : '480px',
)

const isBulkDialogVisible = ref(false)
const bulkOptionsText = ref('')
const selectedPresetId = ref<ChoicePresetId | null>(null)

// Convert presets to menu items format (simple list, no sub-items)
const presetMenuItems = computed<MenuItem[]>(() => {
  // Exclude 'rating' from the visible menu items so it never shows in the sidebar
  return presets.value
    .filter((preset) => preset.id !== 'rating')
    .map((preset) => ({
      index: preset.id,
      label: getLabel(preset.labelKey),
    }))
})

function openBulkDialog() {
  selectedPresetId.value = null
  bulkOptionsText.value = internalChoices.value.length
    ? internalChoices.value.map((choice) => choice.label ?? '').join('\n')
    : ''
  isBulkDialogVisible.value = true
}

function closeBulkDialog() {
  isBulkDialogVisible.value = false
}

function handlePresetSelect(presetId: string) {
  const preset = presets.value.find((p) => p.id === presetId)
  if (!preset) return

  selectedPresetId.value = presetId as ChoicePresetId
  bulkOptionsText.value = preset.options.map((option) => option.label).join('\n')
}

function applyBulkOptions() {
  const lines = bulkOptionsText.value
    .split(/\r?\n/)
    .map((line) => line.trim())
    .filter((line) => line.length > 0)

  if (!lines.length) {
    closeBulkDialog()
    return
  }

  const newChoices: Choice[] = lines.map((line, index) => {
    const normalizedValue = line
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/(^-|-$)/g, '')

    return {
      id: index + 1,
      label: line,
      value: normalizedValue || `option${index + 1}`,
      number: index + 1,
      isDefault: false,
      position: index + 1,
    }
  })

  internalChoices.value = newChoices
  nextId.value = newChoices.length ? newChoices.length + 1 : 1
  defaultChoiceId.value = null
  emit('update:defaultValue', '')
  updatePositions()
  emitChoices()
  closeBulkDialog()
}

function onDelete(index: number) {
  if (internalChoices.value.length === 1) {
    return
  }
  const wasDefault = internalChoices.value[index].isDefault
  internalChoices.value.splice(index, 1)
  updatePositions()
  emitChoices()
  if (type.value === 'radio' || type.value === 'select' || type.value === 'rating') {
    if (wasDefault || !internalChoices.value.some((choice) => choice.isDefault)) {
      if (internalChoices.value[0]) setDefault(internalChoices.value[0].id)
    } else {
      const defaultOption = internalChoices.value.find((choice) => choice.isDefault)
      if (defaultOption) emit('update:defaultValue', defaultOption.value)
    }
  } else if (type.value === 'checkbox' || type.value === 'multi-select') {
    const defaults = internalChoices.value
      .filter((choice) => choice.isDefault)
      .map((choice) => choice.value)
    emit('update:defaultValue', defaults.join(','))
  }
}

function onUpdateLabel(id: number, val: string) {
  const target = internalChoices.value.find((choice) => choice.id === id)
  if (!target) return
  target.label = val
  internalChoices.value = [...internalChoices.value]
  emitChoices()
}

function onUpdateValue(id: number, val: string) {
  const target = internalChoices.value.find((choice) => choice.id === id)
  if (!target) return
  target.value = val
  internalChoices.value = [...internalChoices.value]
  emitChoices()
}

function onDragEnd() {
  updatePositions()
  emitChoices()
}

function emitChoices() {
  emit(
    'update:choices',
    internalChoices.value.map((choice, index) => ({ ...choice, position: index + 1 })),
  )
}
</script>

<style lang="scss" scoped>
.ivyforms-choice-list {
  &__title {
    color: var(--map-base-text-0);
  }

  &__items {
    max-height: 324px;
  }

  &__bulk-dialog {
    &-header {
      color: var(--map-base-text-0);
    }

    &-presets {
      width: 232px;

      .ivyforms-menu-accordion {
        border-right: 1px solid var(--map-base-dusk-stroke--2);
      }
    }

    &-options {
      flex: 1;
    }

    &-label {
      color: var(--map-base-text--1);
    }

    &-textarea {
      :deep(.el-textarea__inner) {
        min-height: 280px;
        color: var(--map-base-text--2);
        border: 1px solid var(--map-base-dusk-stroke--2) !important;
        background: var(--map-base-dusk-o05) !important;
        box-shadow: none !important;

        &:hover {
          border: 1px solid var(--map-base-dusk-stroke-0) !important;
        }

        &:focus,
        &:active {
          border: 1px solid var(--map-base-dusk-stroke--2) !important;
          background: var(--map-base-dusk-o05) !important;
          box-shadow: none !important;
        }
      }
    }
  }
}
</style>
