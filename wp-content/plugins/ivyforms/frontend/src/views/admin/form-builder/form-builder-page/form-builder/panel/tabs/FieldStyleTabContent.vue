<template>
  <IvyScrollbar modifier="outside-vertical">
    <div class="ivyforms-field-style">
      {{ getLabel('style_data') }}
    </div>
  </IvyScrollbar>
</template>
<script setup lang="ts">
import { useLabels } from '@/composables/useLabels'
const { getLabel } = useLabels()
</script>
