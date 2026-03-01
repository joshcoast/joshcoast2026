<template>
  <div
    class="ivyforms-form-builder ivyforms-flex ivyforms-flex-1 ivyforms-flex-direction-column ivyforms-pr-20 ivyforms-pt-20"
    style="max-width: 100%; box-sizing: border-box"
    @click="deselectField"
  >
    <!-- Loading skeleton -->
    <FormBuilderSkeleton v-if="formBuilderStore.isFormLoading" />

    <!-- Form content -->
    <IvyForm v-else>
      <div class="ivyforms-form-builder__drop-area ivyforms-form-builder__grid-container">
        <IvyDraggable>
          <!-- Empty state slot -->
          <template #empty>
            <PageEmptyState :title="getEmptyStateTitle()" image="form-builder" />
          </template>

          <!-- Row actions slot -->
          <template #row-actions="{ row, handlers }">
            <div
              class="ivyforms-form-builder__row-actions-wrapper ivyforms-form-builder__row-drag-handle"
              draggable="true"
              @dragstart="handlers.onDragStart($event, row)"
              @dragend="handlers.onDragEnd"
            >
              <IvyButtonAction
                class="ivyforms-form-builder__row-actions-wrapper-button"
                size="s"
                priority="white"
                type="ghost"
                icon-only
                icon-start="move"
                icon-start-type="outline"
                icon-start-category="builder"
              />
            </div>
            <div v-if="row.rowIndex > 0" class="ivyforms-form-builder__row-actions-wrapper">
              <IvyButtonAction
                class="ivyforms-form-builder__row-actions-wrapper-button"
                size="s"
                priority="white"
                type="ghost"
                icon-only
                icon-start="arrow-up"
                icon-start-type="outline"
                icon-start-category="arrows"
                @click.stop="handlers.moveUp(row)"
              />
            </div>
            <div
              v-if="handlers.canMoveDown(row)"
              class="ivyforms-form-builder__row-actions-wrapper"
            >
              <IvyButtonAction
                class="ivyforms-form-builder__row-actions-wrapper-button"
                size="s"
                priority="white"
                type="ghost"
                icon-only
                icon-start="arrow-down"
                icon-start-type="outline"
                icon-start-category="arrows"
                @click.stop="handlers.moveDown(row)"
              />
            </div>
            <div class="ivyforms-form-builder__row-actions-wrapper">
              <IvyButtonAction
                class="ivyforms-form-builder__row-actions-wrapper-button"
                size="s"
                priority="white"
                type="ghost"
                icon-only
                icon-start="copy"
                icon-start-type="outline"
                icon-start-category="global"
                @click.stop="handlers.duplicate(row)"
              />
            </div>
            <div class="ivyforms-form-builder__row-actions-wrapper">
              <IvyButtonAction
                class="ivyforms-form-builder__row-actions-wrapper-button ivyforms-form-builder__row-actions-wrapper-button--delete"
                size="s"
                priority="white"
                type="ghost"
                icon-only
                icon-start="trash"
                icon-start-type="outline"
                icon-start-category="global"
                @click="handlers.delete(row)"
              />
            </div>
          </template>

          <!-- Field slot -->
          <template #field="{ field, row, fieldHandlers }">
            <IvyFieldUnit
              :id="getFieldUnitId(field)"
              variant="default"
              :type="field.type"
              :field-index="field.fieldIndex"
              @select-field="selectField"
              @dragstart="(e) => fieldHandlers.onDragStart(e, field, row)"
              @dragend="fieldHandlers.onDragEnd"
            />
          </template>
        </IvyDraggable>
      </div>

      <!-- Submit button outside draggable -->
      <div
        v-if="formBuilderStore.fields.length !== 0"
        :class="[
          'ivyforms-form-builder__submit-button-wrapper',
          'ivyforms-flex',
          'ivyforms-p-12',
          'ivyforms-mx-12',
          'ivyforms-mb-12',
          'ivyforms-mt-40',
          submitButtonJustifyClass,
          `ivyforms-form-builder__submit-button-wrapper--${formBuilderStore.submitButtonSettings.position}`,
          {
            'ivyforms-form-builder__submit-button-wrapper--selected':
              formBuilderStore.isSubmitButtonSelected,
            'ivyforms-form-builder__submit-button-wrapper--hovered': isSubmitButtonHovered,
          },
        ]"
        @mouseover="isSubmitButtonHovered = true"
        @mouseleave="isSubmitButtonHovered = false"
        @click="formBuilderStore.selectSubmitButton"
      >
        <div
          v-show="isSubmitButtonHovered || formBuilderStore.isSubmitButtonSelected"
          class="ivyforms-form-builder__submit-button-actions ivyforms-flex ivyforms-gap-4"
        >
          <IvyButtonAction
            size="s"
            priority="white"
            type="ghost"
            icon-only
            icon-start="settings"
            icon-start-type="outline"
            icon-start-category="global"
            @click.stop="formBuilderStore.selectSubmitButton"
          />
        </div>
        <div class="ivyforms-form-builder__submit-button">
          <IvyButtonAction priority="tertiary" type="border" readonly @click.prevent>
            {{ formBuilderStore.submitButtonSettings.label }}
          </IvyButtonAction>
        </div>
      </div>
    </IvyForm>
    <IvyContextMenu :width="140" :offset="2" />
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import IvyFieldUnit from '@/views/admin/form-builder/fields/field-unit/IvyFieldUnit.vue'
import IvyDraggable from './IvyDraggable.vue'
import PageEmptyState from '@/views/admin/parts/PageEmptyState.vue'
import FormBuilderSkeleton from './FormBuilderSkeleton.vue'
import { useFormBuilder } from '@/stores/useFormBuilder.ts'
import { useRoute } from 'vue-router'
import { useLabels } from '@/composables/useLabels'
import { useWcagColors } from '@/composables/useWcagColors'
import type { Field } from '@/types/field'
import IvyButtonAction from '@/views/_components/button/IvyButtonAction.vue'
import IvyContextMenu from '@/views/_components/context-menu/IvyContextMenu.vue'

