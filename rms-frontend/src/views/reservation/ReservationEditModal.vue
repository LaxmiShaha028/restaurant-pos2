<template>

<div
    v-if="show"
    class="reservation-modal-overlay"
    @mousedown.self="closeModal"
>

    <div class="reservation-modal reservation-edit-modal" @click="handleEditModalClick" @keydown.esc="closePickers">


        <!-- ============================================================
             HEADER
        ============================================================= -->

        <div class="reservation-modal-header">

            <div>

                <span class="reservation-modal-eyebrow">
                    Edit Reservation
                </span>

                <h3>

                    <i class="bi bi-pencil"></i>

                    {{
                        reservation?.reservation_no
                        ||
                        'Reservation'
                    }}

                </h3>

                <p>
                    Update customer, schedule, guests and table assignment
                </p>

            </div>


            <button
                type="button"
                class="reservation-modal-close"
                :disabled="saving"
                @click="closeModal"
            >

                <i class="bi bi-x-lg"></i>

            </button>

        </div>


        <!-- ============================================================
             BODY
        ============================================================= -->

        <form
            @submit.prevent="submitForm"
        >

            <div class="reservation-modal-body">


                <!-- ====================================================
                     GENERAL ERROR
                ===================================================== -->

                <div
                    v-if="generalError"
                    class="
                        reservation-form-alert
                        reservation-form-alert-error
                    "
                >

                    <i class="bi bi-exclamation-circle"></i>

                    <span>
                        {{ generalError }}
                    </span>

                </div>


                <!-- ====================================================
                     CUSTOMER
                ===================================================== -->

                <div class="reservation-edit-section">

                    <div class="reservation-edit-section-title">

                        <i class="bi bi-person"></i>

                        Customer

                    </div>


                    <div class="reservation-form-group">

                        <label>
                            Customer
                            <span class="reservation-required">*</span>
                        </label>


                        <div ref="customerDropdownRoot" class="reservation-customer-picker">
                            <button
                                type="button"
                                class="reservation-customer-trigger"
                                :class="{ 'reservation-input-error': hasError('customer_id') }"
                                :disabled="customersLoading || saving"
                                :aria-expanded="customerDropdownOpen"
                                aria-haspopup="listbox"
                                @click.stop="toggleCustomerDropdown"
                            >
                                <span :class="{ 'is-placeholder': !form.customer_id }">{{ selectedCustomerLabel || 'Select Customer' }}</span>
                                <i class="bi bi-chevron-down" aria-hidden="true"></i>
                            </button>
                            <Transition name="reservation-customer-menu">
                                <div v-if="customerDropdownOpen" class="reservation-customer-menu">
                                    <input v-model="customerSearchQuery" type="search" class="reservation-customer-search" placeholder="Search customers..." aria-label="Search customers" @click.stop />
                                    <div class="reservation-customer-options" role="listbox" aria-label="Customers">
                                        <button type="button" class="reservation-customer-option" :class="{ 'is-selected': !form.customer_id }" role="option" :aria-selected="!form.customer_id" @click="selectCustomer('')">Select Customer</button>
                                        <button v-for="customer in filteredCustomers" :key="customer.id" type="button" class="reservation-customer-option" :class="{ 'is-selected': form.customer_id === String(customer.id) }" role="option" :aria-selected="form.customer_id === String(customer.id)" @click="selectCustomer(String(customer.id))">
                                            <span>{{ customer.name }}<small v-if="customer.phone">{{ customer.phone }}</small></span>
                                            <i v-if="form.customer_id === String(customer.id)" class="bi bi-check2" aria-hidden="true"></i>
                                        </button>
                                        <p v-if="filteredCustomers.length === 0" class="reservation-customer-empty">No customers found.</p>
                                    </div>
                                </div>
                            </Transition>
                        </div>

                        <select
                            class="reservation-native-select"
                            tabindex="-1"
                            aria-hidden="true"
                            v-model="form.customer_id"
                            :disabled="customersLoading || saving"
                            :class="{
                                'reservation-input-error':
                                    hasError('customer_id'),
                            }"
                        >

                            <option value="">
                                Select Customer
                            </option>


                            <option
                                v-for="customer in customers"
                                :key="customer.id"
                                :value="String(customer.id)"
                            >

                                {{
                                    customer.name
                                }}

                                <template
                                    v-if="customer.phone"
                                >

                                    —
                                    {{ customer.phone }}

                                </template>

                            </option>

                        </select>


                        <small
                            v-if="hasError('customer_id')"
                            class="reservation-field-error"
                        >
                            {{ firstError('customer_id') }}
                        </small>

                    </div>

                </div>


                <!-- ====================================================
                     DATE / TIME
                ===================================================== -->

                <div class="reservation-edit-section">

                    <div class="reservation-edit-section-title">

                        <i class="bi bi-calendar-event"></i>

                        Reservation Schedule

                    </div>


                    <div class="reservation-form-grid">


                        <!-- Date -->

                        <div class="reservation-form-group">

                            <label>

                                Reservation Date

                                <span class="reservation-required">
                                    *
                                </span>

                            </label>


                            <div ref="reservationDatePickerRoot" class="reservation-edit-date-picker">
                                <button
                                    type="button"
                                    class="reservation-edit-date-trigger"
                                    :class="{ 'reservation-input-error': hasError('reservation_date') }"
                                    :disabled="saving"
                                    :aria-expanded="reservationCalendarOpen"
                                    aria-haspopup="dialog"
                                    @click.stop="toggleReservationCalendar"
                                >
                                    <span>{{ formatEditDate(form.reservation_date) }}</span>
                                    <i class="bi bi-calendar3" aria-hidden="true"></i>
                                </button>
                                <Transition name="reservation-edit-calendar">
                                    <div v-if="reservationCalendarOpen" class="reservation-edit-calendar" role="dialog" aria-label="Choose reservation date">
                                        <div class="reservation-edit-calendar-heading">
                                            <strong>{{ calendarMonthLabel }}</strong>
                                            <div>
                                                <button type="button" aria-label="Previous month" @click="moveCalendar(-1)"><i class="bi bi-chevron-left" aria-hidden="true"></i></button>
                                                <button type="button" aria-label="Next month" @click="moveCalendar(1)"><i class="bi bi-chevron-right" aria-hidden="true"></i></button>
                                            </div>
                                        </div>
                                        <div class="reservation-edit-calendar-weekdays"><span v-for="day in calendarWeekdays" :key="day">{{ day }}</span></div>
                                        <div class="reservation-edit-calendar-days">
                                            <button v-for="day in calendarDays" :key="day.key" type="button" :class="{ 'is-outside': !day.isCurrentMonth, 'is-selected': day.key === form.reservation_date, 'is-today': day.key === todayDate }" :disabled="day.key < todayDate || saving" :aria-label="day.ariaLabel" :aria-pressed="day.key === form.reservation_date" @click="selectCalendarDate(day.key)">{{ day.day }}</button>
                                        </div>
                                        <div class="reservation-edit-calendar-footer">
                                            <button type="button" @click="clearCalendarDate">Clear</button>
                                            <button type="button" @click="selectCalendarDate(todayDate)">Today</button>
                                        </div>
                                    </div>
                                </Transition>
                            </div>

                            <input
                                class="reservation-native-date"
                                tabindex="-1"
                                aria-hidden="true"
                                v-model="form.reservation_date"
                                type="date"
                                :min="todayDate"
                                :disabled="saving"
                                :class="{
                                    'reservation-input-error':
                                        hasError(
                                            'reservation_date'
                                        ),
                                }"
                                @change="refreshAvailableTables"
                            />


                            <small
                                v-if="
                                    hasError(
                                        'reservation_date'
                                    )
                                "
                                class="reservation-field-error"
                            >

                                {{
                                    firstError(
                                        'reservation_date'
                                    )
                                }}

                            </small>

                        </div>


                        <!-- Start -->

                        <div class="reservation-form-group">

                            <label>

                                Start Time

                                <span class="reservation-required">
                                    *
                                </span>

                            </label>


                            <ReservationTimePicker
                                v-model="form.start_time"
                                :disabled="saving"
                                :invalid="hasError('start_time')"
                                placeholder="Select start time"
                                @change="refreshAvailableTables"
                            />


                            <small
                                v-if="
                                    hasError(
                                        'start_time'
                                    )
                                "
                                class="reservation-field-error"
                            >

                                {{
                                    firstError(
                                        'start_time'
                                    )
                                }}

                            </small>

                        </div>


                        <!-- End -->

                        <div class="reservation-form-group">

                            <label>

                                End Time

                                <span class="reservation-required">
                                    *
                                </span>

                            </label>


                            <ReservationTimePicker
                                v-model="form.end_time"
                                :disabled="saving"
                                :invalid="hasError('end_time')"
                                placeholder="Select end time"
                                @change="refreshAvailableTables"
                            />


                            <small
                                v-if="
                                    hasError(
                                        'end_time'
                                    )
                                "
                                class="reservation-field-error"
                            >

                                {{
                                    firstError(
                                        'end_time'
                                    )
                                }}

                            </small>

                        </div>

                    </div>


                    <!-- Duration -->

                    <div
                        v-if="durationLabel"
                        class="reservation-duration-preview"
                    >

                        <i class="bi bi-clock"></i>

                        <span>
                            Reservation Duration
                        </span>

                        <strong>
                            {{ durationLabel }}
                        </strong>

                    </div>

                </div>


                <!-- ====================================================
                     GUEST COUNT
                ===================================================== -->

                <div class="reservation-edit-section">

                    <div class="reservation-edit-section-title">

                        <i class="bi bi-people"></i>

                        Guest Information

                    </div>


                    <div class="reservation-form-group">

                        <label>

                            Guest Count

                            <span class="reservation-required">
                                *
                            </span>

                        </label>


                        <input
                            v-model.number="form.guest_count"
                            type="number"
                            min="1"
                            max="1000"
                            step="1"
                            :disabled="saving"
                            :class="{
                                'reservation-input-error':
                                    hasError(
                                        'guest_count'
                                    ),
                            }"
                            @input="refreshAvailableTablesDebounced"
                            @change="refreshAvailableTables"
                        />


                        <small
                            v-if="
                                hasError(
                                    'guest_count'
                                )
                            "
                            class="reservation-field-error"
                        >

                            {{
                                firstError(
                                    'guest_count'
                                )
                            }}

                        </small>

                    </div>

                </div>


                <!-- ====================================================
                     TABLES
                ===================================================== -->

                <div class="reservation-edit-section">

                    <div
                        class="
                            reservation-edit-section-title
                            reservation-edit-section-title-between
                        "
                    >

                        <div>

                            <i class="bi bi-grid-3x3-gap"></i>

                            Table Assignment

                        </div>


                        <span
                            v-if="tablesLoading"
                            class="
                                reservation-inline-loading
                            "
                        >

                            <span
                                class="spinner-border spinner-border-sm"
                            ></span>

                            Checking availability...

                        </span>

                    </div>


                    <div
                        v-if="
                            hasError(
                                'table_ids'
                            )
                        "
                        class="
                            reservation-form-alert
                            reservation-form-alert-error
                        "
                    >

                        <i class="bi bi-exclamation-circle"></i>

                        <span>

                            {{
                                firstError(
                                    'table_ids'
                                )
                            }}

                        </span>

                    </div>


                    <div
                        v-if="
                            !tablesLoading
                            &&
                            availableTables.length === 0
                        "
                        class="reservation-no-tables"
                    >

                        <i class="bi bi-calendar-x"></i>

                        <strong>
                            No tables available
                        </strong>

                        <span>
                            Try changing the reservation date,
                            time or guest count.
                        </span>

                    </div>


                    <div
                        v-else
                        class="reservation-edit-table-grid"
                    >

                        <label
                            v-for="table in availableTables"
                            :key="table.id"
                            class="
                                reservation-edit-table-option
                            "
                            :class="{
                                selected:
                                    isTableSelected(table.id),

                                current:
                                    table.is_current,

                                disabled:
                                    isTableDisabled(table),
                            }"
                        >

                            <input
                                type="checkbox"
                                :value="Number(table.id)"
                                :checked="
                                    isTableSelected(
                                        table.id
                                    )
                                "
                                :disabled="
                                    saving
                                    ||
                                    isTableDisabled(table)
                                "
                                @change="
                                    toggleTable(
                                        table
                                    )
                                "
                            />


                            <div
                                class="
                                    reservation-edit-table-check
                                "
                            >

                                <i class="bi bi-check-lg"></i>

                            </div>


                            <div
                                class="
                                    reservation-edit-table-content
                                "
                            >

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
                                        ||
                                        0
                                    }}

                                    persons

                                </span>


                                <small
                                    v-if="table.section"
                                >

                                    {{ table.section }}

                                </small>


                                <small
                                    v-if="table.is_current"
                                    class="
                                        reservation-current-table-badge
                                    "
                                >

                                    Current

                                </small>

                            </div>

                        </label>

                    </div>


                    <!-- Selected Summary -->

                    <div
                        v-if="selectedTableIds.length"
                        class="
                            reservation-selected-table-summary
                        "
                    >

                        <div>

                            <span>
                                Selected Tables
                            </span>

                            <strong>
                                {{ selectedTableIds.length }}
                            </strong>

                        </div>


                        <div>

                            <span>
                                Combined Capacity
                            </span>

                            <strong>

                                {{
                                    selectedTablesCapacity
                                }}

                            </strong>

                        </div>


                        <div>

                            <span>
                                Guests
                            </span>

                            <strong>

                                {{
                                    Number(
                                        form.guest_count
                                        ||
                                        0
                                    )
                                }}

                            </strong>

                        </div>

                    </div>

                </div>


                <!-- ====================================================
                     STATUS
                ===================================================== -->

                <div class="reservation-edit-section">

                    <div class="reservation-edit-section-title">

                        <i class="bi bi-flag"></i>

                        Reservation Status

                    </div>


                    <div class="reservation-form-group">

                        <label>
                            Status
                        </label>


                        <select
                            v-model="form.status"
                            :disabled="saving"
                        >

                            <option value="reserved">
                                Reserved
                            </option>

                            <option value="confirmed">
                                Confirmed
                            </option>

                            <option value="seated">
                                Seated
                            </option>

                        </select>


                        <small
                            class="
                                reservation-field-help
                            "
                        >

                            Seated reservations are normally managed
                            through the seating flow.

                        </small>

                    </div>

                </div>


                <!-- ====================================================
                     ADVANCE
                ===================================================== -->

                <div class="reservation-edit-section">

                    <div class="reservation-edit-section-title">

                        <i class="bi bi-cash-stack"></i>

                        Payment

                    </div>


                    <div class="reservation-form-group">

                        <label>
                            Advance Amount
                        </label>


                        <div
                            class="
                                reservation-money-input
                            "
                        >

                            <span>
                                ৳
                            </span>

                            <input
                                v-model.number="
                                    form.advance_amount
                                "
                                type="number"
                                min="0"
                                step="0.01"
                                :disabled="saving"
                                :class="{
                                    'reservation-input-error':
                                        hasError(
                                            'advance_amount'
                                        ),
                                }"
                            />

                        </div>


                        <small
                            v-if="
                                hasError(
                                    'advance_amount'
                                )
                            "
                            class="reservation-field-error"
                        >

                            {{
                                firstError(
                                    'advance_amount'
                                )
                            }}

                        </small>

                    </div>

                </div>


                <!-- ====================================================
                     NOTE
                ===================================================== -->

                <div class="reservation-edit-section">

                    <div class="reservation-edit-section-title">

                        <i class="bi bi-journal-text"></i>

                        Notes

                    </div>


                    <div class="reservation-form-group">

                        <textarea
                            v-model.trim="form.note"
                            rows="4"
                            maxlength="1000"
                            :disabled="saving"
                            placeholder="Optional reservation note"
                        ></textarea>


                        <div
                            class="
                                reservation-note-meta
                            "
                        >

                            <small>
                                Optional
                            </small>

                            <small>
                                {{ form.note.length }}/1000
                            </small>

                        </div>


                        <small
                            v-if="hasError('note')"
                            class="reservation-field-error"
                        >

                            {{
                                firstError(
                                    'note'
                                )
                            }}

                        </small>

                    </div>

                </div>

            </div>


            <!-- ========================================================
                 FOOTER
            ========================================================= -->

            <div class="reservation-modal-footer">

                <button
                    type="button"
                    class="btn btn-light"
                    :disabled="saving"
                    @click="closeModal"
                >

                    <i class="bi bi-x-lg"></i>

                    Cancel

                </button>


                <button
                    type="submit"
                    class="btn btn-primary"
                    :disabled="
                        saving
                        ||
                        tablesLoading
                        ||
                        selectedTableIds.length === 0
                    "
                >

                    <span
                        v-if="saving"
                        class="spinner-border spinner-border-sm"
                    ></span>

                    <i
                        v-else
                        class="bi bi-check-lg"
                    ></i>

                    {{
                        saving
                            ? 'Updating...'
                            : 'Update Reservation'
                    }}

                </button>

            </div>

        </form>

    </div>

