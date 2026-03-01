<template>
  <div
    v-if="isTurnstileConfigured"
    class="ivyforms-turnstile-container ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-12 ivyforms-width-100"
    :class="{ 'ivyforms-turnstile-container--has-error': error }"
  >
    <Turnstile
      :site-key="turnstileConfig.siteKey"
      :theme="turnstileConfig.theme"
      :language="turnstileConfig.language"
      :error="turnstileError"
      @verify="handleVerify"
      @error="handleError"
      @expire="handleExpire"
    />

    <div
      v-if="turnstileError"
      class="ivyforms-security-field-error ivyforms-flex ivyforms-align-items-center regular-14 ivyforms-mt-6 ivyforms-gap-4"
    >
      <IvyIcon name="danger" size="s" type="fill-duo" color="var(--map-status-error-symbol-0)" />
      {{ turnstileError }}
    </div>
  </div>
  <div v-else class="ivyforms-turnstile-not-configured ivyforms-width-100">
    <IvyAlert :description="getLabel('turnstile_not_configured_public')" type="warning" />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useLabels } from '@/composables/useLabels'
import Turnstile from '@/views/_components/turnstile/Turnstile.vue'
import IvyAlert from '@/views/_components/alert/IvyAlert.vue'
import IvyIcon from '@/views/_components/icon/IvyIcon.vue'
import type { Field } from '@/types/field'
import type { TurnstileConfig, WindowTurnstileConfig } from '@/types/turnstile/turnstile-interface'
import type { TurnstileTheme } from '@/types/turnstile/turnstile-type'

declare global {
  interface Window {
    wpIvyTurnstileConfig?: WindowTurnstileConfig | unknown[]
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
const turnstileToken = ref('')
const turnstileError = ref('')

// Check if Turnstile is configured
const isTurnstileConfigured = computed(() => {
  // Check if global Turnstile config exists and is configured
  if (!window.wpIvyTurnstileConfig || Array.isArray(window.wpIvyTurnstileConfig)) {
    return false
  }

  const config = window.wpIvyTurnstileConfig as WindowTurnstileConfig
  const turnstileData = config.turnstile

  if (!turnstileData) {
    return false
  }

  // Convert configured value to boolean (it might be string or number from PHP)
  const isConfigured =
    turnstileData.configured === true ||
    turnstileData.configured === 1 ||
    turnstileData.configured === '1'

  return isConfigured && !!turnstileData.siteKey
})

// Get Turnstile configuration from window object
const turnstileConfig = computed<TurnstileConfig>(() => {
  if (!window.wpIvyTurnstileConfig || Array.isArray(window.wpIvyTurnstileConfig)) {
    return {
      siteKey: '',
      theme: 'auto',
      language: '',
    }
  }

  const config = window.wpIvyTurnstileConfig as WindowTurnstileConfig
  const turnstileData = config.turnstile

  if (!turnstileData) {
    return {
      siteKey: '',
      theme: 'auto',
      language: '',
    }
  }

  return {
    siteKey: turnstileData.siteKey || '',
    theme: (turnstileData.theme as TurnstileTheme) || 'auto',
    language: turnstileData.language || '',
  }
})

// Handle successful verification
const handleVerify = (token: string) => {
  turnstileToken.value = token
  turnstileError.value = ''
  emit('update:modelValue', token)
}

// Handle error
const handleError = (error: string) => {
  turnstileError.value = error || getLabel('turnstile_verification_failed')
  turnstileToken.value = ''
  emit('update:modelValue', '')
}

// Handle expiration
const handleExpire = () => {
  turnstileError.value = getLabel('turnstile_expired')
  turnstileToken.value = ''
  emit('update:modelValue', '')
}

// Watch for external errors
watch(
  () => props.error,
  (newError) => {
    if (newError) {
      turnstileError.value = newError
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
