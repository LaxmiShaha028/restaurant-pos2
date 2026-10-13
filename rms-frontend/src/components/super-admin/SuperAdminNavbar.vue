<script setup>

import {
  computed,
  onBeforeUnmount,
  onMounted,
  ref,
  watch,
} from 'vue'

import {
  useRoute,
  useRouter,
} from 'vue-router'

import {
  useSuperAdminStore,
} from '@/stores/superAdmin'


import {
  getCentralSubscriptions,
} from '@/services/superAdminApi'


/*
|--------------------------------------------------------------------------
| Router / Store
|--------------------------------------------------------------------------
*/

const route =
  useRoute()

const router =
  useRouter()

const superAdminStore =
  useSuperAdminStore()


/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const navbarRef =
  ref(null)

const mobileOpen =
  ref(false)

const openDropdown =
  ref('')

const loggingOut =
  ref(false)

const logoutError =
  ref('')


const notificationOpen =
  ref(false)

const notificationLoading =
  ref(false)

const notificationError =
  ref('')

const expiryNotifications =
  ref([])

const seenExpiryNotificationIds =
  ref(new Set())

const notificationNow =
  ref(Date.now())

let notificationPollTimer =
  null

let notificationClockTimer =
  null


/*
|--------------------------------------------------------------------------
| Current User
|--------------------------------------------------------------------------
*/

const superAdminName =
  computed(() => {

    return (
      superAdminStore.user?.name ||
      superAdminStore.user?.username ||
      superAdminStore.user?.email ||
      'Super Admin'
    )

  })


const superAdminInitials =
  computed(() => {

    const value =
      String(
        superAdminName.value ||
        'SA'
      )
        .trim()


    const words =
      value
        .split(/\s+/)
        .filter(Boolean)


    if (
      words.length >= 2
    ) {

      return (
        `${words[0][0]}${words[1][0]}`
      ).toUpperCase()
    }


    return value
      .slice(0, 2)
      .toUpperCase()

  })



/*
|--------------------------------------------------------------------------
| Subscription Expiry Notifications
|--------------------------------------------------------------------------
|
| Uses the existing Super Admin subscription API.
| No new backend route/service is required.
|
*/

const expiryNotificationCount =
  computed(() => {

    return expiryNotifications.value.filter(
      (notification) =>
        !seenExpiryNotificationIds.value.has(
          `${notification.id}:${notification.end_at}`
        )
    ).length

  })


const expiryNotificationBadge =
  computed(() => {

    return expiryNotificationCount.value > 99
      ? '99+'
      : String(
          expiryNotificationCount.value
        )

  })


function subscriptionEndAt(
  subscription,
  type
) {

  return type === 'trial'
    ? subscription?.trial_end_at
    : subscription?.end_at
}


function normalizeExpiryItems(
  items,
  type
) {

  if (
    !Array.isArray(
      items
    )
  ) {
    return []
  }


  const now =
    Date.now()

  const next24Hours =
    24 * 60 * 60 * 1000


  return items
    .map(
      (
        subscription
      ) => {

        const endAt =
          subscriptionEndAt(
            subscription,
            type
          )

        const endTime =
          endAt
            ? new Date(
                endAt
              ).getTime()
            : NaN


        return {
          id:
            `${type}-${subscription?.id ?? endAt ?? Math.random()}`,

          subscription_id:
            subscription?.id,

          restaurant_id:
            subscription?.restaurant?.id ||
            subscription?.restaurant_id,

          restaurant:
            subscription?.restaurant ||
            null,

          plan:
            subscription?.plan ||
            null,

          type,

          end_at:
            endAt,

          end_time:
            endTime,
        }

      }
    )
    .filter(
      (
        item
      ) => {

        if (
          Number.isNaN(
            item.end_time
          )
        ) {
          return false
        }


        const remaining =
          item.end_time -
          now


        return (
          remaining > 0 &&
          remaining <= next24Hours
        )

      }
    )
}


