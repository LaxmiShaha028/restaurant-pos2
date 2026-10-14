<template>

<section class="reservation-page">

    <ReservationToast
        :show="toast.show"
        :type="toast.type"
        :message="toast.message"
        @close="hideToast"
    />



    <!-- ================================================================
         PAGE HEADER
    ================================================================= -->

    <div class="reservation-header">

        <div class="reservation-page-header-icon">
            <i class="bi bi-calendar-check"></i>
        </div>

        <div class="reservation-header-content">

            <h1 class="page-title">
                Reservations
            </h1>

            <p class="page-subtitle">
                Manage customer reservations,
                table assignments and reservation schedules.
            </p>

        </div>


        <button
            type="button"
            class="btn btn-primary"
            @click="openCreateReservation"
        >

            <i class="bi bi-plus-lg"></i>

            Add Reservation

        </button>

    </div>



    <!-- ================================================================
         SUMMARY
    ================================================================= -->

    <div class="reservation-summary-grid">

        <div class="reservation-summary-card">

            <div class="reservation-summary-icon">
                <i class="bi bi-calendar-event"></i>
            </div>

            <div class="reservation-summary-content">

                <span class="reservation-summary-label">
                    Total Reservations
                </span>

                <strong class="reservation-summary-value">
                    {{ formatNumber(summary.total) }}
                </strong>

            </div>

        </div>


        <div class="reservation-summary-card">

            <div class="reservation-summary-icon">
                <i class="bi bi-calendar-check"></i>
            </div>

            <div class="reservation-summary-content">

                <span class="reservation-summary-label">
                    Reserved
                </span>

                <strong class="reservation-summary-value">
                    {{ formatNumber(summary.reserved) }}
                </strong>

            </div>

        </div>


        <div class="reservation-summary-card">

            <div class="reservation-summary-icon">
                <i class="bi bi-check-circle"></i>
            </div>

            <div class="reservation-summary-content">

                <span class="reservation-summary-label">
                    Confirmed
                </span>

                <strong class="reservation-summary-value">
                    {{ formatNumber(summary.confirmed) }}
                </strong>

            </div>

        </div>


        <div class="reservation-summary-card">

            <div class="reservation-summary-icon">
                <i class="bi bi-people"></i>
            </div>

            <div class="reservation-summary-content">

                <span class="reservation-summary-label">
                    Seated
                </span>

                <strong class="reservation-summary-value">
                    {{ formatNumber(summary.seated) }}
                </strong>

            </div>

        </div>


        <div class="reservation-summary-card">

            <div class="reservation-summary-icon">
                <i class="bi bi-check2-all"></i>
            </div>

            <div class="reservation-summary-content">

                <span class="reservation-summary-label">
                    Completed
                </span>

                <strong class="reservation-summary-value">
                    {{ formatNumber(summary.completed) }}
                </strong>

            </div>

        </div>


        <!-- <div class="reservation-summary-card">

            <div class="reservation-summary-icon">
                <i class="bi bi-x-circle"></i>
            </div>

            <div class="reservation-summary-content">

                <span class="reservation-summary-label">
                    Cancelled
                </span>

                <strong class="reservation-summary-value">
                    {{ summary.cancelled }}
                </strong>

            </div>

        </div> -->

    </div>



    <!-- ================================================================
         FILTER
    ================================================================= -->

    <div class="reservation-filter-card">

        <div class="reservation-filter-grid">


            <!-- Search -->

            <div class="reservation-filter-field">

                <label class="reservation-filter-label">
                    Search
                </label>

                <div class="reservation-search-wrapper">

                    <i class="bi bi-search"></i>

                    <input
                        v-model.trim="filters.search"
                        type="text"
                        class="form-control"
                        placeholder="Reservation, customer, phone or table..."
                        @keyup.enter="applyFilters"
                    />

                </div>

            </div>


            <!-- Status -->

            <div class="reservation-filter-field">

                <label class="reservation-filter-label">
                    Status
                </label>

                <div ref="statusFilterRoot" class="reservation-filter-status-picker">
                    <button
                        type="button"
                        class="reservation-filter-status-trigger"
                        :aria-expanded="statusFilterOpen"
                        aria-haspopup="listbox"
                        @click.stop="statusFilterOpen = !statusFilterOpen"
                        @keydown.esc="statusFilterOpen = false"
                    >
                        <span>{{ selectedFilterStatusLabel }}</span>
                        <i class="bi bi-chevron-down" aria-hidden="true"></i>
                    </button>
                    <Transition name="reservation-filter-status-menu">
                        <div v-if="statusFilterOpen" class="reservation-filter-status-menu" role="listbox" aria-label="Reservation status">
                            <button
                                v-for="option in reservationFilterStatuses"
                                :key="option.value"
                                type="button"
                                class="reservation-filter-status-option"
                                :class="{ 'is-selected': filters.status === option.value }"
                                role="option"
                                :aria-selected="filters.status === option.value"
                                @click="selectFilterStatus(option.value)"
                            >
                                <span>{{ option.label }}</span>
                                <i v-if="filters.status === option.value" class="bi bi-check2" aria-hidden="true"></i>
                            </button>
                        </div>
                    </Transition>
                </div>

            </div>


            <!-- Date -->

            <div class="reservation-filter-field">

                <label class="reservation-filter-label">
                    Reservation Date
                </label>

                <ReservationFilterDatePicker v-model="filters.reservation_date" id="reservation-filter-date" label="reservation date" />

            </div>


            <!-- Date From -->

            <div class="reservation-filter-field">

                <label class="reservation-filter-label">
                    From
                </label>

                <ReservationFilterDatePicker v-model="filters.date_from" id="reservation-filter-from" label="start date" />

            </div>


            <!-- Date To -->

            <div class="reservation-filter-field">

                <label class="reservation-filter-label">
                    To
                </label>

                <ReservationFilterDatePicker v-model="filters.date_to" id="reservation-filter-to" label="end date" />

            </div>


            <!-- Actions -->

            <div class="reservation-filter-actions">

                <button
                    type="button"
                    class="btn btn-primary"
                    :disabled="loading"
                    @click="applyFilters"
                >

                    <span
                        v-if="loading"
                        class="spinner-border spinner-border-sm"
                    ></span>

                    <i
                        v-else
                        class="bi bi-search"
                    ></i>

                    Apply

                </button>


                <button
                    type="button"
                    class="btn btn-light"
                    :disabled="loading"
                    @click="resetFilters"
                >

                    <i class="bi bi-arrow-counterclockwise"></i>

                    Reset

                </button>

            </div>

        </div>

    </div>



    <!-- ================================================================
         ERROR
    ================================================================= -->

    <div
        v-if="errorMessage"
        class="reservation-error"
    >

        <i class="bi bi-exclamation-triangle"></i>

        <span>
            {{ errorMessage }}
        </span>

        <button
            type="button"
            class="reservation-error-close"
            @click="errorMessage = ''"
        >

            <i class="bi bi-x-lg"></i>

        </button>

    </div>



    <!-- ================================================================
         TABLE CARD
    ================================================================= -->

    <div class="reservation-card">


        <!-- Loading -->

        <div
            v-if="loading"
            class="reservation-loading"
        >

            <div class="spinner-border"></div>

            <p>
                Loading reservations...
            </p>

        </div>


        <!-- Empty -->

        <div
            v-else-if="reservations.length === 0"
            class="reservation-empty"
        >

            <div class="reservation-empty-icon">
                <i class="bi bi-calendar-x"></i>
            </div>

            <h3>
                No Reservations Found
            </h3>

            <p>
                There are no reservations matching your current filters.
            </p>

            <button
                type="button"
                class="btn btn-primary"
                @click="openCreateReservation"
            >

                <i class="bi bi-plus-lg"></i>

                Add Reservation

            </button>

        </div>


        <!-- Data -->

        <div
            v-else
            class="reservation-table-wrapper"
        >

            <table class="reservation-table">

                <thead>

                    <tr>

                        <th class="sl-column">
                            SL
                        </th>

                        <th>
                            Reservation
                        </th>

                        <th>
                            Customer
                        </th>

                        <th>
                            Date
                        </th>

                        <th>
                            Time
                        </th>

                        <th>
                            Guests
                        </th>

                        <th>
                            Tables
                        </th>

                        <th>
                            Advance
                        </th>

                        <th>
                            Status
                        </th>

                        <th class="action-column">
                            Action
                        </th>

                    </tr>

                </thead>


                <tbody>

                    <tr
                        v-for="(
                            reservation,
                            index
                        ) in reservations"
                        :key="reservation.id"
                    >


                        <!-- =================================================
                             SL
                        ================================================== -->

                        <td class="sl-column">

                            <span
                                class="reservation-serial"
                            >

                                {{
                                    String(
                                        (
                                            Number(
                                                meta.from
                                            )
                                            ||
                                            1
                                        )
                                        +
                                        index
                                    ).padStart(
                                        2,
                                        '0'
                                    )
                                }}

                            </span>

                        </td>


                        <!-- =================================================
                             Reservation Number
                        ================================================== -->

                        <td>

                            <span
                                class="reservation-number"
                            >

                                {{
                                    reservation.reservation_no
                                    ||
                                    '-'
                                }}

                            </span>

                        </td>


                        <!-- =================================================
                             Customer
                        ================================================== -->

                        <td>

                            <div
                                class="reservation-customer"
                            >

                                <div
                                    class="
                                        reservation-customer-info
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


                                    <span
                                        v-if="
                                            reservation
                                                .customer
                                                ?.phone
                                        "
                                    >

                                        {{
                                            reservation
                                                .customer
                                                .phone
                                        }}

                                    </span>

                                </div>

                            </div>

                        </td>


                        <!-- =================================================
                             Date
                        ================================================== -->

                        <td>

                            <span
                                class="reservation-date"
                            >

                                {{
                                    formatReservationDate(
                                        reservation
                                            .reservation_date
                                    )
                                }}

                            </span>

                        </td>


                        <!-- =================================================
                             Time
                        ================================================== -->

                        <td>

                            <div
                                class="reservation-time"
                            >

                                <span>
                                    {{
                                        reservation.start_time_formatted
                                        ||
                                        reservation.start_time
                                        ||
                                        '-'
                                    }}
                                </span>

                                <i class="bi bi-arrow-right"></i>

                                <span>
                                    {{
                                        reservation.end_time_formatted
                                        ||
                                        reservation.end_time
                                        ||
                                        '-'
                                    }}
                                </span>

                            </div>

                        </td>


                        <!-- =================================================
                             Guests
                        ================================================== -->

                        <td>

                            <span
                                class="reservation-guests"
                            >

                                <i class="bi bi-people"></i>

                                {{
                                    formatNumber(reservation.guest_count || 0)
                                }}

                            </span>

                        </td>


                        <!-- =================================================
                             Tables
                        ================================================== -->

                        <td>

                            <div
                                v-if="
                                    Array.isArray(
                                        reservation.tables
                                    )
                                    &&
                                    reservation.tables.length
                                "
                                class="reservation-tables"
                            >

                                <span
                                    v-for="table in reservation.tables"
                                    :key="table.id"
                                    class="
                                        reservation-table-badge
                                    "
                                >

                                    {{
                                        table.table_name
                                        ||
                                        table.name
                                        ||
                                        `Table ${table.id}`
                                    }}

                                </span>

                            </div>


                            <span
                                v-else
                                class="
                                    reservation-no-data
                                "
                            >
                                -
                            </span>

                        </td>


                        <!-- =================================================
                             Advance
                        ================================================== -->

                        <td>

                            <strong
                                class="reservation-advance"
                            >

                                {{
                                    reservation
                                        .advance_amount_formatted
                                    ||
                                    formatMoney(
                                        reservation
                                            .advance_amount
                                    )
                                }}

                            </strong>

                        </td>


                        <!-- =================================================
                             Status
                        ================================================== -->

                        <td>

                            <span
                                class="reservation-status"
                                :class="
                                    getStatusClass(
                                        reservation.status
                                    )
                                "
                            >

                                {{
                                    reservation.status_label
                                    ||
                                    formatStatus(
                                        reservation.status
                                    )
                                }}

                            </span>

                        </td>


                        <!-- =================================================
                             Actions
                        ================================================== -->

                        <td class="action-column">

                            <div
                                class="
                                    reservation-action-buttons
                                "
                            >


                                <!-- View -->

                                <button
                                    type="button"
                                    class="
                                        btn
                                        btn-sm
                                        btn-outline-primary
                                        reservation-action-view
                                    "
                                    title="View reservation"
                                    @click="
                                        handleViewReservation(
                                            reservation
                                        )
                                    "
                                >

                                    <i class="bi bi-eye"></i>

                                </button>


                                <!-- Edit -->

                                <button
                                    v-if="
                                        canEditReservation(
                                            reservation
                                        )
                                    "
                                    type="button"
                                    class="
                                        btn
                                        btn-sm
                                        btn-outline-secondary
                                        reservation-action-edit
                                    "
                                    title="Edit reservation"
                                    @click="
                                        handleEditReservation(
                                            reservation
                                        )
                                    "
                                >

                                    <i class="bi bi-pencil"></i>

                                </button>


                                <!-- Seat -->

                                <button
                                    v-if="
                                        canSeatReservation(
                                            reservation
                                        )
                                    "
                                    type="button"
                                    class="
                                        btn
                                        btn-sm
                                        btn-outline-success
                                        reservation-action-seat
                                    "
                                    title="Seat reservation"
                                    @click="
                                        handleSeatReservation(
                                            reservation
                                        )
                                    "
                                >

                                    <i class="bi bi-person-check"></i>

                                </button>


                                <!-- Order -->

                                <button
                                    v-if="
                                        canCreateOrder(
                                            reservation
                                        )
                                    "
                                    type="button"
                                    class="
                                        btn
                                        btn-sm
                                        btn-outline-warning
                                        reservation-action-order
                                    "
                                    title="Create order"
                                    @click="
                                        handleCreateOrder(
                                            reservation
                                        )
                                    "
                                >

                                    <i class="bi bi-cart-plus"></i>

                                </button>


                                <!-- Cancel -->

                                <button
                                    v-if="
                                        canCancelReservation(
                                            reservation
                                        )
                                    "
                                    type="button"
                                    class="
                                        btn
                                        btn-sm
                                        btn-outline-danger
                                        reservation-action-cancel
                                    "
                                    title="Cancel reservation"
                                    @click="
                                        handleCancelReservation(
                                            reservation
                                        )
                                    "
                                >

                                    <i class="bi bi-x-circle"></i>

                                </button>

                            </div>

                        </td>

                    </tr>

                </tbody>

            </table>

        </div>



        <!-- ================================================================
             PAGINATION
        ================================================================= -->

        <div
            v-if="
                !loading
                &&
                reservations.length > 0
                &&
                meta.last_page > 1
            "
            class="reservation-pagination"
        >

            <div
                class="reservation-pagination-info"
            >

                Showing

                <strong>
                    {{ formatNumber(meta.from || 0) }}
                </strong>

                to

                <strong>
                    {{ formatNumber(meta.to || 0) }}
                </strong>

                of

                <strong>
                    {{ formatNumber(meta.total || 0) }}
                </strong>

                reservations

            </div>


            <div
                class="
                    reservation-pagination-controls
                "
            >

                <button
                    type="button"
                    class="reservation-page-button"
                    :disabled="
                        loading
                        ||
                        meta.current_page <= 1
                    "
                    @click="
                        goToPage(
                            meta.current_page - 1
                        )
                    "
                >

                    <i class="bi bi-chevron-left"></i>

                </button>


                <span
                    class="reservation-page-number"
                >

                    Page
                    {{ meta.current_page }}
                    of
                    {{ meta.last_page }}

                </span>


                <button
                    type="button"
                    class="reservation-page-button"
                    :disabled="
                        loading
                        ||
                        meta.current_page >=
                        meta.last_page
                    "
                    @click="
                        goToPage(
                            meta.current_page + 1
                        )
                    "
                >

                    <i class="bi bi-chevron-right"></i>

                </button>

            </div>

        </div>

    </div>



    <!-- ================================================================
         VIEW MODAL
    ================================================================= -->

    <ReservationViewModal
        :show="showViewModal"
        :reservation="selectedReservation"
        @close="closeViewModal"
    />



    <!-- ================================================================
         EDIT MODAL
    ================================================================= -->

    <ReservationEditModal
        :show="showEditModal"
        :reservation="selectedReservation"
        @close="closeEditModal"
        @saved="handleReservationSaved"
    />


    <!-- ================================================================
         PROFESSIONAL SEAT / CANCEL ACTION MODAL
    ================================================================= -->

    <ReservationActionModal
        :show="showActionModal"
        :mode="actionMode"
        :reservation="actionReservation"
        :submitting="actionSubmitting"
        :error="actionError"
        @close="closeActionModal"
        @confirm="confirmReservationAction"
    />

