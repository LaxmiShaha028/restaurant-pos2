<template>
  <section class="order-management-page">
    <div class="order-page-header">
  <div class="order-page-header-left">
    <div class="order-page-header-icon">
      <i class="bi bi-receipt"></i>
    </div>

    <div>
      <p class="order-page-eyebrow">
        Restaurant Operations
      </p>

      <h1 class="order-page-title">
        Order Management
      </h1>

      <p class="order-page-subtitle">
        Manage restaurant orders, kitchen progress,
        payments and table activity.
      </p>
    </div>
  </div>

  <div class="order-page-header-actions">
    <button
      type="button"
      class="btn add-order-button"
      @click="router.push({ name: 'pickup-display' })"
    >
      <i class="bi bi-megaphone"></i>
      Pickup Display
    </button>
    <button
      v-if="authStore.hasPermission?.('orders.update') || authStore.hasPermission?.('orders.create')"
      type="button"
      class="btn call-order-button"
      @click="openCallOrderDialog"
    >
      <i class="bi bi-volume-up-fill"></i>
      Call Order
    </button>
    <button
      type="button"
      class="btn add-order-button"
      @click="goToCreateOrder"
    >
      <i class="bi bi-plus-lg"></i>
      Add Order
    </button>
  </div>
</div>

    <div v-if="showCallOrderDialog" class="call-order-overlay" @click.self="closeCallOrderDialog">
      <section class="call-order-dialog" role="dialog" aria-modal="true" aria-labelledby="call-order-title">
        <button type="button" class="call-order-close" aria-label="Close" @click="closeCallOrderDialog">&times;</button>
        <span class="call-order-icon"><i class="bi bi-megaphone-fill"></i></span>
        <h2 id="call-order-title">Call a ready order</h2>
        <p>Select an order to repeat its pickup chime and spoken token on the Pickup Display.</p>
        <div v-if="callOrdersLoading" class="call-order-message">Loading ready orders…</div>
        <div v-else-if="callOrdersError" class="call-order-message call-order-error">{{ callOrdersError }}</div>
        <div v-else-if="!callReadyOrders.length" class="call-order-message">There are no orders ready for pickup.</div>
        <template v-else>
          <label class="call-order-label" for="call-order-select">Ready order</label>
          <select id="call-order-select" v-model="selectedCallOrderId" class="call-order-select">
            <option v-for="order in callReadyOrders" :key="order.id" :value="String(order.id)">
              {{ order.token }}{{ order.customer_name ? ` · ${order.customer_name}` : '' }}
            </option>
          </select>
        </template>
        <div class="call-order-actions">
          <button type="button" class="btn call-order-cancel" @click="closeCallOrderDialog">Cancel</button>
          <button type="button" class="btn call-order-submit" :disabled="!selectedCallOrderId || callOrdersLoading || callAnnouncementSending || Boolean(callOrdersError)" @click="repeatOrderCall">
            <i :class="callAnnouncementSending ? 'bi bi-arrow-repeat' : 'bi bi-volume-up-fill'"></i>
            {{ callAnnouncementSending ? 'Sending…' : 'Announce again' }}
          </button>
        </div>
      </section>
    </div>

    <!-- Success Alert -->
    <div
      v-if="successMessage"
      class="alert alert-success order-alert"
      role="alert"
    >
      <div class="d-flex align-items-center gap-2">
        <i class="bi bi-check-circle-fill"></i>

        <span>
          {{ successMessage }}
        </span>
      </div>

      <button
        type="button"
        class="btn-close"
        aria-label="Close"
        @click="successMessage = ''"
      ></button>
    </div>

    <!-- Error Alert -->
    <div
      v-if="errorMessage"
      class="alert alert-danger order-alert"
      role="alert"
    >
      <div class="d-flex align-items-center gap-2">
        <i class="bi bi-exclamation-triangle-fill"></i>

        <span>
          {{ errorMessage }}
        </span>
      </div>

      <button
        type="button"
        class="btn-close"
        aria-label="Close"
        @click="errorMessage = ''"
      ></button>
    </div>

    <!-- Summary Cards -->
    <OrderSummaryCards
      :summary="summary"
      :loading="loading"
      :active-status="filters.status"
      @filter-status="handleSummaryFilter"
    />

    <!-- Toolbar -->
    <OrderToolbar
      v-model:search="filters.search"
      v-model:status="filters.status"
      v-model:payment-status="filters.payment_status"
      v-model:payment-method="filters.payment_method"
      v-model:date-from="filters.date_from"
      v-model:date-to="filters.date_to"
      :status-options="filterOptions.statuses"
      :payment-status-options="filterOptions.payment_statuses"
      :payment-method-options="filterOptions.payment_methods"
      :loading="loading"
      @search="handleSearch"
      @refresh="refreshOrders"
      @clear="clearFilters"
      @print="printOrders"
      @export-excel="exportOrdersCsv"
      @export-pdf="exportOrdersPdf"
    />

    <!-- Order List -->
    <OrderList
      :orders="orders"
      :loading="loading"
      :updating-order-id="updatingOrderId"
      :meta="meta"
      :can-update-orders="
        authStore.hasPermission?.('orders.update') ?? false
      "
      :can-create-orders="
        authStore.hasPermission?.('orders.create') ?? false
      "
      :can-view-kitchen="canViewKitchen()"
      @change-status="handleStatusChange"
      @mark-collected="handlePickupCollected"
      @edit="handleEditOrder"
      @extend="handleExtendOrder"
      @chef="handleChefAction"
      @cancel="openCancelModal"
      @complete="handleCompleteOrder"
      @view="openKitchenOrderDetails"
      @change-page="changePage"
      @change-per-page="changePerPage"
      @retry="fetchOrders"
    />

    <!-- Cancel Modal -->
    <CancelOrderModal
      :show="showCancelModal"
      :order="selectedOrder"
      :submitting="cancelSubmitting"
      @close="closeCancelModal"
      @confirm="confirmCancelOrder"
    />

    <CompleteOrderModal
      :show="showCompleteModal"
      :order="completionOrder"
      :submitting="
        Boolean(completionOrder?.id) &&
        String(updatingOrderId) ===
          String(completionOrder.id)
      "
      @close="closeCompleteModal"
      @confirm="confirmCompleteOrder"
    />
  </section>
