<script setup>

import {
  computed,
  onMounted,
  ref,
} from 'vue'

import {
  createManagedSubscriptionPlan,
  getManagedSubscriptionPlans,
  updateManagedSubscriptionPlan,
  updateManagedSubscriptionPlanStatus,
} from '@/services/superAdminApi'


/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const plans =
  ref([])

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

    slug:
      '',

    price:
      0,

    currency:
      'BDT',

    billing_interval:
      'month',

    billing_interval_count:
      1,

    trial_days:
      0,

    is_active:
      true,
  })


/*
|--------------------------------------------------------------------------
| Computed
|--------------------------------------------------------------------------
*/

const filteredPlans =
  computed(() => {

    const keyword =
      search.value
        .trim()
        .toLowerCase()


    if (
      !keyword
    ) {
      return plans.value
    }


    return plans.value.filter(
      (plan) => {

        return [
          plan.name,
          plan.slug,
          plan.currency,
          plan.billing_interval,
        ]
          .filter(Boolean)
          .join(' ')
          .toLowerCase()
          .includes(keyword)
      }
    )

  })


const totalPlans =
  computed(() =>
    plans.value.length
  )


const activePlans =
  computed(() =>
    plans.value.filter(
      (plan) =>
        Boolean(plan.is_active)
    ).length
  )


const inactivePlans =
  computed(() =>
    totalPlans.value -
    activePlans.value
  )


const trialPlans =
  computed(() =>
    plans.value.filter(
      (plan) =>
        Number(plan.price || 0) <= 0
    ).length
  )


/*
|--------------------------------------------------------------------------
| Load Plans
|--------------------------------------------------------------------------
*/

async function loadPlans() {

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
      await getManagedSubscriptionPlans()


    if (
      !response?.success
    ) {
      throw new Error(
        response?.message ||
        'Failed to load subscription plans.'
      )
    }


    plans.value =
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
      'Failed to load subscription plans.'

  } finally {

    loading.value =
      false
  }
}


/*
|--------------------------------------------------------------------------
| Create Form
|--------------------------------------------------------------------------
*/

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

    slug:
      '',

    price:
      0,

    currency:
      'BDT',

    billing_interval:
      'month',

    billing_interval_count:
      1,

    trial_days:
      0,

    is_active:
      true,
  }


  showForm.value =
    true
}


/*
|--------------------------------------------------------------------------
| Edit Form
|--------------------------------------------------------------------------
*/

