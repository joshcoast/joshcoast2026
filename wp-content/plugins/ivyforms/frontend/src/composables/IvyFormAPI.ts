import * as Vue from 'vue'
import { markRaw, ref } from 'vue'
import type { Component } from 'vue'
import type { FieldOptionConfig } from '@/types/field-options'

export type ComponentType = Component

export interface IvyFormAPIHooks {
  actions: Record<string, Array<(...args: unknown[]) => void>>
  filters: Record<string, Array<(...args: unknown[]) => unknown>>
  fieldInit?: Array<(field: unknown, context: unknown) => void>
  beforeSaveFields?: Array<(fields: unknown[]) => unknown[]>
  getTemplatePreviewField?: Array<(fieldType: string) => unknown | null>

  addAction: (hook: string, cb: (...args: unknown[]) => void) => void
  doAction: (hook: string, ...args: unknown[]) => void

  addFilter: (hook: string, cb: (...args: unknown[]) => unknown) => void
  applyFilters: <T = unknown>(hook: string, value: T, ...args: unknown[]) => T

  registerFieldInit: (callback: (field: unknown, context: unknown) => void) => void
  registerBeforeSaveFields: (callback: (fields: unknown[]) => unknown[]) => void

  triggerUpdate: () => void
  updateCounter: { value: number }
}

export interface IvyFormAPIFields {
  admin: Record<string, ComponentType>
  public: Record<string, ComponentType>
  registerAdmin: (name: string, component: ComponentType) => void
  registerPublic: (name: string, component: ComponentType) => void
}

export interface IvyFormAPIFieldOptions {
  registry: Record<string, FieldOptionConfig>
  register: (config: FieldOptionConfig) => void
  unregister: (id: string) => void
  getForFieldType: (fieldType: string, tab?: 'general' | 'advanced') => FieldOptionConfig[]
}

export interface IvyFormAPI {
  fields: IvyFormAPIFields
  hooks: IvyFormAPIHooks
  fieldOptions: IvyFormAPIFieldOptions
}

const hooksUpdateCounter = ref(0)
const fieldOptionsRegistry: Record<string, FieldOptionConfig> = {}

const api: IvyFormAPI = {
  fields: {
    admin: {},
    public: {},
    registerAdmin(name, component) {
      this.admin[name] = markRaw(component)
    },
    registerPublic(name, component) {
      this.public[name] = markRaw(component)
    },
  },

  hooks: {
    actions: {},
    filters: {},
    fieldInit: [],
    beforeSaveFields: [],
    updateCounter: hooksUpdateCounter,
    addAction(hook, cb) {
      if (!this.actions[hook]) this.actions[hook] = []
      this.actions[hook].push(cb)
    },
    doAction(hook, ...args) {
      if (this.actions[hook]) this.actions[hook].forEach((cb) => cb(...args))
    },
    addFilter(hook, cb) {
      if (!this.filters[hook]) this.filters[hook] = []
      this.filters[hook].push(cb)
      this.triggerUpdate()
    },
    applyFilters(hook: string, value: unknown, ...args: unknown[]) {
      if (!this.filters[hook]) return value
      return this.filters[hook].reduce(
        (v: unknown, cb: (...args: unknown[]) => unknown) => cb(v, ...args),
        value,
      )
    },
    registerFieldInit(callback) {
      if (!this.fieldInit) this.fieldInit = []
      this.fieldInit.push(callback)
    },
    registerBeforeSaveFields(callback) {
      if (!this.beforeSaveFields) this.beforeSaveFields = []
      this.beforeSaveFields.push(callback)
    },
    triggerUpdate() {
      hooksUpdateCounter.value++
    },
  },

  fieldOptions: {
    registry: fieldOptionsRegistry,
    register(config: FieldOptionConfig) {
      fieldOptionsRegistry[config.id] = markRaw(config) as FieldOptionConfig
      api.hooks.triggerUpdate()
    },
    unregister(id: string) {
      delete fieldOptionsRegistry[id]
      api.hooks.triggerUpdate()
    },
    getForFieldType(fieldType: string, tab?: 'general' | 'advanced'): FieldOptionConfig[] {
      return Object.values(fieldOptionsRegistry)
        .filter((config) => {
          // Filter by field type
          if (!config.fieldTypes.includes(fieldType)) return false
          // Filter by tab if specified
          if (tab && config.tab && config.tab !== tab) return false
          return true
        })
        .sort((a, b) => a.order - b.order)
    },
  },
}

// Ready event helpers
export const IVY_READY_EVENT = 'ivyforms:ready' as const
export function dispatchIvyReady(detail?: Record<string, unknown>) {
  const evt = new CustomEvent(IVY_READY_EVENT, { detail })
  window.dispatchEvent(evt)
  document.dispatchEvent(evt)
}

export function installIvyGlobals() {
  const w = window
  if (!w.Vue) w.Vue = Vue
  w.IvyFormsApi = api

  return api
}

export default api
