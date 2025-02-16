<template>
  <q-card class="full-width" style="max-width: 900px">
    <q-card-section class="row items-center">
      <div class="text-h6">Новая оплата</div>
      <q-space />
      <q-btn icon="fa fa-times" flat round dense v-close-popup />
    </q-card-section>

    <q-card-section>
      <q-form @submit="onSubmit" class="q-gutter-md">
        <q-input
          v-if="!props.user && !form.user"
          v-model="searchQuery"
          label="Поиск ученика *"
          :rules="[val => (!!val || !!form.user) || 'Обязательное поле']"
          class="q-mb-md"
          filled
        >
          <template v-slot:append>
            <q-icon name="search" />
          </template>
        </q-input>

        <q-list v-if="!props.user && !form.user && filteredUsers.length > 0" bordered class="q-mb-md">
          <q-item
            v-for="user in filteredUsers"
            :key="user.id"
            clickable
            v-ripple
            @click="selectUser(user)"
          >
            <q-item-section>
              {{ user.person?.last_name }} {{ user.person?.first_name }}
            </q-item-section>
          </q-item>
        </q-list>

        <q-select
          v-if="!props.user && !form.user"
          :options="userOptions"
          label="Или выберите из списка"
          @update:model-value="selectUserFromList"
          filled
          class="q-mb-md"
        />

        <div v-if="form.user" class="text-subtitle1 q-mb-md">
          Выбранный ученик: {{ form.user.label }}
          <q-btn
            flat
            round
            dense
            icon="close"
            @click="clearSelectedUser"
            class="q-ml-sm"
          />
        </div>

        <div class="row ">
          <div class="col-12 col-sm-6">
            <q-input
              v-model.number="form.amount"
              type="number"
              label="Сумма *"
              :rules="[val => !!val || 'Обязательное поле']"
              filled
            >
              <template v-slot:append>
                <div class="text-grey">₽</div>
              </template>
            </q-input>
          </div>
          <div class="col-12 col-sm-6">
            <q-input
              v-model.number="form.credit_amount"
              type="number"
              label="Кол-во занятий *"
              :rules="[val => !!val || 'Обязательное поле']"
              filled
            >

            </q-input>
          </div>
        </div>

        <q-checkbox
          v-model="form.pay_from"
          label="Безнал"
          true-value="card"
          false-value="cash"
          class="q-mb-md"
        />

        <q-input
          v-model="form.payment_at"
          type="datetime-local"
          label="Дата платежа"
          filled
          class="q-mb-md"
        />

        <q-input
          v-model="form.description"
          type="textarea"
          label="Комментарий"
          filled
          autogrow
        />

        <div class="row justify-end q-mt-lg">
          <q-btn label="Отмена" flat v-close-popup class="q-mr-sm" />
          <q-btn label="Сохранить" type="submit" color="primary" />
        </div>
      </q-form>
    </q-card-section>
  </q-card>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useQuasar } from 'quasar'

const props = defineProps({
  users: {
    type: Array,
    required: false,
    default: () => []
  },
  user: {
    type: Object,
    required: false,
    default: null
  }
})

const emit = defineEmits(['saved'])
const $q = useQuasar()

const form = ref({
  user: null,
  amount: 4000,
  credit_amount: 8,
  pay_from: 'card',
  description: '',
  payment_at: new Date().toISOString().slice(0, 16)
})

if (props.user) {
  form.value.user = {
    label: `${props.user.person?.last_name} ${props.user.person?.first_name}`,
    value: props.user.id
  }
}

const searchQuery = ref('')
const filteredUsers = computed(() => {
  if (!searchQuery.value) return []
  const query = searchQuery.value.toLowerCase()
  return props.users.filter(user => {
    const lastName = user.person?.last_name?.toLowerCase() || ''
    const firstName = user.person?.first_name?.toLowerCase() || ''
    return lastName.includes(query) || firstName.includes(query)
  }).slice(0, 5)
})

const userOptions = computed(() => {
  return props.users.map(user => ({
    label: `${user.person?.last_name} ${user.person?.first_name}`,
    value: user.id,
    user: user
  }))
})

const selectUser = (user) => {
  form.value.user = {
    label: `${user.person?.last_name} ${user.person?.first_name}`,
    value: user.id
  }
  searchQuery.value = ''
}

const selectUserFromList = (option) => {
  if (option) {
    selectUser(option.user)
  }
}

const clearSelectedUser = () => {
  form.value.user = null
  searchQuery.value = ''
}

const onSubmit = async () => {
  try {
    const formData = {
      ...form.value,
      user_id: Number(form.value.user.value),
      coin_id: 1
    }
    
    await axios.post(route('payments.store'), formData)
    $q.notify({
      type: 'positive',
      message: 'Платеж успешно создан',
      position: 'top'
    })
    emit('saved')
  } catch (error) {
    const message = error.response?.data?.message || 'Ошибка при создании платежа'
    $q.notify({
      type: 'negative',
      message: message,
      position: 'top'
    })
    console.error('Payment error:', error.response?.data)
  }
}
</script> 