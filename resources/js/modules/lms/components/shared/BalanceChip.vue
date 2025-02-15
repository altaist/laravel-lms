<template>
  <q-chip
    :color="getBalanceColor(balance)"
    text-color="white"
    size="md"
    class="text-weight-bold"
    rounded
    v-if="viewMode==1"
  >
    {{ balance }}
  </q-chip>
  <div v-else>
    {{ balance }}
  </div>

</template>

<script setup>
const props = defineProps({
  balance: {
    type: Number,
    default: 0
  },
  viewMode: {
    typee: Number,
    default: 1
  }
})

const getBalanceColor = (balance) => {
  if (balance > 3) return 'secondary'
  if (balance >= 1) return 'orange'
  if (balance > 0) return 'negative'
  return 'negative'
}
</script> 