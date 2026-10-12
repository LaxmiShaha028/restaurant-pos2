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


/*
|--------------------------------------------------------------------------
| Suspended Restaurants
|--------------------------------------------------------------------------
*/

const suspendedRestaurants =
  computed(() => {

    return restaurants.value.filter(
      (restaurant) =>
        String(
          restaurant?.status || ''
        ).toLowerCase() ===
        'suspended'
    )

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
| Format Date
|--------------------------------------------------------------------------
*/

function formatDate(
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


  return date.toLocaleDateString(
    'en-GB',
    {
      day:
        '2-digit',

      month:
        'short',

      year:
        'numeric',
    },
  )
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


<template>

  <section class="suspended-restaurants-page">


    <!-- =========================================================
         HEADER
    ========================================================== -->

    <header class="suspended-page-header">

      <div class="suspended-heading">

        <div class="suspended-heading-icon">

          <i class="bi bi-slash-circle"></i>

        </div>


        <div>

          <span class="suspended-eyebrow">
            Restaurant Management
          </span>

          <h1>
            Suspended Restaurants
          </h1>

          <p>
            Review restaurant accounts currently marked as suspended.
          </p>

        </div>

      </div>


      <div class="suspended-header-actions">

        <router-link
          :to="{
            name:
              'super-admin-restaurants',
          }"
          class="btn btn-outline-secondary suspended-action-button"
        >

          <i class="bi bi-arrow-left"></i>

          <span>
            All Restaurants
          </span>

        </router-link>


        <button
          type="button"
          class="btn btn-primary suspended-action-button"
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

      </div>

    </header>


    <!-- =========================================================
         SUMMARY
    ========================================================== -->

    <section
      v-if="
        !loading ||
        suspendedRestaurants.length
      "
      class="suspended-summary-card"
    >

      <div class="suspended-summary-icon">

        <i class="bi bi-shield-exclamation"></i>

      </div>


      <div class="suspended-summary-content">

        <span>
          Suspended Accounts
        </span>

        <strong>
          {{ suspendedRestaurants.length }}
        </strong>

        <p>
          Restaurant accounts whose central status is currently
          <b>suspended</b>.
        </p>

      </div>

    </section>


    <!-- =========================================================
         LOADING
    ========================================================== -->

    <div
      v-if="
        loading &&
        !restaurants.length
      "
      class="suspended-state-card"
    >

      <span class="spinner-border text-primary"></span>


      <div>

        <strong>
          Loading suspended restaurants
        </strong>

        <p>
          Fetching the latest central restaurant list.
        </p>

      </div>

    </div>


    <!-- =========================================================
         ERROR
    ========================================================== -->

    <div
      v-if="error"
      class="suspended-alert"
    >

      <div class="suspended-alert-icon">

        <i class="bi bi-exclamation-triangle-fill"></i>

      </div>


      <div class="suspended-alert-content">

        <strong>
          Suspended restaurants could not be loaded
        </strong>

        <p>
          {{ error }}
        </p>

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


    <!-- =========================================================
         TABLE
    ========================================================== -->

    <section
      v-if="
        !loading &&
        !error &&
        suspendedRestaurants.length
      "
      class="suspended-table-card"
    >

      <div class="suspended-table-header">

        <div>

          <span class="suspended-section-kicker">
            Directory
          </span>

          <h2>
            Suspended Restaurant Accounts
          </h2>

        </div>


        <span class="suspended-count-badge">

          {{ suspendedRestaurants.length }}

          suspended

        </span>

      </div>


      <div class="suspended-table-wrapper">

        <table class="suspended-table">

          <thead>

            <tr>

              <th>
                ID
              </th>

              <th>
                Restaurant
              </th>

              <th>
                Owner
              </th>

              <th>
                Status
              </th>

              <th>
                Tenant Database
              </th>

              <th>
                Domain
              </th>

              <th>
                Created
              </th>

              <th class="suspended-actions-heading">
                Action
              </th>

            </tr>

          </thead>


          <tbody>

            <tr
              v-for="restaurant in suspendedRestaurants"
              :key="restaurant.id"
            >


              <!-- ID -->

              <td
                data-label="ID"
                class="suspended-id-cell"
              >
                #{{ restaurant.id }}
              </td>


              <!-- Restaurant -->

              <td data-label="Restaurant">

                <div class="suspended-primary-cell">

                  <span class="suspended-avatar">

                    {{
                      String(
                        restaurant.name ||
                        'R'
                      )
                        .charAt(0)
                        .toUpperCase()
                    }}

                  </span>


                  <div class="suspended-primary-copy">

                    <strong>
                      {{ restaurant.name }}
                    </strong>

                    <small>
                      {{
                        restaurant.owner_email ||
                        '—'
                      }}
                    </small>

                  </div>

                </div>

              </td>


              <!-- Owner -->

              <td data-label="Owner">

                <div class="suspended-info-cell">

                  <strong>
                    {{
                      restaurant.owner_name ||
                      '—'
                    }}
                  </strong>

                  <small
                    v-if="restaurant.phone"
                  >
                    {{ restaurant.phone }}
                  </small>

                </div>

              </td>


              <!-- Status -->

              <td data-label="Status">

                <span class="suspended-status-badge">

                  <span class="suspended-status-dot"></span>

                  Suspended

                </span>

              </td>


              <!-- Database -->

              <td data-label="Tenant Database">

                <div class="suspended-info-cell">

                  <strong>
                    {{
                      restaurant.database_name ||
                      'Not provisioned'
                    }}
                  </strong>

                  <small>
                    {{
                      restaurant.database_status ||
                      'N/A'
                    }}
                  </small>

                </div>

              </td>


              <!-- Domain -->

              <td data-label="Domain">

                <div class="suspended-info-cell">

                  <strong>
                    {{
                      restaurant.primary_domain ||
                      'Not configured'
                    }}
                  </strong>


                  <small
                    v-if="restaurant.primary_domain"
                    :class="
                      restaurant.domain_verified
                        ? 'suspended-domain-verified'
                        : 'suspended-domain-unverified'
                    "
                  >

                    <i
                      :class="
                        restaurant.domain_verified
                          ? 'bi bi-patch-check-fill'
                          : 'bi bi-exclamation-circle'
                      "
                    ></i>

                    {{
                      restaurant.domain_verified
                        ? 'Verified'
                        : 'Not verified'
                    }}

                  </small>

                </div>

              </td>


              <!-- Created -->

              <td data-label="Created">

                <span class="suspended-date">

                  {{
                    formatDate(
                      restaurant.created_at
                    )
                  }}

                </span>

              </td>


              <!-- Action -->

              <td
                data-label="Action"
                class="suspended-actions-cell"
              >

                <button
                  type="button"
                  class="suspended-view-button"
                  @click="
                    viewRestaurant(
                      restaurant.id
                    )
                  "
                >

                  <i class="bi bi-eye"></i>

                  <span>
                    View Details
                  </span>

                </button>

              </td>

            </tr>

          </tbody>

        </table>

      </div>

    </section>


    <!-- =========================================================
         EMPTY
    ========================================================== -->

    <section
      v-if="
        !loading &&
        !error &&
        !suspendedRestaurants.length
      "
      class="suspended-empty-state"
    >

      <div class="suspended-empty-icon">

        <i class="bi bi-check-circle"></i>

      </div>


      <h2>
        No suspended restaurants
      </h2>


      <p>
        There are currently no restaurant accounts marked as suspended.
      </p>


      <router-link
        :to="{
          name:
            'super-admin-restaurants',
        }"
        class="btn btn-outline-primary"
      >

        <i class="bi bi-shop me-1"></i>

        View All Restaurants

      </router-link>

    </section>

  </section>

</template>


<style
  src="@/styles/super-admin/suspended-restaurants.css"
></style>
