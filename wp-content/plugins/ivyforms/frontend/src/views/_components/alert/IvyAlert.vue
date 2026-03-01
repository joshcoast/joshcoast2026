<template>
  <div class="ivyforms-alert" :class="['ivyforms-alert__type__' + props.type]">
    <div class="ivyforms-alert__icon">
      <IvyIcon
        :name="getIconName()"
        type="fill-duo"
        size="l"
        :color="'var(--map-base-dusk-fill--4)'"
      />
    </div>
    <div v-if="props.title" class="ivyforms-alert__title medium-14">{{ props.title }}</div>
    <div v-if="props.description" class="ivyforms-alert__desc regular-12">
      {{ props.description }}
    </div>
  </div>
</template>

<script setup lang="ts">
import IvyIcon from '@/views/_components/icon/IvyIcon.vue'

interface Props {
  type?: 'info' | 'success' | 'warning' | 'error' | 'default'
  title?: string
  description?: string
}

const props = withDefaults(defineProps<Props>(), {
  type: 'info',
  title: '',
  description: '',
})

const getIconName = () => {
  if (props.type === 'success') {
    return 'check-circle'
  }

  if (props.type === 'warning') {
    return 'danger'
  }

  if (props.type === 'error') {
    return 'danger'
  }

  if (props.type === 'default') {
    return 'sticker'
  }
  return 'info'
}
</script>

<style lang="scss">
@use 'sass:list' as *;
@use 'sass:meta' as *;
// Alert
.ivyforms-alert {
  display: inline-flex;
  padding: 8px 16px;
  align-items: center;
  gap: 8px;
  border: 1px solid var(--map-base-dusk-fill--4);
  background: var(--map-base-dusk-fill-4);
  border-radius: 4px;

  $title_and_desc: '.ivyforms-alert__title, .ivyforms-alert__desc';

  // Icon
  &__icon {
    display: flex;
  }

  // Type
  &__type {
    // Success
    &__success {
      background: var(--map-status-success-fill-0);
    }

    // Warning
    &__warning {
      background: var(--map-status-warning-fill-0);
    }

    // Error
    &__error {
      background: var(--map-status-error-fill-0);
    }

    // Info
    &__info {
      background: var(--map-base-purple-fill-0);
    }
  }
  // Types
  $types: (
    'info': (
      var(--primitive-white),
    ),
    'success': (
      var(--primitive-white),
    ),
    'warning': (
      var(--primitive-white),
    ),
    'error': (
      var(--primitive-white),
    ),
    'default': (
      var(--map-base-dusk-fill--4),
    ),
  );
  // Function to process each type value if is array
  @each $type, $values in $types {
    &.ivyforms-alert__type__#{$type} {
      .ivyforms-alert__icon {
        svg {
          fill: nth($values, 1);
        }
      }
      // Content
      #{$title_and_desc} {
        color: nth($values, 1);
        text-align: start;
      }
    }
  }
}
</style>
