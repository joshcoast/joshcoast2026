<template>
  <!-- Add scenario-specific class for styling -->
  <IvyDialog
    v-model="dialogVisible"
    :show-close="true"
    width="468px"
    align-center
    :class="{ 'ivyforms-pro-upgrade-dialog--scenario-1': isScenario1 }"
  >
    <!-- Body content with icon, title and subtitle -->
    <div class="ivyforms-pro-upgrade-dialog__body">
      <!-- Pro Icon -->
      <div
        class="ivyforms-pro-upgrade-dialog__icon ivyforms-flex ivyforms-align-items-center ivyforms-justify-content-center ivyforms-mb-16"
      >
        <IvyIcon
          :name="dialogData.iconName || 'pro-lightning'"
          type="fill-duo"
          size="l"
          outer-size="40px"
          category="global"
          color="var(--map-accent-amber-symbol-0)"
        />
      </div>

      <h3 v-if="dialogData.title" class="ivyforms-pro-upgrade-dialog__title medium-18">
        {{ dialogData.title }}
      </h3>
      <p v-if="dialogData.subtitle" class="ivyforms-pro-upgrade-dialog__subtitle regular-14">
        {{ dialogData.subtitle }}
      </p>
    </div>

    <!-- Footer with buttons -->
    <template #footer>
      <div
        class="ivyforms-pro-upgrade-dialog__footer ivyforms-flex ivyforms-gap-12 ivyforms-justify-content-center ivyforms-flex-direction-row"
      >
        <IvyButtonAction
          v-if="dialogData.buttons?.confirm"
          priority="secondary"
          size="d"
          full-width
          @click="handleConfirm"
        >
          {{ dialogData.buttons.confirm.text }}
        </IvyButtonAction>
        <IvyButtonAction
          v-if="dialogData.buttons?.close"
          :priority="dialogData.buttons.close.type || 'tertiary'"
          size="d"
          full-width
          @click="handleClose"
        >
          {{ dialogData.buttons.close.text }}
        </IvyButtonAction>
      </div>
    </template>
  </IvyDialog>
</template>

<script setup lang="ts">
import { storeToRefs } from 'pinia'
import { computed } from 'vue'
import { useProUpgradeDialogStore } from '@/stores/proUpgradeDialogStore'
import IvyDialog from '@/views/_components/dialog/IvyDialog.vue'
import IvyButtonAction from '@/views/_components/button/IvyButtonAction.vue'
import IvyIcon from '@/views/_components/icon/IvyIcon.vue'

const proUpgradeStore = useProUpgradeDialogStore()
const { dialogVisible, dialogData } = storeToRefs(proUpgradeStore)

// Scenario 1: Pro not installed (has two buttons - close and confirm)
// Scenario 2: Pro installed but not activated (has only confirm button)
const isScenario1 = computed(() => {
  return !!(dialogData.value.buttons?.close && dialogData.value.buttons?.confirm)
})

const handleClose = () => {
  if (dialogData.value.buttons?.close?.function) {
    dialogData.value.buttons.close.function()
  }
  dialogVisible.value = false
}

const handleConfirm = () => {
  if (dialogData.value.buttons?.confirm?.function) {
    dialogData.value.buttons.confirm.function()
  }
  dialogVisible.value = false
}
</script>

<style lang="scss" scoped>
.ivyforms-pro-upgrade-dialog {
  &__body {
    text-align: center;
  }

  &__title {
    margin: 0 0 12px 0;
    color: var(--map-base-text-0);
    font-size: 18px;
    font-weight: 500;
    line-height: 1.4;
  }

  &__subtitle {
    margin: 0;
    color: var(--map-base-text--1);
    font-size: 14px;
    line-height: 1.5;
  }

  &__footer {
    @media (max-width: 480px) {
      flex-direction: column;
    }
  }
}

// Scenario 1 (Pro not installed) - CSS for dialog body and footer
.ivyforms-pro-upgrade-dialog--scenario-1 {
  :deep(.el-dialog__body) {
    .ivyforms-dialog__body {
      padding: 0 !important;
    }
  }

  :deep(.el-dialog__footer) {
    border-top: none !important;
    justify-content: center !important;
  }
}

// Ensure dialog appears above modals (IvyModal has z-index: 10000)
:deep(.el-overlay) {
  z-index: 10001 !important;
}
</style>
