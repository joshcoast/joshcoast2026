# SafeHTML Component - Double-Layer Sanitization

## Overview

The `SafeHTML` component provides **double-layer security** for rendering HTML from the server:
1. **Backend Sanitization** (PHP) - Using WordPress's `esc_attr()` and `wp_kses_post()`
2. **Frontend Sanitization** (Vue + DOMPurify) - Client-side validation and sanitization

## Architecture

### Security Layers

```
┌─────────────────────────────────────────────────────────────┐
│ Layer 1: Backend (PHP)                                      │
│                                                              │
│  SignatureEntryHooks.php:                                   │
│  └─ formatSignatureValue()                                  │
│     └─ Returns: <img src="<?= esc_attr($value) ?>" />      │
│                                                              │
│  Result: Server-sanitized HTML                              │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│ Layer 2: Frontend (Vue + DOMPurify)                         │
│                                                              │
│  SafeHTML.vue:                                              │
│  └─ DOMPurify.sanitize(html, config)                       │
│     ├─ Field-specific allowed tags                         │
│     ├─ Field-specific allowed attributes                   │
│     └─ URI validation (data URLs, https, etc.)            │
│                                                              │
│  Result: Double-sanitized, field-specific HTML              │
└─────────────────────────────────────────────────────────────┘
```

## Usage

### In Entry Display

```vue
<template>
  <tbody>
    <tr v-for="field in filteredFields" :key="field.id">
      <td>{{ getFieldLabel(field) }}</td>
      
      <!-- HTML fields with SafeHTML -->
      <td v-if="shouldRenderAsHtml(field)">
        <SafeHTML 
          :html="getFieldDisplayValue(field)"
          :field-type="getFormField(field)?.type"
        />
      </td>
      
      <!-- Regular text fields -->
      <td v-else>{{ getFieldDisplayValue(field) }}</td>
    </tr>
  </tbody>
</template>

<script setup>
import SafeHTML from '@/views/_components/SafeHTML.vue'
</script>
```

## Component API

### Props

```typescript
interface Props {
  // The HTML string to sanitize and render (required)
  html: string
  
  // Field type for field-specific sanitization rules (optional)
  fieldType?: string  // 'signature', 'rich-text', 'chart', etc.
  
  // Custom allowed HTML tags (optional, overrides field-type rules)
  allowedTags?: string[]
  
  // Custom allowed attributes (optional, overrides field-type rules)
  allowedAttributes?: Record<string, string[]>
}
```

### Field-Specific Configurations

#### Signature Field

```typescript
fieldType: 'signature'

DOMPurify Config:
- ALLOWED_TAGS: ['img']
- ALLOWED_ATTR: ['src', 'alt', 'style', 'class', 'width', 'height']
- ALLOWED_URI_REGEXP: /^data:image\/(png|jpeg|jpg|gif|svg\+xml);base64,/i

Additional Validation:
- Ensures src contains 'data:image'
- Blocks non-image data URLs
```

#### Rich Text Field

```typescript
fieldType: 'rich-text'

DOMPurify Config:
- ALLOWED_TAGS: ['p', 'br', 'strong', 'em', 'i', 'u', 'a', 'ul', 'ol', 'li', 
                 'h1'-'h6', 'blockquote', 'code', 'pre', 'span', 'div']
- ALLOWED_ATTR: ['href', 'target', 'rel', 'class', 'style']
- Links validated for safe protocols (http, https, mailto)
```

#### Chart/Visualization

```typescript
fieldType: 'chart' | 'visualization'

DOMPurify Config:
- ALLOWED_TAGS: ['div', 'svg', 'canvas', 'img']
- ALLOWED_ATTR: ['class', 'style', 'width', 'height', 'viewBox', 'src', 'alt', 'data-*']
```

#### Default (Unknown Types)

```typescript
fieldType: undefined or unknown

DOMPurify Config:
- ALLOWED_TAGS: ['span', 'div']  // Very restrictive
- ALLOWED_ATTR: ['class']
```

## Custom Configuration

### Example: Custom Allowed Tags

```vue
<SafeHTML 
  :html="customHTML"
  field-type="custom"
  :allowed-tags="['div', 'p', 'img', 'a']"
  :allowed-attributes="{
    img: ['src', 'alt'],
    a: ['href', 'target']
  }"
/>
```

## Security Features

### 1. **Field-Type Based Rules**
Each field type has its own whitelist of allowed tags and attributes.

### 2. **URI Validation**
- Data URLs: Only `data:image/*` for signature fields
- External URLs: Only `http`, `https`, `mailto`, `tel`
- Blocks: `javascript:`, `data:text/html`, `vbscript:`, etc.

### 3. **Attribute Filtering**
Only explicitly allowed attributes pass through. Dangerous attributes like `onclick`, `onerror`, `onload` are automatically blocked.

