<template>
  <div
    class="ivyforms-editor"
    :class="{
      'is-focused': editor?.isFocused,
    }"
  >
    <div
      class="ivyforms-editor__tabs-row ivyforms-flex ivyforms-justify-content-between ivyforms-align-items-center"
    >
      <IvyTabs
        v-model="activeTab"
        type="tonal"
        priority="secondary"
        size="l"
        :tabs="tabs.map((item) => ({ name: item.index, label: item.label }))"
      />
      <IvyPlaceholdersPopover
        v-if="fieldsPlaceholders && generalPlaceholders"
        :fields="fieldsPlaceholders"
        :general="generalPlaceholders"
        @insert-placeholder="onInsertPlaceholder"
      >
        <template #reference>
          <IvyTooltip :content="getLabel('insert_placeholders')">
            <IvyButtonAction
              size="s"
              priority="tertiary"
              icon-only
              icon-start="context-menu-dot"
              icon-start-type="fill"
              :aria-label="getLabel('placeholders')"
              class="ivyforms-editor__placeholders-btn"
            />
          </IvyTooltip>
        </template>
      </IvyPlaceholdersPopover>
    </div>
    <div class="tab-content">
      <div v-if="activeTab === 'visual'">
        <editor-content
          class="ivyforms-editor__content ivyforms-p-12"
          :editor="editor"
          @mousedown="focus"
          @keydown="handleKeydown"
        />

        <div v-if="editor" class="ivyforms-editor__control">
          <IvyButtonOption
            size="s"
            icon-only
            type="ghost"
            priority="white"
            icon-start-category="text-formatting"
            icon-start="text-bold"
            :active="editor.isActive('bold')"
            @click.stop.prevent="editor.chain().focus().toggleBold().run()"
          />

          <IvyButtonOption
            size="s"
            type="ghost"
            priority="white"
            icon-only
            icon-start-category="text-formatting"
            icon-start="text-italic"
            :active="editor.isActive('italic')"
            @click.stop.prevent="editor.chain().focus().toggleItalic().run()"
          />

          <IvyButtonOption
            size="s"
            type="ghost"
            priority="white"
            icon-only
            icon-start-category="text-formatting"
            icon-start="text-underline"
            :active="editor.isActive('underline')"
            @click.stop.prevent="editor.chain().focus().toggleUnderline().run()"
          />

          <IvyButtonOption
            size="s"
            type="ghost"
            priority="white"
            icon-only
            icon-start-category="text-formatting"
            icon-start="text-strikethrough"
            :active="editor.isActive('strike')"
            @click.stop.prevent="editor.chain().focus().toggleStrike().run()"
          />

          <IvyButtonOption
            size="s"
            type="ghost"
            priority="white"
            icon-only
            icon-start-category="text-formatting"
            icon-start="list-bulleted"
            :active="editor.isActive('bulletList')"
            @click.stop.prevent="editor.chain().focus().toggleBulletList().run()"
          />

          <IvyButtonOption
            size="s"
            type="ghost"
            priority="white"
            icon-only
            icon-start-category="text-formatting"
            icon-start="list-numbered"
            :active="editor.isActive('orderedList')"
            @click.stop.prevent="editor.chain().focus().toggleOrderedList().run()"
          />

          <IvyButtonOption
            size="s"
            type="ghost"
            priority="white"
            icon-only
            icon-start-category="text-formatting"
            icon-start="quotes"
            :active="editor.isActive('blockquote')"
            @click.stop.prevent="editor.chain().focus().toggleBlockquote().run()"
          />

          <IvyTextAlignPopoover :editor="editor" />

          <IvyColorPickerPopover :editor="editor" text-style-property="color">
            <template #reference>
              <IvyButtonOption
                size="s"
                type="ghost"
                priority="white"
                icon-only
                icon-start-category="text-formatting"
                icon-start="text-color"
                @click.stop.prevent
              />
            </template>
          </IvyColorPickerPopover>

          <IvyColorPickerPopover :editor="editor" text-style-property="backgroundColor">
            <template #reference>
              <IvyButtonOption
                size="s"
                type="ghost"
                priority="white"
                icon-only
                icon-start-category="text-formatting"
                icon-start="bg-highlight"
                name="bg-highlight"
                @click.stop.prevent
              />
            </template>
          </IvyColorPickerPopover>

          <IvyLinkPopover :editor="editor" />

          <IvyImagePopover :editor="editor" />

          <IvyHeadingPopover :editor="editor" />

          <IvyFontFamilyPopover :editor="editor" />
        </div>
      </div>
      <div v-if="activeTab === 'html'" class="ivyforms-editor__html-view">
        <textarea
          v-model="htmlContent"
          class="ivyforms-editor__html-textarea"
          @input="onHtmlInput"
          @blur="onHtmlBlur"
        ></textarea>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useEditor, EditorContent } from '@tiptap/vue-3'
