<template>
  <q-card style="min-width: 350px">
    <q-card-section class="row items-center">
      <div class="text-h6">{{ title }}</div>
      <q-space />
      <q-btn icon="close" flat round dense v-close-popup @click="$emit('cancel')" />
    </q-card-section>

    <q-card-section>
      <q-form @submit="handleSubmit">
        <!-- Сообщения об ошибках -->
        <div v-if="serverErrors" class="text-negative q-mb-md">
          <div v-for="(errors, field) in serverErrors" :key="field">
            <div v-for="error in errors" :key="error" class="q-mb-sm">
              {{ error }}
            </div>
          </div>
        </div>

        <q-input
          v-model="formData.name"
          label="Название команды *"
          :error="!!serverErrors?.name"
          :error-message="serverErrors?.name?.[0]"
          :rules="[
            val => !!val || 'Название обязательно',
            val => val.length <= 255 || 'Максимальная длина 255 символов'
          ]"
          class="q-mb-md"
        />

        <q-input
          v-model="formData.description"
          type="textarea"
          label="Описание"
          :error="!!serverErrors?.description"
          :error-message="serverErrors?.description?.[0]"
          class="q-mb-md"
        />

        <q-select
          v-model="formData.type"
          :options="typeOptions"
          label="Тип *"
          :error="!!serverErrors?.type"
          :error-message="serverErrors?.type?.[0]"
          :rules="[val => !!val || 'Тип обязателен']"
          emit-value
          map-options
          class="q-mb-md"
        />

        <div class="row justify-end q-gutter-sm">
          <q-btn 
            label="Отмена" 
            color="grey-7" 
            flat 
            @click="$emit('cancel')" 
          />
          <q-btn 
            type="submit"
            :label="team ? 'Сохранить' : 'Создать'"
            color="primary"
            :loading="loading"
          />
        </div>
      </q-form>
    </q-card-section>
  </q-card>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const props = defineProps({
  team: {
    type: Object,
    default: null
  },
  title: {
    type: String,
    default: 'Редактировать команду'
  }
})

const emit = defineEmits(['save', 'cancel'])
const loading = ref(false)
const serverErrors = ref(null)

const typeOptions = [
  { label: 'Группа', value: 'group' },
  { label: 'Индивидуально', value: 'individual' }
]

const formData = ref({
  name: '',
  description: '',
  type: 'group'
})

onMounted(() => {
  if (props.team) {
    formData.value = {
      ...props.team
    }
  }
})

const handleSubmit = async () => {
  try {
    loading.value = true
    serverErrors.value = null
    emit('save', formData.value)
  } catch (error) {
    if (error.response?.data?.errors) {
      serverErrors.value = error.response.data.errors
    }
  } finally {
    loading.value = false
  }
}
</script> 