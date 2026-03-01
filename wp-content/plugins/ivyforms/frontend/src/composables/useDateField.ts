export function useDateField() {
  function parseDateString(
    dateStr: string,
    format?: string,
  ): { day: string; month: string; year: string } {
    if (!dateStr) {
      return { day: '', month: '', year: '' }
    }

    // Try to parse based on separators
    const separators = ['-', '/', '.']
    let parts: string[] = []

    for (const sep of separators) {
      if (dateStr.includes(sep)) {
        parts = dateStr.split(sep)
        break
      }
    }

    if (parts.length !== 3) {
      return { day: '', month: '', year: '' }
    }

    // Determine format (default to MM/DD/YYYY if not provided)
    const dateFormat = (format || 'MM/DD/YYYY').toUpperCase()

    let day = ''
    let month = ''
    let year = ''

    const formatParts = dateFormat.split(/[/.-]/)
    formatParts.forEach((part, index) => {
      const upperPart = part.toUpperCase()
      if (upperPart === 'DD' || upperPart === 'D') day = parts[index]
      if (upperPart === 'MM' || upperPart === 'M') month = parts[index]
      if (upperPart === 'YYYY' || upperPart === 'YY') year = parts[index]
    })

    return { day, month, year }
  }

  function formatDateString(
    day: string,
    month: string,
    year: string,
    format: string = 'MM/DD/YYYY',
  ): string {
    if (!day || !month || !year) return ''

    const separator = format.includes('/') ? '/' : format.includes('-') ? '-' : '.'

    const formatParts = format.split(/[/.-]/)
    const result = formatParts.map((part) => {
      const upperPart = part.toUpperCase()
      if (upperPart === 'DD' || upperPart === 'D') {
        return upperPart === 'DD' ? day.padStart(2, '0') : day
      }
      if (upperPart === 'MM' || upperPart === 'M') {
        return upperPart === 'MM' ? month.padStart(2, '0') : month
      }
      if (upperPart === 'YYYY') return year
      if (upperPart === 'YY') return year.slice(-2)
      return part
    })

    return result.join(separator)
  }

  function isValidDate(
    day: string | number,
    month: string | number,
    year: string | number,
  ): boolean {
    const d = typeof day === 'string' ? parseInt(day, 10) : day
    const m = typeof month === 'string' ? parseInt(month, 10) : month
    const y = typeof year === 'string' ? parseInt(year, 10) : year

    if (isNaN(d) || isNaN(m) || isNaN(y)) return false
    if (m < 1 || m > 12) return false
    if (d < 1 || d > 31) return false
    if (y < 1000 || y > 9999) return false

    // Check day validity for month
    const daysInMonth = new Date(y, m, 0).getDate()
    return d <= daysInMonth
  }

  function getDaysArray(): Array<{ label: string; value: string }> {
    return Array.from({ length: 31 }, (_, i) => {
      const day = (i + 1).toString().padStart(2, '0')
      return { label: day, value: day }
    })
  }

  function getMonthsArray(): Array<{ label: string; value: string }> {
    return Array.from({ length: 12 }, (_, i) => {
      const month = (i + 1).toString().padStart(2, '0')
      return { label: month, value: month }
    })
  }

  function getYearsArray(
    minYear?: number,
    maxYear?: number,
  ): Array<{ label: string; value: string }> {
    const currentYear = new Date().getFullYear()
    const min = minYear || currentYear - 100
    const max = maxYear || currentYear + 100

    const years: Array<{ label: string; value: string }> = []
    for (let year = min; year <= max; year++) {
      years.push({ label: year.toString(), value: year.toString() })
    }
    return years
  }

  function toISOFormat(dateStr: string, format?: string): string {
    const parsed = parseDateString(dateStr, format)
    if (!parsed.day || !parsed.month || !parsed.year) return ''
    return `${parsed.year}-${parsed.month.padStart(2, '0')}-${parsed.day.padStart(2, '0')}`
  }

  function fromISOFormat(isoDate: string, format: string = 'MM/DD/YYYY'): string {
    if (!isoDate) return ''
    const [year, month, day] = isoDate.split('-')
    return formatDateString(day, month, year, format)
  }

  return {
    parseDateString,
    formatDateString,
    isValidDate,
    getDaysArray,
    getMonthsArray,
    getYearsArray,
    toISOFormat,
    fromISOFormat,
  }
}