</section>

</template>


<script setup>

import {
    computed,
    onBeforeUnmount,
    onMounted,
    reactive,
    ref,
} from 'vue'

import {
    useRoute,
    useRouter,
} from 'vue-router'

import reservationService
    from '@/services/reservationService'

import ReservationViewModal
    from './ReservationViewModal.vue'

import ReservationEditModal
    from './ReservationEditModal.vue'

import ReservationActionModal
    from './ReservationActionModal.vue'

import ReservationToast
    from './ReservationToast.vue'

import ReservationFilterDatePicker
    from '@/components/reservation/ReservationFilterDatePicker.vue'


/*
|--------------------------------------------------------------------------
| Router
|--------------------------------------------------------------------------
*/

const router =
    useRouter()

const route =
    useRoute()


/*
|--------------------------------------------------------------------------
| Emits
|--------------------------------------------------------------------------
*/

const emit =
    defineEmits([
        'add',
        'view',
    ])


/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const reservations =
    ref([])

const loading =
    ref(false)

const errorMessage =
    ref('')


/*
|--------------------------------------------------------------------------
| Modal State
|--------------------------------------------------------------------------
*/

const showViewModal =
    ref(false)

const showEditModal =
    ref(false)

const selectedReservation =
    ref(null)


/*
|--------------------------------------------------------------------------
| Professional Action Modal
|--------------------------------------------------------------------------
*/

