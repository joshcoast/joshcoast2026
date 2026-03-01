import '@tiptap/extension-text-style'
import { rgbStyleToHex } from '@/utils/utilColor'

import { Extension } from '@tiptap/core'

export type ColorOptions = {
  types: string[]
}

declare module '@tiptap/core' {
  interface Commands<ReturnType> {
    textColor: {
      /**
       * Set the text color
       * @param color The color to set
       * @example editor.commands.setColor('#ff0000')
       */
      setColor: (color: string) => ReturnType

      /**
       * Unset the text color
       * @example editor.commands.unsetColor()
       */
      unsetColor: () => ReturnType
    }
    textBackgroundColor: {
      /**
       * Set the text background color
       * @param color The color to set
       * @example editor.commands.setTextBackgroundColor('#ffff00')
       */
      setTextBackgroundColor: (color: string) => ReturnType

      /**
       * Unset the text background color
       * @example editor.commands.unsetTextBackgroundColor()
       */
      unsetTextBackgroundColor: () => ReturnType
    }
  }
}

/**
 * This extension allows you to color your text.
 * @see https://tiptap.dev/api/extensions/color
 */
export const IvyEditorColorExtension = Extension.create<ColorOptions>({
  name: 'IvyEditorColorExtension',

  addOptions() {
    return {
      types: ['textStyle'],
    }
  },

  addGlobalAttributes() {
    return [
      {
        types: this.options.types,
        attributes: {
          color: {
            default: null,
            parseHTML: (element) => {
              const color = element.style.color?.replace(/['"]+/g, '')
              return color ? rgbStyleToHex(color) : null
            },
            renderHTML: (attributes) => {
              if (!attributes.color) {
                return {}
              }
              const colorHex = rgbStyleToHex(attributes.color)
              return {
                style: `color: ${colorHex}`,
              }
            },
          },

          backgroundColor: {
            default: null,
            parseHTML: (element) => {
              const bgColor = element.style.backgroundColor?.replace(/['"]+/g, '')
              return bgColor ? rgbStyleToHex(bgColor) : null
            },
            renderHTML: (attributes) => {
              if (!attributes.backgroundColor) {
                return {}
              }
              const bgColorHex = rgbStyleToHex(attributes.backgroundColor)
              return {
                style: `background-color: ${bgColorHex}`,
              }
            },
          },
        },
      },
    ]
  },

  addCommands() {
    return {
      setColor:
        (color) =>
        ({ chain }) => {
          return chain().setMark('textStyle', { color }).run()
        },
      unsetColor:
        () =>
        ({ chain }) => {
          return chain().setMark('textStyle', { color: null }).removeEmptyTextStyle().run()
        },
      setTextBackgroundColor:
        (color) =>
        ({ chain }) => {
          return chain().setMark('textStyle', { backgroundColor: color }).run()
        },
      unsetTextBackgroundColor:
        () =>
        ({ chain }) => {
          return chain()
            .setMark('textStyle', { backgroundColor: null })
            .removeEmptyTextStyle()
            .run()
        },
    }
  },
})
