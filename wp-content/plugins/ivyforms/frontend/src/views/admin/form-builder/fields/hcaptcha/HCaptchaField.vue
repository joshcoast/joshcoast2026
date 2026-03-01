<template>
  <div
    class="ivyforms-form-builder-field-hcaptcha ivyforms-flex ivyforms-flex-direction-column ivyforms-width-100 ivyforms-px-0 ivyforms-py-0"
  >
    <IvyFormItem>
      <!-- Configured hCaptcha - always show the same widget with checkbox, text, and logo -->
      <div v-if="isConfigured">
        <div class="ivyforms-form-builder-field-hcaptcha-widget">
          <div
            class="ivyforms-form-builder-field-hcaptcha-widget__container ivyforms-flex ivyforms-align-items-center ivyforms-gap-12"
          >
            <div class="ivyforms-form-builder-field-hcaptcha-widget__checkbox">
              <!-- Empty checkbox to match hCaptcha design -->
            </div>
            <span
              class="ivyforms-form-builder-field-hcaptcha-widget__text regular-14 ivyforms-flex-1"
              >{{ getLabel('verify_you_are_human') }}</span
            >
            <div
              class="ivyforms-form-builder-field-hcaptcha-widget__right ivyforms-flex ivyforms-flex-direction-column ivyforms-align-items-flex-end"
            >
              <div
                class="ivyforms-form-builder-field-hcaptcha-widget__logo-container ivyforms-flex ivyforms-align-items-center ivyforms-justify-content-center"
              >
                <HCaptchaLogo class="ivyforms-form-builder-field-hcaptcha-widget__logo" />
              </div>
              <div
                class="ivyforms-form-builder-field-hcaptcha-widget__links ivyforms-flex ivyforms-align-items-center ivyforms-gap-4"
              >
                <span class="ivyforms-form-builder-field-hcaptcha-widget__link-text">{{
                  getLabel('privacy')
                }}</span>
                <span class="ivyforms-form-builder-field-hcaptcha-widget__separator">·</span>
                <span class="ivyforms-form-builder-field-hcaptcha-widget__link-text">{{
                  getLabel('terms')
                }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Warning message and configure link for unconfigured state -->
      <div v-else class="ivyforms-hcaptcha-warning">
        <IvyNotification
          state="warning"
          :description="getLabel('hcaptcha_not_configured')"
          :show-button="false"
          :show-link="true"
          :link-text="getLabel('configure_now')"
          :link-url="redirectToSettings()"
        />
      </div>
    </IvyFormItem>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useLabels } from '@/composables/useLabels.ts'
import { useSettingsStore } from '@/stores/useSettingsStore.ts'
import { useNavigation } from '@/composables/useNavigation'
import { IVYFORMS_SETTINGS_PAGE } from '@/constants/pages.ts'
import HCaptchaLogo from '@/assets/images/hCaptcha/hCaptcha-logo.svg'

interface Props {
  fieldIndex: number
}

defineProps<Props>()

const { getLabel } = useLabels()
const settingsStore = useSettingsStore()
const { getAdminPageUrl } = useNavigation()

// Function to redirect to settings page
const redirectToSettings = () => {
  return getAdminPageUrl(IVYFORMS_SETTINGS_PAGE, '/security/hcaptcha')
}

// Computed properties to dynamically get values from the store
const isConfigured = computed(() => settingsStore.isHCaptchaConfigured)
</script>

<style lang="scss" scoped>
.ivyforms-form-builder-field-hcaptcha {
  cursor: default;

  // Configured hCaptcha widget that mimics real hCaptcha appearance
  &-widget {
    border: 1px solid var(--map-base-dusk-stroke--2);
    border-radius: 4px;
    background: var(--map-ground-level-1-foreground);
    padding: 8px 8px 8px 16px;
    min-width: 300px;
    max-width: 450px;
    box-shadow: 0 0 4px rgba(0, 0, 0, 0.08);

    &__checkbox {
      width: 28px;
      height: 28px;
      border: 2px solid var(--map-base-dusk-stroke-0);
      border-radius: 3px;
      background: var(--map-ground-level-1-foreground);
      flex-shrink: 0;
    }

    &__text {
      color: var(--map-base-text-0);
      font-size: 14px;
      font-weight: 400;
    }

    &__logo-container {
      height: auto;
    }

    &__logo {
      height: 24px;
      width: auto;
    }

    &__links {
      font-size: 9px;
      line-height: 1.2;
    }

    &__link-text {
      color: var(--map-base-text-1);
      font-size: 9px;
    }

    &__separator {
      color: var(--map-base-text-2);
      font-size: 9px;
      margin: 0 2px;
    }
  }

  :deep(.el-form-item__content) {
    align-items: start;
    width: 100%;
  }
  .ivyforms-hcaptcha-warning {
    :deep(.ivyforms-icon__svg) {
      width: auto;
    }
  }

  .ivyforms-form-item {
    cursor: default;
    margin-bottom: 0;
    width: 100%;

    :deep(.el-form-item) {
      margin-bottom: 0;
      width: 100%;
    }
    :deep(.el-form-item__label),
    :deep(.ivyforms-form-item__label) {
      display: flex;
      align-items: center;
      color: var(--map-base-text-0);
      font-size: 14px;
      font-style: normal;
      font-weight: 500;
      line-height: 20px;
      margin-bottom: 0;
      height: 0;
      cursor: default !important;
    }
  }
}
</style>
