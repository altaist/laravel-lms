<template>
  <div>


    <!-- Список пользователей -->
    <div class="row q-col-gutter-md">
      <div class="col-12">
        <q-list separator>
          <!-- Заголовок для пользователей группы -->
          <q-item-label header>Ученики группы</q-item-label>
          
          <!-- Список пользователей группы -->
          <q-item v-for="user in teamUsers" :key="user.id">
            <q-item-section>
              <q-item-label>{{ user.name }}</q-item-label>
              <q-item-label caption>{{ user.email }}</q-item-label>
            </q-item-section>

            <q-item-section side>
              <q-toggle
                v-model="userAttachments[user.id]"
                @update:model-value="handleToggle(user)"
                :loading="loadingStates[user.id]"
              />
            </q-item-section>
          </q-item>

          <!-- Разделитель между списками -->
          <q-separator spaced inset />
          
          <!-- Заголовок для доступных пользователей -->
          <q-item-label header>Доступные ученики</q-item-label>
          
          <!-- Список доступных пользователей -->
          <q-item v-for="user in availableUsers" :key="user.id">
            <q-item-section>
              <q-item-label>{{ user.name }}</q-item-label>
              <q-item-label caption>{{ user.email }}</q-item-label>
            </q-item-section>

            <q-item-section side>
              <q-toggle
                v-model="userAttachments[user.id]"
                @update:model-value="handleToggle(user)"
                :loading="loadingStates[user.id]"
              />
            </q-item-section>
          </q-item>
        </q-list>
      </div>
    </div>

    <!-- Диалог добавления учеников из специальной группы -->
    <q-dialog v-model="showAddSpecialStudentsDialog">
      <q-card style="width: 700px; max-width: 80vw;">
        <q-card-section class="row items-center">
          <div class="text-h6">Добавить учеников из специальной группы</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <!--q-card-section>
          <students-multi-select
            :students="availableUsers"
            :teams="[]"
            v-model:selected="selectedStudentsToAdd"
            @confirm="addStudentsToActivity"
          />
        </q-card-section-->
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useQuasar } from 'quasar'
import axios from 'axios'

const $q = useQuasar()

const props = defineProps({
  activity: {
    type: Object,
    required: true
  },
  teamUsers: {
    type: Array,
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

const columns = [
  { name: 'name', label: 'Имя', field: 'name', align: 'left' },
  { name: 'email', label: 'Email', field: 'email', align: 'left' },
  { name: 'actions', label: 'Прикреплен', field: 'actions', align: 'center' }
]

const loadingStates = ref({})
const userAttachments = ref({})
const showAddSpecialStudentsDialog = ref(false)
const selectedStudentsToAdd = ref([])

// Инициализация состояний прикрепления пользователей
onMounted(() => {
  const attachedIds = new Set(props.attachedUsers.map(user => user.id))
  // Инициализируем состояния для обоих списков пользователей
  props.teamUsers.forEach(user => {
    userAttachments.value[user.id] = attachedIds.has(user.id)
  })
  props.availableUsers.forEach(user => {
    userAttachments.value[user.id] = attachedIds.has(user.id)
  })
})

const handleToggle = async (user) => {
  loadingStates.value[user.id] = true
  
  try {
    if (userAttachments.value[user.id]) {
      // Прикрепляем пользователя
      await axios.post(route('activities.add-students'), {
        activityId: props.activity.id,
        studentIds: [user.id]
      })
      
      $q.notify({
        type: 'positive',
        message: 'Ученик успешно прикреплен к занятию',
        position: 'top-right'
      })
    } else {
      // Открепляем пользователя
      await axios.post(route('activities.remove-students'), {
        activityId: props.activity.id,
        studentIds: [user.id]
      })
      
      $q.notify({
        type: 'positive',
        message: 'Ученик успешно откреплен от занятия',
        position: 'top-right'
      })
    }
  } catch (error) {
    console.error('Ошибка при обновлении статуса ученика:', error)
    // Возвращаем переключатель в предыдущее состояние
    userAttachments.value[user.id] = !userAttachments.value[user.id]
    
    $q.notify({
      type: 'negative',
      message: 'Ошибка при обновлении статуса ученика',
      position: 'top-right'
    })
  } finally {
    loadingStates.value[user.id] = false
  }
}

const addStudentsToActivity = async () => {
  // Реализация добавления учеников из специальной группы
  // ...
}
</script> 