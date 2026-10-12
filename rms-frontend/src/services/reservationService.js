/*
|--------------------------------------------------------------------------
| Reservation API Service
|--------------------------------------------------------------------------
|
| Handles:
|
| - Reservation list
| - Single reservation
| - Edit options
| - Create reservation
| - Update reservation
| - Cancel reservation
| - Seat reservation
| - Available tables
| - Customer list
|
|--------------------------------------------------------------------------
*/

import api from '@/services/api'


/*
|--------------------------------------------------------------------------
| API Base URLs
|--------------------------------------------------------------------------
*/

const RESERVATION_BASE_URL =
  '/reservations'

const CUSTOMER_BASE_URL =
  '/customers'


/*
|--------------------------------------------------------------------------
| Request Helpers
|--------------------------------------------------------------------------
*/

/**
 * Remove empty values before sending query parameters.
 */
function cleanRequestParams(
  params = {}
) {
  return Object.fromEntries(
    Object.entries(
      params || {}
    ).filter(
      ([, value]) =>
        value !== undefined &&
        value !== null &&
        value !== ''
    )
  )
}


/**
 * Convert an ID into a valid positive integer.
 */
function resolvePositiveId(
  value,
  label = 'ID'
) {
  const id =
    Number(value)

  if (
    !Number.isInteger(id) ||
    id <= 0
  ) {
    throw new TypeError(
      `${label} must be a positive integer.`
    )
  }

  return id
}


/*
|--------------------------------------------------------------------------
| Response Helper
|--------------------------------------------------------------------------
*/

/**
 * Axios response -> backend response body
 */
function getResponseBody(
  response
) {
  return (
    response?.data
    ??
    response
    ??
    {}
  )
}


/*
|--------------------------------------------------------------------------
| Error Helper
|--------------------------------------------------------------------------
*/

function getReservationErrorMessage(
  error,
  fallbackMessage =
    'Unable to complete the reservation request.'
) {

  /*
  |--------------------------------------------------------------------------
  | Laravel validation errors
  |--------------------------------------------------------------------------
  */

  const validationErrors =
    error
      ?.response
      ?.data
      ?.errors


  if (
    validationErrors &&
    typeof validationErrors ===
      'object'
  ) {

    const firstError =
      Object.values(
        validationErrors
      )
        .flat()
        .find(Boolean)


    if (firstError) {

      return String(
        firstError
      )

    }
  }


  /*
  |--------------------------------------------------------------------------
  | General backend error
  |--------------------------------------------------------------------------
  */

  return String(
    error
      ?.response
      ?.data
      ?.message

    ||

    error?.message

    ||

    fallbackMessage
  )
}


/*
|--------------------------------------------------------------------------
| Default Summary
|--------------------------------------------------------------------------
*/

function createDefaultSummary() {

  return {

    total:
      0,

    reserved:
      0,

    confirmed:
      0,

    seated:
      0,

    completed:
      0,

    cancelled:
      0,

    total_guests:
      0,

    total_advance:
      0,

    total_advance_formatted:
      '৳ 0.00',

  }

}


/*
|--------------------------------------------------------------------------
| Default Pagination
|--------------------------------------------------------------------------
*/

function createDefaultMeta() {

  return {

    current_page:
      1,

    last_page:
      1,

    per_page:
      10,

    total:
      0,

    from:
      null,

    to:
      null,

  }

}


/*
|--------------------------------------------------------------------------
| Pagination Normalizer
|--------------------------------------------------------------------------
*/

function normalizePaginationMeta(
  meta = {}
) {

  return {

    current_page:
      Number(
        meta?.current_page
      ) || 1,

    last_page:
      Number(
        meta?.last_page
      ) || 1,

    per_page:
      Number(
        meta?.per_page
      ) || 10,

    total:
      Number(
        meta?.total
      ) || 0,

    from:
      meta?.from
      ??
      null,

    to:
      meta?.to
      ??
      null,

  }

}