</div>

</template>


<script setup>

import {
    computed,
    ref,
    watch,
} from 'vue'

import ReservationTimePicker
    from '@/components/reservation/ReservationTimePicker.vue'

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
            type:
                Boolean,

            default:
                false,
        },

        reservation: {
            type:
                Object,

            default:
                null,
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
        'saved',
    ])


/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const customers =
    ref([])

const customersLoading =
    ref(false)

const tablesLoading =
    ref(false)

const saving =
    ref(false)

const generalError =
    ref('')

const validationErrors =
    ref({})

const availableTables =
    ref([])

const selectedTableIds =
    ref([])

const customerDropdownRoot = ref(null)
const customerDropdownOpen = ref(false)
const customerSearchQuery = ref('')
const reservationDatePickerRoot = ref(null)
const reservationCalendarOpen = ref(false)
const calendarMonth = ref(new Date())

const selectedCustomerLabel = computed(() => {
    const customer = customers.value.find(
        item => String(item.id) === String(form.value.customer_id)
    )
    if (!customer) return ''
    return customer.phone
        ? `${customer.name} — ${customer.phone}`
        : customer.name
})

const filteredCustomers = computed(() => {
    const query = customerSearchQuery.value.trim().toLowerCase()
    if (!query) return customers.value
    return customers.value.filter(customer =>
        `${customer.name || ''} ${customer.phone || ''}`.toLowerCase().includes(query)
    )
})

