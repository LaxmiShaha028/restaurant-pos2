<template>

<section class="other-income-page">

  <!-- Message -->

  <Transition name="income-message">

    <div
      v-if="message.show"
      class="income-message"
      :class="`income-message-${message.type}`"
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
        aria-label="Close"
        @click="hideMessage"
      >
        <i class="bi bi-x-lg"></i>
      </button>

    </div>

  </Transition>


  <!-- Header -->

  <header class="income-header">

    <div class="income-header-left">

      <div class="income-header-icon">
        <i class="bi bi-graph-up-arrow"></i>
      </div>

      <div>

        <span class="income-eyebrow">
          Accounting
        </span>

        <h1>
          Other Income
        </h1>

        <p>
          Record non-POS restaurant income such as bottle, carton, paper and scrap sales.
        </p>

      </div>

    </div>


    <button
      type="button"
      class="income-primary-btn"
      @click="openCreateModal"
    >

      <i class="bi bi-plus-lg"></i>

      Add Other Income

    </button>

  </header>

  <!-- Summary -->

  <section class="income-summary-grid">

    <article class="income-summary-card income-summary-total">

      <span>
        Total Other Income
      </span>

      <strong>
        {{ money(summary.total_amount) }}
      </strong>

      <small>
        {{ summary.total_records }} transaction(s)
      </small>

    </article>


    <article class="income-summary-card">

      <span>
        Cash Received
      </span>

      <strong>
        {{ money(summary.cash_amount) }}
      </strong>

      <small>
        Selected period
      </small>

    </article>


    <article class="income-summary-card">

      <span>
        Bank Received
      </span>

      <strong>
        {{ money(summary.bank_amount) }}
      </strong>

      <small>
        Bank transfer
      </small>

    </article>


    <article class="income-summary-card">

      <span>
        Mobile Wallet
      </span>

      <strong>
        {{ money(summary.mobile_wallet_amount) }}
      </strong>

      <small>
        bKash + Nagad
      </small>

    </article>

  </section>


  <!-- Filters -->

  <section class="income-filter-card">

    <div class="income-filter-grid">

      <div class="income-field income-search-field">

        <label>
          Search
        </label>

        <div class="income-search-input">

          <i class="bi bi-search"></i>

          <input
            v-model.trim="filters.search"
            type="text"
            placeholder="Category, reference, notes or staff"
            @keyup.enter="applyFilters"
          />

        </div>

      </div>


      <div class="income-field">

        <label>
          From
        </label>

        <input
          v-model="filters.from_date"
          type="date"
          :max="todayDate"
        />

      </div>


      <div class="income-field">

        <label>
          To
        </label>

        <input
          v-model="filters.to_date"
          type="date"
          :min="filters.from_date"
          :max="todayDate"
        />

      </div>


      <div class="income-field">

        <label>
          Category
        </label>

        <select
          v-model="filters.income_category_id"
        >

          <option value="">
            All Categories
          </option>

          <option
            v-for="category in categories"
            :key="category.id"
            :value="String(category.id)"
          >
            {{ category.name }}
          </option>

        </select>

      </div>


      <div class="income-field">

        <label>
          Received Via
        </label>

        <select
          v-model="filters.payment_method"
        >

          <option value="">
            All Methods
          </option>

          <option
            v-for="method in paymentMethods"
            :key="method.value"
            :value="method.value"
          >
            {{ method.label }}
          </option>

        </select>

      </div>


      <div class="income-filter-actions">

        <button
          type="button"
          class="income-filter-apply"
          :disabled="loading"
          @click="applyFilters"
        >

          <i class="bi bi-funnel"></i>

          Apply

        </button>


        <button
          type="button"
          class="income-filter-clear"
          :disabled="loading"
          @click="clearFilters"
        >
          Clear
        </button>

      </div>

    </div>

  </section>


  <!-- Table -->

  <section class="income-table-card">

    <header class="income-table-header">

      <div>

        <h2>
          Other Income Records
        </h2>

        <p>
          {{ periodLabel }}
        </p>

      </div>


      <button
        type="button"
        class="income-refresh-btn"
        :disabled="loading"
        @click="refreshAll"
      >

        <i class="bi bi-arrow-clockwise"></i>

        Refresh

      </button>

    </header>


    <div class="income-table-responsive">

      <table class="income-table">

        <thead>

          <tr>

            <th>SL</th>

            <th>Date</th>

            <th>Category</th>

            <th>Amount</th>

            <th>Received Via</th>

            <th>Reference</th>

            <th>Recorded By</th>

            <th>Notes</th>

            <th>Action</th>

          </tr>

        </thead>


        <tbody>

          <tr v-if="loading">

            <td
              colspan="9"
              class="income-state-cell"
            >

              <span class="income-spinner"></span>

              Loading other income...

            </td>

          </tr>


          <tr v-else-if="errorMessage">

            <td
              colspan="9"
              class="income-state-cell income-error-cell"
            >
              {{ errorMessage }}
            </td>

          </tr>


          <tr v-else-if="incomes.length === 0">

            <td
              colspan="9"
              class="income-state-cell"
            >

              <div class="income-empty">

                <i class="bi bi-cash-coin"></i>

                <strong>
                  No other income found
                </strong>

                <span>
                  Add bottle, carton, paper, scrap or another extra income transaction.
                </span>

              </div>

            </td>

          </tr>


          <tr
            v-for="(income, index) in incomes"
            v-else
            :key="income.id"
          >

            <td>
              {{ serialNumber(index) }}
            </td>


            <td>

              <div class="income-date-cell">

                <strong>
                  {{ income.income_date_label }}
                </strong>

                <small>
                  {{ income.day_label }}
                </small>

              </div>

            </td>


            <td>

              <span class="income-category-badge">
                {{ income.category_name }}
              </span>

            </td>


            <td>

              <strong class="income-amount">
                {{ income.amount_formatted }}
              </strong>

            </td>


            <td>

              <span class="income-payment-badge">
                {{ income.payment_method_label }}
              </span>

            </td>


            <td>
              {{ income.reference || '—' }}
            </td>


            <td>
              {{ income.created_by_name || 'System' }}
            </td>


            <td class="income-notes-cell">
              {{ income.notes || '—' }}
            </td>


            <td>

              <div class="income-row-actions">

                <button
                  type="button"
                  class="income-action-btn income-edit-btn"
                  title="Edit other income"
                  :disabled="
                    income.can_edit === false
                    ||
                    saving
                  "
                  @click="openEditModal(income)"
                >
                  <i class="bi bi-pencil"></i>
                </button>


                <button
                  type="button"
                  class="income-action-btn income-delete-btn"
                  title="Delete other income"
                  :disabled="
                    income.can_delete === false
                    ||
                    deleteLoadingId === income.id
                  "
                  @click="deleteIncome(income)"
                >

                  <span
                    v-if="
                      deleteLoadingId === income.id
                    "
                    class="income-inline-spinner"
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

    <footer
      v-if="!loading"
      class="income-pagination"
    >

      <div>

        Showing

        <strong>
          {{
            meta.total === 0
              ? 0
              : (
                  (meta.current_page - 1)
                  * meta.per_page
                ) + 1
          }}
        </strong>

        —

        <strong>
          {{
            Math.min(
              meta.current_page
              * meta.per_page,
              meta.total
            )
          }}
        </strong>

        of

        <strong>
          {{ meta.total }}
        </strong>

      </div>


      <div class="income-pagination-controls">

        <button
          type="button"
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


        <span>
          Page
          <strong>
            {{ meta.current_page }}
          </strong>
          of
          <strong>
            {{ meta.last_page }}
          </strong>
        </span>


        <button
          type="button"
          :disabled="
            meta.current_page >=
              meta.last_page
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

    </footer>

  </section>


  <!-- Add / Edit Modal -->

  <div
    v-if="showModal"
    class="income-modal-overlay"
    @mousedown.self="closeModal"
  >

    <div class="income-modal">

      <header class="income-modal-header">

        <div>

          <span>
            Accounting / Other Income
          </span>

          <h3>
            {{
              editingIncome
                ? 'Edit Other Income'
                : 'Add Other Income'
            }}
          </h3>

        </div>


        <button
          type="button"
          :disabled="saving"
          @click="closeModal"
        >
          <i class="bi bi-x-lg"></i>
        </button>

      </header>


      <form
        class="income-modal-form"
        @submit.prevent="saveIncome"
      >

        <div class="income-modal-body">

          <div class="income-form-grid">

            <div class="income-field income-field-full">

              <div class="income-label-row">

                <label>
                  Income Category *
                </label>

                <button
                  type="button"
                  class="income-quick-category-toggle"
                  @click="
                    showQuickCategory =
                      !showQuickCategory
                  "
                >

                  <i class="bi bi-plus-circle"></i>

                  Add Category

                </button>

              </div>


              <select
                v-model="form.income_category_id"
                required
              >

                <option value="">
                  Select category
                </option>

                <option
                  v-for="category in categories"
                  :key="category.id"
                  :value="String(category.id)"
                >
                  {{ category.name }}
                </option>

              </select>

            </div>


            <!-- Quick Add Category -->

            <div
              v-if="showQuickCategory"
              class="income-quick-category income-field-full"
            >

              <div class="income-field">

                <label>
                  New Category Name *
                </label>

                <input
                  v-model.trim="categoryForm.name"
                  type="text"
                  maxlength="150"
                  placeholder="Example: Plastic Bottle Sale"
                />

              </div>


              <div class="income-field">

                <label>
                  Description
                </label>

                <input
                  v-model.trim="categoryForm.description"
                  type="text"
                  maxlength="2000"
                  placeholder="Optional"
                />

              </div>


              <button
                type="button"
                class="income-save-category-btn"
                :disabled="
                  categorySaving
                  ||
                  !categoryForm.name
                "
                @click="saveCategory"
              >

                <span
                  v-if="categorySaving"
                  class="income-inline-spinner"
                ></span>

                <i
                  v-else
                  class="bi bi-check2"
                ></i>

                {{
                  categorySaving
                    ? 'Saving...'
                    : 'Save Category'
                }}

              </button>

            </div>


            <div class="income-field">

              <label>
                Income Date *
              </label>

              <input
                v-model="form.income_date"
                type="date"
                :max="todayDate"
                required
              />

            </div>


            <div class="income-field">

              <label>
                Amount *
              </label>

              <input
                v-model="form.amount"
                type="number"
                min="0.01"
                step="0.01"
                placeholder="0.00"
                required
              />

            </div>


            <div class="income-field">

              <label>
                Received Via *
              </label>

              <select
                v-model="form.payment_method"
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

            </div>


            <div class="income-field">

              <label>
                Reference
              </label>

              <input
                v-model.trim="form.reference"
                type="text"
                maxlength="150"
                placeholder="Receipt, buyer or transaction reference"
              />

            </div>


            <div class="income-field income-field-full">

              <label>
                Notes
              </label>

              <textarea
                v-model.trim="form.notes"
                rows="4"
                maxlength="2000"
                placeholder="Example: Sold accumulated waste paper to local recycler."
              ></textarea>

            </div>

          </div>


          <div class="income-accounting-preview">

            <i class="bi bi-journal-check"></i>

            <div>

              <strong>
                Future Ledger Preview
              </strong>

              <span>
                {{
                  paymentAccountPreview
                }}
                will be debited and Other Income will be credited when the Ledger module is connected.
              </span>

            </div>

          </div>

        </div>


        <footer class="income-modal-footer">

          <button
            type="button"
            class="income-secondary-btn"
            :disabled="saving"
            @click="closeModal"
          >
            Cancel
          </button>


          <button
            type="submit"
            class="income-primary-btn"
            :disabled="
              saving
              ||
              !isFormReady
            "
          >

            <span
              v-if="saving"
              class="income-button-spinner"
            ></span>

            <i
              v-else
              class="bi bi-check-lg"
            ></i>

            {{
              saving
                ? 'Saving...'
                : (
                    editingIncome
                      ? 'Update Income'
                      : 'Save Income'
                  )
            }}

          </button>

        </footer>

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

