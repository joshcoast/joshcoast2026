<template>
  <PageSection>
    <router-view v-slot="{ Component }">
      <component :is="Component" @created="handleCreated" />
    </router-view>
  </PageSection>
</template>

<script setup lang="ts">
const emit = defineEmits<{
  (e: 'update:isAddingNotification', value: boolean): void
}>()

const handleCreated = () => {
  emit('update:isAddingNotification', false)
}
</script>
