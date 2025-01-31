<template>
  <q-card class="activity-card q-mb-md">
    <q-card-section>
      <div class="row">
        <div class="col-10 col-md-6">
          <div class="text-h6">{{ activity.name }}</div>
          <div class="text-subtitle2">{{ activity.starting_at }}</div>
        </div>
        <div class="col-2 col-md-6 text-right">
          <div 
            class="row items-center q-gutter-sm" 
            v-if="activity.starting_at && new Date(activity.starting_at) <= new Date()"
          > 
            <q-btn
              :icon="getActionIcon"
              :color="getActionColor"
              :disable="isFinished"
              round
              @click="handleActivityAction"
            >
            <q-tooltip>{{ getActionTooltip }}</q-tooltip>
            </q-btn>
            <q-btn
              v-if="isFinished"
              icon="restart_alt"
              color="warning"
              round
              @click="confirmRestart"
            >
            <q-tooltip>Перезапустить занятие</q-tooltip>
            </q-btn>
          </div>
        </div>
      </div>
    </q-card-section>

    <!-- Диалог для начала/завершения занятия -->
    <q-dialog v-model="dialog.show">
      <q-card style="min-width: 350px">
        <q-card-section class="row items-center">
          <div class="text-h6">{{ dialog.title }}</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section>
          <!-- Время начала -->
          <q-input
            filled
            v-model="dialog.startTime"
            label="Время начала"
            type="time"
            :readonly="isStarted"
            class="q-mb-md"
          />
          
          <!-- Время завершения (только для завершения занятия) -->
          <q-input
            v-if="isStarted"
            filled
            v-model="dialog.endTime"
            label="Время завершения"
            type="time"
          />
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Отмена" color="primary" v-close-popup />
          <q-btn 
            flat 
            :label="dialog.actionLabel" 
            color="primary" 
            @click="isStarted ? stopActivity() : startActivity()" 
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-card>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useQuasar } from 'quasar'
import axios from 'axios'

const $q = useQuasar()

const props = defineProps({
  activity: {
    type: Object,
    required: true
  }
})

const isStarted = computed(() => !!props.activity.started_at)
const isFinished = computed(() => !!props.activity.finished_at)

// Вычисляемые свойства для кнопки
const getActionIcon = computed(() => {
  if (isFinished.value) return 'check_circle'
  return isStarted.value ? 'stop' : 'play_arrow'
})

const getActionColor = computed(() => {
  if (isFinished.value) return 'positive'
  return isStarted.value ? 'negative' : 'primary'
})

const getActionTooltip = computed(() => {
  if (isFinished.value) return 'Занятие завершено'
  return isStarted.value ? 'Завершить занятие' : 'Начать занятие'
})

const dialog = ref({
  show: false,
  title: '',
  startTime: '',
  endTime: '',
  actionLabel: ''
})

const formatTime = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return `${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}`
}

const handleActivityAction = () => {
  if (isFinished.value) return

  dialog.value = {
    show: true,
    title: isStarted.value ? 'Завершить занятие' : 'Начать занятие',
    startTime: isStarted.value 
      ? formatTime(props.activity.started_at)
      : formatTime(props.activity.starting_at),
    endTime: formatTime(new Date()),
    actionLabel: isStarted.value ? 'Завершить' : 'Начать'
  }
}

const startActivity = async () => {
  try {
    const response = await axios.post(`/activities/${props.activity.id}/start`)
    dialog.value.show = false
    // Обновить данные активности после успешного старта
    Object.assign(props.activity, response.data)
    
    $q.notify({
      type: 'positive',
      message: 'Занятие успешно начато',
      position: 'top-right'
    })
  } catch (error) {
    console.error('Ошибка при запуске активности:', error)
    $q.notify({
      type: 'negative',
      message: 'Ошибка при запуске занятия',
      position: 'top-right'
    })
  }
}

const stopActivity = async () => {
  try {
    const response = await axios.post(`/activities/${props.activity.id}/stop`)
    dialog.value.show = false
    // Обновить данные активности после успешного завершения
    Object.assign(props.activity, response.data.activity)
    
    $q.notify({
      type: 'positive',
      message: 'Занятие успешно завершено',
      position: 'top-right'
    })
  } catch (error) {
    console.error('Ошибка при завершении активности:', error)
    $q.notify({
      type: 'negative',
      message: 'Ошибка при завершении занятия',
      position: 'top-right'
    })
  }
}

const confirmRestart = () => {
  $q.dialog({
    title: 'Подтверждение',
    message: 'Вы действительно хотите перезапустить занятие? Это отменит все результаты и списания кредитов.',
    cancel: true,
    persistent: true,
    ok: {
      label: 'Перезапустить',
      color: 'warning'
    },
    cancel: {
      label: 'Отмена',
      color: 'primary'
    }
  }).onOk(restartActivity)
}

const restartActivity = async () => {
  try {
    const response = await axios.post(`/activities/${props.activity.id}/restart`)
    // Обновить данные активности после успешного перезапуска
    Object.assign(props.activity, response.data.activity)
    
    $q.notify({
      type: 'positive',
      message: 'Занятие успешно перезапущено',
      position: 'top-right'
    })
  } catch (error) {
    console.error('Ошибка при перезапуске активности:', error)
    $q.notify({
      type: 'negative',
      message: 'Ошибка при перезапуске занятия',
      position: 'top-right'
    })
  }
}
</script>

<style scoped>
.activity-card {
  width: 100%;
}
</style>