</template>

<script setup>
/*
|--------------------------------------------------------------------------
| Vue Imports
|--------------------------------------------------------------------------
*/

import {
  onBeforeUnmount,
  onMounted,
  reactive,
  ref,
  watch,
} from "vue";

/*
|--------------------------------------------------------------------------
| Vue Router
|--------------------------------------------------------------------------
*/

import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";

/*
|--------------------------------------------------------------------------
| Order Components
|--------------------------------------------------------------------------
*/

import OrderSummaryCards from "@/components/orders/OrderSummaryCards.vue";
import OrderToolbar from "@/components/orders/OrderToolbar.vue";
import OrderList from "@/components/orders/OrderList.vue";
import CancelOrderModal from "@/components/orders/CancelOrderModal.vue";
import CompleteOrderModal from "@/components/orders/CompleteOrderModal.vue";

/*
|--------------------------------------------------------------------------
| Order API Service
|--------------------------------------------------------------------------
*/

import orderService from "@/services/orderService";
import { jsPDF } from "jspdf";
import autoTable from "jspdf-autotable";

/*
|--------------------------------------------------------------------------
| Order Management Styles
|--------------------------------------------------------------------------
*/

import "@/assets/css/order-management.css";

/*
|--------------------------------------------------------------------------
| Router Instance
|--------------------------------------------------------------------------
*/

const router = useRouter();
const authStore = useAuthStore();

const canViewKitchen = () => {
  if (
    typeof authStore.hasPermission !==
    "function"
  ) {
    return false;
  }

  return authStore.hasPermission(
    "kitchen.view"
  );
};

/*
|--------------------------------------------------------------------------
| Order List State
|--------------------------------------------------------------------------
*/

const orders = ref([]);
const loading = ref(false);
const updatingOrderId = ref(null);
const showCallOrderDialog = ref(false);
const callReadyOrders = ref([]);
const selectedCallOrderId = ref("");
const callOrdersLoading = ref(false);
const callOrdersError = ref("");
const callAnnouncementSending = ref(false);

async function openCallOrderDialog() {
  showCallOrderDialog.value = true;
  callOrdersLoading.value = true;
  callOrdersError.value = "";
  selectedCallOrderId.value = "";

  try {
    const response = await orderService.getPickupBoard();
    callReadyOrders.value = (Array.isArray(response?.data) ? response.data : [])
      .filter((order) => order.status === "ready");
    selectedCallOrderId.value = callReadyOrders.value[0]
      ? String(callReadyOrders.value[0].id)
      : "";
  } catch (error) {
    callReadyOrders.value = [];
    callOrdersError.value = orderService.getOrderErrorMessage(
      error,
      "Could not load ready orders. Please try again.",
    );
  } finally {
    callOrdersLoading.value = false;
  }
}

function closeCallOrderDialog() {
  showCallOrderDialog.value = false;
}