/*
|--------------------------------------------------------------------------
| Summary Normalizer
|--------------------------------------------------------------------------
*/

function normalizeSummary(
  summary = {}
) {

  const defaults =
    createDefaultSummary()


  return {

    ...defaults,

    ...summary,

    total:
      Number(
        summary?.total
      ) || 0,

    reserved:
      Number(
        summary?.reserved
      ) || 0,

    confirmed:
      Number(
        summary?.confirmed
      ) || 0,

    seated:
      Number(
        summary?.seated
      ) || 0,

    completed:
      Number(
        summary?.completed
      ) || 0,

    cancelled:
      Number(
        summary?.cancelled
      ) || 0,

    total_guests:
      Number(
        summary?.total_guests
      ) || 0,

    total_advance:
      Number(
        summary?.total_advance
      ) || 0,

    total_advance_formatted:
      summary
        ?.total_advance_formatted
      ||
      defaults
        .total_advance_formatted,

  }

}


/*
|--------------------------------------------------------------------------
| Reservation Payload Normalizer
|--------------------------------------------------------------------------
|
| Used for create/update requests.
|
*/

function normalizeReservationPayload(
  payload = {}
) {

  const data = {
    ...(payload || {}),
  }


  /*
  |--------------------------------------------------------------------------
  | Customer
  |--------------------------------------------------------------------------
  */

  if (
    data.customer_id !== undefined &&
    data.customer_id !== null &&
    data.customer_id !== ''
  ) {

    data.customer_id =
      resolvePositiveId(
        data.customer_id,
        'Customer ID'
      )

  }
  else {

    data.customer_id =
      null

  }


  /*
  |--------------------------------------------------------------------------
  | Guest Count
  |--------------------------------------------------------------------------
  */

  if (
    data.guest_count !== undefined &&
    data.guest_count !== null &&
    data.guest_count !== ''
  ) {

    data.guest_count =
      Number(
        data.guest_count
      )

  }


  /*
  |--------------------------------------------------------------------------
  | Table IDs
  |--------------------------------------------------------------------------
  */

  if (
    !Array.isArray(
      data.table_ids
    )
  ) {

    data.table_ids =
      []

  }
  else {

    data.table_ids =
      data.table_ids
        .map(
          id =>
            resolvePositiveId(
              id,
              'Table ID'
            )
        )
        .filter(
          (
            id,
            index,
            array
          ) =>
            array.indexOf(id) ===
            index
        )

  }


  /*
  |--------------------------------------------------------------------------
  | Advance Amount
  |--------------------------------------------------------------------------
  */

  if (
    data.advance_amount !==
      undefined
    &&
    data.advance_amount !==
      null
    &&
    data.advance_amount !==
      ''
  ) {

    data.advance_amount =
      Number(
        data.advance_amount
      )

  }
  else {

    data.advance_amount =
      0

  }


  /*
  |--------------------------------------------------------------------------
  | Date
  |--------------------------------------------------------------------------
  */

  if (
    data.reservation_date !==
    undefined
  ) {

    data.reservation_date =
      String(
        data.reservation_date
      ).trim()

  }


  /*
  |--------------------------------------------------------------------------
  | Start Time
  |--------------------------------------------------------------------------
  */

  if (
    data.start_time !==
    undefined
  ) {

    data.start_time =
      String(
        data.start_time
      ).trim()

  }


  /*
  |--------------------------------------------------------------------------
  | End Time
  |--------------------------------------------------------------------------
  */

  if (
    data.end_time !==
    undefined
  ) {

    data.end_time =
      String(
        data.end_time
      ).trim()

  }


  /*
  |--------------------------------------------------------------------------
  | Customer Name
  |--------------------------------------------------------------------------
  */

  if (
    data.customer_name !==
      undefined
    &&
    data.customer_name !==
      null
  ) {

    data.customer_name =
      String(
        data.customer_name
      ).trim()

  }


  /*
  |--------------------------------------------------------------------------
  | Customer Phone
  |--------------------------------------------------------------------------
  */

  if (
    data.customer_phone !==
      undefined
    &&
    data.customer_phone !==
      null
  ) {

    data.customer_phone =
      String(
        data.customer_phone
      ).trim()

  }


  /*
  |--------------------------------------------------------------------------
  | Customer Email
  |--------------------------------------------------------------------------
  */

  if (
    data.customer_email !==
      undefined
    &&
    data.customer_email !==
      null
  ) {

    data.customer_email =
      String(
        data.customer_email
      ).trim()

  }


  /*
  |--------------------------------------------------------------------------
  | Status
  |--------------------------------------------------------------------------
  */

  if (
    data.status !==
      undefined
    &&
    data.status !==
      null
    &&
    data.status !== ''
  ) {

    data.status =
      String(
        data.status
      ).trim()

  }


  /*
  |--------------------------------------------------------------------------
  | Note
  |--------------------------------------------------------------------------
  */

  if (
    data.note !==
      undefined
    &&
    data.note !==
      null
  ) {

    data.note =
      String(
        data.note
      ).trim()

  }


  return data
}


