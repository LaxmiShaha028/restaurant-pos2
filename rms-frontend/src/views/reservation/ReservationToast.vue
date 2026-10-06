<template>
  <Teleport to="body">
    <Transition name="reservation-toast">
      <div
        v-if="show && message"
        class="reservation-toast"
        :class="toastClass"
        role="status"
      >
        <span class="reservation-toast-icon">
          <i
            class="bi"
            :class="iconClass"
          ></i>
        </span>

        <div>
          <strong>{{ title }}</strong>
          <p>{{ message }}</p>
        </div>

        <button
          type="button"
          @click="emit('close')"
        >
          <i class="bi bi-x-lg"></i>
        </button>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import {
  computed,
} from "vue";

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },

  type: {
    type: String,
    default: "success",
  },

  message: {
    type: String,
    default: "",
  },
});

const emit = defineEmits([
  "close",
]);

const toastClass =
  computed(
    () =>
      `reservation-toast-${props.type}`
  );

const iconClass =
  computed(() => {
    if (props.type === "error") {
      return "bi-exclamation-circle-fill";
    }

    if (props.type === "warning") {
      return "bi-exclamation-triangle-fill";
    }

    return "bi-check-circle-fill";
  });

const title =
  computed(() => {
    if (props.type === "error") {
      return "Action Failed";
    }

    if (props.type === "warning") {
      return "Attention";
    }

    return "Success";
  });
</script>

<style scoped>

/* ==========================================================================
   RESERVATION TOAST — COMPACT BLUE THEME
   Matches Other Income UI
   ========================================================================== */

.reservation-toast {
    position: fixed;
    top: 12px;
    right: 12px;
    z-index: 14000;

    width: min(360px, calc(100vw - 24px));

    display: grid;
    grid-template-columns: auto 1fr auto;
    align-items: center;
    gap: 6px;

    padding: 7px 9px;

    border: 1px solid #bbf7d0;
    border-radius: 5px;

    background: #f0fdf4;
    color: #166534;

    font-size: 11px;

    box-shadow:
        0 8px 24px rgba(15, 23, 42, 0.08);
}


/* --------------------------------------------------------------------------
   ERROR
   -------------------------------------------------------------------------- */

.reservation-toast-error {
    border-color: #fecaca;
    background: #fef2f2;
    color: #b91c1c;
}


/* --------------------------------------------------------------------------
   WARNING
   -------------------------------------------------------------------------- */

.reservation-toast-warning {
    border-color: #fed7aa;
    background: #fff7ed;
    color: #9a3412;
}


/* --------------------------------------------------------------------------
   ICON
   -------------------------------------------------------------------------- */

.reservation-toast-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    width: 22px;
    height: 22px;

    margin-top: 0;

    border-radius: 4px;

    background: rgba(255, 255, 255, 0.65);

    font-size: 12px;
}


/* --------------------------------------------------------------------------
   TEXT
   -------------------------------------------------------------------------- */

.reservation-toast strong {
    display: block;
    margin-bottom: 1px;

    color: inherit;

    font-size: 11px;
    font-weight: 700;
    line-height: 1.2;
}

.reservation-toast p {
    margin: 0;

    color: inherit;

    font-size: 10px;
    line-height: 1.3;
}


/* --------------------------------------------------------------------------
   CLOSE BUTTON
   -------------------------------------------------------------------------- */

.reservation-toast button {
    display: inline-flex;

    width: 22px;
    height: 22px;

    align-items: center;
    justify-content: center;

    margin: 0;
    padding: 0;

    border: 0;
    border-radius: 4px;

    background: transparent;
    color: inherit;

    font-size: 10px;

    cursor: pointer;

    transition:
        background 0.15s ease,
        color 0.15s ease;
}

.reservation-toast button:hover {
    background: rgba(15, 23, 42, 0.06);
}


/* --------------------------------------------------------------------------
   TRANSITION
   -------------------------------------------------------------------------- */

.reservation-toast-enter-active,
.reservation-toast-leave-active {
    transition:
        opacity 0.16s ease,
        transform 0.16s ease;
}

.reservation-toast-enter-from,
.reservation-toast-leave-to {
    opacity: 0;
    transform: translateY(-6px);
}


/* --------------------------------------------------------------------------
   MOBILE
   -------------------------------------------------------------------------- */

@media (max-width: 480px) {

    .reservation-toast {
        top: 8px;
        right: 8px;

        width: calc(100vw - 16px);

        padding: 7px 8px;
    }

}

</style>
