<template>
  <PageSection>
    <router-view v-slot="{ Component }">
      <component :is="Component" />
    </router-view>
  </PageSection>
</template>

<script setup lang="ts"></script>
