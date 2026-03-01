<template>
  <div class="ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-16">
    <!-- Field Option Item 1 - with label and input -->
    <IvySkeleton animated :rows="1" variant="text">
      <template #template>
        <IvySkeletonItem variant="text" style="height: 20px" />
        <IvySkeletonItem variant="text" style="height: 40px" />
      </template>
    </IvySkeleton>
    <IvySkeleton animated :rows="1" variant="text">
      <template #template>
        <div class="ivyforms-flex ivyforms-gap-16">
          <IvySkeletonItem variant="text" style="height: 20px; width: 20px" />
          <IvySkeletonItem variant="text" style="height: 20px" />
        </div>
        <IvySkeletonItem
          variant="text"
          style="height: 16px; margin-left: 34px; width: 310px; margin-top: 4px"
        />
        <IvySkeletonItem
          variant="text"
          style="height: 16px; margin-left: 34px; width: 310px; margin-top: -2px"
        />
      </template>
    </IvySkeleton>
    <IvySkeleton animated :rows="1" variant="text">
      <template #template>
        <IvySkeletonItem variant="text" style="height: 20px" />
        <IvySkeletonItem variant="text" style="height: 40px" />
      </template>
    </IvySkeleton>
    <IvySkeleton animated :rows="1" variant="text">
      <template #template>
        <IvySkeletonItem variant="text" style="height: 20px" />
        <IvySkeletonItem variant="text" style="height: 40px" />
      </template>
    </IvySkeleton>
    <IvyDivider />
    <IvySkeleton animated :rows="1" variant="text">
      <template #template>
        <div class="ivyforms-flex ivyforms-gap-16">
          <IvySkeletonItem variant="text" style="height: 20px; width: 20px" />
          <IvySkeletonItem variant="text" style="height: 20px" />
        </div>
        <IvySkeletonItem
          variant="text"
          style="height: 16px; margin-left: 34px; width: 310px; margin-top: 4px"
        />
        <IvySkeletonItem
          variant="text"
          style="height: 16px; margin-left: 34px; width: 310px; margin-top: -2px"
        />
      </template>
    </IvySkeleton>
    <IvySkeleton animated :rows="1" variant="text">
      <template #template>
        <IvySkeletonItem variant="text" style="height: 20px" />
        <IvySkeletonItem variant="text" style="height: 40px" />
      </template>
    </IvySkeleton>
    <IvyDivider />
    <IvySkeleton animated :rows="1" variant="text">
      <template #template>
        <IvySkeletonItem variant="text" style="height: 20px" />
        <IvySkeletonItem variant="text" style="height: 40px" />
      </template>
    </IvySkeleton>
    <IvyDivider />
    <IvySkeleton animated :rows="1" variant="text">
      <template #template>
        <IvySkeletonItem variant="text" style="height: 20px" />
        <IvySkeletonItem variant="text" style="height: 40px" />
      </template>
    </IvySkeleton>
  </div>
</template>