const calendarWeekdays = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa']
const calendarMonthLabel = computed(() => calendarMonth.value.toLocaleDateString(undefined, {
    month: 'long',
    year: 'numeric',
}))
const calendarDays = computed(() => {
    const year = calendarMonth.value.getFullYear()
    const month = calendarMonth.value.getMonth()
    const first = new Date(year, month, 1)
    const start = new Date(year, month, 1 - first.getDay())

    return Array.from({ length: 42 }, (_, index) => {
        const date = new Date(start.getFullYear(), start.getMonth(), start.getDate() + index)
        const key = localDateKey(date)
        return {
            key,
            day: date.getDate(),
            isCurrentMonth: date.getMonth() === month,
            ariaLabel: date.toLocaleDateString(undefined, {
                weekday: 'long', month: 'long', day: 'numeric', year: 'numeric',
            }),
        }
    })
})


/*
|--------------------------------------------------------------------------
| Date
|--------------------------------------------------------------------------
*/

function getLocalDate()
{
    const date =
        new Date()


    const year =
        date.getFullYear()


    const month =
        String(
            date.getMonth() + 1
        ).padStart(
            2,
            '0'
        )


    const day =
        String(
            date.getDate()
        ).padStart(
            2,
            '0'
        )


    return `${year}-${month}-${day}`
}


const todayDate =
    getLocalDate()

function localDateKey(date)
{
    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')
    return `${year}-${month}-${day}`
}

function formatEditDate(value)
{
    if (!value) return 'Select a date'
    const [year, month, day] = String(value).slice(0, 10).split('-').map(Number)
    if (!year || !month || !day) return 'Select a date'
    return new Date(year, month - 1, day).toLocaleDateString(undefined, {
        month: '2-digit', day: '2-digit', year: 'numeric',
    })
}

function toggleCustomerDropdown()
{
    reservationCalendarOpen.value = false
    customerDropdownOpen.value = !customerDropdownOpen.value
    if (customerDropdownOpen.value) customerSearchQuery.value = ''
}

function selectCustomer(customerId)
{
    form.value.customer_id = customerId
    const nextErrors = { ...validationErrors.value }
    delete nextErrors.customer_id
    validationErrors.value = nextErrors
    customerDropdownOpen.value = false
    customerSearchQuery.value = ''
}

function toggleReservationCalendar()
{
    customerDropdownOpen.value = false
    if (!reservationCalendarOpen.value) {
        const value = form.value.reservation_date || todayDate
        const [year, month] = String(value).slice(0, 10).split('-').map(Number)
        const date = year && month ? new Date(year, month - 1, 1) : new Date()
        calendarMonth.value = date
    }
    reservationCalendarOpen.value = !reservationCalendarOpen.value
}

function moveCalendar(offset)
{
    calendarMonth.value = new Date(
        calendarMonth.value.getFullYear(),
        calendarMonth.value.getMonth() + offset,
        1,
    )
}

function selectCalendarDate(dateKey)
{
    if (dateKey < todayDate) return
    form.value.reservation_date = dateKey
    const nextErrors = { ...validationErrors.value }
    delete nextErrors.reservation_date
    validationErrors.value = nextErrors
    reservationCalendarOpen.value = false
    refreshAvailableTables()
}

function clearCalendarDate()
{
    form.value.reservation_date = ''
    reservationCalendarOpen.value = false
    const nextErrors = { ...validationErrors.value }
    delete nextErrors.reservation_date
    validationErrors.value = nextErrors
    refreshAvailableTables()
}

function handleEditModalClick(event)
{
    if (!customerDropdownRoot.value?.contains(event.target)) {
        customerDropdownOpen.value = false
    }
    if (!reservationDatePickerRoot.value?.contains(event.target)) {
        reservationCalendarOpen.value = false
    }
}

function closePickers()
{
    customerDropdownOpen.value = false
    reservationCalendarOpen.value = false
}


/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/

function createDefaultForm()
{
    return {

        customer_id:
            '',

        reservation_date:
            todayDate,

        start_time:
            '18:00',

        end_time:
            '20:00',

        guest_count:
            1,

        table_ids:
            [],

        advance_amount:
            0,

        status:
            'reserved',

        note:
            '',

    }
}


const form =
    ref(
        createDefaultForm()
    )


/*
|--------------------------------------------------------------------------
| Duration
|--------------------------------------------------------------------------
*/

