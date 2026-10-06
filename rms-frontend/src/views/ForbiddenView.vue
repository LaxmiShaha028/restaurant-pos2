<template>
  <main class="access-denied-page">
    <div class="access-denied-card">
      <div class="error-icon">
        <i class="bi bi-shield-exclamation"></i>
      </div>

      <h1 class="error-title">
        Access Denied
      </h1>

      <p class="error-text">
        You do not have permission to access this page.
      </p>

      <RouterLink
        to="/dashboard"
        class="btn btn-primary return-button"
      >
        <i class="bi bi-arrow-left me-1"></i>
        Return to Dashboard
      </RouterLink>
    </div>
  </main>
</template>

<script setup>
import { RouterLink } from 'vue-router'
</script>

<style scoped>
.access-denied-page {
  display: flex;

  width: 100%;
  min-height: calc(100vh - 90px);

  align-items: center;
  justify-content: center;

  padding: 20px;
}

/* ========================================
   Card
======================================== */

.access-denied-card {
  width: 100%;
  max-width: 380px;

  padding: 28px 24px;

  border: 1px solid #e5e7eb;
  border-radius: 7px;

  background: #ffffff;

  box-shadow:
    0 3px 10px rgba(15, 23, 42, 0.05);

  text-align: center;
}

/* ========================================
   Icon
======================================== */

.error-icon {
  display: flex;

  width: 46px;
  height: 46px;

  align-items: center;
  justify-content: center;

  margin: 0 auto 12px;

  border-radius: 50%;

  background: #fef2f2;

  color: #dc2626;

  font-size: 21px;
}

/* ========================================
   Title
======================================== */

.error-title {
  margin: 0;

  color: #1f2937;

  font-size: 18px;
  font-weight: 700;

  line-height: 1.3;
}

/* ========================================
   Description
======================================== */

.error-text {
  max-width: 290px;

  margin: 6px auto 16px;

  color: #6b7280;

  font-size: 11.5px;

  line-height: 1.5;
}

/* ========================================
   Button
======================================== */

.return-button {
  min-height: 34px;

  padding: 6px 11px;

  border-radius: 5px;

  font-size: 11.5px;
  font-weight: 600;
}

/* ========================================
   Responsive
======================================== */

@media (max-width: 575.98px) {
  .access-denied-page {
    align-items: flex-start;

    padding: 40px 14px;
  }

  .access-denied-card {
    max-width: 100%;

    padding: 24px 18px;
  }

  .error-title {
    font-size: 17px;
  }

  .error-icon {
    width: 42px;
    height: 42px;

    font-size: 19px;
  }
}
</style>