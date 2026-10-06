<template>
  <header class="supplier-page-header">
    <div class="supplier-header-content">
      <!-- Left: Icon + Text -->
      <div class="supplier-header-left">
        <div class="supplier-header-icon">
          <i class="bi bi-truck" aria-hidden="true"></i>
        </div>

        <div class="supplier-header-text">
          <p class="supplier-page-eyebrow">
            Procurement
          </p>

          <h1>
            Suppliers
          </h1>

          <p>
            Manage supplier information,
            purchase orders and supplier history.
          </p>
        </div>
      </div>

      <!-- Right: Future actions -->
      <div class="supplier-header-actions">
        <!-- Future: Add Supplier Button -->
      </div>
    </div>
  </header>
</template>

<script setup>
</script>

<style scoped>
/* ==========================================================================
   SUPPLIER PAGE HEADER
   Same size & spacing as Kitchen / Order / Table / Billing / etc.
   ========================================================================== */

.supplier-page-header {
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

.supplier-header-content {
  display: flex;
  width: 100%;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
}

.supplier-header-left {
  display: flex;
  min-width: 0;
  align-items: center;
  gap: 7px;
}

.supplier-header-icon {
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

.supplier-header-text {
  min-width: 0;
}

.supplier-page-eyebrow {
  margin: 0 0 1px;
  color: #2563eb;
  font-size: 7px;
  font-weight: 700;
  line-height: 1.1;
  letter-spacing: 0.03em;
  text-transform: uppercase;
}

.supplier-header-text h1 {
  margin: 0;
  color: #172033;
  font-size: 15px;
  font-weight: 700;
  line-height: 1.2;
  letter-spacing: 0;
}

.supplier-header-text p {
  max-width: 580px;
  margin: 2px 0 0;
  overflow: hidden;
  color: #667085;
  font-size: 8px;
  line-height: 1.25;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.supplier-header-actions {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-shrink: 0;
}

/* Responsive */
@media (max-width: 767.98px) {
  .supplier-page-header {
    align-items: flex-start;
  }

  .supplier-header-content {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }

  .supplier-header-text p {
    white-space: normal;
  }
}
</style>