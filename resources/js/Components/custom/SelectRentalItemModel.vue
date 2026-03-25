<template>
  <TransitionRoot as="template" :show="open">
    <Dialog class="relative z-10" @close="closeModal">
      <!-- Modal Overlay -->
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" />
      </TransitionChild>

      <!-- Modal Content -->
      <div class="fixed inset-0 z-10 flex items-center justify-center">
        <TransitionChild
          as="template"
          enter="ease-out duration-300"
          enter-from="opacity-0 scale-95"
          enter-to="opacity-100 scale-100"
          leave="ease-in duration-200"
          leave-from="opacity-100 scale-100"
          leave-to="opacity-0 scale-95"
        >
          <DialogPanel class="bg-white border-1 border-gray-600 rounded-[20px] shadow-xl w-5/6 p-6 text-center h-[90vh] overflow-hidden flex flex-col">
            <div class="flex flex-col items-center justify-start flex-shrink-0">
              <div class="w-full flex">
                <div class="w-full py-4 space-y-4">
                  <div class="flex items-center space-x-4 justify-start">
                    <div class="w-full">
                      <!-- Search Input -->
                      <input
                        v-model="search"
                        @input="debounceFetch"
                        type="text"
                        placeholder="Search by customer name or size..."
                        class="w-full custom-input px-6 py-4 text-xl border rounded-lg focus:outline-none focus:ring focus:border-blue-500"
                      />
                    </div>
                  </div>
                </div>
              </div>

              <div class="flex flex-wrap w-full gap-4 pb-4 border-b">
                <select
                  v-model="selectedCategory"
                  @change="() => fetchItems()"
                  class="px-6 py-3 text-lg font-normal tracking-wider text-blue-600 bg-white border rounded-lg cursor-pointer"
                >
                  <option value="">Filter by Category</option>
                  <option
                    v-for="category in allcategories"
                    :key="category.id"
                    :value="category.id"
                  >
                    {{ category.hierarchy_string ? category.hierarchy_string + " ----> " + category.name : category.name }}
                  </option>
                </select>

                <select
                  v-model="stockStatus"
                  @change="() => fetchItems()"
                  class="px-6 py-3 text-lg font-normal tracking-wider text-blue-600 bg-white border rounded-lg cursor-pointer"
                >
                  <option value="">Filter by Stock</option>
                  <option value="in">Available</option>
                  <option value="out">Rented Out</option>
                </select>
                <select
                  v-model="sort"
                  @change="() => fetchItems()"
                  class="px-6 py-3 text-lg font-normal tracking-wider text-blue-600 bg-white border rounded-lg cursor-pointer"
                >
                  <option value="">Filter by Rent Price</option>
                  <option value="asc">Low to High</option>
                  <option value="desc">High to Low</option>
                </select>

                <select
                  v-model="color"
                  @change="() => fetchItems()"
                  class="px-6 py-3 text-lg font-normal tracking-wider text-blue-600 bg-white border rounded-lg cursor-pointer"
                >
                  <option value="">Filter by Color</option>
                  <option v-for="colorOption in colors" :key="colorOption.id" :value="colorOption.name">
                    {{ colorOption.name }}
                  </option>
                </select>
                
                <span
                  @click="resetFilters"
                  class="px-6 py-3 text-lg font-normal tracking-wider text-white bg-blue-600 rounded-lg cursor-pointer"
                >
                  Reset
                </span>
              </div>
            </div>

            <!-- Scrollable Content Area -->
            <div class="flex-grow overflow-y-auto mt-4 px-2">
              <template v-if="loading">
                <div class="flex items-center justify-center h-full">
                  <p class="text-xl text-blue-500">Loading rental items...</p>
                </div>
              </template>
              <template v-else>
                <template v-if="items.data && items.data.length > 0">
                  <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    <div
                      v-for="item in items.data"
                      :key="item.id"
                      @click="item.rental_quantity > 0 && toggleSelectItem(item)"
                      :class="[
                        'space-y-4 text-white transition-transform duration-300 transform bg-black border-4 shadow-lg rounded-xl overflow-hidden',
                        item.rental_quantity > 0
                          ? isSelected(item)
                            ? 'border-purple-600 hover:-translate-y-2 cursor-pointer scale-[1.02]'
                            : 'border-black hover:-translate-y-2 cursor-pointer'
                          : 'border-red-600 cursor-not-allowed opacity-75',
                      ]"
                    >
                      <div class="relative">
                        <img
                          :src="item.image ? `/${item.image}` : '/images/placeholder.jpg'"
                          alt="Rental Item Image"
                          class="object-cover w-full h-48"
                        />
                        <div v-if="isSelected(item)" class="absolute top-2 right-2 bg-purple-600 rounded-full p-1">
                          <svg xmlns="http://www.w3.org/w0000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                          </svg>
                        </div>
                      </div>
                      <div class="px-4 py-3 space-y-2">
                        <div class="flex items-start justify-between text-sm font-bold tracking-wide">
                          <p class="text-justify line-clamp-1" :title="item.item_name">{{ item.item_name || "N/A" }}</p>
                          <p class="px-2 py-1 text-white bg-green-700 rounded-full flex-shrink-0">
                            {{ item.rent_price || "0.00" }} LKR
                          </p>
                        </div>
                        <div class="flex justify-between text-xs text-gray-400">
                          <div class="flex space-x-1">
                            <p class="font-bold text-gray-300">Category:</p>
                            <p>{{ item.category?.name || "N/A" }}</p>
                          </div>
                          <div class="flex space-x-1">
                            <p class="font-bold text-gray-300">Color:</p>
                            <p>{{ item.color?.name || "N/A" }}</p>
                          </div>
                        </div>
                        <div class="flex justify-between text-xs text-gray-400">
                          <div class="flex space-x-1">
                            <p class="font-bold text-gray-300">Supplier:</p>
                            <p class="line-clamp-1">{{ item.supplier?.name || "N/A" }}</p>
                          </div>
                          <div class="flex space-x-1">
                            <p class="font-bold text-gray-300">Size:</p>
                            <p>{{ item.size || "N/A" }}</p>
                          </div>
                        </div>
                        <div class="flex flex-col pt-2 border-t border-gray-700 text-xs">
                           <div class="flex justify-between">
                              <span class="text-gray-400">Shop Comm (-{{item.commission_percentage_shop}}%):</span>
                              <span class="text-green-400">{{item.commission_amount_shop}} LKR</span>
                           </div>
                           <div class="flex justify-between">
                              <span class="text-gray-400">Supplier Comm (-{{item.commission_percentage_supplier}}%):</span>
                              <span class="text-green-400">{{item.commission_amount_supplier}} LKR</span>
                           </div>
                        </div>
                        <div class="flex items-center justify-between pt-2">
                          <p v-if="item.rental_quantity > 0" class="text-sm font-bold tracking-wider text-green-500">
                            <i class="ri-checkbox-blank-circle-fill"></i> Available ({{ item.rental_quantity }})
                          </p>
                          <p v-else class="text-sm font-bold tracking-wider text-red-500">
                            <i class="ri-checkbox-blank-circle-fill"></i> Rented Out
                          </p>
                        </div>
                      </div>
                    </div>
                  </div>
                </template>
                <template v-else>
                  <div class="flex items-center justify-center h-full text-gray-500">
                    <p class="text-xl text-red-500">No Rental Items available</p>
                  </div>
                </template>
              </template>
            </div>

            <div class="flex-shrink-0 flex items-center justify-between mt-4 pt-4 border-t">
              <div class="pagination flex space-x-2">
                <button
                  @click="fetchPage(items.prev_page_url)"
                  :disabled="!items.prev_page_url || loading"
                  class="px-4 py-2 text-sm text-white bg-blue-500 rounded-md shadow-md hover:bg-blue-600 disabled:bg-gray-300 disabled:cursor-not-allowed"
                >
                  Previous
                </button>
                <div class="px-4 py-2 text-sm text-gray-700 font-bold hidden sm:block">
                  <span v-if="items.total > 0">Page {{ items.current_page }} of {{ items.last_page }}</span>
                </div>
                <button
                  @click="fetchPage(items.next_page_url)"
                  :disabled="!items.next_page_url || loading"
                  class="px-4 py-2 text-sm text-white bg-blue-500 rounded-md shadow-md hover:bg-blue-600 disabled:bg-gray-300 disabled:cursor-not-allowed"
                >
                  Next
                </button>
              </div>
              <div class="flex items-center space-x-4">
                <span class="text-lg font-bold text-gray-700 bg-purple-100 px-4 py-2 rounded-lg border border-purple-200">
                  {{ selectedItems.length }} Selected
                </span>
                <button
                  class="px-8 py-3 text-lg font-bold text-white bg-purple-600 rounded-lg shadow-lg hover:bg-purple-700 transition"
                  @click.prevent="closeModal(true)"
                  :disabled="selectedItems.length === 0"
                  :class="{'opacity-50 cursor-not-allowed': selectedItems.length === 0}"
                >
                  Import to POS
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
import { ref, onMounted } from "vue";
import axios from "axios";
import { debounce } from "lodash";

