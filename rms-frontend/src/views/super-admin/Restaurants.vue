<template>

  <section class="super-admin-restaurants-page">

    <header class="restaurants-page-header">

      <div class="restaurants-heading">

        <div class="restaurants-heading-icon">
          <i class="bi bi-shop"></i>
        </div>

        <div>
          <span class="restaurants-eyebrow">
            Restaurant Management
          </span>

          <h1>Restaurants</h1>

          <p>
            Manage all restaurants registered on the platform.
          </p>
        </div>

      </div>

      <div class="restaurants-header-actions">

        <button
          type="button"
          class="btn btn-outline-secondary restaurants-refresh-button"
          :disabled="loading"
          @click="loadRestaurants"
        >
          <span
            v-if="loading"
            class="spinner-border spinner-border-sm"
          ></span>

          <i
            v-else
            class="bi bi-arrow-clockwise"
          ></i>

          <span>
            {{
              loading
                ? 'Refreshing...'
                : 'Refresh'
            }}
          </span>
        </button>

        <router-link
          :to="{
            name:
              'super-admin-create-restaurant',
          }"
          class="btn btn-primary restaurants-add-button"
        >
          <i class="bi bi-plus-lg"></i>
          <span>Add Restaurant</span>
        </router-link>

      </div>

    </header>


    <section
      v-if="restaurants.length"
      class="restaurants-stat-grid"
    >

      <article class="restaurants-stat-card">
        <span class="restaurants-stat-icon restaurants-stat-icon-primary">
          <i class="bi bi-buildings"></i>
        </span>
        <div>
          <span>Total</span>
          <strong>{{ totalRestaurants }}</strong>
        </div>
      </article>

      <article class="restaurants-stat-card">
        <span class="restaurants-stat-icon restaurants-stat-icon-success">
          <i class="bi bi-check-circle"></i>
        </span>
        <div>
          <span>Active</span>
          <strong>{{ activeRestaurants }}</strong>
        </div>
      </article>

      <article class="restaurants-stat-card">
        <span class="restaurants-stat-icon restaurants-stat-icon-warning">
          <i class="bi bi-hourglass-split"></i>
        </span>
        <div>
          <span>Pending</span>
          <strong>{{ pendingRestaurants }}</strong>
        </div>
      </article>

      <article class="restaurants-stat-card">
        <span class="restaurants-stat-icon restaurants-stat-icon-muted">
          <i class="bi bi-slash-circle"></i>
        </span>
        <div>
          <span>Other / Inactive</span>
          <strong>{{ inactiveRestaurants }}</strong>
        </div>
      </article>

    </section>


    <div
      v-if="loading && !restaurants.length"
      class="restaurants-state-card"
    >
      <span class="spinner-border text-primary"></span>
      <div>
        <strong>Loading restaurants</strong>
        <p>Fetching the latest restaurant records.</p>
      </div>
    </div>


    <div
      v-if="error"
      class="restaurants-alert restaurants-alert-danger"
    >
      <div class="restaurants-alert-icon">
        <i class="bi bi-exclamation-triangle-fill"></i>
      </div>

      <div class="restaurants-alert-content">
        <strong>Restaurants could not be loaded</strong>
        <p>{{ error }}</p>
      </div>

      <button
        type="button"
        class="btn btn-sm btn-outline-danger"
        :disabled="loading"
        @click="loadRestaurants"
      >
        Try Again
      </button>
    </div>


    <div
      v-if="statusActionError"
      class="restaurants-alert restaurants-alert-danger"
    >
      <div class="restaurants-alert-icon">
        <i class="bi bi-exclamation-triangle-fill"></i>
      </div>

      <div class="restaurants-alert-content">
        <strong>Status could not be changed</strong>
        <p>{{ statusActionError }}</p>
      </div>

      <button
        type="button"
        class="btn btn-sm btn-outline-danger"
        @click="statusActionError = ''"
      >
        Dismiss
      </button>
    </div>


    <section
      v-if="!loading && restaurants.length"
      class="restaurants-table-card"
    >

      <div class="restaurants-table-header">
        <div>
          <span class="restaurants-section-kicker">Directory</span>
          <h2>All Restaurants</h2>
        </div>

        <span class="restaurants-table-count">
          {{ totalRestaurants }}
          restaurant{{ totalRestaurants === 1 ? '' : 's' }}
        </span>
      </div>

      <div class="restaurants-table-wrapper">

        <table class="restaurants-table">

          <thead>
            <tr>
              <th>ID</th>
              <th>Restaurant</th>
              <th>Owner</th>
              <th>Status</th>
              <th>Plan</th>
              <th>Login URL</th>
              <th>End Date</th>
              <th class="restaurants-actions-heading">Actions</th>
            </tr>
          </thead>

          <tbody>

            <tr
              v-for="restaurant in restaurants"
              :key="restaurant.id"
            >

              <td data-label="ID" class="restaurants-id-cell">
                #{{ restaurant.id }}
              </td>

              <td data-label="Restaurant">
                <div class="restaurant-primary-cell">
                  <span class="restaurant-avatar">
                    {{
                      String(
                        restaurant.name ||
                        'R'
                      )
                        .charAt(0)
                        .toUpperCase()
                    }}
                  </span>

                  <div class="restaurant-primary-copy">
                    <strong>{{ restaurant.name }}</strong>
                    <small>{{ restaurant.owner_email }}</small>
                  </div>
                </div>
              </td>

              <td data-label="Owner">
                <div class="restaurant-owner-cell">
                  <strong>
                    {{ restaurant.owner_name || '—' }}
                  </strong>

                  <small v-if="restaurant.phone">
                    {{ restaurant.phone }}
                  </small>
                </div>
              </td>

              <td data-label="Status">
                <span
                  class="restaurant-status-badge"
                  :class="restaurantStatusClass(restaurant.status)"
                >
                  <span class="restaurant-status-dot"></span>
                  {{ restaurant.status || 'unknown' }}
                </span>
              </td>

              <td data-label="Plan">
                <div class="restaurant-info-cell">
                  <strong>
                    {{ restaurant.plan_name || 'No plan' }}
                  </strong>

                  <small
                    :class="
                      restaurant.subscription_status === 'active'
                        ? 'restaurant-substatus-success'
                        : 'restaurant-substatus-muted'
                    "
                  >
                    {{
                      restaurant.subscription_status ||
                      'No subscription'
                    }}
                  </small>
                </div>
              </td>

              <td data-label="Login URL">
                <div class="restaurant-login-cell">
                  <a
                    v-if="restaurant.tenant_key"
                    :href="restaurantLoginUrl(restaurant)"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="restaurant-login-link"
                    :title="restaurantLoginUrl(restaurant)"
                  >
                    <i class="bi bi-link-45deg"></i>
                    <span>
                      {{ restaurantLoginUrl(restaurant) }}
                    </span>
                  </a>

                  <span
                    v-else
                    class="restaurant-login-missing"
                  >
                    No login URL
                  </span>
                </div>
              </td>

              <td data-label="End Date">
                <div class="restaurant-info-cell">
                  <strong class="restaurant-date">
                    {{ formatEndDate(restaurant.subscription_end_at) }}
                  </strong>

                  <small
                    v-if="restaurant.subscription_end_at"
                    :class="
                      canActivateRestaurant(restaurant)
                        ? 'restaurant-substatus-success'
                        : 'restaurant-substatus-danger'
                    "
                  >
                    {{
                      canActivateRestaurant(restaurant)
                        ? 'Valid'
                        : 'Expired / unavailable'
                    }}
                  </small>
                </div>
              </td>

              <td
                data-label="Actions"
                class="restaurants-actions-cell"
              >
                <div class="restaurant-action-group">

                  <button
                    type="button"
                    class="restaurant-action-button restaurant-action-view"
                    :aria-label="`View ${restaurant.name}`"
                    :title="`View ${restaurant.name}`"
                    @click="viewRestaurant(restaurant.id)"
                  >
                    <i class="bi bi-eye"></i>
                    <span>View</span>
                  </button>

                  <button
                    type="button"
                    class="restaurant-action-button restaurant-action-status"
                    :aria-label="
                      restaurant.status === 'active'
                        ? `Deactivate ${restaurant.name}`
                        : `Activate ${restaurant.name}`
                    "
                    :class="
                      restaurant.status === 'active'
                        ? 'restaurant-action-status-active'
                        : 'restaurant-action-status-inactive'
                    "
                    :disabled="
                      statusUpdatingId === restaurant.id ||
                      (
                        restaurant.status !== 'active' &&
                        !canActivateRestaurant(restaurant)
                      )
                    "
                    :title="statusToggleTitle(restaurant)"
                    @click="toggleRestaurantStatus(restaurant)"
                  >
                    <span
                      v-if="statusUpdatingId === restaurant.id"
                      class="spinner-border spinner-border-sm"
                    ></span>

                    <i
                      v-else
                      :class="
                        restaurant.status === 'active'
                          ? 'bi bi-check-circle-fill'
                          : 'bi bi-slash-circle'
                      "
                    ></i>

                    <span>
                      {{
                        statusUpdatingId === restaurant.id
                          ? 'Updating...'
                          : (
                              restaurant.status === 'active'
                                ? 'Active'
                                : 'Inactive'
                            )
                      }}
                    </span>
                  </button>

                  <button
                    v-if="
                      restaurant.status === 'active' &&
                      restaurant.tenant_key
                    "
                    type="button"
                    class="restaurant-action-button restaurant-action-open"
                    :aria-label="`Open ${restaurant.name}`"
                    :title="`Open ${restaurant.name}`"
                    @click="openRestaurant(restaurant)"
                  >
                    <i class="bi bi-box-arrow-up-right"></i>
                    <span>Open</span>
                  </button>

                </div>
              </td>

            </tr>

          </tbody>

        </table>

      </div>

    </section>


    <section
      v-if="!loading && !error && !restaurants.length"
      class="restaurants-empty-state"
    >
      <div class="restaurants-empty-icon">
        <i class="bi bi-shop-window"></i>
      </div>

      <h2>No restaurants found</h2>

      <p>
        Create the first restaurant to start managing the platform.
      </p>

      <router-link
        :to="{
          name:
            'super-admin-create-restaurant',
        }"
        class="btn btn-primary"
      >
        <i class="bi bi-plus-lg me-1"></i>
        Add Restaurant
      </router-link>
    </section>

  </section>

