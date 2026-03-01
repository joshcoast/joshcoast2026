<template>
  <IvyModal v-model:visible="isVisible" :teleport="true" @close="handleClose">
    <!-- Normal template selection view -->
    <div
      v-if="!isPreviewMode"
      class="template-modal ivyforms-flex ivyforms-flex-direction-column ivyforms-p-0"
    >
      <div
        class="template-modal__header ivyforms-flex ivyforms-align-items-center ivyforms-p-24 ivyforms-mb-0"
      >
        <span>{{ getLabel('add_new_form') }}</span>
        <div
          class="template-modal__close ivyforms-flex ivyforms-align-items-center ivyforms-justify-content-center"
        >
          <IvyButtonAction
            priority="tertiary"
            size="s"
            icon-only
            icon-start="close"
            icon-start-type="outline"
            type="ghost"
            @click="handleClose"
          >
          </IvyButtonAction>
        </div>
      </div>

      <div
        class="template-modal__options ivyforms-flex ivyforms-gap-16 ivyforms-mb-0 ivyforms-px-24"
      >
        <div
          class="template-modal__option ivyforms-flex ivyforms-justify-content-center ivyforms-align-items-center ivyforms-p-24 ivyforms-gap-16"
          :class="{
            'template-modal__option--active': selectedOption === 'scratch',
            'template-modal__option--disabled':
              isCreatingForm && selectedTemplateId === 'blank_form',
          }"
          @click="selectOption('scratch')"
        >
          <div class="template-modal__option-icon ivyforms-mr-16 ivyforms-align-items-center">
            <div
              class="template-modal__icon-bg"
              :class="{
                'template-modal__icon-bg--active': selectedOption === 'scratch',
                'template-modal__icon-bg--loading':
                  isCreatingForm && selectedTemplateId === 'blank_form',
              }"
            >
              <IvyIcon
                v-if="!(isCreatingForm && selectedTemplateId === 'blank_form')"
                name="plus-circle"
                size="d"
                category="global"
                type="fill-duo"
              />
              <IvyIcon
                v-else
                name="spinner-fill"
                size="d"
                category="loaders"
                color="var(--map-base-purple-symbol-0)"
                class="ivyforms-loading-icon"
              />
            </div>
          </div>
          <div class="template-modal__option-text">
            <div class="template-modal__option-title regular-18 ivyforms-mb-4 ivyforms-gap-8">
              {{
                isCreatingForm && selectedTemplateId === 'blank_form'
                  ? ''
                  : getLabel('start_scratch')
              }}
            </div>
            <div class="template-modal__option-desc regular-16">
              {{ getLabel('start_with_blank_desc') }}
            </div>
          </div>
        </div>
        <div
          class="template-modal__option template-modal__option--disabled ivyforms-flex ivyforms-justify-content-center ivyforms-align-items-center ivyforms-p-24 ivyforms-gap-16"
        >
          <div
            class="template-modal__option-icon ivyforms-align-items-center ivyforms-flex ivyforms-mr-16"
          >
            <div class="template-modal__icon-bg template-modal__icon-bg--disabled">
              <IvyIcon
                name="generate-ai"
                type="outline"
                size="d"
                category="templates"
                color="var(--map-base-dusk-symbol-2)"
              />
            </div>
          </div>
          <div>
            <div
              class="template-modal__option-title regular-18 ivyforms-flex ivyforms-align-items-center ivyforms-mb-4 ivyforms-gap-8"
            >
              {{ getLabel('generate_with_ai') }}
              <ComingSoonBadge image="coming-soon-arrow-text" size="s" />
            </div>
            <div class="template-modal__option-desc regular-16">
              {{ getLabel('generate_with_ai_desc') }}
            </div>
          </div>
        </div>
      </div>

      <div
        class="template-modal__templates ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-24 ivyforms-p-24"
      >
        <div
          class="template-modal__templates-labels ivyforms-flex ivyforms-flex-direction-column ivyforms-align-items-start ivyforms-gap-8"
        >
          <div class="template-modal__select-label ivyforms-m-0">
            {{ getLabel('select_a_template') }}
          </div>
          <div class="template-modal__select-desc ivyforms-m-0 regular-16">
            {{ getLabel('select_template_desc') }}
          </div>
        </div>

        <div
          ref="templatesSectionRef"
          class="template-modal__templates-section ivyforms-flex ivyforms-gap-16"
        >
          <!-- Left Sidebar: Categories -->
          <div class="template-modal__categories-sidebar">
            <IvyScrollbar :style="{ maxHeight: sidebarScrollbarMaxHeight }">
              <IvyMenuAccordion
                v-model="selectedCategory"
                priority="secondary"
                :menu-items="categoryMenuItems"
                @menu-select="handleCategorySelect"
              />
            </IvyScrollbar>
          </div>

          <!-- Right Content: Templates -->
          <div
            class="template-modal__templates-content ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-24 ivyforms-flex-1"
          >
            <div
              class="template-modal__templates-option-bar ivyforms-flex ivyforms-justify-content-between"
            >
              <div class="template-modal__search ivyforms-m-0">
                <IvySearch
                  v-model="searchQuery"
                  :placeholder="getLabel('search_template')"
                  :should-collapse-on-mobile="false"
                />
              </div>

              <div
                class="template-modal__templates-header ivyforms-flex ivyforms-justify-content-between ivyforms-align-items-start ivyforms-gap-16"
              >
                <!-- Filter Tabs -->
                <IvyTabs
                  v-model="selectedFilter"
                  :tabs="filterTabs"
                  tab-style="fill"
                  priority="tertiary"
                  background
                />
              </div>
            </div>

            <IvyScrollbar modifier="outside-vertical" :style="{ maxHeight: scrollbarMaxHeight }">
              <div
                class="template-modal__card-grid ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-16 ivyforms-mt-0"
              >
                <!-- Loading skeleton cards -->
                <template v-if="isLoading">
                  <div
                    v-for="(templateRow, rowIndex) in skeletonRows"
                    :key="`loading-row-${rowIndex}`"
                    class="template-modal__card-row ivyforms-flex ivyforms-gap-16"
                  >
                    <div
                      v-for="(template, cardIndex) in templateRow"
                      :key="`loading-${rowIndex}-${cardIndex}`"
                      class="template-modal__card-container"
                    >
                      <IvyTemplateCard
                        :title="''"
                        :description="''"
                        :starred="false"
                        class="template-modal__card ivyforms-gap-0 ivyforms-pb-0"
                      >
                        <template #header>
                          <div
                            class="template-modal__image-wrapper ivyforms-flex ivyforms-p-0 ivyforms-m-0 ivyforms-justify-content-center"
                          >
                            <div
                              class="template-modal__skeleton-image-placeholder ivyforms-flex ivyforms-flex-direction-column ivyforms-p-10 ivyforms-justify-content-center"
                            >
                              <div class="skeleton-bar skeleton-bar-1"></div>
                              <div class="skeleton-bar skeleton-bar-2"></div>
                              <div class="skeleton-bar skeleton-bar-3"></div>
                              <div class="skeleton-row ivyforms-flex ivyforms-gap-12 ivyforms-mt-8">
                                <div class="skeleton-pill"></div>
                                <div class="skeleton-pill"></div>
                              </div>
                            </div>
                          </div>
                        </template>

                        <template #footer>
                          <div
                            class="template-modal__card-footer-labels ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-4 ivyforms-align-items-start"
                          >
                            <div
                              class="template-modal__empty-title ivyforms-px-16 ivyforms-mt-16 ivyforms-mb-4"
                            ></div>
                            <div
                              class="template-modal__empty-desc ivyforms-px-16 ivyforms-mb-16 ivyforms-mt-0"
                            ></div>
                          </div>
                        </template>
                      </IvyTemplateCard>
                    </div>
                  </div>
                </template>

                <!-- Actual template cards -->
                <template v-for="(templateRow, rowIndex) in templateRows" v-else :key="rowIndex">
                  <div class="template-modal__card-row ivyforms-flex ivyforms-gap-16">
                    <div
                      v-for="template in templateRow"
                      :key="template.id"
                      class="template-modal__card-container"
                    >
                      <IvyTemplateCard
                        :title="template.name"
                        :description="template.description"
                        :starred="isTemplateStarred(template.id)"
                        class="template-modal__card ivyforms-gap-0 ivyforms-pb-0"
                        @star-click="() => handleStarToggle(template.id)"
                      >
                        <template #header>
                          <div
                            class="template-modal__image-wrapper ivyforms-flex ivyforms-p-0 ivyforms-m-0 ivyforms-justify-content-center"
                          >
                            <!-- Pro Badge in top-left corner -->
                            <div
                              v-if="template.is_pro && (!isProInstalled() || !isProLicenseActive())"
                              class="template-modal__pro-badge ivyforms-flex ivyforms-justify-content-center ivyforms-align-items-center ivyforms-py-4 ivyforms-px-8"
                            >
                              <ProBadge image="pro-text" size="s" />
                            </div>

                            <img
                              :src="template.screenshot"
                              :alt="template.name"
                              class="template-modal__image ivyforms-mb-0 ivyforms-mx-0 ivyforms-p-0"
                            />

                            <!-- Overlay is inside image wrapper -->
                            <div
                              class="template-modal__card-actions-overlay ivyforms-flex ivyforms-flex-direction-column ivyforms-justify-content-center ivyforms-align-items-center ivyforms-m-0 ivyforms-p-0"
                            >
                              <div
                                class="template-modal__overlay-buttons ivyforms-flex ivyforms-flex-direction-column ivyforms-justify-content-center ivyforms-align-items-center ivyforms-gap-8"
                              >
                                <!-- Pro templates: Show upgrade/activate button + View Demo if Pro not active -->
                                <template
                                  v-if="
                                    template.is_pro && (!isProInstalled() || !isProLicenseActive())
                                  "
                                >
                                  <!-- View Demo button for Pro templates when Pro is NOT active -->
                                  <IvyButtonAction
                                    v-if="template.demo_url"
                                    priority="tertiary"
                                    @click="handleViewDemo(template)"
                                  >
                                    {{ getLabel('view_demo') }}
                                  </IvyButtonAction>
                                  <IvyButtonAction
                                    priority="pro"
                                    class="template-modal__upgrade-button"
                                    @click="handleUpgrade"
                                  >
                                    {{
                                      isProInstalled()
                                        ? getLabel('activate_license')
                                        : getLabel('upgrade')
                                    }}
                                  </IvyButtonAction>
                                </template>
                                <!-- Original buttons for free templates or Pro templates with active license -->
                                <template v-else>
                                  <IvyButtonAction
                                    priority="tertiary"
                                    @click="handlePreviewTemplate(template)"
                                  >
                                    {{ getLabel('preview') }}
                                  </IvyButtonAction>
                                  <IvyButtonAction
                                    priority="primary"
                                    :loading="isCreatingForm && selectedTemplateId === template.id"
                                    @click="handleUseTemplate(template)"
                                  >
                                    <template v-if="!isCreatingForm">{{
                                      getLabel('use_this_template')
                                    }}</template>
                                  </IvyButtonAction>
                                </template>
                              </div>
                            </div>
                          </div>
                        </template>

                        <template #footer>
                          <div
                            class="template-modal__card-footer-labels ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-4 ivyforms-align-items-start"
                          >
                            <div
                              :ref="(el) => checkTitleHeight(el as HTMLElement)"
                              class="template-modal__card-title ivyforms-flex ivyforms-align-items-center ivyforms-px-16 ivyforms-mt-16 ivyforms-mb-4 ivyforms-gap-8 regular-18"
                            >
                              {{ template.name }}
                              <!-- TODO: Add icon for pro -->
                              <span v-if="template.is_pro" class="ivyforms-flash"></span>
                            </div>
                            <div
                              class="template-modal__card-desc ivyforms-px-16 ivyforms-mb-16 ivyforms-mt-0 regular-16"
                            >
                              {{ template.description }}
                            </div>
                          </div>
                        </template>
                      </IvyTemplateCard>
                    </div>
                  </div>
                </template>

                <!-- Empty State -->
                <div
                  v-if="!isLoading && filteredTemplates.length === 0"
                  class="template-modal__empty-state ivyforms-flex ivyforms-flex-direction-column ivyforms-align-items-center ivyforms-justify-content-center ivyforms-py-24 ivyforms-px-24"
                >
                  <div class="template-modal__empty-icon ivyforms-mb-16">
                    <IvyIcon name="search" type="outline" size="d" category="global" />
                  </div>
                  <h4 class="ivyforms-mb-8 ivyforms-m-0">{{ getLabel('no_templates_found') }}</h4>
                  <p class="ivyforms-m-0">{{ getLabel('try_adjusting_criteria') }}</p>
                </div>
              </div>
            </IvyScrollbar>
          </div>
        </div>
      </div>
    </div>

    <!-- Preview mode view -->
    <div v-else class="template-preview template-preview--active ivyforms-px-16 ivyforms-pb-16">
      <!-- Preview Header -->
      <div
        class="template-preview__header ivyforms-flex ivyforms-justify-content-between ivyforms-align-items-center ivyforms-gap-16 ivyforms-mb-24 ivyforms-pt-16"
      >
        <div
          class="template-preview__header-left ivyforms-flex ivyforms-align-items-center ivyforms-gap-12"
        >
          <IvyButtonAction
            priority="tertiary"
            icon-start="chevron-left"
            icon-start-category="arrows"
            icon-start-type="outline"
            @click="goBackToTemplates"
          >
            {{ getLabel('back') }}
          </IvyButtonAction>
          <div class="template-preview__template-info ivyforms-flex ivyforms-flex-direction-column">
            <h3>{{ selectedTemplate?.name }}</h3>
            <span v-if="selectedTemplate?.is_pro" class="ivyforms-flash">⚡</span>
          </div>
        </div>

        <div
          class="template-preview__header-right ivyforms-flex ivyforms-align-items-center ivyforms-gap-8"
        >
          <IvyButtonAction
            priority="tertiary"
            icon-start="arrow-left"
            icon-start-category="arrows"
            icon-start-type="fill"
            :disabled="!canGoToPrevious"
            @click="goToPrevious"
          />
          <IvyButtonAction
            priority="tertiary"
            icon-start="arrow-right"
            icon-start-category="arrows"
            icon-start-type="fill"
            :disabled="!canGoToNext"
            @click="goToNext"
          />
          <!-- Device preview tabs -->
          <IvyTabs
            v-model="previewDevice"
            background
            :tabs="deviceTabs"
            type="tonal"
            priority="tertiary"
            size="d"
            class="template-preview__device-tabs ivyforms-flex ivyforms-align-items-center ivyforms-gap-4"
          >
          </IvyTabs>

          <IvyButtonAction
            priority="primary"
            :loading="isCreatingForm"
            @click="handleUseTemplate(selectedTemplate)"
          >
            <template v-if="!isCreatingForm">{{ getLabel('use_this_template') }}</template>
          </IvyButtonAction>
        </div>
      </div>

      <!-- Preview Content -->
      <div class="template-preview__content" :class="`device-${previewDevice}`">
        <div class="template-preview__form ivyforms-p-16">
          <!-- Mock form preview -->
          <div class="template-preview__form-container">
            <h2 class="ivyforms-mb-16">{{ selectedTemplate?.name }}</h2>
            <p class="ivyforms-mb-24">{{ selectedTemplate?.description }}</p>

            <!-- Loading state -->
            <div v-if="isLoadingTemplate" class="template-preview__loading-fields">
              <div class="template-preview__loading-message">Loading template fields...</div>
            </div>

            <!-- Dynamic form fields rendering -->
            <template v-else-if="templateFormData?.fields && templateFormData.fields.length > 0">
              <component
                :is="getTemplatePreviewField(field.type)"
                v-for="(field, index) in parentFields"
                :key="field.id || index"
                :model-value="getFieldValue(field) as never"
                :error="''"
                :field-errors="{}"
                :disabled="false"
                :field="
                  {
                    ...field,
                    formId: 0,
                    fieldIndex: field.fieldIndex,
                    id: parseInt(field.id) || 0,
                    position: field.position || index,
                    label: field.label || '',
                    defaultValue: field.defaultValue || '',
                    required: field.required || false,
                    placeholder: field.placeholder || '',
                    fieldOptions: field.fieldOptions || [],
                    minValue: field.minValue,
                    maxValue: field.maxValue,
                  } as Field
                "
                class="template-preview__form-field ivyforms-mb-16"
                @update:model-value="(value) => updateFieldValue(field, value)"
              />
            </template>
            <IvyButtonAction
              class="template-preview__submit-btn ivyforms-p-10"
              priority="tertiary"
              type="border"
            >
              {{ getLabel('submit') }}
            </IvyButtonAction>
          </div>
        </div>
      </div>
    </div>
  </IvyModal>
