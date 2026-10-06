<template>

<div
    v-if="show"
    class="reservation-view-overlay"
    @mousedown.self="closeModal"
>


    <div class="reservation-view-modal">


        <!-- ============================================================
             HEADER
        ============================================================= -->

        <div class="reservation-view-header">

            <div class="reservation-view-title-wrap">

                <div class="reservation-view-title-icon">

                    <i class="bi bi-calendar-check"></i>

                </div>


                <div>

                    <span class="reservation-view-eyebrow">
                        Reservation Details
                    </span>

                    <h3>
                        {{
                            reservation?.reservation_no
                            ||
                            'Reservation'
                        }}
                    </h3>

                </div>

            </div>


            <button
                type="button"
                class="reservation-view-close"
                @click="closeModal"
            >

                <i class="bi bi-x-lg"></i>

            </button>

        </div>



        <!-- ============================================================
             BODY
        ============================================================= -->

        <div class="reservation-view-body">


            <!-- Loading -->

            <div
                v-if="loading"
                class="reservation-view-loading"
            >

                <span class="spinner-border"></span>

                <p>
                    Loading reservation details...
                </p>

            </div>


            <!-- Error -->

            <div
                v-else-if="errorMessage"
                class="reservation-view-error"
            >

                <i class="bi bi-exclamation-triangle"></i>

                <span>
                    {{ errorMessage }}
                </span>

                <button
                    type="button"
                    @click="loadReservation"
                >
                    Retry
                </button>

            </div>


            <!-- Content -->

            <template v-else-if="reservation">


                <!-- ====================================================
                     STATUS / QUICK INFO
                ===================================================== -->

                <div class="reservation-view-top-grid">


                    <!-- Status -->

                    <div class="reservation-view-status-card">

                        <span>
                            Status
                        </span>

                        <strong
                            class="reservation-view-status"
                            :class="
                                getStatusClass(
                                    reservation.status
                                )
                            "
                        >

                            <i
                                class="bi"
                                :class="
                                    getStatusIcon(
                                        reservation.status
                                    )
                                "
                            ></i>

                            {{
                                reservation.status_label
                                ||
                                formatStatus(
                                    reservation.status
                                )
                            }}

                        </strong>

                    </div>


                    <!-- Date -->

                    <div class="reservation-view-info-card">

                        <span>
                            Reservation Date
                        </span>

                        <strong>

                            {{
                                formatReservationDate(
                                    reservation.reservation_date
                                )
                            }}

                        </strong>

                    </div>


                    <!-- Time -->

                    <div class="reservation-view-info-card">

                        <span>
                            Reservation Time
                        </span>

                        <strong>

                            {{
                                reservation.start_time_formatted
                                ||
                                formatTime(
                                    reservation.start_time
                                )
                            }}

                            <i class="bi bi-arrow-right"></i>

                            {{
                                reservation.end_time_formatted
                                ||
                                formatTime(
                                    reservation.end_time
                                )
                            }}

                        </strong>

                    </div>


                    <!-- Guests -->

                    <div class="reservation-view-info-card">

                        <span>
                            Guests
                        </span>

                        <strong>

                            <i class="bi bi-people"></i>

                            {{
                                reservation.guest_count
                                ||
                                0
                            }}

                        </strong>

                    </div>

                </div>



                <!-- ====================================================
                     CUSTOMER
                ===================================================== -->

                <div class="reservation-view-section">

                    <div class="reservation-view-section-title">

                        <i class="bi bi-person"></i>

                        <span>
                            Customer Information
                        </span>

                    </div>


                    <div class="reservation-view-customer-card">


                        <div class="reservation-view-customer-avatar">

                            {{
                                getCustomerInitial(
                                    reservation
                                )
                            }}

                        </div>


                        <div
                            class="
                                reservation-view-customer-details
                            "
                        >

                            <strong>

                                {{
                                    reservation
                                        .customer
                                        ?.name
                                    ||
                                    'Walk-in Customer'
                                }}

                            </strong>


                            <div
                                class="
                                    reservation-view-customer-meta
                                "
                            >

                                <span
                                    v-if="
                                        reservation
                                            .customer
                                            ?.phone
                                    "
                                >

                                    <i class="bi bi-telephone"></i>

                                    {{
                                        reservation
                                            .customer
                                            .phone
                                    }}

                                </span>


                                <span
                                    v-if="
                                        reservation
                                            .customer
                                            ?.email
                                    "
                                >

                                    <i class="bi bi-envelope"></i>

                                    {{
                                        reservation
                                            .customer
                                            .email
                                    }}

                                </span>

                            </div>

                        </div>

                    </div>

                </div>



                <!-- ====================================================
                     TABLES
                ===================================================== -->

                <div class="reservation-view-section">

                    <div class="reservation-view-section-title">

                        <i class="bi bi-grid-3x3-gap"></i>

                        <span>
                            Assigned Tables
                        </span>

                        <small>
                            {{
                                reservation.table_count
                                ??
                                reservation.tables?.length
                                ??
                                0
                            }}
                            table(s)
                        </small>

                    </div>


                    <div
                        v-if="
                            Array.isArray(
                                reservation.tables
                            )
                            &&
                            reservation.tables.length
                        "
                        class="reservation-view-tables"
                    >

                        <div
                            v-for="
                                table in reservation.tables
                            "
                            :key="table.id"
                            class="reservation-view-table-card"
                        >

                            <div
                                class="
                                    reservation-view-table-icon
                                "
                            >

                                <i class="bi bi-grid-3x3"></i>

                            </div>


                            <div>

                                <strong>

                                    {{
                                        table.table_name
                                        ||
                                        table.name
                                        ||
                                        `Table ${table.id}`
                                    }}

                                </strong>


                                <span>

                                    Capacity:
                                    {{
                                        table.capacity
                                        ??
                                        '-'
                                    }}

                                </span>


                                <small
                                    v-if="table.section"
                                >

                                    {{ table.section }}

                                </small>

                            </div>

                        </div>

                    </div>


                    <div
                        v-else
                        class="reservation-view-no-data"
                    >

                        <i class="bi bi-grid-3x3-gap"></i>

                        No tables assigned.

                    </div>

                </div>



                <!-- ====================================================
                     PAYMENT
                ===================================================== -->

                <div class="reservation-view-section">

                    <div class="reservation-view-section-title">

                        <i class="bi bi-wallet2"></i>

                        <span>
                            Payment Information
                        </span>

                    </div>


                    <div class="reservation-view-payment-grid">

                        <div>

                            <span>
                                Advance Amount
                            </span>

                            <strong>

                                {{
                                    reservation
                                        .advance_amount_formatted
                                    ||
                                    formatMoney(
                                        reservation.advance_amount
                                    )
                                }}

                            </strong>

                        </div>


                        <div>

                            <span>
                                Table Capacity
                            </span>

                            <strong>

                                {{
                                    reservation
                                        .total_table_capacity
                                    ??
                                    calculateTableCapacity()
                                }}

                                seats

                            </strong>

                        </div>

                    </div>

                </div>



                <!-- ====================================================
                     ORDER INFORMATION
                ===================================================== -->

                <div class="reservation-view-section">

                    <div class="reservation-view-section-title">

                        <i class="bi bi-cart3"></i>

                        <span>
                            Related Orders
                        </span>

                        <small>
                            {{
                                reservation.order_count
                                ??
                                reservation.orders?.length
                                ??
                                0
                            }}
                            order(s)
                        </small>

                    </div>


                    <div
                        v-if="
                            Array.isArray(
                                reservation.orders
                            )
                            &&
                            reservation.orders.length
                        "
                        class="reservation-view-orders"
                    >

                        <div
                            v-for="
                                order in reservation.orders
                            "
                            :key="order.id"
                            class="reservation-view-order-card"
                        >

                            <div>

                                <strong>
                                    {{
                                        order.order_number
                                        ||
                                        `Order #${order.id}`
                                    }}
                                </strong>

                                <small>

                                    {{
                                        formatStatus(
                                            order.status
                                        )
                                    }}

                                </small>

                            </div>


                            <div>

                                <strong>

                                    {{
                                        formatMoney(
                                            order.total_amount
                                        )
                                    }}

                                </strong>

                                <small>

                                    {{
                                        formatStatus(
                                            order.payment_status
                                        )
                                    }}

                                </small>

                            </div>

                        </div>

                    </div>


                    <div
                        v-else
                        class="reservation-view-no-data"
                    >

                        <i class="bi bi-cart-x"></i>

                        No related orders found.

                    </div>

                </div>



                <!-- ====================================================
                     NOTE
                ===================================================== -->

                <div
                    v-if="reservation.note"
                    class="reservation-view-section"
                >

                    <div class="reservation-view-section-title">

                        <i class="bi bi-journal-text"></i>

                        <span>
                            Notes
                        </span>

                    </div>


                    <div class="reservation-view-note">

                        {{ reservation.note }}

                    </div>

                </div>



                <!-- ====================================================
                     META
                ===================================================== -->

                <div class="reservation-view-meta">

                    <div>

                        <span>
                            Created
                        </span>

                        <strong>
                            {{
                                formatDateTime(
                                    reservation.created_at
                                )
                            }}
                        </strong>

                    </div>


                    <div>

                        <span>
                            Last Updated
                        </span>

                        <strong>
                            {{
                                formatDateTime(
                                    reservation.updated_at
                                )
                            }}
                        </strong>

                    </div>

                </div>

            </template>

        </div>



        <!-- ============================================================
             FOOTER
        ============================================================= -->

        <div class="reservation-view-footer">

            <button
                type="button"
                class="btn btn-light"
                @click="closeModal"
            >

                <i class="bi bi-x-lg"></i>

                Close

            </button>

        </div>

    </div>

