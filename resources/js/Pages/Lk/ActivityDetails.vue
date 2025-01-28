<template>
  <page-layout title="Занятие">
    <!-- Панель активности -->
    <activity-panel :activity="activity" />

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
            hide-teams
          />
          
          <div class="q-mt-md row justify-center">
            <q-btn
              color="primary"
              label="Добавить учеников"
              icon="fa fa-user-plus"
              @click="showAddStudentDialog = true"
            />
          </div>
        </div>

        <!-- Диалог добавления ученика -->
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
                @student-selected="addStudentToActivity"
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
  </page-layout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useQuasar } from 'quasar'
import StudentsList from '@/modules/lms/components/users/StudentsList.vue'
import ActivityPanel from '@/modules/lms/components/activities/ActivityPanel.vue'
import axios from 'axios'

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
  availableUsers: {
    type: Array,
    required: true
  }
})

const activeTab = ref('students')
const showAddStudentDialog = ref(false)

// Отфильтровываем уже прикрепленных пользователей
const availableStudents = computed(() => {
  const attachedIds = new Set(props.attachedUsers.map(user => user.id))
  return props.availableUsers.filter(user => !attachedIds.has(user.id))
})

const addStudentToActivity = async (student) => {
  try {
    await axios.post(route('activities.add-student'), {
      activityId: props.activity.id,
      studentId: student.id
    })
    
    showAddStudentDialog.value = false
    
    // Обновляем список прикрепленных пользователей
    props.attachedUsers.push(student)
    
    $q.notify({
      type: 'positive',
      message: 'Ученик успешно добавлен к занятию',
      position: 'top-right'
    })
  } catch (error) {
    console.error('Ошибка при добавлении ученика:', error)
    $q.notify({
      type: 'negative',
      message: 'Ошибка при добавлении ученика',
      position: 'top-right'
    })
  }
}
</script>