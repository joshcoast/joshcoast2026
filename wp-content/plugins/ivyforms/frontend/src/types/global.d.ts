/**
 * IvyForms Lite Global Type Definitions
 *
 * This file defines all global Window interface extensions used throughout
 * the IvyForms Lite plugin. Pro plugin extends these types in its own global.d.ts.
 *
 * @see ivyforms-pro/frontend/src/types/global.d.ts - Pro extensions
 */

import type * as Vue from 'vue'
import type { Component } from 'vue'
import type {
  IvyFormAPI,
  IvyFormAPIHooks as APIHooks,
  IvyFormAPIFields as APIFields,
} from '@/composables/IvyFormAPI'
import type { Field } from '@/types/field'

/**
 * WordPress-specific global objects provided by backend
 */
export interface WpIvyApiSettings {
  root: string
  nonce: string
  namespace?: string
}

export interface WpIvySettings {
  general: {
    fullscreen: boolean
    version: string
  }
}

export interface WpIvyUrls {
  pluginURL: string
  siteURL: string
}

export interface WpIvyDateFormat {
  dateFormat: string
  timeFormat: string
  dateTimeFormat: string
  locale: string
}

/**
 * WordPress media library interface
 */
export interface WpMedia {
  (): {
    open: () => void
    on: (event: string, callback: (...args: unknown[]) => void) => void
    state: () => {
      get: (key: string) => {
        first: () => {
          toJSON: () => Record<string, unknown>
        }
      }
    }
  }
}

/**
 * Form data structure for public shortcode rendering
 */
export interface FormData {
  id: string | number
  name?: string
  description?: string
  counter?: number
  fields?: Field[]
  confirmations?: unknown[]
}

export interface ShortcodeData {
  id?: string | number
  form?: FormData
  fields?: Field[]
  counter: string | number
  confirmations?: unknown[]
  nonce?: string
}

/**
 * Component registry that can be used as function or object
 */
export interface ComponentRegistry {
  (name?: string, component?: Component): Component | ComponentRegistry
  [key: string]:
    | Component
    | ((name?: string, component?: Component) => Component | ComponentRegistry)
}

/**
 * Pro features interface
 */
export interface IvyProFeatures {
  loaded: boolean
  loading: boolean
  plan: string
  upgradeMap: Record<string, string>
  groups: Record<string, string[]>
  license: Record<string, unknown> | null
  features: Record<string, boolean>
  hasFeature: (slug: string) => boolean
  fetch: (opts?: Record<string, unknown>) => Promise<void>
  refresh: () => Promise<void>
}

/**
 * Main IvyForms global object structure
 */
export interface IvyFormsGlobal {
  api: IvyFormAPI
  hooks: APIHooks
  fields: APIFields
  components?: ComponentRegistry
  formBuilder?: unknown // Avoid circular dependency - type is complex Pinia store
  pro?: IvyProFeatures
  Vue?: typeof Vue
  labels?: Record<string, string>
  getLabel?: (key: string) => string
  IvyMessage?: (options: {
    type?: 'success' | 'warning' | 'error' | 'info' | 'default'
    message: string
    duration?: number
  }) => void
  useApiClient?: () => {
    request: <T = unknown>(
      endpoint: string,
      options?: Record<string, unknown>,
      config?: { useNamespace?: boolean; namespace?: string },
    ) => Promise<{ data: T | null; error: unknown; status: number }>
    apiClient: unknown
    root?: string
    namespace?: string
    nonce?: string
  }
  registerComponent?: (name: string, component: Component) => void
  hasProFeature?: (slug: string) => boolean
  onProReady?: (cb: (pro: IvyProFeatures) => void) => void
  _proReadyQueue?: Array<(pro: IvyProFeatures) => void>
  types?: Record<string, unknown>
  _router?: unknown // Router instance for Pro plugins to add routes dynamically (using unknown to avoid type conflicts)
  adminUrl?: string // WordPress admin URL for generating user edit links
  isGutenbergEditor?: boolean // Editor-only runtime flag: set when public bundle is loaded inside Gutenberg editor
}

/**
 * Vue globals compatibility interface
 */
export interface VueGlobals {
  computed?: <T>(fn: () => T) => { value: T }
  [key: string]: unknown
}

/**
 * Global Window interface extension for IvyForms Lite
 */
declare global {
  interface Window {
    // WordPress globals
    wp?: {
      media: WpMedia
      [key: string]: unknown
    }
    wpIvyApiSettings?: WpIvyApiSettings
    wpIvySettings?: WpIvySettings
    wpIvyUrls?: WpIvyUrls
    wpIvyDateFormat?: WpIvyDateFormat
    wpIvyFormDataList?: ShortcodeData[]
    wpIvyLabels?: Record<string, string>

    // IvyForms globals
    IvyForms?: IvyFormsGlobal
    IvyFormsApi?: IvyFormAPI
    Vue?: VueGlobals
    ivyFormsInitialize?: () => void

    // Pro plugin detection (set by Pro if installed)
    // Note: IvyFormsPro interface is defined in Pro's global.d.ts
    IvyFormsProScripts?: Record<string, string>
    IvyFormsProReady?: boolean
  }
}

// Make this a module
export {}