async function repeatOrderCall() {
  const order = callReadyOrders.value.find(
    (item) => String(item.id) === selectedCallOrderId.value,
  );
  if (!order) return;

  callAnnouncementSending.value = true;
  callOrdersError.value = "";
  try {
    await orderService.announcePickupOrder(order.id);
    successMessage.value = `Order ${order.token} announcement sent to the Pickup Display.`;
    closeCallOrderDialog();
  } catch (error) {
    callOrdersError.value = orderService.getOrderErrorMessage(
      error,
      "Could not send the announcement. Check your connection and try again.",
    );
  } finally {
    callAnnouncementSending.value = false;
  }
}

/*
|--------------------------------------------------------------------------
| Alert Messages
|--------------------------------------------------------------------------
*/

const successMessage = ref("");
const errorMessage = ref("");

/*
|--------------------------------------------------------------------------
| Cancel Order Modal State
|--------------------------------------------------------------------------
*/

const showCancelModal = ref(false);
const selectedOrder = ref(null);
const cancelSubmitting = ref(false);

const showCompleteModal = ref(false);
const completionOrder = ref(null);

/*
|--------------------------------------------------------------------------
| Timer References
|--------------------------------------------------------------------------
*/

let searchTimer = null;
let messageTimer = null;
let autoRefreshTimer = null;
let orderRequestInFlight = false;

const AUTO_REFRESH_INTERVAL_MS =
  5000;

/*
|--------------------------------------------------------------------------
| Order Filters
|--------------------------------------------------------------------------
*/

const filters = reactive({
  search: "",
  status: "",
  payment_status: "",
  payment_method: "",
  date_from: "",
  date_to: "",
  page: 1,
  per_page: 10,
  sort_direction: "desc",
});

/*
|--------------------------------------------------------------------------
| Order Summary
|--------------------------------------------------------------------------
*/

const summary = reactive({
  total_orders: 0,
  pending_orders: 0,
  kitchen_active: 0,
  served_orders: 0,
  completed_orders: 0,
  canceled_orders: 0,

  total_sales: 0,
  paid_amount: 0,
  due_amount: 0,

  total_sales_formatted: "৳ 0.00",
  paid_amount_formatted: "৳ 0.00",
  due_amount_formatted: "৳ 0.00",
});

/*
|--------------------------------------------------------------------------
| Pagination Metadata
|--------------------------------------------------------------------------
*/

const meta = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
  from: 0,
  to: 0,
});

/*
|--------------------------------------------------------------------------
| Default Filter Options
|--------------------------------------------------------------------------
*/

const filterOptions = reactive({
  statuses: [
    {
      value: "pending",
      label: "Pending",
    },
    {
      value: "preparing",
      label: "Preparing",
    },
    {
      value: "ready",
      label: "Ready",
    },
    {
      value: "served",
      label: "Served",
    },
    {
      value: "completed",
      label: "Completed",
    },
    {
      value: "canceled",
      label: "Canceled",
    },
  ],

  payment_statuses: [
    {
      value: "due",
      label: "Due",
    },
    {
      value: "partially_paid",
      label: "Partially Paid",
    },
    {
      value: "paid",
      label: "Paid",
    },
  ],

  payment_methods: [
    {
      value: "cash",
      label: "Cash",
    },
    {
      value: "card",
      label: "Card",
    },
    {
      value: "bkash",
      label: "bKash",
    },
    {
      value: "nagad",
      label: "Nagad",
    },
    {
      value: "bank_transfer",
      label: "Bank Transfer",
    },
    {
      value: "mixed",
      label: "Mixed Payment",
    },
  ],
});

/*
|--------------------------------------------------------------------------
| Build Order List Request Parameters
|--------------------------------------------------------------------------
|
| Empty filter values API request-এ পাঠানো হবে না।
|
*/

const buildRequestParams = () => {
  const params = {
    page: filters.page,
    per_page: filters.per_page,
    sort_direction: filters.sort_direction,
  };

  const search = filters.search.trim();

  if (search) {
    params.search = search;
  }

  if (filters.status) {
    params.status = filters.status;
  }

  if (filters.payment_status) {
    params.payment_status =
      filters.payment_status;
  }

  if (filters.payment_method) {
    params.payment_method =
      filters.payment_method;
  }

  if (filters.date_from) {
    params.date_from = filters.date_from;
  }

  if (filters.date_to) {
    params.date_to = filters.date_to;
  }

  return params;
};

/*
|--------------------------------------------------------------------------
| Fetch Order List
|--------------------------------------------------------------------------
|
| Order list, summary, pagination এবং filter options API থেকে load করে।
|
*/

