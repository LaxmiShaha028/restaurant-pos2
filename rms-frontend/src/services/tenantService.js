import api from '@/services/api'


/*
|--------------------------------------------------------------------------
| Tenant API
|--------------------------------------------------------------------------
*/

const TENANT_BASE_URL =
  '/tenant'


/*
|--------------------------------------------------------------------------
| Resolve Tenant
|--------------------------------------------------------------------------
|
| Resolves a tenant using its public tenant key.
|
*/

export async function resolveTenant(
  tenantKey
) {

  if (
    !tenantKey
  ) {
    throw new Error(
      'Tenant key is required.'
    )
  }


  const normalizedTenantKey =
    String(
      tenantKey
    ).trim()


  if (
    normalizedTenantKey === ''
  ) {
    throw new Error(
      'Tenant key is required.'
    )
  }


  try {

    const response =
      await api.get(
        `${TENANT_BASE_URL}/resolve/${encodeURIComponent(
          normalizedTenantKey
        )}`
      )


    return (
      response?.data
      ??
      {}
    )

  } catch (error) {

    const apiMessage =
      error?.response?.data?.message

    const validationErrors =
      error?.response?.data?.errors


    const normalizedError =
      new Error(
        apiMessage ||
        error?.message ||
        'Failed to resolve restaurant.'
      )


    normalizedError.validationErrors =
      validationErrors ||
      null


    normalizedError.status =
      error?.response?.status ||
      null


    throw normalizedError

  }

}