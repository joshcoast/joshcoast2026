<template>
  <div
    class="ivyforms-settings-security ivyforms-flex ivyforms-flex-1 ivyforms-flex-direction-column ivyforms-pr-20"
  >
    <div class="ivyforms-settings-security__option-bar ivyforms-pb-8 ivyforms-flex">
      <!-- Page Title -->
      <div class="ivyforms-settings-security__option-bar__left ivyforms-flex">
        <span class="ivyforms-settings-security__title medium-18">{{ getCurrentTitle() }}</span>
      </div>

      <!-- Action Buttons -->
      <div class="ivyforms-settings-security__option-bar__right ivyforms-flex ivyforms-gap-8">
        <IvyButtonAction
          :class="['ivyforms-button__action--reset']"
          priority="danger"
          type="fill"
          :disabled="isResetDisabled"
          @click="resetSettings"
        >
          {{ getLabel('clear_settings') }}
        </IvyButtonAction>
        <IvyButtonAction
          :class="['ivyforms-button__action--save']"
          priority="primary"
          :loading="isLoading"
          @click="saveSettings"
        >
          <template v-if="!isLoading">{{ getLabel('save') }}</template>
        </IvyButtonAction>
      </div>
    </div>

    <!-- Dynamic Captcha Settings Section -->
    <div
      class="ivyforms-settings-security__content ivyforms-pt-20 ivyforms-flex ivyforms-flex-direction-column ivyforms-flex-1 ivyforms-gap-24"
    >
      <!-- reCAPTCHA Settings -->
      <template v-if="getCurrentProvider === 'recaptcha'">
        <div
          class="ivyforms-settings-security__content__wrapper ivyforms-flex ivyforms-flex-direction-column ivyforms-p-24 ivyforms-gap-24"
        >
          <!-- reCAPTCHA Type Tabs -->
          <div class="ivyforms-settings-security__tabs">
            <IvyTabs
              v-model="recaptchaType"
              :tabs="recaptchaTabs"
              size="d"
              priority="tertiary"
              type="tonal"
              :width="200"
              :background="true"
              @update:model-value="updateRecaptchaType"
            />
          </div>

          <!-- Site Key Input -->
          <div
            class="ivyforms-settings-security__field ivyforms-flex ivyforms-flex-direction-column"
          >
            <label
              for="site-key"
              class="ivyforms-settings-security__field-label medium-14 ivyforms-mb-6"
            >
              {{ getLabel('captcha_site_key') }}
            </label>
            <IvyTextInput
              v-model="siteKey"
              secondary
              type="text"
              :placeholder="getLabel('enter_site_key')"
              @update:model-value="updateSiteKey"
            />
          </div>

          <!-- Secret Key Input -->
          <div
            class="ivyforms-settings-security__field ivyforms-flex ivyforms-flex-direction-column"
          >
            <label
              for="secret-key"
              class="ivyforms-settings-security__field-label medium-14 ivyforms-mb-6"
            >
              {{ getLabel('captcha_secret_key') }}
            </label>
            <IvyTextInput
              v-model="secretKey"
              secondary
              type="text"
              :placeholder="getLabel('enter_secret_key')"
              @update:model-value="updateSecretKey"
            />
          </div>

          <IvyNotification
            type="stripe"
            state="default"
            :style="'stripe'"
            :show-button="true"
            :button-label="getLabel('learn_more')"
            :show-title-icon="false"
            :show-description-icon="true"
            :description="getLabel('recaptcha_instruction')"
            @button-click="redirectToRecaptcha"
          />
        </div>

        <!-- reCAPTCHA Language -->
        <div class="ivyforms-settings-security__field ivyforms-flex ivyforms-flex-direction-column">
          <label class="ivyforms-settings-security__field-label medium-14 ivyforms-mb-6">
            {{ getLabel('recaptcha_language') }}
          </label>
          <IvySelectInput
            v-model="recaptchaLanguage"
            :placeholder="getLabel('browser_default')"
            secondary
            @update:model-value="updateRecaptchaLanguage"
          >
            <IvySelectOption
              v-for="option in languageOptions"
              :key="option.value"
              :value="option.value"
              :label="option.label"
              secondary
            />
          </IvySelectInput>
        </div>
      </template>

      <!-- hCaptcha Settings -->
      <template v-else-if="getCurrentProvider === 'hcaptcha'">
        <div
          class="ivyforms-settings-security__content__wrapper ivyforms-flex ivyforms-flex-direction-column ivyforms-p-24 ivyforms-gap-24"
        >
          <!-- Site Key Input -->
          <div
            class="ivyforms-settings-security__field ivyforms-flex ivyforms-flex-direction-column"
          >
            <label
              for="hcaptcha-site-key"
              class="ivyforms-settings-security__field-label medium-14 ivyforms-mb-6"
            >
              {{ getLabel('captcha_site_key') }}
            </label>
            <IvyTextInput
              v-model="hcaptchaSiteKey"
              secondary
              type="text"
              :placeholder="getLabel('enter_hcaptcha_site_key')"
              @update:model-value="updateHcaptchaSiteKey"
            />
          </div>

          <!-- Secret Key Input -->
          <div
            class="ivyforms-settings-security__field ivyforms-flex ivyforms-flex-direction-column"
          >
            <label
              for="hcaptcha-secret-key"
              class="ivyforms-settings-security__field-label medium-14 ivyforms-mb-6"
            >
              {{ getLabel('captcha_secret_key') }}
            </label>
            <IvyTextInput
              v-model="hcaptchaSecretKey"
              secondary
              type="text"
              :placeholder="getLabel('enter_hcaptcha_secret_key')"
              @update:model-value="updateHcaptchaSecretKey"
            />
          </div>

          <IvyNotification
            type="stripe"
            state="default"
            :style="'stripe'"
            :show-button="true"
            :button-label="getLabel('learn_more')"
            :show-title-icon="false"
            :show-description-icon="true"
            :description="getLabel('hcaptcha_instruction')"
            @button-click="redirectToHCaptcha"
          />
        </div>
      </template>

      <!-- Turnstile Settings -->
      <template v-else-if="getCurrentProvider === 'turnstile'">
        <div
          class="ivyforms-settings-security__content__wrapper ivyforms-flex ivyforms-flex-direction-column ivyforms-p-24 ivyforms-gap-24"
        >
          <!-- Site Key Input -->
          <div
            class="ivyforms-settings-security__field ivyforms-flex ivyforms-flex-direction-column"
          >
            <label
              for="turnstile-site-key"
              class="ivyforms-settings-security__field-label medium-14 ivyforms-mb-6"
            >
              {{ getLabel('captcha_site_key') }}
            </label>
            <IvyTextInput
              v-model="turnstileSiteKey"
              secondary
              type="text"
              :placeholder="getLabel('enter_site_key')"
              @update:model-value="updateTurnstileSiteKey"
            />
          </div>

          <!-- Secret Key Input -->
          <div
            class="ivyforms-settings-security__field ivyforms-flex ivyforms-flex-direction-column"
          >
            <label
              for="turnstile-secret-key"
              class="ivyforms-settings-security__field-label medium-14 ivyforms-mb-6"
            >
              {{ getLabel('captcha_secret_key') }}
            </label>
            <IvyTextInput
              v-model="turnstileSecretKey"
              secondary
              type="text"
              :placeholder="getLabel('enter_secret_key')"
              @update:model-value="updateTurnstileSecretKey"
            />
          </div>

          <IvyNotification
            type="stripe"
            state="default"
            :style="'stripe'"
            :show-button="true"
            :button-label="getLabel('learn_more')"
            :show-title-icon="false"
            :show-description-icon="true"
            :description="getLabel('turnstile_instruction')"
            @button-click="redirectToTurnstile"
          />
        </div>

        <!-- Theme Selection -->
        <div
          class="ivyforms-settings-security__field ivyforms-flex ivyforms-flex-direction-column ivyforms-settings-security__radio-section"
        >
          <span class="ivyforms-settings-security__field-label medium-14 ivyforms-mb-8">
            {{ getLabel('turnstile_theme') }}
          </span>
          <IvyRadioGroup v-model="turnstileTheme" :priority="'secondary'">
            <IvyRadio value="auto" :priority="'secondary'" @click="updateTurnstileTheme">{{
              getLabel('auto')
            }}</IvyRadio>
            <IvyRadio value="light" :priority="'secondary'" @click="updateTurnstileTheme">{{
              getLabel('light')
            }}</IvyRadio>
            <IvyRadio value="dark" :priority="'secondary'" @click="updateTurnstileTheme">{{
              getLabel('dark')
            }}</IvyRadio>
          </IvyRadioGroup>
        </div>
      </template>

      <!-- Honeypot Settings -->
      <template v-else-if="getCurrentProvider === 'honeypot'">
        <div class="ivyforms-settings-security__field ivyforms-flex ivyforms-flex-direction-column">
          <label class="ivyforms-settings-security__field-label medium-14 ivyforms-mb-6">
            {{ getLabel('honeypot_protection') }}
          </label>
          <p class="ivyforms-settings-security__field-description medium-14 ivyforms-mb-12">
            {{ getLabel('honeypot_protection_description') }}
          </p>

          <!-- Enable Honeypot Checkbox -->
          <div class="ivyforms-settings-security__toggle">
            <IvyCheckbox
              v-model="honeypotEnabled"
              :label="getLabel('enable_honeypot_protection')"
              @change="updateHoneypotEnabled"
            />
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { useLabels } from '@/composables/useLabels.ts'
import { useSettingsStore } from '@/stores/useSettingsStore.ts'
import { RECAPTCHA_LANGUAGE_OPTIONS } from '@/constants/recaptcha-languages.ts'
import type { RecaptchaType } from '@/types/recaptcha/recaptcha-type'
import IvySelectInput from '@/views/_components/input/IvySelectInput.vue'
import IvySelectOption from '@/views/_components/input/IvySelectOption.vue'
import IvyTabs from '@/views/_components/tabs/IvyTabs.vue'
import IvyCheckbox from '@/views/_components/checkbox/IvyCheckbox.vue'
import IvyRadioGroup from '@/views/_components/radio/IvyRadioGroup.vue'
import IvyRadio from '@/views/_components/radio/IvyRadio.vue'

