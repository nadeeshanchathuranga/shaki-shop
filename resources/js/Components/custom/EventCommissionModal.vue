<template>
  <TransitionRoot as="template" :show="open">
    <Dialog as="div" class="relative z-[100]" @close="closeModal">
      <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
      </TransitionChild>

      <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200" leave-from="opacity-100 translate-y-0 sm:scale-100" leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            <DialogPanel class="relative transform overflow-hidden rounded-[20px] bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl p-8 border-4 border-black">
              <div class="mb-6 flex items-center justify-between border-b-4 border-black pb-4">
                <DialogTitle as="h3" class="text-3xl font-extrabold leading-6 text-black uppercase italic">
                  {{ mode === 'view' ? 'View Commission' : (mode === 'edit' ? 'Process Payment / Edit' : 'Event Commission Form') }}
                </DialogTitle>
                <button @click="closeModal" class="text-black hover:text-gray-600 transition">
                  <i class="ri-close-circle-fill text-4xl"></i>
                </button>
              </div>

              <form @submit.prevent="submit" class="space-y-5">
                <!-- Section: Event & Customer Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                  <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-black uppercase mb-1">Event Title <span class="text-red-600">*</span></label>
                    <input v-model="form.event_title" :disabled="mode === 'view' || mode === 'edit'" type="text" placeholder="Enter Event Title" class="w-full px-4 py-3 text-black border-2 border-black rounded-xl focus:ring-2 focus:ring-blue-500 outline-none font-medium disabled:bg-gray-100" required />
                  </div>

                  <div>
                    <label class="block text-sm font-bold text-black uppercase mb-1">Customer Name <span class="text-red-600">*</span></label>
                    <input v-model="form.customer_name" :disabled="mode === 'view' || mode === 'edit'" type="text" placeholder="Enter Customer Name" class="w-full px-4 py-3 text-black border-2 border-black rounded-xl focus:ring-2 focus:ring-blue-500 outline-none font-medium disabled:bg-gray-100" required />
                  </div>

                  <div>
                    <label class="block text-sm font-bold text-black uppercase mb-1">Customer Contact <span class="text-red-600">*</span></label>
                    <input v-model="form.customer_phone" :disabled="mode === 'view' || mode === 'edit'" type="text" placeholder="Enter Contact Number" class="w-full px-4 py-3 text-black border-2 border-black rounded-xl focus:ring-2 focus:ring-blue-500 outline-none font-medium disabled:bg-gray-100" required />
                  </div>

                  <div>
                    <label class="block text-sm font-bold text-black uppercase mb-1">Customer Email</label>
                    <input v-model="form.customer_email" :disabled="mode === 'view' || mode === 'edit'" type="email" placeholder="Enter Email (Optional)" class="w-full px-4 py-3 text-black border-2 border-black rounded-xl focus:ring-2 focus:ring-blue-500 outline-none font-medium disabled:bg-gray-100" />
                  </div>

                  <div>
                    <label class="block text-sm font-bold text-black uppercase mb-1">Select Event Date <span class="text-red-600">*</span></label>
                    <input v-model="form.event_date" :disabled="mode === 'view' || mode === 'edit'" type="date" class="w-full px-4 py-3 text-black border-2 border-black rounded-xl focus:ring-2 focus:ring-blue-500 outline-none font-medium disabled:bg-gray-100" required />
                  </div>

                  <div>
                    <label class="block text-sm font-bold text-black uppercase mb-1">Total Amount <span class="text-red-600">*</span></label>
                    <div class="relative">
                      <input v-model="form.total_amount" :disabled="mode === 'view' || mode === 'edit'" type="number" step="0.01" class="w-full px-4 py-3 text-black border-2 border-black rounded-xl focus:ring-2 focus:ring-blue-500 outline-none font-medium disabled:bg-gray-100" required />
                      <span class="absolute right-4 top-3 text-gray-500 font-bold uppercase">LKR</span>
                    </div>
                  </div>
                </div>

                <!-- Financial Breakdown Section -->
                <div class="p-6 bg-slate-900 rounded-2xl border-4 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] space-y-4">
                  <div class="flex justify-between items-center border-b border-slate-700 pb-3">
                    <span class="text-slate-400 text-xs font-bold uppercase tracking-widest">Total Bill</span>
                    <span class="text-white text-xl font-black">{{ toMoney(form.total_amount) }} LKR</span>
                  </div>
                  <div class="flex justify-between items-center border-b border-slate-700 pb-3">
                    <span class="text-slate-400 text-xs font-bold uppercase tracking-widest">Already Paid</span>
                    <span class="text-emerald-400 text-xl font-black">{{ toMoney(form.advance_amount) }} LKR</span>
                  </div>
                  <div class="flex justify-between items-center">
                    <span class="text-rose-400 text-xs font-bold uppercase tracking-widest italic">Outstanding Balance</span>
                    <span class="text-white text-2xl font-black">{{ toMoney(remainingBalance) }} LKR</span>
                  </div>
                </div>

                <!-- Scenario-Based Payment Actions (Edit Mode Only) -->
                <div v-if="mode === 'edit' && remainingBalance > 0" class="pt-4 border-t-2 border-black border-dashed space-y-4">
                    <div class="flex items-center justify-between">
                        <h4 class="text-lg font-black text-black uppercase italic">Process New Payment</h4>
                        <div v-if="newPayment" class="text-xs font-black uppercase tracking-widest px-3 py-1 rounded bg-slate-100 border border-slate-300">
                            Result: <span :class="predictedStatus === 'paid' ? 'text-emerald-600' : 'text-blue-600'">{{ predictedStatus.replace('_', ' ') }}</span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-1">
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Enter Payment Amount</label>
                            <div class="relative">
                                <input v-model="newPayment" type="number" step="0.01" :max="remainingBalance" class="w-full px-4 py-3 text-black border-2 border-black rounded-xl focus:ring-2 focus:ring-blue-500 outline-none font-medium" placeholder="0.00" />
                                <span class="absolute right-4 top-3 text-gray-500 font-bold uppercase">LKR</span>
                            </div>
                            <p v-if="newPayment > remainingBalance" class="mt-2 text-xs text-red-600 font-black uppercase italic">
                                <i class="ri-error-warning-fill mr-1"></i> Payment exceeds outstanding balance!
                            </p>
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="button" @click="addPartialPayment" :disabled="!newPayment || newPayment <= 0 || newPayment > remainingBalance" class="flex-1 px-4 py-3 bg-blue-600 text-white rounded-xl font-bold uppercase text-xs border-2 border-black shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] hover:translate-y-0.5 active:shadow-none disabled:opacity-50 disabled:bg-gray-400">
                                Log Payment
                            </button>
                            <button type="button" @click="settleFullBalance" class="flex-1 px-4 py-3 bg-emerald-500 text-white rounded-xl font-bold uppercase text-xs border-2 border-black shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] hover:translate-y-0.5 active:shadow-none">
                                Quick Settle (Full)
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Status Card for Completed Events -->
                <div v-if="remainingBalance <= 0 && mode !== 'create'" class="p-4 bg-emerald-100 border-2 border-emerald-500 rounded-xl flex items-center justify-center text-emerald-800 font-black uppercase italic">
                    <i class="ri-checkbox-circle-fill mr-2 text-xl"></i> This event is fully settled and closed.
                </div>

                <div v-if="mode === 'create'" class="pt-4 border-t-2 border-black border-dashed">
                    <label class="block text-sm font-bold text-black uppercase mb-1">Initial Advance Amount <span class="text-red-600">*</span></label>
                    <div class="relative">
                      <input v-model="form.advance_amount" type="number" step="0.01" class="w-full px-4 py-3 text-black border-2 border-black rounded-xl focus:ring-2 focus:ring-blue-500 outline-none font-medium" required />
                      <span class="absolute right-4 top-3 text-gray-500 font-bold uppercase">LKR</span>
                    </div>
                </div>

                <div class="mt-8 flex justify-end space-x-4">
                  <button type="button" @click="closeModal" class="px-10 py-3 text-lg font-bold uppercase border-4 border-black rounded-xl hover:bg-gray-100 transition shadow-sm">
                    {{ mode === 'view' ? 'Close' : 'Cancel' }}
                  </button>
                  <button v-if="mode === 'create'" type="submit" :disabled="isSubmitting" class="px-10 py-3 text-lg font-bold uppercase bg-black text-white rounded-xl hover:bg-gray-800 transition shadow-lg disabled:opacity-50">
                    {{ isSubmitting ? 'Saving...' : 'Save Commission' }}
                  </button>
                </div>
              </form>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { useForm } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const props = defineProps({
  open: Boolean,
  customerData: Object,
  commissionData: Object,
  mode: { type: String, default: 'create' }
});

