<script setup>

import {
  computed,
  onMounted,
  ref,
  watch,
} from 'vue'

import {
  useRouter,
} from 'vue-router'

import {
  getCentralSubscriptions,
} from '@/services/superAdminApi'


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props =
  defineProps({

    type: {
      type:
        String,

      required:
        true,
    },

    title: {
      type:
        String,

      required:
        true,
    },

    description: {
      type:
        String,

      required:
        true,
    },

    icon: {
      type:
        String,

      default:
        'bi bi-credit-card',
    },
  })


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

const subscriptions =
  ref([])

const loading =
  ref(false)

const error =
  ref('')

const search =
  ref('')

const lastUpdatedAt =
  ref(null)


/*
|--------------------------------------------------------------------------
| Filtered Subscriptions
|--------------------------------------------------------------------------
*/

const filteredSubscriptions =
  computed(() => {

    const keyword =
      search.value
        .trim()
        .toLowerCase()


    if (
      !keyword
    ) {

      return subscriptions.value
    }


    return subscriptions.value.filter(
      (subscription) => {

        const restaurant =
          subscription?.restaurant ||
          {}

        const plan =
          subscription?.plan ||
          {}


        const haystack = [
          restaurant.name,
          restaurant.owner_name,
          restaurant.owner_email,
          restaurant.status,
          plan.name,
          plan.slug,
          subscription.status,
          subscription.cancel_reason,
        ]
          .filter(Boolean)
          .join(' ')
          .toLowerCase()


        return haystack.includes(
          keyword
        )
      }
    )

  })


/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

const totalCount =
  computed(() => {

    return subscriptions.value.length

  })


/*
|--------------------------------------------------------------------------
| Load
|--------------------------------------------------------------------------
*/

async function loadSubscriptions() {

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
      await getCentralSubscriptions(
        props.type
      )


    if (
      !response?.success
    ) {

      throw new Error(
        response?.message ||
        'Failed to load subscriptions.'
      )
    }


    subscriptions.value =
      Array.isArray(
        response.data
      )
        ? response.data
        : []


    lastUpdatedAt.value =
      new Date()

  } catch (
    exception
  ) {

    error.value =
      exception?.message ||
      'Failed to load subscriptions.'

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
  subscription
) {

  const restaurantId =
    subscription
      ?.restaurant
      ?.id ||
    subscription
      ?.restaurant_id


  if (
    !restaurantId
  ) {
    return
  }


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
| Formatting
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
    }
  )
}


function formatPrice(
  value,
  currency = 'BDT'
) {

  const amount =
    Number(
      value || 0
    )


  return `${currency || 'BDT'} ${amount.toLocaleString(
    'en-BD',
    {
      maximumFractionDigits:
        2,
    }
  )}`
}


function remainingLabel(
  value
) {

  if (
    !value
  ) {
    return '—'
  }


  const endAt =
    new Date(value)


  if (
    Number.isNaN(
      endAt.getTime()
    )
  ) {
    return '—'
  }


  const milliseconds =
    endAt.getTime() -
    Date.now()


  if (
    milliseconds <= 0
  ) {
    return 'Ended'
  }


  const hours =
    Math.ceil(
      milliseconds /
      (
        1000 *
        60 *
        60
      )
    )


  if (
    hours < 24
  ) {
    return `${hours}h left`
  }


  const days =
    Math.ceil(
      hours /
      24
    )


  return `${days}d left`
}


function formatLastUpdated() {

  if (
    !lastUpdatedAt.value
  ) {
    return 'Not refreshed yet'
  }


  return lastUpdatedAt.value
    .toLocaleTimeString(
      'en-GB',
      {
        hour:
          '2-digit',

        minute:
          '2-digit',

        second:
          '2-digit',
      }
    )
}


/*
|--------------------------------------------------------------------------
| Type Presentation
|--------------------------------------------------------------------------
*/

const typeLabel =
  computed(() => {

    const labels = {
      active:
        'Active Paid',

      trial:
        'Trial',

      expired:
        'Expired',

      cancelled:
        'Cancelled',
    }


    return (
      labels[props.type] ||
      props.type
    )

  })


const statusClass =
  computed(() => {

    return `subscription-status-${props.type}`

  })


/*
|--------------------------------------------------------------------------
| Lifecycle
|--------------------------------------------------------------------------
*/