</template>

<script setup lang="ts">
import { computed, ref, watch, onMounted, onUnmounted, nextTick } from 'vue'
import { type Component } from 'vue'
import { useTemplateStore } from '@/stores/useTemplateStore'
import { useSettingsStore } from '@/stores/useSettingsStore'
import { useLabels } from '@/composables/useLabels'
import { useProFeatureUpgrade } from '@/composables/useProFeatureUpgrade'
import { useNavigation } from '@/composables/useNavigation'
import { useFormBuilder } from '@/stores/useFormBuilder'
import { useGlobalState } from '@/stores/useGlobalState'
import IvyModal from '@/views/_components/modal/IvyModal.vue'
import IvySearch from '@/views/_components/search/IvySearch.vue'
import IvyTemplateCard from '@/views/_components/card/IvyTemplateCard.vue'
import IvyButtonAction from '@/views/_components/button/IvyButtonAction.vue'
import IvyIcon from '@/views/_components/icon/IvyIcon.vue'
import IvyTabs from '@/views/_components/tabs/IvyTabs.vue'
import IvyMenuAccordion from '@/views/_components/menu/IvyMenuAccordion.vue'
import IvyScrollbar from '@/views/_components/scrollbar/IvyScrollbar.vue'
import TextFormField from '@/views/public/fields/TextFormField.vue'
import EmailFormField from '@/views/public/fields/EmailFormField.vue'
import ParagraphFormField from '@/views/public/fields/ParagraphFormField.vue'
import PhoneFormField from '@/views/public/fields/PhoneFormField.vue'
import NumberFormField from '@/views/public/fields/NumberFormField.vue'
import NameFormField from '@/views/public/fields/NameFormField.vue'
import RadioFormField from '@/views/public/fields/RadioFormField.vue'
import SelectFormField from '@/views/public/fields/SelectFormField.vue'
import CheckboxFormField from '@/views/public/fields/CheckboxFormField.vue'
import AddressFormField from '@/views/public/fields/AddressFormField.vue'
import DateFormField from '@/views/public/fields/DateFormField.vue'
import TimeFormField from '@/views/public/fields/TimeFormField.vue'
import RatingFormField from '@/views/public/fields/RatingFormField.vue'
import ComingSoonBadge from '@/views/_components/badges/ComingSoonBadge.vue'
import ProBadge from '@/views/_components/badges/ProBadge.vue'
import type { IconType } from '@/types/icons/icon-type'
import type { IconCategory } from '@/types/icons/icon-category'
import type { IconSize } from '@/types/icons/icon-size'
import type { Field, NameSubField, NameFieldValue, AddressFieldValue } from '@/types/field'
import type { TimeFieldType, TimeFormat } from '@/types/time/time-type'
import { templateCategories } from '@/constants/templateCategories.ts'
import api from '@/composables/IvyFormAPI.ts'

