<script setup>
import { onMounted } from "vue";
import { storeToRefs } from "pinia";
import { useMenuManagementStore } from "../stores/menuManagement";

const menuStore = useMenuManagementStore();

const {
  categories,
  menuItems,
  variants,
  addOnCategories,
  addOns,
  loading,
  errorMessage,
} = storeToRefs(menuStore);

onMounted(async () => {
  try {
    await menuStore.fetchCategories();
    await menuStore.fetchMenuItems();
    await menuStore.fetchVariants();
    await menuStore.fetchAddOnCategories();
    await menuStore.fetchAddOns();
  } catch (error) {
    console.error("Menu store test failed:", error);
  }
});
</script>

<template>
  <section class="menu-test-page">
    <!-- Page Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">
          Menu Store Test
        </h1>

        <p class="page-subtitle">
          Menu management data overview
        </p>
      </div>
    </div>

    <!-- Error -->
    <div
      v-if="errorMessage"
      class="alert alert-danger page-alert"
    >
      <i class="bi bi-exclamation-circle me-2"></i>
      {{ errorMessage }}
    </div>

    <!-- Loading -->
    <div
      v-if="
        loading.categories ||
        loading.menuItems ||
        loading.variants ||
        loading.addOnCategories ||
        loading.addOns
      "
      class="loading-box"
    >
      <div
        class="spinner-border spinner-border-sm text-primary"
        role="status"
      ></div>

      <span>Loading menu data...</span>
    </div>

    <template v-else>
      <!-- Statistics -->
      <div class="row g-3">
        <!-- Categories -->
        <div class="col-12 col-sm-6 col-lg-4 col-xl">
          <div class="card stat-card">
            <div class="card-body">
              <div class="stat-wrapper">
                <div class="stat-content">
                  <div class="stat-label">
                    Categories
                  </div>

                  <div class="stat-value">
                    {{ categories.length }}
                  </div>
                </div>

                <div class="stat-icon categories-icon">
                  <i class="bi bi-tags"></i>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Menu Items -->
        <div class="col-12 col-sm-6 col-lg-4 col-xl">
          <div class="card stat-card">
            <div class="card-body">
              <div class="stat-wrapper">
                <div class="stat-content">
                  <div class="stat-label">
                    Menu Items
                  </div>

                  <div class="stat-value">
                    {{ menuItems.length }}
                  </div>
                </div>

                <div class="stat-icon items-icon">
                  <i class="bi bi-journal-text"></i>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Variants -->
        <div class="col-12 col-sm-6 col-lg-4 col-xl">
          <div class="card stat-card">
            <div class="card-body">
              <div class="stat-wrapper">
                <div class="stat-content">
                  <div class="stat-label">
                    Variants
                  </div>

                  <div class="stat-value">
                    {{ variants.length }}
                  </div>
                </div>

                <div class="stat-icon variants-icon">
                  <i class="bi bi-layers"></i>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Add-on Categories -->
        <div class="col-12 col-sm-6 col-lg-4 col-xl">
          <div class="card stat-card">
            <div class="card-body">
              <div class="stat-wrapper">
                <div class="stat-content">
                  <div class="stat-label">
                    Add-on Categories
                  </div>

                  <div class="stat-value">
                    {{ addOnCategories.length }}
                  </div>
                </div>

                <div class="stat-icon addon-category-icon">
                  <i class="bi bi-grid"></i>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Add-ons -->
        <div class="col-12 col-sm-6 col-lg-4 col-xl">
          <div class="card stat-card">
            <div class="card-body">
              <div class="stat-wrapper">
                <div class="stat-content">
                  <div class="stat-label">
                    Add-ons
                  </div>

                  <div class="stat-value">
                    {{ addOns.length }}
                  </div>
                </div>

                <div class="stat-icon addons-icon">
                  <i class="bi bi-plus-circle"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Categories Section -->
      <div class="categories-section">
        <div class="section-header">
          <div>
            <h2 class="section-title">
              Menu Categories
            </h2>

            <p class="section-subtitle">
              Available menu category list
            </p>
          </div>

          <span class="section-count">
            {{ categories.length }}
          </span>
        </div>

        <!-- Empty -->
        <div
          v-if="!categories.length"
          class="empty-state"
        >
          <i class="bi bi-inbox"></i>
          <span>No categories found.</span>
        </div>

        <!-- Category List -->
        <div
          v-else
          class="category-list"
        >
          <div
            v-for="category in categories"
            :key="category.id"
            class="category-item"
          >
            <div class="category-icon">
              <i class="bi bi-tag"></i>
            </div>

            <span class="category-name">
              {{ category.category_name }}
            </span>

            <span class="category-id">
              #{{ category.id }}
            </span>
          </div>
        </div>
      </div>
    </template>
  </section>
