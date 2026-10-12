<template>

<section class="journal-page">

  <Transition name="journal-message">

    <div
      v-if="message.show"
      class="journal-message"
      :class="`journal-message-${message.type}`"
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


  <header class="journal-header">

    <div class="journal-header-left">

      <div class="journal-header-icon">
        <i class="bi bi-journal-text"></i>
      </div>

      <div>

        <p class="journal-eyebrow">
          Accounting
        </p>

        <h2>
          Journal Entries
        </h2>

        <p>
          Post balanced Debit/Credit entries and preserve immutable accounting history.
        </p>

      </div>

    </div>


    <button
      type="button"
      class="journal-primary-btn"
      @click="openCreateModal"
    >

      <i class="bi bi-plus-lg"></i>

      New Journal Entry

    </button>

  </header>


  <!-- <div class="journal-info-banner">

    <i class="bi bi-shield-lock"></i>

    <div>

      <strong>
        Posted journal lines cannot be edited or deleted.
      </strong>

      <span>
        If an entry is wrong, reverse it. The system creates equal opposite Debit/Credit lines and keeps the original history.
      </span>

    </div>

  </div> -->


  <div class="journal-summary-grid">

    <div class="journal-summary-card journal-summary-primary">

      <span>
        Visible Entries
      </span>

      <strong>
        {{ meta.total }}
      </strong>

      <small>
        Current filters
      </small>

    </div>


    <div class="journal-summary-card">

      <span>
        Page Debit
      </span>

      <strong>
        {{ money(pageDebit) }}
      </strong>

      <small>
        Debit total on current page
      </small>

    </div>


    <div class="journal-summary-card">

      <span>
        Page Credit
      </span>

      <strong>
        {{ money(pageCredit) }}
      </strong>

      <small>
        Credit total on current page
      </small>

    </div>


    <div class="journal-summary-card">

      <span>
        Balance Check
      </span>

      <strong
        :class="
          pageDifference === 0
            ? 'journal-balanced-text'
            : 'journal-unbalanced-text'
        "
      >
        {{
          pageDifference === 0
            ? 'Balanced'
            : money(pageDifference)
        }}
      </strong>

      <small>
        Debit - Credit
      </small>

    </div>

  </div>


  <div class="journal-filter-card">

    <div class="journal-filter-grid">

      <div class="journal-field journal-search-field">

        <label>
          Search
        </label>

        <div class="journal-search-input">

          <i class="bi bi-search"></i>

          <input
            v-model.trim="filters.search"
            type="text"
            placeholder="Journal #, description or account"
            @keyup.enter="applyFilters"
          />

        </div>

      </div>


      <div class="journal-field">

        <label>
          From
        </label>

        <input
          v-model="filters.from_date"
          type="date"
          :max="todayDate"
        />

      </div>


      <div class="journal-field">

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


      <div class="journal-field">

        <label>
          Type
        </label>

        <select
          v-model="filters.entry_type"
        >

          <option value="">
            All Types
          </option>

          <option
            v-for="type in entryTypes"
            :key="type.value"
            :value="type.value"
          >
            {{ type.label }}
          </option>

        </select>

      </div>


      <div class="journal-field">

        <label>
          Status
        </label>

        <select
          v-model="filters.status"
        >

          <option value="">
            All Status
          </option>

          <option
            v-for="status in statuses"
            :key="status.value"
            :value="status.value"
          >
            {{ status.label }}
          </option>

        </select>

      </div>


      <div class="journal-filter-actions">

        <button
          type="button"
          class="journal-primary-btn"
          :disabled="loading"
          @click="applyFilters"
        >
          <i class="bi bi-funnel"></i>
          Apply
        </button>


        <button
          type="button"
          class="journal-secondary-btn"
          :disabled="loading"
          @click="clearFilters"
        >
          Clear
        </button>

      </div>

    </div>

  </div>


  <div class="journal-table-card">

    <div class="journal-table-header">

      <div>

        <h3>
          Posted Journal History
        </h3>

        <p>
          Every row is a permanent accounting document.
        </p>

      </div>

      <span class="journal-result-count">
        {{ meta.total }} entry(s)
      </span>

    </div>


    <div class="journal-table-responsive">

      <table class="journal-table">

        <thead>

          <tr>

            <th>Date</th>
            <th>Journal #</th>
            <th>Description</th>
            <th>Type</th>
            <th>Debit</th>
            <th>Credit</th>
            <th>Status</th>
            <th>Posted By</th>
            <th>Action</th>

          </tr>

        </thead>


        <tbody>

          <tr v-if="loading">

            <td
              colspan="9"
              class="journal-state-cell"
            >
              <span class="journal-spinner"></span>
              Loading journal entries...
            </td>

          </tr>


          <tr v-else-if="errorMessage">

            <td
              colspan="9"
              class="journal-state-cell journal-error-cell"
            >
              {{ errorMessage }}
            </td>

          </tr>


          <tr v-else-if="entries.length === 0">

            <td
              colspan="9"
              class="journal-state-cell"
            >

              <div class="journal-empty">

                <i class="bi bi-journal-x"></i>

                <strong>
                  No journal entries found
                </strong>

                <span>
                  Create a balanced manual entry to test the journal engine.
                </span>

              </div>

            </td>

          </tr>


          <tr
            v-for="entry in entries"
            v-else
            :key="entry.id"
          >

            <td>
              {{ entry.journal_date_label }}
            </td>


            <td>

              <strong class="journal-number">
                {{ entry.journal_number }}
              </strong>

            </td>


            <td class="journal-description-cell">
              {{ entry.description }}
            </td>


            <td>

              <span
                class="journal-type-badge"
                :class="`type-${entry.entry_type}`"
              >
                {{ entry.entry_type_label }}
              </span>

            </td>


            <td>

              <strong class="journal-money">
                {{ entry.total_debit_formatted }}
              </strong>

            </td>


            <td>

              <strong class="journal-money">
                {{ entry.total_credit_formatted }}
              </strong>

            </td>


            <td>

              <span
                class="journal-status-badge"
                :class="entry.status"
              >
                {{ entry.status_label }}
              </span>

            </td>


            <td>
              {{ entry.posted_by_name }}
            </td>


            <td>

              <div class="journal-actions">

                <button
                  type="button"
                  class="journal-action-btn"
                  title="View"
                  @click="openDetails(entry)"
                >
                  <i class="bi bi-eye"></i>
                </button>


                <button
                  type="button"
                  class="journal-action-btn journal-reverse-btn"
                  title="Reverse"
                  :disabled="
                    !entry.can_reverse
                  "
                  @click="openReverseModal(entry)"
                >
                  <i class="bi bi-arrow-counterclockwise"></i>
                </button>

              </div>

            </td>

          </tr>

        </tbody>

      </table>

    </div>


    <div
      v-if="!loading"
      class="journal-pagination"
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


      <div class="journal-pagination-controls">

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
        </button>

      </div>

    </div>

  </div>


  <!-- Manual Journal Modal -->

  <div
    v-if="showCreateModal"
    class="journal-modal-overlay"
    @mousedown.self="closeCreateModal"
  >

    <div class="journal-modal journal-create-modal">

      <div class="journal-modal-header">

        <div>

          <p>
            Accounting / Journal
          </p>

          <h3>
            New Manual Journal Entry
          </h3>

        </div>


        <button
          type="button"
          :disabled="saving"
          @click="closeCreateModal"
        >
          <i class="bi bi-x-lg"></i>
        </button>

      </div>


      <form
        class="journal-modal-form"
        @submit.prevent="saveJournal"
      >

        <div class="journal-modal-body">

          <div class="journal-form-grid">

            <div class="journal-field">

              <label>
                Journal Date *
              </label>

              <input
                v-model="form.journal_date"
                type="date"
                :max="todayDate"
                required
              />

            </div>


            <div class="journal-field journal-field-full">

              <label>
                Description *
              </label>

              <input
                v-model.trim="form.description"
                type="text"
                maxlength="2000"
                placeholder="Example: Owner invested cash into restaurant"
                required
              />

            </div>

          </div>


          <div class="journal-lines-header">

            <div>

              <h4>
                Debit / Credit Lines
              </h4>

              <p>
                Every line must contain either Debit or Credit.
              </p>

            </div>


            <button
              type="button"
              class="journal-secondary-btn"
              @click="addLine"
            >
              <i class="bi bi-plus-lg"></i>
              Add Line
            </button>

          </div>


          <div class="journal-lines-table-wrap">

            <table class="journal-lines-table">

              <thead>

                <tr>

                  <th>Account</th>
                  <th>Debit</th>
                  <th>Credit</th>
                  <th>Memo</th>
                  <th></th>

                </tr>

              </thead>


              <tbody>

                <tr
                  v-for="(line, index) in form.lines"
                  :key="line.key"
                >

                  <td>

                    <select
                      v-model="line.ledger_account_id"
                      required
                    >

                      <option value="">
                        Select account
                      </option>

                      <option
                        v-for="account in accounts"
                        :key="account.id"
                        :value="String(account.id)"
                      >
                        {{ account.code }} — {{ account.name }}
                      </option>

                    </select>

                  </td>


                  <td>

                    <input
                      v-model="line.debit"
                      type="number"
                      min="0"
                      step="0.01"
                      placeholder="0.00"
                      @input="
                        clearOpposite(
                          line,
                          'debit'
                        )
                      "
                    />

                  </td>


                  <td>

                    <input
                      v-model="line.credit"
                      type="number"
                      min="0"
                      step="0.01"
                      placeholder="0.00"
                      @input="
                        clearOpposite(
                          line,
                          'credit'
                        )
                      "
                    />

                  </td>


                  <td>

                    <input
                      v-model.trim="line.memo"
                      type="text"
                      maxlength="500"
                      placeholder="Optional"
                    />

                  </td>


                  <td>

                    <button
                      type="button"
                      class="journal-line-delete-btn"
                      :disabled="
                        form.lines.length <= 2
                      "
                      @click="
                        removeLine(index)
                      "
                    >
                      <i class="bi bi-trash"></i>
                    </button>

                  </td>

                </tr>

              </tbody>

            </table>

          </div>


          <div class="journal-balance-panel">

            <div>

              <span>
                Total Debit
              </span>

              <strong>
                {{ money(formDebit) }}
              </strong>

            </div>


            <div>

              <span>
                Total Credit
              </span>

              <strong>
                {{ money(formCredit) }}
              </strong>

            </div>


            <div
              class="journal-balance-status"
              :class="{
                balanced:
                  isFormBalanced,

                unbalanced:
                  !isFormBalanced,
              }"
            >

              <span>
                Difference
              </span>

              <strong>
                {{
                  isFormBalanced
                    ? '৳ 0.00 — Balanced'
                    : money(formDifference)
                }}
              </strong>

            </div>

          </div>


          <div class="journal-posting-warning">

            <i class="bi bi-lock"></i>

            <span>
              After posting, this entry cannot be edited or deleted. Corrections must use Reversal.
            </span>

          </div>

        </div>


        <div class="journal-modal-footer">

          <button
            type="button"
            class="journal-secondary-btn"
            :disabled="saving"
            @click="closeCreateModal"
          >
            Cancel
          </button>


          <button
            type="submit"
            class="journal-primary-btn"
            :disabled="
              saving
              ||
              !canPostForm
            "
          >

            <span
              v-if="saving"
              class="journal-inline-spinner"
            ></span>

            <i
              v-else
              class="bi bi-lock-fill"
            ></i>

            {{
              saving
                ? 'Posting...'
                : 'Post Journal'
            }}

          </button>

        </div>

      </form>

    </div>

  </div>


  <!-- Details Modal -->

  <div
    v-if="showDetailsModal"
    class="journal-modal-overlay"
    @mousedown.self="closeDetailsModal"
  >

    <div class="journal-modal">

      <div class="journal-modal-header">

        <div>

          <p>
            {{ selectedEntry?.entry_type_label }}
          </p>

          <h3>
            {{ selectedEntry?.journal_number }}
          </h3>

        </div>


        <button
          type="button"
          @click="closeDetailsModal"
        >
          <i class="bi bi-x-lg"></i>
        </button>

      </div>


      <div class="journal-modal-body">

        <div
          v-if="detailsLoading"
          class="journal-details-loading"
        >
          <span class="journal-spinner"></span>
          Loading journal...
        </div>


        <template v-else-if="selectedEntry">

          <div class="journal-details-summary">

            <div>

              <span>
                Date
              </span>

              <strong>
                {{ selectedEntry.journal_date_label }}
              </strong>

            </div>


            <div>

              <span>
                Status
              </span>

              <strong>
                {{ selectedEntry.status_label }}
              </strong>

            </div>


            <div>

              <span>
                Posted By
              </span>

              <strong>
                {{ selectedEntry.posted_by_name }}
              </strong>

            </div>


            <div>

              <span>
                Total
              </span>

              <strong>
                {{ selectedEntry.total_debit_formatted }}
              </strong>

            </div>

          </div>


          <div class="journal-details-description">

            <span>
              Description
            </span>

            <strong>
              {{ selectedEntry.description }}
            </strong>

          </div>


          <div
            v-if="selectedEntry.reversal_reason"
            class="journal-reversal-note"
          >

            <strong>
              Reversal Reason
            </strong>

            <span>
              {{ selectedEntry.reversal_reason }}
            </span>

          </div>


          <div class="journal-lines-table-wrap">

            <table class="journal-lines-table journal-details-lines">

              <thead>

                <tr>

                  <th>Account</th>
                  <th>Debit</th>
                  <th>Credit</th>

                </tr>

              </thead>


              <tbody>

                <tr
                  v-for="line in selectedEntry.lines"
                  :key="line.id"
                >

                  <td>

                    <strong>
                      {{ line.account_code }}
                    </strong>

                    —

                    {{ line.account_name }}

                  </td>

                  <td>
                    {{
                      line.debit > 0
                        ? line.debit_formatted
                        : '—'
                    }}
                  </td>

                  <td>
                    {{
                      line.credit > 0
                        ? line.credit_formatted
                        : '—'
                    }}
                  </td>

                </tr>

              </tbody>

            </table>

          </div>

        </template>

      </div>

    </div>

  </div>


  <!-- Reversal Modal -->

  <div
    v-if="showReverseModal"
    class="journal-modal-overlay"
    @mousedown.self="closeReverseModal"
  >

    <div class="journal-modal journal-reverse-modal">

      <div class="journal-modal-header">

        <div>

          <p>
            Accounting Correction
          </p>

          <h3>
            Reverse {{ reverseEntry?.journal_number }}
          </h3>

        </div>


        <button
          type="button"
          :disabled="reversing"
          @click="closeReverseModal"
        >
          <i class="bi bi-x-lg"></i>
        </button>

      </div>


      <form
        class="journal-modal-form"
        @submit.prevent="confirmReverse"
      >

        <div class="journal-modal-body">

          <div class="journal-reverse-preview">

            <span>
              Original Entry
            </span>

            <strong>
              {{ reverseEntry?.description }}
            </strong>

            <small>
              {{ reverseEntry?.total_debit_formatted }}
            </small>

          </div>


          <div class="journal-field">

            <label>
              Reversal Date *
            </label>

            <input
              v-model="reverseForm.reversal_date"
              type="date"
              :max="todayDate"
              required
            />

          </div>


          <div class="journal-field">

            <label>
              Reason *
            </label>

            <textarea
              v-model.trim="reverseForm.reason"
              rows="4"
              maxlength="500"
              placeholder="Explain why this posted journal must be reversed."
              required
            ></textarea>

          </div>


          <div class="journal-posting-warning journal-danger-warning">

            <i class="bi bi-arrow-counterclockwise"></i>

            <span>
              The original lines will remain permanently visible. A new opposite journal will be posted.
            </span>

          </div>

        </div>


        <div class="journal-modal-footer">

          <button
            type="button"
            class="journal-secondary-btn"
            :disabled="reversing"
            @click="closeReverseModal"
          >
            Cancel
          </button>


          <button
            type="submit"
            class="journal-danger-btn"
            :disabled="
              reversing
              ||
              !reverseForm.reason
              ||
              !reverseForm.reversal_date
            "
          >

            <span
              v-if="reversing"
              class="journal-inline-spinner"
            ></span>

            <i
              v-else
              class="bi bi-arrow-counterclockwise"
            ></i>

            {{
              reversing
                ? 'Reversing...'
                : 'Post Reversal'
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

import journalService
  from '@/services/journalService'


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


const entries =
  ref([])

const accounts =
  ref([])

const entryTypes =
  ref([])

const statuses =
  ref([])

const loading =
  ref(false)

const saving =
  ref(false)

const reversing =
  ref(false)

const detailsLoading =
  ref(false)

const errorMessage =
  ref('')

const showCreateModal =
  ref(false)

const showDetailsModal =
  ref(false)

const showReverseModal =
  ref(false)

const selectedEntry =
  ref(null)

const reverseEntry =
  ref(null)


const filters =
  reactive({

    search: '',

    from_date:
      firstDayOfMonth(),

    to_date:
      todayDate,

    entry_type: '',

    status: '',

    page: 1,

    per_page: 20,
  })


const meta =
  reactive({

    current_page: 1,

    last_page: 1,

    per_page: 20,

    total: 0,
  })


let lineKey =
  0


function emptyLine()
{
  lineKey +=
    1


  return {

    key:
      lineKey,

    ledger_account_id: '',

    debit: '',

    credit: '',

    memo: '',
  }
}


const form =
  reactive({

    journal_date:
      todayDate,

    description: '',

    lines: [
      emptyLine(),
      emptyLine(),
    ],
  })


const reverseForm =
  reactive({

    reversal_date:
      todayDate,

    reason: '',
  })


const message =
  reactive({

    show: false,

    type: 'success',

    text: '',
  })


let messageTimer =
  null


const formDebit =
  computed(() => {

    return roundMoney(
      form.lines.reduce(
        (
          sum,
          line
        ) =>
          sum
          +
          Number(
            line.debit
            ||
            0
          ),
        0
      )
    )
  })


const formCredit =
  computed(() => {

    return roundMoney(
      form.lines.reduce(
        (
          sum,
          line
        ) =>
          sum
          +
          Number(
            line.credit
            ||
            0
          ),
        0
      )
    )
  })


const formDifference =
  computed(() => {

    return roundMoney(
      formDebit.value
      -
      formCredit.value
    )
  })


const isFormBalanced =
  computed(() => {

    return (
      formDebit.value > 0
      &&
      formCredit.value > 0
      &&
      Math.abs(
        formDifference.value
      ) < 0.01
    )
  })


const canPostForm =
  computed(() => {

    return Boolean(
      form.journal_date
      &&
      form.description
      &&
      form.lines.length >= 2
      &&
      form.lines.every(
        line =>
          line.ledger_account_id
          &&
          (
            Number(
              line.debit
              ||
              0
            ) > 0
            ||
            Number(
              line.credit
              ||
              0
            ) > 0
          )
      )
      &&
      isFormBalanced.value
    )
  })


const pageDebit =
  computed(() => {

    return roundMoney(
      entries.value.reduce(
        (
          sum,
          entry
        ) =>
          sum
          +
          Number(
            entry.total_debit
            ||
            0
          ),
        0
      )
    )
  })


const pageCredit =
  computed(() => {

    return roundMoney(
      entries.value.reduce(
        (
          sum,
          entry
        ) =>
          sum
          +
          Number(
            entry.total_credit
            ||
            0
          ),
        0
      )
    )
  })


const pageDifference =
  computed(() => {

    return roundMoney(
      pageDebit.value
      -
      pageCredit.value
    )
  })


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


function roundMoney(
  value
) {
  return Math.round(
    (
      Number(value)
      +
      Number.EPSILON
    )
    *
    100
  ) / 100
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
        message.show =
          false
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


async function loadOptions()
{
  const response =
    await journalService
      .getOptions()


  const data =
    payload(
      response
    )?.data
    ??
    {}


  accounts.value =
    Array.isArray(
      data.accounts
    )
      ? data.accounts
      : []


  entryTypes.value =
    Array.isArray(
      data.entry_types
    )
      ? data.entry_types
      : []


  statuses.value =
    Array.isArray(
      data.statuses
    )
      ? data.statuses
      : []
}


async function loadEntries()
{
  loading.value =
    true

  errorMessage.value =
    ''


  try {

    const response =
      await journalService
        .getEntries({

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

          entry_type:
            filters.entry_type
            ||
            undefined,

          status:
            filters.status
            ||
            undefined,

          page:
            filters.page,

          per_page:
            filters.per_page,
        })


    const data =
      payload(
        response
      )


    entries.value =
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
        per_page: 20,
        total: 0,
      }
    )

  }

  catch (error) {

    entries.value =
      []

    errorMessage.value =
      journalService
        .errorMessage(
          error,
          'Unable to load journal entries.'
        )

  }

  finally {

    loading.value =
      false
  }
}


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


  await loadEntries()
}