interface Tab {
  name: string
  label: string
  path?: string
  disabled?: boolean
  iconConfig?: {
    name: string
    type?: IconType
    category?: IconCategory
    size?: IconSize
    class?: string
  }
}

interface FormField {
  id: string
  type: string
  label?: string
  value?: string | number
  required?: boolean
  placeholder?: string
  options?: Array<{ label: string; value: string }>
  defaultValue?: string
  formId?: number
  fieldIndex?: number
  timeFieldType?: TimeFieldType
  timeFormat?: TimeFormat
  parentId?: number | null
  position?: number
  optionHide?: boolean
  hideLabel?: boolean
  description?: string
  addressType?: string
  minValue?: number
  maxValue?: number
  settings?: string | Record<string, unknown>
  fieldOptions?: Array<{
    id: number
    label: string
    value: string
    isDefault?: boolean
    position?: number
  }>
  nameFields?: NameSubField[]
  // Dynamic properties for composite fields - added after enrichCompositeField
  nameFieldTypes?: string[]
  addressFieldTypes?: string[]
  // Allow dynamic access for other properties added dynamically
  [key: string]: unknown
}

interface FormSettings {
  [key: string]: unknown
}

// Type for form field values - can be primitive, arrays, or complex objects for address/name fields
type FormFieldValue =
  | string
  | number
  | string[]
  | NameFieldValue
  | AddressFieldValue
  | null
  | undefined

interface Template {
  id: string
  name: string
  description: string
  category: string
  subcategory?: string
  is_pro: boolean
  screenshot: string
  form_data: {
    name: string
    description: string
    status: string
    fields: FormField[]
    settings: FormSettings
    showTitle?: boolean
    showDescription?: boolean
    published?: boolean
  }
}

// Props
interface Props {
  visible: boolean
}

const props = withDefaults(defineProps<Props>(), {})

// Emits
const emit = defineEmits<{
  'update:visible': [value: boolean]
  'form-created': [formId: number]
  close: []
}>()

