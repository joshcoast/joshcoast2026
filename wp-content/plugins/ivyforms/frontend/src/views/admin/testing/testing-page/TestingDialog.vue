<template>
  <div>
    <h1>Dialog Testing Page</h1>
    <div>
      Delete dialog(fill, middle) :
      <IvyButtonAction
        :priority="'white'"
        :size="'d'"
        :type="'fill'"
        @click="showDeleteDialog = true"
        >Open Delete
      </IvyButtonAction>
    </div>
    <br />
    <div>
      Edit dialog(r-alinged, middle) :
      <IvyButtonAction
        :priority="'warning'"
        :size="'d'"
        :type="'fill'"
        @click="showEditDialog = true"
        >Open Edit
      </IvyButtonAction>
    </div>
    <br />
    <div>
      Add new dialog(options, top-middle) :
      <IvyButtonAction
        :priority="'shadow-white'"
        :size="'d'"
        :type="'fill'"
        @click="showAddNewDialog = true"
        >Open Add New
      </IvyButtonAction>
    </div>
    <IvyDialog
      v-model="showDeleteDialog"
      title="Delete Item"
      subtitle="Are you sure you want to delete this item?"
      show-close
      align-center
    >
      <div>Delete dialog content goes here...</div>
      <template #footer>
        <IvyButtonAction
          :priority="'tertiary'"
          :size="'d'"
          :type="'fill'"
          :full-width="true"
          @click="showDeleteDialog = false"
        >
          Cancel
        </IvyButtonAction>
        <IvyButtonAction
          :priority="'danger'"
          :size="'d'"
          :type="'fill'"
          :full-width="true"
          @click="showDeleteDialog = false"
        >
          Delete
        </IvyButtonAction>
      </template>
    </IvyDialog>

    <IvyDialog
      v-model="showEditDialog"
      title="Edit Item"
      subtitle="Edit the details of the item"
      show-close
      align-center
    >
      <div>Edit dialog content goes here...</div>
      <template #footer>
        <IvyButtonAction
          :priority="'tertiary'"
          :size="'d'"
          :type="'fill'"
          @click="showEditDialog = false"
        >
          Cancel
        </IvyButtonAction>
        <IvyButtonAction
          :priority="'success'"
          :size="'d'"
          :type="'fill'"
          @click="showEditDialog = false"
        >
          Save
        </IvyButtonAction>
      </template>
    </IvyDialog>

    <IvyDialog
      v-model="showAddNewDialog"
      title="Add New Item"
      subtitle="Enter the details of the new item"
      show-close
    >
      <div>Add new dialog content goes here...</div>
      <template #footer>
        <div class="flex-left">
          <IvyButtonAction
            :priority="'danger'"
            :size="'d'"
            :type="'fill'"
            icon-only
            :icon-start="'trash'"
            @click="showAddNewDialog = false"
          >
            Add
          </IvyButtonAction>
          <IvyButtonAction
            :priority="'warning'"
            :size="'d'"
            :type="'fill'"
            icon-only
            :icon-start="'copy'"
            @click="showAddNewDialog = false"
          >
            Add
          </IvyButtonAction>
          <IvyButtonAction
            :priority="'shadow-white'"
            :size="'d'"
            :type="'fill'"
            icon-only
            :icon-start="'eye-opened'"
            @click="showAddNewDialog = false"
          >
            Add
          </IvyButtonAction>
        </div>
        <div class="flex-right">
          <IvyButtonAction
            :priority="'tertiary'"
            :size="'d'"
            :type="'fill'"
            @click="showAddNewDialog = false"
          >
            Cancel
          </IvyButtonAction>
          <IvyButtonAction
            :priority="'success'"
            :size="'d'"
            :type="'fill'"
            @click="showAddNewDialog = false"
          >
            Add
          </IvyButtonAction>
        </div>
      </template>
    </IvyDialog>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import IvyDialog from '@/views/_components/dialog/IvyDialog.vue'
import IvyButtonAction from '@/views/_components/button/IvyButtonAction.vue'

const showDeleteDialog = ref(false)
const showEditDialog = ref(false)
const showAddNewDialog = ref(false)
</script>

<style scoped>
h1 {
  font-size: 24px;
  margin-bottom: 16px;
}

button {
  margin-top: 16px;
  margin-right: 8px;
}

.flex-right {
  flex: 2;
}
</style>
