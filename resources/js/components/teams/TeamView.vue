<template>
  <div class="q-pa-md">
    <q-card>
      <q-card-section>
        <div class="text-h6">Информация о команде</div>
      </q-card-section>

      <q-card-section>
        <div class="row q-mb-md">
          <div class="col-12 text-subtitle1">Название команды</div>
          <div class="col-12">{{ teamData.team.name }}</div>
        </div>

        <div class="row q-mb-md">
          <div class="col-12 text-subtitle1">Описание</div>
          <div class="col-12">{{ teamData.team.description || 'Нет описания' }}</div>
        </div>

        <div class="row q-mb-md">
          <div class="col-12 text-subtitle1">Статус</div>
          <div class="col-12">
            <q-badge :color="teamData.team.status === 'active' ? 'positive' : 'grey'">
              {{ teamData.team.status === 'active' ? 'Активна' : 'Неактивна' }}
            </q-badge>
          </div>
        </div>

        <div class="text-subtitle1 q-mb-sm">Участники команды</div>
        <q-list separator>
          <q-item v-for="member in teamData.members" :key="member.id">
            <q-item-section>
              <q-item-label>{{ member.name }}</q-item-label>
              <q-item-label caption>
                Последнее посещение: {{ formatDate(member.last_visit_date) }}
              </q-item-label>
            </q-item-section>

            <q-item-section side>
              <q-chip
                :color="member.remaining_lessons > 3 ? 'positive' : 'warning'"
                text-color="white"
              >
                {{ member.remaining_lessons }} занятий
              </q-chip>
            </q-item-section>
          </q-item>
        </q-list>

        <div v-if="!teamData.members.length" class="text-center q-pa-md text-grey">
          В команде пока нет участников
        </div>
      </q-card-section>
    </q-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useQuasar } from 'quasar'
import { date } from 'quasar'

const props = defineProps({
  teamId: {
    type: [Number, String],
    required: true
  }
})

const $q = useQuasar()
const teamData = ref({
  team: {
    name: '',
    description: '',
    status: ''
  },
  members: []
})

const formatDate = (dateString) => {
  if (!dateString) return 'Нет данных'
  return date.formatDate(dateString, 'DD.MM.YYYY')
}

const loadTeamData = async () => {
  try {
    const response = await axios.get(`/api/teams/${props.teamId}/with-members`)
    teamData.value = response.data
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Ошибка при загрузке данных команды',
      position: 'top'
    })
    console.error(error)
  }
}

onMounted(() => {
  loadTeamData()
})
</script> 