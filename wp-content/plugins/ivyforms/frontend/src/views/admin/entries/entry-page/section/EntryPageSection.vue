<template>
  <div
    class="ivyforms-entry-page-section ivyforms-flex ivyforms-flex-1 ivyforms-gap-8 ivyforms-flex-direction-column"
  >
    <PageSection class="ivyforms-pr-20">
      <IvyScrollbar modifier="outside-vertical">
        <EntryPageSectionContent />
      </IvyScrollbar>
    </PageSection>
  </div>
</template>
<script setup lang="ts"></script>
<style scoped lang="scss">
.ivyforms-entry-page-section {
  overflow: hidden;
  white-space: nowrap;
}
</style>
