/**
 * hCaptcha Service for Frontend Integration
 * Handles hCaptcha integration
 */

import type { HCaptchaWidgetOptions } from '@/types/hcaptcha/hcaptcha-interface'
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

export interface HCaptchaInstance {
  render: (container: string | HTMLElement, options: HCaptchaWidgetOptions) => string
  execute: (container?: string | HTMLElement) => void
  reset: (container?: string | HTMLElement) => void
  remove: (container?: string | HTMLElement) => void
  getResponse: (container?: string | HTMLElement) => string | undefined
}

declare global {
  interface Window {
    hcaptcha?: HCaptchaInstance
    onHCaptchaLoad?: () => void
  }
}

export class HCaptchaService {
  private static instance: HCaptchaService
  private siteKey: string | null = null
  private widgetId: string | null = null
  private scriptLoaded = false
  private language: string = 'auto'

  static getInstance(): HCaptchaService {
    if (!HCaptchaService.instance) {
      HCaptchaService.instance = new HCaptchaService()
    }
    return HCaptchaService.instance
  }

  /**
   * Initialize hCaptcha with configuration
   * Note: Assumes hCaptcha script is already loaded by ShortcodeService
   */
  async init(siteKey: string, language?: string): Promise<void> {
    this.siteKey = siteKey
    this.language = language || 'auto'

    return new Promise((resolve) => {
      if (window.hcaptcha) {
        this.scriptLoaded = true
        resolve()
      } else {
        // Set up the onload callback if it doesn't exist
        if (!window.onHCaptchaLoad) {
          window.onHCaptchaLoad = () => {
            this.scriptLoaded = true
            resolve()
          }
        }

        // If script isn't loaded yet, wait a bit and try again
        setTimeout(() => {
          if (window.hcaptcha) {
            this.scriptLoaded = true
            resolve()
          } else {
            console.warn(getLabel('hcaptcha_script_not_found'))
            resolve() // Resolve anyway to avoid hanging
          }
        }, 100)
      }
    })
  }

  /**
   * Render hCaptcha widget
   */
  async render(
    container: string | HTMLElement,
    options: Partial<HCaptchaWidgetOptions> = {},
  ): Promise<string> {
    if (!window.hcaptcha || !this.siteKey) {
      throw new Error(getLabel('hcaptcha_not_initialized'))
    }

    const renderOptions: HCaptchaWidgetOptions = {
      sitekey: this.siteKey,
      callback: options.callback,
      'expired-callback': options['expired-callback'],
      'error-callback': options['error-callback'],
      size: options.size || 'normal',
    }

    return new Promise((resolve) => {
      this.widgetId = window.hcaptcha!.render(container, renderOptions)
      resolve(this.widgetId)
    })
  }

  /**
   * Execute hCaptcha (mainly for invisible mode)
   */
  async execute(): Promise<string> {
    if (!window.hcaptcha) {
      throw new Error(getLabel('hcaptcha_not_initialized'))
    }

    if (!this.widgetId) {
      throw new Error(getLabel('hcaptcha_widget_not_initialized'))
    }

    return new Promise((resolve, reject) => {
      try {
        window.hcaptcha!.execute(this.widgetId!)

        // Set timeout to reject if no response in 30 seconds
        const timeout = setTimeout(() => {
          reject(new Error(getLabel('hcaptcha_execution_timeout')))
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
   * Get response from hCaptcha widget
   */
  getResponse(): string {
    if (!window.hcaptcha || !this.widgetId) {
      return ''
    }

    return window.hcaptcha.getResponse(this.widgetId) || ''
  }

  /**
   * Reset hCaptcha widget
   */
  reset(): void {
    if (window.hcaptcha && this.widgetId) {
      window.hcaptcha.reset(this.widgetId)
    }
  }

  /**
   * Remove hCaptcha widget
   */
  remove(): void {
    if (window.hcaptcha && this.widgetId) {
      window.hcaptcha.remove(this.widgetId)
      this.widgetId = null
    }
  }

  /**
   * Get current widget ID
   */
  getWidgetId(): string | null {
    return this.widgetId
  }

  /**
   * Check if hCaptcha is ready
   */
  isReady(): boolean {
    return this.scriptLoaded && !!window.hcaptcha
  }

  /**
   * Get current site key
   */
  getSiteKey(): string | null {
    return this.siteKey
  }
}

// Export singleton instance
export const hcaptchaService = HCaptchaService.getInstance()
