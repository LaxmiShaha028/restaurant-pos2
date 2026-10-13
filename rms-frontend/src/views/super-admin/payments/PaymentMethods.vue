<script setup>

import {
  computed,
  onMounted,
  ref,
  watch,
} from 'vue'

import PaginationControls from '@/components/shared/PaginationControls.vue'

import {
  createManagedPaymentMethod,
  getManagedPaymentMethods,
  updateManagedPaymentMethod,
  updateManagedPaymentMethodStatus,
} from '@/services/superAdminApi'


const methods =
  ref([])

const currentPage = ref(1)
const pageSize = 10

const loading =
  ref(false)

const saving =
  ref(false)

const togglingId =
  ref(null)

const error =
  ref('')

const success =
  ref('')

const search =
  ref('')

const showForm =
  ref(false)

const editingId =
  ref(null)

const validationErrors =
  ref({})


const form =
  ref({
    name:
      '',

    code:
      '',

    status:
      true,
  })


const filteredMethods =
  computed(() => {

    const keyword =
      search.value
        .trim()
        .toLowerCase()


    if (
      !keyword
    ) {
      return methods.value
    }


    return methods.value.filter(
      (method) =>
        [
          method.name,
          method.code,
        ]
          .filter(Boolean)
          .join(' ')
          .toLowerCase()
          .includes(keyword)
    )

  })

const paginatedMethods = computed(() => {
  const start = (currentPage.value - 1) * pageSize
  return filteredMethods.value.slice(start, start + pageSize)
})

watch(search, () => {
  currentPage.value = 1
})


const totalMethods =
  computed(() =>
    methods.value.length
  )


const activeMethods =
  computed(() =>
    methods.value.filter(
      (method) =>
        Boolean(method.status)
    ).length
  )


const inactiveMethods =
  computed(() =>
    totalMethods.value -
    activeMethods.value
  )


const totalUsage =
  computed(() =>
    methods.value.reduce(
      (
        total,
        method
      ) =>
        total +
        Number(
          method.usage?.total_payments || 0
        ),
      0
    )
  )


function formatNumber(value) {
  const number = Number(value || 0)
  return Number.isInteger(number) && Math.abs(number) < 10
    ? String(number).padStart(2, '0')
    : number.toLocaleString('en-BD')
}


async function loadMethods() {

  if (
    loading.value
  ) {
    return
  }


  loading.value =
    true

  error.value =
    ''


  try {

    const response =
      await getManagedPaymentMethods()


    if (
      !response?.success
    ) {
      throw new Error(
        response?.message ||
        'Failed to load payment methods.'
      )
    }


    methods.value =
      Array.isArray(
        response.data
      )
        ? response.data
        : []

  } catch (
    exception
  ) {

    error.value =
      exception?.message ||
      'Failed to load payment methods.'

  } finally {

    loading.value =
      false
  }
}


function openCreateForm() {

  editingId.value =
    null

  validationErrors.value =
    {}

  error.value =
    ''

  success.value =
    ''

  form.value = {
    name:
      '',

    code:
      '',

    status:
      true,
  }

  showForm.value =
    true
}


function openEditForm(
  method
) {

  editingId.value =
    method.id

  validationErrors.value =
    {}

  error.value =
    ''

  success.value =
    ''

  form.value = {
    name:
      method.name || '',

    code:
      method.code || '',

    status:
      Boolean(
        method.status
      ),
  }

  showForm.value =
    true
}


function closeForm() {

  if (
    saving.value
  ) {
    return
  }

  showForm.value =
    false

  editingId.value =
    null

  validationErrors.value =
    {}
}


async function saveMethod() {

  if (
    saving.value
  ) {
    return
  }


  error.value =
    ''

  success.value =
    ''

  validationErrors.value =
    {}


  saving.value =
    true


  try {

    const payload =
      editingId.value
        ? {
            name:
              String(
                form.value.name || ''
              ).trim(),

            status:
              Boolean(
                form.value.status
              ),
          }
        : {
            name:
              String(
                form.value.name || ''
              ).trim(),

            code:
              String(
                form.value.code || ''
              )
                .trim()
                .toLowerCase(),

            status:
              Boolean(
                form.value.status
              ),
          }


    const response =
      editingId.value
        ? await updateManagedPaymentMethod(
            editingId.value,
            payload
          )
        : await createManagedPaymentMethod(
            payload
          )


    if (
      !response?.success
    ) {
      throw new Error(
        response?.message ||
        'Failed to save payment method.'
      )
    }


    success.value =
      response.message ||
      'Payment method saved successfully.'


    showForm.value =
      false

    editingId.value =
      null


    await loadMethods()

  } catch (
    exception
  ) {

    error.value =
      exception?.message ||
      'Failed to save payment method.'

    validationErrors.value =
      exception?.validationErrors ||
      {}

  } finally {

    saving.value =
      false
  }
}


