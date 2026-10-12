<template>
  <Teleport to="body">
    <Transition name="inventory-modal">
      <div
        v-if="show"
        class="warehouse-lot-modal-backdrop"
        @click.self="requestClose"
      >
        <section
          class="warehouse-lot-modal purchase-return-settlement-modal"
          role="dialog"
          aria-modal="true"
          aria-labelledby="purchase-return-settlement-title"
        >
          <header class="warehouse-lot-modal-header">
            <div>
              <span class="warehouse-lot-modal-eyebrow">
                Supplier Financial Settlement
              </span>

              <h2 id="purchase-return-settlement-title">
                {{ purchaseReturn?.return_no || 'Purchase Return' }}
              </h2>

              <p>
                Allocate the supplier credit without changing the physical return.
              </p>
            </div>

            <button
              type="button"
              class="warehouse-lot-icon-button"
              :disabled="submitting || collecting"
              @click="requestClose"
            >
              <i class="bi bi-x-lg"></i>
            </button>
          </header>


          <div class="warehouse-lot-modal-body">
            <div
              v-if="errorMessage"
              class="warehouse-lot-alert warehouse-lot-alert-danger"
            >
              <i class="bi bi-exclamation-triangle"></i>
              {{ errorMessage }}
            </div>


            <section
              v-if="purchaseReturn"
              class="purchase-return-settlement-summary"
            >
              <article>
                <span>Supplier</span>
                <strong>{{ purchaseReturn.supplier_name }}</strong>
              </article>

              <article>
                <span>Return Credit</span>
                <strong>{{ purchaseReturn.settlement_target_amount_formatted }}</strong>
              </article>

              <article>
                <span>Allocated</span>
                <strong>{{ purchaseReturn.allocated_amount_formatted }}</strong>
              </article>

              <article>
                <span>Still Unallocated</span>
                <strong>{{ purchaseReturn.unallocated_amount_formatted }}</strong>
              </article>
            </section>


            <section
              v-if="
                purchaseReturn
                &&
                Number(purchaseReturn.unallocated_amount || 0) > 0
              "
              class="warehouse-lot-detail-card"
            >
              <div class="warehouse-lot-card-heading">
                <div>
                  <h3>Allocate Return Credit</h3>

                  <p>
                    Split the return across payable reduction, supplier credit or refund.
                  </p>
                </div>
              </div>


              <div class="purchase-return-settlement-form-grid">
                <div class="warehouse-lot-field">
                  <label for="return-settlement-type">
                    Settlement Type
                    <span>*</span>
                  </label>

                  <select
                    id="return-settlement-type"
                    v-model="allocationForm.settlement_type"
                    :disabled="submitting"
                    @change="handleTypeChanged"
                  >
                    <option
                      v-for="option in availableSettlementTypes"
                      :key="option.value"
                      :value="option.value"
                    >
                      {{ option.label }}
                    </option>
                  </select>
                </div>


                <div class="warehouse-lot-field">
                  <label for="return-settlement-amount">
                    Amount
                    <span>*</span>
                  </label>

                  <input
                    id="return-settlement-amount"
                    v-model="allocationForm.amount"
                    type="number"
                    min="0.01"
                    step="0.01"
                    :max="allocationMax"
                    :disabled="submitting"
                  />

                  <small class="warehouse-lot-field-help">
                    Max {{ money(allocationMax) }}
                  </small>

                  <small
                    v-if="fieldError(allocationErrors, 'amount')"
                    class="warehouse-lot-field-error"
                  >
                    {{ fieldError(allocationErrors, 'amount') }}
                  </small>
                </div>


                <div
                  v-if="allocationForm.settlement_type === 'payable_offset'"
                  class="warehouse-lot-field purchase-return-settlement-wide"
                >
                  <label for="return-settlement-po">
                    Original Purchase Order
                    <span>*</span>
                  </label>

                  <select
                    id="return-settlement-po"
                    v-model="allocationForm.purchase_order_id"
                    :disabled="submitting"
                    @change="clampAllocationAmount"
                  >
                    <option value="">
                      Select unpaid original PO
                    </option>

                    <option
                      v-for="po in payablePurchaseOrders"
                      :key="po.id"
                      :value="String(po.id)"
                    >
                      PO #{{ po.id }}
                      — Due {{ money(po.due_amount) }}
                    </option>
                  </select>

                  <small
                    v-if="fieldError(allocationErrors, 'purchase_order_id')"
                    class="warehouse-lot-field-error"
                  >
                    {{ fieldError(allocationErrors, 'purchase_order_id') }}
                  </small>
                </div>


                <template
                  v-if="allocationForm.settlement_type === 'direct_refund'"
                >
                  <div class="warehouse-lot-field">
                    <label for="return-direct-refund-method">
                      Refund Method
                      <span>*</span>
                    </label>

                    <select
                      id="return-direct-refund-method"
                      v-model="allocationForm.payment_method"
                      :disabled="submitting"
                    >
                      <option value="">Select method</option>

                      <option
                        v-for="method in purchaseReturnService.refundMethods"
                        :key="method.value"
                        :value="method.value"
                      >
                        {{ method.label }}
                      </option>
                    </select>

                    <small
                      v-if="fieldError(allocationErrors, 'payment_method')"
                      class="warehouse-lot-field-error"
                    >
                      {{ fieldError(allocationErrors, 'payment_method') }}
                    </small>
                  </div>


                  <div class="warehouse-lot-field">
                    <label for="return-direct-refund-reference">
                      Transaction Reference
                    </label>

                    <input
                      id="return-direct-refund-reference"
                      v-model.trim="allocationForm.transaction_reference"
                      type="text"
                      maxlength="255"
                      :disabled="submitting"
                      placeholder="Bank / wallet / cash reference"
                    />
                  </div>
                </template>


                <div class="warehouse-lot-field">
                  <label for="return-settlement-date">
                    Settlement Date / Time
                  </label>

                  <input
                    id="return-settlement-date"
                    v-model="allocationForm.settled_at"
                    type="datetime-local"
                    :max="nowLocal()"
                    :disabled="submitting"
                  />
                </div>


                <div class="warehouse-lot-field">
                  <label for="return-settlement-notes">
                    Notes
                  </label>

                  <input
                    id="return-settlement-notes"
                    v-model.trim="allocationForm.notes"
                    type="text"
                    maxlength="2000"
                    :disabled="submitting"
                    placeholder="Optional settlement note"
                  />
                </div>
              </div>


              <div class="purchase-return-settlement-effect">
                <i :class="settlementEffect.icon"></i>

                <div>
                  <strong>{{ settlementEffect.title }}</strong>
                  <p>{{ settlementEffect.message }}</p>
                </div>
              </div>


              <div class="purchase-return-settlement-action">
                <button
                  type="button"
                  class="purchase-return-settlement-save-button"
                  :disabled="submitting || !canAllocate"
                  @click="submitAllocation"
                >
                  <span
                    v-if="submitting"
                    class="spinner-border spinner-border-sm"
                  ></span>

                  <i
                    v-else
                    class="bi bi-check2-circle"
                  ></i>

                  Record Settlement
                </button>
              </div>
            </section>


            <section
              v-if="openReceivables.length"
              class="warehouse-lot-detail-card"
            >
              <div class="warehouse-lot-card-heading">
                <div>
                  <h3>Receive Outstanding Refund</h3>

                  <p>
                    Record money when the supplier actually pays a refund receivable.
                  </p>
                </div>

                <span>
                  {{ purchaseReturn.refund_receivable_balance_formatted }}
                </span>
              </div>


              <div class="purchase-return-settlement-form-grid">
                <div class="warehouse-lot-field purchase-return-settlement-wide">
                  <label for="return-receivable-select">
                    Refund Receivable
                    <span>*</span>
                  </label>

                  <select
                    id="return-receivable-select"
                    v-model="collectionForm.receivable_settlement_id"
                    :disabled="collecting"
                    @change="resetCollectionAmount"
                  >
                    <option
                      v-for="settlement in openReceivables"
                      :key="settlement.id"
                      :value="String(settlement.id)"
                    >
                      {{ settlement.settlement_no }}
                      — Balance {{ settlement.receivable_balance_formatted }}
                    </option>
                  </select>
                </div>


                <div class="warehouse-lot-field">
                  <label for="return-collection-amount">
                    Amount Received
                    <span>*</span>
                  </label>

                  <input
                    id="return-collection-amount"
                    v-model="collectionForm.amount"
                    type="number"
                    min="0.01"
                    step="0.01"
                    :max="collectionMax"
                    :disabled="collecting"
                  />

                  <small class="warehouse-lot-field-help">
                    Max {{ money(collectionMax) }}
                  </small>
                </div>


                <div class="warehouse-lot-field">
                  <label for="return-collection-method">
                    Received Via
                    <span>*</span>
                  </label>

                  <select
                    id="return-collection-method"
                    v-model="collectionForm.payment_method"
                    :disabled="collecting"
                  >
                    <option value="">Select method</option>

                    <option
                      v-for="method in purchaseReturnService.refundMethods"
                      :key="method.value"
                      :value="method.value"
                    >
                      {{ method.label }}
                    </option>
                  </select>
                </div>


                <div class="warehouse-lot-field">
                  <label for="return-collection-date">
                    Received Date / Time
                  </label>

                  <input
                    id="return-collection-date"
                    v-model="collectionForm.settled_at"
                    type="datetime-local"
                    :max="nowLocal()"
                    :disabled="collecting"
                  />
                </div>


                <div class="warehouse-lot-field">
                  <label for="return-collection-reference">
                    Transaction Reference
                  </label>

                  <input
                    id="return-collection-reference"
                    v-model.trim="collectionForm.transaction_reference"
                    type="text"
                    maxlength="255"
                    :disabled="collecting"
                    placeholder="Bank / wallet / cash reference"
                  />
                </div>
              </div>


              <div
                v-if="collectionErrorMessage"
                class="warehouse-lot-alert warehouse-lot-alert-danger"
              >
                {{ collectionErrorMessage }}
              </div>


              <div class="purchase-return-settlement-action">
                <button
                  type="button"
                  class="purchase-return-refund-collect-button"
                  :disabled="collecting || !canCollect"
                  @click="submitCollection"
                >
                  <span
                    v-if="collecting"
                    class="spinner-border spinner-border-sm"
                  ></span>

                  <i
                    v-else
                    class="bi bi-cash-stack"
                  ></i>

                  Receive Refund
                </button>
              </div>
            </section>


            <section
              v-if="settlements.length"
              class="warehouse-lot-detail-card"
            >
              <div class="warehouse-lot-card-heading">
                <div>
                  <h3>Settlement Ledger</h3>
                  <p>Immutable financial events for this Purchase Return.</p>
                </div>

                <span>{{ settlements.length }} allocation(s)</span>
              </div>


              <div class="purchase-return-settlement-ledger">
                <article
                  v-for="settlement in settlements"
                  :key="settlement.id"
                >
                  <div>
                    <strong>{{ settlement.settlement_type_label }}</strong>

                    <small>
                      {{ settlement.settlement_no }}
                      ·
                      {{ settlement.settled_at_label }}
                    </small>
                  </div>

                  <div>
                    <strong>{{ settlement.amount_formatted }}</strong>

                    <small v-if="settlement.cash_effect">
                      Cash effect
                    </small>

                    <small
                      v-else-if="
                        settlement.settlement_type === 'refund_receivable'
                      "
                    >
                      Balance {{ settlement.receivable_balance_formatted }}
                    </small>
                  </div>
                </article>
              </div>
            </section>
          </div>


          <footer class="warehouse-lot-modal-footer">
            <button
              type="button"
              class="warehouse-lot-secondary-button"
              :disabled="submitting || collecting"
              @click="requestClose"
            >
              Close
            </button>
          </footer>
        </section>
      </div>
    </Transition>
  </Teleport>
