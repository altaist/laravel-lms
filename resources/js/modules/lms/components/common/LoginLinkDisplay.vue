<template>
  <div v-if="link" class="q-mt-lg">
    <div class="text-subtitle2 q-mb-sm">Ссылка для входа:</div>
    <div class="row items-center q-gutter-x-md">
      <a 
        :href="link"
        target="_blank"
        class="col text-primary ellipsis"
        style="text-decoration: none;"
      >
        {{ link }}
      </a>
      <q-btn
        flat
        round
        icon="content_copy"
        color="primary"
        @click="copyLink"
      />
    </div>
  </div>
</template>

<script setup>
import { useQuasar } from 'quasar'

const props = defineProps({
  link: {
    type: String,
    default: ''
  }
})

const $q = useQuasar()

const copyLink = async () => {
  try {
    await navigator.clipboard.writeText(props.link)
    $q.notify({
      type: 'positive',
      message: 'Ссылка скопирована',
      position: 'top'
    })
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Ошибка при копировании ссылки',
      position: 'top'
    })
  }
}
</script>

<style scoped>
.ellipsis {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style> 