async function loadExpiryNotifications(
  silent = false
) {

  if (
    notificationLoading.value
  ) {
    return
  }


  notificationLoading.value =
    true


  if (
    !silent
  ) {
    notificationError.value =
      ''
  }


  try {

    const results =
      await Promise.allSettled([
        getCentralSubscriptions(
          'active'
        ),

        getCentralSubscriptions(
          'trial'
        ),
      ])


    const activeResult =
      results[0]

    const trialResult =
      results[1]


    const activeItems =
      (
        activeResult.status ===
          'fulfilled' &&
        activeResult.value?.success
      )
        ? activeResult.value.data
        : []


    const trialItems =
      (
        trialResult.status ===
          'fulfilled' &&
        trialResult.value?.success
      )
        ? trialResult.value.data
        : []


    if (
      activeResult.status ===
        'rejected' &&
      trialResult.status ===
        'rejected'
    ) {

      throw new Error(
        'Could not load subscription notifications.'
      )

    }


    expiryNotifications.value = [
      ...normalizeExpiryItems(
        activeItems,
        'active'
      ),

      ...normalizeExpiryItems(
        trialItems,
        'trial'
      ),
    ]
      .sort(
        (
          first,
          second
        ) =>
          first.end_time -
          second.end_time
      )


    notificationNow.value =
      Date.now()

    if (notificationOpen.value) {
      markExpiryNotificationsRead()
    }

    notificationError.value =
      ''

  } catch (
    error
  ) {

    if (
      !silent
    ) {

      notificationError.value =
        error?.message ||
        'Could not load subscription notifications.'

    }

  } finally {

    notificationLoading.value =
      false

  }
}


function toggleNotifications() {

  notificationOpen.value =
    !notificationOpen.value

  openDropdown.value =
    ''


  if (
    notificationOpen.value
  ) {

    markExpiryNotificationsRead()

    loadExpiryNotifications(
      true
    )

  }
}


function markExpiryNotificationsRead() {

  seenExpiryNotificationIds.value = new Set([
    ...seenExpiryNotificationIds.value,
    ...expiryNotifications.value.map(
      (notification) =>
        `${notification.id}:${notification.end_at}`
    ),
  ])

}


function closeNotifications() {

  notificationOpen.value =
    false
}


function notificationRemainingText(
  endAt
) {

  if (
    !endAt
  ) {
    return 'Expiry unavailable'
  }


  const end =
    new Date(
      endAt
    ).getTime()


  if (
    Number.isNaN(
      end
    )
  ) {
    return 'Expiry unavailable'
  }


  const diff =
    end -
    notificationNow.value


  if (
    diff <= 0
  ) {
    return 'Expiring now'
  }


  const totalMinutes =
    Math.max(
      1,
      Math.ceil(
        diff /
        (1000 * 60)
      )
    )

  const hours =
    Math.floor(
      totalMinutes / 60
    )

  const minutes =
    totalMinutes % 60


  if (
    hours > 0
  ) {

    return minutes
      ? `${hours}h ${minutes}m left`
      : `${hours}h left`

  }


  return `${minutes}m left`
}


function notificationExpiryTime(
  value
) {

  if (
    !value
  ) {
    return '—'
  }


  const date =
    new Date(
      value
    )


  if (
    Number.isNaN(
      date.getTime()
    )
  ) {
    return value
  }


  return date.toLocaleString(
    'en-BD',
    {
      day:
        '2-digit',

      month:
        'short',

      year:
        'numeric',

      hour:
        '2-digit',

      minute:
        '2-digit',
    }
  )
}


function notificationUrgencyClass(
  endAt
) {

  if (
    !endAt
  ) {
    return ''
  }


  const diff =
    new Date(
      endAt
    ).getTime() -
    notificationNow.value


  if (
    diff <=
    3 * 60 * 60 * 1000
  ) {
    return 'is-critical'
  }


  if (
    diff <=
    12 * 60 * 60 * 1000
  ) {
    return 'is-urgent'
  }


  return ''
}


function openExpiryNotification(
  notification
) {

  const restaurantId =
    notification?.restaurant?.id ||
    notification?.restaurant_id


  if (
    !restaurantId
  ) {
    return
  }


  closeNavigation()


  router.push({
    name:
      'super-admin-restaurant-details',

    params: {
      restaurant:
        restaurantId,
    },
  })
}


