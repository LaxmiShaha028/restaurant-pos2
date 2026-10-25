<template>
  <Teleport to="body">
    <div
      v-if="show"
      class="complete-order-backdrop"
      @click.self="handleClose"
    >
      <form
        class="complete-order-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="complete-order-title"
        @submit.prevent="emit('confirm')"
      >
        <header class="complete-order-header">
          <div class="complete-order-heading">
            <span
              class="complete-order-icon"
              :class="{ due: hasDue, paid: !hasDue }"
            >
              <i
                :class="
                  hasDue
                    ? 'bi bi-wallet2'
                    : 'bi bi-check2-circle'
                "
              ></i>
            </span>

            <div>
              <p class="complete-order-eyebrow">
                Finalize Restaurant Order
              </p>

              <h2 id="complete-order-title">
                {{
                  hasDue
                    ? "Pay & Complete Order"
                    : "Complete Order"
                }}
              </h2>

              <span class="complete-order-number">
                {{
                  order?.order_number ||
                  "Selected Order"
                }}
              </span>
            </div>
          </div>

          <button
            type="button"
            class="complete-order-close"
            aria-label="Close"
            :disabled="submitting"
            @click="handleClose"
          >
            <i class="bi bi-x-lg"></i>
          </button>
        </header>

        <div class="complete-order-body">
          <div
            class="complete-order-message"
            :class="{ due: hasDue, paid: !hasDue }"
          >
            <i
              :class="
                hasDue
                  ? 'bi bi-info-circle'
                  : 'bi bi-shield-check'
              "
            ></i>

            <p v-if="hasDue">
              This served order still has an outstanding balance.
              Continue to the payment screen to collect the remaining
              amount and complete the order.
            </p>

            <p v-else>
              This served order is fully paid. Confirm completion to
              finalize the bill and release the assigned table(s)
              through the existing backend completion flow.
            </p>
          </div>

          <div class="complete-order-financial-grid">
            <article>
              <span>Total</span>
              <strong>{{ formatMoney(totalAmount) }}</strong>
            </article>

            <article>
              <span>Paid</span>
              <strong class="paid-value">
                {{ formatMoney(paidAmount) }}
              </strong>
            </article>

            <article>
              <span>Due</span>
              <strong :class="{ 'due-value': hasDue }">
                {{ formatMoney(dueAmount) }}
              </strong>
            </article>
          </div>

          <div class="complete-order-summary">
            <div>
              <span>Order Status</span>
              <strong>Served</strong>
            </div>

            <div>
              <span>Next Step</span>
              <strong>
                {{
                  hasDue
                    ? "Collect payment"
                    : "Finalize order"
                }}
              </strong>
            </div>
          </div>
        </div>

        <footer class="complete-order-footer">
          <button
            type="button"
            class="complete-order-secondary"
            :disabled="submitting"
            @click="handleClose"
          >
            Not Now
          </button>

          <button
            type="submit"
            class="complete-order-primary"
            :class="{ due: hasDue }"
            :disabled="submitting"
          >
            <span
              v-if="submitting"
              class="spinner-border spinner-border-sm"
              aria-hidden="true"
            ></span>

            <i
              v-else
              :class="
                hasDue
                  ? 'bi bi-credit-card'
                  : 'bi bi-check2-circle'
              "
            ></i>

            {{
              submitting
                ? "Please wait..."
                : hasDue
                  ? "Continue to Payment"
                  : "Complete Order"
            }}
          </button>
        </footer>
      </form>
    </div>
  </Teleport>
</template>

<script setup>
import { computed, watch, onBeforeUnmount } from "vue";

const props = defineProps({
  show: { type: Boolean, default: false },
  order: { type: Object, default: null },
  submitting: { type: Boolean, default: false },
});

const emit = defineEmits(["close", "confirm"]);

const totalAmount = computed(() =>
  Math.max(Number(props.order?.total_amount || 0), 0)
);

const paidAmount = computed(() =>
  Math.max(Number(props.order?.paid_amount || 0), 0)
);

const dueAmount = computed(() =>
  Math.max(Number(props.order?.due_amount || 0), 0)
);

const hasDue = computed(() =>
  dueAmount.value > 0.005
);

const handleClose = () => {
  if (props.submitting) return;
  emit("close");
};

const formatMoney = (value) =>
  `৳ ${Number(value || 0).toLocaleString("en-BD", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })}`;

watch(
  () => props.show,
  (visible) => {
    document.body.style.overflow =
      visible ? "hidden" : "";
  }
);

onBeforeUnmount(() => {
  document.body.style.overflow = "";
});
</script>

<style scoped>
.complete-order-backdrop {
  position: fixed;
  inset: 0;
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
  background: rgba(17, 17, 17, 0.62);
  backdrop-filter: blur(4px);
}

