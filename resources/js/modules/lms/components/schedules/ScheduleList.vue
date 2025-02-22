<template>
  <div class="full-width">
    <q-list bordered separator>
      <q-item v-for="day in formattedSchedule" :key="day.dayCode">
        <q-item-section>
          <q-item-label class="text-weight-medium">{{ day.dayName }}</q-item-label>
          <q-item-label caption>
            {{ formatTime(day.startTime) }} - {{ formatTime(day.endTime) }}
          </q-item-label>
        </q-item-section>
        
        <!-- Если занятие активно сейчас, показываем индикатор -->
        <q-item-section side v-if="day.isActive">
          <q-badge color="positive" floating>
            Сейчас
          </q-badge>
        </q-item-section>
      </q-item>

      <!-- Если расписание пустое -->
      <q-item v-if="!formattedSchedule.length">
        <q-item-section>
          <q-item-label class="text-grey">
            Расписание не задано
          </q-item-label>
        </q-item-section>
      </q-item>
    </q-list>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  // Принимаем массив объектов ScheduleDay
  scheduleDays: {
    type: Array,
    default: () => []
  }
})

const daysMap = {
  1: 'Понедельник',
  2: 'Вторник',
  3: 'Среда',
  4: 'Четверг',
  5: 'Пятница',
  6: 'Суббота',
  7: 'Воскресенье'
}

// Форматируем время из формата "2024-01-20 09:00:00" в "09:00"
const formatTime = (dateTimeString) => {
  if (!dateTimeString) return ''
  return dateTimeString.split(' ')[1]?.substring(0, 5) || dateTimeString
}

// Проверяем, активно ли занятие сейчас
const isTimeInRange = (startTime, endTime) => {
  const now = new Date()
  const currentTime = now.getHours() * 60 + now.getMinutes()
  
  const startDateTime = new Date(`2000-01-01 ${startTime}`)
  const endDateTime = new Date(`2000-01-01 ${endTime}`)
  
  const startMinutes = startDateTime.getHours() * 60 + startDateTime.getMinutes()
  const endMinutes = endDateTime.getHours() * 60 + endDateTime.getMinutes()
  
  return currentTime >= startMinutes && currentTime <= endMinutes
}

// Форматируем расписание для отображения
const formattedSchedule = computed(() => {
  if (!props.scheduleDays?.length) return []

  const currentDay = new Date().getDay() || 7 // Преобразуем 0 (воскресенье) в 7

  return props.scheduleDays
    .map(day => ({
      dayCode: day.day_of_week,
      dayName: daysMap[day.day_of_week],
      startTime: day.start_time,
      endTime: day.end_time,
      isActive: day.day_of_week === currentDay && 
               isTimeInRange(day.start_time, day.end_time)
    }))
    .sort((a, b) => a.dayCode - b.dayCode) // Сортируем по дням недели
})
</script>