import incomeService
  from '@/services/incomeService'


/*
|--------------------------------------------------------------------------
| Date Helpers
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

  return localDate(
    date
  )
}


const todayDate =
  localDate()


/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const incomes =
  ref([])

const categories =
  ref([])

const paymentMethods =
  ref([])

const loading =
  ref(false)

const saving =
  ref(false)

const categorySaving =
  ref(false)

const deleteLoadingId =
  ref(null)

const errorMessage =
  ref('')

const showModal =
  ref(false)

const showQuickCategory =
  ref(false)

const editingIncome =
  ref(null)


/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

const filters =
  reactive({

    search: '',

    from_date:
      firstDayOfMonth(),

    to_date:
      todayDate,

    income_category_id: '',

    payment_method: '',

    page: 1,

    per_page: 10,

  })


/*
|--------------------------------------------------------------------------
| Meta
|--------------------------------------------------------------------------
*/

const meta =
  reactive({

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

    total_records: 0,

    total_amount: 0,

    cash_amount: 0,

    bank_amount: 0,

    mobile_wallet_amount: 0,

  })


/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/

const form =
  reactive(
    emptyForm()
  )


const categoryForm =
  reactive({

    name: '',

    description: '',

  })


/*
|--------------------------------------------------------------------------
| Message
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

const isFormReady =
  computed(() => {

    return Boolean(
      form.income_category_id
      &&
      form.income_date
      &&
      Number(
        form.amount
      ) > 0
      &&
      form.payment_method
    )
  })


const periodLabel =
  computed(() => {

    return (
      formatDate(
        filters.from_date
      )
      +
      ' — '
      +
      formatDate(
        filters.to_date
      )
    )
  })


const paymentAccountPreview =
  computed(() => {

    switch (
      form.payment_method
    ) {

      case 'cash':
        return 'Cash in Hand'

      case 'bank_transfer':
        return 'Bank'

      case 'bkash':
        return 'bKash Wallet'

      case 'nagad':
        return 'Nagad Wallet'

      case 'card':
        return 'Card Settlement Account'

      case 'mixed':
        return 'Mixed Payment Asset'

      default:
        return 'Receiving Asset Account'
    }
  })


/*
|--------------------------------------------------------------------------
| Response Helpers
|--------------------------------------------------------------------------
*/

