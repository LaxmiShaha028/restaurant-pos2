<template>

<section class="coa-page">

  <Transition name="coa-message">

    <div
      v-if="message.show"
      class="coa-message"
      :class="`coa-message-${message.type}`"
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

  <header class="coa-header">

    <div class="coa-header-left">

      <div class="coa-header-icon">
        <i class="bi bi-diagram-3"></i>
      </div>

      <div>

        <p class="coa-eyebrow">
          Accounting
        </p>

        <h2>
          Chart of Accounts
        </h2>

        <p>
          Define ledger accounts and map income, expense and payment sources.
        </p>

      </div>

    </div>


    <button
      v-if="activeTab === 'accounts'"
      type="button"
      class="coa-primary-btn"
      @click="openCreateModal"
    >

      <i class="bi bi-plus-lg"></i>

      Add Ledger Account

    </button>

  </header>

  <!-- Summary -->

  <div class="coa-summary-grid">

    <div class="coa-summary-card coa-summary-primary">

      <span>
        Total Accounts
      </span>

      <strong>
        {{ summary.total_accounts }}
      </strong>

      <small>
        {{ summary.active_accounts }} active
      </small>

    </div>


    <div class="coa-summary-card">

      <span>
        Asset Accounts
      </span>

      <strong>
        {{ summary.asset_accounts }}
      </strong>

      <small>
        Debit normal balance
      </small>

    </div>


    <div class="coa-summary-card">

      <span>
        Revenue Accounts
      </span>

      <strong>
        {{ summary.revenue_accounts }}
      </strong>

      <small>
        Credit normal balance
      </small>

    </div>


    <div class="coa-summary-card">

      <span>
        Expense + COGS
      </span>

      <strong>
        {{ summary.expense_accounts }}
      </strong>

      <small>
        Debit normal balance
      </small>

    </div>


    <div class="coa-summary-card coa-summary-warning">

      <span>
        Unmapped Categories
      </span>

      <strong>
        {{
          summary.unmapped_income_categories
          +
          summary.unmapped_expense_categories
        }}
      </strong>

      <small>
        Must be 0 before Journal posting
      </small>

    </div>

  </div>


  <!-- Tabs -->

  <div class="coa-tabs">

    <button
      type="button"
      class="coa-tab-btn"
      :class="{ active: activeTab === 'accounts' }"
      @click="activeTab = 'accounts'"
    >
      <i class="bi bi-list-ul"></i>
      Ledger Accounts
    </button>


    <button
      type="button"
      class="coa-tab-btn"
      :class="{ active: activeTab === 'categories' }"
      @click="activeTab = 'categories'"
    >
      <i class="bi bi-link-45deg"></i>
      Category Mapping
    </button>


    <button
      type="button"
      class="coa-tab-btn"
      :class="{ active: activeTab === 'payments' }"
      @click="activeTab = 'payments'"
    >
      <i class="bi bi-credit-card"></i>
      Payment Mapping
    </button>

  </div>


  <!-- Accounts -->

  <template v-if="activeTab === 'accounts'">

    <div class="coa-filter-card">

      <div class="coa-filter-grid">

        <div class="coa-field coa-search-field">

          <label>
            Search
          </label>

          <div class="coa-search-input">

            <i class="bi bi-search"></i>

            <input
              v-model.trim="filters.search"
              type="text"
              placeholder="Account code, name or description"
              @keyup.enter="applyFilters"
            />

          </div>

        </div>


        <div class="coa-field">

          <label>
            Account Type
          </label>

          <select
            v-model="filters.account_type"
          >

            <option value="">
              All Types
            </option>

            <option
              v-for="type in accountTypes"
              :key="type.value"
              :value="type.value"
            >
              {{ type.label }}
            </option>

          </select>

        </div>


        <div class="coa-field">

          <label>
            Status
          </label>

          <select
            v-model="filters.is_active"
          >

            <option value="">
              All Status
            </option>

            <option value="1">
              Active
            </option>

            <option value="0">
              Inactive
            </option>

          </select>

        </div>


        <div class="coa-filter-actions">

          <button
            type="button"
            class="coa-primary-btn"
            :disabled="loading"
            @click="applyFilters"
          >

            <i class="bi bi-funnel"></i>
            Apply

          </button>


          <button
            type="button"
            class="coa-secondary-btn"
            :disabled="loading"
            @click="clearFilters"
          >
            Clear
          </button>

        </div>

      </div>

    </div>


    <div class="coa-table-card">

      <div class="coa-table-header">

        <div>

          <h3>
            Ledger Accounts
          </h3>

          <p>
            System accounts are locked to preserve accounting rules.
          </p>

        </div>

        <span class="coa-result-count">
          {{ meta.total }} account(s)
        </span>

      </div>


      <div class="coa-table-responsive">

        <table class="coa-table">

          <thead>

            <tr>

              <th>Code</th>
              <th>Account Name</th>
              <th>Type</th>
              <th>Normal Balance</th>
              <th>Parent</th>
              <th>Posting</th>
              <th>Status</th>
              <th>Action</th>

            </tr>

          </thead>


          <tbody>

            <tr v-if="loading">

              <td
                colspan="8"
                class="coa-state-cell"
              >
                <span class="coa-spinner"></span>
                Loading chart of accounts...
              </td>

            </tr>


            <tr v-else-if="errorMessage">

              <td
                colspan="8"
                class="coa-state-cell coa-error-cell"
              >
                {{ errorMessage }}
              </td>

            </tr>


            <tr v-else-if="accounts.length === 0">

              <td
                colspan="8"
                class="coa-state-cell"
              >

                <div class="coa-empty">

                  <i class="bi bi-diagram-3"></i>

                  <strong>
                    No ledger accounts found
                  </strong>

                  <span>
                    Try changing your filters.
                  </span>

                </div>

              </td>

            </tr>


            <tr
              v-for="account in accounts"
              v-else
              :key="account.id"
            >

              <td>
                <strong class="coa-code">
                  {{ account.code }}
                </strong>
              </td>


              <td>

                <div class="coa-account-name">

                  <strong>
                    {{ account.name }}
                  </strong>

                  <small
                    v-if="account.is_system"
                  >
                    System Account
                  </small>

                </div>

              </td>


              <td>

                <span
                  class="coa-type-badge"
                  :class="`type-${account.account_type}`"
                >
                  {{ account.account_type_label }}
                </span>

              </td>


              <td>

                <span
                  class="coa-balance-badge"
                  :class="account.normal_balance"
                >
                  {{ account.normal_balance_label }}
                </span>

              </td>


              <td>
                {{
                  account.parent
                    ? `${account.parent.code} — ${account.parent.name}`
                    : '—'
                }}
              </td>


              <td>

                <span
                  :class="
                    account.allow_manual_posting
                      ? 'coa-posting-yes'
                      : 'coa-posting-no'
                  "
                >
                  {{
                    account.allow_manual_posting
                      ? 'Posting'
                      : 'Group'
                  }}
                </span>

              </td>


              <td>

                <span
                  class="coa-status-badge"
                  :class="
                    account.is_active
                      ? 'active'
                      : 'inactive'
                  "
                >
                  {{
                    account.is_active
                      ? 'Active'
                      : 'Inactive'
                  }}
                </span>

              </td>


              <td>

                <div class="coa-actions">

                  <button
                    type="button"
                    class="coa-action-btn"
                    title="Edit"
                    :disabled="
                      account.can_edit_structure === false
                    "
                    @click="openEditModal(account)"
                  >
                    <i class="bi bi-pencil"></i>
                  </button>


                  <button
                    type="button"
                    class="coa-action-btn coa-delete-btn"
                    title="Delete"
                    :disabled="
                      account.can_delete === false
                      ||
                      deleteLoadingId === account.id
                    "
                    @click="deleteAccount(account)"
                  >

                    <span
                      v-if="
                        deleteLoadingId === account.id
                      "
                      class="coa-inline-spinner"
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


      <div
        v-if="!loading"
        class="coa-pagination"
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


        <div class="coa-pagination-controls">

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

  </template>


  <!-- Category Mapping -->

  <template v-else-if="activeTab === 'categories'">

    <div class="coa-mapping-grid">

      <section class="coa-mapping-card">

        <div class="coa-mapping-header">

          <div>

            <h3>
              Other Income Categories
            </h3>

            <p>
              Map each income category to a Revenue account.
            </p>

          </div>

          <span class="coa-mapping-type credit">
            Revenue = Credit
          </span>

        </div>


        <div class="coa-mapping-list">

          <div
            v-for="category in mappings.income_categories"
            :key="`income-${category.id}`"
            class="coa-mapping-row"
          >

            <div>

              <strong>
                {{ category.name }}
              </strong>

              <small>
                Other Income
              </small>

            </div>


            <select
              :value="
                category.ledger_account_id
                ??
                ''
              "
              :disabled="
                mappingLoadingKey ===
                `income-${category.id}`
              "
              @change="
                updateIncomeMapping(
                  category,
                  $event
                )
              "
            >

              <option value="">
                Unmapped
              </option>

              <option
                v-for="account in revenueAccounts"
                :key="account.id"
                :value="account.id"
              >
                {{ account.code }} — {{ account.name }}
              </option>

            </select>

          </div>

        </div>

      </section>


      <section class="coa-mapping-card">

        <div class="coa-mapping-header">

          <div>

            <h3>
              Expense Categories
            </h3>

            <p>
              Map each category to Expense or COGS.
            </p>

          </div>

          <span class="coa-mapping-type debit">
            Expense = Debit
          </span>

        </div>


        <div class="coa-mapping-list">

          <div
            v-for="category in mappings.expense_categories"
            :key="`expense-${category.id}`"
            class="coa-mapping-row"
          >

            <div>

              <strong>
                {{ category.name }}
              </strong>

              <small>
                Expense
              </small>

            </div>


            <select
              :value="
                category.ledger_account_id
                ??
                ''
              "
              :disabled="
                mappingLoadingKey ===
                `expense-${category.id}`
              "
              @change="
                updateExpenseMapping(
                  category,
                  $event
                )
              "
            >

              <option value="">
                Unmapped
              </option>

              <option
                v-for="account in expenseAccounts"
                :key="account.id"
                :value="account.id"
              >
                {{ account.code }} — {{ account.name }}
              </option>

            </select>

          </div>

        </div>

      </section>

    </div>

  </template>


  <!-- Payment Mapping -->

  <template v-else>

    <div class="coa-payment-card">

      <div class="coa-mapping-header">

        <div>

          <h3>
            Payment Method → Asset Account
          </h3>

          <p>
            Money received or paid through each method will use this asset account in the Journal.
          </p>

        </div>

        <span class="coa-mapping-type debit">
          Assets = Debit
        </span>

      </div>


      <div class="coa-payment-grid">

        <div
          v-for="mapping in mappings.payment_mappings"
          :key="mapping.payment_method"
          class="coa-payment-row"
        >

          <div class="coa-payment-icon">
            <i class="bi bi-wallet2"></i>
          </div>


          <div class="coa-payment-name">

            <strong>
              {{ mapping.payment_method_label }}
            </strong>

            <small>
              {{ mapping.payment_method }}
            </small>

          </div>


          <select
            :value="
              mapping.ledger_account_id
            "
            :disabled="
              mappingLoadingKey ===
              `payment-${mapping.payment_method}`
            "
            @change="
              updatePaymentMapping(
                mapping,
                $event
              )
            "
          >

            <option
              v-for="account in assetAccounts"
              :key="account.id"
              :value="account.id"
            >
              {{ account.code }} — {{ account.name }}
            </option>

          </select>

        </div>

      </div>

    </div>

  </template>


  <!-- Add / Edit Account Modal -->

  <div
    v-if="showModal"
    class="coa-modal-overlay"
    @mousedown.self="closeModal"
  >

    <div class="coa-modal">

      <div class="coa-modal-header">

        <div>

          <p>
            Accounting / Chart of Accounts
          </p>

          <h3>
            {{
              editingAccount
                ? 'Edit Ledger Account'
                : 'Add Ledger Account'
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

      </div>


      <form
        class="coa-modal-form"
        @submit.prevent="saveAccount"
      >

        <div class="coa-modal-body">

          <div class="coa-form-grid">

            <div class="coa-field">

              <label>
                Account Code *
              </label>

              <input
                v-model.trim="form.code"
                type="text"
                maxlength="20"
                placeholder="Example: 6800"
                required
              />

            </div>


            <div class="coa-field">

              <label>
                Account Type *
              </label>

              <select
                v-model="form.account_type"
                required
                @change="syncParentForType"
              >

                <option value="">
                  Select type
                </option>

                <option
                  v-for="type in accountTypes"
                  :key="type.value"
                  :value="type.value"
                >
                  {{ type.label }}
                </option>

              </select>

            </div>


            <div class="coa-field coa-field-full">

              <label>
                Account Name *
              </label>

              <input
                v-model.trim="form.name"
                type="text"
                maxlength="150"
                placeholder="Example: Staff Meal Expense"
                required
              />

            </div>


            <div class="coa-field coa-field-full">

              <label>
                Parent Account
              </label>

              <select
                v-model="form.parent_id"
              >

                <option value="">
                  No Parent / Root Account
                </option>

                <option
                  v-for="account in parentOptionsForType"
                  :key="account.id"
                  :value="String(account.id)"
                >
                  {{ account.code }} — {{ account.name }}
                </option>

              </select>

            </div>


            <div class="coa-rule-preview">

              <span>
                Normal Balance
              </span>

              <strong>
                {{ selectedNormalBalance }}
              </strong>

              <small>
                Automatically controlled by account type.
              </small>

            </div>


            <label class="coa-checkbox-card">

              <input
                v-model="form.allow_manual_posting"
                type="checkbox"
              />

              <span>

                <strong>
                  Allow Journal Posting
                </strong>

                <small>
                  Turn off for group/parent accounts that should only contain child accounts.
                </small>

              </span>

            </label>


            <div class="coa-field coa-field-full">

              <label>
                Description
              </label>

              <textarea
                v-model.trim="form.description"
                rows="3"
                maxlength="2000"
                placeholder="Optional accounting description"
              ></textarea>

            </div>

          </div>

        </div>


        <div class="coa-modal-footer">

          <button
            type="button"
            class="coa-secondary-btn"
            :disabled="saving"
            @click="closeModal"
          >
            Cancel
          </button>


          <button
            type="submit"
            class="coa-primary-btn"
            :disabled="
              saving
              ||
              !isFormReady
            "
          >

            <span
              v-if="saving"
              class="coa-inline-spinner"
            ></span>

            <i
              v-else
              class="bi bi-check-lg"
            ></i>

            {{
              saving
                ? 'Saving...'
                : (
                    editingAccount
                      ? 'Update Account'
                      : 'Save Account'
                  )
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
  watch,
} from 'vue'

import chartOfAccountService
  from '@/services/chartOfAccountService'


const activeTab =
  ref('accounts')

const accounts =
  ref([])

const accountTypes =
  ref([])

const parentAccounts =
  ref([])

const revenueAccounts =
  ref([])

const expenseAccounts =
  ref([])

const assetAccounts =
  ref([])

const loading =
  ref(false)

const saving =
  ref(false)

const deleteLoadingId =
  ref(null)

const mappingLoadingKey =
  ref(null)

const errorMessage =
  ref('')

const showModal =
  ref(false)

const editingAccount =
  ref(null)


const filters =
  reactive({

    search: '',

    account_type: '',

    is_active: '',

    page: 1,

    per_page: 10,

  })


const meta =
  reactive({

    current_page: 1,

    last_page: 1,

    per_page: 10,

    total: 0,

  })


const summary =
  reactive({

    total_accounts: 0,

    active_accounts: 0,

    asset_accounts: 0,

    revenue_accounts: 0,

    expense_accounts: 0,

    unmapped_income_categories: 0,

    unmapped_expense_categories: 0,

  })


const mappings =
  reactive({

    income_categories: [],

    expense_categories: [],

    payment_mappings: [],

  })


const form =
  reactive(
    emptyForm()
  )


const message =
  reactive({

    show: false,

    type: 'success',

    text: '',

  })


let messageTimer =
  null


const isFormReady =
  computed(() => {

    return Boolean(
      form.code
      &&
      form.name
      &&
      form.account_type
    )
  })


const selectedNormalBalance =
  computed(() => {

    const type =
      accountTypes.value
        .find(
          item =>
            item.value ===
            form.account_type
        )


    if (!type) {
      return '—'
    }


    return type.normal_balance ===
      'debit'
        ? 'Debit'
        : 'Credit'
  })


const parentOptionsForType =
  computed(() => {

    return parentAccounts.value
      .filter(
        account => {

          if (
            account.account_type !==
            form.account_type
          ) {
            return false
          }


          if (
            editingAccount.value
            &&
            Number(account.id) ===
            Number(
              editingAccount.value.id
            )
          ) {
            return false
          }


          return true
        }
      )
  })


function emptyForm()
{
  return {

    code: '',

    name: '',

    account_type: '',

    parent_id: '',

    allow_manual_posting: true,

    is_active: true,

    description: '',
  }
}


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
    await chartOfAccountService
      .getOptions()


  const data =
    payload(
      response
    )?.data
    ??
    {}


  accountTypes.value =
    Array.isArray(
      data.account_types
    )
      ? data.account_types
      : []


  parentAccounts.value =
    Array.isArray(
      data.parent_accounts
    )
      ? data.parent_accounts
      : []


  revenueAccounts.value =
    Array.isArray(
      data.revenue_accounts
    )
      ? data.revenue_accounts
      : []


  expenseAccounts.value =
    Array.isArray(
      data.expense_accounts
    )
      ? data.expense_accounts
      : []


  assetAccounts.value =
    Array.isArray(
      data.asset_accounts
    )
      ? data.asset_accounts
      : []
}


async function loadAccounts()
{
  loading.value =
    true

  errorMessage.value =
    ''


  try {

    const response =
      await chartOfAccountService
        .getAccounts({

          search:
            filters.search
            ||
            undefined,

          account_type:
            filters.account_type
            ||
            undefined,

          is_active:
            filters.is_active === ''
              ? undefined
              : Number(
                  filters.is_active
                ),

          page:
            filters.page,

          per_page:
            filters.per_page,
        })


    const data =
      payload(
        response
      )


    accounts.value =
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

    accounts.value =
      []

    errorMessage.value =
      chartOfAccountService
        .errorMessage(
          error,
          'Unable to load chart of accounts.'
        )

  }

  finally {

    loading.value =
      false
  }
}


async function loadSummary()
{
  try {

    const response =
      await chartOfAccountService
        .getSummary()


    Object.assign(
      summary,
      payload(
        response
      )?.data
      ??
      {}
    )

  }

  catch {
    //
  }
}


async function loadMappings()
{
  try {

    const response =
      await chartOfAccountService
        .getMappings()


    const data =
      payload(
        response
      )?.data
      ??
      {}


    mappings.income_categories =
      Array.isArray(
        data.income_categories
      )
        ? data.income_categories
        : []


    mappings.expense_categories =
      Array.isArray(
        data.expense_categories
      )
        ? data.expense_categories
        : []


    mappings.payment_mappings =
      Array.isArray(
        data.payment_mappings
      )
        ? data.payment_mappings
        : []

  }

  catch (error) {

    showMessage(
      chartOfAccountService
        .errorMessage(
          error,
          'Unable to load accounting mappings.'
        ),
      'error'
    )
  }
}


async function refreshAll()
{
  await Promise.all([
    loadAccounts(),
    loadSummary(),
    loadMappings(),
    loadOptions(),
  ])
}


async function applyFilters()
{
  filters.page =
    1

  await loadAccounts()
}


async function clearFilters()
{
  filters.search =
    ''

  filters.account_type =
    ''

  filters.is_active =
    ''

  filters.page =
    1

  await loadAccounts()
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


  await loadAccounts()
}


function resetForm()
{
  Object.assign(
    form,
    emptyForm()
  )
}


function openCreateModal()
{
  editingAccount.value =
    null

  resetForm()

  showModal.value =
    true
}


function openEditModal(
  account
) {

  if (
    account.can_edit_structure ===
    false
  ) {
    return
  }


  editingAccount.value = {
    ...account,
  }


  Object.assign(
    form,
    {

      code:
        account.code,

      name:
        account.name,

      account_type:
        account.account_type,

      parent_id:
        account.parent_id
          ? String(
              account.parent_id
            )
          : '',

      allow_manual_posting:
        Boolean(
          account.allow_manual_posting
        ),

      is_active:
        Boolean(
          account.is_active
        ),

      description:
        account.description
        ||
        '',
    }
  )


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

  editingAccount.value =
    null

  resetForm()
}


function syncParentForType()
{
  const parentStillValid =
    parentOptionsForType.value
      .some(
        account =>
          String(account.id) ===
          String(form.parent_id)
      )


  if (!parentStillValid) {
    form.parent_id =
      ''
  }
}


async function saveAccount()
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

    code:
      form.code,

    name:
      form.name,

    account_type:
      form.account_type,

    parent_id:
      form.parent_id
        ? Number(
            form.parent_id
          )
        : null,

    allow_manual_posting:
      Boolean(
        form.allow_manual_posting
      ),

    is_active:
      Boolean(
        form.is_active
      ),

    description:
      form.description
      ||
      null,
  }


  try {

    const response =
      editingAccount.value

        ? await chartOfAccountService
            .updateAccount(
              editingAccount
                .value
                .id,
              data
            )

        : await chartOfAccountService
            .createAccount(
              data
            )


    showModal.value =
      false

    editingAccount.value =
      null

    resetForm()


    await refreshAll()


    showMessage(
      payload(
        response
      )?.message
      ??
      'Ledger account saved successfully.'
    )

  }

  catch (error) {

    showMessage(
      chartOfAccountService
        .errorMessage(
          error,
          'Unable to save ledger account.'
        ),
      'error'
    )
  }

  finally {

    saving.value =
      false
  }
}


