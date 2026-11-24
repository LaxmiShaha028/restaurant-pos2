<template>
  <div class="dashboard-layout" :class="{ 'pickup-kiosk-layout': isPickupKiosk, 'dashboard-route': route.name === 'dashboard' }">
    <AppSidebar
      v-if="!isPickupKiosk"
      :mobile-open="mobileSidebarOpen"
      :collapsed="desktopSidebarCollapsed"
      @close="closeMobileSidebar"
      @toggle-collapse="toggleDesktopSidebar"
      @user-menu="openUserDropdown"
    />

    <div
      class="main-wrapper"
      :class="{
        'main-wrapper-collapsed': desktopSidebarCollapsed,
        'pickup-kiosk-main': isPickupKiosk,
      }"
    >
      <header v-if="!isPickupKiosk" class="app-topbar">
        <div class="topbar-left">
          <button
            type="button"
            class="topbar-icon-button d-lg-none"
            aria-label="Open sidebar"
            @click="openMobileSidebar"
          >
            <i class="bi bi-list"></i>
          </button>

          <RouterLink
            :to="{ name: 'dashboard' }"
            class="mobile-restaurant-brand d-lg-none"
            :title="systemSettingStore.restaurantName || 'Restaurant'"
            aria-label="Restaurant dashboard"
          >
            <img
              v-if="mobileLogoUrl && !mobileLogoFailed"
              :src="mobileLogoUrl"
              :alt="systemSettingStore.restaurantName || 'Restaurant logo'"
              class="mobile-restaurant-logo"
              @error="mobileLogoFailed = true"
            />
            <span v-else class="mobile-restaurant-logo-fallback" aria-hidden="true">
              <i class="bi bi-shop"></i>
            </span>
            <span class="mobile-restaurant-name">
              {{ systemSettingStore.restaurantName || 'Restaurant' }}
            </span>
          </RouterLink>

          <button
            type="button"
            class="topbar-icon-button d-none d-lg-flex"
            :title="
              desktopSidebarCollapsed
                ? 'Expand sidebar'
                : 'Collapse sidebar'
            "
            :aria-label="
              desktopSidebarCollapsed
                ? 'Expand sidebar'
                : 'Collapse sidebar'
            "
            @click="toggleDesktopSidebar"
          >
            <i
              class="bi"
              :class="
                desktopSidebarCollapsed
                  ? 'bi-layout-sidebar-inset'
                  : 'bi-layout-sidebar-inset-reverse'
              "
            ></i>
          </button>

          <div class="page-heading">
            <h2 class="page-heading-title">
              {{ currentPageTitle }}
            </h2>
            <p class="page-heading-subtitle">
              Restaurant Management System
            </p>
          </div>
        </div>

        <div class="topbar-right">
          <!-- Notification -->
          <div
            ref="notificationDropdownContainer"
            class="notification-center"
            @click.stop
          >
            <button
              type="button"
              class="topbar-icon-button notification-button"
              :class="{ show: notificationOpen }"
              title="Notifications"
              aria-label="Notifications"
              :aria-expanded="notificationOpen"
              @click.stop="toggleNotifications"
            >
              <i
                :class="
                  notificationCount
                    ? 'bi bi-bell-fill'
                    : 'bi bi-bell'
                "
              ></i>

              <span
                v-if="notificationCount"
                class="notification-badge"
              >
                {{ notificationBadge }}
              </span>
            </button>

            <div
              v-show="notificationOpen"
              class="notification-dropdown"
              @click.stop
            >
              <div class="notification-header">
                <div>
                  <span class="notification-eyebrow">
                    RESTAURANT ALERTS
                  </span>
                  <h3>Notifications</h3>
                </div>

                <button
                  type="button"
                  class="notification-refresh-button"
                  :disabled="notificationLoading"
                  title="Refresh notifications"
                  @click.stop="loadNotifications(false)"
                >
                  <span
                    v-if="notificationLoading"
                    class="spinner-border spinner-border-sm"
                  ></span>
                  <i
                    v-else
                    class="bi bi-arrow-clockwise"
                  ></i>
                </button>
              </div>

              <div
                v-if="notificationLoading && !notificationUpdatedAt"
                class="notification-state"
              >
                <span class="spinner-border spinner-border-sm"></span>
                <span>Checking restaurant activity...</span>
              </div>

              <div
                v-else-if="notificationError"
                class="notification-state notification-error"
              >
                <i class="bi bi-exclamation-circle"></i>
                <div>
                  <strong>Could not load notifications</strong>
                  <span>{{ notificationError }}</span>
                </div>
              </div>

              <div
                v-else-if="notifications.length"
                class="notification-list"
              >
                <div
                  v-for="item in notifications"
                  :key="item.key"
                  class="notification-item"
                  :class="`notification-${item.type}`"
                >
                  <span class="notification-item-icon">
                    <i :class="item.icon"></i>
                  </span>

                  <div class="notification-item-content">
                    <div class="notification-item-top">
                      <strong>{{ item.title }}</strong>
                      <span>{{ item.value }}</span>
                    </div>
                    <p>{{ item.message }}</p>
                  </div>
                </div>
              </div>

              <div
                v-else
                class="notification-empty"
              >
                <span>
                  <i class="bi bi-check2-circle"></i>
                </span>
                <strong>No new notifications</strong>
                <p>You're all caught up right now.</p>
              </div>

              <div class="notification-footer">
                <span>
                  <i class="bi bi-clock"></i>
                  Updated {{ notificationUpdatedText }}
                </span>

                <button
                  type="button"
                  @click="closeNotifications"
                >
                  Close
                </button>
              </div>
            </div>
          </div>

          <div class="topbar-shortcuts" aria-label="Quick links">
            <RouterLink
              v-if="canOpenPosOrder"
              :to="{ name: 'pos-order-tables' }"
              class="topbar-icon-button pos-order-shortcut"
              title="POS Order"
              aria-label="Open POS Order"
            >
              <i class="bi bi-columns-gap" aria-hidden="true"></i>
            </RouterLink>

            <RouterLink
              v-if="canOpenSuppliers"
              :to="{ name: 'suppliers' }"
              class="topbar-icon-button pos-order-shortcut"
              title="Suppliers"
              aria-label="Open Suppliers"
            >
              <i class="bi bi-truck" aria-hidden="true"></i>
            </RouterLink>

            <RouterLink
              v-if="canOpenStaff"
              :to="{ name: 'staff' }"
              class="topbar-icon-button pos-order-shortcut"
              title="Staff"
              aria-label="Open Staff"
            >
              <i class="bi bi-person-badge" aria-hidden="true"></i>
            </RouterLink>

            <RouterLink
              v-if="canOpenReports"
              :to="{ name: 'reports' }"
              class="topbar-icon-button pos-order-shortcut"
              title="Reports"
              aria-label="Open Reports"
            >
              <i class="bi bi-bar-chart" aria-hidden="true"></i>
            </RouterLink>

            <RouterLink
              v-if="canOpenReservations"
              :to="{ name: 'reservations' }"
              class="topbar-icon-button pos-order-shortcut"
              title="Reservations"
              aria-label="Open Reservations"
            >
              <i class="bi bi-calendar2-check" aria-hidden="true"></i>
            </RouterLink>
          </div>

          <!-- User dropdown -->
          <div
            ref="userDropdownContainer"
            class="dropdown user-dropdown-container"
            @click.stop
          >
            <button
              ref="userDropdownButton"
              class="user-dropdown-button dropdown-toggle"
              type="button"
              :class="{ show: userDropdownOpen }"
              :aria-expanded="userDropdownOpen"
              @click.stop="toggleUserDropdown"
            >
              <div class="topbar-avatar">
                {{ userInitials }}
              </div>

              <div class="user-dropdown-details d-none d-sm-block">
                <div class="topbar-user-name">
                  {{ authStore.user?.name || 'User' }}
                </div>
                <div class="topbar-user-role">
                  {{ roleDisplayName }}
                </div>
              </div>
            </button>

            <ul
              v-show="userDropdownOpen"
              class="dropdown-menu dropdown-menu-end user-dropdown-menu show"
              @click.stop
            >
              <li class="dropdown-user-summary">
                <div class="dropdown-avatar">
                  {{ userInitials }}
                </div>
                <div>
                  <div class="fw-semibold">
                    {{ authStore.user?.name || 'User' }}
                  </div>
                  <div class="small text-secondary">
                    {{ roleDisplayName }}
                  </div>
                  <div class="small text-secondary">
                    {{ authStore.user?.email }}
                  </div>
                </div>
              </li>

              <li>
                <hr class="dropdown-divider" />
              </li>

              <li>
                <RouterLink
                  to="/settings"
                  class="dropdown-item"
                  @click="closeUserDropdown"
                >
                  <i class="bi bi-gear me-2"></i>
                  Account Settings
                </RouterLink>
              </li>

              <li>
                <button
                  type="button"
                  class="dropdown-item text-danger"
                  :disabled="authStore.loading"
                  @click="handleLogout"
                >
                  <i class="bi bi-box-arrow-right me-2"></i>
                  {{
                    authStore.loading
                      ? 'Signing out...'
                      : 'Logout'
                  }}
                </button>
              </li>
            </ul>
          </div>
        </div>
      </header>

      <main class="dashboard-content">
        <RouterView />
      </main>
    </div>
  </div>
