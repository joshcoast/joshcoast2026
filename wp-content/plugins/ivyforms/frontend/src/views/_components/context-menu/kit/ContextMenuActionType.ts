import type { ContextMenuAction } from '@/views/_components/context-menu/context-menu-action'
import { useLabels } from '@/composables/useLabels'
export enum ContextMenuActionType {
  Rename = 'Rename',
  ShowAll = 'ShowAll',
  MoveToFolder = 'MoveToFolder',
  MoveUp = 'MoveUp',
  Export = 'Export',
  Entries = 'Entries',
  Download = 'Download',
  MarkAsSpam = 'MarkAsSpam',
  ResendNotification = 'ResendNotification',
  Save = 'Save',
  Placeholder = 'Placeholder',
  AddSubfolder = 'AddSubfolder',
  Print = 'Print',
  Folder = 'Folder',
  MoveDown = 'MoveDown',
  MoveLeft = 'MoveLeft',
  MoveRight = 'MoveRight',
  Import = 'Import',
  Preview = 'Preview',
  AddStar = 'AddStar',
  MarkAsRead = 'MarkAsRead',
  Trash = 'Trash',
  SaveAsDraft = 'SaveAsDraft',
  Cancel = 'Cancel',
  Edit = 'Edit',
  SortBy = 'SortBy',
  Duplicate = 'Duplicate',
  Translate = 'Translate',
  Settings = 'Settings',
  Locate = 'Locate',
  RemoveStar = 'RemoveStar',
  MarkAsUnread = 'MarkAsUnread',
  Restore = 'Restore',
  Page = 'Page',
  Delete = 'Delete',
  Starred = 'Starred',
  Unstarred = 'Unstarred',
  Published = 'Published',
  Unpublished = 'Unpublished',
  Reset = 'Reset',
  ResetFilters = 'Reset Filters',
  Read = 'Read',
  Unread = 'Unread',
}

