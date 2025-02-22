<template>
    <page-layout title="Расписание">
        <div class="q-pa-none q-py-md">
          <all-schedules :initial-view-mode="viewMode" />
        </div>
    </page-layout>
</template>

<script setup>
import { ref } from 'vue'
import AllSchedules from '@/modules/lms/components/schedules/AllSchedules.vue'

const viewMode = ref('group') // Можно изменить на 'day' по умолчанию, если требуется
</script>
