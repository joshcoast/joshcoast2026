<template>
  <div class="ivyforms-recaptcha-field ivyforms-mx-0 ivyforms-my-20">
    <!-- Show reCAPTCHA widget if configured -->
    <Recaptcha
      v-if="isRecaptchaConfigured"
      ref="recaptchaWidgetRef"
      v-model="recaptchaToken"
      :config="recaptchaConfig"
      :error="recaptchaError"
      @verify="onRecaptchaVerify"
      @expire="onRecaptchaExpire"
      @error="onRecaptchaError"
    />

    <!-- Show error message if present -->
    <div
      v-if="recaptchaError"
      class="ivyforms-security-field-error ivyforms-flex ivyforms-align-items-center regular-14 ivyforms-mt-6 ivyforms-gap-4"
    >
      <IvyIcon name="danger" size="s" type="fill-duo" color="var(--map-status-error-symbol-0)" />
      {{ recaptchaError }}
    </div>

    <!-- Show warning message if not configured -->
    <div v-else-if="!isRecaptchaConfigured" class="ivyforms-recaptcha-not-configured">
      <IvyAlert :description="getLabel('recaptcha_not_configured')" type="warning" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useLabels } from '@/composables/useLabels'
import Recaptcha from '@/views/_components/recaptcha/Recaptcha.vue'
import IvyAlert from '@/views/_components/alert/IvyAlert.vue'
import IvyIcon from '@/views/_components/icon/IvyIcon.vue'
import type { Field } from '@/types/field'
import type { RecaptchaConfig, WindowRecaptchaConfig } from '@/types/recaptcha/recaptcha-interface'
import type { RecaptchaType } from '@/types/recaptcha/recaptcha-type'

declare global {
  interface Window {
    wpIvyRecaptchaConfig?: WindowRecaptchaConfig | unknown[]
  }
}

interface Props {
  field: Field
  modelValue?: string
  error?: string
}

interface Emits {
  (event: 'update:modelValue', value: string): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const { getLabel } = useLabels()
const recaptchaToken = ref('')
const recaptchaError = ref('')

// Watch for error changes from parent component
watch(
  () => props.error,
  (newError) => {
    if (newError) {
      recaptchaError.value = newError
    }
  },
  { immediate: true },
)

// Check if reCAPTCHA is configured
const isRecaptchaConfigured = computed(() => {
  // Check if global reCAPTCHA config exists and is configured
  if (typeof window !== 'undefined' && window.wpIvyRecaptchaConfig) {
    const config = window.wpIvyRecaptchaConfig as WindowRecaptchaConfig

    // Handle both empty array case and proper object case
    if (!config || Array.isArray(config) || typeof config !== 'object') {
      return false
    }

    // Check if CAPTCHA is enabled
    const isEnabled =
      config.enabled === true ||
      config.enabled === 'true' ||
      config.enabled === '1' ||
      config.enabled === 1

    // Check if provider is recaptcha
    const isRecaptchaProvider = config.provider === 'recaptcha'

    // Check if recaptcha configuration exists and is configured
    const hasRecaptchaConfig = config.recaptcha && typeof config.recaptcha === 'object'

    if (!hasRecaptchaConfig) {
      return false
    }

    const recaptchaConfig = config.recaptcha

    // Check if properly configured - handle both boolean and string values
    const isConfigured =
      recaptchaConfig.configured === true ||
      recaptchaConfig.configured === 'true' ||
      recaptchaConfig.configured === '1' ||
      recaptchaConfig.configured === 1

    // Check if it has valid site key (the actual fix!)
    const hasSiteKey =
      recaptchaConfig.siteKey &&
      typeof recaptchaConfig.siteKey === 'string' &&
      recaptchaConfig.siteKey.trim() !== ''

    return isEnabled && isRecaptchaProvider && isConfigured && hasSiteKey
  }

  return false
})

// reCAPTCHA configuration from global settings
const recaptchaConfig = computed((): RecaptchaConfig => {
  if (typeof window !== 'undefined' && window.wpIvyRecaptchaConfig) {
    const config = window.wpIvyRecaptchaConfig as WindowRecaptchaConfig
    // Only process if config is a proper object (not array) and has recaptcha config
    if (config && typeof config === 'object' && !Array.isArray(config) && config.recaptcha) {
      const recaptchaData = config.recaptcha
      return {
        siteKey: recaptchaData.siteKey || '',
        type: (recaptchaData.type || 'v2') as RecaptchaType,
        theme: (recaptchaData.theme || 'light') as 'light' | 'dark',
        language: recaptchaData.language || 'en',
      }
    }
  }

  return {
    siteKey: '',
    type: 'v2' as RecaptchaType,
    theme: 'light' as const,
    language: 'en',
  }
})

// Handle reCAPTCHA verification
const onRecaptchaVerify = (token: string) => {
  recaptchaToken.value = token
  recaptchaError.value = '' // Clear any previous errors
  emit('update:modelValue', token)
}

// Handle reCAPTCHA expiration
const onRecaptchaExpire = () => {
  recaptchaToken.value = ''
  recaptchaError.value = getLabel('recaptcha_expired')
  emit('update:modelValue', '')
}

// Handle reCAPTCHA errors
const onRecaptchaError = (error: Error) => {
  console.error('reCAPTCHA error:', error)
  recaptchaToken.value = ''
  recaptchaError.value = error.message
  emit('update:modelValue', '')
}

// For v3, we need to execute the reCAPTCHA when the form is submitted
const recaptchaWidgetRef = ref<InstanceType<typeof Recaptcha>>()

// Expose method for parent components to trigger v3 execution
const executeRecaptcha = async (action = 'submit') => {
  recaptchaError.value = ''

  if (!recaptchaWidgetRef.value) {
    const errorMsg = getLabel('recaptcha_widget_not_initialized_field')
    recaptchaError.value = errorMsg
    throw new Error(errorMsg)
  }

  if (recaptchaConfig.value.type === 'v3') {
    try {
      return await recaptchaWidgetRef.value.execute(action)
    } catch (error) {
      const errorMsg = getLabel('recaptcha_v3_failed')
      console.error(errorMsg, error)
      recaptchaError.value = errorMsg
      throw error
    }
  } else if (recaptchaConfig.value.type === 'invisible') {
    try {
      return await recaptchaWidgetRef.value.execute()
    } catch (error) {
      const errorMsg = getLabel('recaptcha_invisible_failed')
      console.error(errorMsg, error)
      recaptchaError.value = errorMsg
      throw error
    }
  }

  // For v2 checkbox, check if we have a token
  const currentToken = recaptchaToken.value
  if (!currentToken) {
    const errorMsg = getLabel('recaptcha_complete_verification')
    recaptchaError.value = errorMsg
    throw new Error(errorMsg)
  }

  return currentToken
}

// Expose the execute method for parent components
defineExpose({
  executeRecaptcha,
})

// Initialize with current value
onMounted(() => {
  if (props.modelValue) {
    recaptchaToken.value = props.modelValue
    recaptchaError.value = '' // Clear any errors when we have a valid value
  }
})
</script>

<style lang="scss" scoped>
.ivyforms-security-field-error {
  color: var(--map-status-error-symbol-0);
}
</style>