</template>


<script setup>
import {
  computed,
  reactive,
  ref,
  watch,
} from 'vue'

import purchaseReturnService
  from '@/services/purchaseReturnService'


const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },

  purchaseReturn: {
    type: Object,
    default: null,
  },
})


const emit = defineEmits([
  'close',
  'changed',
])


const submitting =
  ref(false)

const collecting =
  ref(false)

const errorMessage =
  ref('')

const collectionErrorMessage =
  ref('')

const allocationErrors =
  ref({})

const settlements =
  ref([])


const allocationForm =
  reactive({
    settlement_type: 'supplier_credit',
    amount: '',
    purchase_order_id: '',
    payment_method: '',
    settled_at: '',
    transaction_reference: '',
    notes: '',
  })


const collectionForm =
  reactive({
    receivable_settlement_id: '',
    amount: '',
    payment_method: '',
    settled_at: '',
    transaction_reference: '',
    notes: '',
  })


const payablePurchaseOrders =
  computed(() => {
    return Array.isArray(
      props.purchaseReturn?.linked_purchase_orders,
    )
      ? props.purchaseReturn.linked_purchase_orders
          .filter(
            (po) =>
              Number(po?.due_amount || 0) > 0,
          )
      : []
  })


const availableSettlementTypes =
  computed(() => {
    return purchaseReturnService
      .settlementTypes
      .filter(
        (option) => {
          if (
            option.value ===
            'payable_offset'
          ) {
            return payablePurchaseOrders.value.length > 0
          }

          return true
        },
      )
  })


