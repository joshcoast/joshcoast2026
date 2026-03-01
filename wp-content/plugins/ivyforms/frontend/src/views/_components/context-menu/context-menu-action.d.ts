import type { IconCategory } from '@/types/icons/icon-category'
import type { IconType } from '@/types/icons/icon-type'
export interface ContextMenuAction {
  label: string | (() => string)
  icon?: string | (() => string)
  iconType?: IconType
  iconCategory?: IconCategory
  iconColor?: string
  iconOnly?: boolean
  iconSize?: 'xs' | 's' | 'd' | 'l'
  secondary?: boolean | (() => boolean)
  divided?: boolean | (() => boolean)
  dividerDown?: boolean | (() => boolean)
  danger?: boolean | (() => boolean)
  color?: 'default' | 'theme' | 'danger'
  grouped?: boolean
  showArrow?: boolean
  infoIcon?: boolean
  questionIcon?: boolean
  rightText?: string
  checkboxRight?: boolean
  checkboxRightHandler?: (entityId: number | null) => void
  isCheckboxRightActive?: () => boolean
  handler?: (entityId: number | null) => void
  isSelected?: () => boolean
  isHidden?: () => boolean
  isActive?: () => boolean
  isComingSoon?: boolean | (() => boolean)
  value?: string | number
  rightText?: string | (() => string)
}
