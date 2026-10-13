<template>
  <Teleport to="body">
    <div
      v-if="show"
      class="reservation-action-overlay"
      @mousedown.self="handleClose"
    >
      <div
        class="reservation-action-modal"
        role="dialog"
        aria-modal="true"
      >
        <div
          class="reservation-action-header"
          :class="{
            danger: isCancel,
          }"
        >
          <div class="reservation-action-title-wrap">
            <span class="reservation-action-icon">
              <i
                class="bi"
                :class="
                  isCancel
                    ? 'bi-x-octagon'
                    : 'bi-person-check'
                "
              ></i>
            </span>

            <div>
              <span class="reservation-action-eyebrow">
                Reservation Action
              </span>

              <h3>
                {{
                  isCancel
                    ? "Cancel Reservation"
                    : "Seat Guests"
                }}
              </h3>

              <p>
                {{
                  reservation?.reservation_no
                  ||
                  "Selected reservation"
                }}
              </p>
            </div>
          </div>

          <button
            type="button"
            class="reservation-action-close"
            :disabled="submitting"
            @click="handleClose"
          >
            <i class="bi bi-x-lg"></i>
          </button>
        </div>

        <div class="reservation-action-body">
          <div class="reservation-action-summary">
            <div>
              <span>Customer</span>
              <strong>
                {{
                  reservation
                    ?.customer
                    ?.name
                  ||
                  "Walk-in Customer"
                }}
              </strong>
            </div>

            <div>
              <span>Guests</span>
              <strong>
                {{
                  reservation?.guest_count
                  ||
                  0
                }}
              </strong>
            </div>

            <div>
              <span>Tables</span>
              <strong>
                {{ tableNames }}
              </strong>
            </div>

            <div>
              <span>Schedule</span>
              <strong>
                {{ scheduleLabel }}
              </strong>
            </div>
          </div>

          <div
            v-if="!isCancel"
            class="reservation-action-info"
          >
            <i class="bi bi-info-circle"></i>

            <div>
              <strong>
                Live table availability will be checked now.
              </strong>

              <span>
                If an assigned table is currently occupied,
                cleaning, or already has an active order,
                seating will be blocked safely.
              </span>
            </div>
          </div>

          <div
            v-else
            class="reservation-action-warning"
          >
            <i class="bi bi-exclamation-triangle"></i>

            <div>
              <strong>
                This reservation will be cancelled.
              </strong>

              <span>
                Cancelling a future reservation will not release
                a table that is physically occupied by another
                live order.
              </span>
            </div>
          </div>

          <label
            v-if="isCancel"
            class="reservation-action-field"
          >
            <span>
              Cancellation Reason
              <small>Optional</small>
            </span>

            <textarea
              v-model="reason"
              rows="4"
              maxlength="1000"
              :disabled="submitting"
              placeholder="Write the cancellation reason..."
            ></textarea>

            <small class="reservation-action-counter">
              {{ reason.length }}/1000
            </small>
          </label>

          <div
            v-if="error"
            class="reservation-action-error"
          >
            <i class="bi bi-exclamation-circle"></i>
            <span>{{ error }}</span>
          </div>
        </div>

        <div class="reservation-action-footer">
          <button
            type="button"
            class="btn btn-light"
            :disabled="submitting"
            @click="handleClose"
          >
            {{
              isCancel
                ? "Keep Reservation"
                : "Not Now"
            }}
          </button>

          <button
            type="button"
            class="btn"
            :class="
              isCancel
                ? 'btn-danger'
                : 'btn-primary'
            "
            :disabled="submitting"
            @click="submit"
          >
            <span
              v-if="submitting"
              class="spinner-border spinner-border-sm"
            ></span>

            <i
              v-else
              class="bi"
              :class="
                isCancel
                  ? 'bi-x-circle'
                  : 'bi-person-check'
              "
            ></i>

            {{
              submitting
                ? "Processing..."
                : isCancel
                  ? "Cancel Reservation"
                  : "Confirm Seating"
            }}
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import {
  computed,
  ref,
  watch,
} from "vue";

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },

  mode: {
    type: String,
    default: "seat",
  },

  reservation: {
    type: Object,
    default: null,
  },

  submitting: {
    type: Boolean,
    default: false,
  },

  error: {
    type: String,
    default: "",
  },
});

const emit = defineEmits([
  "close",
  "confirm",
]);

const reason = ref("");

const isCancel =
  computed(
    () =>
      props.mode === "cancel"
  );

const tableNames =
  computed(() => {
    const tables =
      Array.isArray(
        props.reservation?.tables
      )
        ? props.reservation.tables
        : [];

    if (!tables.length) {
      return "-";
    }

    return tables
      .map(
        (table) =>
          table?.table_name
          ||
          table?.name
          ||
          `Table ${table?.id}`
      )
      .join(", ");
  });

const scheduleLabel =
  computed(() => {
    const date =
      props.reservation
        ?.reservation_date
      ||
      "-";

    const start =
      props.reservation
        ?.start_time_formatted
      ||
      props.reservation
        ?.start_time
      ||
      "-";

    const end =
      props.reservation
        ?.end_time_formatted
      ||
      props.reservation
        ?.end_time
      ||
      "-";

    return `${date} · ${start} - ${end}`;
  });

watch(
  () => props.show,
  (show) => {
    if (show) {
      reason.value = "";
      document.body.style.overflow =
        "hidden";
    } else {
      document.body.style.overflow =
        "";
    }
  }
);

function handleClose()
{
  if (props.submitting) {
    return;
  }

  emit("close");
}

function submit()
{
  emit(
    "confirm",
    isCancel.value
      ? reason.value.trim()
      : null
  );
}
</script>