</div>

</template>


<script setup>

import {
    ref,
    watch,
} from 'vue'


import reservationService
    from '@/services/reservationService'


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props =
    defineProps({

        show: {
            type: Boolean,
            default: false,
        },

        reservation: {
            type: Object,
            default: null,
        },

    })


/*
|--------------------------------------------------------------------------
| Emits
|--------------------------------------------------------------------------
*/

const emit =
    defineEmits([
        'close',
    ])


/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const loading =
    ref(false)

const errorMessage =
    ref('')

const loadedReservation =
    ref(null)


/*
|--------------------------------------------------------------------------
| Active Reservation
|--------------------------------------------------------------------------
*/

const reservation =
    ref(null)


/*
|--------------------------------------------------------------------------
| Load Single Reservation
|--------------------------------------------------------------------------
*/

async function loadReservation()
{

    /*
    |--------------------------------------------------------------------------
    | Reset
    |--------------------------------------------------------------------------
    */

    errorMessage.value =
        ''

    /*
    |--------------------------------------------------------------------------
    | Use existing list object first.
    |--------------------------------------------------------------------------
    */

    reservation.value =
        props.reservation
            ? {
                ...props.reservation,
            }
            : null


    if (!props.reservation?.id) {

        return

    }


    loading.value =
        true


    try {

        const response =
            await reservationService
                .getReservation(
                    props.reservation.id
                )


        const body =
            response?.data
            ??
            response
            ??
            {}


        loadedReservation.value =
            body?.data
            ??
            body


        if (
            loadedReservation.value?.id
        ) {

            reservation.value =
                loadedReservation.value

        }

    }

    catch (error) {

        console.error(
            'Reservation details error:',
            error
        )


        /*
        |--------------------------------------------------------------------------
        | Keep list data visible even if detail request fails.
        |--------------------------------------------------------------------------
        */

        if (!reservation.value) {

            errorMessage.value =
                reservationService
                    .getReservationErrorMessage(
                        error,
                        'Unable to load reservation details.'
                    )

        }

    }

    finally {

        loading.value =
            false

    }

}


