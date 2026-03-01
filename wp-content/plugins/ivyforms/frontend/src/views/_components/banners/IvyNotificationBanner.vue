<template>
  <div
    v-if="isVisible"
    :class="[
      'ivyforms-notification-banner ivyforms-flex ivyforms-py-12 ivyforms-px-16 ivyforms-flex-direction-column ivyforms-justify-content-center',
      `ivyforms-notification-banner--state-${state}`,
    ]"
  >
    <div
      class="ivyforms-flex ivyforms-justify-content-center ivyforms-align-items-center ivyforms-gap-8"
    >
      <IvyIcon name="info" type="fill-duo" category="global" color="var(--map-base-dusk-fill--4)" />
      <div class="ivyforms-flex ivyforms-gap-8">
        <span v-if="title" class="ivyforms-notification-banner__title medium-14">{{ title }}</span>
        <span v-if="description" class="ivyforms-notification-banner__desc regular-14">{{
          description
        }}</span>
        <IvyLink
          v-if="link && linkText"
          class="ivyforms-notification-banner__link"
          :href="link"
          size="s"
          target="_blank"
        >
          {{ linkText }}
        </IvyLink>
      </div>
    </div>
    <IvyButtonAction
      class="ivyforms-notification-banner__close-icon"
      priority="tertiary"
      size="l"
      icon-only
      icon-start="close-circle"
      icon-start-type="fill"
      icon-color="var(--map-base-dusk-fill--4)"
      type="ghost"
      :aria-label="getLabel('close')"
      @click="closeBanner"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import IvyButtonAction from '@/views/_components/button/IvyButtonAction.vue'
import IvyIcon from '@/views/_components/icon/IvyIcon.vue'
import IvyLink from '@/views/_components/link/IvyLink.vue'
import { useLabels } from '@/composables/useLabels'

interface Props {
  title?: string
  description?: string
  link?: string
  linkText?: string
  state?: 'default' | 'success' | 'danger' | 'warning' | 'brand' | 'secondary'
  modelValue?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  title: '',
  description: '',
  link: '',
  linkText: '',
  state: 'default',
  modelValue: false,
})

const emit = defineEmits<{
  close: []
  'update:modelValue': [value: boolean]
}>()

const isVisible = ref(props.modelValue)

// Watch for external changes to modelValue
watch(
  () => props.modelValue,
  (newValue) => {
    isVisible.value = newValue
  },
)

const { getLabel } = useLabels()

const closeBanner = () => {
  isVisible.value = false
  emit('update:modelValue', false)
  emit('close')
}
</script>

<style lang="scss" scoped>
@use 'sass:map';

.ivyforms-notification-banner {
  border-radius: 4px;
  position: relative;

  &__close-icon {
    position: absolute;
    right: 6px;

    :deep(.ivyforms-button-action:hover) {
      .ivyforms-button-action__transition {
        opacity: 0 !important;
      }
    }
  }

  &__title,
  &__desc {
    color: var(--map-base-dusk-fill--4);
  }

  &__link {
    :deep(.ivyforms-link) {
      color: var(--map-base-dusk-fill--4);
      font-weight: 400;
      text-decoration: underline;
    }
  }

  // --- State color definitions (same as IvyNotification) ---
  $notification-states: (
    default: (
      bg: var(--map-base-dusk-fill-4),
    ),
    brand: (
      bg: var(--map-base-brand-fill-0),
    ),
    secondary: (
      bg: var(--map-base-purple-fill-0),
    ),
    warning: (
      bg: var(--map-status-warning-fill-0),
    ),
    danger: (
      bg: var(--map-status-error-fill-0),
    ),
    success: (
      bg: var(--map-status-success-fill-0),
    ),
  );

  // --- Loop through states ---
  @each $name, $colors in $notification-states {
    &--state-#{$name} {
      background-color: map.get($colors, bg);
    }
  }
}
</style>
