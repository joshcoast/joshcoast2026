<template>
  <div class="ivyforms-add-field-wrapper ivyforms-flex ivyforms-align-items-center">
    <button
      class="ivyforms-add-field ivyforms-flex ivyforms-justify-content-between ivyforms-align-items-center ivyforms-p-4"
      :disabled="props.disabled || props.comingSoon"
      :class="[
        'ivyforms-add-field__type__' + props.type,
        {
          'is-icon-start': props.iconStart,
          'is-icon-end': props.iconEnd || props.pro,
          'is-pro': props.pro,
        },
      ]"
    >
      <span
        class="ivyforms-add-field__left-content ivyforms-flex ivyforms-align-items-end ivyforms-gap-4"
      >
        <IvyIcon
          v-if="props.iconStart"
          :name="props.iconStart"
          :category="props.iconStartCategory"
          :type="props.iconStartType"
          class="ivyforms-add-field__icon-start"
          size="d"
        />
        <span class="ivyforms-add-field__name medium-14">
          {{ props.fieldName }}
        </span>
      </span>
      <span class="ivyforms-add-field__right-content">
        <IvyIcon
          v-if="props.iconEnd"
          :name="props.iconEnd"
          :category="props.iconEndCategory"
          :type="props.iconEndType"
          class="ivyforms-add-field__icon-end"
          size="d"
        />
      </span>
      <!-- ProBadge outside button to keep it visible when disabled -->
      <ProBadge v-if="props.pro" image="pro-bolt" size="s" class="ivyforms-add-field__pro-badge" />
    </button>
    <!-- ComingSoonBadge outside button to keep it visible when disabled -->
    <ComingSoonBadge v-if="props.comingSoon" class="ivyforms-add-field__coming-soon" :size="'s'" />
  </div>
</template>

<script setup lang="ts">
import type { IconCategory } from '@/types/icons/icon-category'
import type { IconType } from '@/types/icons/icon-type'
import ProBadge from '@/views/_components/badges/ProBadge.vue'

interface Props {
  type?: 'fill' | 'stroke'
  disabled?: boolean
  comingSoon?: boolean
  iconStart?: string
  iconEnd?: string
  iconStartCategory?: IconCategory
  iconEndCategory?: IconCategory
  iconStartType?: IconType
  iconEndType?: IconType
  fieldName?: string
  fieldType?: string
  pro?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  type: 'fill',
  disabled: false,
  iconStart: '', // Default value for iconStart
  iconEnd: '', // Default value for iconEnd
  iconStartCategory: 'global',
  iconEndCategory: 'global',
  iconStartType: 'fill',
  iconEndType: 'fill',
  fieldName: '',
  fieldType: 'text',
  pro: false,
})
</script>

<style scoped lang="scss">
.ivyforms-add-field-wrapper {
  position: relative;
}

.ivyforms-add-field {
  position: relative;
  white-space: nowrap;
  height: 32px;
  width: 100%;
  color: var(--map-base-text-0);
  cursor: move;

  &.sortable-ghost {
    display: none;
  }

  &__name {
    line-height: 22px; /* 142.857% */
    border-radius: 4px;
  }

  &#{&}__type__fill {
    background: var(--map-base-dusk-o05);
    border: none;

    &:hover:not(:disabled) {
      background: var(--map-hover);
    }
  }
  &#{&}__type__stroke {
    border: 1px solid var(--map-base-dusk--2);
    background: transparent;

    &:hover:not(:disabled) {
      border: 1px solid var(--map-base-dusk--1);
    }
  }
  &__icon-end {
    fill: var(--map-accent-amber-fill-0);
  }
  &__icon-start {
    fill: var(--map-base-dusk-symbol-2);
  }

  // Disabled
  &:disabled {
    cursor: not-allowed;

    .ivyforms-add-field__left-content,
    .ivyforms-add-field__right-content {
      opacity: 0.5;
    }
  }

  &__right-content {
    align-self: flex-end;
  }

  &__coming-soon {
    position: absolute;
    right: 4px;
    top: 50%;
    transform: translateY(-50%);
  }
  &__pro-badge {
    background: transparent !important;
    border: none !important;
  }
}
</style>
