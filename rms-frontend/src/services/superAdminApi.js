import api from '@/services/api'


/*
|--------------------------------------------------------------------------
| Central API Base URL
|--------------------------------------------------------------------------
*/

const CENTRAL_BASE_URL =
  '/central'


/*
|--------------------------------------------------------------------------
| Error Normalizer
|--------------------------------------------------------------------------
*/

function normalizeError(error) {

  return (
    error?.response?.data?.message ||
    error?.message ||
    'Something went wrong.'
  )
}


/*
|--------------------------------------------------------------------------
| Central Dashboard
|--------------------------------------------------------------------------
*/

export async function getCentralDashboard() {

  try {

    const response =
      await api.get(
        `${CENTRAL_BASE_URL}/dashboard`
      )

    return response.data

  } catch (error) {

    throw new Error(
      normalizeError(error)
    )

  }
}


/*
|--------------------------------------------------------------------------
| Restaurants
|--------------------------------------------------------------------------
*/

export async function getRestaurants() {

  try {

    const response =
      await api.get(
        `${CENTRAL_BASE_URL}/restaurants`
      )

    return response.data

  } catch (error) {

    throw new Error(
      normalizeError(error)
    )

  }
}


/*
|--------------------------------------------------------------------------
| Subscription Plans
|--------------------------------------------------------------------------
*/

export async function getSubscriptionPlans() {

  try {

    const response =
      await api.get(
        `${CENTRAL_BASE_URL}/restaurants/plans`
      )

    return response.data

  } catch (error) {

    throw new Error(
      normalizeError(error)
    )

  }
}


/*
|--------------------------------------------------------------------------
| Payment Methods
|--------------------------------------------------------------------------
*/

export async function getPaymentMethods() {

  try {

    const response =
      await api.get(
        `${CENTRAL_BASE_URL}/payment-methods`
      )

    return response.data

  } catch (error) {

    const normalizedError =
      new Error(
        normalizeError(error)
      )

    normalizedError.validationErrors =
      error?.response?.data?.errors ||
      null

    throw normalizedError
  }
}


/*
|--------------------------------------------------------------------------
| Restaurant Details
|--------------------------------------------------------------------------
|
| Cache buster is used so the details page always receives the latest
| restaurant/subscription lifecycle state from the backend.
|
*/

export async function getRestaurant(
  restaurantId
) {

  try {

    const response =
      await api.get(
        `${CENTRAL_BASE_URL}/restaurants/${restaurantId}`,
        {
          params: {
            _t: Date.now(),
          },
        }
      )

    return response.data

  } catch (error) {

    throw new Error(
      normalizeError(error)
    )

  }
}


/*
|--------------------------------------------------------------------------
| Restaurant Subscription
|--------------------------------------------------------------------------
|
| Cache buster prevents a stale subscription response after end_at/status
| changes in the central database.
|
*/

export async function getSubscription(
  restaurantId
) {

  try {

    const response =
      await api.get(
        `${CENTRAL_BASE_URL}/restaurants/${restaurantId}/subscription`,
        {
          params: {
            _t: Date.now(),
          },
        }
      )

    return response.data

  } catch (error) {

    throw new Error(
      normalizeError(error)
    )

  }
}




/*
|--------------------------------------------------------------------------
| Central Subscription Lists
|--------------------------------------------------------------------------
|
| type:
| active | trial | expired | cancelled
|
*/

export async function getCentralSubscriptions(
  type = 'active'
) {

  try {

    const response =
      await api.get(
        `${CENTRAL_BASE_URL}/subscriptions`,
        {
          params: {
            type,
            _t:
              Date.now(),
          },
        }
      )

    return response.data

  } catch (error) {

    const normalizedError =
      new Error(
        normalizeError(error)
      )

    normalizedError.validationErrors =
      error?.response?.data?.errors ||
      null

    throw normalizedError
  }
}




/*
|--------------------------------------------------------------------------
| Central Payment Transactions
|--------------------------------------------------------------------------
*/

export async function getCentralTransactions() {

  try {

    const response =
      await api.get(
        `${CENTRAL_BASE_URL}/payments/transactions`,
        {
          params: {
            _t:
              Date.now(),
          },
        }
      )

    return response.data

  } catch (error) {

    throw new Error(
      normalizeError(error)
    )
  }
}


/*
|--------------------------------------------------------------------------
| Central Revenue
|--------------------------------------------------------------------------
*/

export async function getCentralRevenue() {

  try {

    const response =
      await api.get(
        `${CENTRAL_BASE_URL}/payments/revenue`,
        {
          params: {
            _t:
              Date.now(),
          },
        }
      )

    return response.data

  } catch (error) {

    throw new Error(
      normalizeError(error)
    )
  }
}




/*
|--------------------------------------------------------------------------
| Managed Subscription Plans
|--------------------------------------------------------------------------
*/

