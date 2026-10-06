<script setup>
import {
  ref,
  onMounted,
} from 'vue'

import {
  useRoute,
  useRouter,
} from 'vue-router'

import {
  useSuperAdminStore,
} from '@/stores/superAdmin'


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

const username =
  ref('')

const password =
  ref('')

const loading =
  ref(false)

const showPassword =
  ref(false)

const errorMessage =
  ref('')


/*
|--------------------------------------------------------------------------
| Login
|--------------------------------------------------------------------------
*/

async function handleLogin() {

  errorMessage.value =
    ''


  /*
  |--------------------------------------------------------------------------
  | Basic Validation
  |--------------------------------------------------------------------------
  */

  if (
    !username.value.trim()
  ) {

    errorMessage.value =
      'Username is required.'

    return
  }


  if (
    !password.value
  ) {

    errorMessage.value =
      'Password is required.'

    return
  }


  loading.value =
    true


  try {

    /*
    |--------------------------------------------------------------------------
    | Super Admin Login
    |--------------------------------------------------------------------------
    */

    const result =
      await superAdminStore.login({

        login:
          username.value.trim(),

        password:
          password.value,

      })


    /*
    |--------------------------------------------------------------------------
    | Stop If Login Failed
    |--------------------------------------------------------------------------
    */

    if (
      !result?.success
    ) {

      errorMessage.value =
        result?.message ||
        'Login failed.'

      return
    }


    /*
    |--------------------------------------------------------------------------
    | Redirect
    |--------------------------------------------------------------------------
    */

    const redirect =
      String(
        route.query?.redirect ||
        '',
      ).trim()


    if (
      redirect &&
      redirect.startsWith('/')
    ) {

      await router.push(
        redirect,
      )

      return
    }


    /*
    |--------------------------------------------------------------------------
    | Default Super Admin Dashboard
    |--------------------------------------------------------------------------
    */

    await router.push({
      name:
        'super-admin-dashboard',
    })

  } catch (
    error
  ) {

    console.error(
      'Super Admin login error:',
      error,
    )


    errorMessage.value =
      error?.message ||
      'Login failed.'

  } finally {

    loading.value =
      false
  }
}


/*
|--------------------------------------------------------------------------
| Mounted
|--------------------------------------------------------------------------
*/

onMounted(
  async () => {

    /*
    |--------------------------------------------------------------------------
    | Already Logged In
    |--------------------------------------------------------------------------
    */

    if (
      superAdminStore.isAuthenticated
    ) {

      await router.replace({
        name:
          'super-admin-dashboard',
      })

    }

  },
)
</script>


