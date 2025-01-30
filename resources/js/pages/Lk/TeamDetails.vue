<template>
  <page-layout title="Группа">
    <!-- Заголовок группы -->
    <div class="q-pa-md">
      <div class="text-h4">{{ team.name }}</div>
      <div class="text-subtitle1 text-grey-7">{{ team.description || 'Нет описания' }}</div>
    </div>

    <!-- Табы -->
    <q-tabs
      v-model="activeTab"
      class="text-primary"
      align="left"
    >
      <q-tab name="info" label="Инфо" />
      <q-tab name="students" label="Ученики" />
      <q-tab name="activities" label="Занятия" />
    </q-tabs>

    <q-separator />

    <q-tab-panels v-model="activeTab" animated>
      <!-- Таб Инфо -->
      <q-tab-panel name="info" class="q-pa-none">
        <q-card flat>
          <q-card-section>
            <div class="text-subtitle2">Тип: {{ team.type || 'Не указан' }}</div>
            
            <div class="q-mt-md">
              <div>Количество учеников: {{ users.length }}</div>
              <div>Количество занятий: {{ activities.length }}</div>
            </div>

            <div class="q-mt-md">
              <div class="text-h6 q-mb-sm">Расписание занятий</div>
              
              <!-- Компонент отображения расписания -->
              <schedule-list 
                :schedule-days="team.schedule_days || []"
              />

              <!-- Кнопка редактирования расписания -->
              <div class="q-mt-md">
                <q-btn
                  color="primary"
                  icon="edit"
                  label="Редактировать расписание"
                  @click="showScheduleEdit = true"
                />
              </div>

              <!-- Диалог редактирования расписания -->
              <schedule-edit-dialog
                :model-value="showScheduleEdit"
                @update:model-value="showScheduleEdit = $event"
                :team-id="team.id"
                :schedule-days="team.schedule_days || []"
                @update:schedule-days="updateScheduleDays"
                @saved="showScheduleEdit = false"
                @cancelled="showScheduleEdit = false"
              />
            </div>
          </q-card-section>
        </q-card>
      </q-tab-panel>

      <!-- Таб Ученики -->
      <q-tab-panel name="students" class="q-px-none">
        <students-list
          :students="users"
          :teams="[]"
          hide-teams
        />
        <div class="q-pa-md row q-gutter-sm justify-center">
          <q-btn
            color="primary"
            label="Добавить"
            icon="fa fa-user-plus"
            stack
            @click="showAddStudentDialog = true"
          />
          <q-btn
            color="negative"
            label="Удалить"
            icon="fa fa-user-minus"
            stack
            @click="showRemoveStudentDialog = true"
          />
        </div>

        <!-- Диалоги для работы со студентами -->
        <q-dialog v-model="showAddStudentDialog">
          <q-card style="width: 700px; max-width: 80vw;">
            <q-card-section class="row items-center">
              <div class="text-h6">Добавить ученика</div>
              <q-space />
              <q-btn icon="close" flat round dense v-close-popup />
            </q-card-section>

            <q-card-section>
              <students-list
                :students="availableStudents"
                :teams="[]"
                hide-teams
                selection-mode
                @student-selected="addStudentToTeam"
              />
            </q-card-section>
          </q-card>
        </q-dialog>

        <q-dialog v-model="showRemoveStudentDialog">
          <q-card style="width: 700px; max-width: 80vw;">
            <q-card-section class="row items-center">
              <div class="text-h6">Удалить ученика</div>
              <q-space />
              <q-btn icon="close" flat round dense v-close-popup />
            </q-card-section>

            <q-card-section>
              <students-list
                :students="users"
                :teams="[]"
                hide-teams
                selection-mode
                @student-selected="removeStudentFromTeam"
              />
            </q-card-section>
          </q-card>
        </q-dialog>
      </q-tab-panel>

      <!-- Таб Занятия -->
      <q-tab-panel name="activities" class="q-px-none">
        <div class="q-pa-none">
          <q-list separator>
            <q-item
              v-for="activity in activities"
              :key="activity.id"
              clickable
              v-ripple
              @click="router.get(route('teacher.activity.details', activity.id))"
            >
              <q-item-section>
                <q-item-label class="text-h6">{{ activity.name }}</q-item-label>
                <q-item-label caption>
                  <div class="row items-center">
                    {{ formatDateTime(activity.starting_at) }}
                    <template v-if="activity.finished_at">
                      <q-icon name="arrow_forward" size="xs" class="q-mx-xs" />
                      {{ formatDateTime(activity.finished_at) }}
                    </template>
                  </div>
                </q-item-label>
              </q-item-section>

              <q-item-section side>
                <div class="row items-center">
                  {{ activity.users ? activity.users.length : 0 }}
                </div>
              </q-item-section>

              <!-- Кнопка удаления -->
              <q-item-section side>
                <q-btn
                  flat
                  round
                  color="negative"
                  icon="delete"
                  :disable="activity.started_at !== null || activity.finished_at !== null"
                  @click.stop="deleteActivity(activity.id)"
                >
                  <q-tooltip>Удалить занятие</q-tooltip>
                </q-btn>
              </q-item-section>
            </q-item>
          </q-list>
        </div>
      </q-tab-panel>
    </q-tab-panels>

    <!-- FAB кнопка перемещена за пределы tab-panel -->
    <q-btn
      v-if="activeTab === 'activities'"
      fab
      icon="add"
      color="primary"
      class="fixed-bottom-right"
      @click="showAddDialog = true"
    />

    <!-- Диалог добавления активности -->
    <q-dialog v-model="showAddDialog">
      <q-card style="min-width: 350px">
        <q-card-section>
          <div class="text-h6">Добавить занятие</div>
        </q-card-section>

        <q-card-section>
          <q-select
            v-model="newActivity.schedule_id"
            :options="team.schedule_days"
            :option-label="(schedule) => schedule ? `${daysMap[schedule.day_of_week]}: ${formatTime(schedule.start_time)} - ${formatTime(schedule.end_time)}` : ''"
            option-value="id"
            label="Выберите расписание"
            class="q-mb-md"
            @update:model-value="onScheduleSelect"
          />
          <q-input
            v-model="newActivity.starting_at"
            type="datetime-local"
            label="Дата и время начала"
            class="q-mb-md"
          />
          <q-input
            v-model="newActivity.description"
            type="textarea"
            label="Описание"
          />
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Отмена" v-close-popup />
          <q-btn flat label="Сохранить" @click="saveActivity" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </page-layout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { date, useQuasar } from 'quasar'
