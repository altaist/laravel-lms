<template>
  <!-- Фильтры -->
  <div class="row q-gutter-md q-mb-md">
    <q-input
      dense
      debounce="300"
      v-model="filter"
      placeholder="Поиск по имени"
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

  <!-- Список учеников -->
  <q-list bordered separator>
    <q-item
      v-for="student in filteredStudents"
      :key="student.id"
      clickable
      @click="showStudentDetails(student)"
    >
      <q-item-section>
        <q-item-label>{{ student.name }}</q-item-label>
        <q-item-label caption>
          {{ student.teams?.map(team => team.name).join(', ') || 'Нет групп' }}
        </q-item-label>
      </q-item-section>
      
      <q-item-section side>
        <balance-chip
          :balance="getBalance(student)"
        />
      </q-item-section>
    </q-item>
  </q-list>

  <!-- Сообщение, если учеников нет -->
  <div v-if="filteredStudents.length === 0" class="text-center q-pa-md">
    Ученики не найдены
  </div>

  <!-- Диалог с деталями ученика -->
  <q-dialog v-model="showDialog" persistent :max-width="'90vw'" :width="'90vw'">
    <q-card>
      <q-card-section class="row items-center">
        <div class="text-h6">Информация об ученике</div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>

      <q-card-section v-if="selectedStudent">
        <div class="q-gutter-y-md">
          <div class="row q-col-gutter-md">
            <div class="col-12 col-sm-6">
              <div class="text-subtitle2">ФИО</div>
              <div>{{ selectedStudent.name }}</div>
            </div>
            <div class="col-12 col-sm-6">
              <div class="text-subtitle2">Группы</div>
              <div>{{ selectedStudent.teams?.map(team => team.name).join(', ') || 'Нет групп' }}</div>
            </div>
          </div>
        </div>
      </q-card-section>

      <!-- Добавляем секцию с кнопкой -->
      <q-card-actions align="right">
        <q-btn
          color="primary"
          label="Открыть в карточке"
          @click="openStudentDetails"
        />
      </q-card-actions>

    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import BalanceChip from '@/Components/BalanceChip.vue'

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
const showDialog = ref(false)
const selectedStudent = ref(null)

const teamOptions = computed(() => {
  return props.teams.map(team => ({
    label: team.name,
    value: team.id,
    ...team
  }))
})

const filteredStudents = computed(() => {
  let filtered = props.students

  // Фильтрация по группе
  if (selectedTeam.value) {
    filtered = filtered.filter(student => 
      student.teams?.some(team => team.id === selectedTeam.value)
    )
  }

  // Фильтрация по имени
  if (filter.value) {
    const searchTerm = filter.value.toLowerCase()
    filtered = filtered.filter(student => 
      student.name.toLowerCase().includes(searchTerm)
    )
  }

  return filtered
})

const showStudentDetails = (student) => {
  selectedStudent.value = student
  showDialog.value = true
}

const openStudentDetails = () => {
  if (selectedStudent.value) {
    const onRowClick = (evt, row) => {
}
    router.visit(route('teacher.student.details', { studentId: selectedStudent.value.id }))
  }
}

const getBalance = (student) => {
  const balance = student.balances?.find(b => b.coin_id === 2)
  return balance ? balance.amount : 0
}
</script> 