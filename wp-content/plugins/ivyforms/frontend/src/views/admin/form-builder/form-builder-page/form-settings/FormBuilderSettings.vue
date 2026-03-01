<template>
  <div
    class="ivyforms-form-builder-settings ivyforms-flex ivyforms-flex-1 ivyforms-flex-direction-column"
  >
    <div class="ivyforms-form-builder-settings__option-bar ivyforms-pb-8 ivyforms-flex">
      <div
        class="ivyforms-form-builder-settings__option-bar__left ivyforms-flex ivyforms-align-items-center ivyforms-justify-content-start ivyforms-gap-8"
      >
        <IvyToggle
          v-model="formBuilderStore.published"
          priority="primary"
          size="s"
          :title="getLabel('published')"
          text-position="left"
        />
      </div>
      <div
        class="ivyforms-form-builder-settings__option-bar__right ivyforms-align-items-center ivyforms-justify-content-end ivyforms-flex ivyforms-gap-8"
      >
        <IvyButtonAction
          v-if="formBuilderStore.isEditingSettingGeneral"
          :class="['ivyforms-button__action--update']"
          priority="primary"
          :loading="loading"
          @click="updateForm"
        >
          <template v-if="!loading">{{ getLabel('save') }}</template>
        </IvyButtonAction>
        <IvyButtonAction
          v-else
          :class="['ivyforms-button__action--save']"
          priority="primary"
          :loading="loading"
          @click="saveForm"
        >
          <template v-if="!loading">{{ getLabel('save') }}</template>
        </IvyButtonAction>
      </div>
    </div>
    <IvyScrollbar modifier="outside-vertical">
      <IvyForm :model="formData">
        <IvyFormItem :label="getLabel('form_name')" prop="formName" secondary>
          <IvyTextInput
            v-model="formBuilderStore.name"
            :placeholder="getLabel('form_name_placeholder')"
          ></IvyTextInput>
        </IvyFormItem>
        <IvyFormItem
          :label="getLabel('form_desc')"
          prop="formDescription"
          class="ivyforms-form-settings-description"
          secondary
        >
          <IvyTextInput
            v-model="formBuilderStore.description"
            :type="'textarea'"
            :placeholder="getLabel('form_description')"
          ></IvyTextInput>
        </IvyFormItem>
        <div
          class="ivyforms-flex ivyforms-flex-grow ivyforms-gap-16 ivyforms-flex-direction-column"
        >
          <IvyCheckbox
            v-model="formBuilderStore.showTitle"
            priority="secondary"
            size="d"
            type="checkmark"
          >
            {{ getLabel('show_form_title') }}
          </IvyCheckbox>
          <IvyCheckbox
            v-model="formBuilderStore.showDescription"
            priority="secondary"
            size="d"
            type="checkmark"
          >
            {{ getLabel('show_form_description') }}
          </IvyCheckbox>
          <IvyDivider class="ivyforms-my-8" />
          <IvyCheckbox
            v-model="formBuilderStore.storeEntries"
            priority="secondary"
            size="d"
            type="checkmark"
          >
            {{ getLabel('store_entries_db') }}
          </IvyCheckbox>
        </div>
      </IvyForm>
    </IvyScrollbar>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useFormBuilder } from '@/stores/useFormBuilder.ts'
import { useRoute } from 'vue-router'
import { useLabels } from '@/composables/useLabels'
import { useWcagColors } from '@/composables/useWcagColors'
const { getLabel } = useLabels()

const { startWatching } = useWcagColors()
startWatching()

const route = useRoute()

const formData = ref({
  formName: '',
  formDescription: '',
})

const formBuilderStore = useFormBuilder()
const loading = ref(false)
onMounted(async () => {
  if (route.params.formId) {
    await formBuilderStore.loadForm(route.params.formId as string)
  }
})
const saveForm = async () => {
  loading.value = true
  try {
    await formBuilderStore.saveFormSettings()
  } finally {
    loading.value = false
  }
}
// Function to Update (Existing Form)
const updateForm = async () => {
  loading.value = true
  try {
    await formBuilderStore.updateForm()
  } finally {
    loading.value = false
  }
}
</script>

<style scoped lang="scss">
.ivyforms-form-builder-settings {
  height: 100%;
  &__option-bar {
    border-bottom: 1px solid var(--map-divider);
    background: var(--map-ground-level-1-foreground);

    &__left,
    &__right {
      flex: 1 1 50%; // Make both sections flex-grow and flex-shrink with 50% base width
      min-width: 0; // Allow sections to shrink below their content size if needed
    }

    &__right {
      :deep(.ivyforms-button-action) {
        min-width: 67px;
      }
    }

    .ivyforms-toggle {
      gap: 12px;
    }
  }

  :deep(.ivyforms-form) {
    padding-top: 24px;
    .ivyforms-form-item {
      margin-bottom: 24px;
    }
  }
}
</style>
