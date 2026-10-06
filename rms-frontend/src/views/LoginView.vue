<template>
  <main class="login-page">
    <div class="login-wrapper">
      <!-- Brand / Header -->
      <div class="login-header text-center">
        <div class="brand-icon mx-auto">
          <i class="bi bi-shop"></i>
        </div>

        <h1 class="login-title">
          Restaurant Management System
        </h1>

        <p class="login-subtitle">
          Sign in to manage restaurant operations
        </p>
      </div>

      <!-- Login Card -->
      <div class="card login-card">
        <div class="card-body">
          <!-- Error Message -->
          <div
            v-if="errorMessage"
            class="alert alert-danger login-alert"
            role="alert"
          >
            <i class="bi bi-exclamation-circle"></i>
            <span>{{ errorMessage }}</span>
          </div>

          <form @submit.prevent="handleLogin">
            <!-- Login -->
            <div class="form-section">
              <label
                for="login"
                class="login-form-label"
              >
                Email or Username
              </label>

              <div class="input-group login-input-group">
                <span class="input-group-text">
                  <i class="bi bi-person"></i>
                </span>

                <input
                  id="login"
                  v-model.trim="form.login"
                  type="text"
                  class="form-control"
                  :class="{ 'is-invalid': fieldErrors.login }"
                  placeholder="Enter email or username"
                  autocomplete="username"
                  autofocus
                />
              </div>

              <div
                v-if="fieldErrors.login"
                class="field-error"
              >
                {{ fieldErrors.login }}
              </div>
            </div>

            <!-- Password -->
            <div class="form-section">
              <label
                for="password"
                class="login-form-label"
              >
                Password
              </label>

              <div
                class="input-group login-input-group password-group"
                :class="{ 'has-error': fieldErrors.password }"
              >
                <span class="input-group-text">
                  <i class="bi bi-lock"></i>
                </span>

                <input
                  id="password"
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  class="form-control"
                  :class="{ 'is-invalid': fieldErrors.password }"
                  placeholder="Enter password"
                  autocomplete="current-password"
                />

                <button
                  type="button"
                  class="btn password-toggle"
                  @click="showPassword = !showPassword"
                  :aria-label="
                    showPassword
                      ? 'Hide password'
                      : 'Show password'
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

              <div
                v-if="fieldErrors.password"
                class="field-error"
              >
                {{ fieldErrors.password }}
              </div>
            </div>

            <!-- Submit -->
            <button
              type="submit"
              class="btn btn-primary login-btn w-100"
              :disabled="authStore.loading"
            >
              <span
                v-if="authStore.loading"
                class="spinner-border spinner-border-sm"
                aria-hidden="true"
              ></span>

              <span>
                {{
                  authStore.loading
                    ? 'Signing in...'
                    : 'Sign In'
                }}
              </span>
            </button>
          </form>

          <!-- Development Accounts -->
          <div class="dev-accounts">
            <div class="dev-title">
              <i class="bi bi-code-slash"></i>
              <span>Development Accounts</span>
            </div>

            <div class="account-list">
              <div class="account-item">
                <span class="account-role">Admin</span>
                <span class="account-info">
                  admin / Admin@12345
                </span>
              </div>

              <div class="account-item">
                <span class="account-role">Manager</span>
                <span class="account-info">
                  manager / Manager@12345
                </span>
              </div>

              <div class="account-item">
                <span class="account-role">Chef</span>
                <span class="account-info">
                  chef / Chef@12345
                </span>
              </div>

              <div class="account-item">
                <span class="account-role">Waiter</span>
                <span class="account-info">
                  waiter12 / Waiter@12345
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <p class="login-footer">
        &copy; {{ currentYear }} Restaurant Management System
      </p>
    </div>
  </main>
</template>

