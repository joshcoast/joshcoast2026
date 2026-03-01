declare module '@/views/_components/icon/iconImports' {
  import { Component } from 'vue'

  interface IconCategory {
    [key: string]: {
      [key: string]: {
        [key: string]: Component
      }
    }
  }

  const iconImports: {
    global: IconCategory
    arrows: IconCategory
    builder: IconCategory
    promotion: IconCategory
    security: {
      [key: string]: Component
    }
    templates: {
      [key: string]: Component
    }
    flags: {
      [key: string]: Component
    }
  }

  export default iconImports
}
