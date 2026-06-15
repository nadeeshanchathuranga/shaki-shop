<template>
  <Head title="Calendar" />
  <Banner />
  <div class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gray-50 md:px-36 px-4">
    <Header />

    <!-- Calendar Controls -->
    <div class="w-full flex md:flex-row flex-col justify-between items-center bg-white p-6 border-4 border-black rounded-2xl shadow-[8px_8px_0px_0px_rgba(0,0,0,1)]">
      <div class="flex items-center space-x-4">
        <Link href="/"><img src="/images/back-arrow.png" class="w-12 h-12 hover:scale-110 transition" /></Link>
        <h1 class="text-4xl font-black uppercase tracking-tighter text-black">Calendar</h1>
      </div>

      <div class="flex items-center space-x-3 mt-4 md:mt-0">
        <div class="flex bg-gray-100 p-1 rounded-xl border-2 border-black">
          <button 
            @click="viewMode = 'month'" 
            :class="[viewMode === 'month' ? 'bg-black text-white' : 'hover:bg-gray-200 text-black']"
            class="px-4 py-1.5 rounded-lg font-bold text-sm transition uppercase"
          >Month</button>
          <button 
            @click="viewMode = 'week'" 
            :class="[viewMode === 'week' ? 'bg-black text-white' : 'hover:bg-gray-200 text-black']"
            class="px-4 py-1.5 rounded-lg font-bold text-sm transition uppercase"
          >Week</button>
        </div>
        
        <div class="flex items-center space-x-2 bg-white border-2 border-black rounded-xl p-1 overflow-hidden">
             <button @click="prev" class="p-2 hover:bg-gray-100 rounded-lg transition"><i class="ri-arrow-left-s-line text-xl"></i></button>
             <span class="text-lg font-black min-w-[150px] text-center uppercase">{{ currentPeriodLabel }}</span>
             <button @click="next" class="p-2 hover:bg-gray-100 rounded-lg transition"><i class="ri-arrow-right-s-line text-xl"></i></button>
        </div>

        <button 
          @click="openAddModal"
          class="bg-blue-600 text-white border-2 border-black px-6 py-2.5 rounded-xl font-bold shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-none transition active:bg-blue-700"
        >
          <i class="ri-add-line mr-1 font-black"></i> ADD TASK
        </button>
      </div>
    </div>

    <!-- Calendar Grid -->
    <div class="w-full bg-white border-4 border-black rounded-3xl overflow-hidden shadow-[12px_12px_0px_0px_rgba(0,0,0,1)]">
      
      <!-- Month View -->
      <div v-if="viewMode === 'month'" class="w-full">
        <div class="grid grid-cols-7 border-b-2 border-black bg-gray-50 uppercase font-black text-xs tracking-widest text-gray-500 py-3">
          <div v-for="day in weekDays" :key="day" class="text-center">{{ day }}</div>
        </div>
        
        <div class="grid grid-cols-7 border-collapse">
          <div 
            v-for="(day, idx) in calendarDays" 
            :key="idx" 
            class="min-h-[140px] border-r-2 border-b-2 last:border-r-0 border-gray-100 relative group cursor-pointer hover:bg-gray-50 transition p-2"
            :class="[day.isCurrentMonth ? 'bg-white' : 'bg-gray-50 text-gray-400']"
            @click="onDateClick(day.date)"
          >
            <div class="flex justify-between items-start mb-1">
              <span 
                class="w-8 h-8 flex items-center justify-center font-black rounded-full"
                :class="[isToday(day.date) ? 'bg-blue-600 text-white border-2 border-black' : '']"
              >{{ day.dayNumber }}</span>
            </div>

            <!-- Event List -->
            <div class="space-y-1 overflow-y-auto max-h-[100px] scrollbar-hide">
              <div 
                v-for="event in day.events" 
                :key="event.id"
                @click.stop="viewEvent(event)"
                class="px-2 py-0.5 rounded border-b border-black/10 text-[10px] font-bold text-white truncate shadow-sm hover:scale-[1.02] transition"
                :class="[event.color, event.status === 'completed' ? 'opacity-50 line-through' : '']"
              >
                {{ event.title }}
              </div>
            </div>
            
            <!-- Quick Link to add -->
            <div class="absolute bottom-1 right-1 opacity-0 group-hover:opacity-100 transition">
              <i class="ri-add-circle-fill text-blue-500 text-xl"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Week View -->
      <div v-if="viewMode === 'week'" class="w-full">
         <div class="grid grid-cols-8 border-b-2 border-black">
            <div class="border-r-2 border-black bg-gray-50 p-2"></div>
            <div v-for="day in weekGrid" :key="day.date" class="p-4 text-center border-r-2 border-black last:border-r-0" :class="[isToday(day.date) ? 'bg-blue-50' : '']">
              <p class="text-xs font-black text-gray-500 uppercase">{{ weekDays[day.date.getDay()] }}</p>
              <p class="text-2xl font-black" :class="[isToday(day.date) ? 'text-blue-600' : '']">{{ day.date.getDate() }}</p>
            </div>
         </div>
         <div class="flex flex-col">
            <div 
              v-for="hour in 12" 
              :key="hour" 
              class="grid grid-cols-8 border-b border-gray-100 hover:bg-gray-50/50 transition h-20"
            >
               <div class="border-r-2 border-black bg-gray-50 text-[10px] p-2 font-bold text-gray-400 text-right">
                 {{ hour + 7 }}:00
               </div>
               <div 
                 v-for="(day, idx) in weekGrid" 
                 :key="idx" 
                 class="border-r border-gray-100 last:border-r-0 relative"
               >
                  <!-- We just show events at the top in this simple week view for clarity -->
                  <div v-if="hour === 1" class="p-1 space-y-1">
                     <div 
                       v-for="event in day.events" 
                       :key="event.id"
                       class="p-1.5 rounded border border-black/20 text-[10px] font-bold text-white"
                       :class="event.color"
                     >
                       {{ event.title }}
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>

    </div>

    <!-- Details/Legend -->
    <div class="w-full grid md:grid-cols-4 grid-cols-2 gap-4">
      <div class="bg-white border-2 border-black p-4 rounded-xl flex items-center space-x-3">
        <div class="w-5 h-5 bg-orange-500 border-2 border-black rounded shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]"></div>
        <span class="font-bold text-sm">Event Booking</span>
      </div>
      <div class="bg-white border-2 border-black p-4 rounded-xl flex items-center space-x-3">
        <div class="w-5 h-5 bg-purple-500 border-2 border-black rounded shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]"></div>
        <span class="font-bold text-sm">Rental Dispatch</span>
      </div>
      <div class="bg-white border-2 border-black p-4 rounded-xl flex items-center space-x-3">
        <div class="w-5 h-5 bg-emerald-500 border-2 border-black rounded shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]"></div>
        <span class="font-bold text-sm">Rental Return</span>
      </div>
      <div class="bg-white border-2 border-black p-4 rounded-xl flex items-center space-x-3">
        <div class="w-5 h-5 bg-blue-500 border-2 border-black rounded shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]"></div>
        <span class="font-bold text-sm">Operation / Task</span>
      </div>
    </div>

    <!-- Modals -->
    <CalendarEventModal 
      v-model:open="isAddModalOpen" 
      :initialDate="selectedDate"
      @success="reload" 
    />

    <CalendarViewEventModal 
      v-model:open="isViewModalOpen" 
      :event="selectedEvent"
      @deleted="reload"
      @updated="reload"
    />

    <Footer />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import Banner from "@/Components/Banner.vue";