</template>

<script setup>
import {
  computed,
  onBeforeUnmount,
  onMounted,
  ref,
  watch,
} from 'vue'
import {
  RouterLink,
  RouterView,
  useRoute,
  useRouter,
} from 'vue-router'

import AppSidebar from '@/components/AppSidebar.vue'
import { useAuthStore } from '@/stores/auth'
import { useSystemSettingStore } from '@/stores/systemSetting'
import { normalizeRestaurantLogoUrl } from '@/utils/restaurantLogo'
import api from '@/services/api'

const SIDEBAR_STORAGE_KEY = 'rms_sidebar_collapsed'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const systemSettingStore = useSystemSettingStore()
const mobileLogoFailed = ref(false)
const mobileLogoUrl = computed(() =>
  normalizeRestaurantLogoUrl(systemSettingStore.restaurantLogo),
)

watch(mobileLogoUrl, () => {
  mobileLogoFailed.value = false
})

const isPickupKiosk = computed(() => route.name === 'pickup-display')

const canOpenPosOrder = computed(() => {
  if (typeof authStore.hasPermission !== 'function') {
    return false
  }

  return authStore.hasPermission('orders.create')
})

const canOpenSuppliers = computed(() => {
  return typeof authStore.hasPermission === 'function' &&
    authStore.hasPermission('suppliers.manage')
})

const canOpenStaff = computed(() => {
  return typeof authStore.hasPermission === 'function' &&
    authStore.hasPermission('staff.manage')
})

const canOpenReports = computed(() => {
  return typeof authStore.hasPermission === 'function' &&
    authStore.hasPermission('reports.view')
})

const canOpenReservations = computed(() => {
  return typeof authStore.hasPermission === 'function' &&
    authStore.hasPermission('reservations.manage')
})

