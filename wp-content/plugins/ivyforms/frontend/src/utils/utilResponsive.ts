import { useBreakpoints } from '@vueuse/core'

const utilResponsive = () => {
  const breakpoints = useBreakpoints({
    tabletStart: 768,
    desktopSmallStart: 1024,
    desktopLargeStart: 1200,
  })

  const isMobile = breakpoints.smaller('tabletStart')
  const isTablet = breakpoints.between('tabletStart', 'desktopSmallStart')
  const isDesktop = breakpoints.greaterOrEqual('desktopSmallStart')
  const isDesktopSmall = breakpoints.between('desktopSmallStart', 'desktopLargeStart')
  const isDesktopLarge = breakpoints.greaterOrEqual('desktopLargeStart')

  return { isMobile, isTablet, isDesktop, isDesktopSmall, isDesktopLarge }
}

export default utilResponsive
