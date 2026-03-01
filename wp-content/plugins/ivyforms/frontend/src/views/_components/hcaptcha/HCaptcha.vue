<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <div
    ref="hcaptchaContainer"
    class="ivyforms-hcaptcha-container ivyforms-flex ivyforms-justify-content-start ivyforms-align-items-center"
    :class="{
      'ivyforms-hcaptcha-container--invisible': type === 'invisible',
      'ivyforms-mb-18': !props.error,
    }"
  ></div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch } from 'vue'
import { hcaptchaService } from '@/services/hcaptcha'
import { useLabels } from '@/composables/useLabels'
import type { HCaptchaType } from '@/types/hcaptcha/hcaptcha-type'

interface Props {
  siteKey: string
  type: HCaptchaType
  modelValue?: string
  error?: string
}

interface Emits {
  (event: 'update:modelValue', value: string): void
  (event: 'verify', token: string): void
  (event: 'expire'): void
  (event: 'error', error: string): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const hcaptchaContainer = ref<HTMLDivElement>()
const widgetId = ref<string | null>(null)
const isLoading = ref(true)
const internalError = ref<string | null>(null)

const { getLabel } = useLabels()

// Initialize hCaptcha when component mounts
onMounted(async () => {
  try {
    await initHCaptcha()
  } catch (err) {
    internalError.value = err instanceof Error ? err.message : getLabel('hcaptcha_init_failed')
    emit('error', internalError.value)
  }
})

// Clean up when component unmounts
onUnmounted(() => {
  if (widgetId.value !== null) {
    hcaptchaService.reset()
  }
})

// Watch for config changes and reinitialize
watch(
  () => [props.siteKey, props.type],
  async () => {
    await initHCaptcha()
  },
)

/**
 * Initialize hCaptcha widget
 */
async function initHCaptcha() {
  if (!props.siteKey || !hcaptchaContainer.value) {
    return
  }

  try {
    isLoading.value = true
    internalError.value = null

    // Initialize the service
    await hcaptchaService.init(props.siteKey)

    // Render the widget
    const options = {
      callback: onVerify,
      'expired-callback': onExpire,
      'error-callback': onError,
      size: 'normal' as const,
    }

    widgetId.value = await hcaptchaService.render(hcaptchaContainer.value, options)
    isLoading.value = false
  } catch (err) {
    internalError.value =
      err instanceof Error ? err.message : getLabel('hcaptcha_initialization_failed')
    isLoading.value = false
    emit('error', internalError.value)
  }
}

/**
 * Handle successful verification
 */
function onVerify(token: string) {
  emit('update:modelValue', token)
  emit('verify', token)
}

/**
 * Handle token expiration
 */
function onExpire() {
  emit('update:modelValue', '')
  emit('expire')
}

/**
 * Handle hCaptcha errors
 */
function onError() {
  const errorMsg = getLabel('hcaptcha_verification_failed')
  emit('update:modelValue', '')
  emit('error', errorMsg)
}

/**
 * Execute hCaptcha (for invisible)
 */
async function execute(): Promise<string> {
  try {
    const token = await hcaptchaService.execute()
    emit('update:modelValue', token)
    emit('verify', token)
    return token
  } catch (err) {
    const errorMsg = err instanceof Error ? err.message : getLabel('hcaptcha_execution_failed')
    emit('error', errorMsg)
    throw err
  }
}

/**
 * Reset hCaptcha widget
 */
function reset() {
  hcaptchaService.reset()
  emit('update:modelValue', '')
}

/**
 * Get current response token
 */
function getResponse(): string {
  return hcaptchaService.getResponse()
}

// Expose methods for parent components
defineExpose({
  execute,
  reset,
  getResponse,
  isLoading: () => isLoading.value,
  hasError: () => !!internalError.value,
  getError: () => internalError.value,
})
</script>

<style lang="scss" scoped>
.ivyforms-hcaptcha-container {
  min-height: 78px; // Standard hCaptcha height

  // Hide for invisible
  &:empty {
    min-height: 0;
    display: none;
  }

  // Hide invisible hCaptcha containers
  &--invisible {
    min-height: 0;
    display: none;
  }

  :deep(.h-captcha) {
    transform-origin: 0 0;
  }

  @keyframes pulse {
    0%,
    100% {
      opacity: 1;
    }
    50% {
      opacity: 0.5;
    }
  }
}
</style>