const mobileSidebarOpen = ref(false)
const desktopSidebarCollapsed = ref(
  localStorage.getItem(SIDEBAR_STORAGE_KEY) === 'true',
)

const userDropdownContainer = ref(null)
const userDropdownOpen = ref(false)

const notificationDropdownContainer = ref(null)
const notificationOpen = ref(false)
const notificationLoading = ref(false)
const notificationError = ref('')
const notificationSummary = ref({})
const notificationSubscription = ref(null)
const notificationUpdatedAt = ref(null)
const seenNotificationKeys = ref(new Set())

let notificationPollTimer = null

/* =========================================================
   COMPUTED VALUES
========================================================= */
const currentPageTitle = computed(() => {
  return route.meta.title || 'Dashboard'
})

const roleDisplayName = computed(() => {
  return (
    authStore.user?.role?.display_name ||
    authStore.user?.role?.name ||
    'User'
  )
})

const userInitials = computed(() => {
  const name = authStore.user?.name?.trim()

  if (!name) {
    return 'U'
  }

  const words = name
    .split(/\s+/)
    .filter(Boolean)

  if (words.length === 1) {
    return words[0]
      .charAt(0)
      .toUpperCase()
  }

  return `${words[0].charAt(0)}${words[
    words.length - 1
  ].charAt(0)}`.toUpperCase()
})

/* =========================================================
   NOTIFICATIONS
========================================================= */
function subscriptionRemainingText(endAt) {
  if (!endAt) {
    return 'N/A'
  }

  const end = new Date(endAt)

  if (Number.isNaN(end.getTime())) {
    return 'N/A'
  }

  const diffMs = end.getTime() - Date.now()

  if (diffMs <= 0) {
    return 'Expired'
  }

  const totalMinutes = Math.floor(
    diffMs / (1000 * 60),
  )

  const days = Math.floor(
    totalMinutes / (60 * 24),
  )

  const hours = Math.floor(
    (totalMinutes % (60 * 24)) / 60,
  )

  const minutes = Math.max(
    1,
    totalMinutes % 60,
  )

  if (days > 0) {
    return `${days} day${days === 1 ? '' : 's'} left`
  }

  if (hours > 0) {
    return `${hours} hour${hours === 1 ? '' : 's'} left`
  }

  return `${minutes} min left`
}

function subscriptionExpiryText(endAt) {
  if (!endAt) {
    return 'Expiry date is not available.'
  }

  const end = new Date(endAt)

  if (Number.isNaN(end.getTime())) {
    return 'Expiry date is not available.'
  }

  return `Expires ${end.toLocaleString('en-BD', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })}.`
}

const notifications = computed(() => {
  const summary = notificationSummary.value || {}
  const subscription =
    notificationSubscription.value || {}
  const items = []

  const subscriptionEndAt =
    subscription.end_at ||
    subscription.trial_end_at ||
    null

  if (
    subscription.status ||
    subscriptionEndAt
  ) {
    const planName =
      subscription.plan?.name ||
      'Current Plan'

    items.push({
      key: 'subscription',
      type: 'subscription',
      icon: 'bi bi-hourglass-split',
      title: 'Subscription',
      value: subscriptionRemainingText(
        subscriptionEndAt,
      ),
      message:
        `${planName}. ${subscriptionExpiryText(
          subscriptionEndAt,
        )}`,
    })
  }

  const pendingKitchen = Number(
    summary.pending_kitchen_orders || 0,
  )
  const upcomingReservations = Number(
    summary.upcoming_reservations || 0,
  )
  const dueAmount = Number(
    summary.due_amount || 0,
  )
  const activeOrders = Number(
    summary.active_orders || 0,
  )

  if (pendingKitchen > 0) {
    items.push({
      key: 'pending-kitchen',
      type: 'kitchen',
      icon: 'bi bi-fire',
      title: 'Kitchen Queue',
      value: String(pendingKitchen),
      message:
        `${pendingKitchen} order${
          pendingKitchen === 1 ? '' : 's'
        } waiting in the kitchen queue.`,
    })
  }

  if (upcomingReservations > 0) {
    items.push({
      key: 'upcoming-reservations',
      type: 'reservation',
      icon: 'bi bi-calendar-event',
      title: 'Upcoming Reservations',
      value: String(upcomingReservations),
      message:
        `${upcomingReservations} upcoming reservation${
          upcomingReservations === 1 ? '' : 's'
        } to review.`,
    })
  }

  if (dueAmount > 0) {
    items.push({
      key: 'due-payment',
      type: 'payment',
      icon: 'bi bi-wallet2',
      title: 'Payment Due',
      value: money(dueAmount),
      message:
        'There is still an outstanding customer payment amount.',
    })
  }

  if (activeOrders > 0) {
    items.push({
      key: 'active-orders',
      type: 'order',
      icon: 'bi bi-receipt',
      title: 'Active Orders',
      value: String(activeOrders),
      message:
        `${activeOrders} order${
          activeOrders === 1 ? '' : 's'
        } currently active in restaurant operations.`,
    })
  }

  return items
})

const notificationCount = computed(() => {
  return notifications.value.filter(
    (notification) =>
      !seenNotificationKeys.value.has(
        notificationReadKey(notification),
      ),
  ).length
})

const notificationBadge = computed(() => {
  return notificationCount.value > 9
    ? '9+'
    : String(notificationCount.value)
})

const notificationUpdatedText = computed(() => {
  if (!notificationUpdatedAt.value) {
    return 'just now'
  }

  return new Date(
    notificationUpdatedAt.value,
  ).toLocaleTimeString('en-BD', {
    hour: '2-digit',
    minute: '2-digit',
  })
})

