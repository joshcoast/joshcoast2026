// Utility functions for number formatting and parsing

export type NumberFormat = 'us_decimal' | 'us_int' | 'eu_decimal' | 'eu_int' | ''

export const formatWithThousandsDynamic = (n: number, thousandsSep: string) => {
  const parts = n.toString().split('.')
  let intPart = parts[0]
  const decPart = parts[1] ?? ''
  intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, thousandsSep)
  return { intPart, decPart }
}

export const formatNumber = (
  value: number | null | undefined,
  numberFormat: NumberFormat = '',
): string => {
  if (value === null || value === undefined || isNaN(value)) return ''
  const num = value
  switch (numberFormat) {
    case 'us_decimal': {
      const { intPart, decPart } = formatWithThousandsDynamic(Math.abs(num), ',')
      return (num < 0 ? '-' : '') + (decPart ? `${intPart}.${decPart}` : intPart)
    }
    case 'us_int': {
      const { intPart } = formatWithThousandsDynamic(Math.trunc(Math.abs(num)), ',')
      return (num < 0 ? '-' : '') + intPart
    }
    case 'eu_decimal': {
      const { intPart, decPart } = formatWithThousandsDynamic(Math.abs(num), '.')
      return (num < 0 ? '-' : '') + (decPart ? `${intPart},${decPart}` : intPart)
    }
    case 'eu_int': {
      const { intPart } = formatWithThousandsDynamic(Math.trunc(Math.abs(num)), '.')
      return (num < 0 ? '-' : '') + intPart
    }
    default:
      return String(num)
  }
}

export const sanitizeRaw = (raw: string, numberFormat: NumberFormat = ''): string => {
  if (!raw) return ''
  let v = raw.replace(/[^0-9,.-]/g, '')
  v = v.replace(/-/g, (m, idx) => (idx === 0 ? '-' : ''))
  if (v.length > 1 && v[0] !== '-') {
    v = v.replace(/-/g, '')
  }
  const fmt = numberFormat
  const isEU = fmt === 'eu_decimal' || fmt === 'eu_int'
  const allowDecimal = fmt === 'eu_decimal' || fmt === 'us_decimal' || fmt === ''
  if (isEU) {
    let seenDecimal = false
    let out = ''
    for (const ch of v) {
      if (ch === ',') {
        if (!allowDecimal || seenDecimal) {
          continue
        }
        seenDecimal = true
        out += ch
      } else if (ch === '.') {
        // skip thousands dot
      } else {
        out += ch
      }
    }
    v = out
  } else {
    let seenDecimal = false
    let out = ''
    for (const ch of v) {
      if (ch === '.') {
        if (!allowDecimal || seenDecimal) {
          continue
        }
        seenDecimal = true
        out += ch
      } else if (ch === ',') {
        // skip thousands comma
      } else {
        out += ch
      }
    }
    v = out
  }
  return v
}

export const parseToNumber = (
  raw: string,
  numberFormat: NumberFormat = '',
  min: number = -Infinity,
  max: number = Infinity,
): number | null => {
  if (!raw) return null
  const fmt = numberFormat
  let v = raw.trim()
  if (fmt === 'eu_decimal' || fmt === 'eu_int') {
    v = v.replace(/\./g, '')
    if (fmt === 'eu_decimal') v = v.replace(',', '.')
    else v = v.split(',')[0]
  } else if (fmt === 'us_decimal' || fmt === 'us_int') {
    v = v.replace(/,/g, '')
    if (fmt === 'us_int') v = v.split('.')[0]
  }
  v = v.replace(/[^0-9.+-]/g, '')
  if (v === '-' || v === '' || v === '.' || v === '-.') return null
  const num = Number(v)
  if (isNaN(num)) return null
  return Math.min(Math.max(num, min), max)
}

export const formatNumberForField = (raw: unknown, fmt: NumberFormat): string => {
  if (raw === null || raw === undefined || raw === '') return ''
  let num: number | null = null
  if (typeof raw === 'number') {
    num = isNaN(raw) ? null : raw
  } else if (typeof raw === 'string') {
    const s = raw.trim()
    if (s === '') return ''
    if (/^[0-9]+$/.test(s)) {
      num = parseFloat(s)
    } else if (/^[0-9.,-]+$/.test(s)) {
      let normalized = s
      const lastComma = s.lastIndexOf(',')
      const lastDot = s.lastIndexOf('.')
      if (lastComma !== -1 && lastDot !== -1) {
        if (lastComma > lastDot) {
          normalized = s.replace(/\./g, '').replace(',', '.')
        } else {
          normalized = s.replace(/,/g, '')
        }
      } else if (lastComma !== -1) {
        normalized = s
          .replace(/\./g, '')
          .replace(/,/g, '.')
          .replace(/(\.)(?=.*\.)/g, '')
      } else if (lastDot !== -1) {
        const parts = s.split('.')
        if (parts.length > 2) {
          const dec = parts.pop() as string
          const intPart = parts.join('')
          normalized = intPart + '.' + dec
        } else {
          normalized = s
        }
        normalized = normalized.replace(/,/g, '')
      }
      const parsed = parseFloat(normalized)
      num = isNaN(parsed) ? null : parsed
    } else {
      return String(raw)
    }
  }
  if (num === null) return ''
  const absParts = (n: number, decimals = 2) => {
    const str = n.toFixed(decimals)
    const [i, d] = str.split('.')
    return { i, d: d || '' }
  }
  const addThousands = (intPart: string, sep: string) =>
    intPart.replace(/\B(?=(\d{3})+(?!\d))/g, sep)
  switch (fmt) {
    case 'us_decimal': {
      const { i, d } = absParts(Math.abs(num), 2)
      const intF = addThousands(i, ',')
      return (num < 0 ? '-' : '') + `${intF}.${d}`
    }
    case 'us_int': {
      const t = Math.trunc(Math.abs(num))
      const intF = addThousands(t.toString(), ',')
      return (num < 0 ? '-' : '') + intF
    }
    case 'eu_decimal': {
      const { i, d } = absParts(Math.abs(num), 2)
      const intF = addThousands(i, '.')
      return (num < 0 ? '-' : '') + `${intF},${d}`
    }
    case 'eu_int': {
      const t = Math.trunc(Math.abs(num))
      const intF = addThousands(t.toString(), '.')
      return (num < 0 ? '-' : '') + intF
    }
    default:
      // Show up to 2 decimals if needed, otherwise integer
      return num % 1 === 0 ? num.toString() : num.toFixed(2)
  }
}
