<template>
  <div class="ivyforms-draggable ivyforms-width-100">
    <!-- Empty state - shown when no fields exist -->
    <div
      v-if="fieldRows.length === 0"
      class="ivyforms-draggable__empty ivyforms-form-builder__item__wrapper__empty ivyforms-flex ivyforms-align-items-center ivyforms-width-100 ivyforms-justify-content-center"
      @dragover.prevent="onDragOverEmpty"
      @dragleave.prevent="onDragLeaveEmpty"
      @drop.prevent="onDropEmpty"
    >
      <slot name="empty" />
    </div>

    <!-- Rows - shown when fields exist -->
    <div
      v-else
      class="ivyforms-draggable__rows ivyforms-form-builder__rows-container ivyforms-width-100 ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-18"
      @dragover.prevent="onDragOverRowsContainer"
      @drop="onDropInRowsContainer"
    >
      <template v-for="(row, rowArrayIndex) in fieldRows" :key="row.rowIndex">
        <!-- Drop zone above first row -->
        <div
          v-if="rowArrayIndex === 0"
          class="ivyforms-draggable__row-drop-zone ivyforms-form-builder__row-drop-zone ivyforms-width-100 ivyforms-m-0 ivyforms-flex ivyforms-justify-content-center ivyforms-align-items-center"
          :class="{ 'is-dragging': isDragging, 'is-active': dropZoneRowIndex === -1 }"
        />

        <div
          class="ivyforms-draggable__row-wrapper ivyforms-form-builder__row-wrapper ivyforms-width-100 ivyforms-p-1"
          :class="{
            'ivyforms-draggable__row-wrapper--has-actions': row.fields.length > 1,
            'ivyforms-draggable__row-wrapper--hovered': hoveredRowIndex === row.rowIndex,
            'ivyforms-draggable__row-wrapper--selected': isRowSelected(row),
            'ivyforms-draggable__row-wrapper--dragging':
              draggedRow && draggedRow.rowIndex === row.rowIndex,
          }"
          :data-row-index="row.rowIndex"
          @dragover.prevent="onDragOverRow($event)"
          @drop="onDropInRow($event)"
          @mouseenter="!resizingField && (hoveredRowIndex = row.rowIndex)"
          @mouseleave="!resizingField && (hoveredRowIndex = null)"
        >
          <!-- Row actions (visible only for rows with multiple fields) -->
          <div
            v-if="row.fields.length > 1"
            v-show="!resizingField && (hoveredRowIndex === row.rowIndex || isRowSelected(row))"
            class="ivyforms-draggable__row-actions ivyforms-form-builder__row-actions ivyforms-flex ivyforms-gap-4"
            @mouseenter="hoveredRowIndex = row.rowIndex"
          >
            <slot name="row-actions" :row="row" :handlers="rowHandlers" />
          </div>

          <div
            class="ivyforms-draggable__row ivyforms-form-builder__row ivyforms-flex ivyforms-gap-4 ivyforms-width-100"
          >
            <!-- Native drag/drop for fields within a row -->
            <div
              class="ivyforms-draggable__row-fields ivyforms-form-builder__row-fields ivyforms-flex ivyforms-gap-12 ivyforms-width-100"
              @drop="onDropInRow($event)"
            >
              <template v-for="field in row.fields" :key="field.fieldIndex">
                <div
                  class="ivyforms-draggable__item-wrapper ivyforms-form-builder__item-wrapper ivyforms-flex ivyforms-flex-direction-column"
                  :class="{
                    'ivyforms-draggable__item-wrapper--dragging':
                      draggedField && draggedField.fieldIndex === field.fieldIndex,
                    'ivyforms-draggable__item-wrapper--resizable':
                      !isDragging && shouldShowResizeHandles(field),
                    'ivyforms-draggable__item-wrapper--resizing': isFieldResizing(field),
                  }"
                  :data-field-index="field.fieldIndex"
                  :style="getFieldStyleInRow(field, row.fields)"
                  @dragover.prevent.stop="onDragOverField($event, field, row.fields)"
                  @dragleave="onDragLeaveField"
                  @drop.prevent.stop="onDropOnField($event, field, row)"
                  @mouseenter="setHoveredFieldForResize(field)"
                  @mouseleave="setHoveredFieldForResize(null)"
                >
                  <!-- Left drop indicator - only show on first field -->
                  <div
                    v-if="
                      !isDraggingRow &&
                      row.fields.length < 5 &&
                      field.columnIndex === 0 &&
                      dropIndicator &&
                      dropIndicator.fieldIndex === field.fieldIndex &&
                      dropIndicator.position === 'left'
                    "
                    class="ivyforms-draggable__field-drop-indicator ivyforms-form-builder__field-drop-indicator ivyforms-form-builder__field-drop-indicator--left"
                  />

                  <div
                    class="ivyforms-draggable__field-container ivyforms-form-builder__field-container ivyforms-flex-1"
                  >
                    <!-- Width indicator - show when resizing any field in the row -->
                    <FieldWidthIndicator
                      v-if="isAnyFieldInRowResizing(row)"
                      :width="field.width ?? 100"
                    />

                    <div
                      class="ivyforms-draggable__field-wrapper ivyforms-form-builder__field-wrapper ivyforms-width-100"
                    >
                      <slot
                        name="field"
                        :field="field"
                        :row="row"
                        :field-handlers="fieldHandlers"
                      />
                    </div>

                    <!-- Right resize handle -->
                    <FieldResizeHandle
                      v-if="!isDragging && shouldShowResizeHandles(field) && canResizeRight(field)"
                      :field="field"
                      direction="right"
                    />
                  </div>

                  <!-- Right drop indicator - show on all fields -->
                  <div
                    v-if="
                      !isDraggingRow &&
                      row.fields.length < 5 &&
                      dropIndicator &&
                      dropIndicator.fieldIndex === field.fieldIndex &&
                      dropIndicator.position === 'right'
                    "
                    class="ivyforms-draggable__field-drop-indicator ivyforms-form-builder__field-drop-indicator ivyforms-form-builder__field-drop-indicator--right"
                  />
                </div>
              </template>
            </div>
          </div>
        </div>

        <!-- Drop zone below this row -->
        <div
          class="ivyforms-draggable__row-drop-zone ivyforms-form-builder__row-drop-zone"
          :class="{ 'is-dragging': isDragging, 'is-active': dropZoneRowIndex === row.rowIndex }"
        />
      </template>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useFormBuilderDraggable } from '@/composables/useFormBuilderDraggable'