function startNotificationWatchers() {

  stopNotificationWatchers()


  notificationPollTimer =
    window.setInterval(
      () => {

        loadExpiryNotifications(
          true
        )

      },
      60_000
    )


  notificationClockTimer =
    window.setInterval(
      () => {

        notificationNow.value =
          Date.now()

      },
      30_000
    )
}


function stopNotificationWatchers() {

  if (
    notificationPollTimer !==
    null
  ) {

    window.clearInterval(
      notificationPollTimer
    )

    notificationPollTimer =
      null
  }


  if (
    notificationClockTimer !==
    null
  ) {

    window.clearInterval(
      notificationClockTimer
    )

    notificationClockTimer =
      null
  }
}


/*
|--------------------------------------------------------------------------
| Active Sections
|--------------------------------------------------------------------------
|
| Path based checks avoid route-name mismatch problems.
|
*/

const isDashboardActive =
  computed(() => {

    return (
      route.path ===
      '/super-admin/dashboard'
    )

  })


const isRestaurantsActive =
  computed(() => {

    return route.path.startsWith(
      '/super-admin/restaurants'
    )

  })


const isSubscriptionsActive =
  computed(() => {

    return route.path.startsWith(
      '/super-admin/subscriptions'
    )

  })


const isPaymentsActive =
  computed(() => {

    return route.path.startsWith(
      '/super-admin/payments'
    )

  })


/*
|--------------------------------------------------------------------------
| Dropdown / Mobile Navigation
|--------------------------------------------------------------------------
*/

function toggleDropdown(
  dropdown
) {

  closeNotifications()

  openDropdown.value =
    openDropdown.value === dropdown
      ? ''
      : dropdown
}


function closeNavigation() {

  closeNotifications()

  mobileOpen.value =
    false

  openDropdown.value =
    ''
}


function handleDocumentPointerDown(
  event
) {

  const navbar =
    navbarRef.value


  if (
    !navbar ||
    navbar.contains(
      event.target
    )
  ) {
    return
  }


  closeNavigation()
}


function handleDocumentKeydown(
  event
) {

  if (
    event.key ===
    'Escape'
  ) {

    closeNavigation()
  }
}


/*
|--------------------------------------------------------------------------
| Logout
|--------------------------------------------------------------------------
*/

async function handleLogout() {

  if (
    loggingOut.value
  ) {
    return
  }


  logoutError.value =
    ''


  if (
    !window.confirm(
      'Sign out from the Super Admin panel?'
    )
  ) {
    return
  }


  loggingOut.value =
    true


  try {

    await superAdminStore.logout()

    closeNavigation()


    await router.replace(
      '/super-admin/login'
    )

  } catch (
    error
  ) {

    logoutError.value =
      error?.message ||
      'Logout failed.'

  } finally {

    loggingOut.value =
      false
  }
}


/*
|--------------------------------------------------------------------------
| Lifecycle
|--------------------------------------------------------------------------
*/

watch(
  () =>
    route.fullPath,

  () => {

    closeNavigation()
  }
)


onMounted(() => {

  document.addEventListener(
    'pointerdown',
    handleDocumentPointerDown
  )

  document.addEventListener(
    'keydown',
    handleDocumentKeydown
  )

  loadExpiryNotifications(
    true
  )

  startNotificationWatchers()
})


onBeforeUnmount(() => {

  document.removeEventListener(
    'pointerdown',
    handleDocumentPointerDown
  )

  document.removeEventListener(
    'keydown',
    handleDocumentKeydown
  )

  stopNotificationWatchers()
})

</script>


