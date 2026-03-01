<template>
  <PageSection has-table>
    <AllForms />
  </PageSection>
</template>
<script setup lang="ts">
import { useWcagColors } from '@/composables/useWcagColors'

// Initialize WCAG color system for AllFormsPageSection
useWcagColors()
</script>
