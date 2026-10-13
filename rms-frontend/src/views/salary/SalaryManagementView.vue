<template>

<section class="salary-management-page">

  <!-- Notification -->

  <Transition name="salary-message">

    <div
      v-if="message.show"
      class="salary-message"
      :class="`salary-message-${message.type}`"
    >

      <i
        class="bi"
        :class="
          message.type === 'error'
            ? 'bi-exclamation-circle'
            : 'bi-check-circle'
        "
      ></i>

      <span>
        {{ message.text }}
      </span>

      <button
        type="button"
        @click="hideMessage"
      >
        <i class="bi bi-x-lg"></i>
      </button>

    </div>

  </Transition>


    <!-- Header -->
  <header class="salary-page-header">
    <div class="salary-page-header-left">
      <div class="salary-page-icon">
        <i class="bi bi-cash-stack"></i>
      </div>

      <div>
        <p class="salary-page-eyebrow">
          Payroll
        </p>

        <h2>
          Salary Management
        </h2>

        <p>
          Monthly employee salary overview and payment management
        </p>
      </div>
    </div>

    <div class="salary-period-header">
      <span>
        Selected Period
      </span>

      <strong>
        {{ formattedPeriod }}
      </strong>
    </div>
  </header>


  <!-- Tabs -->
  <div class="salary-tabs">
    <button
      type="button"
      class="salary-tab-button"
      :class="{ active: activeTab === 'salary' }"
      @click="activeTab = 'salary'"
    >
      <i class="bi bi-wallet2"></i>
      Monthly Salary
    </button>

    <button
      type="button"
      class="salary-tab-button"
      :class="{ active: activeTab === 'salary_details' }"
      @click="activeTab = 'salary_details'"
    >
      <i class="bi bi-receipt-cutoff"></i>
      Daily Salary Details
    </button>
  </div>


  <!-- Monthly Salary -->

  <template v-if="activeTab === 'salary'">

    <!-- Period / Recalculate -->

    <div class="salary-control-card">

      <div class="salary-period-selector">

        <label>
          Payroll Month
        </label>

        <input
          v-model="selectedMonth"
          type="month"
          :max="currentMonthValue"
          :disabled="loading || generating"
          @change="changePayrollMonth"
        />

        <small>
          Past months can be reviewed and paid. Current month keeps accumulating automatically.
        </small>

      </div>

      <div class="salary-control-period">

        <div class="salary-control-icon">
          <i class="bi bi-calendar3"></i>
        </div>

        <div>

          <span>
            Salary Period
          </span>

          <strong>
            {{ formattedPeriod }}
          </strong>

          <small>
            Current month accumulates daily. Past months stay available for review and payment.
          </small>

        </div>

      </div>


      <button
        type="button"
        class="salary-recalculate-btn"
        :disabled="generating"
        @click="recalculateSelectedPeriod"
      >

        <span
          v-if="generating"
          class="salary-button-spinner"
        ></span>

        <i
          v-else
          class="bi bi-arrow-repeat"
        ></i>

        {{
          generating
            ? 'Recalculating...'
            : 'Recalculate Period'
        }}

      </button>

    </div>


    <!-- Summary -->

    <div class="salary-summary-grid">

      <div class="salary-summary-card">

        <span>
          Employees
        </span>

        <strong>
          {{ formatCount(summary.total_payrolls) }}
        </strong>

        <small>
          Salary records
        </small>

      </div>


      <div class="salary-summary-card">

        <span>
          Worked Time
        </span>

        <strong>
          {{ durationLabel(summary.worked_minutes) }}
        </strong>

        <small>
          Total working time
        </small>

      </div>


      <div class="salary-summary-card salary-summary-overtime">

        <span>
          Overtime
        </span>

        <strong>
          {{ durationLabel(summary.overtime_minutes) }}
        </strong>

        <small>
          Total overtime
        </small>

      </div>


      <div class="salary-summary-card">

        <span>
          Regular Salary
        </span>

        <strong>
          {{ money(summary.regular_salary) }}
        </strong>

      </div>


      <div class="salary-summary-card salary-summary-paid">

        <span>
          Paid
        </span>

        <strong>
          {{ money(summary.paid_amount) }}
        </strong>

        <small>
          {{ formatCount(summary.paid_count) }} employee(s)
        </small>

      </div>


      <div class="salary-summary-card salary-summary-total">

        <span>
          Total Salary
        </span>

        <strong>
          {{ money(summary.total_amount) }}
        </strong>

        <small>
          {{ summary.unpaid_count }} unpaid
        </small>

      </div>

    </div>


    <!-- Filters -->

    <div class="salary-filter-card">

      <div class="salary-filter-grid">

        <div class="salary-filter-group salary-search-group">

          <label>
            Search Employee
          </label>

          <div class="salary-search-input">

            <i class="bi bi-search"></i>

            <input
              v-model.trim="filters.search"
              type="text"
              placeholder="Name, phone or email"
              @keyup.enter="applyFilters"
            />

          </div>

        </div>


        <div class="salary-filter-group">

          <label>
            Employee
          </label>

          <select
            v-model="filters.employee_id"
            :disabled="employeesLoading"
          >

            <option value="">
              All Employees
            </option>

            <option
              v-for="employee in employees"
              :key="employee.id"
              :value="String(employee.id)"
            >

              {{ employee.employee_name }}

            </option>

          </select>

        </div>


        <div class="salary-filter-group">

          <label>
            Payment Status
          </label>

          <select
            v-model="filters.payment_status"
          >

            <option value="">
              All Statuses
            </option>

            <option value="paid">
              Paid
            </option>

            <option value="unpaid">
              Unpaid
            </option>

          </select>

        </div>


        <div class="salary-filter-actions">

          <button
            type="button"
            class="salary-filter-btn"
            :disabled="loading"
            @click="applyFilters"
          >

            <i class="bi bi-funnel"></i>

            Apply

          </button>


          <button
            type="button"
            class="salary-clear-btn"
            :disabled="loading"
            @click="clearFilters"
          >
            Clear
          </button>

        </div>

      </div>

    </div>


    <!-- Table -->

    <div class="salary-table-card">

      <div class="salary-table-title">

        <div>

          <h3>
            Employee Salary Summary
          </h3>

          <p>
            {{ formattedPeriod }}
          </p>

        </div>

        <span class="salary-result-count">
          {{ formatCount(meta.total) }} employee(s)
        </span>

      </div>


      <div class="salary-table-responsive">

        <table class="salary-table">

          <thead>

            <tr>

              <th>
                SL
              </th>

              <th>
                Employee
              </th>

              <th>
                Role
              </th>

              <th>
                Hourly Rate
              </th>

              <th>
                Worked
              </th>

              <th>
                Overtime
              </th>

              <th>
                Regular
              </th>

              <th>
                OT Salary
              </th>

              <th>
                Adjustment
              </th>

              <th>
                Total
              </th>

              <th>
                Status
              </th>

              <th>
                Action
              </th>

            </tr>

          </thead>


          <tbody>

            <!-- Loading -->

            <tr v-if="loading">

              <td
                colspan="12"
                class="salary-table-state"
              >

                <span class="salary-table-spinner"></span>

                Loading monthly salary...

              </td>

            </tr>


            <!-- Error -->

            <tr v-else-if="errorMessage">

              <td
                colspan="12"
                class="salary-table-state salary-table-error"
              >

                {{ errorMessage }}

              </td>

            </tr>


            <!-- Empty -->

            <tr v-else-if="salaries.length === 0">

              <td
                colspan="12"
                class="salary-table-state"
              >

                <div class="salary-empty-state">

                  <i class="bi bi-wallet2"></i>

                  <strong>
                    No salary records found
                  </strong>

                  <span>
                    Run today's salary calculation or adjust your filters.
                  </span>

                </div>

              </td>

            </tr>


            <!-- Data -->

            <tr
              v-for="(salary, index) in salaries"
              v-else
              :key="salary.id || `employee-${salary.employee_id}-${salary.period_start}`"
            >

              <!-- SL -->

              <td>
                {{ formatCount(serialNumber(index)) }}
              </td>


              <!-- Employee -->

              <td>

                <div class="salary-employee-cell">

                  <div class="salary-avatar">
                    {{ initials(salary.employee_name) }}
                  </div>

                  <div>

                    <strong>
                      {{ salary.employee_name }}
                    </strong>

                    <small>
                      {{ salary.employee_phone || 'No phone' }}
                    </small>

                  </div>

                </div>

              </td>


              <!-- Role -->

              <td>

                <span class="salary-role-badge">
                  {{ salary.role_label || salary.role_name || 'Staff' }}
                </span>

              </td>


              <!-- Hourly Rate -->

              <td>
                {{ salary.hourly_rate_formatted }}
              </td>


              <!-- Worked -->

              <td>

                <div class="salary-time-cell">

                  <strong>
                    {{ salary.worked_duration_label }}
                  </strong>

                  <small>
                    {{ salary.working_days }} day(s)
                  </small>

                </div>

              </td>


              <!-- Overtime -->

              <td>

                <span
                  class="salary-overtime-value"
                  :class="{
                    active:
                      salary.overtime_minutes > 0
                  }"
                >

                  {{ salary.overtime_duration_label }}

                </span>

              </td>


              <!-- Regular -->

              <td>
                {{ salary.regular_salary_formatted }}
              </td>


              <!-- OT Salary -->

              <td>
                {{ salary.overtime_salary_formatted }}
              </td>


              <!-- Adjustment -->

              <td>
                {{ salary.adjustment_amount_formatted }}
              </td>


              <!-- Total -->

              <td>

                <div class="salary-total-cell">

                  <strong>
                    {{ salary.total_amount_formatted }}
                  </strong>

                </div>

              </td>


              <!-- Status -->

              <td>

                <div class="salary-status-wrapper">

                  <!-- <span
                    class="salary-status-badge"
                    :class="
                      salary.payment_status === 'paid'
                        ? 'paid'
                        : 'unpaid'
                    "
                  >

                    <i
                      class="bi"
                      :class="
                        salary.payment_status === 'paid'
                          ? 'bi-check-circle'
                          : 'bi-clock'
                      "
                    ></i>

                    {{ salary.payment_status_label }}

                  </span> -->


                  <select
                    class="salary-payment-select"
                    :value="salary.payment_status"
                    :disabled="
                      paymentLoadingId === salary.id
                    "
                    @change="
                      handlePaymentSelection(
                        salary,
                        $event
                      )
                    "
                  >

                    <option value="unpaid">
                      Unpaid
                    </option>

                    <option
                      value="paid"
                      :disabled="
                        salary.payment_status !== 'paid'
                        &&
                        !salary.can_mark_paid
                      "
                    >
                      Paid
                    </option>

                  </select>

                </div>

              </td>


              <!-- Actions -->

              <td>

                <div class="salary-actions">

                  <button
                    type="button"
                    class="salary-action-btn salary-view-btn"
                    title="View employee salary details"
                    @click="openDetails(salary)"
                  >

                    <i class="bi bi-eye"></i>

                  </button>


                  <button
                    type="button"
                    class="salary-action-btn salary-edit-btn"
                    title="Edit adjustment and notes"
                    :disabled="!salary.can_edit"
                    @click="openEditModal(salary)"
                  >

                    <i class="bi bi-pencil"></i>

                  </button>


                  <button
                    type="button"
                    class="salary-action-btn salary-delete-btn"
                    title="Delete unpaid salary"
                    :disabled="
                      !salary.can_delete
                      ||
                      deleteLoadingId === salary.id
                    "
                    @click="deleteSalary(salary)"
                  >

                    <span
                      v-if="
                        deleteLoadingId === salary.id
                      "
                      class="salary-inline-spinner"
                    ></span>

                    <i
                      v-else
                      class="bi bi-trash"
                    ></i>

                  </button>

                </div>

              </td>

            </tr>

          </tbody>

        </table>

      </div>


      <!-- Pagination -->
      <div
        v-if="!loading"
        class="salary-pagination"
      >

        <div class="salary-pagination-info">

          <span class="salary-pagination-label">
            Showing
          </span>

          <strong>
            {{
              formatCount(
                meta.total === 0
                  ? 0
                  : (
                      (meta.current_page - 1)
                      * meta.per_page
                    ) + 1
              )
            }}
          </strong>

          <span>
            —
          </span>

          <strong>
            {{ formatCount(
              Math.min(
                meta.current_page * meta.per_page,
                meta.total
              )
            ) }}
          </strong>

          <span class="salary-pagination-label">
            of
          </span>

          <strong>
            {{ formatCount(meta.total) }}
          </strong>

          <span class="salary-pagination-label">
            employees
          </span>

        </div>


        <div class="salary-pagination-controls">

          <button
            type="button"
            class="salary-pagination-button"
            :disabled="
              meta.current_page <= 1
              ||
              loading
            "
            @click="
              changePage(
                meta.current_page - 1
              )
            "
          >

            <i class="bi bi-chevron-left"></i>

            Previous

          </button>


          <div class="salary-pagination-current">

            <span>
              Page
            </span>

            <strong>
              {{ formatCount(meta.current_page) }}
            </strong>

            <span>
              of
            </span>

            <strong>
              {{ formatCount(meta.last_page) }}
            </strong>

          </div>


          <button
            type="button"
            class="salary-pagination-button"
            :disabled="
              meta.current_page >= meta.last_page
              ||
              loading
            "
            @click="
              changePage(
                meta.current_page + 1
              )
            "
          >

            Next

            <i class="bi bi-chevron-right"></i>

          </button>

        </div>

      </div>
    </div>

  </template>


  <!-- Daily Details -->

  <SalaryDetailsSection
  v-else
  :employee-id="selectedDetailEmployeeId"
  :from-date="selectedPeriodStart"
  :to-date="selectedPeriodEnd"
  @back="activeTab = 'salary'"
