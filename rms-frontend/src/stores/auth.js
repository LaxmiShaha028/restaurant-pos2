import { defineStore } from 'pinia'
import api from '@/services/api'

/*
|--------------------------------------------------------------------------
| Tenant Authentication Storage Keys
|--------------------------------------------------------------------------
*/

const TOKEN_KEY =
  'rms_tenant_access_token'

const USER_KEY =
  'rms_tenant_user'

const AUTH_TYPE_KEY =
  'rms_auth_type'

const TENANT_CONTEXT_KEY =
  'rms_tenant_context'


/*
|--------------------------------------------------------------------------
| Stored User
|--------------------------------------------------------------------------
*/

function getStoredUser() {
  const storedUser =
    localStorage.getItem(
      USER_KEY,
    )

  if (!storedUser) {
    return null
  }

  try {
    return JSON.parse(
      storedUser,
    )
  } catch {
    localStorage.removeItem(
      USER_KEY,
    )

    return null
  }
}


/*
|--------------------------------------------------------------------------
| Stored Tenant Context
|--------------------------------------------------------------------------
*/

function getStoredTenantContext() {
  const storedContext =
    sessionStorage.getItem(
      TENANT_CONTEXT_KEY,
    )

  if (!storedContext) {
    return null
  }

  try {
    return JSON.parse(
      storedContext,
    )
  } catch {
    sessionStorage.removeItem(
      TENANT_CONTEXT_KEY,
    )

    return null
  }
}


/*
|--------------------------------------------------------------------------
| Tenant Authentication Store
|--------------------------------------------------------------------------
*/