import { useFieldResize } from '@/composables/useFieldResize'
import type { Field } from '@/types/field'
import FieldResizeHandle from '@/components/form-builder/FieldResizeHandle.vue'
import FieldWidthIndicator from '@/components/form-builder/FieldWidthIndicator.vue'

const {
  isDragging,
  isDraggingRow,
  draggedRow,
  draggedField,
  dropIndicator,
  dropZoneRowIndex,
  hoveredRowIndex,
  fieldRows,
  onRowDragStart,
  onRowDragEnd,
  onFieldDragStart,
  onFieldDragEnd,
  onDragOverRow,
  onDragOverField,
  onDragLeaveField,
  onDropInRow,
  onDropOnField,
  onDragOverRowsContainer,
  onDropInRowsContainer,
  onDragOverEmpty,
  onDragLeaveEmpty,
  onDropEmpty,
  isRowSelected,
  moveRowUp,
  moveRowDown,
  duplicateRow,
  deleteRow,
  canMoveRowUp,
  canMoveRowDown,
  getFieldStyleInRow,
} = useFormBuilderDraggable()

const { setHoveredField, shouldShowResizeHandles, resizingField } = useFieldResize()

const setHoveredFieldForResize = (field: Field | null) => {
  // Don't update hovered field if we're currently resizing
  if (resizingField.value) return
  setHoveredField(field)
}

const isFieldResizing = (field: Field): boolean => {
  if (!resizingField.value) return false

  // Show indicator for the field being resized
  if (resizingField.value.fieldIndex === field.fieldIndex) return true

  // Also show for adjacent field if resizing between two fields
  const rowFields = fieldRows.value.find((r) => r.rowIndex === field.rowIndex)?.fields ?? []
  const resizingFieldIndex = rowFields.findIndex(
    (f) => f.fieldIndex === resizingField.value!.fieldIndex,
  )
  const currentFieldIndex = rowFields.findIndex((f) => f.fieldIndex === field.fieldIndex)

  // Show on right field when resizing
  return resizingFieldIndex >= 0 && currentFieldIndex === resizingFieldIndex + 1
}

const isAnyFieldInRowResizing = (row: { rowIndex: number; fields: Field[] }): boolean => {
  return (
    resizingField.value !== null &&
    row.fields.some((f) => f.fieldIndex === resizingField.value!.fieldIndex)
  )
}

const canResizeRight = (field: Field): boolean => {
  const rowFields = fieldRows.value.find((r) => r.rowIndex === field.rowIndex)?.fields ?? []

  // Show right handle for all fields in rows with multiple fields
  // For single field: can shrink to create empty space
  // For multiple fields: always show handle (even for last field)
  return rowFields.length > 1 || rowFields.length === 1
}

// Expose handlers for slots
const rowHandlers = computed(() => ({
  onDragStart: onRowDragStart,
  onDragEnd: onRowDragEnd,
  moveUp: moveRowUp,
  moveDown: moveRowDown,
  duplicate: duplicateRow,
  delete: deleteRow,
  canMoveUp: canMoveRowUp,
  canMoveDown: canMoveRowDown,
}))

const fieldHandlers = computed(() => ({
  onDragStart: onFieldDragStart,
  onDragEnd: onFieldDragEnd,
}))
</script>