<style scoped>
.reservation-action-overlay {
  position: fixed;
  inset: 0;
  z-index: 12000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 18px;
  background: rgba(15, 23, 42, 0.58);
  backdrop-filter: blur(5px);
}

.reservation-action-modal {
  width: min(100%, 560px);
  overflow: hidden;
  border-radius: 18px;
  background: #ffffff;
  box-shadow:
    0 25px 60px rgba(15, 23, 42, 0.22),
    0 8px 25px rgba(15, 23, 42, 0.12);
}

.reservation-action-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
  padding: 20px 22px;
  background: #c2410c;
  color: #ffffff;
}

.reservation-action-header.danger {
  background: linear-gradient(
    135deg,
    #dc2626,
    #b91c1c
  );
}

.reservation-action-title-wrap {
  display: flex;
  min-width: 0;
  align-items: center;
  gap: 12px;
}

.reservation-action-icon {
  display: inline-flex;
  width: 44px;
  height: 44px;
  flex: 0 0 44px;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
  background: rgba(255,255,255,.16);
  font-size: 19px;
}

.reservation-action-eyebrow {
  display: block;
  margin-bottom: 2px;
  color: rgba(255,255,255,.76);
  font-size: 10px;
  font-weight: 700;
  letter-spacing: .08em;
  text-transform: uppercase;
}

.reservation-action-header h3 {
  margin: 0;
  color: #ffffff;
  font-size: 20px;
  font-weight: 750;
}

.reservation-action-header p {
  margin: 4px 0 0;
  color: rgba(255,255,255,.84);
  font-size: 12px;
}

.reservation-action-close {
  width: 40px;
  height: 40px;
  flex: 0 0 40px;
  border: 0;
  border-radius: 50%;
  background: rgba(255,255,255,.14);
  color: #ffffff;
}

.reservation-action-body {
  padding: 20px 22px;
}

.reservation-action-summary {
  display: grid;
  grid-template-columns:
    repeat(2, minmax(0, 1fr));
  gap: 9px;
}

.reservation-action-summary > div {
  min-width: 0;
  padding: 11px 12px;
  border: 1px solid #eee3da;
  border-radius: 11px;
  background: #fffaf5;
}

.reservation-action-summary span,
.reservation-action-summary strong {
  display: block;
}

.reservation-action-summary span {
  margin-bottom: 3px;
  color: #64748b;
  font-size: 10px;
  font-weight: 600;
}

.reservation-action-summary strong {
  overflow: hidden;
  color: #172033;
  font-size: 12px;
  font-weight: 700;
  text-overflow: ellipsis;
}

.reservation-action-info,
.reservation-action-warning,
.reservation-action-error {
  display: flex;
  align-items: flex-start;
  gap: 9px;
  margin-top: 14px;
  padding: 11px 12px;
  border-radius: 10px;
  font-size: 11px;
  line-height: 1.45;
}

.reservation-action-info {
  border: 1px solid #f5ddca;
  background: #fff8f1;
  color: #9a3412;
}

.reservation-action-warning {
  border: 1px solid #fed7aa;
  background: #fff7ed;
  color: #9a3412;
}

.reservation-action-error {
  border: 1px solid #fecaca;
  background: #fef2f2;
  color: #b91c1c;
}

.reservation-action-info strong,
.reservation-action-warning strong {
  display: block;
  margin-bottom: 2px;
}

.reservation-action-info span,
.reservation-action-warning span {
  display: block;
}

.reservation-action-field {
  display: block;
  margin-top: 14px;
}

.reservation-action-field > span {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 6px;
  color: #475569;
  font-size: 12px;
  font-weight: 650;
}

.reservation-action-field > span small {
  color: #94a3b8;
  font-size: 10px;
}

.reservation-action-field textarea {
  width: 100%;
  min-height: 92px;
  padding: 11px 12px;
  border: 1px solid #eee3da;
  border-radius: 10px;
  outline: 0;
  color: #172033;
  font-size: 13px;
  resize: vertical;
}

.reservation-action-field textarea:focus {
  border-color: #f97316;
  box-shadow:
    0 0 0 3px rgba(249,115,22,.14);
}

.reservation-action-counter {
  display: block;
  margin-top: 4px;
  color: #94a3b8;
  font-size: 9px;
  text-align: right;
}

.reservation-action-footer {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 9px;
  padding: 13px 22px;
  border-top: 1px solid #eee3da;
  background: #fffaf5;
}

.reservation-action-footer .btn {
  min-height: 40px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 0 15px;
  border-radius: 9px;
  font-size: 12px;
  font-weight: 650;
}

.reservation-action-footer .btn-light {
  border: 1px solid #eee3da;
  background: #ffffff;
  color: #667085;
}

.reservation-action-footer .btn-light:hover:not(:disabled) {
  border-color: #fdba74;
  background: #fff1e6;
  color: #c2410c;
}

.reservation-action-footer .btn-primary {
  border: 1px solid #292929;
  background: #111111;
  color: #f97316;
}

.reservation-action-footer .btn-primary:hover:not(:disabled) {
  border-color: #3b3b3b;
  background: #242424;
  color: #fb923c;
}

@media (max-width: 560px) {
  .reservation-action-overlay {
    align-items: flex-end;
    padding: 0;
  }

  .reservation-action-modal {
    width: 100%;
    border-radius: 20px 20px 0 0;
  }

  .reservation-action-summary {
    grid-template-columns: 1fr;
  }

  .reservation-action-footer {
    flex-direction: column-reverse;
  }

  .reservation-action-footer .btn {
    width: 100%;
  }
}
</style>
