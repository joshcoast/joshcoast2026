import type { VNode } from 'vue'
import type { TableV2FixedDir } from 'element-plus'

export interface TableColumn {
  key: string
  title?: string
  sortable?: boolean
  width?: string | number
  headerRenderer?: () => VNode
  cellRenderer?: (props: { rowData: { [key: string]: string | number | boolean } }) => VNode
  class?: string
  visible?: boolean
  showInModal?: boolean
  minWidth?: string | number
  comingSoon?: boolean
  fixed?: true | TableV2FixedDir
}
