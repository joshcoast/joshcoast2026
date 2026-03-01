<template>
  <div class="ivyforms-subdialog-wrapper">
    <ElDialog
      ref="dialogRef"
      v-model="dialogVisible"
      v-bind="{ ...$attrs, type: actionType }"
      :show-close="false"
      :style="{ bottom: dialogBottom + 'px' }"
      class="ivyforms-subdialog"
      :class="[`type-${actionType}`, `ivyforms-align-${props.align}`, { 'position-top': top }]"
      :width="props.width"
      @open="onOpen()"
    >
      <template #header>
        <div ref="dialogHeader" class="ivyforms-subdialog__header">
          <div class="ivyforms-subdialog__header__drag-handle" />
          <!-- Hide icon for Pro dialogs -->
          <div v-if="dialogData.dialogType !== 'pro'" class="ivyforms-subdialog-header-icon">
            <IvyIcon :name="getIconName()" type="fill-duo" size="l" />
          </div>
          <div v-if="$slots.header" class="ivyforms-subdialog__header__title-wrapper">
            <slot name="header" />
          </div>
          <div
            v-if="dialogData.title && !$slots.header"
            class="ivyforms-subdialog__header__title-wrapper ivyforms-flex ivyforms-flex-direction-column ivyforms-justify-content-center ivyforms-gap-8"
            :class="{
              'ivyforms-subdialog__header__title-wrapper--no-icon': dialogData.dialogType === 'pro',
            }"
          >
            <div
              class="ivyforms-subdialog__header__title ivyforms-flex ivyforms-gap-8 ivyforms-justify-content-between ivyforms-align-items-center"
            >
              <span class="medium-14">{{ dialogData.title }}</span>
              <slot name="titleButton" />
            </div>
            <!-- Show subtitle in header for Pro dialogs, in body for others -->
            <span
              v-if="dialogData.subtitle && dialogData.dialogType === 'pro'"
              class="regular-14 ivyforms-text-secondary"
            >
              {{ dialogData.subtitle }}
            </span>
          </div>
          <div v-if="showClose" class="ivyforms-subdialog__header__close">
            <IvyIcon
              priority="tertiary"
              name="close"
              type="outline"
              size="s"
              category="global"
              color="var(--map-base-dusk-symbol-2)"
              @click="dialogVisible = false"
            />
          </div>
        </div>
      </template>
      <!-- Body shows subtitle only for non-Pro dialogs -->
      <div
        v-if="dialogData.subtitle && dialogData.dialogType !== 'pro'"
        class="ivyforms-subdialog__body"
      >
        {{ dialogData.subtitle }}
      </div>
      <template v-if="dialogData.buttons" #footer>
        <IvyButtonAction
          :priority="dialogData.buttons?.close?.type ? dialogData.buttons.close.type : 'tertiary'"
          size="d"
          type="fill"
          :full-width="true"
          @click="clickClose"
        >
          {{ dialogData.buttons?.close?.text || 'Close' }}
        </IvyButtonAction>
        <IvyButtonAction
          v-if="dialogData.buttons?.confirm"
          :priority="dialogData.buttons?.confirm?.type || 'primary'"
          size="d"
          type="fill"
          :full-width="true"
          @click="clickConfirm"
        >
          {{ dialogData.buttons?.confirm?.text || 'Confirm' }}
        </IvyButtonAction>
      </template>
    </ElDialog>
  </div>
</template>

<script setup lang="ts">
import { useSwipe } from '@vueuse/core'
import { ref } from 'vue'
import IvyButtonAction from '@/views/_components/button/IvyButtonAction.vue'
import { storeToRefs } from 'pinia'
import { useActionEntityStore } from '@/stores/actionEntityStore.ts'
import { useWcagColors } from '@/composables/useWcagColors'

const { startWatching } = useWcagColors()
startWatching()
interface Props {
  title?: string
  subtitle?: string | null
  showClose?: boolean
  width?: string
  type?: 'info' | 'success' | 'warning' | 'error' | 'default' | 'pro' | 'upcoming'
  align?: 'left' | 'center' | 'right'
  top?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  title: '',
  subtitle: '',
  showClose: true,
  width: '',
  type: 'success',
  align: 'center',
  top: false,
})

const { dialogVisible, dialogData, actionType } = storeToRefs(useActionEntityStore())
const { confirmAction } = useActionEntityStore()

const clickClose = () => {
  if (dialogData.value?.buttons?.close.function) {
    dialogData.value?.buttons?.close.function()
    dialogVisible.value = false
  } else {
    dialogVisible.value = false
  }
}

