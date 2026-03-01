import globals from 'globals'
import path from 'node:path'
import { fileURLToPath } from 'node:url'
import js from '@eslint/js'
import { FlatCompat } from '@eslint/eslintrc'
import pluginVue from 'eslint-plugin-vue'
import {
    defineConfigWithVueTs,
    vueTsConfigs,
    configureVueProject
} from '@vue/eslint-config-typescript'

const __filename = fileURLToPath(import.meta.url)
const __dirname = path.dirname(__filename)
const compat = new FlatCompat({
    baseDirectory: __dirname,
    recommendedConfig: js.configs.recommended,
    allConfig: js.configs.all
})

// Correct `scriptLangs` configuration
configureVueProject({
    scriptLangs: ['ts'], // Allow only TypeScript in `<script>` blocks
    rootDir: __dirname // Set the root directory for `.vue` files
})

export default defineConfigWithVueTs([
    // Add Vue's flat configuration and TypeScript configurations
    pluginVue.configs['flat/recommended'], // Recommended Vue rules for flat config
    vueTsConfigs.recommended, // Recommended TypeScript rules
    ...compat.extends(
        'eslint:recommended',
        '@vue/eslint-config-prettier/skip-formatting'
    ),
    {
        // General ESLint settings
        ignores: ['node_modules/**', 'dist/**'], // Ignored directories
        files: ['**/*.js', '**/*.ts', '**/*.vue'], // Files to lint
        languageOptions: {
            globals: {
              ...globals.node, // Node.js globals
              ...globals.browser // Browser globals (window, document, DOM APIs)
            },
            ecmaVersion: 'latest', // Use the latest ECMAScript version
            sourceType: 'module' // Set source type to ES module
        },
        rules: {
            // Disable unused-vars rules globally
            'no-unused-vars': 'off' // Turn off the base rule
        }
    }
])
