<template>
      <div class="q-gutter-y-md q-mt-md">
        <div class="row q-col-gutter-md">
          <div class="col-12 col-sm-6">
            <q-input
              readonly
              outlined
              dense
              label="Фамилия"
              :model-value="student.person?.last_name"
            />
          </div>
          <div class="col-12 col-sm-6">
            <q-input
              readonly
              outlined
              dense
              label="Имя"
              :model-value="student.person?.first_name"
            />
          </div>
        </div>

        <q-input
          readonly
          outlined
          dense
          label="ФИО родителя"
          :model-value="student.person?.parent_fio"
        />

        <q-input
          readonly
          outlined
          dense
          label="Телефон родителя"
          :model-value="formatPhone(student.person?.parent_tel)"
        >
          <template v-slot:append>
            <q-btn
              flat
              round
              icon="phone"
              color="primary"
              @click="callPhone(student.person?.parent_tel)"
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
              :model-value="student.person?.gender === 'male' ? 'Мужской' : 'Женский'"
            />
          </div>
          <div class="col-12 col-sm-6">
            <q-input
              readonly
              outlined
              dense
              label="Смена"
              :model-value="student.person?.shift || student.person?.shift_comment"
            />
          </div>
        </div>

        <q-input
          readonly
          outlined
          dense
          label="Дата рождения"
          :model-value="formatDate(student.person?.birth_date)"
        />

        <div class="row q-col-gutter-md">
          <div class="col-12 col-sm-6">
            <q-input
              readonly
              outlined
              dense
              label="Номер карты"
              :model-value="student.card_number"
            />
          </div>
          <div class="col-12 col-sm-6">
            <q-input
              readonly
              outlined
              dense
              label="Статус карты"
              :model-value="getCardStatus"
            />
          </div>
        </div>

        <!-- Ссылка для входа -->
        <login-link-display :link="student.loginLink" />
      </div>
</template>

<script setup>
import { date } from 'quasar'
import { ref, computed } from 'vue'
import { useQuasar } from 'quasar'
import LoginLinkDisplay from '@/modules/lms/components/common/LoginLinkDisplay.vue'

const props = defineProps({
  student: {
    type: Object,
    required: true
  }
})

const $q = useQuasar()

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

// Обработчик события генерации ссылки
const onLinkGenerated = (link) => {
  props.student.loginLink = link
}

const getCardStatus = computed(() => {
  if (!props.student.card_number) return 'Не выдана'
  return props.student.card_delivered_at ? 'Активна' : 'Деактивирована'
})

defineExpose({
  onLinkGenerated
})
</script>

<style scoped>
.ellipsis {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style> 