function money(value) {
  return `৳ ${Number(value || 0).toLocaleString(
    'en-BD',
    {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    },
  )}`
}

function notificationReadKey(notification) {
  const identity =
    notification.key === 'subscription'
      ? notificationSubscription.value?.id ||
        notificationSubscription.value?.end_at
      : notification.value

  return `${notification.key}:${identity ?? ''}`
}

function markNotificationsRead() {
  seenNotificationKeys.value = new Set([
    ...seenNotificationKeys.value,
    ...notifications.value.map(
      notificationReadKey,
    ),
  ])
}

async function loadNotifications(silent = false) {
  if (notificationLoading.value) {
    return
  }

  if (!silent) {
    notificationError.value = ''
  }

  notificationLoading.value = true

  try {
    const tenantContext =
      authStore.getTenantContext?.() ||
      authStore.tenant ||
      null

    const tenantKey = String(
      authStore.tenantKey ||
      tenantContext?.tenant_key ||
      '',
    ).trim()

    const dashboardRequest =
      api.get('/dashboard')

    const subscriptionRequest =
      tenantKey
        ? api.get(
            `/tenant/resolve/${encodeURIComponent(
              tenantKey,
            )}`,
            {
              params: {
                _t: Date.now(),
              },
            },
          )
        : Promise.resolve(null)

    const [
      dashboardResult,
      subscriptionResult,
    ] = await Promise.allSettled([
      dashboardRequest,
      subscriptionRequest,
    ])

    let loadedSomething = false

    if (
      dashboardResult.status ===
      'fulfilled'
    ) {
      notificationSummary.value =
        dashboardResult.value?.data?.data
          ?.summary ?? {}

      loadedSomething = true
    }

    if (
      subscriptionResult.status ===
        'fulfilled' &&
      subscriptionResult.value
    ) {
      notificationSubscription.value =
        subscriptionResult.value?.data?.data
          ?.subscription ?? null

      loadedSomething = true
    }

    if (!loadedSomething) {
      const dashboardError =
        dashboardResult.status ===
        'rejected'
          ? dashboardResult.reason
          : null

      const subscriptionError =
        subscriptionResult.status ===
        'rejected'
          ? subscriptionResult.reason
          : null

      throw (
        dashboardError ||
        subscriptionError ||
        new Error(
          'Could not load notifications.',
        )
      )
    }

    notificationUpdatedAt.value =
      Date.now()

    notificationError.value = ''

    if (notificationOpen.value) {
      markNotificationsRead()
    }
  } catch (error) {
    if (
      !silent ||
      !notificationUpdatedAt.value
    ) {
      notificationError.value =
        error?.response?.data?.message ||
        error?.message ||
        'Could not load notifications.'
    }
  } finally {
    notificationLoading.value = false
  }
}

function toggleNotifications() {
  notificationOpen.value =
    !notificationOpen.value

  closeUserDropdown()

  if (notificationOpen.value) {
    markNotificationsRead()
    loadNotifications(true)
  }
}

function closeNotifications() {
  notificationOpen.value = false
}

/* =========================================================
   SIDEBAR METHODS
========================================================= */
function toggleDesktopSidebar() {
  desktopSidebarCollapsed.value =
    !desktopSidebarCollapsed.value

  localStorage.setItem(
    SIDEBAR_STORAGE_KEY,
    String(desktopSidebarCollapsed.value),
  )
}

function openMobileSidebar() {
  mobileSidebarOpen.value = true

  document.body.classList.add(
    'sidebar-mobile-active',
  )
}

function closeMobileSidebar() {
  mobileSidebarOpen.value = false

  document.body.classList.remove(
    'sidebar-mobile-active',
  )
}

/* =========================================================
   USER DROPDOWN
========================================================= */
function toggleUserDropdown() {
  userDropdownOpen.value =
    !userDropdownOpen.value

  closeNotifications()
}

function openUserDropdown() {
  userDropdownOpen.value = true
  closeNotifications()
}

function closeUserDropdown() {
  userDropdownOpen.value = false
}

function handleDocumentClick(event) {
  const userContainer =
    userDropdownContainer.value

  const notificationContainer =
    notificationDropdownContainer.value

  if (
    userContainer &&
    !userContainer.contains(event.target)
  ) {
    closeUserDropdown()
  }

  if (
    notificationContainer &&
    !notificationContainer.contains(event.target)
  ) {
    closeNotifications()
  }
}

function handleEscapeKey(event) {
  if (event.key === 'Escape') {
    closeUserDropdown()
    closeNotifications()
  }
}

/* =========================================================
   LOGOUT
========================================================= */
async function handleLogout() {
  closeUserDropdown()
  closeNotifications()
  closeMobileSidebar()

  try {
    await authStore.logout()
  } finally {
    await router.replace('/login')
  }
}

/* =========================================================
   RESPONSIVE HANDLING
========================================================= */
function handleWindowResize() {
  if (window.innerWidth >= 992) {
    closeMobileSidebar()
  }
}

/* =========================================================
   LIFECYCLE
========================================================= */
onMounted(() => {
  loadNotifications(true)

  notificationPollTimer = window.setInterval(
    () => loadNotifications(true),
    60000,
  )

  window.addEventListener(
    'resize',
    handleWindowResize,
  )

  document.addEventListener(
    'click',
    handleDocumentClick,
  )

  document.addEventListener(
    'keydown',
    handleEscapeKey,
  )
})