function payload(
  response
) {

  return response?.data
  ??
  response
}


function collection(
  response
) {

  const data =
    payload(
      response
    )

  return Array.isArray(
    data?.data
  )
    ? data.data
    : []
}


/*
|--------------------------------------------------------------------------
| Notifications
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
| Options
|--------------------------------------------------------------------------
*/

async function loadOptions()
{

  try {

    const response =
      await incomeService
        .getOptions()


    const data =
      payload(
        response
      )?.data
      ??
      {}


    categories.value =
      Array.isArray(
        data.categories
      )
        ? data.categories
        : []


    paymentMethods.value =
      Array.isArray(
        data.payment_methods
      )
        ? data.payment_methods
        : []

  }

  catch (error) {

    categories.value =
      []

    paymentMethods.value =
      []

    showMessage(
      incomeService
        .getErrorMessage(
          error,
          'Unable to load income options.'
        ),
      'error'
    )
  }
}


/*
|--------------------------------------------------------------------------
| Filter Payload
|--------------------------------------------------------------------------
*/

function buildFilterPayload(
  includePagination = true
) {

  const data = {

    search:
      filters.search
      ||
      undefined,

    from_date:
      filters.from_date
      ||
      undefined,

    to_date:
      filters.to_date
      ||
      undefined,

    income_category_id:
      filters.income_category_id
        ? Number(
            filters.income_category_id
          )
        : undefined,

    payment_method:
      filters.payment_method
      ||
      undefined,

  }


  if (includePagination) {

    data.page =
      filters.page

    data.per_page =
      filters.per_page
  }


  return data
}


