<template>
  <div
    class="ivyforms-form-builder-settings-notification ivyforms-flex ivyforms-flex-1 ivyforms-flex-direction-column"
  >
    <div
      class="ivyforms-form-builder-settings-notification__option-bar ivyforms-pb-8 ivyforms-flex"
    >
      <div
        class="ivyforms-form-builder-settings-notification__option-bar__left ivyforms-flex ivyforms-gap-8"
      >
        <IvyToggle
          v-model="notificationStore.enabled"
          priority="primary"
          size="s"
          :title="getLabel('active')"
          text-position="left"
        />
      </div>

      <div
        class="ivyforms-form-builder-settings-notification__option-bar__right ivyforms-flex ivyforms-gap-8"
      >
        <IvyButtonAction
          :class="['ivyforms-button__action--cancel']"
          priority="tertiary"
          @click="goBack"
        >
          {{ getLabel('cancel') }}
        </IvyButtonAction>
        <IvyButtonAction
          :class="`ivyforms-button__action--${isEditing ? 'update' : 'save'}`"
          priority="primary"
          :loading="loading"
          @click="saveNotification"
        >
          <template v-if="!loading">
            {{ getLabel(isEditing ? 'update' : 'save') }}
          </template>
        </IvyButtonAction>
      </div>
    </div>
    <IvyScrollbar modifier="outside-vertical">
      <IvyForm :model="formData">
        <IvyFormItem :label="getLabel('name')" prop="notificationName" secondary>
          <IvyTextInput
            v-model="notificationStore.name"
            :placeholder="getLabel('example')"
          ></IvyTextInput>
        </IvyFormItem>
        <IvyFormItem :label="getLabel('send_from')" prop="notificationSender" secondary>
          <IvyTextInput
            ref="senderInputRef"
            v-model="notificationStore.sender"
            :placeholder="'ivy@forms.io'"
          />
          <IvyPlaceholdersPopover
            :fields="emailFieldsPlaceholders"
            :general="adminEmailGeneralPlaceholders"
            class="ivyforms-form-builder-settings-notification__placeholder-popover"
            @insert-placeholder="(value) => handleInsertPlaceholderInput('sender', value)"
          >
            <template #reference>
              <IvyTooltip :content="getLabel('insert_placeholders')">
                <IvyButtonAction
                  size="s"
                  priority="tertiary"
                  icon-only
                  icon-start="context-menu-dot"
                  icon-start-type="fill"
                  type="ghost"
                  :aria-label="getLabel('placeholders')"
                  class="ivyforms-form-builder-settings-notification__placeholders-btn"
                />
              </IvyTooltip>
            </template>
          </IvyPlaceholdersPopover>
        </IvyFormItem>
        <IvyFormItem :label="getLabel('reply_to')" prop="notificationReplyTo">
          <IvyTextInput
            ref="replyToInputRef"
            v-model="notificationStore.replyTo"
            :placeholder="'ivy@forms.io'"
          />
          <IvyPlaceholdersPopover
            :fields="emailFieldsPlaceholders"
            :general="adminEmailGeneralPlaceholders"
            class="ivyforms-form-builder-settings-notification__placeholder-popover"
            @insert-placeholder="(value) => handleInsertPlaceholderInput('replyTo', value)"
          >
            <template #reference>
              <IvyTooltip :content="getLabel('insert_placeholders')">
                <IvyButtonAction
                  size="s"
                  priority="tertiary"
                  icon-only
                  icon-start="context-menu-dot"
                  icon-start-type="fill"
                  type="ghost"
                  :aria-label="getLabel('placeholders')"
                  class="ivyforms-form-builder-settings-notification__placeholders-btn"
                />
              </IvyTooltip>
            </template>
          </IvyPlaceholdersPopover>
        </IvyFormItem>
        <IvyFormItem :label="getLabel('send_to')" prop="notificationReceiver" secondary>
          <IvyTextInput
            ref="receiverInputRef"
            v-model="notificationStore.receiver"
            :placeholder="'ivy@forms.io'"
          />
          <IvyPlaceholdersPopover
            :fields="emailFieldsPlaceholders"
            :general="adminEmailGeneralPlaceholders"
            class="ivyforms-form-builder-settings-notification__placeholder-popover"
            @insert-placeholder="(value) => handleInsertPlaceholderInput('receiver', value)"
          >
            <template #reference>
              <IvyTooltip :content="getLabel('insert_placeholders')">
                <IvyButtonAction
                  size="s"
                  priority="tertiary"
                  icon-only
                  icon-start="context-menu-dot"
                  icon-start-type="fill"
                  type="ghost"
                  :aria-label="getLabel('placeholders')"
                  class="ivyforms-editor__placeholders-btn"
                />
              </IvyTooltip>
            </template>
          </IvyPlaceholdersPopover>
        </IvyFormItem>
        <IvyFormItem :label="getLabel('subject')" prop="notificationSubject">
          <IvyTextInput
            ref="subjectInputRef"
            v-model="notificationStore.subject"
            :placeholder="getLabel('example')"
            secondary
          />
          <IvyPlaceholdersPopover
            class="ivyforms-form-builder-settings-notification__placeholder-popover"
            @insert-placeholder="(value) => handleInsertPlaceholderInput('subject', value)"
          >
            <template #reference>
              <IvyTooltip :content="getLabel('insert_placeholders')">
                <IvyButtonAction
                  size="s"
                  priority="tertiary"
                  icon-only
                  icon-start="context-menu-dot"
                  icon-start-type="fill"
                  type="ghost"
                  :aria-label="getLabel('placeholders')"
                  class="ivyforms-editor__placeholders-btn"
                />
              </IvyTooltip>
            </template>
          </IvyPlaceholdersPopover>
        </IvyFormItem>
        <IvyFormItem
          :label="getLabel('message')"
          prop="formDescription"
          class="ivyforms-form-builder-settings-notification__editor"
        >
          <IvyEditor
            ref="editorRef"
            v-model="notificationStore.message"
            :fields-placeholders="fieldsPlaceholders"
            :general-placeholders="generalPlaceholders"
            @insert-placeholder="handleInsertPlaceholder"
          />
        </IvyFormItem>
      </IvyForm>
    </IvyScrollbar>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useFormBuilder } from '@/stores/useFormBuilder.ts'
