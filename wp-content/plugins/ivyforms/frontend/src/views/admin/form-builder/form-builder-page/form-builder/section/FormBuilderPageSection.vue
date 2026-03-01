<template>
  <div
    class="ivyforms-form-builder-section ivyforms-flex ivyforms-flex-1 ivyforms-gap-8 ivyforms-flex-direction-column"
  >
    <FormBuilderPageSectionHeader />
    <PageSection>
      <IvyScrollbar modifier="outside-vertical">
        <FormBuilder />
      </IvyScrollbar>
    </PageSection>
  </div>
</template>
<style scoped lang="scss">
.ivyforms-form-builder-section {
  max-width: 100%;
  overflow: hidden;
  .el-scrollbar {
    border-radius: 12px;
    max-width: 100%;
  }

  :deep(.el-scrollbar__wrap) {
    max-width: 100%;
    overflow-x: hidden;
  }

  :deep(.el-scrollbar__view) {
    max-width: 100%;
  }
}
</style>