</template>

<script setup>

import {
  computed,
  onMounted,
  ref,
} from 'vue'

import {
  useRouter,
} from 'vue-router'

import {
  getRestaurants,
  updateRestaurantStatus,
} from '@/services/superAdminApi'


/*
|--------------------------------------------------------------------------
| Router
|--------------------------------------------------------------------------
*/

const router =
  useRouter()


/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const restaurants =
  ref([])

const loading =
  ref(false)

const error =
  ref('')

const statusUpdatingId =
  ref(null)

const statusActionError =
  ref('')


/*
|--------------------------------------------------------------------------
| Derived Statistics
|--------------------------------------------------------------------------
*/

const totalRestaurants =
  computed(() => {

    return restaurants.value.length

  })


const activeRestaurants =
  computed(() => {

    return restaurants.value.filter(
      (restaurant) =>
        restaurant.status ===
        'active'
    ).length

  })


const pendingRestaurants =
  computed(() => {

    return restaurants.value.filter(
      (restaurant) =>
        restaurant.status ===
        'pending'
    ).length

  })


const inactiveRestaurants =
  computed(() => {

    return restaurants.value.filter(
      (restaurant) =>
        ![
          'active',
          'pending',
        ].includes(
          restaurant.status
        )
    ).length

  })


/*
|--------------------------------------------------------------------------
| Load Restaurants
|--------------------------------------------------------------------------
*/

