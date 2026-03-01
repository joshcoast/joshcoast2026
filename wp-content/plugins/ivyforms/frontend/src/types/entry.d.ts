export interface Entry {
  id: number
  formId: number
  status: string
  userId: number
  ipAddress: string
  userAgent: string
  author: string
  sourceURL: string
  starred: boolean
  dateCreated?: string
  dateEdited?: string
}