// Composables
const templateStore = useTemplateStore()
const settingsStore = useSettingsStore()
const formBuilderStore = useFormBuilder()
const globalState = useGlobalState()
const { getLabel } = useLabels()
const { navigateToFormBuilder } = useNavigation()
const { showUpgradeDialog, isProInstalled, isProLicenseActive } = useProFeatureUpgrade()

// Reactive state
const isVisible = computed({
  get: () => props.visible,
  set: (value) => emit('update:visible', value),
})

const searchQuery = ref('')
const isLoading = ref(false)
const isCreatingForm = ref(false)
const selectedTemplateId = ref<string | null>(null)
const isPreviewMode = ref(false)
const selectedTemplate = ref<Template | null>(null)
const currentTemplateIndex = ref(0)
const previewDevice = ref<'mobile' | 'tablet' | 'desktop'>('desktop')
const isLoadingTemplate = ref(false)
const templateFormData = ref<Template['form_data'] | null>(null)
const selectedOption = ref('scratch')
const selectedCategory = ref('all')
const selectedFilter = ref('all')
const fieldValues = ref<Record<string, FormFieldValue>>({})
const templatesSectionRef = ref<HTMLElement | null>(null)
const scrollbarMaxHeight = ref('500px')
const sidebarScrollbarMaxHeight = ref('564px') // 500px + 64px

// Category menu items configuration
const categoryMenuItems = templateCategories

// Filter tabs configuration
const filterTabs = ref<Tab[]>([
  {
    name: 'all',
    label: 'All',
  },
  {
    name: 'free',
    label: 'Free',
  },
  {
    name: 'pro',
    label: 'Pro',
  },
])

// Device tabs configuration
const deviceTabs = ref<Tab[]>([
  {
    name: 'desktop',
    label: '',
    iconConfig: {
      name: 'desktop',
      type: 'outline' as IconType,
      size: 'd',
      category: 'global' as IconCategory,
    },
  },
  {
    name: 'tablet',
    label: '',
    iconConfig: {
      name: 'tablet', //test to see if is working
      type: 'outline' as IconType,
      size: 'd',
      category: 'global' as IconCategory,
    },
  },
  {
    name: 'mobile',
    label: '',
    iconConfig: {
      name: 'mobile',
      type: 'outline' as IconType,
      size: 'd',
      category: 'global' as IconCategory,
    },
  },
])

// Computed
const filteredTemplates = computed(() => {
  let filtered = templateStore.templates

  // Filter by favourites first (if favourites category is selected, show ONLY favorited templates)
  if (selectedCategory.value === 'favourites') {
    filtered = filtered.filter((template) => settingsStore.isTemplateFavorited(template.id))
  }
  // Filter by subcategory (check if it's a subcategory)
  else if (selectedCategory.value !== 'all') {
    // First, check if the selected category is a subcategory
    const isSubcategory = templateCategories.value.some((cat) =>
      cat.subItems?.some((sub) => sub.index === selectedCategory.value),
    )

    if (isSubcategory) {
      // Filter by subcategory
      filtered = filtered.filter((template) => template.subcategory === selectedCategory.value)
    } else {
      // Filter by main category
      filtered = filtered.filter((template) => template.category === selectedCategory.value)
    }
  }

  // Filter by price (free/pro)
  if (selectedFilter.value === 'free') {
    filtered = filtered.filter((template) => !template.is_pro)
  } else if (selectedFilter.value === 'pro') {
    filtered = filtered.filter((template) => template.is_pro)
  }

  // Filter by search query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(
      (template) =>
        template.name.toLowerCase().includes(query) ||
        template.description.toLowerCase().includes(query),
    )
  }

  return filtered
})

// Organize templates into rows of 3
const templateRows = computed(() => {
  const templates = filteredTemplates.value
  const rows = []
  for (let i = 0; i < templates.length; i += 3) {
    rows.push(templates.slice(i, i + 3))
  }
  return rows
})

// Create skeleton rows based on actual templates but with placeholder data
const skeletonRows = computed(() => {
  const baseCount = templateStore.templates.length > 0 ? templateStore.templates.length : 6
  const skeletonTemplates = Array.from({ length: baseCount }, (_, index) => ({
    id: `skeleton-${index}`,
  }))

  const rows: Array<Array<{ id: string }>> = []
  for (let i = 0; i < skeletonTemplates.length; i += 3) {
    rows.push(skeletonTemplates.slice(i, i + 3))
  }
  return rows
})

const canGoToPrevious = computed(() => {
  return currentTemplateIndex.value > 0
})

const canGoToNext = computed(() => {
  return currentTemplateIndex.value < filteredTemplates.value.length - 1
})

// Helper function to handle composite fields (name, address, etc.)
const enrichCompositeField = (
  parent: FormField,
  allFields: FormField[],
  fieldType: 'name' | 'address',
): FormField => {
  // Find child fields for this parent
  const childFields = allFields.filter((field: FormField) => {
    return field.parentId === 0 && field.fieldIndex === parent.fieldIndex
  })

  const dynamicProps: Record<string, unknown> = {}
  const fieldTypes: string[] = []

  if (fieldType === 'name') {
    // Process name fields
    childFields.forEach((child: FormField, index: number) => {
      const fieldTypeName = `nameField${index + 1}`
      fieldTypes.push(fieldTypeName)
      dynamicProps[`${fieldTypeName}Label`] = child.label || ''
      dynamicProps[`${fieldTypeName}Placeholder`] = child.placeholder || ''
      dynamicProps[`${fieldTypeName}Required`] = child.required || false
      dynamicProps[`${fieldTypeName}HideLabel`] = child.optionHide || false
      dynamicProps[`${fieldTypeName}Description`] = ''
    })

    return {
      ...parent,
      nameFieldTypes: fieldTypes,
      ...dynamicProps,
    }
  } else {
    // Process address fields
    childFields.forEach((child: FormField) => {
      let addressType = child.addressType
      if (!addressType && child.settings) {
        try {
          const settings =
            typeof child.settings === 'string' ? JSON.parse(child.settings) : child.settings
          addressType = settings.type || settings.addressType
        } catch (error) {
          console.warn(getLabel('failed_to_parse_address_settings'), error)
        }
      }

      if (addressType) {
        fieldTypes.push(addressType)
        dynamicProps[`${addressType}Label`] = child.label || ''
        dynamicProps[`${addressType}Placeholder`] = child.placeholder || ''
        dynamicProps[`${addressType}Required`] = child.required || false
        dynamicProps[`${addressType}HideLabel`] = child.hideLabel || false
        dynamicProps[`${addressType}Description`] = child.description || ''

        // Parse additional settings if available
        if (child.settings) {
          try {
            const settings =
              typeof child.settings === 'string' ? JSON.parse(child.settings) : child.settings
            if (settings.visible !== undefined) {
              dynamicProps[`${addressType}Visible`] = settings.visible
            }
          } catch (error) {
            console.warn(getLabel('failed_to_parse_address_settings'), error)
          }
        }
      }
    })

    return {
      ...parent,
      addressFieldTypes: fieldTypes,
      ...dynamicProps,
    }
  }
}

