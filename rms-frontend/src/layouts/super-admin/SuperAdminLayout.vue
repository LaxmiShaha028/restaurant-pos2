<script setup>

import SuperAdminNavbar
  from '@/components/super-admin/SuperAdminNavbar.vue'

</script>


<template>

  <div class="super-admin-layout">

    <SuperAdminNavbar />

    <main class="super-admin-main">

      <div class="super-admin-page-container">

        <router-view />

      </div>

    </main>

  </div>

</template>


<style
  src="@/styles/super-admin/layout.css"
></style>
