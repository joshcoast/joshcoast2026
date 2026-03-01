/**
 * Composable function for time field logic
 */
export function useTimeField() {
  function parseTimeString(str: string) {
    if (!str) return { hours: '', minutes: '', amPm: '' }
    const fullMatch = str.match(/^(\d{2}):?(\d{2})?(?:\s*(AM|PM))?$/)
    const minutesAmPmMatch = str.match(/^:(\d{2})\s*(AM|PM)$/)
    const hoursMatch = str.match(/^\d{2}$/)
    const minutesMatch = str.match(/^:(\d{2})$/)
    const ampmMatch = str.match(/^(AM|PM)$/)
    if (fullMatch) {
      return {
        hours: fullMatch[1] || '',
        minutes: fullMatch[2] || '',
        amPm: fullMatch[3] || '',
      }
    }
    if (minutesAmPmMatch) {
      return {
        hours: '',
        minutes: minutesAmPmMatch[1],
        amPm: minutesAmPmMatch[2],
      }
    }
    if (hoursMatch) {
      return {
        hours: str,
        minutes: '',
        amPm: '',
      }
    }
    if (minutesMatch) {
      return {
        hours: '',
        minutes: minutesMatch[1],
        amPm: '',
      }
    }
    if (ampmMatch) {
      return {
        hours: '',
        minutes: '',
        amPm: ampmMatch[1],
      }
    }
    return { hours: '', minutes: '', amPm: '' }
  }

  function formatTimeString(hours: string, minutes: string, amPm: string) {
    if (!hours && !minutes && amPm) return amPm
    if (!hours && minutes && amPm) return `:${minutes} ${amPm}`
    if (hours && minutes) return amPm ? `${hours}:${minutes} ${amPm}` : `${hours}:${minutes}`
    if (hours) return hours
    if (minutes) return `:${minutes}`
    return ''
  }

  function getAmPmFromHours(hours: string): 'AM' | 'PM' {
    const h = parseInt(hours, 10)
    return h >= 12 ? 'PM' : 'AM'
  }

  function to24hFormat(time: string): string {
    if (!time) return ''
    const match = time.match(/^(\d{1,2}):(\d{2})\s*(AM|PM)?$/i)
    if (!match) return time
    let hours = parseInt(match[1], 10)
    const minutes = match[2]
    const ampm = match[3]?.toUpperCase()
    if (ampm === 'PM' && hours < 12) hours += 12
    if (ampm === 'AM' && hours === 12) hours = 0
    return `${hours.toString().padStart(2, '0')}:${minutes}`
  }

  return {
    parseTimeString,
    formatTimeString,
    getAmPmFromHours,
    to24hFormat,
  }
}
