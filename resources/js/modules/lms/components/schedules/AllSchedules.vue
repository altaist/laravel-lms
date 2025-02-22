<template>
  <div class="all-schedules q-pa-none">
    <div class="q-mb-md">
      <q-select
        v-model="currentViewMode"
        :options="viewModeOptions"
        label="Выберите режим отображения"
        outlined
        emit-value
        map-options
      />
    </div>

    <!-- Режим отображения по группам -->
    <div v-if="currentViewMode === 'group'">
      <q-list separator>
        <q-item v-for="team in teams" :key="team.id">
          <q-item-section>
            <q-item-label class="text-weight-bold">{{ team.name }}</q-item-label>
            <q-item-label caption>{{ team.description }}</q-item-label>
            <schedule-list :schedule-days="team.schedule?.days || []" />
          </q-item-section>
        </q-item>

        <!-- Если расписания пустые -->
        <q-item v-if="!teams.length">
          <q-item-section>
            <q-item-label class="text-grey">
              Нет доступных расписаний.
            </q-item-label>
          </q-item-section>
        </q-item>
      </q-list>
    </div>

    <!-- Режим отображения по дням недели -->
    <div v-else-if="currentViewMode === 'day'">
      <q-list separator>
        <div v-for="(groups, day) in schedulesByDay" :key="day">
          <q-item-label class="text-h6">{{ daysMap[day] }}</q-item-label>
          <q-item v-for="group in sortedGroupsByTime(groups)" :key="group.id" class="q-ml-md">
            <q-item-section>
              <q-item-label class="text-weight-medium">{{ group.name }}</q-item-label>
              <q-item-label caption>{{ group.description }}</q-item-label>
              <div>
                {{ formatTime(group.start_time) }} - {{ formatTime(group.end_time) }}
              </div>
            </q-item-section>
          </q-item>
          <q-separator />
        </div>

        <!-- Если расписания пустые -->
        <q-item v-if="!Object.keys(schedulesByDay).length">
          <q-item-section>
            <q-item-label class="text-grey">
              Нет доступных расписаний.
            </q-item-label>
          </q-item-section>
        </q-item>
      </q-list>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useQuasar } from 'quasar'
import axios from 'axios'
import ScheduleList from '@/modules/lms/components/schedules/ScheduleList.vue'

const $q = useQuasar()
const teams = ref([])

// Входной параметр для выбора режима отображения
const props = defineProps({
  initialViewMode: {
    type: String,
    default: 'group', // 'group' или 'day'
    validator: (value) => ['group', 'day'].includes(value)
  }
})

// Возможные варианты режимов отображения
const viewModeOptions = [
  { label: 'По группам', value: 'group' },
  { label: 'По дням недели', value: 'day' }
]

// Текущий режим отображения
const currentViewMode = ref(props.initialViewMode)

// Карта дней недели
const daysMap = {
  1: 'Понедельник',
  2: 'Вторник',
  3: 'Среда',
  4: 'Четверг',
  5: 'Пятница',
  6: 'Суббота',
  7: 'Воскресенье'
}

// Форматирование времени
const formatTime = (time) => {
  if (!time) return ''
  return time.substring(0, 5)
}

// Получение всех расписаний групп
const fetchAllSchedules = async () => {
  try {
    const response = await axios.get('/teams/schedules')
    teams.value = response.data
  } catch (error) {
    console.error('Ошибка при загрузке расписаний:', error)
    $q.notify({
      color: 'negative',
      message: 'Не удалось загрузить расписания групп.'
    })
  }
}

// Функция сортировки групп по времени
const sortedGroupsByTime = (groups) => {
  return [...groups].sort((a, b) => {
    // Преобразуем время в минуты для сравнения
    const getMinutes = (time) => {
      const [hours, minutes] = time.split(':').map(Number)
      return hours * 60 + minutes
    }
    
    const aMinutes = getMinutes(a.start_time)
    const bMinutes = getMinutes(b.start_time)
    
    return aMinutes - bMinutes
  })
}

// Группировка расписаний по дням недели с сортировкой по времени
const schedulesByDay = computed(() => {
  const grouped = {}

  teams.value.forEach((team) => {
    team.schedule?.days?.forEach((day) => {
      if (!grouped[day.day_of_week]) {
        grouped[day.day_of_week] = []
      }
      grouped[day.day_of_week].push({
        id: team.id,
        name: team.name,
        description: team.description,
        start_time: day.start_time,
        end_time: day.end_time
      })
    })
  })

  // Сортировка дней по порядку
  const sortedGrouped = {}
  Object.keys(daysMap).forEach((day) => {
    if (grouped[day]) {
      sortedGrouped[day] = grouped[day]
    }
  })

  return sortedGrouped
})

onMounted(() => {
  fetchAllSchedules()
})
</script>

<style scoped>
.all-schedules {
  margin: 0 auto;
}
.text-h6 {
  margin-top: 16px;
}
.q-ml-md {
  margin-left: 32px;
}
</style> 