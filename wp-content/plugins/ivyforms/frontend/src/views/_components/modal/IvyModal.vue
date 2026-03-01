<template>
  <Teleport v-if="teleport" to="body">
    <Transition name="ivyforms-modal">
      <div
        v-if="isVisible"
        class="ivyforms-modal-overlay"
        :class="{ 'ivyforms-modal-local': !teleport, 'ivyforms-modal-changelog': isChangelog }"
        @click.self="handleOverlayClick"
      >
        <div
          class="ivyforms-modal-container"
          :class="{ 'ivyforms-modal-changelog-container': isChangelog }"
          :style="modalStyles"
          role="dialog"
          aria-modal="true"
          :aria-labelledby="title ? 'ivyforms-modal-title' : undefined"
        >
          <slot />
        </div>
      </div>
    </Transition>
  </Teleport>

  <Transition v-else name="ivyforms-modal">
    <div
      v-if="isVisible"
      class="ivyforms-modal-overlay ivyforms-modal-local"
      :class="{ 'ivyforms-modal-changelog': isChangelog }"
      @click.self="handleOverlayClick"
    >
      <div
        class="ivyforms-modal-container"
        :class="{ 'ivyforms-modal-changelog-container': isChangelog }"
        :style="modalStyles"
        role="dialog"
        aria-modal="true"
        :aria-labelledby="title ? 'ivyforms-modal-title' : undefined"
      >
        <slot />
      </div>
    </div>
  </Transition>
</template>

<script setup lang="ts">
import { computed, onMounted, onUnmounted, watch } from 'vue'

interface Props {
  visible: boolean
  title?: string
  width?: number | string
  maxWidth?: number | string
  showCloseButton?: boolean
  showHeader?: boolean
  closeOnOverlayClick?: boolean
  closeOnEscape?: boolean
  teleport?: boolean
  isChangelog?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  title: '',
  width: '',
  maxWidth: '1300px',
  showCloseButton: true,
  showHeader: true,
  closeOnOverlayClick: true,
  closeOnEscape: true,
  teleport: true,
  isChangelog: false,
})

const emit = defineEmits<{
  'update:visible': [value: boolean]
  close: []
  open: []
}>()

// Computed properties
const isVisible = computed({
  get: () => props.visible,
  set: (value) => emit('update:visible', value),
})

const modalStyles = computed(() => {
  if (props.isChangelog) {
    return {
      width: typeof props.width === 'number' ? `${props.width}px` : props.width || '480px',
      maxWidth: '90vw',
      height: 'auto',
      maxHeight: '85vh',
    }
  }

  return {
    width: typeof props.width === 'number' ? `${props.width}px` : props.width,
    maxWidth: typeof props.maxWidth === 'number' ? `${props.maxWidth}px` : props.maxWidth,
  }
})

// Methods
const handleClose = () => {
  isVisible.value = false
  emit('close')
}

const handleOverlayClick = () => {
  if (props.closeOnOverlayClick) {
    handleClose()
  }
}

const handleEscapeKey = (event: KeyboardEvent) => {
  if (event.key === 'Escape' && props.closeOnEscape && isVisible.value) {
    handleClose()
  }
}

const preventBodyScroll = () => {
  document.body.style.overflow = 'hidden'
}

const restoreBodyScroll = () => {
  document.body.style.overflow = ''
}

// Lifecycle hooks
onMounted(() => {
  document.addEventListener('keydown', handleEscapeKey)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleEscapeKey)
  restoreBodyScroll()
})

// Watch for visibility changes
watch(isVisible, (newValue) => {
  if (newValue) {
    preventBodyScroll()
    emit('open')
  } else {
    restoreBodyScroll()
  }
})
</script>

<style scoped lang="scss">
.ivyforms-modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 9999;
  background: var(--map-overlay);
  display: flex;
  justify-content: center;
  align-items: flex-start;

  &.ivyforms-modal-local {
    @apply absolute;
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    z-index: 100;
    width: 100% !important;
    height: 100% !important;
  }

  &.ivyforms-modal-changelog {
    align-items: center;

    .ivyforms-modal-container {
      margin: 20px auto;
      min-width: auto;
      min-height: auto;
      height: auto;
      overflow: visible;

      @media (max-width: 960px) {
        width: calc(100vw - 2rem) !important;
        max-width: calc(100vw - 2rem) !important;
        margin: 20px auto;
      }

      @media (max-width: 600px) {
        width: calc(100vw - 1rem) !important;
        margin: 10px auto;
      }
    }
  }

  .ivyforms-modal-container {
    width: 100%;
    min-width: 800px;
    height: calc(100vh - 80px);
    max-height: none;
    min-height: 600px;
    padding: 0;
    overflow-y: auto;
    margin: 45px 20px 20px 180px;
    border-radius: var(--Radius-radius-xl, 16px);
    background: var(--map-ground-level-2-foreground);
    box-shadow:
      0 8px 12px 6px rgba(18, 26, 38, 0.15),
      0 4px 4px 0 rgba(18, 26, 38, 0.3);
    box-sizing: border-box;
    position: relative;
    z-index: 10000;

    // Responsive adjustments
    @media (max-width: 1200px) {
      width: calc(100vw - 200px);
      margin-left: 180px;
      min-width: 700px;
      height: calc(100vh - 60px);
      min-height: 500px;
    }

    @media (max-width: 960px) {
      width: calc(100vw - 2rem) !important;
      max-width: calc(100vw - 2rem) !important;
      min-width: auto !important;
      height: calc(100vh - 40px) !important;
      min-height: auto !important;
      margin: 20px auto auto auto;
    }

    @media (max-width: 782px) {
      width: calc(100vw - 76px);
      margin-left: 56px;
      min-width: auto;
      height: calc(100vh - 60px);
      min-height: 400px;
    }

    @media (max-width: 600px) {
      width: calc(100vw - 1rem) !important;
      margin: 10px auto auto auto;
      min-width: auto !important;
      height: calc(100vh - 20px) !important;
      min-height: auto !important;
    }

    &.ivyforms-modal-changelog-container {
      min-width: auto !important;
      min-height: auto !important;
      height: auto !important;
      overflow: visible !important;
      margin: 20px auto !important;

      @media (max-width: 960px) {
        width: calc(100vw - 2rem) !important;
        max-width: calc(100vw - 2rem) !important;
      }

      @media (max-width: 600px) {
        width: calc(100vw - 1rem) !important;
      }
    }
  }
}
// Transitions
.ivyforms-modal-enter-active,
.ivyforms-modal-leave-active {
  transition: opacity 0.3s ease;

  .ivyforms-modal-container {
    transition: transform 0.3s ease;
  }
}

.ivyforms-modal-enter-from,
.ivyforms-modal-leave-to {
  opacity: 0;

  .ivyforms-modal-container {
    transform: scale(0.9) translateY(-20px);
  }
}
.ivyforms-fullscreen-mode {
  .ivyforms-modal-overlay {
    align-items: center;
    padding: 20px;

    .ivyforms-modal-container {
      margin: 0 !important;
      max-width: 1300px;

      @media (max-width: 1200px) {
        width: calc(100% - 40px);
        margin: 0 !important;
      }

      @media (max-width: 960px) {
        width: calc(100vw - 2rem) !important;
        max-width: calc(100vw - 2rem) !important;
        margin: 0 !important;
      }

      @media (max-width: 782px) {
        width: calc(100vw - 2rem);
        margin: 0 !important;
      }

      @media (max-width: 600px) {
        width: calc(100vw - 1rem) !important;
        margin: 0 !important;
      }
    }
  }
}
</style>