</template>

<style scoped>
.menu-test-page {
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
   ALERT
========================================================= */

.page-alert {
  margin-bottom: 12px;

  padding: 8px 10px;

  border-radius: 5px;

  font-size: 11.5px;
}


/* =========================================================
   LOADING
========================================================= */

.loading-box {
  display: flex;

  min-height: 90px;

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
  padding: 10px 11px;
}

.stat-wrapper {
  display: flex;

  min-height: 40px;

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

  font-size: 9.5px;
  font-weight: 500;

  line-height: 1.2;

  text-overflow: ellipsis;
  white-space: nowrap;
}

.stat-value {
  color: #111827;

  font-size: 18px;
  font-weight: 700;

  line-height: 1.1;
}


/* =========================================================
   STAT ICON
========================================================= */

.stat-icon {
  display: flex;

  width: 30px;
  height: 30px;

  flex-shrink: 0;

  align-items: center;
  justify-content: center;

  border-radius: 5px;

  font-size: 13px;
}

.categories-icon {
  background: #eff6ff;
  color: #2563eb;
}

.items-icon {
  background: #f0fdf4;
  color: #15803d;
}

.variants-icon {
  background: #f5f3ff;
  color: #7c3aed;
}

.addon-category-icon {
  background: #fff7ed;
  color: #ea580c;
}

.addons-icon {
  background: #fffbeb;
  color: #d97706;
}


/* =========================================================
   CATEGORY SECTION
========================================================= */

.categories-section {
  margin-top: 14px;

  overflow: hidden;

  border: 1px solid #e5e7eb;
  border-radius: 6px;

  background: #ffffff;

  box-shadow:
    0 1px 4px rgba(15, 23, 42, 0.035);
}


/* =========================================================
   SECTION HEADER
========================================================= */

.section-header {
  display: flex;

  align-items: center;
  justify-content: space-between;

  gap: 10px;

  padding: 9px 11px;

  border-bottom: 1px solid #e9ecef;

  background: #fafbfc;
}

.section-title {
  margin: 0;

  color: #1f2937;

  font-size: 12px;
  font-weight: 650;

  line-height: 1.3;
}

.section-subtitle {
  margin: 1px 0 0;

  color: #8a9199;

  font-size: 9.5px;

  line-height: 1.3;
}

.section-count {
  display: inline-flex;

  min-width: 22px;
  height: 21px;

  align-items: center;
  justify-content: center;

  padding: 0 6px;

  border-radius: 4px;

  background: #eef4ff;

  color: #2563eb;

  font-size: 9.5px;
  font-weight: 700;
}


/* =========================================================
   CATEGORY LIST
========================================================= */

.category-list {
  width: 100%;
}

.category-item {
  display: flex;

  min-height: 37px;

  align-items: center;

  gap: 7px;

  padding: 6px 10px;

  border-bottom: 1px solid #f0f1f3;

  transition:
    background 0.15s ease;
}

.category-item:last-child {
  border-bottom: 0;
}

.category-item:hover {
  background: #fafbfc;
}

.category-icon {
  display: flex;

  width: 25px;
  height: 25px;

  flex-shrink: 0;

  align-items: center;
  justify-content: center;

  border-radius: 4px;

  background: #eef4ff;

  color: #2563eb;

  font-size: 10.5px;
}

.category-name {
  min-width: 0;

  flex: 1;

  overflow: hidden;

  color: #374151;

  font-size: 10.5px;
  font-weight: 500;

  text-overflow: ellipsis;
  white-space: nowrap;
}

.category-id {
  flex-shrink: 0;

  color: #9ca3af;

  font-size: 9px;
}


/* =========================================================
   EMPTY STATE
========================================================= */

.empty-state {
  display: flex;

  min-height: 80px;

  align-items: center;
  justify-content: center;

  gap: 6px;

  color: #9ca3af;

  font-size: 10.5px;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 1199.98px) {
  .stat-value {
    font-size: 17px;
  }
}

@media (max-width: 767.98px) {
  .page-header {
    margin-bottom: 10px;
  }

  .page-title {
    font-size: 17px;
  }

  .categories-section {
    margin-top: 12px;
  }
}

@media (max-width: 575.98px) {
  .stat-card .card-body {
    padding: 9px 10px;
  }

  .stat-value {
    font-size: 16px;
  }

  .stat-icon {
    width: 28px;
    height: 28px;

    font-size: 12px;
  }

  .category-item {
    min-height: 35px;

    padding: 6px 8px;
  }
}
</style>