const showActionModal =
    ref(false)

const actionMode =
    ref('seat')

const actionReservation =
    ref(null)

const actionSubmitting =
    ref(false)

const actionError =
    ref('')


/*
|--------------------------------------------------------------------------
| Toast
|--------------------------------------------------------------------------
*/

const toast =
    reactive({

        show:
            false,

        type:
            'success',

        message:
            '',

    })

let toastTimer =
    null


function showToast(
    message,
    type = 'success'
) {

    if (toastTimer) {

        clearTimeout(
            toastTimer
        )

    }


    toast.show =
        true

    toast.type =
        type

    toast.message =
        String(
            message
            ||
            ''
        )


    toastTimer =
        setTimeout(
            () => {

                hideToast()

            },
            3600
        )

}


function hideToast()
{
    toast.show =
        false

    toast.message =
        ''

    if (toastTimer) {

        clearTimeout(
            toastTimer
        )

        toastTimer =
            null

    }
}


/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

const summary =
    reactive({

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

    })


/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/

const meta =
    reactive({

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

    })


/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

const filters =
    reactive({

        search:
            '',

        status:
            '',

        reservation_date:
            '',

        date_from:
            '',

        date_to:
            '',

        sort:
            'latest',

        page:
            1,

        per_page:
            10,

    })

