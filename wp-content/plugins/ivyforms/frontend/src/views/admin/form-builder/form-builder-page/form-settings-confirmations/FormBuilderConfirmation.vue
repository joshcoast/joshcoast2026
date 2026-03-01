<template>
  <div
    v-if="isReady"
    class="ivyforms-form-builder-settings-confirmation ivyforms-flex ivyforms-flex-1 ivyforms-flex-direction-column ivyforms-pr-20"
  >
    <div
      class="ivyforms-form-builder-settings-confirmation__option-bar ivyforms-pb-8 ivyforms-flex"
    >
      <!-- Enable Toggle -->
      <div class="ivyforms-form-builder-settings-confirmation__option-bar-left ivyforms-flex">
        <IvyToggle
          v-model="confirmationStore.enabled"
          priority="primary"
          size="s"
          text-position="left"
          :title="getLabel('active')"
        ></IvyToggle>
      </div>

      <!-- Save Button -->
      <div class="ivyforms-form-builder-settings-confirmation__option-bar-right ivyforms-flex">
        <IvyButtonAction
          :class="['ivyforms-button__action--save']"
          priority="primary"
          :loading="loading"
          @click="saveSettings"
        >
          <template v-if="!loading">{{ getLabel('save') }}</template>
        </IvyButtonAction>
      </div>
    </div>

    <!-- Menu -->
    <IvyTabs
      v-model="activeIndex"
      :mode="mode"
      type="tonal"
      priority="tertiary"
      class="ivyforms-form-builder-settings-confirmation__menu"
      :tabs="menuItems.map((item) => ({ name: item.index, label: item.label }))"
    />

    <!-- Content Section -->
    <div
      v-if="activeIndex === 'successMessage'"
      class="ivyforms-form-builder-settings-confirmation__content-section"
    >
      <div
        class="ivyforms-form-builder-settings-confirmation__field-label ivyforms-flex ivyforms-flex-direction-column ivyforms-mb-6 ivyforms-gap-4"
      >
        <div
          class="ivyforms-form-builder-settings-confirmation__field-label-row ivyforms-flex ivyforms-gap-4"
        >
          <span class="confirmation-title medium-14">{{ getLabel('message') }}</span>
          <IvyTooltip
            :content="getLabel('message_tooltip')"
            :aria-label="getLabel('message_tooltip')"
            placement="top"
            theme="inverted"
          >
            <IvyIcon
              name="question"
              type="outline"
              size="s"
              color="var(--map-base-dusk-symbol-2)"
              @click.prevent
            />
          </IvyTooltip>
        </div>
      </div>
      <IvyEditor
        ref="editorRef"
        v-model="confirmationStore.message"
        :fields-placeholders="fieldsPlaceholders"
        :general-placeholders="generalPlaceholders"
        :aria-label="getLabel('success_message')"
        @insert-placeholder="handleInsertPlaceholder"
      />
      <div class="ivyforms-form-builder-settings-confirmation__radio-section">
        <p>{{ getLabel('after_form_submission') }}</p>
        <IvyRadioGroup v-model="confirmationStore.showForm" :priority="'secondary'">
          <IvyRadio :value="false" :priority="'secondary'">{{ getLabel('hide_form') }}</IvyRadio>
          <IvyRadio :value="true" :priority="'secondary'">{{ getLabel('reset_form') }}</IvyRadio>
        </IvyRadioGroup>
      </div>
    </div>

    <div
      v-else-if="activeIndex === 'redirectToPage'"
      class="ivyforms-form-builder-settings-confirmation__content-section"
    >
      <div
        class="ivyforms-form-builder-settings-confirmation__field-label ivyforms-flex ivyforms-flex-direction-column ivyforms-mb-6 ivyforms-gap-4"
      >
        <div
          class="ivyforms-form-builder-settings-confirmation__field-label-row ivyforms-flex ivyforms-gap-4"
        >
          <span class="confirmation-title medium-14">{{ getLabel('select_page') }}</span>
          <IvyTooltip
            :content="getLabel('select_tooltip')"
            :aria-label="getLabel('select_tooltip')"
            placement="top"
            theme="inverted"
          >
            <IvyIcon
              name="question"
              type="outline"
              size="s"
              color="var(--map-base-dusk-symbol-2)"
              @click.prevent
            />
          </IvyTooltip>
        </div>
      </div>
      <IvyFormItem :error="pageError" secondary>
        <IvySelectInput v-model="confirmationStore.page" :placeholder="getLabel('select')">
          <IvySelectOption
            v-for="page in pages"
            :key="page.value"
            :value="page.value"
            :label="page.label"
          >
          </IvySelectOption>
        </IvySelectInput>
      </IvyFormItem>
    </div>

    <div
      v-else-if="activeIndex === 'redirectToCustomUrl'"
      class="ivyforms-form-builder-settings-confirmation__content-section"
    >
      <div
        class="ivyforms-form-builder-settings-confirmation__field-label-row ivyforms-flex ivyforms-gap-4"
      >
        <span class="confirmation-title medium-14">{{ getLabel('custom_url') }}</span>
        <IvyTooltip :content="getLabel('redirection_url_tooltip')" placement="top" theme="inverted">
          <IvyIcon
            name="question"
            type="outline"
            size="s"
            color="var(--map-base-dusk-symbol-2)"
            @click.prevent
          />
        </IvyTooltip>
      </div>
      <IvyFormItem :error="urlError" secondary>
        <IvyTextInput v-model="confirmationStore.url" :placeholder="getLabel('enter_url')" />
      </IvyFormItem>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue'
