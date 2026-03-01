<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <div
    ref="turnstileContainer"
    class="ivyforms-turnstile-container ivyforms-flex ivyforms-justify-content-start ivyforms-align-items-center"
    :class="{ 'ivyforms-mb-18': !props.error }"
  ></div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch } from 'vue'
import { turnstileService } from '@/services/turnstile'
import { useLabels } from '@/composables/useLabels'
import type { TurnstileTheme } from '@/types/turnstile/turnstile-type'

interface Props {
  siteKey: string
  theme?: TurnstileTheme
  language?: string
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

const turnstileContainer = ref<HTMLDivElement>()
const widgetId = ref<string | null>(null)
const isLoading = ref(true)
const internalError = ref<string | null>(null)

const { getLabel } = useLabels()

// Initialize Turnstile when component mounts
onMounted(async () => {
  try {
    await initTurnstile()
  } catch (err) {
    internalError.value = err instanceof Error ? err.message : getLabel('turnstile_init_failed')
    emit('error', internalError.value)
  }
})

// Clean up when component unmounts
onUnmounted(() => {
  if (widgetId.value !== null) {
    turnstileService.reset()
  }
})

// Watch for config changes and reinitialize
watch(
  () => [props.siteKey, props.theme, props.language],
  async () => {
    await initTurnstile()
  },
)

/**
 * Initialize Turnstile widget
 */
async function initTurnstile() {
  if (!props.siteKey || !turnstileContainer.value) {
    return
  }

  try {
    isLoading.value = true
    internalError.value = null

    // Initialize the service
    await turnstileService.init(props.siteKey, props.language)

    // Render the widget
    const options = {
      callback: onVerify,
      'expired-callback': onExpire,
      'error-callback': onError,
      size: 'normal' as const,
      theme: props.theme || 'auto',
    }

    widgetId.value = await turnstileService.render(turnstileContainer.value, options)
    isLoading.value = false
  } catch (err) {
    internalError.value =
      err instanceof Error ? err.message : getLabel('turnstile_initialization_failed')
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
 * Handle Turnstile errors
 */
function onError() {
  const errorMsg = getLabel('turnstile_verification_failed')
  emit('update:modelValue', '')
  emit('error', errorMsg)
}

/**
 * Execute Turnstile (for invisible)
 */
async function execute(): Promise<string> {
  try {
    const token = await turnstileService.execute()
    emit('update:modelValue', token)
    emit('verify', token)
    return token
  } catch (err) {
    const errorMsg = err instanceof Error ? err.message : getLabel('turnstile_execution_failed')
    emit('error', errorMsg)
    throw err
  }
}

/**
 * Reset Turnstile widget
 */
function reset() {
  turnstileService.reset()
  emit('update:modelValue', '')
}

/**
 * Get current response token
 */
function getResponse(): string {
  return turnstileService.getResponse()
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
.ivyforms-turnstile-container {
  min-height: 65px; // Standard Turnstile height

  // Hide for invisible
  &:empty {
    min-height: 0;
    display: none;
  }

  // Hide invisible Turnstile containers
  &--invisible {
    min-height: 0;
    display: none;
  }

  :deep(.cf-turnstile) {
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