onBeforeUnmount(() => {
  if (notificationPollTimer) {
    window.clearInterval(notificationPollTimer)
    notificationPollTimer = null
  }

  window.removeEventListener(
    'resize',
    handleWindowResize,
  )

  document.removeEventListener(
    'click',
    handleDocumentClick,
  )

  document.removeEventListener(
    'keydown',
    handleEscapeKey,
  )

  document.body.classList.remove(
    'sidebar-mobile-active',
  )
})
</script>

<style scoped>
:global(body.sidebar-mobile-active) {
  overflow: hidden;
  overscroll-behavior: none;
}

/* =========================================================
   LAYOUT
========================================================= */
.dashboard-layout {
  --sidebar-width: 170px;
  --sidebar-collapsed-width: 48px;
  --topbar-height: 46px;
  --content-gap: 10px;

  width: 100%;
  min-height: 100vh;
  overflow-x: hidden;
  background: #f1f5f9;
}

.main-wrapper {
  width: calc(100% - var(--sidebar-width));
  min-width: 0;
  min-height: 100vh;
  margin-left: var(--sidebar-width);
  background: #f1f5f9;
  transition:
    margin-left .2s ease,
    width .2s ease;
}

.main-wrapper-collapsed {
  width: calc(
    100% - var(--sidebar-collapsed-width)
  );
  margin-left: var(--sidebar-collapsed-width);
}

.pickup-kiosk-layout .main-wrapper,
.pickup-kiosk-layout .main-wrapper.main-wrapper-collapsed {
  width: 100%;
  margin-left: 0;
}

.pickup-kiosk-layout .dashboard-content {
  min-height: 100vh;
  padding: 0;
}

/* =========================================================
   TOPBAR
========================================================= */
.app-topbar {
  position: sticky;
  z-index: 1000;
  top: 0;

  display: flex;
  width: 100%;
  min-height: var(--topbar-height);
  align-items: center;
  justify-content: space-between;
  gap: 8px;

  padding: 0 10px;
  border-bottom: 1px solid #e5e7eb;
  background: rgba(255, 255, 255, .98);
  box-shadow:
    0 1px 3px rgba(15, 23, 42, .035);
  backdrop-filter: blur(6px);
}

.topbar-left,
.topbar-right {
  display: flex;
  align-items: center;
}

.topbar-left {
  min-width: 0;
  flex: 1;
  gap: 6px;
}

.topbar-right {
  flex-shrink: 0;
  gap: 5px;
}

.topbar-shortcuts {
  display: flex;
  align-items: center;
  gap: 5px;
}

.topbar-icon-button {
  position: relative;
  display: flex;
  width: 28px;
  height: 28px;
  flex-shrink: 0;
  align-items: center;
  justify-content: center;
  padding: 0;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  background: #fff;
  color: #4b5563;
  font-size: 11px;
  cursor: pointer;
  transition:
    border-color .15s ease,
    background .15s ease,
    color .15s ease;
}

.topbar-icon-button:hover,
.topbar-icon-button.show {
  border-color: #bde3e9;
  background: #e6f6f8;
  color: #45aabd;
}

.mobile-restaurant-brand {
  display: flex;
  min-width: 0;
  max-width: 142px;
  flex: 0 1 142px;
  align-items: center;
  gap: 6px;
  overflow: hidden;
  color: #28535e;
  font-size: 10px;
  font-weight: 700;
  text-decoration: none;
}

.mobile-restaurant-logo,
.mobile-restaurant-logo-fallback {
  display: block;
  width: 28px;
  height: 28px;
  flex: 0 0 28px;
  overflow: hidden;
  border-radius: 6px;
  background: #e6f6f8;
}

.mobile-restaurant-logo {
  padding: 2px;
  object-fit: contain;
}

.mobile-restaurant-logo-fallback {
  display: flex;
  align-items: center;
  justify-content: center;
  color: #3c9cad;
}

.mobile-restaurant-logo-fallback i {
  font-size: 16px;
}

