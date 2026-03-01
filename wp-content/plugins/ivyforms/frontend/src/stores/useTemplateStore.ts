import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useLabels } from '@/composables/useLabels'
import IvyMessage from '@/views/_components/message/ivyMessage.ts'
import { useApiClient } from '@/composables/useApiClient.ts'

interface FieldData {
  id: string
  type: string
  label: string
  placeholder?: string
  required: boolean
  validation?: Record<string, unknown>
  settings?: Record<string, unknown>
}

interface FormSettings {
  success_message?: string
  [key: string]: unknown
}

interface IntegrationSettings {
  wpdatatables: {
    enabled: boolean
  }
  [key: string]: unknown
}

interface FormData {
  name: string
  description: string
  status: string
  published?: boolean
  showTitle?: boolean
  showDescription?: boolean
  storeEntries?: boolean
  integrationSettings?: IntegrationSettings
  fields: FieldData[]
  settings: FormSettings
}

interface Template {
  id: string
  name: string
  description: string
  category: string
  subcategory?: string
  is_pro: boolean
  screenshot: string
  form_data: FormData
}

interface TemplateCategory {
  name: string
  description: string
}

export const useTemplateStore = defineStore('ivyformsTemplate', () => {
  // State
  const templates = ref<Template[]>([])
  const categories = ref<Record<string, TemplateCategory>>({})
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  // Composables
  const { getLabel } = useLabels()
  const { request } = useApiClient()

  // Actions
  const fetchTemplates = async (): Promise<void> => {
    isLoading.value = true
    error.value = null

    try {
      const { data, error: apiError } = await request('templates', {
        method: 'GET',
      })

      if (apiError) {
        throw new Error(apiError)
      }

      if (data.data.success) {
        // Use the associative array from backend, preserve IDs
        templates.value = Object.values(data.data.data.templates)
        categories.value = data.data.data.categories
      } else {
        throw new Error(getLabel('failed_to_fetch_template'))
      }
    } catch (err) {
      error.value = err instanceof Error ? err.message : getLabel('failed_to_fetch_template')
      console.error(getLabel('template_fetch_error'), err)
      IvyMessage({
        message: `${getLabel('failed_to_fetch_template')} ${error.value}`,
        type: 'error',
      })
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const getTemplate = async (templateId: string): Promise<Template | null> => {
    try {
      const { data, error: apiError } = await request(`template/${templateId}`, {
        method: 'GET',
      })

      if (apiError) {
        throw new Error(apiError)
      }

      if (data.data.success) {
        return data.data.data
      } else {
        throw new Error(getLabel('failed_to_fetch_template'))
      }
    } catch (err) {
      error.value = err instanceof Error ? err.message : getLabel('failed_to_fetch_template')
      console.error(getLabel('template_fetch_error'), err)
      IvyMessage({
        message: `${getLabel('failed_to_fetch_template')} ${error.value}`,
        type: 'error',
      })
      return null
    }
  }

  const getTemplatesByCategory = (categoryId: string): Template[] => {
    return templates.value.filter((template) => template.category === categoryId)
  }

  const searchTemplates = (query: string): Template[] => {
    if (!query.trim()) {
      return templates.value
    }

    const searchTerm = query.toLowerCase()
    return templates.value.filter(
      (template) =>
        template.name.toLowerCase().includes(searchTerm) ||
        template.description.toLowerCase().includes(searchTerm),
    )
  }

  const clearError = (): void => {
    error.value = null
  }

  const resetStore = (): void => {
    templates.value = []
    categories.value = {}
    isLoading.value = false
    error.value = null
  }

  // Getters
  const getTemplateById = (id: string): Template | undefined => {
    return templates.value.find((template) => template.id === id)
  }

  const getAvailableCategories = (): TemplateCategory[] => {
    return Object.values(categories.value)
  }

  const getTemplatesCount = (): number => {
    return templates.value.length
  }

  const getProTemplatesCount = (): number => {
    return templates.value.filter((template) => template.is_pro).length
  }

  const getFreeTemplatesCount = (): number => {
    return templates.value.filter((template) => !template.is_pro).length
  }

  return {
    // State
    templates,
    categories,
    isLoading,
    error,
    fetchTemplates,
    getTemplate,
    getTemplatesByCategory,
    searchTemplates,
    clearError,
    resetStore,
    getTemplateById,
    getAvailableCategories,
    getTemplatesCount,
    getProTemplatesCount,
    getFreeTemplatesCount,
  }
})