const reservationFilterStatuses = [
    { value: '', label: 'All Statuses' },
    { value: 'reserved', label: 'Reserved' },
    { value: 'confirmed', label: 'Confirmed' },
    { value: 'seated', label: 'Seated' },
    { value: 'completed', label: 'Completed' },
    { value: 'cancelled', label: 'Cancelled' },
]
const statusFilterRoot = ref(null)
const statusFilterOpen = ref(false)
const selectedFilterStatusLabel = computed(() =>
    reservationFilterStatuses.find(option => option.value === filters.status)?.label || 'All Statuses'
)

function selectFilterStatus(status) {
    filters.status = status
    statusFilterOpen.value = false
}

function handleFilterDocumentClick(event) {
    if (!statusFilterRoot.value?.contains(event.target)) {
        statusFilterOpen.value = false
    }
}


/*
|--------------------------------------------------------------------------
| Load Reservations
|--------------------------------------------------------------------------
*/

async function loadReservations()
{
    loading.value =
        true

    errorMessage.value =
        ''


    try {

        const response =
            await reservationService
                .getReservations(
                    filters
                )


        reservations.value =
            Array.isArray(
                response?.data
            )
                ? response.data
                : []


        Object.assign(
            summary,
            {

                total:
                    Number(
                        response
                            ?.summary
                            ?.total
                        ??
                        0
                    ),

                reserved:
                    Number(
                        response
                            ?.summary
                            ?.reserved
                        ??
                        0
                    ),

                confirmed:
                    Number(
                        response
                            ?.summary
                            ?.confirmed
                        ??
                        0
                    ),

                seated:
                    Number(
                        response
                            ?.summary
                            ?.seated
                        ??
                        0
                    ),

                completed:
                    Number(
                        response
                            ?.summary
                            ?.completed
                        ??
                        0
                    ),

                cancelled:
                    Number(
                        response
                            ?.summary
                            ?.cancelled
                        ??
                        0
                    ),

                total_guests:
                    Number(
                        response
                            ?.summary
                            ?.total_guests
                        ??
                        0
                    ),

                total_advance:
                    Number(
                        response
                            ?.summary
                            ?.total_advance
                        ??
                        0
                    ),

            }
        )


        Object.assign(
            meta,
            {

                current_page:
                    Number(
                        response
                            ?.meta
                            ?.current_page
                        ??
                        1
                    ),

                last_page:
                    Number(
                        response
                            ?.meta
                            ?.last_page
                        ??
                        1
                    ),

                per_page:
                    Number(
                        response
                            ?.meta
                            ?.per_page
                        ??
                        10
                    ),

                total:
                    Number(
                        response
                            ?.meta
                            ?.total
                        ??
                        0
                    ),

                from:
                    response
                        ?.meta
                        ?.from
                    ??
                    null,

                to:
                    response
                        ?.meta
                        ?.to
                    ??
                    null,

            }
        )

    }

    catch (error) {

        console.error(
            'Reservation list error:',
            error
        )


        errorMessage.value =
            reservationService
                .getReservationErrorMessage(
                    error,
                    'Unable to load reservations.'
                )


        reservations.value =
            []

    }

    finally {

        loading.value =
            false

    }

}


