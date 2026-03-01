/**
 * Register all Lite components to window.IvyForms.components
 *
 * This makes all Lite components available to the Pro plugin and other extenders.
 * Pro plugin cannot import components directly because they are in separate bundles.
 * Instead, they access components through the global window.IvyForms.components registry.
 */

import type { Component } from 'vue'

// Import all Lite components
import IvyFormItem from '@/views/_components/form/IvyFormItem.vue'
import IvyTextInput from '@/views/_components/input/IvyTextInput.vue'
import IvyIcon from '@/views/_components/icon/IvyIcon.vue'
import IvyNumberInput from '@/views/_components/input/IvyNumberInput.vue'
import IvyPhoneInput from '@/views/_components/input/IvyPhoneInput.vue'
import IvySelectInput from '@/views/_components/input/IvySelectInput.vue'
import IvySelectOption from '@/views/_components/input/IvySelectOption.vue'
import IvyTextNumberInput from '@/views/_components/input/IvyTextNumberInput.vue'
import IvyComingSoonInput from '@/views/_components/input/IvyComingSoonInput.vue'
import IvyRating from '@/views/_components/input/IvyRating.vue'
import IvyButtonAction from '@/views/_components/button/IvyButtonAction.vue'
import IvyButtonOption from '@/views/_components/button/IvyButtonOption.vue'
import IvyIndicatorButton from '@/views/_components/button/IvyIndicatorButton.vue'
import IvyTemplateCard from '@/views/_components/card/IvyTemplateCard.vue'
import IvyDivider from '@/views/_components/divider/IvyDivider.vue'
import IvyScrollbar from '@/views/_components/scrollbar/IvyScrollbar.vue'
import IvyCheckbox from '@/views/_components/checkbox/IvyCheckbox.vue'
import IvyCheckboxGroup from '@/views/_components/checkbox/IvyCheckboxGroup.vue'
import IvyRadio from '@/views/_components/radio/IvyRadio.vue'
import IvyRadioGroup from '@/views/_components/radio/IvyRadioGroup.vue'
import IvyToggle from '@/views/_components/toggle/IvyToggle.vue'
import IvyChoiceItem from '@/views/_components/choice/IvyChoiceItem.vue'
import IvyChoiceList from '@/views/_components/choice/IvyChoiceList.vue'
import IvyFilter from '@/views/_components/filter/IvyFilter.vue'
import IvyFilterItems from '@/views/_components/filter/IvyFilterItems.vue'
import IvyDropdown from '@/views/_components/dropdown/IvyDropdown.vue'
import IvyDropdownItem from '@/views/_components/dropdown/IvyDropdownItem.vue'
import IvyDropdownMenu from '@/views/_components/dropdown/IvyDropdownMenu.vue'
import IvyMenu from '@/views/_components/menu/IvyMenu.vue'
import IvyMenuAccordion from '@/views/_components/menu/IvyMenuAccordion.vue'
import IvyHeaderButton from '@/views/_components/menu/IvyHeaderButton.vue'
import IvyModal from '@/views/_components/modal/IvyModal.vue'
import IvyDialog from '@/views/_components/dialog/IvyDialog.vue'
import IvyShortDialog from '@/views/_components/sub-dialog/IvyShortDialog.vue'
import IvyAlert from '@/views/_components/alert/IvyAlert.vue'
import IvyNotification from '@/views/_components/notification/IvyNotification.vue'
import IvyTooltip from '@/views/_components/tooltip/IvyTooltip.vue'
import IvyTable from '@/views/_components/table/IvyTable.vue'
import IvyTabs from '@/views/_components/tabs/IvyTabs.vue'
import IvyCollapse from '@/views/_components/collapse/IvyCollapse.vue'
import IvyCollapseItem from '@/views/_components/collapse/IvyCollapseItem.vue'
import IvySkeleton from '@/views/_components/skeleton/IvySkeleton.vue'
import IvySkeletonItem from '@/views/_components/skeleton/IvySkeletonItem.vue'
import IvyPagination from '@/views/_components/pagination/IvyPagination.vue'
import IvyEditor from '@/views/_components/html-editor/IvyEditor.vue'
import IvyDatePicker from '@/views/_components/datepicker/IvyDatePicker.vue'
import IvyTimePicker from '@/views/_components/timepicker/IvyTimePicker.vue'
import IvyPopover from '@/views/_components/popover/IvyPopover.vue'
import IvyPlaceholdersPopover from '@/views/_components/popover/IvyPlaceholdersPopover.vue'
import IvyContextMenu from '@/views/_components/context-menu/IvyContextMenu.vue'
import IvyContextMenuActions from '@/views/_components/context-menu/IvyContextMenuActions.vue'
import IvyLink from '@/views/_components/link/IvyLink.vue'
import IvyLogo from '@/views/_components/logo/IvyLogo.vue'
import IvySearch from '@/views/_components/search/IvySearch.vue'
import IvyThemeSwitch from '@/views/_components/theme-switch/IvyThemeSwitch.vue'
import ComingSoonBadge from '@/views/_components/badges/ComingSoonBadge.vue'
import Recaptcha from '@/views/_components/recaptcha/Recaptcha.vue'
import IvyNotificationBanner from '@/views/_components/banners/IvyNotificationBanner.vue'
import IvyNotificationActionsBanner from '@/views/_components/banners/IvyNotificationActionsBanner.vue'

/**
 * Register all Lite components to a registry object
 *
 * @param componentsRegistry - The registry object to populate
 * @param registerGlobal - Optional Vue app instance to register components globally (admin only)
 */
export function registerLiteComponents(
  componentsRegistry: Record<string, Component>,
  registerGlobal?: { component: (name: string, component: Component) => void },
): void {
  const components = {
    IvyFormItem,
    IvyTextInput,
    IvyIcon,
    IvyNumberInput,
    IvyPhoneInput,
    IvySelectInput,
    IvySelectOption,
    IvyTextNumberInput,
    IvyComingSoonInput,
    IvyRating,
    IvyButtonAction,
    IvyButtonOption,
    IvyIndicatorButton,
    IvyTemplateCard,
    IvyDivider,
    IvyScrollbar,
    IvyCheckbox,
    IvyCheckboxGroup,
    IvyRadio,
    IvyRadioGroup,
    IvyToggle,
    IvyChoiceItem,
    IvyChoiceList,
    IvyFilter,
    IvyFilterItems,
    IvyDropdown,
    IvyDropdownItem,
    IvyDropdownMenu,
    IvyMenu,
    IvyMenuAccordion,
    IvyHeaderButton,
    IvyModal,
    IvyDialog,
    IvyShortDialog,
    IvyAlert,
    IvyNotification,
    IvyTooltip,
    IvyTable,
    IvyTabs,
    IvyCollapse,
    IvyCollapseItem,
    IvySkeleton,
    IvySkeletonItem,
    IvyPagination,
    IvyEditor,
    IvyDatePicker,
    IvyTimePicker,
    IvyPopover,
    IvyPlaceholdersPopover,
    IvyContextMenu,
    IvyContextMenuActions,
    IvyLink,
    IvyLogo,
    IvySearch,
    IvyThemeSwitch,
    ComingSoonBadge,
    Recaptcha,
    IvyNotificationBanner,
    IvyNotificationActionsBanner,
  }

  // Register each component
  Object.entries(components).forEach(([name, component]) => {
    componentsRegistry[name] = component

    // Also register globally to Vue app if provided (admin only)
    if (registerGlobal) {
      registerGlobal.component(name, component)
    }
  })
}