const fetchOrders = async ({
  silent = false,
} = {}) => {
  /*
  |--------------------------------------------------------------------------
  | Prevent overlapping manual/background requests
  |--------------------------------------------------------------------------
  */

  if (orderRequestInFlight) {
    return;
  }

  orderRequestInFlight = true;

  if (!silent) {
    loading.value = true;
    errorMessage.value = "";
  }

  try {
    const response =
      await orderService.getOrders(
        buildRequestParams()
      );

    orders.value = Array.isArray(
      response?.data
    )
      ? response.data
      : [];

    updateSummary(
      response?.summary ?? {}
    );

    updateMeta(
      response?.meta ?? {}
    );

    updateFilterOptions(
      response?.filters ?? {}
    );
  } catch (error) {
    /*
    |--------------------------------------------------------------------------
    | Background refresh must not destroy the current screen
    |--------------------------------------------------------------------------
    */

    if (silent) {
      console.error(
        "Order background refresh failed:",
        error
      );

      return;
    }

    orders.value = [];

    errorMessage.value =
      getErrorMessage(
        error,
        "Unable to load orders."
      );
  } finally {
    orderRequestInFlight = false;

    if (!silent) {
      loading.value = false;
    }
  }
};

/*
|--------------------------------------------------------------------------
| Update Order Summary
|--------------------------------------------------------------------------
|
| Backend summary response reactive summary state-এ assign করে।
|
*/

const updateSummary = (data) => {
  Object.assign(summary, {
    total_orders:
      Number(data.total_orders) || 0,

    pending_orders:
      Number(data.pending_orders) || 0,

    kitchen_active:
      Number(data.kitchen_active) || 0,

    served_orders:
      Number(data.served_orders) || 0,

    completed_orders:
      Number(data.completed_orders) || 0,

    canceled_orders:
      Number(data.canceled_orders) || 0,

    total_sales:
      Number(data.total_sales) || 0,

    paid_amount:
      Number(data.paid_amount) || 0,

    due_amount:
      Number(data.due_amount) || 0,

    total_sales_formatted:
      data.total_sales_formatted ??
      "৳ 0.00",

    paid_amount_formatted:
      data.paid_amount_formatted ??
      "৳ 0.00",

    due_amount_formatted:
      data.due_amount_formatted ??
      "৳ 0.00",
  });
};

/*
|--------------------------------------------------------------------------
| Update Pagination Metadata
|--------------------------------------------------------------------------
*/

const updateMeta = (data) => {
  Object.assign(meta, {
    current_page:
      Number(data.current_page) || 1,

    last_page:
      Number(data.last_page) || 1,

    per_page:
      Number(data.per_page) ||
      filters.per_page,

    total:
      Number(data.total) || 0,

    from:
      Number(data.from) || 0,

    to:
      Number(data.to) || 0,
  });

  filters.page =
    meta.current_page;
};

/*
|--------------------------------------------------------------------------
| Update Filter Options
|--------------------------------------------------------------------------
|
| Backend filter options থাকলে default options replace করে।
|
*/

const updateFilterOptions = (data) => {
  if (
    Array.isArray(data.statuses) &&
    data.statuses.length
  ) {
    filterOptions.statuses =
      data.statuses;
  }

  if (
    Array.isArray(
      data.payment_statuses
    ) &&
    data.payment_statuses.length
  ) {
    filterOptions.payment_statuses =
      data.payment_statuses;
  }

  if (
    Array.isArray(
      data.payment_methods
    ) &&
    data.payment_methods.length
  ) {
    filterOptions.payment_methods =
      data.payment_methods;
  }
};

/*
|--------------------------------------------------------------------------
| Search Orders
|--------------------------------------------------------------------------
|
| Search button click করলে প্রথম page থেকে result load করে।
|
*/

const handleSearch = () => {
  filters.page = 1;

  fetchOrders();
};

/*
|--------------------------------------------------------------------------
| Refresh Orders
|--------------------------------------------------------------------------
|
| Current filters ও current page রেখে order list আবার load করে।
|
*/

const refreshOrders = () => {
  fetchOrders();
};

/*
|--------------------------------------------------------------------------
| Clear All Filters
|--------------------------------------------------------------------------
*/

const clearFilters = () => {
  filters.search = "";
  filters.status = "";
  filters.payment_status = "";
  filters.payment_method = "";
  filters.date_from = "";
  filters.date_to = "";
  filters.page = 1;

  fetchOrders();
};

/*
|--------------------------------------------------------------------------
| Filter from Summary Card
|--------------------------------------------------------------------------
|
| একই summary status আবার click করলে filter clear হবে।
|
*/

const handleSummaryFilter = (status) => {
  filters.status =
    filters.status === status
      ? ""
      : status;

  filters.page = 1;

  fetchOrders();
};

/*
|--------------------------------------------------------------------------
| Change Pagination Page
|--------------------------------------------------------------------------
*/

const changePage = (page) => {
  const nextPage = Number(page);

  if (
    !Number.isInteger(nextPage) ||
    nextPage < 1 ||
    nextPage > meta.last_page ||
    nextPage === filters.page
  ) {
    return;
  }

  filters.page = nextPage;

  fetchOrders();
};

