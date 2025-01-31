<template>
  <div class="q-pa-md">
    <div class="row q-mb-md">
      <q-select
        v-model="selectedTeam"
        :options="teams"
        option-label="name"
        option-value="id"
        label="Выберите команду"
        clearable
        class="col-12 col-sm-6"
        @update:model-value="filterUsers"
      />
    </div>

    <q-list separator>
      <q-item v-for="user in filteredUsers" :key="user.id">
        <q-item-section>
          <q-item-label>{{ user.last_name }} {{ user.first_name }}</q-item-label>
          <q-item-label caption lines="2">
            <div>Последний платеж: {{ formatDate(user.last_payment_date) }}</div>
            <div>Группы: {{ formatTeams(user.teams) }}</div>
          </q-item-label>
        </q-item-section>

        <q-item-section side>
          <div class="text-h5 text-weight-bold" :class="getLessonsClass(user.lessons_remaining)">
            {{ user.lessons_remaining }}
          </div>
          <div class="text-caption">занятий</div>
        </q-item-section>
      </q-item>
    </q-list>

    <div v-if="loading" class="row justify-center q-pa-md">
      <q-spinner color="primary" size="3em" />
    </div>

    <div v-if="!loading && filteredUsers.length === 0" class="text-center q-pa-md">
      Пользователи не найдены
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { date } from 'quasar'
import axios from 'axios'

const loading = ref(false)
const users = ref([])
const teams = ref([])
const selectedTeam = ref(null)

const filteredUsers = computed(() => {
  if (!selectedTeam.value) {
    return users.value
  }
  return users.value.filter(user => 
    user.teams.some(team => team.id === selectedTeam.value.id)
  )
})

const formatDate = (dateString) => {
  if (!dateString) return 'Нет данных'
  return date.formatDate(dateString, 'DD.MM.YYYY')
}

const formatTeams = (teams) => {
  if (!teams || teams.length === 0) return 'Нет групп'
  return teams.map(team => team.name).join(', ')
}

const getLessonsClass = (lessons) => {
  if (lessons <= 3) return 'text-negative'
  if (lessons <= 5) return 'text-warning'
  return 'text-positive'
}

const fetchData = async () => {
  try {
    loading.value = true
    const [usersResponse, teamsResponse] = await Promise.all([
      axios.get('/api/users-with-teams'),
      axios.get('/api/teams')
    ])
    users.value = usersResponse.data
    teams.value = teamsResponse.data
  } catch (error) {
    console.error('Ошибка при загрузке данных:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchData()
})
</script>

<style scoped>
.text-warning {
  color: #F2C037;
}
</style> 