// Filter out child fields (those with parentId set)
// Only show parent/top-level fields in preview
const parentFields = computed(() => {
  if (!templateFormData.value?.fields) {
    return []
  }

  const allFields = templateFormData.value.fields

  // Get all parent fields (fields with parentId as null, not 0)
  const parents = allFields.filter((field: FormField) => {
    return field.parentId === null || field.parentId === undefined
  })

  // For each parent field, if it's a composite field (name, address, etc.), attach child fields
  return parents.map((parent: FormField) => {
    if (parent.type === 'name' || parent.type === 'address') {
      return enrichCompositeField(parent, allFields, parent.type as 'name' | 'address')
    }
    return parent
  })
})

// Helper to get field value with proper key and type
const getFieldValue = (
  field: FormField,
): string | number | NameFieldValue | AddressFieldValue | string[] => {
  const key = `${field.type}_${field.fieldIndex}`

  // Initialize if not exists
  if (!(key in fieldValues.value)) {
    // For address fields, initialize with empty object
    if (field.type === 'address') {
      fieldValues.value[key] = {}
    }
    // For name fields, initialize with empty object
    else if (field.type === 'name') {
      fieldValues.value[key] = {}
    }
    // For select fields, initialize with empty string or array for multi-select
    else if (field.type === 'select' || field.type === 'multi-select') {
      fieldValues.value[key] = field.type === 'multi-select' ? [] : ''
    }
    // For other fields, use default value or empty string
    else {
      fieldValues.value[key] = field.defaultValue || ''
    }
  }

  return fieldValues.value[key] as string | number | NameFieldValue | AddressFieldValue | string[]
}

// Helper to update field value
const updateFieldValue = (field: FormField, value: FormFieldValue) => {
  const key = `${field.type}_${field.fieldIndex}`
  fieldValues.value[key] = value
}

// Methods
const loadTemplates = async () => {
  isLoading.value = true
  try {
    await templateStore.fetchTemplates()
  } catch (error) {
    console.error(getLabel('failed_to_tab_form'), error)
  } finally {
    isLoading.value = false
  }
}

const handleStartFromScratch = async () => {
  if (isCreatingForm.value) {
    return
  }

  isCreatingForm.value = true
  selectedTemplateId.value = 'blank_form'

  try {
    // Reset form builder to clean state
    await formBuilderStore.resetForm()

    // Save blank form using form builder with template_id - let backend handle all data from BlankFormTemplate
    await formBuilderStore.saveForm({ template_id: 'blank_form' })

    // Set page title to the form name from formBuilderStore
    if (formBuilderStore.name) {
      globalState.setPageTitle(formBuilderStore.name)
    }

    // Emit form-created event to ensure proper state initialization
    if (formBuilderStore.formId) {
      emit('form-created', parseInt(formBuilderStore.formId))
      navigateToFormBuilder(parseInt(formBuilderStore.formId))
    }

    isVisible.value = false
  } catch (error) {
    console.error(getLabel('failed_to_create_form'), error)
  } finally {
    isCreatingForm.value = false
    selectedTemplateId.value = null
  }
}

const handlePreviewTemplate = async (template: Template) => {
  selectedTemplate.value = template
  isPreviewMode.value = true
  currentTemplateIndex.value = filteredTemplates.value.findIndex((t) => t.id === template.id)

  // Fetch full template data if not already available
  if (!template.form_data || !template.form_data.fields || template.form_data.fields.length === 0) {
    isLoadingTemplate.value = true
    try {
      const fullTemplate = await templateStore.getTemplate(template.id)
      if (fullTemplate) {
        // Ensure proper type conversion for published property
        const normalizedTemplate = {
          ...fullTemplate,
          form_data: {
            ...fullTemplate.form_data,
            published: Boolean(fullTemplate.form_data.published),
          },
        }
        selectedTemplate.value = normalizedTemplate as Template
        templateFormData.value = normalizedTemplate.form_data as Template['form_data']
      }
    } catch (error) {
      console.error(getLabel('failed_to_tab_form'), error)
    } finally {
      isLoadingTemplate.value = false
    }
  } else {
    const normalizedFormData = {
      ...template.form_data,
      published: Boolean(template.form_data.published),
    }
    templateFormData.value = normalizedFormData
  }
}

// Helper function to get the appropriate field component for template preview
const getTemplatePreviewField = (fieldType: string): Component => {
  let base: Component

  switch (fieldType) {
    case 'text':
      base = TextFormField
      break
    case 'email':
      base = EmailFormField
      break
    case 'textarea':
    case 'paragraph':
      base = ParagraphFormField
      break
    case 'tel':
    case 'phone':
      base = PhoneFormField
      break
    case 'number':
      base = NumberFormField
      break
    case 'name':
      base = NameFormField
      break
    case 'radio':
      base = RadioFormField
      break
    case 'checkbox':
      base = CheckboxFormField
      break
    case 'select':
    case 'dropdown':
    case 'multi-select':
      base = SelectFormField
      break
    case 'address':
      base = AddressFormField
      break
    case 'date':
      base = DateFormField
      break
    case 'time':
      base = TimeFormField
      break
    case 'rating':
      base = RatingFormField
      break
    default:
      base = TextFormField
      break
  }

  try {
    const filtered = api.hooks.applyFilters('ivyforms/template/field/filter/component', base, {
      type: fieldType,
    }) as Component
    return filtered || base
  } catch {
    return base
  }
}