/*
|--------------------------------------------------------------------------
| Watch Modal
|--------------------------------------------------------------------------
*/

watch(
    () => props.show,
    (
        isOpen
    ) => {

        if (isOpen) {

            loadReservation()

        }
        else {

            reservation.value =
                null

            loadedReservation.value =
                null

            errorMessage.value =
                ''

        }

    }
)


/*
|--------------------------------------------------------------------------
| Watch Reservation
|--------------------------------------------------------------------------
*/

watch(
    () => props.reservation,
    (
        newReservation
    ) => {

        if (
            props.show
            &&
            newReservation
        ) {

            loadReservation()

        }

    },
    {
        deep: true,
    }
)


/*
|--------------------------------------------------------------------------
| Close
|--------------------------------------------------------------------------
*/

function closeModal()
{
    emit(
        'close'
    )
}


/*
|--------------------------------------------------------------------------
| Customer Initial
|--------------------------------------------------------------------------
*/

function getCustomerInitial(
    reservation
) {

    const name =
        String(
            reservation
                ?.customer
                ?.name
            ||
            'Walk-in Customer'
        ).trim()


    return name
        ? name
            .charAt(
                0
            )
            .toUpperCase()
        : 'C'

}


/*
|--------------------------------------------------------------------------
| Status Class
|--------------------------------------------------------------------------
*/