<style scoped lang="scss">
.ivyforms-draggable {
  &__row-wrapper {
    position: relative;

    &--has-actions {
      border-radius: var(--Radius-radius-md, 8px) 0 var(--Radius-radius-md, 8px)
        var(--Radius-radius-md, 8px);
      border: 1px dashed transparent;
      &.ivyforms-draggable__row-wrapper--selected {
        border: 1px dashed var(--map-base-purple-stroke-0) !important;

        .ivyforms-draggable__row-actions {
          border: 1px dashed var(--map-base-purple-stroke-0) !important;
        }
      }

      &.ivyforms-draggable__row-wrapper--hovered {
        border: 1px dashed var(--map-base-dusk-stroke-0);
      }
    }

    &--dragging {
      opacity: 0.5;
    }
  }

  &__row-drop-zone {
    height: 4px;
    position: relative;
    background: transparent;
    transition:
      background 0.15s ease,
      box-shadow 0.15s ease;
    z-index: 100;
    pointer-events: none;

    &.is-dragging {
      pointer-events: auto;
    }

    &.is-active {
      pointer-events: auto;

      &::after {
        content: '';
        width: 100%;
        height: 4px;
        background: var(--map-base-purple-stroke-0);
        box-shadow: 0 0 8px rgba(99, 102, 241, 0.4);
        animation: pulse 1.5s ease-in-out infinite;
        display: block;
      }
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
    flex-wrap: nowrap;
    position: relative;
    min-height: 60px;
    border-radius: 4px;
    padding: 16px 6px; // Equal 6px on left/right to match half of 12px gap
    box-sizing: border-box;
  }

  &__row-actions {
    position: absolute;
    right: -1px;
    top: -35px;
    z-index: 350;
    border-radius: 8px 8px 0 0;
    border: 1px dashed var(--map-base-dusk-stroke-0);
    background: var(--map-ground-level-1-foreground);
    pointer-events: auto;
  }

  &__row-fields {
    flex-wrap: nowrap;
  }

  &__item-wrapper {
    position: relative;
    box-sizing: border-box;

    &--dragging {
      opacity: 0.5;
    }

    &--resizable {
      .ivyforms-draggable__field-container {
        &:hover {
          .field-resize-handle {
            opacity: 1 !important;
          }
        }
      }
    }

    &--resizing {
      .field-resize-handle {
        opacity: 1 !important;
      }
    }
  }

  &__field-container {
    position: relative;
    min-width: 0;
    overflow: visible;
  }

  &__field-wrapper {
    position: relative;
    cursor: default;
    min-height: 60px;
  }

  &__field-drop-indicator {
    position: absolute;
    top: 0;
    bottom: 0;
    width: 4px;
    background: var(--map-base-purple-stroke-0);
    z-index: 1000;
    pointer-events: none;
    box-shadow: 0 0 8px rgba(99, 102, 241, 0.6);
    animation: pulse 1.5s ease-in-out infinite;

    &--left {
      left: -6px; // Half of 12px gap to center
    }

    &--right {
      right: -12px; // Centered in 12px gap between fields
    }
  }
}
</style>

<style lang="scss">
// Non-scoped styles for ivyforms-form-builder__ classes used by composable
.ivyforms-form-builder {
  &__item__wrapper__empty {
    width: 100%;
    margin: 8px 8px 20px 8px;
    border-radius: 8px;
    border: 2px dashed var(--map-base-dusk-stroke--2);
    position: relative;
    transition: border-color 0.2s ease;

    &.dragging-over {
      border-color: var(--map-base-purple-stroke-0);
      background-color: rgba(99, 102, 241, 0.05);

      &[data-drop-position='top']::before {
        content: '';
        position: absolute;
        top: 12px;
        left: 12px;
        right: 12px;
        height: 3px;
        background-color: var(--map-base-purple-stroke-0);
      }

      &[data-drop-position='bottom']::before {
        content: '';
        position: absolute;
        bottom: 12px;
        left: 12px;
        right: 12px;
        height: 3px;
        background-color: var(--map-base-purple-stroke-0);
      }

      &[data-drop-position='left']::before {
        content: '';
        position: absolute;
        left: 12px;
        top: 12px;
        bottom: 12px;
        width: 3px;
        background-color: var(--map-base-purple-stroke-0);
      }

      &[data-drop-position='right']::before {
        content: '';
        position: absolute;
        right: 12px;
        top: 12px;
        bottom: 12px;
        width: 3px;
        background-color: var(--map-base-purple-stroke-0);
      }
    }
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
    animation: fieldDropPulse 1s ease-in-out infinite;
    pointer-events: none;

    &--left {
      left: -6px;
    }

    &--right {
      right: -8px;
    }
  }

  @keyframes fieldDropPulse {
    0%,
    100% {
      opacity: 1;
    }
    50% {
      opacity: 0.6;
    }
  }
}
</style>
