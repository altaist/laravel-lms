<template>
  <q-chip
    :color="getBalanceColor(balance)"
    text-color="white"
    size="lg"
    class="text-weight-bold"
    rounded
  >
    {{ balance }}
  </q-chip>
</template>

<script setup>
const props = defineProps({
  balance: {
    type: Number,
    default: 0
  }
})

const getBalanceColor = (balance) => {
  if (balance > 3) return 'grey'
  if (balance >= 1) return 'secondary'
  if (balance > 0) return 'orange'
  return 'negative'
}
</script> 