import type { RouteRecordRaw } from 'vue-router'
import { createWebHashHistory, createRouter } from 'vue-router'
import DashboardPage from '../views/admin/dashboard/dashboard-page/DashboardPage.vue'
import SettingsPage from '../views/admin/settings/settings-page/SettingsPage.vue'
import GeneralSettingsPage from '../views/admin/settings/settings-page/section/general-settings/GeneralSettingsPage.vue'
import SecuritySettingsPage from '../views/admin/settings/settings-page/section/security-settings/SecuritySettingsPage.vue'
import FormsPage from '../views/admin/forms/forms-page/FormsPage.vue'
import FormBuilderPage from '../views/admin/form-builder/form-builder-page/FormBuilderPage.vue'
import FormBuilderSettingsPage from '../views/admin/form-builder/form-builder-page/FormBuilderSettingsPage.vue'
import FormBuilderManageNotification from '../views/admin/form-builder/form-builder-page/form-settings-notification/FormBuilderManageNotification.vue'
import FormBuilderSettings from '../views/admin/form-builder/form-builder-page/form-settings/FormBuilderSettings.vue'
import FormBuilderNotificationTable from '../views/admin/form-builder/form-builder-page/form-settings-notification/FormBuilderNotificationTable.vue'
import FormBuilderConfirmation from '../views/admin/form-builder/form-builder-page/form-settings-confirmations/FormBuilderConfirmation.vue'
import FormBuilderIntegrations from '../views/admin/form-builder/form-builder-page/form-settings-integrations/FormBuilderIntegrations.vue'
import EntriesPage from '@/views/admin/entries/entries-page/EntriesPage.vue'
import EntryPage from '@/views/admin/entries/entry-page/EntryPage.vue'
import FormBuilderResultsEntries from '@/views/admin/form-builder/form-builder-page/form-results/FormBuilderResultsEntries.vue'
import FormBuilderResultsPage from '@/views/admin/form-builder/form-builder-page/FormBuilderResultsPage.vue'
import {
  IVYFORMS_FORM_BUILDER_PAGE,
  IVYFORMS_ALLFORMS_PAGE,
  IVYFORMS_DASHBOARD_PAGE,
  IVYFORMS_SETTINGS,
  IVYFORMS_DASHBOARD,
  IVYFORMS_ALL_FORMS,
  IVYFORMS_ADD_FORM,
  IVYFORMS_EDIT_FORM,
  IVYFORMS_FORM_SETTING,
  IVYFORMS_FORM_NOTIFICATIONS,
  IVYFORMS_NEW_NOTIFICATIONS,
  IVYFORMS_EDIT_NOTIFICATIONS,
  IVYFORMS_FORM_CONFIRMATION,
  IVYFORMS_FORM_INTEGRATIONS,
  IVYFORMS_ENTRIES,
  IVYFORMS_ENTRY_DETAILS,
  IVYFORMS_RESULTS_ENTRY_DETAILS,
  IVYFORMS_FORM_RESULTS,
  IVYFORMS_RESULTS,
  IVYFORMS_ENTRIES_PAGE,
  IVYFORMS_SETTINGS_PAGE,
  IVYFORMS_GLOBAL_SETTINGS,
  IVYFORMS_GENERAL_SETTINGS,
  IVYFORMS_SECURITY_SETTINGS,
  IVYFORMS_INTEGRATIONS_PAGE,
  IVYFORMS_INTEGRATIONS,
} from '@/constants/pages.ts'
import IntegrationsPage from '@/views/admin/integrations/integrations-page/IntegrationsPage.vue'
import { useUnsavedChangesStore } from '@/stores/useUnsavedChangesStore'
import { useActionEntityStore } from '@/stores/actionEntityStore'
import { useFormBuilder } from '@/stores/useFormBuilder'
import { useConfirmationSettingBuilder } from '@/stores/useConfirmationSettingBuilder'
import { useNotificationSettingBuilder } from '@/stores/useNotificationSettingBuilder'

const currentPage = new URLSearchParams(window.location.search).get('page')
let routes: RouteRecordRaw[] = []

if (currentPage === IVYFORMS_DASHBOARD_PAGE) {
  routes = [
    {
      path: '/',
      name: IVYFORMS_DASHBOARD,
      component: DashboardPage,
      meta: {
        pageType: 'dashboard',
        breadcrumb: 'Dashboard',
      },
    },
  ]
}

if (currentPage === IVYFORMS_ALLFORMS_PAGE) {
  routes = [
    {
      path: '/',
      name: IVYFORMS_ALL_FORMS,
      component: FormsPage,
      meta: {
        pageType: 'forms',
        breadcrumb: 'All Forms',
      },
    },
  ]
}

