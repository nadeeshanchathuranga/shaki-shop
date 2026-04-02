<template>
  <TransitionRoot as="template" :show="open">
    <Dialog class="relative z-10" @close="$emit('update:open', false)">
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
        <div
          class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
        />
      </TransitionChild>
      <!-- Modal Content -->
      <div class="fixed inset-0 z-10 flex items-center justify-center overflow-y-auto">
        <TransitionChild
          as="template"
          enter="ease-out duration-300"
          enter-from="opacity-0 scale-95"
          enter-to="opacity-100 scale-100"
          leave="ease-in duration-200"
          leave-from="opacity-100 scale-100"
          leave-to="opacity-0 scale-95"
        >
          <DialogPanel
            class="bg-black border-4 border-blue-600 rounded-[20px] shadow-xl w-5/6 lg:w-3/6 p-10 text-center max-h-[90vh] overflow-y-auto"
          >
            <DialogTitle class="text-xl font-bold text-white"
              >Add Rental Item</DialogTitle
            >

            <div class="mt-4 mb-6">
              <button
                type="button"
                @click="isQuickSupplierOpen = true"
                class="px-6 py-2 text-white bg-orange-600 rounded hover:bg-orange-700 text-sm font-medium"
              >
                Add New Supplier
              </button>
            </div>

            <form @submit.prevent="submit" enctype="multipart/form-data">
              <!-- Modal Form -->
              <div class="mt-6 space-y-4 text-left">
                <!-- Row: Category & Item Name -->
                <div class="flex items-center gap-8">
                  <div class="w-full">
                    <label class="block text-sm font-medium text-gray-300">Category Name:</label>
                    <select
                      required
                      v-model="form.category_id"
                      class="w-full px-4 py-2 mt-2 text-black bg-white rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    >
                      <option value="">Select a Category</option>
                      <option v-for="category in categories" :key="category.id" :value="category.id">
                        {{
                          category.hierarchy_string
                            ? category.hierarchy_string + " ----> " + category.name
                            : category.name
                        }}
                      </option>
                    </select>
                    <span v-if="form.errors.category_id" class="mt-2 text-red-500">{{
                      form.errors.category_id
                    }}</span>
                  </div>
                  <div class="w-full">
                    <label class="block text-sm font-medium text-gray-300">Item Name:</label>
                    <input
                      v-model="form.item_name"
                      type="text"
                      required
                      placeholder="Enter Item Name"
                      class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    />
                    <span v-if="form.errors.item_name" class="mt-2 text-red-500">{{
                      form.errors.item_name
                    }}</span>
                  </div>
                </div>

                <!-- Row: Color & Size -->
                <div class="flex items-center gap-8">
                  <div class="w-full">
                    <label class="block text-sm font-medium text-gray-300">Color:</label>
                    <select
                      v-model="form.color_id"
                      class="w-full px-4 py-2 mt-2 text-black bg-white rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    >
                      <option value="">Select a Color</option>
                      <option v-for="color in colors" :key="color.id" :value="color.id">
                        {{ color.name }}
                      </option>
                    </select>
                    <span v-if="form.errors.color_id" class="mt-2 text-red-500">{{
                      form.errors.color_id
                    }}</span>
                  </div>
                  <div class="w-full">
                    <label class="block text-sm font-medium text-gray-300">Size:</label>
                    <input
                      v-model="form.size"
                      type="text"
                      placeholder="Enter Size"
                      class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    />
                    <span v-if="form.errors.size" class="mt-2 text-red-500">{{
                      form.errors.size
                    }}</span>
                  </div>
                </div>

                <!-- Row: Supplier Name & Rental Quantity -->
                <div class="flex items-center gap-8">
                  <div class="w-full">
                    <label class="block text-sm font-medium text-gray-300">Supplier Name:</label>
                    <select
                      v-model="form.supplier_id"
                      required
                      class="w-full px-4 py-2 mt-2 text-black bg-white rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    >
                      <option value="">Select a Supplier</option>
                      <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                        {{ supplier.name }}
                      </option>
                    </select>
                    <span v-if="form.errors.supplier_id" class="mt-2 text-red-500">{{
                      form.errors.supplier_id
                    }}</span>
                  </div>
                  <div class="w-full">
                    <label class="block text-sm font-medium text-gray-300">Rental Quantity:</label>
                    <input
                      v-model="form.rental_quantity"
                      type="number"
                      required
                      min="1"
                      placeholder="Enter Quantity"
                      class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    />
                    <span v-if="form.errors.rental_quantity" class="mt-2 text-red-500">{{
                      form.errors.rental_quantity
                    }}</span>
                  </div>
                </div>

                <!-- Row: Rent Price -->
                <div class="flex items-center gap-8">
                  <div class="w-full">
                    <label class="block text-sm font-medium text-gray-300"
                      >Rent Price:</label
                    >
                    <input
                      v-model="form.rent_price"
                      type="number"
                      step="0.01"
                      required
                      min="0"
                      placeholder="Enter Rent Price"
                      class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    />
                    <span v-if="form.errors.rent_price" class="mt-2 text-red-500">{{
                      form.errors.rent_price
                    }}</span>
                  </div>
                </div>

                <!-- Row: Commission % Shop & Commission % Supplier -->
                <div class="flex items-center gap-8">
                  <div class="w-full">
                    <div class="flex items-center justify-between mb-2">
                      <label class="block text-sm font-medium text-gray-300"
                        >Commission for Shop per item:</label
                      >
                      <div class="flex gap-2">
                        <button
                          type="button"
                          @click="updateCommissionTypeShop('fixed')"
                          :class="[
                            'px-3 py-1 text-xs font-semibold rounded',
                            commissionTypeShop === 'fixed'
                              ? 'bg-blue-600 text-white'
                              : 'bg-gray-600 text-gray-300 hover:bg-gray-500'
                          ]"
                        >
                          Rs
                        </button>
                        <button
                          type="button"
                          @click="updateCommissionTypeShop('percentage')"
                          :class="[
                            'px-3 py-1 text-xs font-semibold rounded',
                            commissionTypeShop === 'percentage'
                              ? 'bg-blue-600 text-white'
                              : 'bg-gray-600 text-gray-300 hover:bg-gray-500'
                          ]"
                        >
                          %
                        </button>
                      </div>
                    </div>
                    <input
                      v-model="form.commission_percentage_shop"
                      type="number"
                      step="0.01"
                      required
                      min="0"
                      :max="commissionTypeShop === 'percentage' ? '100' : undefined"
                      :placeholder="commissionTypeShop === 'percentage' ? 'Enter Shop Commission %' : 'Enter Fixed Amount (Rs)'"
                      class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    />
                    <span v-if="form.errors.commission_percentage_shop" class="mt-2 text-red-500">{{
                      form.errors.commission_percentage_shop
                    }}</span>
                    <!-- Calculated commission amount -->
                    <p
                      v-if="shopCommissionAmount !== null"
                      class="mt-2 text-sm font-semibold text-green-400"
                    >
                      Final Amount: Rs {{ shopCommissionAmount }}
                    </p>
                  </div>
                  <div class="w-full">
                    <div class="flex items-center justify-between mb-2">
                      <label class="block text-sm font-medium text-gray-300"
                        >Commission for Supplier per item:</label
                      >
                      <div class="flex gap-2">
                        <button
                          type="button"
                          @click="updateCommissionTypeSupplier('fixed')"
                          :class="[
                            'px-3 py-1 text-xs font-semibold rounded',
                            commissionTypeSupplier === 'fixed'
                              ? 'bg-blue-600 text-white'
                              : 'bg-gray-600 text-gray-300 hover:bg-gray-500'
                          ]"
                        >
                          Rs
                        </button>
                        <button
                          type="button"
                          @click="updateCommissionTypeSupplier('percentage')"
                          :class="[
                            'px-3 py-1 text-xs font-semibold rounded',
                            commissionTypeSupplier === 'percentage'
                              ? 'bg-blue-600 text-white'
                              : 'bg-gray-600 text-gray-300 hover:bg-gray-500'
                          ]"
                        >
                          %
                        </button>
                      </div>
                    </div>
                    <input
                      v-model="form.commission_percentage_supplier"
                      type="number"
                      step="0.01"
                      required
                      min="0"
                      :max="commissionTypeSupplier === 'percentage' ? '100' : undefined"
                      :placeholder="commissionTypeSupplier === 'percentage' ? 'Enter Supplier Commission %' : 'Enter Fixed Amount (Rs)'"
                      class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    />
                    <span v-if="form.errors.commission_percentage_supplier" class="mt-2 text-red-500">{{
                      form.errors.commission_percentage_supplier
                    }}</span>
                    <!-- Calculated commission amount -->
                    <p
                      v-if="supplierCommissionAmount !== null"
                      class="mt-2 text-sm font-semibold text-green-400"
                    >
                      Final Amount: Rs {{ supplierCommissionAmount }}
                    </p>
                  </div>
                </div>

                <!-- Row: Image -->
                <div class="flex items-center gap-8">
                  <div class="w-full">
                    <label class="block text-sm font-medium text-gray-300"
                      >Image:</label
                    >
                    <input
                      type="file"
                      @change="handleImageUpload"
                      class="w-full px-4 py-2 mt-2 text-white bg-gray-800 rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    />
                    <span v-if="form.errors.image" class="mt-2 text-red-500">
                      {{ form.errors.image }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Modal Buttons -->
              <div class="mt-6 space-x-4">
                <button
                  class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700"
                  type="submit"
                >
                  Save
                </button>
                <button
                  class="px-4 py-2 text-gray-700 bg-gray-300 rounded hover:bg-gray-400"
                  @click="
                    () => {
                      emit('update:open', false);
                    }
                  "
                  type="button"
                >
                  Cancel
                </button>
              </div>
            </form>
          </DialogPanel>
        </TransitionChild>
      </div>
    </Dialog>
  </TransitionRoot>

  <!-- Quick Supplier Creation Modal -->
  <QuickSupplierCreateModel
    :open="isQuickSupplierOpen"
    @update:open="isQuickSupplierOpen = $event"
    @supplier-created="handleSupplierCreated"
  />
</template>

<script setup>
import {
  Dialog,
  DialogPanel,
  DialogTitle,
  TransitionChild,
  TransitionRoot,
} from "@headlessui/vue";
import { computed, ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import QuickSupplierCreateModel from "@/Components/custom/QuickSupplierCreateModel.vue";

const emit = defineEmits(["update:open", "success"]);
const isQuickSupplierOpen = ref(false);

// Define props
const { open, categories, colors, suppliers } = defineProps({
  open: {
    type: Boolean,
    required: true,
  },
  categories: {
    type: Array,
    required: true,
  },
  colors: {
    type: Array,
    required: true,
  },
  suppliers: {
    type: Array,
    required: true,
  },
});

const form = useForm({
  category_id: "",
  item_name: "",
  color_id: "",
  size: "",
  supplier_id: "",
  rental_quantity: null,
  rent_price: null,
  commission_percentage_shop: null,
  commission_percentage_supplier: null,
  commission_type_shop: "fixed", // 'percentage' or 'fixed'
  commission_type_supplier: "fixed", // 'percentage' or 'fixed'
  image: null,
});

const commissionTypeShop = ref("fixed"); // Default to fixed amount
const commissionTypeSupplier = ref("fixed"); // Default to fixed amount


// Computed commission amounts
const shopCommissionAmount = computed(() => {
  if (commissionTypeShop.value === "percentage") {
    if (form.rent_price && form.commission_percentage_shop) {
      return (
        (parseFloat(form.rent_price) *
          parseFloat(form.commission_percentage_shop)) /
        100
      ).toFixed(2);
    }
  } else {
    // Fixed amount
    return form.commission_percentage_shop ? parseFloat(form.commission_percentage_shop).toFixed(2) : null;
  }
  return null;
});

const supplierCommissionAmount = computed(() => {
  if (commissionTypeSupplier.value === "percentage") {
    if (form.rent_price && form.commission_percentage_supplier) {
      return (
        (parseFloat(form.rent_price) *
          parseFloat(form.commission_percentage_supplier)) /
        100
      ).toFixed(2);
    }
  } else {
    // Fixed amount
    return form.commission_percentage_supplier ? parseFloat(form.commission_percentage_supplier).toFixed(2) : null;
  }
  return null;
});

const updateCommissionTypeShop = (type) => {
  commissionTypeShop.value = type;
  form.commission_type_shop = type;
};

const updateCommissionTypeSupplier = (type) => {
  commissionTypeSupplier.value = type;
  form.commission_type_supplier = type;
};

const isSubmitting = ref(false);

const handleImageUpload = (event) => {
  form.image = event.target.files[0];
};

const submit = () => {
  if (isSubmitting.value) return; // Prevent double submission
  isSubmitting.value = true;
  
  form.post("/rental-items", {
    preserveScroll: false, // Allow scroll to top on redirect
    preserveState: false,  // Ensure fresh page state
    onSuccess: () => {
      // Reset form and close modal on successful redirect
      form.reset();
      emit("success", "Rental item created successfully!");
      emit("update:open", false);
      isSubmitting.value = false;
    },
    onError: (errors) => {
      console.error("Form submission failed:", errors);
      isSubmitting.value = false;
    },
  });
};

const handleSupplierCreated = (newSupplier) => {
  // If a new supplier was created, add it to the list
  if (newSupplier && newSupplier.id) {
    // Push the new supplier to the list if not already present
    const supplierExists = suppliers.some(s => s.id === newSupplier.id);
    if (!supplierExists) {
      suppliers.push(newSupplier);
    }
    // Auto-select the new supplier
    form.supplier_id = newSupplier.id;
  }
};
</script>
