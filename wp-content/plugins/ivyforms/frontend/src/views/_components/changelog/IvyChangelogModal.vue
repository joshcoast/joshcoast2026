<template>
  <IvyModal
    :visible="isVisible"
    :width="480"
    :close-on-overlay-click="false"
    :is-changelog="true"
    @update:visible="handleClose"
  >
    <div class="ivyforms-changelog-modal ivyforms-flex ivyforms-flex-direction-column">
      <div class="ivyforms-changelog-modal__header ivyforms-flex ivyforms-flex-direction-column">
        <div class="ivyforms-changelog-modal__header-top ivyforms-flex">
          <div class="ivyforms-changelog-modal__logo">
            <IvyLogo orientation="logoOnly" />
          </div>
          <IvyButtonAction
            priority="tertiary"
            size="s"
            icon-only
            icon-start="close"
            icon-start-type="outline"
            type="ghost"
            class="ivyforms-changelog-modal__close"
            @click="handleClose"
          >
          </IvyButtonAction>
        </div>
        <div class="ivyforms-changelog-modal__header-text">
          <h2 class="ivyforms-changelog-modal__title">
            {{ labels.whats_new_in_ivyforms }} {{ changelogData.version }}
          </h2>
        </div>
      </div>

      <div
        class="ivyforms-changelog-modal__content ivyforms-flex ivyforms-flex-direction-column"
        :style="{ height: contentMinHeight + 'px' }"
      >
        <!-- Navigation Arrows - Positioned relative to content, not slider -->
        <IvyButtonAction
          priority="tertiary"
          size="s"
          type="ghost"
          icon-only
          icon-start="chevron-left"
          icon-start-type="outline"
          icon-start-category="arrows"
          class="ivyforms-changelog-modal__arrow ivyforms-changelog-modal__arrow--left"
          :class="{ 'ivyforms-changelog-modal__arrow--hidden': currentSlide === 0 }"
          @click="previousSlide"
        >
        </IvyButtonAction>

        <IvyButtonAction
          priority="tertiary"
          size="s"
          type="ghost"
          icon-only
          icon-start="chevron-right"
          icon-start-type="outline"
          icon-start-category="arrows"
          class="ivyforms-changelog-modal__arrow ivyforms-changelog-modal__arrow--right"
          :class="{ 'ivyforms-changelog-modal__arrow--hidden': currentSlide >= totalSlides - 1 }"
          @click="nextSlide"
        />

        <!-- Slider Container -->
        <div
          class="ivyforms-changelog-modal__slider ivyforms-flex ivyforms-align-items-stretch ivyforms-justify-content-center"
        >
          <transition :name="slideDirection" mode="out-in">
            <div
              :key="currentSlide"
              class="ivyforms-changelog-modal__slide ivyforms-flex ivyforms-align-items-stretch ivyforms-justify-content-center"
            >
              <!-- Features Slide -->
              <div
                v-if="currentSlide === 0 && changelogData.items.features?.length"
                class="ivyforms-changelog-modal__section ivyforms-flex ivyforms-flex-direction-column"
              >
                <div class="ivyforms-changelog-modal__section-header ivyforms-flex">
                  <div
                    class="ivyforms-changelog-modal__icon ivyforms-changelog-modal__icon--feature"
                  >
                    <IvyIcon
                      name="star"
                      type="fill-duo"
                      size="d"
                      color="var(--map-base-purple-fill-0)"
                    />
                  </div>
                  <h3>{{ labels.new_features }}</h3>
                </div>
                <ul
                  class="ivyforms-changelog-modal__list ivyforms-flex ivyforms-flex-direction-column"
                >
                  <li
                    v-for="(item, index) in changelogData.items.features"
                    :key="`feature-${index}`"
                    class="ivyforms-changelog-modal__item ivyforms-flex"
                  >
                    <span class="ivyforms-changelog-modal__bullet"></span>
                    {{ item.text }}
                  </li>
                </ul>
              </div>

              <!-- Improvements Slide -->
              <div
                v-if="currentSlide === 1 && changelogData.items.improvements?.length"
                class="ivyforms-changelog-modal__section ivyforms-flex ivyforms-flex-direction-column"
              >
                <div class="ivyforms-changelog-modal__section-header ivyforms-flex">
                  <div
                    class="ivyforms-changelog-modal__icon ivyforms-changelog-modal__icon--improvement"
                  >
                    <IvyIcon
                      name="star"
                      type="fill-duo"
                      size="d"
                      color="var(--map-base-purple-fill-0)"
                    />
                  </div>
                  <h3>{{ labels.improvements }}</h3>
                </div>
                <ul
                  class="ivyforms-changelog-modal__list ivyforms-flex ivyforms-flex-direction-column"
                >
                  <li
                    v-for="(item, index) in changelogData.items.improvements"
                    :key="`improvement-${index}`"
                    class="ivyforms-changelog-modal__item ivyforms-flex"
                  >
                    <span class="ivyforms-changelog-modal__bullet"></span>
                    {{ item.text }}
                  </li>
                </ul>
              </div>

              <!-- Bug Fixes Slide -->
              <div
                v-if="currentSlide === 2 && changelogData.items.bugfixes?.length"
                class="ivyforms-changelog-modal__section ivyforms-flex ivyforms-flex-direction-column"
              >
                <div class="ivyforms-changelog-modal__section-header ivyforms-flex">
                  <div
                    class="ivyforms-changelog-modal__icon ivyforms-changelog-modal__icon--bugfix"
                  >
                    <IvyIcon
                      name="star"
                      type="fill-duo"
                      size="d"
                      color="var(--map-base-purple-fill-0)"
                    />
                  </div>
                  <h3>{{ labels.bug_fixes }}</h3>
                </div>
                <ul
                  class="ivyforms-changelog-modal__list ivyforms-flex ivyforms-flex-direction-column"
                >
                  <li
                    v-for="(item, index) in changelogData.items.bugfixes"
                    :key="`bugfix-${index}`"
                    class="ivyforms-changelog-modal__item ivyforms-flex"
                  >
                    <span class="ivyforms-changelog-modal__bullet"></span>
                    {{ item.text }}
                  </li>
                </ul>
              </div>
            </div>
          </transition>
        </div>

        <!-- Slide Indicators -->
        <div
          class="ivyforms-changelog-modal__indicators ivyforms-flex ivyforms-justify-content-center"
        >
          <span
            v-for="slide in totalSlides"
            :key="slide"
            class="ivyforms-changelog-modal__indicator"
            :class="{ 'ivyforms-changelog-modal__indicator--active': currentSlide === slide - 1 }"
            @click="goToSlide(slide - 1)"
          ></span>
        </div>
      </div>

      <div
        class="ivyforms-changelog-modal__footer ivyforms-flex ivyforms-justify-content-between ivyforms-align-items-center"
      >
        <div class="ivyforms-changelog-modal__footer-counter">
          {{ currentSlide + 1 }} / {{ totalSlides }}
        </div>
        <IvyButtonAction priority="primary" size="d" type="fill" @click="handleClose">
          {{ labels.got_it_thanks }}
        </IvyButtonAction>
      </div>
    </div>
  </IvyModal>
