// URL validation utility

/**
 * Checks if a string is a valid URL for the form builder.
 * Allows domains, localhost, IPv4, IPv6, with optional port and path.
 * Disallows multiple protocols and garbage before the URL.
 */
export function isValidUrl(urlInput: string): boolean {
  if (!urlInput) return false
  const url = urlInput.trim()
  // Allow domains, localhost, IPv4, IPv6
  const startPattern =
    /^(https?:\/\/)?([a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*|localhost|\d{1,3}(\.\d{1,3}){3}|\[[0-9a-fA-F:]+\])(:\d+)?(\/|$)/
  if (!startPattern.test(url)) return false
  // Disallow protocol more than once
  const protocolMatches = url.match(/https?:\/\//gi)
  if (protocolMatches && protocolMatches.length > 1) return false
  // Use URL constructor for validation
  let urlToTest = url
  try {
    if (!/^https?:\/\//i.test(url)) {
      urlToTest = 'https://' + url
    }
    new URL(urlToTest)
    return true
  } catch {
    return false
  }
}
