# IvyForms Developer Documentation

This document provides technical information for developers who want to contribute to or understand the IvyForms WordPress plugin.

## Project Overview

IvyForms is a modern form builder for WordPress built with PHP 7.4+ on the backend and Vue.js/TypeScript on the frontend. The plugin uses a component-based architecture and dependency injection to maintain clean code.

## Project Structure

```
ivyforms/
└── backend/
    └── src/
        ├── Config/
        ├── Common/
        │   ├── Exceptions/
        │   └── Sanitizer/
        ├── Controllers/
        ├── Entity/
        ├── Factory/
        ├── Repository/
        ├── Routes/
        ├── Services/
        └── ValueObjects/
    ├── vendor/
    ├── composer.json 
    ├── composer.lock
    ├── phpcs.xml
    ├── phpmd-ruleset.xml 
    ├── phpstan.neon 
└── frontend/ 
    ├── src/ 
    ├── dist/
    ├── node_modules/ 
├── languages/
├── view/
    ├── backend/
    └── frontend/
```

### Directories Explanation

- **backend/**: Contains the PHP backend code and dependencies.
    - **src/**: Source code for the backend.
        - **Config/**: Contains configuration files for the application
        - **Common/**: Contains common files such as exceptions and helper classes
            - **Exceptions/**: Custom exception classes.
            - **Sanitizer/**: Contains class for sanitizing data.
        - **Controllers/**: Handle HTTP requests and responses. They use services to process the business logic
        - **Entity/**: Represent the core data structures of the application.
        - **Factory/**: Responsible for creating instances of entities or other objects.
        - **Repository/**: Handle data persistence and retrieval, typically interacting with a database.
        - **Routes/**: Define the application's routes and map them to controller actions.
        - **Services/**: Contain the business logic of the application.
        - **ValueObjects/**: Represent immutable values that describe certain aspects of the domain.

- **frontend/**: Contains the frontend code and dependencies.
    - **src/**: Source code for the frontend.
    - **dist/**: Compiled and bundled frontend assets.
    - **node_modules/**: npm dependencies.
    - Various configuration files for TypeScript, ESLint, and build tools.

- **view/**: Contains view templates.
    - **backend/**: Backend view files.
    - **frontend/**: Frontend view files.
- **languages/**: Contains language files for localization.

## Development Environment Setup

### Requirements

- PHP 7.4 or higher
- WordPress 5.0 or higher
- Node.js 14.0 or higher
- Composer 2.0 or higher
- npm 6.0 or higher

### Backend Setup
1. Navigate to the backend directory:
```bash
cd backend
```

2. Install PHP dependencies:
```bash
composer install
```

3. Run PHP code quality tools:
```bash
# Run PHP Code Sniffer
backend/vendor/bin/phpcs --standard=PSR12 backend/src

# Run PHPStan (Level 6)
backend/vendor/bin/phpstan analyse -c backend/phpstan.neon -l 6 backend/src --memory-limit=-1

# Run PHP Mess Detector:
backend/vendor/bin/phpmd backend/src ansi backend/phpmd-ruleset.xml
```

### Frontend Setup

1. Navigate to the frontend directory:
```bash
cd frontend
```

2. Install NPM dependencies:
```bash
npm install --force
```

3. Start development server:
```bash
npm run dev
```

4. Build for production:
```bash
npm run build
```

5. Run code quality tools:
```bash
# Run ESLint
npm run lint

# Run TypeScript type checking
npm run type-check

# Run formatting checks
npm run format
```

## Build Process

The plugin uses modern build tools to optimize the production code:

### Frontend Build Process

- **Development**: During development, Vite provides a fast development server with Hot Module Replacement (HMR).
- **Production**: For production, frontend assets are compiled and minified using Vite's build process.

The frontend is built using:
- **Vue.js 3**: Progressive JavaScript framework
- **TypeScript**: Typed JavaScript for better development experience
- **Vite**: Build tool for modern web projects
- **Pinia/Vuex**: State management
- **ESLint**: JavaScript/TypeScript linting

### Backend Build Process

The backend is managed using:
- **Composer**: PHP dependency management
- **PHP_CodeSniffer**: Code style checking
- **PHPStan**: Static analysis tool
- **PHP Mess Detector**: Code quality tool
- **PHP Dependency Injection (DI)** for managing dependencies

## External Libraries & Services

### JavaScript Libraries

The compiled files in `frontend/dist/` are built from source code in the `frontend/src/` directory. The build process uses npm and Vite to compile Vue.js components, TypeScript files, and other assets.

### External Services

The plugin uses the following external services:

1. **ipwho.is API**: Used to auto-detect user's country code for phone number input fields.
    - Implementation: Used in the phone number component to improve UX by pre-selecting the user's country code.

## Coding Standards

### PHP Coding Standards

We follow the WordPress PHP Coding Standards with some modifications:
- PSR-4 autoloading
- PSR-12 code style
- Type hints and return types

### JavaScript/TypeScript Coding Standards

- Vue.js Style Guide (Priority A & B rules)
- AirBnB JavaScript Style Guide with TypeScript extensions
- Component names in PascalCase
- Props in camelCase

### SCSS/CSS (BEM) Guidelines

Adopt BEM (Block–Element–Modifier) for predictable, reusable, and low-specificity styles in SCSS.

- Naming
  - Block: `.component` (semantic, standalone piece of UI)
  - Element: `.component__element` (a part of a block that has no standalone meaning)
  - Modifier: `.component--modifier` or `.component__element--modifier` (a variant/state)
  - State helpers (transient/JS-driven): `.is-active`, `.is-open`, `.has-error`
- Rules
  - Modifiers are never used alone; always together with their base block/element
  - Keep specificity low: avoid IDs and tag selectors, prefer classes
  - Keep selectors flat: no chaining like `.block .other-block`; communicate via classes
  - SCSS nesting depth: max 2 levels inside a block (elements/modifiers only)
  - Scope third‑party selectors (e.g., Element Plus `.el-*`) under a block wrapper
  - Prefer utility classes for spacing/layout (e.g., `ivyforms-flex`, `ivyforms-p-4`)
  - Use design tokens/CSS variables (`var(--map-...)`) instead of hard-coded values
- File organization
  - Co-locate styles with the component (SFC `<style lang="scss">` or a matching SCSS file)
  - One block per component when possible; use clear, page-scoped blocks like `.ivyforms-all-entries`

## Debugging

### PHP Debugging

The plugin uses WordPress's built-in debugging tools:
- Set `WP_DEBUG` to `true` in your `wp-config.php` file
- Use `error_log()` for logging
- update IVYFORMS_DEV constant to `true` in `ivyforms.php` for development mode

### JavaScript Debugging

- Vue.js DevTools browser extension for Vue component debugging
- Console logging with `console.log()`, `console.error()`, etc.

## Source Code & Compilation

The minified JavaScript and CSS files in `frontend/dist/` directory are compiled from source code in the `frontend/src/` directory. The full source code is available in the plugin repository.

To modify the frontend code, edit the files in `frontend/src/` and run the build process to update the compiled assets.

## License

This project is licensed under the GPL v2 or later.
