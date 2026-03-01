<template>
  <div
    :class="[
      'ivyforms-notification ivyforms-flex ivyforms-align-items-center ivyforms-gap-12 ivyforms-p-8',
      `ivyforms-notification--type-${type}`,
      `ivyforms-notification--state-${state}`,
      `ivyforms-notification--style-${style}`,
    ]"
    role="alert"
    :aria-live="state === 'danger' ? 'assertive' : 'polite'"
  >
    <!-- Content -->
    <div class="ivyforms-notification__content ivyforms-flex-1">
      <!-- Title -->
      <div v-if="title" class="ivyforms-notification__title ivyforms-flex ivyforms-gap-4">
        <IvyIcon
          v-if="showTitleIcon"
          :name="getTitleIconName()"
          :type="getTitleIconType()"
          :category="getTitleIconCategory()"
          size="s"
          :color="getTitleIconColor()"
          class="ivyforms-notification__title-icon"
        />
        <span
          class="ivyforms-notification__title-text medium-14 ivyforms-flex-1 ivyforms-align-items-center ivyforms-mt-2"
          >{{ title }}</span
        >
      </div>

      <!-- Description -->
      <div
        v-if="description"
        class="ivyforms-notification__description ivyforms-flex ivyforms-gap-4"
      >
        <IvyIcon
          v-if="showDescriptionIcon"
          :name="getDescriptionIconName()"
          :type="getDescriptionIconType()"
          :category="getDescriptionIconCategory()"
          size="s"
          :color="getDescriptionIconColor()"
          class="ivyforms-notification__description-icon ivyforms-mt-2"
        />
        <span
          class="ivyforms-notification__description-text regular-14 ivyforms-flex-1 ivyforms-align-items-center ivyforms-mt-2"
        >
          {{ description }}
          <a
            v-if="showLink"
            class="ivyforms-notification__description-text__link"
            :href="linkUrl"
            :target="linkTarget"
            >{{ linkText }}
          </a>
        </span>
      </div>
    </div>

    <!-- Actions -->
    <div
      v-if="showButton || $slots.actions"
      class="ivyforms-notification__actions ivyforms-flex ivyforms-align-items-center ivyforms-gap-4"
    >
      <slot name="actions">
        <IvyButtonAction
          v-if="showButton"
          :priority="getButtonPriority()"
          :type="getButtonType()"
          size="xs"
          @click="handleButtonClick"
        >
          {{ buttonLabel || getLabel('action') }}
        </IvyButtonAction>
      </slot>
    </div>
  </div>
</template>

<script setup lang="ts">
import IvyIcon from '@/views/_components/icon/IvyIcon.vue'
import IvyButtonAction from '@/views/_components/button/IvyButtonAction.vue'
import type { IconType } from '@/types/icons/icon-type'
import type { IconCategory } from '@/types/icons/icon-category'
import { useLabels } from '@/composables/useLabels'
import { useTheme } from '@/composables/useTheme'

const { getLabel } = useLabels()
const { isDark } = useTheme()

type NotificationType = 'stripe'
type NotificationState = 'default' | 'success' | 'danger' | 'warning' | 'upcoming' | 'pro' | 'notes'
type NotificationStyle = 'fill' | 'stripe'

export interface IvyNotificationProps {
  type?: NotificationType
  state?: NotificationState
  style?: NotificationStyle
  title?: string
  description?: string
  showIcon?: boolean
  showTitleIcon?: boolean
  showDescriptionIcon?: boolean
  showButton?: boolean
  buttonLabel?: string
  showLink?: boolean
  linkText?: string
  linkUrl?: string
  linkTarget?: '_self' | '_blank'
}

const props = withDefaults(defineProps<IvyNotificationProps>(), {
  type: 'stripe',
  state: 'default',
  style: 'fill',
  title: '',
  description: '',
  showIcon: false,
  showTitleIcon: true,
  showDescriptionIcon: true,
  showButton: false,
  buttonLabel: '',
  showLink: false,
  linkText: '',
  linkUrl: '',
  linkTarget: '_blank',
})

const emit = defineEmits<{
  buttonClick: []
}>()

// Icon logic based on state
const getIconName = () => {
  switch (props.state) {
    case 'success':
    case 'notes':
      return 'check-circle'
    case 'danger':
    case 'warning':
      return 'danger'
    case 'upcoming':
      return 'upcoming-soon-colored'
    case 'pro':
      return 'bolt-lightning'
    default:
      return 'info'
  }
}