const selectedPurchaseOrder =
  computed(() => {
    const id =
      Number(
        allocationForm.purchase_order_id,
      )

    return payablePurchaseOrders.value
      .find(
        (po) =>
          Number(po.id) === id,
      )
      || null
  })


const allocationMax =
  computed(() => {
    const remaining =
      Number(
        props.purchaseReturn?.unallocated_amount
        || 0,
      )

    if (
      allocationForm.settlement_type !==
      'payable_offset'
    ) {
      return Math.max(0, remaining)
    }

    const poDue =
      Number(
        selectedPurchaseOrder.value?.due_amount
        || 0,
      )

    return Math.max(
      0,
      Math.min(
        remaining,
        poDue,
      ),
    )
  })


const canAllocate =
  computed(() => {
    const amount =
      Number(
        allocationForm.amount,
      )

    if (
      !Number.isFinite(amount)
      ||
      amount <= 0
      ||
      amount > allocationMax.value
    ) {
      return false
    }

    if (
      allocationForm.settlement_type ===
      'payable_offset'
      &&
      !selectedPurchaseOrder.value
    ) {
      return false
    }

    if (
      allocationForm.settlement_type ===
      'direct_refund'
      &&
      !allocationForm.payment_method
    ) {
      return false
    }

    return true
  })