async function clearFilters()
{
  filters.search =
    ''

  filters.from_date =
    firstDayOfMonth()

  filters.to_date =
    todayDate

  filters.entry_type =
    ''

  filters.status =
    ''

  filters.page =
    1


  await loadEntries()
}


async function changePage(
  page
) {

  const value =
    Number(page)


  if (
    value < 1
    ||
    value > meta.last_page
  ) {
    return
  }


  filters.page =
    value


  await loadEntries()
}


function resetForm()
{
  form.journal_date =
    todayDate

  form.description =
    ''

  form.lines.splice(
    0,
    form.lines.length,
    emptyLine(),
    emptyLine()
  )
}


function openCreateModal()
{
  resetForm()

  showCreateModal.value =
    true
}


function closeCreateModal()
{
  if (saving.value) {
    return
  }


  showCreateModal.value =
    false
}


function addLine()
{
  if (
    form.lines.length >= 50
  ) {
    return
  }


  form.lines.push(
    emptyLine()
  )
}


function removeLine(
  index
) {
  if (
    form.lines.length <= 2
  ) {
    return
  }


  form.lines.splice(
    index,
    1
  )
}


function clearOpposite(
  line,
  side
) {

  if (
    side === 'debit'
    &&
    Number(
      line.debit
      ||
      0
    ) > 0
  ) {
    line.credit =
      ''
  }


  if (
    side === 'credit'
    &&
    Number(
      line.credit
      ||
      0
    ) > 0
  ) {
    line.debit =
      ''
  }
}


