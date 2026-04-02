<template>
  <TransitionRoot as="template" :show="open">
    <Dialog class="relative z-10" @close="closeModal">
      <!-- Overlay -->
      <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
        leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" />
      </TransitionChild>

      <!-- Modal Content -->
      <div class="fixed inset-0 z-10 flex items-center justify-center">
        <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0 scale-95"
          enter-to="opacity-100 scale-100" leave="ease-in duration-200" leave-from="opacity-100 scale-100"
          leave-to="opacity-0 scale-95">
          <DialogPanel
            class="bg-white border-1 border-gray-600 rounded-[20px] shadow-xl w-5/6 max-w-4xl p-6 text-center h-[90vh] overflow-hidden flex flex-col">

            <h2 class="text-3xl font-bold text-black mb-4">Return Rental Items</h2>

            <!-- Step 1: Search & Select a rental bill -->
            <div v-if="!selectedSale" class="flex flex-col flex-grow overflow-hidden">
              <div class="flex items-center space-x-4 mb-4">
                <input v-model="searchQuery" @input="debouncedSearch" type="text"
                  placeholder="Search by Order ID..."
                  class="w-full px-6 py-3 text-lg border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500" />
              </div>

              <div class="flex-grow overflow-y-auto">
                <template v-if="loading">
                  <p class="text-xl text-blue-500 py-8">Loading rental bills...</p>
                </template>
                <template v-else-if="sales.data && sales.data.length > 0">
                  <table class="w-full text-left">
                    <thead class="bg-gray-100 sticky top-0">
                      <tr>
                        <th class="px-4 py-3 text-sm font-bold text-gray-700">Order ID</th>
                        <th class="px-4 py-3 text-sm font-bold text-gray-700">Customer</th>
                        <th class="px-4 py-3 text-sm font-bold text-gray-700">Type</th>
                        <th class="px-4 py-3 text-sm font-bold text-gray-700">Rental Period</th>
                        <th class="px-4 py-3 text-sm font-bold text-gray-700">Deposit</th>
                        <th class="px-4 py-3 text-sm font-bold text-gray-700">Total</th>
                        <th class="px-4 py-3 text-sm font-bold text-gray-700">Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="sale in sales.data" :key="sale.id"
                        class="border-b hover:bg-gray-50 transition cursor-pointer">
                        <td class="px-4 py-3 text-sm font-mono font-bold">{{ sale.order_id }}</td>
                        <td class="px-4 py-3 text-sm">{{ sale.customer?.name || 'Walk-in' }}</td>
                        <td class="px-4 py-3 text-sm">
                          <span :class="sale.rental_type === 'rent_now' ? 'bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs font-bold' : 'bg-sky-100 text-sky-700 px-2 py-1 rounded-full text-xs font-bold'">
                            {{ sale.rental_type === 'rent_now' ? 'Rent Now' : 'Rent Later' }}
                          </span>
                        </td>
                        <td class="px-4 py-3 text-sm">{{ sale.rental_date_from }} to {{ sale.rental_date_to }}</td>
                        <td class="px-4 py-3 text-sm">{{ Number(sale.deposit).toFixed(2) }} LKR</td>
                        <td class="px-4 py-3 text-sm">{{ Number(sale.total_amount).toFixed(2) }} LKR</td>
                        <td class="px-4 py-3">
                          <button @click="selectSale(sale)"
                            class="px-4 py-2 text-sm font-bold text-white bg-purple-600 rounded-lg hover:bg-purple-700 transition">
                            Select
                          </button>
                        </td>
                      </tr>
                    </tbody>
                  </table>

                  <div class="flex items-center justify-between mt-4 px-2">
                    <button @click="fetchPage(sales.prev_page_url)" :disabled="!sales.prev_page_url"
                      class="px-4 py-2 text-sm text-white bg-blue-500 rounded-md disabled:bg-gray-300 disabled:cursor-not-allowed">
                      Previous
                    </button>
                    <span class="text-sm text-gray-600">Page {{ sales.current_page }} of {{ sales.last_page }}</span>
                    <button @click="fetchPage(sales.next_page_url)" :disabled="!sales.next_page_url"
                      class="px-4 py-2 text-sm text-white bg-blue-500 rounded-md disabled:bg-gray-300 disabled:cursor-not-allowed">
                      Next
                    </button>
                  </div>
                </template>
                <template v-else>
                  <p class="text-xl text-red-500 py-8">No rental bills found.</p>
                </template>
              </div>
            </div>

            <!-- Step 2: Selected bill details & return processing -->
            <div v-else class="flex flex-col flex-grow overflow-y-auto space-y-4 text-left">
              <button @click="selectedSale = null"
                class="self-start px-4 py-2 text-sm font-bold text-purple-600 border border-purple-600 rounded-lg hover:bg-purple-50 transition">
                ← Back to Bills
              </button>

              <div class="bg-gray-50 rounded-xl p-4 space-y-3 border">
                <div class="flex justify-between text-lg">
                  <span class="font-bold text-gray-700">Order ID:</span>
                  <span class="font-mono font-bold">{{ selectedSale.order_id }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="font-bold text-gray-700">Customer:</span>
                  <span>{{ selectedSale.customer?.name || 'Walk-in' }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="font-bold text-gray-700">Rental Period:</span>
                  <span>{{ selectedSale.rental_date_from }} to {{ selectedSale.rental_date_to }}</span>
                </div>

                <!-- Items -->
                <div class="border-t pt-3">
                  <p class="font-bold text-gray-700 mb-2">Rental Items:</p>
                  <div v-for="item in rentalSaleItems" :key="item.id"
                    class="flex justify-between text-sm py-1 border-b border-gray-200">
                    <span>{{ item.rental_item?.item_name || 'Rental Item' }} × {{ item.quantity }}</span>
                    <span>{{ Number(item.total_price).toFixed(2) }} LKR</span>
                  </div>
                </div>

                <div class="border-t pt-3 space-y-2">
                  <div class="flex justify-between">
                    <span class="font-bold">Sub Total:</span>
                    <span>{{ Number(selectedSale.total_amount).toFixed(2) }} LKR</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="font-bold">Advance Paid:</span>
                    <span class="text-green-600 font-bold">{{ Number(selectedSale.advance_amount).toFixed(2) }} LKR</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="font-bold">Amount After Advance:</span>
                    <span>{{ amountAfterAdvance.toFixed(2) }} LKR</span>
                  </div>
                </div>

                <!-- Late fee calculation -->
                <div class="border-t pt-3 space-y-2">
                  <div class="flex justify-between">
                    <span class="font-bold">Agreed Return Date:</span>
                    <span>{{ selectedSale.rental_date_to }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="font-bold">Actual Return Date:</span>
                    <span>{{ todayDate }}</span>
                  </div>
                  <div v-if="lateDays > 0" class="bg-red-50 border border-red-300 rounded-lg p-3 space-y-1">
                    <div class="flex justify-between text-red-700">
                      <span class="font-bold">Late by:</span>
                      <span class="font-bold">{{ lateDays }} day(s)</span>
                    </div>
                    <div class="flex justify-between text-red-700">
                      <span class="font-bold">Late Fee (Rs. 200 × {{ lateDays }}):</span>
                      <span class="font-bold">{{ lateFee.toFixed(2) }} LKR</span>
                    </div>
                  </div>
                  <div v-else class="bg-green-50 border border-green-300 rounded-lg p-3">
                    <p class="text-green-700 font-bold text-center">Returned on time — No late fee!</p>
                  </div>
                </div>

                <div class="border-t pt-3 space-y-2">
                  <div class="flex justify-between text-xl font-bold">
                    <span>Deposit Paid:</span>
                    <span>{{ Number(selectedSale.deposit).toFixed(2) }} LKR</span>
                  </div>
                </div>

                <!-- Damage Amount Input -->
                <div class="border-t pt-3 space-y-2">
                  <div class="flex items-center justify-between">
                    <span class="font-bold text-lg">Damage Amount:</span>
                    <input v-model.number="damageAmount" type="number" min="0" step="0.01"
                      class="w-48 px-4 py-2 text-lg border-2 border-gray-300 rounded-lg text-right focus:ring-2 focus:ring-red-500" />
                  </div>
                </div>

                <!-- Deposit Deduction Summary -->
                <div class="border-t pt-3 space-y-2">
                  <p class="font-bold text-gray-700 mb-2">Deposit Deduction Summary:</p>
                  <div v-if="lateDays > 0" class="flex justify-between text-red-700">
                    <span>Late Fee (Rs. 200 × {{ lateDays }}):</span>
                    <span class="font-bold">{{ lateFee.toFixed(2) }} LKR</span>
                  </div>
                  <div v-else class="flex justify-between text-green-700">
                    <span>Late Fee:</span>
                    <span class="font-bold">None (On time)</span>
                  </div>
                  <div v-if="damageAmount > 0" class="flex justify-between text-red-700">
                    <span>Damage Deduction:</span>
                    <span class="font-bold">{{ Number(damageAmount).toFixed(2) }} LKR</span>
                  </div>
                  <div class="flex justify-between font-bold">
                    <span>Total Deductions:</span>
                    <span>{{ totalDeductions.toFixed(2) }} LKR</span>
                  </div>
                  <div class="flex justify-between font-bold text-xl" :class="depositRefund >= 0 ? 'text-green-700' : 'text-red-700'">
                    <span>{{ depositRefund >= 0 ? 'Deposit Refund:' : 'Customer Must Pay:' }}</span>
                    <span>{{ Math.abs(depositRefund).toFixed(2) }} LKR</span>
                  </div>
                  <div v-if="depositRefund < 0" class="bg-red-50 border border-red-300 rounded-lg p-3">
                    <p class="text-red-700 font-bold text-center">⚠ Deposit is not enough! Customer must pay {{ Math.abs(depositRefund).toFixed(2) }} LKR</p>
                  </div>
                </div>

                <div class="border-t pt-3 space-y-2">
                  <div class="flex justify-between text-xl font-bold">
                    <span>Total Amount to Pay:</span>
                    <span>{{ totalToPay.toFixed(2) }} LKR</span>
                  </div>
                </div>

                <!-- Cash Input -->
                <div class="border-t pt-3 space-y-2">
                  <div class="flex items-center justify-between">
                    <span class="font-bold text-lg">Cash Received:</span>
                    <input v-model.number="cashReceived" type="number" min="0" step="0.01"
                      class="w-48 px-4 py-2 text-lg border-2 border-gray-300 rounded-lg text-right focus:ring-2 focus:ring-purple-500" />
                  </div>
                  <div class="flex justify-between text-lg font-bold" :class="returnBalance >= 0 ? 'text-green-700' : 'text-red-700'">
                    <span>Balance:</span>
                    <span>{{ returnBalance.toFixed(2) }} LKR</span>
                  </div>
                </div>

                <!-- Payment Method -->
                <div class="border-t pt-3 space-y-4">
                  <div class="flex items-center justify-center w-full pt-4 space-x-8">
                    <p class="text-xl text-black">Payment Method :</p>
                    <div @click="paymentMethod = 'cash'" :class="[
                      'cursor-pointer w-[100px] border border-black rounded-xl flex flex-col justify-center items-center text-center p-2',
                      paymentMethod === 'cash' ? 'bg-yellow-500 font-bold' : 'text-black'
                    ]">
                      <img src="/images/money-stack.png" class="w-24" />
                    </div>
                    <div @click="paymentMethod = 'card'" :class="[
                      'cursor-pointer w-[100px] border border-black rounded-xl flex flex-col justify-center items-center text-center p-2',
                      paymentMethod === 'card' ? 'bg-yellow-500 font-bold' : 'text-black'
                    ]">
                      <img src="/images/bank-card.png" class="w-24" />
                    </div>
                  </div>
                </div>
              </div>

              <!-- Confirm Return Button -->
              <div class="flex justify-center pt-2">
                <button @click="confirmReturn" :disabled="returnBalance < 0 || processing"
                  :class="[
                    'px-12 py-4 text-xl font-bold text-white uppercase rounded-xl tracking-wider transition',
                    returnBalance >= 0 && !processing ? 'bg-purple-600 hover:bg-purple-700 cursor-pointer' : 'bg-gray-400 cursor-not-allowed'
                  ]">
                  {{ processing ? 'Processing...' : 'Confirm Return & Print Receipt' }}
                </button>
              </div>
            </div>

            <!-- Close button -->
            <div class="flex justify-end mt-4 flex-shrink-0" v-if="!selectedSale">
              <button @click="closeModal"
                class="px-6 py-2 text-lg font-bold text-white bg-red-600 rounded-lg hover:bg-red-700">
                Close
              </button>
            </div>
          </DialogPanel>
        </TransitionChild>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import {
  Dialog,
  DialogPanel,
  TransitionChild,
  TransitionRoot,
} from "@headlessui/vue";
import { ref, computed } from "vue";
import axios from "axios";
import { debounce } from "lodash";
import { usePage } from "@inertiajs/vue3";