const openReceivables =
  computed(() => {
    return settlements.value
      .filter(
        (settlement) =>
          settlement?.settlement_type ===
            'refund_receivable'
          &&
          Number(
            settlement?.receivable_balance
            || 0,
          ) > 0,
      )
  })


const selectedReceivable =
  computed(() => {
    const id =
      Number(
        collectionForm.receivable_settlement_id,
      )

    return openReceivables.value
      .find(
        (settlement) =>
          Number(settlement.id) === id,
      )
      || null
  })


const collectionMax =
  computed(() => {
    return Math.max(
      0,
      Number(
        selectedReceivable.value?.receivable_balance
        || 0,
      ),
    )
  })


const canCollect =
  computed(() => {
    const amount =
      Number(
        collectionForm.amount,
      )

    return (
      Boolean(
        selectedReceivable.value,
      )
      &&
      Number.isFinite(amount)
      &&
      amount > 0
      &&
      amount <= collectionMax.value
      &&
      Boolean(
        collectionForm.payment_method,
      )
    )
  })


const settlementEffect =
  computed(() => {
    return {
      payable_offset: {
        icon: 'bi bi-receipt-cutoff',
        title: 'Supplier payable will decrease',
        message:
          'Original PO total and payment history stay unchanged. Only effective due decreases.',
      },

      supplier_credit: {
        icon: 'bi bi-wallet2',
        title: 'Supplier credit balance will be created',
        message:
          'No cash moves now. The supplier credit stays available for future use.',
      },

      refund_receivable: {
        icon: 'bi bi-hourglass-split',
        title: 'Refund receivable will be created',
        message:
          'No cash moves now. Use Receive Refund when the supplier actually pays.',
      },

      direct_refund: {
        icon: 'bi bi-cash-stack',
        title: 'Cash / bank refund will be recorded now',
        message:
          'This allocation has immediate cash effect.',
      },
    }[
      allocationForm.settlement_type
    ]
    || {
      icon: 'bi bi-info-circle',
      title: 'Settlement',
      message: '',
    }
  })