const items = ref({});
const loading = ref(false);
const search = ref("");
const selectedCategory = ref("");
const stockStatus = ref("");
const sort = ref("");
const color = ref("");

const selectedItems = ref([]);

const isSelected = (item) => {
  return selectedItems.value.some((p) => p.id === item.id);
};

const toggleSelectItem = (item) => {
  const index = selectedItems.value.findIndex((p) => p.id === item.id);
  if (index === -1) {
    selectedItems.value.push(item);
  } else {
    selectedItems.value.splice(index, 1);
  }
};

const resetFilters = () => {
  search.value = "";
  selectedCategory.value = "";
  stockStatus.value = "";
  sort.value = "";
  color.value = "";
  fetchItems();
};

const playClickSound = () => {
  try {
    const clickSound = new Audio("/sounds/click-sound.mp3");
    clickSound.play().catch(e => console.log('Audio play prevented by browser policy'));
  } catch (e) {
    // Ignore audio errors
  }
};

const emit = defineEmits(["update:open", "selected-rental-items"]);

const { open, allcategories, colors } = defineProps({
  open: {
    type: Boolean,
    required: true,
  },
  allcategories: Array,
  colors: Array,
});

const closeModal = (triggerImport = false) => {
  if (triggerImport && selectedItems.value.length > 0) {
    playClickSound();
    emit("selected-rental-items", [...selectedItems.value]);
    selectedItems.value = []; // Clear selection after successful import
  }
  emit("update:open", false);
};

const fetchItems = async (url = "/api/rental-items") => {
  loading.value = true;
  try {
    const response = await axios.post(url, {
      search: search.value,
      selectedCategory: selectedCategory.value,
      stockStatus: stockStatus.value,
      sort: sort.value,
      color: color.value,
    });
    items.value = response.data.products; // Using 'products' key as per API response
  } catch (error) {
    console.error("Error fetching rental items:", error);
  } finally {
    loading.value = false;
  }
};

// Debounce search input to avoid overwhelming the server
const debounceFetch = debounce(() => {
  fetchItems();
}, 300);

const fetchPage = (url) => {
  if (url) {
    fetchItems(url);
  }
};

// Fetch items when component mounts
onMounted(() => {
  fetchItems();
});
</script>