import { Document } from '@tiptap/extension-document'
import { Text } from '@tiptap/extension-text'
import { Paragraph } from '@tiptap/extension-paragraph'
import { computed, watch, ref, onMounted } from 'vue'
import { Bold } from '@tiptap/extension-bold'
import TextStyle from '@tiptap/extension-text-style'
import TextAlign from '@tiptap/extension-text-align'
import { Heading } from '@tiptap/extension-heading'
import { Italic } from '@tiptap/extension-italic'
import { Underline } from '@tiptap/extension-underline'
import { Strike } from '@tiptap/extension-strike'
import { Blockquote } from '@tiptap/extension-blockquote'
import { BulletList } from '@tiptap/extension-bullet-list'
import { ListItem } from '@tiptap/extension-list-item'
import { OrderedList } from '@tiptap/extension-ordered-list'
import { Superscript } from '@tiptap/extension-superscript'
import { Subscript } from '@tiptap/extension-subscript'
import { FontFamily } from '@tiptap/extension-font-family'
import { Link } from '@tiptap/extension-link'
import { Image } from '@tiptap/extension-image'
import IvyEditorClassExtension from '@/views/_components/html-editor/extensions/ivyEditorClassExtension.ts'
import { IvyEditorColorExtension } from '@/views/_components/html-editor/extensions/ivyEditorColorExtension.ts'
import Indent from '@/views/_components/html-editor/extensions/indent.ts'
import { useLabels } from '@/composables/useLabels'
const { getLabel } = useLabels()

const activeTab = ref('visual')
const currentFontFamily = ref('Inter')
const currentHeadingLevel = ref(0)
const currentTextAlign = ref('left')
const htmlContent = ref('')

const tabs = [
  { label: getLabel('visual'), index: 'visual' },
  { label: getLabel('html'), index: 'html' },
]
const props = defineProps<{
  modelValue?: string
  fieldsPlaceholders?: Array<Record<string, unknown>>
  generalPlaceholders?: Array<Record<string, unknown>>
  ariaLabel?: string
}>()

const emit = defineEmits(['update:modelValue', 'input', 'insertPlaceholder'])

function onInsertPlaceholder(placeholder: string) {
  emit('insertPlaceholder', placeholder)
}

const localModelValue = computed({
  get() {
    return props.modelValue
  },
  set(value) {
    emit('update:modelValue', value)
  },
})

const editor = useEditor({
  extensions: [
    Document,
    Text,
    Paragraph,
    Bold,
    Italic,
    Underline,
    Strike,
    Blockquote,
    TextStyle,
    TextAlign.configure({
      types: ['heading', 'paragraph', 'blockquote', 'listItem'],
    }),
    Heading.configure({
      levels: [1, 2, 3, 4, 5, 6],
    }),
    BulletList.configure({
      HTMLAttributes: {
        class: 'ivyforms-bullet-list',
      },
    }),
    OrderedList.configure({
      HTMLAttributes: {
        class: 'ivyforms-ordered-list',
      },
    }),
    ListItem,
    Superscript,
    Subscript,
    FontFamily,
    Link.configure({
      openOnClick: false,
      defaultProtocol: 'https',
      HTMLAttributes: {
        class: 'ivyforms-link',
      },
    }),
    Image.configure({
      HTMLAttributes: {
        class: 'ivyforms-image',
      },
    }),
    IvyEditorClassExtension,
    IvyEditorColorExtension,
    Indent,
  ],
  content: localModelValue.value,
  editorProps: {
    attributes: {
      class: 'ivyforms-prosemirror-editor',
      spellcheck: 'false',
      'aria-label': props.ariaLabel || getLabel('text_editor'),
    },
  },
  onUpdate: () => {
    localModelValue.value = editor.value.getHTML()
  },
  onSelectionUpdate: ({ editor }) => {
    updateToolbarState(editor)
  },
})