const getButtonPriority = () => {
  switch (props.state) {
    case 'success':
    case 'notes':
      return 'primary'
    case 'danger':
      return 'danger'
    case 'warning':
      return 'warning'
    case 'upcoming':
    case 'pro':
      return 'secondary'
    default:
      return 'tertiary'
  }
}
const getButtonType = () => {
  switch (props.state) {
    case 'default':
      return 'border'
    default:
      return 'fill'
  }
}

const getIconType = (): IconType => {
  switch (props.state) {
    case 'pro':
      return 'fill'
    case 'upcoming':
      return isDark() ? 'outline' : 'fill'
    default:
      return 'fill-duo'
  }
}

const getIconCategory = (): IconCategory => {
  switch (props.state) {
    case 'upcoming':
      return 'promotion'
    default:
      return 'global'
  }
}

const getIconColor = () => {
  switch (props.state) {
    case 'success':
      return 'var(--map-status-success-symbol-0)'
    case 'danger':
      return 'var(--map-status-error-symbol-0)'
    case 'warning':
      return 'var(--map-status-warning-symbol-0)'
    case 'upcoming':
      return 'var(--map-base-purple-symbol-0)'
    case 'pro':
      return 'var(--map-accent-amber-symbol-0)'
    case 'notes':
      return 'var(--map-status-success-symbol-0)'
    default:
      return 'var(--map-base-dusk-symbol-0)'
  }
}

// Title icon logic
const getTitleIconName = () => getIconName()
const getTitleIconType = (): IconType => getIconType()
const getTitleIconCategory = (): IconCategory => getIconCategory()
const getTitleIconColor = () => getIconColor()

// Description icon logic
const getDescriptionIconName = () => getIconName()
const getDescriptionIconType = (): IconType => getIconType()
const getDescriptionIconCategory = (): IconCategory => getIconCategory()
const getDescriptionIconColor = () => getIconColor()

// Event handlers
const handleButtonClick = () => {
  emit('buttonClick')
}
</script>

<style lang="scss">
@use 'sass:map';
.ivyforms-notification {
  border-radius: var(--Radius-radius-md, 8px);
  position: relative;
  transition: all 0.2s ease-in-out;
  flex-direction: row;

  &--style-fill {
    border: none;
    box-shadow: none;
  }

  &--style-stripe {
    border-left: 2px solid;
    box-shadow: none;
  }

  &__content {
    min-width: 0;
  }

  &__title,
  &__description {
    align-items: center;

    &-icon {
      flex-shrink: 0;
    }
  }
  &__license-banner {
    justify-content: center;
    .ivyforms-notification__content {
      flex: none !important;
    }
    .ivyforms-notification__description {
      align-items: center !important;
    }
  }

  // --- State color definitions ---
  $notification-states: (
    default: (
      bg: var(--map-base-dusk-o10),
      border: var(--map-base-dusk-stroke-0),
      text: var(--map-base-text-0),
    ),
    success: (
      bg: var(--map-status-success-o10),
      border: var(--map-status-success-stroke-0),
      text: var(--map-status-success-symbol-0),
    ),
    danger: (
      bg: var(--map-status-error-o10),
      border: var(--map-status-error-stroke-0),
      text: var(--map-status-error-symbol-0),
    ),
    warning: (
      bg: var(--map-status-warning-o10),
      border: var(--map-status-warning-stroke-0),
      text: var(--map-status-warning-symbol-0),
    ),
    upcoming: (
      bg: var(--map-accent-maroon-o10),
      border: var(--map-accent-maroon-stroke-0),
      text: var(--map-accent-maroon-fill-0),
    ),
    pro: (
      bg: var(--map-accent-amber-o10),
      border: var(--map-accent-amber-symbol-0),
      text: var(--map-accent-amber-symbol-0),
    ),
    notes: (
      bg: var(--map-status-success-o10),
      border: var(--map-status-success-stroke-0),
      text: var(--map-status-success-symbol-0),
    ),
  );

  // --- Loop through states ---
  @each $name, $colors in $notification-states {
    &--state-#{$name} {
      background-color: map.get($colors, bg);
      border-left-color: map.get($colors, border);

      .ivyforms-notification__title-text,
      .ivyforms-notification__description-text,
      .ivyforms-notification__description-text__link {
        color: map.get($colors, text);
      }
    }
  }
}
</style>
