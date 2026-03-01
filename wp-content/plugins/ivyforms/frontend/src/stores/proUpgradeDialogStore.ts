/**
 * Pro Upgrade Dialog Store
 *
 * Centralized store for managing Pro feature upgrade dialogs
 */

import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { Ref } from 'vue'

type ButtonType = 'primary' | 'secondary' | 'tertiary'

export type ProUpgradeDialogData = {
  title: string
  subtitle?: string
  iconName?: string
  buttons?: {
    close?: {
      type?: ButtonType
      text: string
      function?: () => void
    }
    confirm?: {
      type?: ButtonType
      text: string
      function?: () => void
    }
  }
}

export const useProUpgradeDialogStore = defineStore('proUpgradeDialog', () => {
  const dialogVisible: Ref<boolean> = ref(false)
  const dialogData: Ref<ProUpgradeDialogData> = ref({
    title: '',
    subtitle: '',
  })

  const showDialog = (data: ProUpgradeDialogData) => {
    dialogData.value = data
    dialogVisible.value = true
  }

  const hideDialog = () => {
    dialogVisible.value = false
  }

  return {
    dialogVisible,
    dialogData,
    showDialog,
    hideDialog,
  }
})
