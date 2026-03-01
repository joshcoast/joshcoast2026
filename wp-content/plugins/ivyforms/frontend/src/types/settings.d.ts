import type { RecaptchaSettings } from './recaptcha/recaptcha-interface'
import type { TurnstileSettings } from './turnstile/turnstile-interface'
import type { HCaptchaSettings } from './hcaptcha/hcaptcha-interface'

export interface SecuritySettings {
  recaptcha: RecaptchaSettings | null
  turnstile: TurnstileSettings | null
  hcaptcha: HCaptchaSettings | null
}

// Future general settings interfaces (commented out for now)
// export interface SiteSettings {
//   siteName?: string
//   adminEmail?: string
//   timezone?: string
// }

// export interface FormDefaults {
//   defaultTheme?: string
//   enableAutoSave?: boolean
//   enableProgressBar?: boolean
// }

export interface GeneralSettings {
  wcagBackend?: boolean
  delete_on_uninstall?: boolean
  // Future extensions:
  // site_settings: SiteSettings | null
  // form_defaults: FormDefaults | null
  [key: string]: unknown
}

export interface IntegrationSettings {
  enabled: boolean
  [key: string]: unknown
}

export interface IntegrationsSettings {
  wpdatatables?: IntegrationSettings | null
  // Future extensions:
  // mailchimp?: IntegrationSettings | null
  [key: string]: IntegrationSettings | null | undefined
}

export interface ProLicenseInfo {
  status: string // 'valid' | 'inactive' | 'expired' | etc.
  plan: string // 'lite' | 'essentials' | 'growth' | 'agency'
  key_masked?: string // Masked license key for display
}

export interface ProSettings {
  license: ProLicenseInfo | null
}

export interface AllSettings {
  security: SecuritySettings
  general: GeneralSettings
  integrations: IntegrationsSettings
  pro?: ProSettings // Optional - only present when Pro is installed
}