/*
|--------------------------------------------------------------------------
| Refresh
|--------------------------------------------------------------------------
*/

async function refreshReservations()
{
    await loadReservations()
}


/*
|--------------------------------------------------------------------------
| Filter
|--------------------------------------------------------------------------
*/

async function applyFilters()
{
    filters.page =
        1

    await loadReservations()
}


async function resetFilters()
{
    filters.search =
        ''

    filters.status =
        ''

    filters.reservation_date =
        ''

    filters.date_from =
        ''

    filters.date_to =
        ''

    filters.sort =
        'latest'

    filters.page =
        1


    await loadReservations()
}


/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/

async function goToPage(
    page
) {

    const targetPage =
        Number(page)


    if (
        !Number.isInteger(
            targetPage
        )
    ) {
        return
    }


    if (
        targetPage < 1
        ||
        targetPage >
        meta.last_page
    ) {
        return
    }


    filters.page =
        targetPage


    await loadReservations()

}


/*
|--------------------------------------------------------------------------
| Add
|--------------------------------------------------------------------------
*/

function openCreateReservation()
{
    router.push({
        name:
            'reservation-create',
    })
}


/*
|--------------------------------------------------------------------------
| View
|--------------------------------------------------------------------------
*/

function handleViewReservation(
    reservation
) {

    selectedReservation.value =
        {
            ...reservation,
        }


    showViewModal.value =
        true


    emit(
        'view',
        reservation
    )

}


