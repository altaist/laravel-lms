<template>

      <q-table
        :rows="students"
        :columns="columns"
        row-key="id"
        :filter="filter"
      >
        <template v-slot:top>
          <q-input
            dense
            debounce="300"
            v-model="filter"
            placeholder="Поиск"
          >
            <template v-slot:append>
              <q-icon name="fa fa-search" />
            </template>
          </q-input>
        </template>
      </q-table>

</template>

<script setup>
import { ref } from 'vue'

defineProps({
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
    name: 'team',
    required: true,
    label: 'Группа',
    align: 'left',
    field: row => row.team?.name,
    sortable: true
  },
  // Добавьте другие необходимые колонки
]
</script> 