const updateToolbarState = (editorInstance) => {
  if (!editorInstance) return

  // Update font family
  const fontFamily = editorInstance.getAttributes('textStyle').fontFamily
  currentFontFamily.value = fontFamily || 'Inter'

  // Update heading level
  for (let level = 1; level <= 6; level++) {
    if (editorInstance.isActive('heading', { level })) {
      currentHeadingLevel.value = level
      return
    }
  }
  currentHeadingLevel.value = 0 // Paragraph

  // Update text alignment
  const alignment =
    editorInstance.getAttributes('paragraph').textAlign ||
    editorInstance.getAttributes('heading').textAlign ||
    'left'
  currentTextAlign.value = alignment
}

onMounted(() => {
  if (editor.value) {
    updateToolbarState(editor.value)
    // Initialize HTML content
    htmlContent.value = editor.value.getHTML()
  }
})

watch(localModelValue, () => {
  const isSame = editor.value.getHTML() === localModelValue.value

  if (isSame) {
    return
  }

  editor.value.commands.setContent(localModelValue.value)
  // Update HTML content when model value changes
  htmlContent.value = editor.value.getHTML()
})

// Watch activeTab to sync HTML content when switching tabs
watch(activeTab, (newTab) => {
  if (newTab === 'html' && editor.value) {
    // Switching to HTML tab - update textarea with current editor HTML
    htmlContent.value = editor.value.getHTML()
  } else if (newTab === 'visual' && editor.value) {
    // Switching back to visual - update editor with HTML from textarea
    if (htmlContent.value !== editor.value.getHTML()) {
      editor.value.commands.setContent(htmlContent.value)
    }
  }
})

// Handle HTML textarea input
const onHtmlInput = (event: Event) => {
  const target = event.target as HTMLTextAreaElement
  htmlContent.value = target.value
}

// Handle HTML textarea blur - update editor when user leaves the textarea
const onHtmlBlur = () => {
  if (editor.value && htmlContent.value !== editor.value.getHTML()) {
    editor.value.commands.setContent(htmlContent.value)
    localModelValue.value = htmlContent.value
  }
}

const focus = () => {
  editor.value.commands.focus()
}

