<template>
  <page-layout 
    title="Платежи"
    footer-text="Контакты"
    :left-btn-go-back="true"
  >
    <div class="q-pa-md">
      <!-- Фильтр по пользователю -->
      <q-select
        v-model="selectedUser"
        :options="students"
        option-label="name"
        label="Выберите ученика"
        clearable
        class="q-mb-md"
      />

      <!-- Список платежей -->
      <q-list bordered separator>
        <q-item
          v-for="payment in filteredPayments"
          :key="payment.id"
          clickable
          @click="showPaymentDetails(payment)"
        >
          <q-item-section>
            <q-item-label>{{ payment.user.name }}</q-item-label>
            <q-item-label caption>
              Дата: {{ formatDate(payment.payment_at) }}
            </q-item-label>
          </q-item-section>
          <q-item-section side>
            {{ payment.amount }} ₽
          </q-item-section>
        </q-item>
      </q-list>
    </div>

    <!-- Диалог с деталями платежа -->
    <q-dialog v-model="showDialog">
      <q-card style="min-width: 350px">
        <q-card-section class="row items-center">
          <div class="text-h6">Информация о платеже</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section v-if="selectedPayment">
          <div class="q-gutter-y-md">
            <div>
              <div class="text-subtitle2">Ученик</div>
              <div>{{ selectedPayment.user.name }}</div>
            </div>
            <div>
              <div class="text-subtitle2">Сумма</div>
              <div>{{ selectedPayment.amount }} ₽</div>
            </div>
            <div>
              <div class="text-subtitle2">Дата платежа</div>
              <div>{{ formatDate(selectedPayment.payment_at) }}</div>
            </div>
            <div>
              <div class="text-subtitle2">Способ оплаты</div>
              <div>{{ selectedPayment.pay_from }}</div>
            </div>
            <div v-if="selectedPayment.description">
              <div class="text-subtitle2">Описание</div>
              <div>{{ selectedPayment.description }}</div>
            </div>
          </div>
        </q-card-section>
      </q-card>
    </q-dialog>
  </page-layout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { date } from 'quasar'

const props = defineProps({
  payments: {
    type: Array,
    required: true
  },
  students: {
    type: Array,
    required: true
  }
})

const selectedUser = ref(null)
const showDialog = ref(false)
const selectedPayment = ref(null)

const filteredPayments = computed(() => {
  if (!selectedUser.value) return props.payments
  return props.payments.filter(payment => payment.user_id === selectedUser.value.id)
})

const formatDate = (dateString) => {
  return date.formatDate(dateString, 'DD.MM.YYYY HH:mm')
}

const showPaymentDetails = (payment) => {
  selectedPayment.value = payment
  showDialog.value = true
}
</script> 