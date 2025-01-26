<template>
  <div class="q-pa-md">
    <q-card>
      <q-card-section>
        <div class="text-h6">{{ isNewTeam ? 'Создание команды' : 'Редактирование команды' }}</div>
      </q-card-section>

      <q-card-section>
        <q-form @submit="saveTeam">
          <q-input
            v-model="team.name"
            label="Название команды"
            :rules="[val => !!val || 'Название обязательно']"
            class="q-mb-md"
          />

          <q-input
            v-model="team.description"
            type="textarea"
            label="Описание"
            class="q-mb-md"
          />

          <q-select
            v-model="team.status"
            :options="statusOptions"
            label="Статус"
            class="q-mb-md"
          />

          <div class="row justify-end">
            <q-btn
              type="submit"
              color="primary"
              :label="isNewTeam ? 'Создать' : 'Сохранить'"
              :loading="loading"
            />
          </div>
        </q-form>
      </q-card-section>
    </q-card>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import axios from 'axios'
import { useQuasar } from 'quasar'

const props = defineProps({
  initialTeam: {
    type: Object,
    default: () => ({
      name: '',
      description: '',
      status: 'active'
    })
  }
})

const $q = useQuasar()
const team = ref({ ...props.initialTeam })
const loading = ref(false)

const isNewTeam = computed(() => !team.value.id)

const statusOptions = [
  { label: 'Активна', value: 'active' },
  { label: 'Неактивна', value: 'inactive' }
]

const saveTeam = async () => {
  try {
    loading.value = true
    
    if (isNewTeam.value) {
      const response = await axios.post('/api/teams', team.value)
      team.value = response.data
      
      $q.notify({
        type: 'positive',
        message: 'Команда успешно создана',
        position: 'top'
      })
    } else {
      await axios.put(`/api/teams/${team.value.id}`, team.value)
      
      $q.notify({
        type: 'positive',
        message: 'Данные команды успешно обновлены',
        position: 'top'
      })
    }
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Произошла ошибка при сохранении данных',
      position: 'top'
    })
    console.error(error)
  } finally {
    loading.value = false
  }
}
</script> 