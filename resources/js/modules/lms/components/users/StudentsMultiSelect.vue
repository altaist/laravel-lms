<template>
  <div>  <!-- Добавляем корневой div для правильного наследования атрибутов -->
    <!-- Фильтры -->
    <div v-if="showSearch && filteredStudents.length > 10" class="row q-gutter-md q-mb-md">
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
        v-if="!hideTeams"
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
    <q-list bordered separator v-if="filteredStudents.length > 0">
      <q-item
        v-for="student in filteredStudents"
        :key="student.id"
        clickable
        @click="toggleStudentSelection(student)"
      >
        <q-item-section side>
          <q-checkbox
            v-model="selectedStudents"
            :val="student"
            :label="null"
          />
        </q-item-section>

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

    <!-- Кнопки управления -->
    <div class="row justify-end q-mt-md q-gutter-sm">
      <q-btn
        color="grey"
        label="Отменить"
        @click="clearSelection"
        :disable="selectedStudents.length === 0"
      />
      <q-btn
        color="primary"
        label="Выбрать"
        @click="confirmSelection"
        :disable="selectedStudents.length === 0"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import BalanceChip from '@/modules/lms/components/shared/BalanceChip.vue'

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
  showSearch: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['update:selected', 'confirm'])

const filter = ref('')
const selectedTeam = ref(null)
const selectedStudents = ref([])

const teamOptions = computed(() => {
  return props.teams.map(team => ({
    label: team.name,
    value: team.id,
    ...team
  }))
})

const filteredStudents = computed(() => {
  let filtered = props.students

  if (!props.hideTeams && selectedTeam.value) {
    filtered = filtered.filter(student => 
      student.teams?.some(team => team.id === selectedTeam.value)
    )
  }

  if (filter.value) {
    const searchTerm = filter.value.toLowerCase()
    filtered = filtered.filter(student => 
      student.name.toLowerCase().includes(searchTerm)
    )
  }

  return filtered
})

const toggleStudentSelection = (student) => {
  const index = selectedStudents.value.findIndex(s => s.id === student.id)
  if (index === -1) {
    selectedStudents.value.push(student)
  } else {
    selectedStudents.value.splice(index, 1)
  }
  emit('update:selected', selectedStudents.value)
}

const clearSelection = () => {
  selectedStudents.value = []
  emit('update:selected', [])
}

const confirmSelection = () => {
  emit('confirm', selectedStudents.value)
}

const getBalance = (student) => {
  const balance = student.balances?.find(b => b.coin_id === 2)
  return balance ? balance.amount : 0
}
</script> 