export const useAuthStore =
  defineStore('auth', {

    /*
    |--------------------------------------------------------------------------
    | State
    |--------------------------------------------------------------------------
    */

    state: () => ({
      token:
        localStorage.getItem(
          TOKEN_KEY,
        ),

      user:
        getStoredUser(),

      tenant:
        getStoredTenantContext(),

      loading:
        false,

      initialized:
        false,

      error:
        null,
    }),


    /*
    |--------------------------------------------------------------------------
    | Getters
    |--------------------------------------------------------------------------
    */

    getters: {

      isAuthenticated: (
        state,
      ) =>
        Boolean(
          state.token &&
          state.user,
        ),


      roleName: (
        state,
      ) =>
        state.user?.role?.name ??
        null,


      permissions: (
  state,
) => {

  const permissions =
    state.user?.permissions

  if (
    !Array.isArray(
      permissions,
    )
  ) {

    return []

  }

  return permissions.map(
    (permission) => {

      if (
        typeof permission ===
        'string'
      ) {

        return permission

      }

      return permission?.name ||
              permission?.slug ||
              null

          }
        ).filter(Boolean)

      },


      tenantKey: (
        state,
      ) =>
        state.tenant?.tenant_key ??
        null,


      restaurantName: (
        state,
      ) =>
        state.tenant?.restaurant_name ??
        null,


      restaurantId: (
        state,
      ) =>
        state.tenant?.restaurant_id ??
        null,


      isAdmin() {
        return this.roleName ===
          'admin'
      },


      isManager() {
        return this.roleName ===
          'manager'
      },


      isChef() {
        return this.roleName ===
          'chef'
      },


      isWaiter() {
        return this.roleName ===
          'waiter'
      },
    },


    /*
    |--------------------------------------------------------------------------
    | Actions
    |--------------------------------------------------------------------------
    */

    actions: {

      /*
      |--------------------------------------------------------------------------
      | Permission
      |--------------------------------------------------------------------------
      */

      hasPermission(
        permission,
      ) {
        return this.permissions.includes(
          permission,
        )
      },


      /*
      |--------------------------------------------------------------------------
      | Any Permission
      |--------------------------------------------------------------------------
      */

      hasAnyPermission(
        requiredPermissions = [],
      ) {

        if (
          !requiredPermissions.length
        ) {
          return true
        }

        return requiredPermissions.some(
          (
            permission,
          ) =>
            this.hasPermission(
              permission,
            ),
        )
      },


      /*
      |--------------------------------------------------------------------------
      | Get Tenant Context
      |--------------------------------------------------------------------------
      */

      getTenantContext() {

        if (
          this.tenant?.tenant_key
        ) {
          return this.tenant
        }

        const context =
          getStoredTenantContext()

        if (context) {
          this.tenant =
            context

          return context
        }

        return null
      },


      /*
      |--------------------------------------------------------------------------
      | Store Tenant Context
      |--------------------------------------------------------------------------
      */

      storeTenantContext(
        context,
      ) {

        if (
          !context ||
          typeof context !==
            'object'
        ) {
          return
        }

        const normalizedContext = {
          tenant_key:
            context.tenant_key
              ? String(
                  context.tenant_key,
                ).trim()
              : null,

          restaurant_id:
            context.restaurant_id
              ?? null,

          restaurant_name:
            context.restaurant_name
              ?? null,

          slug:
            context.slug
              ?? null,

          status:
            context.status
              ?? null,

          logo_path:
            context.logo_path
              ?? null,

          domain:
            context.domain
              ?? null,
        }

        this.tenant =
          normalizedContext

        sessionStorage.setItem(
          TENANT_CONTEXT_KEY,
          JSON.stringify(
            normalizedContext,
          ),
        )
      },


      /*
      |--------------------------------------------------------------------------
      | Clear Tenant Context
      |--------------------------------------------------------------------------
      */

      clearTenantContext() {

        this.tenant =
          null

        sessionStorage.removeItem(
          TENANT_CONTEXT_KEY,
        )
      },


      /*
      |--------------------------------------------------------------------------
      | Store Tenant Session
      |--------------------------------------------------------------------------
      */

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
          'tenant',
        )


        /*
        |--------------------------------------------------------------------------
        | Remove Legacy Authentication
        |--------------------------------------------------------------------------
        */

        localStorage.removeItem(
          'rms_access_token',
        )

        localStorage.removeItem(
          'rms_user',
        )
      },


      /*
      |--------------------------------------------------------------------------
      | Clear Tenant Session
      |--------------------------------------------------------------------------
      */

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


        /*
        |--------------------------------------------------------------------------
        | Only Clear Auth Type When
        | Current Session Is Tenant
        |--------------------------------------------------------------------------
        */

        if (
          localStorage.getItem(
            AUTH_TYPE_KEY,
          ) === 'tenant'
        ) {

          localStorage.removeItem(
            AUTH_TYPE_KEY,
          )
        }
      },


      /*
      |--------------------------------------------------------------------------
      | Login
      |--------------------------------------------------------------------------
      */

      async login(
        credentials,
      ) {

        this.loading =
          true

        this.error =
          null


        try {

          /*
          |--------------------------------------------------------------------------
          | Resolve Tenant Context
          |--------------------------------------------------------------------------
          */

          const tenantContext =
  this.getTenantContext()


const credentialTenantKey =
  String(
    credentials?.tenant_key ||
    ''
  ).trim()


const contextTenantKey =
  String(
    tenantContext?.tenant_key ||
    ''
  ).trim()


const tenantKey =
  credentialTenantKey ||
  contextTenantKey


          if (
            !tenantKey
          ) {

            throw new Error(
              'Restaurant information is missing. Please open the restaurant login link again.',
            )
          }


          const login =
            String(
              credentials?.login ??
              '',
            ).trim()


          const password =
            credentials?.password ??
            ''


          /*
          |--------------------------------------------------------------------------
          | Tenant Login Request
          |--------------------------------------------------------------------------
          |
          | localhost development mode uses tenant_key because the browser
          | is no longer using the generated *.local domain.
          |
          */

          const response =
            await api.post(
              '/tenant/login',
              {
                tenant_key:
                  tenantKey,

                login:
                  login,

                password:
                  password,
              },
            )


          /*
          |--------------------------------------------------------------------------
          | TenantAuthController Response
          |--------------------------------------------------------------------------
          |
          | {
          |     message: "...",
          |     token: "...",
          |     token_type: "Bearer",
          |     user: {...}
          | }
          |
          */

          const token =
  response.data?.token

const rawUser =
  response.data?.user

const responseRole =
  response.data?.role

const responsePermissions =
  response.data?.permissions


if (
  !token ||
  !rawUser
) {

  throw new Error(
    'Login response did not contain a valid tenant authentication session.',
  )
}


/*
|--------------------------------------------------------------------------
| Build Complete Tenant User
|--------------------------------------------------------------------------
|
| Backend returns:
|
| user
| role
| permissions
|
| Sidebar/Auth store expects:
|
| user.role
| user.permissions
|
*/

const user = {

  ...rawUser,

  role:
    responseRole ||
    rawUser?.role ||
    null,

  permissions:
    Array.isArray(
      responsePermissions
    )
      ? responsePermissions
      : (
          Array.isArray(
            rawUser?.permissions
          )
            ? rawUser.permissions
            : []
        ),

}


          /*
          |--------------------------------------------------------------------------
          | Store Session
          |--------------------------------------------------------------------------
          */

          this.storeSession(
            token,
            user,
          )


          /*
          |--------------------------------------------------------------------------
          | Refresh Tenant Context
          |--------------------------------------------------------------------------
          |
          | Keep the context available after successful login.
          |
          */

          if (
            tenantContext
          ) {

            this.storeTenantContext(
              {
                ...tenantContext,

                tenant_key:
                  tenantKey,
              },
            )

          } else {

            this.storeTenantContext({
              tenant_key:
                tenantKey,

              restaurant_id:
                user?.restaurant_id
                  ?? null,

              restaurant_name:
                user?.restaurant?.name
                  ?? null,

              slug:
                user?.restaurant?.slug
                  ?? null,

              status:
                'active',

              logo_path:
                user?.restaurant?.logo_path
                  ?? null,

              domain:
                user?.restaurant?.domain
                  ?? null,
            })

          }


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
            error.response?.data?.message
            ||
            error.message
            ||
            'Could not connect to the server. Make sure the Laravel API is running.'


          return {
            success:
              false,

            message:
              this.error,

            errors:
              error.response?.data?.errors
              ??
              null,
          }

        } finally {

          this.loading =
            false
        }
      },


      /*
      |--------------------------------------------------------------------------
      | Fetch Current Tenant User
      |--------------------------------------------------------------------------
      */

      /*
|--------------------------------------------------------------------------
| Fetch Current Tenant User
|--------------------------------------------------------------------------
*/

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
        '/tenant/me',
      )


    /*
    |--------------------------------------------------------------------------
    | Backend Response
    |--------------------------------------------------------------------------
    |
    | Expected:
    |
    | {
    |   user: {...},
    |   role: {...},
    |   permissions: [...]
    | }
    |
    */

    const rawUser =
      response.data?.user

    const responseRole =
      response.data?.role

    const responsePermissions =
      response.data?.permissions


    if (
      !rawUser
    ) {

      throw new Error(
        'Current tenant user could not be resolved.',
      )
    }


    /*
    |--------------------------------------------------------------------------
    | Build Complete Tenant User
    |--------------------------------------------------------------------------
    |
    | Auth store expects:
    |
    | user.role
    | user.permissions
    |
    */

    const user = {

      ...rawUser,

      role:
        responseRole ||
        rawUser?.role ||
        null,

      permissions:
        Array.isArray(
          responsePermissions
        )
          ? responsePermissions
          : (
              Array.isArray(
                rawUser?.permissions
              )
                ? rawUser.permissions
                : []
            ),

    }


    /*
    |--------------------------------------------------------------------------
    | Store Updated User
    |--------------------------------------------------------------------------
    */

    this.user =
      user


    localStorage.setItem(
      USER_KEY,
      JSON.stringify(
        user,
      ),
    )


    return true

  } catch (
    error
  ) {

    console.error(
      'Failed to fetch current tenant user:',
      error,
    )

    this.clearSession()

    return false

  } finally {

    this.initialized =
      true
  }
},


      /*
      |--------------------------------------------------------------------------
      | Logout
      |--------------------------------------------------------------------------
      */

      async logout() {

        this.loading =
          true


        try {

          if (
            this.token
          ) {

            await api.post(
              '/tenant/logout',
            )
          }

        } catch (
          error
        ) {

          console.error(
            'Tenant logout request failed:',
            error,
          )

        } finally {

          this.clearSession()

          /*
          |--------------------------------------------------------------------------
          | Keep Tenant Context
          |--------------------------------------------------------------------------
          |
          | Logout should normally keep the restaurant context so the user
          | can login again without losing the selected restaurant.
          |
          */

          this.loading =
            false
        }
      },


      /*
      |--------------------------------------------------------------------------
      | Initialize Authentication
      |--------------------------------------------------------------------------
      */

      async initializeAuth() {

        if (
          this.initialized
        ) {
          return
        }


        /*
        |--------------------------------------------------------------------------
        | Restore Tenant Context
        |--------------------------------------------------------------------------
        */

        this.tenant =
          getStoredTenantContext()


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


      /*
      |--------------------------------------------------------------------------
      | Default Route
      |--------------------------------------------------------------------------
      */

      getDefaultRoute() {

        return {
          name:
            'dashboard',
        }
      },
    },
  })