const clickConfirm = () => {
  if (dialogData.value?.buttons?.confirm.function) {
    dialogData.value?.buttons?.confirm.function()
    dialogVisible.value = false
  } else {
    confirmAction()
  }
}

const dialogHeader = ref<HTMLElement | null>(null)
const dialogBottom = ref(0)
const dialogHeight = ref(0)
const dialogRef = ref(null)

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
        dialogVisible.value = false
      } else {
        dialogBottom.value = 0
      }
    },
  })

  dialogRef.value.$el.nextSibling.querySelector('.el-overlay-dialog').scrollTo(0, 0)
}

const calculateDialogHeight = () => {
  const dialogElement = document.querySelector('.ivyforms-subdialog')
  if (dialogElement) {
    dialogHeight.value = dialogElement.clientHeight
  }
}

const getIconName = () => {
  if (actionType.value) {
    switch (actionType.value) {
      case 'duplicate':
        return 'copy'
      case 'unsaved_changes':
      case 'delete':
        return 'danger'
    }
  }
}
</script>
<style lang="scss">
@use 'sass:list' as *;
@use 'sass:meta' as *;
// Dialog
.ivyforms-subdialog {
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
  // Type
  $types: (
    'delete': var(--map-status-error-fill-0),
    'duplicate': var(--map-status-success-fill-0),
    'unsaved_changes': var(--map-status-warning-fill-0),
  );
  &.position-top {
    top: 5%;
    bottom: auto !important;
    position: fixed;
    left: 50%;
    transform: translateX(-50%);
    margin: 0 auto;
  }
  @each $type, $fill in $types {
    &.type-#{$type} {
      .el-dialog__header {
        .ivyforms-subdialog__header {
          .ivyforms-subdialog-header-icon {
            .ivyforms-icon {
              svg {
                fill: $fill;
              }
            }
          }
        }
      }
    }
  }

  $alignments: (
    'center': (
      center,
      center,
      center,
    ),
    'left': (
      flex-start,
      left,
      flex-start,
    ),
    'right': (
      flex-start,
      left,
      flex-end,
    ),
  );

  @each $align, $values in $alignments {
    &.ivyforms-align-#{$align} {
      .el-dialog__header {
        .ivyforms-subdialog__header {
          &__title {
            align-self: nth($values, 1);
            span {
              text-align: nth($values, 2);
            }
          }
          .ivyforms-subdialog-header-icon {
            align-self: nth($values, 1);
          }
        }
      }

      .el-dialog__footer {
        justify-content: nth($values, 3);
      }

      .el-dialog__body {
        .ivyforms-subdialog__body {
          // Tablet and up
          @include tablet-and-up {
            text-align: nth($values, 2);
            padding: 0px;
            horiz-align: nth($values, 2);
          }
        }
      }
    }
  }

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

    &.position-top {
      top: 5%;
      bottom: auto !important;
      position: fixed;
      left: 50%;
      transform: translateX(-50%);
      margin: 0 auto;
    }
    // Element Header
    .el-dialog__header {
      padding: 24px 24px 8px 24px;
      width: 100%;
    }

    .el-dialog__header {
      padding: 0;
    }
    // Default Header
    .ivyforms-subdialog__header {
      width: 100%;
      display: flex;
      justify-content: space-between;
      padding: 16px 16px 8px;
      box-sizing: border-box;
      display: flex;
      flex-direction: column;
      background-color: var(--map-ground-level-2-foreground);

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
          margin-top: 16px;
        }
        span {
          color: var(--map-base-text-0);
        }
      }

      // Title wrapper modifier for no-icon (Pro dialogs)
      &__title-wrapper {
        &--no-icon {
          text-align: center;

          .ivyforms-subdialog__header__title {
            span {
              text-align: center;
            }
          }

          .ivyforms-text-secondary {
            text-align: center;
          }
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
        text-align: center;
        color: var(--map-base-text--1);
      }
    }

    // Element Body
    .el-dialog__body {
      width: 100%;
      height: 100%;
      box-sizing: border-box;
      color: var(--map-base-text--1);
      background-color: var(--map-ground-level-2-foreground);
      overflow: auto;

      // Tablet And Up
      @include tablet-and-up {
        height: initial;
        max-height: unset;
      }
    }

    // Element Footer
    .el-dialog__footer {
      display: flex;
      gap: 12px;
      width: 100%;
      padding: 16px;
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
