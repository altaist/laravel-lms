<template>
  <q-dialog :model-value="modelValue" @update:model-value="$emit('update:modelValue')">
    <q-card class="schedule-edit-dialog">
      <q-card-section class="row items-center">
        <div class="text-h6">Редактирование расписания</div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>

      <q-card-section>
        <schedule-editor
          :team-id="teamId"
          :schedule-days="scheduleDays"
          @update:schedule-days="$emit('update:scheduleDays', $event)"
          @saved="$emit('saved')"
          @cancelled="$emit('cancelled')"
        />
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script setup>
import ScheduleEditor from './ScheduleEditor.vue'

defineProps({
  modelValue: Boolean,
  teamId: {
    type: [Number, String],
    required: true
  },
  scheduleDays: {
    type: Array,
    default: () => []
  }
})

defineEmits(['update:modelValue', 'update:scheduleDays', 'saved', 'cancelled'])
</script>

<style scoped>
.schedule-edit-dialog {
  max-width: 90vw;
}
</style> 