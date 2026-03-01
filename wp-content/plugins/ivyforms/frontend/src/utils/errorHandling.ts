/**
 * Error handling utilities
 */

// Define error type for better type safety
interface ErrorWithMessage {
  message?: string
}

/**
 * Safely extracts error message from unknown error type
 * @param error - Unknown error object
 * @returns Error message as string
 */
export const getErrorMessage = (error: unknown): string => {
  if (error && typeof error === 'object' && 'message' in error) {
    return String((error as ErrorWithMessage).message)
  }
  return String(error)
}
