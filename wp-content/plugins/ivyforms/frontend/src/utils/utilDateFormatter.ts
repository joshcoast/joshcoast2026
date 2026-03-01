import { format } from 'date-fns'
import { enGB, sr } from 'date-fns/locale'
import dayjs from 'dayjs'

function convertWPFormat(wpFormat: string, target: 'dateFns' | 'dayjs' = 'dateFns'): string {
  const map: Record<string, { dayjs?: string; dateFns?: string; all?: string }> = {
    Y: { dayjs: 'YYYY', dateFns: 'yyyy' },
    y: { dayjs: 'YY', dateFns: 'yy' },
    F: { all: 'MMMM' },
    M: { dayjs: 'MMM', dateFns: 'MMM' },
    m: { all: 'MM' },
    n: { all: 'M' },
    d: { dayjs: 'DD', dateFns: 'dd' },
    j: { dayjs: 'D', dateFns: 'd' },
    D: { dayjs: 'ddd', dateFns: 'eee' },
    l: { dayjs: 'dddd', dateFns: 'eeee' },
  }

  return wpFormat.replace(/\b[a-zA-Z]\b/g, (token) => {
    const formats = map[token]
    return formats?.[target] || formats?.all || token
  })
}

function formatWPDate(date: string | number | Date | null | undefined | boolean): string {
  // Handle null, undefined, or boolean values
  if (!date || typeof date === 'boolean') return ''

  let jsDate: Date

  if (typeof date === 'string') {
    if (date.trim() === '') return ''
    jsDate = new Date(date)
    if (isNaN(jsDate.getTime())) return ''
  } else if (typeof date === 'number') {
    if (date === 0) return ''
    jsDate = new Date(date)
    if (isNaN(jsDate.getTime())) return ''
  } else if (date instanceof Date) {
    jsDate = date
    if (isNaN(jsDate.getTime())) return ''
  } else {
    return ''
  }

  const wpFormat = window.wpIvyDateFormat?.dateFormat || 'dd MMM yyyy'
  const locale = window.wpIvyDateFormat?.locale === 'sr_RS' ? sr : enGB
  const dateFnsFormat = convertWPFormat(wpFormat, 'dateFns')

  try {
    return format(jsDate, dateFnsFormat, { locale })
  } catch {
    return ''
  }
}

function formatDateRangeForApi(dateValue: unknown): string[] {
  if (Array.isArray(dateValue)) {
    return dateValue
      .map((d) =>
        typeof d === 'string' || typeof d === 'number' || d instanceof Date || dayjs.isDayjs(d)
          ? dayjs(d).format('YYYY-MM-DD')
          : null,
      )
      .filter(Boolean) as string[]
  } else if (
    typeof dateValue === 'string' ||
    typeof dateValue === 'number' ||
    dateValue instanceof Date ||
    dayjs.isDayjs(dateValue)
  ) {
    return [dayjs(dateValue).format('YYYY-MM-DD')]
  }
  return []
}

export default {
  formatWPDate,
  convertWPFormat,
  formatDateRangeForApi,
}