function closeViewModal()
{
    showViewModal.value =
        false

    selectedReservation.value =
        null
}


/*
|--------------------------------------------------------------------------
| Edit
|--------------------------------------------------------------------------
*/

function handleEditReservation(
    reservation
) {

    if (
        !canEditReservation(
            reservation
        )
    ) {
        return
    }


    selectedReservation.value =
        {
            ...reservation,
        }


    showEditModal.value =
        true

}


function closeEditModal()
{
    showEditModal.value =
        false

    selectedReservation.value =
        null
}


async function handleReservationSaved(
    savedMessage
) {

    closeEditModal()

    await loadReservations()

    showToast(
        savedMessage
        ||
        'Reservation updated successfully.'
    )

}


/*
|--------------------------------------------------------------------------
| Cancel
|--------------------------------------------------------------------------
*/

function handleCancelReservation(
    reservation
) {

    if (
        !canCancelReservation(
            reservation
        )
    ) {
        return
    }


    openActionModal(
        'cancel',
        reservation
    )

}


/*
|--------------------------------------------------------------------------
| Seat
|--------------------------------------------------------------------------
*/

function handleSeatReservation(
    reservation
) {

    if (
        !canSeatReservation(
            reservation
        )
    ) {
        return
    }


    openActionModal(
        'seat',
        reservation
    )

}