/*
|--------------------------------------------------------------------------
| Reservation Service
|--------------------------------------------------------------------------
*/

const reservationService = {


  /*
  |--------------------------------------------------------------------------
  | Reservation List
  |--------------------------------------------------------------------------
  |
  | GET /api/reservations
  |
  */

  async getReservations(
    filters = {}
  ) {

    const response =
      await api.get(
        RESERVATION_BASE_URL,
        {
          params:
            cleanRequestParams(
              filters
            ),
        }
      )


    const body =
      getResponseBody(
        response
      )


    return {

      success:
        Boolean(
          body?.success
        ),

      message:
        body?.message
        ||
        '',

      data:
        Array.isArray(
          body?.data
        )
          ? body.data
          : [],

      summary:
        normalizeSummary(
          body?.summary
        ),

      meta:
        normalizePaginationMeta(
          body?.meta
        ),

      filters:
        body?.filters
        ||
        {
          statuses: [],
          sorts: [],
        },

    }

  },


  /*
  |--------------------------------------------------------------------------
  | Single Reservation
  |--------------------------------------------------------------------------
  |
  | GET /api/reservations/{id}
  |
  */

  async getReservation(
    reservationId
  ) {

    const id =
      resolvePositiveId(
        reservationId,
        'Reservation ID'
      )


    const response =
      await api.get(
        `${RESERVATION_BASE_URL}/${id}`
      )


    return getResponseBody(
      response
    )

  },


  /*
  |--------------------------------------------------------------------------
  | Edit Options
  |--------------------------------------------------------------------------
  |
  | GET /api/reservations/{id}/edit-options
  |
  | Returns:
  |
  | - reservation
  | - current_table_ids
  | - available_tables
  | - edit_window
  |
  */

  async getEditOptions(
    reservationId,
    params = {}
  ) {

    const id =
      resolvePositiveId(
        reservationId,
        'Reservation ID'
      )


    const response =
      await api.get(
        `${RESERVATION_BASE_URL}/${id}/edit-options`,
        {
          params:
            cleanRequestParams(
              params
            ),
        }
      )


    const body =
      getResponseBody(
        response
      )


    return {

      success:
        Boolean(
          body?.success
        ),

      message:
        body?.message
        ||
        '',

      data:
        body?.data
        ||
        {
          reservation:
            null,

          current_table_ids:
            [],

          available_tables:
            [],

          edit_window:
            null,
        },

    }

  },


  /*
  |--------------------------------------------------------------------------
  | Customer List
  |--------------------------------------------------------------------------
  |
  | GET /api/customers
  |
  */

  async getCustomers(
    params = {}
  ) {

    const response =
      await api.get(
        CUSTOMER_BASE_URL,
        {
          params:
            cleanRequestParams(
              params
            ),
        }
      )


    return getResponseBody(
      response
    )

  },
  /*
|--------------------------------------------------------------------------
| Reservation Edit Options
|--------------------------------------------------------------------------
*/

async getReservationEditOptions(
    reservationId,
    params = {}
) {

    const id =
        resolvePositiveId(
            reservationId,
            'Reservation ID'
        )

    const response =
        await api.get(
            `${RESERVATION_BASE_URL}/${id}/edit-options`,
            {
                params:
                    cleanRequestParams(
                        params
                    ),
            }
        )

    return getResponseBody(
        response
    )

},


/*
|--------------------------------------------------------------------------
| Update Reservation
|--------------------------------------------------------------------------
*/

async updateReservation(
    reservationId,
    payload = {}
) {

    const id =
        resolvePositiveId(
            reservationId,
            'Reservation ID'
        )


    const requestPayload = {
        ...(payload || {}),
    }


    if (
        requestPayload.customer_id !==
        undefined
    ) {

        requestPayload.customer_id =
            resolvePositiveId(
                requestPayload.customer_id,
                'Customer ID'
            )

    }


    if (
        requestPayload.guest_count !==
        undefined
    ) {

        requestPayload.guest_count =
            Number(
                requestPayload.guest_count
            )

    }


    if (
        Array.isArray(
            requestPayload.table_ids
        )
    ) {

        requestPayload.table_ids =
            requestPayload.table_ids
                .map(
                    tableId =>
                        resolvePositiveId(
                            tableId,
                            'Table ID'
                        )
                )
                .filter(
                    (
                        tableId,
                        index,
                        array
                    ) =>
                        array.indexOf(
                            tableId
                        ) === index
                )

    }


    if (
        requestPayload.advance_amount !==
            undefined
        &&
        requestPayload.advance_amount !==
            null
        &&
        requestPayload.advance_amount !==
            ''
    ) {

        requestPayload.advance_amount =
            Number(
                requestPayload.advance_amount
            )

    }


    if (
        requestPayload.reservation_date !==
        undefined
    ) {

        requestPayload.reservation_date =
            String(
                requestPayload.reservation_date
            ).trim()

    }


    if (
        requestPayload.start_time !==
        undefined
    ) {

        requestPayload.start_time =
            String(
                requestPayload.start_time
            ).trim()

    }


    if (
        requestPayload.end_time !==
        undefined
    ) {

        requestPayload.end_time =
            String(
                requestPayload.end_time
            ).trim()

    }


    if (
        requestPayload.status !==
            undefined
        &&
        requestPayload.status !==
            null
    ) {

        requestPayload.status =
            String(
                requestPayload.status
            ).trim()

    }


    if (
        requestPayload.note !==
            undefined
        &&
        requestPayload.note !==
            null
    ) {

        requestPayload.note =
            String(
                requestPayload.note
            ).trim()

    }


    const response =
        await api.put(
            `${RESERVATION_BASE_URL}/${id}`,
            requestPayload
        )


    return getResponseBody(
        response
    )

},

  /*
  |--------------------------------------------------------------------------
  | Create Reservation
  |--------------------------------------------------------------------------
  |
  | POST /api/reservations
  |
  */

  async createReservation(
    payload = {}
  ) {

    const requestPayload =
      normalizeReservationPayload(
        payload
      )


    const response =
      await api.post(
        RESERVATION_BASE_URL,
        requestPayload
      )


    return getResponseBody(
      response
    )

  },


  /*
  |--------------------------------------------------------------------------
  | Update Reservation
  |--------------------------------------------------------------------------
  |
  | PUT /api/reservations/{id}
  |
  */

  async updateReservation(
    reservationId,
    payload = {}
  ) {

    const id =
      resolvePositiveId(
        reservationId,
        'Reservation ID'
      )


    const requestPayload =
      normalizeReservationPayload(
        payload
      )


    const response =
      await api.put(
        `${RESERVATION_BASE_URL}/${id}`,
        requestPayload
      )


    return getResponseBody(
      response
    )

  },


  /*
  |--------------------------------------------------------------------------
  | Available Tables
  |--------------------------------------------------------------------------
  |
  | GET /api/reservations/available-tables
  |
  */

  async getAvailableTables(
    params = {}
  ) {

    const requestParams = {
      ...(params || {}),
    }


    /*
    |--------------------------------------------------------------------------
    | Guest Count
    |--------------------------------------------------------------------------
    */

    if (
      requestParams.guest_count !==
        undefined
      &&
      requestParams.guest_count !==
        null
      &&
      requestParams.guest_count !==
        ''
    ) {

      requestParams.guest_count =
        Number(
          requestParams.guest_count
        )

    }


    /*
    |--------------------------------------------------------------------------
    | Date
    |--------------------------------------------------------------------------
    */

    if (
      requestParams.date !==
      undefined
    ) {

      requestParams.date =
        String(
          requestParams.date
        ).trim()

    }


    /*
    |--------------------------------------------------------------------------
    | Compatibility:
    | reservation_date
    |--------------------------------------------------------------------------
    |
    | Some frontend forms may use reservation_date instead of date.
    |
    */

    if (
      !requestParams.date
      &&
      requestParams.reservation_date
    ) {

      requestParams.date =
        String(
          requestParams.reservation_date
        ).trim()

    }


    /*
    |--------------------------------------------------------------------------
    | Start Time
    |--------------------------------------------------------------------------
    */

    if (
      requestParams.start_time !==
      undefined
    ) {

      requestParams.start_time =
        String(
          requestParams.start_time
        ).trim()

    }


    /*
    |--------------------------------------------------------------------------
    | End Time
    |--------------------------------------------------------------------------
    */

    if (
      requestParams.end_time !==
      undefined
    ) {

      requestParams.end_time =
        String(
          requestParams.end_time
        ).trim()

    }


    const response =
      await api.get(
        `${RESERVATION_BASE_URL}/available-tables`,
        {
          params:
            cleanRequestParams(
              requestParams
            ),
        }
      )


    const body =
      getResponseBody(
        response
      )


    return {

      success:
        Boolean(
          body?.success
        ),

      message:
        body?.message
        ||
        '',

      data:
        Array.isArray(
          body?.data
        )
          ? body.data
          : [],

    }

  },


  /*
  |--------------------------------------------------------------------------
  | Cancel Reservation
  |--------------------------------------------------------------------------
  |
  | POST /api/reservations/{id}/cancel
  |
  */

  async cancelReservation(
    reservationId,
    reason = null
  ) {

    const id =
      resolvePositiveId(
        reservationId,
        'Reservation ID'
      )


    const requestBody = {

      reason:
        reason !== null
          ? String(reason).trim()
          : null,

    }


    const response =
      await api.post(
        `${RESERVATION_BASE_URL}/${id}/cancel`,
        requestBody
      )


    return getResponseBody(
      response
    )

  },


  /*
  |--------------------------------------------------------------------------
  | Seat Reservation
  |--------------------------------------------------------------------------
  |
  | POST /api/reservations/{id}/seat
  |
  */

  async seatReservation(
    reservationId
  ) {

    const id =
      resolvePositiveId(
        reservationId,
        'Reservation ID'
      )


    const response =
      await api.post(
        `${RESERVATION_BASE_URL}/${id}/seat`
      )


    return getResponseBody(
      response
    )

  },


  /*
  |--------------------------------------------------------------------------
  | Error Message Helper
  |--------------------------------------------------------------------------
  */

  getReservationErrorMessage,


  /*
  |--------------------------------------------------------------------------
  | Factories
  |--------------------------------------------------------------------------
  */

  createDefaultSummary,

  createDefaultMeta,

}


/*
|--------------------------------------------------------------------------
| Named Exports
|--------------------------------------------------------------------------
*/

export {

  cleanRequestParams,

  resolvePositiveId,

  normalizePaginationMeta,

  normalizeSummary,

  normalizeReservationPayload,

  getReservationErrorMessage,

}


/*
|--------------------------------------------------------------------------
| Default Export
|--------------------------------------------------------------------------
*/

export default reservationService