function timeToMinutes(
    time
) {

    if (!time) {
        return null
    }


    const [
        hours,
        minutes,
    ] =
        String(time)
            .split(':')
            .map(Number)


    if (
        Number.isNaN(hours)
        ||
        Number.isNaN(minutes)
    ) {
        return null
    }


    return (
        hours * 60
        +
        minutes
    )

}


const durationLabel =
    computed(() => {

        const start =
            timeToMinutes(
                form.value.start_time
            )

        let end =
            timeToMinutes(
                form.value.end_time
            )


        if (
            start === null
            ||
            end === null
            ||
            start === end
        ) {
            return ''
        }


        if (end < start) {
            end += 24 * 60
        }


        const total =
            end - start


        const hours =
            Math.floor(
                total / 60
            )


        const minutes =
            total % 60


        if (
            hours > 0
            &&
            minutes > 0
        ) {

            return `${hours}h ${minutes}m`

        }


        if (hours > 0) {

            return `${hours}h`

        }


        return `${minutes}m`

    })


/*
|--------------------------------------------------------------------------
| Selected Tables
|--------------------------------------------------------------------------
*/

const selectedTablesCapacity =
    computed(() => {

        return availableTables.value
            .filter(
                table =>
                    selectedTableIds.value.includes(
                        Number(table.id)
                    )
            )
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

    })


/*
|--------------------------------------------------------------------------
| Watch Modal
|--------------------------------------------------------------------------
*/

watch(
    () => props.show,
    async (
        isOpen
    ) => {

        if (!isOpen) {
            return
        }

        await initializeForm()

    }
)


/*
|--------------------------------------------------------------------------
| Watch Reservation
|--------------------------------------------------------------------------
*/

watch(
    () => props.reservation,
    async () => {

        if (!props.show) {
            return
        }

        await initializeForm()

    }
)


/*
|--------------------------------------------------------------------------
| Initialize
|--------------------------------------------------------------------------
*/

async function initializeForm()
{
    resetState()


    if (!props.reservation) {

        return

    }


    const reservation =
        props.reservation


    form.value = {

        customer_id:
            reservation.customer_id
            ? String(
                reservation.customer_id
            )
            : '',

        reservation_date:
            reservation.reservation_date
            ||
            todayDate,

        start_time:
            reservation.start_time
            ||
            '18:00',

        end_time:
            reservation.end_time
            ||
            '20:00',

        guest_count:
            Number(
                reservation.guest_count
                ||
                1
            ),

        table_ids:
            Array.isArray(
                reservation.table_ids
            )
                ? reservation.table_ids
                    .map(
                        Number
                    )
                : Array.isArray(
                    reservation.tables
                )
                    ? reservation.tables
                        .map(
                            table =>
                                Number(
                                    table.id
                                )
                        )
                    : [],

        advance_amount:
            Number(
                reservation.advance_amount
                ||
                0
            ),

        status:
            reservation.status
            &&
            [
                'reserved',
                'confirmed',
                'seated',
            ].includes(
                reservation.status
            )
                ? reservation.status
                : 'reserved',

        note:
            reservation.note
            ||
            '',

    }


    selectedTableIds.value =
        [
            ...form.value.table_ids,
        ]


    await Promise.all([
        loadCustomers(),
        loadEditOptions(),
    ])

}


/*
|--------------------------------------------------------------------------
| Reset
|--------------------------------------------------------------------------
*/

function resetState()
{
    closePickers()
    customerSearchQuery.value = ''

    customers.value =
        []

    availableTables.value =
        []

    selectedTableIds.value =
        []

    customersLoading.value =
        false

    tablesLoading.value =
        false

    saving.value =
        false

    generalError.value =
        ''

    validationErrors.value =
        {}

    form.value =
        createDefaultForm()
}


/*
|--------------------------------------------------------------------------
| Load Customers
|--------------------------------------------------------------------------
*/

async function loadCustomers()
{
    customersLoading.value =
        true


    try {

        const response =
            await reservationService
                .getCustomers()


        const payload =
            response?.data
            ??
            response


        if (
            Array.isArray(
                payload?.data
            )
        ) {

            customers.value =
                payload.data

        }
        else if (
            Array.isArray(
                payload
            )
        ) {

            customers.value =
                payload

        }
        else {

            customers.value =
                []

        }

    }

    catch (error) {

        customers.value =
            []

        generalError.value =
            reservationService
                .getReservationErrorMessage(
                    error,
                    'Unable to load customers.'
                )

    }

    finally {

        customersLoading.value =
            false

    }
}


/*
|--------------------------------------------------------------------------
| Load Edit Options
|--------------------------------------------------------------------------
*/

async function loadEditOptions()
{
    if (
        !props.reservation?.id
    ) {
        return
    }


    tablesLoading.value =
        true


    try {

        const response =
            await reservationService
                .getReservationEditOptions(
                    props.reservation.id,
                    {

                        reservation_date:
                            form.value
                                .reservation_date,

                        start_time:
                            form.value
                                .start_time,

                        end_time:
                            form.value
                                .end_time,

                        guest_count:
                            Number(
                                form.value
                                    .guest_count
                            ),

                    }
                )


        const payload =
            response?.data
            ??
            response


        const data =
            payload?.data
            ??
            payload


        availableTables.value =
            Array.isArray(
                data?.available_tables
            )
                ? data.available_tables
                : []


        /*
        |--------------------------------------------------------------------------
        | Current table IDs
        |--------------------------------------------------------------------------
        */

        const currentIds =
            Array.isArray(
                data?.current_table_ids
            )
                ? data.current_table_ids
                    .map(
                        Number
                    )
                : []


        /*
        |--------------------------------------------------------------------------
        | Keep only valid/current visible selections
        |--------------------------------------------------------------------------
        */

        const visibleIds =
            new Set(
                availableTables.value
                    .map(
                        table =>
                            Number(
                                table.id
                            )
                    )
            )


        selectedTableIds.value =
            currentIds.filter(
                id =>
                    visibleIds.has(id)
            )


        form.value.table_ids =
            [
                ...selectedTableIds.value,
            ]

    }

    catch (error) {

        availableTables.value =
            []

        generalError.value =
            reservationService
                .getReservationErrorMessage(
                    error,
                    'Unable to load reservation edit options.'
                )

    }

    finally {

        tablesLoading.value =
            false

    }
}


/*
|--------------------------------------------------------------------------
| Refresh Available Tables
|--------------------------------------------------------------------------
*/

let refreshTimer = null


async function refreshAvailableTables()
{
    validationErrors.value =
        {}

    if (
        !form.value.reservation_date
        ||
        !form.value.start_time
        ||
        !form.value.end_time
        ||
        Number(
            form.value.guest_count
        ) < 1
    ) {
        return
    }


    if (
        props.reservation?.id
    ) {

        await loadEditOptions()

        return
    }
}


function refreshAvailableTablesDebounced()
{
    if (refreshTimer) {
        clearTimeout(
            refreshTimer
        )
    }


    refreshTimer =
        setTimeout(
            () => {

                refreshAvailableTables()

            },
            350
        )
}


/*
|--------------------------------------------------------------------------
| Table Selection
|--------------------------------------------------------------------------
*/

function isTableSelected(
    tableId
) {

    return selectedTableIds.value
        .includes(
            Number(tableId)
        )

}


function isTableDisabled(
    table
) {

    if (
        table?.is_current
    ) {
        return false
    }


    /*
    |--------------------------------------------------------------------------
    | A table returned by backend is available.
    |--------------------------------------------------------------------------
    */

    return false

}


function toggleTable(
    table
) {

    const id =
        Number(
            table.id
        )


    if (
        selectedTableIds.value
            .includes(id)
    ) {

        selectedTableIds.value =
            selectedTableIds.value
                .filter(
                    tableId =>
                        tableId !== id
                )

    }

    else {

        selectedTableIds.value =
            [
                ...selectedTableIds.value,
                id,
            ]

    }


    form.value.table_ids =
        [
            ...selectedTableIds.value,
        ]

}


