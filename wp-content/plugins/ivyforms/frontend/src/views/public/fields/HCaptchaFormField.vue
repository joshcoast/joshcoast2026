<template>
  <div
    v-if="isHCaptchaConfigured"
    class="ivyforms-hcaptcha-container ivyforms-flex ivyforms-flex-direction-column ivyforms-width-100"
    :class="{ 'ivyforms-hcaptcha-container--has-error': error }"
  >
    <HCaptcha
      :site-key="hcaptchaConfig.siteKey"
      :type="hcaptchaConfig.type"
      :error="hcaptchaError"
      @verify="handleVerify"
      @error="handleError"
      @expire="handleExpire"
    />

    <div
      v-if="hcaptchaError"
      class="ivyforms-security-field-error ivyforms-flex ivyforms-align-items-center regular-14 ivyforms-mt-6 ivyforms-gap-4"
    >
      <IvyIcon name="danger" size="s" type="fill-duo" color="var(--map-status-error-symbol-0)" />
      {{ hcaptchaError }}
    </div>
  </div>
  <div v-else class="ivyforms-hcaptcha-not-configured ivyforms-width-100">
    <IvyAlert :description="getLabel('hcaptcha_not_configured_public')" type="warning" />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useLabels } from '@/composables/useLabels'
import HCaptcha from '@/views/_components/hcaptcha/HCaptcha.vue'
import IvyAlert from '@/views/_components/alert/IvyAlert.vue'
import IvyIcon from '@/views/_components/icon/IvyIcon.vue'
import type { Field } from '@/types/field'
import type { HCaptchaConfig, WindowHCaptchaConfig } from '@/types/hcaptcha/hcaptcha-interface'
import type { HCaptchaType } from '@/types/hcaptcha/hcaptcha-type'

declare global {
  interface Window {
    wpIvyHCaptchaConfig?: WindowHCaptchaConfig | unknown[]
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
const hcaptchaToken = ref('')
const hcaptchaError = ref('')

// Check if hCaptcha is configured
const isHCaptchaConfigured = computed(() => {
  // Check if global hCaptcha config exists and is configured
  if (!window.wpIvyHCaptchaConfig || Array.isArray(window.wpIvyHCaptchaConfig)) {
    return false
  }

  const config = window.wpIvyHCaptchaConfig as WindowHCaptchaConfig
  const hcaptchaData = config.hcaptcha

  if (!hcaptchaData) {
    return false
  }

  // Convert configured value to boolean (it might be string or number from PHP)
  const isConfigured =
    hcaptchaData.configured === true ||
    hcaptchaData.configured === 1 ||
    hcaptchaData.configured === '1'

  return isConfigured && !!hcaptchaData.siteKey
})

// Get hCaptcha configuration from window object
const hcaptchaConfig = computed<HCaptchaConfig>(() => {
  if (!window.wpIvyHCaptchaConfig || Array.isArray(window.wpIvyHCaptchaConfig)) {
    return {
      siteKey: '',
      type: 'checkbox',
    }
  }

  const config = window.wpIvyHCaptchaConfig as WindowHCaptchaConfig
  const hcaptchaData = config.hcaptcha

  if (!hcaptchaData) {
    return {
      siteKey: '',
      type: 'checkbox',
    }
  }

  return {
    siteKey: hcaptchaData.siteKey || '',
    type: (hcaptchaData.type as HCaptchaType) || 'checkbox',
  }
})

// Handle successful verification
const handleVerify = (token: string) => {
  hcaptchaToken.value = token
  hcaptchaError.value = ''
  emit('update:modelValue', token)
}

// Handle error
const handleError = (error: string) => {
  hcaptchaError.value = error || getLabel('hcaptcha_verification_failed')
  hcaptchaToken.value = ''
  emit('update:modelValue', '')
}

// Handle expiration
const handleExpire = () => {
  hcaptchaError.value = getLabel('hcaptcha_expired')
  hcaptchaToken.value = ''
  emit('update:modelValue', '')
}

// Watch for external errors
watch(
  () => props.error,
  (newError) => {
    if (newError) {
      hcaptchaError.value = newError
    }
  },
  { immediate: true },
)
</script>

<style lang="scss" scoped>
.ivyforms-security-field-error {
  color: var(--map-status-error-symbol-0);
}
</style>
