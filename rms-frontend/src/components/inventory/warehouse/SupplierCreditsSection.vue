<template>
  <section
    class="supplier-credits-section"
    :aria-busy="loading"
  >
    <div class="warehouse-lot-section-heading">
      <div>
        <h3>Supplier Credits</h3>

        <p>
          Purchase Return credit balances and their application to later Purchase Orders.
        </p>
      </div>

      <span class="warehouse-lot-result-pill">
        {{ meta.total }} credit source(s)
      </span>
    </div>


    <div class="supplier-credit-summary-grid">
      <article>
        <span>Credit Issued</span>
        <strong>{{ summary.issued_amount_formatted }}</strong>
      </article>

      <article>
        <span>Applied to Purchase Orders</span>
        <strong>{{ summary.applied_amount_formatted }}</strong>
      </article>

      <article>
        <span>Available Supplier Credit</span>
        <strong>{{ summary.available_amount_formatted }}</strong>
      </article>

      <article>
        <span>Open Refund Receivable</span>
        <strong>{{ accountingOpenRefund }}</strong>
      </article>
    </div>


    <div class="warehouse-lot-filter-card">
      <div class="warehouse-expiry-filter-row">
        <div class="warehouse-lot-field warehouse-lot-search-field">
          <label for="supplier-credit-search">
            Search
          </label>

          <div class="warehouse-lot-search-input">
            <i class="bi bi-search"></i>

            <input
              id="supplier-credit-search"
              v-model.trim="search"
              type="search"
              placeholder="Settlement no, return no or supplier"
              :disabled="loading"
              @keyup.enter="applySearch"
            />
          </div>
        </div>

        <label class="supplier-credit-available-toggle">
          <input
            v-model="availableOnly"
            type="checkbox"
            :disabled="loading"
            @change="applySearch"
          />

          <span>Available only</span>
        </label>

        <button
          type="button"
          class="warehouse-lot-primary-button"
          :disabled="loading"
          @click="applySearch"
        >
          Refresh
        </button>
      </div>
    </div>


    <div
      v-if="errorMessage"
      class="warehouse-lot-alert warehouse-lot-alert-danger"
    >
      {{ errorMessage }}
    </div>


    <div
      v-if="loading && credits.length === 0"
      class="warehouse-lot-loading-state"
    >
      <span class="spinner-border spinner-border-sm"></span>
      Loading Supplier Credits...
    </div>


    <div
      v-else-if="!loading && credits.length === 0"
      class="warehouse-lot-empty-state"
    >
      <i class="bi bi-wallet2"></i>

      <strong>No Supplier Credit found</strong>

      <p>
        Settle a Purchase Return as Supplier Credit to create a reusable credit balance.
      </p>
    </div>


    <div
      v-else
      class="supplier-credit-list"
    >
      <article
        v-for="credit in credits"
        :key="credit.id"
        class="supplier-credit-card"
      >
        <header>
          <div>
            <span
              class="supplier-credit-status"
              :class="
                `supplier-credit-status-${credit.status}`
              "
            >
              {{ credit.status_label }}
            </span>

            <h4>{{ credit.settlement_no }}</h4>

            <p>
              {{ credit.supplier_name }}
              ·
              {{ credit.return_no || 'Purchase Return' }}
              ·
              {{ credit.issued_at_label }}
            </p>
          </div>

          <div class="supplier-credit-card-balance">
            <span>Available</span>
            <strong>{{ credit.available_amount_formatted }}</strong>
          </div>
        </header>


        <div class="supplier-credit-card-grid">
          <div>
            <span>Issued</span>
            <strong>{{ credit.issued_amount_formatted }}</strong>
          </div>

          <div>
            <span>Applied</span>
            <strong>{{ credit.applied_amount_formatted }}</strong>
          </div>

          <div>
            <span>Remaining</span>
            <strong>{{ credit.available_amount_formatted }}</strong>
          </div>
        </div>


        <div
          v-if="normalizedApplications(credit).length"
          class="supplier-credit-application-history"
        >
          <strong>Application History</strong>

          <div
            v-for="application in normalizedApplications(credit)"
            :key="application.id"
          >
            <span>
              {{ application.application_no }}
              ·
              PO #{{ application.purchase_order_id }}
              ·
              {{ application.applied_at_label }}
            </span>

            <strong>{{ application.amount_formatted }}</strong>
          </div>
        </div>


        <footer>
          <small>
            Supplier Credit never creates a cash payment. It reduces the target PO's effective due.
          </small>

          <button
            v-if="
              canManage
              &&
              Number(
                credit.available_amount || 0,
              ) > 0
            "
            type="button"
            class="supplier-credit-apply-open-button"
            @click="openApply(credit)"
          >
            <i class="bi bi-receipt-cutoff"></i>
            Apply to Purchase Order
          </button>
        </footer>
      </article>
    </div>


    <div
      v-if="meta.last_page > 1"
      class="warehouse-lot-pagination"
    >
      <span>
        Showing {{ meta.from || 0 }}-{{ meta.to || 0 }} of {{ meta.total }}
      </span>

      <div>
        <button
          type="button"
          :disabled="loading || meta.current_page <= 1"
          @click="changePage(meta.current_page - 1)"
        >
          Previous
        </button>

        <span>
          Page {{ meta.current_page }} / {{ meta.last_page }}
        </span>

        <button
          type="button"
          :disabled="loading || meta.current_page >= meta.last_page"
          @click="changePage(meta.current_page + 1)"
        >
          Next
        </button>
      </div>
    </div>


    <section
      v-if="accountingSummary"
      class="warehouse-lot-detail-card supplier-credit-accounting-bridge"
    >
      <div class="warehouse-lot-card-heading">
        <div>
          <h3>Accounting Bridge</h3>

          <p>
            Read-only Purchase Return financial data prepared for the Accounting module.
          </p>
        </div>
      </div>

      <div class="supplier-credit-accounting-grid">
        <div>
          <span>Payable Offset</span>
          <strong>{{ money(accountingSummary.financial_activity?.payable_offset) }}</strong>
        </div>

        <div>
          <span>Supplier Credit Issued</span>
          <strong>{{ money(accountingSummary.financial_activity?.supplier_credit_issued) }}</strong>
        </div>

        <div>
          <span>Supplier Credit Applied</span>
          <strong>{{ money(accountingSummary.financial_activity?.supplier_credit_applied_to_po) }}</strong>
        </div>

        <div>
          <span>Cash Refunds</span>
          <strong>{{ money(accountingSummary.financial_activity?.cash_refund_total) }}</strong>
        </div>

        <div>
          <span>Current Open Credit</span>
          <strong>{{ money(accountingSummary.open_balances?.supplier_credit) }}</strong>
        </div>

        <div>
          <span>Current Refund Receivable</span>
          <strong>{{ money(accountingSummary.open_balances?.refund_receivable) }}</strong>
        </div>
      </div>
    </section>


    <SupplierCreditApplyModal
      :show="showApply"
      :credit="selectedCredit"
      @close="closeApply"
      @applied="handleApplied"
    />
  </section>