const { getLabel } = useLabels()
const { startWatching } = useWcagColors()
startWatching()
const route = useRoute()

const formBuilderStore = useFormBuilder()

// Submit button hover state
const isSubmitButtonHovered = ref(false)

// Map submit button position to justify-content utility class
const submitButtonJustifyClass = computed(() => {
  const position = formBuilderStore.submitButtonSettings.position
  const justifyMap: Record<string, string> = {
    default: 'ivyforms-justify-content-start',
    left: 'ivyforms-justify-content-start',
    center: 'ivyforms-justify-content-center',
    right: 'ivyforms-justify-content-end',
  }
  return justifyMap[position] || 'ivyforms-justify-content-start'
})

// Load form on mount
const getEmptyStateTitle = () => getLabel('empty_state_title')

onMounted(async () => {
  if (route.params.id) {
    formBuilderStore.formId = route.params.id as string
    if (!formBuilderStore.fields.length) {
      formBuilderStore.counterFields = 0
    }
  }
})

const getFieldUnitId = (element: Field) => {
  return 'ivyforms-field-unit_' + formBuilderStore.formId + '_' + element.fieldIndex
}

const selectField = (field: number) => {
  formBuilderStore.selectField(field)
}

const deselectField = (event: Event) => {
  formBuilderStore.deselectField(event)
}
</script>

