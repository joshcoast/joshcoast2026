// Utility for pagination logic
export function isSinglePage(total: number, pageSize: number): boolean {
  if (!pageSize || !total) return true
  return Math.ceil(total / pageSize) <= 1
}
