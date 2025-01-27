<template>
    <page-layout title="Расписание">
        <div class="all-group-schedules">
          <all-schedules :initial-view-mode="viewMode" />
        </div>
    </page-layout>
</template>

<script setup>
import { ref } from 'vue'
import AllSchedules from '@/components/AllSchedules.vue'

const viewMode = ref('group') // Можно изменить на 'day' по умолчанию, если требуется
</script>

<style scoped>
.all-group-schedules {
  padding: 20px;
}
</style> 