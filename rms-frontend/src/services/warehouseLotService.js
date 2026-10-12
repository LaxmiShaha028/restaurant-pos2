import api from '@/services/api'


const BASE_URL =
  '/inventory/warehouse-lots'


async function getLots(
  params = {},
)
{
  const response =
    await api.get(
      BASE_URL,
      {
        params,
      },
    )

  return response.data
}


async function getSummary()
{
  const response =
    await api.get(
      `${BASE_URL}/summary`,
    )

  return response.data
}


async function getLot(
  lotId,
)
{
  const response =
    await api.get(
      `${BASE_URL}/${lotId}`,
    )

  return response.data
}


async function updateMetadata(
  lotId,
  payload,
)
{
  const response =
    await api.patch(
      `${BASE_URL}/${lotId}/metadata`,
      payload,
    )

  return response.data
}


async function getMetadataHistory(
  lotId,
)
{
  const response =
    await api.get(
      `${BASE_URL}/${lotId}/metadata-history`,
    )

  return response.data
}


function getValidationErrors(
  error,
)
{
  const errors =
    error?.response?.data?.errors

  if (
    !errors
    ||
    typeof errors !== 'object'
    ||
    Array.isArray(errors)
  ) {
    return {}
  }

  return errors
}


function getErrorMessage(
  error,
  fallback =
    'Unable to load warehouse lot information.',
)
{
  return (
    error?.response?.data?.message
    ||
    error?.message
    ||
    fallback
  )
}


function createDefaultMeta()
{
  return {
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0,
    from: null,
    to: null,
  }
}


function createDefaultSummary()
{
  return {
    open_lots: 0,
    healthy: 0,
    expiring_soon: 0,
    critical: 0,
    expired: 0,
    unknown_expiry: 0,
    depleted: 0,
    expiry_watch_count: 0,
    risk_value: 0,
    risk_value_formatted: '৳ 0.00',
    expired_value: 0,
    expired_value_formatted: '৳ 0.00',
    critical_days: 7,
    expiring_soon_days: 30,
  }
}


export default {
  getLots,
  getSummary,
  getLot,
  updateMetadata,
  getMetadataHistory,
  getValidationErrors,
  getErrorMessage,
  createDefaultMeta,
  createDefaultSummary,
}
