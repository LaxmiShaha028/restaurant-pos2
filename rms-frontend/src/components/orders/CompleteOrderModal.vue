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
  background: rgba(15, 23, 42, 0.58);
  backdrop-filter: blur(7px);
}

.complete-order-modal {
  width: min(560px, 100%);
  overflow: hidden;
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 22px;
  background: #fff;
  box-shadow: 0 28px 70px rgba(15, 23, 42, 0.22);
}

.complete-order-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 20px;
  padding: 24px 24px 20px;
  border-bottom: 1px solid #eef2f7;
}

.complete-order-heading {
  display: flex;
  align-items: flex-start;
  gap: 14px;
}

.complete-order-icon {
  width: 48px;
  height: 48px;
  flex: 0 0 48px;
  display: grid;
  place-items: center;
  border-radius: 15px;
  font-size: 22px;
}

.complete-order-icon.due {
  color: #b45309;
  background: #fff7ed;
}

.complete-order-icon.paid {
  color: #047857;
  background: #ecfdf5;
}

.complete-order-eyebrow {
  margin: 0 0 3px;
  color: #64748b;
  font-size: 11px;
  font-weight: 800;
  letter-spacing: .08em;
  text-transform: uppercase;
}

.complete-order-header h2 {
  margin: 0;
  color: #0f172a;
  font-size: 21px;
  font-weight: 800;
}

.complete-order-number {
  display: inline-block;
  margin-top: 5px;
  color: #64748b;
  font-size: 13px;
  font-weight: 700;
}

.complete-order-close {
  width: 38px;
  height: 38px;
  display: grid;
  place-items: center;
  border: 0;
  border-radius: 11px;
  color: #64748b;
  background: #f8fafc;
}

.complete-order-body {
  padding: 22px 24px 24px;
}

.complete-order-message {
  display: flex;
  gap: 11px;
  padding: 14px 15px;
  border: 1px solid;
  border-radius: 14px;
}

.complete-order-message p {
  margin: 0;
  color: #475569;
  font-size: 13px;
  line-height: 1.65;
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
  gap: 10px;
  margin-top: 18px;
}

.complete-order-financial-grid article {
  padding: 15px;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  background: #f8fafc;
}

.complete-order-financial-grid span,
.complete-order-summary span {
  display: block;
  margin-bottom: 5px;
  color: #64748b;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
}

.complete-order-financial-grid strong {
  color: #0f172a;
  font-size: 15px;
}

.complete-order-financial-grid .paid-value { color: #047857; }
.complete-order-financial-grid .due-value { color: #dc2626; }

.complete-order-summary {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
  margin-top: 14px;
}

.complete-order-summary div {
  padding: 13px 14px;
  border-radius: 12px;
  background: #f8fafc;
}

.complete-order-summary strong {
  color: #334155;
  font-size: 13px;
}

.complete-order-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding: 18px 24px 22px;
  border-top: 1px solid #eef2f7;
  background: #fbfdff;
}

.complete-order-secondary,
.complete-order-primary {
  min-height: 42px;
  padding: 0 17px;
  border-radius: 12px;
  font-size: 13px;
  font-weight: 800;
}

.complete-order-secondary {
  border: 1px solid #dbe3ee;
  color: #475569;
  background: #fff;
}

.complete-order-primary {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  border: 1px solid #047857;
  color: #fff;
  background: #059669;
}

.complete-order-primary.due {
  border-color: #1d4ed8;
  background: #2563eb;
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
