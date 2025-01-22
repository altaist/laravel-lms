<template>
  <page-layout 
    title="Платежи"
    footer-text="Контакты"
    :left-btn-go-back="true"
  >
    <div class="q-pa-md">
      <!-- Фильтр по группе -->
      <q-select
        v-model="selectedTeam"
        :options="teams"
        option-label="name"
        label="Группа"
        dense
        clearable
        class="q-mb-md"
      />

      <!-- Поиск по имени -->
      <q-input
        v-model="searchQuery"
        label="Поиск по имени"
        dense
        clearable
        class="q-mb-md"
      >
        <template v-slot:append>
          <q-icon name="search" />
        </template>
      </q-input>

      <!-- Фильтр по дате -->
      <q-select
        v-model="selectedDateFilter"
        :options="dateFilterOptions"
        option-label="label"
        option-value="value"
        label="Период"
        dense
        class="q-mb-md"
        @update:model-value="updateDateFilter"
      />

      <!-- Фильтр по пользователю -->
      <q-select
        v-if="filteredStudents.length > 0 && searchQuery"
        v-model="selectedUser"
        :options="filteredStudents"
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
              {{ payment.amount }} ₽ - {{ payment.pay_from }}
            </q-item-label>
          </q-item-section>
          <q-item-section side>
            {{ formatDateShort(payment.payment_at) }}
          </q-item-section>
        </q-item>
      </q-list>

      <!-- Сообщение, если платежей нет -->
      <div v-if="filteredPayments.length === 0" class="text-center q-pa-md">
        Платежи не найдены
      </div>
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
  },
  teams: {
    type: Array,
    required: true
  }
})

const selectedTeam = ref(null)
const searchQuery = ref('')
const selectedUser = ref(null)
const showDialog = ref(false)
const selectedPayment = ref(null)
const dateFilterOptions = [
    { label: 'Все', value: 'all' },
    { label: 'Сегодня', value: 'today' },
  { label: 'Вчера', value: 'yesterday' },
  { label: 'Завтра', value: 'tomorrow' },
  { label: 'Эта неделя', value: 'week' },
  { label: 'Этот месяц', value: 'month' },
  { label: 'Последние 30 дней', value: '30days' }
]
const selectedDateFilter = ref(dateFilterOptions[0])

// Фильтрация студентов по поисковому запросу
const filteredStudents = computed(() => {
  if (!searchQuery.value) return props.students
  const query = searchQuery.value.toLowerCase()
  return props.students.filter(student => 
    student.name.toLowerCase().includes(query)
  )
})

// Обработчик изменения фильтра даты
const updateDateFilter = (value) => {
  selectedDateFilter.value = value
}

// Функция для проверки даты платежа
const isPaymentInPeriod = (paymentDate, period) => {
  const today = new Date()
  const paymentDateTime = new Date(paymentDate)
  
  switch (period.value) {
    case 'today': {
      const startOfDay = new Date(today.getFullYear(), today.getMonth(), today.getDate())
      const endOfDay = new Date(today.getFullYear(), today.getMonth(), today.getDate(), 23, 59, 59)
      return paymentDateTime >= startOfDay && paymentDateTime <= endOfDay
    }
    
    case 'yesterday': {
      const yesterday = new Date(today)
      yesterday.setDate(today.getDate() - 1)
      const startOfDay = new Date(yesterday.getFullYear(), yesterday.getMonth(), yesterday.getDate())
      const endOfDay = new Date(yesterday.getFullYear(), yesterday.getMonth(), yesterday.getDate(), 23, 59, 59)
      return paymentDateTime >= startOfDay && paymentDateTime <= endOfDay
    }
    
    case 'tomorrow': {
      const tomorrow = new Date(today)
      tomorrow.setDate(today.getDate() + 1)
      const startOfDay = new Date(tomorrow.getFullYear(), tomorrow.getMonth(), tomorrow.getDate())
      const endOfDay = new Date(tomorrow.getFullYear(), tomorrow.getMonth(), tomorrow.getDate(), 23, 59, 59)
      return paymentDateTime >= startOfDay && paymentDateTime <= endOfDay
    }
    
    case 'week': {
      const startOfWeek = new Date(today)
      startOfWeek.setDate(today.getDate() - today.getDay())
      startOfWeek.setHours(0, 0, 0, 0)
      const endOfWeek = new Date(startOfWeek)
      endOfWeek.setDate(startOfWeek.getDate() + 6)
      endOfWeek.setHours(23, 59, 59, 999)
      return paymentDateTime >= startOfWeek && paymentDateTime <= endOfWeek
    }
    
    case 'month': {
      const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1)
      const endOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0, 23, 59, 59)
      return paymentDateTime >= startOfMonth && paymentDateTime <= endOfMonth
    }
    
    case '30days': {
      const thirtyDaysAgo = new Date(today)
      thirtyDaysAgo.setDate(today.getDate() - 30)
      thirtyDaysAgo.setHours(0, 0, 0, 0)
      const endOfToday = new Date(today)
      endOfToday.setHours(23, 59, 59, 999)
      return paymentDateTime >= thirtyDaysAgo && paymentDateTime <= endOfToday
    }
    
    default: // 'all'
      return true
  }
}

// Фильтрация платежей по всем критериям
const filteredPayments = computed(() => {
  let result = [...props.payments]
  
  // Фильтрация по группе
  if (selectedTeam.value) {
    result = result.filter(payment => 
      payment.user?.teams?.some(team => team.id === selectedTeam.value.id)
    )
  }
  
  // Фильтрация по поисковому запросу
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(payment => 
      payment.user?.name?.toLowerCase().includes(query)
    )
  }
  
  // Фильтрация по выбранному пользователю
  if (selectedUser.value) {
    result = result.filter(payment => 
      payment.user_id === selectedUser.value.id
    )
  }
  
  // Фильтрация по дате
  if (selectedDateFilter.value !== 'all') {
    result = result.filter(payment => 
      isPaymentInPeriod(payment.payment_at, selectedDateFilter.value)
    )
  }
  
  return result
})

// Форматирование даты без времени
const formatDateShort = (dateString) => {
  return date.formatDate(dateString, 'DD.MM.YYYY')
}

// Форматирование даты с временем (для диалога)
const formatDate = (dateString) => {
  return date.formatDate(dateString, 'DD.MM.YYYY HH:mm')
}

const showPaymentDetails = (payment) => {
  selectedPayment.value = payment
  showDialog.value = true
}
</script> 