<template>
  <q-card class="full-width" style="max-width: 900px">
    <q-card-section class="row items-center">
      <div class="text-h6">Новая оплата</div>
      <q-space />
      <q-btn icon="fa fa-times" flat round dense v-close-popup />
    </q-card-section>

    <q-card-section>
      <q-form @submit="onSubmit" class="q-gutter-md">
        <q-select
          v-if="!props.user"
          v-model="form.user"
          :options="userOptions"
          label="Пользователь *"
          :rules="[val => !!val || 'Обязательное поле']"
          class="q-mb-md"
        />

        <div v-else class="text-subtitle1 q-mb-md">
          Ученик: {{ props.user.person?.lastName }} {{ props.user.person?.firstName }}
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
    label: `${props.user.person?.lastName} ${props.user.person?.firstName}`,
    value: props.user.id
  }
}

const userOptions = computed(() => {
  if (props.user) return []
  return props.users.map(user => ({
    label: `${user.name} ${user.surname || ''}`,
    value: user.id
  }))
})

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