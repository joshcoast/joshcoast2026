<template>
  <div class="ivyforms-form-builder-field-recaptcha">
    <IvyFormItem>
      <!-- Configured reCAPTCHA - show different widgets based on type -->
      <div v-if="isConfigured">
        <!-- v2 checkbox reCAPTCHA widget -->
        <div v-if="recaptchaType === 'v2'" class="ivyforms-recaptcha-widget">
          <div class="ivyforms-recaptcha-widget__header">
            <div class="ivyforms-recaptcha-widget__checkbox">
              <IvyIcon
                name="check"
                type="outline"
                category="global"
                size="s"
                class="ivyforms-recaptcha-widget__icon"
              />
            </div>
            <span class="ivyforms-recaptcha-widget__text"> {{ getLabel('not_a_robot') }}</span>
            <div class="ivyforms-recaptcha-widget__logos">
              <IvyIcon name="recaptcha-colored" type="fill" category="security" size="d" />
            </div>
          </div>
        </div>

        <!-- v3 and invisible reCAPTCHA - show "protected by reCAPTCHA" badge -->
        <div v-else class="ivyforms-recaptcha-badge">
          <div class="ivyforms-recaptcha-badge__icon">
            <IvyIcon name="recaptcha-colored" type="fill" category="security" size="d" />
          </div>
          <div class="ivyforms-recaptcha-badge__content">
            <div class="ivyforms-recaptcha-badge__text">
              <span class="ivyforms-recaptcha-badge__protected"
                >{{ getLabel('protected_by') }} reCAPTCHA</span
              >
            </div>
            <div class="ivyforms-recaptcha-badge__links">
              <a href="#" class="ivyforms-recaptcha-badge__link">{{ getLabel('privacy') }}</a>
              <span class="ivyforms-recaptcha-badge__separator">-</span>
              <a href="#" class="ivyforms-recaptcha-badge__link">{{ getLabel('terms') }}</a>
            </div>
          </div>
        </div>
      </div>

      <!-- Warning message and configure link for unconfigured state -->
      <div v-else class="ivyforms-recaptcha-warning">
        <IvyNotification
          state="warning"
          :description="getLabel('recaptcha_not_configured')"
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

interface Props {
  fieldIndex: number
}

defineProps<Props>()

const { getLabel } = useLabels()
const settingsStore = useSettingsStore()
const { getAdminPageUrl } = useNavigation()

// Function to redirect to settings page
const redirectToSettings = () => {
  return getAdminPageUrl(IVYFORMS_SETTINGS_PAGE, '/security/recaptcha')
}

// Computed properties to dynamically get values from the store
const isConfigured = computed(() => settingsStore.isRecaptchaConfigured)
const recaptchaType = computed(() => settingsStore.recaptchaType)
</script>

<style lang="scss" scoped>
.ivyforms-form-builder-field-recaptcha {
  display: flex;
  flex-direction: column;
  padding: 12px 0;
  cursor: default;
  width: 100%;

  :deep(.el-form-item__content) {
    align-items: start;
    width: 100%;
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

// Configured reCAPTCHA widget that mimics real reCAPTCHA appearance
.ivyforms-recaptcha-widget {
  border: 1px solid var(--map-base-dusk-stroke--2);
  border-radius: 3px;
  background: var(--map-ground-level-1-foreground);
  padding: 12px 16px;
  min-width: 280px;
  max-width: 300px;

  &__header {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  &__checkbox {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    border: 1px solid var(--map-base-dusk-stroke--1);
    border-radius: 2px;
    background: var(--map-ground-level-1-foreground);
    color: var(--map-base-brand-symbol-0);

    svg {
      fill: var(--map-base-dusk-symbol-0);
    }
  }

  &__text {
    font-size: 14px;
    color: var(--map-base-text-0);
    flex: 1;
    line-height: 1.2;
  }

  &__logos {
    display: flex;
    flex-direction: column;
    align-items: center;
    opacity: 0.8;
    color: var(--map-base-dusk-symbol--1);
  }
  &__icon {
    flex-shrink: 0;
    fill: var(--map-base-dusk-symbol-2);
  }
}

.ivyforms-recaptcha-warning {
  width: 100%;
  margin-top: 8px;

  :deep(.ivyforms-notification__content) {
    flex: none;
  }
  :deep(.ivyforms-icon__svg) {
    width: auto;
  }
}

// reCAPTCHA badge for v3 and invisible types (mimics "protected by reCAPTCHA")
.ivyforms-recaptcha-badge {
  display: inline-flex;
  align-items: stretch;
  border-radius: 3px;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.15);
  cursor: default;
  user-select: none;
  min-width: 156px;
  overflow: hidden;

  &__icon {
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--map-ground-level-1-foreground);
    padding: 8px;
    flex-shrink: 0;
  }

  &__content {
    display: flex;
    flex-direction: column;
    background: var(--map-accent-blue-fill-0);
    color: var(--primitive-white);
    padding: 6px 8px;
    flex: 1;
    justify-content: center;
  }

  &__text {
    display: flex;
    flex-direction: column;
    line-height: 1.2;
    margin-bottom: 2px;
  }

  &__protected {
    font-size: 11px;
    color: var(--primitive-white);
    font-weight: 400;
    line-height: 12px;
    margin-bottom: 1px;
  }

  &__recaptcha {
    font-size: 12px;
    font-weight: 500;
    color: var(--primitive-white);
    line-height: 14px;
  }

  &__links {
    display: flex;
    align-items: center;
    gap: 3px;
    font-size: 8px;
    line-height: 10px;
  }

  &__link {
    color: var(--primitive-white);
    text-decoration: none;

    &:hover {
      text-decoration: underline;
    }
  }

  &__separator {
    margin: 0 1px;
  }
}
</style>