/*
|--------------------------------------------------------------------------
| Professional Reservation Action Modal
|--------------------------------------------------------------------------
*/

function openActionModal(
    mode,
    reservation
) {

    actionMode.value =
        mode

    actionReservation.value =
        {
            ...reservation,
        }

    actionError.value =
        ''

    showActionModal.value =
        true

}


function closeActionModal()
{
    if (
        actionSubmitting.value
    ) {
        return
    }


    showActionModal.value =
        false

    actionReservation.value =
        null

    actionError.value =
        ''

}


async function confirmReservationAction(
    reason
) {

    if (
        !actionReservation.value?.id
    ) {
        return
    }


    actionSubmitting.value =
        true

    actionError.value =
        ''


    try {

        let successMessage =
            ''


        if (
            actionMode.value ===
            'cancel'
        ) {

            await reservationService
                .cancelReservation(
                    actionReservation.value.id,
                    String(
                        reason
                        ||
                        ''
                    ).trim()
                    ||
                    null
                )


            successMessage =
                'Reservation cancelled successfully.'

        }
        else {

            await reservationService
                .seatReservation(
                    actionReservation.value.id
                )


            successMessage =
                'Guests seated successfully.'

        }


        showActionModal.value =
            false

        actionReservation.value =
            null


        await loadReservations()


        showToast(
            successMessage
        )

    }

    catch (error) {

        actionError.value =
            reservationService
                .getReservationErrorMessage(
                    error,
                    actionMode.value ===
                        'cancel'
                        ? 'Unable to cancel reservation.'
                        : 'Unable to seat reservation.'
                )

    }

    finally {

        actionSubmitting.value =
            false

    }

}


/*
|--------------------------------------------------------------------------
| Create Order
|--------------------------------------------------------------------------
*/


function handleCreateOrder(
    reservation
) {

    if (
        !canCreateOrder(
            reservation
        )
    ) {
        return
    }


    router.push({

        name:
            'order-create',

        query: {

            reservation_id:
                reservation.id,

        },

    })

}


/*
|--------------------------------------------------------------------------
| Action Rules
|--------------------------------------------------------------------------
*/

function canEditReservation(
    reservation
) {

    return (
        reservation?.can_edit
        ??
        [
            'reserved',
            'confirmed',
        ].includes(
            reservation?.status
        )
    )

}


function canCancelReservation(
    reservation
) {

    return (
        reservation?.can_cancel
        ??
        [
            'reserved',
            'confirmed',
        ].includes(
            reservation?.status
        )
    )

}


function canSeatReservation(
    reservation
) {

    return [
        'reserved',
        'confirmed',
    ].includes(
        reservation?.status
    )

}


function canCreateOrder(
    reservation
) {

    return (
        reservation?.status ===
        'seated'
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


    if (!name) {
        return 'C'
    }


    return name
        .charAt(
            0
        )
        .toUpperCase()

}


/*
|--------------------------------------------------------------------------
| Date
|--------------------------------------------------------------------------
*/

function formatNumber(value) {
    const number = Number(value || 0)
    return Number.isInteger(number) && Math.abs(number) < 10
        ? String(number).padStart(2, '0')
        : number.toLocaleString('en-BD')
}


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


    return parsedDate.toLocaleDateString(
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
| Status Text
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
            /_/g,
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
| Mounted
|--------------------------------------------------------------------------
*/

onMounted(
    async () => {

        document.addEventListener('click', handleFilterDocumentClick)

        await loadReservations()


        if (
            route.query.notice ===
            'created'
        ) {

            showToast(
                'Reservation created successfully.'
            )


            const nextQuery =
                {
                    ...route.query,
                }


            delete nextQuery.notice


            await router.replace({
                query:
                    nextQuery,
            })

        }

    }
)


/*
|--------------------------------------------------------------------------
| Cleanup
|--------------------------------------------------------------------------
*/

onBeforeUnmount(
    () => {

        document.removeEventListener('click', handleFilterDocumentClick)

        if (toastTimer) {

            clearTimeout(
                toastTimer
            )

        }

    }
)

</script>


<style
    src="@/assets/css/reservation/reservation-list.css"
></style>