<template>

  <header
    ref="navbarRef"
    class="super-admin-navbar"
  >

    <div class="super-admin-navbar-inner">


      <!-- =========================================================
           BRAND
      ========================================================== -->

      <router-link
        to="/super-admin/dashboard"
        class="super-admin-brand"
        @click="closeNavigation"
      >

        <span class="super-admin-brand-icon">

          <i class="bi bi-shield-check"></i>

        </span>


        <span class="super-admin-brand-copy">

          <strong>
            RMS
          </strong>

          <small>
            Super Admin
          </small>

        </span>

      </router-link>


      <!-- =========================================================
           MOBILE TOGGLE
      ========================================================== -->

      <button
        type="button"
        class="super-admin-mobile-toggle"
        :aria-expanded="mobileOpen"
        aria-controls="super-admin-navigation"
        aria-label="Toggle Super Admin navigation"
        @click="
          mobileOpen =
            !mobileOpen
        "
      >

        <i
          :class="
            mobileOpen
              ? 'bi bi-x-lg'
              : 'bi bi-list'
          "
        ></i>

      </button>


      <!-- =========================================================
           NAVIGATION
      ========================================================== -->

      <nav
        id="super-admin-navigation"
        class="super-admin-nav"
        :class="{
          'is-open':
            mobileOpen
        }"
        aria-label="Super Admin navigation"
      >


        <!-- Dashboard -->

        <router-link
          to="/super-admin/dashboard"
          class="super-admin-nav-link"
          :class="{
            active:
              isDashboardActive
          }"
          @click="closeNavigation"
        >

          <i class="bi bi-grid-1x2"></i>

          <span>
            Dashboard
          </span>

        </router-link>


        <!-- Restaurants -->

        <div
          class="super-admin-nav-group"
          :class="{
            active:
              isRestaurantsActive
          }"
        >

          <button
            type="button"
            class="super-admin-nav-link super-admin-dropdown-toggle"
            :aria-expanded="
              openDropdown ===
              'restaurants'
            "
            @click="
              toggleDropdown(
                'restaurants'
              )
            "
          >

            <span class="super-admin-nav-link-main">

              <i class="bi bi-shop"></i>

              <span>
                Restaurants
              </span>

            </span>


            <i
              class="bi bi-chevron-down dropdown-chevron"
              :class="{
                rotated:
                  openDropdown ===
                  'restaurants'
              }"
            ></i>

          </button>


          <div
            v-show="
              openDropdown ===
              'restaurants'
            "
            class="super-admin-dropdown"
          >

            <router-link
              to="/super-admin/restaurants"
              class="super-admin-dropdown-link"
            >

              <i class="bi bi-list-ul"></i>

              All Restaurants

            </router-link>


            <router-link
              to="/super-admin/restaurants/create"
              class="super-admin-dropdown-link"
            >

              <i class="bi bi-plus-circle"></i>

              Add Restaurant

            </router-link>

          </div>

        </div>


        <!-- Subscriptions -->

        <div
          class="super-admin-nav-group"
          :class="{
            active:
              isSubscriptionsActive
          }"
        >

          <button
            type="button"
            class="super-admin-nav-link super-admin-dropdown-toggle"
            :aria-expanded="
              openDropdown ===
              'subscriptions'
            "
            @click="
              toggleDropdown(
                'subscriptions'
              )
            "
          >

            <span class="super-admin-nav-link-main">

              <i class="bi bi-credit-card"></i>

              <span>
                Subscriptions
              </span>

            </span>


            <i
              class="bi bi-chevron-down dropdown-chevron"
              :class="{
                rotated:
                  openDropdown ===
                  'subscriptions'
              }"
            ></i>

          </button>


          <div
            v-show="
              openDropdown ===
              'subscriptions'
            "
            class="super-admin-dropdown"
          >

            <router-link
              to="/super-admin/subscriptions/plans"
              class="super-admin-dropdown-link"
            >

              <i class="bi bi-boxes"></i>

              Plans

            </router-link>


            <router-link
              to="/super-admin/subscriptions/active"
              class="super-admin-dropdown-link"
            >

              <i class="bi bi-check-circle"></i>

              Active

            </router-link>


            <router-link
              to="/super-admin/subscriptions/trial"
              class="super-admin-dropdown-link"
            >

              <i class="bi bi-hourglass-split"></i>

              Trial

            </router-link>


            <router-link
              to="/super-admin/subscriptions/expired"
              class="super-admin-dropdown-link"
            >

              <i class="bi bi-clock-history"></i>

              Expired

            </router-link>


            <router-link
              to="/super-admin/subscriptions/cancelled"
              class="super-admin-dropdown-link"
            >

              <i class="bi bi-x-circle"></i>

              Cancelled

            </router-link>

          </div>

        </div>


        <!-- Payments -->

        <div
          class="super-admin-nav-group"
          :class="{
            active:
              isPaymentsActive
          }"
        >

          <button
            type="button"
            class="super-admin-nav-link super-admin-dropdown-toggle"
            :aria-expanded="
              openDropdown ===
              'payments'
            "
            @click="
              toggleDropdown(
                'payments'
              )
            "
          >

            <span class="super-admin-nav-link-main">

              <i class="bi bi-wallet2"></i>

              <span>
                Payments
              </span>

            </span>


            <i
              class="bi bi-chevron-down dropdown-chevron"
              :class="{
                rotated:
                  openDropdown ===
                  'payments'
              }"
            ></i>

          </button>


          <div
            v-show="
              openDropdown ===
              'payments'
            "
            class="super-admin-dropdown"
          >

            <router-link
              to="/super-admin/payments/methods"
              class="super-admin-dropdown-link"
            >

              <i class="bi bi-credit-card-2-front"></i>

              Payment Methods

            </router-link>


            <router-link
              to="/super-admin/payments/transactions"
              class="super-admin-dropdown-link"
            >

              <i class="bi bi-receipt"></i>

              Transactions

            </router-link>


            <router-link
              to="/super-admin/payments/revenue"
              class="super-admin-dropdown-link"
            >

              <i class="bi bi-graph-up-arrow"></i>

              Revenue

            </router-link>

          </div>

        </div>


        <!-- =====================================================
             ACCOUNT
        ====================================================== -->

        <div class="super-admin-account">

          <!-- Subscription Expiry Notifications -->

          <div
            class="super-admin-notification-center"
            @click.stop
          >

            <button
              type="button"
              class="super-admin-notification-button"
              :class="{
                'has-notifications':
                  expiryNotificationCount > 0,
              }"
              :aria-expanded="notificationOpen"
              aria-label="Subscription expiry notifications"
              title="Subscription expiry notifications"
              @click="toggleNotifications"
            >

              <i
                :class="
                  expiryNotificationCount
                    ? 'bi bi-bell-fill'
                    : 'bi bi-bell'
                "
              ></i>

              <span
                v-if="expiryNotificationCount"
                class="super-admin-notification-badge"
              >
                {{ expiryNotificationBadge }}
              </span>

            </button>


            <div
              v-if="notificationOpen"
              class="super-admin-notification-panel"
            >

              <div class="super-admin-notification-head">

                <div>
                  <small>
                    SUBSCRIPTION ALERTS
                  </small>

                  <strong>
                    Expiring within 24 hours
                  </strong>
                </div>


                <button
                  type="button"
                  class="super-admin-notification-refresh"
                  :disabled="notificationLoading"
                  aria-label="Refresh notifications"
                  title="Refresh notifications"
                  @click="
                    loadExpiryNotifications(
                      false
                    )
                  "
                >

                  <span
                    v-if="notificationLoading"
                    class="spinner-border spinner-border-sm"
                  ></span>

                  <i
                    v-else
                    class="bi bi-arrow-clockwise"
                  ></i>

                </button>

              </div>


              <div
                v-if="
                  notificationLoading &&
                  !expiryNotifications.length
                "
                class="super-admin-notification-state"
              >

                <span class="spinner-border spinner-border-sm"></span>

                Checking subscriptions...

              </div>


              <div
                v-else-if="notificationError"
                class="super-admin-notification-state is-error"
              >

                <i class="bi bi-exclamation-circle"></i>

                {{ notificationError }}

              </div>


              <div
                v-else-if="expiryNotifications.length"
                class="super-admin-notification-list"
              >

                <button
                  v-for="notification in expiryNotifications"
                  :key="notification.id"
                  type="button"
                  class="super-admin-notification-item"
                  :class="
                    notificationUrgencyClass(
                      notification.end_at
                    )
                  "
                  @click="
                    openExpiryNotification(
                      notification
                    )
                  "
                >

                  <span class="super-admin-notification-alert-icon">

                    <i
                      :class="
                        notification.type === 'trial'
                          ? 'bi bi-hourglass-split'
                          : 'bi bi-alarm'
                      "
                    ></i>

                  </span>


                  <span class="super-admin-notification-copy">

                    <span class="super-admin-notification-item-top">

                      <strong>
                        {{
                          notification.restaurant?.name ||
                          'Restaurant'
                        }}
                      </strong>

                      <small>
                        {{
                          notificationRemainingText(
                            notification.end_at
                          )
                        }}
                      </small>

                    </span>


                    <span class="super-admin-notification-item-meta">

                      {{
                        notification.type === 'trial'
                          ? 'Trial'
                          : notification.plan?.name ||
                            'Active Plan'
                      }}

                      <span>•</span>

                      {{
                        notificationExpiryTime(
                          notification.end_at
                        )
                      }}

                    </span>

                  </span>

                </button>

              </div>


              <div
                v-else
                class="super-admin-notification-empty"
              >

                <i class="bi bi-check2-circle"></i>

                <strong>
                  No upcoming expirations
                </strong>

                <span>
                  Nothing expires within the next 24 hours.
                </span>

              </div>


              <div class="super-admin-notification-foot">

                <span>
                  Auto-refreshes every minute
                </span>

                <strong>
                  {{ expiryNotificationCount }}
                  alert{{
                    expiryNotificationCount === 1
                      ? ''
                      : 's'
                  }}
                </strong>

              </div>

            </div>

          </div>


          <div class="super-admin-user">

            <span class="super-admin-user-avatar">

              {{ superAdminInitials }}

            </span>


            <span class="super-admin-user-copy">

              <strong>
                {{ superAdminName }}
              </strong>

              <small>
                Administrator
              </small>

            </span>

          </div>


          <button
            type="button"
            class="super-admin-logout-button"
            :disabled="loggingOut"
            @click="handleLogout"
          >

            <span
              v-if="loggingOut"
              class="spinner-border spinner-border-sm"
            ></span>

            <i
              v-else
              class="bi bi-box-arrow-right"
            ></i>

            <span>
              {{
                loggingOut
                  ? 'Signing out...'
                  : 'Logout'
              }}
            </span>

          </button>

        </div>


        <div
          v-if="logoutError"
          class="super-admin-logout-error"
          role="alert"
        >
          {{ logoutError }}
        </div>

      </nav>

    </div>

  </header>

