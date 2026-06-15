<template>
  <div v-if="open && event" class="fixed inset-0 z-[60] flex items-center justify-center bg-black bg-opacity-70 p-4 backdrop-blur-sm">
    <div class="bg-white border-4 border-black w-full max-w-lg rounded-3xl shadow-[12px_12px_0px_0px_rgba(0,0,0,1)] flex flex-col p-8 space-y-6 relative overflow-hidden">
      
      <!-- Top Accent Bar -->
      <div class="absolute top-0 left-0 right-0 h-4" :class="event.color"></div>
      
      <button @click="close" class="absolute top-6 right-6 text-3xl font-black text-black hover:scale-110 transition">
        <i class="ri-close-line"></i>
      </button>

      <div class="flex flex-col space-y-2">
        <span class="inline-block px-3 py-1 border-2 border-black rounded-lg text-xs font-black uppercase w-fit" :class="event.color + ' text-white'">
          {{ event.type.replace('_', ' ') }}
        </span>
        <h2 class="text-4xl font-black uppercase tracking-tighter leading-none pt-2">{{ event.title }}</h2>
        <p class="text-lg font-bold text-gray-500"><i class="ri-calendar-line mr-2"></i>{{ formatDate(event.date) }}</p>
      </div>

      <div v-if="event.description" class="bg-gray-50 border-2 border-black p-4 rounded-xl">
        <p class="text-sm font-black text-gray-400 uppercase mb-2">Description / Notes</p>
        <p class="font-bold text-gray-800 leading-relaxed">{{ event.description }}</p>
      </div>

      <!-- Action Buttons -->
      <div v-if="event.type === 'task'" class="grid grid-cols-2 gap-4 mt-4">
        <button 
          v-if="event.status === 'pending'"
          @click="toggleStatus('completed')"
          class="bg-emerald-500 text-white border-2 border-black p-4 rounded-2xl font-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-none transition uppercase flex items-center justify-center"
        >
          <i class="ri-check-double-line mr-2 text-xl"></i> Mark Done
        </button>
        <button 
          v-else
          @click="toggleStatus('pending')"
          class="bg-amber-500 text-white border-2 border-black p-4 rounded-2xl font-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-none transition uppercase flex items-center justify-center"
        >
          <i class="ri-restart-line mr-2 text-xl"></i> Re-Open
        </button>

        <button 
          @click="deleteEvent"
          class="bg-rose-500 text-white border-2 border-black p-4 rounded-2xl font-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-none transition uppercase flex items-center justify-center"
        >
          <i class="ri-delete-bin-line mr-2 text-xl"></i> Delete
        </button>
      </div>

      <!-- Detail Links for Commissions/Rentals -->
      <div v-else class="mt-4">
        <Link 
          v-if="event.type === 'commission'"
          href="/event-commissions"
          class="w-full bg-orange-600 text-white border-4 border-black p-4 rounded-2xl font-black text-center block shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-none transition uppercase"
        >
          Manage Commission Module
        </Link>
        <Link 
          v-if="event.type.includes('rental')"
          href="/rental-summary"
          class="w-full bg-purple-600 text-white border-4 border-black p-4 rounded-2xl font-black text-center block shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-none transition uppercase"
        >
          View Rental Summary
        </Link>
      </div>

    </div>
  </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const props = defineProps({
  open: Boolean,
  event: Object
});

const emit = defineEmits(['update:open', 'deleted', 'updated']);

const close = () => {
    emit('update:open', false);
};

const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
};

const toggleStatus = (newStatus) => {
    const realId = props.event.real_id;
    router.patch(route('calendar.status', realId), { status: newStatus }, {
        onSuccess: () => {
            emit('updated');
            close();
        }
    });
};

const deleteEvent = () => {
    Swal.fire({
        title: 'Are you sure?',
        text: 'This task will be permanently removed.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Yes, delete it'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('calendar.destroy', props.event.real_id), {
                onSuccess: () => {
                    emit('deleted');
                    close();
                }
            });
        }
    });
};
</script>
