/**
 * reCAPTCHA interface definitions
 */
import type { RecaptchaType } from './recaptcha-type'

export interface RecaptchaSettings {
  type: RecaptchaType
  siteKey: string
  secretKey: string
  language?: string
}

export interface RecaptchaConfig {
  siteKey: string
  type: RecaptchaType
  theme?: 'light' | 'dark'
  language?: string
}

export interface WindowRecaptchaConfig {
  enabled?: boolean | string | number
  provider?: string
  recaptcha?: {
    siteKey: string
    type: string
    theme?: string
    language?: string
    configured?: boolean | string | number
    scriptUrl?: string
    size?: string
  }
}

// reCAPTCHA widget options for rendering
export interface RecaptchaWidgetOptions {
  sitekey: string // Required site key for Google reCAPTCHA API
  callback?: (token: string) => void
  'expired-callback'?: () => void
  'error-callback'?: () => void
  size?: 'compact' | 'normal' | 'invisible'
  theme?: 'light' | 'dark'
  tabindex?: number
}

// reCAPTCHA service response types
export interface RecaptchaResponse {
  success: boolean
  'error-codes'?: string[]
  challenge_ts?: string
  hostname?: string
  score?: number
  action?: string
}
