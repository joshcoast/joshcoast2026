<template>
  <IvyPopover
    ref="popoverRef"
    trigger="click"
    :width="showUrlInput ? 350 : 200"
    popper-background="level-2-foreground"
    :placement="showUrlInput ? 'bottom-end' : 'bottom'"
    @hide="onHide"
  >
    <template v-if="!showUrlInput">
      <IvyButtonAction
        type="ghost"
        class="insert-image-button"
        priority="secondary"
        @click.stop.prevent="showUrlInput = true"
      >
        {{ getLabel('insert_image_by_url') }}
      </IvyButtonAction>

      <el-upload :auto-upload="false" :show-file-list="false" :on-change="uploadImage">
        <template #trigger>
          <IvyButtonAction type="ghost" priority="secondary">
            {{ getLabel('upload_image') }}
          </IvyButtonAction>
        </template>
      </el-upload>
    </template>

    <template v-else>
      <div class="ivyforms-flex ivyforms-gap-8">
        <IvyTextInput v-model="imageUrl" :placeholder="getLabel('image_url')" />
        <IvyButtonAction @click="applyImageUrl" @click.stop.prevent>
          {{ getLabel('save') }}</IvyButtonAction
        >
      </div>
      <IvyCheckbox v-model="openInNewTab" class="ivyforms-ml-8">
        {{ getLabel('open_in_new_tab') }}
      </IvyCheckbox>
    </template>

    <template #reference>
      <IvyButtonOption
        size="s"
        type="ghost"
        priority="white"
        icon-start-category="text-formatting"
        icon-start="image"
        @click.stop.prevent
      />
    </template>
  </IvyPopover>
</template>

<script setup lang="ts">
import { ref, unref } from 'vue'
import { Editor } from '@tiptap/vue-3'
import type { UploadFile } from 'element-plus'
import IvyTextInput from '@/views/_components/input/IvyTextInput.vue'
import IvyButtonOption from '@/views/_components/button/IvyButtonOption.vue'
import IvyButtonAction from '@/views/_components/button/IvyButtonAction.vue'
import { useLabels } from '@/composables/useLabels'
const { getLabel } = useLabels()

const props = defineProps<{
  editor: Editor
}>()

const popoverRef = ref(null)
const showUrlInput = ref<boolean>(false)
const imageUrl = ref<string | null>(null)
const openInNewTab = ref<boolean>(false)

const uploadImage = async (file: UploadFile) => {
  const url = await readFileDataUrl(file.raw)
  props.editor.commands.setImage({ src: url })
  unref(popoverRef).hide()
}

const applyImageUrl = () => {
  props.editor.commands.setImage({ src: imageUrl.value })
  unref(popoverRef).hide()
}

const readFileDataUrl = (file: File): Promise<string> => {
  const reader = new FileReader()

  return new Promise((resolve, reject) => {
    reader.onload = (readerEvent: ProgressEvent<FileReader>) => {
      if (readerEvent.target?.result) {
        resolve(readerEvent.target.result as string)
      } else {
        reject(new Error('Failed to read file'))
      }
    }
    reader.onerror = reject

    reader.readAsDataURL(file)
  })
}

const onHide = () => {
  imageUrl.value = null
  showUrlInput.value = false
}
</script>
<style lang="scss">
.ivyforms-popover-popper {
  .insert-image-button {
    .ivyforms-button-action {
      span {
        display: flex;
        text-overflow: unset;
        overflow: unset;
      }
    }
  }
}
</style>
