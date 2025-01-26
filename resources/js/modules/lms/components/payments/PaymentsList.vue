<template>
  <!-- Список платежей -->
  
  <q-list bordered separator>
    <q-item
      v-for="payment in payments"
      :key="payment.id"
      clickable
      @click="showPaymentDetails(payment)"
    >
      <q-item-section>
        <q-item-label>{{ payment.user.name }}</q-item-label>
        <q-item-label caption>
          {{ payment.amount }} ₽ - {{ payment.pay_from }}
        </q-item-label>
      </q-item-section>
      <q-item-section side>
        {{ formatDateShort(payment.payment_at) }}
      </q-item-section>
    </q-item>
  </q-list>

  <!-- Сообщение, если платежей нет -->
  <div v-if="payments.length === 0" class="text-center q-pa-md">
    Платежи не найдены
  </div>

  <!-- Диалог с деталями платежа -->
  <q-dialog v-model="showDialog" persistent :max-width="'90vw'" :width="'90vw'">
    <q-card>
      <q-card-section class="row items-center">
        <div class="text-h6">Информация о платеже</div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>

      <q-card-section v-if="selectedPayment">
        <div class="q-gutter-y-md">
          <div class="row q-col-gutter-md">
            <div class="col-12 col-sm-6">
              <div class="text-subtitle2">Ученик</div>
              <div>{{ selectedPayment.user.name }}</div>
            </div>
            <div class="col-12 col-sm-6">
              <div class="text-subtitle2">Сумма</div>
              <div>{{ selectedPayment.amount }} ₽</div>
            </div>
            <div class="col-12 col-sm-6">
              <div class="text-subtitle2">Дата платежа</div>
              <div>{{ formatDate(selectedPayment.payment_at) }}</div>
            </div>
            <div class="col-12 col-sm-6">
              <div class="text-subtitle2">Способ оплаты</div>
              <div>{{ selectedPayment.pay_from }}</div>
            </div>
            <div class="col-12" v-if="selectedPayment.description">
              <div class="text-subtitle2">Описание</div>
              <div>{{ selectedPayment.description }}</div>
            </div>
          </div>
        </div>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref } from 'vue'
import { date } from 'quasar'

const props = defineProps({
  payments: {
    type: Array,
    required: true
  }
})

const showDialog = ref(false)
const selectedPayment = ref(null)

const formatDateShort = (dateString) => {
  return date.formatDate(dateString, 'DD.MM.YYYY')
}

const formatDate = (dateString) => {
  return date.formatDate(dateString, 'DD.MM.YYYY HH:mm')
}

const showPaymentDetails = (payment) => {
  selectedPayment.value = payment
  showDialog.value = true
}
</script> 