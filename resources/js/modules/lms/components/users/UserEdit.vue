<template>
  <q-card flat class="full-width">
    <q-card-section>
      <q-form @submit="onSubmit">
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
              v-model="form.person.last_name"
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
              v-model="form.person.first_name"
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
              v-model="form.person.parent_fio"
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
              v-model="form.person.parent_tel"
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
              v-model="form.person.birth_date"
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
              :loading="loadingLink"
            />
          </div>
          
          <div class="col-12 row justify-end q-mt-md">
            <q-btn label="Отмена" flat v-close-popup class="q-mr-sm" />
            <q-btn 
              label="Сохранить" 
              type="submit" 
              color="primary"
              :loading="loading" 
            />
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
    last_name: props.user?.person?.last_name || '',
    first_name: props.user?.person?.first_name || '',
    birth_date: props.user?.person?.birth_date || '',
    gender: props.user?.person?.gender || '',
    shift: props.user?.person?.shift || '',
    parent_fio: props.user?.person?.parent_fio || '',
    parent_tel: props.user?.person?.parent_tel || ''
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
const loading = ref(false)
const loadingLink = ref(false)

const initForm = () => {
  if (props.user) {
    form.value = {
      email: props.user.email,
      name: props.user.name,
      teamId: props.user.teams?.[0]?.id || null,
      user_id: props.user?.id || null,
      person: {
        last_name: props.user.person?.last_name || '',
        first_name: props.user.person?.first_name || '',
        birth_date: props.user.person?.birth_date || null,
        gender: props.user.person?.gender || '',
        shift: props.user.person?.shift || '',
        parent_fio: props.user.person?.parent_fio || '',
        parent_tel: props.user.person?.parent_tel || ''
      }
    }
  }
}

const onSubmit = async () => {
  try {
    loading.value = true
    const url = props.user
      ? `/users/${props.user.id}` 
      : '/users'
    
    const method = props.user ? 'put' : 'post'
    form.value.name = `${form.value.person.first_name} ${form.value.person.last_name}`;
    await axios[method](url, form.value)
    
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
  } finally {
    loading.value = false
  }
}

const generateLoginLink = async () => {
  try {
    loadingLink.value = true
    const response = await axios.post(route('users.login-link', props.user.id))
    
    $q.notify({
      type: 'positive',
      message: 'Ссылка для входа сгенерирована',
      position: 'top'
    })
    
    // Сохраняем ссылку для отображения в форме
    generatedLink.value = response.data.link;
    props.user.loginLink = response.data.link;
    
    // Передаем ссылку родительскому компоненту
    emit('linkGenerated', response.data.link)
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: error.response?.data?.message || 'Ошибка при генерации ссылки',
      position: 'top'
    })
  } finally {
    loadingLink.value = false
  }
}

onMounted(() => {
  initForm()
})
</script> 