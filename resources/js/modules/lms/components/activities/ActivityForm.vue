<template>
  <q-card style="min-width: 350px">
    <q-card-section>
      <div class="text-h6">{{ title }}</div>
    </q-card-section>

    <q-card-section>
      <q-select
        v-if="!isEditing"
        v-model="form.schedule_id"
        :options="scheduleDays"
        :option-label="(schedule) => schedule ? `${daysMap[schedule.day_of_week]}: ${formatTime(schedule.start_time)} - ${formatTime(schedule.end_time)}` : ''"
        option-value="id"
        label="Выберите расписание"
        class="q-mb-md"
        @update:model-value="onScheduleSelect"
      />
      <q-input
        v-model="form.name"
        label="Название *"
        :rules="[val => !!val || 'Название обязательно для заполнения']"
        class="q-mb-md"
      />
      <q-input
        v-model="form.starting_at"
        type="datetime-local"
        label="Дата и время начала"
        class="q-mb-md"
      />
      <q-input
        v-model="form.description"
        type="textarea"
        label="Описание (необязательно)"
      />
    </q-card-section>

    <q-card-actions align="right">
      <q-btn flat label="Отмена" @click="$emit('cancel')" />
      <q-btn flat label="Сохранить" @click="save" color="primary" />
    </q-card-actions>
  </q-card>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { date, useQuasar } from 'quasar'

const $q = useQuasar()

const props = defineProps({
  scheduleDays: {
    type: Array,
    default: () => [],
    required: false
  },
  title: {
    type: String,
    default: 'Добавить занятие'
  },
  activity: {
    type: Object,
    default: () => null
  }
})

const emit = defineEmits(['save', 'cancel'])

const isEditing = ref(false)

const daysMap = {
  1: 'Понедельник',
  2: 'Вторник',
  3: 'Среда',
  4: 'Четверг',
  5: 'Пятница',
  6: 'Суббота',
  7: 'Воскресенье'
}

const getDefaultStartTime = () => {
  const date = new Date()
  date.setHours(date.getHours() + 1)
  date.setMinutes(0)
  date.setSeconds(0)
  return date.toISOString().slice(0, 16)
}

const generateDefaultName = (startingAt) => {
  const dateObj = new Date(startingAt)
  const formattedDate = dateObj.toLocaleString('ru-RU', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
  return `Новое занятие ${formattedDate}`
}

const form = ref({
  starting_at: getDefaultStartTime(),
  name: '',
  description: '',
  schedule_id: null
})

onMounted(() => {
  if (props.activity) {
    isEditing.value = true
    form.value = {
      starting_at: props.activity.starting_at?.slice(0, 16) || getDefaultStartTime(),
      name: props.activity.name || '',
      description: props.activity.description || '',
      schedule_id: props.activity.schedule_id || null
    }
  } else {
    form.value.name = generateDefaultName(form.value.starting_at)
  }
})

watch(() => form.value.starting_at, (newValue) => {
  if (!isEditing.value && !form.value.name.includes('Изменено:')) {
    form.value.name = generateDefaultName(newValue)
  }
})

const formatTime = (dateTimeString) => {
  if (!dateTimeString) return ''
  return dateTimeString.split(' ')[1]?.substring(0, 5) || dateTimeString
}

const onScheduleSelect = (scheduleItem) => {
  const selectedSchedule = props.scheduleDays.find(s => s.id === scheduleItem.id)
  if (selectedSchedule) {
    const now = new Date()
    const targetDay = parseInt(selectedSchedule.day_of_week)
    const [hours, minutes] = formatTime(selectedSchedule.start_time).split(':')
    
    let targetDate = new Date()
    targetDate.setHours(parseInt(hours), parseInt(minutes), 0, 0)
    
    const currentDay = now.getDay()
    const adjustedCurrentDay = currentDay === 0 ? 7 : currentDay
    
    let daysUntilTarget = targetDay - adjustedCurrentDay
    
    if (daysUntilTarget < 0 || (daysUntilTarget === 0 && targetDate < now)) {
      daysUntilTarget += 7
    }
    
    targetDate.setDate(targetDate.getDate() + daysUntilTarget)
    
    const year = targetDate.getFullYear()
    const month = String(targetDate.getMonth() + 1).padStart(2, '0')
    const day = String(targetDate.getDate()).padStart(2, '0')
    const formattedHours = String(targetDate.getHours()).padStart(2, '0')
    const formattedMinutes = String(targetDate.getMinutes()).padStart(2, '0')
    
    form.value = {
      ...form.value,
      starting_at: `${year}-${month}-${day}T${formattedHours}:${formattedMinutes}`,
      schedule_id: scheduleItem
    }
  }
}

const save = async () => {
  try {
    if (!form.value.name?.trim()) {
      $q.notify({
        type: 'negative',
        message: 'Название занятия обязательно для заполнения',
        position: 'top-right'
      })
      return
    }

    emit('save', {
      ...form.value,
      name: form.value.name.trim(),
      id: props.activity?.id
    })
  } catch (error) {
    let errorMessage = 'Произошла ошибка при сохранении'
    
    if (error.response?.data?.errors) {
      // Получаем первую ошибку из ответа сервера
      errorMessage = Object.values(error.response.data.errors)[0][0]
    } else if (error.response?.data?.message) {
      errorMessage = error.response.data.message
    }

    $q.notify({
      type: 'negative',
      message: errorMessage,
      position: 'top-right'
    })
  }
}
</script> 