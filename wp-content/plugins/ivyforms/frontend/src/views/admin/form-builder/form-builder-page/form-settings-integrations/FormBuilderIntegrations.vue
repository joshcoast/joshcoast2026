<template>
  <div
    class="ivyforms-form-integration ivyforms-flex ivyforms-flex-1 ivyforms-flex-direction-column ivyforms-pr-20"
  >
    <div
      class="ivyforms-form-integration__option-bar ivyforms-pb-8 ivyforms-flex ivyforms-align-items-center"
    >
      <div
        class="ivyforms-form-integration__title ivyforms-flex ivyforms-align-items-center regular-16"
      >
        {{ getLabel(getCurrentIntegration + '_integration_settings') }}
      </div>
      <!-- Action Buttons -->
      <div
        class="ivyforms-form-integration__option-bar__right ivyforms-flex ivyforms-gap-8 ivyforms-align-items-center ivyforms-justify-content-end"
      >
        <IvyButtonAction
          :class="['ivyforms-button__action--reset']"
          priority="danger"
          type="fill"
          :disabled="isResetDisabled"
          @click="resetSettings"
        >
          {{ getLabel('clear_settings') }}
        </IvyButtonAction>
        <IvyButtonAction
          :class="['ivyforms-button__action--save']"
          priority="primary"
          :loading="loading"
          @click="saveSettings"
        >
          <template v-if="!loading">{{ getLabel('save') }}</template>
        </IvyButtonAction>
      </div>
    </div>

    <!-- Dynamic Settings Section -->
    <div
      class="ivyforms-form-integration__content ivyforms-pt-20 ivyforms-flex ivyforms-flex-direction-column ivyforms-flex-1 ivyforms-gap-24"
    >
      <div
        class="ivyforms-form-integration__content__wrapper ivyforms-flex ivyforms-flex-direction-column ivyforms-p-24 ivyforms-gap-24 ivyforms-border-radius-16"
      >
        <!-- Try to load Pro integration component first -->
        <component :is="getProIntegrationComponent" v-if="getProIntegrationComponent" />

        <!-- Fallback to hardcoded Lite integrations -->
        <template v-else>
          <!-- wpDataTables Integration Settings (Lite) -->
          <template v-if="getCurrentIntegration === 'wpdatatables'">
            <IvyCheckbox
              v-model="formBuilderStore.integrationSettings.wpdatatables.enabled"
              priority="secondary"
              type="checkmark"
            >
              {{ getLabel('allow_ivy_wpdatatables_integration') }}
            </IvyCheckbox>
          </template>

          <!-- No integration component found -->
          <div v-else class="ivyforms-form-integration__not-available">
            <p>{{ getLabel('integration_not_available') }}</p>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref, onBeforeUnmount } from 'vue'
import { useRoute } from 'vue-router'
import { useLabels } from '@/composables/useLabels.ts'
import { useFormBuilder } from '@/stores/useFormBuilder'
import { useIntegrations } from '@/composables/useIntegrations'

const { getLabel } = useLabels()
const formBuilderStore = useFormBuilder()
const route = useRoute()
const loading = ref(false)
const { loadIntegrations, integrations: allIntegrations } = useIntegrations()

// Reactive trigger for component updates
const componentUpdateTrigger = ref(0)

// Load integrations on mount
onMounted(async () => {
  await loadIntegrations()

  // Listen for ivyforms:ready event (when Pro registers components)
  window.addEventListener('ivyforms:ready', handleComponentsReady)

  // Listen for Pro component registration events
  window.addEventListener('ivyforms:pro:component-registered', handleComponentsReady)

  // Also trigger an update after a short delay to catch already-registered components
  setTimeout(() => {
    componentUpdateTrigger.value++
  }, 100)

  // Load form data
  if (formId.value) {
    await formBuilderStore.loadForm(formId.value as string)
  }
})

onBeforeUnmount(() => {
  window.removeEventListener('ivyforms:ready', handleComponentsReady)
  window.removeEventListener('ivyforms:pro:component-registered', handleComponentsReady)
})

// Get current integration from route parameter
const getCurrentIntegration = computed(() => {
  return (route.params.integration as string) || 'wpdatatables'
})

// Get current integration config from registry
const currentIntegrationConfig = computed(() => {
  // eslint-disable-next-line @typescript-eslint/no-unused-expressions
  componentUpdateTrigger.value // Make reactive
  const slug = getCurrentIntegration.value
  return allIntegrations.value.find((i) => i.value === slug) || null
})

/**
 * Get Pro integration component dynamically from registry
 * Returns the component registered in window.IvyForms.components
 */
const getProIntegrationComponent = computed(() => {
  // Access trigger to make this reactive
  // eslint-disable-next-line @typescript-eslint/no-unused-expressions
  componentUpdateTrigger.value

  if (typeof window === 'undefined') return null

  const w = window as Window & {
    IvyForms?: {
      components?: Record<string, unknown>
    }
  }

  // Get component name from registry config
  const config = currentIntegrationConfig.value
  if (!config || !config.component) {
    return null
  }

  const componentName = config.component
  const component = w.IvyForms?.components?.[componentName]

  return component || null
})

// Listen for Pro component registration
const handleComponentsReady = () => {
  componentUpdateTrigger.value++
}

const saveSettings = async () => {
  loading.value = true
  try {
    await formBuilderStore.updateForm()
  } finally {
    loading.value = false
  }
}

const formId = computed(() => {
  const id = route.params.formId
  return Array.isArray(id) ? id[0] : id
})

// Computed property to check if integration has any data
const isResetDisabled = computed(() => {
  const integrationName = getCurrentIntegration.value
  const settings = formBuilderStore.integrationSettings?.[integrationName]

  if (!settings) return true

  // Generic check: has any non-empty values
  return !Object.values(settings).some((value) => {
    if (typeof value === 'boolean') return value
    if (typeof value === 'string') return value.length > 0
    if (typeof value === 'object' && value !== null) return Object.keys(value).length > 0
    return false
  })
})

// Reset integration settings
const resetSettings = () => {
  const integrationName = getCurrentIntegration.value

  if (!formBuilderStore.integrationSettings) return

  // Clear the integration settings - generic approach for all integrations
  formBuilderStore.integrationSettings[integrationName] = {}
}
</script>

<style lang="scss" scoped>
@use 'sass:list' as *;
@use 'sass:meta' as *;

.ivyforms-form-integration {
  height: 100%;

  &__title {
    color: var(--map-base-text-0);
  }

  &__option-bar {
    border-bottom: 1px solid var(--map-divider);
    background: var(--map-ground-level-1-foreground);

    &__right {
      flex: 1 1 50%;
      min-width: 0;

      .ivyforms-button__action--save {
        min-width: 67px;
        gap: 0;

        :deep(.ivyforms-button-action),
        :deep(.ivyforms-button-action.is-loading) {
          min-width: 67px;
          gap: 0;
          width: 100%;
        }
      }
    }
  }

  &__tabs {
    .ivyforms-tabs-wrapper {
      width: fit-content;
      display: block;

      .ivyforms-tabs-group.el-tabs--card .el-tabs__header .el-tabs__item {
        min-width: auto !important;
      }
    }
  }

  &__content {
    overflow-y: auto;

    &__wrapper {
      border: 1px solid var(--map-divider);
    }
  }

  &__field-label {
    color: var(--map-base-text-0);
    font-weight: 500;
  }

  &__field-description {
    color: var(--map-base-text-1);
    font-style: italic;
  }
}
</style>
