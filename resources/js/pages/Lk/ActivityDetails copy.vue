<template>
  <page-layout title="Занятие">
    <!-- Панель активности -->
    <activity-panel :activity="activity" class="q-my-md"/>

    <!-- Табы -->
    <q-tabs
      v-model="activeTab"
      class="text-primary"
      align="left"
    >
      <q-tab name="students" label="Ученики" />
      <q-tab name="content" label="Контент" />
      <q-tab name="results" label="Результаты" />
    </q-tabs>

    <q-separator />

    <q-tab-panels v-model="activeTab" animated>
      <!-- Таб Ученики -->
      <q-tab-panel name="students" class="q-px-none">
        <div class="q-pa-md">
          <div class="text-h6 q-mb-md">Прикрепленные ученики</div>
          <students-list
            :students="attachedUsers"
            :teams="[]"
            
          />
          
          <div class="q-mt-md row justify-center q-gutter-sm">
            <q-btn
            v-if="availableStudents.length > 0"
              color="primary"
              label="Добавить учеников"
              icon="fa fa-user-plus"
              @click="showAddAvailableStudentsDialog = true"
            />
            <q-btn
              v-if="teamUsers && teamUsers.length > 0"
              color="primary"
              label="Добавить учеников из группы"
              icon="fa fa-user-plus"
              @click="showAddTeamStudentsDialog = true"
            />
            <q-btn
              v-if="attachedUsers.length > 0"
              color="negative"
              label="Удалить учеников"
              icon="fa fa-user-minus"
              @click="showRemoveStudentDialog = true"
            />
          </div>
        </div>

        <!-- Диалог добавления ученика -->
        <q-dialog v-model="showAddAvailableStudentsDialog">
          <q-card style="width: 700px; max-width: 80vw;">
            <q-card-section class="row items-center">
              <div class="text-h6">Добавить учеников</div>
              <q-space />
              <q-btn icon="close" flat round dense v-close-popup />
            </q-card-section>

            <q-card-section>
              <students-multi-select
                :students="availableStudents"
                :teams="[]"
                v-model:selected="selectedStudentsToAdd"
                @confirm="addStudentsToActivity"
              />
            </q-card-section>
          </q-card>
        </q-dialog>

        <q-dialog v-model="showAddTeamStudentsDialog">
          <q-card style="width: 700px; max-width: 80vw;">
            <q-card-section class="row items-center">
              <div class="text-h6">Добавить учеников из группы</div>
              <q-space />
              <q-btn icon="close" flat round dense v-close-popup />
            </q-card-section>

            <q-card-section>
              <students-multi-select
                :students="teamUsers"
                :teams="[]"
                v-model:selected="selectedStudentsToAdd"
                @confirm="addStudentsToActivity"
              />
            </q-card-section>
          </q-card>
        </q-dialog>

        <!-- Диалог удаления ученика -->
        <q-dialog v-model="showRemoveStudentDialog">
          <q-card style="width: 700px; max-width: 80vw;">
            <q-card-section class="row items-center">
              <div class="text-h6">Удалить учеников</div>
              <q-space />
              <q-btn icon="close" flat round dense v-close-popup />
            </q-card-section>

            <q-card-section>
              <students-multi-select
                :students="attachedUsers"
                :teams="[]"
                v-model:selected="selectedStudentsToRemove"
                @confirm="confirmRemoveStudents"
              />
            </q-card-section>
          </q-card>
        </q-dialog>
      </q-tab-panel>

      <!-- Таб Контент -->
      <q-tab-panel name="content">
        <div class="text-h6">Контент занятия</div>
        <!-- Здесь будет контент -->
      </q-tab-panel>

      <!-- Таб Результаты -->
      <q-tab-panel name="results">
        <div class="text-h6">Результаты занятия</div>
        <!-- Здесь будут результаты -->
      </q-tab-panel>
    </q-tab-panels>

    <activity-users
      :activity="activity"
      :team-users="teamUsers"
      :attached-users="attachedUsers"
      :available-users="availableUsers"
    />
  </page-layout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useQuasar } from 'quasar'
import axios from 'axios'
import StudentsList from '@/modules/lms/components/users/StudentsList.vue'
import ActivityPanel from '@/modules/lms/components/activities/ActivityPanel.vue'
import StudentsMultiSelect from '@/modules/lms/components/users/StudentsMultiSelect.vue'
import ActivityUsers from '@/modules/lms/components/activities/ActivityUsers.vue'

const $q = useQuasar()

const props = defineProps({
  activity: {
    type: Object,
    required: true
  },
  team: {
    type: Object,
    required: true
  },
  attachedUsers: {
    type: Array,
    required: true
  },
  teamUsers: {
    type: Array,
    required: true
  },
  availableUsers: {
    type: Array,
    required: true
  }
})

const activeTab = ref('students')
const showAddAvailableStudentsDialog = ref(false)
const showAddTeamStudentsDialog = ref(false)
const showRemoveStudentDialog = ref(false)

// Добавляем новые refs для хранения выбранных студентов
const selectedStudentsToAdd = ref([])
const selectedStudentsToRemove = ref([])

// Отфильтровываем уже прикрепленных пользователей
const availableStudents = computed(() => {
  const attachedIds = new Set(props.attachedUsers.map(user => user.id))
  return props.availableUsers.filter(user => !attachedIds.has(user.id))
})

// Заменяем функцию добавления одного студента на добавление нескольких
const addStudentsToActivity = async (students) => {
  try {
    await axios.post(route('activities.add-students'), {
      activityId: props.activity.id,
      studentIds: students.map(s => s.id)
    })
    
    // showAddStudentDialog.value = false
    selectedStudentsToAdd.value = []
    
    // Обновляем список прикрепленных пользователей
    props.attachedUsers.push(...students)
    
    $q.notify({
      type: 'positive',
      message: `${students.length} учеников успешно добавлены к занятию`,
      position: 'top-right'
    })
  } catch (error) {
    console.error('Ошибка при добавлении учеников:', error)
    $q.notify({
      type: 'negative',
      message: 'Ошибка при добавлении учеников',
      position: 'top-right'
    })
  }
}

// Заменяем функцию подтверждения удаления
const confirmRemoveStudents = (students) => {
  $q.dialog({
    title: 'Подтверждение',
    message: `Вы действительно хотите удалить ${students.length} учеников из занятия?`,
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
  }).onOk(() => removeStudentsFromActivity(students))
}

// Заменяем функцию удаления одного студента на удаление нескольких
const removeStudentsFromActivity = async (students) => {
  try {
    await axios.post(route('activities.remove-students'), {
      activityId: props.activity.id,
      studentIds: students.map(s => s.id)
    })
    
    // Удаляем учеников из локального списка
    const studentIds = new Set(students.map(s => s.id))
    props.attachedUsers.splice(
      0,
      props.attachedUsers.length,
      ...props.attachedUsers.filter(u => !studentIds.has(u.id))
    )
    
    showRemoveStudentDialog.value = false
    selectedStudentsToRemove.value = []
    
    $q.notify({
      type: 'positive',
      message: `${students.length} учеников успешно удалены из занятия`,
      position: 'top-right'
    })
  } catch (error) {
    console.error('Ошибка при удалении учеников:', error)
    $q.notify({
      type: 'negative',
      message: 'Ошибка при удалении учеников',
      position: 'top-right'
    })
  }
}
</script>