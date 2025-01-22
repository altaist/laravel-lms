<template>
  <page-layout>
    <div class="q-pa-md">
      <div class="text-h5 q-mb-md">Информация о студенте</div>
      
      <q-card class="q-mb-md">
        <q-card-section>
          <div class="text-h6">Основная информация</div>
          <div class="q-mt-sm">
            <div><strong>ФИО:</strong> {{ student.name }}</div>
            <div><strong>Email:</strong> {{ student.email }}</div>
          </div>
        </q-card-section>
      </q-card>

      <q-card class="q-mb-md">
        <q-card-section>
          <div class="text-h6">Группы</div>
          <q-list>
            <q-item v-for="team in student.teams" :key="team.id">
              <q-item-section>{{ team.name }}</q-item-section>
            </q-item>
          </q-list>
        </q-card-section>
      </q-card>

      <q-card>
        <q-card-section>
          <div class="text-h6">Активность</div>
          <!-- Здесь можно добавить дополнительную информацию -->
        </q-card-section>
      </q-card>
    </div>
  </page-layout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'

defineProps({
  student: {
    type: Object,
    required: true
  }
})
</script> 