const page = usePage();
const companyInfo = computed(() => page.props.companyInfo);

const sales = ref({});
const loading = ref(false);
const searchQuery = ref("");
const selectedSale = ref(null);
const cashReceived = ref(0);
const paymentMethod = ref("cash");
const processing = ref(false);
const damageAmount = ref(0);

const emit = defineEmits(["update:open"]);

const props = defineProps({
  open: { type: Boolean, required: true },
});

const todayDate = computed(() => {
  return new Date().toISOString().split('T')[0];
});

const rentalSaleItems = computed(() => {
  if (!selectedSale.value) return [];
  return (selectedSale.value.sale_items || []).filter(item => item.rental_item_id);
});

const amountAfterAdvance = computed(() => {
  if (!selectedSale.value) return 0;
  const total = Number(selectedSale.value.total_amount) - Number(selectedSale.value.discount || 0) - Number(selectedSale.value.custom_discount || 0);
  return total - Number(selectedSale.value.advance_amount);
});

const lateDays = computed(() => {
  if (!selectedSale.value) return 0;
  const agreedDate = new Date(selectedSale.value.rental_date_to);
  const today = new Date();
  agreedDate.setHours(0, 0, 0, 0);
  today.setHours(0, 0, 0, 0);
  const diff = Math.floor((today - agreedDate) / (1000 * 60 * 60 * 24));
  return Math.max(0, diff);
});

