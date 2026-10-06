<script setup>
import SubscriptionListPage from '@/components/super-admin/SubscriptionListPage.vue'
</script>

<template>
  <SubscriptionListPage
    type="expired"
    title="Expired Subscriptions"
    description="Review restaurant subscriptions whose current subscription period has expired."
    icon="bi bi-clock-history"
  />
</template>
