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
      <div class="billing-range-field">
        <span class="billing-range-label">
          <i
            class="bi bi-calendar3"
            aria-hidden="true"
          ></i>
          From Date
        </span>

        <ReservationFilterDatePicker
          :model-value="dateFrom"
          :max="resolvedFromMax"
          id="billing-date-from"
          label="billing from date"
          @update:model-value="emit('update:date-from', $event)"
        />
      </div>

      <!-- To Date -->
      <div class="billing-range-field">
        <span class="billing-range-label">
          <i
            class="bi bi-calendar3"
            aria-hidden="true"
          ></i>
          To Date
        </span>

        <ReservationFilterDatePicker
          :model-value="dateTo"
          :min="resolvedToMin"
          :max="today"
          id="billing-date-to"
          label="billing to date"
          @update:model-value="emit('update:date-to', $event)"
        />
      </div>

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
import ReservationFilterDatePicker from "@/components/reservation/ReservationFilterDatePicker.vue";

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