/*
|--------------------------------------------------------------------------
| Change Rows per Page
|--------------------------------------------------------------------------
*/

const changePerPage = (perPage) => {
  const resolvedPerPage =
    Number(perPage);

  if (
    !Number.isInteger(
      resolvedPerPage
    ) ||
    resolvedPerPage <= 0
  ) {
    return;
  }

  filters.per_page =
    resolvedPerPage;

  filters.page = 1;

  fetchOrders();
};

/*
|--------------------------------------------------------------------------
| Update Normal Order Status
|--------------------------------------------------------------------------
|
| Completed এবং canceled status OrderList component থেকে dedicated
| complete/cancel event হিসেবে আসে। এই function normal status update করে।
|
*/

const handleStatusChange = async ({
  order,
  status,
}) => {
  if (
    !order?.id ||
    !status ||
    status === order.status
  ) {
    return;
  }

  const previousStatus =
    order.status;

  updatingOrderId.value =
    order.id;

  errorMessage.value = "";

  try {
    const response =
      await orderService.updateStatus(
        order.id,
        status
      );

    showSuccess(
      response?.message ??
        "Order status updated successfully."
    );

    await fetchOrders();
  } catch (error) {
    order.status =
      previousStatus;

    errorMessage.value =
      getErrorMessage(
        error,
        "Unable to update order status."
      );
  } finally {
    updatingOrderId.value = null;
  }
};

const handlePickupCollected = async (order) => {
  if (!order?.id || updatingOrderId.value) return;
  updatingOrderId.value = order.id;
  errorMessage.value = "";
  try {
    const response = await orderService.markPickupCollected(order.id);
    showSuccess(response?.message ?? "Takeaway order marked as collected.");
    await fetchOrders();
  } catch (error) {
    errorMessage.value = getErrorMessage(error, "Unable to mark this order as collected.");
  } finally {
    updatingOrderId.value = null;
  }
};

/*
|--------------------------------------------------------------------------
| Open Kitchen Details from View Button
|--------------------------------------------------------------------------
|
| Order number অথবা eye icon click করলে ওই order-এর Kitchen Details page
| open হবে।
|
*/

const openKitchenOrderDetails = async (
  order
) => {
  if (!order?.id) {
    errorMessage.value =
      "Unable to open this order.";

    return;
  }

  /*
  |--------------------------------------------------------------------------
  | Order Number / View Action
  |--------------------------------------------------------------------------
  |
  | Order Management is protected by orders.view. Therefore the normal view
  | action must open Order Details, not the kitchen-only route.
  |
  */

  try {
    await router.push({
      name:
        "order-details",

      params: {
        id: String(order.id),
      },
    });
  } catch (error) {
    errorMessage.value =
      "Unable to open the order details page.";

    console.error(
      "Order details navigation failed:",
      error
    );
  }
};

/*
|--------------------------------------------------------------------------
| Open Kitchen Details from Fire Button
|--------------------------------------------------------------------------
|
| Fire/Chef icon click করলেও একই Kitchen Order Details page open হবে।
|
*/

const handleChefAction = async (
  order
) => {
  if (!order?.id) {
    errorMessage.value =
      "Unable to open this order.";

    return;
  }

  /*
  |--------------------------------------------------------------------------
  | Permission-aware Kitchen Action
  |--------------------------------------------------------------------------
  |
  | Chef / kitchen.view users:
  |   -> Kitchen Order Details
  |
  | Waiter / Manager / other orders.view users:
  |   -> Normal Order Details (read-only operational tracking)
  |
  | This removes the broken Access Denied navigation without granting
  | kitchen-control permission to users who do not have it.
  |
  */

  const targetRoute =
    canViewKitchen()
      ? "kitchen-order-details"
      : "order-details";

  try {
    await router.push({
      name:
        targetRoute,

      params: {
        id: String(order.id),
      },
    });
  } catch (error) {
    errorMessage.value =
      "Unable to open the order status page.";

    console.error(
      "Order status navigation failed:",
      error
    );
  }
};

/*
|--------------------------------------------------------------------------
| Open Cancel Order Modal
|--------------------------------------------------------------------------
*/

const openCancelModal = (order) => {
  if (!order?.id) {
    return;
  }

  selectedOrder.value = order;
  showCancelModal.value = true;
};

/*
|--------------------------------------------------------------------------
| Close Cancel Order Modal
|--------------------------------------------------------------------------
*/

const closeCancelModal = () => {
  if (cancelSubmitting.value) {
    return;
  }

  showCancelModal.value = false;
  selectedOrder.value = null;
};

/*
|--------------------------------------------------------------------------
| Confirm Order Cancellation
|--------------------------------------------------------------------------
|
| Cancellation reason দিয়ে dedicated cancel API call করে।
|
*/

