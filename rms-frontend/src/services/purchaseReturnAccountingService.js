import api from '@/services/api'


const BASE_URL =
  '/inventory/purchase-return-accounting'


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


async function getLedger(
  params = {},
)
{
  const response =
    await api.get(
      `${BASE_URL}/ledger`,
      {
        params,
      },
    )

  return response.data
}


function getErrorMessage(
  error,
  fallback =
    'Unable to load Purchase Return accounting data.',
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
  getSummary,
  getLedger,
  getErrorMessage,
}
