import { ref, onBeforeUnmount } from 'vue'
import type { Field } from '@/types'
import { useFormBuilder } from '@/stores/useFormBuilder'

const MIN_WIDTH_PERCENT = 20 // Minimum field width
const MAX_WIDTH_PERCENT = 100 // Maximum field width

// Shared state across all instances (singleton pattern)
const resizingField = ref<Field | null>(null)
const resizeDirection = ref<'left' | 'right' | null>(null)
const resizeStartX = ref(0)
const resizeStartWidth = ref(0)
const resizeStartRightFieldWidth = ref(0)
const hoveredFieldForResize = ref<Field | null>(null)

// Track active component instances using this composable
let activeInstances = 0

export function useFieldResize() {
  // Get store instance (initialized after Pinia is available)
  // The store must be initialized inside the composable function
  // (after Pinia is available), not at the module level
  const formBuilderStore = useFormBuilder()

  // Increment instance counter
  activeInstances++

  /**
   * Normalize all field widths in a row to ensure they sum to exactly 100%
   * Only normalizes if fields are close to 100% (within 5%) - otherwise allows empty space
   * Respects minimum width of 20% per field
   */
  const normalizeRowWidths = (rowFields: Field[]): void => {
    if (rowFields.length === 0) return

    const totalWidth = rowFields.reduce((sum, f) => sum + (f.width ?? 100), 0)

    // Only normalize if fields are CLOSE to 100% (between 95% and 105%)
    // This means user intended to fill the row, but rounding caused small differences
    if (totalWidth >= 95 && totalWidth <= 105) {
      if (totalWidth > 100) {
        // Scale down proportionally if over 100%
        const scaleFactor = 100 / totalWidth
        rowFields.forEach((f) => {
          f.width = Math.max(MIN_WIDTH_PERCENT, (f.width ?? 100) * scaleFactor)
        })
      } else if (totalWidth < 100) {
        // Add the missing percentage to the last field to reach exactly 100%
        const difference = 100 - totalWidth
        const lastField = rowFields[rowFields.length - 1]
        lastField.width = (lastField.width ?? 100) + difference
      }

      // Round all widths to 1 decimal place
      rowFields.forEach((f) => {
        f.width = Math.round((f.width ?? 100) * 10) / 10
      })

      // Final check: ensure we're at exactly 100% by adjusting last field for any rounding errors
      const finalTotal = rowFields.reduce((sum, f) => sum + (f.width ?? 100), 0)
      if (Math.abs(finalTotal - 100) > 0.01) {
        const lastField = rowFields[rowFields.length - 1]
        lastField.width = Math.round(((lastField.width ?? 100) + (100 - finalTotal)) * 10) / 10
      }
    } else {
      // Fields don't sum close to 100%, so user wants empty space - just round the values
      rowFields.forEach((f) => {
        f.width = Math.round((f.width ?? 100) * 10) / 10
      })
    }
  }

  /**
   * Get available space in a row (percentage)
   */
  const getAvailableSpace = (rowIndex: number, excludeFieldIndex?: number): number => {
    const rowFields = formBuilderStore.fields.filter(
      (f) => f.rowIndex === rowIndex && f.fieldIndex !== excludeFieldIndex,
    )
    const usedWidth = rowFields.reduce((sum, f) => sum + (f.width ?? 100), 0)
    return 100 - usedWidth
  }

  /**
   * Start resizing a field
   */
  const startResize = (event: MouseEvent, field: Field, direction: 'left' | 'right') => {
    event.preventDefault()
    event.stopPropagation()

    resizingField.value = field
    resizeDirection.value = direction
    resizeStartX.value = event.clientX
    resizeStartWidth.value = field.width ?? 100

    // Clear hovered field to prevent other handles from showing
    hoveredFieldForResize.value = null

    // Add global class to body to keep resize handles visible
    document.body.classList.add('ivyforms-resizing-field')

    // Set cursor style globally to maintain col-resize cursor
    document.body.style.cursor = 'col-resize'
    document.body.style.userSelect = 'none'

    // Find if there's a field to the right and store its initial width
    const rowFields = formBuilderStore.fields.filter((f) => f.rowIndex === field.rowIndex)
    rowFields.sort((a, b) => (a.columnIndex ?? 0) - (b.columnIndex ?? 0))
    const currentFieldIndex = rowFields.findIndex((f) => f.fieldIndex === field.fieldIndex)
    const rightField =
      currentFieldIndex < rowFields.length - 1 ? rowFields[currentFieldIndex + 1] : null

    resizeStartRightFieldWidth.value = rightField ? (rightField.width ?? 100) : 0

    document.addEventListener('mousemove', handleResize, { passive: false })
    document.addEventListener('mouseup', stopResize)
  }

  /**
   * Handle resize dragging - smooth resize with automatic width distribution
   */
  const handleResize = (event: MouseEvent) => {
    event.preventDefault()
    event.stopPropagation()

    if (!resizingField.value || resizeDirection.value !== 'right') return

    const deltaX = event.clientX - resizeStartX.value
    const containerWidth =
      document.querySelector('.ivyforms-form-builder__grid-container')?.clientWidth ?? 800
    const deltaPercent = (deltaX / containerWidth) * 100

    const field = resizingField.value
    const rowFields = formBuilderStore.fields.filter((f) => f.rowIndex === field.rowIndex)
    rowFields.sort((a, b) => (a.columnIndex ?? 0) - (b.columnIndex ?? 0))

    const currentFieldIndex = rowFields.findIndex((f) => f.fieldIndex === field.fieldIndex)
    const rightField =
      currentFieldIndex < rowFields.length - 1 ? rowFields[currentFieldIndex + 1] : null

    // Calculate new width
    let newWidth = resizeStartWidth.value + deltaPercent

    // Clamp to min/max
    newWidth = Math.max(MIN_WIDTH_PERCENT, Math.min(MAX_WIDTH_PERCENT, newWidth))

    if (rightField) {
      // Resize between two fields - take from right field
      const totalWidth = resizeStartWidth.value + resizeStartRightFieldWidth.value
      const newRightWidth = totalWidth - newWidth

      // Ensure both fields stay within valid range
      if (newRightWidth >= MIN_WIDTH_PERCENT) {
        field.width = Math.round(newWidth * 10) / 10 // Round to 1 decimal
        rightField.width = Math.round(newRightWidth * 10) / 10
      }
    } else {
      // Last field in row: can resize into empty space
      // Calculate space used by OTHER fields (excluding current field)
      const otherFieldsWidth = rowFields
        .filter((f) => f.fieldIndex !== field.fieldIndex)
        .reduce((sum, f) => sum + (f.width ?? 100), 0)

      // Maximum width is 100% minus other fields' widths
      const maxAllowedWidth = 100 - otherFieldsWidth

      // Clamp to valid range
      newWidth = Math.max(MIN_WIDTH_PERCENT, Math.min(maxAllowedWidth, newWidth))
      field.width = Math.round(newWidth * 10) / 10
    }
  }

  /**
   * Stop resizing
   */
  const stopResize = () => {
    // Remove global resizing class and cursor styles
    document.body.classList.remove('ivyforms-resizing-field')
    document.body.style.cursor = ''
    document.body.style.userSelect = ''

    if (resizingField.value) {
      // Get all fields in the row
      const rowFields = formBuilderStore.fields.filter(
        (f) => f.rowIndex === resizingField.value!.rowIndex,
      )

      // Normalize widths to ensure they fit within 100%
      normalizeRowWidths(rowFields)

      // Update field positions
      formBuilderStore.updateFieldPositions()
    }

    resizingField.value = null
    resizeDirection.value = null
    resizeStartRightFieldWidth.value = 0

    document.removeEventListener('mousemove', handleResize)
    document.removeEventListener('mouseup', stopResize)
  }

  /**
   * Cleanup function to ensure event listeners are removed
   */
  const cleanup = () => {
    // Decrement instance counter
    activeInstances--

    // If this was the last instance, clean up all shared state and listeners
    if (activeInstances === 0) {
      // Remove any active event listeners
      document.removeEventListener('mousemove', handleResize)
      document.removeEventListener('mouseup', stopResize)

      // Clean up DOM modifications
      document.body.classList.remove('ivyforms-resizing-field')
      document.body.style.cursor = ''
      document.body.style.userSelect = ''

      // Reset shared state
      resizingField.value = null
      resizeDirection.value = null
      resizeStartX.value = 0
      resizeStartWidth.value = 0
      resizeStartRightFieldWidth.value = 0
      hoveredFieldForResize.value = null
    }
  }

  // Setup cleanup on component unmount
  onBeforeUnmount(cleanup)

  /**
   * Set hovered field for showing resize handles
   */
  const setHoveredField = (field: Field | null) => {
    // Don't update hover state if currently resizing
    if (resizingField.value) {
      return
    }

    hoveredFieldForResize.value = field
  }

  /**
   * Check if resize handles should be shown for a field
   */
  const shouldShowResizeHandles = (field: Field): boolean => {
    const formBuilderStore = useFormBuilder()

    // Check if row has 5 fields (all at minimum 20%, can't resize)
    const rowFields = formBuilderStore.fields.filter((f) => f.rowIndex === field.rowIndex)
    if (rowFields.length >= 5) {
      return false // Hide resize handles when row is at maximum capacity
    }

    // If actively resizing, ONLY show handle for the field being resized
    if (resizingField.value) {
      return resizingField.value.fieldIndex === field.fieldIndex
    }

    // Otherwise show handles if field is hovered
    return hoveredFieldForResize.value?.fieldIndex === field.fieldIndex
  }

  /**
   * Calculate width for new field dropped into empty space
   */
  const getWidthForDropInEmptySpace = (rowIndex: number): number => {
    const availableSpace = getAvailableSpace(rowIndex)
    return Math.max(MIN_WIDTH_PERCENT, Math.min(MAX_WIDTH_PERCENT, availableSpace))
  }

  /**
   * Check if a field can be duplicated (will it fit in the row)
   */
  const canDuplicateField = (field: Field): boolean => {
    const availableSpace = getAvailableSpace(field.rowIndex ?? 0)
    return availableSpace >= MIN_WIDTH_PERCENT
  }

  /**
   * Get the width for a duplicated field
   */
  const getWidthForDuplicate = (field: Field): number => {
    const fieldWidth = field.width ?? 100
    const availableSpace = getAvailableSpace(field.rowIndex ?? 0)

    if (availableSpace >= fieldWidth) {
      return fieldWidth
    } else {
      return Math.max(MIN_WIDTH_PERCENT, availableSpace)
    }
  }

  return {
    // State - return raw refs, not computed
    resizingField,
    hoveredFieldForResize,

    // Methods
    startResize,
    setHoveredField,
    shouldShowResizeHandles,
    getAvailableSpace,
    getWidthForDropInEmptySpace,
    canDuplicateField,
    getWidthForDuplicate,
    normalizeRowWidths,
  }
}