const confirmCancelOrder = async (
  cancellationReason
) => {
  if (
    !selectedOrder.value?.id ||
    !cancellationReason
  ) {
    return;
  }

  cancelSubmitting.value = true;
  errorMessage.value = "";

  try {
    const response =
      await orderService.cancelOrder(
        selectedOrder.value.id,
        cancellationReason
      );

    showCancelModal.value = false;
    selectedOrder.value = null;

    showSuccess(
      response?.message ??
        "Order canceled successfully."
    );

    await fetchOrders();
  } catch (error) {
    errorMessage.value =
      getErrorMessage(
        error,
        "Unable to cancel the order."
      );
  } finally {
    cancelSubmitting.value = false;
  }
};

/*
|--------------------------------------------------------------------------
| Complete Order
|--------------------------------------------------------------------------
|
| Served ও fully-paid order complete করে, customer spending update করে এবং
| assigned restaurant table release করে।
|
*/

const openCompletionPaymentFlow = async (
  order
) => {
  await router.push({
    name: "order-completion",

    params: {
      id: String(order.id),
    },

    query: {
      complete: "1",
    },
  });
};

/*
|--------------------------------------------------------------------------
| Detect Backend Payment Requirement
|--------------------------------------------------------------------------
|
| Complete is always attempted first. Only the backend decides whether the
| immutable ledger is fully settled. A due-related 422 then opens payment.
|
*/

const isCompletionPaymentRequiredError = (
  error
) => {
  if (
    error?.response?.status !== 422
  ) {
    return false;
  }

  const errors =
    error?.response?.data?.errors ??
    {};

  const dueErrors =
    errors?.due_amount;

  if (
    Array.isArray(dueErrors) &&
    dueErrors.length
  ) {
    return true;
  }

  /*
  |--------------------------------------------------------------------------
  | Backward-Compatible Validation Message Fallback
  |--------------------------------------------------------------------------
  |
  | Older backend builds used an `order` validation key for the same rule.
  | Keep the UI safe during rollout while the backend remains authoritative.
  |
  */

  const candidates = [
    ...(Array.isArray(errors?.order)
      ? errors.order
      : []),

    error?.response?.data?.message,
  ]
    .filter(Boolean)
    .map((value) =>
      String(value).toLowerCase()
    );

  return candidates.some(
    (message) =>
      message.includes("fully paid") ||
      message.includes("outstanding due") ||
      message.includes("due amount")
  );
};

/*
|--------------------------------------------------------------------------
| Complete Order
|--------------------------------------------------------------------------
|
| IMPORTANT:
| Do not trust list-row due/payment snapshots to decide completion.
| Every served-order Complete click calls the backend first.
|
*/

const handleCompleteOrder = (
  order
) => {
  if (!order?.id) {
    return;
  }

  if (
    String(
      order.status || ""
    ) !== "served"
  ) {
    errorMessage.value =
      "Only a served order can be completed.";

    return;
  }

  completionOrder.value = order;
  showCompleteModal.value = true;
};

const closeCompleteModal = () => {
  if (
    completionOrder.value?.id &&
    String(updatingOrderId.value) ===
      String(completionOrder.value.id)
  ) {
    return;
  }

  showCompleteModal.value = false;
  completionOrder.value = null;
};

const confirmCompleteOrder = async () => {
  const order =
    completionOrder.value;

  if (!order?.id) {
    return;
  }

  updatingOrderId.value =
    order.id;

  errorMessage.value = "";

  try {
    const response =
      await orderService.completeOrder(
        order.id
      );

    showCompleteModal.value = false;
    completionOrder.value = null;

    showSuccess(
      response?.message ??
        "Order completed successfully."
    );

    await fetchOrders();
  } catch (error) {
    if (
      isCompletionPaymentRequiredError(
        error
      )
    ) {
      showCompleteModal.value = false;
      completionOrder.value = null;

      try {
        await openCompletionPaymentFlow(
          order
        );
      } catch (
        navigationError
      ) {
        errorMessage.value =
          "The order still has an outstanding balance, but the payment screen could not be opened.";

        console.error(
          "Completion payment navigation failed:",
          navigationError
        );
      }

      return;
    }

    showCompleteModal.value = false;
    completionOrder.value = null;

    errorMessage.value =
      getErrorMessage(
        error,
        "Unable to complete the order."
      );
  } finally {
    updatingOrderId.value =
      null;
  }
};

/*
|--------------------------------------------------------------------------
| Add More Items To Served Order
|--------------------------------------------------------------------------
|
| Uses a dedicated operational route protected by orders.create.
| The existing served-order backend extension logic creates the next
| OrderKitchenBatch while keeping historical served items locked.
|
*/

