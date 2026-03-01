import { ref, computed } from 'vue'
import { storeToRefs } from 'pinia'
import type { Field } from '@/types'
import { useFormBuilder } from '@/stores/useFormBuilder'
import { duplicateFieldForStore } from '@/utils/fieldUtils'
import { useLabels } from '@/composables/useLabels'

interface DropIndicator {
  fieldIndex: number
  position: 'left' | 'right'
}

interface FieldRow {
  rowIndex: number
  fields: Field[]
}

export function useFormBuilderDraggable() {
  const formBuilderStore = useFormBuilder()
  const { selectedField, fields } = storeToRefs(formBuilderStore)
  const { getLabel } = useLabels()

  // Drag state
  const isDragging = ref(false)
  const isDraggingRow = ref(false)
  const draggedField = ref<Field | null>(null)
  const draggedRow = ref<FieldRow | null>(null)
  const draggedFromRow = ref<FieldRow | null>(null)
  const dropIndicator = ref<DropIndicator | null>(null)
  const dropZoneRowIndex = ref<number | null>(null)
  const hoveredRowIndex = ref<number | null>(null)

  // Field rows computed
  const fieldRows = computed(() => {
    const rows: FieldRow[] = []
    const rowMap = new Map<number, Field[]>()

    formBuilderStore.fields.forEach((field) => {
      const rowIdx = field.rowIndex ?? 0
      if (!rowMap.has(rowIdx)) {
        rowMap.set(rowIdx, [])
      }
      rowMap.get(rowIdx)!.push(field)
    })

    const sortedRowIndexes = Array.from(rowMap.keys()).sort((a, b) => a - b)

    sortedRowIndexes.forEach((rowIdx) => {
      const fieldsInRow = rowMap.get(rowIdx)!
      fieldsInRow.sort((a, b) => (a.columnIndex ?? 0) - (b.columnIndex ?? 0))
      rows.push({
        rowIndex: rowIdx,
        fields: fieldsInRow,
      })
    })

    return rows
  })

  // Row drag handlers
  const onRowDragStart = (event: DragEvent, row: FieldRow) => {
    if (!event.dataTransfer) return
    isDragging.value = true
    isDraggingRow.value = true
    draggedRow.value = row

    event.dataTransfer.effectAllowed = 'move'
    event.dataTransfer.setData('rowData', JSON.stringify({ rowIndex: row.rowIndex }))

    const rowWrapper = (event.target as HTMLElement).closest(
      '.ivyforms-form-builder__row-wrapper',
    ) as HTMLElement
    if (rowWrapper) {
      const clone = rowWrapper.cloneNode(true) as HTMLElement
      clone.style.position = 'absolute'
      clone.style.top = '-9999px'
      clone.style.opacity = '0.9'
      clone.style.pointerEvents = 'none'
      clone.style.width = `${rowWrapper.clientWidth}px`
      clone.style.background = 'var(--map-ground-level-1-foreground)'
      clone.style.padding = '8px'
      clone.style.borderRadius = '8px'
      clone.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)'
      clone.style.marginTop = '0'

      // Hide row action buttons in the clone
      const rowActions = clone.querySelectorAll(
        '.ivyforms-draggable__row-actions, .ivyforms-form-builder__row-actions',
      )
      rowActions.forEach((btn) => ((btn as HTMLElement).style.display = 'none'))

      // Hide field action buttons in the clone
      const actionButtons = clone.querySelectorAll('.ivyforms-field-unit__action-buttons')
      actionButtons.forEach((btn) => ((btn as HTMLElement).style.display = 'none'))

      document.body.appendChild(clone)
      event.dataTransfer.setDragImage(clone, rowWrapper.clientWidth / 2, 10)
      setTimeout(() => document.body.removeChild(clone), 0)
    }
  }

  const onRowDragEnd = () => {
    isDragging.value = false
    isDraggingRow.value = false
    draggedRow.value = null
    dropZoneRowIndex.value = null
  }

  // Field drag handlers
  const onFieldDragStart = (event: DragEvent, field: Field, row: FieldRow) => {
    if (!event.dataTransfer) return
    isDragging.value = true
    draggedField.value = field
    draggedFromRow.value = row

    event.dataTransfer.effectAllowed = 'move'
    event.dataTransfer.setData('fieldData', JSON.stringify(field))

    const fieldElement = (event.target as HTMLElement).closest(
      '.ivyforms-form-builder__field-container',
    ) as HTMLElement
    if (fieldElement) {
      const clone = fieldElement.cloneNode(true) as HTMLElement
      clone.style.position = 'absolute'
      clone.style.top = '-9999px'
      clone.style.opacity = '0.9'
      clone.style.pointerEvents = 'none'
      clone.style.width = `${fieldElement.clientWidth}px`
      clone.style.background = 'var(--map-ground-level-1-foreground)'
      clone.style.padding = '8px'
      clone.style.borderRadius = '8px'
      clone.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)'
      clone.style.margin = '0'

      // Remove margin from the ivyforms-field-unit inside the clone
      const fieldUnit = clone.querySelector('.ivyforms-field-unit')
      if (fieldUnit) {
        ;(fieldUnit as HTMLElement).style.margin = '0'
      }

      // Hide action buttons in the clone
      const actionButtons = clone.querySelectorAll('.ivyforms-field-unit__action-buttons')
      actionButtons.forEach((btn) => ((btn as HTMLElement).style.display = 'none'))

      document.body.appendChild(clone)
      event.dataTransfer.setDragImage(clone, fieldElement.clientWidth / 2, 10)
      setTimeout(() => document.body.removeChild(clone), 0)
    }
  }

  const onFieldDragEnd = () => {
    isDragging.value = false
    draggedField.value = null
    draggedFromRow.value = null
    dropIndicator.value = null
  }

  // Drop zone handlers (for rows)
  let rowZoneLeaveTimeout: ReturnType<typeof setTimeout> | null = null

  const onDragOverRowZone = (event: DragEvent, afterRowIndex: number) => {
    if (!event.dataTransfer) return
    event.preventDefault()
    event.dataTransfer.dropEffect = 'move'

    // Set isDragging if not already set (happens when dragging from panel)
    if (!isDragging.value) {
      isDragging.value = true
    }

    if (rowZoneLeaveTimeout) {
      clearTimeout(rowZoneLeaveTimeout)
      rowZoneLeaveTimeout = null
    }

    // Activate drop zone - the visibility is controlled by onDragOverRow logic
    dropZoneRowIndex.value = afterRowIndex
  }

  const onDragLeaveRowZone = () => {
    if (rowZoneLeaveTimeout) {
      clearTimeout(rowZoneLeaveTimeout)
    }
    rowZoneLeaveTimeout = setTimeout(() => {
      dropZoneRowIndex.value = null
    }, 50)
  }

  const onDropRowZone = (event: DragEvent, afterRowIndex: number) => {
    event.stopPropagation()
    event.preventDefault()

    // IMMEDIATE reset to prevent UI blocking - do this FIRST before any other logic
    isDragging.value = false
    isDraggingRow.value = false

    if (!event.dataTransfer) {
      dropZoneRowIndex.value = null
      draggedField.value = null
      draggedRow.value = null
      return
    }

    // Clear draggedField immediately to remove opacity from ghost
    draggedField.value = null

    // Check if dropping a whole row
    const rowData = event.dataTransfer.getData('rowData')
    if (rowData) {
      try {
        const { rowIndex: draggedRowIndex } = JSON.parse(rowData)
        const newRowIndex = afterRowIndex + 1

        if (draggedRowIndex === newRowIndex || draggedRowIndex === newRowIndex - 1) {
          dropZoneRowIndex.value = null
          return
        }

        formBuilderStore.fields.forEach((f) => {
          const currentRow = f.rowIndex ?? 0

          if (draggedRowIndex < newRowIndex) {
            // Moving row down
            if (currentRow > draggedRowIndex && currentRow < newRowIndex) {
              // Shift rows between dragged and target up by 1
              f.rowIndex = currentRow - 1
            } else if (currentRow === draggedRowIndex) {
              // Move dragged row to new position
              f.rowIndex = newRowIndex - 1
            }
          } else {
            // Moving row up
            if (currentRow >= newRowIndex && currentRow < draggedRowIndex) {
              // Shift rows between target and dragged down by 1
              f.rowIndex = currentRow + 1
            } else if (currentRow === draggedRowIndex) {
              // Move dragged row to new position
              f.rowIndex = newRowIndex
            }
          }
        })

        formBuilderStore.updateFieldPositions()

        // Fix columnIndex for all rows after reordering
        // Sort by columnIndex to maintain field order within each row
        const uniqueRowIndexes = [...new Set(formBuilderStore.fields.map((f) => f.rowIndex ?? 0))]
        uniqueRowIndexes.forEach((rowIdx) => {
          const rowFields = formBuilderStore.fields.filter((f) => f.rowIndex === rowIdx)
          // Sort by columnIndex to maintain the correct left-to-right order
          rowFields.sort((a, b) => (a.columnIndex ?? 0) - (b.columnIndex ?? 0))
          // Reassign columnIndex sequentially
          rowFields.forEach((f, idx) => {
            f.columnIndex = idx
          })
        })

        // Recalculate widths for all affected rows after row reordering
        const allRowIndexes = [...new Set(formBuilderStore.fields.map((f) => f.rowIndex ?? 0))]
        allRowIndexes.forEach((rowIdx) => {
          formBuilderStore.recalculateRowWidths(rowIdx)
        })

        dropZoneRowIndex.value = null
        isDragging.value = false
        isDraggingRow.value = false
        draggedRow.value = null
        return
      } catch {
        // Error parsing row data
        dropZoneRowIndex.value = null
        isDragging.value = false
        isDraggingRow.value = false
      }
    }

    // Otherwise, dropping a field to create new row
    let fieldData = event.dataTransfer.getData('fieldData')
    if (!fieldData) {
      fieldData = event.dataTransfer.getData('application/json')
    }

    if (!fieldData) {
      dropZoneRowIndex.value = null
      dropIndicator.value = null
      return
    }

    try {
      const parsedField = JSON.parse(fieldData) as Field
      const field =
        formBuilderStore.fields.find((f) => f.fieldIndex === parsedField.fieldIndex) || parsedField

      const existingFieldIndex = formBuilderStore.fields.findIndex(
        (f) => f.fieldIndex === field.fieldIndex,
      )
      const isExistingField = existingFieldIndex !== -1
      const newRowIndex = afterRowIndex + 1

      if (isExistingField) {
        const oldRowIndex = field.rowIndex ?? 0

        // Shift rows to make space
        formBuilderStore.fields.forEach((f) => {
          const fRow = f.rowIndex ?? 0
          if (fRow >= newRowIndex && f.fieldIndex !== field.fieldIndex) {
            f.rowIndex = fRow + 1
          }
        })

        field.rowIndex = newRowIndex
        field.columnIndex = 0
        field.width = 100

        // Clean up old row
        const oldRowFields = formBuilderStore.fields.filter(
          (f) => f.rowIndex === oldRowIndex && f.fieldIndex !== field.fieldIndex,
        )
        oldRowFields.sort((a, b) => (a.columnIndex ?? 0) - (b.columnIndex ?? 0))
        oldRowFields.forEach((f, idx) => {
          f.columnIndex = idx
        })
        formBuilderStore.recalculateRowWidths(oldRowIndex)

        // Compact row indexes
        const allRowIndexes = [
          ...new Set(formBuilderStore.fields.map((f) => f.rowIndex ?? 0)),
        ].sort((a, b) => a - b)
        const rowMapping = new Map<number, number>()
        allRowIndexes.forEach((oldIdx, newIdx) => {
          rowMapping.set(oldIdx, newIdx)
        })
        formBuilderStore.fields.forEach((f) => {
          const oldIdx = f.rowIndex ?? 0
          f.rowIndex = rowMapping.get(oldIdx) ?? oldIdx
        })
      } else {
        // New field from panel
        formBuilderStore.fields.forEach((f) => {
          const fRow = f.rowIndex ?? 0
          if (fRow >= newRowIndex) {
            f.rowIndex = fRow + 1
          }
        })

        field.rowIndex = newRowIndex
        field.columnIndex = 0
        field.width = 100
        formBuilderStore.addField(field)
      }

      formBuilderStore.updateFieldPositions()

      // Recalculate widths only for affected rows (new row and old row if moving existing field)
      if (isExistingField && draggedFromRow.value !== null) {
        // Recalculate old row where field was moved from
        formBuilderStore.recalculateRowWidths(draggedFromRow.value.rowIndex)
      }
      // Recalculate new row where field was dropped
      formBuilderStore.recalculateRowWidths(newRowIndex)

      dropZoneRowIndex.value = null
      dropIndicator.value = null
      isDragging.value = false
      draggedField.value = null
      draggedFromRow.value = null
    } catch {
      dropZoneRowIndex.value = null
      dropIndicator.value = null
      isDragging.value = false
    }
  }

  // Field drop handlers
  const onDragOverRow = (event: DragEvent) => {
    // CRITICAL: If not dragging, do NOTHING - don't even check dataTransfer
    if (!isDragging.value && !formBuilderStore.isDraggingFromPanel) {
      return
    }

    if (!event.dataTransfer) return

    // Don't prevent default if we're in a gap (let container handle it)
    const target = event.target as HTMLElement
    if (!target.closest('.ivyforms-form-builder__row-wrapper')) {
      return
    }

    event.preventDefault()
    event.dataTransfer.dropEffect = 'move'

    // Set isDragging if not already set (happens when dragging from panel)
    if (!isDragging.value) {
      isDragging.value = true
    }

    // Get the row wrapper element (current target is row wrapper now)
    const rowWrapper = event.currentTarget as HTMLElement

    if (!rowWrapper) return

    // Get the row index from data attribute
    const currentRowIndex = parseInt(rowWrapper.getAttribute('data-row-index') || '0', 10)

    const rect = rowWrapper.getBoundingClientRect()
    const mouseY = event.clientY

    // When dragging a ROW: use 50% height split (similar to left/right field logic)
    if (isDraggingRow.value) {
      const midpoint = rect.top + rect.height / 2
      const isTopHalf = mouseY < midpoint

      // Top half - show drop zone above this row
      if (isTopHalf) {
        dropZoneRowIndex.value = currentRowIndex - 1
      }
      // Bottom half - show drop zone below this row
      else {
        dropZoneRowIndex.value = currentRowIndex
      }
      return
    }

    // When dragging a FIELD: detect padding areas (outside field boundaries)
    const rowFieldsElement = rowWrapper.querySelector(
      '.ivyforms-form-builder__row-fields',
    ) as HTMLElement
    if (!rowFieldsElement) {
      dropZoneRowIndex.value = null
      return
    }

    const fieldsRect = rowFieldsElement.getBoundingClientRect()

    // Calculate padding areas (row wrapper has p-1 = 8px padding)
    const paddingTop = fieldsRect.top - rect.top
    const paddingBottom = rect.bottom - fieldsRect.bottom

    // Define threshold for showing drop zones (use full padding area)
    const topThreshold = rect.top + paddingTop
    const bottomThreshold = rect.bottom - paddingBottom

    // Show top drop zone if in top padding area
    if (mouseY < topThreshold) {
      dropZoneRowIndex.value = currentRowIndex - 1
    }
    // Show bottom drop zone if in bottom padding area
    else if (mouseY > bottomThreshold) {
      dropZoneRowIndex.value = currentRowIndex
    }
    // Clear drop zone when in the middle (over fields area)
    else {
      dropZoneRowIndex.value = null
    }
  }

  const onDragOverField = (event: DragEvent, targetField: Field, rowFields: Field[]) => {
    // CRITICAL: If not dragging, do NOTHING
    if (!isDragging.value && !formBuilderStore.isDraggingFromPanel) {
      return
    }

    if (isDraggingRow.value) {
      dropIndicator.value = null
      return
    }
    if (!event.dataTransfer) return
    event.preventDefault()
    event.stopPropagation() // Stop propagation to prevent row handler from being called

    // Set isDragging if not already set (happens when dragging from panel)
    if (!isDragging.value) {
      isDragging.value = true
    }

    // Clear row drop zone indicator when dragging over fields
    dropZoneRowIndex.value = null

    // Check if dragging from panel (new field) or from a different row
    const isDraggingFromPanel = !draggedField.value
    const isDraggingFromDifferentRow =
      draggedField.value && draggedFromRow.value?.rowIndex !== targetField.rowIndex

    // Don't show indicators if row already has 5 fields and it's a new field or from different row
    if ((isDraggingFromPanel || isDraggingFromDifferentRow) && rowFields.length >= 5) {
      dropIndicator.value = null
      return
    }

    const element = event.currentTarget as HTMLElement
    const rect = element.getBoundingClientRect()
    const mouseX = event.clientX
    const midpoint = rect.left + rect.width / 2
    const isLeftHalf = mouseX < midpoint

    // Find the current field's position in the row
    const currentFieldIndex = rowFields.findIndex((f) => f.fieldIndex === targetField.fieldIndex)
    const previousField = currentFieldIndex > 0 ? rowFields[currentFieldIndex - 1] : null

    // If hovering on left half and there's a previous field, show right indicator of previous field
    if (isLeftHalf && previousField) {
      dropIndicator.value = {
        fieldIndex: previousField.fieldIndex,
        position: 'right',
      }
    } else if (isLeftHalf && currentFieldIndex === 0) {
      // First field, left half - show left indicator (for dropping before first field)
      dropIndicator.value = {
        fieldIndex: targetField.fieldIndex,
        position: 'left',
      }
    } else {
      // Right half - show right indicator of current field
      dropIndicator.value = {
        fieldIndex: targetField.fieldIndex,
        position: 'right',
      }
    }
  }

  const onDragLeaveField = () => {
    dropIndicator.value = null
  }

  const onDropInRow = (event: DragEvent) => {
    event.preventDefault()
    event.stopPropagation()

    // If dropZoneRowIndex is set, handle as row zone drop
    if (dropZoneRowIndex.value !== null) {
      onDropRowZone(event, dropZoneRowIndex.value)
      return
    }

    // Otherwise, ignore if dragging a row
    if (isDraggingRow.value) return
  }

  // Rows container handlers (for first drop zone and gaps between rows)
  const onDragOverRowsContainer = (event: DragEvent) => {
    // CRITICAL: If not dragging, do NOTHING - don't even check dataTransfer
    // This prevents ANY interference with normal page interaction
    if (!isDragging.value && !formBuilderStore.isDraggingFromPanel) {
      return
    }

    if (!event.dataTransfer) return

    const target = event.target as HTMLElement

    // Check if hovering directly on a drop zone element
    if (
      target.classList.contains('ivyforms-draggable__row-drop-zone') ||
      target.classList.contains('ivyforms-form-builder__row-drop-zone')
    ) {
      event.preventDefault()
      event.dataTransfer.dropEffect = 'move'

      // Determine which drop zone based on classes
      if (target.classList.contains('is-active')) {
        // Drop zone is already active, keep current dropZoneRowIndex
        return
      }
    }

    // Check if hovering in the gap between rows (not over a row wrapper)
    if (!target.closest('.ivyforms-form-builder__row-wrapper')) {
      event.preventDefault()
      event.dataTransfer.dropEffect = 'move'

      const container = event.currentTarget as HTMLElement
      const mouseY = event.clientY

      // Get all row wrappers to find which gap we're in
      const rowWrappers = Array.from(
        container.querySelectorAll('.ivyforms-form-builder__row-wrapper'),
      )

      if (rowWrappers.length === 0) return

      // Check if above first row
      const firstRowRect = rowWrappers[0].getBoundingClientRect()
      if (mouseY < firstRowRect.top) {
        dropZoneRowIndex.value = -1
        return
      }

      // Check if in gap between rows
      for (let i = 0; i < rowWrappers.length - 1; i++) {
        const currentRowRect = rowWrappers[i].getBoundingClientRect()
        const nextRowRect = rowWrappers[i + 1].getBoundingClientRect()

        if (mouseY > currentRowRect.bottom && mouseY < nextRowRect.top) {
          const currentRowIndex = parseInt(rowWrappers[i].getAttribute('data-row-index') || '0', 10)
          dropZoneRowIndex.value = currentRowIndex
          return
        }
      }

      // Check if below last row
      const lastRowRect = rowWrappers[rowWrappers.length - 1].getBoundingClientRect()
      if (mouseY > lastRowRect.bottom) {
        const lastRowIndex = parseInt(
          rowWrappers[rowWrappers.length - 1].getAttribute('data-row-index') || '0',
          10,
        )
        dropZoneRowIndex.value = lastRowIndex
      }
    }
  }

  const onDropInRowsContainer = (event: DragEvent) => {
    // Handle drops on any drop zone (gaps between rows)
    const target = event.target as HTMLElement

    // Only handle if not dropping on a row wrapper (let row handle its own drops)
    if (!target.closest('.ivyforms-form-builder__row-wrapper')) {
      if (dropZoneRowIndex.value !== null) {
        event.preventDefault()
        event.stopPropagation()
        onDropRowZone(event, dropZoneRowIndex.value)
      }
    }
  }

  const onDropOnField = (event: DragEvent, targetField: Field, row: FieldRow) => {
    // IMMEDIATE reset to prevent UI blocking - do this FIRST
    isDragging.value = false
    isDraggingRow.value = false
    draggedField.value = null

    if (isDraggingRow.value) return
    if (!event.dataTransfer) return

    const fieldDataStr = event.dataTransfer.getData('fieldData')
    if (!fieldDataStr) return

    try {
      const parsedField = JSON.parse(fieldDataStr) as Field
      const field =
        formBuilderStore.fields.find((f) => f.fieldIndex === parsedField.fieldIndex) || parsedField

      const existingIndex = formBuilderStore.fields.findIndex(
        (f) => f.fieldIndex === field.fieldIndex,
      )
      const isMove = existingIndex !== -1

      const fieldIsFromDifferentRow = isMove && field.rowIndex !== row.rowIndex
      const isNewField = !isMove
      if ((isNewField || fieldIsFromDifferentRow) && row.fields.length >= 5) {
        dropIndicator.value = null
        return
      }

      const targetFieldInStore = formBuilderStore.fields.find(
        (f) => f.fieldIndex === targetField.fieldIndex,
      )
      let targetColumnIndex = targetFieldInStore?.columnIndex ?? 0

      if (dropIndicator.value && dropIndicator.value.fieldIndex === targetField.fieldIndex) {
        targetColumnIndex =
          dropIndicator.value.position === 'left' ? targetColumnIndex : targetColumnIndex + 1
      }

      if (isMove) {
        const oldRowIndex = field.rowIndex ?? 0
        const oldColumnIndex = field.columnIndex ?? 0
        const wasInSameRow = oldRowIndex === row.rowIndex

        if (wasInSameRow) {
          if (field.fieldIndex === targetField.fieldIndex) {
            dropIndicator.value = null
            return
          }

          const fieldsInRow = formBuilderStore.fields.filter((f) => f.rowIndex === row.rowIndex)

          fieldsInRow.forEach((f) => {
            if (f.fieldIndex === field.fieldIndex) return
            const currentCol = f.columnIndex ?? 0

            if (targetColumnIndex <= oldColumnIndex) {
              if (currentCol >= targetColumnIndex && currentCol < oldColumnIndex) {
                f.columnIndex = currentCol + 1
              }
            } else {
              if (currentCol > oldColumnIndex && currentCol < targetColumnIndex) {
                f.columnIndex = currentCol - 1
              }
            }
          })

          field.columnIndex = targetColumnIndex
          fieldsInRow.sort((a, b) => (a.columnIndex ?? 0) - (b.columnIndex ?? 0))
          fieldsInRow.forEach((f, idx) => (f.columnIndex = idx))

          // Don't recalculate widths when reordering within same row - preserve existing widths
          formBuilderStore.updateFieldPositions()
        } else {
          // Moving to a different row - reset all widths to even distribution
          const targetRowFieldsBeforeMove = formBuilderStore.fields.filter(
            (f) => f.rowIndex === row.rowIndex && f.fieldIndex !== field.fieldIndex,
          )

          const totalFieldsAfter = targetRowFieldsBeforeMove.length + 1
          const evenWidth =
            totalFieldsAfter >= 5 ? 20 : Math.round((100 / totalFieldsAfter) * 10) / 10

          // Reset all fields in target row to even width
          targetRowFieldsBeforeMove.forEach((f) => {
            f.width = evenWidth
          })

          field.width = evenWidth

          field.rowIndex = row.rowIndex
          field.columnIndex = targetColumnIndex

          const oldRowFields = formBuilderStore.fields.filter(
            (f) => f.rowIndex === oldRowIndex && f.fieldIndex !== field.fieldIndex,
          )
          oldRowFields.sort((a, b) => (a.columnIndex ?? 0) - (b.columnIndex ?? 0))
          oldRowFields.forEach((f, idx) => {
            f.columnIndex = idx
          })

          formBuilderStore.fields.forEach((f) => {
            if (f.rowIndex === row.rowIndex && f.fieldIndex !== field.fieldIndex) {
              if ((f.columnIndex ?? 0) >= targetColumnIndex) {
                f.columnIndex = (f.columnIndex ?? 0) + 1
              }
            }
          })

          const newRowFields = formBuilderStore.fields.filter((f) => f.rowIndex === row.rowIndex)
          newRowFields.sort((a, b) => (a.columnIndex ?? 0) - (b.columnIndex ?? 0))
          newRowFields.forEach((f, idx) => (f.columnIndex = idx))

          formBuilderStore.recalculateRowWidths(oldRowIndex, false)

          const allRowIndexes = [
            ...new Set(formBuilderStore.fields.map((f) => f.rowIndex ?? 0)),
          ].sort((a, b) => a - b)
          const rowMapping = new Map<number, number>()
          allRowIndexes.forEach((oldIdx, newIdx) => {
            rowMapping.set(oldIdx, newIdx)
          })
          formBuilderStore.fields.forEach((f) => {
            const oldIdx = f.rowIndex ?? 0
            f.rowIndex = rowMapping.get(oldIdx) ?? oldIdx
          })

          formBuilderStore.updateFieldPositions()
        }
      } else {
        // Dropping a new field - reset all widths to even distribution
        const fieldsInRow = formBuilderStore.fields.filter((f) => f.rowIndex === row.rowIndex)
        const totalFieldsAfter = fieldsInRow.length + 1
        const evenWidth =
          totalFieldsAfter >= 5 ? 20 : Math.round((100 / totalFieldsAfter) * 10) / 10

        // Reset all fields in row to even width
        fieldsInRow.forEach((f) => {
          f.width = evenWidth
        })

        field.width = evenWidth

        fieldsInRow.forEach((f) => {
          if ((f.columnIndex ?? 0) >= targetColumnIndex) {
            f.columnIndex = (f.columnIndex ?? 0) + 1
          }
        })

        field.rowIndex = row.rowIndex
        field.columnIndex = targetColumnIndex

        formBuilderStore.addField(field)

        const allFieldsInRow = formBuilderStore.fields.filter((f) => f.rowIndex === row.rowIndex)
        allFieldsInRow.sort((a, b) => (a.columnIndex ?? 0) - (b.columnIndex ?? 0))
        allFieldsInRow.forEach((f, idx) => {
          f.columnIndex = idx
        })
      }

      formBuilderStore.updateFieldPositions()
      dropIndicator.value = null
      isDragging.value = false
      draggedField.value = null
      draggedFromRow.value = null
    } catch {
      dropIndicator.value = null
      isDragging.value = false
      draggedField.value = null
      draggedFromRow.value = null
    }
  }

  // Empty state drag handlers
  const emptyDropPosition = ref<'left' | 'right' | 'top' | 'bottom' | null>(null)

  const onDragOverEmpty = (event: DragEvent) => {
    // CRITICAL: If not dragging, do NOTHING
    if (!isDragging.value && !formBuilderStore.isDraggingFromPanel) {
      return
    }

    event.preventDefault()
    const emptyArea = event.currentTarget as HTMLElement
    const rect = emptyArea.getBoundingClientRect()
    const offsetX = event.clientX - rect.left
    const offsetY = event.clientY - rect.top

    // Determine which quadrant of the empty area
    const isTop = offsetY < rect.height / 2
    const isLeft = offsetX < rect.width / 2

    if (Math.abs(offsetY - rect.height / 2) > Math.abs(offsetX - rect.width / 2)) {
      // Vertical
      emptyDropPosition.value = isTop ? 'top' : 'bottom'
    } else {
      // Horizontal
      emptyDropPosition.value = isLeft ? 'left' : 'right'
    }

    emptyArea.classList.add('dragging-over')
    emptyArea.setAttribute('data-drop-position', emptyDropPosition.value)
  }

  const onDragLeaveEmpty = (event: DragEvent) => {
    event.preventDefault()
    const emptyArea = event.currentTarget as HTMLElement
    emptyArea.classList.remove('dragging-over')
    emptyArea.removeAttribute('data-drop-position')
    emptyDropPosition.value = null
  }

  const onDropEmpty = (event: DragEvent) => {
    event.preventDefault()
    event.stopPropagation()

    // IMMEDIATE reset to prevent UI blocking - do this FIRST
    isDragging.value = false
    isDraggingRow.value = false
    draggedField.value = null

    const emptyArea = event.currentTarget as HTMLElement
    emptyArea.classList.remove('dragging-over')
    emptyArea.removeAttribute('data-drop-position')
    emptyDropPosition.value = null

    if (!event.dataTransfer) return

    let fieldData = event.dataTransfer.getData('fieldData')
    if (!fieldData) {
      fieldData = event.dataTransfer.getData('application/json')
    }

    if (!fieldData) return

    try {
      const field = JSON.parse(fieldData) as Field
      field.rowIndex = 0
      field.columnIndex = 0
      field.width = 100
      formBuilderStore.addField(field)
    } catch {
      // Error parsing field data
    }
  }

  // Row management functions
  const isRowSelected = (row: FieldRow) => {
    return row.fields.some(
      (field) => field.fieldIndex === formBuilderStore.selectedField?.fieldIndex,
    )
  }

  const moveRowUp = (row: FieldRow) => {
    if (row.rowIndex === 0) return

    const selectedFieldIndex = selectedField.value?.fieldIndex
    const currentRowIndex = row.rowIndex
    const targetRowIndex = currentRowIndex - 1

    // Swap rowIndex with fields in the row above
    fields.value.forEach((f) => {
      if (f.rowIndex === currentRowIndex) {
        f.rowIndex = targetRowIndex
      } else if (f.rowIndex === targetRowIndex) {
        f.rowIndex = currentRowIndex
      }
    })

    formBuilderStore.updateFieldPositions()

    // Re-select using the store method (same as clicking on a field)
    if (selectedFieldIndex !== undefined) {
      formBuilderStore.selectField(selectedFieldIndex)

      // Scroll the field into view if it moved off-screen
      setTimeout(() => {
        const fieldElement = document.querySelector(
          `[data-field-index="${selectedFieldIndex}"]`,
        ) as HTMLElement
        if (fieldElement) {
          fieldElement.style.scrollMargin = '20px'
          fieldElement.scrollIntoView({ behavior: 'smooth', block: 'center' })
        }
      }, 100)
    }
  }

  const moveRowDown = (row: FieldRow) => {
    const maxRow = Math.max(...fields.value.map((f) => f.rowIndex ?? 0))
    if (row.rowIndex >= maxRow) return

    const selectedFieldIndex = selectedField.value?.fieldIndex
    const currentRowIndex = row.rowIndex
    const targetRowIndex = currentRowIndex + 1

    // Swap rowIndex with fields in the row below
    fields.value.forEach((f) => {
      if (f.rowIndex === currentRowIndex) {
        f.rowIndex = targetRowIndex
      } else if (f.rowIndex === targetRowIndex) {
        f.rowIndex = currentRowIndex
      }
    })

    formBuilderStore.updateFieldPositions()

    // Re-select using the store method (same as clicking on a field)
    if (selectedFieldIndex !== undefined) {
      formBuilderStore.selectField(selectedFieldIndex)

      // Scroll the field into view if it moved off-screen
      setTimeout(() => {
        const fieldElement = document.querySelector(
          `[data-field-index="${selectedFieldIndex}"]`,
        ) as HTMLElement
        if (fieldElement) {
          fieldElement.style.scrollMargin = '20px'
          fieldElement.scrollIntoView({ behavior: 'smooth', block: 'center' })
        }
      }, 100)
    }
  }

  const duplicateRow = (row: FieldRow) => {
    const currentRowIndex = row.rowIndex
    const newRowIndex = currentRowIndex + 1

    const selectedFieldIndex = formBuilderStore.selectedField?.fieldIndex
    const selectedFieldColumnIndex = row.fields.find(
      (f) => f.fieldIndex === selectedFieldIndex,
    )?.columnIndex

    formBuilderStore.fields.forEach((f) => {
      if ((f.rowIndex ?? 0) > currentRowIndex) {
        f.rowIndex = (f.rowIndex ?? 0) + 1
      }
    })

    const duplicatedFields: Field[] = []
    row.fields.forEach((field) => {
      const newIndex = formBuilderStore.increaseCounter()
      const copy = duplicateFieldForStore(field, newIndex, getLabel)
      copy.rowIndex = newRowIndex
      copy.columnIndex = field.columnIndex
      copy.width = field.width
      duplicatedFields.push(copy)
    })

    duplicatedFields.forEach((field) => {
      formBuilderStore.fields.push(field)
    })

    formBuilderStore.updateFieldPositions()

    if (duplicatedFields.length > 0) {
      if (selectedFieldColumnIndex !== undefined) {
        const correspondingField = duplicatedFields.find(
          (f) => f.columnIndex === selectedFieldColumnIndex,
        )
        if (correspondingField) {
          formBuilderStore.selectedField = correspondingField
        } else {
          formBuilderStore.selectedField = duplicatedFields[0]
        }
      } else {
        formBuilderStore.selectedField = duplicatedFields[0]
      }
      formBuilderStore.activeTab = 'options'
    }
  }

  const deleteRow = (row: FieldRow) => {
    const fieldIndexes = row.fields.map((f) => f.fieldIndex)
    fieldIndexes.forEach((fieldIndex) => {
      formBuilderStore.deleteField(fieldIndex)
    })

    const remainingRowIndexes = [
      ...new Set(formBuilderStore.fields.map((f) => f.rowIndex ?? 0)),
    ].sort((a, b) => a - b)
    const rowMapping = new Map<number, number>()
    remainingRowIndexes.forEach((oldIdx, newIdx) => {
      rowMapping.set(oldIdx, newIdx)
    })
    formBuilderStore.fields.forEach((f) => {
      const oldIdx = f.rowIndex ?? 0
      f.rowIndex = rowMapping.get(oldIdx) ?? oldIdx
    })

    formBuilderStore.updateFieldPositions()
  }

  // Helper methods to check if row can move
  const canMoveRowUp = (row: FieldRow): boolean => {
    return row.rowIndex > 0
  }

  const canMoveRowDown = (row: FieldRow): boolean => {
    const maxRow = Math.max(...fields.value.map((f) => f.rowIndex ?? 0))
    return row.rowIndex < maxRow
  }

  // Field styling
  const getFieldStyleInRow = (field: Field, rowFields: Field[]) => {
    const width = field.width ?? 100

    // Single field that takes 100% width
    if (rowFields.length === 1 && width === 100) {
      return {
        width: '100%',
        flexShrink: 0,
      }
    }

    // For multiple fields in a row with gap-12 between them:
    // With flexbox gap, each field needs to subtract its proportional share of total gap space
    // Formula: fieldWidth = width% - (totalGaps * width / 100)
    // Add small adjustment (2px) to keep resize handle inside row border

    const numGaps = rowFields.length - 1
    const totalGapPx = numGaps * 12 // gap-12 = 12px

    // Calculate proportional gap share for this field
    const gapShare = (totalGapPx * width) / 100

    // Add 2px adjustment to keep resize handle visible inside border
    const borderAdjustment = 2

    return {
      width: `calc(${width}% - ${gapShare + borderAdjustment}px)`,
      flexShrink: 0,
    }
  }

  return {
    // State
    isDragging,
    isDraggingRow,
    draggedField,
    draggedRow,
    draggedFromRow,
    dropIndicator,
    dropZoneRowIndex,
    hoveredRowIndex,
    emptyDropPosition,
    fieldRows,

    // Row drag handlers
    onRowDragStart,
    onRowDragEnd,

    // Field drag handlers
    onFieldDragStart,
    onFieldDragEnd,

    // Drop zone handlers
    onDragOverRowZone,
    onDragLeaveRowZone,
    onDropRowZone,

    // Field drop handlers
    onDragOverRow,
    onDragOverField,
    onDragLeaveField,
    onDropInRow,
    onDropOnField,

    // Rows container handlers
    onDragOverRowsContainer,
    onDropInRowsContainer,

    // Empty state handlers
    onDragOverEmpty,
    onDragLeaveEmpty,
    onDropEmpty,

    // Row management
    isRowSelected,
    moveRowUp,
    moveRowDown,
    duplicateRow,
    deleteRow,
    canMoveRowUp,
    canMoveRowDown,

    // Utilities
    getFieldStyleInRow,
  }
}
