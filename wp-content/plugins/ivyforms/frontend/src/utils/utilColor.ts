const getBrightness = (color: string) => {
  if (!isHex(color)) {
    color = rgbaToHex(color)
  }

  const r = parseInt(color.slice(1, 3), 16)
  const g = parseInt(color.slice(3, 5), 16)
  const b = parseInt(color.slice(5, 7), 16)

  return (r * 299 + g * 587 + b * 114) / 1000
}

function rgbStyleToHex(color: string | null): string | null {
  if (!color || color.indexOf('rgb') < 0) {
    return color
  }

  if (color.charAt(0) == '#') {
    return color
  }

  const nums = /(.*?)rgb\((\d+),\s*(\d+),\s*(\d+)\)/i.exec(color),
    r = parseInt(nums![2], 10).toString(16),
    g = parseInt(nums![3], 10).toString(16),
    b = parseInt(nums![4], 10).toString(16)

  return (
    '#' +
    ((r.length == 1 ? '0' + r : r) + (g.length == 1 ? '0' + g : g) + (b.length == 1 ? '0' + b : b))
  ).toLowerCase()
}

const rgbaToHex = (color: string) => {
  return `#${color
    .match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*(\d+\.{0,1}\d*))?\)$/)
    .slice(1)
    .map((n, i) =>
      (i === 3 ? Math.round(parseFloat(n) * 255) : parseFloat(n))
        .toString(16)
        .padStart(2, '0')
        .replace('NaN', ''),
    )
    .join('')}`
}

const isHex = (color: string) => {
  return /^#([0-9a-f]{3}){1,2}$/i.test(color)
}

const isDark = (color: string) => {
  return getBrightness(color) < 128
}

const isLight = (color: string) => {
  return getBrightness(color) > 128
}

export { isLight, isDark, rgbStyleToHex }