</template>


<style
  src="@/styles/super-admin/navbar.css"
></style>


<style scoped>

.super-admin-notification-center {
  position: relative;
  flex: 0 0 auto;
}

.super-admin-notification-button {
  position: relative;
  display: inline-flex;
  width: 40px;
  height: 40px;
  align-items: center;
  justify-content: center;
  padding: 0;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  background: #ffffff;
  color: #64748b;
  font-size: 17px;
  line-height: 1;
  cursor: pointer;
  transition:
    border-color .18s ease,
    background .18s ease,
    color .18s ease,
    box-shadow .18s ease,
    transform .18s ease;
}

.super-admin-notification-button:hover {
  border-color: #bfdbfe;
  background: #eff6ff;
  color: #2563eb;
  box-shadow:
    0 8px 20px
    rgba(37, 99, 235, .10);
  transform: translateY(-1px);
}

.super-admin-notification-button.has-notifications {
  border-color: #fecaca;
  background: #fff7f7;
  color: #dc2626;
}

.super-admin-notification-badge {
  position: absolute;
  top: -6px;
  right: -6px;
  display: inline-flex;
  min-width: 19px;
  height: 19px;
  align-items: center;
  justify-content: center;
  padding: 0 5px;
  border: 2px solid #ffffff;
  border-radius: 999px;
  background: #ef4444;
  color: #ffffff;
  font-size: 9px;
  font-weight: 800;
  line-height: 1;
}

