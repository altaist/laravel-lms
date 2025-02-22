<template>
  <div class="q-pa-md">
    <!-- Фильтр по группам -->
    <div class="q-mb-md">
      <q-select
        v-model="selectedTeam"
        :options="teams"
        option-label="name"
        option-value="id"
        label="Выберите группу"
        clearable
        emit-value
        map-options
        @clear="filterActivities"
        @update:model-value="filterActivities"
      />
    </div>

    <!-- Список активностей -->
    <div class="q-gutter-y-md">
      <!-- Сообщение если список пуст -->
      <div v-if="!filteredActivities.length" class="text-center q-pa-lg">
        <q-icon name="fa fa-calendar-xmark" size="48px" color="grey-6" />
        <div class="text-h6 text-grey-6 q-mt-md">
          {{ selectedTeam ? 'В выбранной группе нет занятий' : 'Список занятий пуст' }}
        </div>
      </div>

      <!-- Список активностей -->
      <template v-else>
        <q-card
          v-for="activity in filteredActivities"
          :key="activity.id"
          class="cursor-pointer"
        >
          <q-card-section>
            <div class="row justify-between items-start">
              <div class="col">
                <div @click="goToActivityDetails(activity.id)">
                  <div class="text-h6">{{ activity.name }}</div>
                  <div class="text-subtitle2">Группа: {{ activity.team?.name }}</div>
                </div>
                
                <!-- Обновленная кнопка удаления -->
                <div class="q-mt-sm">
                  <q-btn
                    v-if="!activity.started_at"
                    flat
                    round
                    color="negative"
                    icon="delete"
                    size="md"
                    @click.stop="confirmDeleteActivity(activity)"
                  >
                    <q-tooltip>Удалить занятие</q-tooltip>
                  </q-btn>
                </div>
              </div>
              
              <div class="col-auto">
                <div class="text-right" @click="goToActivityDetails(activity.id)">
                  <div class="text-subtitle1">{{ formatDate(activity.starting_at) }}</div>
                  <div class="text-caption">{{ formatTime(activity.starting_at) }}</div>
                </div>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </template>
    </div>

    <!-- FAB кнопка добавления -->
    <q-btn
      fab
      icon="add"
      color="primary"
      class="fixed-bottom-right"
      @click="showAddDialog = true"
    />

    <!-- Диалог добавления активности -->
    <q-dialog v-model="showAddDialog">
      <activity-form-new
        :teams="teams"
        :schedule-days="[]"
        :teamId="null"
        @save="saveActivity"
        @cancel="showAddDialog = false"
      />
    </q-dialog>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { date, useQuasar } from 'quasar'
import ActivityFormNew from '@/modules/lms/components/activities/ActivityFormNew.vue'
import axios from 'axios'

const $q = useQuasar()
const showAddDialog = ref(false)

const props = defineProps({
  activities: {
    type: Array,
    required: true
  },
  teams: {
    type: Array,
    required: true
  }
})

const selectedTeam = ref(null)

const filteredActivities = computed(() => {
  if (!selectedTeam.value) return props.activities
  return props.activities.filter(activity => activity.team_id === selectedTeam.value)
})

const formatDate = (dateString) => {
  return date.formatDate(dateString, 'DD.MM.YYYY')
}

const formatTime = (dateString) => {
  return date.formatDate(dateString, 'HH:mm')
}

const goToActivityDetails = (activityId) => {
  router.visit(route('teacher.activity.details', activityId))
}

const filterActivities = () => {
  const params = selectedTeam.value ? { team_id: selectedTeam.value } : {}
  
  router.visit(route('activities.all'), {
    data: params,
    preserveState: true,
    preserveScroll: true,
    only: ['activities'],
    replace: true
  })
}

const saveActivity = async (formData) => {
  try {
    await axios.post(route('activities.store'), {
      team_id: formData.team_id,
      name: formData.name,
      starting_at: formData.starting_at,
      description: formData.description,
      duration: 60
    })
    
    showAddDialog.value = false
    
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
      
      // Обновляем список активностей
      router.reload({ only: ['activities'] })
      
      $q.notify({
        type: 'positive',
        message: 'Занятие успешно удалено',
        position: 'top-right'
      })
    } catch (error) {
      let errorMessage = 'Ошибка при удалении занятия'
      
      if (error.response?.data?.message) {
        errorMessage = error.response.data.message
      }
      
      $q.notify({
        type: 'negative',
        message: errorMessage,
        position: 'top-right'
      })
    }
  })
}
</script>

<style scoped>
.fixed-bottom-right {
  position: fixed;
  right: 44px;
  bottom: 44px;
  z-index: 2000;
}
</style> 