<template>
  <div class="ivyforms-dialog-wrapper">
    <ElDialog
      ref="dialogRef"
      v-model="localModelValue"
      v-bind="{ ...$attrs, ...$props }"
      :show-close="false"
      :style="{ bottom: dialogBottom + 'px' }"
      class="ivyforms-dialog"
      :class="[`type-${props.type}`]"
      :width="props.width"
      @open="onOpen()"
    >
      <template #header>
        <div ref="dialogHeader" class="ivyforms-dialog__header">
          <div class="ivyforms-dialog__header__drag-handle" />
          <div v-if="$slots.header" class="ivyforms-dialog__header__title-wrapper">
            <slot name="header" />
          </div>
          <div
            v-if="props.title && !$slots.header"
            class="ivyforms-dialog__header__title-wrapper ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-8"
          >
            <div
              class="ivyforms-dialog__header__title ivyforms-flex ivyforms-gap-8 ivyforms-justify-content-between ivyforms-align-items-center"
            >
              <span class="medium-18">{{ props.title }}</span>
              <slot name="titleButton" />
            </div>
            <span v-if="props.subtitle" class="regular-16 ivyforms-text-secondary">
              {{ props.subtitle }}
            </span>
          </div>
          <div v-if="showClose" class="ivyforms-dialog__header__close">
            <IvyIcon
              priority="tertiary"
              name="close"
              type="outline"
              size="s"
              category="global"
              color="var(--map-base-dusk-symbol-2)"
              @click="localModelValue = false"
            />
          </div>
        </div>
      </template>
      <div v-if="!!$slots.default" class="ivyforms-dialog__body">
        <slot />
      </div>
      <template v-if="$slots.footer" #footer>
        <slot name="footer" />
      </template>
    </ElDialog>
  </div>
</template>

<script setup lang="ts">
import { useSwipe } from '@vueuse/core'
import { computed, ref } from 'vue'
interface Props {
  modelValue: boolean
  title?: string
  subtitle?: string | null
  showClose?: boolean
  width?: string
  type?: 'fill' | 'r-alinged' | 'option'
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: false,
  title: '',
  subtitle: '',
  showClose: true,
  width: '500',
  type: 'fill',
})

const emit = defineEmits(['update:modelValue'])

const dialogHeader = ref<HTMLElement | null>(null)
const dialogBottom = ref(0)
const dialogHeight = ref(0)
const dialogRef = ref(null)

const localModelValue = computed({
  get() {
    return props.modelValue
  },
  set(value) {
    emit('update:modelValue', value)
  },
})

const onOpen = () => {
  dialogBottom.value = 0
  calculateDialogHeight()

  const { direction, lengthY } = useSwipe(dialogHeader, {
    threshold: 0,
    onSwipe() {
      if (direction.value === 'down') {
        dialogBottom.value = Math.min(lengthY.value, window.innerHeight)
      } else if (direction.value === 'up') {
        dialogBottom.value = Math.max(dialogBottom.value - lengthY.value, 0)
      }
    },
    onSwipeEnd() {
      if (direction.value === 'down' && Math.abs(lengthY.value) > dialogHeight.value / 2) {
        localModelValue.value = false
      } else {
        dialogBottom.value = 0
      }
    },
  })

  dialogRef.value.$el.nextSibling.querySelector('.el-overlay-dialog').scrollTo(0, 0)
}

const calculateDialogHeight = () => {
  const dialogElement = document.querySelector('.ivyforms-dialog')
  if (dialogElement) {
    dialogHeight.value = dialogElement.clientHeight
  }
}
</script>

<style lang="scss">
@use 'sass:list' as *;
@use 'sass:meta' as *;
// Dialog
.ivyforms-dialog {
  display: flex;
  max-width: 100%;
  padding: 0;
  flex-direction: column;
  align-items: flex-start;
  border-radius: 16px;
  background-color: var(--map-ground-level-2-foreground);
  margin: 0 auto;
  max-height: calc(100% - 78px);
  box-shadow: var(--shadow-500);
  position: absolute;
  bottom: 0;
  overflow: hidden;

  // Tablet And Up
  @include tablet-and-up {
    max-height: unset;
    border-radius: 12px;
    position: relative;
    min-width: unset;
  }

  // Element Dialog
  &.el-dialog {
    border-radius: 16px;
    background-color: var(--map-ground-level-2-foreground);
    padding: 0;
    // Element Header
    .el-dialog__header {
      padding: 24px 24px 8px 24px;
      width: 100%;
    }

    .el-dialog__header {
      padding: 0;
    }
    // Default Header
    .ivyforms-dialog__header {
      width: 100%;
      display: flex;
      justify-content: space-between;
      padding: 24px 24px 8px 24px;
      box-sizing: border-box;
      flex-direction: column;
      background-color: var(--map-ground-level-2-foreground);
      // Tablet And Up
      @include tablet-and-up {
        padding: 24px 24px 8px 24px;
      }

      // Drag Handle
      &__drag-handle {
        width: 32px;
        height: 4px;
        border-radius: 100px;
        opacity: 0.4;
        background: var(--map-base-dusk-fill-0);
        margin: 16px auto 16px;

        // Tablet And Up
        @include tablet-and-up {
          display: none;
        }
      }

      // Title
      &__title {
        // Tablet And Up
        @include tablet-and-up {
          margin-right: 16px;
        }
        span {
          color: var(--map-base-text-0);
        }
      }

      // Close
      &__close {
        display: none;
        position: absolute;
        top: 8px;
        right: 10px;
        cursor: pointer;

        // Tablet And Up
        @include tablet-and-up {
          display: flex;
        }
      }
      .ivyforms-text-secondary {
        color: var(--map-base-text--1);
      }
    }

    // Element Body
    .el-dialog__body {
      display: flex !important;
      width: 100%;
      height: 100%;
      box-sizing: border-box;
      color: var(--map-base-text-0);
      background-color: var(--map-ground-level-2-foreground);
      overflow: auto;

      // Tablet And Up
      @include tablet-and-up {
        height: initial;
        max-height: unset;
      }

      // Body
      .ivyforms-dialog__body {
        width: 100%;
        // Tablet And Up
        @include tablet-and-up {
          padding: 24px;
        }

        .ivyforms-checkbox {
          margin-top: 8px;
        }
      }
    }

    // Element Footer
    .el-dialog__footer {
      display: flex;
      justify-content: flex-end;
      gap: 12px;
      width: 100%;
      padding: 12px 24px 24px 24px;
      border-top: 1px solid var(--map-base-dusk-stroke--2);
      background-color: var(--map-ground-level-2-foreground);
      margin-top: auto;
      position: sticky;
      bottom: 0;
      z-index: 2;

      // Button
      button {
        flex: 1;

        // Tablet And Up
        @include tablet-and-up {
          flex: 0 1 auto;
        }
      }
    }
  }
}

// Overlay
.el-overlay {
  overflow: hidden;
  background-color: var(--map-overlay);

  // Tablet And Up
  @include tablet-and-up {
    overflow: auto;
  }
}
</style>