export async function getManagedSubscriptionPlans() {

  try {

    const response =
      await api.get(
        `${CENTRAL_BASE_URL}/management/subscription-plans`,
        {
          params: {
            _t:
              Date.now(),
          },
        }
      )

    return response.data

  } catch (error) {

    throw new Error(
      normalizeError(error)
    )
  }
}


export async function createManagedSubscriptionPlan(
  payload
) {

  try {

    const response =
      await api.post(
        `${CENTRAL_BASE_URL}/management/subscription-plans`,
        payload
      )

    return response.data

  } catch (error) {

    const normalizedError =
      new Error(
        normalizeError(error)
      )

    normalizedError.validationErrors =
      error?.response?.data?.errors ||
      null

    throw normalizedError
  }
}


export async function updateManagedSubscriptionPlan(
  planId,
  payload
) {

  try {

    const response =
      await api.put(
        `${CENTRAL_BASE_URL}/management/subscription-plans/${planId}`,
        payload
      )

    return response.data

  } catch (error) {

    const normalizedError =
      new Error(
        normalizeError(error)
      )

    normalizedError.validationErrors =
      error?.response?.data?.errors ||
      null

    throw normalizedError
  }
}


export async function updateManagedSubscriptionPlanStatus(
  planId,
  isActive
) {

  try {

    const response =
      await api.patch(
        `${CENTRAL_BASE_URL}/management/subscription-plans/${planId}/status`,
        {
          is_active:
            Boolean(isActive),
        }
      )

    return response.data

  } catch (error) {

    throw new Error(
      normalizeError(error)
    )
  }
}


/*
|--------------------------------------------------------------------------
| Managed Payment Methods
|--------------------------------------------------------------------------
*/

export async function getManagedPaymentMethods() {

  try {

    const response =
      await api.get(
        `${CENTRAL_BASE_URL}/management/payment-methods`,
        {
          params: {
            _t:
              Date.now(),
          },
        }
      )

    return response.data

  } catch (error) {

    throw new Error(
      normalizeError(error)
    )
  }
}


export async function createManagedPaymentMethod(
  payload
) {

  try {

    const response =
      await api.post(
        `${CENTRAL_BASE_URL}/management/payment-methods`,
        payload
      )

    return response.data

  } catch (error) {

    const normalizedError =
      new Error(
        normalizeError(error)
      )

    normalizedError.validationErrors =
      error?.response?.data?.errors ||
      null

    throw normalizedError
  }
}


export async function updateManagedPaymentMethod(
  methodId,
  payload
) {

  try {

    const response =
      await api.put(
        `${CENTRAL_BASE_URL}/management/payment-methods/${methodId}`,
        payload
      )

    return response.data

  } catch (error) {

    const normalizedError =
      new Error(
        normalizeError(error)
      )

    normalizedError.validationErrors =
      error?.response?.data?.errors ||
      null

    throw normalizedError
  }
}


export async function updateManagedPaymentMethodStatus(
  methodId,
  status
) {

  try {

    const response =
      await api.patch(
        `${CENTRAL_BASE_URL}/management/payment-methods/${methodId}/status`,
        {
          status:
            Boolean(status),
        }
      )

    return response.data

  } catch (error) {

    throw new Error(
      normalizeError(error)
    )
  }
}


/*
|--------------------------------------------------------------------------
| Restaurant Payment History
|--------------------------------------------------------------------------
*/

export async function getPaymentHistory(
  restaurantId
) {

  try {

    const response =
      await api.get(
        `${CENTRAL_BASE_URL}/restaurants/${restaurantId}/payments`
      )

    return response.data

  } catch (error) {

    throw new Error(
      normalizeError(error)
    )

  }
}


/*
|--------------------------------------------------------------------------
| Retry Paid Restaurant Provisioning
|--------------------------------------------------------------------------
*/

export async function retryRestaurantProvisioning(
  restaurantId
) {
  try {
    const response = await api.post(
      `${CENTRAL_BASE_URL}/restaurants/${restaurantId}/subscriptions/retry-provisioning`
    )

    return response.data
  } catch (error) {
    const provisioningError =
      error?.response?.data?.errors?.provisioning?.[0]

    throw new Error(
      provisioningError || normalizeError(error)
    )
  }
}


/*
|--------------------------------------------------------------------------
| Update Restaurant Status
|--------------------------------------------------------------------------
*/

export async function updateRestaurantStatus(
  restaurantId,
  status
) {

  try {

    const response =
      await api.patch(
        `${CENTRAL_BASE_URL}/restaurants/${restaurantId}/status`,
        {
          status,
        }
      )

    return response.data

  } catch (error) {

    throw new Error(
      normalizeError(error)
    )

  }
}


/*
|--------------------------------------------------------------------------
| Update Subscription Status
|--------------------------------------------------------------------------
*/

