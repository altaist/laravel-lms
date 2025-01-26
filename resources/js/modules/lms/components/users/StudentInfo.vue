<template>
      <div class="q-gutter-y-md q-mt-md">
        <div class="row q-col-gutter-md">
          <div class="col-12 col-sm-6">
            <q-input
              readonly
              outlined
              dense
              label="Фамилия"
              :model-value="student.person?.lastName"
            />
          </div>
          <div class="col-12 col-sm-6">
            <q-input
              readonly
              outlined
              dense
              label="Имя"
              :model-value="student.person?.firstName"
            />
          </div>
        </div>

        <q-input
          readonly
          outlined
          dense
          label="ФИО родителя"
          :model-value="student.person?.parentFio"
        />

        <q-input
          readonly
          outlined
          dense
          label="Телефон родителя"
          :model-value="formatPhone(student.person?.parentTel)"
        >
          <template v-slot:append>
            <q-btn
              flat
              round
              icon="phone"
              color="primary"
              @click="callPhone(student.person?.parentTel)"
            />
          </template>
        </q-input>

        <div class="row q-col-gutter-md">
          <div class="col-12 col-sm-6">
            <q-input
              readonly
              outlined
              dense
              label="Пол"
              :model-value="student.person?.gender === 'M' ? 'Мужской' : 'Женский'"
            />
          </div>
          <div class="col-12 col-sm-6">
            <q-input
              readonly
              outlined
              dense
              label="Смена"
              :model-value="student.person?.shift"
            />
          </div>
        </div>

        <q-input
          readonly
          outlined
          dense
          label="Дата рождения"
          :model-value="formatDate(student.person?.birthDate)"
        />
      </div>
</template>

<script setup>
import { date } from 'quasar'

const props = defineProps({
  student: {
    type: Object,
    required: true
  }
})

// Функция форматирования телефона
const formatPhone = (phone) => {
  if (!phone) return '';
  return `+7 ${phone}`;
}

// Функция форматирования даты
const formatDate = (dateStr) => {
  if (!dateStr) return '';
  return date.formatDate(dateStr, 'DD.MM.YYYY');
}

// Функция для звонка
const callPhone = (phone) => {
  if (!phone) return;
  window.location.href = `tel:+7${phone}`;
}
</script> 