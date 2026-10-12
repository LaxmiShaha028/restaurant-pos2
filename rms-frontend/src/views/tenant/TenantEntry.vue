<template>

  <section
    class="tenant-entry-page min-vh-100 d-flex align-items-center justify-content-center bg-light px-3"
  >

    <div
      class="tenant-entry-card card border-0 shadow-sm"
      style="width: 100%; max-width: 520px;"
    >

      <div class="card-body p-4 p-md-5">


        <!-- =====================================================
             LOADING
        ====================================================== -->

        <div
          v-if="loading"
          class="text-center py-4"
        >

          <div
            class="spinner-border text-primary mb-3"
            role="status"
            aria-hidden="true"
          ></div>

          <h4 class="mb-2">
            Loading Restaurant...
          </h4>

          <p class="text-muted mb-0">
            Resolving restaurant information.
          </p>

        </div>


        <!-- =====================================================
             SUCCESS
        ====================================================== -->

        <div
          v-else-if="restaurant"
        >

          <!-- Logo / Icon -->

          <div
            class="text-center mb-4"
          >

            <div
              class="tenant-entry-logo d-inline-flex align-items-center justify-content-center rounded-circle bg-primary text-white"
              style="width: 72px; height: 72px;"
            >

              <i
                class="bi bi-shop fs-2"
              ></i>

            </div>

          </div>


          <!-- Restaurant -->

          <div
            class="text-center"
          >

            <h2 class="mb-2">
              {{ restaurant.name }}
            </h2>

            <p class="text-muted mb-4">
              Restaurant Management System
            </p>

          </div>


          <!-- Status -->

          <div
            class="alert alert-success d-flex align-items-center gap-2"
          >

            <i
              class="bi bi-check-circle-fill"
            ></i>

            <div>
              Restaurant is active and ready to use.
            </div>

          </div>


          <!-- Tenant Information -->

          <div
            class="border rounded p-3 mb-4"
          >

            <div
              class="small text-muted mb-1"
            >
              Tenant

            </div>

            <div
              class="fw-semibold text-break"
            >
              {{ restaurant.tenant_key }}
            </div>

          </div>


          <!-- Login Button -->

          <button
            type="button"
            class="tenant-entry-login-button btn btn-primary btn-lg w-100"
            :disabled="redirecting"
            @click="continueToLogin"
          >

            <span
              v-if="redirecting"
            >

              <span
                class="spinner-border spinner-border-sm me-2"
                role="status"
                aria-hidden="true"
              ></span>

              Opening Login...

            </span>


            <span
              v-else
            >

              <i
                class="bi bi-box-arrow-in-right me-2"
              ></i>

              Continue to Login

            </span>

          </button>


        </div>


        <!-- =====================================================
             ERROR
        ====================================================== -->

        <div
          v-else
          class="text-center"
        >

          <div
            class="d-inline-flex align-items-center justify-content-center rounded-circle bg-danger-subtle text-danger mb-3"
            style="width: 72px; height: 72px;"
          >

            <i
              class="bi bi-exclamation-triangle fs-2"
            ></i>

          </div>


          <h4 class="mb-2">
            Restaurant Unavailable
          </h4>


          <p
            class="text-muted mb-4"
          >
            {{ errorMessage }}
          </p>


          <button
            type="button"
            class="btn btn-outline-primary"
            @click="resolveCurrentTenant"
          >

            <i
              class="bi bi-arrow-clockwise me-1"
            ></i>

            Try Again

          </button>

        </div>


      </div>

    </div>

  </section>

</template>


<script setup>

import {
  onMounted,
  ref,
} from 'vue'


import {
  useAuthStore,
} from '@/stores/auth'



import {
  useRoute,
  useRouter,
} from 'vue-router'


import {
  resolveTenant,
} from '@/services/tenantService'


/*
|--------------------------------------------------------------------------
| Router
|--------------------------------------------------------------------------
*/

const route =
  useRoute()

const router =
  useRouter()


const authStore =
  useAuthStore()


/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const loading =
  ref(true)

const redirecting =
  ref(false)

const restaurant =
  ref(null)

const errorMessage =
  ref('')


/*
|--------------------------------------------------------------------------
| Resolve Current Tenant
|--------------------------------------------------------------------------
*/