async function saveJournal()
{
  if (
    !canPostForm.value
    ||
    saving.value
  ) {
    return
  }


  const confirmed =
    window.confirm(
      'Post this balanced journal? Posted financial lines cannot be edited or deleted.'
    )


  if (!confirmed) {
    return
  }


  saving.value =
    true


  try {

    const response =
      await journalService
        .createManual({

          journal_date:
            form.journal_date,

          description:
            form.description,

          lines:
            form.lines.map(
              line => ({

                ledger_account_id:
                  Number(
                    line.ledger_account_id
                  ),

                debit:
                  Number(
                    line.debit
                    ||
                    0
                  ),

                credit:
                  Number(
                    line.credit
                    ||
                    0
                  ),

                memo:
                  line.memo
                  ||
                  null,
              })
            ),
        })


    showCreateModal.value =
      false


    await loadEntries()


    showMessage(
      payload(
        response
      )?.message
      ??
      'Journal posted successfully.'
    )

  }

  catch (error) {

    showMessage(
      journalService
        .errorMessage(
          error,
          'Unable to post journal.'
        ),
      'error'
    )

  }

  finally {

    saving.value =
      false
  }
}


async function openDetails(
  entry
) {

  selectedEntry.value = {
    ...entry,
    lines: [],
  }

  showDetailsModal.value =
    true

  detailsLoading.value =
    true


  try {

    const response =
      await journalService
        .getEntry(
          entry.id
        )


    selectedEntry.value =
      payload(
        response
      )?.data
      ??
      entry

  }

  catch (error) {

    showMessage(
      journalService
        .errorMessage(
          error,
          'Unable to load journal details.'
        ),
      'error'
    )

  }

  finally {

    detailsLoading.value =
      false
  }
}


