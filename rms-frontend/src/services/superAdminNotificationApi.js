import api from '@/services/api'


/*
|--------------------------------------------------------------------------
| Subscription Expiry Notifications
|--------------------------------------------------------------------------
|
| Fetches restaurants whose latest active subscription expires
| inside the next 24 hours.
|
*/

export async function getExpiringSubscriptionNotifications() {

  const response =
    await api.get(
      '/central/notifications/subscription-expiring',
      {
        params: {
          _t: Date.now(),
        },
      }
    )

  return response.data
}
