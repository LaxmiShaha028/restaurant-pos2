<template>
  <div class="table-responsive">

    <table class="table table-hover align-middle mb-0">

      <thead class="table-light">

        <tr>

          <th class="ps-4">
            Date
          </th>

          <th>
            Journal No
          </th>

          <th>
            Description
          </th>

          <th class="text-end">
            Debit
          </th>

          <th class="text-end pe-4">
            Credit
          </th>

        </tr>

      </thead>


      <tbody>

        <tr
          v-for="journal in journals"
          :key="journal.id"
        >

          <td class="ps-4 text-nowrap">
            {{ formatDate(journal.journal_date) }}
          </td>


          <td class="text-nowrap">

            <span class="journal-number">
              {{ journal.journal_number }}
            </span>

          </td>


          <td>
            {{ journal.description || '-' }}
          </td>


          <td class="text-end fw-medium text-success">
            {{ money(journal.total_debit) }}
          </td>


          <td class="text-end fw-medium text-danger pe-4">
            {{ money(journal.total_credit) }}
          </td>

        </tr>


        <tr
          v-if="journals.length === 0"
        >

          <td
            colspan="5"
            class="text-center text-muted py-5"
          >

            <i class="bi bi-journal-x fs-3 d-block mb-2"></i>

            No journal entry found

          </td>

        </tr>

      </tbody>

    </table>

  </div>
</template>


<script setup>
defineProps({

  journals: {

    type:
      Array,

    default:
      () => [],

  },

})


function money(
  value
) {

  return (
    '৳ '
    +
    Number(
      value
      ??
      0
    )
      .toLocaleString(
        'en-BD',
        {
          minimumFractionDigits:
            2,

          maximumFractionDigits:
            2,
        }
      )
  )

}


function formatDate(
  date
) {

  if (!date) {

    return '-'

  }


  const value =
    new Date(
      date
    )


  if (
    Number.isNaN(
      value.getTime()
    )
  ) {

    return date

  }


  return value.toLocaleDateString(
    'en-BD',
    {
      year:
        'numeric',

      month:
        'short',

      day:
        '2-digit',
    }
  )

}
</script>


<style scoped>
.table > :not(caption) > * > * {
  padding-top: 0.85rem;

  padding-bottom: 0.85rem;
}


.journal-number {
  display: inline-block;

  font-size: 0.82rem;

  font-weight: 600;

  padding: 0.3rem 0.55rem;

  border-radius: 6px;

  color: #0d6efd;

  background: rgba(
    13,
    110,
    253,
    0.08
  );
}
</style>