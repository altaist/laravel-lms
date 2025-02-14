<template>
  <q-list separator>
    <q-item v-for="coin in coins" :key="coin.id">
      <q-item-section>
        <q-item-label>{{ coin.name }}</q-item-label>
      </q-item-section>
      <q-item-section side>
        <q-item-label>
          {{ getBalanceAmount(coin.id) }}
        </q-item-label>
      </q-item-section>
    </q-item>
  </q-list>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  student: {
    type: Object,
    required: true
  }
})

// Получаем все коины из props студента
const coins = computed(() => props.student?.coins || [])

// Функция для получения баланса по id коина
const getBalanceAmount = (coinId) => {
  const balance = props.student?.balances?.find(b => b.coin_id === coinId)
  return balance ? balance.amount : '—'
}
</script> 