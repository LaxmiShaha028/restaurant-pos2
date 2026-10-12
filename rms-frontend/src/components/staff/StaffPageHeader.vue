<template>
  <header class="staff-header-card">
    <div class="staff-header-content">
      <!-- Left: Icon + Text -->
      <div class="staff-header-left">
        <div class="staff-header-icon">
          <i class="bi bi-person-badge" aria-hidden="true"></i>
        </div>

        <div class="staff-header-text">
          <p class="staff-page-eyebrow">
            Team Management
          </p>

          <h2>
            Staff Management
          </h2>

          <p>
            Manage employees, attendance, shifts, breaks and work schedules
          </p>
        </div>
      </div>

      <!-- Actions -->
      <div class="staff-header-actions">
        <button
          type="button"
          class="staff-refresh-btn"
          title="Refresh current section"
          @click="refresh"
        >
          <i class="bi bi-arrow-clockwise"></i>
        </button>
      </div>
    </div>
  </header>
</template>

<script setup>
const emit = defineEmits([
  'refresh',
])

function refresh() {
  emit('refresh')
}
</script>

<style scoped>
/* ==========================================================================
   STAFF HEADER
   Same size & spacing as Kitchen / Order / Table / Billing / etc.
   ========================================================================== */

.staff-header-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  margin-bottom: 8px;
  padding: 7px 8px;
  border: 1px solid #e5e7eb;
  border-radius: 5px;
  background: #ffffff;
  box-shadow: none;
}

.staff-header-content {
  display: flex;
  width: 100%;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
}

.staff-header-left {
  display: flex;
  min-width: 0;
  align-items: center;
  gap: 7px;
  flex: 1;
}

.staff-header-icon {
  display: inline-flex;
  width: 28px;
  height: 28px;
  flex: 0 0 28px;
  align-items: center;
  justify-content: center;
  border-radius: 4px;
  background: #eff6ff;
  color: #2563eb;
  font-size: 11px;
}

.staff-header-text {
  min-width: 0;
}

.staff-page-eyebrow {
  margin: 0 0 1px;
  color: #2563eb;
  font-size: 7px;
  font-weight: 700;
  line-height: 1.1;
  letter-spacing: 0.03em;
  text-transform: uppercase;
}

.staff-header-text h2 {
  margin: 0;
  color: #172033;
  font-size: 15px;
  font-weight: 700;
  line-height: 1.2;
  letter-spacing: 0;
}

.staff-header-text p {
  max-width: 580px;
  margin: 2px 0 0;
  overflow: hidden;
  color: #667085;
  font-size: 8px;
  line-height: 1.25;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.staff-header-actions {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-shrink: 0;
}

.staff-refresh-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
  min-height: 28px;
  padding: 0 8px;
  border: 1px solid #e5e7eb;
  border-radius: 4px;
  background: #f8fafc;
  color: #374151;
  font-size: 8px;
  font-weight: 600;
  white-space: nowrap;
  cursor: pointer;
  transition: background 0.15s ease;
}

.staff-refresh-btn:hover {
  background: #f1f5f9;
}

/* Responsive */
@media (max-width: 767.98px) {
  .staff-header-card {
    align-items: flex-start;
  }

  .staff-header-content {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }

  .staff-header-actions {
    width: 100%;
  }

  .staff-refresh-btn {
    width: 100%;
  }

  .staff-header-text p {
    white-space: normal;
  }
}
</style>