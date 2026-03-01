import { defineStore } from 'pinia'

// Note: Window.wpIvyLabels is defined in src/types/global.d.ts
// No need to duplicate declaration here

export const useLabelsStore = defineStore('labels', {
  state: () => ({
    labels: {} as Record<string, string>,
  }),
  actions: {
    initialize() {
      // Get labels from the global variable injected by WordPress
      if (typeof window.wpIvyLabels !== 'undefined') {
        this.labels = window.wpIvyLabels
      }
    },
    getLabel(key: string, fallback: string = ''): string {
      return this.labels[key] || fallback
    },
  },
  getters: {
    allLabels: (state) => state.labels,
  },
})
