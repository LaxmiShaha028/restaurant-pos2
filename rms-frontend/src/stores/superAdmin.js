import { defineStore } from 'pinia'
import api from '@/services/api'

const TOKEN_KEY =
  'rms_central_access_token'

const USER_KEY =
  'rms_central_user'

const AUTH_TYPE_KEY =
  'rms_auth_type'

function getStoredUser() {
  const storedUser =
    localStorage.getItem(USER_KEY)

  if (!storedUser) {
    return null
  }

  try {
    return JSON.parse(storedUser)
  } catch {
    localStorage.removeItem(USER_KEY)
    return null
  }
}

export const useSuperAdminStore =
  defineStore('superAdmin', {

    state: () => ({
      token:
        localStorage.getItem(
          TOKEN_KEY,
        ),

      user:
        getStoredUser(),

      loading:
        false,

      initialized:
        false,

      error:
        null,
    }),

    getters: {

      isAuthenticated: (
        state,
      ) =>
        Boolean(
          state.token &&
          state.user,
        ),
    },

    actions: {

      storeSession(
        token,
        user,
      ) {

        this.token =
          token

        this.user =
          user

        localStorage.setItem(
          TOKEN_KEY,
          token,
        )

        localStorage.setItem(
          USER_KEY,
          JSON.stringify(
            user,
          ),
        )

        localStorage.setItem(
          AUTH_TYPE_KEY,
          'central',
        )
      },

      clearSession() {

        this.token =
          null

        this.user =
          null

        localStorage.removeItem(
          TOKEN_KEY,
        )

        localStorage.removeItem(
          USER_KEY,
        )

        if (
          localStorage.getItem(
            AUTH_TYPE_KEY,
          ) === 'central'
        ) {
          localStorage.removeItem(
            AUTH_TYPE_KEY,
          )
        }
      },

      async login(
  credentials,
) {

  this.loading =
    true

  this.error =
    null

  try {

    const response =
      await api.post(
        '/central/auth/login',
        {
          login:
            credentials.login,

          password:
            credentials.password,
        },
      )

    /*
    |--------------------------------------------------------------------------
    | Backend Response
    |--------------------------------------------------------------------------
    |
    | {
    |   success: true,
    |   data: {
    |     token: "...",
    |     token_type: "Bearer",
    |     super_admin: {...}
    |   }
    | }
    |
    */

    const token =
      response.data?.data?.token

    const user =
      response.data?.data?.super_admin

    if (
      !token ||
      !user
    ) {

      throw new Error(
        'Super Admin login response did not contain a valid authentication session.',
      )
    }

    this.storeSession(
      token,
      user,
    )

    return {
      success:
        true,

      user:
        user,
    }

  } catch (
    error
  ) {

    this.clearSession()

    this.error =
      error.response?.data?.message ||
      error.message ||
      'Could not connect to the server.'

    return {
      success:
        false,

      message:
        this.error,

      errors:
        error.response?.data?.errors
        ?? null,
    }

  } finally {

    this.loading =
      false
  }
},

      async fetchUser() {

        if (
          !this.token
        ) {

          this.initialized =
            true

          return false
        }

        try {

          const response =
            await api.get(
              '/central/auth/me',
            )

          const user =
            response.data?.user

          if (
            !user
          ) {
            throw new Error(
              'Super Admin user could not be resolved.',
            )
          }

          this.user =
            user

          localStorage.setItem(
            USER_KEY,
            JSON.stringify(
              user,
            ),
          )

          return true

        } catch {

          this.clearSession()

          return false

        } finally {

          this.initialized =
            true
        }
      },

      async logout() {

        this.loading =
          true

        try {

          if (
            this.token
          ) {
            await api.post(
              '/central/auth/logout',
            )
          }

        } catch (
          error
        ) {

          console.error(
            'Super Admin logout request failed:',
            error,
          )

        } finally {

          this.clearSession()

          this.loading =
            false
        }
      },

      async initializeAuth() {

        if (
          this.initialized
        ) {
          return
        }

        if (
          !this.token
        ) {

          this.clearSession()

          this.initialized =
            true

          return
        }

        await this.fetchUser()
      },
    },
  })