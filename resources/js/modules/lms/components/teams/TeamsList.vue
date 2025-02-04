<template>
  <div>
    <!-- Поиск или фильтрация -->
    <div class="row q-mb-md">
      <q-input
        v-model="searchQuery"
        label="Поиск по названию"
        clearable
        class="col-12"
      >
        <template v-slot:append>
          <q-icon name="search" />
        </template>
      </q-input>
    </div>

    <q-list separator>
      <q-item
        v-for="team in filteredTeams"
        :key="team.id"
        clickable
        class="q-px-sm q-my-md"
        @click="router.visit(route('teacher.team.details', team.id))"
      >
        <q-item-section>
          <q-item-label >{{ team.name }}</q-item-label>
          <!-- Удалено отображение типа команды -->
          <!-- <q-item-label caption>
            Тип: {{ team.type || 'Не указан' }}
          </q-item-label> -->
          <q-item-label caption v-if="team.description">
            {{ team.description }}
          </q-item-label>
        </q-item-section>

        <q-item-section side>
          <div class="text-h6 text-weight-bold">
            {{ team.users?.length || 0 }}
          </div>
          <!-- Удалено слово "участников" -->
          <!-- <div class="text-caption">участников</div> -->
        </q-item-section>
      </q-item>
    </q-list>

    <div v-if="filteredTeams.length === 0" class="text-center">
      Группы не найдены
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  teams: {
    type: Array,
    required: true,
    default: () => []
  }
})

const searchQuery = ref('')

const filteredTeams = computed(() => {
  if (!searchQuery.value) {
    return props.teams
  }
  const query = searchQuery.value.toLowerCase()
  return props.teams.filter(team => 
    team.name.toLowerCase().includes(query) ||
    (team.type && team.type.toLowerCase().includes(query))
  )
})
</script>

<style scoped>
.q-item {
  cursor: pointer;
  transition: background-color 0.3s;
}

.q-item:hover {
  background-color: rgba(0, 0, 0, 0.03);
}
</style> 