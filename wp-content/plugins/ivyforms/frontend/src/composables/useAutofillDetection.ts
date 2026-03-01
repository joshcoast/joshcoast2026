import { onMounted, onUnmounted, nextTick } from 'vue'

export interface AutofillField {
  id: string
  getValue: () => string
  setValue: (value: string) => void
  onClearError?: () => void
}

export interface AutofillDetectionOptions {
  fields: AutofillField[]
  pollingDuration?: number
  pollingInterval?: number
  enableMutationObserver?: boolean
  enableVisibilityChange?: boolean
  initialCheckDelay?: number
}

export function useAutofillDetection(options: AutofillDetectionOptions) {
  const {
    fields,
    pollingDuration = 5000,
    pollingInterval = 100,
    enableMutationObserver = true,
    enableVisibilityChange = true,
    initialCheckDelay = 200,
  } = options

  let autofillCleanup: (() => void) | null = null

  const checkForInitialAutofill = () => {
    // Check immediately for any pre-filled values on page load
    fields.forEach((field) => {
      const input = document.getElementById(field.id) as HTMLInputElement
      if (input && input.value && input.value !== field.getValue()) {
        field.setValue(input.value)
        field.onClearError?.()
      }
    })
  }

  const setupAutofillDetection = () => {
    // Clean up previous detection if exists
    if (autofillCleanup) {
      autofillCleanup()
      autofillCleanup = null
    }

    const cleanupFunctions: (() => void)[] = []

    // Initial check for pre-filled values (important for page load autofill)
    setTimeout(checkForInitialAutofill, initialCheckDelay)

    // Additional checks at different intervals for slow autofill
    setTimeout(checkForInitialAutofill, 500)
    setTimeout(checkForInitialAutofill, 1000)
    setTimeout(checkForInitialAutofill, 2000)

    // Strategy 1: MutationObserver for DOM changes
    let observer: MutationObserver | null = null
    if (enableMutationObserver) {
      observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
          if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
            const input = mutation.target as HTMLInputElement
            const field = fields.find((f) => f.id === input.id)
            if (field && input.value !== field.getValue()) {
              field.setValue(input.value)
              field.onClearError?.()
            }
          }
        })
      })
    }

    // Strategy 2: Direct DOM polling (enhanced)
    const checkForAutofillChanges = () => {
      fields.forEach((field) => {
        const input = document.getElementById(field.id) as HTMLInputElement
        if (input && input.value !== field.getValue()) {
          field.setValue(input.value)
          field.onClearError?.()
        }
      })
    }

    const intervalId = setInterval(checkForAutofillChanges, pollingInterval)
    const timeoutId = setTimeout(() => clearInterval(intervalId), pollingDuration)

    // Strategy 3: Multiple event listeners
    fields.forEach((field) => {
      const input = document.getElementById(field.id) as HTMLInputElement
      if (input) {
        // Observe this input for changes
        if (observer) {
          observer.observe(input, {
            attributes: true,
            attributeFilter: ['value'],
            subtree: false,
          })
        }

        // Add event listeners for various autofill scenarios
        const events = [
          'input',
          'change',
          'blur',
          'focus',
          'animationstart',
          'animationend',
          'transitionstart',
          'transitionend',
          'DOMAutoComplete',
          'autocompleteerror',
        ]

        const handleInputChange = () => {
          if (input.value !== field.getValue()) {
            field.setValue(input.value)
            field.onClearError?.()
          }
        }

        events.forEach((eventType) => {
          input.addEventListener(eventType, handleInputChange, { passive: true })
        })

        // Cleanup function for this input
        cleanupFunctions.push(() => {
          events.forEach((eventType) => {
            input.removeEventListener(eventType, handleInputChange)
          })
        })
      }
    })

    // Strategy 4: Page visibility change detection
    let handleVisibilityChange: (() => void) | null = null
    if (enableVisibilityChange) {
      handleVisibilityChange = () => {
        if (!document.hidden) {
          setTimeout(checkForAutofillChanges, 100)
        }
      }
      document.addEventListener('visibilitychange', handleVisibilityChange)
    }

    // Strategy 5: Focus/blur on window (for browser autofill detection)
    const handleWindowFocus = () => {
      setTimeout(checkForAutofillChanges, 100)
    }

    window.addEventListener('focus', handleWindowFocus, { passive: true })
    cleanupFunctions.push(() => {
      window.removeEventListener('focus', handleWindowFocus)
    })

    // Combine all cleanup functions
    autofillCleanup = () => {
      clearInterval(intervalId)
      clearTimeout(timeoutId)
      observer?.disconnect()
      if (handleVisibilityChange) {
        document.removeEventListener('visibilitychange', handleVisibilityChange)
      }
      cleanupFunctions.forEach((cleanup) => cleanup())
    }
  }

  const restartDetection = () => {
    // Wait for DOM to be ready
    nextTick(() => {
      setupAutofillDetection()
    })
  }

  // Setup autofill detection on mount
  onMounted(() => {
    // Multiple setup attempts to catch different timing scenarios
    setTimeout(setupAutofillDetection, 50)
    setTimeout(restartDetection, 200)
    setTimeout(restartDetection, 500)
  })

  // Cleanup on unmount
  onUnmounted(() => {
    if (autofillCleanup) {
      autofillCleanup()
    }
  })

  return {
    setupAutofillDetection,
    restartDetection,
    checkForInitialAutofill,
    cleanup: () => {
      if (autofillCleanup) {
        autofillCleanup()
      }
    },
  }
}