</template>

<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted, nextTick, watch } from 'vue'
import IvyModal from '@/views/_components/modal/IvyModal.vue'
import IvyButtonAction from '@/views/_components/button/IvyButtonAction.vue'
import IvyIcon from '@/views/_components/icon/IvyIcon.vue'
import IvyLogo from '@/views/_components/logo/IvyLogo.vue'
import { useApiClient } from '@/composables/useApiClient.ts'

const { request } = useApiClient()
interface ChangelogItem {
  text: string
  link?: string
}

interface ChangelogItems {
  date: string
  improvements?: ChangelogItem[]
  features?: ChangelogItem[]
  bugfixes?: ChangelogItem[]
}

interface ChangelogData {
  version: string
  oldVersion?: string
  shouldShow: boolean
  items: ChangelogItems
}

const changelogData = ref<ChangelogData>({
  version: '',
  oldVersion: '',
  shouldShow: false,
  items: {
    date: '',
    improvements: [],
    features: [],
    bugfixes: [],
  },
})
interface Props {
  visible?: boolean
}

const isLoading = ref(false)
const showChangelogModal = ref(false)

const initChangelog = async (): Promise<void> => {
  try {
    isLoading.value = true
    const response = await request('changelog', { method: 'GET' })

    const data = response?.data?.data?.data || {}
    changelogData.value = {
      version: data.version || '',
      oldVersion: data.oldVersion || '',
      shouldShow: !!data.shouldShow,
      items: {
        date: data.release_date || '',
        improvements: data.improvements || [],
        features: data.features || [],
        bugfixes: data.bugfixes || [],
      },
    }
    // Only show modal if shouldShow is true AND version !== oldVersion
    showChangelogModal.value = !!data.shouldShow && data.version !== data.oldVersion
  } catch (error) {
    console.error('Error fetching changelog:', error)
  } finally {
    isLoading.value = false
  }
}

