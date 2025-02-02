<template>
  <page-layout 
    title="Занятие"
    right-btn-icon="fa-solid fa-pen"
    @click:header:right="showEditDialog = true"
  >
    <!-- Панель активности -->
    <activity-panel :activity="activity" class="q-my-md"/>

    <!-- Табы -->
    <q-tabs
      v-model="activeTab"
      class="text-primary"
      align="left"
      @update:model-value="handleTabChange"
    >
      <q-tab name="students" label="Ученики" />
      <q-tab name="content" label="Контент" />
      <q-tab name="results" label="Результаты" />
    </q-tabs>

    <q-separator />

    <q-tab-panels v-model="activeTab" animated>
      <!-- Таб Ученики -->
      <q-tab-panel name="students" class="q-px-none">
        <activity-users
          :activity="activity"
          :team-users="teamUsers"
          :attached-users="attachedUsers"
          :available-users="availableUsers"
        />
      </q-tab-panel>

      <!-- Таб Контент -->
      <q-tab-panel name="content">
        <div class="text-h6">Контент занятия</div>
        <!-- Здесь будет контент -->
      </q-tab-panel>

      <!-- Таб Результаты -->
      <q-tab-panel name="results">
        <div class="text-h6 q-mb-md">Результаты занятия</div>
        
        <div class="row q-col-gutter-md">
          <!-- Планируемая дата -->
          <div class="col-12 col-sm-6">
            <div class="statistics-card">
              <div class="param-name">Планируемая дата</div>
              <div class="param-value">
                {{ activity.starting_at ? new Date(activity.starting_at).toLocaleString() : 'Не задана' }}
              </div>
            </div>
          </div>

          <!-- Фактическая дата -->
          <div class="col-12 col-sm-6">
            <div class="statistics-card">
              <div class="param-name">Фактическая дата начала</div>
              <div class="param-value">
                {{ activity.started_at ? new Date(activity.started_at).toLocaleString() : 'Не начато' }}
              </div>
            </div>
          </div>

          <!-- Дата завершения -->
          <div class="col-12 col-sm-6">
            <div class="statistics-card">
              <div class="param-name">Дата завершения</div>
              <div class="param-value">
                {{ activity.finished_at ? new Date(activity.finished_at).toLocaleString() : 'Не завершено' }}
              </div>
            </div>
          </div>

          <!-- Количество учеников -->
          <div class="col-12 col-sm-6">
            <div class="statistics-card">
              <div class="param-name">Количество учеников</div>
              <div class="param-value">
                {{ attachedUsers.length }}
              </div>
            </div>
          </div>
        </div>
      </q-tab-panel>
    </q-tab-panels>

    <!-- Диалог редактирования -->
    <q-dialog v-model="showEditDialog">
      <activity-form
        :activity="activity"
        :schedule-days="scheduleDays"
        title="Редактировать занятие"
        @save="handleEditActivity"
        @cancel="showEditDialog = false"
      />
    </q-dialog>
  </page-layout>
</template>

<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import ActivityPanel from '@/modules/lms/components/activities/ActivityPanel.vue'
import ActivityUsers from '@/modules/lms/components/activities/ActivityUsers.vue'
import ActivityForm from '@/modules/lms/components/activities/ActivityForm.vue'
import { useQuasar } from 'quasar'

const $q = useQuasar()
const showEditDialog = ref(false)
const activeTab = ref('students')

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
  },
  scheduleDays: {
    type: Array
  }
})

const handleTabChange = (tab) => {
  if (tab === 'results') {
    router.reload({ 
      only: ['activity', 'attachedUsers'],
      preserveScroll: true,
      preserveState: true 
    })
  }
}

const handleEditActivity = async (formData) => {
  try {
    await axios.put(route('activities.update', formData.id), formData)
    showEditDialog.value = false
    
    $q.notify({
      type: 'positive',
      message: 'Занятие успешно обновлено',
      position: 'top-right'
    })
    
    // Обновляем данные на странице
    window.location.reload()
  } catch (error) {
    console.error('Ошибка при обновлении занятия:', error)
    $q.notify({
      type: 'negative',
      message: 'Ошибка при обновлении занятия',
      position: 'top-right'
    })
  }
}
</script>

<style scoped>
.statistics-card {
  background: #fff;
  border-radius: 8px;
  padding: 16px;
  height: 100%;
  box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
  transition: all 0.3s;
}

.statistics-card:hover {
  box-shadow: 0 3px 6px rgba(0,0,0,0.16), 0 3px 6px rgba(0,0,0,0.23);
}

.param-name {
  font-size: 0.9rem;
  font-weight: 500;
  color: #666;
  margin-bottom: 8px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.param-value {
  font-size: 1.1rem;
  color: #333;
  font-weight: 500;
}

@media (max-width: 599px) {
  .statistics-card {
    text-align: center;
    padding: 20px;
  }

  .param-name {
    font-size: 0.85rem;
    margin-bottom: 12px;
  }

  .param-value {
    font-size: 1rem;
  }
}
</style>