.mobile-restaurant-name {
  min-width: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.pos-order-shortcut {
  text-decoration: none;
}

/* =========================================================
   PAGE HEADING
========================================================= */
.page-heading {
  min-width: 0;
}

.page-heading-title {
  margin: 0;
  overflow: hidden;
  color: #1f2937;
  font-size: 11px;
  font-weight: 700;
  line-height: 1.2;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.page-heading-subtitle {
  margin: 1px 0 0;
  overflow: hidden;
  color: #8a94a6;
  font-size: 8.5px;
  line-height: 1.15;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* =========================================================
   NOTIFICATIONS
========================================================= */
.notification-center {
  position: relative;
}

.notification-badge {
  position: absolute;
  top: -5px;
  right: -5px;

  display: inline-flex;
  min-width: 16px;
  height: 16px;
  align-items: center;
  justify-content: center;

  padding: 0 4px;
  border: 2px solid #fff;
  border-radius: 999px;
  background: #ef4444;
  color: #fff;
  font-size: 8px;
  font-weight: 800;
  line-height: 1;
}

.notification-dropdown {
  position: absolute;
  z-index: 1300;
  top: calc(100% + 7px);
  right: 0;

  width: min(360px, calc(100vw - 24px));
  overflow: hidden;

  border: 1px solid #e5e7eb;
  border-radius: 12px;
  background: #fff;
  box-shadow:
    0 18px 50px rgba(15, 23, 42, .16);
}

.notification-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;

  padding: 13px 14px 11px;
  border-bottom: 1px solid #eef2f7;
  background:
    linear-gradient(180deg, #fff, #fbfdff);
}

.notification-eyebrow {
  display: block;
  margin-bottom: 2px;
  color: #94a3b8;
  font-size: 7.5px;
  font-weight: 800;
  letter-spacing: .8px;
}

.notification-header h3 {
  margin: 0;
  color: #1e293b;
  font-size: 13px;
  font-weight: 800;
}

.notification-refresh-button {
  display: inline-flex;
  width: 28px;
  height: 28px;
  align-items: center;
  justify-content: center;

  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: #fff;
  color: #64748b;
  font-size: 10px;
  cursor: pointer;
}

.notification-refresh-button:hover:not(:disabled) {
  border-color: #bde3e9;
  background: #e6f6f8;
  color: #45aabd;
}

.notification-refresh-button:disabled {
  cursor: not-allowed;
  opacity: .65;
}

.notification-list {
  max-height: 330px;
  overflow-y: auto;
  padding: 6px;
}

.notification-item {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  padding: 10px;
  border-radius: 9px;
  transition: background .15s ease;
}

.notification-item:hover {
  background: #f8fafc;
}

.notification-item + .notification-item {
  margin-top: 2px;
}

.notification-item-icon {
  display: inline-flex;
  width: 32px;
  height: 32px;
  flex-shrink: 0;
  align-items: center;
  justify-content: center;
  border-radius: 9px;
  font-size: 13px;
}

.notification-kitchen .notification-item-icon {
  background: #edf8fb;
  color: #45aabd;
}

.notification-reservation .notification-item-icon {
  background: #edf8fb;
  color: #58b9ca;
}

.notification-payment .notification-item-icon {
  background: #fef2f2;
  color: #dc2626;
}

.notification-order .notification-item-icon {
  background: #e6f6f8;
  color: #45aabd;
}

.notification-subscription .notification-item-icon {
  background: #ecfeff;
  color: #0891b2;
}

.notification-subscription {
  border: 1px solid #cffafe;
  background:
    linear-gradient(
      135deg,
      rgba(236, 254, 255, .72),
      rgba(255, 255, 255, .96)
    );
}

.notification-subscription:hover {
  background: #ecfeff;
}

.notification-item-content {
  min-width: 0;
  flex: 1;
}

.notification-item-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
}

.notification-item-top strong {
  color: #334155;
  font-size: 10.5px;
  font-weight: 750;
}

.notification-item-top span {
  flex-shrink: 0;
  padding: 2px 6px;
  border-radius: 999px;
  background: #f1f5f9;
  color: #475569;
  font-size: 8.5px;
  font-weight: 800;
}

.notification-item p {
  margin: 3px 0 0;
  color: #7c8798;
  font-size: 9px;
  line-height: 1.45;
}

.notification-state,
.notification-empty {
  display: flex;
  min-height: 150px;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 20px;
  color: #64748b;
  font-size: 9.5px;
}

.notification-error {
  align-items: flex-start;
  justify-content: flex-start;
  color: #b91c1c;
}

.notification-error > i {
  margin-top: 1px;
  font-size: 14px;
}

.notification-error div {
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.notification-error strong {
  font-size: 10px;
}

.notification-error span {
  color: #7f1d1d;
  font-size: 9px;
  line-height: 1.4;
}

.notification-empty {
  flex-direction: column;
  gap: 4px;
  text-align: center;
}

.notification-empty > span {
  display: inline-flex;
  width: 36px;
  height: 36px;
  align-items: center;
  justify-content: center;
  margin-bottom: 3px;
  border-radius: 50%;
  background: #ecfdf5;
  color: #059669;
  font-size: 17px;
}

.notification-empty strong {
  color: #334155;
  font-size: 10.5px;
}

.notification-empty p {
  margin: 0;
  color: #94a3b8;
  font-size: 9px;
}

.notification-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  padding: 8px 11px;
  border-top: 1px solid #eef2f7;
  background: #fafcff;
}

.notification-footer span {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  color: #94a3b8;
  font-size: 8px;
}

.notification-footer button {
  padding: 3px 7px;
  border: 0;
  border-radius: 5px;
  background: transparent;
  color: #45aabd;
  font-size: 8.5px;
  font-weight: 700;
  cursor: pointer;
}

/* =========================================================
   USER DROPDOWN
========================================================= */
.user-dropdown-container {
  position: relative;
}

.user-dropdown-button {
  display: flex;
  min-height: 30px;
  align-items: center;
  gap: 5px;

  padding: 2px 6px 2px 3px;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  background: #fff;
  color: #374151;
  text-align: left;

  transition:
    border-color .15s ease,
    background .15s ease,
    box-shadow .15s ease;
}

.user-dropdown-button:hover,
.user-dropdown-button.show {
  border-color: #bde3e9;
  background: #f8fbff;
  box-shadow:
    0 1px 4px rgba(37, 99, 235, .05);
}

.topbar-avatar,
.dropdown-avatar {
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background:
    linear-gradient(
      135deg,
      #2563eb,
      #4f46e5
    );
  color: #fff;
  font-weight: 700;
}

.topbar-avatar {
  width: 24px;
  height: 24px;
  flex-shrink: 0;
  font-size: 8px;
  background: linear-gradient(135deg, #7bcbd7, #58b9ca);
}

.user-dropdown-details {
  min-width: 0;
  max-width: 105px;
}

.topbar-user-name {
  overflow: hidden;
  color: #374151;
  font-size: 9px;
  font-weight: 600;
  line-height: 1.15;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.topbar-user-role {
  margin-top: 1px;
  overflow: hidden;
  color: #8a94a6;
  font-size: 8px;
  line-height: 1.1;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.user-dropdown-menu {
  position: absolute;
  z-index: 1200;
  top: 100%;
  right: 0;
  left: auto;

  display: block;
  width: 210px;
  margin-top: 5px !important;
  padding: 4px;

  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background: #fff;
  box-shadow:
    0 7px 20px rgba(15, 23, 42, .12);
}

.dropdown-user-summary {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px;
}

.dropdown-avatar {
  width: 30px;
  height: 30px;
  flex-shrink: 0;
  font-size: 8px;
}

.dropdown-user-summary .fw-semibold {
  font-size: 9.5px;
}

.dropdown-user-summary .small {
  font-size: 8.5px !important;
}

.dropdown-divider {
  margin: 4px 0;
}

.dropdown-item {
  padding: 5px 6px;
  border-radius: 4px;
  font-size: 9px;
}

/* =========================================================
   MAIN CONTENT
========================================================= */
.dashboard-content {
  width: 100%;
  max-width: none;
  min-height:
    calc(100vh - var(--topbar-height));
  margin: 0;
  padding: var(--content-gap);
}

.dashboard-content > * {
  width: 100%;
  max-width: none;
  margin: 0;
}

.dashboard-content :deep(.container),
.dashboard-content :deep(.container-fluid) {
  width: 100%;
  max-width: none;
  margin-left: 0;
  margin-right: 0;
}

.dashboard-content :deep(.container) {
  padding-left: 0;
  padding-right: 0;
}

.dashboard-content :deep(.dashboard-page),
.dashboard-content :deep(.admin-overview-page),
.dashboard-content :deep(.kitchen-overview-page),
.dashboard-content :deep(.operations-overview-page),
.dashboard-content :deep(.menu-test-page),
.dashboard-content :deep(.page-wrapper),
.dashboard-content :deep(.page-content-inner) {
  width: 100%;
  max-width: none;
  margin: 0;
  padding: 0;
}

.dashboard-content :deep(section) {
  max-width: none;
  margin-left: 0;
  margin-right: 0;
}

.dashboard-content :deep(.page-header) {
  margin-bottom: 8px !important;
}

.dashboard-content :deep(section > .mb-4:first-child) {
  margin-bottom: 8px !important;
}

.dashboard-content :deep(.page-title) {
  margin-bottom: 1px;
  font-size: 15px;
  line-height: 1.2;
}

.dashboard-content :deep(.page-subtitle) {
  font-size: 9.5px;
  line-height: 1.3;
}

.dashboard-content :deep(.row) {
  --bs-gutter-x: 8px;
  --bs-gutter-y: 8px;
}

.dashboard-content :deep(.card) {
  border-radius: 5px;
}

.dashboard-content :deep(.card-body) {
  padding: 9px;
}

.dashboard-content :deep(.table-responsive) {
  width: 100%;
  max-width: 100%;
  margin: 0;
}

/* =========================================================
   RESPONSIVE
========================================================= */
@media (min-width: 992px) {
  .main-wrapper {
    width: calc(
      100% - var(--sidebar-width)
    );
    margin-left: var(--sidebar-width);
  }

  .main-wrapper.main-wrapper-collapsed {
    width: calc(
      100% - var(--sidebar-collapsed-width)
    );
    margin-left:
      var(--sidebar-collapsed-width);
  }
}

@media (max-width: 991.98px) {
  .main-wrapper,
  .main-wrapper-collapsed {
    width: 100%;
    margin-left: 0;
  }

  .app-topbar {
    min-height: 46px;
    padding: 0 7px;
  }

  .dashboard-content {
    min-height: calc(100vh - 46px);
    padding: 7px;
  }
}

@media (max-width: 575.98px) {
  .app-topbar {
    flex-direction: row;
    gap: 4px;
    min-height: 44px;
    padding: 0 5px;
  }

  .topbar-left {
    display: flex;
    min-width: 0;
    flex: 1 1 auto;
    gap: 4px;
  }

  .mobile-restaurant-brand {
    max-width: 29vw;
    flex: 0 1 29vw;
    gap: 3px;
  }

  .mobile-restaurant-logo,
  .mobile-restaurant-logo-fallback {
    width: 21px;
    height: 21px;
    flex-basis: 21px;
  }

  .page-heading {
    min-width: 0;
    flex: 0 1 auto;
  }

  .page-heading-title {
    max-width: 64px;
    overflow: hidden;
    font-size: 11px;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .topbar-right {
    min-width: 0;
    max-width: 48%;
    flex: 0 1 auto;
    margin-left: auto;
    gap: 4px;
    overflow: visible;
  }

  .topbar-shortcuts {
    min-width: 0;
    flex: 1 1 auto;
    gap: 2px;
    overflow-x: auto;
    overflow-y: hidden;
    scrollbar-width: none;
    overscroll-behavior-x: contain;
    -webkit-overflow-scrolling: touch;
  }

  .topbar-shortcuts::-webkit-scrollbar {
    display: none;
  }

  .topbar-shortcuts .topbar-icon-button {
    width: 22px;
    flex: 0 0 22px;
    height: 24px;
    font-size: 10px;
  }

  .mobile-restaurant-name {
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    line-height: 1.15;
  }

  .topbar-right .topbar-icon-button {
    width: 22px;
    height: 24px;
  }

  .dashboard-content {
    min-height: calc(100vh - 44px);
    padding: 6px;
  }

  .page-heading-subtitle {
    display: none;
  }

  .notification-dropdown {
    position: fixed;
    top: 50px;
    right: 6px;
    left: 6px;
    width: auto;
    max-width: none;
  }

  .user-dropdown-button {
    min-height: 28px;
    padding: 2px;
  }

  .user-dropdown-button::after {
    display: none;
  }

  .topbar-avatar {
    width: 24px;
    height: 24px;
  }

  .user-dropdown-menu {
    width: 200px;
  }
}

/* Dashboard-only light aqua shell to match the reference */
.dashboard-layout.dashboard-route {
  background: linear-gradient(145deg, #f7f9fb 0%, #eef2f6 55%, #f5f7fa 100%);
}

.dashboard-layout.dashboard-route .main-wrapper {
  background: transparent;
}

.dashboard-layout.dashboard-route .app-topbar {
  border-bottom-color: #e0f0f3;
  background: rgba(249, 254, 255, .96);
  box-shadow: 0 1px 8px rgba(56, 132, 148, .04);
}

.dashboard-layout.dashboard-route .topbar-icon-button {
  border-color: #e1f0f3;
  background: rgba(255, 255, 255, .88);
  color: #5c8490;
}

.dashboard-layout.dashboard-route .topbar-icon-button:hover,
.dashboard-layout.dashboard-route .topbar-icon-button.show {
  border-color: #bde3e9;
  background: #e6f6f8;
  color: #3c9cad;
}

.dashboard-layout.dashboard-route .page-heading-title {
  color: #28535e;
}

.dashboard-layout.dashboard-route .page-heading-subtitle {
  color: #7899a1;
}

.dashboard-layout.dashboard-route .user-dropdown-button {
  border-color: #e1f0f3;
  background: rgba(255, 255, 255, .88);
}

.dashboard-layout.dashboard-route .user-dropdown-button:hover,
.dashboard-layout.dashboard-route .user-dropdown-button.show {
  border-color: #bde3e9;
  background: #f1fafb;
}

.dashboard-layout.dashboard-route :deep(.app-sidebar) {
  border-right: 1px solid #cde9ee;
  background: linear-gradient(180deg, #d8f0f4 0%, #e5f6f8 54%, #effafb 100%);
  color: #426d78;
  box-shadow: 2px 0 12px rgba(44, 119, 135, .06);
}

.dashboard-layout.dashboard-route :deep(.sidebar-header) {
  border-bottom-color: rgba(83, 157, 171, .14);
}

.dashboard-layout.dashboard-route :deep(.sidebar-brand),
.dashboard-layout.dashboard-route :deep(.brand-title) {
  color: #28535e;
}

.dashboard-layout.dashboard-route :deep(.brand-subtitle) {
  color: #76959d;
}

.dashboard-layout.dashboard-route :deep(.section-heading) {
  color: #71959e;
}

.dashboard-layout.dashboard-route :deep(.section-heading:hover) {
  color: #3d7d8a;
}

.dashboard-layout.dashboard-route :deep(.sidebar-menu-link) {
  color: #527783;
}

.dashboard-layout.dashboard-route :deep(.sidebar-menu-link:hover) {
  background: rgba(255, 255, 255, .55);
  color: #2b6572;
}

.dashboard-layout.dashboard-route :deep(.sidebar-menu-link.router-link-active),
.dashboard-layout.dashboard-route :deep(.sidebar-menu-link.router-link-exact-active) {
  border-color: rgba(104, 190, 204, .24);
  background: linear-gradient(100deg, #c8ebf0, #d7f1f4);
  color: #286b79;
  box-shadow: inset 2px 0 0 #5ebdcb;
}

.dashboard-layout.dashboard-route :deep(.sidebar-menu-link.router-link-active .menu-icon),
.dashboard-layout.dashboard-route :deep(.sidebar-menu-link.router-link-exact-active .menu-icon),
.dashboard-layout.dashboard-route :deep(.sidebar-menu-link.router-link-active .menu-label),
.dashboard-layout.dashboard-route :deep(.sidebar-menu-link.router-link-exact-active .menu-label) {
  color: #286b79;
}

.dashboard-layout.dashboard-route :deep(.sidebar-menu-link.router-link-active .menu-label),
.dashboard-layout.dashboard-route :deep(.sidebar-menu-link.router-link-exact-active .menu-label) {
  font-weight: 700;
}

.dashboard-layout.dashboard-route :deep(.sidebar-menu-link.router-link-active:hover),
.dashboard-layout.dashboard-route :deep(.sidebar-menu-link.router-link-exact-active:hover) {
  background: #bee6ec;
}

.dashboard-layout.dashboard-route :deep(.sidebar-user) {
  border-top-color: rgba(83, 157, 171, .16);
  background: rgba(255, 255, 255, .35);
}

.dashboard-layout.dashboard-route :deep(.user-name) {
  color: #315e69;
}

.dashboard-layout.dashboard-route :deep(.user-role),
.dashboard-layout.dashboard-route :deep(.user-options-button) {
  color: #7899a1;
}

.dashboard-layout.dashboard-route :deep(.user-options-button:hover) {
  background: rgba(255, 255, 255, .6);
  color: #3d7d8a;
}

.dashboard-layout.dashboard-route :deep(.sidebar-scroll) {
  scrollbar-color: rgba(84, 164, 179, .3) transparent;
}

.dashboard-layout.dashboard-route :deep(.sidebar-scroll::-webkit-scrollbar-thumb) {
  background: rgba(84, 164, 179, .28);
}

.dashboard-layout.dashboard-route :deep(.desktop-collapse-button) {
  border-color: #d8f0f4;
  background: linear-gradient(135deg, #7bcbd7, #58b9ca);
  color: #fff;
}

@media (max-width: 560px) {
  .dashboard-layout.dashboard-route :deep(.app-sidebar) {
    background: linear-gradient(180deg, #d8f0f4 0%, #effafb 100%);
  }
}
</style>