const handleExtendOrder = async (
  order
) => {
  if (!order?.id) {
    errorMessage.value =
      "Unable to extend this order.";

    return;
  }

  if (
    String(order.status || "") !==
    "served"
  ) {
    errorMessage.value =
      "Only a served order can be extended with a new kitchen batch.";

    return;
  }

  try {
    await router.push({
      name:
        "order-extension",

      params: {
        id: String(order.id),
      },

      query: {
        extend: "1",
      },
    });
  } catch (error) {
    errorMessage.value =
      "Unable to open the Add More Items page.";

    console.error(
      "Order extension navigation failed:",
      error
    );
  }
};


/*
|--------------------------------------------------------------------------
| Open Edit Order Page
|--------------------------------------------------------------------------
*/

const handleEditOrder = async (
  order
) => {
  if (!order?.id) {
    errorMessage.value =
      "Unable to open this order for editing.";

    return;
  }

  try {
    await router.push({
      name: "order-edit",

      params: {
        id: String(order.id),
      },
    });
  } catch (error) {
    errorMessage.value =
      "Unable to open the order edit page.";

    console.error(
      "Order edit navigation failed:",
      error
    );
  }
};

/*
|--------------------------------------------------------------------------
| Open Create Order Page
|--------------------------------------------------------------------------
*/

const goToCreateOrder = async () => {
  try {
    await router.push({
      name: "order-create",
    });
  } catch (error) {
    errorMessage.value =
      "Unable to open the create order page.";

    console.error(
      "Create order navigation failed:",
      error
    );
  }
};

/*
|--------------------------------------------------------------------------
| Print Order List
|--------------------------------------------------------------------------
*/

const printOrders = () => {
  window.print();
};

