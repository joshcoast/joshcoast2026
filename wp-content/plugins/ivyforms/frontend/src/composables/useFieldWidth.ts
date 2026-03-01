import type { Field } from '@/types/field'

/**
 * Composable for calculating field widths in grid layout
 *
 * Supports:
 * - Auto width based on fields per row (1=100%, 2=50%, 3=33%, 4=25%, 5=20%)
 * - Custom widths from manual resize
 * - Maximum 5 fields per row
 */
export function useFieldWidth() {
  /**
   * Calculate width percentage based on number of fields in row
   * Returns values with one decimal place for precision
   * Maximum fields per row is 5 (minimum 20% each)
   * CSS will handle the gap spacing separately
   */
  const getAutoWidth = (fieldsInRow: number): number => {
    if (fieldsInRow === 1) return 100
    if (fieldsInRow >= 5) return 20 // Max 5 fields = 20% each

    // Divide 100% evenly and round to 1 decimal place
    return Math.round((100 / fieldsInRow) * 10) / 10
  }

  /**
   * Group fields by row index
   */
  const groupFieldsByRow = (fields: Field[]): Map<number, Field[]> => {
    const rows = new Map<number, Field[]>()

    fields.forEach((field) => {
      const rowIndex = field.rowIndex ?? 0
      if (!rows.has(rowIndex)) {
        rows.set(rowIndex, [])
      }
      rows.get(rowIndex)!.push(field)
    })

    // Sort fields within each row by columnIndex
    rows.forEach((rowFields) => {
      rowFields.sort((a, b) => (a.columnIndex ?? 0) - (b.columnIndex ?? 0))
    })

    return rows
  }

  /**
   * Calculate width for a field
   * Returns custom width if set, otherwise calculates based on fields in row
   */
  const getFieldWidth = (field: Field, fieldsInRow: Field[]): number => {
    // If custom width is set, use it
    if (field.width && field.width >= 20 && field.width <= 100) {
      return field.width
    }

    // Otherwise calculate based on number of fields in row
    return getAutoWidth(fieldsInRow.length)
  }

  /**
   * Get CSS width style for a field
   * Uses calc() to account for gap between fields
   */
  const getFieldWidthStyle = (field: Field, fieldsInRow: Field[]): string => {
    const width = getFieldWidth(field, fieldsInRow)
    const numFields = fieldsInRow.length

    // For single field, use full width
    if (numFields === 1) {
      return '100%'
    }

    // Calculate width accounting for gaps
    // Formula: calc(width% - (total_gaps / num_fields))
    // Gap is 12px (gap-12 class), and there are (numFields - 1) gaps total
    const gapAdjustment = `${(12 * (numFields - 1)) / numFields}px`
    return `calc(${width}% - ${gapAdjustment})`
  }

  /**
   * Check if row can accept more fields (max 5 per row)
   */
  const canAddToRow = (fieldsInRow: Field[]): boolean => {
    return fieldsInRow.length < 5
  }

  /**
   * Get next available column index in row
   */
  const getNextColumnIndex = (fieldsInRow: Field[]): number => {
    if (fieldsInRow.length === 0) return 0

    // Find the maximum columnIndex and add 1
    const maxColumn = Math.max(...fieldsInRow.map((f) => f.columnIndex ?? 0))
    return maxColumn + 1
  }

  /**
   * Distribute widths intelligently when adding a new field to a row
   * - New field gets equal share (1/n where n is total fields after adding)
   * - Existing fields are proportionally scaled to fit remaining space
   *
   * Example: Row has 2 fields at 40% and 60%, adding 3rd field:
   * - New field gets 33.33% (1/3)
   * - Remaining space: 100% - 33.33% = 66.67%
   * - Existing fields scaled proportionally:
   *   - Field 1: 40/(40+60) * 66.67% = 26.67%
   *   - Field 2: 60/(40+60) * 66.67% = 40%
   */
  const distributeWidthsEvenly = (fields: Field[]): Field[] => {
    if (fields.length === 0) return []
    if (fields.length === 1) return fields.map((f) => ({ ...f, width: 100 }))

    // For rows with gaps (12px each), we need to account for gap space
    // Each field should take up width that, when combined with gaps, totals 100%
    // Formula: width% = 100 / fieldCount
    // The calc() in CSS will handle subtracting the gap portion
    const width = Math.round((100 / fields.length) * 10) / 10

    return fields.map((field) => ({
      ...field,
      width: width,
    }))
  }

  /**
   * Distribute widths proportionally when adding a new field to existing resized fields
   * Maintains the relative proportions of existing fields
   */
  const distributeWidthsProportionally = (
    existingFields: Field[],
    newFieldCount: number = 1,
  ): Field[] => {
    if (existingFields.length === 0) return []

    const totalFieldsAfter = existingFields.length + newFieldCount
    const newFieldWidth = getAutoWidth(totalFieldsAfter)
    const remainingSpace = 100 - newFieldWidth * newFieldCount

    // Calculate total width of existing fields
    const existingTotalWidth = existingFields.reduce((sum, f) => sum + (f.width ?? 100), 0)

    // Scale existing fields proportionally to fit remaining space
    return existingFields.map((field) => {
      const currentWidth = field.width ?? 100
      const proportion =
        existingTotalWidth > 0 ? currentWidth / existingTotalWidth : 1 / existingFields.length
      const newWidth = remainingSpace * proportion

      return {
        ...field,
        width: Math.max(10, Math.min(100, newWidth)), // Clamp between 10% and 100%
      }
    })
  }

  /**
   * Validate and normalize widths in a row
   * Ensures total doesn't exceed 100% and all fields have valid widths
   */
  const normalizeRowWidths = (fields: Field[]): Field[] => {
    const totalWidth = fields.reduce((sum, f) => sum + (f.width ?? 100), 0)

    // If total exceeds 100%, distribute evenly
    if (totalWidth > 100) {
      return distributeWidthsEvenly(fields)
    }

    // Ensure all fields have width set
    return fields.map((field) => ({
      ...field,
      width: field.width ?? getAutoWidth(fields.length),
    }))
  }

  return {
    getAutoWidth,
    groupFieldsByRow,
    getFieldWidth,
    getFieldWidthStyle,
    canAddToRow,
    getNextColumnIndex,
    distributeWidthsEvenly,
    distributeWidthsProportionally,
    normalizeRowWidths,
  }
}
