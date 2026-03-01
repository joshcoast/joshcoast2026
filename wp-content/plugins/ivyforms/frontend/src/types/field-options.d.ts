/**
 * Field Options Types
 *
 * Types for dynamic field option configuration system
 */

import type { Component } from 'vue'
import type { Field } from './field'

/**
 * Configuration for a field option section
 */
export interface FieldOptionConfig {
  /** Unique identifier for this option section */
  id: string

  /** Field types this option applies to (e.g., ['email', 'text']) */
  fieldTypes: string[]

  /** Display order (lower numbers appear first) */
  order: number

  /** Vue component to render for this option */
  component: Component

  /** Tab this option belongs to ('general' or 'advanced') */
  tab?: 'general' | 'advanced'

  /** Whether to show divider before this section */
  showDividerBefore?: boolean

  /** Whether to show divider after this section */
  showDividerAfter?: boolean

  /** Condition function to determine if option should be shown */
  condition?: (field: Field) => boolean
}

/**
 * Context passed to field option components
 */
export interface FieldOptionContext {
  /** The currently selected field */
  field: Field

  /** Function to update field properties */
  updateField: (key: string, value: unknown) => void

  /** Function to get translated labels */
  getLabel: (key: string) => string
}

/**
 * Registry of field option configurations
 */
export type FieldOptionRegistry = Record<string, FieldOptionConfig>
