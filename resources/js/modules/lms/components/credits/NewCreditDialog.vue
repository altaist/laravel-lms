<template>
  <q-card class="new-credit-dialog full-width" >
    <q-card-section class="row items-center">
      <div class="text-h6">Новое начисление</div>
      <q-space />
      <q-btn icon="close" flat round dense v-close-popup />
    </q-card-section>

    <q-card-section>
      <q-form @submit.prevent="onSubmit">
        <div class="q-gutter-md">
          <q-input
            v-model.number="form.amount"
            type="number"
            label="Сумма"
            :rules="[val => !!val || 'Обязательное поле']"
          />

          <q-select
            v-model="form.coin_id"
            :options="coinsOptions"
            label="Валюта"
            emit-value
            map-options
            :rules="[val => !!val || 'Обязательное поле']"
          />

          <q-input
            v-model="form.description"
            type="textarea"
            label="Описание"
          />
        </div>

        <div class="row justify-end q-mt-md">
          <q-btn
            label="Отмена"
            flat
            v-close-popup
            class="q-mr-sm"
          />
          <q-btn
            label="Сохранить"
            type="submit"
            color="primary"
            :loading="loading"
          />
        </div>
      </q-form>
    </q-card-section>
  </q-card>
</template>

<script setup>
import { ref, computed, reactive } from 'vue'
import axios from 'axios'
import { COINS, DEFAULT_COIN_ID } from '@/modules/lms/constants/coins'
import { Notify } from 'quasar'

const props = defineProps({
  user: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['saved'])
const loading = ref(false)

const coinsOptions = computed(() => {
  return COINS.map(coin => ({
    label: coin.name,
    value: coin.id
  }))
})

const form = reactive({
  user_id: props.user.id,
  amount: null,
  coin_id: DEFAULT_COIN_ID,
  description: '',
  reason_id: 6 // MANUAL_ADJUSTMENT из CreditReasonEnum
})

const onSubmit = async () => {
  if (!form.amount || !form.coin_id) {
    Notify.create({
      type: 'negative',
      message: 'Заполните обязательные поля'
    })
    return
  }

  loading.value = true
  try {
    await axios.post(route('credits.manual'), form)
    
    Notify.create({
      type: 'positive',
      message: 'Начисление успешно создано'
    })
    
    emit('saved')
  } catch (error) {
    console.error('Ошибка при создании начисления:', error)
    Notify.create({
      type: 'negative',
      message: 'Ошибка при создании начисления'
    })
  } finally {
    loading.value = false
  }
}
</script> 