if (currentPage === IVYFORMS_FORM_BUILDER_PAGE) {
  routes = [
    {
      path: '/',
      name: IVYFORMS_ADD_FORM,
      component: FormBuilderPage,
      meta: {
        pageType: 'form-builder',
        breadcrumb: 'Add Form',
      },
    },
    {
      path: '/manage/:formId(\\d+)',
      redirect: { name: IVYFORMS_EDIT_FORM },
      children: [
        {
          path: '',
          name: IVYFORMS_EDIT_FORM,
          component: FormBuilderPage,
          meta: {
            pageType: 'form-builder',
            breadcrumb: 'Edit Form',
            requiresForm: true,
          },
        },
        {
          path: 'settings',
          name: IVYFORMS_SETTINGS,
          component: FormBuilderSettingsPage,
          meta: {
            pageType: 'form-settings',
            breadcrumb: 'Settings',
            requiresForm: true,
          },
          children: [
            {
              path: 'general',
              name: IVYFORMS_FORM_SETTING,
              component: FormBuilderSettings,
              meta: {
                breadcrumb: 'General',
                requiresForm: true,
              },
            },
            {
              path: 'notification',
              name: IVYFORMS_FORM_NOTIFICATIONS,
              component: FormBuilderNotificationTable,
              meta: {
                breadcrumb: 'Notifications',
                requiresForm: true,
              },
            },
            {
              path: 'notification/manage',
              name: IVYFORMS_NEW_NOTIFICATIONS,
              component: FormBuilderManageNotification,
              meta: {
                breadcrumb: 'New Notification',
                requiresForm: true,
              },
            },
            {
              path: 'notification/manage/:notificationId(\\d+)',
              name: IVYFORMS_EDIT_NOTIFICATIONS,
              component: FormBuilderManageNotification,
              meta: {
                breadcrumb: 'Edit Notification',
                requiresForm: true,
              },
            },
            {
              path: 'confirmation/manage/:confirmationId(\\d+)',
              name: IVYFORMS_FORM_CONFIRMATION,
              component: FormBuilderConfirmation,
              meta: {
                breadcrumb: 'Confirmation',
                requiresForm: true,
              },
            },
            {
              path: 'integrations',
              redirect: 'integrations/wpdatatables',
            },
            {
              path: 'integrations/:integration',
              name: IVYFORMS_FORM_INTEGRATIONS,
              component: FormBuilderIntegrations,
              meta: {
                breadcrumb: 'Integrations',
                requiresForm: true,
              },
            },
          ],
        },
        {
          path: 'results',
          name: IVYFORMS_RESULTS,
          component: FormBuilderResultsPage,
          meta: {
            pageType: 'form-results',
            breadcrumb: 'Results',
            requiresForm: true,
          },
          children: [
            {
              path: 'entries',
              name: IVYFORMS_FORM_RESULTS,
              component: FormBuilderResultsEntries,
              meta: {
                breadcrumb: 'Entries',
                requiresForm: true,
              },
            },
          ],
        },
        {
          path: 'results/entries/details/:entryId(\\d+)',
          name: IVYFORMS_RESULTS_ENTRY_DETAILS,
          component: EntryPage,
          meta: {
            breadcrumb: 'Entry Details',
            requiresForm: true,
            returnContext: 'form-results',
          },
        },
      ],
    },
  ]
}

if (currentPage === IVYFORMS_ENTRIES_PAGE) {
  routes = [
    {
      path: '/',
      name: IVYFORMS_ENTRIES,
      component: EntriesPage,
      meta: {
        pageType: 'entries',
        breadcrumb: 'All Entries',
      },
    },
    {
      path: '/details/:id(\\d+)',
      name: IVYFORMS_ENTRY_DETAILS,
      component: EntryPage,
      meta: {
        breadcrumb: 'Entry Details',
        returnContext: 'all-entries',
      },
    },
  ]
}
if (currentPage === IVYFORMS_SETTINGS_PAGE) {
  routes = [
    {
      path: '/',
      name: IVYFORMS_GLOBAL_SETTINGS,
      component: SettingsPage,
      redirect: '/general',
      meta: {
        pageType: 'settings',
        breadcrumb: 'Settings',
      },
      children: [
        {
          path: 'general',
          name: IVYFORMS_GENERAL_SETTINGS,
          component: GeneralSettingsPage,
          meta: {
            breadcrumb: 'General',
          },
        },
        {
          path: 'security',
          redirect: 'security/recaptcha',
        },
        {
          path: 'security/:provider(recaptcha|hcaptcha|turnstile|honeypot)',
          name: IVYFORMS_SECURITY_SETTINGS,
          component: SecuritySettingsPage,
          meta: {
            breadcrumb: 'Security',
          },
        },
      ],
    },
  ]
}

if (currentPage === IVYFORMS_INTEGRATIONS_PAGE) {
  routes = [
    {
      path: '/',
      name: IVYFORMS_INTEGRATIONS,
      component: IntegrationsPage,
      meta: {
        pageType: 'integrations',
        breadcrumb: 'Integrations',
      },
    },
  ]
}

const router = createRouter({
  history: createWebHashHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    // Restore scroll position on browser back/forward
    if (savedPosition) {
      return savedPosition
    }
    // Scroll to top on new navigation
    return { top: 0 }
  },
})

