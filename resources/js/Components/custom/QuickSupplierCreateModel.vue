<template>
  <div v-if="open" class="fixed inset-0 z-[1000]">
    <div
      class="fixed inset-0 bg-gray-500 bg-opacity-75"
      @click="handleClose"
    ></div>
    <div
      class="fixed inset-0 z-[1001] flex items-center justify-center p-4"
      @click.stop
    >
      <div class="bg-black border-4 border-blue-600 rounded-[20px] shadow-xl w-full max-w-lg p-10 text-center">
            <!-- Modal Title -->
            <h2 class="text-2xl font-bold text-white">
              Add New Supplier
            </h2>

            <form @submit.prevent="submit" class="mt-6 space-y-6 text-left">
              <!-- Supplier Name -->
              <div>
                <label class="block text-sm font-medium text-gray-300">
                  Supplier Name: <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.name"
                  type="text"
                  required
                  placeholder="Enter Supplier Name"
                  class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                />
                <span v-if="errors.name" class="mt-2 text-red-500">
                  {{ errors.name[0] }}
                </span>
              </div>

              <!-- Email (Optional) -->
              <div>
                <label class="block text-sm font-medium text-gray-300">
                  Email: <span class="text-gray-500 text-xs">(Optional)</span>
                </label>
                <input
                  v-model="form.email"
                  type="email"
                  placeholder="Enter Email"
                  class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                />
                <span v-if="errors.email" class="mt-2 text-red-500">
                  {{ errors.email[0] }}
                </span>
              </div>

              <p v-if="errors.general" class="text-red-500 text-sm">
                {{ errors.general[0] }}
              </p>

              <!-- Modal Buttons -->
              <div class="mt-8 flex gap-4 justify-center">
                <button
                  @click="playClickSound"
                  class="px-6 py-2 text-white bg-blue-600 rounded hover:bg-blue-700 font-semibold"
                  :disabled="isSubmitting"
                  type="submit"
                >
                  Save Supplier
                </button>
                <button
                  class="px-6 py-2 text-gray-700 bg-gray-300 rounded hover:bg-gray-400 font-semibold"
                  @click="
                    () => {
                      playClickSound();
                      emit('update:open', false);
                    }
                  "
                  type="button"
                >
                  Cancel
                </button>
              </div>
            </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";

const emit = defineEmits(["update:open", "supplier-created"]);
const errors = ref({});
const isSubmitting = ref(false);

const playClickSound = () => {
  const clickSound = new Audio("/sounds/click-sound.mp3");
  clickSound.play();
};

defineProps({
  open: {
    type: Boolean,
    required: true,
  },
});

const form = useForm({
  name: "",
  email: "",
});

const handleClose = () => {
  errors.value = {};
  emit("update:open", false);
};

const submit = async () => {
  if (isSubmitting.value) return;
  isSubmitting.value = true;
  errors.value = {}; // Clear previous errors
  const formData = new FormData();
  formData.append('name', form.name);
  formData.append('email', form.email);

  // Get CSRF token from meta tag
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  try {
    const response = await fetch('/suppliers', {
      method: 'POST',
      body: formData,
      headers: {
        'X-CSRF-TOKEN': token,
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
      },
    });

    const data = await response.json().catch(() => ({}));

    if (response.ok) {
      if (data.supplier) {
        emit("supplier-created", data.supplier);
      }
      form.reset();
      handleClose();
    } else {
      if (data.errors) {
        errors.value = data.errors;
      } else {
        errors.value = { general: [data.message || 'Failed to create supplier.'] };
      }
    }
  } catch (error) {
    console.error('Error creating supplier:', error);
    errors.value = { general: ['An unexpected error occurred while creating the supplier.'] };
  } finally {
    isSubmitting.value = false;
  }
};
</script>