const props = withDefaults(defineProps<Props>(), {
  visible: false,
})

const emit = defineEmits<{
  'update:visible': [value: boolean]
  close: []
}>()

const currentSlide = ref(0)
const slideDirection = ref('slide-left')
const scrollHeight = ref(280)
const contentMinHeight = ref(0)

const isVisible = computed({
  get: () => props.visible || showChangelogModal.value,
  set: (value) => emit('update:visible', value),
})

const labels = computed(() => window.wpIvyLabels || {})

// Calculate total slides based on available sections
const totalSlides = computed(() => {
  let count = 0
  if (changelogData.value.items.features?.length) count++
  if (changelogData.value.items.improvements?.length) count++
  if (changelogData.value.items.bugfixes?.length) count++
  return count
})

const nextSlide = () => {
  if (currentSlide.value < totalSlides.value - 1) {
    slideDirection.value = 'slide-left'
    currentSlide.value++
  }
}

const previousSlide = () => {
  if (currentSlide.value > 0) {
    slideDirection.value = 'slide-right'
    currentSlide.value--
  }
}

const goToSlide = (index: number) => {
  if (index >= 0 && index < totalSlides.value) {
    slideDirection.value = index > currentSlide.value ? 'slide-left' : 'slide-right'
    currentSlide.value = index
  }
}

const handleClose = () => {
  showChangelogModal.value = false
  emit('update:visible', false)
  emit('close')
}

// Calculate dynamic scroll height based on content
const calculateScrollHeight = () => {
  nextTick(() => {
    const section = document.querySelector('.ivyforms-changelog-section')
    if (section) {
      const header = section.querySelector('.ivyforms-changelog-section-header')
      const headerHeight = header?.clientHeight || 0
      const availableHeight = 320 - headerHeight - 40 // 320 min height - header - padding
      scrollHeight.value = Math.max(200, Math.min(280, availableHeight))
    }
  })
}

// Calculate dynamic min-height based on all content
const calculateContentMinHeight = () => {
  nextTick(() => {
    let maxHeight = 0
    const sections = [
      changelogData.value.items.features,
      changelogData.value.items.improvements,
      changelogData.value.items.bugfixes,
    ]
    sections.forEach((section) => {
      if (section?.length) {
        const estimatedHeight = 50 + section.length * 32 + 40
        maxHeight = Math.max(maxHeight, estimatedHeight)
      }
    })
    contentMinHeight.value = Math.min(450, Math.max(250, maxHeight))
  })
}

watch(
  () => currentSlide.value,
  () => {
    calculateScrollHeight()
  },
)

watch(
  () => showChangelogModal.value,
  (newVal) => {
    if (newVal) {
      currentSlide.value = 0
      calculateContentMinHeight()
    }
  },
)

onMounted(() => {
  initChangelog()
  calculateScrollHeight()
  calculateContentMinHeight()

  const handleKeydown = (event: KeyboardEvent) => {
    if (event.key === 'Escape' && isVisible.value) {
      handleClose()
    }
  }
  window.addEventListener('keydown', handleKeydown)
  onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown)
  })
})
</script>