import { useNotificationSettingBuilder } from '@/stores/useNotificationSettingBuilder'
import { storeToRefs } from 'pinia'
import { useRouter, useRoute } from 'vue-router'
import { useLabels } from '@/composables/useLabels'
import { IVYFORMS_FORM_NOTIFICATIONS } from '@/constants/pages.ts'
import { getGeneralPlaceholders } from '@/constants/generalPlaceholders.ts'
import { useWcagColors } from '@/composables/useWcagColors'

const router = useRouter()
const route = useRoute()
const { getLabel } = useLabels()
const { startWatching } = useWcagColors()
startWatching()

// Extract formId and notificationId from route
const formId = computed(() => {
  const id = route.params.formId
  return Array.isArray(id) ? id[0] : id
})
const notificationId = computed(() => {
  const id = route.params.notificationId
  return Array.isArray(id) ? id[0] : id
})

const goBack = () => {
  router.push({
    name: IVYFORMS_FORM_NOTIFICATIONS,
    params: { formId: formId.value },
  })
}

const notificationStore = useNotificationSettingBuilder()
const { isEditing } = storeToRefs(notificationStore)

const formData = ref({
  formName: '',
  formDescription: '',
})

// Load notification if notificationId exists
if (notificationId.value) {
  notificationStore.loadNotification(Number(notificationId.value))
  notificationStore.isEditing = true
} else {
  notificationStore.resetNotification(formId.value)
  notificationStore.isEditing = false
}

const formBuilderStore = useFormBuilder()
const { formId: storeFormId, fields: storeFields } = storeToRefs(formBuilderStore)
const loading = ref(false)

const editorRef = ref(null)
const senderInputRef = ref()
const replyToInputRef = ref()
const receiverInputRef = ref()
const subjectInputRef = ref()

function handleInsertPlaceholder(placeholder) {
  if (editorRef.value && editorRef.value.editor) {
    const editor = editorRef.value.editor
    editor.chain().focus().insertContent(placeholder).run()
  }
}

