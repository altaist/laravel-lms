<template>
  <page-layout
    title="Студент"
  >
    <div class="q-pa-md">
      
      <q-dialog v-model="showEditDialog">
        <q-card class="q-pa-md" style="min-width: 500px">
          <q-card-section class="row items-center">
            <div class="text-h6">Редактировать</div>
            <q-space />
            <q-btn icon="close" flat round dense v-close-popup />
          </q-card-section>

          <q-card-section>
            <user-edit
              :user="student"
              :teams="teams"
              @saved="onUserEdited"
              @cancelled="showEditDialog = false"
            />
          </q-card-section>
        </q-card>
      </q-dialog>

      <q-card class="q-mb-md">
        <q-card-section>
          <div class="row items-center">
            <div class="col">
              <div class="text-h5">{{ student.name }}</div>
              <div class="text-caption">{{ student.email }}</div>
            </div>
            <div class="col-auto">
              <balance-chip
                :balance="getBalance(student)"
              />
            </div>
          </div>
        </q-card-section>
      </q-card>
      
      <q-tabs v-model="tab" class="q-mb-md">
        <q-tab name="info" label="Инфо" />
        <q-tab name="teams" label="Команды" />
        <q-tab name="activities" label="Занятия" />
        <q-tab name="payments" label="Платежи" />
      </q-tabs>

      <q-tab-panels v-model="tab" class="q-px-none">
        <q-tab-panel name="info" class="q-pa-sm">
          <q-card>
            <q-card-section>
              <div class="text-h6">Информация о студенте</div>
              <div class="q-gutter-y-md q-mt-md">
                <div class="row q-col-gutter-md">
                  <div class="col-12 col-sm-6">
                    <q-input
                      readonly
                      outlined
                      dense
                      label="Фамилия"
                      :model-value="student.person?.lastName"
                    />
                  </div>
                  <div class="col-12 col-sm-6">
                    <q-input
                      readonly
                      outlined
                      dense
                      label="Имя"
                      :model-value="student.person?.firstName"
                    />
                  </div>
                </div>

                <q-input
                  readonly
                  outlined
                  dense
                  label="ФИО родителя"
                  :model-value="student.person?.parentFio"
                />

                <q-input
                  readonly
                  outlined
                  dense
                  label="Телефон родителя"
                  :model-value="formatPhone(student.person?.parentTel)"
                >
                  <template v-slot:append>
                    <q-btn
                      flat
                      round
                      icon="phone"
                      color="primary"
                      @click="callPhone(student.person?.parentTel)"
                    />
                  </template>
                </q-input>

                <div class="row q-col-gutter-md">
                  <div class="col-12 col-sm-6">
                    <q-input
                      readonly
                      outlined
                      dense
                      label="Пол"
                      :model-value="student.person?.gender === 'M' ? 'Мужской' : 'Женский'"
                    />
                  </div>
                  <div class="col-12 col-sm-6">
                    <q-input
                      readonly
                      outlined
                      dense
                      label="Смена"
                      :model-value="student.person?.shift"
                    />
                  </div>
                </div>

                <q-input
                  readonly
                  outlined
                  dense
                  label="Дата рождения"
                  :model-value="formatDate(student.person?.birthDate)"
                />
              </div>
            </q-card-section>
          </q-card>
          <div class="q-my-md">
        <q-btn
          color="primary"
          icon="edit"
          label="Изменить"
          @click="showEditDialog = true"
        />
      </div>
        </q-tab-panel>
        <q-tab-panel name="teams" class="q-pa-sm">
          <q-card>
            <q-card-section>
              <div class="text-h6">Группы ученика</div>
              <q-list>
                <q-item v-for="team in student.teams" :key="team.id">
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
          </q-card><q-card>
            <q-card-section>
              <div class="text-h6">Все группы</div>
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
        <q-tab-panel name="activities" class="q-pa-sm">
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
        <q-tab-panel name="payments" class="q-pa-sm">
          <div class="q-mb-md">
            <q-btn
              color="primary"
              icon="add"
              label="Новый платеж"
              @click="showNewPaymentDialog = true"
            />
          </div>
          
          <payments-list :payments="payments" />

          <!-- Диалог нового платежа -->
          <q-dialog v-model="showNewPaymentDialog">
            <new-payment-dialog
              :user="student"
              @saved="onPaymentSaved"
            />
          </q-dialog>
        </q-tab-panel>
      </q-tab-panels>

      
    </div>
  </page-layout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'
import BalanceChip from '@/Components/BalanceChip.vue'
import UserEdit from '@/modules/lms/Components/users/UserEdit.vue'
import { router } from '@inertiajs/vue3'
import { date } from 'quasar'
import PaymentsList from '@/modules/lms/Components/payments/PaymentsList.vue'
import NewPaymentDialog from '@/modules/lms/Components/lk/teacher/NewPaymentDialog.vue'

const props = defineProps({
  student: Object,
  teams: Array,
  activities: Array,
  payments: Array,
})

const tab = ref('info')
const showEditDialog = ref(false)
const showNewPaymentDialog = ref(false)

const getBalance = (student) => {
  const balance = student.balances?.find(b => b.coin_id === 2)
  return balance ? balance.amount : 0
}

const toggleTeamMembership = async (teamId, isSelected) => {
  await fetch(route('api.student.toggle-team', { team_id: teamId, student_id: props.student.id }), {
    method: isSelected ? 'POST' : 'DELETE',
  });
};

const onUserEdited = () => {
  showEditDialog.value = false;
  router.reload({ only: ['student'] });
}

const onPaymentSaved = () => {
  showNewPaymentDialog.value = false;
  router.reload({ only: ['payments'] });
}

// Функция форматирования телефона
const formatPhone = (phone) => {
  if (!phone) return '';
  return `+7 ${phone}`;
}

// Функция форматирования даты
const formatDate = (dateStr) => {
  if (!dateStr) return '';
  return date.formatDate(dateStr, 'DD.MM.YYYY');
}

// Функция для звонка
const callPhone = (phone) => {
  if (!phone) return;
  window.location.href = `tel:+7${phone}`;
}
</script> 