async function deleteAccount(
  account
) {

  const confirmed =
    window.confirm(
      `Delete ledger account ${account.code} — ${account.name}?`
    )


  if (!confirmed) {
    return
  }


  deleteLoadingId.value =
    account.id


  try {

    const response =
      await chartOfAccountService
        .deleteAccount(
          account.id
        )


    await refreshAll()


    showMessage(
      payload(
        response
      )?.message
      ??
      'Ledger account deleted successfully.'
    )

  }

  catch (error) {

    showMessage(
      chartOfAccountService
        .errorMessage(
          error,
          'Unable to delete ledger account.'
        ),
      'error'
    )
  }

  finally {

    deleteLoadingId.value =
      null
  }
}


async function updateIncomeMapping(
  category,
  event
) {

  const oldValue =
    category.ledger_account_id

  const newValue =
    event.target.value
      ? Number(
          event.target.value
        )
      : null


  const key =
    `income-${category.id}`


  mappingLoadingKey.value =
    key


  try {

    await chartOfAccountService
      .updateIncomeCategoryMapping(
        category.id,
        newValue
      )


    await Promise.all([
      loadMappings(),
      loadSummary(),
    ])


    showMessage(
      `${category.name} mapping updated.`
    )

  }

  catch (error) {

    event.target.value =
      oldValue
      ??
      ''


    showMessage(
      chartOfAccountService
        .errorMessage(
          error,
          'Unable to update income category mapping.'
        ),
      'error'
    )

  }

  finally {

    mappingLoadingKey.value =
      null
  }
}