const lateFee = computed(() => lateDays.value * 200);

const totalDeductions = computed(() => lateFee.value + (Number(damageAmount.value) || 0));

const depositRefund = computed(() => {
  if (!selectedSale.value) return 0;
  const deposit = Number(selectedSale.value.deposit) || 0;
  return deposit - totalDeductions.value;
});

const totalToPay = computed(() => {
  // If deposit doesn't cover deductions, customer must pay the deficit
  if (depositRefund.value < 0) {
    return Math.abs(depositRefund.value);
  }
  return 0;
});

const returnBalance = computed(() => (cashReceived.value || 0) - totalToPay.value);

const closeModal = () => {
  selectedSale.value = null;
  searchQuery.value = "";
  cashReceived.value = 0;
  paymentMethod.value = "cash";
  damageAmount.value = 0;
  emit("update:open", false);
};

const selectSale = (sale) => {
  selectedSale.value = sale;
  cashReceived.value = 0;
  paymentMethod.value = "cash";
  damageAmount.value = 0;
};

const fetchSales = async (url = "/api/rental-sales") => {
  loading.value = true;
  try {
    const response = await axios.post(url, { search: searchQuery.value });
    sales.value = response.data.sales;
  } catch (error) {
    console.error("Error fetching rental sales:", error);
  } finally {
    loading.value = false;
  }
};