onMounted(
  loadSubscriptions
)


watch(
  () =>
    props.type,

  () => {

    search.value =
      ''

    loadSubscriptions()

  }
)

</script>


<template>

  <section class="subscriptions-page">


    <!-- =========================================================
         HEADER
    ========================================================== -->

    <header class="subscriptions-page-header">

      <div class="subscriptions-heading">

        <div
          class="subscriptions-heading-icon"
          :class="
            `subscriptions-heading-icon-${type}`
          "
        >

          <i :class="icon"></i>

        </div>


        <div>

          <span class="subscriptions-eyebrow">
            Subscription Management
          </span>

          <h1>
            {{ title }}
          </h1>

          <p>
            {{ description }}
          </p>

        </div>

      </div>


      <div class="subscriptions-header-actions">

        <div class="subscriptions-last-updated">

          <span>
            Last updated
          </span>

          <strong>
            {{ formatLastUpdated() }}
          </strong>

        </div>


        <button
          type="button"
          class="btn btn-primary subscriptions-refresh-button"
          :disabled="loading"
          @click="loadSubscriptions"
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

    <section class="subscriptions-toolbar-card">

      <div class="subscriptions-summary">

        <span
          class="subscriptions-summary-icon"
          :class="
            `subscriptions-summary-icon-${type}`
          "
        >

          <i :class="icon"></i>

        </span>


        <div>

          <span>
            {{ typeLabel }}
          </span>

          <strong>
            {{ totalCount }}
          </strong>

        </div>

      </div>


      <div class="subscriptions-search">

        <i class="bi bi-search"></i>

        <input
          v-model="search"
          type="search"
          placeholder="Search restaurant, owner or plan..."
          aria-label="Search subscriptions"
        />

        <button
          v-if="search"
          type="button"
          aria-label="Clear search"
          @click="
            search = ''
          "
        >
          <i class="bi bi-x-lg"></i>
        </button>

      </div>

    </section>


    <!-- =========================================================
         ERROR
    ========================================================== -->

    <div
      v-if="error"
      class="subscriptions-alert"
    >

      <div class="subscriptions-alert-icon">

        <i class="bi bi-exclamation-triangle-fill"></i>

      </div>


      <div class="subscriptions-alert-content">

        <strong>
          Subscriptions could not be loaded
        </strong>

        <p>
          {{ error }}
        </p>

      </div>


      <button
        type="button"
        class="btn btn-sm btn-outline-danger"
        :disabled="loading"
        @click="loadSubscriptions"
      >
        Try Again
      </button>

    </div>


    <!-- =========================================================
         INITIAL LOADING
    ========================================================== -->

    <div
      v-if="
        loading &&
        !subscriptions.length
      "
      class="subscriptions-loading-card"
    >

      <span class="spinner-border text-primary"></span>


      <div>

        <strong>
          Loading subscriptions
        </strong>

        <p>
          Fetching the latest central subscription records.
        </p>

      </div>

    </div>


    <!-- =========================================================
         TABLE
    ========================================================== -->

    <section
      v-if="
        !loading &&
        !error &&
        filteredSubscriptions.length
      "
      class="subscriptions-table-card"
    >

      <div class="subscriptions-table-header">

        <div>

          <span class="subscriptions-section-kicker">
            Directory
          </span>

          <h2>
            {{ title }}
          </h2>

        </div>


        <span class="subscriptions-count-badge">

          {{ filteredSubscriptions.length }}

          record{{
            filteredSubscriptions.length === 1
              ? ''
              : 's'
          }}

        </span>

      </div>


      <div class="subscriptions-table-wrapper">

        <table class="subscriptions-table">

          <thead>

            <tr>

              <th>
                Restaurant
              </th>

              <th>
                Plan
              </th>

              <th>
                Status
              </th>

              <th>
                Start
              </th>

              <th>
                {{
                  type === 'trial'
                    ? 'Trial End'
                    : type === 'cancelled'
                      ? 'Cancelled'
                      : 'End'
                }}
              </th>

              <th>
                {{
                  type === 'cancelled'
                    ? 'Reason'
                    : 'Period'
                }}
              </th>

              <th class="subscriptions-actions-heading">
                Action
              </th>

            </tr>

          </thead>


          <tbody>

            <tr
              v-for="subscription in filteredSubscriptions"
              :key="subscription.id"
            >


              <!-- Restaurant -->

              <td data-label="Restaurant">

                <div class="subscription-restaurant-cell">

                  <span class="subscription-restaurant-avatar">

                    {{
                      String(
                        subscription.restaurant?.name ||
                        'R'
                      )
                        .charAt(0)
                        .toUpperCase()
                    }}

                  </span>


                  <div class="subscription-primary-copy">

                    <strong>
                      {{
                        subscription.restaurant?.name ||
                        'Unknown Restaurant'
                      }}
                    </strong>

                    <small>
                      {{
                        subscription.restaurant?.owner_email ||
                        '—'
                      }}
                    </small>

                  </div>

                </div>

              </td>


              <!-- Plan -->

              <td data-label="Plan">

                <div class="subscription-info-cell">

                  <strong>
                    {{
                      subscription.plan?.name ||
                      'Unknown Plan'
                    }}
                  </strong>

                  <small>
                    {{
                      formatPrice(
                        subscription.plan?.price,
                        subscription.plan?.currency
                      )
                    }}
                  </small>

                </div>

              </td>


              <!-- Status -->

              <td data-label="Status">

                <span
                  class="subscription-status-badge"
                  :class="statusClass"
                >

                  <span class="subscription-status-dot"></span>

                  {{ typeLabel }}

                </span>

              </td>


              <!-- Start -->

              <td data-label="Start">

                <span class="subscription-date">
                  {{
                    formatDate(
                      type === 'trial'
                        ? subscription.trial_start_at
                        : subscription.start_at
                    )
                  }}
                </span>

              </td>


              <!-- End -->

              <td
                :data-label="
                  type === 'trial'
                    ? 'Trial End'
                    : type === 'cancelled'
                      ? 'Cancelled'
                      : 'End'
                "
              >

                <span class="subscription-date">
                  {{
                    formatDate(
                      type === 'trial'
                        ? subscription.trial_end_at
                        : type === 'cancelled'
                          ? subscription.cancelled_at
                          : subscription.end_at
                    )
                  }}
                </span>

              </td>


              <!-- Period / Reason -->

              <td
                :data-label="
                  type === 'cancelled'
                    ? 'Reason'
                    : 'Period'
                "
              >

                <div
                  v-if="
                    type === 'cancelled'
                  "
                  class="subscription-reason"
                >
                  {{
                    subscription.cancel_reason ||
                    'No reason provided'
                  }}
                </div>


                <span
                  v-else
                  class="subscription-remaining"
                >

                  {{
                    remainingLabel(
                      type === 'trial'
                        ? subscription.trial_end_at
                        : subscription.end_at
                    )
                  }}

                </span>

              </td>


              <!-- Action -->

              <td
                data-label="Action"
                class="subscriptions-actions-cell"
              >

                <button
                  type="button"
                  class="subscription-manage-button"
                  @click="
                    viewRestaurant(
                      subscription
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
         SEARCH EMPTY
    ========================================================== -->

    <section
      v-if="
        !loading &&
        !error &&
        subscriptions.length &&
        !filteredSubscriptions.length
      "
      class="subscriptions-empty-state"
    >

      <div class="subscriptions-empty-icon">

        <i class="bi bi-search"></i>

      </div>


      <h2>
        No matching subscriptions
      </h2>


      <p>
        Try a different restaurant, owner or plan search.
      </p>


      <button
        type="button"
        class="btn btn-outline-primary"
        @click="
          search = ''
        "
      >
        Clear Search
      </button>

    </section>


    <!-- =========================================================
         EMPTY
    ========================================================== -->

    <section
      v-if="
        !loading &&
        !error &&
        !subscriptions.length
      "
      class="subscriptions-empty-state"
    >

      <div
        class="subscriptions-empty-icon"
        :class="
          `subscriptions-empty-icon-${type}`
        "
      >

        <i :class="icon"></i>

      </div>


      <h2>
        No {{ typeLabel.toLowerCase() }} subscriptions
      </h2>


      <p>
        There are currently no subscription records in this category.
      </p>


      <router-link
        :to="{
          name:
            'super-admin-restaurants',
        }"
        class="btn btn-outline-primary"
      >

        <i class="bi bi-shop me-1"></i>

        View Restaurants

      </router-link>

    </section>

  </section>

</template>


<style
  src="@/styles/super-admin/subscriptions.css"
></style>