async function loadRestaurants() {

  if (
    loading.value
  ) {
    return
  }


  loading.value =
    true

  error.value =
    ''


  try {

    const response =
      await getRestaurants()


    if (
      response?.success
    ) {

      restaurants.value =
        Array.isArray(
          response.data
        )
          ? response.data
          : []

    } else {

      error.value =
        response?.message ||
        'Failed to load restaurants.'

    }

  } catch (
    exception
  ) {

    error.value =
      exception?.message ||
      'Failed to load restaurants.'

  } finally {

    loading.value =
      false
  }
}


/*
|--------------------------------------------------------------------------
| Open Restaurant
|--------------------------------------------------------------------------
*/

function openRestaurant(
  restaurant
) {

  if (
    !restaurant?.tenant_key
  ) {
    return
  }


  if (
    restaurant.status !==
    'active'
  ) {
    return
  }


  const tenantKey =
    String(
      restaurant.tenant_key
    ).trim()


  if (
    !tenantKey
  ) {
    return
  }


  const protocol =
    window.location.protocol


  const host =
    window.location.hostname


  const port =
    window.location.port
      ? `:${window.location.port}`
      : ''


  const tenantUrl =
    `${protocol}//` +
    `${host}` +
    `${port}` +
    `/tenant/` +
    `${encodeURIComponent(tenantKey)}`


  window.open(
    tenantUrl,
    '_blank',
    'noopener,noreferrer',
  )
}


/*
|--------------------------------------------------------------------------
| View Restaurant
|--------------------------------------------------------------------------
*/

function viewRestaurant(
  restaurantId
) {

  router.push({
    name:
      'super-admin-restaurant-details',

    params: {
      restaurant:
        restaurantId,
    },
  })
}


/*
|--------------------------------------------------------------------------
| Format Subscription End Date
|--------------------------------------------------------------------------
*/

function formatEndDate(
  value
) {

  if (
    !value
  ) {
    return '—'
  }


  const date =
    new Date(value)


  if (
    Number.isNaN(
      date.getTime()
    )
  ) {
    return value
  }


  return date.toLocaleString(
    'en-GB',
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
    },
  )
}


/*
|--------------------------------------------------------------------------
| Restaurant Login URL
|--------------------------------------------------------------------------
*/

