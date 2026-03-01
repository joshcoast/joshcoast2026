/**
 * Turnstile interface definitions
 */
import type { TurnstileTheme } from './turnstile-type'

export interface TurnstileSettings {
  siteKey: string
  secretKey: string
  theme?: TurnstileTheme
  language?: string
}

export interface TurnstileConfig {
  siteKey: string
  theme?: TurnstileTheme
  language?: string
}

export interface WindowTurnstileConfig {
  enabled?: boolean | string | number
  provider?: string
  turnstile?: {
    siteKey: string
    type: string
    theme?: string
    language?: string
    configured?: boolean | string | number
    scriptUrl?: string
    size?: string
  }
}

// Turnstile widget options for rendering
export interface TurnstileWidgetOptions {
  sitekey: string // Required site key for Cloudflare Turnstile API
  callback?: (token: string) => void
  'expired-callback'?: () => void
  'error-callback'?: () => void
  'timeout-callback'?: () => void
  size?: 'normal' | 'compact' | 'flexible'
  theme?: TurnstileTheme
  language?: string
  tabindex?: number
  'response-field'?: boolean
  'response-field-name'?: string
}

// Turnstile service response types
export interface TurnstileResponse {
  success: boolean
  'error-codes'?: string[]
  challenge_ts?: string
  hostname?: string
  action?: string
  cdata?: string
}
