<template>
  <page-layout
    title="Студент"
    right-btn-icon="fa-solid fa-pen"
    @click:header:right="showEditDialog = true"
  >
    <!-- Основная информация о студенте -->
    <q-card class="q-mb-md">
      <q-card-section>
        <div class="row items-center q-col-gutter-md">
          <div class="col-12 col-sm-8">
            <div class="text-h5">{{ student.person?.firstName || student.name }} {{ student.person?.lastName || '' }}</div>
            <div class="text-subtitle2 q-mt-sm">{{ formatPhone(student.person?.parentTel) }}</div>
          </div>
          <div class="col-12 col-sm-4 text-right">
            <balance-chip
              :balance="getBalance(student)"
            />
          </div>
        </div>
      </q-card-section>
    </q-card>

    <!-- Информация о студенте -->
    <q-card class="q-mb-md">
      <q-card-section>
        <div class="text-h6 q-mb-md">Информация</div>
        <student-info
          ref="studentInfo"
          :student="student"
        />
      </q-card-section>
    </q-card>

    <!-- Группы -->
    <q-card class="q-mb-md">
      <q-card-section>
        <div class="text-h6 q-mb-md">Группы</div>
        <q-list separator>
          <q-item
            v-for="team in student.teams"
            :key="team.id"
            class="q-py-md"
          >
            <q-item-section>
              <q-item-label class="text-h6">{{ team.name }}</q-item-label>
              <q-item-label caption>
                <div v-if="team.scheduleDays && team.scheduleDays.length">
                  <div v-for="day in team.scheduleDays" :key="day.id">
                    {{ formatScheduleDay(day) }}
                  </div>
                </div>
                <div v-else class="text-grey">Расписание не задано</div>
              </q-item-label>
            </q-item-section>
          </q-item>
          <q-item v-if="!student.teams?.length">
            <q-item-section>
              <q-item-label class="text-grey text-center">
                Вы не состоите ни в одной группе
              </q-item-label>
            </q-item-section>
          </q-item>
        </q-list>
      </q-card-section>
    </q-card>

    <!-- Платежи -->
    <q-card class="q-mb-md">
      <q-card-section>
        <div class="text-h6 q-mb-md">Платежи</div>
        <payments-list
          :payments="student.payments"
        />
      </q-card-section>
    </q-card>

    <!-- История начислений -->
    <q-card class="q-mb-md">
      <q-card-section>
        <div class="text-h6 q-mb-md">История начислений</div>
        <q-list separator>
          <q-item v-for="credit in student.credits" :key="credit.id">
            <q-item-section>
              <q-item-label>{{ credit.description }}</q-item-label>
              <q-item-label caption>
                {{ formatDate(credit.created_at) }}
              </q-item-label>
            </q-item-section>
            <q-item-section side>
              <div :class="credit.amount >= 0 ? 'text-positive' : 'text-negative'">
                {{ credit.amount > 0 ? '+' : '' }}{{ credit.amount }}
              </div>
            </q-item-section>
          </q-item>
          <q-item v-if="!student.credits?.length">
            <q-item-section>
              <q-item-label class="text-grey text-center">
                История начислений пуста
              </q-item-label>
            </q-item-section>
          </q-item>
        </q-list>
      </q-card-section>
    </q-card>

    <!-- Диалог редактирования -->
    <q-dialog v-model="showEditDialog">
      <q-card class="full-width" style="max-width: 900px; margin: 20px;">
        <q-card-section class="row items-center">
          <div class="text-h6">Редактировать</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section class="q-pa-sm">
          <user-edit
            :user="student"
            :teams="[]"
            :can="$page.props.can"
            @saved="onUserEdited"
            @cancelled="showEditDialog = false"
            @linkGenerated="onLoginLinkGenerated"
          />
        </q-card-section>
      </q-card>
    </q-dialog>

  </page-layout>
</template>

<script setup>
import { ref } from 'vue'
import { date } from 'quasar'
import StudentInfo from '@/modules/lms/components/users/StudentInfo.vue'
import PaymentsList from '@/modules/lms/components/payments/PaymentsList.vue'
import BalanceChip from '@/modules/lms/components/shared/BalanceChip.vue'
import UserEdit from '@/modules/lms/components/users/UserEdit.vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  student: {
    type: Object,
    required: true
  }
})

const showEditDialog = ref(false)
const studentInfo = ref(null)

// Получение баланса
const getBalance = (student) => {
  const balance = student.balances?.find(b => b.coin_id === 2)
  return balance ? balance.amount : 0
}

// Форматирование телефона
const formatPhone = (phone) => {
  if (!phone) return ''
  return `+7 ${phone}`
}

// Форматирование даты
const formatDate = (dateStr) => {
  if (!dateStr) return ''
  return date.formatDate(dateStr, 'DD.MM.YYYY HH:mm')
}

// Форматирование дня расписания
const formatScheduleDay = (day) => {
  const weekDays = ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота']
  return `${weekDays[day.week_day]}: ${day.time}`
}

const onUserEdited = () => {
  showEditDialog.value = false
  router.reload({ only: ['student'] })
}

const onLoginLinkGenerated = (link) => {
  if (studentInfo.value) {
    studentInfo.value.onLinkGenerated(link)
  }
}
</script>

<style scoped>
.balance-section {
  border-left: 1px solid #ddd;
}

@media (max-width: 600px) {
  .balance-section {
    border-left: none;
    border-top: 1px solid #ddd;
    padding-top: 1rem;
    margin-top: 1rem;
  }
}
</style> 