<style lang="scss" scoped>
.ivyforms-changelog-modal {
  background: var(--map-ground-level-2-foreground);
  border-radius: 12px;
  overflow: hidden;

  &__header {
    gap: 10px;
    padding: 16px 16px 12px;
    border-bottom: 1px solid var(--map-base-dusk-separator-1);

    &-top {
      align-items: center;
      justify-content: space-between;
    }
    &-text {
      flex: 1;
    }
  }

  &__logo {
    flex-shrink: 0;
    img {
      width: 75px;
      height: auto;
    }
  }

  &__title {
    font-size: 16px;
    font-weight: 600;
    line-height: 1.3;
    color: var(--map-base-dusk-symbol-1);
    margin: 0 0 2px 0;
  }

  &__close {
    flex-shrink: 0;
    background: none;
    border: none;
    cursor: pointer;
    padding: 4px;
    color: var(--map-base-dusk-symbol-2);
    transition: color 0.2s;
    &:hover {
      color: var(--map-base-dusk-symbol-1);
    }
  }

  &__content {
    padding: 12px 0;
    gap: 12px;
    position: relative;
    transition: min-height 0.3s ease;
    overflow-y: auto;
    max-height: calc(100vh - 200px);
  }

  &__slider {
    position: relative;
    padding: 0 50px;
    align-items: stretch;
    justify-content: center;
  }

  &__slide {
    width: 100%;
    align-items: stretch;
    justify-content: center;
  }

  &__section {
    gap: 16px;
    width: 100%;
    padding: 8px 0;

    &-header {
      align-items: center;
      gap: 10px;
      h3 {
        font-size: 16px;
        font-weight: 600;
        color: var(--map-base-dusk-symbol-1);
        margin: 0;
      }
    }
  }

  &__icon {
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    flex-shrink: 0;
    &--feature {
    }
    &--improvement {
    }
    &--bugfix {
    }
  }

  &__list {
    list-style: none;
    padding: 0;
    margin: 0;
    gap: 10px;
  }

  &__item {
    align-items: flex-start;
    gap: 12px;
    font-size: 14px;
    line-height: 1.5;
    color: var(--map-base-dusk-symbol-2);
    padding: 0 4px;
  }

  &__bullet {
    flex-shrink: 0;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--map-primary-brand);
    margin-top: 7px;
  }

  &__arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    z-index: 10;
    flex-shrink: 0;
    &--left {
      left: 4px;
    }
    &--right {
      right: 4px;
    }
    &--hidden {
      visibility: hidden;
      opacity: 0;
      pointer-events: none;
      transition:
        visibility 0s 0.3s,
        opacity 0.3s linear;
    }
  }

  &__indicators {
    justify-content: center;
    gap: 6px;
    padding: 0 50px;
    margin-top: 4px;
  }

  &__indicator {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--map-base-dusk-separator-2);
    cursor: pointer;
    transition: all 0.3s ease;
    &--active {
      background: var(--map-primary-brand);
      width: 18px;
      border-radius: 3px;
    }
    &:hover {
      background: var(--map-primary-brand);
      opacity: 0.7;
    }
  }

  &__footer {
    justify-content: space-between;
    align-items: center;
    padding: 10px 16px 14px;
    border-top: 1px solid var(--map-base-dusk-separator-1);
    &-counter {
      font-size: 12px;
      font-weight: 500;
      color: var(--map-base-dusk-symbol-2);
    }
  }
}

.slide-left-enter-active,
.slide-left-leave-active,
.slide-right-enter-active,
.slide-right-leave-active {
  transition: all 0.3s ease;
}
.slide-left-enter-from {
  opacity: 0;
  transform: translateX(30px);
}
.slide-left-leave-to {
  opacity: 0;
  transform: translateX(-30px);
}
.slide-right-enter-from {
  opacity: 0;
  transform: translateX(-30px);
}
.slide-right-leave-to {
  opacity: 0;
  transform: translateX(30px);
}
</style>