const emit = defineEmits(['update:open', 'success']);

const form = useForm({
  id: null,
  event_title: '',
  event_date: new Date().toISOString().split('T')[0],
  customer_id: null,
  customer_name: '',
  customer_email: '',
  customer_phone: '',
  total_amount: 0,
  advance_amount: 0,
  payment_status: 'pending',
  notes: ''
});

const newPayment = ref("");

const remainingBalance = computed(() => {
    return Number(form.total_amount || 0) - Number(form.advance_amount || 0);
});

const predictedStatus = computed(() => {
    const payment = Number(newPayment.value || 0);
    const totalPaid = Number(form.advance_amount) + payment;
    if (totalPaid >= Number(form.total_amount)) return 'paid';
    if (totalPaid > 0) return 'partially_paid';
    return 'pending';
});

const toMoney = (value) => {
  return Number(value || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

// Robust watcher for pre-populating data
watch([() => props.open, () => props.commissionData], ([isOpen, data]) => {
  if (isOpen && data) {
    // Manually map fields to ensure they load
    form.id = data.id;
    form.event_title = data.event_title || '';
    form.event_date = data.event_date || '';
    form.customer_id = data.customer_id;
    form.customer_name = data.customer_name || '';
    form.customer_email = data.customer_email || '';
    form.customer_phone = data.customer_phone || '';
    form.total_amount = data.total_amount || 0;
    form.advance_amount = data.advance_amount || 0;
    form.payment_status = data.payment_status || 'pending';
    form.notes = data.notes || '';
  } else if (isOpen && props.mode === 'create') {
      form.reset();
  }
}, { immediate: true });

// Auto-fill from POS customer panel (only for create mode)
watch(() => props.customerData, (newVal) => {
  if (newVal && props.mode === 'create' && props.open) {
    if (newVal.id) form.customer_id = newVal.id;
    if (newVal.name) form.customer_name = newVal.name;
    if (newVal.email) form.customer_email = newVal.email;
    if (newVal.contactNumber) form.customer_phone = newVal.contactNumber;
  }
});

const isSubmitting = ref(false);

const closeModal = () => {
  emit('update:open', false);
  form.reset();
  newPayment.value = "";
};

const addPartialPayment = () => {
    const payment = Number(newPayment.value);
    if (!payment || payment <= 0) return;

    if (payment > remainingBalance.value) {
        Swal.fire('Warning', 'Payment exceeds outstanding balance. Please use "Pay Balance" instead.', 'warning');
        return;
    }

    const updatedAdvance = Number(form.advance_amount) + payment;
    const newStatus = updatedAdvance >= Number(form.total_amount) ? 'paid' : 'partially_paid';

    confirmAndExecute({
        advance_amount: updatedAdvance,
        payment_status: newStatus
    }, `Log additional payment of ${toMoney(payment)} LKR?`);
};

const settleFullBalance = () => {
    confirmAndExecute({
        advance_amount: form.total_amount,
        payment_status: 'paid'
    }, `Settle remaining balance of ${toMoney(remainingBalance.value)} LKR and close this event?`);
};

const confirmAndExecute = (payload, text) => {
    Swal.fire({
        title: 'Confirm Payment',
        text: text,
        icon: 'info',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        confirmButtonText: 'Yes, Log Payment'
    }).then((result) => {
        if (result.isConfirmed) {
            updateRecord(payload);
        }
    });
};

const updateRecord = (payload) => {
    isSubmitting.value = true;
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    
    fetch(`/event-commissions/${form.id}`, {
        method: 'PATCH',
        body: JSON.stringify(payload),
        headers: {
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
        },
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Recorded!', 'Payment has been updated and synchronized.', 'success');
            emit('success', data.data);
            // Update local form to reflect new state immediately
            form.advance_amount = data.data.advance_amount;
            form.payment_status = data.data.payment_status;
            newPayment.value = "";
        }
    })
    .finally(() => isSubmitting.value = false);
};

const submit = () => {
  if (isSubmitting.value || props.mode !== 'create') return;
  
  isSubmitting.value = true;
  
  // Predict status based on financials
  if (remainingBalance.value <= 0) form.payment_status = 'paid';
  else if (Number(form.advance_amount) > 0) form.payment_status = 'partially_paid';
  else form.payment_status = 'pending';

  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  
  fetch('/event-commissions', {
    method: 'POST',
    body: JSON.stringify(form.data()),
    headers: {
      'X-CSRF-TOKEN': token,
      'X-Requested-With': 'XMLHttpRequest',
      'Content-Type': 'application/json',
    },
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      // 1. Show the success alert and wait for user acknowledgment
      Swal.fire({ 
        title: 'Success!', 
        text: `Event commission for "${form.event_title}" has been successfully recorded.`, 
        icon: 'success',
        confirmButtonColor: '#2563eb',
      }).then(() => {
          // 2. ONLY close the modal after acknowledgment
          closeModal();
      });

      // 3. Notify parent of success
      emit('success', data.data);
    } else {
        Swal.fire('Error', data.message || 'Failed to save commission. Please check your inputs.', 'error');
    }
  })
  .catch(err => {
      console.error(err);
      Swal.fire('Connection Error', 'Failed to reach the server. Please try again.', 'error');
  })
  .finally(() => isSubmitting.value = false);
};
</script>