import StudentsList from '@/modules/lms/components/users/StudentsList.vue'
import ScheduleList from '@/components/ScheduleList.vue'
import ScheduleEditDialog from '@/components/ScheduleEditDialog.vue'
import axios from 'axios'

const $q = useQuasar()

const props = defineProps({
  team: {
    type: Object,
    required: true
  },
  users: {
    type: Array,
    required: true
  },
  activities: {
    type: Array,
    required: true
  },
  payments: {
    type: Array,
    required: true
  },
  allStudents: {
    type: Array,
    required: true
  }
})

const activeTab = ref('info')
const showAddStudentDialog = ref(false)
const showRemoveStudentDialog = ref(false)
const showScheduleEdit = ref(false)
const showAddDialog = ref(false)

const availableStudents = computed(() => {
  const currentUserIds = new Set(props.users.map(user => user.id))
  return props.allStudents.filter(student => !currentUserIds.has(student.id))
})

const addStudentToTeam = (student) => {
  router.post(route('teams.add-student'), {
    teamId: props.team.id,
    studentId: student.id
  }, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      showAddStudentDialog.value = false
      $q.notify({
        type: 'positive',
        message: 'Ученик успешно добавлен в группу',
        position: 'top-right'
      })
    },
    onError: () => {
      $q.notify({
        type: 'negative',
        message: 'Ошибка при добавлении ученика',
        position: 'top-right'
      })
    }
  })
}

const removeStudentFromTeam = (student) => {
  showRemoveStudentDialog.value = false

  $q.dialog({
    title: 'Подтверждение',
    message: `Вы действительно хотите удалить ученика ${student.name} из группы?`,
    cancel: true,
    persistent: true,
    ok: {
      label: 'Удалить',
      color: 'negative'
    },
    cancel: {
      label: 'Отмена',
      color: 'primary'
    }
  }).onOk(() => {
    router.post(route('teams.remove-student'), {
      teamId: props.team.id,
      studentId: student.id
    }, {
      preserveScroll: true,
      preserveState: true,
      onSuccess: () => {
        $q.notify({
          type: 'positive',
          message: 'Ученик успешно удален из группы',
          position: 'top-right'
        })
      },
      onError: () => {
        $q.notify({
          type: 'negative',
          message: 'Ошибка при удалении ученика',
          position: 'top-right'
        })
      }
    })
  })
}

const formatDateTime = (dateString) => {
  if (!dateString) return 'Нет данных'
  return date.formatDate(dateString, 'DD.MM.YYYY HH:mm')
}

