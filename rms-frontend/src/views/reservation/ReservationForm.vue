<template>
  <div class="reservation-form-page">

    <!-- ================================================================
         PAGE HEADER
    ================================================================= -->

    <div class="reservation-page-header">

      <span class="reservation-form-header-icon">
        <i class="bi bi-calendar-plus"></i>
      </span>

      <div>

        <h1 class="reservation-page-title">
          Add Reservation
        </h1>

        <p class="reservation-page-subtitle">
          Reserve one or more restaurant tables for a customer.
        </p>

      </div>


      <button
        type="button"
        class="reservation-back-button"
        @click="goBack"
      >
        <i class="bi bi-arrow-left"></i>

        Back to Reservations
      </button>

    </div>


    <!-- ================================================================
         FORM CARD
    ================================================================= -->

    <div class="reservation-form-card">

      <form
        @submit.prevent="handleSubmit"
      >


        <!-- ============================================================
             CUSTOMER
        ============================================================= -->

        <section class="reservation-section">

          <div class="reservation-section-header">

            <div class="reservation-section-icon">
              <i class="bi bi-person"></i>
            </div>

            <div>

              <h2>
                Customer Information
              </h2>

              <p>
                Select an existing customer or book quickly using only a new customer name.
              </p>

            </div>

          </div>


          <!-- Existing / New customer mode -->

          <div class="reservation-customer-mode">

            <button
              type="button"
              :class="{
                active:
                  customerMode === 'existing'
              }"
              @click="setCustomerMode('existing')"
            >
              <i class="bi bi-people"></i>

              Existing Customer
            </button>


            <button
              type="button"
              :class="{
                active:
                  customerMode === 'new'
              }"
              @click="setCustomerMode('new')"
            >
              <i class="bi bi-person-plus"></i>

              New Customer
            </button>

          </div>


          <!-- ==========================================================
               EXISTING CUSTOMER
          =========================================================== -->

          <div
            v-if="customerMode === 'existing'"
            class="reservation-form-grid"
          >

            <div class="reservation-field full-width">

              <label class="reservation-label">

                Existing Customer

                <span class="required">
                  *
                </span>

              </label>


              <div class="reservation-customer-wrapper">

                <input
                  v-model="customerSearch"
                  type="text"
                  class="reservation-input"
                  placeholder="Search customer by name, phone or email..."
                  autocomplete="off"
                  @focus="customerDropdownOpen = true"
                  @input="customerDropdownOpen = true"
                />


                <div
                  v-if="customerDropdownOpen"
                  class="reservation-customer-dropdown"
                >

                  <button
                    v-for="customer in filteredCustomers"
                    :key="customer.id"
                    type="button"
                    class="reservation-customer-option"
                    @click="selectCustomer(customer)"
                  >

                    <div class="reservation-customer-avatar">

                      {{
                        getCustomerInitial(
                          customer.name
                        )
                      }}

                    </div>


                    <div class="reservation-customer-info">

                      <strong>
                        {{
                          customer.name ||
                          "Unnamed Customer"
                        }}
                      </strong>

                      <span>

                        {{
                          customer.phone ||
                          customer.email ||
                          "No contact information"
                        }}

                      </span>

                    </div>

                  </button>


                  <div
                    v-if="filteredCustomers.length === 0"
                    class="reservation-empty-dropdown"
                  >
                    No customer found.
                    You can switch to
                    <strong>New Customer</strong>
                    and book using only the name.
                  </div>

                </div>

              </div>


              <div
                v-if="selectedCustomer"
                class="reservation-selected-customer"
              >

                <div class="reservation-selected-customer-avatar">

                  {{
                    getCustomerInitial(
                      selectedCustomer.name
                    )
                  }}

                </div>


                <div class="reservation-selected-customer-info">

                  <strong>
                    {{ selectedCustomer.name }}
                  </strong>

                  <span>

                    {{
                      selectedCustomer.phone ||
                      selectedCustomer.email ||
                      "No contact information"
                    }}

                  </span>

                </div>


                <button
                  type="button"
                  class="reservation-remove-customer"
                  @click="clearCustomer"
                >

                  <i class="bi bi-x-lg"></i>

                </button>

              </div>


              <div
                v-if="errors.customer_id"
                class="reservation-field-error"
              >
                {{ errors.customer_id }}
              </div>

            </div>

          </div>


          <!-- ==========================================================
               NEW CUSTOMER
          =========================================================== -->

          <div
            v-else
            class="reservation-form-grid"
          >

            <div class="reservation-field full-width">

              <label
                for="reservation-new-customer-name"
                class="reservation-label"
              >

                Customer Name

                <span class="required">
                  *
                </span>

              </label>


              <input
                id="reservation-new-customer-name"
                v-model.trim="form.customer_name"
                type="text"
                maxlength="150"
                class="reservation-input"
                placeholder="Enter customer name"
                autocomplete="name"
                @input="
                  errors.customer_name = ''
                "
              />


              <div
                v-if="errors.customer_name"
                class="reservation-field-error"
              >
                {{ errors.customer_name }}
              </div>


              <div class="reservation-name-only-hint">

                <i class="bi bi-info-circle"></i>

                <span>
                  Phone and email are optional.
                  You can complete the reservation using only the customer name.
                </span>

              </div>

            </div>


            <div class="reservation-field">

              <label
                for="reservation-new-customer-phone"
                class="reservation-label"
              >
                Phone
                <span class="reservation-optional">
                  Optional
                </span>
              </label>


              <input
                id="reservation-new-customer-phone"
                v-model.trim="form.customer_phone"
                type="text"
                maxlength="30"
                class="reservation-input"
                placeholder="Optional phone number"
                autocomplete="tel"
                @input="
                  errors.customer_phone = ''
                "
              />


              <div
                v-if="errors.customer_phone"
                class="reservation-field-error"
              >
                {{ errors.customer_phone }}
              </div>

            </div>


            <div class="reservation-field">

              <label
                for="reservation-new-customer-email"
                class="reservation-label"
              >
                Email
                <span class="reservation-optional">
                  Optional
                </span>
              </label>


              <input
                id="reservation-new-customer-email"
                v-model.trim="form.customer_email"
                type="email"
                maxlength="150"
                class="reservation-input"
                placeholder="Optional email address"
                autocomplete="email"
                @input="
                  errors.customer_email = ''
                "
              />


              <div
                v-if="errors.customer_email"
                class="reservation-field-error"
              >
                {{ errors.customer_email }}
              </div>

            </div>

          </div>

        </section>


        <!-- ============================================================
             RESERVATION SCHEDULE
        ============================================================= -->

        <section class="reservation-section">

          <div class="reservation-section-header">

            <div class="reservation-section-icon">

              <i class="bi bi-calendar-event"></i>

            </div>

            <div>

              <h2>
                Reservation Schedule
              </h2>

              <p>
                Select date and reservation time.
              </p>

            </div>

          </div>


          <div class="reservation-form-grid">


            <!-- Date -->

            <div class="reservation-field">

              <label
                for="reservation-date"
                class="reservation-label"
              >

                Reservation Date

                <span class="required">
                  *
                </span>

              </label>


              <div
                ref="reservationDatePicker"
                class="reservation-date-picker"
                @keydown.esc="reservationCalendarOpen = false"
              >
                <button
                  id="reservation-date"
                  type="button"
                  class="reservation-input reservation-date-trigger"
                  :aria-expanded="reservationCalendarOpen"
                  aria-haspopup="dialog"
                  @click="toggleReservationCalendar"
                >
                  <span :class="{ 'is-placeholder': !form.reservation_date }">
                    {{ formatReservationDateDisplay(form.reservation_date) || 'Select a date' }}
                  </span>
                  <i class="bi bi-calendar3" aria-hidden="true"></i>
                </button>

                <Transition name="reservation-calendar">
                  <div
                    v-if="reservationCalendarOpen"
                    class="reservation-calendar-popover"
                    role="dialog"
                    aria-label="Choose reservation date"
                  >
                    <div class="reservation-calendar-header">
                      <strong>{{ reservationCalendarMonthLabel }}</strong>
                      <div class="reservation-calendar-navigation">
                        <button
                          type="button"
                          aria-label="Previous month"
                          @click="moveReservationCalendar(-1)"
                        >
                          <i class="bi bi-chevron-left" aria-hidden="true"></i>
                        </button>
                        <button
                          type="button"
                          aria-label="Next month"
                          @click="moveReservationCalendar(1)"
                        >
                          <i class="bi bi-chevron-right" aria-hidden="true"></i>
                        </button>
                      </div>
                    </div>

                    <div class="reservation-calendar-weekdays" aria-hidden="true">
                      <span v-for="day in reservationCalendarWeekdays" :key="day">{{ day }}</span>
                    </div>

                    <div class="reservation-calendar-days">
                      <button
                        v-for="day in reservationCalendarDays"
                        :key="day.key"
                        type="button"
                        class="reservation-calendar-day"
                        :class="{
                          'is-outside-month': !day.isCurrentMonth,
                          'is-selected': day.key === form.reservation_date,
                          'is-today': day.key === today,
                        }"
                        :disabled="day.isPast"
                        :aria-label="day.ariaLabel"
                        :aria-pressed="day.key === form.reservation_date"
                        @click="selectReservationDate(day.key)"
                      >
                        {{ day.dayNumber }}
                      </button>
                    </div>

                    <div class="reservation-calendar-footer">
                      <button type="button" class="calendar-clear" @click="clearReservationDate">Clear</button>
                      <button type="button" class="calendar-today" @click="selectReservationDate(today)">Today</button>
                    </div>
                  </div>
                </Transition>
              </div>


              <div
                v-if="errors.reservation_date"
                class="reservation-field-error"
              >
                {{ errors.reservation_date }}
              </div>

            </div>


            <!-- Start Time -->

            <div class="reservation-field">

              <label
                for="reservation-start"
                class="reservation-label"
              >

                Start Time

                <span class="required">
                  *
                </span>

              </label>


              <ReservationTimePicker
                id="reservation-start"
                v-model="form.start_time"
                :invalid="Boolean(errors.start_time)"
                placeholder="Select start time"
                @change="resetAvailability"
              />


              <div
                v-if="errors.start_time"
                class="reservation-field-error"
              >
                {{ errors.start_time }}
              </div>

            </div>


            <!-- End Time -->

            <div class="reservation-field">

              <label
                for="reservation-end"
                class="reservation-label"
              >

                End Time

                <span class="required">
                  *
                </span>

              </label>


              <ReservationTimePicker
                id="reservation-end"
                v-model="form.end_time"
                :invalid="Boolean(errors.end_time)"
                placeholder="Select end time"
                @change="resetAvailability"
              />


              <div
                v-if="errors.end_time"
                class="reservation-field-error"
              >
                {{ errors.end_time }}
              </div>

            </div>


            <!-- Guest Count -->

            <div class="reservation-field">

              <label
                for="guest-count"
                class="reservation-label"
              >

                Guest Count

                <span class="required">
                  *
                </span>

              </label>


              <input
                id="guest-count"
                v-model.number="form.guest_count"
                type="number"
                min="1"
                max="999"
                class="reservation-input"
                placeholder="e.g. 8"
                @input="resetAvailability"
              />


              <div class="reservation-input-help">

                Number of guests.

              </div>


              <div
                v-if="errors.guest_count"
                class="reservation-field-error"
              >
                {{ errors.guest_count }}
              </div>

            </div>

          </div>


          <!-- Check availability -->

          <div class="reservation-availability-action">

            <button
              type="button"
              class="reservation-check-button"
              :disabled="checkingAvailability"
              @click="checkAvailableTables"
            >

              <span
                v-if="checkingAvailability"
                class="spinner-border spinner-border-sm"
              ></span>


              <i
                v-else
                class="bi bi-search"
              ></i>


              {{
                checkingAvailability
                  ? "Checking..."
                  : "Check Available Tables"
              }}

            </button>

          </div>


          <!-- Availability error -->

          <div
            v-if="availabilityError"
            class="reservation-alert reservation-alert-danger"
          >

            <i class="bi bi-exclamation-triangle"></i>

            <span>
              {{ availabilityError }}
            </span>

          </div>

        </section>


        <!-- ============================================================
             AVAILABLE TABLES
        ============================================================= -->

        <section
          v-if="availabilityChecked"
          class="reservation-section"
        >

          <div class="reservation-section-header">

            <div class="reservation-section-icon">

              <i class="bi bi-grid-3x3-gap"></i>

            </div>

            <div>

              <h2>
                Available Tables
              </h2>

              <p>
                Select one or more tables.
              </p>

            </div>

          </div>


          <!-- Summary -->

          <div class="reservation-capacity-summary">

            <div class="reservation-summary-item">

              <span>
                Guests
              </span>

              <strong>
                {{ form.guest_count }}
              </strong>

            </div>


            <div class="reservation-summary-divider"></div>


            <div class="reservation-summary-item">

              <span>
                Selected Capacity
              </span>

              <strong
                :class="{
                  sufficient:
                    selectedCapacity >=
                    Number(form.guest_count || 0),

                  insufficient:
                    selectedCapacity <
                    Number(form.guest_count || 0)
                }"
              >
                {{ selectedCapacity }}
              </strong>

            </div>


            <div class="reservation-summary-divider"></div>


            <div class="reservation-summary-item">

              <span>
                Selected Tables
              </span>

              <strong>
                {{ selectedTables.length }}
              </strong>

            </div>

          </div>


          <!-- No tables -->

          <div
            v-if="availableTables.length === 0"
            class="reservation-empty-state"
          >

            <i class="bi bi-calendar-x"></i>

            <h3>
              No Available Table
            </h3>

            <p>
              No table is available for the selected date and time.
            </p>

            <button
              type="button"
              class="reservation-secondary-button"
              @click="resetAvailability"
            >
              Change Schedule
            </button>

          </div>


          <!-- Table cards -->

          <div
            v-else
            class="reservation-table-grid"
          >

            <button
              v-for="table in availableTables"
              :key="table.id"
              type="button"
              class="reservation-table-card"
              :class="{
                selected:
                  isTableSelected(table.id)
              }"
              @click="toggleTable(table)"
            >

              <div class="reservation-table-card-top">

                <span class="reservation-table-icon">

                  <i class="bi bi-grid"></i>

                </span>


                <span
                  v-if="isTableSelected(table.id)"
                  class="reservation-table-selected-icon"
                >

                  <i class="bi bi-check-circle-fill"></i>

                </span>

              </div>


              <div class="reservation-table-name">

                {{ table.table_name }}

              </div>


              <div class="reservation-table-meta">

                <span>

                  <i class="bi bi-people"></i>

                  {{ table.capacity }} seats

                </span>


                <span>

                  {{ formatSection(table.section) }}

                </span>

              </div>

            </button>

          </div>


          <!-- Selected tables -->

          <div
            v-if="selectedTables.length"
            class="reservation-selected-tables"
          >

            <div class="reservation-selected-tables-header">

              <h3>
                Selected Tables
              </h3>

              <span>
                {{ selectedTables.length }}
              </span>

            </div>


            <div class="reservation-selected-table-list">

              <div
                v-for="table in selectedTables"
                :key="table.id"
                class="reservation-selected-table-item"
              >

                <div>

                  <strong>
                    {{ table.table_name }}
                  </strong>

                  <span>

                    {{ table.capacity }} seats

                    ·

                    {{ formatSection(table.section) }}

                  </span>

                </div>


                <button
                  type="button"
                  class="reservation-remove-table"
                  @click="toggleTable(table)"
                >

                  <i class="bi bi-x-lg"></i>

                </button>

              </div>

            </div>


            <!-- insufficient capacity -->

            <div
              v-if="
                selectedCapacity <
                Number(
                  form.guest_count || 0
                )
              "
              class="reservation-alert reservation-alert-warning"
            >

              <i class="bi bi-exclamation-circle"></i>

              <span>

                Selected tables are not enough for
                {{ form.guest_count }} guests.

              </span>

            </div>


            <!-- sufficient capacity -->

            <div
              v-else
              class="reservation-alert reservation-alert-success"
            >

              <i class="bi bi-check-circle"></i>

              <span>

                Selected tables can accommodate all
                {{ form.guest_count }} guests.

              </span>

            </div>

          </div>

        </section>


        <!-- ============================================================
             EXTRA DETAILS
        ============================================================= -->

        <section class="reservation-section">

          <div class="reservation-section-header">

            <div class="reservation-section-icon">

              <i class="bi bi-file-text"></i>

            </div>

            <div>

              <h2>
                Additional Details
              </h2>

              <p>
                Add advance payment or special notes.
              </p>

            </div>

          </div>


          <div class="reservation-form-grid">


            <!-- Advance -->

            <div class="reservation-field">

              <label
                for="advance-amount"
                class="reservation-label"
              >

                Advance Amount

              </label>


              <div class="reservation-money-input">

                <span>
                  ৳
                </span>


                <input
                  id="advance-amount"
                  v-model.number="form.advance_amount"
                  type="number"
                  min="0"
                  step="0.01"
                  class="reservation-input"
                  placeholder="0.00"
                />

              </div>


              <div
                v-if="errors.advance_amount"
                class="reservation-field-error"
              >
                {{ errors.advance_amount }}
              </div>

            </div>


            <!-- Note -->

            <div class="reservation-field full-width">

              <label
                for="reservation-note"
                class="reservation-label"
              >

                Note

              </label>


              <textarea
                id="reservation-note"
                v-model="form.note"
                class="reservation-textarea"
                rows="4"
                maxlength="1000"
                placeholder="Special request or seating preference..."
              ></textarea>


              <div class="reservation-character-count">

                {{ form.note.length }}/1000

              </div>


              <div
                v-if="errors.note"
                class="reservation-field-error"
              >
                {{ errors.note }}
              </div>

            </div>

          </div>

        </section>


        <!-- ============================================================
             FORM ERROR
        ============================================================= -->

        <div
          v-if="formError"
          class="reservation-alert reservation-alert-danger"
        >

          <i class="bi bi-exclamation-triangle"></i>

          <span>
            {{ formError }}
          </span>

        </div>


        <!-- ============================================================
             FORM FOOTER
        ============================================================= -->

        <div class="reservation-form-footer">

          <button
            type="button"
            class="reservation-secondary-button"
            :disabled="saving"
            @click="goBack"
          >
            Cancel
          </button>


          <button
            type="submit"
            class="reservation-primary-button"
            :disabled="saving"
          >

            <span
              v-if="saving"
              class="spinner-border spinner-border-sm"
            ></span>

            <i
              v-else
              class="bi bi-arrow-right"
            ></i>

            Continue

          </button>

        </div>

      </form>

    </div>

  </div>