export async function updateSubscriptionStatus(
  restaurantId,
  status,
  cancelReason = null,
) {

  try {

    const response =
      await api.patch(
        `${CENTRAL_BASE_URL}/restaurants/${restaurantId}/subscription/status`,
        {
          status,
          cancel_reason:
            cancelReason || null,
        }
      )

    return response.data

  } catch (error) {

    const normalizedError =
      new Error(
        normalizeError(error)
      )

    normalizedError.validationErrors =
      error?.response?.data?.errors ||
      null

    throw normalizedError
  }
}


/*
|--------------------------------------------------------------------------
| Create Restaurant
|--------------------------------------------------------------------------
*/

export async function createRestaurant(
  payload
) {

  try {

    const response =
      await api.post(
        `${CENTRAL_BASE_URL}/restaurants`,
        payload
      )

    return response.data

  } catch (error) {

    const apiMessage =
      error?.response?.data?.message

    const validationErrors =
      error?.response?.data?.errors

    const message =
      apiMessage ||
      error?.message ||
      'Failed to create restaurant.'

    const normalizedError =
      new Error(message)

    normalizedError.validationErrors =
      validationErrors || null

    throw normalizedError
  }
}


/*
|--------------------------------------------------------------------------
| Complete Restaurant Subscription Payment
|--------------------------------------------------------------------------
*/

export async function completeRestaurantPayment(
  restaurantId,
  payload
) {

  try {

    const response =
      await api.post(
        `${CENTRAL_BASE_URL}/restaurants/${restaurantId}/subscriptions/payment`,
        payload
      )

    return response.data

  } catch (error) {

    const apiMessage =
      error?.response?.data?.message

    const validationErrors =
      error?.response?.data?.errors

    const message =
      apiMessage ||
      error?.message ||
      'Failed to complete restaurant payment.'

    const normalizedError =
      new Error(message)

    normalizedError.validationErrors =
      validationErrors || null

    throw normalizedError
  }
}

export async function createStripeCheckout(payload) {
  try {
    const response = await api.post(`${CENTRAL_BASE_URL}/stripe/checkout`, payload)
    return response.data
  } catch (error) {
    throw new Error(normalizeError(error))
  }
}

export async function confirmStripePayment(sessionId) {
  try {
    const response = await api.post(`${CENTRAL_BASE_URL}/stripe/confirm`, { session_id: sessionId })
    return response.data
  } catch (error) {
    throw new Error(normalizeError(error))
  }
}

export async function getStripePaymentDocuments(paymentId) {
  try {
    const response = await api.get(`${CENTRAL_BASE_URL}/stripe/payments/${paymentId}/documents`)
    return response.data
  } catch (error) {
    throw new Error(normalizeError(error))
  }
}


/*
|--------------------------------------------------------------------------
| Complete Subscription Renewal
|--------------------------------------------------------------------------
*/

export async function completeSubscriptionRenewal(
  restaurantId,
  payload
) {

  try {

    const response =
      await api.post(
        `${CENTRAL_BASE_URL}/restaurants/${restaurantId}/subscriptions/renew`,
        payload
      )

    return response.data

  } catch (error) {

    const normalizedError =
      new Error(
        normalizeError(error)
      )

    normalizedError.validationErrors =
      error?.response?.data?.errors ||
      null

    throw normalizedError
  }
}


/*
|--------------------------------------------------------------------------
| Upgrade Trial To Paid
|--------------------------------------------------------------------------
*/

export async function upgradeTrialToPaid(
  restaurantId,
  payload
) {

  try {

    const response =
      await api.post(
        `${CENTRAL_BASE_URL}/restaurants/${restaurantId}/subscriptions/upgrade`,
        payload
      )

    return response.data

  } catch (error) {

    const normalizedError =
      new Error(
        normalizeError(error)
      )

    normalizedError.validationErrors =
      error?.response?.data?.errors ||
      null

    throw normalizedError
  }
}


/*
|--------------------------------------------------------------------------
| Update Active Paid Subscription Plan
|--------------------------------------------------------------------------
*/

export async function updatePaidSubscriptionPlan(
  restaurantId,
  payload
) {

  try {

    const response =
      await api.post(
        `${CENTRAL_BASE_URL}/restaurants/${restaurantId}/subscriptions/plan`,
        payload
      )

    return response.data

  } catch (error) {

    const normalizedError =
      new Error(
        normalizeError(error)
      )

    normalizedError.validationErrors =
      error?.response?.data?.errors ||
      null

    throw normalizedError
  }
}


/*
|--------------------------------------------------------------------------
| Reset Tenant Admin Credentials
|--------------------------------------------------------------------------
*/

export async function resetTenantAdminCredentials(
  restaurantId
) {

  try {

    const response =
      await api.post(
        `${CENTRAL_BASE_URL}/restaurants/${restaurantId}/reset-admin-credentials`
      )

    return response.data

  } catch (error) {

    throw new Error(
      normalizeError(error)
    )

  }
}
