import { useLabels } from '@/composables/useLabels.ts'

export function getGeneralPlaceholders() {
  const { getLabel } = useLabels()
  return [
    { key: 'all_data', label: getLabel('all_data'), value: '{{all_data}}' },
    { key: 'admin_email', label: getLabel('admin_email'), value: '{{wp.admin_email}}' },
    { key: 'site_url', label: getLabel('site_url'), value: '{{wp.site_url}}' },
    { key: 'site_title', label: getLabel('site_title'), value: '{{wp.site_title}}' },
    { key: 'user_id', label: getLabel('user_id'), value: '{{wp.user_id}}' },
    { key: 'user_name', label: getLabel('user_name'), value: '{{wp.user_name}}' },
    { key: 'user_email', label: getLabel('user_email'), value: '{{wp.user_email}}' },
    { key: 'user_first_name', label: getLabel('user_first_name'), value: '{{wp.user_first_name}}' },
    { key: 'user_last_name', label: getLabel('user_last_name'), value: '{{wp.user_last_name}}' },
    { key: 'user_ip', label: getLabel('user_ip'), value: '{{wp.user_ip}}' },
    { key: 'user_agent', label: getLabel('user_agent'), value: '{{wp.user_agent}}' },
    { key: 'date', label: getLabel('date'), value: '{{wp.date}}' },
    { key: 'post_id', label: getLabel('post_id'), value: '{{wp.post_id}}' },
    { key: 'post_title', label: getLabel('post_title'), value: '{{wp.post_title}}' },
    { key: 'post_permalink', label: getLabel('post_permalink'), value: '{{wp.post_permalink}}' },
    { key: 'referer_url', label: getLabel('referer_url'), value: '{{wp.referer_url}}' },
    { key: 'entry_id', label: getLabel('entry_id'), value: '{{wp.entry_id}}' },
  ]
}
