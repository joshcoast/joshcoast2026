import type { Column } from 'element-plus'

export interface CellRenderProps {
  column: Column
  columns: Column[]
  columnIndex: number
  rowData: string | number | boolean
  rowIndex: number
}