<style scoped lang="scss">
.ivyforms-form-builder {
  &__grid-container {
    background: var(--map-ground-level-1-foreground);
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
    position: relative;
  }

  &__rows-container {
    width: 100%;
    display: flex;
    flex-direction: column;
  }

  &__rows-wrapper {
    display: flex;
    flex-direction: column;
    gap: 8px;
    width: 100%;
  }

  &__rows-container {
    width: 100%;
  }

  &__row-wrapper {
    width: 100%;
    padding: 1px;
    position: relative;

    &--has-actions {
      border-radius: var(--Radius-radius-md, 8px);
      &.ivyforms-form-builder__row-wrapper--selected,
      &.ivyforms-form-builder__row-wrapper--hovered {
        border: 1px dashed var(--map-base-purple-stroke-0) !important;
        padding: 0;
        z-index: 101;
      }
    }
  }

  &__row-drop-zone {
    width: 100%;
    height: 4px;
    position: relative;
    background: transparent;
    transition:
      background 0.15s ease,
      box-shadow 0.15s ease;
    z-index: 100;
    margin: 10px 0;
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: none;

    &.is-dragging {
      pointer-events: auto;
    }

    &.is-active {
      pointer-events: auto;
      background: var(--map-base-purple-stroke-0);
      box-shadow: 0 0 8px rgba(99, 102, 241, 0.4);
      animation: pulse 1.5s ease-in-out infinite;
    }
  }

  @keyframes pulse {
    0%,
    100% {
      opacity: 1;
    }
    50% {
      opacity: 0.6;
    }
  }

  &__row {
    display: flex;
    flex-wrap: nowrap;
    gap: 8px;
    width: 100%;
    position: relative;
    min-height: 60px;
    border-radius: 4px;
    padding: 2px 8px 8px;
    box-sizing: border-box;
    transition:
      border-color 0.2s ease,
      background-color 0.2s ease;

    &--ghost {
      opacity: 0.4;
      background: #f0f0f0;
      border-color: #ddd;
    }

    &--chosen {
      border-color: var(--map-base-purple-stroke-0);
      background: rgba(99, 102, 241, 0.05);
    }

    &--drag {
      opacity: 0.8;
      cursor: grabbing !important;
    }
  }

  &__row-wrapper {
    position: relative;

    &--dragging {
      opacity: 0.5;
      pointer-events: none;
    }
  }

  &__row-actions {
    position: absolute;
    right: 12px;
    top: -20px;
    display: flex;
    gap: 4px;
    z-index: 1001;
    border-radius: 8px;
    border: 1px dashed var(--map-base-purple-stroke-0);
    background: var(--map-ground-level-1-foreground);
    pointer-events: auto;
  }

  &__row-actions-wrapper {
    position: relative;
    z-index: 1;
  }

  &__row-actions-wrapper-button {
    cursor: pointer;
    border-radius: 4px;
    transition: background-color 0.2s ease;

    :deep(button) {
      background: none;
      border: none;
      cursor: pointer;
      transition: background-color 0.2s ease;

      .ivyforms-icon__svg.name-move {
        svg {
          path {
            fill: var(--map-base-purple-symbol-0) !important;
            stroke: var(--map-base-purple-symbol-0) !important;
          }
        }
      }

      svg {
        fill: var(--map-base-purple-symbol-0) !important;
        stroke: var(--map-base-purple-symbol-0) !important;

        path {
          fill: var(--map-base-purple-symbol-0) !important;
          //stroke: var(--map-base-purple-symbol-0) !important;
        }
      }

      &:hover {
        background: var(--map-base-purple-o05);
      }
    }
  }

  &__row-drag-handle {
    cursor: grab;
    user-select: none;
    -webkit-user-drag: element;
    position: relative;
    z-index: 1002;
    pointer-events: auto;

    .ivyforms-button-wrapper {
      cursor: grab;
      user-select: none;
      -webkit-user-drag: element;
      pointer-events: auto;
    }

    :deep(button) {
      cursor: grab !important;
      pointer-events: none;

      &:active {
        cursor: grabbing !important;
      }
    }

    svg {
      fill: var(--map-base-purple-symbol-0);
      stroke: var(--map-base-purple-symbol-0);
      pointer-events: none;
      cursor: grab;

      path {
        fill: var(--map-base-purple-symbol-0);
        stroke: var(--map-base-purple-symbol-0);
      }
    }

    &:hover {
      cursor: grab;

      :deep(button) {
        background: var(--map-base-purple-o05);
      }

      svg {
        fill: var(--map-base-purple-symbol-0);
        stroke: var(--map-base-purple-symbol-0);
      }
    }

    &:active {
      cursor: grabbing;
    }
  }

  &__row-fields {
    display: flex;
    flex-wrap: nowrap;
    gap: 8px;
    width: 100%;
    flex: 1;
    min-height: 52px;
  }

  &__item-wrapper {
    position: relative;
    flex-shrink: 1;
    flex-grow: 0;
    box-sizing: border-box;
    min-width: 0;
    max-width: 100%;
  }

  &__field-drop-indicator {
    position: absolute;
    top: 0;
    bottom: 0;
    width: 4px;
    background: var(--map-base-purple-stroke-0);
    border-radius: 2px;
    z-index: 1000;
    box-shadow: 0 0 8px rgba(99, 102, 241, 0.5);
    animation: pulse 1s ease-in-out infinite;

    &--left {
      left: -6px;
    }

    &--right {
      right: -6px;
    }
  }

  &__field-container {
    flex-shrink: 1;
    flex-grow: 0;
    box-sizing: border-box;
    position: relative;
    border-radius: 4px;
    z-index: 1;
    min-width: 0;
  }

  &__field-wrapper {
    position: relative;
    cursor: default; /* Changed from move since dragging is now handle-based */
    min-height: 60px;
    width: 100%;
    height: 100%;
    transition:
      opacity 0.2s ease,
      box-shadow 0.2s ease,
      transform 0.2s ease;

    &.drag-class {
      opacity: 0.5;
    }

    &--ghost {
      display: none !important;
      visibility: hidden !important;
    }

    &--fallback {
      display: none !important;
      visibility: hidden !important;
    }

    &--chosen {
      opacity: 0.9 !important;
      box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.3);
      z-index: 1000;
    }

    &--drag {
      display: none !important;
      visibility: hidden !important;
    }
  }

  // VueDraggable ghost element styling - HIDE COMPLETELY
  :deep(.sortable-ghost) {
    display: none !important;
    visibility: hidden !important;
  }

  :deep(.sortable-chosen) {
    opacity: 0.9;
    box-shadow: 0 0 0 2px var(--map-base-purple-stroke-0);
  }

  // Drop placeholder - shown where item will be inserted
  :deep(.sortable-drag) {
    opacity: 1;
    cursor: grabbing !important;
  }

  .drop-indicator {
    background-color: var(--map-base-purple-stroke-0);
    z-index: 1000;
    pointer-events: none;

    &-left {
      position: absolute;
      left: -7px;
      top: -2px;
      bottom: -2px;
      width: 5px;
      display: block;
      border-radius: 2px;
    }

    &-right {
      position: absolute;
      right: -7px;
      top: -2px;
      bottom: -2px;
      width: 5px;
      display: block;
      border-radius: 2px;
    }

    // Row indicator line
    &-row {
      width: 100%;
      height: 3px;
      background-color: var(--map-base-purple-stroke-0);
    }
  }

  // Row indicator wrapper - positioned absolutely, doesn't affect layout
  .drop-indicator-row-wrapper {
    position: absolute;
    left: 0;
    height: 5px;
    z-index: 1000;
    pointer-events: none;

    &.drop-indicator-row-above {
      top: -7px;
    }

    &.drop-indicator-row-below {
      bottom: -7px;
    }

    // Row indicator line
    .drop-indicator-row {
      width: 100%;
      height: 5px;
      background-color: var(--map-base-purple-stroke-0);
      border-radius: 2px;
    }
  }

  &__submit-button-wrapper {
    position: relative;
    border-radius: 8px;
    border-right: 2px solid transparent; // Transparent by default to prevent shifting
    cursor: pointer;
    transition: all 0.2s ease;

    &--hovered {
      background: var(--map-base-dusk-o05);
      border-radius: 8px;
      border-right: 2px solid var(--map-base-purple-stroke-0);
    }

    &--selected {
      border-radius: 8px;
      border-right: 2px solid var(--map-base-purple-stroke-0);
      background: var(--map-base-dusk-o05);
    }
  }

  &__submit-button {
    position: relative;
    pointer-events: none;
  }

  &__submit-button-actions {
    position: absolute;
    right: 12px;
    top: -20px;
    cursor: default !important;
    border-radius: 8px;
    background:
      linear-gradient(0deg, var(--map-base-purple-o10) 0%, var(--map-base-purple-o10) 100%),
      var(--map-ground-level-1-foreground);

    :deep(button) {
      background: none;
      border: none;
      cursor: pointer;

      svg {
        fill: var(--map-base-purple-symbol-0) !important;
        stroke: var(--map-base-purple-symbol-0);

        path {
          fill: var(--map-base-purple-symbol-0);
          stroke: var(--map-base-purple-symbol-0);
        }

        &:hover {
          fill: var(--map-base-purple-symbol-0) !important;
          stroke: var(--map-base-purple-symbol-0);
        }
      }

      &:hover {
        background: var(--map-base-purple-o05);
      }
    }
  }

  // Field label truncation - only in form builder to prevent breaking row heights
  .ivyforms-form-builder__field-wrapper {
    :deep(.ivyforms-form-item) {
      // Override Element UI's fit-content width
      .el-form-item--label-top .el-form-item__label {
        width: 100% !important;
      }

      .ivyforms-form-item__left-label,
      .ivyforms-form-item__right-label {
        min-width: 0 !important;
        overflow: hidden !important;
        width: 100% !important;

        .ivyforms-form-item__label {
          overflow: hidden !important;
          white-space: nowrap !important;
          text-overflow: ellipsis !important;
          min-width: 0 !important;
          flex: 1 1 auto !important;
          max-width: 100% !important;
          width: 100% !important;

          span {
            overflow: hidden !important;
            white-space: nowrap !important;
            text-overflow: ellipsis !important;
            display: block !important;
            max-width: 100% !important;
          }
        }
      }
    }
  }
}
</style>