/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

function validateClientForm()
{
    const errors = {}


    if (
        !form.value.customer_id
    ) {

        errors.customer_id = [
            'Please select a customer.',
        ]

    }


    if (
        !form.value.reservation_date
    ) {

        errors.reservation_date = [
            'Please select a reservation date.',
        ]

    }


    if (
        !form.value.start_time
    ) {

        errors.start_time = [
            'Please select a reservation start time.',
        ]

    }


    if (
        !form.value.end_time
    ) {

        errors.end_time = [
            'Please select a reservation end time.',
        ]

    }


    const guestCount =
        Number(
            form.value.guest_count
        )


    if (
        !Number.isInteger(
            guestCount
        )
        ||
        guestCount < 1
    ) {

        errors.guest_count = [
            'Guest count must be at least 1.',
        ]

    }


    if (
        selectedTableIds.value.length === 0
    ) {

        errors.table_ids = [
            'Please select at least one table.',
        ]

    }


    if (
        form.value.advance_amount !== ''
        &&
        (
            Number.isNaN(
                Number(
                    form.value.advance_amount
                )
            )
            ||
            Number(
                form.value.advance_amount
            ) < 0
        )
    ) {

        errors.advance_amount = [
            'Advance amount cannot be negative.',
        ]

    }


    if (
        form.value.start_time
        &&
        form.value.end_time
        &&
        form.value.start_time ===
        form.value.end_time
    ) {

        errors.end_time = [
            'End time must be different from start time.',
        ]

    }


    /*
    |--------------------------------------------------------------------------
    | Today start time protection
    |--------------------------------------------------------------------------
    */

    if (
        form.value.reservation_date ===
        todayDate
        &&
        form.value.start_time
    ) {

        const start =
            new Date(
                `${form.value.reservation_date}T${form.value.start_time}:00`
            )


        if (
            start <=
            new Date()
        ) {

            errors.start_time = [
                'Reservation start time must be in the future.',
            ]

        }

    }


    validationErrors.value =
        errors


    return (
        Object.keys(
            errors
        ).length === 0
    )

}


/*
|--------------------------------------------------------------------------
| Error Helpers
|--------------------------------------------------------------------------
*/

function hasError(
    field
) {

    return Boolean(
        validationErrors.value
            ?.[field]
            ?.length
    )

}


function firstError(
    field
) {

    return (
        validationErrors.value
            ?.[field]
            ?.[0]
        ??
        ''
    )

}


/*
|--------------------------------------------------------------------------
| Submit
|--------------------------------------------------------------------------
*/

async function submitForm()
{
    generalError.value =
        ''

    validationErrors.value =
        {}


    if (
        !validateClientForm()
    ) {
        return
    }


    if (
        !props.reservation?.id
    ) {

        generalError.value =
            'No reservation was selected.'

        return

    }


    saving.value =
        true


    try {

        const payload = {

            customer_id:
                Number(
                    form.value.customer_id
                ),

            reservation_date:
                form.value.reservation_date,

            start_time:
                form.value.start_time,

            end_time:
                form.value.end_time,

            guest_count:
                Number(
                    form.value.guest_count
                ),

            table_ids:
                [
                    ...selectedTableIds.value,
                ],

            advance_amount:
                Number(
                    form.value.advance_amount
                    ||
                    0
                ),

            status:
                form.value.status
                ||
                'reserved',

            note:
                form.value.note
                ||
                null,

        }


        const response =
            await reservationService
                .updateReservation(
                    props.reservation.id,
                    payload
                )


        const responseBody =
            response?.data
            ??
            response


        emit(
            'saved',
            responseBody?.message
            ??
            'Reservation updated successfully.'
        )

    }

    catch (error) {

        validationErrors.value =
            error?.response
                ?.data
                ?.errors
            ??
            {}

        generalError.value =
            reservationService
                .getReservationErrorMessage(
                    error,
                    'Unable to update reservation.'
                )

    }

    finally {

        saving.value =
            false

    }

}


/*
|--------------------------------------------------------------------------
| Close
|--------------------------------------------------------------------------
*/

function closeModal()
{
    if (saving.value) {
        return
    }


    emit(
        'close'
    )

}


/*
|--------------------------------------------------------------------------
| Date
|--------------------------------------------------------------------------
*/

