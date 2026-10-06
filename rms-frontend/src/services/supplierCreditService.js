import api from '@/services/api'


const BASE_URL =
  '/inventory/supplier-credits'


async function getCredits(
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


async function getSummary(
  params = {},
)
{
  const response =
    await api.get(
      `${BASE_URL}/summary`,
      {
        params,
      },
    )

  return response.data
}


async function getApplications(
  params = {},
)
{
  const response =
    await api.get(
      `${BASE_URL}/applications`,
      {
        params,
      },
    )

  return response.data
}


async function getEligiblePurchaseOrders(
  supplierCreditId,
)
{
  const response =
    await api.get(
      `${BASE_URL}/${supplierCreditId}/eligible-purchase-orders`,
    )

  return response.data
}


async function applyCredit(
  payload,
)
{
  const response =
    await api.post(
      `${BASE_URL}/apply`,
      payload,
    )

  return response.data
}


function getValidationErrors(
  error,
)
{
  const errors =
    error?.response?.data?.errors

  return (
    errors
    &&
    typeof errors === 'object'
    &&
    !Array.isArray(errors)
  )
    ? errors
    : {}
}


function getErrorMessage(
  error,
  fallback =
    'Unable to process Supplier Credit.',
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


export default {
  getCredits,
  getSummary,
  getApplications,
  getEligiblePurchaseOrders,
  applyCredit,
  getValidationErrors,
  getErrorMessage,
}