import { useConfirmationSettingBuilder } from '@/stores/useConfirmationSettingBuilder'
import { useFormBuilder } from '@/stores/useFormBuilder.ts'
import { useRoute } from 'vue-router'
import { useLabels } from '@/composables/useLabels'
import { useApiClient } from '@/composables/useApiClient'
import { isValidUrl } from '@/utils/url'
const { getLabel } = useLabels()
const { request } = useApiClient()
import { getGeneralPlaceholders } from '@/constants/generalPlaceholders.ts'
import { useWcagColors } from '@/composables/useWcagColors'

const route = useRoute()
const { startWatching } = useWcagColors()
startWatching()
// Extract formId and confirmationId from route
const formId = computed(() => {
  const id = route.params.formId
  return Array.isArray(id) ? id[0] : id
})
const confirmationId = computed(() => {
  const id = route.params.confirmationId
  return Array.isArray(id) ? id[0] : id
})

// Use the confirmation store
const confirmationStore = useConfirmationSettingBuilder()
const formBuilderStore = useFormBuilder()

// Bind store state to local state
const mode = ref('horizontal')
const activeIndex = ref('successMessage')
const pages = ref([])
const isReady = ref(false)
const loading = ref(false)
const editorRef = ref(null)
const pageError = ref('')
const urlError = ref('')
// Menu items
const menuItems = [
  { label: getLabel('success_message'), index: 'successMessage' },
  { label: getLabel('redirect_to_page'), index: 'redirectToPage' },
  { label: getLabel('redirect_to_custom_url'), index: 'redirectToCustomUrl' },
]

function handleInsertPlaceholder(placeholder) {
  if (editorRef.value && editorRef.value.editor) {
    const editor = editorRef.value.editor
    editor
      .chain()
      .focus()
      .insertContent(placeholder + ' ')
      .run()
  }
}

const fieldsPlaceholders = computed(() => {
  return (formBuilderStore.fields || []).filter(
    (field) => field.type && typeof field.fieldIndex !== 'undefined',
  )
})

const generalPlaceholders = computed(() => getGeneralPlaceholders())

// Save settings function
const saveSettings = async () => {
  pageError.value = ''
  urlError.value = ''
  // Validation
  if (activeIndex.value === 'redirectToPage' && !confirmationStore.page) {
    pageError.value = getLabel('please_select_a_page')
    return
  }
  if (activeIndex.value === 'redirectToCustomUrl') {
    if (!confirmationStore.url) {
      urlError.value = getLabel('please_enter_a_url')
      return
    }
    if (!isValidUrl(confirmationStore.url)) {
      urlError.value = getLabel('please_enter_a_valid_url')
      return
    }
  }
  loading.value = true

  const confirmationData: {
    formId: string | number
    type: string
    enabled: boolean
    showForm: boolean
    message?: string
    page?: string
    url?: string
  } = {
    formId: formId.value || formBuilderStore.formId,
    type: activeIndex.value,
    enabled: confirmationStore.enabled,
    showForm: confirmationStore.showForm,
  }

  switch (activeIndex.value) {
    case 'successMessage':
      confirmationData.message = confirmationStore.message || ''
      break
    case 'redirectToPage':
      confirmationData.page = confirmationStore.page || ''
      break
    case 'redirectToCustomUrl':
      confirmationData.url = confirmationStore.url || ''
      break
  }

  confirmationStore.type = activeIndex.value
  confirmationStore.formId = Number(formId.value || formBuilderStore.formId)

  try {
    if (confirmationStore.id) {
      await confirmationStore.updateConfirmation(confirmationStore.id, confirmationData)
    } else {
      await confirmationStore.createConfirmation()
    }
  } finally {
    loading.value = false
  }
}

