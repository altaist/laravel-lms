<template>
  <q-card class="full-width q-pa-sm" style="max-width: 900px">
    <q-card-section class="row items-center">
      <div class="text-h6">История начислений</div>
      <q-space />

      <q-btn icon="close" flat round dense v-close-popup />
    </q-card-section>
    <q-card-section>
      <q-select
        v-model="selectedCoin"
        :options="coinOptions"
        label="Фильтр"
        dense
        options-dense
        class="q-mr-md"
        clearable
        emit-value
        map-options
      />
    </q-card-section>

    <q-card-section class="q-pa-none">
      <q-list separator>
        <q-item v-for="credit in filteredCredits" :key="credit.id">
          <q-item-section>
            <q-item-label class="text-subtitle1">
              {{ credit.reason?.name }}
            </q-item-label>
            <q-item-label caption>
              {{ credit.description }}
            </q-item-label>
            <q-item-label caption>
            </q-item-label>
            <div class="text-caption">
              {{ formatDate(credit.created_at) }}
            </div>
          </q-item-section>
          <q-item-section side>
            <div class="text-right">
              <q-chip
                :color="credit.amount >= 0 ? 'positive' : 'negative'"
                text-color="white"
                class="q-mb-xs"
              >
                {{ credit.amount >= 0 ? '+' : '' }}{{ credit.amount }}
              </q-chip>
            </div>
            <div class="text-caption">
              {{ credit.coin?.name || 'Без описания коина' }}
            </div>
          </q-item-section>
        </q-item>
        <q-item v-if="!filteredCredits?.length">
          <q-item-section>
            <q-item-label class="text-grey text-center">
              Нет начислений
            </q-item-label>
          </q-item-section>
        </q-item>
      </q-list>
    </q-card-section>
  </q-card>
</template>

<script setup>
import { ref, computed } from 'vue';
import { date } from 'quasar';

const props = defineProps({
  credits: {
    type: Array,
    required: true,
    default: () => []
  }
});

const selectedCoin = ref(null);

// Получаем уникальные коины из истории начислений
const coinOptions = computed(() => {
  const uniqueCoins = new Map();
  
  props.credits.forEach(credit => {
    if (credit.coin && !uniqueCoins.has(credit.coin.id)) {
      uniqueCoins.set(credit.coin.id, {
        label: credit.coin.name,
        value: credit.coin.id
      });
    }
  });
  
  return Array.from(uniqueCoins.values());
});

// Фильтруем начисления по выбранному коину
const filteredCredits = computed(() => {
  if (!selectedCoin.value) return props.credits;
  return props.credits.filter(credit => credit.coin?.id === selectedCoin.value);
});

const formatDate = (dateStr) => {
  return date.formatDate(dateStr, 'DD.MM.YYYY HH:mm');
};
</script> 