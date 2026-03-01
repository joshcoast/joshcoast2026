<template>
  <div
    class="ivyforms-submit-button-settings ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-16 ivyforms-pt-16 ivyforms-pr-8"
  >
    <IvyFormItem :label="getLabel('button_label')" label-position="top">
      <IvyTextInput v-model="buttonLabel" :placeholder="getLabel('submit')" @input="updateLabel" />
    </IvyFormItem>

    <IvyFormItem :label="getLabel('button_position')">
      <IvyRadioGroup :model-value="buttonPosition" :priority="'secondary'" @change="updatePosition">
        <IvyRadio value="default" priority="secondary">{{
          getLabel('label_position_default')
        }}</IvyRadio>
        <IvyRadio value="left" priority="secondary">{{ getLabel('label_position_left') }}</IvyRadio>
        <IvyRadio value="center" priority="secondary">{{ getLabel('center') }}</IvyRadio>
        <IvyRadio value="right" priority="secondary">{{
          getLabel('label_position_right')
        }}</IvyRadio>
      </IvyRadioGroup>
    </IvyFormItem>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { useFormBuilder } from '@/stores/useFormBuilder'
import { useLabels } from '@/composables/useLabels'
import IvyFormItem from '@/views/_components/form/IvyFormItem.vue'
import IvyTextInput from '@/views/_components/input/IvyTextInput.vue'
import IvyRadioGroup from '@/views/_components/radio/IvyRadioGroup.vue'
import IvyRadio from '@/views/_components/radio/IvyRadio.vue'

const { getLabel } = useLabels()
const formBuilderStore = useFormBuilder()
const { submitButtonSettings } = storeToRefs(formBuilderStore)

const buttonLabel = ref(submitButtonSettings.value.label)
const buttonPosition = ref(submitButtonSettings.value.position)

// Watch for external changes to submit button settings
watch(
  submitButtonSettings,
  (newSettings) => {
    buttonLabel.value = newSettings.label
    buttonPosition.value = newSettings.position
  },
  { deep: true },
)

const updateLabel = (value: string) => {
  formBuilderStore.updateSubmitButtonSettings('label', value)
}

const updatePosition = (value: string) => {
  if (value === 'default' || value === 'left' || value === 'center' || value === 'right') {
    formBuilderStore.updateSubmitButtonSettings('position', value)
  }
}
</script>

<style scoped lang="scss">
.ivyforms-submit-button-settings {
  :deep(.ivyforms-form-item),
  :deep(.el-form-item) {
    margin-bottom: 0;
    gap: var(--Spacing-xs, 6px);
  }

  :deep(.el-radio-group.ivyforms-radio-group) {
    padding: 0;
  }
}
</style>