import CalendarEventModal from "@/Components/custom/CalendarEventModal.vue";
import CalendarViewEventModal from "@/Components/custom/CalendarViewEventModal.vue";

const props = defineProps({
  events: { type: Array, required: true }
});

const viewMode = ref('month'); // month, week
const currentDate = ref(new Date());
const isAddModalOpen = ref(false);
const isViewModalOpen = ref(false);
const selectedDate = ref(null);
const selectedEvent = ref(null);

const weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

// NAVIGATION
const prev = () => {
    if (viewMode.value === 'month') {
        currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() - 1, 1);
    } else {
        const d = new Date(currentDate.value);
        d.setDate(d.getDate() - 7);
        currentDate.value = d;
    }
};

const next = () => {
    if (viewMode.value === 'month') {
        currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 1);
    } else {
        const d = new Date(currentDate.value);
        d.setDate(d.getDate() + 7);
        currentDate.value = d;
    }
};

const currentPeriodLabel = computed(() => {
    if (viewMode.value === 'month') {
        return currentDate.value.toLocaleString('default', { month: 'long', year: 'numeric' });
    }
    const end = new Date(currentDate.value);
    end.setDate(end.getDate() + 6);
    return `${currentDate.value.getDate()} - ${end.getDate()} ${currentDate.value.toLocaleString('default', { month: 'short' })}`;
});

const isToday = (date) => {
    const today = new Date();
    return date.getDate() === today.getDate() && 
           date.getMonth() === today.getMonth() && 
           date.getFullYear() === today.getFullYear();
};

// DATA GRID CALCULATION
const calendarDays = computed(() => {
    const year = currentDate.value.getFullYear();
    const month = currentDate.value.getMonth();
    
    // First day of current month
    const firstDay = new Date(year, month, 1);
    const startOffset = firstDay.getDay(); // index of Sunday (0) to Saturday (6)
    
    const days = [];
    
    // Previous month padding
    const prevMonthLastDay = new Date(year, month, 0).getDate();
    for (let i = startOffset - 1; i >= 0; i--) {
        days.push({
            date: new Date(year, month - 1, prevMonthLastDay - i),
            dayNumber: prevMonthLastDay - i,
            isCurrentMonth: false,
            events: []
        });
    }
    
    // Current month days
    const lastDay = new Date(year, month + 1, 0).getDate();
    for (let i = 1; i <= lastDay; i++) {
        const date = new Date(year, month, i);
        days.push({
            date: date,
            dayNumber: i,
            isCurrentMonth: true,
            events: props.events.filter(e => e.date === date.toISOString().split('T')[0])
        });
    }
    
    // Next month padding
    const totalSlots = 42; // standard 6 rows
    const remaining = totalSlots - days.length;
    for (let i = 1; i <= remaining; i++) {
        days.push({
            date: new Date(year, month + 1, i),
            dayNumber: i,
            isCurrentMonth: false,
            events: props.events.filter(e => e.date === new Date(year, month + 1, i).toISOString().split('T')[0])
        });
    }
    
    return days;
});

const weekGrid = computed(() => {
    const grid = [];
    const d = new Date(currentDate.value);
    // Find start of week (Sunday)
    d.setDate(d.getDate() - d.getDay());
    
    for (let i = 0; i < 7; i++) {
        const current = new Date(d);
        current.setDate(current.getDate() + i);
        grid.push({
            date: current,
            events: props.events.filter(e => e.date === current.toISOString().split('T')[0])
        });
    }
    return grid;
});

// ACTIONS
const openAddModal = () => {
    selectedDate.value = new Date().toISOString().split('T')[0];
    isAddModalOpen.value = true;
};

const onDateClick = (date) => {
    selectedDate.value = date.toISOString().split('T')[0];
    isAddModalOpen.value = true;
};

const viewEvent = (event) => {
    selectedEvent.value = event;
    isViewModalOpen.value = true;
};

const reload = () => {
    router.reload({ preserveScroll: true });
};
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
