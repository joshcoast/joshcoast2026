/**
 * Turnstile Service for Frontend Integration
 * Handles Cloudflare Turnstile integration
 */

import type { TurnstileWidgetOptions } from '@/types/turnstile/turnstile-interface'
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

export interface TurnstileInstance {
  render: (container: string | HTMLElement, options: TurnstileWidgetOptions) => string
  execute: (container?: string | HTMLElement) => void
  reset: (container?: string | HTMLElement) => void
  remove: (container?: string | HTMLElement) => void
  getResponse: (container?: string | HTMLElement) => string | undefined
  isExpired: (container?: string | HTMLElement) => boolean
}

declare global {
  interface Window {
    turnstile?: TurnstileInstance
    onTurnstileLoad?: () => void
  }
}

export class TurnstileService {
  private static instance: TurnstileService
  private siteKey: string | null = null
  private widgetId: string | null = null
  private scriptLoaded = false
  private language: string = 'auto'

  static getInstance(): TurnstileService {
    if (!TurnstileService.instance) {
      TurnstileService.instance = new TurnstileService()
    }
    return TurnstileService.instance
  }

  /**
   * Initialize Turnstile with configuration
   * Note: Assumes Cloudflare Turnstile script is already loaded by ShortcodeService
   */
  async init(siteKey: string, language?: string): Promise<void> {
    this.siteKey = siteKey
    this.language = language || 'auto'

    return new Promise((resolve) => {
      if (window.turnstile) {
        this.scriptLoaded = true
        resolve()
      } else {
        // If script isn't loaded yet, wait a bit and try again
        setTimeout(() => {
          if (window.turnstile) {
            this.scriptLoaded = true
            resolve()
          } else {
            console.warn(getLabel('turnstile_script_not_found'))
            resolve() // Resolve anyway to avoid hanging
          }
        }, 100)
      }
    })
  }

  /**
   * Render Turnstile widget
   */
  async render(
    container: string | HTMLElement,
    options: Partial<TurnstileWidgetOptions> = {},
  ): Promise<string> {
    if (!window.turnstile || !this.siteKey) {
      throw new Error(getLabel('turnstile_not_initialized'))
    }

    const renderOptions: TurnstileWidgetOptions = {
      sitekey: this.siteKey,
      callback: options.callback,
      'expired-callback': options['expired-callback'],
      'error-callback': options['error-callback'],
      'timeout-callback': options['timeout-callback'],
      size: options.size || 'normal',
      theme: options.theme || 'auto',
      language: this.language,
      tabindex: options.tabindex,
      'response-field': options['response-field'],
      'response-field-name': options['response-field-name'],
    }

    return new Promise((resolve) => {
      this.widgetId = window.turnstile!.render(container, renderOptions)
      resolve(this.widgetId)
    })
  }

  /**
   * Execute Turnstile
   */
  async execute(): Promise<string> {
    if (!window.turnstile) {
      throw new Error(getLabel('turnstile_not_initialized'))
    }

    if (!this.widgetId) {
      throw new Error(getLabel('turnstile_widget_not_initialized'))
    }

    return new Promise((resolve, reject) => {
      try {
        window.turnstile!.execute(this.widgetId!)

        // Set timeout to reject if no response in 30 seconds
        const timeout = setTimeout(() => {
          reject(new Error(getLabel('turnstile_execution_timeout')))
        }, 30000)

        // Poll for response
        const checkResponse = () => {
          const response = this.getResponse()
          if (response) {
            clearTimeout(timeout)
            resolve(response)
          } else {
            setTimeout(checkResponse, 100)
          }
        }
        checkResponse()
      } catch (error) {
        reject(error)
      }
    })
  }

  /**
   * Get response from Turnstile widget
   */
  getResponse(): string {
    if (!window.turnstile || !this.widgetId) {
      return ''
    }

    return window.turnstile.getResponse(this.widgetId) || ''
  }

  /**
   * Reset Turnstile widget
   */
  reset(): void {
    if (window.turnstile && this.widgetId) {
      window.turnstile.reset(this.widgetId)
    }
  }

  /**
   * Remove Turnstile widget
   */
  remove(): void {
    if (window.turnstile && this.widgetId) {
      window.turnstile.remove(this.widgetId)
      this.widgetId = null
    }
  }

  /**
   * Check if token is expired
   */
  isExpired(): boolean {
    if (!window.turnstile || !this.widgetId) {
      return true
    }

    return window.turnstile.isExpired(this.widgetId)
  }

  /**
   * Get current widget ID
   */
  getWidgetId(): string | null {
    return this.widgetId
  }

  /**
   * Check if Turnstile is ready
   */
  isReady(): boolean {
    return this.scriptLoaded && !!window.turnstile
  }

  /**
   * Get current site key
   */
  getSiteKey(): string | null {
    return this.siteKey
  }
}

// Export singleton instance
export const turnstileService = TurnstileService.getInstance()