function formatDate(
    date
) {

    if (!date) {
        return '-'
    }


    const parsed =
        new Date(
            `${date}T00:00:00`
        )


    if (
        Number.isNaN(
            parsed.getTime()
        )
    ) {
        return date
    }


    return parsed.toLocaleDateString(
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

</script>
<style scoped>

/* =========================================================
   MODAL OVERLAY
========================================================= */

.reservation-modal-overlay {
    position: fixed;
    inset: 0;
    z-index: 9999;

    display: flex;
    align-items: center;
    justify-content: center;

    padding: 20px;

    background: rgba(15, 23, 42, 0.58);

    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);

    overflow: hidden;

    animation: reservationOverlayIn 0.2s ease-out;
}


/* =========================================================
   MAIN MODAL
========================================================= */

.reservation-modal {
    position: relative;

    width: min(100%, 780px);

    /*
     * Important:
     * Modal itself stays fixed within viewport.
     * Only body content will scroll.
     */
    height: min(88vh, 820px);
    max-height: 88vh;

    display: flex;
    flex-direction: column;

    min-height: 0;

    overflow: hidden;

    background: #ffffff;

    border-radius: 20px;

    box-shadow:
        0 25px 60px rgba(15, 23, 42, 0.22),
        0 8px 25px rgba(15, 23, 42, 0.12);

    animation: reservationModalIn 0.25s ease-out;
}

.reservation-edit-modal {
    min-height: 0;
}


/* =========================================================
   HEADER
========================================================= */

.reservation-modal-header {
    flex: 0 0 auto;

    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 18px;

    padding: 22px 28px;

    background: linear-gradient(135deg, #fb923c 0%, #f97316 58%, #ea580c 100%);

    color: #ffffff;
}

.reservation-modal-header > div:first-child {
    min-width: 0;
}

.reservation-modal-eyebrow {
    display: block;

    margin-bottom: 5px;

    font-size: 11px;
    font-weight: 700;

    line-height: 1.3;

    letter-spacing: 0.09em;
    text-transform: uppercase;

    color: rgba(255, 255, 255, 0.78);
}

.reservation-modal-header h3 {
    margin: 0;

    display: flex;
    align-items: center;

    gap: 9px;

    font-size: 23px;
    font-weight: 750;

    line-height: 1.2;

    letter-spacing: -0.02em;

    color: #ffffff;
}

.reservation-modal-header h3 i {
    font-size: 20px;
}

.reservation-modal-header p {
    margin: 7px 0 0;

    font-size: 13px;
    line-height: 1.45;

    color: rgba(255, 255, 255, 0.85);
}


/* =========================================================
   CLOSE BUTTON
========================================================= */

.reservation-modal-close {
    flex: 0 0 auto;

    width: 48px;
    height: 48px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    margin-left: 10px;

    border: 0;
    border-radius: 50%;

    background: rgba(255, 255, 255, 0.14);

    color: #ffffff;

    cursor: pointer;

    transition:
        background 0.2s ease,
        transform 0.2s ease,
        opacity 0.2s ease;
}

.reservation-modal-close:hover {
    background: rgba(255, 255, 255, 0.22);

    transform: scale(1.04);
}

.reservation-modal-close:active {
    transform: scale(0.96);
}

.reservation-modal-close:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.reservation-modal-close i {
    font-size: 18px;
}


/* =========================================================
   FORM
========================================================= */

.reservation-modal form {
    min-height: 0;

    flex: 1 1 auto;

    display: flex;
    flex-direction: column;
}


/* =========================================================
   MODAL BODY
   ONLY THIS AREA SCROLLS
========================================================= */

.reservation-modal-body {
    flex: 1 1 auto;

    min-height: 0;

    height: 0;

    overflow-y: auto;
    overflow-x: hidden;

    padding: 22px 28px 26px;

    background: #f4f1ed;

    overscroll-behavior: contain;

    -webkit-overflow-scrolling: touch;

    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 transparent;
}


/* Chrome / Edge / Safari Scrollbar */

.reservation-modal-body::-webkit-scrollbar {
    width: 7px;
}

.reservation-modal-body::-webkit-scrollbar-track {
    background: transparent;
}

.reservation-modal-body::-webkit-scrollbar-thumb {
    background: #cbd5e1;

    border-radius: 20px;
}

.reservation-modal-body::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}


/* =========================================================
   FORM SECTIONS
========================================================= */

.reservation-edit-section {
    margin-bottom: 16px;

    padding: 20px;

    border: 1px solid #eee3da;

    border-radius: 16px;

    background: #ffffff;

    box-shadow:
        0 2px 8px rgba(15, 23, 42, 0.025);
}

.reservation-edit-section:last-child {
    margin-bottom: 0;
}


/* =========================================================
   SECTION TITLE
========================================================= */

.reservation-edit-section-title {
    display: flex;
    align-items: center;

    gap: 9px;

    margin-bottom: 18px;

    font-size: 16px;
    font-weight: 750;

    line-height: 1.3;

    color: #172033;
}

.reservation-edit-section-title > i {
    width: 31px;
    height: 31px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    flex: 0 0 auto;

    border-radius: 9px;

    background: #fff1e6;

    color: #f97316;

    font-size: 14px;
}

.reservation-edit-section-title-between {
    justify-content: space-between;
}

.reservation-edit-section-title-between > div {
    display: flex;
    align-items: center;

    gap: 9px;
}


/* =========================================================
   FORM GRID
========================================================= */

.reservation-form-grid {
    display: grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    gap: 16px;
}


/* =========================================================
   FORM GROUP
========================================================= */

.reservation-form-group {
    min-width: 0;
}

.reservation-form-group > label {
    display: block;

    margin-bottom: 7px;

    font-size: 13px;
    font-weight: 650;

    line-height: 1.4;

    color: #475569;
}

.reservation-native-select,
.reservation-native-date {
    display: none !important;
}

.reservation-customer-picker,
.reservation-edit-date-picker {
    position: relative;
    width: 100%;
}

.reservation-customer-trigger,
.reservation-edit-date-trigger {
    display: flex;
    width: 100%;
    height: 46px;
    min-height: 46px;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 0 13px;
    border: 1px solid #d8e1ec;
    border-radius: 10px;
    background: #fff;
    color: #172033;
    font: inherit;
    font-size: 14px;
    font-weight: 500;
    text-align: left;
    cursor: pointer;
    transition: border-color .18s ease, box-shadow .18s ease;
}

.reservation-customer-trigger:hover,
.reservation-edit-date-trigger:hover,
.reservation-customer-trigger[aria-expanded="true"],
.reservation-edit-date-trigger[aria-expanded="true"] {
    border-color: #fb923c;
}

.reservation-customer-trigger:focus-visible,
.reservation-edit-date-trigger:focus-visible {
    outline: none;
    border-color: #f97316;
    box-shadow: 0 0 0 3px rgba(249, 115, 22, .14);
}

.reservation-customer-trigger > i,
.reservation-edit-date-trigger > i {
    color: #ea580c;
}

.reservation-customer-trigger .is-placeholder {
    color: #94a3b8;
}

.reservation-customer-trigger:disabled,
.reservation-edit-date-trigger:disabled {
    background: #f1f5f9;
    color: #64748b;
    cursor: not-allowed;
}

.reservation-customer-menu,
.reservation-edit-calendar {
    position: absolute;
    z-index: 80;
    top: calc(100% + 6px);
    left: 0;
    width: 100%;
    padding: 8px;
    border: 1px solid #eee3da;
    border-radius: 11px;
    background: #fff;
    box-shadow: 0 14px 32px rgba(23, 32, 51, .16);
}

.reservation-customer-search {
    width: 100%;
    height: 36px !important;
    min-height: 36px !important;
    margin-bottom: 6px;
    padding: 0 10px !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 7px !important;
    background: #fff !important;
    font-size: 12px !important;
}

.reservation-customer-search:focus {
    border-color: #fb923c !important;
    box-shadow: 0 0 0 3px rgba(249, 115, 22, .1) !important;
}

.reservation-customer-options {
    max-height: 230px;
    overflow-y: auto;
}

.reservation-customer-option {
    display: flex;
    width: 100%;
    min-height: 38px;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 7px 9px;
    border: 0;
    border-radius: 7px;
    background: transparent;
    color: #334155;
    font: inherit;
    font-size: 12px;
    text-align: left;
    cursor: pointer;
}

.reservation-customer-option:hover,
.reservation-customer-option.is-selected {
    background: #fff1e6;
    color: #c2410c;
}

.reservation-customer-option small {
    display: block;
    margin-top: 2px;
    color: #8491a3;
    font-size: 10px;
}

.reservation-customer-option i {
    color: #ea580c;
}

.reservation-customer-empty {
    margin: 0;
    padding: 12px 9px;
    color: #8491a3;
    font-size: 11px;
    text-align: center;
}

.reservation-edit-calendar {
    width: 300px;
    padding: 12px;
}

.reservation-edit-calendar-heading,
.reservation-edit-calendar-heading > div,
.reservation-edit-calendar-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.reservation-edit-calendar-heading {
    margin-bottom: 8px;
    color: #172033;
    font-size: 13px;
}

.reservation-edit-calendar-heading > div {
    gap: 4px;
}

.reservation-edit-calendar-heading button {
    display: grid;
    width: 28px;
    height: 28px;
    place-items: center;
    border: 0;
    border-radius: 7px;
    background: transparent;
    color: #475569;
    cursor: pointer;
}

.reservation-edit-calendar-heading button:hover {
    background: #fff1e6;
    color: #c2410c;
}

.reservation-edit-calendar-weekdays,
.reservation-edit-calendar-days {
    display: grid;
    grid-template-columns: repeat(7, minmax(0, 1fr));
    text-align: center;
}

.reservation-edit-calendar-weekdays {
    margin-bottom: 4px;
    color: #8491a3;
    font-size: 10px;
    font-weight: 700;
}

.reservation-edit-calendar-weekdays span {
    padding: 5px 0;
}

.reservation-edit-calendar-days {
    gap: 2px;
}

.reservation-edit-calendar-days button {
    display: grid;
    min-width: 0;
    aspect-ratio: 1;
    place-items: center;
    border: 1px solid transparent;
    border-radius: 7px;
    background: transparent;
    color: #172033;
    font: inherit;
    font-size: 11px;
    cursor: pointer;
}

.reservation-edit-calendar-days button:hover:not(:disabled) {
    border-color: #fed7aa;
    background: #fff7ed;
    color: #c2410c;
}

.reservation-edit-calendar-days button.is-outside {
    color: #b6c0cd;
}

.reservation-edit-calendar-days button.is-today:not(.is-selected) {
    border-color: #fdba74;
    color: #c2410c;
    font-weight: 700;
}

.reservation-edit-calendar-days button.is-selected {
    background: #f97316;
    color: #fff;
    font-weight: 700;
}

.reservation-edit-calendar-days button:disabled {
    color: #cbd5e1;
    cursor: not-allowed;
}

.reservation-edit-calendar-footer {
    margin-top: 8px;
    padding-top: 8px;
    border-top: 1px solid #f1eee9;
}

.reservation-edit-calendar-footer button {
    padding: 5px 7px;
    border: 0;
    border-radius: 6px;
    background: transparent;
    color: #c2410c;
    font-size: 11px;
    font-weight: 650;
    cursor: pointer;
}

.reservation-edit-calendar-footer button:hover {
    background: #fff1e6;
}

.reservation-customer-menu-enter-active,
.reservation-customer-menu-leave-active,
.reservation-edit-calendar-enter-active,
.reservation-edit-calendar-leave-active {
    transform-origin: top left;
    transition: opacity .14s ease, transform .14s ease;
}

.reservation-customer-menu-enter-from,
.reservation-customer-menu-leave-to,
.reservation-edit-calendar-enter-from,
.reservation-edit-calendar-leave-to {
    opacity: 0;
    transform: translateY(-4px) scale(.98);
}

.reservation-required {
    margin-left: 3px;

    color: #ef4444;
}


/* =========================================================
   INPUT / SELECT / TEXTAREA
========================================================= */

.reservation-form-group input,
.reservation-form-group select,
.reservation-form-group textarea {
    width: 100%;

    border: 1px solid #d8e1ec;

    border-radius: 10px;

    background: #ffffff;

    color: #172033;

    font-size: 14px;
    font-weight: 500;

    outline: none;

    transition:
        border-color 0.2s ease,
        box-shadow 0.2s ease,
        background 0.2s ease;
}

.reservation-form-group input,
.reservation-form-group select {
    height: 46px;

    min-height: 46px;

    padding: 0 13px;
}

.reservation-form-group textarea {
    min-height: 105px;

    padding: 12px 13px;

    resize: vertical;
}

.reservation-form-group input:hover,
.reservation-form-group select:hover,
.reservation-form-group textarea:hover {
    border-color: #b8c5d6;
}

.reservation-form-group input:focus,
.reservation-form-group select:focus,
.reservation-form-group textarea:focus {
    border-color: #f97316;

    box-shadow:
        0 0 0 3px rgba(249, 115, 22, 0.14);

    background: #ffffff;
}

.reservation-form-group input::placeholder,
.reservation-form-group textarea::placeholder {
    color: #94a3b8;
}

.reservation-form-group input:disabled,
.reservation-form-group select:disabled,
.reservation-form-group textarea:disabled {
    background: #f1f5f9;

    color: #64748b;

    cursor: not-allowed;
}


/* =========================================================
   ERROR
========================================================= */

.reservation-input-error {
    border-color: #ef4444 !important;

    box-shadow:
        0 0 0 3px rgba(239, 68, 68, 0.08) !important;
}

.reservation-field-error {
    display: block;

    margin-top: 5px;

    font-size: 11px;

    line-height: 1.4;

    color: #dc2626;
}

.reservation-field-help {
    display: block;

    margin-top: 6px;

    font-size: 11px;

    line-height: 1.45;

    color: #64748b;
}


/* =========================================================
   ALERT
========================================================= */

.reservation-form-alert {
    display: flex;
    align-items: flex-start;

    gap: 9px;

    margin-bottom: 15px;

    padding: 11px 13px;

    border-radius: 10px;

    font-size: 12px;

    line-height: 1.45;
}

.reservation-form-alert-error {
    border: 1px solid #fecaca;

    background: #fef2f2;

    color: #b91c1c;
}

.reservation-form-alert i {
    flex: 0 0 auto;

    margin-top: 1px;
}


/* =========================================================
   DURATION PREVIEW
========================================================= */

.reservation-duration-preview {
    display: flex;
    align-items: center;

    justify-content: space-between;

    gap: 10px;

    margin-top: 13px;

    padding: 11px 13px;

    border: 1px solid #f5ddca;

    border-radius: 10px;

    background: #fff8f1;

    color: #9a3412;
}

.reservation-duration-preview > i {
    font-size: 14px;
}

.reservation-duration-preview > span {
    flex: 1;

    font-size: 12px;

    font-weight: 550;
}

.reservation-duration-preview > strong {
    font-size: 13px;

    font-weight: 750;

    color: #c2410c;
}


/* =========================================================
   MONEY INPUT
========================================================= */

.reservation-money-input {
    display: flex;
    align-items: center;

    height: 46px;

    overflow: hidden;

    border: 1px solid #d8e1ec;

    border-radius: 10px;

    background: #ffffff;

    transition:
        border-color 0.2s ease,
        box-shadow 0.2s ease;
}

.reservation-money-input:focus-within {
    border-color: #f97316;

    box-shadow:
        0 0 0 3px rgba(249, 115, 22, 0.14);
}

.reservation-money-input > span {
    flex: 0 0 auto;

    padding-left: 13px;

    font-size: 15px;
    font-weight: 700;

    color: #f97316;
}

.reservation-money-input input {
    min-width: 0;

    flex: 1;

    height: 44px;

    border: 0 !important;

    border-radius: 0 !important;

    box-shadow: none !important;

    outline: none;
}


/* =========================================================
   TABLE GRID
========================================================= */

.reservation-edit-table-grid {
    display: grid;

    grid-template-columns:
        repeat(auto-fill, minmax(155px, 1fr));

    gap: 10px;
}

.reservation-edit-table-option {
    position: relative;

    min-height: 82px;

    display: flex;
    align-items: flex-start;

    gap: 10px;

    padding: 12px;

    border: 1px solid #dbe3ed;

    border-radius: 12px;

    background: #ffffff;

    cursor: pointer;

    transition:
        border-color 0.2s ease,
        background 0.2s ease,
        box-shadow 0.2s ease,
        transform 0.2s ease;
}

.reservation-edit-table-option:hover {
    transform: translateY(-1px);

    border-color: #fdba74;

    box-shadow:
        0 5px 12px rgba(15, 23, 42, 0.06);
}

.reservation-edit-table-option.selected {
    border-color: #f97316;

    background: #fff1e6;

    box-shadow:
        0 0 0 2px rgba(249, 115, 22, 0.12);
}

.reservation-edit-table-option.current {
    border-color: #fb923c;
}

.reservation-edit-table-option.disabled {
    opacity: 0.55;

    cursor: not-allowed;
}

.reservation-edit-table-option > input {
    position: absolute;

    width: 1px;
    height: 1px;

    opacity: 0;

    pointer-events: none;
}


/* =========================================================
   TABLE CHECKBOX
========================================================= */

.reservation-edit-table-check {
    flex: 0 0 auto;

    width: 20px;
    height: 20px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    margin-top: 1px;

    border: 2px solid #cbd5e1;

    border-radius: 6px;

    background: #ffffff;

    color: #ffffff;

    transition:
        background 0.2s ease,
        border-color 0.2s ease;
}

.reservation-edit-table-option.selected
.reservation-edit-table-check {
    border-color: #f97316;

    background: #f97316;
}

.reservation-edit-table-check i {
    font-size: 11px;

    opacity: 0;
}

.reservation-edit-table-option.selected
.reservation-edit-table-check i {
    opacity: 1;
}


/* =========================================================
   TABLE CONTENT
========================================================= */

.reservation-edit-table-content {
    min-width: 0;

    display: flex;
    flex-direction: column;

    gap: 3px;
}

.reservation-edit-table-content strong {
    font-size: 13px;

    font-weight: 750;

    line-height: 1.35;

    color: #172033;
}

.reservation-edit-table-content span {
    font-size: 11px;

    line-height: 1.35;

    color: #64748b;
}

.reservation-edit-table-content small {
    font-size: 10px;

    line-height: 1.3;

    color: #64748b;
}


/* =========================================================
   CURRENT TABLE BADGE
========================================================= */

.reservation-current-table-badge {
    align-self: flex-start;

    display: inline-flex;

    margin-top: 2px;

    padding: 3px 7px;

    border-radius: 999px;

    background: #dcfce7;

    color: #15803d;

    font-size: 9px !important;

    font-weight: 750;
}


/* =========================================================
   SELECTED TABLE SUMMARY
========================================================= */

.reservation-selected-table-summary {
    display: grid;

    grid-template-columns:
        repeat(3, minmax(0, 1fr));

    gap: 9px;

    margin-top: 14px;

    padding: 12px;

    border: 1px solid #eee3da;

    border-radius: 11px;

    background: #fffaf5;
}

.reservation-selected-table-summary > div {
    display: flex;
    flex-direction: column;

    gap: 2px;
}

.reservation-selected-table-summary span {
    font-size: 10px;

    font-weight: 600;

    color: #64748b;
}

.reservation-selected-table-summary strong {
    font-size: 14px;

    font-weight: 750;

    color: #172033;
}


/* =========================================================
   NO TABLES
========================================================= */

.reservation-no-tables {
    display: flex;
    flex-direction: column;

    align-items: center;
    justify-content: center;

    min-height: 145px;

    padding: 22px;

    border: 1px dashed #f0d9c8;

    border-radius: 13px;

    background: #fffaf5;

    text-align: center;
}

.reservation-no-tables i {
    margin-bottom: 9px;

    font-size: 26px;

    color: #94a3b8;
}

.reservation-no-tables strong {
    font-size: 13px;

    color: #334155;
}

.reservation-no-tables span {
    margin-top: 5px;

    font-size: 11px;

    line-height: 1.45;

    color: #64748b;
}


/* =========================================================
   INLINE LOADING
========================================================= */

.reservation-inline-loading {
    display: inline-flex;
    align-items: center;

    gap: 7px;

    font-size: 11px;

    font-weight: 600;

    color: #64748b;
}

.reservation-inline-loading .spinner-border {
    width: 15px;
    height: 15px;
}


/* =========================================================
   NOTE META
========================================================= */

.reservation-note-meta {
    display: flex;
    align-items: center;

    justify-content: space-between;

    margin-top: 5px;
}

.reservation-note-meta small {
    font-size: 10px;

    color: #94a3b8;
}


/* =========================================================
   FOOTER
   Stays visible while body scrolls.
========================================================= */

.reservation-modal-footer {
    flex: 0 0 auto;

    display: flex;
    align-items: center;

    justify-content: flex-end;

    gap: 10px;

    padding: 14px 28px;

    border-top: 1px solid #eee3da;

    background: #ffffff;

    position: relative;

    z-index: 10;

    box-shadow:
        0 -3px 10px rgba(15, 23, 42, 0.025);
}


/* =========================================================
   FOOTER BUTTONS
========================================================= */

.reservation-modal-footer .btn {
    min-height: 42px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    gap: 7px;

    padding: 0 17px;

    border-radius: 9px;

    font-size: 13px;

    font-weight: 650;

    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease,
        background 0.2s ease;
}

.reservation-modal-footer .btn:hover {
    transform: translateY(-1px);
}

.reservation-modal-footer .btn-light {
    border: 1px solid #eee3da;

    background: #ffffff;

    color: #475569;
}

.reservation-modal-footer .btn-light:hover {
    border-color: #fdba74;
    background: #fff1e6;
    color: #c2410c;
}

.reservation-modal-footer .btn-primary {
    border: 1px solid #292929;

    background: #111111;

    color: #f97316;

    box-shadow: none;
}

.reservation-modal-footer .btn-primary:hover {
    border-color: #3b3b3b;
    background: #242424;
    color: #fb923c;
}

.reservation-modal-footer .btn:disabled {
    opacity: 0.58;

    cursor: not-allowed;

    transform: none;
}


/* =========================================================
   FOCUS
========================================================= */

.reservation-modal-close:focus-visible,
.reservation-modal-footer .btn:focus-visible,
.reservation-form-group input:focus-visible,
.reservation-form-group select:focus-visible,
.reservation-form-group textarea:focus-visible,
.reservation-edit-table-option:focus-within {
    outline: 2px solid #f97316;

    outline-offset: 2px;
}


/* =========================================================
   TABLET
========================================================= */

@media (max-width: 900px) {

    .reservation-modal-overlay {
        padding: 15px;
    }

    .reservation-modal {
        width: min(100%, 720px);

        height: 88vh;

        max-height: 88vh;

        border-radius: 18px;
    }

    .reservation-modal-header {
        padding: 20px 23px;
    }

    .reservation-modal-body {
        padding: 20px 23px 23px;
    }

    .reservation-modal-footer {
        padding: 13px 23px;
    }

    .reservation-edit-section {
        padding: 18px;
    }

}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 700px) {

    .reservation-modal-overlay {
        padding: 10px;
    }

    .reservation-modal {
        width: 100%;

        height: 92vh;

        max-height: 92vh;

        border-radius: 17px;
    }

    .reservation-form-grid {
        grid-template-columns: 1fr;

        gap: 14px;
    }

    .reservation-selected-table-summary {
        grid-template-columns: 1fr;
    }

    .reservation-edit-table-grid {
        grid-template-columns:
            repeat(2, minmax(0, 1fr));
    }

    .reservation-modal-header h3 {
        font-size: 21px;
    }

}


