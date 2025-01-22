<template>

      <q-table
        :rows="filteredStudents"
        :columns="columns"
        row-key="id"
        :filter="filter"
        @row-click="onRowClick"
        class="cursor-pointer"
      >
        <template v-slot:top>
          <div class="row q-gutter-md">
            <q-input
              dense
              debounce="300"
              v-model="filter"
              placeholder="Поиск"
              class="col"
            >
              <template v-slot:append>
                <q-icon name="fa fa-search" />
              </template>
            </q-input>
            
            <q-select
              dense
              v-model="selectedTeam"
              :options="teamOptions"
              option-label="name"
              option-value="id"
              emit-value
              map-options
              label="Группа"
              clearable
              class="col"
            />
          </div>
        </template>
      </q-table>

</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  students: {
    type: Array,
    required: true
  },
  teams: {
    type: Array,
    required: true
  }
})

const filter = ref('')
const selectedTeam = ref(null)

const teamOptions = computed(() => {
  return props.teams.map(team => ({
    label: team.name,
    value: team.id,
    ...team
  }))
})

const filteredStudents = computed(() => {
  if (!selectedTeam.value) return props.students
  return props.students.filter(student => 
    student.teams?.some(team => team.id === selectedTeam.value)
  )
})

const columns = [
  {
    name: 'name',
    required: true,
    label: 'ФИО',
    align: 'left',
    field: row => row.name,
    sortable: true
  },
  {
    name: 'teams',
    required: true,
    label: 'Группы',
    align: 'left',
    field: row => row.teams?.map(team => team.name).join(', ') || '',
    sortable: true
  },
  // Добавьте другие необходимые колонки
]

const onRowClick = (evt, row) => {
  router.visit(route('teacher.student.details', { studentId: row.id }))
}
</script>

<style scoped>
.cursor-pointer >>> tbody tr {
  cursor: pointer;
}
</style> 