const exportOrdersCsv = () => {
  const columns = ['SL', 'Order ID', 'Time', 'Customer', 'Date', 'Table', 'Merged With', 'Status', 'Total', 'Due', 'Payment Status', 'Payment Method'];
  const escapeCsvValue = (value) => {
    const text = String(value ?? '');
    const safeText = /^[=+@\-]/.test(text) ? String.fromCharCode(39) + text : text;
    return String.fromCharCode(34) + safeText.replace(/"/g, String.fromCharCode(34, 34)) + String.fromCharCode(34);
  };
  const rows = orders.value.map((order, index) => {
    const statusLabel = filterOptions.statuses.find((option) => option.value === order.status)?.label || order.status || '';
    return [Number(meta.from || 1) + index, order.order_number, order.time, order.customer?.name, order.date, order.primary_table?.table_name || 'No Table', (order.merged_tables || []).map((table) => table.table_name).join(', '), statusLabel, order.total_amount_formatted || order.total_amount, Number(order.due_amount) > 0 ? (order.due_amount_formatted || order.due_amount) : '0.00', order.payment_status_label || order.payment_status, order.payment_method_label || order.payment_method];
  });
  const csv = [columns, ...rows].map((row) => row.map(escapeCsvValue).join(',')).join(String.fromCharCode(13, 10));
  const blob = new Blob([String.fromCharCode(0xFEFF), csv], { type: 'text/csv;charset=utf-8' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.download = 'orders-page-' + (meta.current_page || 1) + '.csv';
  document.body.appendChild(link);
  link.click();
  link.remove();
  window.setTimeout(() => URL.revokeObjectURL(url), 0);
  showSuccess('Order list CSV downloaded. It can be opened in Excel.');
};

const exportOrdersPdf = () => {
  try {
    const doc = new jsPDF({ orientation: 'landscape', unit: 'pt', format: 'a4' });
    const reportRows = orders.value.map((order, index) => {
      const statusLabel = filterOptions.statuses.find((option) => option.value === order.status)?.label || order.status || '';
      const amount = (value) => 'BDT ' + Number(value || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      return [Number(meta.from || 1) + index, order.order_number || '', order.time || '', order.customer?.name || '', order.date || '', order.primary_table?.table_name || 'No Table', (order.merged_tables || []).map((table) => table.table_name).join(', ') || '—', statusLabel, amount(order.total_amount), order.payment_status_label || order.payment_status || '—', order.payment_method_label || order.payment_method || '—'];
    });
    doc.setFontSize(16);
    doc.text('Order Report', 40, 36);
    doc.setFontSize(9);
    doc.text('Showing ' + (meta.from || 0) + ' to ' + (meta.to || 0) + ' of ' + (meta.total || 0) + ' orders', 40, 52);
    autoTable(doc, {
      startY: 66,
      head: [['SL', 'Order ID', 'Time', 'Customer', 'Date', 'Table', 'Merged With', 'Status', 'Total', 'Payment Status', 'Payment Method']],
      body: reportRows,
      theme: 'grid',
      styles: { font: 'helvetica', fontSize: 8, cellPadding: 5, overflow: 'linebreak' },
      headStyles: { fillColor: [76, 45, 160], textColor: 255, fontStyle: 'bold' },
      didDrawPage: () => {
        const pageCount = doc.internal.getNumberOfPages();
        doc.setFontSize(8);
        doc.text('Page ' + doc.internal.getCurrentPageInfo().pageNumber + ' of ' + pageCount, doc.internal.pageSize.getWidth() - 70, doc.internal.pageSize.getHeight() - 18);
      },
    });
    doc.save('orders-page-' + (meta.current_page || 1) + '.pdf');
    showSuccess('Order list PDF downloaded.');
  } catch (error) {
    console.error('Order PDF export failed:', error);
    errorMessage.value = 'Unable to create the order PDF.';
  }
};

/*
|--------------------------------------------------------------------------
| Display Success Message
|--------------------------------------------------------------------------
|
| Success message চার সেকেন্ড পরে automatically clear হয়।
|
*/

const showSuccess = (message) => {
  successMessage.value =
    String(message || "");

  if (messageTimer) {
    clearTimeout(messageTimer);
  }

  messageTimer = setTimeout(() => {
    successMessage.value = "";
  }, 4000);
};

/*
|--------------------------------------------------------------------------
| Resolve API Error Message
|--------------------------------------------------------------------------
|
| Laravel validation error, standard API message অথবা JavaScript error থেকে
| প্রথম readable message return করে।
|
*/

const getErrorMessage = (
  error,
  fallback
) => {
  const validationErrors =
    error?.response?.data?.errors;

  if (
    validationErrors &&
    typeof validationErrors ===
      "object"
  ) {
    const firstError =
      Object.values(
        validationErrors
      )
        .flat()
        .find(Boolean);

    if (firstError) {
      return String(firstError);
    }
  }

  return (
    error?.response?.data?.message ||
    error?.message ||
    fallback
  );
};

/*
|--------------------------------------------------------------------------
| Automatic Order Status Refresh
|--------------------------------------------------------------------------
|
| Chef status changes happen on the Kitchen screen. Order Management performs
| a silent refresh every 5 seconds so Waiter / Manager / Admin see:
|
| Pending -> Preparing -> Ready -> Served
|
| without manually reloading the page.
|
| The refresh:
| - keeps current filters/page
| - does not show the large loading spinner
| - does not clear current rows on a temporary failure
| - pauses while the browser tab is hidden
| - avoids overlapping requests/actions
|
*/

const canRunAutoRefresh = () => {
  return (
    document.visibilityState === "visible" &&
    !orderRequestInFlight &&
    !showCancelModal.value &&
    !cancelSubmitting.value &&
    !showCompleteModal.value &&
    updatingOrderId.value === null
  );
};

const runAutoRefresh = () => {
  if (!canRunAutoRefresh()) {
    return;
  }

  void fetchOrders({
    silent: true,
  });
};

const startAutoRefresh = () => {
  if (autoRefreshTimer) {
    clearInterval(
      autoRefreshTimer
    );
  }

  autoRefreshTimer =
    window.setInterval(
      runAutoRefresh,
      AUTO_REFRESH_INTERVAL_MS
    );
};

const stopAutoRefresh = () => {
  if (!autoRefreshTimer) {
    return;
  }

  clearInterval(
    autoRefreshTimer
  );

  autoRefreshTimer = null;
};

const handleVisibilityChange = () => {
  if (
    document.visibilityState ===
    "visible"
  ) {
    runAutoRefresh();
  }
};

/*
|--------------------------------------------------------------------------
| Debounced Search Watcher
|--------------------------------------------------------------------------
|
| Search input change হওয়ার 500ms পরে automatically order list reload করে।
|
*/

watch(
  () => filters.search,
  () => {
    if (searchTimer) {
      clearTimeout(searchTimer);
    }

    searchTimer = setTimeout(() => {
      filters.page = 1;

      fetchOrders();
    }, 500);
  }
);

/*
|--------------------------------------------------------------------------
| Initial Order List Load
|--------------------------------------------------------------------------
*/

onMounted(() => {
  void fetchOrders();

  startAutoRefresh();

  document.addEventListener(
    "visibilitychange",
    handleVisibilityChange
  );
});

/*
|--------------------------------------------------------------------------
| Component Cleanup
|--------------------------------------------------------------------------
|
| Component unmount হওয়ার সময় active timerগুলো clear করে।
|
*/

onBeforeUnmount(() => {
  stopAutoRefresh();

  document.removeEventListener(
    "visibilitychange",
    handleVisibilityChange
  );

  if (searchTimer) {
    clearTimeout(searchTimer);
  }

  if (messageTimer) {
    clearTimeout(messageTimer);
  }
});
</script>