watch(
  () => props.show,
  (show) => {
    if (show) {
      void initialize()
    }
  },
)


watch(
  () => props.purchaseReturn?.id,
  () => {
    if (props.show) {
      void initialize()
    }
  },
)


async function initialize()
{
  resetAllocation()
  resetCollection()
  await loadSettlements()
}


async function loadSettlements()
{
  if (
    !props.purchaseReturn?.id
  ) {
    settlements.value = []
    return
  }

  try {
    const response =
      await purchaseReturnService
        .getSettlements(
          props.purchaseReturn.id,
        )

    settlements.value =
      Array.isArray(response?.data)
        ? response.data
        : []

    syncReceivableSelection()
  } catch (error) {
    errorMessage.value =
      purchaseReturnService
        .getErrorMessage(
          error,
          'Unable to load Purchase Return settlement ledger.',
        )
  }
}


function handleTypeChanged()
{
  allocationErrors.value = {}
  allocationForm.payment_method = ''
  allocationForm.transaction_reference = ''

  if (
    allocationForm.settlement_type ===
    'payable_offset'
  ) {
    allocationForm.purchase_order_id =
      payablePurchaseOrders.value[0]?.id
        ? String(
            payablePurchaseOrders.value[0].id,
          )
        : ''
  } else {
    allocationForm.purchase_order_id = ''
  }

  clampAllocationAmount()
}


function clampAllocationAmount()
{
  const current =
    Number(
      allocationForm.amount,
    )

  const max =
    allocationMax.value

  if (
    !Number.isFinite(current)
    ||
    current <= 0
    ||
    current > max
  ) {
    allocationForm.amount =
      max > 0
        ? max.toFixed(2)
        : ''
  }
}


async function submitAllocation()
{
  if (
    submitting.value
    ||
    !canAllocate.value
    ||
    !props.purchaseReturn?.id
  ) {
    return
  }

  submitting.value = true
  errorMessage.value = ''
  allocationErrors.value = {}

  try {
    await purchaseReturnService
      .createSettlement(
        props.purchaseReturn.id,
        {
          settlement_type:
            allocationForm.settlement_type,

          amount:
            Number(
              allocationForm.amount,
            ),

          purchase_order_id:
            allocationForm.settlement_type ===
              'payable_offset'
              ? Number(
                  allocationForm.purchase_order_id,
                )
              : null,

          payment_method:
            allocationForm.settlement_type ===
              'direct_refund'
              ? allocationForm.payment_method
              : null,

          settled_at:
            allocationForm.settled_at
            || null,

          transaction_reference:
            allocationForm.transaction_reference.trim()
            || null,

          notes:
            allocationForm.notes.trim()
            || null,
        },
      )

    emit('changed')

    resetAllocation()

    await loadSettlements()
  } catch (error) {
    allocationErrors.value =
      purchaseReturnService
        .getValidationErrors(
          error,
        )

    errorMessage.value =
      purchaseReturnService
        .getErrorMessage(
          error,
          'Unable to record Purchase Return settlement.',
        )
  } finally {
    submitting.value = false
  }
}


