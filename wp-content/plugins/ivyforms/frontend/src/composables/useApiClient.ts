import axios from 'axios'
import type { AxiosRequestConfig, AxiosResponse } from 'axios'

export const useApiClient = () => {
  const { root, nonce, namespace } = window.wpIvyApiSettings || {}

  const apiClient = axios.create({
    baseURL: root || '',
  })

  apiClient.interceptors.request.use(
    (config) => {
      if (nonce) {
        if (typeof config.headers?.set === 'function') {
          config.headers.set('X-WP-Nonce', nonce)
        } else {
          ;(config.headers as Record<string, string>)['X-WP-Nonce'] = nonce
        }
      }
      return config
    },
    (error) => Promise.reject(error),
  )

  const handleResponse = async <T = unknown>(promise: Promise<AxiosResponse<T>>) => {
    try {
      const response = await promise
      //console.log('API Response:', response.status, response.data)
      return { data: response.data, error: null, status: response.status }
    } catch (error: unknown) {
      //console.error('API Error:', error)
      const status =
        typeof error === 'object' &&
        error !== null &&
        'response' in error &&
        typeof (error as { response?: { status?: number } }).response?.status === 'number'
          ? (error as { response: { status: number } }).response.status
          : 500

      // Extract error message from response if available
      const errorData =
        typeof error === 'object' &&
        error !== null &&
        'response' in error &&
        typeof (error as { response?: { data?: unknown } }).response?.data === 'object'
          ? (error as { response: { data: unknown } }).response.data
          : null

      return {
        data: null,
        error: errorData || error,
        status,
      }
    }
  }

  const request = async <T = unknown>(
    endpoint: string,
    options: AxiosRequestConfig = {},
    config: { useNamespace?: boolean; namespace?: string } = {},
  ) => {
    let url = root || ''
    const ns = config.namespace || namespace
    const useNs = config.useNamespace !== undefined ? config.useNamespace : true
    if (useNs && ns) {
      url += ns.endsWith('/') ? ns : ns + '/'
    }
    url += endpoint.startsWith('/') ? endpoint.slice(1) : endpoint
    return handleResponse<T>(apiClient.request({ url, ...options }))
  }

  return {
    request,
    apiClient,
    root,
    namespace,
    nonce,
  }
}