function closeDetailsModal()
{
  showDetailsModal.value =
    false

  selectedEntry.value =
    null
}


function openReverseModal(
  entry
) {

  if (!entry.can_reverse) {
    return
  }


  reverseEntry.value = {
    ...entry,
  }

  reverseForm.reversal_date =
    todayDate

  reverseForm.reason =
    ''

  showReverseModal.value =
    true
}


function closeReverseModal()
{
  if (reversing.value) {
    return
  }


  showReverseModal.value =
    false

  reverseEntry.value =
    null

  reverseForm.reason =
    ''
}


async function confirmReverse()
{
  if (
    !reverseEntry.value
    ||
    !reverseForm.reason
    ||
    !reverseForm.reversal_date
    ||
    reversing.value
  ) {
    return
  }


  const confirmed =
    window.confirm(
      `Reverse ${reverseEntry.value.journal_number}? A new opposite journal will be permanently posted.`
    )


  if (!confirmed) {
    return
  }


  reversing.value =
    true


  try {

    const response =
      await journalService
        .reverse(
          reverseEntry.value.id,
          {
            reason:
              reverseForm.reason,

            reversal_date:
              reverseForm.reversal_date,
          }
        )


    showReverseModal.value =
      false

    reverseEntry.value =
      null


    await loadEntries()


    showMessage(
      payload(
        response
      )?.message
      ??
      'Journal reversed successfully.'
    )

  }

  catch (error) {

    showMessage(
      journalService
        .errorMessage(
          error,
          'Unable to reverse journal.'
        ),
      'error'
    )

  }

  finally {

    reversing.value =
      false
  }
}


onMounted(
  async () => {

    try {

      await Promise.all([
        loadOptions(),
        loadEntries(),
      ])

    }

    catch (error) {

      showMessage(
        journalService
          .errorMessage(
            error,
            'Unable to initialize Journal Entry page.'
          ),
        'error'
      )
    }
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


<style src="@/assets/css/accounting/journal-entries.css"></style>