/*
|--------------------------------------------------------------------------
| Load List
|--------------------------------------------------------------------------
*/

async function loadIncomes()
{

  loading.value =
    true

  errorMessage.value =
    ''


  try {

    const response =
      await incomeService
        .getIncomes(
          buildFilterPayload(
            true
          )
        )


    const data =
      payload(
        response
      )


    incomes.value =
      collection(
        response
      )


    Object.assign(
      meta,
      data?.meta
      ??
      {
        current_page: 1,
        last_page: 1,
        per_page: 10,
        total: 0,
      }
    )

  }

  catch (error) {

    incomes.value =
      []

    errorMessage.value =
      incomeService
        .getErrorMessage(
          error,
          'Unable to load other income.'
        )

  }

  finally {

    loading.value =
      false
  }
}


/*
|--------------------------------------------------------------------------
| Load Summary
|--------------------------------------------------------------------------
*/

async function loadSummary()
{

  try {

    const response =
      await incomeService
        .getSummary(
          buildFilterPayload(
            false
          )
        )


    const data =
      payload(
        response
      )?.data
      ??
      {}


    Object.assign(
      summary,
      {

        total_records:
          Number(
            data.total_records
            ??
            0
          ),

        total_amount:
          Number(
            data.total_amount
            ??
            0
          ),

        cash_amount:
          Number(
            data.cash_amount
            ??
            0
          ),

        bank_amount:
          Number(
            data.bank_amount
            ??
            0
          ),

        mobile_wallet_amount:
          Number(
            data.mobile_wallet_amount
            ??
            0
          ),

      }
    )

  }

  catch (error) {

    Object.assign(
      summary,
      {

        total_records: 0,

        total_amount: 0,

        cash_amount: 0,

        bank_amount: 0,

        mobile_wallet_amount: 0,

      }
    )
  }
}