.complete-order-modal {
  width: min(520px, 100%);
  overflow: hidden;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background: #fff;
  box-shadow: 0 20px 55px rgba(17, 17, 17, 0.24);
}

.complete-order-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 20px;
  padding: 16px 18px;
  border-bottom: 1px solid #e5e7eb;
}

.complete-order-heading {
  display: flex;
  align-items: flex-start;
  gap: 10px;
}

.complete-order-icon {
  width: 40px;
  height: 40px;
  flex: 0 0 40px;
  display: grid;
  place-items: center;
  border-radius: 5px;
  font-size: 18px;
}

.complete-order-icon.due {
  color: #ea580c;
  background: #fff7ed;
}

.complete-order-icon.paid {
  color: #047857;
  background: #ecfdf5;
}

.complete-order-eyebrow {
  margin: 0 0 3px;
  color: #ea580c;
  font-size: 10px;
  font-weight: 700;
  letter-spacing: .04em;
  text-transform: uppercase;
}

.complete-order-header h2 {
  margin: 0;
  color: #111827;
  font-size: 17px;
  font-weight: 700;
}

.complete-order-number {
  display: inline-block;
  margin-top: 5px;
  color: #6b7280;
  font-size: 11px;
  font-weight: 600;
}

.complete-order-close {
  width: 30px;
  height: 30px;
  display: grid;
  place-items: center;
  border: 1px solid #e5e7eb;
  border-radius: 5px;
  color: #6b7280;
  background: #fff;
}

.complete-order-body {
  padding: 16px 18px;
}

.complete-order-message {
  display: flex;
  gap: 8px;
  padding: 10px 11px;
  border: 1px solid;
  border-radius: 5px;
}

.complete-order-message p {
  margin: 0;
  color: #475569;
  font-size: 12px;
  line-height: 1.5;
}

.complete-order-message.due {
  border-color: #fed7aa;
  background: #fffaf5;
}

.complete-order-message.due i { color: #c2410c; }

.complete-order-message.paid {
  border-color: #a7f3d0;
  background: #f3fff9;
}

.complete-order-message.paid i { color: #059669; }

.complete-order-financial-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 6px;
  margin-top: 12px;
}

.complete-order-financial-grid article {
  padding: 10px;
  border: 1px solid #e2e8f0;
  border-radius: 5px;
  background: #f9fafb;
}

.complete-order-financial-grid span,
.complete-order-summary span {
  display: block;
  margin-bottom: 5px;
  color: #6b7280;
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
}

.complete-order-financial-grid strong {
  color: #111827;
  font-size: 13px;
}

.complete-order-financial-grid .paid-value { color: #047857; }
.complete-order-financial-grid .due-value { color: #dc2626; }

.complete-order-summary {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 8px;
  margin-top: 8px;
}

.complete-order-summary div {
  padding: 9px 10px;
  border: 1px solid #e5e7eb;
  border-radius: 5px;
  background: #f9fafb;
}

.complete-order-summary strong {
  color: #374151;
  font-size: 12px;
}

.complete-order-footer {
  display: flex;
  justify-content: flex-end;
  gap: 6px;
  padding: 12px 18px;
  border-top: 1px solid #e5e7eb;
  background: #f9fafb;
}

.complete-order-secondary,
.complete-order-primary {
  min-height: 32px;
  padding: 0 11px;
  border-radius: 5px;
  font-size: 12px;
  font-weight: 600;
}

.complete-order-secondary {
  border: 1px solid #d1d5db;
  color: #374151;
  background: #fff;
}

.complete-order-secondary:hover:not(:disabled) {
  border-color: #9ca3af;
  background: #f3f4f6;
}

.complete-order-primary {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  border: 1px solid #111111;
  color: #f97316;
  background: #111111;
}

.complete-order-primary.due {
  border-color: #111111;
  color: #f97316;
  background: #111111;
}

.complete-order-primary:hover:not(:disabled) {
  border-color: #000000;
  color: #fb923c;
  background: #000000;
}

.complete-order-secondary:disabled,
.complete-order-primary:disabled,
.complete-order-close:disabled {
  cursor: not-allowed;
  opacity: .65;
}

@media (max-width: 640px) {
  .complete-order-backdrop {
    align-items: flex-end;
    padding: 12px;
  }

  .complete-order-modal {
    border-radius: 20px 20px 14px 14px;
  }

  .complete-order-financial-grid,
  .complete-order-summary {
    grid-template-columns: 1fr;
  }

  .complete-order-footer {
    flex-direction: column-reverse;
  }

  .complete-order-secondary,
  .complete-order-primary {
    width: 100%;
  }
}
</style>
