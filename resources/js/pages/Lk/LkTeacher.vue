<template>
  <page-layout-home 
    title="ROBOT 04"
    footer-text="Контакты"
  >
    <!-- Панель с именем преподавателя -->
    <div class="text-h5 q-my-xl text-center">
      {{ teacher.name }}
    </div>

    <!-- Сетка кнопок -->
    <div class="row q-col-gutter-md justify-center">
      <!-- Новый ученик -->
      <div class="col-6 col-sm-3">
        <q-btn
          class="full-width"
          color="primary"
          stack
          label="Новый ученик"
          icon="fa fa-user-plus"
          @click="showNewStudentDialog = true"
        />
          
      </div>

      <!-- Новая оплата -->
      <div class="col-6 col-sm-3">
        <q-btn
          class="full-width"
          color="positive"
          stack
          label="Новая оплата"
          icon="fa fa-money-bill-wave"
          @click="showNewPaymentDialog = true"
        />
      </div>

    </div>

    <!-- Диалоговые окна -->
    <q-dialog v-model="showNewStudentDialog">
      <q-card class="full-width" style="max-width: 900px; margin: 20px;">
        <q-card-section class="row items-center">
          <div class="text-h6">Новый ученик</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section class="q-pa-sm">
          <user-edit
            :teams="teams"
            @saved="onUserSaved"
            @cancelled="showNewStudentDialog = false"
          />
        </q-card-section>
      </q-card>
    </q-dialog>

    <q-dialog v-model="showTransferStudentDialog">
      <transfer-student-dialog
        :teams="teams"
        :students="students"
      />
    </q-dialog>

    <q-dialog v-model="showNewPaymentDialog">
      <new-payment-dialog
        :teams="teams"
        :students="students"
        :payments="payments"
        :users="students"
        @saved="showNewPaymentDialog = false"
      />
    </q-dialog>

    <q-dialog v-model="showSubscriptionDialog">
      <subscription-dialog
        :teams="teams"
        :students="students"
        :payments="payments"
      />
    </q-dialog>

    <!-- Дополнительные кнопки -->
    <div class="row q-col-gutter-md justify-center q-mt-lg">
      <!-- Ученики -->
      <div class="col-12 col-sm-3">
        <q-btn
          class="full-width"
          color="deep-orange"
          stack
          label="Ученики"
          icon="fa fa-users"
          @click="router.visit(route('teacher.students'))"
        />
      </div>

      <!-- Группы -->
      <div class="col-12 col-sm-3">
        <q-btn
          class="full-width"
          color="purple"
          stack
          label="Группы"
          icon="fa fa-user-friends"
          @click="router.visit(route('teacher.teams'))"
        />
      </div>

      <!-- Все занятия -->
      <div class="col-12 col-sm-3">
        <q-btn
          class="full-width"
          color="blue-grey"
          stack
          label="Все занятия"
          icon="fa fa-calendar-check"
          @click="router.visit(route('activities.all'))"
        />
      </div>
      <!-- Расписание -->
      <div class="col-12 col-sm-3">
        <q-btn
          class="full-width"
          color="teal"
          stack
          label="Расписание"
          icon="fa fa-calendar-alt"
          @click="navigateToAllSchedules"
        />
      </div>

      <!-- Отчеты -->
      <div class="col-12 col-sm-3">
        <q-btn
          class="full-width"
          color="brown"
          stack
          label="Платежи"
          icon="fa fa-money-bill-alt"
          @click="router.visit(route('teacher.payments'))"
        />
      </div>

    </div>

    <!-- Ссылка Выйти -->
    <div class="row justify-center q-mt-xl">
      <q-btn
        flat
        color="grey-7"
        label="Выйти"
        icon="fa fa-sign-out-alt"
        @click="logout"
      />
    </div>
  </page-layout-home>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import UserEdit from '@/modules/lms/components/users/UserEdit.vue'
import TransferStudentDialog from '@/modules/lms/components/lk/teacher/TransferStudentDialog.vue'
import NewPaymentDialog from '@/modules/lms/components/lk/teacher/NewPaymentDialog.vue'
import SubscriptionDialog from '@/modules/lms/components/lk/teacher/SubscriptionDialog.vue'

const showNewStudentDialog = ref(false)
const showTransferStudentDialog = ref(false)
const showNewPaymentDialog = ref(false)
const showSubscriptionDialog = ref(false)

const props = defineProps({
  teams: {
    type: Array,
    required: true
  },
  students: {
    type: Array,
    required: true
  },
  activities: {
    type: Array,
    required: true
  },
  payments: {
    type: Array,
    required: true
  },
  teacher: {
    type: Object,
    required: true
  }
})

const onUserSaved = () => {
  showNewStudentDialog.value = false;
  // Обновляем список учеников с сервера
  router.reload({ only: ['students'] });
}

// Функция для навигации к расписаниям всех групп
const navigateToAllSchedules = () => {
  router.visit(route('teams.schedules.all.view'))
}

const logout = () => {
  router.post(route('logout'))
}
</script> 