/*
|--------------------------------------------------------------------------
| Refresh
|--------------------------------------------------------------------------
*/

async function refreshAll()
{

  await Promise.all([
    loadIncomes(),
    loadSummary(),
  ])
}


/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

async function applyFilters()
{

  if (
    filters.to_date
    <
    filters.from_date
  ) {

    showMessage(
      'To date cannot be before from date.',
      'error'
    )

    return
  }


  filters.page =
    1


  await refreshAll()
}


async function clearFilters()
{

  filters.search =
    ''

  filters.from_date =
    firstDayOfMonth()

  filters.to_date =
    todayDate

  filters.income_category_id =
    ''

  filters.payment_method =
    ''

  filters.page =
    1


  await refreshAll()
}


/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/

async function changePage(
  page
) {

  const value =
    Number(
      page
    )


  if (
    value < 1
    ||
    value >
      meta.last_page
  ) {
    return
  }


  filters.page =
    value


  await loadIncomes()
}


function serialNumber(
  index
) {

  return (
    (
      Number(
        meta.current_page
        ||
        1
      )
      -
      1
    )
    *
    Number(
      meta.per_page
      ||
      10
    )
  )
  +
  index
  +
  1
}


/*
|--------------------------------------------------------------------------
| Modal
|--------------------------------------------------------------------------
*/

function emptyForm()
{

  return {

    income_category_id: '',

    income_date:
      todayDate,

    amount: '',

    payment_method: 'cash',

    reference: '',

    notes: '',

  }
}


function resetForm()
{

  Object.assign(
    form,
    emptyForm()
  )


  Object.assign(
    categoryForm,
    {
      name: '',
      description: '',
    }
  )


  showQuickCategory.value =
    false
}


function openCreateModal()
{

  editingIncome.value =
    null

  resetForm()

  showModal.value =
    true
}