const { getLabel } = useLabels()
const settingsStore = useSettingsStore()
const route = useRoute()

// Tab configuration
const recaptchaTabs: Array<{ name: RecaptchaType; label: string }> = [
  { name: 'v2', label: 'v2' },
  { name: 'invisible', label: 'Invisible v2' },
  { name: 'v3', label: 'v3' },
]

// Get current captcha provider from route parameter
const getCurrentProvider = computed(() => {
  return (route.params.provider as string) || 'recaptcha'
})

// Get current page title based on captcha provider
const getCurrentTitle = () => {
  const provider = getCurrentProvider.value
  switch (provider) {
    case 'recaptcha':
      return 'reCAPTCHA'
    case 'hcaptcha':
      return 'hCaptcha'
    case 'turnstile':
      return 'Turnstile'
    case 'honeypot':
      return 'Honeypot'
    default:
      return 'reCAPTCHA'
  }
}

// Computed properties that reference the store directly
const recaptchaType = computed({
  get: () => settingsStore.recaptchaType,
  set: (value) => settingsStore.setRecaptchaType(value),
})

const siteKey = computed({
  get: () => settingsStore.recaptchaSiteKey,
  set: (value) => settingsStore.setRecaptchaSiteKey(value),
})

const secretKey = computed({
  get: () => settingsStore.recaptchaSecretKey,
  set: (value) => settingsStore.setRecaptchaSecretKey(value),
})