const handleKeydown = (event: KeyboardEvent) => {
  const isArrowKey = ['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(event.key)

  if (editor?.value?.isFocused && isArrowKey) {
    event.stopPropagation()
  }
}

const hasContent = () => {
  const nodesArray = editor?.value?.getJSON()?.content

  return !(
    Array.isArray(nodesArray) &&
    nodesArray.length === 1 &&
    !Object.hasOwn(nodesArray[0], 'content')
  )
}

defineExpose({
  editor,
  focus,
  hasContent,
})
</script>

<style lang="scss">
.ivyforms-editor {
  display: flex;
  padding: 8px;
  flex-direction: column;
  align-items: flex-start;
  gap: 6px;
  align-self: stretch;
  border-radius: 8px;
  border: 1px solid var(--map-base-dusk-stroke--2);

  .tab-content > div {
    width: 100% !important;
  }
  .tab-content {
    width: 100%;
  }
  ::v-deep(.tab-content > div) {
    width: 100%;
  }

  &__content {
    min-height: 80px;
    max-height: 300px;
    overflow-y: auto;
    cursor: text;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 6px;
    align-self: stretch;
    color: var(--map-base-text--2);
    border-radius: 8px;
    border: 1px solid var(--map-base-dusk-stroke--2);
    background: var(--map-base-dusk-o05);
    box-sizing: border-box;
    width: 100%;
    position: relative;

    // Make the ProseMirror editor fill the container properly
    .ProseMirror {
      width: 100%;
      min-height: 56px;
      outline: none;
      padding: 0;
      cursor: text;

      // Improve cursor visibility
      &:focus {
        outline: none;
        caret-color: var(--map-base-text--2);
      }

      // Show cursor when empty
      &:empty::before {
        content: attr(data-placeholder);
        float: left;
        color: var(--map-base-text--3);
        pointer-events: none;
        height: 0;
      }

      // Ensure cursor is visible in empty editor
      &:empty {
        cursor: text;
      }
    }

    &.ivyforms-editor-html {
      .ProseMirror {
        font-family: 'Courier New', monospace;
        white-space: pre-wrap;
        cursor: text;
      }
    }

    & > :first-child {
      margin-top: 0;
      outline: none;
    }

    img {
      display: block;
      height: auto;
      margin: 1.5rem 0;
      max-width: 100%;
      cursor: pointer;
    }

    a {
      cursor: pointer;
      color: var(--map-base-brand-text--1);
      text-decoration: underline;

      &:hover {
        text-decoration: none;
      }
    }

    // Fix list indentation - make both ul and ol have consistent indentation
    ul,
    ol {
      margin: 0.5rem 0;
      padding-left: 1.5rem; // Consistent padding for both list types

      li {
        margin: 0.25rem 0;

        p {
          margin: 0.25em 0;
        }
      }
    }

    ul {
      list-style-type: disc;
    }

    ol {
      list-style-type: decimal;
    }

    // Nested list styling
    ul ul,
    ol ol,
    ul ol,
    ol ul {
      margin: 0.25rem 0;
      padding-left: 1.5rem;
    }

    blockquote {
      border-left: 3px solid var(--map-base-brand-stroke--2);
      margin: 1.5rem 0;
      padding-left: 1rem;
      font-style: italic;
      color: var(--map-base-text--3);
    }

    h1,
    h2,
    h3,
    h4,
    h5,
    h6 {
      line-height: 1.1;
      color: var(--map-base-text--1);
      margin: 1rem 0 0.5rem 0;
      font-weight: bold;

      &:first-child {
        margin-top: 0;
      }
    }

    h1 {
      font-size: 1.6rem;
    }

    h2 {
      font-size: 1.5rem;
    }

    h3 {
      font-size: 1.4rem;
    }

    h4 {
      font-size: 1.3rem;
    }

    h5 {
      font-size: 1.2rem;
    }

    h6 {
      font-size: 1.1rem;
    }

    p {
      margin: 0.5rem 0;
      line-height: 1.5;

      &:first-child {
        margin-top: 0;
      }

      &:last-child {
        margin-bottom: 0;
      }
    }
  }

  &__control {
    padding: 4px;
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    border-top: 1px solid var(--map-base-dusk-stroke--2);
    margin-top: 6px;

    .ivyforms-form-item-select {
      width: 150px;
    }

    button[name='bg-highlight'] {
      svg {
        path:first-of-type {
          stroke: var(--map-base-dusk-symbol-2);
          opacity: 0.5;
        }
      }
    }
  }

  &__tabs-row {
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;
  }

  &__html-view {
    width: 100%;
    height: 100%;
  }

  &__html-textarea {
    width: 100%;
    min-height: 120px;
    max-height: 300px;
    padding: 12px;
    font-size: 13px;
    line-height: 1.5;
    border: 1px solid var(--map-base-dusk-stroke--2);
    border-radius: 4px;
    background-color: var(--map-base-dusk-o05);
    color: var(--map-base-text--1);
    overflow-y: auto;

    &:focus {
      outline: none;
      border-color: var(--map-base-purple-stroke-0);
      background-color: var(--map-base-light);
    }

    &::placeholder {
      color: var(--map-base-text--3);
    }
  }
}
</style>