const fieldsPlaceholders = computed(() => {
  return (formBuilderStore.fields || []).map((field) => ({
    label: field.label,
    value: `{{${field.type}_${field.fieldIndex}}}`,
    type: field.type,
    fieldIndex: field.fieldIndex,
  }))
})

const generalPlaceholders = computed(() => getGeneralPlaceholders())

// Filtered for email fields only
const emailFieldsPlaceholders = computed(() =>
  fieldsPlaceholders.value.filter((f) => f.type === 'email'),
)
// Only admin email from general placeholders
const adminEmailGeneralPlaceholders = computed(() =>
  generalPlaceholders.value.filter((item) => item.key && item.key === 'admin_email'),
)

const saveNotification = async () => {
  loading.value = true
  try {
    const notificationPayload = {
      name: notificationStore.name,
      sender: notificationStore.sender,
      replyTo: notificationStore.replyTo,
      receiver: notificationStore.receiver,
      enabled: notificationStore.enabled,
      subject: notificationStore.subject,
      message: notificationStore.message,
      smartLogic: notificationStore.smartLogic,
      formId: formId.value || notificationStore.formId,
    }

    if (notificationStore.isEditing && notificationId.value) {
      await notificationStore.updateNotification(notificationId.value, notificationPayload)
    } else {
      await notificationStore.createNotification(formId.value)
    }
    goBack()
  } catch (error) {
    console.error(getLabel('error_saving_notification'), error)
  } finally {
    loading.value = false
  }
}

function handleInsertPlaceholderInput(
  field: 'sender' | 'receiver' | 'subject' | 'replyTo',
  value: string,
) {
  let inputRef
  switch (field) {
    case 'sender':
      inputRef = senderInputRef.value
      break
    case 'replyTo':
      inputRef = replyToInputRef.value
      break
    case 'receiver':
      inputRef = receiverInputRef.value
      break
    case 'subject':
      inputRef = subjectInputRef.value
      break
  }
  if (inputRef && inputRef.$el) {
    // Try to find the native input element
    const input = inputRef.$el.querySelector('input') || inputRef.$el.querySelector('textarea')
    if (input) {
      const start = input.selectionStart
      const end = input.selectionEnd
      const originalValue = input.value
      input.value = originalValue.slice(0, start) + value + originalValue.slice(end)
      input.dispatchEvent(new Event('input'))
      // Move cursor after inserted placeholder
      input.setSelectionRange(start + value.length, start + value.length)
      input.focus()
    }
  }
}

// Ensure the form builder store has the form loaded when this view is opened directly
onMounted(() => {
  if (formId.value) {
    const needsLoad =
      !storeFormId.value ||
      String(storeFormId.value) !== String(formId.value) ||
      (Array.isArray(storeFields.value) && storeFields.value.length === 0)

    if (needsLoad) {
      formBuilderStore.loadForm(String(formId.value))
    }
  }
})
</script>

<style scoped lang="scss">
.ivyforms-form-builder-settings-notification {
  height: 100%;

  &__option-bar {
    border-bottom: 1px solid var(--map-divider);
    background: var(--map-ground-level-1-foreground);
    &__left,
    &__right {
      flex: 1 1 50%; // Make both sections flex-grow and flex-shrink with 50% base width
      min-width: 0; // Allow sections to shrink below their content size if needed

      .ivyforms-button__action {
        &--save {
          :deep(.ivyforms-button-action) {
            min-width: 67px;
          }
        }
        &--update {
          :deep(.ivyforms-button-action) {
            min-width: 83px;
          }
        }
      }
    }

    &__left {
      align-items: center;
      justify-content: flex-start;
    }

    &__right {
      align-items: center;
      justify-content: flex-end;
    }
  }

  :deep(.ivyforms-form) {
    padding-top: 24px;
    .ivyforms-form-item {
      margin-bottom: 24px;
    }
  }

  &__placeholder-popover {
    position: absolute;
    right: 10px;
    bottom: -3px;
    z-index: 1;

    &:has(.el-popover:not([style*='display: none'])) {
      z-index: 1000;
    }
  }

  .ivyforms-form-checkbox {
    gap: 16px;
    flex-grow: 1;
  }

  .ivyforms-toggle {
    gap: 12px;
  }
}
</style>