.super-admin-notification-panel {
  position: absolute;
  z-index: 2000;
  top: calc(100% + 10px);
  right: 0;
  width: min(390px, calc(100vw - 24px));
  overflow: hidden;
  border: 1px solid #e5e7eb;
  border-radius: 16px;
  background: #ffffff;
  box-shadow:
    0 22px 60px
    rgba(15, 23, 42, .18);
}

.super-admin-notification-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
  padding: 15px 16px;
  border-bottom: 1px solid #eef2f7;
  background:
    linear-gradient(
      180deg,
      #ffffff,
      #fbfdff
    );
}

.super-admin-notification-head > div {
  min-width: 0;
}

.super-admin-notification-head small {
  display: block;
  margin-bottom: 3px;
  color: #94a3b8;
  font-size: 9px;
  font-weight: 800;
  letter-spacing: .8px;
}

.super-admin-notification-head strong {
  display: block;
  color: #0f172a;
  font-size: 14px;
  font-weight: 800;
}

.super-admin-notification-refresh {
  display: inline-flex;
  width: 32px;
  height: 32px;
  flex: 0 0 auto;
  align-items: center;
  justify-content: center;
  padding: 0;
  border: 1px solid #e2e8f0;
  border-radius: 9px;
  background: #ffffff;
  color: #64748b;
  cursor: pointer;
}