/* =========================================================
   SMALL MOBILE
========================================================= */

@media (max-width: 560px) {

    .reservation-modal-overlay {
        padding: 0;

        align-items: flex-end;
    }

    .reservation-modal {
        width: 100%;

        height: 94vh;

        max-height: 94vh;

        border-radius:
            20px 20px 0 0;
    }

    .reservation-modal-header {
        padding: 18px 17px;
    }

    .reservation-modal-header h3 {
        font-size: 20px;
    }

    .reservation-modal-header p {
        font-size: 11px;

        max-width: 92%;
    }

    .reservation-modal-close {
        width: 43px;
        height: 43px;
    }

    .reservation-modal-body {
        padding: 14px 14px 18px;
    }

    .reservation-edit-section {
        margin-bottom: 13px;

        padding: 15px;

        border-radius: 13px;
    }

    .reservation-edit-section-title {
        font-size: 14px;

        margin-bottom: 15px;
    }

    .reservation-edit-section-title > i {
        width: 28px;
        height: 28px;

        font-size: 13px;
    }

    .reservation-edit-table-grid {
        grid-template-columns: 1fr;
    }

    .reservation-modal-footer {
        padding: 12px 14px;

        flex-direction: column-reverse;

        gap: 8px;
    }

    .reservation-modal-footer .btn {
        width: 100%;

        min-height: 42px;
    }

}


/* =========================================================
   VERY SMALL SCREEN
========================================================= */

@media (max-width: 380px) {

    .reservation-modal {
        height: 95vh;

        max-height: 95vh;
    }

    .reservation-modal-header {
        padding: 16px 14px;
    }

    .reservation-modal-body {
        padding: 12px;
    }

    .reservation-edit-section {
        padding: 13px;
    }

    .reservation-modal-header p {
        display: none;
    }

}


/* =========================================================
   ANIMATIONS
========================================================= */

@keyframes reservationOverlayIn {

    from {
        opacity: 0;
    }

    to {
        opacity: 1;
    }

}

@keyframes reservationModalIn {

    from {
        opacity: 0;

        transform:
            translateY(18px)
            scale(0.985);
    }

    to {
        opacity: 1;

        transform:
            translateY(0)
            scale(1);
    }

}

</style>
