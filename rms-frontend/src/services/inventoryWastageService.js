import api from '@/services/api'


/*
|--------------------------------------------------------------------------
| Damage / Wastage API
|--------------------------------------------------------------------------
*/

const BASE_URL =
  '/inventory/wastages'


/*
|--------------------------------------------------------------------------
| List Wastage History
|--------------------------------------------------------------------------
*/

async function getWastages(
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


/*
|--------------------------------------------------------------------------
| Record Restaurant Wastage
|--------------------------------------------------------------------------
*/

async function createWastage(
  payload,
)
{
  const response =
    await api.post(
      BASE_URL,
      payload,
    )

  return response.data
}


/*
|--------------------------------------------------------------------------
| Single Wastage
|--------------------------------------------------------------------------
*/

async function getWastage(
  wastageId,
)
{
  const response =
    await api.get(
      `${BASE_URL}/${wastageId}`,
    )

  return response.data
}


/*
|--------------------------------------------------------------------------
| Error Message
|--------------------------------------------------------------------------
*/

function getWastageErrorMessage(
  error,
  fallback =
    'Something went wrong while processing damage / wastage.',
)
{
  const errors =
    error?.response?.data?.errors

  if (
    errors
    &&
    typeof errors === 'object'
  ) {
    const validationMessage =
      Object.values(errors)
        .flat()
        .filter(
          (message) =>
            typeof message ===
            'string',
        )
        .join(' ')
        .trim()

    if (validationMessage) {
      return validationMessage
    }
  }

  return (
    error?.response?.data?.message
    ||
    error?.message
    ||
    fallback
  )
}


export default {
  getWastages,
  createWastage,
  getWastage,
  getWastageErrorMessage,
}
