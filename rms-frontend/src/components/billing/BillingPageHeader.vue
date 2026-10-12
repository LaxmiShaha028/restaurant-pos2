<template>
  <header class="billing-page-header">
    <!-- ==================================================
         Left side: Icon + Heading
    =================================================== -->
    <div class="billing-page-header-left">
      <div class="billing-page-header-icon">
        <i class="bi bi-cash-stack"></i>
      </div>

      <div class="billing-page-heading">
        <p class="billing-page-eyebrow">
          Financial Overview
        </p>

        <h1>
          Billing &amp; Statement
        </h1>

        <p>
          Monitor sales, settlements, payment methods
          and payment activity.
        </p>
      </div>
    </div>

    <!-- ==================================================
         Date Range and Refresh
    =================================================== -->
    <div class="billing-header-actions">
      <!-- From Date -->
      <label class="billing-range-field">
        <span class="billing-range-label">
          <i
            class="bi bi-calendar3"
            aria-hidden="true"
          ></i>
          From Date
        </span>

        <input
          :value="dateFrom"
          type="date"
          :max="resolvedFromMax"
          aria-label="Select billing from date"
          @change="handleFromDateChange"
        />
      </label>

      <!-- To Date -->
      <label class="billing-range-field">
        <span class="billing-range-label">
          <i
            class="bi bi-calendar3"
            aria-hidden="true"
          ></i>
          To Date
        </span>

        <input
          :value="dateTo"
          type="date"
          :min="resolvedToMin"
          :max="today"
          aria-label="Select billing to date"
          @change="handleToDateChange"
        />
      </label>

      <!-- Refresh Button -->
      <button
        type="button"
        class="billing-refresh-button"
        :disabled="loading"
        :aria-busy="loading"
        @click="handleRefresh"
      >
        <span
          v-if="loading"
          class="spinner-border spinner-border-sm"
          role="status"
          aria-hidden="true"
        ></span>

        <i
          v-else
          class="bi bi-arrow-clockwise"
          aria-hidden="true"
        ></i>

        <span>
          {{
            loading
              ? "Refreshing..."
              : "Refresh"
          }}
        </span>
      </button>
    </div>
  </header>
</template>

<script setup>
import {
  computed,
} from "vue";

/*
|--------------------------------------------------------------------------
| Component Props
|--------------------------------------------------------------------------
*/

const props = defineProps({
  dateFrom: {
    type: String,
    default: "",
  },

  dateTo: {
    type: String,
    default: "",
  },

  today: {
    type: String,
    required: true,
  },

  loading: {
    type: Boolean,
    default: false,
  },
});

/*
|--------------------------------------------------------------------------
| Component Events
|--------------------------------------------------------------------------
*/

const emit = defineEmits([
  "update:date-from",
  "update:date-to",
  "refresh",
]);

/*
|--------------------------------------------------------------------------
| From Date Maximum
|--------------------------------------------------------------------------
*/

const resolvedFromMax =
  computed(() => {
    return (
      props.dateTo ||
      props.today
    );
  });

/*
|--------------------------------------------------------------------------
| To Date Minimum
|--------------------------------------------------------------------------
*/

const resolvedToMin =
  computed(() => {
    return (
      props.dateFrom ||
      undefined
    );
  });

/*
|--------------------------------------------------------------------------
| Handle From Date Change
|--------------------------------------------------------------------------
*/

function handleFromDateChange(
  event
) {
  const selectedDate =
    event?.target?.value || "";

  emit(
    "update:date-from",
    selectedDate
  );
}

/*
|--------------------------------------------------------------------------
| Handle To Date Change
|--------------------------------------------------------------------------
*/

function handleToDateChange(
  event
) {
  const selectedDate =
    event?.target?.value || "";

  emit(
    "update:date-to",
    selectedDate
  );
}

/*
|--------------------------------------------------------------------------
| Handle Refresh
|--------------------------------------------------------------------------
*/

function handleRefresh() {
  if (props.loading) {
    return;
  }

  emit("refresh");
}
</script>