<template>

  <div
    class="super-admin-login-page"
  >

    <div
      class="super-admin-login-card"
    >

      <!-- =========================================================
           HEADER
      ========================================================== -->

      <div
        class="login-header"
      >

        <div
          class="admin-logo-wrapper"
        >

          <div
            class="admin-logo"
          >

            <i
              class="bi bi-shield-lock"
            ></i>

          </div>

        </div>


        <h1>
          Super Admin
        </h1>


        <p>
          Restaurant Management System
        </p>


        <span
          class="admin-badge"
        >

          <i
            class="bi bi-shield-check me-1"
          ></i>

          Central Administration

        </span>

      </div>


      <!-- =========================================================
           ERROR
      ========================================================== -->

      <div
        v-if="errorMessage"
        class="alert alert-danger"
      >

        <i
          class="bi bi-exclamation-circle me-1"
        ></i>

        {{ errorMessage }}

      </div>


      <!-- =========================================================
           LOGIN FORM
      ========================================================== -->

      <form
        @submit.prevent="handleLogin"
      >

        <!-- Username -->

        <div
          class="mb-3"
        >

          <label
            for="super-admin-login"
            class="form-label"
          >
            Username
          </label>


          <div
            class="input-group"
          >

            <span
              class="input-group-text"
            >

              <i
                class="bi bi-person"
              ></i>

            </span>


            <input
              id="super-admin-login"
              v-model="username"
              type="text"
              class="form-control"
              placeholder="Enter super admin username"
              autocomplete="username"
              :disabled="loading"
            />

          </div>

        </div>


        <!-- Password -->

        <div
          class="mb-3"
        >

          <label
            for="super-admin-password"
            class="form-label"
          >
            Password
          </label>


          <div
            class="input-group"
          >

            <span
              class="input-group-text"
            >

              <i
                class="bi bi-lock"
              ></i>

            </span>


            <input
              id="super-admin-password"
              v-model="password"
              :type="
                showPassword
                  ? 'text'
                  : 'password'
              "
              class="form-control"
              placeholder="Enter password"
              autocomplete="current-password"
              :disabled="loading"
            />


            <button
              type="button"
              class="btn btn-outline-secondary"
              :disabled="loading"
              @click="
                showPassword =
                  !showPassword
              "
            >

              <i
                :class="
                  showPassword
                    ? 'bi bi-eye-slash'
                    : 'bi bi-eye'
                "
              ></i>

            </button>

          </div>

        </div>


        <!-- Login Button -->

        <button
          type="submit"
          class="btn btn-primary w-100 login-button"
          :disabled="loading"
        >

          <span
            v-if="loading"
          >

            <span
              class="spinner-border spinner-border-sm me-1"
              role="status"
            ></span>

            Signing in...

          </span>


          <span
            v-else
          >

            <i
              class="bi bi-box-arrow-in-right me-1"
            ></i>

            Sign In

          </span>

        </button>

      </form>


      <!-- =========================================================
           INFORMATION
      ========================================================== -->

      <div
        class="login-footer"
      >

        <div
          class="footer-line"
        ></div>


        <small>
          <i
            class="bi bi-info-circle me-1"
          ></i>

          This portal is for Super Administrators only.

        </small>

      </div>

    </div>

  </div>

</template>


<style scoped>

.super-admin-login-page {

  min-height: 100vh;

  display: flex;

  align-items: center;

  justify-content: center;

  padding: 24px;

  background:
    linear-gradient(
      135deg,
      #f5f7fb 0%,
      #eef2f7 100%
    );

}


.super-admin-login-card {

  width: 100%;

  max-width: 430px;

  background: #ffffff;

  border-radius: 18px;

  padding: 34px;

  box-shadow:
    0 18px 50px
    rgba(
      15,
      23,
      42,
      0.10
    );

}


.login-header {

  text-align: center;

  margin-bottom: 28px;

}


.admin-logo-wrapper {

  display: flex;

  justify-content: center;

  margin-bottom: 16px;

}


.admin-logo {

  width: 72px;

  height: 72px;

  display: flex;

  align-items: center;

  justify-content: center;

  border-radius: 18px;

  background: #212529;

  color: #ffffff;

  font-size: 30px;

}


.login-header h1 {

  margin: 0;

  font-size: 26px;

  font-weight: 700;

  color: #212529;

}


.login-header p {

  margin:
    7px
    0
    12px;

  color: #6c757d;

  font-size: 14px;

}


.admin-badge {

  display: inline-flex;

  align-items: center;

  padding:
    6px
    10px;

  border-radius: 20px;

  background: #f1f3f5;

  color: #495057;

  font-size: 12px;

  font-weight: 600;

}


.form-label {

  font-weight: 600;

  font-size: 14px;

  color: #343a40;

}


.input-group-text {

  background: #f8f9fa;

  color: #6c757d;

}


.form-control {

  height: 46px;

}


.login-button {

  height: 46px;

  margin-top: 8px;

  font-weight: 600;

  border-radius: 10px;

}


.login-footer {

  text-align: center;

  margin-top: 25px;

}


.footer-line {

  height: 1px;

  background: #e9ecef;

  margin-bottom: 15px;

}


.login-footer small {

  color: #868e96;

  font-size: 12px;

}


@media (
  max-width: 480px
) {

  .super-admin-login-card {

    padding:
      25px
      20px;

  }

}

</style>