import { Extension } from '@tiptap/core'

const PreserveClassExtension = Extension.create({
  name: 'preserveClass',

  addGlobalAttributes() {
    return [
      {
        types: ['paragraph', 'heading', 'bulletList', 'orderedList', 'listItem', 'blockquote'],
        attributes: {
          class: {
            default: null,
            parseHTML: (element) => element.getAttribute('class'),
            renderHTML: (attributes) => {
              const existingClass = attributes.class || ''
              return {
                class: existingClass,
              }
            },
          },
        },
      },
    ]
  },
})

export default PreserveClassExtension
