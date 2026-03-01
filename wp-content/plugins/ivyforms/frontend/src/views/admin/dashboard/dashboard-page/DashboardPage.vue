<template>
  <PageHeader />
</template>

<script setup lang="ts">
import { provide } from 'vue'
import { useLabels } from '@/composables/useLabels'
const { getLabel } = useLabels()

const title: string = getLabel('dashboard')
provide('pageTitle', title)
</script>
