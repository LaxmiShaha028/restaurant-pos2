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
  background:
    radial-gradient(circle at 0 0, rgba(249, 115, 22, .055), transparent 28%),
    radial-gradient(circle at 100% 8%, rgba(251, 146, 60, .045), transparent 25%),
    #f4f1ed;
}

/* ========================================
   Card
======================================== */

.access-denied-card {
  width: 100%;
  max-width: 380px;

  padding: 28px 24px;

  border: 1px solid #eee3da;
  border-radius: 14px;

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

  background: #fff1e6;

  color: #f97316;

  font-size: 21px;
}

/* ========================================
   Title
======================================== */

.error-title {
  margin: 0;

  color: #111827;

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

  color: #667085;

  font-size: 11.5px;

  line-height: 1.5;
}

/* ========================================
   Button
======================================== */

.return-button {
  min-height: 34px;

  padding: 6px 11px;

  border: 1px solid #292929;
  border-radius: 10px;
  background: linear-gradient(145deg, #191919 0%, #111 54%, #090909);
  color: #f97316;

  font-size: 11.5px;
  font-weight: 600;
}

.return-button:hover,
.return-button:focus {
  border-color: #3b3b3b;
  background: linear-gradient(145deg, #242424 0%, #171717 54%, #101010);
  color: #fb923c;
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
