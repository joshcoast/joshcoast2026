<template>
  <div
    class="ivyforms-choice-item ivyforms-flex ivyforms-flex-direction-row ivyforms-align-items-center ivyforms-gap-8 ivyforms-mb-8 ivyforms-radius-8 ivyforms-py-8 ivyforms-px-12"
    v-bind="$attrs"
  >
    <!-- Drag handle icon - directly without wrapper -->
    <IvyIcon
      name="drag"
      category="builder"
      type="outline"
      class="ivyforms-choice-item__drag-handle"
    />

    <!-- Checkbox for multi-select - directly without wrapper -->
    <IvyCheckbox
      v-if="type === 'checkbox' || type === 'multi-select'"
      priority="secondary"
      type="checkmark"
      class="ivyforms-choice-item__type-icon"
      :model-value="isDefault"
      @update:model-value="$emit('set-default', id)"
    >
      <div v-if="isDefault" class="ivyforms-choice-item__default-label">
        {{ getLabel('') }}
      </div>
    </IvyCheckbox>

    <!-- Radio for single-select - directly without wrapper -->
    <IvyRadio
      v-else
      priority="secondary"
      class="ivyforms-choice-item__type-icon"
      :model-value="isDefault"
      :value="true"
      @update:model-value="$emit('set-default', id)"
    />

    <!-- Display mode: image - directly without wrapper -->
    <img
      v-if="displayMode === 'withImage'"
      :src="imageUrl"
      alt="option image"
      class="ivyforms-choice-item__image"
    />

    <!-- Display mode: icon - directly without wrapper -->
    <IvyIcon v-else-if="displayMode === 'withIcons' && iconName" :name="iconName" />

    <!-- Label input -->
    <IvyFormItem :error="error" :style="showValue ? 'flex: 1 1 50%' : 'flex: 1 1 100%'" secondary>
      <IvyTextInput
        v-model="localLabel"
        :placeholder="getLabel('option_label')"
        @input="$emit('update:label', localLabel)"
        @blur="$emit('blur-label', localLabel)"
      />
    </IvyFormItem>

    <!-- Value input -->
    <IvyFormItem v-if="showValue" :style="'flex: 1 1 50%'" secondary>
      <IvyTextInput
        v-model="localValue"
        :placeholder="getLabel('option_value')"
        @input="$emit('update:value', localValue)"
        @blur="$emit('blur-value', localValue)"
      />
    </IvyFormItem>

    <!-- Number input -->
    <IvyNumberInput
      v-if="calcValueEnabled"
      v-model="localNumber"
      @input="$emit('update:number', localNumber)"
    />

    <!-- Add button - directly without wrapper -->
    <IvyButtonAction
      icon-start="plus-circle"
      priority="secondary"
      type="ghost"
      class="ivyforms-choice-item__add-btn"
      @click="$emit('add')"
    />

    <!-- Remove button - directly without wrapper -->
    <IvyButtonAction
      icon-start="minus"
      priority="secondary"
      type="ghost"
      class="ivyforms-choice-item__remove-btn"
      :class="{ 'ivyforms-choice-item__remove-btn--is-disabled': disabledDelete }"
      @click="onDeleteClick"
    />
  </div>
  <IvyDivider />
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { useLabels } from '@/composables/useLabels.ts'
import IvyFormItem from '@/views/_components/form/IvyFormItem.vue'

const { getLabel } = useLabels()

const props = defineProps({
  id: { type: [String, Number], required: true },
  label: { type: String, required: true },
  value: { type: String, default: '' },
  number: { type: Number, default: 1 },
  showValue: { type: Boolean, default: false },
  calcValueEnabled: { type: Boolean, default: false },
  type: { type: String, default: 'single' },
  disabledDelete: { type: Boolean, default: false },
  displayMode: { type: String, default: 'none' },
  imageUrl: { type: String, default: '' },
  iconName: { type: String, default: '' },
  isDefault: { type: Boolean, default: false },
  radioModelValue: { type: [String, Number], default: null },
  radioValue: { type: [String, Number], default: null },
  error: { type: String, default: '' },
})

const emit = defineEmits([
  'delete',
  'move',
  'update:label',
  'update:value',
  'update:number',
  'set-default',
  'try-delete',
  'add',
  'blur-label',
  'blur-value',
])

const localLabel = ref(props.label)
const localValue = ref(props.value)
const localNumber = ref(props.number)

watch(
  () => props.label,
  (val) => (localLabel.value = val),
)
watch(
  () => props.value,
  (val) => (localValue.value = val),
)
watch(
  () => props.number,
  (val) => (localNumber.value = val),
)

function onDeleteClick() {
  if (props.disabledDelete) {
    emit('try-delete')
  } else {
    emit('delete')
  }
}
</script>

<style scoped lang="scss">
.ivyforms-choice-item {
  &__drag-handle {
    cursor: grab;
  }

  &__image {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    object-fit: cover;
  }

  &__delete-btn {
    &--is-disabled {
      opacity: 0.5;
      pointer-events: none;
    }
  }
}
</style>