</template>


<script setup>

import {
  computed,
  onBeforeUnmount,
  onMounted,
  reactive,
  ref,
} from "vue";

import { useRouter } from "vue-router";

import reservationService from "@/services/reservationService";
import ReservationTimePicker from "@/components/reservation/ReservationTimePicker.vue";

import "@/assets/css/reservation/reservation-form.css";


/*
|--------------------------------------------------------------------------
| Router
|--------------------------------------------------------------------------
*/

const router =
  useRouter();


/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/

const form =
  reactive({

    customer_id:
      null,

    customer_name:
      "",

    customer_phone:
      "",

    customer_email:
      "",

    reservation_date:
      "",

    start_time:
      "",

    end_time:
      "",

    guest_count:
      1,

    advance_amount:
      0,

    note:
      "",

  });


/*
|--------------------------------------------------------------------------
| Customer
|--------------------------------------------------------------------------
*/

const customers =
  ref([]);

const customerMode =
  ref("existing");

const customerSearch =
  ref("");

const selectedCustomer =
  ref(null);

const customerDropdownOpen =
  ref(false);


/*
|--------------------------------------------------------------------------
| Tables
|--------------------------------------------------------------------------
*/

const availableTables =
  ref([]);

const selectedTables =
  ref([]);


/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const checkingAvailability =
  ref(false);