// Load pages and confirmation data on mount
onMounted(async () => {
  try {
    if (formId.value) {
      await formBuilderStore.loadForm(formId.value)
    }
    const { data, error, status } = await request(
      'wp/v2/pages',
      {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
        },
      },
      { useNamespace: false },
    )

    if (!error && status === 200 && Array.isArray(data)) {
      pages.value = data.map((page) => ({
        value: page.id,
        label: page.title.rendered,
      }))
    } else {
      pages.value = []
    }

    if (formId.value && confirmationId.value) {
      await confirmationStore.loadConfirmation(Number(confirmationId.value))
    }
    const type = confirmationStore.type
    activeIndex.value = type || 'successMessage'

    if (typeof confirmationStore.resetEntityState === 'function') {
      confirmationStore.resetEntityState()
    }
    isReady.value = true

    // Map stored page ID to its name for display
    if (confirmationStore.page) {
      const selectedPage = pages.value.find((page) => page.value === Number(confirmationStore.page))
      confirmationStore.page = selectedPage ? selectedPage.value : confirmationStore.page
    }
  } catch (error) {
    console.error(getLabel('error_loading_data_confirmation'), error)
  }
})

watch(
  () => isReady.value && activeIndex.value,
  (val) => {
    if (val && confirmationStore.type !== val) {
      confirmationStore.type = val
    }
  },
)

watch(
  () => isReady.value && confirmationStore.type,
  (newType) => {
    if (newType && activeIndex.value !== newType) {
      activeIndex.value = newType
    }
  },
)

watch(activeIndex, () => {
  pageError.value = ''
  urlError.value = ''
})

// Watch for page selection to clear error
watch(
  () => confirmationStore.page,
  (newPage) => {
    if (pageError.value && newPage) {
      pageError.value = ''
    }
  },
)

// Watch for url input to clear error
watch(
  () => confirmationStore.url,
  (newUrl) => {
    if (!urlError.value) return
    if (urlError.value === getLabel('please_enter_a_url')) {
      if (newUrl && newUrl.trim() !== '') {
        urlError.value = ''
      }
    } else if (urlError.value === getLabel('please_enter_a_valid_url')) {
      if (newUrl && newUrl.trim() !== '') {
        try {
          const url = newUrl.trim()
          if (/^\/\//.test(url)) {
            new URL('http:' + url)
          } else {
            new URL(url)
          }
          urlError.value = ''
        } catch {
          // Input is still not a valid URL; keep the error message.
        }
      }
    }
  },
)
</script>

<style lang="scss">
@use 'sass:list' as *;
@use 'sass:meta' as *;
.ivyforms-form-builder-settings-confirmation {
  flex-direction: column;
  height: 100%;
  &__menu.ivyforms-tabs-wrapper.bg-transparent {
    background-color: var(--map-base-dusk-o10);
    width: 50%;
    min-width: fit-content;
    display: flex;
    align-items: center;
    gap: 4px;
    border-radius: 8px;
    background: var(--map-base-dusk-o10);
    margin-bottom: 24px;
    margin-top: 24px;
    .el-tabs__item.is-active {
      display: flex;
      min-width: 36px;
      justify-content: center;
      align-items: center;
      gap: 8px;
    }
  }
  &__option-bar {
    border-bottom: 1px solid var(--map-divider);
    background: var(--map-ground-level-1-foreground);

    &-left,
    &-right {
      flex: 1 1 50%; // Make both sections flex-grow and flex-shrink with 50% base width
      min-width: 0; // Allow sections to shrink below their content size if needed
    }

    &-left {
      align-items: center;
      justify-content: flex-start;
    }

    &-right {
      align-items: center;
      justify-content: flex-end;

      .ivyforms-button-action {
        min-width: 67px;
      }
    }
  }

  &__content-section {
    .ivyforms-form-item-select {
      .el-select__selection {
        .el-select__selected-item {
          margin-top: 1px;
          margin-left: 1px;
        }
      }
    }
  }
  &__radio-section {
    p {
      color: var(--map-base-text-0);
    }
  }
  &__field-label {
    &-row {
      display: flex;
      align-items: center;
    }

    .confirmation-title {
      color: var(--map-base-text-0);
    }

    .redirection-description {
      color: var(--map-status-warning-symbol-0);
    }
  }
  &__save-section {
    margin-top: 24px;
    text-align: right;
  }
  .ivyforms-toggle {
    gap: 12px;
  }
}
</style>