async function resolveCurrentTenant() {

  loading.value =
    true

  errorMessage.value =
    ''

  restaurant.value =
    null


  try {

    const tenantKey =
      String(
        route.params.tenantKey
        ?? ''
      ).trim()


    if (
      !tenantKey
    ) {

      throw new Error(
        'Tenant key was not provided.'
      )

    }


    const response =
      await resolveTenant(
        tenantKey
      )


    if (
      !response?.success
    ) {

      throw new Error(
        response?.message
        ||
        'Restaurant could not be resolved.'
      )

    }


    const tenantData =
      response?.data


    if (
      !tenantData?.restaurant
    ) {

      throw new Error(
        'Restaurant information was not returned.'
      )

    }


    /*
    |--------------------------------------------------------------------------
    | Store Tenant Context
    |--------------------------------------------------------------------------
    |
    | We intentionally use sessionStorage instead of localStorage.
    | This context belongs only to the current browser tab/session.
    |
    */

    sessionStorage.setItem(
      'rms_tenant_context',
      JSON.stringify({
        tenant_key:
          tenantData.restaurant.tenant_key,

        restaurant_id:
          tenantData.restaurant.id,

        restaurant_name:
          tenantData.restaurant.name,

        slug:
          tenantData.restaurant.slug,

        status:
          tenantData.restaurant.status,

        logo_path:
          tenantData.restaurant.logo_path
          ?? null,

        domain:
          tenantData.domain?.domain
          ?? null,
      })
    )
/*
|--------------------------------------------------------------------------
| Store Tenant Context In Auth Store
|--------------------------------------------------------------------------
*/

authStore.storeTenantContext({
  tenant_key:
    tenantData.restaurant.tenant_key,

  restaurant_id:
    tenantData.restaurant.id,

  restaurant_name:
    tenantData.restaurant.name,

  slug:
    tenantData.restaurant.slug,

  status:
    tenantData.restaurant.status,

  logo_path:
    tenantData.restaurant.logo_path
    ?? null,

  domain:
    tenantData.domain?.domain
    ?? null,
})



    /*
    |--------------------------------------------------------------------------
    | Store Resolved Restaurant
    |--------------------------------------------------------------------------
    */

    restaurant.value =
      tenantData.restaurant

  } catch (error) {

    errorMessage.value =
      error?.message
      ||
      'Unable to load restaurant.'

  } finally {

    loading.value =
      false

  }

}


/*
|--------------------------------------------------------------------------
| Continue To Login
|--------------------------------------------------------------------------
*/

async function continueToLogin() {

  if (
    !restaurant.value?.tenant_key
  ) {
    return
  }


  redirecting.value =
    true


  try {

    await router.push({

      name:
        'login',

      query: {

        tenant_key:
          restaurant.value.tenant_key,

      },

    })

  } catch (error) {

    console.error(
      'Tenant login navigation failed:',
      error
    )

    redirecting.value =
      false

  }

}


/*
|--------------------------------------------------------------------------
| Initial Load
|--------------------------------------------------------------------------
*/

onMounted(
  resolveCurrentTenant
)

</script>

<style scoped>
.tenant-entry-page {
  background:
    radial-gradient(circle at 0 0, rgba(249, 115, 22, .055), transparent 28%),
    radial-gradient(circle at 100% 8%, rgba(251, 146, 60, .045), transparent 25%),
    #f4f1ed !important;
}

.tenant-entry-card {
  border: 1px solid #eee3da !important;
  border-radius: 16px !important;
  background: #fff !important;
  box-shadow: 0 12px 32px rgba(15, 23, 42, .08) !important;
}

.tenant-entry-page :deep(h2),
.tenant-entry-page :deep(.fw-semibold) {
  color: #111827;
}

.tenant-entry-page :deep(.text-muted) {
  color: #667085 !important;
}

.tenant-entry-page .tenant-entry-logo {
  background: #f97316 !important;
  color: #fff !important;
  box-shadow: 0 8px 18px rgba(249, 115, 22, .2);
}

.tenant-entry-page :deep(.spinner-border.text-primary) {
  color: #f97316 !important;
}

.tenant-entry-page .tenant-entry-login-button,
.tenant-entry-page .tenant-entry-login-button:hover:not(:disabled) {
  border: 1px solid #292929 !important;
  border-radius: 10px !important;
  background: linear-gradient(145deg, #191919 0%, #111 54%, #090909) !important;
  color: #f97316 !important;
  box-shadow: none !important;
}

.tenant-entry-page .tenant-entry-login-button i,
.tenant-entry-page .tenant-entry-login-button .spinner-border {
  color: #f97316 !important;
}

.tenant-entry-page :deep(.border.rounded) {
  border-color: #eee3da !important;
  border-radius: 10px !important;
  background: #fff8f1;
}

.tenant-entry-page :deep(.alert-success) {
  border-color: #fed7aa;
  background: #fff8f1;
  color: #111827;
}

.tenant-entry-page :deep(.alert-success i) {
  color: #f97316;
}

.tenant-entry-page :deep(.bg-danger-subtle) {
  background: #fff1e6 !important;
}

.tenant-entry-page :deep(.text-danger) {
  color: #f97316 !important;
}

.tenant-entry-page :deep(.btn-outline-primary) {
  border-color: #fed7aa;
  color: #c2410c;
}

.tenant-entry-page :deep(.btn-outline-primary:hover) {
  border-color: #fdba74;
  background: #fff1e6;
  color: #9a3412;
}
</style>
