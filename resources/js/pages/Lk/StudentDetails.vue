<template>
  <page-layout
    title="Студент"
    right-btn-icon="fa-solid fa-pen"
    @click:header:right="showEditDialog = true"
  >
    <div class="q-pa-md">
      
      <q-dialog v-model="showEditDialog">
        <q-card class="full-width" style="max-width: 900px; margin: 20px;">
          <q-card-section class="row items-center">
            <div class="text-h6">Редактировать</div>
            <q-space />
            <q-btn icon="close" flat round dense v-close-popup />
          </q-card-section>
          

          <q-card-section class="q-pa-sm">
            <user-edit
              :user="student"
              :teams="teams"
              :can="$page.props.can"
              @saved="onUserEdited"
              @cancelled="showEditDialog = false"
              @linkGenerated="onLoginLinkGenerated"
            />
          </q-card-section>
        </q-card>
      </q-dialog>

      <q-card class="q-mb-md">
        <q-card-section>
          <div class="row items-center">
            <div class="col">
              <div class="text-h5">{{ student.person?.first_name || student.name }} {{ student.person?.last_name || '' }}</div>
              <div class="text-caption">
                <a 
    :href="`tel:${student.person?.parent_tel}`" 
    class="text-primary"
    v-if="student.person?.parent_tel"
  >
    {{ formatPhone(student.person.parent_tel) }}
  </a>
</div>
            </div>
            <div class="col-auto" @click="showBalancesDialog = true">
              <balance-chip
                :balance="getBalance(student)"
              />
            </div>
          </div>
        </q-card-section>
      </q-card>
      
      <q-tabs v-model="tab" class="q-mb-md">
        <q-tab name="info" label="Инфо" />
        <q-tab name="activities" label="Занятия" />
        <q-tab name="payments" label="Платежи" />
      </q-tabs>

      <q-tab-panels v-model="tab" class="q-px-none">
        <q-tab-panel name="info" class="q-pa-none">
          <div class="q-py-md">
            <div class="text-h4 q-mb-md">Группы</div>
            <q-list separator class="full-width">
            <q-item
              v-for="team in student.teams"
              :key="team.id"
              clickable
              v-ripple
              @click="() => router.visit(route('teacher.team.details', team.id))"
            >
              <q-item-section>
                <q-item-label>{{ team.name }}</q-item-label>
                <q-item-label caption>
                  Количество учеников: {{ team.students_count || 0 }}
                </q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-icon name="chevron_right" color="grey" />
              </q-item-section>
            </q-item>
            <q-item v-if="!student.teams?.length">
              <q-item-section>
                <q-item-label class="text-grey">
                  Студент не состоит ни в одной группе
                </q-item-label>
              </q-item-section>
            </q-item>
          </q-list>
          </div>
          <div class="q-py-md">
            <div class="text-h4 q-mb-md">Информация</div>
            <student-info 
            ref="studentInfo"
            :student="student" 
          />
          <div class="q-my-md">
            <q-btn
              color="primary"
              icon="edit"
              label="Изменить"
              @click="showEditDialog = true"
            />
          </div>
          </div>


        </q-tab-panel>
        <q-tab-panel name="groups" class="q-pa-none">
          <q-list separator class="full-width">
            <q-item
              v-for="team in student.teams"
              :key="team.id"
              clickable
              v-ripple
              @click="() => router.visit(route('teacher.team.details', team.id))"
            >
              <q-item-section>
                <q-item-label>{{ team.name }}</q-item-label>
                <q-item-label caption>
                  Количество учеников: {{ team.students_count || 0 }}
                </q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-icon name="chevron_right" color="grey" />
              </q-item-section>
            </q-item>
            <q-item v-if="!student.teams?.length">
              <q-item-section>
                <q-item-label class="text-grey">
                  Студент не состоит ни в одной группе
                </q-item-label>
              </q-item-section>
            </q-item>
          </q-list>
        </q-tab-panel>
        <q-tab-panel name="teams" class="q-pa-sm">
          <q-card>
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
        <q-tab-panel name="activities" class="q-pa-none">
        <div v-if="activities.length">
          <q-list>
            <q-item v-for="activity in activities" :key="activity.id" 
            clickable v-ripple @click="() => router.visit(route('teacher.activity.details', activity.id))">
              <q-item-section>
                {{ activity.name }} - {{ activity.date }}
              </q-item-section>
            </q-item>
          </q-list>
        </div>
        <div class="text-center q-mt-md" v-else>
          Студент не имеет занятий
        </div>

        </q-tab-panel>
        <q-tab-panel name="payments" class="q-pa-none">
          <div class="row q-col-gutter-sm">
            <div class="col-6">
              <q-btn
                color="secondary"
                icon="add"
                label="Новая корректировка"
                stack
                class="full-width"
                @click="showNewCreditDialog = true"
              />
            </div>
            <div class="col-6">
              <q-btn
                color="secondary"
                icon="history"
                label="История начислений"
                stack
                class="full-width"
                @click="loadCreditsAndShowDialog"
              />
            </div>
            <div class="col-6">
              <q-btn
                color="primary"
                icon="add"
                label="Новый платеж"
                stack
                class="full-width"
                @click="showNewPaymentDialog = true"
              />
            </div>
            
          </div>
          <div class="q-mt-lg">
            <div class="text-h4 q-mb-md">Платежи</div>
            <payments-list :payments="payments" />
          </div>
          

          <!-- Диалог нового платежа -->
          <q-dialog v-model="showNewPaymentDialog">
            <new-payment-dialog
              :user="student"
              @saved="onPaymentSaved"
            />
          </q-dialog>

          <!-- Диалог истории начислений -->
          <q-dialog v-model="showCreditsDialog">
            <user-credits-dialog :credits="userCredits" />
          </q-dialog>

          <q-dialog v-model="showNewCreditDialog">
            <new-credit-dialog
              :user="student"
              :coins="student.coins"
              @saved="onCreditSaved"
            />
          </q-dialog>
        </q-tab-panel>
      </q-tab-panels>

      
    </div>

    <!-- Добавить новый диалог перед закрывающим тегом page-layout -->
    <q-dialog v-model="showBalancesDialog">
      <q-card class="q-pa-md full-width" >
        <q-card-section class="row items-center">
          <div class="text-h6">Баланс</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section>
          <user-balances-list :student="student" />
        </q-card-section>
      </q-card>
    </q-dialog>
  </page-layout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'
