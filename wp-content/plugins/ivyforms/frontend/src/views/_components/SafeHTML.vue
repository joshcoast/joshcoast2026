<template>
  <div ref="containerRef" class="ivyforms-safe-html"></div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, nextTick } from 'vue'
import DOMPurify, { type Config } from 'dompurify'

interface Props {
  html: string
  fieldType?: string
  allowedTags?: string[]
  allowedAttributes?: Record<string, string[]>
}

const props = withDefaults(defineProps<Props>(), {
  fieldType: 'unknown',
  allowedTags: undefined,
  allowedAttributes: undefined,
})

const containerRef = ref<HTMLDivElement>()

/**
 * Get DOMPurify configuration based on field type
 */
const getDOMPurifyConfig = (): Config => {
  const baseConfig: Config = {
    RETURN_DOM_FRAGMENT: false,
    RETURN_DOM: false,
    ALLOWED_URI_REGEXP:
      /^(?:(?:(?:f|ht)tps?|mailto|tel|callto|sms|cid|xmpp|data):|[^a-z]|[a-z+.-]+(?:[^a-z+.-:]|$))/i,
  }

  // Field-specific configurations
  switch (props.fieldType) {
    case 'signature':
      return {
        ...baseConfig,
        ALLOWED_TAGS: ['img'],
        ALLOWED_ATTR: ['src', 'alt', 'style', 'class', 'width', 'height'],
        ALLOWED_URI_REGEXP: /^data:image\/(png|jpeg|jpg|gif|svg\+xml);base64,/i,
      }

    case 'confirmation':
      // For confirmation messages that may contain signature images and formatted text
      return {
        ...baseConfig,
        ALLOWED_TAGS: [
          'p',
          'br',
          'strong',
          'b',
          'em',
          'i',
          'u',
          'a',
          'ul',
          'ol',
          'li',
          'span',
          'div',
          'img', // Allow images for signatures
          'h1',
          'h2',
          'h3',
          'h4',
          'h5',
          'h6',
          'blockquote',
        ],
        ALLOWED_ATTR: ['href', 'target', 'rel', 'class', 'style', 'src', 'alt', 'width', 'height'],
        ALLOWED_URI_REGEXP: /^(?:(?:(?:f|ht)tps?|mailto|tel|data):)/i, // Allow data URIs for signature images
        // Allow all CSS in style attributes
        ALLOW_UNKNOWN_PROTOCOLS: false,
        SANITIZE_DOM: true,
      }

    case 'rich-text':
      return {
        ...baseConfig,
        ALLOWED_TAGS: [
          'p',
          'br',
          'strong',
          'b',
          'em',
          'i',
          'u',
          'a',
          'ul',
          'ol',
          'li',
          'h1',
          'h2',
          'h3',
          'h4',
          'h5',
          'h6',
          'blockquote',
          'code',
          'pre',
          'span',
          'div',
        ],
        ALLOWED_ATTR: ['href', 'target', 'rel', 'class', 'style'],
      }

    case 'chart':
    case 'visualization':
      return {
        ...baseConfig,
        ALLOWED_TAGS: ['div', 'svg', 'canvas', 'img'],
        ALLOWED_ATTR: ['class', 'style', 'width', 'height', 'viewBox', 'src', 'alt', 'data-*'],
      }

    default:
      // Custom configuration if provided
      if (props.allowedTags || props.allowedAttributes) {
        return {
          ...baseConfig,
          ALLOWED_TAGS: props.allowedTags,
          ALLOWED_ATTR: props.allowedAttributes
            ? Object.values(props.allowedAttributes).flat()
            : undefined,
        }
      }

      // Fallback: very restrictive
      return {
        ...baseConfig,
        ALLOWED_TAGS: ['span', 'div'],
        ALLOWED_ATTR: ['class'],
      }
  }
}

/**
 * Sanitize HTML using DOMPurify with field-specific configuration
 */
const sanitizeHTML = (html: string): string => {
  if (!html || typeof html !== 'string') {
    return ''
  }

  const config = getDOMPurifyConfig()

  try {
    // Store original style attributes to preserve them
    const styleMap = new Map<Element, string>()

    // Hook to save original style attributes before DOMPurify processes them
    DOMPurify.addHook('beforeSanitizeAttributes', (node) => {
      if (node instanceof Element && node.hasAttribute('style')) {
        const originalStyle = node.getAttribute('style')
        if (originalStyle) {
          styleMap.set(node, originalStyle)
        }
      }
    })

    // Hook to restore original style attributes after DOMPurify sanitizes
    DOMPurify.addHook('afterSanitizeAttributes', (node) => {
      if (node instanceof Element && styleMap.has(node)) {
        const originalStyle = styleMap.get(node)
        if (originalStyle) {
          // Restore the original style attribute
          // This preserves all CSS including rgb(), rgba(), etc.
          node.setAttribute('style', originalStyle)
        }
      }
    })

    // Sanitize the HTML
    const clean = DOMPurify.sanitize(html, {
      ...config,
      // Allow data URIs and other safe protocols
      ALLOW_DATA_ATTR: true,
      // Keep comments for debugging (optional)
      KEEP_CONTENT: true,
      // Allow all CSS in style attributes
      WHOLE_DOCUMENT: false,
      // Force body context
      FORCE_BODY: true,
    }) as string

    // Clean up hooks after sanitization
    DOMPurify.removeHook('beforeSanitizeAttributes')
    DOMPurify.removeHook('afterSanitizeAttributes')

    return clean
  } catch (error) {
    console.error('SafeHTML: Error sanitizing HTML:', error)
    DOMPurify.removeAllHooks()
    return ''
  }
}

/**
 * Render sanitized HTML into the container
 */
const renderHTML = () => {
  if (!containerRef.value) return

  const sanitized = sanitizeHTML(props.html)

  // Set the sanitized HTML
  containerRef.value.innerHTML = sanitized
}

// Render on mount and when HTML changes
onMounted(() => {
  nextTick(() => {
    renderHTML()
  })
})

watch(
  () => props.html,
  () => {
    nextTick(() => {
      renderHTML()
    })
  },
)
</script>

<style scoped lang="scss">
.ivyforms-safe-html {
  // Base styles for sanitized HTML content

  :deep(img) {
    max-width: 100%;
    height: auto;
    display: block;
  }

  :deep(a) {
    color: var(--map-base-brand-symbol-0);
    text-decoration: none;

    &:hover {
      text-decoration: underline;
    }
  }

  :deep(code) {
    background-color: var(--map-base-dusk-o05);
    padding: 2px 6px;
    border-radius: 4px;
    font-family: monospace;
    font-size: 0.9em;
  }

  :deep(pre) {
    background-color: var(--map-base-dusk-o05);
    padding: 12px;
    border-radius: 8px;
    overflow-x: auto;

    code {
      background: none;
      padding: 0;
    }
  }

  :deep(blockquote) {
    border-left: 4px solid var(--map-base-dusk-stroke-0);
    padding-left: 12px;
    margin: 8px 0;
    color: var(--map-base-text-1);
  }
}
</style>
