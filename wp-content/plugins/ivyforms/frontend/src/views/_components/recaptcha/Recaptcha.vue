<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <div
    ref="recaptchaContainer"
    class="ivyforms-recaptcha-container ivyforms-flex ivyforms-justify-content-start ivyforms-align-items-center"
    :class="{
      'ivyforms-recaptcha-container--invisible':
        config?.type === 'invisible' || config?.type === 'v3',
      'ivyforms-mb-18': !props.error,
    }"
  ></div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch } from 'vue'
import { recaptchaService } from '@/services/recaptcha'
import { useLabels } from '@/composables/useLabels'
import type { RecaptchaConfig } from '@/types/recaptcha/recaptcha-interface'

interface Props {
  config: RecaptchaConfig
  modelValue?: string
  error?: string
}

interface Emits {
  (event: 'update:modelValue', value: string): void
  (event: 'verify', token: string): void
  (event: 'expire'): void
  (event: 'error', error: Error): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const recaptchaContainer = ref<HTMLDivElement>()
const widgetId = ref<number | null>(null)
const isLoading = ref(true)
const internalError = ref<string | null>(null)

const { getLabel } = useLabels()

// Initialize reCAPTCHA when component mounts
onMounted(async () => {
  try {
    await initRecaptcha()
  } catch (err) {
    internalError.value = err instanceof Error ? err.message : getLabel('recaptcha_init_failed')
    emit('error', err instanceof Error ? err : new Error(getLabel('recaptcha_init_failed')))
  }
})

// Clean up when component unmounts
onUnmounted(() => {
  if (widgetId.value !== null) {
    recaptchaService.reset()
  }
})

// Watch for config changes and reinitialize
watch(
  () => props.config,
  async (newConfig) => {
    if (newConfig) {
      await initRecaptcha()
    }
  },
  { deep: true },
)

/**
 * Initialize reCAPTCHA widget
 */
async function initRecaptcha() {
  if (!props.config || !recaptchaContainer.value) {
    return
  }

  try {
    isLoading.value = true
    internalError.value = null

    // Initialize the service with config
    await recaptchaService.init(props.config)

    // Render the widget based on type
    if (props.config.type === 'v3' || props.config.type === 'invisible') {
      // For v3 and invisible, we don't render a visible widget
      // For invisible, we still need to render it but with size: 'invisible'
      if (props.config.type === 'invisible') {
        const options = {
          callback: onVerify,
          'expired-callback': onExpire,
          'error-callback': onError,
          size: 'invisible' as const,
        }
        widgetId.value = await recaptchaService.render(recaptchaContainer.value, options)
      }
      isLoading.value = false
    } else {
      // For v2 checkbox, render the visible widget
      const options = {
        callback: onVerify,
        'expired-callback': onExpire,
        'error-callback': onError,
      }

      widgetId.value = await recaptchaService.render(recaptchaContainer.value, options)
      isLoading.value = false
    }
  } catch (err) {
    internalError.value =
      err instanceof Error ? err.message : getLabel('recaptcha_initialization_failed')
    isLoading.value = false
    emit(
      'error',
      err instanceof Error ? err : new Error(getLabel('recaptcha_initialization_failed')),
    )
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
 * Handle reCAPTCHA errors
 */
function onError() {
  const error = new Error(getLabel('recaptcha_verification_failed'))
  emit('update:modelValue', '')
  emit('error', error)
}

/**
 * Execute reCAPTCHA (for v3 and invisible)
 */
async function execute(action: string = 'submit'): Promise<string> {
  if (props.config.type === 'v3') {
    try {
      const token = await recaptchaService.execute(action)
      emit('update:modelValue', token)
      emit('verify', token)
      return token
    } catch (err) {
      emit('error', err instanceof Error ? err : new Error(getLabel('recaptcha_v3_failed')))
      throw err
    }
  } else if (props.config.type === 'invisible') {
    try {
      const token = await recaptchaService.execute()
      emit('update:modelValue', token)
      emit('verify', token)
      return token
    } catch (err) {
      emit('error', err instanceof Error ? err : new Error(getLabel('recaptcha_invisible_failed')))
      throw err
    }
  } else {
    // For v2, get the current response
    const token = recaptchaService.getResponse()
    if (!token) {
      throw new Error(getLabel('recaptcha_complete_verification'))
    }
    return token
  }
}

/**
 * Reset reCAPTCHA widget
 */
function reset() {
  recaptchaService.reset()
  emit('update:modelValue', '')
}

/**
 * Get current response token
 */
function getResponse(): string {
  return recaptchaService.getResponse()
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
.ivyforms-recaptcha-container {
  min-height: 78px; // Standard reCAPTCHA height

  // Hide for v3 (invisible) and invisible v2
  &:empty {
    min-height: 0;
    display: none;
  }

  // Hide invisible reCAPTCHA containers
  &--invisible {
    min-height: 0;
    display: none;
  }

  :deep(.g-recaptcha) {
    transform-origin: 0 0;
  }

  // Dark theme support
  &.dark-theme {
    :deep(.g-recaptcha) {
      filter: invert(1) hue-rotate(180deg);
    }
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

// Error state
.ivyforms-recaptcha-error {
  color: var(--map-status-error-fill-0);
  font-size: 14px;
  margin-top: 4px;
}
</style>
