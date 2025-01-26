<template>
  <page-layout 
    title="Ученики"
    footer-text="Контакты"
    :left-btn-go-back="true"
  >
    <div class="q-pa-md">
      <students-list
        :students="students"
        :teams="teams"
      />
    </div>
  </page-layout>
</template>

<script setup>
import StudentsList from '@/modules/lms/components/users/StudentsList.vue'

defineProps({
  students: {
    type: Array,
    required: true
  },
  teams: {
    type: Array,
    required: true
  }
})
</script> 