const updateScheduleDays = (newDays) => {
  if (props.team) {
    props.team.schedule_days = newDays
  }
}

const getDefaultStartTime = () => {
  const date = new Date()
  date.setHours(date.getHours() + 1)
  date.setMinutes(0)
  date.setSeconds(0)
  return date.toISOString().slice(0, 16)
}

const newActivity = ref({
  starting_at: getDefaultStartTime(),
  name: '',
  description: '',
  schedule_id: null
})

const saveActivity = async () => {
  try {
    await axios.post(route('activities.store'), {
      team_id: props.team.id,
      name: newActivity.value.name || 'Новое занятие ' + newActivity.value.starting_at,
      starting_at: newActivity.value.starting_at,
      description: newActivity.value.description,
      duration: 60
    })
    
    showAddDialog.value = false
    newActivity.value = { 
      starting_at: getDefaultStartTime(),
      name: '',
      description: '',
      schedule_id: null
    }
    
    // Обновляем страницу через Inertia
    router.reload({ only: ['activities'] })
    
    $q.notify({
      type: 'positive',
      message: 'Занятие успешно добавлено',
      position: 'top-right'
    })
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Ошибка при добавлении занятия',
      position: 'top-right'
    })
  }
}

const deleteActivity = async (activityId) => {
  try {
    $q.dialog({
      title: 'Подтверждение',
      message: 'Вы действительно хотите удалить это занятие?',
      cancel: true,
      persistent: true,
      ok: {
        label: 'Удалить',
        color: 'negative'
      },
      cancel: {
        label: 'Отмена',
        color: 'primary'
      }
    }).onOk(async () => {
      console.log('Удаление занятия')
      try {
        await axios.delete(route('activities.destroy', activityId))
        
        // Обновляем страницу через Inertia
        router.reload({ only: ['activities'] })
        
        $q.notify({
          type: 'positive',
          message: 'Занятие успешно удалено',
          position: 'top-right'
        })
      } catch (error) {
        $q.notify({
          type: 'negative',
          message: 'Ошибка при удалении занятия',
          position: 'top-right'
        })
      }
    })
  } catch (error) {
    console.error('Ошибка при показе диалога:', error)
  }
}

// Добавляем мапинг дней недели
const daysMap = {
  1: 'Понедельник',
  2: 'Вторник',
  3: 'Среда',
  4: 'Четверг',
  5: 'Пятница',
  6: 'Суббота',
  7: 'Воскресенье'
}

// Функция форматирования времени
const formatTime = (dateTimeString) => {
  if (!dateTimeString) return ''
  return dateTimeString.split(' ')[1]?.substring(0, 5) || dateTimeString
}

const onScheduleSelect = (scheduleItem) => {
  const selectedSchedule = props.team.schedule_days.find(s => s.id === scheduleItem.id)
  if (selectedSchedule) {
    const now = new Date()
    const targetDay = parseInt(selectedSchedule.day_of_week)
    const [hours, minutes] = formatTime(selectedSchedule.start_time).split(':')
    
    // Создаем дату на основе выбранного расписания
    let targetDate = new Date()
    targetDate.setHours(parseInt(hours), parseInt(minutes), 0, 0)
    
    const currentDay = now.getDay()
    const adjustedCurrentDay = currentDay === 0 ? 7 : currentDay
    
    let daysUntilTarget = targetDay - adjustedCurrentDay
    
    if (daysUntilTarget < 0 || (daysUntilTarget === 0 && targetDate < now)) {
      daysUntilTarget += 7
    }
    
    targetDate.setDate(targetDate.getDate() + daysUntilTarget)
    
    // Форматируем дату без учета временной зоны
    const year = targetDate.getFullYear()
    const month = String(targetDate.getMonth() + 1).padStart(2, '0')
    const day = String(targetDate.getDate()).padStart(2, '0')
    const formattedHours = String(targetDate.getHours()).padStart(2, '0')
    const formattedMinutes = String(targetDate.getMinutes()).padStart(2, '0')
    
    newActivity.value = {
      ...newActivity.value,
      starting_at: `${year}-${month}-${day}T${formattedHours}:${formattedMinutes}`,
      schedule_id: scheduleItem
    }
  }
}
</script>

<style scoped>
.fixed-bottom-right {
  position: fixed;
  right: 44px;
  bottom: 44px;
  z-index: 2000; /* Увеличиваем z-index, чтобы кнопка была поверх всех элементов */
}
</style> 