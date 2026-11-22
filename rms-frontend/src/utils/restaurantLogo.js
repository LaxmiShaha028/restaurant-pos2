import api from '@/services/api'

export function normalizeRestaurantLogoUrl(logo) {
  if (!logo) return ''

  const value = String(logo).trim()
  if (!value) return ''
  if (value.startsWith('data:')) return value

  const apiOrigin = new URL(api.defaults.baseURL, window.location.origin).origin

  if (/^https?:\/\//i.test(value)) {
    const parsed = new URL(value)
    return parsed.pathname.includes('/storage/')
      ? `${apiOrigin}${parsed.pathname}${parsed.search}${parsed.hash}`
      : value
  }

  if (value.startsWith('/storage/')) return `${apiOrigin}${value}`
  if (value.startsWith('/')) return value
  if (value.startsWith('storage/')) return `${apiOrigin}/${value}`

  return `${apiOrigin}/storage/${value}`
}
