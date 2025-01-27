<template>
  <div class="schedule-list">
    <q-list bordered separator>
      <q-item v-for="day in formattedSchedule" :key="day.dayCode">
        <q-item-section>
          <q-item-label class="text-weight-medium">{{ day.dayName }}</q-item-label>
          <q-item-label caption>
            {{ day.startTime }} - {{ day.endTime }}
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
  // Принимаем расписание в формате массива [день, время_начала, время_конца]
  schedule: {
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

// Проверяем, активно ли занятие сейчас
const isTimeInRange = (startTime, endTime) => {
  const now = new Date()
  const currentTime = now.getHours() * 60 + now.getMinutes()
  
  const [startHour, startMinute] = startTime.split(':').map(Number)
  const [endHour, endMinute] = endTime.split(':').map(Number)
  
  const startMinutes = startHour * 60 + startMinute
  const endMinutes = endHour * 60 + endMinute
  
  return currentTime >= startMinutes && currentTime <= endMinutes
}

// Форматируем расписание для отображения
const formattedSchedule = computed(() => {
  if (!props.schedule) return []

  const currentDay = new Date().getDay() || 7 // Преобразуем 0 (воскресенье) в 7
    console.log(props.schedule);
  return props.schedule
    .map(([dayCode, startTime, endTime]) => ({
      dayCode,
      dayName: daysMap[dayCode],
      startTime,
      endTime,
      isActive: dayCode === currentDay && isTimeInRange(startTime, endTime)
    }))
    .sort((a, b) => a.dayCode - b.dayCode) // Сортируем по дням недели
})
</script>

<style scoped>
.schedule-list {
  max-width: 400px;
}
</style> 