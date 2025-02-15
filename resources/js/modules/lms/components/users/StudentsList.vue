<template>
  <!-- Фильтры -->
  <div v-if="showSearch" class="row q-col-gutter-md q-mb-md">

    <div class="col-6 col-sm-6 col-md-4">
      <q-select
        dense
        v-model="debtFilter"
        :options="debtFilterOptions"
        label="Статус баланса"
      clearable
      />
    </div>

    <div class="col-6 col-sm-6 col-md-4">
      <q-select
      v-if="!hideTeamsFilter"
      dense
      v-model="selectedTeam"
      :options="teamOptions"
      option-label="name"
      option-value="id"
      emit-value
      map-options
      label="Группа"
      clearable
    />

    </div>
    <div class="col-12 col-sm-12 col-md-4">
      <q-input
      dense
      debounce="300"
      v-model="filter"
      placeholder="Поиск по имени"
    >
      <template v-slot:append>
        <q-icon name="fa fa-search" />
      </template>
    </q-input>

    </div>

  </div>
  <div class="q-mt-lg">
  <!-- Список учеников -->
  <q-list bordered separator v-if="filteredStudents.length > 0" >
    <q-item
      v-for="student in filteredStudents"
      :key="student.id"
      clickable
      @click="handleStudentClick(student)"
    >
      <q-item-section>
        <q-item-label>{{ student.name }}</q-item-label>
        <q-item-label v-if="!hideTeams" caption>
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
  <div v-else class="text-center q-pa-md">
    Ученики не найдены
  </div>

  </div>

  <!-- Диалог с деталями ученика -->
  <q-dialog v-model="showDialog" persistent :max-width="'90vw'" :width="'90vw'">
    <q-card class="full-width q-my-xl">
      <q-card-section class="row items-center">
        <div class="text-h6">Информация об ученике</div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>

      <q-card-section v-if="selectedStudent">
        <student-info :student="selectedStudent" />
      </q-card-section>

      <q-card-actions align="right">
        <q-btn
          class="q-mb-md q-mr-sm"
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
import BalanceChip from '@/modules/lms/components/shared/BalanceChip.vue'
import StudentInfo from '@/modules/lms/components/users/StudentInfo.vue'

const props = defineProps({
  students: {
    type: Array,
    required: true
  },
  teams: {
    type: Array,
    required: true
  },
  hideTeams: {
    type: Boolean,
    default: false
  },
  hideTeamsFilter: {
    type: Boolean,
    default: false
  },
  selectionMode: {
    type: Boolean,
    default: false
  },
  showSearch: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['student-selected'])

const filter = ref('')
const selectedTeam = ref(null)
const showDialog = ref(false)
const selectedStudent = ref(null)

const debtFilter = ref(null)
const debtFilterOptions = [
  { label: 'Долг', value: 'debt' },
  { label: 'Скоро долг', value: 'soon_debt' },
  { label: 'Без долгов', value: 'no_debt' }
]

const teamOptions = computed(() => {
  return props.teams.map(team => ({
    label: team.name,
    value: team.id,
    ...team
  }))
})

const filteredStudents = computed(() => {
  let filtered = props.students

  // Фильтрация по долгам
  if (debtFilter.value) {
    filtered = filtered.filter(student => {
      const balance = getBalance(student)
      switch (debtFilter.value.value) {
        case 'debt':
          return balance <= 0
        case 'soon_debt':
          return balance > 0 && balance < 2
        case 'no_debt':
          return balance >= 2
        default:
          return true
      }
    })
  }

  // Фильтрация по группе
  if (!props.hideTeams && selectedTeam.value) {
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

const handleStudentClick = (student) => {
  if (props.selectionMode) {
    emit('student-selected', student)
  } else {
    selectedStudent.value = student
    showDialog.value = true
  }
}

const openStudentDetails = () => {
  if (selectedStudent.value) {
    router.visit(route('teacher.student.details', { studentId: selectedStudent.value.id }))
  }
}

const getBalance = (student) => {
  const balance = student.balances?.find(b => b.coin_id === 2)
  return balance ? balance.amount : 0
}
</script> 