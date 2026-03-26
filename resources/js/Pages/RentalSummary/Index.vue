<style lang="css">
.tab-btn {
  @apply px-8 py-4 text-lg font-bold rounded-xl transition-all duration-300 cursor-pointer;
}
.tab-btn-active {
  @apply text-white shadow-lg transform scale-105;
}
.tab-btn-inactive {
  @apply text-gray-700 bg-gray-200 hover:bg-gray-300;
}
.pagination-disabled {
  color: rgb(37 99 235);
  transition: all 0.5s ease;
  background: rgb(229 231 235 / var(--tw-bg-opacity));
}
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 20px;
  font-size: 14px;
  float: right;
}
.pagination a:first-child,
.pagination a:last-child {
  padding: 8px 16px;
}
</style>
<template>
  <Head title="Rental Summary" />
  <Banner />
  <div class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gray-100 md:px-36 px-16">
    <Header />
    <div class="w-full md:w-5/6 py-12 space-y-8">

      <!-- Page Header -->
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <Link href="/">
            <img src="/images/back-arrow.png" class="w-14 h-14" />
          </Link>
          <p class="text-4xl font-bold tracking-wide text-black uppercase">
            Rental Summary
          </p>
        </div>
        <!-- Search -->
        <div class="flex items-center space-x-3">
          <input v-model="searchQuery" type="text" placeholder="Search by Order ID / Customer..."
            class="px-6 py-3 text-lg border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 w-96" />
          <button @click="performSearch"
            class="px-6 py-3 text-lg font-bold text-white bg-purple-600 rounded-xl hover:bg-purple-700 transition">
            <i class="ri-search-line mr-2"></i> Search
          </button>
        </div>
      </div>

      <!-- Tabs -->
      <div class="flex items-center space-x-4">
        <button @click="activeTab = 'booked'"
          :class="[
            'tab-btn',
            activeTab === 'booked' ? 'tab-btn-active bg-sky-500' : 'tab-btn-inactive'
          ]">
          <i class="ri-bookmark-line mr-2"></i> Booked Items
          <span class="ml-2 px-3 py-1 rounded-full text-sm"
            :class="activeTab === 'booked' ? 'bg-white text-sky-600' : 'bg-gray-300 text-gray-600'">
            {{ bookedItems.total || 0 }}
          </span>
        </button>
        <button @click="activeTab = 'rented'"
          :class="[
            'tab-btn',
            activeTab === 'rented' ? 'tab-btn-active bg-green-600' : 'tab-btn-inactive'
          ]">
          <i class="ri-shopping-bag-3-line mr-2"></i> Rent Items
          <span class="ml-2 px-3 py-1 rounded-full text-sm"
            :class="activeTab === 'rented' ? 'bg-white text-green-600' : 'bg-gray-300 text-gray-600'">
            {{ rentedItems.total || 0 }}
          </span>
        </button>
        <button @click="activeTab = 'returned'"
          :class="[
            'tab-btn',
            activeTab === 'returned' ? 'tab-btn-active bg-purple-600' : 'tab-btn-inactive'
          ]">
          <i class="ri-arrow-go-back-line mr-2"></i> Return Rent Items
          <span class="ml-2 px-3 py-1 rounded-full text-sm"
            :class="activeTab === 'returned' ? 'bg-white text-purple-600' : 'bg-gray-300 text-gray-600'">
            {{ returnedItems.total || 0 }}
          </span>
        </button>
      </div>

      <!-- BOOKED ITEMS TAB -->
      <div v-show="activeTab === 'booked'">
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden border-2 border-sky-200">
          <div class="bg-sky-500 px-6 py-4">
            <h3 class="text-xl font-bold text-white"><i class="ri-bookmark-line mr-2"></i> Booked Items</h3>
            <p class="text-sky-100 text-sm">Items reserved by customers for future rental</p>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-left" v-if="bookedItems.data && bookedItems.data.length > 0">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-4 text-sm font-bold text-gray-700">Booking ID</th>
                  <th class="px-6 py-4 text-sm font-bold text-gray-700">Customer</th>
                  <th class="px-6 py-4 text-sm font-bold text-gray-700">Item</th>
                  <th class="px-6 py-4 text-sm font-bold text-gray-700">Qty</th>
                  <th class="px-6 py-4 text-sm font-bold text-gray-700">Rental Period</th>
                  <th class="px-6 py-4 text-sm font-bold text-gray-700">Advance</th>
                  <th class="px-6 py-4 text-sm font-bold text-gray-700">Total</th>
                  <th class="px-6 py-4 text-sm font-bold text-gray-700">Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="booking in bookedItems.data" :key="booking.id"
                  class="border-b hover:bg-sky-50 transition">
                  <td class="px-6 py-4 text-sm font-mono font-bold text-sky-700">{{ booking.booking_order_id }}</td>
                  <td class="px-6 py-4 text-sm">{{ booking.customer_name }}</td>
                  <td class="px-6 py-4 text-sm">{{ booking.rental_item?.item_name || 'N/A' }}</td>
                  <td class="px-6 py-4 text-sm text-center">{{ booking.quantity }}</td>
                  <td class="px-6 py-4 text-sm">{{ booking.rental_date_from }} to {{ booking.rental_date_to }}</td>
                  <td class="px-6 py-4 text-sm font-bold">{{ Number(booking.advance_amount).toFixed(2) }} LKR</td>
                  <td class="px-6 py-4 text-sm font-bold">{{ Number(booking.total_price).toFixed(2) }} LKR</td>
                  <td class="px-6 py-4">
                    <span class="px-3 py-1 text-xs font-bold text-sky-700 bg-sky-100 rounded-full">Booked</span>
                  </td>
                </tr>
              </tbody>
            </table>
            <div v-else class="text-center py-12">
              <i class="ri-bookmark-line text-6xl text-gray-300"></i>
              <p class="text-gray-500 text-lg mt-4">No booked items found</p>
            </div>
          </div>
          <!-- Pagination -->
          <div v-if="bookedItems.last_page > 1" class="px-6 py-4 bg-gray-50 flex items-center justify-between border-t">
            <span class="text-sm text-gray-600">Page {{ bookedItems.current_page }} of {{ bookedItems.last_page }}</span>
            <div class="flex space-x-2">
              <button @click="navigateTo(bookedItems.prev_page_url)" :disabled="!bookedItems.prev_page_url"
                class="px-4 py-2 text-sm text-white bg-sky-500 rounded-md disabled:bg-gray-300 disabled:cursor-not-allowed">Previous</button>
              <button @click="navigateTo(bookedItems.next_page_url)" :disabled="!bookedItems.next_page_url"
                class="px-4 py-2 text-sm text-white bg-sky-500 rounded-md disabled:bg-gray-300 disabled:cursor-not-allowed">Next</button>
            </div>
          </div>
        </div>
      </div>

      <!-- RENT ITEMS TAB -->
      <div v-show="activeTab === 'rented'">
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden border-2 border-green-200">
          <div class="bg-green-600 px-6 py-4">
            <h3 class="text-xl font-bold text-white"><i class="ri-shopping-bag-3-line mr-2"></i> Rent Items</h3>
            <p class="text-green-100 text-sm">Items currently rented out to customers</p>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-left" v-if="rentedItems.data && rentedItems.data.length > 0">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-4 text-sm font-bold text-gray-700">Order ID</th>
                  <th class="px-6 py-4 text-sm font-bold text-gray-700">Customer</th>
                  <th class="px-6 py-4 text-sm font-bold text-gray-700">Type</th>
                  <th class="px-6 py-4 text-sm font-bold text-gray-700">Items</th>
                  <th class="px-6 py-4 text-sm font-bold text-gray-700">Rental Period</th>
                  <th class="px-6 py-4 text-sm font-bold text-gray-700">Deposit</th>
                  <th class="px-6 py-4 text-sm font-bold text-gray-700">Total</th>
                  <th class="px-6 py-4 text-sm font-bold text-gray-700">Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="sale in rentedItems.data" :key="sale.id"
                  class="border-b hover:bg-green-50 transition">
                  <td class="px-6 py-4 text-sm font-mono font-bold text-green-700">{{ sale.order_id }}</td>
                  <td class="px-6 py-4 text-sm">{{ sale.customer?.name || 'Walk-in' }}</td>
                  <td class="px-6 py-4">
                    <span :class="sale.rental_type === 'rent_now'
                      ? 'px-3 py-1 text-xs font-bold text-green-700 bg-green-100 rounded-full'
                      : 'px-3 py-1 text-xs font-bold text-sky-700 bg-sky-100 rounded-full'">
                      {{ sale.rental_type === 'rent_now' ? 'Rent Now' : 'Rent Later' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-sm">
                    <span v-for="(si, idx) in sale.sale_items" :key="si.id">
                      {{ si.rental_item?.item_name || 'Item' }} (×{{ si.quantity }}){{ idx < sale.sale_items.length - 1 ? ', ' : '' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-sm">{{ sale.rental_date_from }} to {{ sale.rental_date_to }}</td>
                  <td class="px-6 py-4 text-sm font-bold">{{ Number(sale.deposit).toFixed(2) }} LKR</td>
                  <td class="px-6 py-4 text-sm font-bold">{{ Number(sale.total_amount).toFixed(2) }} LKR</td>
                  <td class="px-6 py-4">
                    <span class="px-3 py-1 text-xs font-bold text-orange-700 bg-orange-100 rounded-full">Active</span>
                  </td>
                </tr>
              </tbody>
            </table>
            <div v-else class="text-center py-12">
              <i class="ri-shopping-bag-3-line text-6xl text-gray-300"></i>
              <p class="text-gray-500 text-lg mt-4">No active rentals found</p>
            </div>
          </div>
          <div v-if="rentedItems.last_page > 1" class="px-6 py-4 bg-gray-50 flex items-center justify-between border-t">
            <span class="text-sm text-gray-600">Page {{ rentedItems.current_page }} of {{ rentedItems.last_page }}</span>
            <div class="flex space-x-2">
              <button @click="navigateTo(rentedItems.prev_page_url)" :disabled="!rentedItems.prev_page_url"
                class="px-4 py-2 text-sm text-white bg-green-600 rounded-md disabled:bg-gray-300 disabled:cursor-not-allowed">Previous</button>
              <button @click="navigateTo(rentedItems.next_page_url)" :disabled="!rentedItems.next_page_url"
                class="px-4 py-2 text-sm text-white bg-green-600 rounded-md disabled:bg-gray-300 disabled:cursor-not-allowed">Next</button>
            </div>
          </div>
        </div>
      </div>

      <!-- RETURN RENT ITEMS TAB -->
      <div v-show="activeTab === 'returned'">
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden border-2 border-purple-200">
          <div class="bg-purple-600 px-6 py-4">
            <h3 class="text-xl font-bold text-white"><i class="ri-arrow-go-back-line mr-2"></i> Return Rent Items</h3>
            <p class="text-purple-100 text-sm">Items that have been returned by customers</p>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-left" v-if="returnedItems.data && returnedItems.data.length > 0">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-4 text-sm font-bold text-gray-700">Order ID</th>
                  <th class="px-6 py-4 text-sm font-bold text-gray-700">Customer</th>
                  <th class="px-6 py-4 text-sm font-bold text-gray-700">Type</th>
                  <th class="px-6 py-4 text-sm font-bold text-gray-700">Items</th>
                  <th class="px-6 py-4 text-sm font-bold text-gray-700">Rental Period</th>
                  <th class="px-6 py-4 text-sm font-bold text-gray-700">Deposit</th>
                  <th class="px-6 py-4 text-sm font-bold text-gray-700">Late Fee</th>
                  <th class="px-6 py-4 text-sm font-bold text-gray-700">Damage Fee</th>
                  <th class="px-6 py-4 text-sm font-bold text-gray-700">Deposit Refund</th>
                  <th class="px-6 py-4 text-sm font-bold text-gray-700">Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="sale in returnedItems.data" :key="sale.id"
                  class="border-b hover:bg-purple-50 transition">
                  <td class="px-6 py-4 text-sm font-mono font-bold text-purple-700">{{ sale.order_id }}</td>
                  <td class="px-6 py-4 text-sm">{{ sale.customer?.name || 'Walk-in' }}</td>
                  <td class="px-6 py-4">
                    <span :class="sale.rental_type === 'rent_now'
                      ? 'px-3 py-1 text-xs font-bold text-green-700 bg-green-100 rounded-full'
                      : 'px-3 py-1 text-xs font-bold text-sky-700 bg-sky-100 rounded-full'">
                      {{ sale.rental_type === 'rent_now' ? 'Rent Now' : 'Rent Later' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-sm">
                    <span v-for="(si, idx) in sale.sale_items" :key="si.id">
                      {{ si.rental_item?.item_name || 'Item' }} (×{{ si.quantity }}){{ idx < sale.sale_items.length - 1 ? ', ' : '' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-sm">{{ sale.rental_date_from }} to {{ sale.rental_date_to }}</td>
                  <td class="px-6 py-4 text-sm font-bold">{{ Number(sale.deposit).toFixed(2) }} LKR</td>
                  <td class="px-6 py-4 text-sm">
                    <span v-if="Number(sale.late_fee) > 0" class="text-red-600 font-bold">
                      {{ Number(sale.late_fee).toFixed(2) }} LKR
                      <span class="block text-xs text-red-400">({{ sale.late_days }} day{{ sale.late_days > 1 ? 's' : '' }})</span>
                    </span>
                    <span v-else class="text-green-600 font-bold">None</span>
                  </td>
                  <td class="px-6 py-4 text-sm">
                    <span v-if="Number(sale.damage_amount) > 0" class="text-red-600 font-bold">
                      {{ Number(sale.damage_amount).toFixed(2) }} LKR
                    </span>
                    <span v-else class="text-green-600 font-bold">None</span>
                  </td>
                  <td class="px-6 py-4 text-sm font-bold">
                    <span v-if="Number(sale.deposit_refund) >= 0" class="text-green-700">
                      {{ Number(sale.deposit_refund).toFixed(2) }} LKR
                    </span>
                    <span v-else class="text-red-700">
                      -{{ Math.abs(Number(sale.deposit_refund)).toFixed(2) }} LKR
                      <span class="block text-xs text-red-400">(Deficit)</span>
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <span class="px-3 py-1 text-xs font-bold text-purple-700 bg-purple-100 rounded-full">Returned</span>
                  </td>
                </tr>
              </tbody>
            </table>
            <div v-else class="text-center py-12">
              <i class="ri-arrow-go-back-line text-6xl text-gray-300"></i>
              <p class="text-gray-500 text-lg mt-4">No returned items found</p>
            </div>
          </div>
          <div v-if="returnedItems.last_page > 1" class="px-6 py-4 bg-gray-50 flex items-center justify-between border-t">
            <span class="text-sm text-gray-600">Page {{ returnedItems.current_page }} of {{ returnedItems.last_page }}</span>
            <div class="flex space-x-2">
              <button @click="navigateTo(returnedItems.prev_page_url)" :disabled="!returnedItems.prev_page_url"
                class="px-4 py-2 text-sm text-white bg-purple-600 rounded-md disabled:bg-gray-300 disabled:cursor-not-allowed">Previous</button>
              <button @click="navigateTo(returnedItems.next_page_url)" :disabled="!returnedItems.next_page_url"
                class="px-4 py-2 text-sm text-white bg-purple-600 rounded-md disabled:bg-gray-300 disabled:cursor-not-allowed">Next</button>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
  <Footer />
</template>

<script setup>
import { ref } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import Banner from "@/Components/Banner.vue";

const activeTab = ref("booked");
const searchQuery = ref("");

const props = defineProps({
  bookedItems: Object,
  rentedItems: Object,
  returnedItems: Object,
  search: String,
});

// Initialize search from props
searchQuery.value = props.search || "";

const performSearch = () => {
  router.get("/rental-summary", { search: searchQuery.value }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const navigateTo = (url) => {
  if (!url) return;
  router.visit(url, {
    preserveState: true,
    preserveScroll: true,
  });
};
</script>
