<template>
  <q-card flat style="min-width: 350px">
    <q-card-section>
      <q-form @submit="onSubmit" class="q-gutter-md">
        <!-- Скрытые поля -->
        <q-input v-if="false" v-model="form.email" type="hidden" />
        <q-input v-if="false" v-model="form.name" type="hidden" />

        <div class="row q-col-gutter-md">
          <!-- Группа -->
          <div class="col-12">
            <q-select
              v-model="form.teamId"
              :options="teamOptions"
              label="Группа *"
              :rules="[val => !!val || 'Обязательное поле']"
              emit-value
              map-options
            />
          </div>

          <!-- Фамилия и Имя в одной строке -->
          <div class="col-12 col-sm-6">
            <q-input
              v-model="form.person.lastName"
              label="Фамилия *"
              :rules="[val => !!val || 'Обязательное поле']"
              filled
            >
              <template v-slot:append>
                <div class="text-red">*</div>
              </template>
            </q-input>
          </div>

          <div class="col-12 col-sm-6">
            <q-input
              v-model="form.person.firstName"
              label="Имя *"
              :rules="[val => !!val || 'Обязательное поле']"
              filled
            >
              <template v-slot:append>
                <div class="text-red">*</div>
              </template>
            </q-input>
          </div>

          <!-- ФИО родителя -->
          <div class="col-12">
            <q-input
              v-model="form.person.parentFio"
              label="ФИО родителя *"
              :rules="[val => !!val || 'Обязательное поле']"
              filled
            >
              <template v-slot:append>
                <div class="text-red">*</div>
              </template>
            </q-input>
          </div>

          <!-- Телефон родителя -->
          <div class="col-12">
            <q-input
              v-model="form.person.parentTel"
              label="Телефон родителя *"
              mask="(###) ###-##-##"
              :rules="[val => !!val || 'Обязательное поле']"
              filled
            >
              <template v-slot:prepend>
                <div class="text-grey">+7</div>
              </template>
              <template v-slot:append>
                <div class="text-red">*</div>
              </template>
            </q-input>
          </div>

          <!-- Пол и Смена в одной строке -->
          <div class="col-6">
            <q-select
              v-model="form.person.gender"
              :options="genderOptions"
              label="Пол"
              emit-value
              map-options
              filled
            />
          </div>

          <div class="col-6">
            <q-select
              v-model="form.person.shift"
              :options="shiftOptions"
              label="Смена"
              emit-value
              map-options
              filled
            />
          </div>

          <!-- Дата рождения -->
          <div class="col-12">
            <q-input
              v-model="form.person.birthDate"
              type="date"
              label="Дата рождения"
              filled
            />
          </div>
        </div>

        <!-- Кнопки -->
        <div class="row justify-between q-mt-md">
          <div class="col-12" v-if="true">
            <!-- Отображение существующей ссылки -->
            <login-link-display :link="user?.loginLink || generatedLink" />
            
            <!-- Кнопка генерации -->
            <q-btn
              class="q-mt-md"
              label="Создать ссылку"
              color="secondary"
              icon="link"
              @click="generateLoginLink"
            />
          </div>
          
          <div class="col-12 row justify-end q-mt-md">
            <q-btn label="Отмена" flat v-close-popup class="q-mr-sm" />
            <q-btn label="Сохранить" type="submit" color="primary" />
          </div>
        </div>
      </q-form>
    </q-card-section>
  </q-card>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import { router } from '@inertiajs/vue3'
import LoginLinkDisplay from '@/modules/lms/components/common/LoginLinkDisplay.vue'

const props = defineProps({
  user: {
    type: Object,
    default: null
  },
  teams: {
    type: Array,
    required: true
  },
  can: {
    type: Object,
    default: () => ({
      generateLoginLinks: false
    })
  }
})

const emit = defineEmits(['saved', 'linkGenerated'])
const $q = useQuasar()

const form = ref({
  name: props.user?.name || '',
  email: props.user?.email || '',
  teamId: props.user?.teamId || null,
  user: props.user || null,
  person: {
    lastName: props.user?.person?.lastName || '',
    firstName: props.user?.person?.firstName || '',
    birthDate: props.user?.person?.birthDate || '',
    gender: props.user?.person?.gender || '',
    shift: props.user?.person?.shift || '',
    parentFio: props.user?.person?.parentFio || '',
    parentTel: props.user?.person?.parentTel || ''
  }
})

const genderOptions = [
  { label: 'Не указано', value: '' },
  { label: 'Мужской', value: 'male' },
  { label: 'Женский', value: 'female' }
]

const shiftOptions = [
  { label: 'Не указано', value: '' },
  { label: 'Первая', value: 'first' },
  { label: 'Вторая', value: 'second' }
]

const teamOptions = computed(() => {
  return props.teams.map(team => ({
    label: team.name,
    value: team.id
  }))
})

const generatedLink = ref('')

const initForm = () => {
  if (props.user) {
    form.value = {
      email: props.user.email,
      name: props.user.name,
      teamId: props.user.teams?.[0]?.id || null,
      user_id: props.user?.id || null,
      person: {
        lastName: props.user.person?.lastName || '',
        firstName: props.user.person?.firstName || '',
        birthDate: props.user.person?.birthDate || null,
        gender: props.user.person?.gender || '',
        shift: props.user.person?.shift || '',
        parentFio: props.user.person?.parentFio || '',
        parentTel: props.user.person?.parentTel || ''
      }
    }
  }
}

const onSubmit = async () => {
  try {
    const url = props.user 
      ? `/users/${props.user.id}` 
      : '/users'
    
    const method = props.user ? 'put' : 'post'
    
    await axios[method](url, form.value, {
      headers: {
        'X-XSRF-TOKEN': document.cookie
          .split('; ')
          .find(row => row.startsWith('XSRF-TOKEN='))
          ?.split('=')[1],
      },
      withCredentials: true
    })
    
    $q.notify({
      type: 'positive',
      message: props.user ? 'Пользователь успешно обновлен' : 'Пользователь успешно создан',
      position: 'top'
    })
    
    emit('saved')
  } catch (error) {
    const message = error.response?.data?.message || 'Ошибка при сохранении пользователя'
    $q.notify({
      type: 'negative',
      message: message,
      position: 'top'
    })
    console.error('User save error:', error.response?.data)
  }
}

const generateLoginLink = async () => {
  try {
    const response = await axios.post(route('users.login-link', props.user.id))
    
    $q.notify({
      type: 'positive',
      message: 'Ссылка для входа сгенерирована',
      position: 'top'
    })
    
    // Сохраняем ссылку для отображения в форме
    generatedLink.value = response.data.link
    
    // Передаем ссылку родительскому компоненту
    emit('linkGenerated', response.data.link)
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: error.response?.data?.message || 'Ошибка при генерации ссылки',
      position: 'top'
    })
  }
}

onMounted(() => {
  initForm()
})
</script> 