<script setup>
import { computed, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const router = useRouter()
const route = useRoute()

const showPassword = ref(false)
const errorMessage = ref('')
const fieldErrors = ref({})

const form = reactive({
  login: '',
  password: '',
})

const currentYear = computed(() => new Date().getFullYear())

async function handleLogin() {

  errorMessage.value = ''

  fieldErrors.value = {}


  /*
  |--------------------------------------------------------------------------
  | Basic Validation
  |--------------------------------------------------------------------------
  */

  if (
    !form.login
  ) {

    fieldErrors.value.login =
      'Email or username is required.'

  }


  if (
    !form.password
  ) {

    fieldErrors.value.password =
      'Password is required.'

  }


  if (
    !form.login ||
    !form.password
  ) {

    return

  }


  /*
  |--------------------------------------------------------------------------
  | Resolve Tenant Key
  |--------------------------------------------------------------------------
  */

  const tenantKey =
    String(
      route.query?.tenant_key ||
      authStore.tenantKey ||
      ''
    ).trim()


  if (
    !tenantKey
  ) {

    errorMessage.value =
      'Restaurant information is missing. Please open the restaurant login link again.'

    return
  }


  /*
  |--------------------------------------------------------------------------
  | Login
  |--------------------------------------------------------------------------
  */

  const result =
    await authStore.login({

      tenant_key:
        tenantKey,

      login:
        form.login,

      password:
        form.password,

    })


  /*
  |--------------------------------------------------------------------------
  | Login Failed
  |--------------------------------------------------------------------------
  */

  if (
    !result?.success
  ) {

    errorMessage.value =
      result?.message ||
      'Login failed.'

    fieldErrors.value = {

      login:
        result?.errors?.login?.[0],

      password:
        result?.errors?.password?.[0],

    }

    return
  }


  /*
  |--------------------------------------------------------------------------
  | Redirect
  |--------------------------------------------------------------------------
  */

  const redirectPath =
    typeof route.query.redirect === 'string'
      ? route.query.redirect
      : null


  if (
    redirectPath &&
    redirectPath.startsWith('/')
  ) {

    await router.replace(
      redirectPath
    )

    return
  }


  await router.replace({
    name:
      'dashboard',
  })
}
</script>

<style scoped>
.login-page {
  width: 100%;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px 16px;
  background: #f5f6f8;
  color: #212529;
}

.login-wrapper {
  width: 100%;
  max-width: 390px;
}

/* =========================
   Header
========================= */

.login-header {
  margin-bottom: 20px;
}

.brand-icon {
  width: 52px;
  height: 52px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 14px;
  border-radius: 10px;
  background: #0d6efd;
  color: #ffffff;
  font-size: 23px;
  box-shadow: 0 4px 12px rgba(13, 110, 253, 0.18);
}

.login-title {
  margin: 0;
  font-size: 22px;
  line-height: 1.3;
  font-weight: 700;
  color: #212529;
}

.login-subtitle {
  margin: 6px 0 0;
  font-size: 13px;
  color: #6c757d;
}

/* =========================
   Card
========================= */

.login-card {
  border: 1px solid #e2e5e9;
  border-radius: 10px;
  background: #ffffff;
  box-shadow: 0 3px 12px rgba(0, 0, 0, 0.05);
  overflow: hidden;
}

.login-card .card-body {
  padding: 26px;
}

/* =========================
   Alert
========================= */

.login-alert {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  margin-bottom: 18px;
  padding: 10px 12px;
  border-radius: 6px;
  font-size: 13px;
  line-height: 1.4;
}

.login-alert i {
  margin-top: 1px;
  flex-shrink: 0;
}

/* =========================
   Form
========================= */

.form-section {
  margin: 16px;
}

.login-form-label {
  margin-top: 16px;
  margin-bottom: 16px;
  font-size: 14px;
  font-weight: 700;
  color: #343a40;
}

.login-input-group {
  flex-wrap: nowrap;
}

.login-input-group .input-group-text {
  width: 42px;
  height: 33px;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  border-color: #d9dde2;
  background: #f8f9fa;
  color: #6c757d;
  font-size: 15px;
}

.login-input-group .form-control {
  height: 42px;
  padding: 8px 12px;
  border-color: #d9dde2;
  font-size: 15px;
  color: #212529;
  box-shadow: none;
}

.login-input-group .form-control::placeholder {
  color: #adb5bd;
}

.login-input-group .form-control:focus {
  position: relative;
  z-index: 3;
  border-color: #86b7fe;
  box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.09);
}

.login-input-group .form-control.is-invalid {
  border-color: #dc3545;
  background-image: none;
  padding-right: 12px;
}

.login-input-group .form-control.is-invalid:focus {
  border-color: #dc3545;
  box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.08);
}

.field-error {
  margin-top: 5px;
  font-size: 12px;
  line-height: 1.35;
  color: #dc3545;
}

/* =========================
   Password
========================= */

.password-toggle {
  width: 42px;
  height: 29px;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid #d9dde2;
  border-left: 0;
  background: #ffffff;
  color: #6c757d;
  border-radius: 0 6px 6px 0;
  font-size: 15px;
}

.password-toggle:hover {
  background: #f8f9fa;
  color: #212529;
}

.password-toggle:focus {
  box-shadow: none;
}

.password-group .form-control {
  border-radius: 0;
}

.password-group.has-error .password-toggle {
  border-color: #dc3545;
}

/* =========================
   Login Button
========================= */

.login-btn {
  height: 42px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  margin-top: 45px;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 600;
}

.login-btn:disabled {
  cursor: not-allowed;
  opacity: 0.7;
}

/* =========================
   Development Accounts
========================= */

.dev-accounts {
  margin-top: 22px;
  padding-top: 18px;
  border-top: 1px solid #eceff2;
}

.dev-title {
  display: flex;
  align-items: center;
  gap: 7px;
  margin-bottom: 10px;
  font-size: 12px;
  font-weight: 600;
  color: #6c757d;
}

.dev-title i {
  font-size: 13px;
}

.account-list {
  overflow: hidden;
  border: 1px solid #e7e9ec;
  border-radius: 6px;
  background: #fafbfc;
}

.account-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 7px 9px;
  border-bottom: 1px solid #eceef0;
  font-size: 11.5px;
}

.account-item:last-child {
  border-bottom: 0;
}

.account-role {
  width: 58px;
  flex-shrink: 0;
  font-weight: 600;
  color: #495057;
}

.account-info {
  min-width: 0;
  color: #6c757d;
  word-break: break-word;
}

/* =========================
   Footer
========================= */

.login-footer {
  margin: 16px 0 0;
  text-align: center;
  font-size: 11.5px;
  color: #8a9198;
}

/* =========================
   Responsive
========================= */

@media (max-width: 575.98px) {
  .login-page {
    align-items: flex-start;
    padding: 35px 14px 20px;
  }

  .login-wrapper {
    max-width: 100%;
  }

  .login-card .card-body {
    padding: 22px 18px;
  }

  .login-title {
    font-size: 20px;
  }

  .brand-icon {
    width: 48px;
    height: 48px;
    font-size: 21px;
  }

  .account-item {
    align-items: flex-start;
  }
}

@media (max-height: 650px) and (min-width: 576px) {
  .login-page {
    align-items: flex-start;
    padding-top: 24px;
    padding-bottom: 24px;
  }

  .login-header {
    margin-bottom: 14px;
  }

  .brand-icon {
    width: 46px;
    height: 46px;
    margin-bottom: 10px;
    font-size: 20px;
  }

  .login-card .card-body {
    padding: 22px;
  }

  .dev-accounts {
    margin-top: 16px;
    padding-top: 14px;
  }
}
</style>