export const createContextMenuAction = (
  type: ContextMenuActionType,
  overrides: Partial<ContextMenuAction> = {},
): ContextMenuAction => {
  const { getLabel } = useLabels()

  const baseActions: Record<ContextMenuActionType, ContextMenuAction> = {
    [ContextMenuActionType.Edit]: {
      label: getLabel('edit'),
      icon: 'edit',
      handler: () => alert('Edit clicked'),
    },
    [ContextMenuActionType.Delete]: {
      label: getLabel('delete'),
      icon: 'trash',
      danger: true,
      handler: () => alert('Delete clicked'),
    },
    [ContextMenuActionType.MoveToFolder]: {
      label: getLabel('move_to_folder'),
      icon: 'move-to',
      showArrow: true,
      handler: () => {
        alert('Move to folder clicked')
      },
    },
    [ContextMenuActionType.Folder]: {
      label: '',
      icon: 'folder',
      handler: (entityId: number | null) => {
        const folderName = String(entityId)
        alert(`${getLabel('move_up')} ${folderName}`)
      },
    },
    [ContextMenuActionType.Duplicate]: {
      label: getLabel('duplicate'),
      icon: 'copy',
      handler: () => alert('Duplicate clicked'),
    },
    [ContextMenuActionType.MoveUp]: {
      label: getLabel('move_up'),
      icon: 'arrow-up',
      iconCategory: 'arrows',
      handler: () => alert('Move up clicked'),
    },
    [ContextMenuActionType.MoveDown]: {
      label: getLabel('move_down'),
      icon: 'arrow-down',
      iconCategory: 'arrows',
      handler: () => alert('Move down clicked'),
    },
    [ContextMenuActionType.MoveLeft]: {
      label: getLabel('move_left'),
      icon: 'arrow-left',
      iconCategory: 'arrows',
      handler: () => alert('Move left clicked'),
    },
    [ContextMenuActionType.MoveRight]: {
      label: getLabel('move_right'),
      icon: 'arrow-right',
      iconCategory: 'arrows',
      handler: () => alert('Move right clicked'),
    },
    [ContextMenuActionType.Save]: {
      label: getLabel('save'),
      icon: 'cloud-save',
      handler: () => alert('Save clicked'),
    },
    [ContextMenuActionType.SaveAsDraft]: {
      label: getLabel('save_as_draft'),
      icon: 'archive-check',
      handler: () => alert('Save as draft clicked'),
    },
    [ContextMenuActionType.Cancel]: {
      label: getLabel('cancel'),
      icon: 'close-circle',
      danger: true,
      handler: () => alert('Cancel clicked'),
    },
    [ContextMenuActionType.Print]: {
      label: getLabel('print'),
      icon: 'print',
      handler: () => alert('Print clicked'),
    },
    [ContextMenuActionType.Export]: {
      label: getLabel('export'),
      icon: 'export',
      handler: () => alert('Export clicked'),
    },
    [ContextMenuActionType.Import]: {
      label: getLabel('import'),
      icon: 'import',
      handler: () => alert('Import clicked'),
    },
    [ContextMenuActionType.Download]: {
      label: getLabel('download'),
      icon: 'cloud-download',
      handler: () => alert('Download clicked'),
    },
    [ContextMenuActionType.MarkAsSpam]: {
      label: getLabel('mark_as_spam'),
      icon: 'spam',
      handler: () => alert('Mark as spam clicked'),
    },
    [ContextMenuActionType.MarkAsRead]: {
      label: getLabel('mark_as_read'),
      icon: 'read',
      handler: () => alert('Mark as read clicked'),
    },
    [ContextMenuActionType.MarkAsUnread]: {
      label: getLabel('mark_as_unread'),
      icon: 'unread',
      handler: () => alert('Mark as unread clicked'),
    },
    [ContextMenuActionType.AddStar]: {
      label: getLabel('add_star'),
      icon: 'star',
      iconType: 'fill',
      handler: () => alert('Add star clicked'),
    },
    [ContextMenuActionType.RemoveStar]: {
      label: getLabel('remove_star'),
      icon: 'star',
      handler: () => alert('Remove star clicked'),
    },
    [ContextMenuActionType.Locate]: {
      label: getLabel('locate'),
      icon: 'send-locate',
      handler: () => alert('Locate clicked'),
    },
    [ContextMenuActionType.Settings]: {
      label: getLabel('settings'),
      icon: 'settings',
      dividerDown: true,
      handler: () => alert('Settings clicked'),
    },
    [ContextMenuActionType.SortBy]: {
      label: getLabel('sort_by'),
      icon: 'sort',
      handler: () => alert('Sort by clicked'),
    },
    [ContextMenuActionType.Translate]: {
      label: getLabel('translate'),
      icon: 'globe',
      handler: () => alert('Translate clicked'),
    },
    [ContextMenuActionType.Preview]: {
      label: getLabel('preview'),
      icon: 'preview',
      handler: () => alert('Preview clicked'),
    },
    [ContextMenuActionType.AddSubfolder]: {
      label: getLabel('add_subfolder'),
      icon: 'plus',
      handler: () => alert('Add subfolder clicked'),
    },
    [ContextMenuActionType.Trash]: {
      label: getLabel('trash'),
      icon: 'archive',
      handler: () => alert('Trash clicked'),
    },
    [ContextMenuActionType.Restore]: {
      label: getLabel('restore'),
      icon: 'restore',
      handler: () => alert('Restore clicked'),
    },
    [ContextMenuActionType.ShowAll]: {
      label: getLabel('show_all'),
      icon: 'checklist',
      handler: () => alert('Show all clicked'),
    },
    [ContextMenuActionType.Entries]: {
      label: getLabel('entries'),
      icon: 'login',
      iconCategory: 'arrows',
      dividerDown: true,
      handler: () => alert('Entries clicked'),
    },
    [ContextMenuActionType.Page]: {
      label: '',
      handler: () => alert('Page clicked'),
    },
    [ContextMenuActionType.Placeholder]: {
      label: '[placeholder name]',
      handler: () => alert('Placeholder clicked'),
    },
    [ContextMenuActionType.Rename]: {
      label: getLabel('rename'),
      icon: 'edit-field',
      handler: () => alert('Rename clicked'),
    },
    [ContextMenuActionType.ResendNotification]: {
      label: getLabel('resend_notification'),
      icon: 'resend',
      handler: () => alert('Resend notification clicked'),
    },
    [ContextMenuActionType.Starred]: {
      label: getLabel('starred'),
      icon: 'star',
      iconCategory: 'global',
      iconType: 'fill',
      iconSize: 's',
      handler: () => alert('Starred clicked'),
    },
    [ContextMenuActionType.Unstarred]: {
      label: getLabel('unstarred'),
      icon: 'star',
      iconCategory: 'global',
      iconType: 'outline',
      iconSize: 's',
      handler: () => alert('Unstarred clicked'),
    },
    [ContextMenuActionType.Unpublished]: {
      label: getLabel('unpublished'),
      icon: 'toggle-off',
      iconCategory: 'global',
      iconType: 'fill',
      iconSize: 's',
      handler: () => alert('Unpublished clicked'),
    },
    [ContextMenuActionType.Published]: {
      label: getLabel('published'),
      icon: 'toggle-on',
      iconCategory: 'global',
      iconType: 'fill',
      iconSize: 's',
      handler: () => alert('Published clicked'),
    },
    [ContextMenuActionType.Reset]: {
      label: getLabel('reset'),
      danger: true,
      icon: 'refresh',
      iconType: 'outline',
      iconCategory: 'arrows',
      iconSize: 's',
      handler: () => alert('Reset'),
    },
    [ContextMenuActionType.Read]: {
      label: getLabel('read'),
      icon: 'email',
      iconSize: 's',
      handler: () => alert('Read clicked'),
    },
    [ContextMenuActionType.Unread]: {
      label: getLabel('unread'),
      icon: 'unread',
      iconSize: 's',
      handler: () => alert('Unread clicked'),
    },
    [ContextMenuActionType.ResetFilters]: {
      label: getLabel('reset_filters'),
      danger: true,
      icon: 'refresh',
      iconType: 'outline',
      iconCategory: 'arrows',
      iconSize: 's',
      handler: () => alert('Reset clicked'),
    },
  }

  return { ...baseActions[type], ...overrides }
}