/>


  <!-- Edit Modal -->

  <div
    v-if="showEditModal"
    class="salary-modal-overlay"
    @mousedown.self="closeEditModal"
  >

    <div class="salary-modal">

      <div class="salary-modal-header">

        <div>

          <h3>
            Edit Salary
          </h3>

          <p>
            {{ selectedSalary?.employee_name }}
          </p>

        </div>

        <button
          type="button"
          @click="closeEditModal"
        >
          <i class="bi bi-x-lg"></i>
        </button>

      </div>


      <form @submit.prevent="saveSalary">

        <div class="salary-modal-body">

          <div class="salary-edit-summary">

            <div>

              <span>
                Regular Salary
              </span>

              <strong>
                {{ selectedSalary?.regular_salary_formatted }}
              </strong>

            </div>


            <div>

              <span>
                Overtime Salary
              </span>

              <strong>
                {{ selectedSalary?.overtime_salary_formatted }}
              </strong>

            </div>


            <div>

              <span>
                Worked Time
              </span>

              <strong>
                {{ selectedSalary?.worked_duration_label || '0m' }}
              </strong>

            </div>


            <div>

              <span>
                Overtime
              </span>

              <strong>
                {{ selectedSalary?.overtime_duration_label || '0m' }}
              </strong>

            </div>

          </div>


          <div class="salary-filter-group">

            <label>
              Adjustment Amount
            </label>

            <input
              v-model="editForm.adjustment_amount"
              type="number"
              step="0.01"
              placeholder="Example: 500 or -200"
            />

            <small>
              Positive value adds a bonus. Negative value applies a deduction.
            </small>

          </div>


          <div class="salary-filter-group">

            <label>
              Notes
            </label>

            <textarea
              v-model.trim="editForm.notes"
              rows="4"
              maxlength="2000"
              placeholder="Optional salary notes"
            ></textarea>

          </div>


          <div class="salary-final-preview">

            <span>
              Final Amount
            </span>

            <strong>
              {{ editTotalPreview }}
            </strong>

          </div>

        </div>


        <div class="salary-modal-footer">

          <button
            type="button"
            class="salary-clear-btn"
            :disabled="savingEdit"
            @click="closeEditModal"
          >
            Cancel
          </button>


          <button
            type="submit"
            class="salary-generate-btn"
            :disabled="savingEdit"
          >

            <span
              v-if="savingEdit"
              class="salary-button-spinner"
            ></span>

            <i
              v-else
              class="bi bi-check-lg"
            ></i>

            {{
              savingEdit
                ? 'Saving...'
                : 'Save Salary'
            }}

          </button>

        </div>

      </form>

    </div>

  </div>


  <!-- Salary Payment Modal -->

  <div
    v-if="showPaymentModal"
    class="salary-modal-overlay"
    @mousedown.self="closePaymentModal"
  >

    <div class="salary-modal">

      <div class="salary-modal-header">

        <div>

          <h3>
            Pay Salary
          </h3>

          <p>
            {{ paymentSalary?.employee_name }}
          </p>

        </div>

        <button
          type="button"
          :disabled="paymentSaving"
          @click="closePaymentModal"
        >
          <i class="bi bi-x-lg"></i>
        </button>

      </div>


      <form @submit.prevent="confirmSalaryPayment">

        <div class="salary-modal-body">

          <div class="salary-edit-summary">

            <div>

              <span>
                Payroll Period
              </span>

              <strong>
                {{ paymentSalary?.period_label || '—' }}
              </strong>

            </div>

            <div>

              <span>
                Employee
              </span>

              <strong>
                {{ paymentSalary?.employee_name || '—' }}
              </strong>

            </div>

            <div>

              <span>
                Amount to Pay
              </span>

              <strong>
                {{ paymentSalary?.total_amount_formatted || money(0) }}
              </strong>

            </div>

            <div>

              <span>
                Expense
              </span>

              <strong>
                Salary & Wages
              </strong>

            </div>

          </div>


          <div class="salary-filter-group">

            <label>
              Payment Method
            </label>

            <select
              v-model="paymentForm.payment_method"
              required
            >

              <option value="">
                Select payment method
              </option>

              <option
                v-for="method in paymentMethods"
                :key="method.value"
                :value="method.value"
              >
                {{ method.label }}
              </option>

            </select>

            <small>
              This method will also be saved on the linked Salary & Wages expense.
            </small>

          </div>


          <div class="salary-filter-group">

            <label>
              Payment Date
            </label>

            <input
              v-model="paymentForm.payment_date"
              type="date"
              :max="todayDate"
              required
            />

          </div>


          <div class="salary-filter-group">

            <label>
              Payment Reference
            </label>

            <input
              v-model.trim="paymentForm.payment_reference"
              type="text"
              maxlength="150"
              placeholder="Bank ref, bKash trx ID, cheque no. (optional)"
            />

          </div>


          <div class="salary-final-preview">

            <span>
              Expense to be created
            </span>

            <strong>
              {{ paymentSalary?.total_amount_formatted || money(0) }}
            </strong>

          </div>

        </div>


        <div class="salary-modal-footer">

          <button
            type="button"
            class="salary-clear-btn"
            :disabled="paymentSaving"
            @click="closePaymentModal"
          >
            Cancel
          </button>


          <button
            type="submit"
            class="salary-generate-btn"
            :disabled="
              paymentSaving
              ||
              !paymentForm.payment_method
              ||
              !paymentForm.payment_date
            "
          >

            <span
              v-if="paymentSaving"
              class="salary-button-spinner"
            ></span>

            <i
              v-else
              class="bi bi-cash-coin"
            ></i>

            {{
              paymentSaving
                ? 'Paying...'
                : 'Confirm Salary Payment'
            }}

          </button>

        </div>

      </form>

    </div>

  </div>

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

