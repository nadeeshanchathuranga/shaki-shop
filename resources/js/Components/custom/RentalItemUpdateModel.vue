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
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" />
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
            <DialogTitle class="text-xl font-bold text-white">
              Edit Rental Item
            </DialogTitle>

            <form @submit.prevent="submit" enctype="multipart/form-data">
              <!-- Modal Form -->
              <div class="mt-6 space-y-4 text-left">
                <!-- Row: Category & Item Name -->
                <div class="flex items-center gap-8">
                  <div class="w-full">
                    <label class="block text-sm font-medium text-gray-300">Category Name:</label>
                    <select
                      v-model="form.category_id"
                      required
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
                    <span v-if="form.errors.category_id" class="mt-2 text-red-500">
                      {{ form.errors.category_id }}
                    </span>
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
                    <span v-if="form.errors.color_id" class="mt-2 text-red-500">
                      {{ form.errors.color_id }}
                    </span>
                  </div>
                  <div class="w-full">
                    <label class="block text-sm font-medium text-gray-300">Size:</label>
                    <input
                      v-model="form.size"
                      type="text"
                      placeholder="Enter Size"
                      class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    />
                    <span v-if="form.errors.size" class="mt-2 text-red-500">
                      {{ form.errors.size }}
                    </span>
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
                    <span v-if="form.errors.supplier_id" class="mt-2 text-red-500">
                      {{ form.errors.supplier_id }}
                    </span>
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
                    <span v-if="form.errors.rental_quantity" class="mt-2 text-red-500">
                      {{ form.errors.rental_quantity }}
                    </span>
                  </div>
                </div>

                <!-- Row: Rent Price -->
                <div class="flex items-center gap-8">
                  <div class="w-full">
                    <label class="block text-sm font-medium text-gray-300">Rent Price:</label>
                    <input
                      v-model="form.rent_price"
                      type="number"
                      step="0.01"
                      required
                      min="0"
                      placeholder="Enter Rent Price"
                      class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    />
                    <span v-if="form.errors.rent_price" class="mt-2 text-red-500">
                      {{ form.errors.rent_price }}
                    </span>
                  </div>
                </div>

                <!-- Row: Commission % Shop & Commission % Supplier -->
                <div class="flex items-center gap-8">
                  <div class="w-full">
                    <label class="block text-sm font-medium text-gray-300">Commission % for Shop per item:</label>
                    <input
                      v-model="form.commission_percentage_shop"
                      type="number"
                      step="0.01"
                      required
                      min="0"
                      max="100"
                      placeholder="Enter Shop Commission %"
                      class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    />
                    <span v-if="form.errors.commission_percentage_shop" class="mt-2 text-red-500">
                      {{ form.errors.commission_percentage_shop }}
                    </span>
                    <p v-if="shopCommissionAmount !== null" class="mt-2 text-sm font-semibold text-green-400">
                      Commission Amount: {{ shopCommissionAmount }}
                    </p>
                  </div>
                  <div class="w-full">
                    <label class="block text-sm font-medium text-gray-300">Commission % for Supplier per item:</label>
                    <input
                      v-model="form.commission_percentage_supplier"
                      type="number"
                      step="0.01"
                      required
                      min="0"
                      max="100"
                      placeholder="Enter Supplier Commission %"
                      class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    />
                    <span v-if="form.errors.commission_percentage_supplier" class="mt-2 text-red-500">
                      {{ form.errors.commission_percentage_supplier }}
                    </span>
                    <p v-if="supplierCommissionAmount !== null" class="mt-2 text-sm font-semibold text-green-400">
                      Commission Amount: {{ supplierCommissionAmount }}
                    </p>
                  </div>
                </div>

                <!-- Row: Image -->
                <div class="flex items-center gap-8">
                  <div class="w-full">
                    <label class="block text-sm font-medium text-gray-300">Image:</label>
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
                  Update
                </button>
                <button
                  class="px-4 py-2 text-gray-700 bg-gray-300 rounded hover:bg-gray-400"
                  @click="$emit('update:open', false)"
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
</template>

<script setup>
import {
  Dialog,
  DialogPanel,
  DialogTitle,
  TransitionChild,
  TransitionRoot,
} from "@headlessui/vue";
import { computed, watch } from "vue";
import { useForm } from "@inertiajs/vue3";

const emit = defineEmits(["update:open", "success"]);

const props = defineProps({
  open: { type: Boolean, required: true },
  categories: { type: Array, required: true },
  colors: { type: Array, required: true },
  suppliers: { type: Array, required: true },
  selectedItem: { type: Object, default: null },
});

const form = useForm({
  _method: "PUT", // Important for Laravel's route binding with file uploads
  category_id: "",
  item_name: "",
  color_id: "",
  size: "",
  supplier_id: "",
  rental_quantity: null,
  rent_price: null,
  commission_percentage_shop: null,
  commission_percentage_supplier: null,
  image: null,
});


// Watch for changes in the selected item and populate the form
watch(
  () => props.selectedItem,
  (newItem) => {
    if (newItem) {
      form.category_id = newItem.category_id || "";
      form.item_name = newItem.item_name || "";
      form.color_id = newItem.color_id || "";
      form.size = newItem.size || "";
      form.supplier_id = newItem.supplier_id || "";
      form.rental_quantity = newItem.rental_quantity || null;
      form.rent_price = newItem.rent_price || null;
      form.commission_percentage_shop = newItem.commission_percentage_shop || null;
      form.commission_percentage_supplier = newItem.commission_percentage_supplier || null;
      form.image = null; // Don't pre-fill file inputs
    }
  },
  { immediate: true }
);

// Computed commission amounts
const shopCommissionAmount = computed(() => {
  if (form.rent_price && form.commission_percentage_shop) {
    return (
      (parseFloat(form.rent_price) * parseFloat(form.commission_percentage_shop)) /
      100
    ).toFixed(2);
  }
  return null;
});

const supplierCommissionAmount = computed(() => {
  if (form.rent_price && form.commission_percentage_supplier) {
    return (
      (parseFloat(form.rent_price) * parseFloat(form.commission_percentage_supplier)) /
      100
    ).toFixed(2);
  }
  return null;
});

const handleImageUpload = (event) => {
  form.image = event.target.files[0];
};

const submit = () => {
  if (!props.selectedItem) return;

  // Since we're uploading files, Inertia requires us to use POST with _method: 'PUT'
  form.post(`/rental-items/${props.selectedItem.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      emit("success", "Rental item updated successfully!");
      form.reset();
      emit("update:open", false);
    },
    onError: (errors) => {
      console.error("Form update failed:", errors);
    },
  });
};
</script>
