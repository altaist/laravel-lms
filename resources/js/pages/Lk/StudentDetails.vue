<template>
  <page-layout
    title="Студент"
  >
    <div class="q-pa-md">
      <div class="text-h5 q-mb-md">{{ student.name }}</div>
      
      <q-tabs v-model="tab" class="q-mb-md">
        <q-tab name="teams" label="Команды" />
        <q-tab name="activities" label="Занятия" />
        <q-tab name="payments" label="Платежи" />
      </q-tabs>

      <q-tab-panels v-model="tab">
        <q-tab-panel name="teams">
          <q-card>
            <q-card-section>
              <div class="text-h6">Команды</div>
              <q-list>
                <q-item v-for="team in teams" :key="team.id">
                  <q-item-section>
                    <div>{{ team.name }}</div>
                    <div class="text-caption">{{ team.schedule }}</div>
                  </q-item-section>
                  <q-item-section side>
                    <q-toggle
                      v-model="team.selected"
                      @update:model-value="(val) => toggleTeamMembership(team.id, val)"
                      color="primary"
                    />
                  </q-item-section>
                </q-item>
              </q-list>
            </q-card-section>
          </q-card>
        </q-tab-panel>
        <q-tab-panel name="activities">
          <q-card>
            <q-card-section>
              <div class="text-h6">Занятия</div>
              <q-list>
                <q-item v-for="activity in activities" :key="activity.id">
                  <q-item-section>
                    {{ activity.name }} - {{ activity.date }}
                  </q-item-section>
                </q-item>
              </q-list>
            </q-card-section>
          </q-card>
        </q-tab-panel>
        <q-tab-panel name="payments">
          <q-card>
            <q-card-section>
              <div class="text-h6">Платежи</div>
              <q-list>
                <q-item v-for="payment in payments" :key="payment.id">
                  <q-item-section>{{ payment.amount }}</q-item-section>
                </q-item>
              </q-list>
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
import { ref, onMounted } from 'vue'

const props = defineProps({
  student: Object,
  teams: Array,
  activities: Array,
  payments: Array,
})

const tab = ref('teams')

const toggleTeamMembership = async (teamId, isSelected) => {
  await fetch(route('api.student.toggle-team', { team_id: teamId, student_id: props.student.id }), {
    method: isSelected ? 'POST' : 'DELETE',
  });
};
</script> 