const handleUseTemplate = async (template: Template) => {
  if (isCreatingForm.value) {
    return
  }

  isCreatingForm.value = true
  selectedTemplateId.value = template.id

  try {
    // Reset form builder to clean state but keep isFormLoading = true
    formBuilderStore.formId = null
    formBuilderStore.isEditing = false
    formBuilderStore.fields = []
    formBuilderStore.counterFields = 0
    formBuilderStore.selectedField = null
    formBuilderStore.activeTab = 'addField'
    formBuilderStore.description = null
    formBuilderStore.name = 'Blank form'
    formBuilderStore.published = true
    formBuilderStore.showTitle = false
    formBuilderStore.showDescription = false
    formBuilderStore.storeEntries = false
    formBuilderStore.confirmationId = null
    formBuilderStore.isFormLoading = true

    // Fetch full template data if not already available
    let fullTemplate = template
    if (
      !template.form_data ||
      !template.form_data.fields ||
      template.form_data.fields.length === 0
    ) {
      try {
        const fetchedTemplate = await templateStore.getTemplate(template.id)
        if (fetchedTemplate) {
          fullTemplate = {
            ...fetchedTemplate,
            form_data: {
              ...fetchedTemplate.form_data,
              published: Boolean(fetchedTemplate.form_data.published),
            },
          } as Template
        }
      } catch (error) {
        console.error('Failed to fetch full template data:', error)
        // Fall back to original template if fetching fails
      }
    }

    // Apply template data to form builder
    if (fullTemplate.form_data) {
      // Set form metadata
      formBuilderStore.name = fullTemplate.form_data.name || fullTemplate.name
      formBuilderStore.description = fullTemplate.form_data.description || fullTemplate.description
      formBuilderStore.showTitle = fullTemplate.form_data.showTitle ?? true
      formBuilderStore.showDescription = fullTemplate.form_data.showDescription ?? false
      formBuilderStore.published = fullTemplate.form_data.published ?? false

      // Set form fields if they exist
      if (fullTemplate.form_data.fields && fullTemplate.form_data.fields.length > 0) {
        // Reset counter and fields
        formBuilderStore.counterFields = 0
        formBuilderStore.fields = []

        // Check if template has grid layout data
        const hasGridData = fullTemplate.form_data.fields.some(
          (field) => field.rowIndex !== undefined || field.columnIndex !== undefined,
        )

        // Add template fields to form builder
        fullTemplate.form_data.fields.forEach((fieldData, index) => {
          const fieldIndex = formBuilderStore.increaseCounter()

          // If template has grid data, use it; otherwise put all fields in row 0
          const rowIndex = hasGridData ? ((fieldData.rowIndex as number | undefined) ?? 0) : 0
          const columnIndex = hasGridData
            ? ((fieldData.columnIndex as number | undefined) ?? index)
            : index
          const width = (fieldData.width as number | undefined) ?? 100

          formBuilderStore.addField({
            id: 0, // Always use 0 for new fields - backend will assign unique IDs
            fieldIndex: fieldIndex,
            type: fieldData.type,
            label: fieldData.label,
            defaultValue: fieldData.defaultValue || '',
            required: fieldData.required || false,
            placeholder: fieldData.placeholder || '',
            position: index + 1,
            timeFieldType: fieldData.timeFieldType as TimeFieldType,
            timeFormat: fieldData.timeFormat as TimeFormat,
            rowIndex: rowIndex,
            columnIndex: columnIndex,
            width: width,
          })
        })
      }
    }
    // Save form using form builder
    await formBuilderStore.saveForm({ template_id: template.id })

    // Clear loading state after save completes
    formBuilderStore.isFormLoading = false

    // Set page title to the form name from formBuilderStore
    if (formBuilderStore.name) {
      globalState.setPageTitle(formBuilderStore.name)
    }

    // Refetch form data from backend to ensure all fields are properly normalized
    // (especially parent-child fields like name and address)
    // Do this BEFORE navigation so FormBuilder renders with correct data
    if (formBuilderStore.formId) {
      await formBuilderStore.loadForm(formBuilderStore.formId)
    }

    // Close modal - form is now ready to display
    isVisible.value = false

    // Navigate based on source page type and handle browser history
    if (formBuilderStore.formId) {
      emit('form-created', parseInt(formBuilderStore.formId))
      navigateToFormBuilder(parseInt(formBuilderStore.formId))
    }
  } catch (error) {
    console.error(getLabel('failed_to_create_form'), error)
  } finally {
    isCreatingForm.value = false
    selectedTemplateId.value = null
  }
}

const handleClose = () => {
  isCreatingForm.value = false
  selectedTemplateId.value = null
  isVisible.value = false
  searchQuery.value = ''
  selectedTemplate.value = null
  isPreviewMode.value = false
  currentTemplateIndex.value = 0
  previewDevice.value = 'desktop'
  fieldValues.value = {}
  emit('close')
}

// Watch for visibility changes
watch(isVisible, async (newValue) => {
  if (newValue) {
    // Load favorite templates first, before templates
    await settingsStore.loadFavoriteTemplates()

    if (templateStore.templates.length === 0) {
      await loadTemplates()
    }
  }
})

const goToPrevious = async () => {
  if (canGoToPrevious.value) {
    currentTemplateIndex.value--
    const newTemplate = filteredTemplates.value[currentTemplateIndex.value]
    selectedTemplate.value = newTemplate

    // Load template data for the new template
    if (
      !newTemplate.form_data ||
      !newTemplate.form_data.fields ||
      newTemplate.form_data.fields.length === 0
    ) {
      isLoadingTemplate.value = true
      try {
        const fullTemplate = await templateStore.getTemplate(newTemplate.id)
        if (fullTemplate) {
          // Ensure proper type conversion for published property
          const normalizedTemplate = {
            ...fullTemplate,
            form_data: {
              ...fullTemplate.form_data,
              published: Boolean(fullTemplate.form_data.published),
            },
          }
          selectedTemplate.value = normalizedTemplate as Template
          templateFormData.value = normalizedTemplate.form_data as Template['form_data']
        }
      } catch (error) {
        console.error(getLabel('failed_to_tab_form'), error)
      } finally {
        isLoadingTemplate.value = false
      }
    } else {
      // Ensure proper type conversion for published property
      const normalizedFormData = {
        ...newTemplate.form_data,
        published: Boolean(newTemplate.form_data.published),
      }
      templateFormData.value = normalizedFormData
    }
  }
}

const goToNext = async () => {
  if (canGoToNext.value) {
    currentTemplateIndex.value++
    const newTemplate = filteredTemplates.value[currentTemplateIndex.value]
    selectedTemplate.value = newTemplate

    // Load template data for the new template
    if (
      !newTemplate.form_data ||
      !newTemplate.form_data.fields ||
      newTemplate.form_data.fields.length === 0
    ) {
      isLoadingTemplate.value = true
      try {
        const fullTemplate = await templateStore.getTemplate(newTemplate.id)
        if (fullTemplate) {
          // Ensure proper type conversion for published property
          const normalizedTemplate = {
            ...fullTemplate,
            form_data: {
              ...fullTemplate.form_data,
              published: Boolean(fullTemplate.form_data.published),
            },
          }
          selectedTemplate.value = normalizedTemplate as Template
          templateFormData.value = normalizedTemplate.form_data as Template['form_data']
        }
      } catch (error) {
        console.error(getLabel('failed_to_tab_form'), error)
      } finally {
        isLoadingTemplate.value = false
      }
    } else {
      // Ensure proper type conversion for published property
      const normalizedFormData = {
        ...newTemplate.form_data,
        published: Boolean(newTemplate.form_data.published),
      }
      templateFormData.value = normalizedFormData
    }
  }
}

const goBackToTemplates = () => {
  isPreviewMode.value = false
  selectedTemplate.value = null
  currentTemplateIndex.value = 0
  templateFormData.value = null
  isLoadingTemplate.value = false
  fieldValues.value = {}
}

const selectOption = (option: string) => {
  if (option === 'scratch' && !isCreatingForm.value) {
    selectedOption.value = option
    handleStartFromScratch()
  }
}

const handleCategorySelect = (categoryIndex: string) => {
  selectedCategory.value = categoryIndex
}

const handleStarToggle = async (templateId: string) => {
  await settingsStore.toggleFavoriteTemplate(templateId)
}

const isTemplateStarred = (templateId: string): boolean => {
  return settingsStore.isTemplateFavorited(templateId)
}

const TEMPLATES_URL = 'https://ivyforms.com/form-templates'

const handleViewDemo = (template: Template) => {
  const slug = template.name.toLowerCase().replace(/\s+/g, '-')
  window.open(`${TEMPLATES_URL}/${slug}/`, '_blank')
}

const handleUpgrade = () => {
  showUpgradeDialog()
}

// Update scrollbar max height based on templates section height
const updateScrollbarHeight = () => {
  if (templatesSectionRef.value) {
    const sectionHeight = templatesSectionRef.value.clientHeight
    // Reserve space for search/filters (approx 80px) and some padding
    const availableHeight = sectionHeight - 80
    scrollbarMaxHeight.value = `${availableHeight}px`
    // Sidebar gets extra 64px height
    sidebarScrollbarMaxHeight.value = `${availableHeight + 64}px`
  }
}

