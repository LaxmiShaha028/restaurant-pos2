<template>
  <section class="admin-overview-page">
    <!-- Page Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">
          Admin Overview
        </h1>

        <p class="page-subtitle">
          System users, roles and permission summary
        </p>
      </div>
    </div>

    <!-- Loading -->
    <div
      v-if="loading"
      class="page-loading"
    >
      <div
        class="spinner-border spinner-border-sm text-primary"
        role="status"
      ></div>

      <span>Loading overview...</span>
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
      <!-- Users -->
      <div class="col-12 col-sm-6 col-xl-4">
        <div class="card stat-card">
          <div class="card-body">
            <div class="stat-content">
              <div class="stat-icon">
                <i class="bi bi-people"></i>
              </div>

              <div class="stat-details">
                <div class="stat-label">
                  Total Users
                </div>

                <div class="stat-value">
                  {{ overview.total_users ?? 0 }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Roles -->
      <div class="col-12 col-sm-6 col-xl-4">
        <div class="card stat-card">
          <div class="card-body">
            <div class="stat-content">
              <div class="stat-icon">
                <i class="bi bi-person-badge"></i>
              </div>

              <div class="stat-details">
                <div class="stat-label">
                  Total Roles
                </div>

                <div class="stat-value">
                  {{ overview.total_roles ?? 0 }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Permissions -->
      <div class="col-12 col-sm-6 col-xl-4">
        <div class="card stat-card">
          <div class="card-body">
            <div class="stat-content">
              <div class="stat-icon">
                <i class="bi bi-shield-check"></i>
              </div>

              <div class="stat-details">
                <div class="stat-label">
                  Total Permissions
                </div>

                <div class="stat-value">
                  {{ overview.total_permissions ?? 0 }}
                </div>
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

onMounted(async () => {
  try {
    const response = await api.get('/admin/overview')

    overview.value = response.data.data
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message ||
      'Could not load admin overview.'
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.admin-overview-page {
  width: 100%;
}

/* ========================================
   Header
======================================== */

.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;

  margin-bottom: 14px;
}

.page-title {
  margin: 0;

  color: #1f2937;

  font-size: 18px;
  font-weight: 700;

  line-height: 1.3;
}

.page-subtitle {
  margin: 3px 0 0;

  color: #6b7280;

  font-size: 11.5px;

  line-height: 1.4;
}

/* ========================================
   Loading
======================================== */

.page-loading {
  display: flex;

  min-height: 120px;

  align-items: center;
  justify-content: center;

  gap: 8px;

  color: #6b7280;

  font-size: 12px;
}

/* ========================================
   Alert
======================================== */

.overview-alert {
  margin: 0;

  padding: 9px 11px;

  border-radius: 5px;

  font-size: 12px;
}

/* ========================================
   Stat Cards
======================================== */

.stat-card {
  height: 100%;

  border: 1px solid #e6e9ed;

  border-radius: 7px;

  background: #ffffff;

  box-shadow:
    0 2px 6px rgba(15, 23, 42, 0.04);

  transition:
    border-color 0.15s ease,
    box-shadow 0.15s ease;
}

.stat-card:hover {
  border-color: #d8dee6;

  box-shadow:
    0 3px 9px rgba(15, 23, 42, 0.06);
}

.stat-card .card-body {
  padding: 14px 15px;
}

/* ========================================
   Stat Content
======================================== */

.stat-content {
  display: flex;

  align-items: center;

  gap: 11px;
}

/* ========================================
   Icon
======================================== */

.stat-icon {
  display: flex;

  width: 36px;
  height: 36px;

  flex-shrink: 0;

  align-items: center;
  justify-content: center;

  border-radius: 6px;

  background: #eef4ff;

  color: #2563eb;

  font-size: 15px;
}

/* ========================================
   Details
======================================== */

.stat-details {
  min-width: 0;
}

.stat-label {
  margin-bottom: 2px;

  color: #6b7280;

  font-size: 10.5px;
  font-weight: 500;

  line-height: 1.25;
}

.stat-value {
  color: #111827;

  font-size: 22px;
  font-weight: 700;

  line-height: 1.15;
}

/* ========================================
   Responsive
======================================== */

@media (max-width: 767.98px) {
  .page-header {
    margin-bottom: 12px;
  }

  .page-title {
    font-size: 17px;
  }

  .stat-card .card-body {
    padding: 12px 13px;
  }

  .stat-value {
    font-size: 20px;
  }
}
</style>