<template>
  <Head title="Event Commissions" />
  <Banner />
  <div class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gray-100 md:px-36 px-16">
    <Header />

    <div class="w-full md:w-5/6 py-12 space-y-12">
      <!-- Stats Summary -->
      <div class="flex items-center justify-between">
        <div class="flex items-center justify-center space-x-4"></div>
        <p class="text-3xl italic font-bold text-black">
          <span class="px-4 py-1 mr-3 text-white bg-black rounded-xl">
            {{ commissions.length }}
          </span>
          <span class="text-xl">/ Total Events</span>
        </p>
      </div>

      <!-- Header & Action Row -->
      <div class="flex md:flex-row flex-col w-full justify-between items-center gap-4">
        <div class="flex items-center w-full h-16 space-x-4 rounded-2xl">
          <Link href="/">
            <img src="/images/back-arrow.png" class="w-14 h-14" />
          </Link>
          <p class="text-4xl font-bold tracking-wide text-black uppercase">
            Event Commissions
          </p>
        </div>
        
        <div class="flex justify-end w-full">
          <p
            @click="() => openModal('create')"
            class="md:px-12 py-4 px-4 md:text-2xl font-bold tracking-wider text-white uppercase bg-blue-600 rounded-xl cursor-pointer hover:bg-blue-700 transition"
          >
            <i class="md:pr-4 ri-add-circle-fill"></i> Add New Event
          </p>
        </div>
      </div>

      <!-- Data Table -->
      <template v-if="commissions && commissions.length > 0">
        <div class="overflow-x-auto">
          <table id="CommissionTable" class="w-full text-gray-700 bg-white border border-gray-300 rounded-lg shadow-md table-auto">
            <thead>
              <tr class="bg-gradient-to-r from-blue-600 via-blue-500 to-blue-600 text-[16px] text-white border-b border-blue-700">
                <th class="p-4 font-semibold tracking-wide text-center uppercase whitespace-nowrap">Status</th>
                <th class="p-4 font-semibold tracking-wide text-left uppercase whitespace-nowrap">Event Name / Title</th>
                <th class="p-4 font-semibold tracking-wide text-left uppercase whitespace-nowrap">Customer Contact</th>
                <th class="p-4 font-semibold tracking-wide text-right uppercase whitespace-nowrap">Total Amount</th>
                <th class="p-4 font-semibold tracking-wide text-right uppercase whitespace-nowrap">Advance Paid</th>
                <th class="p-4 font-semibold tracking-wide text-right uppercase whitespace-nowrap text-red-200 bg-blue-700">Due Amount</th>
                <th class="p-4 font-semibold tracking-wide text-left uppercase whitespace-nowrap">Event Date</th>
                <th class="p-4 font-semibold tracking-wide text-center uppercase whitespace-nowrap">Actions</th>
              </tr>
            </thead>
            <tbody class="text-[14px] font-normal">
              <tr v-for="c in commissions" :key="c.id" class="transition duration-200 ease-in-out hover:bg-gray-200 hover:shadow-lg border-b">
                <td class="p-4 text-center">
                    <span :class="statusBadgeClass(c.payment_status)" class="px-4 py-1.5 rounded-full text-[12px] font-bold uppercase">
                        {{ c.payment_status === 'paid' ? 'Completed' : 'Pending' }}
                    </span>
                </td>
                <td class="p-4 font-bold text-black uppercase">{{ c.event_title }}</td>
                <td class="p-4 font-medium">{{ c.customer_phone }}</td>
                <td class="p-4 text-right font-bold">{{ toMoney(c.total_amount) }} LKR</td>
                <td class="p-4 text-right font-bold text-amber-600">{{ toMoney(c.advance_amount) }} LKR</td>
                <td class="p-4 text-right font-black text-red-600 bg-red-50">
                    {{ toMoney(Number(c.total_amount) - Number(c.advance_amount)) }} LKR
                </td>
                <td class="p-4 whitespace-nowrap">{{ formatDate(c.event_date) }}</td>
                <td class="p-4 text-center">
                  <div class="inline-flex items-center space-x-4">
                    <!-- View Button -->
                    <button @click="openModal('view', c)" class="flex items-center text-blue-600 hover:text-blue-800 transition font-bold uppercase text-xs">
                      <i class="ri-eye-line mr-1 text-xl"></i> View
                    </button>
                    <!-- Edit Button -->
                    <button @click="openModal('edit', c)" class="flex items-center text-amber-600 hover:text-amber-800 transition font-bold uppercase text-xs">
                      <i class="ri-edit-box-line mr-1 text-xl"></i> Edit
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </template>

      <div v-else class="flex flex-col items-center justify-center py-20 bg-white rounded-2xl border-2 border-dashed border-gray-300">
        <i class="ri-folder-info-line text-6xl text-gray-300"></i>
        <p class="mt-4 text-xl font-bold text-gray-400 uppercase tracking-widest">No commissions found</p>
      </div>
    </div>
    
    <Footer />
  </div>

  <!-- Reusable Modal -->
  <EventCommissionModal 
    v-model:open="modalState.open" 
    :mode="modalState.mode" 
    :commissionData="modalState.data" 
    @success="handleSuccess" 
  />
</template>

<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, reactive } from "vue";
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import Banner from "@/Components/Banner.vue";
import EventCommissionModal from "@/Components/custom/EventCommissionModal.vue";

const props = defineProps({
  commissions: Array
});

const modalState = reactive({
  open: false,
  mode: 'create',
  data: null
});

const toMoney = (value) => {
  return Number(value || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
};

const statusBadgeClass = (status) => {
  return status === 'paid' 
    ? 'bg-green-600 text-white shadow-sm' 
    : 'bg-amber-500 text-white shadow-sm';
};

const openModal = (mode, data = null) => {
  modalState.mode = mode;
  modalState.data = data;
  modalState.open = true;
};

const handleSuccess = () => {
  router.reload();
};
</script>