async function toggleStatus(
  method
) {

  if (
    togglingId.value
  ) {
    return
  }


  const nextStatus =
    !Boolean(
      method.status
    )


  if (
    !window.confirm(
      nextStatus
        ? `Enable "${method.name}"?`
        : `Disable "${method.name}"? It will no longer appear as an available payment option for new payments.`
    )
  ) {
    return
  }


  togglingId.value =
    method.id

  error.value =
    ''

  success.value =
    ''


  try {

    const response =
      await updateManagedPaymentMethodStatus(
        method.id,
        nextStatus
      )


    if (
      !response?.success
    ) {
      throw new Error(
        response?.message ||
        'Failed to update payment method status.'
      )
    }


    success.value =
      response.message ||
      'Payment method status updated successfully.'


    await loadMethods()

  } catch (
    exception
  ) {

    error.value =
      exception?.message ||
      'Failed to update payment method status.'

  } finally {

    togglingId.value =
      null
  }
}


function fieldError(
  field
) {

  const errors =
    validationErrors.value?.[field]

  return Array.isArray(
    errors
  )
    ? errors[0] || ''
    : ''
}


onMounted(
  loadMethods
)

</script>


<template>

  <section class="management-page payment-methods-page">


    <header class="management-page-header">

      <div class="management-heading">

        <div class="management-heading-icon payment-method-heading-icon">
          <i class="bi bi-wallet2"></i>
        </div>

        <div>
          <span class="management-eyebrow">
            Payment Management
          </span>

          <h1>
            Payment Methods
          </h1>

          <p>
            Control which payment methods Super Admin can use
            when recording subscription payments.
          </p>
        </div>

      </div>


      <div class="management-header-actions">

        <button
          type="button"
          class="btn btn-outline-secondary"
          :disabled="loading"
          @click="loadMethods"
        >
          <i class="bi bi-arrow-clockwise me-1"></i>
          Refresh
        </button>

        <button
          type="button"
          class="btn btn-primary"
          @click="openCreateForm"
        >
          <i class="bi bi-plus-lg me-1"></i>
          Add Method
        </button>

      </div>

    </header>


    <div
      v-if="error"
      class="management-alert management-alert-danger"
    >
      <i class="bi bi-exclamation-triangle-fill"></i>
      <span>{{ error }}</span>
    </div>

    <div
      v-if="success"
      class="management-alert management-alert-success"
    >
      <i class="bi bi-check-circle-fill"></i>
      <span>{{ success }}</span>
    </div>


    <section class="management-stat-grid">

      <article class="management-stat-card">
        <span class="management-stat-icon tone-primary">
          <i class="bi bi-wallet2"></i>
        </span>
        <div>
          <span>Total Methods</span>
          <strong>{{ formatNumber(totalMethods) }}</strong>
        </div>
      </article>

      <article class="management-stat-card">
        <span class="management-stat-icon tone-success">
          <i class="bi bi-check-circle"></i>
        </span>
        <div>
          <span>Enabled</span>
          <strong>{{ formatNumber(activeMethods) }}</strong>
        </div>
      </article>

      <article class="management-stat-card">
        <span class="management-stat-icon tone-muted">
          <i class="bi bi-slash-circle"></i>
        </span>
        <div>
          <span>Disabled</span>
          <strong>{{ formatNumber(inactiveMethods) }}</strong>
        </div>
      </article>

      <article class="management-stat-card">
        <span class="management-stat-icon tone-warning">
          <i class="bi bi-receipt"></i>
        </span>
        <div>
          <span>Recorded Usage</span>
          <strong>{{ formatNumber(totalUsage) }}</strong>
        </div>
      </article>

    </section>


    <section
      v-if="showForm"
      class="management-form-card"
    >

      <div class="management-form-header">

        <div>
          <span class="management-eyebrow">
            {{
              editingId
                ? 'Edit Method'
                : 'New Method'
            }}
          </span>

          <h2>
            {{
              editingId
                ? 'Update Payment Method'
                : 'Create Payment Method'
            }}
          </h2>
        </div>

        <button
          type="button"
          class="management-close-button"
          :disabled="saving"
          @click="closeForm"
        >
          <i class="bi bi-x-lg"></i>
        </button>

      </div>


      <form
        class="management-form-grid payment-method-form-grid"
        @submit.prevent="saveMethod"
      >

        <div class="management-field">
          <label>Display Name *</label>
          <input
            v-model="form.name"
            type="text"
            class="form-control"
            placeholder="e.g. bKash"
            :disabled="saving"
          />
          <small v-if="fieldError('name')" class="management-field-error">
            {{ fieldError('name') }}
          </small>
        </div>


        <div class="management-field">
          <label>Code *</label>
          <input
            v-model="form.code"
            type="text"
            class="form-control"
            placeholder="e.g. bkash"
            :disabled="
              saving ||
              Boolean(editingId)
            "
          />
          <small class="management-field-help">
            The code becomes permanent after creation so historical payments remain consistent.
          </small>
          <small v-if="fieldError('code')" class="management-field-error">
            {{ fieldError('code') }}
          </small>
        </div>


        <div class="management-field management-switch-field">
          <label>Availability</label>

          <label class="management-switch">
            <input
              v-model="form.status"
              type="checkbox"
              :disabled="saving"
            />
            <span></span>
            <strong>
              {{
                form.status
                  ? 'Enabled'
                  : 'Disabled'
              }}
            </strong>
          </label>
        </div>


        <div class="management-form-actions">

          <button
            type="button"
            class="btn btn-outline-secondary"
            :disabled="saving"
            @click="closeForm"
          >
            Cancel
          </button>

          <button
            type="submit"
            class="btn btn-primary"
            :disabled="saving"
          >
            <span
              v-if="saving"
              class="spinner-border spinner-border-sm me-1"
            ></span>

            {{
              saving
                ? 'Saving...'
                : editingId
                  ? 'Update Method'
                  : 'Create Method'
            }}
          </button>

        </div>

      </form>

    </section>


    <section class="management-toolbar">

      <div class="management-search">
        <i class="bi bi-search"></i>
        <input
          v-model="search"
          type="search"
          placeholder="Search payment method..."
        />
      </div>

      <span class="management-toolbar-count">
        {{ formatNumber(filteredMethods.length) }} method{{
          filteredMethods.length === 1
            ? ''
            : 's'
        }}
      </span>

    </section>


    <div
      v-if="
        loading &&
        !methods.length
      "
      class="management-loading"
    >
      <span class="spinner-border text-primary"></span>
      <span>Loading payment methods...</span>
    </div>


    <section
      v-if="
        !loading &&
        filteredMethods.length
      "
      class="management-table-card"
    >

      <div class="management-table-wrapper">

        <table class="management-table">

          <thead>
            <tr>
              <th>Payment Method</th>
              <th>Code</th>
              <th>Status</th>
              <th>Total Payments</th>
              <th>Paid Payments</th>
              <th class="management-actions-heading">
                Actions
              </th>
            </tr>
          </thead>

          <tbody>

            <tr
              v-for="method in paginatedMethods"
              :key="method.id"
            >

              <td data-label="Payment Method">
                <div class="managed-method-name">
                  <span>
                    <i class="bi bi-credit-card"></i>
                  </span>
                  <strong>{{ method.name }}</strong>
                </div>
              </td>

              <td data-label="Code">
                <code class="managed-code">
                  {{ method.code }}
                </code>
              </td>

              <td data-label="Status">
                <span
                  class="managed-status-badge"
                  :class="
                    method.status
                      ? 'active'
                      : 'inactive'
                  "
                >
                  {{
                    method.status
                      ? 'Enabled'
                      : 'Disabled'
                  }}
                </span>
              </td>

              <td data-label="Total Payments">
                <strong>
                  {{
                    formatNumber(method.usage?.total_payments || 0)
                  }}
                </strong>
              </td>

              <td data-label="Paid Payments">
                <strong>
                  {{
                    formatNumber(method.usage?.paid_payments || 0)
                  }}
                </strong>
              </td>

              <td
                data-label="Actions"
                class="management-actions-cell"
              >
                <div class="management-action-group">

                  <button
                    type="button"
                    class="btn btn-sm btn-outline-primary"
                    aria-label="Edit payment method"
                    title="Edit payment method"
                    @click="openEditForm(method)"
                  >
                    <i class="bi bi-pencil"></i>
                  </button>

                  <button
                    type="button"
                    class="btn btn-sm btn-outline-primary"
                    :aria-label="method.status ? 'Disable payment method' : 'Enable payment method'"
                    :title="method.status ? 'Disable payment method' : 'Enable payment method'"
                    :disabled="
                      togglingId === method.id
                    "
                    @click="toggleStatus(method)"
                  >
                    <span
                      v-if="
                        togglingId === method.id
                      "
                      class="spinner-border spinner-border-sm"
                    ></span>

                    <i
                      v-else
                      :class="
                        method.status
                          ? 'bi bi-slash-circle'
                          : 'bi bi-check-circle'
                      "
                    ></i>

                  </button>

                </div>
              </td>

            </tr>

          </tbody>

        </table>

      </div>

      <PaginationControls
        v-model:page="currentPage"
        :total="filteredMethods.length"
        :per-page="pageSize"
      />

    </section>


    <section
      v-if="
        !loading &&
        !filteredMethods.length
      "
      class="management-empty"
    >
      <i class="bi bi-wallet2"></i>
      <h2>No payment methods found</h2>
      <p>
        Add a payment method or change your search.
      </p>
    </section>

  </section>

</template>


<style
  src="@/styles/super-admin/management.css"
></style>

