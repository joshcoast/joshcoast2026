/**
 * reCAPTCHA Service for Frontend Integration
 * Handles Google reCAPTCHA v2, v3, and invisible integration
 */

import type { RecaptchaConfig, RecaptchaWidgetOptions } from '@/types/recaptcha/recaptcha-interface'
import { useLabels } from '@/composables/useLabels'

/**
 * Helper function to get labels safely (defers useLabels call until runtime)
 */
function getLabel(key: string): string {
  try {
    const { getLabel: getLabelFn } = useLabels()
    return getLabelFn(key)
  } catch {
    return key // Fallback if Pinia not ready
  }
}

export interface RecaptchaInstance {
  render: (container: string | HTMLElement, options: RecaptchaWidgetOptions) => number
  execute: (siteKeyOrWidgetId: string | number, options?: { action?: string }) => Promise<string>
  getResponse: (widgetId?: number) => string
  reset: (widgetId?: number) => void
  ready: (callback: () => void) => void
}

declare global {
  interface Window {
    grecaptcha?: RecaptchaInstance
    onRecaptchaLoad?: () => void
  }
}

export class RecaptchaService {
  private static instance: RecaptchaService
  private config: RecaptchaConfig | null = null
  private widgetId: number | null = null
  private scriptLoaded = false
  private executeResolver: ((token: string) => void) | null = null

  static getInstance(): RecaptchaService {
    if (!RecaptchaService.instance) {
      RecaptchaService.instance = new RecaptchaService()
    }
    return RecaptchaService.instance
  }

  /**
   * Initialize reCAPTCHA with configuration
   * Note: Assumes Google reCAPTCHA script is already loaded by ShortcodeService
   */
  async init(config: RecaptchaConfig): Promise<void> {
    this.config = config

    return new Promise((resolve) => {
      if (window.grecaptcha) {
        window.grecaptcha.ready(() => {
          this.scriptLoaded = true
          resolve()
        })
      } else {
        // If script isn't loaded yet, wait a bit and try again
        setTimeout(() => {
          if (window.grecaptcha) {
            window.grecaptcha.ready(() => {
              this.scriptLoaded = true
              resolve()
            })
          } else {
            console.warn(getLabel('recaptcha_script_not_found'))
            resolve() // Resolve anyway to avoid hanging
          }
        }, 100)
      }
    })
  }

  /**
   * Render reCAPTCHA widget
   */
  async render(
    container: string | HTMLElement,
    options: Partial<RecaptchaWidgetOptions> = {},
  ): Promise<number> {
    if (!window.grecaptcha || !this.config) {
      throw new Error(getLabel('recaptcha_not_initialized'))
    }

    const renderOptions: RecaptchaWidgetOptions = {
      sitekey: this.config.siteKey, // This is the critical missing piece!
      callback: options.callback,
      'expired-callback': options['expired-callback'],
      'error-callback': options['error-callback'],
      size: this.config.type === 'invisible' ? 'invisible' : options.size || 'normal',
      theme: this.config.theme || 'light',
      tabindex: options.tabindex,
    }

    // For invisible reCAPTCHA, we need to wrap the callback to handle execute promise resolution
    if (this.config.type === 'invisible' && options.callback) {
      const originalCallback = options.callback
      renderOptions.callback = (token: string) => {
        originalCallback(token)
        // Also resolve any pending execute promise
        if (this.executeResolver) {
          this.executeResolver(token)
          this.executeResolver = null
        }
      }
    }

    return new Promise((resolve) => {
      window.grecaptcha!.ready(() => {
        this.widgetId = window.grecaptcha!.render(container, renderOptions)
        resolve(this.widgetId)
      })
    })
  }

  /**
   * Execute reCAPTCHA (for v3 and invisible)
   */
  async execute(action: string = 'submit'): Promise<string> {
    if (!window.grecaptcha || !this.config) {
      throw new Error(getLabel('recaptcha_not_initialized'))
    }

    return new Promise((resolve, reject) => {
      window.grecaptcha!.ready(async () => {
        try {
          if (this.config!.type === 'v3') {
            const token = await window.grecaptcha!.execute(this.config!.siteKey, { action })
            resolve(token)
          } else if (this.config!.type === 'invisible') {
            // For invisible v2, execute the widget and wait for callback
            if (this.widgetId === null) {
              reject(new Error(getLabel('recaptcha_widget_not_initialized')))
              return
            }

            // Set up promise resolver for callback
            this.executeResolver = resolve

            // Execute the invisible reCAPTCHA
            await window.grecaptcha!.execute(this.widgetId)

            // Set timeout to reject if no response in 30 seconds
            setTimeout(() => {
              if (this.executeResolver) {
                this.executeResolver = null
                reject(new Error(getLabel('recaptcha_execution_timeout')))
              }
            }, 30000)
          } else {
            reject(new Error(getLabel('recaptcha_execute_not_supported')))
          }
        } catch (error) {
          reject(error)
        }
      })
    })
  }

  /**
   * Get response from reCAPTCHA widget
   */
  getResponse(): string {
    if (!window.grecaptcha) {
      return ''
    }

    return window.grecaptcha.getResponse(this.widgetId || 0)
  }

  /**
   * Reset reCAPTCHA widget
   */
  reset(): void {
    if (window.grecaptcha && this.widgetId !== null) {
      window.grecaptcha.reset(this.widgetId)
    }
  }

  /**
   * Get current widget ID
   */
  getWidgetId(): number | null {
    return this.widgetId
  }

  /**
   * Check if reCAPTCHA is ready
   */
  isReady(): boolean {
    return this.scriptLoaded && !!window.grecaptcha
  }

  /**
   * Get current configuration
   */
  getConfig(): RecaptchaConfig | null {
    return this.config
  }
}

// Export singleton instance
export const recaptchaService = RecaptchaService.getInstance()