</template>


<script setup>
import {
  computed,
  reactive,
  ref,
  watch,
} from 'vue'

import supplierCreditService
  from '@/services/supplierCreditService'

import purchaseReturnAccountingService
  from '@/services/purchaseReturnAccountingService'

import SupplierCreditApplyModal
  from '@/components/inventory/warehouse/SupplierCreditApplyModal.vue'


const props = defineProps({
  refreshKey: {
    type: Number,
    default: 0,
  },

  canManage: {
    type: Boolean,
    default: false,
  },
})


const emit = defineEmits([
  'changed',
])


const credits =
  ref([])

const loading =
  ref(false)

const errorMessage =
  ref('')

const search =
  ref('')

const availableOnly =
  ref(false)

const page =
  ref(1)

const showApply =
  ref(false)

const selectedCredit =
  ref(null)

const accountingSummary =
  ref(null)


const summary =
  reactive({
    issued_amount: 0,
    issued_amount_formatted: '৳ 0.00',
    applied_amount: 0,
    applied_amount_formatted: '৳ 0.00',
    available_amount: 0,
    available_amount_formatted: '৳ 0.00',
  })


const meta =
  reactive({
    current_page: 1,
    last_page: 1,
    per_page: 20,
    total: 0,
    from: null,
    to: null,
  })


const accountingOpenRefund =
  computed(() => {
    return money(
      accountingSummary.value
        ?.open_balances
        ?.refund_receivable,
    )
  })


async function loadAll()
{
  loading.value = true
  errorMessage.value = ''

  try {
    const [
      creditsResponse,
      summaryResponse,
      accountingResponse,
    ] =
      await Promise.all([
        supplierCreditService
          .getCredits({
            search:
              search.value
              || undefined,

            available_only:
              availableOnly.value
                ? 1
                : undefined,

            page:
              page.value,

            per_page:
              20,
          }),

        supplierCreditService
          .getSummary(),

        purchaseReturnAccountingService
          .getSummary(),
      ])


    credits.value =
      Array.isArray(
        creditsResponse?.data,
      )
        ? creditsResponse.data
        : []


    Object.assign(
      meta,
      creditsResponse?.meta
      || {},
    )


    Object.assign(
      summary,
      summaryResponse?.data
      || {},
    )


    accountingSummary.value =
      accountingResponse?.data
      || null
  } catch (error) {
    errorMessage.value =
      supplierCreditService
        .getErrorMessage(
          error,
          'Unable to load Supplier Credit information.',
        )
  } finally {
    loading.value = false
  }
}


async function applySearch()
{
  page.value = 1
  await loadAll()
}


async function changePage(
  nextPage,
)
{
  const target =
    Number(nextPage)

  if (
    !Number.isInteger(target)
    ||
    target < 1
    ||
    target > Number(meta.last_page)
  ) {
    return
  }

  page.value = target
  await loadAll()
}


function openApply(
  credit,
)
{
  selectedCredit.value = {
    ...credit,
  }

  showApply.value = true
}


function closeApply()
{
  showApply.value = false
  selectedCredit.value = null
}


async function handleApplied()
{
  closeApply()

  await loadAll()

  emit('changed')
}


function normalizedApplications(
  credit,
)
{
  const value =
    credit?.applications?.data
    ??
    credit?.applications
    ??
    []

  return Array.isArray(value)
    ? value
    : []
}


function money(
  value,
)
{
  return `৳ ${Number(value || 0).toFixed(2)}`
}


watch(
  () => props.refreshKey,
  (next, previous) => {
    if (
      next !== previous
    ) {
      void loadAll()
    }
  },
  {
    immediate: true,
  },
)
</script>
