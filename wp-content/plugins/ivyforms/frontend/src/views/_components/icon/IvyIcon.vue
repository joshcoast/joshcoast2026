<template>
  <div class="ivyforms-icon">
    <!-- The ESLint warning is disabled with a
    comment since the SVG content is safe -
    it's loaded from the plugin's own asset files,
    not from user input. -->
    <!-- eslint-disable vue/no-v-html -->
    <div
      v-if="svgContent"
      class="ivyforms-icon__svg"
      :class="[
        `size-${props.size}`,
        `name-${props.name}`,
        `type-${props.type}`,
        `category-${props.category}`,
      ]"
      v-html="svgContent"
    />
    <!-- eslint-enable vue/no-v-html -->
  </div>
</template>

<script setup lang="ts">
import { computed, ref, watchEffect } from 'vue'
import type { IconCategory } from '@/types/icons/icon-category'
import type { IconType } from '@/types/icons/icon-type'

type Props = {
  category?: IconCategory
  name: string
  type?: IconType
  color?: string
  colorHover?: string
  size?: 'l' | 'd' | 's' | 'xs'
  outerSize?: string
}

const props = withDefaults(defineProps<Props>(), {
  category: 'global',
  type: 'fill',
  color: '',
  colorHover: '',
  size: 'd',
  outerSize: '24px',
})

const svgContent = ref('')

const colorHoverComputed = computed(() => props.colorHover || props.color)

const loadIcon = async (category: string, type: string, name: string) => {
  try {
    // Categories that have type subdirectories (fill, line, outline, etc.)
    const categoriesWithTypes = ['global', 'builder', 'arrows', 'promotion']

    // Build the relative path based on category structure
    const relativePath = categoriesWithTypes.includes(category)
      ? `src/assets/icons/${category}/${type}/${name}.svg`
      : `src/assets/icons/${category}/${name}.svg`

    // In production, use the WordPress plugin URL
    const iconPath = `${window.wpIvyUrls?.pluginURL || ''}frontend/${relativePath}`

    // Fetch the SVG content
    const response = await fetch(iconPath)
    if (!response.ok) {
      svgContent.value = ''
      return
    }

    svgContent.value = await response.text()
  } catch {
    // Silently fail for missing icons
    svgContent.value = ''
  }
}

watchEffect(async () => {
  await loadIcon(props.category, props.type, props.name)
})
</script>

<style lang="scss">
// Icon
.ivyforms-icon {
  width: v-bind('outerSize');
  height: v-bind('outerSize');
  display: flex;
  align-items: center;
  justify-content: center;
  flex: none;

  // SVG
  &__svg {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;

    // Ensure SVG elements inside use full dimensions
    :deep(svg) {
      width: 100%;
      height: 100%;
    }

    // Large
    &.size-l {
      transform: scale(1.2);
    }

    // Default
    &.size-d {
      transform: scale(1);
    }

    // Small
    &.size-s {
      transform: scale(0.8);
    }

    // Extra Small
    &.size-xs {
      transform: scale(0.6);
    }

    // Global, Arrows, Text Formatting & Templates
    &.category-global,
    &.category-arrows,
    &.category-text-formatting,
    &.category-templates {
      fill: v-bind('color');
      stroke: v-bind('color');

      // Fill
      &.type-fill *:not(g) {
        fill: inherit;
        stroke: none;

        // Stroke Path
        &.stroke-path {
          fill: none;
          stroke: inherit;
        }
      }

      // Fill Duo
      &.type-fill-duo *:not(g) {
        fill: inherit;
        stroke: none;

        // Stroke Path
        &.stroke-path {
          fill: none;
          stroke: inherit;
        }
      }

      // Line
      &.type-line *:not(g) {
        fill: none;

        // Fill Path
        &.fill-path {
          fill: inherit;
          stroke: none;
        }

        // Stroke Path
        &.stroke-path {
          stroke: inherit;
          fill: none;
        }
      }

      // Outline
      &.type-outline *:not(g) {
        fill: inherit;
        stroke: none;

        // Stroke Path
        &.stroke-path {
          fill: none;
          stroke: inherit;
        }
      }

      // Broken
      &.type-broken *:not(g) {
        fill: inherit;
        stroke: none;

        // Stroke Path
        &.stroke-path {
          fill: none;
          stroke: inherit;
        }
      }
    }
  }

  // Hover
  &:hover,
  &[aria-describedby] {
    // SVG
    .ivyforms-icon__svg {
      // Global & Arrows
      &.category-global,
      &.category-arrows,
      &.category-templates {
        fill: v-bind('colorHoverComputed');
        stroke: v-bind('colorHoverComputed');
      }
    }
  }
}
</style>
