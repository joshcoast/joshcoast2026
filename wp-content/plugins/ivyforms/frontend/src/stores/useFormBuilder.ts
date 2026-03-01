import { defineStore } from 'pinia'
import { ref, computed, nextTick, watch } from 'vue'
import { useLabels } from '@/composables/useLabels'
import IvyMessage from '@/views/_components/message/ivyMessage.ts'
import { useRouter } from 'vue-router'
import { useUnsavedChangesStore } from '@/stores/useUnsavedChangesStore'
import { useApiClient } from '@/composables/useApiClient'
import { useGlobalState } from '@/stores/useGlobalState'
import type { Field } from '@/types/field'
import { normalizeFieldsForBuilder, denormalizeFieldsForApi } from '@/utils/fieldsNormalization'
import { buildNewField } from '@/utils/addFieldUtil.ts'
import { duplicateFieldForStore } from '@/utils/fieldUtils'
import { useFieldWidth } from '@/composables/useFieldWidth'
import type { DropZone } from '@/types/grid-layout'
import { getErrorMessage } from '@/utils/errorHandling'

export const useFormBuilder = defineStore('formBuilderData', () => {
  const formId = ref<string | null>(null)
  const isEditing = ref(false)
  const fields = ref<Field[]>([])
  const counterFields = ref<number>(0)
  const selectedField = ref<Field | null>(null)
  const activeTab = ref<string>('addField')
  const description = ref<string | null>(null)
  const name = ref<string | null>('Blank form')
  const published = ref(true)
  const showTitle = ref(false)
  const showDescription = ref(false)
  const storeEntries = ref(false)
  // Flag to indicate the form was loaded specifically for viewing an entry
  const loadedForEntry = ref<boolean>(false)
  const integrationSettings = ref<{
    wpdatatables: { enabled: boolean }
    [key: string]: Record<string, unknown>
  }>({
    wpdatatables: { enabled: true },
  })
  const isEditingSettingGeneral = ref(false)
  const confirmationId = ref<string | null>(null)
  const router = useRouter()
  const isFormLoading = ref(true)
  const isDraggingFromPanel = ref(false)

  const { getLabel } = useLabels()
  const { request } = useApiClient()

  // Submit button settings
  const submitButtonSettings = ref({
    label: getLabel('submit'),
    position: 'default' as 'default' | 'left' | 'center' | 'right',
  })
  const isSubmitButtonSelected = ref(false)
  const unsavedChangesStore = useUnsavedChangesStore()

  // Flag to suppress change tracking during load/reset operations
  let suppressDirtyTracking = false

  /**
   * Mark the form builder as having unsaved changes
   * Called automatically by watchers when state changes
   */
  const markDirty = () => {
    if (!suppressDirtyTracking && isEditing.value) {
      unsavedChangesStore.markDirty('formBuilder')
    }
  }

  /**
   * Mark the form builder as clean (no unsaved changes)
   * Called after successful save operations
   */
  const markClean = () => {
    unsavedChangesStore.markClean('formBuilder')
  }

  // Watch all tracked state for changes and mark dirty
  watch(
    [name, description, published, showTitle, showDescription, storeEntries],
    () => markDirty(),
    { deep: false },
  )

  watch(integrationSettings, () => markDirty(), { deep: true })
  watch(submitButtonSettings, () => markDirty(), { deep: true })
  watch(fields, () => markDirty(), { deep: true })

  const selectField = (fieldIndex: number) => {
    const field = fields.value.find((field) => field.fieldIndex === fieldIndex) || null

    // If selecting the same field, still ensure the tab is set to options
    if (selectedField.value?.fieldIndex === fieldIndex) {
      activeTab.value = 'options'
      return
    }

    selectedField.value = field
    isSubmitButtonSelected.value = false
    activeTab.value = 'options'
  }

  const selectSubmitButton = () => {
    selectedField.value = null
    isSubmitButtonSelected.value = true
    activeTab.value = 'options'
  }

  const deselectSubmitButton = () => {
    isSubmitButtonSelected.value = false
  }

  const updateSubmitButtonSettings = (key: 'label' | 'position', value: string) => {
    if (
      key === 'position' &&
      (value === 'default' || value === 'left' || value === 'center' || value === 'right')
    ) {
      submitButtonSettings.value.position = value
    } else if (key === 'label') {
      submitButtonSettings.value.label = value
    }
  }

  function deselectField(event: Event) {
    event.stopPropagation()
    const clickedElement = event.target as HTMLElement
    const fieldElements = document.querySelectorAll('.ivyforms-form-builder__list-group__item')
    const submitButtonElement = document.querySelector(
      '.ivyforms-form-builder__submit-button-wrapper',
    )
    let clickedInsideField = false

    fieldElements.forEach((el) => {
      if (el.contains(clickedElement)) {
        clickedInsideField = true
      }
    })

    // Check if clicked inside submit button
    if (submitButtonElement && submitButtonElement.contains(clickedElement)) {
      clickedInsideField = true
    }

    if (!clickedInsideField) {
      selectedField.value = null
      isSubmitButtonSelected.value = false
    }
  }

  const isSelected = (fieldIndex: number) => {
    return selectedField.value?.fieldIndex === fieldIndex
  }

  // Only update the selectedField object and the canonical entry in fields[]
  const updateSelectedField = (key: string, value: unknown) => {
    const sel = selectedField.value as Record<string, unknown> | null
    if (sel) {
      sel[key] = value
    }
    const index = fields.value.findIndex((f) => f.fieldIndex === selectedField.value?.fieldIndex)
    if (index !== -1) {
      const target = fields.value[index] as Record<string, unknown>
      if (target !== sel) {
        target[key] = value
      }
    }
  }

  function addField(fieldData: Partial<Field> & { type: string }) {
    const newField = buildNewField(fieldData, counterFields.value, getLabel)

    // If rowIndex, columnIndex, and width are already specified (e.g., from click-to-add), use them
    if (
      fieldData.rowIndex !== undefined &&
      fieldData.columnIndex !== undefined &&
      fieldData.width !== undefined
    ) {
      newField.rowIndex = fieldData.rowIndex
      newField.columnIndex = fieldData.columnIndex
      newField.width = fieldData.width
    } else {
      // Auto-calculate position: add to last row if space available, otherwise create new row
      const lastRow = Math.max(0, ...fields.value.map((f) => f.rowIndex ?? 0))
      const fieldsInLastRow = fields.value.filter((f) => (f.rowIndex ?? 0) === lastRow)

      let rowIndex = lastRow
      let columnIndex = fieldsInLastRow.length

      // If last row is full (5 fields), create a new row
      if (fieldsInLastRow.length >= 5) {
        rowIndex = lastRow + 1
        columnIndex = 0
      }

      newField.rowIndex = rowIndex
      newField.columnIndex = columnIndex
      newField.width = 100 // Will be auto-calculated by useFieldWidth
    }

    fields.value.push(newField)
    updateFieldPositions()

    // Scroll to the newly added field after DOM update
    nextTick(() => {
      const fieldElement = document.querySelector(
        `[data-field-index="${newField.fieldIndex}"]`,
      ) as HTMLElement
      if (fieldElement) {
        fieldElement.scrollIntoView({
          behavior: 'smooth',
          block: 'center',
        })
      }
    }).catch(() => {
      // Ignore nextTick errors
    })
  }

  const updateFieldPositions = () => {
    fields.value.forEach((field, fieldIndex) => {
      field.position = fieldIndex + 1
    })
  }

  // Grid layout utilities
  const {
    groupFieldsByRow,
    distributeWidthsEvenly,
    distributeWidthsProportionally,
    canAddToRow,
    getAutoWidth,
  } = useFieldWidth()

  // Computed property for grouped fields
  const fieldsByRow = computed(() => groupFieldsByRow(fields.value))

  /**
   * Move a field to a specific position in the grid
   * @param fieldIndex - The fieldIndex of the field to move
   * @param dropZone - The target drop zone (rowIndex, columnIndex, position)
   */
  const moveFieldToPosition = (fieldIndex: number, dropZone: DropZone) => {
    const field = fields.value.find((f) => f.fieldIndex === fieldIndex)
    if (!field) return

    const targetRowIndex = dropZone.rowIndex
    const targetColumnIndex = dropZone.columnIndex
    const position = dropZone.position

    // Handle different drop positions
    if (position === 'above') {
      // Insert in a new row above
      fields.value.forEach((f) => {
        if ((f.rowIndex ?? 0) >= targetRowIndex) {
          f.rowIndex = (f.rowIndex ?? 0) + 1
        }
      })
      field.rowIndex = targetRowIndex
      field.columnIndex = 0
    } else if (position === 'below') {
      // Insert in a new row below
      fields.value.forEach((f) => {
        if ((f.rowIndex ?? 0) > targetRowIndex) {
          f.rowIndex = (f.rowIndex ?? 0) + 1
        }
      })
      field.rowIndex = targetRowIndex + 1
      field.columnIndex = 0
    } else if (position === 'left') {
      // Insert to the left in the same row
      const fieldsInRow = fields.value.filter((f) => (f.rowIndex ?? 0) === targetRowIndex)
      if (fieldsInRow.length >= 5) {
        return
      }
      // Shift fields to the right
      fields.value.forEach((f) => {
        if ((f.rowIndex ?? 0) === targetRowIndex && (f.columnIndex ?? 0) >= targetColumnIndex) {
          f.columnIndex = (f.columnIndex ?? 0) + 1
        }
      })
      field.rowIndex = targetRowIndex
      field.columnIndex = targetColumnIndex
    } else if (position === 'right') {
      // Insert to the right in the same row
      const fieldsInRow = fields.value.filter((f) => (f.rowIndex ?? 0) === targetRowIndex)
      if (fieldsInRow.length >= 5) {
        return
      }
      // Shift fields to the right
      fields.value.forEach((f) => {
        if ((f.rowIndex ?? 0) === targetRowIndex && (f.columnIndex ?? 0) > targetColumnIndex) {
          f.columnIndex = (f.columnIndex ?? 0) + 1
        }
      })
      field.rowIndex = targetRowIndex
      field.columnIndex = targetColumnIndex + 1
    }

    // Recalculate widths for affected rows
    recalculateRowWidths(field.rowIndex ?? 0)
    if (position === 'above' || position === 'below') {
      // Also recalculate the original row if it still exists
      const originalRow = fields.value.filter((f) => (f.rowIndex ?? 0) === targetRowIndex)
      if (originalRow.length > 0) {
        recalculateRowWidths(targetRowIndex)
      }
    }

    updateFieldPositions()
  }

  /**
   * Recalculate widths for all fields in a row
   * Uses proportional distribution to maintain relative field sizes when adding/removing fields
   * @param rowIndex - The row to recalculate
   * @param isAddingField - Whether a new field is being added (affects distribution strategy)
   */
  const recalculateRowWidths = (rowIndex: number, isAddingField: boolean = false) => {
    const fieldsInRow = fields.value.filter((f) => (f.rowIndex ?? 0) === rowIndex)

    if (fieldsInRow.length === 0) return

    // Check if fields have custom widths (not standard auto widths)
    const hasCustomWidths = fieldsInRow.some((f) => {
      const expectedAutoWidth = getAutoWidth(fieldsInRow.length)
      const actualWidth = f.width ?? 100
      // Allow 1% tolerance for rounding
      return Math.abs(actualWidth - expectedAutoWidth) > 1
    })

    let updatedFields: Field[]

    if (isAddingField && hasCustomWidths && fieldsInRow.length > 1) {
      // When adding to a row with custom widths, use proportional distribution
      // This maintains the relative proportions of existing fields
      updatedFields = distributeWidthsProportionally(fieldsInRow, 0)
    } else {
      // Otherwise, distribute evenly
      updatedFields = distributeWidthsEvenly(fieldsInRow)
    }

    updatedFields.forEach((updatedField) => {
      const field = fields.value.find((f) => f.fieldIndex === updatedField.fieldIndex)
      if (field) {
        field.width = updatedField.width
      }
    })
  }

  const increaseCounter = () => {
    counterFields.value++
    return counterFields.value
  }

  /**
   * Apply beforeSaveFields hooks to allow Pro or other extensions to modify fields
   * @param fieldsArray - The fields array to process
   * @returns Processed fields array after all hooks have been applied
   */
  const applyBeforeSaveFieldsHooks = (fieldsArray: Field[]): Field[] => {
    let fieldsToSave = [...fieldsArray]

    if (typeof window !== 'undefined') {
      const w = window as Window & {
        IvyForms?: {
          hooks?: {
            beforeSaveFields?: Array<(fields: Field[]) => Field[]>
          }
        }
      }
      const hooks = w.IvyForms?.hooks?.beforeSaveFields || []
      hooks.forEach((hook) => {
        try {
          fieldsToSave = hook(fieldsToSave) as Field[]
        } catch (error) {
          console.error(getLabel('error_in_before_save_field_hook'), error)
        }
      })
    }

    return fieldsToSave
  }

  // Load existing form data if formId is provided
  const loadForm = async (id: string) => {
    isFormLoading.value = true
    const globalState = useGlobalState()

    // Suppress dirty tracking during load
    suppressDirtyTracking = true

    try {
      const { data, error, status } = await request(`form/${id}/`, {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
        },
      })
      if (!error && status === 200 && data?.data?.data) {
        formId.value = id
        isEditing.value = true
        isEditingSettingGeneral.value = true
        const responseData = data.data.data
        name.value = responseData.name ?? getLabel('blank_form')
        description.value = responseData.description || ''
        published.value = responseData.published || false
        showTitle.value = responseData.showTitle || false
        showDescription.value = responseData.showDescription || false
        storeEntries.value = responseData.storeEntries

        // Load integration settings from the response
        if (responseData.integrationSettings) {
          integrationSettings.value = responseData.integrationSettings
        }

        // Load submit button settings from the response
        if (responseData.formActionButtons?.submitButtonSettings) {
          submitButtonSettings.value = responseData.formActionButtons.submitButtonSettings
        }

        confirmationId.value = responseData.confirmationId || null

        const rawFields = (responseData.fields || []) as Field[]

        fields.value = normalizeFieldsForBuilder(rawFields)

        // Sort fields by row and column for proper grid display
        fields.value.sort((a, b) => {
          const rowA = a.rowIndex ?? 0
          const rowB = b.rowIndex ?? 0
          if (rowA !== rowB) return rowA - rowB
          return (a.columnIndex ?? 0) - (b.columnIndex ?? 0)
        })

        if (name.value) {
          globalState.setPageTitle(name.value)
        }

        // Mark form builder as being edited and clean (just loaded)
        unsavedChangesStore.startEditing('formBuilder')
        markClean()

        counterFields.value = fields.value.length
          ? Math.max(...fields.value.map((f) => f.fieldIndex))
          : 0
      } else {
        const errorMessage = getErrorMessage(error)
        IvyMessage({
          message: `${getLabel('failed_to_tab_form')} ${errorMessage}`,
          type: 'error',
        })
        console.error(getLabel('failed_to_tab_form'), errorMessage)
      }
    } catch (error) {
      const errorMessage = getErrorMessage(error)
      IvyMessage({
        message: `${getLabel('error_loading_form')} ${errorMessage}`,
        type: 'error',
      })
      console.error(getLabel('error_loading_form'), error)
    } finally {
      isFormLoading.value = false
      // Wait for watchers to fire before re-enabling dirty tracking
      await nextTick()
      suppressDirtyTracking = false
    }
  }

  const updateForm = async () => {
    if (!formId.value) return

    if (!name.value) {
      name.value = `${getLabel('form')} #${formId.value}`
    }

    // Allow Pro or other extensions to modify fields before saving
    const fieldsToSave = applyBeforeSaveFieldsHooks(fields.value)

    const denormalizedFields = denormalizeFieldsForApi(fieldsToSave)

    const formData = {
      name: name.value ?? '',
      description: description.value || '',
      published: published.value || false,
      showTitle: showTitle.value || false,
      showDescription: showDescription.value || false,
      storeEntries: storeEntries.value,
      integrationSettings: integrationSettings.value,
      fields: denormalizedFields,
      formActionButtons: {
        submitButtonSettings: submitButtonSettings.value,
      },
    }

    try {
      const { error, status } = await request(`form/update/${formId.value}/`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        data: formData,
      })
      if (!error && status === 200) {
        IvyMessage({
          message: getLabel('form_updated'),
          type: 'success',
        })
        markClean()
      } else {
        const errorMessage = getErrorMessage(error)
        IvyMessage({
          message: `${getLabel('failed_to_update_form')} ${errorMessage}`,
          type: 'error',
        })
        console.error(getLabel('failed_to_update_form'), errorMessage)
      }
    } catch (error) {
      const errorMessage = getErrorMessage(error)
      IvyMessage({
        message: `${getLabel('error_updating_form')} ${errorMessage}`,
        type: 'error',
      })
      console.error(getLabel('error_updating_form'), error)
    }
  }

  // Save form using WordPress REST API
  const saveForm = async (options?: { template_id?: string }) => {
    // Allow Pro or other extensions to modify fields before saving
    const fieldsToSave = applyBeforeSaveFieldsHooks(fields.value)

    const denormalizedFields = denormalizeFieldsForApi(fieldsToSave)

    const formData = {
      name: name.value ?? '',
      description: description.value || '',
      showTitle: showTitle.value || false,
      published: published.value || false,
      showDescription: showDescription.value || false,
      storeEntries: storeEntries.value,
      integrationSettings: integrationSettings.value,
      fields: denormalizedFields,
      formActionButtons: {
        submitButtonSettings: submitButtonSettings.value,
      },
      ...(options?.template_id && { template_id: options.template_id }),
    }

    try {
      const { data, error, status } = await request('form/add/', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        data: formData,
      })
      if (!error && status === 200 && data?.data?.data?.id) {
        const responseData = data.data.data
        formId.value = responseData.id
        confirmationId.value = responseData.confirmationId
        isEditing.value = true
        isEditingSettingGeneral.value = true
        // Start editing tracking for the newly saved form
        unsavedChangesStore.startEditing('formBuilder')
        await router.push({ hash: `manage/${formId.value}` })
        IvyMessage({
          message: getLabel('form_saved'),
          type: 'success',
        })
        markClean()
      } else {
        const errorMessage = getErrorMessage(error)
        IvyMessage({
          message: `${getLabel('failed_to_save_form')} ${errorMessage}`,
          type: 'error',
        })
      }
    } catch (error) {
      const errorMessage = getErrorMessage(error)
      IvyMessage({
        message: `${getLabel('error_save_form')} ${errorMessage}`,
        type: 'error',
      })
    }
  }

  const saveFormSettings = async () => {
    // ...existing code...
    const fieldsToSave = applyBeforeSaveFieldsHooks(fields.value)

    const denormalizedFields = denormalizeFieldsForApi(fieldsToSave)

    const formData = {
      name: name.value ?? '',
      description: description.value || '',
      published: published.value || false,
      showTitle: showTitle.value || false,
      showDescription: showDescription.value || false,
      storeEntries: storeEntries.value,
      fields: denormalizedFields,
      formActionButtons: {
        submitButtonSettings: submitButtonSettings.value,
      },
    }
    try {
      const { data, error, status } = await request('form/add/', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        data: formData,
      })
      if (!error && status === 200 && data?.data?.data?.id) {
        formId.value = data.data.data.id
        isEditing.value = true
        isEditingSettingGeneral.value = true
        // Start editing tracking for the newly saved form
        unsavedChangesStore.startEditing('formBuilder')
        await router.push({ hash: `/settings/general/manage/${formId.value}` })
        IvyMessage({
          message: getLabel('form_saved'),
          type: 'success',
        })
        markClean()
      } else {
        const errorMessage = getErrorMessage(error)
        IvyMessage({
          message: `${getLabel('failed_to_save_form')} ${errorMessage}`,
          type: 'error',
        })
      }
    } catch (error) {
      const errorMessage = getErrorMessage(error)
      IvyMessage({
        message: `${getLabel('error_save_form')} ${errorMessage}`,
        type: 'error',
      })
    }
  }

  const moveFieldUp = (fieldIndex: number) => {
    const currentIndex = fields.value.findIndex((f) => f.fieldIndex === fieldIndex)
    if (currentIndex > 0) {
      const temp = fields.value[currentIndex]
      fields.value[currentIndex] = fields.value[currentIndex - 1]
      fields.value[currentIndex - 1] = temp
      updateFieldPositions()

      // Keep the field selected after moving
      if (selectedField.value?.fieldIndex === fieldIndex) {
        selectedField.value = temp
      }

      // Scroll the field into view if it moved off-screen
      setTimeout(() => {
        const fieldElement = document.querySelector(
          `[data-field-index="${fieldIndex}"]`,
        ) as HTMLElement
        if (fieldElement) {
          fieldElement.style.scrollMargin = '20px'
          fieldElement.scrollIntoView({ behavior: 'smooth', block: 'center' })
        }
      }, 100)
    }
  }

  const moveFieldDown = (fieldIndex: number) => {
    const currentIndex = fields.value.findIndex((f) => f.fieldIndex === fieldIndex)
    if (currentIndex < fields.value.length - 1) {
      const temp = fields.value[currentIndex]
      fields.value[currentIndex] = fields.value[currentIndex + 1]
      fields.value[currentIndex + 1] = temp
      updateFieldPositions()

      // Keep the field selected after moving
      if (selectedField.value?.fieldIndex === fieldIndex) {
        selectedField.value = temp
      }

      // Scroll the field into view if it moved off-screen
      setTimeout(() => {
        const fieldElement = document.querySelector(
          `[data-field-index="${fieldIndex}"]`,
        ) as HTMLElement
        if (fieldElement) {
          fieldElement.style.scrollMargin = '20px'
          fieldElement.scrollIntoView({ behavior: 'smooth', block: 'center' })
        }
      }, 100)
    }
  }

  const moveFieldLeft = (fieldIndex: number) => {
    const field = fields.value.find((f) => f.fieldIndex === fieldIndex)
    if (!field || field.columnIndex === undefined) return

    // Get all fields in the same row, sorted by columnIndex
    const fieldsInRow = fields.value
      .filter((f) => (f.rowIndex ?? 0) === (field.rowIndex ?? 0))
      .sort((a, b) => (a.columnIndex ?? 0) - (b.columnIndex ?? 0))

    const currentPosition = fieldsInRow.findIndex((f) => f.fieldIndex === fieldIndex)

    // Can't move left if already first
    if (currentPosition <= 0) return

    // Swap columnIndex with field to the left
    const leftField = fieldsInRow[currentPosition - 1]
    const tempColumnIndex = field.columnIndex
    field.columnIndex = leftField.columnIndex
    leftField.columnIndex = tempColumnIndex

    updateFieldPositions()

    // Keep the field selected after moving
    if (selectedField.value?.fieldIndex === fieldIndex) {
      selectedField.value = field
    }
  }

  const moveFieldRight = (fieldIndex: number) => {
    const field = fields.value.find((f) => f.fieldIndex === fieldIndex)
    if (!field || field.columnIndex === undefined) return

    // Get all fields in the same row, sorted by columnIndex
    const fieldsInRow = fields.value
      .filter((f) => (f.rowIndex ?? 0) === (field.rowIndex ?? 0))
      .sort((a, b) => (a.columnIndex ?? 0) - (b.columnIndex ?? 0))

    const currentPosition = fieldsInRow.findIndex((f) => f.fieldIndex === fieldIndex)

    // Can't move right if already last
    if (currentPosition >= fieldsInRow.length - 1) return

    // Swap columnIndex with field to the right
    const rightField = fieldsInRow[currentPosition + 1]
    const tempColumnIndex = field.columnIndex
    field.columnIndex = rightField.columnIndex
    rightField.columnIndex = tempColumnIndex

    updateFieldPositions()

    // Keep the field selected after moving
    if (selectedField.value?.fieldIndex === fieldIndex) {
      selectedField.value = field
    }
  }

  const deleteField = (fieldIndex: number) => {
    const i = fields.value.findIndex((f) => f.fieldIndex === fieldIndex)
    if (i !== -1) {
      const deletedField = fields.value[i]
      const rowIndex = deletedField.rowIndex ?? 0

      // Get remaining fields in row BEFORE deletion
      const remainingFields = fields.value.filter(
        (f) => f.rowIndex === rowIndex && f.fieldIndex !== fieldIndex,
      )

      // Reset remaining fields to even distribution and update columnIndex
      if (remainingFields.length > 0) {
        const evenWidth =
          remainingFields.length >= 5 ? 20 : Math.round((100 / remainingFields.length) * 10) / 10

        // Sort by columnIndex to maintain left-to-right order
        remainingFields.sort((a, b) => (a.columnIndex ?? 0) - (b.columnIndex ?? 0))

        remainingFields.forEach((f, index) => {
          f.width = evenWidth
          f.columnIndex = index // Reassign columnIndex sequentially from 0
        })
      }

      fields.value.splice(i, 1)

      if (selectedField.value?.fieldIndex === fieldIndex) {
        selectedField.value = null
      }

      updateFieldPositions()
    }
  }

  const duplicateField = (fieldIndex: number) => {
    const original = fields.value.find((f) => f.fieldIndex === fieldIndex)
    if (!original) return

    // Check if current row has space
    const currentRowIndex = original.rowIndex ?? 0
    const fieldsInCurrentRow = fields.value.filter((f) => (f.rowIndex ?? 0) === currentRowIndex)

    const newIndex = increaseCounter()
    const copy = duplicateFieldForStore(original, newIndex, getLabel)
    const insertAt = fields.value.findIndex((f) => f.fieldIndex === fieldIndex)

    // If field is alone in a row OR row is full, create new row below
    if (fieldsInCurrentRow.length === 1 || fieldsInCurrentRow.length >= 5) {
      // Create new row below
      copy.rowIndex = currentRowIndex + 1
      copy.columnIndex = 0
      copy.width = 100

      // Shift all rows below down by 1
      fields.value.forEach((f) => {
        if ((f.rowIndex ?? 0) > currentRowIndex) {
          f.rowIndex = (f.rowIndex ?? 0) + 1
        }
      })

      fields.value.splice(insertAt + 1, 0, copy)
    } else {
      // Add to same row (multi-field row with space)
      copy.rowIndex = currentRowIndex
      copy.columnIndex = original.columnIndex! + 1

      // Shift column indexes for fields to the right
      fields.value.forEach((f) => {
        if (
          (f.rowIndex ?? 0) === currentRowIndex &&
          (f.columnIndex ?? 0) > (original.columnIndex ?? 0)
        ) {
          f.columnIndex = (f.columnIndex ?? 0) + 1
        }
      })

      fields.value.splice(insertAt + 1, 0, copy)

      // Reset all fields in row to even distribution
      const fieldsInRow = fields.value.filter((f) => (f.rowIndex ?? 0) === currentRowIndex)
      const evenWidth =
        fieldsInRow.length >= 5 ? 20 : Math.round((100 / fieldsInRow.length) * 10) / 10
      fieldsInRow.forEach((f) => {
        f.width = evenWidth
      })
    }

    updateFieldPositions()

    // Select the newly duplicated field
    selectedField.value = copy
    activeTab.value = 'options'
  }

  const resetForm = () => {
    // Suppress dirty tracking during reset
    suppressDirtyTracking = true

    formId.value = null
    isEditing.value = false
    fields.value = []
    counterFields.value = 0
    selectedField.value = null
    activeTab.value = 'addField'
    description.value = null
    name.value = 'Blank form'
    published.value = true
    showTitle.value = true
    showDescription.value = false
    storeEntries.value = true
    isEditingSettingGeneral.value = false
    confirmationId.value = null
    isFormLoading.value = false
    submitButtonSettings.value = {
      label: getLabel('submit'),
      position: 'default',
    }
    isSubmitButtonSelected.value = false

    // Stop editing and clear dirty flag
    unsavedChangesStore.stopEditing('formBuilder')

    // Re-enable dirty tracking
    suppressDirtyTracking = false
  }

  const updateFieldOptions = (key: string, value: unknown) => {
    if (!selectedField.value) return
    const index = fields.value.findIndex((f) => f.fieldIndex === selectedField.value?.fieldIndex)
    if (index !== -1) {
      // Mutate the existing object instead of creating a new one
      // This keeps the reference intact for selectedField
      const target = fields.value[index] as Record<string, unknown>
      target[key] = value
    }
  }

  return {
    formId,
    loadedForEntry,
    fields,
    isEditing,
    selectedField,
    activeTab,
    counterFields,
    description,
    name,
    published,
    showTitle,
    showDescription,
    storeEntries,
    integrationSettings,
    isEditingSettingGeneral,
    confirmationId,
    isFormLoading,
    isDraggingFromPanel,
    markDirty,
    markClean,
    saveForm,
    loadForm,
    updateForm,
    increaseCounter,
    addField,
    selectField,
    deselectField,
    isSelected,
    updateSelectedField,
    updateFieldOptions,
    updateFieldPositions,
    moveFieldUp,
    moveFieldDown,
    moveFieldLeft,
    moveFieldRight,
    deleteField,
    duplicateField,
    saveFormSettings,
    resetForm,
    // Grid layout methods
    fieldsByRow,
    moveFieldToPosition,
    recalculateRowWidths,
    canAddToRow,
    getAutoWidth,
    // Submit button
    submitButtonSettings,
    isSubmitButtonSelected,
    selectSubmitButton,
    deselectSubmitButton,
    updateSubmitButtonSettings,
  }
})
