<template>
  <div class="ivyforms-card ivyforms-border-radius-8 ivyforms-overflow-hidden">
    <div class="ivyforms-card__header">
      <slot name="header">
        <div
          class="ivyforms-card__image-wrapper ivyforms-flex ivyforms-align-items-start ivyforms-justify-content-center"
        >
          <img
            v-if="props.image"
            :src="props.image"
            alt="Card image"
            class="ivyforms-card__image ivyforms-border-radius-8"
          />
          <div v-else class="ivyforms-card__image-placeholder ivyforms-width-100"></div>
        </div>
      </slot>
      <div
        class="ivyforms-card__star-wrapper ivyforms-flex ivyforms-align-items-center ivyforms-justify-content-center ivyforms-border-radius-8 ivyforms-cursor-pointer"
      >
        <IvyIcon
          name="star"
          :type="props.starred ? 'fill' : 'outline'"
          size="d"
          :color="
            props.starred ? 'var(--map-accent-amber-symbol-0)' : 'var(--map-base-dusk-symbol--1)'
          "
          class="ivyforms-card__star-icon ivyforms-cursor-pointer"
          @click.stop="handleStarClick"
        />
      </div>
    </div>
    <div
      class="ivyforms-card__footer ivyforms-flex ivyforms-flex-direction-column ivyforms-justify-content-start"
    >
      <slot name="footer">
        <div class="ivyforms-card__title medium-16 ivyforms-mb-4">{{ props.title }}</div>
        <div class="ivyforms-card__description regular-14 text-secondary">
          {{ props.description }}
        </div>
      </slot>
    </div>
  </div>
</template>

<script setup lang="ts">
import IvyIcon from '@/views/_components/icon/IvyIcon.vue'

const props = defineProps<{
  title: string
  description: string
  image?: string
  starred?: boolean
}>()

const emit = defineEmits<{
  'star-click': []
}>()

const handleStarClick = () => {
  emit('star-click')
}
</script>

<style scoped lang="scss">
.ivyforms-card {
  border: 1px solid var(--map-base-dusk-stroke--2);
  background: var(--map-ground-level-1-background);
  width: 271px;
  padding: 0;
  font-family: inherit;

  &__header {
    background: var(--map-ground-level-1-foreground);
    position: relative;
    min-height: 120px;
    flex: 0 0 auto;
  }

  &__image-wrapper {
    height: 140px !important;
    overflow: hidden;
    border-radius: 8px 8px 0 0;
    background: var(--map-ground-level-1-foreground);
  }

  &__image {
    width: 100%;
    height: 100%;
    display: block;
    object-fit: cover;
    border-radius: 4px 4px 0 0;
    background: var(--map-ground-level-1-foreground);
    transform: translateY(8px) scale(0.92);
    transform-origin: center top;
    object-position: 50% 18%;
    will-change: transform;
  }

  &__image-placeholder {
    height: 140px;
    background: var(--map-base-dusk-o05);
    border-radius: 8px 8px 0 0;
  }

  &__footer {
    background: var(--map-ground-level-1-foreground);
    flex: 1 1 auto;
  }

  &__title {
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2; /* allow up to 2 lines for longer titles */
    line-clamp: 2;
    -webkit-box-orient: vertical;
  }

  &__description {
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 3; /* allow up to 3 lines for descriptions */
    line-clamp: 3;
    -webkit-box-orient: vertical;
  }

  &__star-wrapper {
    position: absolute;
    top: 6px;
    right: 6px;
    background: var(--map-ground-level-1-foreground);
    border-radius: 50%;
    width: 40px;
    height: 40px;
    transition: all 0.2s ease;
    box-shadow: var(--shadow-200);
    z-index: 1000;

    &:hover {
      transform: scale(1.06);
      box-shadow: var(--shadow-300);
    }
  }
}
</style>