async function submitCollection()
{
  if (
    collecting.value
    ||
    !canCollect.value
    ||
    !props.purchaseReturn?.id
  ) {
    return
  }

  collecting.value = true
  collectionErrorMessage.value = ''

  try {
    await purchaseReturnService
      .collectRefund(
        props.purchaseReturn.id,
        {
          receivable_settlement_id:
            Number(
              collectionForm.receivable_settlement_id,
            ),

          amount:
            Number(
              collectionForm.amount,
            ),

          payment_method:
            collectionForm.payment_method,

          settled_at:
            collectionForm.settled_at
            || null,

          transaction_reference:
            collectionForm.transaction_reference.trim()
            || null,

          notes:
            collectionForm.notes.trim()
            || null,
        },
      )

    emit('changed')

    resetCollection()

    await loadSettlements()
  } catch (error) {
    collectionErrorMessage.value =
      purchaseReturnService
        .getErrorMessage(
          error,
          'Unable to record supplier refund collection.',
        )
  } finally {
    collecting.value = false
  }
}


function resetAllocation()
{
  const firstType =
    payablePurchaseOrders.value.length
      ? 'payable_offset'
      : 'supplier_credit'

  allocationForm.settlement_type =
    firstType

  allocationForm.purchase_order_id =
    firstType === 'payable_offset'
      &&
      payablePurchaseOrders.value[0]?.id
        ? String(
            payablePurchaseOrders.value[0].id,
          )
        : ''

  allocationForm.amount = ''
  allocationForm.payment_method = ''
  allocationForm.settled_at =
    nowLocal()
  allocationForm.transaction_reference = ''
  allocationForm.notes = ''

  allocationErrors.value = {}
  errorMessage.value = ''

  clampAllocationAmount()
}


function resetCollection()
{
  collectionForm.receivable_settlement_id = ''
  collectionForm.amount = ''
  collectionForm.payment_method = ''
  collectionForm.settled_at =
    nowLocal()
  collectionForm.transaction_reference = ''
  collectionForm.notes = ''
  collectionErrorMessage.value = ''

  syncReceivableSelection()
}


function syncReceivableSelection()
{
  const first =
    openReceivables.value[0]

  if (!first) {
    collectionForm.receivable_settlement_id = ''
    collectionForm.amount = ''
    return
  }

  collectionForm.receivable_settlement_id =
    String(first.id)

  resetCollectionAmount()
}


function resetCollectionAmount()
{
  collectionForm.amount =
    collectionMax.value > 0
      ? collectionMax.value.toFixed(2)
      : ''
}


function requestClose()
{
  if (
    submitting.value
    ||
    collecting.value
  ) {
    return
  }

  emit('close')
}


function fieldError(
  errors,
  field,
)
{
  const value =
    errors?.[field]

  if (
    Array.isArray(value)
    &&
    value.length
  ) {
    return String(value[0])
  }

  return value
    ? String(value)
    : ''
}


function money(
  value,
)
{
  return `৳ ${Number(value || 0).toFixed(2)}`
}


function nowLocal()
{
  const date =
    new Date()

  const offset =
    date.getTimezoneOffset()

  const local =
    new Date(
      date.getTime()
      -
      offset
      *
      60000,
    )

  return local
    .toISOString()
    .slice(0, 16)
}
</script>