function openEditModal(
  income
) {

  if (
    income?.can_edit ===
    false
  ) {
    return
  }


  editingIncome.value = {
    ...income,
  }


  Object.assign(
    form,
    {

      income_category_id:
        String(
          income
            .income_category_id
          ??
          ''
        ),

      income_date:
        income.income_date
        ||
        todayDate,

      amount:
        Number(
          income.amount
          ||
          0
        ),

      payment_method:
        income.payment_method
        ||
        'cash',

      reference:
        income.reference
        ||
        '',

      notes:
        income.notes
        ||
        '',

    }
  )


  showQuickCategory.value =
    false

  showModal.value =
    true
}


function closeModal()
{

  if (saving.value) {
    return
  }


  showModal.value =
    false

  editingIncome.value =
    null

  resetForm()
}


/*
|--------------------------------------------------------------------------
| Quick Category
|--------------------------------------------------------------------------
*/

async function saveCategory()
{

  if (
    !categoryForm.name
    ||
    categorySaving.value
  ) {
    return
  }


  categorySaving.value =
    true


  try {

    const response =
      await incomeService
        .createCategory({

          name:
            categoryForm.name,

          description:
            categoryForm.description
            ||
            null,

        })


    const newCategory =
      payload(
        response
      )?.data


    await loadOptions()


    if (
      newCategory?.id
    ) {

      form.income_category_id =
        String(
          newCategory.id
        )
    }


    Object.assign(
      categoryForm,
      {
        name: '',
        description: '',
      }
    )


    showQuickCategory.value =
      false


    showMessage(
      payload(
        response
      )?.message
      ??
      'Income category saved successfully.'
    )

  }

  catch (error) {

    showMessage(
      incomeService
        .getErrorMessage(
          error,
          'Unable to save income category.'
        ),
      'error'
    )
  }

  finally {

    categorySaving.value =
      false
  }
}


/*
|--------------------------------------------------------------------------
| Save Income
|--------------------------------------------------------------------------
*/

async function saveIncome()
{

  if (
    !isFormReady.value
    ||
    saving.value
  ) {
    return
  }


  saving.value =
    true


  const data = {

    income_category_id:
      Number(
        form.income_category_id
      ),

    income_date:
      form.income_date,

    amount:
      Number(
        form.amount
      ),

    payment_method:
      form.payment_method,

    reference:
      form.reference
      ||
      null,

    notes:
      form.notes
      ||
      null,

  }


  try {

    const response =
      editingIncome.value

        ? await incomeService
            .updateIncome(
              editingIncome
                .value
                .id,
              data
            )

        : await incomeService
            .createIncome(
              data
            )


    showModal.value =
      false

    editingIncome.value =
      null

    resetForm()


    filters.page =
      1


    await refreshAll()


    showMessage(
      payload(
        response
      )?.message
      ??
      'Other income saved successfully.'
    )

  }

  catch (error) {

    showMessage(
      incomeService
        .getErrorMessage(
          error,
          'Unable to save other income.'
        ),
      'error'
    )
  }

  finally {

    saving.value =
      false
  }
}


/*
|--------------------------------------------------------------------------
| Delete
|--------------------------------------------------------------------------
*/

async function deleteIncome(
  income
) {

  if (
    income?.can_delete ===
    false
  ) {
    return
  }


  const confirmed =
    window.confirm(
      `Delete ${income.category_name} income of ${income.amount_formatted}?`
    )


  if (!confirmed) {
    return
  }


  deleteLoadingId.value =
    income.id


  try {

    const response =
      await incomeService
        .deleteIncome(
          income.id
        )


    if (
      incomes.value.length === 1
      &&
      filters.page > 1
    ) {
      filters.page -=
        1
    }


    await refreshAll()


    showMessage(
      payload(
        response
      )?.message
      ??
      'Other income deleted successfully.'
    )

  }

  catch (error) {

    showMessage(
      incomeService
        .getErrorMessage(
          error,
          'Unable to delete other income.'
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
| Format
|--------------------------------------------------------------------------
*/

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
  ).format(
    parsed
  )
}


/*
|--------------------------------------------------------------------------
| Lifecycle
|--------------------------------------------------------------------------
*/

onMounted(
  async () => {

    await Promise.all([
      loadOptions(),
      refreshAll(),
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


<style src="@/assets/css/income/other-income.css"></style>
