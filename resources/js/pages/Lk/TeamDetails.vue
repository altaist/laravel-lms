<template>
  <page-layout 
    title="Группа"
    right-btn-icon="fa-solid fa-pen"
    @click:header:right="showEditDialog = true"
  >
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
            
            <div class="q-mt-md q-pa-md bg-grey-2">
              <div class="row q-col-gutter-md">
                <div class="col-6">Учеников: {{ users.length }}</div>
                <div class="col-6">Занятий: {{ activities.length }}</div>
              </div>
            </div>

            <div class="q-mt-lg">
              <div class="text-h4 q-mb-sm">Расписание занятий</div>
              
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
      <q-tab-panel name="students" class="q-px-none q-py-lg">
        <students-list
          :students="users"
          :teams="[]"
          hide-teams
        />
        <div class="q-mt-lg row q-gutter-sm justify-center">
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

        <!-- Диалоги для работы со учениками -->
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
                  @click.stop="confirmDeleteActivity(activity)"
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
      <activity-form
        :schedule-days="team.schedule_days"
        @save="saveActivity"
        @cancel="showAddDialog = false"
      />
    </q-dialog>

    <!-- Добавляем диалог редактирования -->
    <q-dialog v-model="showEditDialog">
      <team-form
        :team="team"
        title="Редактировать группу"
        @save="handleEditTeam"
        @cancel="showEditDialog = false"
      />
    </q-dialog>
  </page-layout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { date, useQuasar } from 'quasar'
import StudentsList from '@/modules/lms/components/users/StudentsList.vue'
import ScheduleList from '@/modules/lms/components/schedules/ScheduleList.vue'
import ScheduleEditDialog from '@/modules/lms/components/schedules/ScheduleEditDialog.vue'
import ActivityForm from '@/modules/lms/components/activities/ActivityForm.vue'
import TeamForm from '@/modules/lms/components/teams/TeamForm.vue'
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
const showEditDialog = ref(false)

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

const saveActivity = async (formData) => {
  try {
    await axios.post(route('activities.store'), {
      team_id: props.team.id,
      name: formData.name,
      starting_at: formData.starting_at,
      description: formData.description,
      duration: 60
    })
    
    showAddDialog.value = false
    
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

const confirmDeleteActivity = (activity) => {
  $q.dialog({
    title: 'Подтверждение',
    message: `Вы действительно хотите удалить занятие "${activity.name}"?`,
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
    try {
      await axios.delete(route('activities.destroy', activity.id))
      
      // Обновляем локальный список активностей
      const index = props.activities.findIndex(a => a.id === activity.id)
      if (index !== -1) {
        props.activities.splice(index, 1)
      }
      
      $q.notify({
        type: 'positive',
        message: 'Занятие успешно удалено',
        position: 'top-right'
      })
    } catch (error) {
      console.error('Ошибка при удалении занятия:', error)
      $q.notify({
        type: 'negative',
        message: 'Ошибка при удалении занятия',
        position: 'top-right'
      })
    }
  })
}

const handleEditTeam = async (formData) => {
  try {
    const response = await axios.put(route('teams.update', props.team.id), formData)
    showEditDialog.value = false
    
    $q.notify({
      type: 'positive',
      message: response.data.message || 'Команда успешно обновлена',
      position: 'top-right'
    })
    
    router.reload({ only: ['team'] })
  } catch (error) {
    console.error('Ошибка при обновлении команды:', error)
    
    const errorMessage = error.response?.data?.message 
      || 'Ошибка при обновлении команды'
    
    $q.notify({
      type: 'negative',
      message: errorMessage,
      position: 'top-right'
    })
    
    // Прокидываем ошибку дальше для обработки в форме
    throw error
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