// Check if title wraps to multiple lines and apply smaller font class
const checkTitleHeight = (el: HTMLElement | null) => {
  if (!el) return

  // Use nextTick to ensure DOM is updated
  nextTick(() => {
    const lineHeight = parseFloat(getComputedStyle(el).lineHeight)
    const height = el.offsetHeight

    // If height is more than 1.5x line height, it's wrapped to 2+ lines
    if (height > lineHeight * 1.5) {
      el.classList.add('template-modal__card-title--multiline')
    } else {
      el.classList.remove('template-modal__card-title--multiline')
    }
  })
}

onMounted(() => {
  // Initial calculation
  setTimeout(updateScrollbarHeight, 100)

  // Update on window resize
  window.addEventListener('resize', updateScrollbarHeight)
})

onUnmounted(() => {
  window.removeEventListener('resize', updateScrollbarHeight)
})

// Watch for visibility changes to recalculate height
watch(isVisible, (newValue) => {
  if (newValue) {
    setTimeout(updateScrollbarHeight, 100)
  }
})
</script>

<style scoped lang="scss">
@use '@/assets/scss/abstracts/mixins' as *;
.template-modal {
  height: 100%;
  font-family: inherit;
  background: var(--map-ground-level-2-foreground);
  overflow: hidden;
  margin: 0 auto;

  &__header {
    align-self: stretch;
    font-weight: 600;
    font-size: 1.15rem;
    color: var(--map-base-text-0);
  }

  &__close {
    position: absolute;
    top: 10px;
    right: 10px;
    background: var(--map-ground-level-2-foreground);
    border: none;
    cursor: pointer;
    border-radius: 6px;
    transition: all 0.2s ease;
    width: 40px;
    height: 40px;
  }

  &__options {
    gap: 16px;
    margin-bottom: 0;
    padding: 0 24px;
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
  }

  &__option {
    flex: 1 0 0;
    max-width: 350px;
    border-radius: 8px;
    border: 1px solid var(--map-base-dusk-stroke--2);
    background: var(--map-ground-level-1-foreground);
    cursor: pointer;

    .-text {
      flex: 1;
      width: 100%;
      height: 100%;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    &:hover {
      border: 2px solid var(--map-base-purple-stroke-0);
      padding: 23px;
      .template-modal__icon-bg {
        background: var(--map-base-purple-o10);
      }
      :deep(.ivyforms-icon) {
        .ivyforms-icon__svg {
          fill: var(--map-base-purple-symbol-0);
          stroke: var(--map-base-purple-symbol-0);
        }
      }
    }

    &--disabled {
      cursor: not-allowed;
      pointer-events: none;

      &.active {
        border: 2px solid var(--map-base-purple-stroke-0);
        padding: 23px;
      }
      &:hover {
        border: 2px solid var(--map-base-dusk-stroke--2);
        padding: 23px;
      }
    }
  }

  &__option-icon {
    width: 48px;
    height: 48px;
    justify-content: center;
    flex-shrink: 0;
    background: transparent;
  }

  &__icon-bg {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--map-base-dusk-o10);
    transition: background 0.2s;

    &--disabled {
      background: var(--map-base-dusk-o10);
    }

    &--loading {
      .ivyforms-loading-icon {
        animation: spin 1s linear infinite;
      }
    }
  }

  @keyframes spin {
    from {
      transform: rotate(0deg);
    }
    to {
      transform: rotate(360deg);
    }
  }

  &__option-title {
    color: var(--map-base-text-0);
  }

  &__option-desc {
    color: var(--map-base-text--1);
    max-width: 300px;
    font-size: 16px;
    font-style: normal;
    font-weight: 400;
    line-height: 22px; /* 137.5% */
  }

  &__option-text {
    flex: 1;
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
  }
  &__templates {
    align-items: flex-start;
    flex: 1 0 0;
    align-self: stretch;
  }

  &__templates-section {
    align-self: stretch;
    width: 100%;
    height: 100%;
  }

  &__select-label {
    color: var(--map-base-text-0);
    font-size: 18px;
    font-style: normal;
    font-weight: 500;
    line-height: 24px;
  }

  &__select-desc {
    color: var(--map-base-text--1);
  }

  &__search {
    width: 309px;
  }

  &__card-grid {
    align-self: stretch;
    width: 100%;
  }

  &__card-row {
    align-self: stretch;
    width: 100%;
    flex: 1 0 0;
  }

  &__card {
    border-radius: 8px;
    border: 1px solid var(--map-base-dusk-stroke--2);
    background: var(--map-ground-level-1-background);
    position: relative;
    width: 271px;
    min-width: 0;
    height: 100%;
  }

  &__card-container {
    &:hover {
      .template-modal__card-actions-overlay {
        opacity: 1;
        pointer-events: all;
      }
    }
  }

  &__image-wrapper {
    position: relative;
    width: 100%;
    height: 120px;
    min-height: 120px;
    max-height: 160px;
    overflow: hidden;
    border-radius: 8px 8px 0 0;
    background: var(--map-ground-level-1-foreground);
  }

  &__pro-badge {
    position: absolute;
    top: 9px;
    left: -4px;
    z-index: 2;
    background: var(--map-base-orange-background-0);
    border-radius: 4px;

    :deep(.ivyforms-pro-badge svg) {
      border-radius: 0 !important;
    }
  }

  &__image {
    width: 180px;
    height: fit-content;
    transition: transform 0.3s ease;
    border-radius: 8px 8px 0 0;
    box-shadow:
      0 1px 2px 0 rgba(18, 26, 38, 0.3),
      0 2px 6px 2px rgba(18, 26, 38, 0.15);
  }

  &__card-actions-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    opacity: 0;
    pointer-events: none;
    border-radius: 8px 8px 0 0;
    background:
      linear-gradient(0deg, var(--map-base-dusk-o20) 0%, var(--map-base-dusk-o20) 100%),
      var(--map-base-dusk-o05);
    z-index: 2;
    transition: all 0.3s ease;
    width: 100%;
    height: 100%;
  }

  &__overlay-buttons {
    align-self: stretch;

    .ivyforms-button-wrapper {
      :deep(.ivyforms-button-action),
      :deep(.ivyforms-button-action.is-loading) {
        min-width: 165px;
      }
    }

    // Purple background only for upgrade button (pro templates)
    .template-modal__upgrade-button {
      :deep(.ivyforms-button-action.ivyforms-button-action__priority__primary) {
        background: var(--map-base-purple-fill-0);

        &:hover {
          background: var(--map-base-purple-fill-0);
          opacity: 0.9;
        }
      }
    }
  }

  &__skeleton-image-placeholder {
    width: 100%;
    height: 120px;
    min-height: 120px;
    max-height: 160px;
    border-radius: 8px 8px 0 0;
    background: var(--map-base-dusk-o05);
    position: relative;
    overflow: hidden;

    .skeleton-bar,
    .skeleton-pill {
      border-radius: 999px;
      background: linear-gradient(
        90deg,
        var(--map-skeleton) 25%,
        var(--map-skeleton-to) 37%,
        var(--map-skeleton) 63%
      );
      background-size: 400% 100%;
      animation: skeleton-shine 1.2s ease-in-out infinite;
    }

    .skeleton-bar {
      height: 18px;
      width: 100%;
      margin-bottom: 14px;
    }

    .skeleton-bar-1 {
      height: 20px;
    }

    .skeleton-pill {
      height: 16px;
      width: 50%;
    }
  }

  @keyframes skeleton-shine {
    0% {
      background-position: -200% 0;
    }
    100% {
      background-position: 200% 0;
    }
  }

  &__empty-title,
  &__empty-desc {
    height: 20px;
    width: 0;
    opacity: 0;
  }

  &__card-footer-labels {
    align-self: stretch;
    background: var(--map-ground-level-1-background);
    padding: 12px 0 12px 0;
    min-height: 96px;
  }

  &__card-title,
  &__card-desc {
    @extend .ivyforms-pl-16;
    @extend .ivyforms-pr-16;
  }

  &__card-title {
    color: var(--map-base-text-0);
    line-height: 1.35;
    word-wrap: break-word;
    overflow-wrap: break-word;
    hyphens: auto;

    // Reduce font size for titles that wrap to multiple lines
    &--multiline {
      font-size: 16px !important;
      line-height: 1.4 !important;
    }
  }

  &__card-desc {
    color: var(--map-base-text--1);
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.4;
    max-height: 4.2em; // Fallback: 3 lines height (1.4em × 3)
    display: -webkit-box;
    -webkit-line-clamp: 3;
    line-clamp: 3;
    -webkit-box-orient: vertical;
    white-space: normal;
  }

  &__empty-state {
    text-align: center;
    color: var(--map-base-text--1);

    h4 {
      font-size: 1.125rem;
      font-weight: 600;
      color: var(--map-base-text-0);
    }

    p {
      font-size: 0.875rem;
    }
  }

  &__empty-icon {
    font-size: 3rem;
  }

  &__categories-sidebar {
    width: 300px;
    min-width: 300px;
  }

  &__templates-option-bar {
    align-items: flex-start;
    align-self: stretch;
  }
}

