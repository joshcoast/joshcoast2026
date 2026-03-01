/**
 * Drop position types for row-based grid layout
 */

export type DropPosition = 'above' | 'below' | 'left' | 'right'

export interface DropZone {
  rowIndex: number
  columnIndex: number
  position: DropPosition
}

export interface FieldPosition {
  rowIndex: number
  columnIndex: number
}

export interface DragContext {
  sourceField?: {
    fieldIndex: number
    rowIndex: number
    columnIndex: number
  }
  targetPosition?: DropZone
}
