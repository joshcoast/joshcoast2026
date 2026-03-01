declare module '*.svg?component' {
  import type { DefineComponent } from 'vue'
  const content: DefineComponent<object, object, unknown>
  export default content
}
