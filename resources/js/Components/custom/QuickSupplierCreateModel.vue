<template>
  <TransitionRoot as="template" :show="open">
    <Dialog class="relative z-20" @close="$emit('update:open', false)">
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
      <div class="fixed inset-0 z-20 flex items-center justify-center">
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
            class="bg-black border-4 border-blue-600 rounded-[20px] shadow-xl w-5/6 lg:w-2/5 p-10 text-center"
          >
            <!-- Modal Title -->
            <DialogTitle class="text-2xl font-bold text-white">
              Add New Supplier
            </DialogTitle>

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

              <!-- Modal Buttons -->
              <div class="mt-8 flex gap-4 justify-center">
                <button
                  @click="playClickSound"
                  class="px-6 py-2 text-white bg-blue-600 rounded hover:bg-blue-700 font-semibold"
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
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";

const emit = defineEmits(["update:open", "supplier-created"]);
const errors = ref({});

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

const submit = async () => {
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

    if (response.ok) {
      const data = await response.json();
      emit("supplier-created", data.supplier);
      form.reset();
      emit("update:open", false);
    } else {
      const errorData = await response.json();
      if (errorData.errors) {
        errors.value = errorData.errors;
      }
    }
  } catch (error) {
    console.error('Error creating supplier:', error);
  }
};
</script>
