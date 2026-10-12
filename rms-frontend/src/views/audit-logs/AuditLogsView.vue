<template>
  <section class="audit-page">
    <!-- Header -->
    <header class="audit-header">
      <div class="audit-header-left">
        <div class="audit-header-icon">
          <i class="bi bi-journal-text" aria-hidden="true"></i>
        </div>

        <div>
          <p class="audit-page-eyebrow">
            Security
          </p>

          <h2>
            Audit Logs
          </h2>

          <p>
            Track system activities and user actions
          </p>
        </div>
      </div>
    </header>

    <div class="audit-card">
      <div class="card-body">
        <AuditFilter
          v-model="filters"
          @change="loadLogs"
        />

        <AuditTable
          :logs="logs"
          :loading="loading"
          @view="viewDetails"
        />
      </div>
    </div>

    <AuditDetailsModal
      v-if="showDetails"
      :log="selectedLog"
      @close="
        showDetails = false;
        selectedLog = null;
      "
    />
  </section>
</template>







<script setup>


import {

    ref,

    reactive,

    onMounted

}

from 'vue'



import auditLogService

from '@/services/auditLogService'



import AuditFilter

from '@/components/audit/AuditFilter.vue'



import AuditTable

from '@/components/audit/AuditTable.vue'



import AuditDetailsModal

from '@/components/audit/AuditDetailsModal.vue'

import '@/assets/css/audit/audit-table.css'

import '@/assets/css/audit/audit-details.css'

import '@/assets/css/audit/audit-page.css'

import '@/assets/css/audit/audit-filter.css'

import '@/assets/css/audit/audit-compact.css'


const logs = ref([])


const loading = ref(false)


const selectedLog = ref(null)


const showDetails = ref(false)





const filters = reactive({

    search:'',

    module:'',

    action:'',

    per_page:20

})







async function loadLogs()

{


    loading.value=true


    try{


        const response =

            await auditLogService.getLogs(

                filters

            )



        logs.value =

            response.data.data



    }


    catch(error)

    {

        console.error(error)

    }


    finally

    {

        loading.value=false

    }


}







function viewDetails(log)

{

    selectedLog.value = log


    showDetails.value=true

}







onMounted(()=>{


    loadLogs()


})

</script>