function restaurantLoginUrl(
  restaurant
) {

  const tenantKey =
    String(
      restaurant?.tenant_key ||
      ''
    ).trim()


  if (
    !tenantKey
  ) {
    return ''
  }


  return (
    `${window.location.origin}` +
    `/tenant/` +
    `${encodeURIComponent(tenantKey)}`
  )
}


/*
|--------------------------------------------------------------------------
| Can Restaurant Be Activated?
|--------------------------------------------------------------------------
|
| Super Admin may deactivate a restaurant at any time.
|
| Reactivation is allowed only when:
| - the latest subscription is ACTIVE
| - subscription_end_at exists
| - subscription_end_at is still in the future
|
*/

function canActivateRestaurant(
  restaurant
) {

  if (
    restaurant?.subscription_status !==
    'active'
  ) {
    return false
  }


  if (
    !restaurant?.subscription_end_at
  ) {
    return false
  }


  const endAt =
    new Date(
      restaurant.subscription_end_at
    )


  if (
    Number.isNaN(
      endAt.getTime()
    )
  ) {
    return false
  }


  return (
    endAt.getTime() >
    Date.now()
  )
}


/*
|--------------------------------------------------------------------------
| Toggle Restaurant Active / Inactive
|--------------------------------------------------------------------------
*/

async function toggleRestaurantStatus(
  restaurant
) {

  if (
    !restaurant?.id ||
    statusUpdatingId.value
  ) {
    return
  }


  const isCurrentlyActive =
    restaurant.status ===
    'active'


  /*
  |--------------------------------------------------------------------------
  | Inactive -> Active Protection
  |--------------------------------------------------------------------------
  */

  if (
    !isCurrentlyActive &&
    !canActivateRestaurant(
      restaurant
    )
  ) {

    statusActionError.value =
      'This restaurant cannot be activated because its subscription is expired, inactive, or has no valid remaining end date.'

    return
  }


  const nextStatus =
    isCurrentlyActive
      ? 'inactive'
      : 'active'


  statusUpdatingId.value =
    restaurant.id

  statusActionError.value =
    ''


  try {

    const response =
      await updateRestaurantStatus(
        restaurant.id,
        nextStatus
      )


    if (
      !response?.success
    ) {
      throw new Error(
        response?.message ||
        'Restaurant status could not be updated.'
      )
    }


    restaurant.status =
      response?.data?.restaurant?.status ||
      nextStatus

  } catch (
    exception
  ) {

    statusActionError.value =
      exception?.message ||
      'Restaurant status could not be updated.'

  } finally {

    statusUpdatingId.value =
      null
  }
}


/*
|--------------------------------------------------------------------------
| Status Toggle Tooltip
|--------------------------------------------------------------------------
*/

function statusToggleTitle(
  restaurant
) {

  if (
    restaurant.status ===
    'active'
  ) {
    return 'Click to make this restaurant inactive.'
  }


  if (
    canActivateRestaurant(
      restaurant
    )
  ) {
    return 'Click to make this restaurant active.'
  }


  return 'Cannot activate: subscription has expired or is not currently active.'
}


/*
|--------------------------------------------------------------------------
| Restaurant Status Class
|--------------------------------------------------------------------------
*/

function restaurantStatusClass(
  status
) {

  if (
    status === 'active'
  ) {
    return 'restaurant-status-active'
  }


  if (
    status === 'pending'
  ) {
    return 'restaurant-status-pending'
  }


  if (
    status === 'failed'
  ) {
    return 'restaurant-status-failed'
  }


  if (
    status === 'suspended'
  ) {
    return 'restaurant-status-suspended'
  }


  return 'restaurant-status-inactive'
}


/*
|--------------------------------------------------------------------------
| Initial Load
|--------------------------------------------------------------------------
*/

onMounted(
  loadRestaurants
)

</script>


<style
  src="@/styles/super-admin/restaurants.css"
></style>

<style scoped>
.restaurant-login-cell {
  max-width: 290px;
}

.restaurant-login-link {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  max-width: 100%;
  color: inherit;
  text-decoration: none;
}

.restaurant-login-link span {
  display: block;
  max-width: 245px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.restaurant-login-link:hover {
  text-decoration: underline;
}

.restaurant-login-missing {
  color: var(--bs-secondary-color);
  font-size: 0.875rem;
}

.restaurant-action-status-active {
  border-color: rgba(25, 135, 84, 0.35);
  color: var(--bs-success);
}

.restaurant-action-status-inactive {
  border-color: rgba(108, 117, 125, 0.35);
  color: var(--bs-secondary);
}

.restaurant-action-status:disabled {
  cursor: not-allowed;
  opacity: 0.55;
}
</style>
