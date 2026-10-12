<script setup>

import {
  ref,
  computed,
  onMounted,
} from 'vue'

import {
  useRouter,
} from 'vue-router'

import {
  getSubscriptionPlans,
  createRestaurant,
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

const loadingPlans =
  ref(false)

const submitting =
  ref(false)

const errorMessage =
  ref('')

const successMessage =
  ref('')


/*
|--------------------------------------------------------------------------
| Plans
|--------------------------------------------------------------------------
*/

const plans =
  ref([])


/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/

const form =
  ref({

    /*
    |----------------------------------------------------------------------
    | Restaurant
    |----------------------------------------------------------------------
    */

    name:
      '',

    logo_path:
      '',


    /*
    |----------------------------------------------------------------------
    | Owner
    |----------------------------------------------------------------------
    */

    owner_name:
      '',

    owner_email:
      '',

    phone:
      '',


    /*
    |----------------------------------------------------------------------
    | Address
    |----------------------------------------------------------------------
    */

    address:
      '',

    city:
      '',

    country:
      'Bangladesh',


    /*
    |----------------------------------------------------------------------
    | Tenant Admin
    |----------------------------------------------------------------------
    */

    username:
      '',

    password:
      '',


    /*
    |----------------------------------------------------------------------
    | Subscription
    |----------------------------------------------------------------------
    */

    subscription_plan_id:
      '',
  })


/*
|--------------------------------------------------------------------------
| Validation Errors
|--------------------------------------------------------------------------
*/

const validationErrors =
  ref({})


/*
|--------------------------------------------------------------------------
| Selected Plan
|--------------------------------------------------------------------------
*/

const selectedPlan =
  computed(() => {

    return plans.value.find(
      (plan) =>
        String(plan.id) ===
        String(
          form.value.subscription_plan_id
        )
    ) || null

  })


/*
|--------------------------------------------------------------------------
| Load Subscription Plans
|--------------------------------------------------------------------------
*/

async function loadPlans() {

  loadingPlans.value =
    true

  errorMessage.value =
    ''

  try {

    const response =
      await getSubscriptionPlans()


    if (
      response?.success
    ) {

      plans.value =
        Array.isArray(
          response.data
        )
          ? response.data
          : []

    } else {

      errorMessage.value =
        response?.message ||
        'Failed to load subscription plans.'

    }

  } catch (
    error
  ) {

    errorMessage.value =
      error?.message ||
      'Failed to load subscription plans.'

  } finally {

    loadingPlans.value =
      false
  }
}


/*
|--------------------------------------------------------------------------
| Submit
|--------------------------------------------------------------------------
*/

async function handleSubmit() {

  errorMessage.value =
    ''

  successMessage.value =
    ''

  validationErrors.value =
    {}


  /*
  |--------------------------------------------------------------------------
  | Basic Client Validation
  |--------------------------------------------------------------------------
  */

  if (
    !form.value.name.trim()
  ) {

    errorMessage.value =
      'Restaurant name is required.'

    return
  }


  if (
    !form.value.owner_name.trim()
  ) {

    errorMessage.value =
      'Owner name is required.'

    return
  }


  if (
    !form.value.owner_email.trim()
  ) {

    errorMessage.value =
      'Owner email is required.'

    return
  }


  if (
    !form.value.subscription_plan_id
  ) {

    errorMessage.value =
      'Please select a subscription plan.'

    return
  }


  /*
  |--------------------------------------------------------------------------
  | Submit
  |--------------------------------------------------------------------------
  */

  submitting.value =
    true

  try {

    const payload = {

      /*
      |----------------------------------------------------------------------
      | Restaurant Information
      |----------------------------------------------------------------------
      */

      name:
        form.value.name.trim(),

      logo_path:
        form.value.logo_path.trim() ||
        null,


      /*
      |----------------------------------------------------------------------
      | Owner Information
      |----------------------------------------------------------------------
      */

      owner_name:
        form.value.owner_name.trim(),

      owner_email:
        form.value.owner_email.trim(),

      phone:
        form.value.phone.trim() ||
        null,


      /*
      |----------------------------------------------------------------------
      | Address
      |----------------------------------------------------------------------
      */

      address:
        form.value.address.trim() ||
        null,

      city:
        form.value.city.trim() ||
        null,

      country:
        form.value.country.trim() ||
        null,


      /*
      |----------------------------------------------------------------------
      | Tenant Admin
      |----------------------------------------------------------------------
      */

      username:
        form.value.username.trim() ||
        null,

      password:
        form.value.password ||
        null,


      /*
      |----------------------------------------------------------------------
      | Subscription
      |----------------------------------------------------------------------
      */

      subscription_plan_id:
        Number(
          form.value.subscription_plan_id
        ),
    }


    const response =
      await createRestaurant(
        payload
      )


    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    */

    successMessage.value =
      response?.message ||
      'Restaurant created successfully.'


    /*
    |--------------------------------------------------------------------------
    | Navigate To Restaurant Details
    |--------------------------------------------------------------------------
    */

    const restaurant =
      response?.data?.restaurant


    /*
    | Trial creation may return restaurant
    | inside provisioning result.
    */

    const restaurantId =
      restaurant?.id ||
      response?.data?.id ||
      response?.data?.restaurant_id


    if (
      restaurantId
    ) {

      setTimeout(() => {

        router.push({

          name:
            'super-admin-restaurant-details',

          params: {

            restaurant:
              restaurantId,
          },
        })

      }, 700)

    } else {

      /*
      | Paid restaurant:
      | backend creates a pending restaurant
      | and payment must be completed later.
      */

      setTimeout(() => {

        router.push({

          name:
            'super-admin-restaurants',

        })

      }, 1200)

    }

  } catch (
    error
  ) {

    errorMessage.value =
      error?.message ||
      'Failed to create restaurant.'


    validationErrors.value =
      error?.validationErrors ||
      {}

  } finally {

    submitting.value =
      false
  }
}


/*
|--------------------------------------------------------------------------
| Validation Error Helper
|--------------------------------------------------------------------------
*/

function fieldError(
  field
) {

  const errors =
    validationErrors.value?.[field]


  if (
    Array.isArray(errors) &&
    errors.length
  ) {

    return errors[0]
  }


  return ''
}


/*
|--------------------------------------------------------------------------
| Format Price
|--------------------------------------------------------------------------
*/

function formatPrice(
  price
) {

  const amount =
    Number(
      price || 0
    )


  return amount.toLocaleString(
    'en-BD',
    {
      minimumFractionDigits:
        0,

      maximumFractionDigits:
        2,
    }
  )
}


/*
|--------------------------------------------------------------------------
| Billing Label
|--------------------------------------------------------------------------
*/

function billingLabel(
  plan
) {

  const count =
    Number(
      plan?.billing_interval_count ||
      1
    )

  const interval =
    plan?.billing_interval ||
    'month'


  if (
    count === 1
  ) {

    return interval
  }


  return `${count} ${interval}s`
}


/*
|--------------------------------------------------------------------------
| Initial Load
|--------------------------------------------------------------------------
*/

onMounted(
  loadPlans
)

</script>


<template>

  <section class="create-restaurant-page">


    <!-- =========================================================
         HEADER
    ========================================================== -->

    <header class="create-restaurant-header">

      <div class="create-restaurant-heading">

        <div class="create-restaurant-heading-icon">

          <i class="bi bi-shop-window"></i>

        </div>


        <div>

          <span class="create-restaurant-eyebrow">
            Restaurant Management
          </span>

          <h1>
            Create Restaurant
          </h1>

          <p>
            Create a restaurant, configure the owner and tenant admin,
            then assign a subscription plan.
          </p>

        </div>

      </div>


      <button
        type="button"
        class="btn btn-outline-secondary create-back-button"
        :disabled="submitting"
        @click="
          router.push({
            name: 'super-admin-restaurants',
          })
        "
      >

        <i class="bi bi-arrow-left"></i>

        <span>
          Back to Restaurants
        </span>

      </button>

    </header>


    <!-- =========================================================
         ERROR
    ========================================================== -->

    <div
      v-if="errorMessage"
      class="create-alert create-alert-danger"
    >

      <div class="create-alert-icon">

        <i class="bi bi-exclamation-triangle-fill"></i>

      </div>


      <div>

        <strong>
          Restaurant could not be created
        </strong>

        <p>
          {{ errorMessage }}
        </p>

      </div>

    </div>


    <!-- =========================================================
         SUCCESS
    ========================================================== -->

    <div
      v-if="successMessage"
      class="create-alert create-alert-success"
    >

      <div class="create-alert-icon">

        <i class="bi bi-check-circle-fill"></i>

      </div>


      <div>

        <strong>
          Success
        </strong>

        <p>
          {{ successMessage }}
        </p>

      </div>

    </div>


    <!-- =========================================================
         LOADING PLANS
    ========================================================== -->

    <div
      v-if="loadingPlans"
      class="create-loading-card"
    >

      <span class="spinner-border text-primary"></span>


      <div>

        <strong>
          Loading subscription plans
        </strong>

        <p>
          Fetching available plans from the central system.
        </p>

      </div>

    </div>


    <!-- =========================================================
         FORM
    ========================================================== -->

    <form
      class="create-restaurant-form"
      @submit.prevent="handleSubmit"
    >

      <div class="create-form-grid">


        <!-- =====================================================
             RESTAURANT INFORMATION
        ====================================================== -->

        <section class="create-form-card">

          <div class="create-form-card-header">

            <div class="create-form-card-icon">

              <i class="bi bi-shop"></i>

            </div>


            <div>

              <span class="create-form-card-kicker">
                Step 1
              </span>

              <h2>
                Restaurant Information
              </h2>

              <p>
                Basic restaurant identity and address information.
              </p>

            </div>

          </div>


          <div class="create-form-card-body">


            <!-- Restaurant Name -->

            <div class="create-field">

              <label
                for="restaurant-name"
                class="create-label"
              >

                Restaurant Name

                <span class="create-required">
                  *
                </span>

              </label>


              <input
                id="restaurant-name"
                v-model="form.name"
                type="text"
                class="form-control create-control"
                placeholder="Enter restaurant name"
                :disabled="submitting"
              />


              <small
                v-if="fieldError('name')"
                class="create-field-error"
              >
                {{ fieldError('name') }}
              </small>

            </div>


            <!-- Logo Path -->

            <div class="create-field">

              <label
                for="logo-path"
                class="create-label"
              >
                Logo Path
              </label>


              <input
                id="logo-path"
                v-model="form.logo_path"
                type="text"
                class="form-control create-control"
                placeholder="Optional logo path"
                :disabled="submitting"
              />


              <small class="create-field-help">
                Leave blank if the restaurant does not have a logo yet.
              </small>

            </div>


            <!-- Address -->

            <div class="create-field">

              <label
                for="restaurant-address"
                class="create-label"
              >
                Address
              </label>


              <textarea
                id="restaurant-address"
                v-model="form.address"
                class="form-control create-control create-textarea"
                rows="3"
                placeholder="Enter restaurant address"
                :disabled="submitting"
              ></textarea>


              <small
                v-if="fieldError('address')"
                class="create-field-error"
              >
                {{ fieldError('address') }}
              </small>

            </div>


            <!-- City / Country -->

            <div class="create-field-row">

              <div class="create-field">

                <label
                  for="restaurant-city"
                  class="create-label"
                >
                  City
                </label>


                <input
                  id="restaurant-city"
                  v-model="form.city"
                  type="text"
                  class="form-control create-control"
                  placeholder="e.g. Sylhet"
                  :disabled="submitting"
                />

              </div>


              <div class="create-field">

                <label
                  for="restaurant-country"
                  class="create-label"
                >
                  Country
                </label>


                <input
                  id="restaurant-country"
                  v-model="form.country"
                  type="text"
                  class="form-control create-control"
                  placeholder="e.g. Bangladesh"
                  :disabled="submitting"
                />

              </div>

            </div>

          </div>

        </section>


        <!-- =====================================================
             OWNER / ADMIN INFORMATION
        ====================================================== -->

        <section class="create-form-card">

          <div class="create-form-card-header">

            <div class="create-form-card-icon">

              <i class="bi bi-person-badge"></i>

            </div>


            <div>

              <span class="create-form-card-kicker">
                Step 2
              </span>

              <h2>
                Owner & Admin
              </h2>

              <p>
                Owner contact details and tenant administrator credentials.
              </p>

            </div>

          </div>


          <div class="create-form-card-body">


            <!-- Owner Name -->

            <div class="create-field">

              <label
                for="owner-name"
                class="create-label"
              >

                Owner Name

                <span class="create-required">
                  *
                </span>

              </label>


              <input
                id="owner-name"
                v-model="form.owner_name"
                type="text"
                class="form-control create-control"
                placeholder="Enter owner name"
                :disabled="submitting"
              />


              <small
                v-if="fieldError('owner_name')"
                class="create-field-error"
              >
                {{ fieldError('owner_name') }}
              </small>

            </div>


            <!-- Owner Email -->

            <div class="create-field">

              <label
                for="owner-email"
                class="create-label"
              >

                Owner Email

                <span class="create-required">
                  *
                </span>

              </label>


              <input
                id="owner-email"
                v-model="form.owner_email"
                type="email"
                class="form-control create-control"
                placeholder="owner@example.com"
                :disabled="submitting"
              />


              <small
                v-if="fieldError('owner_email')"
                class="create-field-error"
              >
                {{ fieldError('owner_email') }}
              </small>

            </div>


            <!-- Phone -->

            <div class="create-field">

              <label
                for="owner-phone"
                class="create-label"
              >
                Phone
              </label>


              <input
                id="owner-phone"
                v-model="form.phone"
                type="text"
                class="form-control create-control"
                placeholder="01XXXXXXXXX"
                :disabled="submitting"
              />


              <small
                v-if="fieldError('phone')"
                class="create-field-error"
              >
                {{ fieldError('phone') }}
              </small>

            </div>


            <div class="create-divider"></div>


            <!-- Admin Username -->

            <div class="create-field">

              <label
                for="admin-username"
                class="create-label"
              >
                Admin Username
              </label>


              <input
                id="admin-username"
                v-model="form.username"
                type="text"
                class="form-control create-control"
                placeholder="Leave blank to auto-generate"
                :disabled="submitting"
              />


              <small class="create-field-help">
                If blank, the backend generates a username from the owner email.
              </small>


              <small
                v-if="fieldError('username')"
                class="create-field-error"
              >
                {{ fieldError('username') }}
              </small>

            </div>


            <!-- Admin Password -->

            <div class="create-field">

              <label
                for="admin-password"
                class="create-label"
              >
                Admin Password
              </label>


              <input
                id="admin-password"
                v-model="form.password"
                type="password"
                class="form-control create-control"
                placeholder="Optional"
                autocomplete="new-password"
                :disabled="submitting"
              />


              <small class="create-field-help">
                Leave blank when credentials will be set during payment completion.
              </small>


              <small
                v-if="fieldError('password')"
                class="create-field-error"
              >
                {{ fieldError('password') }}
              </small>

            </div>

          </div>

        </section>

      </div>


      <!-- =====================================================
           SUBSCRIPTION PLAN
      ====================================================== -->

      <section class="create-form-card create-plan-section">

        <div class="create-form-card-header">

          <div class="create-form-card-icon">

            <i class="bi bi-credit-card"></i>

          </div>


          <div>

            <span class="create-form-card-kicker">
              Step 3
            </span>

            <h2>
              Select Subscription Plan
            </h2>

            <p>
              Choose the plan that should be assigned to this restaurant.
            </p>

          </div>

        </div>


        <div class="create-form-card-body">


          <!-- No Plans -->

          <div
            v-if="
              !loadingPlans &&
              !plans.length
            "
            class="create-empty-plans"
          >

            <div class="create-empty-plans-icon">

              <i class="bi bi-credit-card-2-front"></i>

            </div>


            <strong>
              No active subscription plans
            </strong>


            <p>
              There are currently no plans available for restaurant creation.
            </p>


            <button
              type="button"
              class="btn btn-outline-primary btn-sm"
              @click="loadPlans"
            >
              Reload Plans
            </button>

          </div>


          <!-- Plan Cards -->

          <div
            v-else
            class="create-plan-grid"
          >

            <label
              v-for="plan in plans"
              :key="plan.id"
              class="create-plan-card"
              :class="{
                'is-selected':
                  String(
                    form.subscription_plan_id
                  ) ===
                  String(plan.id)
              }"
            >

              <input
                v-model="
                  form.subscription_plan_id
                "
                class="create-plan-radio"
                type="radio"
                name="subscription_plan"
                :value="String(plan.id)"
                :disabled="submitting"
              />


              <div class="create-plan-top">

                <div>

                  <span class="create-plan-name">
                    {{ plan.name }}
                  </span>


                  <span
                    v-if="Number(plan.trial_days) > 0"
                    class="create-plan-trial-badge"
                  >

                    <i class="bi bi-clock"></i>

                    {{ plan.trial_days }}
                    day trial

                  </span>

                </div>


                <span
                  class="create-plan-check"
                  :class="{
                    visible:
                      String(
                        form.subscription_plan_id
                      ) ===
                      String(plan.id)
                  }"
                >

                  <i class="bi bi-check-lg"></i>

                </span>

              </div>


              <div class="create-plan-price">

                <strong>

                  {{ plan.currency || 'BDT' }}

                  {{ formatPrice(plan.price) }}

                </strong>

                <span>
                  /
                  {{ billingLabel(plan) }}
                </span>

              </div>


              <p
                v-if="plan.description"
                class="create-plan-description"
              >
                {{ plan.description }}
              </p>


              <div
                v-if="
                  Array.isArray(
                    plan.features
                  ) &&
                  plan.features.length
                "
                class="create-plan-features"
              >

                <div
                  v-for="(
                    feature,
                    index
                  ) in plan.features"
                  :key="index"
                  class="create-plan-feature"
                >

                  <i class="bi bi-check2"></i>

                  <span>
                    {{ feature }}
                  </span>

                </div>

              </div>

            </label>

          </div>


          <!-- Selected Plan -->

          <div
            v-if="selectedPlan"
            class="create-selected-plan"
          >

            <div class="create-selected-plan-icon">

              <i class="bi bi-check-circle-fill"></i>

            </div>


            <div class="create-selected-plan-copy">

              <span>
                Selected Plan
              </span>

              <strong>
                {{ selectedPlan.name }}
              </strong>

            </div>


            <div class="create-selected-plan-price">

              <strong>

                {{ selectedPlan.currency || 'BDT' }}

                {{ formatPrice(selectedPlan.price) }}

              </strong>

              <span>
                /
                {{ billingLabel(selectedPlan) }}
              </span>

            </div>

          </div>

        </div>

      </section>


      <!-- =====================================================
           ACTIONS
      ====================================================== -->

      <footer class="create-form-actions">

        <div class="create-form-actions-note">

          <i class="bi bi-info-circle"></i>

          <span>
            Required fields are marked with an asterisk.
          </span>

        </div>


        <div class="create-form-actions-buttons">

          <button
            type="button"
            class="btn btn-outline-secondary"
            :disabled="submitting"
            @click="
              router.push({
                name: 'super-admin-restaurants',
              })
            "
          >
            Cancel
          </button>


          <button
            type="submit"
            class="btn btn-primary create-submit-button"
            :disabled="
              submitting ||
              loadingPlans ||
              !plans.length ||
              !form.subscription_plan_id
            "
          >

            <span
              v-if="submitting"
            >

              <span
                class="spinner-border spinner-border-sm"
              ></span>

              Creating...

            </span>


            <span
              v-else
            >

              <i class="bi bi-plus-circle"></i>

              Create Restaurant

            </span>

          </button>

        </div>

      </footer>

    </form>

  </section>

</template>


<style
  src="@/styles/super-admin/create-restaurant.css"
></style>
