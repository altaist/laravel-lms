<template>
  <q-card style="min-width: 350px">
    <q-card-section>
      <div class="text-h6">{{ title }}</div>
    </q-card-section>

    <q-card-section>
      <q-select
        v-model="form.schedule_id"
        :options="scheduleDays"
        :option-label="(schedule) => schedule ? `${daysMap[schedule.day_of_week]}: ${formatTime(schedule.start_time)} - ${formatTime(schedule.end_time)}` : ''"
        option-value="id"
        label="Выберите расписание"
        class="q-mb-md"
        @update:model-value="onScheduleSelect"
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
        label="Описание"
      />
    </q-card-section>

    <q-card-actions align="right">
      <q-btn flat label="Отмена" @click="$emit('cancel')" />
      <q-btn flat label="Сохранить" @click="save" />
    </q-card-actions>
  </q-card>
</template>

<script setup>
import { ref, watch } from 'vue'
import { date } from 'quasar'

const props = defineProps({
  scheduleDays: {
    type: Array,
    required: true
  },
  title: {
    type: String,
    default: 'Добавить занятие'
  }
})

const emit = defineEmits(['save', 'cancel'])

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

const form = ref({
  starting_at: getDefaultStartTime(),
  name: '',
  description: '',
  schedule_id: null
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
      starting_at: `${year}-${month}-${day} ${formattedHours}:${formattedMinutes}`,
      schedule_id: scheduleItem
    }
  }
}

const save = () => {
  emit('save', {
    ...form.value,
    name: form.value.name || 'Новое занятие ' + form.value.starting_at
  })
}
</script> 