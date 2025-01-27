<template>
  <div class="schedule-editor">
    <q-list bordered separator>
      <!-- Список существующих дней -->
      <q-item v-for="(day, index) in localSchedule" :key="index">
        <q-item-section>
          <div class="row q-col-gutter-sm items-center">
            <!-- Выбор дня недели -->
            <div class="col-12 col-sm-4">
              <q-select
                v-model="day.day_of_week"
                :options="availableDays"
                option-label="label"
                option-value="value"
                label="День недели"
                dense
                outlined
              />
            </div>
            <!-- Время начала -->
            <div class="col-12 col-sm-3">
              <q-input
                v-model="day.start_time"
                type="time"
                label="Начало"
                dense
                outlined
              />
            </div>
            <!-- Время окончания -->
            <div class="col-12 col-sm-3">
              <q-input
                v-model="day.end_time"
                type="time"
                label="Окончание"
                dense
                outlined
              />
            </div>
            <!-- Кнопка удаления -->
            <div class="col-12 col-sm-2">
              <q-btn
                flat
                round
                color="negative"
                icon="delete"
                @click="removeDay(index)"
              />
            </div>
          </div>
        </q-item-section>
      </q-item>

      <!-- Кнопка добавления нового дня -->
      <q-item>
        <q-item-section>
          <q-btn
            color="primary"
            icon="add"
            label="Добавить день"
            @click="addDay"
          />
        </q-item-section>
      </q-item>
    </q-list>

    <!-- Кнопки сохранения/отмены -->
    <div class="row q-col-gutter-sm q-mt-md">
      <div class="col">
        <q-btn
          color="primary"
          label="Сохранить"
          :loading="saving"
          @click="saveSchedule"
        />
        <q-btn
          flat
          color="grey"
          label="Отменить"
          class="q-ml-sm"
          @click="resetSchedule"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useQuasar } from 'quasar'

const $q = useQuasar()

const props = defineProps({
  scheduleDays: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['update:scheduleDays'])

const saving = ref(false)
const localSchedule = ref([])

// Доступные дни недели
const availableDays = [
  { label: 'Понедельник', value: 1 },
  { label: 'Вторник', value: 2 },
  { label: 'Среда', value: 3 },
  { label: 'Четверг', value: 4 },
  { label: 'Пятница', value: 5 },
  { label: 'Суббота', value: 6 },
  { label: 'Воскресенье', value: 7 }
]

// Инициализация локального расписания
const initLocalSchedule = () => {
  localSchedule.value = props.scheduleDays.map(day => ({
    day_of_week: day.day_of_week,
    start_time: day.start_time,
    end_time: day.end_time
  }))
}

// Добавление нового дня
const addDay = () => {
  localSchedule.value.push({
    day_of_week: 1,
    start_time: '09:00',
    end_time: '10:00'
  })
}

// Удаление дня
const removeDay = (index) => {
  localSchedule.value.splice(index, 1)
}

// Сохранение расписания
const saveSchedule = async () => {
  // Валидация
  for (const day of localSchedule.value) {
    if (!day.day_of_week || !day.start_time || !day.end_time) {
      $q.notify({
        color: 'negative',
        message: 'Заполните все поля расписания'
      })
      return
    }
  }

  saving.value = true
  try {
    emit('update:scheduleDays', localSchedule.value)
    $q.notify({
      color: 'positive',
      message: 'Расписание сохранено'
    })
  } catch (error) {
    $q.notify({
      color: 'negative',
      message: 'Ошибка при сохранении расписания'
    })
  } finally {
    saving.value = false
  }
}

// Сброс изменений
const resetSchedule = () => {
  initLocalSchedule()
}

// Инициализация при создании компонента
initLocalSchedule()
</script>

<style scoped>
.schedule-editor {
  max-width: 800px;
}
</style>

/*
const emit = defineEmits(['update:scheduleDays'])

const saving = ref(false)
const localSchedule = ref([])

// ... остальные вспомогательные функции без изменений ...

// Обновленная функция сохранения
const saveSchedule = async () => {
  // Валидация
  for (const day of localSchedule.value) {
    if (!day.day_of_week || !day.start_time || !day.end_time) {
      $q.notify({
        color: 'negative',
        message: 'Заполните все поля расписания'
      })
      return
    }
  }

  saving.value = true
  try {
    // Отправляем запрос на обновление расписания
    const { data } = await axios.put(`/api/teams/${props.teamId}/schedule`, {
      schedule_days: localSchedule.value
    })

    // Обновляем локальное состояние
    emit('update:scheduleDays', data.schedule_days)

    $q.notify({
      color: 'positive',
      message: 'Расписание успешно сохранено'
    })
  } catch (error) {
    console.error('Ошибка при сохранении расписания:', error)
    $q.notify({
      color: 'negative',
      message: error.response?.data?.message || 'Ошибка при сохранении расписания'
    })
  } finally {
    saving.value = false
  }
}
*/