<template>
  <section class="kitchen-overview-page">
    <!-- Page Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">
          Kitchen Overview
        </h1>

        <p class="page-subtitle">
          Kitchen Display System summary
        </p>
      </div>
    </div>

    <!-- Loading -->
    <div
      v-if="loading"
      class="overview-loading"
    >
      <div
        class="spinner-border spinner-border-sm text-primary"
        role="status"
      ></div>

      <span>
        Loading kitchen overview...
      </span>
    </div>

    <!-- Error -->
    <div
      v-else-if="errorMessage"
      class="alert alert-danger overview-alert"
    >
      <i class="bi bi-exclamation-circle me-2"></i>
      {{ errorMessage }}
    </div>

    <!-- Statistics -->
    <div
      v-else
      class="row g-3"
    >
      <div
        v-for="item in cards"
        :key="item.key"
        class="col-12 col-sm-6 col-xl-3"
      >
        <div
          class="card stat-card"
          :class="`stat-${item.key}`"
        >
          <div class="card-body">
            <div class="stat-wrapper">
              <div class="stat-content">
                <div class="stat-label">
                  {{ item.label }}
                </div>

                <div class="stat-value">
                  {{ overview[item.key] ?? 0 }}
                </div>
              </div>

              <div class="stat-icon">
                <i :class="item.icon"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import api from '@/services/api'

const loading = ref(true)
const errorMessage = ref('')
const overview = ref({})

const cards = [
  {
    key: 'new_orders',
    label: 'New Orders',
    icon: 'bi bi-receipt',
  },
  {
    key: 'preparing_orders',
    label: 'Preparing',
    icon: 'bi bi-fire',
  },
  {
    key: 'ready_orders',
    label: 'Ready',
    icon: 'bi bi-check-circle',
  },
  {
    key: 'delayed_orders',
    label: 'Delayed',
    icon: 'bi bi-clock-history',
  },
]

onMounted(async () => {
  try {
    const response = await api.get('/kitchen/overview')

    overview.value = response.data.data
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message ||
      'Could not load kitchen overview.'
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.kitchen-overview-page {
  width: 100%;
}

/* =========================================================
   PAGE HEADER
========================================================= */

.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;

  margin-bottom: 12px;
}

.page-title {
  margin: 0;

  color: #1f2937;

  font-size: 18px;
  font-weight: 700;

  line-height: 1.25;
}

.page-subtitle {
  margin: 2px 0 0;

  color: #6b7280;

  font-size: 11px;

  line-height: 1.4;
}


/* =========================================================
   LOADING
========================================================= */

.overview-loading {
  display: flex;

  min-height: 100px;

  align-items: center;
  justify-content: center;

  gap: 8px;

  border: 1px solid #e5e7eb;
  border-radius: 6px;

  background: #ffffff;

  color: #6b7280;

  font-size: 11.5px;
}


/* =========================================================
   ERROR
========================================================= */

.overview-alert {
  margin: 0;

  padding: 8px 10px;

  border-radius: 5px;

  font-size: 11.5px;
}


/* =========================================================
   STAT CARD
========================================================= */

.stat-card {
  height: 100%;

  border: 1px solid #e5e7eb;
  border-radius: 6px;

  background: #ffffff;

  box-shadow:
    0 1px 4px rgba(15, 23, 42, 0.04);

  transition:
    border-color 0.15s ease,
    box-shadow 0.15s ease;
}

.stat-card:hover {
  border-color: #d7dce2;

  box-shadow:
    0 2px 7px rgba(15, 23, 42, 0.06);
}

.stat-card .card-body {
  padding: 11px 12px;
}


/* =========================================================
   STAT CONTENT
========================================================= */

.stat-wrapper {
  display: flex;

  min-height: 42px;

  align-items: center;
  justify-content: space-between;

  gap: 8px;
}

.stat-content {
  min-width: 0;

  flex: 1;
}

.stat-label {
  margin-bottom: 3px;

  overflow: hidden;

  color: #6b7280;

  font-size: 10px;
  font-weight: 500;

  line-height: 1.2;

  text-overflow: ellipsis;
  white-space: nowrap;
}

.stat-value {
  color: #111827;

  font-size: 19px;
  font-weight: 700;

  line-height: 1.1;
}


/* =========================================================
   STAT ICON
========================================================= */

.stat-icon {
  display: flex;

  width: 32px;
  height: 32px;

  flex-shrink: 0;

  align-items: center;
  justify-content: center;

  border-radius: 5px;

  font-size: 14px;
}


/* =========================================================
   NEW ORDERS
========================================================= */

.stat-new_orders .stat-icon {
  background: #fff7ed;
  color: #ea580c;
}


/* =========================================================
   PREPARING
========================================================= */

.stat-preparing_orders .stat-icon {
  background: #eff6ff;
  color: #2563eb;
}


/* =========================================================
   READY
========================================================= */

.stat-ready_orders .stat-icon {
  background: #f0fdf4;
  color: #15803d;
}


/* =========================================================
   DELAYED
========================================================= */

.stat-delayed_orders .stat-icon {
  background: #fef2f2;
  color: #dc2626;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 1199.98px) {
  .stat-card .card-body {
    padding: 10px 11px;
  }

  .stat-value {
    font-size: 18px;
  }
}

@media (max-width: 767.98px) {
  .page-header {
    margin-bottom: 10px;
  }

  .page-title {
    font-size: 17px;
  }
}

@media (max-width: 575.98px) {
  .stat-wrapper {
    min-height: 38px;
  }

  .stat-card .card-body {
    padding: 10px;
  }

  .stat-value {
    font-size: 17px;
  }

  .stat-icon {
    width: 30px;
    height: 30px;

    font-size: 13px;
  }
}
</style>