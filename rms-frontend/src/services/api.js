import axios from 'axios'


/*
|--------------------------------------------------------------------------
| API Base URL
|--------------------------------------------------------------------------
|
| Tenant frontend:
|   fifth-restaurant.local:5173
|   -> fifth-restaurant.local:8000/api
|
| Super Admin frontend:
|   localhost:5173
|   -> localhost:8000/api
|
| VITE_API_BASE_URL can still override this when needed.
|
*/

const defaultBaseURL =
  import.meta.env.VITE_API_ORIGIN
    ? `${import.meta.env.VITE_API_ORIGIN.replace(/\/+$/, '')}/api`
    : `${window.location.protocol}//${window.location.hostname}:8000/api`


const api = axios.create({

  baseURL:
    import.meta.env.VITE_API_BASE_URL ||
    defaultBaseURL,

  timeout: 60000,

  headers: {

    Accept: 'application/json',

  },

})


/*
|--------------------------------------------------------------------------
| Token Helpers
|--------------------------------------------------------------------------
*/


function getTenantToken() {

  return localStorage.getItem(
    'rms_tenant_access_token',
  )

}



function getCentralToken() {

  return localStorage.getItem(
    'rms_central_access_token',
  )

}



/*
|--------------------------------------------------------------------------
| Request Interceptor
|--------------------------------------------------------------------------
*/

api.interceptors.request.use(

  (config) => {


    const url =
      config.url || ''



    /*
    |--------------------------------------------------------------------------
    | Central Request Detection
    |--------------------------------------------------------------------------
    */

    const isCentralRequest =

      url === '/central' ||
      url.startsWith('/central/')




    /*
    |--------------------------------------------------------------------------
    | Tenant Request Detection
    |--------------------------------------------------------------------------
    */

const isTenantRequest =
  !isCentralRequest




    /*
    |--------------------------------------------------------------------------
    | Token Selection
    |--------------------------------------------------------------------------
    */

    const token =

      isCentralRequest

        ? getCentralToken()

        : getTenantToken()




    if (token) {


      config.headers =
        config.headers || {}


      config.headers.Authorization =
        `Bearer ${token}`

    }




    /*
    |--------------------------------------------------------------------------
    | Tenant Context Header
    |--------------------------------------------------------------------------
    |
    | Sends tenant identity automatically
    | for all tenant API requests.
    |
    */

    if (isTenantRequest) {


      try {


        const storedContext =

          sessionStorage.getItem(
            'rms_tenant_context'
          )



        if (storedContext) {


          const context =

            JSON.parse(
              storedContext
            )



          const tenantKey =

            String(

              context?.tenant_key ||

              ''

            ).trim()




          if (tenantKey) {


            config.headers =
              config.headers || {}



            config.headers[
              'X-Tenant-Key'
            ] =

              tenantKey


          }


        }


      }

      catch(error) {


        console.warn(

          'Unable to read tenant context:',

          error

        )


      }


    }




    return config


  },


  (error) =>

    Promise.reject(error)

)





/*
|--------------------------------------------------------------------------
| Response Interceptor
|--------------------------------------------------------------------------
*/

api.interceptors.response.use(


  (response) =>

    response,



  (error) => {


    const status =

      error.response?.status



    const url =

      error.config?.url || ''




    const isCentralRequest =

      url === '/central' ||

      url.startsWith('/central/')





    /*
    |--------------------------------------------------------------------------
    | Central Authentication Failure
    |--------------------------------------------------------------------------
    */

    if (

      status === 401 &&

      isCentralRequest

    ) {



      localStorage.removeItem(

        'rms_central_access_token',

      )



      localStorage.removeItem(

        'rms_central_user',

      )




      if (

        window.location.pathname !==

        '/super-admin/login'

      ) {


        window.location.href =

          '/super-admin/login'


      }




      return Promise.reject(error)


    }





    /*
    |--------------------------------------------------------------------------
    | Tenant Authentication Failure
    |--------------------------------------------------------------------------
    */

    if (

      status === 401 &&

      !isCentralRequest

    ) {



      localStorage.removeItem(

        'rms_tenant_access_token',

      )



      localStorage.removeItem(

        'rms_tenant_user',

      )



      localStorage.removeItem(

        'rms_access_token',

      )



      localStorage.removeItem(

        'rms_user',

      )





      if (

        window.location.pathname !== '/login' &&

        window.location.pathname !== '/'

      ) {


        window.location.href =

          '/login'


      }


    }





    return Promise.reject(error)


  }

)




export default api
