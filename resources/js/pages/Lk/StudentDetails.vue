<template>
  <page-layout
    title="Ученик"
  >
    <div class="q-pa-md">
      <div class="text-h5 q-mb-md">{{ student.name }}</div>
      
      <q-tabs v-model="tab" class="q-mb-md">
        <q-tab name="groups" label="Группы" />
        <q-tab name="classes" label="Занятия" />
        <q-tab name="payments" label="Платежи" />
      </q-tabs>

      <q-tab-panels v-model="tab">
        <q-tab-panel name="groups">
          <q-card>
            <q-card-section>
              <div class="text-h6">Группы</div>
              <q-list>
                <q-item v-for="team in student.teams" :key="team.id">
                  <q-item-section>{{ team.name }}</q-item-section>
                </q-item>
              </q-list>
            </q-card-section>
          </q-card>
        </q-tab-panel>
        <q-tab-panel name="classes">
          <q-card>
            <q-card-section>
              <div class="text-h6">Занятия</div>
              <!-- Здесь можно добавить информацию о занятиях -->
            </q-card-section>
          </q-card>
        </q-tab-panel>
        <q-tab-panel name="payments">
          <q-card>
            <q-card-section>
              <div class="text-h6">Платежи</div>
              <!-- Здесь можно добавить информацию о платежах -->
            </q-card-section>
          </q-card>
        </q-tab-panel>
      </q-tab-panels>

      <q-card class="q-mb-md">
        <q-card-section>
          <div class="text-h6">Основная информация</div>
          <div class="q-mt-sm">
            <div><strong>ФИО:</strong> {{ student.name }}</div>
            <div><strong>Email:</strong> {{ student.email }}</div>
          </div>
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
import { ref } from 'vue'

const tab = ref('groups')

defineProps({
  student: {
    type: Object,
    required: true
  }
})
</script> 