import salaryService
  from '@/services/salaryService'

import SalaryDetailsSection
  from '@/components/salary/SalaryDetailsSection.vue'


/*
|--------------------------------------------------------------------------
| Page State
|--------------------------------------------------------------------------
*/
const selectedDetailEmployeeId =
  ref(null)

const activeTab =
  ref('salary')

const salaries =
  ref([])

const employees =
  ref([])

const loading =
  ref(false)

const generating =
  ref(false)

const employeesLoading =
  ref(false)

const paymentLoadingId =
  ref(null)

const deleteLoadingId =
  ref(null)

const errorMessage =
  ref('')

const showEditModal =
  ref(false)

const selectedSalary =
  ref(null)

const savingEdit =
  ref(false)


const showPaymentModal =
  ref(false)

const paymentSalary =
  ref(null)

const paymentSaving =
  ref(false)

const paymentMethods =
  ref([])


/*
|--------------------------------------------------------------------------
| Current Date Helpers
|--------------------------------------------------------------------------
*/

function localDate(
  date = new Date()
) {

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


function firstDayOfMonth()
{
  const date =
    new Date()

  date.setDate(1)

  return localDate(date)
}


const todayDate =
  localDate()


const currentMonthStart =
  firstDayOfMonth()


const currentMonthValue =
  todayDate.slice(
    0,
    7
  )


const selectedMonth =
  ref(
    currentMonthValue
  )


/*
|--------------------------------------------------------------------------
| Selected Payroll Period
|--------------------------------------------------------------------------
*/

const selectedPeriodStart =
  computed(() => {

    return `${selectedMonth.value}-01`

  })


const selectedPeriodEnd =
  computed(() => {

    if (
      selectedMonth.value ===
      currentMonthValue
    ) {
      return todayDate
    }

    const [
      year,
      month,
    ] =
      selectedMonth.value
        .split('-')
        .map(Number)

    const lastDate =
      new Date(
        year,
        month,
        0
      )

    return localDate(
      lastDate
    )

  })


/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

const filters = reactive({

  search: '',

  employee_id: '',

  payment_status: '',

  page: 1,

  per_page: 10,

})


/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/

const meta =
  ref({

    current_page: 1,

    last_page: 1,

    per_page: 10,

    total: 0,

  })


/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

const summary =
  reactive({

    total_payrolls: 0,

    paid_count: 0,

    unpaid_count: 0,

    regular_salary: 0,

    overtime_salary: 0,

    adjustment_amount: 0,

    total_amount: 0,

    paid_amount: 0,

    unpaid_amount: 0,

    scheduled_minutes: 0,

    worked_minutes: 0,

    break_minutes: 0,

    overtime_minutes: 0,

  })


/*
|--------------------------------------------------------------------------
| Edit Form
|--------------------------------------------------------------------------
*/

const editForm =
  reactive({

    adjustment_amount: 0,

    notes: '',

  })


const paymentForm =
  reactive({

    payment_method: '',

    payment_date:
      todayDate,

    payment_reference: '',

  })


/*
|--------------------------------------------------------------------------
| Notification
|--------------------------------------------------------------------------
*/

const message =
  reactive({

    show: false,

    type: 'success',

    text: '',

  })


let messageTimer =
  null


/*
|--------------------------------------------------------------------------
| Computed
|--------------------------------------------------------------------------
*/

const formattedPeriod =
  computed(() => {

    return (
      formatDate(
        selectedPeriodStart.value
      )
      +
      ' — '
      +
      formatDate(
        selectedPeriodEnd.value
      )
    )

  })


const editTotalPreview =
  computed(() => {

    if (!selectedSalary.value) {
      return money(0)
    }

    return money(

      Number(
        selectedSalary
          .value
          .regular_salary
        ||
        0
      )

      +

      Number(
        selectedSalary
          .value
          .overtime_salary
        ||
        0
      )

      +

      Number(
        editForm
          .adjustment_amount
        ||
        0
      )

    )

  })


/*
|--------------------------------------------------------------------------
| Response Helpers
|--------------------------------------------------------------------------
*/

function extractPayload(
  response
) {

  return response?.data
    ??
    response

}


function extractCollection(
  response
) {

  const payload =
    extractPayload(
      response
    )

  if (
    Array.isArray(
      payload?.data
    )
  ) {
    return payload.data
  }

  if (
    Array.isArray(
      payload
    )
  ) {
    return payload
  }

  return []

}


function getErrorMessage(
  error,
  fallback
) {

  const errors =
    error
      ?.response
      ?.data
      ?.errors

  if (errors) {

    return Object
      .values(errors)
      .flat()
      .join(' ')

  }

  return (
    error
      ?.response
      ?.data
      ?.message

    ??

    error?.message

    ??

    fallback
  )

}


/*
|--------------------------------------------------------------------------
| Notification
|--------------------------------------------------------------------------
*/

function showMessage(
  text,
  type = 'success'
) {

  if (messageTimer) {
    clearTimeout(
      messageTimer
    )
  }

  message.text =
    text

  message.type =
    type

  message.show =
    true

  messageTimer =
    setTimeout(
      () => {
        message.show = false
      },
      4000
    )

}


function hideMessage()
{

  message.show =
    false

  if (messageTimer) {

    clearTimeout(
      messageTimer
    )

  }

}


/*
|--------------------------------------------------------------------------
| Load Employees
|--------------------------------------------------------------------------
*/

async function loadEmployees()
{

  employeesLoading.value =
    true

  try {

    const response =
      await salaryService
        .getEmployees()

    employees.value =
      extractCollection(
        response
      )

  }

  catch (error) {

    employees.value =
      []

    showMessage(
      getErrorMessage(
        error,
        'Unable to load employees.'
      ),
      'error'
    )

  }

  finally {

    employeesLoading.value =
      false

  }

}


/*
|--------------------------------------------------------------------------
| Load Salary Options
|--------------------------------------------------------------------------
*/

async function loadSalaryOptions()
{

  try {

    const response =
      await salaryService
        .getOptions()

    const payload =
      extractPayload(
        response
      )

    paymentMethods.value =
      Array.isArray(
        payload
          ?.data
          ?.payment_methods
      )
        ? payload
            .data
            .payment_methods
        : []

  }

  catch (error) {

    paymentMethods.value =
      []

    console.error(
      'Unable to load salary options:',
      error
    )

  }

}


/*
|--------------------------------------------------------------------------
| Load Salaries
|--------------------------------------------------------------------------
*/

async function loadSalaries()
{

  loading.value =
    true

  errorMessage.value =
    ''

  try {

    const response =
      await salaryService
        .getSalaries({

          search:
            filters.search
            ||
            undefined,

          employee_id:
            filters.employee_id
              ? Number(
                  filters.employee_id
                )
              : undefined,

          payment_status:
            filters.payment_status
            ||
            undefined,

          from_date:
            selectedPeriodStart.value,

          to_date:
            selectedPeriodEnd.value,

          page:
            filters.page,

          per_page:
            filters.per_page,

        })


    const payload =
      extractPayload(
        response
      )


    salaries.value =
      extractCollection(
        response
      )


    meta.value =
      payload?.meta
      ??
      {
        current_page: 1,
        last_page: 1,
        per_page: 10,
        total: 0,
      }


    Object.assign(
      summary,
      {

        total_payrolls:
          Number(
            payload
              ?.summary
              ?.total_payrolls
            ??
            0
          ),

        paid_count:
          Number(
            payload
              ?.summary
              ?.paid_count
            ??
            0
          ),

        unpaid_count:
          Number(
            payload
              ?.summary
              ?.unpaid_count
            ??
            0
          ),

        regular_salary:
          Number(
            payload
              ?.summary
              ?.regular_salary
            ??
            0
          ),

        overtime_salary:
          Number(
            payload
              ?.summary
              ?.overtime_salary
            ??
            0
          ),

        adjustment_amount:
          Number(
            payload
              ?.summary
              ?.adjustment_amount
            ??
            0
          ),

        total_amount:
          Number(
            payload
              ?.summary
              ?.total_amount
            ??
            0
          ),

        paid_amount:
          Number(
            payload
              ?.summary
              ?.paid_amount
            ??
            0
          ),

        unpaid_amount:
          Number(
            payload
              ?.summary
              ?.unpaid_amount
            ??
            0
          ),

        scheduled_minutes:
          Number(
            payload
              ?.summary
              ?.scheduled_minutes
            ??
            0
          ),

        worked_minutes:
          Number(
            payload
              ?.summary
              ?.worked_minutes
            ??
            0
          ),

        break_minutes:
          Number(
            payload
              ?.summary
              ?.break_minutes
            ??
            0
          ),

        overtime_minutes:
          Number(
            payload
              ?.summary
              ?.overtime_minutes
            ??
            0
          ),

      }
    )

  }

  catch (error) {

    salaries.value =
      []

    errorMessage.value =
      getErrorMessage(
        error,
        'Unable to load monthly salary.'
      )

  }

  finally {

    loading.value =
      false

  }

}


/*
|--------------------------------------------------------------------------
| Recalculate Today
|--------------------------------------------------------------------------
*/

async function recalculateSelectedPeriod()
{

  generating.value =
    true

  try {

    const response =
      await salaryService
        .generateSalaries({

          from_date:
            selectedPeriodStart.value,

          to_date:
            selectedPeriodEnd.value,

          employee_id:
            null,

        })


    filters.page =
      1

    await loadSalaries()

    showMessage(
      extractPayload(
        response
      )?.message
      ??
      'Salary recalculation completed successfully.'
    )

  }

  catch (error) {

    showMessage(
      getErrorMessage(
        error,
        'Salary recalculation failed.'
      ),
      'error'
    )

  }

  finally {

    generating.value =
      false

  }

}


/*
|--------------------------------------------------------------------------
| Change Payroll Month
|--------------------------------------------------------------------------
*/

async function changePayrollMonth()
{

  if (
    !selectedMonth.value
    ||
    selectedMonth.value >
      currentMonthValue
  ) {

    selectedMonth.value =
      currentMonthValue

  }


  filters.page =
    1


  await loadSalaries()

}


/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

async function applyFilters()
{

  filters.page =
    1

  await loadSalaries()

}


async function clearFilters()
{

  filters.search =
    ''

  filters.employee_id =
    ''

  filters.payment_status =
    ''

  filters.page =
    1

  await loadSalaries()

}


/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/

async function changePage(
  page
) {

  const targetPage =
    Number(page)

  if (
    targetPage < 1
    ||
    targetPage >
      meta.value.last_page
  ) {
    return
  }

  filters.page =
    targetPage

  await loadSalaries()

}


function serialNumber(
  index
) {

  return (

    (
      Number(
        meta.value
          .current_page
        ||
        1
      )
      -
      1
    )

    *

    Number(
      meta.value
        .per_page
      ||
      10
    )

  )
  +
  index
  +
  1

}


function formatCount(value) {

  return String(Number(value) || 0).padStart(2, '0')

}


/*
|--------------------------------------------------------------------------
| Employee Details
|--------------------------------------------------------------------------
*/

function openDetails(
  salary
) {

  selectedDetailEmployeeId.value =
    Number(
      salary.employee_id
    )

  activeTab.value =
    'salary_details'
}


/*
|--------------------------------------------------------------------------
| Payment Status
|--------------------------------------------------------------------------
*/

async function handlePaymentSelection(
  salary,
  event
) {

  const selectElement =
    event.target

  const previousStatus =
    salary.payment_status

  const newStatus =
    selectElement.value


  /*
  |--------------------------------------------------------------------------
  | Keep Select Controlled By Server State
  |--------------------------------------------------------------------------
  */

  selectElement.value =
    previousStatus


  if (
    newStatus ===
      previousStatus
    ||
    paymentLoadingId.value !==
      null
  ) {
    return
  }


  if (
    newStatus ===
    'paid'
  ) {

    if (
      !salary.can_mark_paid
    ) {

      showMessage(
        salary.is_current_period
          ? 'Current month payroll is still open and cannot be paid yet.'
          : 'Complete/recalculate the full closed month before marking this payroll as paid.',
        'error'
      )

      return
    }


    openPaymentModal(
      salary
    )

    return
  }


  await reverseSalaryPayment(
    salary
  )

}


function openPaymentModal(
  salary
) {

  paymentSalary.value = {
    ...salary,
  }

  paymentForm.payment_method =
    ''

  paymentForm.payment_date =
    todayDate

  paymentForm.payment_reference =
    ''

  showPaymentModal.value =
    true

}


function closePaymentModal(
  force = false
)
{

  if (
    paymentSaving.value
    &&
    !force
  ) {
    return
  }

  showPaymentModal.value =
    false

  paymentSalary.value =
    null

  paymentForm.payment_method =
    ''

  paymentForm.payment_date =
    todayDate

  paymentForm.payment_reference =
    ''

}


async function confirmSalaryPayment()
{

  if (
    !paymentSalary.value
    ||
    paymentSaving.value
  ) {
    return
  }


  if (
    !paymentForm.payment_method
    ||
    !paymentForm.payment_date
  ) {

    showMessage(
      'Payment method and payment date are required.',
      'error'
    )

    return
  }


  const confirmed =
    window.confirm(
      `Pay ${paymentSalary.value.employee_name}'s salary of ${paymentSalary.value.total_amount_formatted}?`
    )


  if (!confirmed) {
    return
  }


  paymentSaving.value =
    true

  paymentLoadingId.value =
    paymentSalary.value.id


  try {

    const response =
      await salaryService
        .updatePaymentStatus(
          paymentSalary.value.id,
          {

            payment_status:
              'paid',

            payment_method:
              paymentForm
                .payment_method,

            payment_date:
              paymentForm
                .payment_date,

            payment_reference:
              paymentForm
                .payment_reference
              ||
              null,

          }
        )


    closePaymentModal(
      true
    )


    await loadSalaries()


    showMessage(
      extractPayload(
        response
      )?.message
      ??
      'Salary paid and expense created successfully.'
    )

  }

  catch (error) {

    showMessage(
      getErrorMessage(
        error,
        'Salary payment failed.'
      ),
      'error'
    )

  }

  finally {

    paymentSaving.value =
      false

    paymentLoadingId.value =
      null

  }

}


async function reverseSalaryPayment(
  salary
) {

  const confirmed =
    window.confirm(
      `Change ${salary.employee_name}'s salary back to unpaid? The linked Salary & Wages expense will also be reversed.`
    )


  if (!confirmed) {
    return
  }


  paymentLoadingId.value =
    salary.id


  try {

    const response =
      await salaryService
        .updatePaymentStatus(
          salary.id,
          {
            payment_status:
              'unpaid',
          }
        )


    await loadSalaries()


    showMessage(
      extractPayload(
        response
      )?.message
      ??
      'Salary payment reversed successfully.'
    )

  }

  catch (error) {

    showMessage(
      getErrorMessage(
        error,
        'Salary payment reversal failed.'
      ),
      'error'
    )

  }

  finally {

    paymentLoadingId.value =
      null

  }

}


/*
|--------------------------------------------------------------------------
| Edit Salary
|--------------------------------------------------------------------------
*/

function openEditModal(
  salary
) {

  if (!salary.can_edit) {
    return
  }

  selectedSalary.value = {
    ...salary,
  }

  editForm.adjustment_amount =
    Number(
      salary.adjustment_amount
      ||
      0
    )

  editForm.notes =
    salary.notes
    ||
    ''

  showEditModal.value =
    true

}


function closeEditModal()
{

  if (savingEdit.value) {
    return
  }

  showEditModal.value =
    false

  selectedSalary.value =
    null

  editForm.adjustment_amount =
    0

  editForm.notes =
    ''

}


async function saveSalary()
{

  if (!selectedSalary.value) {
    return
  }

  savingEdit.value =
    true

  try {

    const response =
      await salaryService
        .updateSalary(
          selectedSalary.value.id,
          {

            adjustment_amount:
              Number(
                editForm
                  .adjustment_amount
                ||
                0
              ),

            notes:
              editForm.notes
              ||
              null,

          }
        )

    closeEditModal()

    await loadSalaries()

    showMessage(
      extractPayload(
        response
      )?.message
      ??
      'Salary updated successfully.'
    )

  }

  catch (error) {

    showMessage(
      getErrorMessage(
        error,
        'Salary update failed.'
      ),
      'error'
    )

  }

  finally {

    savingEdit.value =
      false

  }

}


/*
|--------------------------------------------------------------------------
| Delete Salary
|--------------------------------------------------------------------------
*/

async function deleteSalary(
  salary
) {

  if (!salary.can_delete) {
    return
  }


  const confirmed =
    window.confirm(
      `Delete the unpaid salary payroll for ${salary.employee_name}?`
    )


  if (!confirmed) {
    return
  }


  deleteLoadingId.value =
    salary.id


  try {

    const response =
      await salaryService
        .deleteSalary(
          salary.id
        )

    if (
      salaries.value.length === 1
      &&
      filters.page > 1
    ) {

      filters.page -=
        1

    }

    await loadSalaries()

    showMessage(
      extractPayload(
        response
      )?.message
      ??
      'Salary deleted successfully.'
    )

  }

  catch (error) {

    showMessage(
      getErrorMessage(
        error,
        'Salary deletion failed.'
      ),
      'error'
    )

  }

  finally {

    deleteLoadingId.value =
      null

  }

}


/*
|--------------------------------------------------------------------------
| Formatters
|--------------------------------------------------------------------------
*/

function initials(
  name
) {

  if (!name) {
    return 'NA'
  }

  return String(name)
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map(
      word =>
        word.charAt(0)
    )
    .join('')
    .toUpperCase()

}


function money(
  amount
) {

  return new Intl.NumberFormat(
    'en-BD',
    {

      style: 'currency',

      currency: 'BDT',

      minimumFractionDigits: 2,

    }
  ).format(
    Number(
      amount
      ||
      0
    )
  )

}


function durationLabel(
  minutes
) {

  const totalMinutes =
    Math.max(
      0,
      Number(
        minutes
        ||
        0
      )
    )

  const hours =
    Math.floor(
      totalMinutes / 60
    )

  const remaining =
    totalMinutes % 60

  if (
    hours > 0
    &&
    remaining > 0
  ) {

    return `${hours}h ${remaining}m`

  }

  if (hours > 0) {

    return `${hours}h`

  }

  return `${remaining}m`

}


function formatDate(
  date
) {

  if (!date) {
    return '—'
  }

  const parsed =
    new Date(
      `${date}T00:00:00`
    )

  return new Intl.DateTimeFormat(
    'en-GB',
    {

      day: '2-digit',

      month: 'short',

      year: 'numeric',

    }
  ).format(parsed)

}


/*
|--------------------------------------------------------------------------
| Initial Load
|--------------------------------------------------------------------------
*/

onMounted(
  async () => {

    await Promise.all([
      loadEmployees(),
      loadSalaryOptions(),
      loadSalaries(),
    ])

  }
)


onBeforeUnmount(
  () => {

    if (messageTimer) {

      clearTimeout(
        messageTimer
      )

    }

  }
)

</script>
<style
  src="@/assets/css/salary/salary-management.css"
></style>
<style
  src="@/assets/css/salary/salary-details.css"
></style>
<style
  src="@/assets/css/salary/salary-compact.css"
></style>

<style scoped>
.salary-period-selector {
  display: flex;
  min-width: 190px;
  flex-direction: column;
  gap: 5px;
}

.salary-period-selector label {
  font-size: 12px;
  font-weight: 700;
}

.salary-period-selector input {
  min-height: 40px;
  padding: 8px 10px;
  border: 1px solid rgba(15, 23, 42, 0.14);
  border-radius: 8px;
  background: #fff;
}

.salary-period-selector small {
  max-width: 260px;
  font-size: 11px;
  line-height: 1.35;
  opacity: 0.72;
}
</style>


<style scoped>
/*
|--------------------------------------------------------------------------
| Salary Modal Scroll / Sticky Footer Fix
|--------------------------------------------------------------------------
|
| Keeps the payment/edit action buttons visible even on smaller screens.
|
*/

.salary-modal-overlay {
  overflow-y: auto;
  padding: 16px;
}

.salary-modal {
  display: flex;
  width: min(100%, 720px);
  max-height: calc(100dvh - 32px);
  flex-direction: column;
  overflow: hidden;
}

.salary-modal > form {
  display: flex;
  min-height: 0;
  flex: 1;
  flex-direction: column;
  overflow: hidden;
}

.salary-modal-body {
  min-height: 0;
  flex: 1;
  overflow-y: auto;
  overscroll-behavior: contain;
  padding-bottom: 20px;
}

.salary-modal-footer {
  position: sticky;
  bottom: 0;
  z-index: 5;
  flex-shrink: 0;
  border-top: 1px solid rgba(15, 23, 42, 0.08);
  background: #fff;
}

@media (max-width: 575.98px) {
  .salary-modal-overlay {
    padding: 8px;
  }

  .salary-modal {
    width: 100%;
    max-height: calc(100dvh - 16px);
  }

  .salary-modal-footer {
    gap: 8px;
  }

  .salary-modal-footer > button {
    flex: 1;
  }
}
</style>
