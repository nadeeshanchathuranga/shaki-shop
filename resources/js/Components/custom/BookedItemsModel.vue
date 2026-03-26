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
            class="bg-white border-1 border-gray-600 rounded-[20px] shadow-xl w-5/6 max-w-5xl p-6 text-center h-[90vh] overflow-hidden flex flex-col">

            <h2 class="text-3xl font-bold text-black mb-4">Booked Rental Items</h2>

            <!-- Search -->
            <div class="flex items-center space-x-4 mb-4 flex-shrink-0">
              <input v-model="searchQuery" @input="debouncedSearch" type="text"
                placeholder="Search by Booking Order ID..."
                class="w-full px-6 py-3 text-lg border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500" />
            </div>

            <!-- Content -->
            <div class="flex-grow overflow-y-auto">
              <template v-if="loading">
                <p class="text-xl text-blue-500 py-8">Loading booked items...</p>
              </template>
              <template v-else-if="bookings.data && bookings.data.length > 0">
                <table class="w-full text-left">
                  <thead class="bg-gray-100 sticky top-0">
                    <tr>
                      <th class="px-4 py-3 text-sm font-bold text-gray-700">Booking ID</th>
                      <th class="px-4 py-3 text-sm font-bold text-gray-700">Customer</th>
                      <th class="px-4 py-3 text-sm font-bold text-gray-700">Item</th>
                      <th class="px-4 py-3 text-sm font-bold text-gray-700">Qty</th>
                      <th class="px-4 py-3 text-sm font-bold text-gray-700">Rental Period</th>
                      <th class="px-4 py-3 text-sm font-bold text-gray-700">Advance</th>
                      <th class="px-4 py-3 text-sm font-bold text-gray-700">Total</th>
                      <th class="px-4 py-3 text-sm font-bold text-gray-700">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="booking in bookings.data" :key="booking.id"
                      class="border-b hover:bg-gray-50 transition cursor-pointer"
                      :class="{ 'bg-sky-50 border-sky-300': isSelected(booking) }">
                      <td class="px-4 py-3 text-sm font-mono font-bold">{{ booking.booking_order_id }}</td>
                      <td class="px-4 py-3 text-sm">{{ booking.customer_name }}</td>
                      <td class="px-4 py-3 text-sm">{{ booking.rental_item?.item_name || 'N/A' }}</td>
                      <td class="px-4 py-3 text-sm text-center">{{ booking.quantity }}</td>
                      <td class="px-4 py-3 text-sm">{{ booking.rental_date_from }} to {{ booking.rental_date_to }}</td>
                      <td class="px-4 py-3 text-sm">{{ Number(booking.advance_amount).toFixed(2) }} LKR</td>
                      <td class="px-4 py-3 text-sm">{{ Number(booking.total_price).toFixed(2) }} LKR</td>
                      <td class="px-4 py-3">
                        <button @click="toggleSelectBooking(booking)"
                          :class="isSelected(booking)
                            ? 'px-4 py-2 text-sm font-bold text-white bg-red-500 rounded-lg hover:bg-red-600 transition'
                            : 'px-4 py-2 text-sm font-bold text-white bg-sky-500 rounded-lg hover:bg-sky-600 transition'">
                          {{ isSelected(booking) ? 'Deselect' : 'Select' }}
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>

                <!-- Pagination -->
                <div class="flex items-center justify-between mt-4 px-2">
                  <button @click="fetchPage(bookings.prev_page_url)" :disabled="!bookings.prev_page_url"
                    class="px-4 py-2 text-sm text-white bg-blue-500 rounded-md disabled:bg-gray-300 disabled:cursor-not-allowed">
                    Previous
                  </button>
                  <span class="text-sm text-gray-600">Page {{ bookings.current_page }} of {{ bookings.last_page
                    }}</span>
                  <button @click="fetchPage(bookings.next_page_url)" :disabled="!bookings.next_page_url"
                    class="px-4 py-2 text-sm text-white bg-blue-500 rounded-md disabled:bg-gray-300 disabled:cursor-not-allowed">
                    Next
                  </button>
                </div>
              </template>
              <template v-else>
                <p class="text-xl text-red-500 py-8">No booked items found.</p>
              </template>
            </div>

            <!-- Footer -->
            <div class="flex-shrink-0 flex items-center justify-between mt-4 pt-4 border-t">
              <span class="text-lg font-bold text-gray-700 bg-sky-100 px-4 py-2 rounded-lg border border-sky-200">
                {{ selectedBookings.length }} Selected
              </span>
              <div class="flex items-center space-x-4">
                <button
                  class="px-8 py-3 text-lg font-bold text-white bg-sky-500 rounded-lg shadow-lg hover:bg-sky-600 transition"
                  @click.prevent="importToPos"
                  :disabled="selectedBookings.length === 0"
                  :class="{ 'opacity-50 cursor-not-allowed': selectedBookings.length === 0 }">
                  Import to POS
                </button>
                <button @click="closeModal"
                  class="px-6 py-3 text-lg font-bold text-white bg-red-600 rounded-lg hover:bg-red-700 transition">
                  Close
                </button>
              </div>
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
import { ref, watch } from "vue";
import axios from "axios";
import { debounce } from "lodash";

const bookings = ref({});
const loading = ref(false);
const searchQuery = ref("");
const selectedBookings = ref([]);

const emit = defineEmits(["update:open", "import-booked-item"]);

const props = defineProps({
  open: { type: Boolean, required: true },
});

const isSelected = (booking) => {
  return selectedBookings.value.some((b) => b.id === booking.id);
};

const toggleSelectBooking = (booking) => {
  const index = selectedBookings.value.findIndex((b) => b.id === booking.id);
  if (index === -1) {
    selectedBookings.value.push(booking);
  } else {
    selectedBookings.value.splice(index, 1);
  }
};

const closeModal = () => {
  selectedBookings.value = [];
  searchQuery.value = "";
  emit("update:open", false);
};

const importToPos = () => {
  if (selectedBookings.value.length > 0) {
    emit("import-booked-item", [...selectedBookings.value]);
    selectedBookings.value = [];
    emit("update:open", false);
  }
};

const fetchBookings = async (url = "/api/booked-items") => {
  loading.value = true;
  try {
    const response = await axios.post(url, { search: searchQuery.value });
    bookings.value = response.data.bookings;
  } catch (error) {
    console.error("Error fetching booked items:", error);
  } finally {
    loading.value = false;
  }
};

const debouncedSearch = debounce(() => fetchBookings(), 300);

const fetchPage = (url) => {
  if (url) fetchBookings(url);
};

// Fetch when modal opens
watch(() => props.open, (val) => {
  if (val) fetchBookings();
});
</script>
