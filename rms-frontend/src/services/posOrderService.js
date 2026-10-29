import api from '@/services/api'
import orderService from '@/services/orderService'
import systemSettingService from '@/services/systemSettingService'

const BACKEND_URL = (
  import.meta.env.VITE_BACKEND_URL ||
  'http://127.0.0.1:8000'
).replace(/\/+$/, '')

function unwrap(value) {
  return value?.data?.data ?? value?.data ?? value ?? {}
}

function asArray(value) {
  return Array.isArray(value) ? value : []
}

function positiveId(value) {
  const id = Number(value)
  return Number.isInteger(id) && id > 0 ? id : null
}

function seededFoodPhoto(menuItem) {
  const name = String(menuItem?.menu_name ?? menuItem?.name ?? '').toLowerCase()
  const category = String(
    menuItem?.category_name ?? menuItem?.category?.category_name ?? '',
  ).toLowerCase()

  let file = 'chicken-burger.jpg'
  if (name.includes('pepperoni')) file = 'pepperoni-pizza.jpg'
  else if (name.includes('pizza') || category.includes('pizza')) file = 'chicken-pizza.jpg'
  else if (name.includes('fries')) file = 'french-fries.jpg'
  else if (name.includes('wings')) file = 'chicken-wings.jpg'
  else if (name.includes('burger') && name.includes('beef')) file = 'beef-burger.jpg'
  else if (name.includes('burger') || category.includes('burger')) file = 'chicken-burger.jpg'
  else if (name.includes('beef')) file = 'beef-tehari.jpg'
  else if (name.includes('biryani') || name.includes('rice') || name.includes('set meal')) file = 'chicken-biryani.jpg'
  else if (name.includes('lemon') || name.includes('juice')) file = 'lemon-juice.jpg'
  else if (name.includes('coffee')) file = 'cold-coffee.jpg'
  else if (name.includes('soft drink')) file = 'soft-drink.jpg'
  else if (name.includes('brownie')) file = 'chocolate-brownie.jpg'
  else if (name.includes('ice cream') || name.includes('sundae')) file = 'vanilla-ice-cream.jpg'

  return `${window.location.origin}/food-photos/${file}`
}

function money(value) {
  return `৳ ${Number(value || 0).toLocaleString('en-BD', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })}`
}

function resolveMenuImage(menuItem) {
  const raw =
    menuItem?.image_url ??
    menuItem?.image ??
    menuItem?.menu_image_url ??
    menuItem?.menu_image ??
    menuItem?.photo_url ??
    menuItem?.photo ??
    ''

  if (!raw) return seededFoodPhoto(menuItem)

  const value = String(raw).trim()

  // Demo menu photos ship with the frontend in public/food-photos, while
  // the API model also exposes them as /storage/food-photos/... URLs.
  // Point those seeded assets at the frontend's public directory.
  const foodPhotoMatch = value.match(/(?:^|\/)food-photos\/(.+)$/i)
  if (foodPhotoMatch) {
    return `${window.location.origin}/food-photos/${foodPhotoMatch[1]}`
  }

  if (
    value.startsWith('http://') ||
    value.startsWith('https://') ||
    value.startsWith('data:')
  ) {
    return value
  }

  if (value.startsWith('/')) {
    return `${BACKEND_URL}${value}`
  }

  if (value.startsWith('storage/')) {
    return `${BACKEND_URL}/${value}`
  }

  return `${BACKEND_URL}/storage/${value}`
}

function normalizeBootstrap(response) {
  const data = unwrap(response)

  return {
    tables: asArray(data?.pos_tables ?? data?.tables),
    categories: asArray(data?.categories),
    menuItems: asArray(data?.menu_items),
    addons: asArray(data?.addons),
    waiter: data?.waiter ?? data?.user ?? null,
  }
}

function addSeatedReservationTables(tables, response) {
  const data = unwrap(response)
  const reservations = asArray(data?.data ?? data)
    .filter((reservation) =>
      String(reservation?.status || '').toLowerCase() === 'seated',
    )
  const tableMap = new Map(
    tables.map((table) => [Number(table.id), { ...table }]),
  )

  reservations.forEach((reservation) => {
    asArray(reservation?.tables).forEach((table) => {
      const id = positiveId(table?.id)
      if (!id) return

      const existing = tableMap.get(id)
      tableMap.set(id, {
        ...(existing || table),
        has_seated_reservation: true,
        reservation_no: reservation.reservation_no || '',
        reservation_date: reservation.reservation_date || reservation.date || '',
      })
    })
  })

  return [...tableMap.values()]
}

function normalizeSettings(response) {
  const data = unwrap(response)

  return {
    restaurant_name: data?.restaurant_name ?? '',
    vat_type:
      data?.vat_type === 'fixed'
        ? 'fixed'
        : 'percentage',
    vat_rate: Math.max(Number(data?.vat_rate ?? 0) || 0, 0),
    service_charge_type:
      data?.service_charge_type === 'fixed'
        ? 'fixed'
        : 'percentage',
    service_charge_rate: Math.max(
      Number(data?.service_charge_rate ?? 0) || 0,
      0,
    ),
  }
}

function normalizeCustomerSearch(response) {
  const body = response?.data ?? response ?? {}
  const rows = Array.isArray(body)
    ? body
    : Array.isArray(body?.data)
      ? body.data
      : []

  return rows
    .map((customer) => ({
      id: positiveId(customer?.id),
      name: String(customer?.name ?? '').trim(),
      phone: String(customer?.phone ?? '').trim(),
      email: String(customer?.email ?? '').trim(),
    }))
    .filter((customer) => customer.id)
}

const posOrderService = {
  async loadBootstrap(filters = {}) {
    const reservationParams = {
      per_page: 100,
      ...(filters?.reservation_date
        ? { reservation_date: filters.reservation_date }
        : {}),
    }
    const [optionsResponse, reservationsResponse] = await Promise.all([
      orderService.getCreateOptions(),
      orderService.getSeatedReservations(reservationParams),
    ])
    const data = normalizeBootstrap(optionsResponse)

    return {
      ...data,
      tables: addSeatedReservationTables(data.tables, reservationsResponse),
    }
  },

  async loadOperationalSettings() {
    if (
      typeof systemSettingService.getOperationalSettings ===
      'function'
    ) {
      return normalizeSettings(
        await systemSettingService.getOperationalSettings(),
      )
    }

    return normalizeSettings(
      await systemSettingService.getSettings(),
    )
  },

  async searchCustomers(search) {
    const query = String(search || '').trim()

    if (query.length < 2) return []

    const response = await api.get(
      '/order-management/customer-search',
      {
        params: {
          search: query,
          limit: 10,
        },
      },
    )

    return normalizeCustomerSearch(response)
  },

  async createOrder(payload) {
    return orderService.createOrder(payload)
  },

  getErrorMessage(error, fallback = 'Unable to complete the POS request.') {
    if (
      typeof orderService.getOrderErrorMessage ===
      'function'
    ) {
      return orderService.getOrderErrorMessage(error, fallback)
    }

    return (
      error?.response?.data?.message ||
      error?.message ||
      fallback
    )
  },

  money,
  resolveMenuImage,
}

export { money, resolveMenuImage }
export default posOrderService
