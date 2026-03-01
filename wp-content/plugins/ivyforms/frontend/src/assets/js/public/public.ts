/* eslint no-unused-vars: "off" */
import { createApp } from 'vue'
import type { Component } from 'vue'
import type { ShortcodeData, IvyFormsGlobal } from '@/types/global'
import type { IvyFormAPI } from '@/composables/IvyFormAPI'
//import * as Vue from 'vue'
import { createPinia } from 'pinia'
import FormRender from '../../../views/public/FormRender.vue'
import '@/assets/scss/main.scss'
import 'element-plus/dist/index.css'
import { useLabelsStore } from '@/stores/labels'
import api, { installIvyGlobals, dispatchIvyReady } from '@/composables/IvyFormAPI'
import { registerLiteComponents } from '@/composables/registerLiteComponents'
import * as LiteTypes from '@/types/index'

/**
 * Try to parse JSON, handling HTML-encoded strings
 */
function tryParseJson(raw: string): Record<string, unknown> | null {
  if (!raw || typeof raw !== 'string') return null
  try {
    return JSON.parse(raw)
  } catch {
    try {
      // Decode HTML entities
      let decoded = raw.replace(/&quot;/g, '"')
      decoded = decoded.replace(/&amp;/g, '&')
      decoded = decoded.replace(/&#039;/g, "'")
      decoded = decoded.replace(/&lt;/g, '<')
      decoded = decoded.replace(/&gt;/g, '>')
      return JSON.parse(decoded)
    } catch {
      return null
    }
  }
}

/**
 * Extract form data from DOM elements (for Gutenberg SSR)
 * Looks for data-ivyforms-data attribute on wrapper or embedded JSON scripts
 */
function extractFormDataFromDOM(counter: string): ShortcodeData | null {
  // Look for the form app element
  const appEl = document.getElementById(`ivyforms-app-${counter}`)
  if (!appEl) return null

  // Look for wrapper with data-ivyforms-data attribute (parent or ancestor)
  const wrapper = appEl.closest('[data-ivyforms-data]') as HTMLElement | null
  if (wrapper) {
    const raw = wrapper.getAttribute('data-ivyforms-data')
    if (raw) {
      const parsed = tryParseJson(raw)
      if (parsed && parsed[counter]) {
        return parsed[counter] as ShortcodeData
      }
    }
  }

  // Look for embedded JSON script with matching counter
  const script = document.querySelector(
    `script.ivyforms-ssr-data[data-ivyforms-counter="${counter}"]`,
  ) as HTMLScriptElement | null
  if (script) {
    const txt = script.textContent || script.innerText || ''
    const parsed = tryParseJson(txt)
    if (parsed) {
      return parsed as unknown as ShortcodeData
    }
  }

  return null
}

/**
 * Initialize a single form instance
 */
function initializeFormInstance(formData: Record<string, unknown>) {
  const el = document.querySelector(`#ivyforms-app-${formData.counter}`)
  if (el) {
    // Check if already mounted (avoid double mounting)
    if (el.querySelector('[data-v-app]') || el.hasAttribute('data-v-app')) {
      return
    }

    // Apply initial visibility of title/description from wrapper attributes
    try {
      const wrapper = el.closest('.ivyforms-gutenberg-block') as HTMLElement | null
      if (wrapper) {
        const showTitle = wrapper.getAttribute('data-show-title') !== 'false'
        const showDescription = wrapper.getAttribute('data-show-description') !== 'false'
        if (!showTitle) {
          const titleEl = el.querySelector('.ivyforms-form-title') as HTMLElement | null
          if (titleEl) titleEl.style.display = 'none'
        }
        if (!showDescription) {
          const descEl = el.querySelector('.ivyforms-form-description') as HTMLElement | null
          if (descEl) descEl.style.display = 'none'
        }
      }
    } catch {
      // ignore DOM measurement errors
    }

    const app = createApp(FormRender, { formData })
    const pinia = createPinia()
    app.use(pinia)

    // Initialize the labels store with WordPress data BEFORE mounting
    const labelsStore = useLabelsStore(pinia)
    labelsStore.initialize()

    // Expose IvyFormsApi and dispatch readiness for public runtime as well
    ;(function exposeGlobals() {
      installIvyGlobals()
      const w = window as Window & { IvyForms?: Partial<IvyFormsGlobal> }

      // Initialize IvyForms with api, hooks, and fields if not already present
      w.IvyForms =
        w.IvyForms || ({ api, hooks: api.hooks, fields: api.fields } as unknown as IvyFormsGlobal)

      // Assign typed API reference
      w.IvyForms.api = api as unknown as IvyFormAPI

      // Make components available globally for Pro plugin access (matching admin pattern)
      // Create a callable components registry:
      // - Use as function:   w.IvyForms.components('MyComp', Comp)
      // - Or as object map:  w.IvyForms.components.MyComp
      type ComponentRegistry = {
        (name?: string, component?: Component): Component | ComponentRegistry
        [key: string]: Component | unknown
      }
      const componentsRegistry = ((name?: string, component?: Component) => {
        if (name && component) {
          ;(componentsRegistry as Record<string, Component>)[name] = component
          return component
        }
        return componentsRegistry
      }) as ComponentRegistry

      // Register all Lite components for Pro plugin access (no global registration for public)
      registerLiteComponents(componentsRegistry)

      w.IvyForms.components = componentsRegistry
      w.IvyForms.registerComponent = (name: string, component: Component) =>
        componentsRegistry(name, component)
      // Expose shared types for Pro plugin and other extenders
      w.IvyForms.types = LiteTypes
      // w.IvyForms.hooks = api.hooks // Direct access to hooks for easier Pro integration
      // w.IvyForms.fields = api.fields // Direct access to field registration
      // // Expose Vue for pro plugins with guaranteed compatibility
      // w.IvyForms.Vue = Vue
      // w.Vue = w.Vue || Vue // Global fallback for compatibility with other plugins
      // Only initialize/use pro features if pro plugin is present
      if (w.IvyForms.pro) {
        // You can add public-side pro logic here if needed
      }
    })()

    app.mount(el)

    // Signal readiness for any public extenders waiting on Lite
    dispatchIvyReady({ scope: 'public', counter: formData.counter })
  }
}

/**
 * Initialize all forms from wpIvyFormDataList
 */
function initializeAllForms() {
  if (typeof window !== 'undefined' && window.wpIvyFormDataList) {
    // Convert object to array if needed (handles both array and object formats)
    const formDataList: ShortcodeData[] = Array.isArray(window.wpIvyFormDataList)
      ? (window.wpIvyFormDataList as ShortcodeData[])
      : Object.values(window.wpIvyFormDataList as Record<string, ShortcodeData>)

    formDataList.forEach((formData) => {
      if (formData && typeof formData === 'object' && formData.counter !== undefined) {
        initializeFormInstance(formData as unknown as Record<string, unknown>)
      }
    })
  }
}

/**
 * Initialize forms by scanning DOM for embedded data (for Gutenberg SSR)
 * This is needed for existing blocks that render before/without wpIvyFormDataList
 */
function initializeFormsFromDOM() {
  // Find all form app containers in the DOM
  const formContainers = document.querySelectorAll('[id^="ivyforms-app-"]')

  formContainers.forEach((container) => {
    const counter = container.id.replace('ivyforms-app-', '')

    // Skip if already mounted
    if (container.querySelector('[data-v-app]') || container.hasAttribute('data-v-app')) {
      return
    }

    // Try to extract form data from DOM
    const formData = extractFormDataFromDOM(counter)
    if (formData) {
      // Store in wpIvyFormDataList for consistency
      if (!window.wpIvyFormDataList) {
        ;(
          window as unknown as { wpIvyFormDataList: Record<string, ShortcodeData> }
        ).wpIvyFormDataList = {}
      }
      ;(window.wpIvyFormDataList as unknown as Record<string, ShortcodeData>)[counter] = formData
      initializeFormInstance(formData as unknown as Record<string, unknown>)
    }
  })
}

// Expose initialization function globally for Gutenberg block preview only when in editor
if (typeof window !== 'undefined' && window.IvyForms?.isGutenbergEditor) {
  window.ivyFormsInitialize = initializeAllForms
}

// Initialize forms on page load
initializeAllForms()

// For Gutenberg editor: also scan DOM for embedded data (handles existing blocks)
// and retry after a delay to catch async-rendered content
if (typeof window !== 'undefined' && window.IvyForms?.isGutenbergEditor) {
  // Immediate scan for already-rendered forms
  initializeFormsFromDOM()

  // Retry scan after short delays to catch ServerSideRender async responses
  // This handles existing blocks that render shortly after page load
  const retryDelays = [100, 300, 500, 1000, 2000]
  retryDelays.forEach((delay) => {
    setTimeout(() => {
      initializeFormsFromDOM()
    }, delay)
  })
}

// Watch for new forms being added to the DOM (for Gutenberg editor preview)
if (
  typeof window !== 'undefined' &&
  typeof MutationObserver !== 'undefined' &&
  window.IvyForms?.isGutenbergEditor
) {
  const observer = new MutationObserver((mutations) => {
    const newCounters = new Set<string | number>()

    mutations.forEach((mutation) => {
      mutation.addedNodes.forEach((node) => {
        if (!(node instanceof HTMLElement)) {
          return
        }

        // If the added node itself is a form container, extract the counter
        if (node.id && node.id.startsWith('ivyforms-app-')) {
          const counter = node.id.replace('ivyforms-app-', '')
          newCounters.add(counter)
          return
        }

        // Otherwise, look for any descendant form containers
        const descendant = node.querySelector('[id^="ivyforms-app-"]') as HTMLElement | null
        if (descendant && descendant.id) {
          const counter = descendant.id.replace('ivyforms-app-', '')
          newCounters.add(counter)
        }
      })
    })

    if (newCounters.size > 0) {
      // Initialize each newly added form instance only
      newCounters.forEach((counter) => {
        // small delay to ensure the DOM nodes are fully parsed
        setTimeout(() => {
          // First, try to extract form data from DOM (for Gutenberg SSR where data is embedded)
          const formData = extractFormDataFromDOM(counter as string)
          if (formData) {
            // Store in wpIvyFormDataList for consistency
            if (!window.wpIvyFormDataList) {
              ;(
                window as unknown as { wpIvyFormDataList: Record<string, ShortcodeData> }
              ).wpIvyFormDataList = {}
            }
            ;(window.wpIvyFormDataList as unknown as Record<string, ShortcodeData>)[counter] =
              formData
            initializeFormInstance(formData as unknown as Record<string, unknown>)
            return
          }

          // Fallback: Look up from wpIvyFormDataList (for regular page loads)
          const rawFormDataList = window.wpIvyFormDataList as unknown
          const formDataList = rawFormDataList as Record<string, ShortcodeData> | undefined
          if (formDataList && formDataList[counter]) {
            initializeFormInstance(formDataList[counter] as unknown as Record<string, unknown>)
          } else {
            // Retry a few times
            let retries = 0
            const maxRetries = 5
            const retryInterval = setInterval(() => {
              // Try DOM extraction again
              const retryFormData = extractFormDataFromDOM(counter as string)
              if (retryFormData) {
                clearInterval(retryInterval)
                if (!window.wpIvyFormDataList) {
                  ;(
                    window as unknown as { wpIvyFormDataList: Record<string, ShortcodeData> }
                  ).wpIvyFormDataList = {}
                }
                ;(window.wpIvyFormDataList as unknown as Record<string, ShortcodeData>)[counter] =
                  retryFormData
                initializeFormInstance(retryFormData as unknown as Record<string, unknown>)
                return
              }

              const rawUpdatedFormDataList = window.wpIvyFormDataList as unknown
              const updatedFormDataList = rawUpdatedFormDataList as
                | Record<string, ShortcodeData>
                | undefined
              if (updatedFormDataList && updatedFormDataList[counter]) {
                clearInterval(retryInterval)
                initializeFormInstance(
                  updatedFormDataList[counter] as unknown as Record<string, unknown>,
                )
              } else if (++retries >= maxRetries) {
                clearInterval(retryInterval)
              }
            }, 100)
          }
        }, 50)
      })
    }
  })

  // Observe the entire document for changes
  observer.observe(document.body, {
    childList: true,
    subtree: true,
  })
}
