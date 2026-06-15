<template>
  <div v-if="open" class="fixed inset-0 z-[60] flex items-center justify-center bg-black bg-opacity-70 p-4 backdrop-blur-sm">
    <div class="bg-white border-4 border-black w-full max-w-lg rounded-3xl shadow-[12px_12px_0px_0px_rgba(0,0,0,1)] flex flex-col p-8 space-y-6 relative">
      
      <button @click="close" class="absolute top-4 right-4 text-3xl font-black text-black hover:scale-110 transition">
        <i class="ri-close-line"></i>
      </button>

      <div class="flex items-center space-x-4 border-b-4 border-black pb-4">
        <div class="p-3 bg-blue-100 border-2 border-black rounded-2xl">
          <i class="ri-calendar-todo-line text-3xl text-blue-600"></i>
        </div>
        <div>
          <h2 class="text-3xl font-black uppercase tracking-tighter">Add To-Do / Task</h2>
          <p class="text-sm font-bold text-gray-500">{{ formatDate(initialDate) }}</p>
        </div>
      </div>

      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="block text-sm font-black uppercase mb-1">Task Title</label>
          <input 
            v-model="form.title" 
            placeholder="e.g. Clean display units" 
            required
            class="w-full border-2 border-black p-3 rounded-xl focus:ring-2 focus:ring-blue-500 font-bold"
          />
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-black uppercase mb-1">Date</label>
            <input 
              v-model="form.event_date" 
              type="date"
              required
              class="w-full border-2 border-black p-3 rounded-xl focus:ring-2 focus:ring-blue-500 font-bold"
            />
          </div>
          <div>
            <label class="block text-sm font-black uppercase mb-1">Type</label>
            <select 
              v-model="form.type"
              class="w-full border-2 border-black p-3 rounded-xl focus:ring-2 focus:ring-blue-500 font-bold"
            >
              <option value="task">Operational Task</option>
              <option value="operational">Function / Operation</option>
              <option value="reminder">Reminder</option>
            </select>
          </div>
        </div>

        <div>
          <label class="block text-sm font-black uppercase mb-1">Notes / Description</label>
          <textarea 
            v-model="form.description" 
            rows="3"
            placeholder="Add any specific instructions..."
            class="w-full border-2 border-black p-3 rounded-xl focus:ring-2 focus:ring-blue-500 font-bold"
          ></textarea>
        </div>

        <div class="pt-4">
          <button 
            type="submit" 
            :disabled="form.processing"
            class="w-full bg-blue-600 text-white border-4 border-black p-4 rounded-2xl font-black text-xl shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-none transition disabled:opacity-50 uppercase tracking-widest"
          >
            {{ form.processing ? 'Saving...' : 'Save Task' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
  open: Boolean,
  initialDate: String
});

const emit = defineEmits(['update:open', 'success']);

const form = useForm({
  title: '',
  description: '',
  event_date: '',
  type: 'task'
});

watch(() => props.initialDate, (newDate) => {
  if (newDate) form.event_date = newDate;
});

const close = () => {
  emit('update:open', false);
  form.reset();
};

const formatDate = (date) => {
  if (!date) return '';
  return new Date(date).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
};

const submit = () => {
  form.post(route('calendar.store'), {
    onSuccess: () => {
      Swal.fire({
        title: 'Task Saved!',
        icon: 'success',
        confirmButtonColor: '#2563eb'
      });
      emit('success');
      close();
    }
  });
};
</script>