import BalanceChip from '@/modules/lms/components/shared/BalanceChip.vue'
import UserEdit from '@/modules/lms/components/users/UserEdit.vue'
import StudentInfo from '@/modules/lms/components/users/StudentInfo.vue'
import { router } from '@inertiajs/vue3'
import { date } from 'quasar'
import PaymentsList from '@/modules/lms/components/payments/PaymentsList.vue'
import NewPaymentDialog from '@/modules/lms/components/lk/teacher/NewPaymentDialog.vue'
import UserCreditsDialog from '@/modules/lms/components/credits/UserCreditsDialog.vue'
import NewCreditDialog from '@/modules/lms/components/credits/NewCreditDialog.vue'
import UserBalancesList from '@/modules/lms/components/users/UserBalancesList.vue'

const props = defineProps({
  student: Object,
  teams: Array,
  activities: Array,
  payments: Array,
  coins: Array,
})

const tab = ref('info')
const showEditDialog = ref(false)
const showNewPaymentDialog = ref(false)
const showCreditsDialog = ref(false)
const showNewCreditDialog = ref(false)
const userCredits = ref([])
const studentInfo = ref(null)
const showBalancesDialog = ref(false)

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

const onCreditSaved = () => {
  showNewCreditDialog.value = false
  router.reload({ only: ['student'] })
}

const loadCreditsAndShowDialog = async () => {
  try {
    const response = await fetch(route('credits.user', props.student.id))
    const data = await response.json()
    userCredits.value = data
    showCreditsDialog.value = true
  } catch (error) {
    console.error('Ошибка при загрузке кредитов:', error)
  }
}

const onLoginLinkGenerated = (link) => {
  if (studentInfo.value) {
    studentInfo.value.onLinkGenerated(link)
  }
}

// Функция форматирования телефона
const formatPhone = (phone) => {
  // Убираем все нецифровые символы
  const cleaned = phone.replace(/\D/g, '')
  
  // Форматируем номер как +7 (XXX) XXX-XX-XX
  if (cleaned.length === 11) {
    return cleaned.replace(/(\d{1})(\d{3})(\d{3})(\d{2})(\d{2})/, '+$1 ($2) $3-$4-$5')
  }
  
  return phone
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