const recaptchaLanguage = computed({
  get: () => settingsStore.recaptchaLanguage,
  set: (value) => settingsStore.setRecaptchaLanguage(value),
})

// Turnstile computed properties
const turnstileSiteKey = computed({
  get: () => settingsStore.turnstileSiteKey,
  set: (value) => settingsStore.setTurnstileSiteKey(value),
})

const turnstileSecretKey = computed({
  get: () => settingsStore.turnstileSecretKey,
  set: (value) => settingsStore.setTurnstileSecretKey(value),
})

const turnstileTheme = computed({
  get: () => settingsStore.turnstileTheme,
  set: (value) => settingsStore.setTurnstileTheme(value),
})

// hCaptcha computed properties
const hcaptchaSiteKey = computed({
  get: () => settingsStore.hcaptchaSiteKey,
  set: (value) => settingsStore.setHCaptchaSiteKey(value),
})

const hcaptchaSecretKey = computed({
  get: () => settingsStore.hcaptchaSecretKey,
  set: (value) => settingsStore.setHCaptchaSecretKey(value),
})

// Local state for other captcha providers (until store is updated)
const honeypotEnabled = ref(false)

// Additional computed properties
const isLoading = computed(() => settingsStore.isLoading)

// Language options from constants with translation support
const languageOptions = computed(() => {
  return RECAPTCHA_LANGUAGE_OPTIONS.map((option) => ({
    ...option,
    label: option.value === '' ? getLabel('browser_default') : option.label,
  }))
})

// Update methods
const updateRecaptchaType = (newType?: RecaptchaType) => {
  if (newType) {
    settingsStore.setRecaptchaType(newType)
  } else {
    settingsStore.setRecaptchaType(recaptchaType.value)
  }
}

const updateSiteKey = () => {
  settingsStore.setRecaptchaSiteKey(siteKey.value)
}

const updateSecretKey = () => {
  settingsStore.setRecaptchaSecretKey(secretKey.value)
}

const updateRecaptchaLanguage = () => {
  settingsStore.setRecaptchaLanguage(recaptchaLanguage.value)
}

// Redirect to Google reCAPTCHA page
const redirectToRecaptcha = () => {
  window.open('https://cloud.google.com/security/products/recaptcha', '_blank')
}

// Update methods for other captcha providers
const updateHcaptchaSiteKey = () => {
  settingsStore.setHCaptchaSiteKey(hcaptchaSiteKey.value)
}