### 4. **XSS Protection**
DOMPurify removes:
- `<script>` tags
- Event handlers (`onclick`, `onerror`, etc.)
- `javascript:` URIs
- Form elements (`<form>`, `<input>`) unless explicitly allowed
- Iframes unless explicitly allowed

### 5. **Error Handling**
If sanitization fails, returns empty string and logs error to console.

## Performance

### Optimization Strategies

1. **Lazy Rendering**: SafeHTML only renders when the container is mounted
2. **Watch Optimization**: Only re-sanitizes when `html` prop changes
3. **Config Caching**: DOMPurify config is determined once per render

### Bundle Size

- **DOMPurify**: ~30KB minified + gzipped
- **SafeHTML Component**: ~2KB
- **Total Overhead**: ~32KB for XSS protection

## Example Outputs

### Signature Field

**Input:**
```html
<img src="data:image/png;base64,iVBORw0KGgo..." alt="Signature" style="max-width: 100%;" />
```

**After SafeHTML:**
```html
<img src="data:image/png;base64,iVBORw0KGgo..." alt="Signature" style="max-width: 100%;">
```

### Malicious Input (Signature Field)

**Input:**
```html
<img src="data:image/png;base64,..." onerror="alert('xss')" />
<script>alert('xss')</script>
```

**After SafeHTML:**
```html
<img src="data:image/png;base64,..." alt="">
<!-- script tag completely removed -->
```

### Rich Text Field

**Input:**
```html
<p>Hello <strong>world</strong>!</p>
<a href="javascript:alert('xss')">Click me</a>
```

**After SafeHTML:**
```html
<p>Hello <strong>world</strong>!</p>
<a>Click me</a>  <!-- href removed because javascript: protocol -->
```

## Testing

### Security Test Cases

```typescript
// Test 1: Script injection
const malicious1 = '<img src=x onerror="alert(1)">'
// Expected: <img src="x"> (onerror removed)

// Test 2: JavaScript protocol
const malicious2 = '<a href="javascript:alert(1)">Click</a>'
// Expected: <a>Click</a> (href removed)

// Test 3: Data URL for non-signature
const malicious3 = '<img src="data:text/html,<script>alert(1)</script>">'
// Expected: Empty or <img> without src (depends on field type)

// Test 4: Valid signature
const valid = '<img src="data:image/png;base64,iVBORw..." alt="Signature">'
// Expected: Same output (valid)
```

## Integration with Pro Plans

### Adding New HTML Field Types

1. **Backend**: Create entry hooks in `{Plan}/Entries/{FieldType}EntryHooks.php`
2. **Frontend Hook**: Register in `plans/{plan}/hooks/entryHtmlFieldsHooks.ts`
3. **SafeHTML Config**: Add field-type case in `getDOMPurifyConfig()`

Example for a new "video" field:

```typescript
// SafeHTML.vue
case 'video':
  return {
    ...baseConfig,
    ALLOWED_TAGS: ['video', 'source'],
    ALLOWED_ATTR: ['src', 'type', 'controls', 'width', 'height', 'poster'],
    ALLOWED_URI_REGEXP: /^https:\/\//i,  // Only HTTPS videos
  }
```

## Benefits Over v-html

| Feature | `v-html` | `SafeHTML` |
|---------|----------|------------|
| Backend Sanitization | ✅ | ✅ |
| Frontend Sanitization | ❌ | ✅ |
| Field-Specific Rules | ❌ | ✅ |
| XSS Protection | ⚠️ (Backend only) | ✅✅ (Double layer) |
| Custom Configurations | ❌ | ✅ |
| Error Handling | ❌ | ✅ |
| Bundle Size | 0KB | +32KB |

## Migration from v-html

```vue
<!-- Before: Simple v-html -->
<td v-html="getFieldDisplayValue(field)"></td>

<!-- After: SafeHTML component -->
<td>
  <SafeHTML 
    :html="getFieldDisplayValue(field)"
    :field-type="getFormField(field)?.type"
  />
</td>
```

## Dependencies

```json
{
  "dependencies": {
    "dompurify": "^3.0.0"
  },
  "devDependencies": {
    "@types/dompurify": "^3.0.0"
  }
}
```

## Files

- **Component**: `ivyforms/frontend/src/views/_components/SafeHTML.vue`
- **Usage**: `ivyforms/frontend/src/views/admin/entries/entry-page/section/EntryPageSectionContent.vue`
- **Backend**: `ivyforms-pro/backend/src/{Plan}/Entries/*EntryHooks.php`
- **Frontend Hooks**: `ivyforms-pro/frontend/src/plans/{plan}/hooks/entryHtmlFieldsHooks.ts`

## Conclusion

The `SafeHTML` component provides enterprise-grade security for rendering dynamic HTML in WordPress forms, with double-layer sanitization, field-specific rules, and comprehensive XSS protection.