async function updateExpenseMapping(
  category,
  event
) {

  const oldValue =
    category.ledger_account_id

  const newValue =
    event.target.value
      ? Number(
          event.target.value
        )
      : null


  const key =
    `expense-${category.id}`


  mappingLoadingKey.value =
    key


  try {

    await chartOfAccountService
      .updateExpenseCategoryMapping(
        category.id,
        newValue
      )


    await Promise.all([
      loadMappings(),
      loadSummary(),
    ])


    showMessage(
      `${category.name} mapping updated.`
    )

  }

  catch (error) {

    event.target.value =
      oldValue
      ??
      ''


    showMessage(
      chartOfAccountService
        .errorMessage(
          error,
          'Unable to update expense category mapping.'
        ),
      'error'
    )

  }

  finally {

    mappingLoadingKey.value =
      null
  }
}


async function updatePaymentMapping(
  mapping,
  event
) {

  const oldValue =
    mapping.ledger_account_id

  const newValue =
    Number(
      event.target.value
    )


  const key =
    `payment-${mapping.payment_method}`


  mappingLoadingKey.value =
    key


  try {

    await chartOfAccountService
      .updatePaymentMapping(
        mapping.payment_method,
        newValue
      )


    await loadMappings()


    showMessage(
      `${mapping.payment_method_label} mapping updated.`
    )

  }

  catch (error) {

    event.target.value =
      oldValue


    showMessage(
      chartOfAccountService
        .errorMessage(
          error,
          'Unable to update payment mapping.'
        ),
      'error'
    )

  }

  finally {

    mappingLoadingKey.value =
      null
  }
}


watch(
  activeTab,
  async () => {

    if (
      activeTab.value ===
      'categories'
      ||
      activeTab.value ===
      'payments'
    ) {
      await Promise.all([
        loadMappings(),
        loadOptions(),
        loadSummary(),
      ])
    }
  }
)


onMounted(
  async () => {

    try {

      await refreshAll()

    }

    catch (error) {

      showMessage(
        chartOfAccountService
          .errorMessage(
            error,
            'Unable to initialize accounting.'
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


<style src="@/assets/css/accounting/chart-of-accounts.css"></style>