const debouncedSearch = debounce(() => fetchSales(), 300);

const fetchPage = (url) => {
  if (url) fetchSales(url);
};

const confirmReturn = async () => {
  if (returnBalance.value < 0) return;

  // Open the print window immediately from the user click to avoid popup blocking on some browsers/devices.
  const preOpenedPrintWindow = window.open("", "_blank");

  processing.value = true;

  try {
    const response = await axios.post("/pos/rental-return", {
      sale_id: selectedSale.value.id,
      cash_received: cashReceived.value,
      payment_method: paymentMethod.value,
      damage_amount: damageAmount.value || 0,
    });

    // Print the final return receipt
    printReturnReceipt(response.data, preOpenedPrintWindow);

    // Reset and close
    selectedSale.value = null;
    cashReceived.value = 0;
    searchQuery.value = "";
    fetchSales(); // Refresh the list
  } catch (error) {
    if (preOpenedPrintWindow && !preOpenedPrintWindow.closed) {
      preOpenedPrintWindow.close();
    }
    console.error("Error processing return:", error);
    alert("Failed to process rental return. Please try again.");
  } finally {
    processing.value = false;
  }
};

const printReturnReceipt = (data, openedWindow = null) => {
  const sale = data.sale;
  const rentalItems = (sale.sale_items || []).filter(i => i.rental_item_id);

  const itemRows = rentalItems.map(item => `
    <tr>
      <td>${item.rental_item?.item_name || 'Rental Item'}</td>
      <td style="text-align:center;">${item.quantity}</td>
      <td>${Number(item.total_price).toFixed(2)}</td>
    </tr>
  `).join('');

  const receiptHTML = `
  <!DOCTYPE html>
  <html lang="en">
  <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Rental Return Receipt</title>
      <style>
          @media print {
              body { margin: 0; padding: 0 5mm 0 0; -webkit-print-color-adjust: exact; }
          }
          body { background:#fff; font-size:12px; font-family:'Arial',sans-serif; margin:0; padding:10px 5mm 10mm 7mm; color:#000; }
          .header { text-align:center; margin-bottom:16px; }
          .header h1 { font-size:20px; font-weight:bold; margin:0; }
          .header p { font-size:12px; margin:4px 0; }
          .section { margin-bottom:16px; padding-top:8px; border-top:1px solid #000; }
          .info-row { display:flex; justify-content:space-between; font-size:14px; margin-top:8px; }
          .info-row p { margin:0; font-weight:bold; }
          .info-row small { font-weight:normal; }
          table { width:100%; font-size:12px; border-collapse:collapse; margin-top:8px; }
          table th, table td { padding:6px 8px; }
          table th { text-align:left; }
          table td { text-align:right; }
          table td:first-child { text-align:left; }
          .totals { border-top:1px solid #000; padding-top:8px; font-size:12px; }
          .totals div { display:flex; justify-content:space-between; margin-bottom:8px; }
          .footer { text-align:center; font-size:10px; margin-top:16px; }
          .footer p { margin:6px 0; }
      </style>
  </head>
  <body>
      <div class="receipt-container">
          <div class="header">
              <img src="/images/billlogo.png" style="width: 230px; height: 100px;" />
              <p>No 51/1/1, Mahabage road, Ragama | 0756865900</p>
              <p style="font-weight:bold; font-size:14px; margin-top:8px; border:1px solid #000; display:inline-block; padding:2px 12px;">RENTAL RETURN RECEIPT</p>
          </div>

          <div class="section">
              <div class="info-row">
                  <div><p>Date:</p><small>${new Date().toLocaleDateString()}</small></div>
                  <div><p>Order No:</p><small>${sale.order_id}</small></div>
              </div>
              <div class="info-row">
                  <div><p>Customer:</p><small>${sale.customer?.name || 'Walk-in'}</small></div>
              </div>
          </div>

          <div class="section">
              <table>
                  <colgroup><col style="width:60%;"><col style="width:15%;"><col style="width:25%;"></colgroup>
                  <thead><tr><th>Items</th><th style="text-align:center;">Qty</th><th style="text-align:right;">Price</th></tr></thead>
                  <tbody style="font-size:11px;">${itemRows}</tbody>
              </table>
          </div>

          <div class="totals">
              <div>
                  <span>Sub Total</span>
                  <span>${Number(sale.total_amount).toFixed(2)} LKR</span>
              </div>
              <div>
                  <span>Rental Period</span>
                  <span>${sale.rental_date_from} to ${sale.rental_date_to}</span>
              </div>
              <div>
                  <span>Advance Paid</span>
                  <span>${Number(sale.advance_amount).toFixed(2)} LKR</span>
              </div>
              <div>
                  <span>Amount After Advance</span>
                  <span>${Number(data.amount_after_advance).toFixed(2)} LKR</span>
              </div>
              ${data.late_days > 0 ? `
              <div>
                  <span>Actual Return Date</span>
                  <span>${new Date().toLocaleDateString()}</span>
              </div>
              <div>
                  <span>Late Days</span>
                  <span>${data.late_days} day(s)</span>
              </div>
              <div>
                  <span>Late Fee (Rs. 200 × ${data.late_days})</span>
                  <span>${Number(data.late_fee).toFixed(2)} LKR</span>
              </div>
              ` : `
              <div>
                  <span>Late Fee</span>
                  <span>None (Returned on time)</span>
              </div>
              `}
              ${Number(data.damage_amount) > 0 ? `
              <div>
                  <span>Damage Deduction</span>
                  <span>${Number(data.damage_amount).toFixed(2)} LKR</span>
              </div>
              ` : ''}
              <div>
                  <span>Deposit Paid</span>
                  <span>${Number(data.deposit).toFixed(2)} LKR</span>
              </div>
              <div>
                  <span>Total Deductions (Late + Damage)</span>
                  <span>${Number(data.total_deductions).toFixed(2)} LKR</span>
              </div>
              <div style="font-weight:bold; font-size:14px;">
                  <span>${Number(data.deposit_refund) >= 0 ? 'Deposit Refund' : 'Customer Must Pay'}</span>
                  <span>${Math.abs(Number(data.deposit_refund)).toFixed(2)} LKR</span>
              </div>
              ${Number(data.customer_deficit) > 0 ? `
              <div style="font-weight:bold; font-size:14px;">
                  <span>Total Amount Paid by Customer</span>
                  <span>${Number(data.total_to_pay).toFixed(2)} LKR</span>
              </div>
              <div>
                  <span>Cash Received</span>
                  <span>${Number(data.cash_received).toFixed(2)} LKR</span>
              </div>
              <div>
                  <span>Payment Method</span>
                  <span style="text-transform: capitalize;">${data.payment_method || 'Cash'}</span>
              </div>
              <div style="font-weight:bold;">
                  <span>Balance</span>
                  <span>${Number(data.balance).toFixed(2)} LKR</span>
              </div>
              ` : ''}
          </div>

          <div class="footer">
              <p>THANK YOU COME AGAIN</p>
              <p style="font-weight: bold;">Powered by JAAN Network Ltd.</p>
              <p>${new Date().toLocaleTimeString()}</p>
              <div style="font-size:9px; font-style:italic; border-top:1px dashed #000; padding-top:6px; margin-top:8px; line-height:1.6; display:flex; flex-direction:column; text-align:left;">
                  <span style="margin:1px 0; display:block;">★ ගිවිසගත් දිනයට පෙර භාරගත් අයිතමය භාර නොදී සිටීමෙන්, අදාළ දිනය ඉක්මවා ඇති එක් දිනක් සඳහා රු. 200 ක මුදලක් අමතරව අය කෙරේ.</span>
                  <span style="margin:1px 0; display:block;">★ භාරගත් අයිතමයට යම් හානියක් සිදුවී ඇත්නම්, ඊට අදාළ අලාභය තැන්පතු මුදලින් අය කරගනු ලැබේ.</span>
                  <span style="margin:1px 0; display:block;">★ අත්තිකාරම් මුදලක් ගෙවා භාරගත් අයිතමය ගනුදෙනුව හදිසියේ අවසන් කර භාරදෙන්නේ නම්, කිසිම හේතුවක් මත අත්තිකාරම් මුදල ආපසු ගෙවනු නොලැබේ.</span>
              </div>
          </div>
      </div>
  </body>
  </html>
  `;

  const printWindow = openedWindow && !openedWindow.closed ? openedWindow : window.open("", "_blank");
  if (!printWindow) {
    alert("Failed to open print window. Please allow pop-ups and try again.");
    return;
  }
  printWindow.document.open();
  printWindow.document.write(receiptHTML);
  printWindow.document.close();
  printWindow.onload = () => {
    // Small delay helps ensure assets (like logo image) are rendered before printing.
    setTimeout(() => {
      printWindow.focus();
      printWindow.print();

      const closeWindow = () => {
        if (!printWindow.closed) {
          printWindow.close();
        }
      };

      printWindow.onafterprint = closeWindow;
      setTimeout(closeWindow, 1500);
    }, 250);
  };
};

// Fetch sales when modal opens
import { watch } from "vue";
watch(() => props.open, (val) => {
  if (val) fetchSales();
});
</script>
