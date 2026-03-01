import { defineStore } from 'pinia'

export const useGlobalState = defineStore('globalState', {
  state: () => ({
    currentPage: '',
    isFullScreenMode: false,
    isFullScreenModeInitialized: false,
    pageTitle: '',
  }),
  actions: {
    setCurrentPage(page: string) {
      this.currentPage = page
    },
    setFullScreenMode(isFullScreen: boolean) {
      this.isFullScreenMode = isFullScreen
      this.isFullScreenModeInitialized = true
    },
    setPageTitle(title: string) {
      this.pageTitle = title
    },
  },
})