.super-admin-notification-refresh:hover:not(:disabled) {
  border-color: #bfdbfe;
  background: #eff6ff;
  color: #2563eb;
}

.super-admin-notification-refresh:disabled {
  opacity: .6;
  cursor: not-allowed;
}

.super-admin-notification-list {
  max-height: 340px;
  overflow-y: auto;
  padding: 7px;
}

.super-admin-notification-item {
  display: flex;
  width: 100%;
  align-items: flex-start;
  gap: 10px;
  padding: 11px;
  border: 0;
  border-radius: 11px;
  background: transparent;
  text-align: left;
  cursor: pointer;
  transition:
    background .16s ease,
    transform .16s ease;
}

.super-admin-notification-item + .super-admin-notification-item {
  margin-top: 3px;
}

.super-admin-notification-item:hover {
  background: #f8fafc;
  transform: translateX(2px);
}

.super-admin-notification-item.is-urgent {
  background: #fffaf0;
}

.super-admin-notification-item.is-critical {
  background: #fff1f2;
}

.super-admin-notification-alert-icon {
  display: inline-flex;
  width: 34px;
  height: 34px;
  flex: 0 0 auto;
  align-items: center;
  justify-content: center;
  border-radius: 10px;
  background: #fff7ed;
  color: #ea580c;
  font-size: 14px;
}

.super-admin-notification-item.is-critical
.super-admin-notification-alert-icon {
  background: #fee2e2;
  color: #dc2626;
}

.super-admin-notification-copy {
  min-width: 0;
  flex: 1;
}

.super-admin-notification-item-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
}

.super-admin-notification-item-top strong {
  min-width: 0;
  overflow: hidden;
  color: #1e293b;
  font-size: 12px;
  font-weight: 750;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.super-admin-notification-item-top small {
  flex: 0 0 auto;
  color: #dc2626;
  font-size: 9px;
  font-weight: 800;
}

.super-admin-notification-item-meta {
  display: block;
  margin-top: 5px;
  overflow: hidden;
  color: #7c8798;
  font-size: 10px;
  line-height: 1.5;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.super-admin-notification-item-meta > span {
  margin: 0 4px;
  color: #cbd5e1;
}

.super-admin-notification-state,
.super-admin-notification-empty {
  display: flex;
  min-height: 150px;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 24px 18px;
  color: #64748b;
  font-size: 11px;
  text-align: center;
}

.super-admin-notification-state.is-error {
  color: #b91c1c;
}

.super-admin-notification-empty {
  flex-direction: column;
  gap: 5px;
}

.super-admin-notification-empty > i {
  margin-bottom: 4px;
  color: #059669;
  font-size: 27px;
}

.super-admin-notification-empty strong {
  color: #334155;
  font-size: 12px;
}

.super-admin-notification-empty span {
  color: #94a3b8;
  font-size: 10px;
}

.super-admin-notification-foot {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 9px 13px;
  border-top: 1px solid #eef2f7;
  background: #fafcff;
  color: #94a3b8;
  font-size: 9px;
}

.super-admin-notification-foot strong {
  color: #475569;
  font-weight: 800;
}

@media (
  max-width: 768px
) {

  .super-admin-notification-panel {
    position: fixed;
    top: 76px;
    right: 12px;
    left: 12px;
    width: auto;
  }

}

</style>
