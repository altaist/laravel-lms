<template>
  <page-layout 
    title="Все занятия"
    footer-text="Контакты"
    :left-btn-go-back="true"
  >
    <div class="q-py-md">
      <all-activities
        :activities="activities"
        :teams="teams"
      />
    </div>
  </page-layout>
</template>

<script setup>
import AllActivities from '@/modules/lms/components/activities/AllActivities.vue'

defineProps({
  activities: {
    type: Array,
    required: true
  },
  teams: {
    type: Array,
    required: true
  }
})
</script> 