function openEditForm(
  plan
) {

  editingId.value =
    plan.id

  validationErrors.value =
    {}

  error.value =
    ''

  success.value =
    ''


  form.value = {
    name:
      plan.name || '',

    slug:
      plan.slug || '',

    price:
      Number(
        plan.price || 0
      ),

    currency:
      plan.currency || 'BDT',

    billing_interval:
      plan.billing_interval || 'month',

    billing_interval_count:
      Number(
        plan.billing_interval_count || 1
      ),

    trial_days:
      Number(
        plan.trial_days || 0
      ),

    is_active:
      Boolean(
        plan.is_active
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


/*
|--------------------------------------------------------------------------
| Save Plan
|--------------------------------------------------------------------------
*/

async function savePlan() {

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


  if (
    !String(
      form.value.name || ''
    ).trim()
  ) {

    error.value =
      'Plan name is required.'

    return
  }


  if (
    Number(
      form.value.price
    ) < 0
  ) {

    error.value =
      'Plan price cannot be negative.'

    return
  }


  if (
    Number(
      form.value.billing_interval_count
    ) < 1
  ) {

    error.value =
      'Billing interval count must be at least 1.'

    return
  }


  saving.value =
    true


  try {

    const payload = {

      name:
        String(
          form.value.name
        ).trim(),

      slug:
        String(
          form.value.slug || ''
        ).trim() ||
        null,

      price:
        Number(
          form.value.price || 0
        ),

      currency:
        String(
          form.value.currency || 'BDT'
        )
          .trim()
          .toUpperCase(),

      billing_interval:
        form.value.billing_interval,

      billing_interval_count:
        Number(
          form.value.billing_interval_count || 1
        ),

      trial_days:
        Number(
          form.value.trial_days || 0
        ),

      is_active:
        Boolean(
          form.value.is_active
        ),
    }


    const response =
      editingId.value
        ? await updateManagedSubscriptionPlan(
            editingId.value,
            payload
          )
        : await createManagedSubscriptionPlan(
            payload
          )


    if (
      !response?.success
    ) {

      throw new Error(
        response?.message ||
        'Failed to save subscription plan.'
      )
    }


    success.value =
      response.message ||
      (
        editingId.value
          ? 'Subscription plan updated successfully.'
          : 'Subscription plan created successfully.'
      )


    showForm.value =
      false

    editingId.value =
      null


    await loadPlans()

  } catch (
    exception
  ) {

    error.value =
      exception?.message ||
      'Failed to save subscription plan.'


    validationErrors.value =
      exception?.validationErrors ||
      {}

  } finally {

    saving.value =
      false
  }
}


/*
|--------------------------------------------------------------------------
| Enable / Disable
|--------------------------------------------------------------------------
*/

async function togglePlanStatus(
  plan
) {

  if (
    togglingId.value
  ) {
    return
  }


  const nextStatus =
    !Boolean(
      plan.is_active
    )


  if (
    !window.confirm(
      nextStatus
        ? `Enable "${plan.name}"?`
        : `Disable "${plan.name}"? Existing subscriptions will remain unchanged.`
    )
  ) {
    return
  }


  togglingId.value =
    plan.id

  error.value =
    ''

  success.value =
    ''


  try {

    const response =
      await updateManagedSubscriptionPlanStatus(
        plan.id,
        nextStatus
      )


    if (
      !response?.success
    ) {

      throw new Error(
        response?.message ||
        'Failed to update plan status.'
      )
    }


    success.value =
      response.message ||
      'Plan status updated successfully.'


    await loadPlans()

  } catch (
    exception
  ) {

    error.value =
      exception?.message ||
      'Failed to update plan status.'

  } finally {

    togglingId.value =
      null
  }
}


/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

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


function formatPrice(
  plan
) {

  return `${plan.currency || 'BDT'} ${Number(
    plan.price || 0
  ).toLocaleString(
    'en-BD',
    {
      maximumFractionDigits:
        2,
    }
  )}`
}


function billingLabel(
  plan
) {

  const count =
    Number(
      plan.billing_interval_count || 1
    )


  const interval =
    plan.billing_interval || 'month'


  return count === 1
    ? `Every ${interval}`
    : `Every ${count} ${interval}s`
}


/*
|--------------------------------------------------------------------------
| Initial Load
|--------------------------------------------------------------------------
*/

onMounted(
  loadPlans
)

</script>


<template>

  <section class="management-page">


    <!-- Header -->

    <header class="management-page-header">

      <div class="management-heading">

        <div class="management-heading-icon">
          <i class="bi bi-boxes"></i>
        </div>


        <div>

          <span class="management-eyebrow">
            Subscription Management
          </span>


          <h1>
            Subscription Plans
          </h1>


          <p>
            Create, edit, enable and disable subscription plans
            for your restaurant SaaS platform.
          </p>

        </div>

      </div>


      <div class="management-header-actions">

        <button
          type="button"
          class="btn btn-outline-secondary"
          :disabled="loading"
          @click="loadPlans"
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

          Create Plan

        </button>

      </div>

    </header>


    <!-- Messages -->

    <div
      v-if="error"
      class="management-alert management-alert-danger"
    >

      <i class="bi bi-exclamation-triangle-fill"></i>

      <span>
        {{ error }}
      </span>

    </div>


    <div
      v-if="success"
      class="management-alert management-alert-success"
    >

      <i class="bi bi-check-circle-fill"></i>

      <span>
        {{ success }}
      </span>

    </div>


    <!-- Stats -->

    <section class="management-stat-grid">

      <article class="management-stat-card">

        <span class="management-stat-icon tone-primary">
          <i class="bi bi-boxes"></i>
        </span>

        <div>
          <span>Total Plans</span>
          <strong>{{ totalPlans }}</strong>
        </div>

      </article>


      <article class="management-stat-card">

        <span class="management-stat-icon tone-success">
          <i class="bi bi-check-circle"></i>
        </span>

        <div>
          <span>Active Plans</span>
          <strong>{{ activePlans }}</strong>
        </div>

      </article>


      <article class="management-stat-card">

        <span class="management-stat-icon tone-muted">
          <i class="bi bi-slash-circle"></i>
        </span>

        <div>
          <span>Disabled Plans</span>
          <strong>{{ inactivePlans }}</strong>
        </div>

      </article>


      <article class="management-stat-card">

        <span class="management-stat-icon tone-warning">
          <i class="bi bi-hourglass-split"></i>
        </span>

        <div>
          <span>Free / Trial Plans</span>
          <strong>{{ trialPlans }}</strong>
        </div>

      </article>

    </section>


    <!-- Create / Edit Form -->

    <section
      v-if="showForm"
      class="management-form-card"
    >

      <div class="management-form-header">

        <div>

          <span class="management-eyebrow">

            {{
              editingId
                ? 'Edit Existing Plan'
                : 'New Plan'
            }}

          </span>


          <h2>

            {{
              editingId
                ? 'Update Subscription Plan'
                : 'Create Subscription Plan'
            }}

          </h2>

        </div>


        <button
          type="button"
          class="management-close-button"
          aria-label="Close form"
          :disabled="saving"
          @click="closeForm"
        >

          <i class="bi bi-x-lg"></i>

        </button>

      </div>


      <form
        class="management-form-grid"
        @submit.prevent="savePlan"
      >


        <div class="management-field">

          <label>
            Plan Name *
          </label>

          <input
            v-model="form.name"
            type="text"
            class="form-control"
            placeholder="e.g. Professional"
            :disabled="saving"
          />

          <small
            v-if="fieldError('name')"
            class="management-field-error"
          >
            {{ fieldError('name') }}
          </small>

        </div>


        <div class="management-field">

          <label>
            Slug
          </label>

          <input
            v-model="form.slug"
            type="text"
            class="form-control"
            placeholder="Leave blank to auto-generate"
            :disabled="saving"
          />

          <small class="management-field-help">
            Example: professional-monthly
          </small>

          <small
            v-if="fieldError('slug')"
            class="management-field-error"
          >
            {{ fieldError('slug') }}
          </small>

        </div>


        <div class="management-field">

          <label>
            Price *
          </label>

          <input
            v-model.number="form.price"
            type="number"
            min="0"
            step="0.01"
            class="form-control"
            :disabled="saving"
          />

          <small class="management-field-help">
            Set price to 0 for a free / trial plan.
          </small>

        </div>


        <div class="management-field">

          <label>
            Currency *
          </label>

          <input
            v-model="form.currency"
            type="text"
            maxlength="10"
            class="form-control"
            placeholder="BDT"
            :disabled="saving"
          />

        </div>


        <div class="management-field">

          <label>
            Billing Interval *
          </label>

          <select
            v-model="form.billing_interval"
            class="form-select"
            :disabled="saving"
          >

            <option value="day">
              Day
            </option>

            <option value="week">
              Week
            </option>

            <option value="month">
              Month
            </option>

            <option value="year">
              Year
            </option>

          </select>

        </div>


        <div class="management-field">

          <label>
            Interval Count *
          </label>

          <input
            v-model.number="form.billing_interval_count"
            type="number"
            min="1"
            max="120"
            class="form-control"
            :disabled="saving"
          />

          <small class="management-field-help">
            Example: 1 month, 3 months, 1 year.
          </small>

        </div>


        <div class="management-field">

          <label>
            Trial Days
          </label>

          <input
            v-model.number="form.trial_days"
            type="number"
            min="0"
            max="3650"
            class="form-control"
            :disabled="saving"
          />

          <small class="management-field-help">
            Example: 7, 14 or 30 days.
          </small>

        </div>


        <div class="management-field management-switch-field">

          <label>
            Availability
          </label>


          <label class="management-switch">

            <input
              v-model="form.is_active"
              type="checkbox"
              :disabled="saving"
            />

            <span></span>

            <strong>

              {{
                form.is_active
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
                  ? 'Update Plan'
                  : 'Create Plan'
            }}

          </button>

        </div>

      </form>

    </section>


    <!-- Search -->

    <section class="management-toolbar">

      <div class="management-search">

        <i class="bi bi-search"></i>


        <input
          v-model="search"
          type="search"
          placeholder="Search plan name, slug, currency..."
        />

      </div>


      <span class="management-toolbar-count">

        {{ filteredPlans.length }}

        plan{{
          filteredPlans.length === 1
            ? ''
            : 's'
        }}

      </span>

    </section>


    <!-- Loading -->

    <div
      v-if="
        loading &&
        !plans.length
      "
      class="management-loading"
    >

      <span class="spinner-border text-primary"></span>

      <span>
        Loading subscription plans...
      </span>

    </div>


    <!-- Plan Cards -->

    <section
      v-if="
        !loading &&
        filteredPlans.length
      "
      class="plan-management-grid"
    >

      <article
        v-for="plan in filteredPlans"
        :key="plan.id"
        class="managed-plan-card"
        :class="{
          disabled:
            !plan.is_active
        }"
      >

        <div class="managed-plan-top">

          <div>

            <span
              class="managed-status-badge"
              :class="
                plan.is_active
                  ? 'active'
                  : 'inactive'
              "
            >

              {{
                plan.is_active
                  ? 'Active'
                  : 'Disabled'
              }}

            </span>


            <h2>
              {{ plan.name }}
            </h2>


            <code>
              {{ plan.slug }}
            </code>

          </div>


          <div class="managed-plan-price">

            <strong>
              {{ formatPrice(plan) }}
            </strong>

            <span>
              {{ billingLabel(plan) }}
            </span>

          </div>

        </div>


        <div class="managed-plan-meta">

          <div>

            <span>
              Trial
            </span>

            <strong>

              {{
                Number(plan.trial_days || 0)
                  ? `${plan.trial_days} days`
                  : 'None'
              }}

            </strong>

          </div>


          <div>

            <span>
              Total Assignments
            </span>

            <strong>

              {{
                plan.usage?.total_subscriptions || 0
              }}

            </strong>

          </div>


          <div>

            <span>
              Active Subscriptions
            </span>

            <strong>

              {{
                plan.usage?.active_subscriptions || 0
              }}

            </strong>

          </div>

        </div>


        <div class="managed-plan-actions">

          <button
            type="button"
            class="btn btn-sm btn-outline-primary"
            @click="openEditForm(plan)"
          >

            <i class="bi bi-pencil me-1"></i>

            Edit

          </button>


          <button
            type="button"
            class="btn btn-sm"
            :class="
              plan.is_active
                ? 'btn-outline-danger'
                : 'btn-outline-success'
            "
            :disabled="
              togglingId === plan.id
            "
            @click="
              togglePlanStatus(plan)
            "
          >

            <span
              v-if="
                togglingId === plan.id
              "
              class="spinner-border spinner-border-sm me-1"
            ></span>


            <i
              v-else
              :class="
                plan.is_active
                  ? 'bi bi-slash-circle me-1'
                  : 'bi bi-check-circle me-1'
              "
            ></i>


            {{
              plan.is_active
                ? 'Disable'
                : 'Enable'
            }}

          </button>

        </div>

      </article>

    </section>


    <!-- Empty -->

    <section
      v-if="
        !loading &&
        !filteredPlans.length
      "
      class="management-empty"
    >

      <i class="bi bi-boxes"></i>


      <h2>
        No subscription plans found
      </h2>


      <p>
        Create a plan or change your current search.
      </p>

    </section>

  </section>

</template>


<style
  src="@/styles/super-admin/management.css"
></style>