// Wrap addRoute to prevent warnings when parent route doesn't exist
// This happens when Pro plugin tries to add routes to settings page when we're on a different page
const originalAddRoute = router.addRoute.bind(router)
router.addRoute = function (parentOrRoute: string | RouteRecordRaw, route?: RouteRecordRaw) {
  if (typeof parentOrRoute === 'string' && route) {
    // Only call with (string, RouteRecordRaw)
    if (!router.hasRoute(parentOrRoute)) {
      return undefined as unknown
    }
    return originalAddRoute(parentOrRoute, route)
  } else if (typeof parentOrRoute !== 'string' && !route) {
    // Only call with (RouteRecordRaw)
    return originalAddRoute(parentOrRoute)
  }
  // Do not call originalAddRoute with invalid argument combinations
  return undefined as unknown
} as typeof router.addRoute

// Make router available globally for Pro plugin and other extensions to add routes dynamically
if (typeof window !== 'undefined') {
  ;(
    window as unknown as {
      IvyFormsRouter?: typeof router
      IvyFormsAddRoute?: (parentName: string, route: RouteRecordRaw) => boolean
    }
  ).IvyFormsRouter = router
  ;(
    window as unknown as {
      IvyFormsRouter?: typeof router
      IvyFormsAddRoute?: (parentName: string, route: RouteRecordRaw) => boolean
    }
  ).IvyFormsAddRoute = (parentName: string, route: RouteRecordRaw) => {
    if (router.hasRoute(parentName)) {
      router.addRoute(parentName, route)
      return true
    } else {
      return false
    }
  }
}

// Navigation guard for unsaved changes
let isFirstNavigation = true
let skipNextGuard = false

// Define which routes use which stores
const formBuilderRoutes = [
  IVYFORMS_ADD_FORM,
  IVYFORMS_EDIT_FORM,
  IVYFORMS_FORM_SETTING,
  IVYFORMS_FORM_CONFIRMATION,
  IVYFORMS_FORM_INTEGRATIONS,
]
const confirmationRoutes = [IVYFORMS_FORM_CONFIRMATION]
const notificationRoutes = [
  IVYFORMS_FORM_NOTIFICATIONS,
  IVYFORMS_NEW_NOTIFICATIONS,
  IVYFORMS_EDIT_NOTIFICATIONS,
]

router.beforeEach((to, from, next) => {
  // Skip guard if we're navigating after user confirmed in dialog
  if (skipNextGuard) {
    skipNextGuard = false
    next()
    return
  }

  // Skip unsaved changes check on initial page load
  if (isFirstNavigation) {
    isFirstNavigation = false
    next()
    return
  }

  // Skip unsaved changes check when navigating from entry details pages
  // These pages temporarily load forms for viewing only
  if (from.name === IVYFORMS_ENTRY_DETAILS || from.name === IVYFORMS_RESULTS_ENTRY_DETAILS) {
    next()
    return
  }

  const unsavedChangesStore = useUnsavedChangesStore()
  const formBuilderStore = useFormBuilder()
  const confirmationStore = useConfirmationSettingBuilder()
  const notificationStore = useNotificationSettingBuilder()
  const actionEntityStore = useActionEntityStore()

  // Only check stores that are relevant to the route we're leaving from
  const entitiesToCheck: ('formBuilder' | 'confirmationBuilder' | 'notificationBuilder')[] = []

  // Check form builder only if:
  // 1. We're leaving a form builder route
  // 2. AND the form builder is actually editing a form (not just initialized with defaults)
  if (formBuilderRoutes.includes(from.name as string) && formBuilderStore.isEditing) {
    entitiesToCheck.push('formBuilder')
  }

  // Check confirmation store only if we're leaving a confirmation route AND actively editing
  if (confirmationRoutes.includes(from.name as string) && confirmationStore.isEditing) {
    entitiesToCheck.push('confirmationBuilder')
  }

  // Check notification store only if we're leaving a notification route AND actively editing
  if (notificationRoutes.includes(from.name as string) && notificationStore.isEditing) {
    entitiesToCheck.push('notificationBuilder')
  }

  // If no entities need to be checked, proceed immediately
  if (entitiesToCheck.length === 0) {
    next()
    return
  }

  // Check if there are any unsaved changes in the relevant stores using new dirty flag approach
  if (unsavedChangesStore.hasAnyUnsavedChanges(entitiesToCheck)) {
    // Show confirmation dialog
    actionEntityStore.handleActionClick(null, null, 'form', 'unsaved_changes', {}, () => {
      // Clear the dirty flags for the entities we checked
      entitiesToCheck.forEach((entity) => unsavedChangesStore.markClean(entity))
      // Set flag to skip guard on the next navigation
      skipNextGuard = true
      // User confirmed - manually navigate to the target route
      router.push(to)
    })

    // Cancel the current navigation
    next(false)
    return
  }

  // No unsaved changes - proceed immediately
  next()
})

export default router
