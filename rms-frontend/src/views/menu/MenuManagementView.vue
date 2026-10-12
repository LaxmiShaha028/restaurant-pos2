<script setup>
import {
  computed,
  ref,
} from 'vue'

import MenuCategoriesSection from '@/components/menu/sections/MenuCategoriesSection.vue'
import MenuItemsSection from '@/components/menu/sections/MenuItemsSection.vue'
import MenuVariantsSection from '@/components/menu/sections/MenuVariantsSection.vue'
import AddOnsSection from '@/components/menu/sections/AddOnsSection.vue'

const activeTab = ref('categories')

const tabs = [
  {
    id: 'categories',
    label: 'Categories',
    icon: 'bi bi-grid',
    component: MenuCategoriesSection,
  },
  {
    id: 'items',
    label: 'Menu Items',
    icon: 'bi bi-card-list',
    component: MenuItemsSection,
  },
  {
    id: 'variants',
    label: 'Variants',
    icon: 'bi bi-layers',
    component: MenuVariantsSection,
  },
  {
    id: 'addons',
    label: 'Add-ons',
    icon: 'bi bi-plus-circle',
    component: AddOnsSection,
  },
]

const activeComponent = computed(() => {
  return (
    tabs.find(
      (tab) => tab.id === activeTab.value,
    )?.component ?? MenuCategoriesSection
  )
})

const activeLabel = computed(() => {
  return (
    tabs.find(
      (tab) => tab.id === activeTab.value,
    )?.label ?? 'Categories'
  )
})
</script>

<template>
  <div class="menu-management-page">
    <!-- ==================================================
         Page Header (same style as Kitchen / Order / etc.)
    =================================================== -->
    <header class="menu-management-header">
      <div class="menu-management-header-left">
        <div class="menu-management-header-icon">
          <i class="bi bi-journal-richtext"></i>
        </div>

        <div>
          <p class="menu-management-eyebrow">
            Back Office
          </p>

          <h1 class="menu-management-title">
            Menu Management
          </h1>

          <p class="menu-management-description">
            Currently managing:
            <strong>{{ activeLabel }}</strong>
          </p>
        </div>
      </div>
    </header>

    <!-- ==================================================
         Main Card + Tabs
    =================================================== -->
    <section class="menu-management-card">
      <nav class="menu-management-tabs">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          type="button"
          class="menu-management-tab"
          :class="{ active: activeTab === tab.id }"
          @click="activeTab = tab.id"
        >
          <i :class="tab.icon"></i>
          <span>{{ tab.label }}</span>
        </button>
      </nav>

      <KeepAlive>
        <component :is="activeComponent" />
      </KeepAlive>
    </section>
  </div>
</template>

<style scoped>
/* ==========================================================================
   MENU MANAGEMENT PAGE
   Compact POS / Admin Theme
   ========================================================================== */

.menu-management-page {
  width: 100%;
  max-width: none;
  min-width: 0;
  min-height: 100%;
  margin: 0;
  padding: 0;
  background: transparent;
  color: #344054;
  font-size: 9px;
}

/* =========================================================
   HEADER  (same size & spacing as other pages)
========================================================= */

.menu-management-header {
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

.menu-management-header-left {
  display: flex;
  min-width: 0;
  align-items: center;
  gap: 7px;
}

.menu-management-header-icon {
  display: inline-flex;
  width: 28px;
  height: 28px;
  flex: 0 0 28px;
  align-items: center;
  justify-content: center;
  border-radius: 4px;
  background: #eff6ff;
  color: #2563eb;
  font-size: 15px;
}

.menu-management-header-left > div:last-child {
  min-width: 0;
}

.menu-management-eyebrow {
  margin: 0 0 1px;
  color: #2563eb;
  font-size: 10px;
  font-weight: 700;
  line-height: 1.1;
  letter-spacing: 0.03em;
  text-transform: uppercase;
}

.menu-management-title {
  margin: 0;
  color: #172033;
  font-size: 15px;
  font-weight: 700;
  line-height: 1.2;
  letter-spacing: 0;
}

.menu-management-description {
  max-width: 620px;
  margin: 2px 0 0;
  overflow: hidden;
  color: #667085;
  font-size: 10px;
  line-height: 1.25;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.menu-management-description strong {
  color: #172033;
  font-weight: 650;
}

/* =========================================================
   CARD
========================================================= */

.menu-management-card {
  overflow: hidden;
  border: 1px solid #e5e7eb;
  border-radius: 5px;
  background: #ffffff;
  box-shadow: none;
}

/* =========================================================
   TABS
========================================================= */

.menu-management-tabs {
  display: flex;
  gap: 2px;
  overflow-x: auto;
  padding: 5px 8px 0;
  border-bottom: 1px solid #e5e7eb;
  background: #fafbfc;
}

.menu-management-tab {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  flex-shrink: 0;
  padding: 7px 10px;
  border: 0;
  border-bottom: 2px solid transparent;
  background: transparent;
  color: #667085;
  font-size: 12px;
  font-weight: 600;
  white-space: nowrap;
  cursor: pointer;
  transition: color 0.15s ease, border-color 0.15s ease;
}

.menu-management-tab i {
  font-size: 12px;
}

.menu-management-tab:hover {
  color: #2563eb;
}

.menu-management-tab.active {
  border-bottom-color: #2563eb;
  color: #2563eb;
}

/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 767.98px) {
  .menu-management-header {
    margin-bottom: 7px;
  }

  .menu-management-title {
    font-size: 14px;
  }

  .menu-management-description {
    white-space: normal;
  }

  .menu-management-tab {
    padding: 6px 9px;
  }
}
</style>

<style src="@/assets/css/menu-compact.css"></style>