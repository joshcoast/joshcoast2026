import { ElMessage } from 'element-plus'
import 'element-plus/es/components/message/style/css'
import { h } from 'vue'
import IvyIcon from '@/views/_components/icon/IvyIcon.vue'

const IvyMessage = (options) => {
  const messageColors = {
    success: 'var(--map-status-success-fill-0)',
    warning: 'var(--map-status-warning-fill-0)',
    error: 'var(--map-status-error-fill-0)',
    info: 'var(--map-base-purple-fill-0)',
    default: 'var(--map-base-dusk-fill-4)',
  }

  const messageTextColors = {
    success: 'var(--primitive-white)',
    warning: 'var(--primitive-white)',
    error: 'var(--primitive-white)',
    info: 'var(--primitive-white)',
    default: 'var(--map-base-dusk-fill--4)',
  }

  const iconNames = {
    success: 'check-circle',
    warning: 'danger',
    error: 'danger',
    info: 'danger',
    default: 'sticker',
  }

  // Default auto-close durations per type (ms)
  // Shorten success to ~1.5s to allow immediate interactions after save
  const defaultDurations = {
    success: 1000,
    info: 2000,
    warning: 2000,
    error: 3000,
    default: 1000,
  } as const

  const type = options?.type || 'default'

  const mergedOptions = {
    ...options,
    class: 'ivyforms-message regular-14 ivyforms-pr-8',
    offset: 48,
    duration: options?.duration ?? defaultDurations[type] ?? defaultDurations.default,
    icon: h(IvyIcon, {
      name: iconNames[type] || 'sticker',
      size: 's',
      outerSize: '24px',
      type: 'fill-duo',
      color: 'var(--map-base-dusk-fill--4)',
    }),
    style: {
      backgroundColor: messageColors[type] || messageColors.default,
      borderColor: messageColors[type] || messageColors.default,
    },
  }

  ElMessage(mergedOptions)

  const style = document.createElement('style')
  style.textContent = `
        .ivyforms-message.el-message .el-message__content {
            color: ${messageTextColors[type]};
        }
        .ivyforms-message .el-message__icon .ivyforms-icon svg {
            width: 24px;
            height: 24px;
            fill: ${messageTextColors[type]};
        }
    `
  document.head.appendChild(style)
}

export default IvyMessage
