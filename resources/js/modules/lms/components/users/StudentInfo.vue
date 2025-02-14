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
          :model-value="formatDate(student.person?.birth_date)"
        />

        <!-- Ссылка для входа -->
        <login-link-display :link="student.loginLink" />
      </div>
</template>

<script setup>
import { date } from 'quasar'
import { ref } from 'vue'
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