const availabilityChecked =
  ref(false);

const availabilityError =
  ref("");

const formError =
  ref("");

const saving =
  ref(false);


/*
|--------------------------------------------------------------------------
| Validation Errors
|--------------------------------------------------------------------------
*/

const errors =
  reactive({

    customer_id:
      "",

    customer_name:
      "",

    customer_phone:
      "",

    customer_email:
      "",

    reservation_date:
      "",

    start_time:
      "",

    end_time:
      "",

    guest_count:
      "",

    advance_amount:
      "",

    note:
      "",

  });


/*
|--------------------------------------------------------------------------
| Today
|--------------------------------------------------------------------------
*/

const today =
  computed(() => {

    const date =
      new Date();

    const year =
      date.getFullYear();

    const month =
      String(
        date.getMonth() + 1
      ).padStart(
        2,
        "0"
      );

    const day =
      String(
        date.getDate()
      ).padStart(
        2,
        "0"
      );

    return `${year}-${month}-${day}`;

  });

const reservationDatePicker = ref(null);
const reservationCalendarOpen = ref(false);
const reservationCalendarMonth = ref(new Date());
const reservationCalendarWeekdays = ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa"];

const reservationCalendarMonthLabel = computed(() =>
  reservationCalendarMonth.value.toLocaleDateString(undefined, {
    month: "long",
    year: "numeric",
  })
);

