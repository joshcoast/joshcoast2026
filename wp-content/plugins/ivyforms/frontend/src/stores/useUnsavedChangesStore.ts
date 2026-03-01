import { ref, computed } from 'vue'
import type { ComputedRef } from 'vue'
import { defineStore } from 'pinia'
import { useActionEntityStore } from '@/stores/actionEntityStore'

/**
 * Entity types that can track unsaved changes
 */
export type UnsavedEntityKey = 'formBuilder' | 'confirmationBuilder' | 'notificationBuilder'

/**
 * Design principles:
 * 1. Uses a dirty flag approach
 * 2. Each entity store is responsible for calling markDirty() when changes occur
 * 3. Changes are marked clean after successful save operations
 *
 * How to use in entity stores:
 * 1. Call startEditing() when loading data or starting to edit
 * 2. Call markDirty() whenever user makes any change
 * 3. Call markClean() after successful save
 * 4. Call stopEditing() when leaving the editor
 */
export const useUnsavedChangesStore = defineStore('unsavedChanges', () => {
  // Track dirty state for each entity type
  const dirtyFlags = ref<Record<UnsavedEntityKey, boolean>>({
    formBuilder: false,
    confirmationBuilder: false,
    notificationBuilder: false,
  })

  // Track which entities are currently being edited (loaded from server)
  const editingFlags = ref<Record<UnsavedEntityKey, boolean>>({
    formBuilder: false,
    confirmationBuilder: false,
    notificationBuilder: false,
  })

  // Action entity store for showing the dialog
  const actionEntityStore = useActionEntityStore()

  /**
   * Mark an entity as dirty (has unsaved changes)
   * Call this whenever user makes a change to form fields, settings, etc.
   */
  const markDirty = (entityKey: UnsavedEntityKey) => {
    // Only mark dirty if the entity is being edited
    if (editingFlags.value[entityKey]) {
      dirtyFlags.value[entityKey] = true
    }
  }

  /**
   * Mark an entity as clean (no unsaved changes)
   * Call this after successful save operations
   */
  const markClean = (entityKey: UnsavedEntityKey) => {
    dirtyFlags.value[entityKey] = false
  }

  /**
   * Mark an entity as being edited (loaded from server or new form started)
   * This enables dirty tracking for the entity
   */
  const startEditing = (entityKey: UnsavedEntityKey) => {
    editingFlags.value[entityKey] = true
    dirtyFlags.value[entityKey] = false
  }

  /**
   * Mark an entity as no longer being edited
   * This disables dirty tracking and clears the dirty flag
   */
  const stopEditing = (entityKey: UnsavedEntityKey) => {
    editingFlags.value[entityKey] = false
    dirtyFlags.value[entityKey] = false
  }

  /**
   * Check if a specific entity has unsaved changes
   */
  const isDirty = (entityKey: UnsavedEntityKey): boolean => {
    return dirtyFlags.value[entityKey]
  }

  /**
   * Get a computed ref for dirty state (useful for reactive bindings)
   */
  const isDirtyRef = (entityKey: UnsavedEntityKey): ComputedRef<boolean> => {
    return computed(() => dirtyFlags.value[entityKey])
  }

  /**
   * Check if a specific entity is being edited
   */
  const isEntityEditing = (entityKey: UnsavedEntityKey): boolean => {
    return editingFlags.value[entityKey]
  }

  /**
   * Check if any of the specified entities have unsaved changes
   * @param entities - Array of entity keys to check (defaults to all)
   */
  const hasAnyUnsavedChanges = (
    entities: UnsavedEntityKey[] = ['formBuilder', 'confirmationBuilder', 'notificationBuilder'],
  ): boolean => {
    return entities.some((entity) => dirtyFlags.value[entity])
  }

  /**
   * Get a computed ref for any unsaved changes (useful for reactive bindings)
   */
  const hasAnyUnsavedChangesRef = (
    entities: UnsavedEntityKey[] = ['formBuilder', 'confirmationBuilder', 'notificationBuilder'],
  ): ComputedRef<boolean> => {
    return computed(() => entities.some((entity) => dirtyFlags.value[entity]))
  }

  /**
   * Clear all dirty flags
   * Use when navigating away after user confirms, or after successful save of all entities
   */
  const clearAllDirtyFlags = () => {
    Object.keys(dirtyFlags.value).forEach((key) => {
      dirtyFlags.value[key as UnsavedEntityKey] = false
    })
  }

  /**
   * Reset all state (dirty flags and editing flags)
   * Use when completely leaving the form builder context
   */
  const resetAllState = () => {
    Object.keys(dirtyFlags.value).forEach((key) => {
      dirtyFlags.value[key as UnsavedEntityKey] = false
      editingFlags.value[key as UnsavedEntityKey] = false
    })
  }

  /**
   * Show confirmation dialog if there are unsaved changes, then proceed
   * @param onProceed - Callback to execute if user confirms or no changes exist
   * @param entities - Which entities to check (defaults to all)
   */
  const confirmIfDirty = (
    onProceed: () => void,
    entities: UnsavedEntityKey[] = ['formBuilder', 'confirmationBuilder', 'notificationBuilder'],
  ) => {
    if (!hasAnyUnsavedChanges(entities)) {
      onProceed()
      return
    }

    actionEntityStore.showUnsavedChangesDialog(() => {
      // Clear dirty flags before proceeding since user confirmed they want to leave
      entities.forEach((entity) => markClean(entity))
      onProceed()
    })
  }

  /**
   * Handle admin menu click with unsaved changes check
   * Used to intercept WordPress admin menu clicks
   * @param event - Click event
   * @param entities - Which entities to check
   */
  const handleAdminMenuClick = (
    event: Event,
    entities: UnsavedEntityKey[] = ['formBuilder', 'confirmationBuilder', 'notificationBuilder'],
  ) => {
    const mouseEvent = event as MouseEvent
    const target = mouseEvent.target as HTMLElement
    const link = target.closest('a') as HTMLAnchorElement | null

    if (!link) return

    // Prevent default navigation first, before checking for unsaved changes
    mouseEvent.preventDefault()
    mouseEvent.stopPropagation()

    // If no unsaved changes, proceed with navigation immediately
    if (!hasAnyUnsavedChanges(entities)) {
      window.location.href = link.href
      return
    }

    confirmIfDirty(() => {
      window.location.href = link.href
    }, entities)
  }
  return {
    // State tracking
    markDirty,
    markClean,
    startEditing,
    stopEditing,
    isDirty,
    isDirtyRef,
    isEntityEditing,

    // Aggregated state checks
    hasAnyUnsavedChanges,
    hasAnyUnsavedChangesRef,
    clearAllDirtyFlags,
    resetAllState,

    // Navigation helpers
    confirmIfDirty,
    handleAdminMenuClick,
  }
})
