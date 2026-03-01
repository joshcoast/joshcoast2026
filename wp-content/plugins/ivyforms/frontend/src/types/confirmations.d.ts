export interface Confirmation {
  id: number
  formId: number
  type: string
  enabled: boolean
  showForm: boolean
  message: string
  url: string
  page: string
  pageUrl?: string
}
