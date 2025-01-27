<template>
  <page-layout title="Группы">
    <div class="q-pa-md">
      <teams-list :teams="teams" />
    </div>
  </page-layout>
</template>

<script setup>
import TeamsList from '@/modules/lms/components/teams/TeamsList.vue'

defineProps({
  teams: {
    type: Array,
    required: true
  }
})
</script> 