const updateHcaptchaSecretKey = () => {
  settingsStore.setHCaptchaSecretKey(hcaptchaSecretKey.value)
}

// Redirect to hCaptcha page
const redirectToHCaptcha = () => {
  window.open('https://www.hcaptcha.com/', '_blank')
}

const updateTurnstileSiteKey = () => {
  settingsStore.setTurnstileSiteKey(turnstileSiteKey.value)
}

const updateTurnstileSecretKey = () => {
  settingsStore.setTurnstileSecretKey(turnstileSecretKey.value)
}

const updateTurnstileTheme = () => {
  settingsStore.setTurnstileTheme(turnstileTheme.value)
}

// Redirect to Cloudflare Turnstile page
const redirectToTurnstile = () => {
  window.open('https://www.cloudflare.com/en-gb/application-services/products/turnstile/', '_blank')
}

const updateHoneypotEnabled = () => {
  // TODO: Implement Honeypot enabled update when store is extended
}

const resetSettings = () => {
  const provider = getCurrentProvider.value

  switch (provider) {
    case 'recaptcha':
      settingsStore.setRecaptchaSiteKey('')
      settingsStore.setRecaptchaSecretKey('')
      settingsStore.setRecaptchaLanguage('')
      break
    case 'hcaptcha':
      settingsStore.setHCaptchaSiteKey('')
      settingsStore.setHCaptchaSecretKey('')
      settingsStore.setHCaptchaType('checkbox')
      break
    case 'turnstile':
      settingsStore.setTurnstileSiteKey('')
      settingsStore.setTurnstileSecretKey('')
      settingsStore.setTurnstileTheme('auto')
      break
    case 'honeypot':
      honeypotEnabled.value = false
      break
  }
}

const saveSettings = async () => {
  const provider = getCurrentProvider.value

  switch (provider) {
    case 'recaptcha':
      await settingsStore.saveRecaptchaSettings()
      break
    case 'hcaptcha':
      await settingsStore.saveHCaptchaSettings()
      break
    case 'turnstile':
      await settingsStore.saveTurnstileSettings()
      break
    case 'honeypot':
      // TODO: Implement Honeypot save method in store
      break
  }
}

// Load settings on component mount and set default type to v2 if not set
onMounted(async () => {
  await settingsStore.loadAllSettings()
  // Set default to v2 if no type is configured
  if (!settingsStore.recaptchaType) {
    settingsStore.setRecaptchaType('v2')
  }
})

// Computed property to determine if reset button should be disabled
const isResetDisabled = computed(() => {
  const provider = getCurrentProvider.value

  switch (provider) {
    case 'recaptcha':
      return (
        !settingsStore.recaptchaSiteKey &&
        !settingsStore.recaptchaSecretKey &&
        !settingsStore.recaptchaLanguage
      )
    case 'hcaptcha':
      return !hcaptchaSiteKey.value && !hcaptchaSecretKey.value
    case 'turnstile':
      return !settingsStore.turnstileSiteKey && !settingsStore.turnstileSecretKey
    case 'honeypot':
      return !honeypotEnabled.value
    default:
      return true
  }
})
</script>

<style lang="scss" scoped>
@use 'sass:list' as *;
@use 'sass:meta' as *;

.ivyforms-settings-security {
  height: 100%;

  &__title {
    color: var(--map-base-text-0);
  }

  &__option-bar {
    border-bottom: 1px solid var(--map-divider);
    background: var(--map-ground-level-1-foreground);

    &__left,
    &__right {
      flex: 1 1 50%;
      min-width: 0;
    }

    &__left {
      align-items: center;
      justify-content: flex-start;
    }

    &__right {
      align-items: center;
      justify-content: flex-end;

      .ivyforms-button__action--save {
        min-width: 67px;
        gap: 0;

        :deep(.ivyforms-button-action),
        :deep(.ivyforms-button-action.is-loading) {
          min-width: 67px;
          gap: 0;
          width: 100%;
        }
      }
    }
  }

  &__content {
    overflow-y: auto;

    &__wrapper {
      border-radius: 16px;
      border: 1px solid var(--map-divider);
    }
  }

  &__field-label {
    color: var(--map-base-text-0);
    font-weight: 500;
  }

  &__field-description {
    color: var(--map-base-text-1);
    font-style: italic;
  }

  &__field {
    .el-radio-group.ivyforms-radio-group {
      padding: 0;
    }
  }
}
</style>

<style lang="scss">
.ivyforms-settings-security {
  &__tabs {
    .ivyforms-tabs-wrapper {
      width: fit-content;
      display: block;

      .ivyforms-tabs-group.el-tabs--card .el-tabs__header .el-tabs__item {
        min-width: auto !important;
      }
    }
  }
}
</style>