const reservationCalendarDays = computed(() => {
  const year = reservationCalendarMonth.value.getFullYear();
  const month = reservationCalendarMonth.value.getMonth();
  const firstDay = new Date(year, month, 1);
  const gridStart = new Date(year, month, 1 - firstDay.getDay());

  return Array.from({ length: 42 }, (_, index) => {
    const date = new Date(
      gridStart.getFullYear(),
      gridStart.getMonth(),
      gridStart.getDate() + index,
    );
    const key = formatLocalDateKey(date);

    return {
      key,
      dayNumber: date.getDate(),
      isCurrentMonth: date.getMonth() === month,
      isPast: key < today.value,
      ariaLabel: date.toLocaleDateString(undefined, {
        weekday: "long",
        month: "long",
        day: "numeric",
        year: "numeric",
      }),
    };
  });
});

function formatLocalDateKey(date) {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const day = String(date.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
}

function parseLocalDate(value) {
  if (!value) return null;
  const [year, month, day] = value.split("-").map(Number);
  return new Date(year, month - 1, day);
}

function formatReservationDateDisplay(value) {
  const date = parseLocalDate(value);
  return date
    ? date.toLocaleDateString(undefined, {
        month: "2-digit",
        day: "2-digit",
        year: "numeric",
      })
    : "";
}

function toggleReservationCalendar() {
  if (!reservationCalendarOpen.value) {
    const selectedDate = parseLocalDate(form.reservation_date);
    const initialDate = selectedDate || parseLocalDate(today.value);
    reservationCalendarMonth.value = new Date(
      initialDate.getFullYear(),
      initialDate.getMonth(),
      1,
    );
  }
  reservationCalendarOpen.value = !reservationCalendarOpen.value;
}

function moveReservationCalendar(offset) {
  reservationCalendarMonth.value = new Date(
    reservationCalendarMonth.value.getFullYear(),
    reservationCalendarMonth.value.getMonth() + offset,
    1,
  );
}

function selectReservationDate(dateKey) {
  if (dateKey < today.value) return;
  form.reservation_date = dateKey;
  reservationCalendarOpen.value = false;
  resetAvailability();
}

function clearReservationDate() {
  form.reservation_date = "";
  reservationCalendarOpen.value = false;
  resetAvailability();
}


/*
|--------------------------------------------------------------------------
| Customer Filter
|--------------------------------------------------------------------------
*/

const filteredCustomers =
  computed(() => {

    const search =
      customerSearch.value
        .trim()
        .toLowerCase();


    if (!search) {

      return customers.value.slice(
        0,
        10
      );

    }


    return customers.value
      .filter(
        (customer) => {

          const name =
            String(
              customer?.name ||
              ""
            ).toLowerCase();

          const phone =
            String(
              customer?.phone ||
              ""
            ).toLowerCase();

          const email =
            String(
              customer?.email ||
              ""
            ).toLowerCase();


          return (
            name.includes(search) ||
            phone.includes(search) ||
            email.includes(search)
          );

        }
      )
      .slice(
        0,
        10
      );

  });


/*
|--------------------------------------------------------------------------
| Selected Capacity
|--------------------------------------------------------------------------
*/

const selectedCapacity =
  computed(() => {

    return selectedTables.value.reduce(
      (
        total,
        table
      ) => {

        return (
          total +
          Number(
            table?.capacity || 0
          )
        );

      },
      0
    );

  });


/*
|--------------------------------------------------------------------------
| Customer Initial
|--------------------------------------------------------------------------
*/

const getCustomerInitial =
  (name) => {

    const value =
      String(
        name || ""
      ).trim();


    if (!value) {
      return "C";
    }


    return value
      .charAt(0)
      .toUpperCase();

  };


/*
|--------------------------------------------------------------------------
| Load Customers
|--------------------------------------------------------------------------
*/

const loadCustomers =
  async () => {

    try {

      const response =
        await reservationService
          .getCustomers();


      const data =
        response?.data ??
        response ??
        {};


      customers.value =
        Array.isArray(
          data?.data
        )
          ? data.data
          : Array.isArray(data)
            ? data
            : [];

    } catch (error) {

      console.error(
        "Customer loading failed:",
        error
      );

      customers.value = [];

    }

  };


/*
|--------------------------------------------------------------------------
| Customer Mode
|--------------------------------------------------------------------------
*/

const setCustomerMode =
  (mode) => {

    customerMode.value =
      mode === "new"
        ? "new"
        : "existing";


    errors.customer_id =
      "";

    errors.customer_name =
      "";

    errors.customer_phone =
      "";

    errors.customer_email =
      "";


    if (
      customerMode.value ===
      "new"
    ) {

      selectedCustomer.value =
        null;

      form.customer_id =
        null;

      customerSearch.value =
        "";

      customerDropdownOpen.value =
        false;

      return;

    }


    form.customer_name =
      "";

    form.customer_phone =
      "";

    form.customer_email =
      "";

  };


/*
|--------------------------------------------------------------------------
| Select Customer
|--------------------------------------------------------------------------
*/

const selectCustomer =
  (customer) => {

    customerMode.value =
      "existing";


    selectedCustomer.value =
      customer;


    form.customer_id =
      Number(
        customer.id
      );


    form.customer_name =
      "";

    form.customer_phone =
      "";

    form.customer_email =
      "";


    customerSearch.value =
      customer.name ||
      "";


    customerDropdownOpen.value =
      false;


    errors.customer_id =
      "";

  };


/*
|--------------------------------------------------------------------------
| Clear Customer
|--------------------------------------------------------------------------
*/

const clearCustomer =
  () => {

    selectedCustomer.value =
      null;


    form.customer_id =
      null;


    customerSearch.value =
      "";


    errors.customer_id =
      "";

  };


/*
|--------------------------------------------------------------------------
| Close customer dropdown
|--------------------------------------------------------------------------
*/

const handleDocumentClick =
  (event) => {

    const target =
      event.target;

    if (
      reservationCalendarOpen.value &&
      !reservationDatePicker.value?.contains(target)
    ) {
      reservationCalendarOpen.value = false;
    }


    if (
      !target.closest(
        ".reservation-customer-wrapper"
      )
    ) {

      customerDropdownOpen.value =
        false;

    }

  };


/*
|--------------------------------------------------------------------------
| Reset Availability
|--------------------------------------------------------------------------
*/

const resetAvailability =
  () => {

    availabilityChecked.value =
      false;

    availabilityError.value =
      "";

    availableTables.value =
      [];

    selectedTables.value =
      [];

  };


/*
|--------------------------------------------------------------------------
| Validate Schedule
|--------------------------------------------------------------------------
*/

const validateSchedule =
  () => {

    errors.reservation_date =
      "";

    errors.start_time =
      "";

    errors.end_time =
      "";

    errors.guest_count =
      "";


    let valid =
      true;


    if (
      !form.reservation_date
    ) {

      errors.reservation_date =
        "Please select a reservation date.";

      valid =
        false;

    }


    if (
      !form.start_time
    ) {

      errors.start_time =
        "Please select a start time.";

      valid =
        false;

    }


    if (
      !form.end_time
    ) {

      errors.end_time =
        "Please select an end time.";

      valid =
        false;

    }


    if (
      form.start_time &&
      form.end_time &&
      form.end_time <=
        form.start_time
    ) {

      errors.end_time =
        "End time must be later than start time.";

      valid =
        false;

    }


    if (
      !form.guest_count ||
      Number(
        form.guest_count
      ) < 1
    ) {

      errors.guest_count =
        "Guest count must be at least 1.";

      valid =
        false;

    }


    return valid;

  };


/*
|--------------------------------------------------------------------------
| Check Available Tables
|--------------------------------------------------------------------------
*/

const checkAvailableTables =
  async () => {

    availabilityError.value =
      "";

    formError.value =
      "";


    if (
      !validateSchedule()
    ) {
      return;
    }


    checkingAvailability.value =
      true;


    try {

      const response =
        await reservationService
          .getAvailableTables({

            date:
              form.reservation_date,

            start_time:
              form.start_time,

            end_time:
              form.end_time,

            guest_count:
              Number(
                form.guest_count
              ),

          });


      availableTables.value =
        Array.isArray(
          response?.data
        )
          ? response.data
          : [];


      availabilityChecked.value =
        true;


      selectedTables.value =
        selectedTables.value.filter(
          selected =>
            availableTables.value.some(
              table =>
                Number(
                  table.id
                ) ===
                Number(
                  selected.id
                )
            )
        );

    } catch (error) {

      availabilityChecked.value =
        false;


      availabilityError.value =
        reservationService
          .getReservationErrorMessage(
            error,
            "Unable to check table availability."
          );

    } finally {

      checkingAvailability.value =
        false;

    }

  };


/*
|--------------------------------------------------------------------------
| Toggle Table
|--------------------------------------------------------------------------
*/

const toggleTable =
  (table) => {

    const tableId =
      Number(
        table.id
      );


    const index =
      selectedTables.value.findIndex(
        selected =>
          Number(
            selected.id
          ) === tableId
      );


    if (index >= 0) {

      selectedTables.value.splice(
        index,
        1
      );

      return;

    }


    selectedTables.value.push(
      table
    );

  };


/*
|--------------------------------------------------------------------------
| Is Table Selected
|--------------------------------------------------------------------------
*/

const isTableSelected =
  (tableId) => {

    return selectedTables.value.some(
      table =>
        Number(
          table.id
        ) ===
        Number(
          tableId
        )
    );

  };


/*
|--------------------------------------------------------------------------
| Format Section
|--------------------------------------------------------------------------
*/

const formatSection =
  (section) => {

    return String(
      section || ""
    )
      .replaceAll(
        "_",
        " "
      )
      .replace(
        /\b\w/g,
        character =>
          character.toUpperCase()
      );

  };


/*
|--------------------------------------------------------------------------
| Validate Entire Form
|--------------------------------------------------------------------------
*/

const validateForm =
  () => {

    formError.value =
      "";


    errors.customer_id =
      "";

    errors.customer_name =
      "";

    errors.customer_phone =
      "";

    errors.customer_email =
      "";

    errors.advance_amount =
      "";

    errors.note =
      "";


    let valid =
      true;


    if (
      customerMode.value ===
      "existing"
    ) {

      if (
        !form.customer_id
      ) {

        errors.customer_id =
          "Please select an existing customer.";

        valid =
          false;

      }

    }
    else {

      const customerName =
        String(
          form.customer_name ||
          ""
        ).trim();


      if (
        customerName.length < 2
      ) {

        errors.customer_name =
          "Customer name must contain at least 2 characters.";

        valid =
          false;

      }


      const customerEmail =
        String(
          form.customer_email ||
          ""
        ).trim();


      if (
        customerEmail
        &&
        !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(
          customerEmail
        )
      ) {

        errors.customer_email =
          "Please enter a valid email address.";

        valid =
          false;

      }

    }


    if (
      !availabilityChecked.value
    ) {

      formError.value =
        "Please check table availability first.";

      valid =
        false;

    }


    if (
      selectedTables.value.length ===
      0
    ) {

      formError.value =
        "Please select at least one table.";

      valid =
        false;

    }


    if (
      selectedCapacity.value <
      Number(
        form.guest_count || 0
      )
    ) {

      formError.value =
        "Selected table capacity is not enough for the guest count.";

      valid =
        false;

    }


    if (
      Number(
        form.advance_amount || 0
      ) < 0
    ) {

      errors.advance_amount =
        "Advance amount cannot be negative.";

      valid =
        false;

    }


    return valid;

  };


/*
|--------------------------------------------------------------------------
| Submit
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Submit Reservation
|--------------------------------------------------------------------------
*/

const handleSubmit =
  async () => {


    if (
      !validateForm()
    ) {
        return;
    }


    saving.value = true;

    formError.value = "";


    try {


        const payload = {

            customer_id:
                customerMode.value ===
                    "existing"
                    &&
                    form.customer_id
                        ? Number(
                            form.customer_id
                        )
                        : null,


            customer_name:
                customerMode.value ===
                    "new"
                    ? String(
                        form.customer_name ||
                        ""
                      ).trim()
                    : null,


            customer_phone:
                customerMode.value ===
                    "new"
                    ? String(
                        form.customer_phone ||
                        ""
                      ).trim()
                      ||
                      null
                    : null,


            customer_email:
                customerMode.value ===
                    "new"
                    ? String(
                        form.customer_email ||
                        ""
                      ).trim()
                      ||
                      null
                    : null,


            reservation_date:
                form.reservation_date,


            start_time:
                form.start_time,


            end_time:
                form.end_time,


            guest_count:
                Number(
                    form.guest_count
                ),


            advance_amount:
                Number(
                    form.advance_amount || 0
                ),


            note:
                form.note || null,


            table_ids:
                selectedTables.value.map(
                    table =>
                        Number(
                            table.id
                        )
                ),

        };



        console.log(
            "CREATE RESERVATION PAYLOAD",
            payload
        );



        const response =
            await reservationService.createReservation(
                payload
            );



        console.log(
            "RESERVATION CREATED",
            response
        );



        await router.push({
          path:
            "/reservations",

          query: {
            notice:
              "created",
          },
        });



    } catch(error) {


        console.error(
            "Reservation creation failed:",
            error
        );


        const validationErrors =
            error?.response
                ?.data
                ?.errors
            ??
            {};


        [
          "customer_id",
          "customer_name",
          "customer_phone",
          "customer_email",
          "reservation_date",
          "start_time",
          "end_time",
          "guest_count",
          "advance_amount",
          "note",
        ].forEach(
          (field) => {

            const first =
              Array.isArray(
                validationErrors?.[field]
              )
                ? validationErrors[field][0]
                : validationErrors?.[field];


            if (
              first
              &&
              Object.prototype
                .hasOwnProperty
                .call(
                  errors,
                  field
                )
            ) {

              errors[field] =
                String(first);

            }

          }
        );


        formError.value =
            reservationService
                .getReservationErrorMessage(
                    error,
                    "Unable to create reservation."
                );


    } finally {


        saving.value = false;


    }

};


/*
|--------------------------------------------------------------------------
| Back
|--------------------------------------------------------------------------
*/

const goBack =
  () => {

    router.back();

  };


/*
|--------------------------------------------------------------------------
| Lifecycle
|--------------------------------------------------------------------------
*/

onMounted(
  async () => {

    document.addEventListener(
      "click",
      handleDocumentClick
    );


    await loadCustomers();

  }
);


onBeforeUnmount(
  () => {

    document.removeEventListener(
      "click",
      handleDocumentClick
    );

  }
);

</script>


<style scoped>
/* =========================================================
   RESERVATION CUSTOMER MODE
========================================================= */

.reservation-customer-mode {
  display: inline-flex;
  gap: 4px;
  margin-bottom: 14px;
  padding: 4px;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  background: #f8fafc;
}

.reservation-customer-mode button {
  min-height: 34px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 0 12px;
  border: 1px solid transparent;
  border-radius: 5px;
  background: transparent;
  color: #667085;
  font-size: 11px;
  font-weight: 650;
}

.reservation-customer-mode button:hover {
  color: #2563eb;
}

.reservation-customer-mode button.active {
  border-color: #bfdbfe;
  background: #ffffff;
  color: #1d4ed8;
  box-shadow:
    0 1px 3px rgba(15, 23, 42, .06);
}

.reservation-optional {
  margin-left: 5px;
  color: #98a2b3;
  font-size: 9px;
  font-weight: 500;
}

.reservation-name-only-hint {
  display: flex;
  align-items: flex-start;
  gap: 6px;
  margin-top: 7px;
  padding: 8px 9px;
  border: 1px solid #bfdbfe;
  border-radius: 6px;
  background: #eff6ff;
  color: #1e40af;
  font-size: 10px;
  line-height: 1.4;
}

.reservation-name-only-hint i {
  margin-top: 1px;
  flex: 0 0 auto;
}

/* Order Management theme: warm canvas, black text and orange accents. */
.reservation-form-page {
  padding: 18px !important;
  border-radius: 24px;
  background:
    radial-gradient(circle at 0 0, rgba(249, 115, 22, .055), transparent 28%),
    radial-gradient(circle at 100% 8%, rgba(251, 146, 60, .045), transparent 25%),
    #f4f1ed !important;
  color: #111827 !important;
}

.reservation-page-header,
.reservation-form-card {
  border: 1px solid #eee3da !important;
  border-radius: 14px !important;
  background: #fff !important;
  box-shadow: 0 1px 2px rgba(15, 23, 42, .04) !important;
}

.reservation-page-header {
  min-height: 100px;
  margin-bottom: 12px !important;
  padding: 18px 20px !important;
}

.reservation-form-header-icon {
  display: grid;
  width: 40px;
  height: 40px;
  flex: 0 0 40px;
  place-items: center;
  border-radius: 12px;
  background: #fff1e6;
  color: #f97316;
  font-size: 17px;
}

.reservation-page-title,
.reservation-page-subtitle,
.reservation-section-header h2,
.reservation-section-header p,
.reservation-label,
.reservation-customer-mode button,
.reservation-form-page .form-control,
.reservation-form-page .form-select,
.reservation-table-card,
.reservation-selected-table-item strong {
  color: #111827 !important;
}

.reservation-section-icon,
.reservation-selected-customer-avatar,
.reservation-table-icon {
  border-radius: 10px !important;
  background: #fff1e6 !important;
  color: #f97316 !important;
}

.reservation-form-page .form-control:focus,
.reservation-form-page .form-select:focus,
.reservation-form-page textarea:focus {
  border-color: #fb923c !important;
  box-shadow: 0 0 0 .2rem rgba(249, 115, 22, .14) !important;
}

.reservation-customer-mode {
  border-color: #eee3da !important;
  background: #f8f1e9 !important;
}

.reservation-customer-mode button.active {
  border-color: #fed7aa !important;
  background: #fff !important;
  color: #c2410c !important;
}

.reservation-customer-mode button.active i {
  color: #f97316 !important;
}

.reservation-check-button,
.reservation-primary-button,
.reservation-check-button:hover:not(:disabled),
.reservation-primary-button:hover:not(:disabled) {
  border: 1px solid #292929 !important;
  border-radius: 10px !important;
  background: linear-gradient(145deg, #191919 0%, #111 54%, #090909 100%) !important;
  color: #f97316 !important;
}

.reservation-check-button i,
.reservation-primary-button i {
  color: #f97316 !important;
}

.reservation-back-button {
  border-color: #eee3da !important;
  border-radius: 10px !important;
  background: #fff !important;
  color: #111827 !important;
}

.reservation-back-button i {
  color: #f97316 !important;
}

.reservation-table-card.selected {
  border-color: #f97316 !important;
  background: #fff8f1 !important;
  box-shadow: 0 0 0 1px rgba(249, 115, 22, .16) !important;
}

.reservation-form-footer {
  border-color: #eee3da !important;
  background: #fff8f1 !important;
}

@media (max-width: 560px) {
  .reservation-customer-mode {
    width: 100%;
  }

  .reservation-customer-mode button {
    flex: 1;
  }

  .reservation-form-page {
    padding: 10px !important;
    border-radius: 18px;
  }

  .reservation-page-header {
    padding: 14px !important;
  }
}
</style>
