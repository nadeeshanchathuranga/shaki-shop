<template>
  <Head :title="`Supplier Invoice - ${supplier.name}`" />
  <Banner />

  <div class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gray-100 md:px-36 px-8">
    <Header />

    <div class="w-full md:w-5/6 py-8 space-y-8">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center space-x-4">
          <Link :href="route('suppliers.index')">
            <img src="/images/back-arrow.png" class="w-12 h-12" alt="Back" />
          </Link>
          <div>
            <p class="text-3xl font-bold tracking-wide text-black uppercase">Supplier Invoice</p>
            <p class="text-sm text-gray-700">
              {{ supplier.name }} | {{ supplier.contact || 'No contact' }}
            </p>
          </div>
        </div>

        <div class="text-sm text-gray-700">
          Outstanding: <span class="font-bold text-red-600">{{ formatCurrency(totals.outstanding_amount) }} LKR</span>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        
        <div class="bg-white rounded-xl p-4 shadow border border-gray-200">
          <p class="text-xs uppercase text-gray-500">Supplier Commission</p>
          <p class="text-lg font-bold text-sky-700">{{ formatCurrency(totals.total_supplier_commission) }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow border border-gray-200">
          <p class="text-xs uppercase text-gray-500">To Be Paid</p>
          <p class="text-lg font-bold text-red-700">{{ formatCurrency(totals.outstanding_amount) }}</p>
        </div>
      </div>

      <div class="bg-white rounded-xl p-6 shadow border border-gray-200 space-y-4">
        <p class="text-lg font-bold text-black">Record Supplier Payment</p>

        <form class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end" @submit.prevent="submitPayment">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Amount (LKR)</label>
            <input
              v-model="paymentForm.amount"
              type="number"
              min="0.01"
              step="0.01"
              class="w-full border border-gray-300 rounded-lg px-3 py-2"
              placeholder="Enter payment amount"
            />
            <p v-if="paymentForm.errors.amount" class="text-xs text-red-600 mt-1">{{ paymentForm.errors.amount }}</p>
          </div>

          <div class="md:col-span-2">
            <label class="block text-sm font-semibold text-gray-700 mb-1">Notes</label>
            <input
              v-model="paymentForm.notes"
              type="text"
              class="w-full border border-gray-300 rounded-lg px-3 py-2"
              placeholder="Optional note"
            />
            <p v-if="paymentForm.errors.notes" class="text-xs text-red-600 mt-1">{{ paymentForm.errors.notes }}</p>
          </div>

          <button
            type="submit"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold disabled:opacity-50"
            :disabled="paymentForm.processing || Number(totals.outstanding_amount) <= 0"
          >
            Pay Commission
          </button>
        </form>
      </div>

      <div class="bg-white rounded-xl p-6 shadow border border-gray-200 space-y-4">
        <p class="text-lg font-bold text-black">Commission Details (Rental Sales)</p>

        <div class="overflow-x-auto">
          <table class="w-full text-sm border border-gray-200">
            <thead class="bg-blue-600 text-white">
              <tr>
                <th class="p-3 text-left">Date</th>
                <th class="p-3 text-left">Order</th>
                <th class="p-3 text-left">Item</th>
                <th class="p-3 text-right">Qty</th>
                <th class="p-3 text-right">Rent</th>
                <th class="p-3 text-right">Total Rent</th>
                <th class="p-3 text-right">Supplier Comm.</th>
                
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in commissionRows" :key="row.id" class="border-t border-gray-200">
                <td class="p-3">{{ row.date }}</td>
                <td class="p-3">{{ row.order_id || '-' }}</td>
                <td class="p-3">{{ row.item_name || 'Rental Item' }}</td>
                <td class="p-3 text-right">{{ row.quantity }}</td>
                <td class="p-3 text-right">{{ formatCurrency(row.unit_price) }}</td>
                <td class="p-3 text-right">{{ formatCurrency(row.total_price) }}</td>
                <td class="p-3 text-right font-semibold text-sky-700">{{ formatCurrency(row.supplier_commission) }}</td>
                
              </tr>
              <tr v-if="commissionRows.length === 0">
                <td colspan="8" class="p-4 text-center text-gray-500">No rental commission records found for this supplier.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="bg-white rounded-xl p-6 shadow border border-gray-200 space-y-4">
        <p class="text-lg font-bold text-black">Payment History</p>

        <div class="overflow-x-auto">
          <table class="w-full text-sm border border-gray-200">
            <thead class="bg-gray-900 text-white">
              <tr>
                <th class="p-3 text-left">Date</th>
                <th class="p-3 text-right">Amount</th>
                <th class="p-3 text-right">Remaining Payment</th>
                <th class="p-3 text-left">Paid By</th>
                <th class="p-3 text-left">Notes</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in paymentRowsWithRemaining" :key="row.id" class="border-t border-gray-200">
                <td class="p-3">{{ row.date }}</td>
                <td class="p-3 text-right font-semibold text-indigo-700">{{ formatCurrency(row.amount) }}</td>
                <td class="p-3 text-right font-semibold text-red-700">{{ formatCurrency(row.remaining_payment) }}</td>
                <td class="p-3">{{ row.paid_by || '-' }}</td>
                <td class="p-3">{{ row.notes || '-' }}</td>
              </tr>
              <tr v-if="paymentRows.length === 0">
                <td colspan="5" class="p-4 text-center text-gray-500">No supplier payments yet.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <Footer />
</template>

<script setup>
import { useForm, Link, Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import Header from '@/Components/custom/Header.vue';
import Footer from '@/Components/custom/Footer.vue';
import Banner from '@/Components/Banner.vue';

const props = defineProps({
  supplier: {
    type: Object,
    required: true,
  },
  commissionRows: {
    type: Array,
    default: () => [],
  },
  paymentRows: {
    type: Array,
    default: () => [],
  },
  totals: {
    type: Object,
    required: true,
  },
});

const paymentForm = useForm({
  amount: '',
  notes: '',
});

const formatCurrency = (value) => {
  const amount = Number(value || 0);
  return amount.toLocaleString('en-LK', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
};

const paymentRowsWithRemaining = computed(() => {
  const sortedAsc = [...props.paymentRows].sort((a, b) => {
    const dateA = new Date(a.date || 0).getTime();
    const dateB = new Date(b.date || 0).getTime();

    if (dateA !== dateB) {
      return dateA - dateB;
    }

    return Number(a.id || 0) - Number(b.id || 0);
  });

  let remaining = Number(props.totals.total_supplier_commission || 0);
  const remainingMap = new Map();

  sortedAsc.forEach((row) => {
    remaining = Math.max(remaining - Number(row.amount || 0), 0);
    remainingMap.set(row.id, Number(remaining.toFixed(2)));
  });

  return props.paymentRows.map((row) => ({
    ...row,
    remaining_payment: remainingMap.get(row.id) ?? Number(props.totals.outstanding_amount || 0),
  }));
});

const submitPayment = () => {
  paymentForm.post(route('suppliers.invoice.payments.store', props.supplier.id), {
    preserveScroll: true,
    onSuccess: () => {
      paymentForm.reset();
    },
  });
};
</script>
