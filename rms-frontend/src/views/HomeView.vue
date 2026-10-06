<script setup>
const applicationName = 'Restaurant Management System'
</script>

<template>
  <main class="foundation-page">
    <!-- Top Navbar -->
    <nav class="foundation-navbar">
      <div class="foundation-container navbar-content">
        <a
          class="brand-link"
          href="#"
        >
          <span class="brand-icon">
            <i class="bi bi-shop"></i>
          </span>

          <span class="brand-text">
            RMS
          </span>
        </a>

        <span class="status-badge">
          <i class="bi bi-check-circle"></i>
          Foundation Ready
        </span>
      </div>
    </nav>

    <!-- Main Content -->
    <section class="foundation-content">
      <div class="foundation-container">
        <div class="foundation-card">
          <!-- Header -->
          <div class="application-header">
            <div class="feature-icon">
              <i class="bi bi-cup-hot"></i>
            </div>

            <div class="application-details">
              <h1 class="application-title">
                {{ applicationName }}
              </h1>

              <p class="application-subtitle">
                Laravel REST API and Vue 3 frontend
              </p>
            </div>
          </div>

          <!-- Success Message -->
          <div class="success-message">
            <i class="bi bi-check-circle-fill"></i>

            <span>
              Vue frontend has been configured successfully.
            </span>
          </div>

          <!-- Modules -->
          <div class="row g-3">
            <div class="col-12 col-md-4">
              <div class="module-card">
                <div class="module-icon">
                  <i class="bi bi-server"></i>
                </div>

                <div class="module-content">
                  <h2>Laravel API</h2>

                  <p>
                    Backend business logic and database management.
                  </p>
                </div>
              </div>
            </div>

            <div class="col-12 col-md-4">
              <div class="module-card">
                <div class="module-icon">
                  <i class="bi bi-window-sidebar"></i>
                </div>

                <div class="module-content">
                  <h2>Vue 3</h2>

                  <p>
                    Responsive user interface and application screens.
                  </p>
                </div>
              </div>
            </div>

            <div class="col-12 col-md-4">
              <div class="module-card">
                <div class="module-icon">
                  <i class="bi bi-database"></i>
                </div>

                <div class="module-content">
                  <h2>MySQL</h2>

                  <p>
                    Restaurant operational and financial data storage.
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div class="foundation-footer">
            <i class="bi bi-info-circle"></i>

            <span>
              Next step: MySQL database configuration and initial
              system database structure.
            </span>
          </div>
        </div>
      </div>
    </section>
  </main>
</template>

<style scoped>
.foundation-page {
  width: 100%;
  min-height: 100vh;

  background: #f4f6f9;

  color: #1f2937;
}


/* =========================================================
   CONTAINER
========================================================= */

.foundation-container {
  width: 100%;
  max-width: 1050px;

  margin: 0 auto;

  padding: 0 16px;
}


/* =========================================================
   NAVBAR
========================================================= */

.foundation-navbar {
  height: 54px;

  display: flex;
  align-items: center;

  border-bottom: 1px solid #e5e7eb;

  background: #ffffff;
}

.navbar-content {
  display: flex;

  align-items: center;
  justify-content: space-between;
}


/* =========================================================
   BRAND
========================================================= */

.brand-link {
  display: inline-flex;

  align-items: center;

  gap: 7px;

  color: #2563eb;

  text-decoration: none;
}

.brand-icon {
  display: flex;

  width: 30px;
  height: 30px;

  align-items: center;
  justify-content: center;

  border-radius: 5px;

  background: #eef4ff;

  color: #2563eb;

  font-size: 14px;
}

.brand-text {
  font-size: 13px;
  font-weight: 700;
}


/* =========================================================
   STATUS
========================================================= */

.status-badge {
  display: inline-flex;

  align-items: center;

  gap: 4px;

  padding: 4px 7px;

  border-radius: 4px;

  background: #ecfdf5;

  color: #047857;

  font-size: 9.5px;
  font-weight: 600;
}


/* =========================================================
   MAIN CONTENT
========================================================= */

.foundation-content {
  padding: 28px 0;
}


/* =========================================================
   CARD
========================================================= */

.foundation-card {
  padding: 20px;

  border: 1px solid #e5e7eb;
  border-radius: 7px;

  background: #ffffff;

  box-shadow:
    0 2px 8px rgba(15, 23, 42, 0.04);
}


/* =========================================================
   APPLICATION HEADER
========================================================= */

.application-header {
  display: flex;

  align-items: center;

  gap: 10px;

  margin-bottom: 15px;
}

.feature-icon {
  display: flex;

  width: 38px;
  height: 38px;

  flex-shrink: 0;

  align-items: center;
  justify-content: center;

  border-radius: 6px;

  background: #eef4ff;

  color: #2563eb;

  font-size: 17px;
}

.application-details {
  min-width: 0;
}

.application-title {
  margin: 0;

  color: #1f2937;

  font-size: 17px;
  font-weight: 700;

  line-height: 1.3;
}

.application-subtitle {
  margin: 2px 0 0;

  color: #6b7280;

  font-size: 10.5px;

  line-height: 1.4;
}


/* =========================================================
   SUCCESS MESSAGE
========================================================= */

.success-message {
  display: flex;

  align-items: center;

  gap: 7px;

  margin-bottom: 15px;

  padding: 8px 10px;

  border: 1px solid #bbf7d0;
  border-radius: 5px;

  background: #f0fdf4;

  color: #166534;

  font-size: 11px;
}

.success-message i {
  flex-shrink: 0;

  font-size: 12px;
}


/* =========================================================
   MODULE CARD
========================================================= */

.module-card {
  display: flex;

  height: 100%;

  align-items: flex-start;

  gap: 9px;

  padding: 12px;

  border: 1px solid #e5e7eb;
  border-radius: 6px;

  background: #fafbfc;

  transition:
    border-color 0.15s ease,
    background 0.15s ease;
}

.module-card:hover {
  border-color: #d8dee6;

  background: #ffffff;
}

.module-icon {
  display: flex;

  width: 30px;
  height: 30px;

  flex-shrink: 0;

  align-items: center;
  justify-content: center;

  border-radius: 5px;

  background: #eef4ff;

  color: #2563eb;

  font-size: 13px;
}

.module-content {
  min-width: 0;
}

.module-card h2 {
  margin: 0 0 3px;

  color: #1f2937;

  font-size: 11.5px;
  font-weight: 650;

  line-height: 1.3;
}

.module-card p {
  margin: 0;

  color: #6b7280;

  font-size: 10px;

  line-height: 1.45;
}


/* =========================================================
   FOOTER
========================================================= */

.foundation-footer {
  display: flex;

  align-items: flex-start;

  gap: 6px;

  margin-top: 15px;
  padding-top: 13px;

  border-top: 1px solid #eceff2;

  color: #6b7280;

  font-size: 10px;

  line-height: 1.45;
}

.foundation-footer i {
  margin-top: 1px;

  flex-shrink: 0;

  color: #2563eb;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 767.98px) {
  .foundation-content {
    padding: 18px 0;
  }

  .foundation-card {
    padding: 16px;
  }

  .application-title {
    font-size: 16px;
  }
}

@media (max-width: 575.98px) {
  .foundation-container {
    padding: 0 12px;
  }

  .foundation-navbar {
    height: 50px;
  }

  .foundation-card {
    padding: 14px;
  }

  .application-header {
    align-items: flex-start;
  }

  .status-badge {
    font-size: 9px;
  }
}
</style>