function getStatusClass(
    status
) {

    switch (
        String(
            status
            ||
            ''
        ).toLowerCase()
    ) {

        case 'reserved':
            return 'status-reserved'

        case 'confirmed':
            return 'status-confirmed'

        case 'seated':
            return 'status-seated'

        case 'completed':
            return 'status-completed'

        case 'cancelled':
        case 'canceled':
            return 'status-cancelled'

        default:
            return 'status-default'

    }

}


/*
|--------------------------------------------------------------------------
| Status Icon
|--------------------------------------------------------------------------
*/

function getStatusIcon(
    status
) {

    switch (
        String(
            status
            ||
            ''
        ).toLowerCase()
    ) {

        case 'reserved':
            return 'bi-calendar-check'

        case 'confirmed':
            return 'bi-check-circle'

        case 'seated':
            return 'bi-people'

        case 'completed':
            return 'bi-check2-all'

        case 'cancelled':
        case 'canceled':
            return 'bi-x-circle'

        default:
            return 'bi-info-circle'

    }

}


/*
|--------------------------------------------------------------------------
| Status Label
|--------------------------------------------------------------------------
*/

function formatStatus(
    status
) {

    if (!status) {

        return '-'

    }


    return String(
        status
    )
        .replace(
            /[_-]+/g,
            ' '
        )
        .replace(
            /\b\w/g,
            character =>
                character.toUpperCase()
        )

}


/*
|--------------------------------------------------------------------------
| Date
|--------------------------------------------------------------------------
*/

function formatReservationDate(
    date
) {

    if (!date) {

        return '-'

    }


    const parsedDate =
        new Date(
            `${date}T00:00:00`
        )


    if (
        Number.isNaN(
            parsedDate.getTime()
        )
    ) {

        return date

    }


    return new Intl.DateTimeFormat(
        'en-GB',
        {

            weekday:
                'short',

            day:
                '2-digit',

            month:
                'short',

            year:
                'numeric',

        }
    ).format(
        parsedDate
    )

}


/*
|--------------------------------------------------------------------------
| Time
|--------------------------------------------------------------------------
*/

function formatTime(
    time
) {

    if (!time) {

        return '-'

    }


    /*
    |--------------------------------------------------------------------------
    | Already formatted
    |--------------------------------------------------------------------------
    */

    if (
        /AM|PM/i.test(
            String(time)
        )
    ) {

        return time

    }


    const [
        hours,
        minutes,
    ] =
        String(
            time
        )
            .split(':')
            .map(
                Number
            )


    if (
        Number.isNaN(hours)
        ||
        Number.isNaN(minutes)
    ) {

        return time

    }


    const date =
        new Date()

    date.setHours(
        hours,
        minutes,
        0,
        0
    )


    return new Intl.DateTimeFormat(
        'en-US',
        {

            hour:
                '2-digit',

            minute:
                '2-digit',

            hour12:
                true,

        }
    ).format(
        date
    )

}


/*
|--------------------------------------------------------------------------
| Money
|--------------------------------------------------------------------------
*/

function formatMoney(
    value
) {

    const amount =
        Number(
            value
        )
        ||
        0


    return (
        '৳ '
        +
        amount.toLocaleString(
            'en-BD',
            {

                minimumFractionDigits:
                    2,

                maximumFractionDigits:
                    2,

            }
        )
    )

}


/*
|--------------------------------------------------------------------------
| Table Capacity
|--------------------------------------------------------------------------
*/

function calculateTableCapacity()
{

    if (
        !Array.isArray(
            reservation.value?.tables
        )
    ) {

        return 0

    }


    return reservation.value
        .tables
        .reduce(
            (
                total,
                table
            ) => {

                return (
                    total
                    +
                    Number(
                        table.capacity
                        ||
                        0
                    )
                )

            },
            0
        )

}


/*
|--------------------------------------------------------------------------
| Date Time
|--------------------------------------------------------------------------
*/

function formatDateTime(
    value
) {

    if (!value) {

        return '-'

    }


    const date =
        new Date(
            value
        )


    if (
        Number.isNaN(
            date.getTime()
        )
    ) {

        return value

    }


    return new Intl.DateTimeFormat(
        'en-GB',
        {

            day:
                '2-digit',

            month:
                'short',

            year:
                'numeric',

            hour:
                '2-digit',

            minute:
                '2-digit',

            hour12:
                true,

        }
    ).format(
        date
    )

}

</script>


<style
    src="@/assets/css/reservation/reservation-view-modal.css"
></style>