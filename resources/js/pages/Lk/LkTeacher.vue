<template>
  <page-layout 
    title="ПРИЛОЖЕНИЕ"
    footer-text="Контакты"
    :left-btn-go-back="true"

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

      <!-- Перенос ученика -->
      <div class="col-6 col-sm-3">
        <q-btn
          class="full-width"
          color="secondary"
          stack
          label="Перенос ученика"
          icon="fa fa-exchange-alt"
          @click="showTransferStudentDialog = true"
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

      <!-- Абонемент -->
      <div class="col-6 col-sm-3">
        <q-btn
          class="full-width"
          color="purple"
          stack
          label="Абонемент"
          icon="fa fa-id-card"
          @click="showSubscriptionDialog = true"
        />
      </div>
    </div>

    <!-- Диалоговые окна -->
    <q-dialog v-model="showNewStudentDialog">
      <new-student-dialog
        :teams="teams"
        :students="students"
        :payments="payments"
      />
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
      <div class="col-12 col-sm-4">
        <q-btn
          class="full-width"
          color="deep-orange"
          stack
          label="Ученики"
          icon="fa fa-users"
          @click="showStudentsList = true"
        />
      </div>

      <!-- Расписание -->
      <div class="col-12 col-sm-4">
        <q-btn
          class="full-width"
          color="teal"
          stack
          label="Расписание"
          icon="fa fa-calendar-alt"
        />
      </div>

      <!-- Отчеты -->
      <div class="col-12 col-sm-4">
        <q-btn
          class="full-width"
          color="brown"
          stack
          label="Отчеты"
          icon="fa fa-chart-bar"
        />
      </div>
    </div>

    <!-- Диалог для списка учеников -->
    <q-dialog v-model="showStudentsList" full-width>
      <q-card>
        <q-card-section class="row items-center">
          <div class="text-h6">Список учеников</div>
          <q-space />
          <q-btn icon="fa fa-times" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section>
          <students-list
            :students="students"
            :teams="teams"
          />
        </q-card-section>
      </q-card>
    </q-dialog>
  </page-layout>
</template>

<script setup>
import { ref } from 'vue'
import NewStudentDialog from '@/modules/lms/Components/lk/teacher/NewStudentDialog.vue'
import TransferStudentDialog from '@/modules/lms/Components/lk/teacher/TransferStudentDialog.vue'
import NewPaymentDialog from '@/modules/lms/Components/lk/teacher/NewPaymentDialog.vue'
import SubscriptionDialog from '@/modules/lms/Components/lk/teacher/SubscriptionDialog.vue'
import StudentsList from '@/modules/lms/Components/lk/teacher/StudentsList.vue'

const showNewStudentDialog = ref(false)
const showTransferStudentDialog = ref(false)
const showNewPaymentDialog = ref(false)
const showSubscriptionDialog = ref(false)
const showStudentsList = ref(false)

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
</script> 