.template-preview {
  font-family: inherit;
  background: var(--map-ground-level-2-foreground);
  margin: 0 auto;

  &__header {
    gap: 16px;
    margin-bottom: 24px;
    position: sticky;
    top: 0;
    z-index: 10;
    background: var(--map-ground-level-2-foreground);
  }

  &__header-left {
    gap: 12px;
  }

  &__template-info {
    h3 {
      color: var(--map-base-text-0);
    }
  }

  &__header-right {
    gap: 8px;

    .ivyforms-button-wrapper {
      :deep(.ivyforms-button-action.ivyforms-button-action__priority__primary),
      :deep(.ivyforms-button-action.ivyforms-button-action__priority__primary.is-loading) {
        min-width: 165px;
      }
    }
  }

  &__form {
    border: 1px solid var(--map-base-dusk-o05);
    border-radius: 8px;
    background: var(--map-base-dusk-o05);
    transition: all 0.3s ease;
  }

  &__form-container {
    margin: 0 auto;

    h2 {
      font-size: 1.5rem;
      color: var(--map-base-text-0);
    }

    p {
      font-size: 1rem;
      color: var(--map-base-text--1);
    }
  }

  &__form-field {
    label {
      font-size: 1rem;
    }

    input,
    textarea {
      font-size: 1rem;
    }
  }

  &__submit-btn {
    width: auto;
    min-width: 140px;
    font-size: 1rem;
  }

  &__loading-fields {
    .template-preview__loading-message {
      color: var(--map-base-text--1);
    }
  }

  &__content {
    &.device-mobile .template-preview__form {
      max-width: 320px;
      margin: 0 auto;
      transition: all 0.3s ease;
    }

    &.device-tablet .template-preview__form {
      max-width: 768px;
      margin: 0 auto;
      transition: all 0.3s ease;
    }

    &.device-desktop .template-preview__form {
      max-width: 1024px;
      margin: 0 auto;
      transition: all 0.3s ease;
    }

    &.device-mobile {
      .template-preview__form-container {
        h2 {
          font-size: 1.2rem;
        }

        p {
          font-size: 0.9rem;
        }
      }

      .template-preview__form-field {
        label {
          font-size: 0.9rem;
        }

        input,
        textarea {
          font-size: 0.9rem;
        }
      }

      .template-preview__submit-btn {
        width: 100%;
        font-size: 0.9rem;
      }
    }

    &.device-tablet {
      .template-preview__form-container {
        h2 {
          font-size: 1.4rem;
        }

        p {
          font-size: 1rem;
        }
      }

      .template-preview__form-field {
        label {
          font-size: 0.95rem;
        }

        input,
        textarea {
          font-size: 0.95rem;
        }
      }

      .template-preview__submit-btn {
        width: auto;
        min-width: 120px;
        font-size: 0.95rem;
      }
    }

    &.device-desktop {
      .template-preview__form-container {
        h2 {
          font-size: 1.5rem;
        }

        p {
          font-size: 1rem;
        }
      }

      .template-preview__form-field {
        label {
          font-size: 1rem;
        }

        input,
        textarea {
          font-size: 1rem;
        }
      }

      .template-preview__submit-btn {
        width: auto;
        min-width: 140px;
        font-size: 1rem;
      }
    }

    :deep(.el-select__input) {
      border: 0;
      background: transparent;
      padding: 0;

      &:hover {
        border: 0;
        background: transparent;
      }

      &:focus,
      &:focus-visible {
        border: 0;
        background: transparent;
        outline: none;
        box-shadow: unset;
      }

      &:active {
        border: 0;
        background: transparent;
        box-shadow: unset;
      }
    }
  }

  &__device-tabs {
    border-radius: var(--radius-l, 8px);
    background: var(--map-base-dusk-o10);
    gap: var(--spacing-01, 8px) !important;

    :deep(.el-tabs__item.is-top.is-active) {
      background: #ffffff !important;
      border-radius: var(--radius-l, 8px);
      box-shadow: 0 4px 15px 0 rgba(0, 0, 0, 0.05);
      padding: 6px !important;
    }

    :deep(.el-tabs__item.is-top) {
      max-width: 36px;
    }
  }
}
</style>

<style lang="scss">
// Hide validation errors in template preview
.template-preview {
  .el-form-item__error {
    display: none !important;
  }

  .el-form-item.is-error .ivyforms-input.el-input .el-input__wrapper,
  .el-form-item.is-error .ivyforms-textarea .el-textarea__inner {
    border: 1px solid var(--map-base-dusk-stroke--2) !important;
    box-shadow: none !important;
    &:hover {
      border: 1px solid var(--map-base-dusk-stroke--2) !important;
      box-shadow: none !important;
    }
    &.is-focus {
      border: 1px solid var(--map-base-dusk-stroke--2) !important;
      box-shadow: none !important;
    }
  }
}

// Global styles for Element Plus dropdowns when template modal is open
body:has(.template-preview--active) {
  .el-select-dropdown,
  .el-popper.is-light {
    z-index: 99999 !important;
  }
}

// Backup: Force all select dropdowns to have high z-index
.el-select-dropdown {
  &.is-multiple {
    z-index: 99999 !important;
  }
}

.el-popper.is-light {
  z-index: 99999 !important;
}
</style>
