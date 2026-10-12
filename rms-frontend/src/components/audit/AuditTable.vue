<template>
  <div class="audit-table-wrapper">
    <div
      v-if="loading"
      class="audit-loading"
    >
      Loading audit logs...
    </div>

    <div
      v-else-if="!logs.length"
      class="audit-empty"
    >
      No audit activity found.
    </div>

    <table
      v-else
      class="audit-table"
    >
      <thead>
        <tr>
          <th>
            User
          </th>

          <th>
            Module
          </th>

          <th>
            Description
          </th>

          <th>
            Date
          </th>

          <th>
            View
          </th>
        </tr>
      </thead>

      <tbody>
        <tr
          v-for="log in logs"
          :key="log.id"
        >
          <td>
            <div class="user-info">
              <strong>
                {{
                  log.user?.name
                    ??
                    'System'
                }}
              </strong>

              <span>
                {{
                  log.user?.email
                    ??
                    '-'
                }}
              </span>
            </div>
          </td>

          <td>
            <span class="module-badge">
              {{ log.module }}
            </span>
          </td>

          <td>
            {{ log.description ?? '-' }}
          </td>

          <td>
            {{ formatDate(log.created_at) }}
          </td>

          <td>
            <button
              type="button"
              class="view-btn"
              title="View details"
              @click="$emit('view', log)"
            >
              <i
                class="bi bi-eye"
                aria-hidden="true"
              ></i>
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
defineProps({
  logs: {
    type: Array,
    default: () => [],
  },

  loading: {
    type: Boolean,
    default: false,
  },
})

defineEmits(['view'])

function formatDate(date) {
  if (!date) {
    return '-'
  }

  return new Date(date).toLocaleString()
}

// function getActionClass(action) {
//   switch (action) {
//     case 'created':
//       return 'created'
//     case 'updated':
//       return 'updated'
//     case 'deleted':
//       return 'deleted'
//     case 'blocked':
//       return 'blocked'
//     case 'unblocked':
//       return 'unblocked'
//     default:
//       return ''
//   }
// }

// function getActionIcon(action) {
//   switch (action) {
//     case 'created':
//       return 'bi-check-circle-fill'
//     case 'updated':
//       return 'bi-pencil-fill'
//     case 'deleted':
//       return 'bi-trash-fill'
//     case 'blocked':
//       return 'bi-x-circle-fill'
//     case 'unblocked':
//       return 'bi-unlock-fill'
//     default:
//       return 'bi-circle-fill'
//   }
// }
</script>

<style scoped>
/* ==========================================================================
   AUDIT TABLE – Compact UI
   ========================================================================== */

.audit-table-wrapper {
  width: 100%;
  min-width: 0;
  overflow-x: auto;
}

.audit-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 12px;
}

.audit-table th,
.audit-table td {
  padding: 6px 6px;
  text-align: left;
  vertical-align: middle;
  line-height: 1.2;
}

.audit-table th {
  background: #f8f1e9;
  border-bottom: 1px solid #eee3da;
  color: #475569;
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.02em;
  white-space: nowrap;
}

.audit-table td {
  border-bottom: 1px solid #f1f5f9;
  color: #374151;
  font-size: 10px;
}

.audit-table tbody tr:hover {
  background: #fff8f1;
}

.audit-table th:first-child,
.audit-table td:first-child {
  padding-left: 10px;
}

/* User */
.user-info {
  display: flex;
  min-width: 0;
  flex-direction: column;
  gap: 1px;
}

.user-info strong {
  overflow: hidden;
  color: #172033;
  font-size: 12px;
  font-weight: 700;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.user-info span {
  overflow: hidden;
  color: #667085;
  font-size: 12px;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Module */
.module-badge {
  display: inline-flex;
  max-width: 100%;
  padding: 2px 6px;
  border-radius: 10px;
  background: #fff1e6;
  color: #f97316;
  font-size: 12px;
  font-weight: 600;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Status badges – distinct colors (not all green) */
.action-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 3px;
  min-height: 20px;
  padding: 2px 6px;
  border-radius: 10px;
  font-size: 12px;
  font-weight: 600;
  white-space: nowrap;
  background: #f1f5f9;
  color: #475569;
}

.action-badge i {
  font-size: 12px;
  line-height: 1;
}

.action-badge.created {
  background: #dcfce7 !important;
  color: #15803d !important;
}

.action-badge.updated {
  background: #fef3c7 !important;
  color: #b45309 !important;
}

.action-badge.deleted {
  background: #fee2e2 !important;
  color: #b91c1c !important;
}

.action-badge.blocked {
  background: #fee2e2 !important;
  color: #991b1b !important;
}

.action-badge.unblocked {
  background: #fff1e6 !important;
  color: #c2410c !important;
}

/* View button – icon only */
.view-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  height: 24px;
  padding: 0;
  border: 1px solid #f97316;
  border-radius: 4px;
  background: #f97316;
  color: #ffffff;
  font-size: 12px;
  cursor: pointer;
}

.view-btn:hover {
  background: #c2410c;
  border-color: #c2410c;
}

/* States */
.audit-loading,
.audit-empty {
  padding: 20px 8px;
  color: #667085;
  font-size: 12px;
  text-align: center;
}

/* Desktop column widths */
@media (min-width: 900px) {
  .audit-table {
    table-layout: fixed;
  }

  .audit-table-wrapper {
    overflow-x: hidden;
  }

  .audit-table th:nth-child(1),
  .audit-table td:nth-child(1) {
    width: 16%;
  }

  .audit-table th:nth-child(2),
  .audit-table td:nth-child(2) {
    width: 12%;
  }

  .audit-table th:nth-child(3),
  .audit-table td:nth-child(3) {
    width: 14%;
    text-align: center;
  }

  .audit-table th:nth-child(4),
  .audit-table td:nth-child(4) {
    width: 30%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .audit-table th:nth-child(5),
  .audit-table td:nth-child(5) {
    width: 18%;
  }

  .audit-table th:nth-child(6),
  .audit-table td:nth-child(6) {
    width: 10%;
    text-align: center;
  }
}

@media (max-width: 899px) {
  .audit-table {
    min-width: 720px;
  }
}
</style>