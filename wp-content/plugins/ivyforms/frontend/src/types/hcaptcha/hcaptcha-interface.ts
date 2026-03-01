import type { HCaptchaType } from './hcaptcha-type'

/**
 * hCaptcha settings interface
 */
export interface HCaptchaSettings {
  type: HCaptchaType
  siteKey: string
  secretKey: string
}

/**
 * hCaptcha configuration interface for frontend
 */
export interface HCaptchaConfig {
  siteKey: string
  type: HCaptchaType
}

/**
 * Window configuration for hCaptcha
 */
export interface WindowHCaptchaConfig {
  enabled?: boolean | string | number
  provider?: string
  hcaptcha?: {
    siteKey: string
    type: string
    configured?: boolean | string | number
    scriptUrl?: string
  }
}

/**
 * hCaptcha widget options interface
 */
export interface HCaptchaWidgetOptions {
  sitekey: string
  size?: 'normal' | 'compact' | 'invisible'
  tabindex?: number
  callback?: (token: string) => void
  'expired-callback'?: () => void
  'error-callback'?: () => void
}
