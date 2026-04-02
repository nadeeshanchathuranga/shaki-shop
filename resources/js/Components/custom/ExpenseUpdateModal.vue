<template>
  <TransitionRoot as="template" :show="open">
    <Dialog class="relative z-10" @close="$emit('update:open', false)">
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
          <DialogPanel
            class="bg-black border-4 border-blue-600 rounded-[20px] shadow-xl w-5/6 lg:w-3/6 p-10 text-center"
          >
            <DialogTitle class="text-xl font-bold text-white">Edit Expense</DialogTitle>

            <form @submit.prevent="submit">
              <div class="mt-6 space-y-4 text-left">
                <div>
                  <label class="block text-sm font-medium text-gray-300">Amount:</label>
                  <input
                    v-model="form.amount"
                    type="number"
                    step="0.01"
                    min="0"
                    required
                    class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                  />
                  <span v-if="form.errors.amount" class="mt-1 text-red-500 text-sm">
                    {{ form.errors.amount }}
                  </span>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-300">Reason:</label>
                  <textarea
                    v-model="form.description"
                    rows="3"
                    required
                    class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                  ></textarea>
                  <span v-if="form.errors.description" class="mt-1 text-red-500 text-sm">
                    {{ form.errors.description }}
                  </span>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-300">Date:</label>
                  <input
                    v-model="form.date"
                    type="date"
                    class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                  />
                  <span v-if="form.errors.date" class="mt-1 text-red-500 text-sm">
                    {{ form.errors.date }}
                  </span>
                </div>
              </div>

              <div class="mt-6 space-x-4">
                <button
                  class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700"
                  type="submit"
                  :disabled="form.processing"
                >
                  Save
                </button>
                <button
                  class="px-4 py-2 text-gray-700 bg-gray-300 rounded hover:bg-gray-400"
                  type="button"
                  @click="emit('update:open', false)"
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
import { watch } from "vue";
import { useForm } from "@inertiajs/vue3";

const emit = defineEmits(["update:open"]);

const props = defineProps({
  open: {
    type: Boolean,
    required: true,
  },
  selectedExpense: {
    type: Object,
    default: null,
  },
});

const form = useForm({
  amount: "",
  description: "",
  date: "",
});

watch(
  () => props.selectedExpense,
  (expense) => {
    if (expense) {
      form.amount = expense.amount;
      form.description = expense.description;
      form.date = expense.date;
    }
  },
  { immediate: true }
);

const submit = () => {
  if (!props.selectedExpense?.id) return;

  form.put(`/expenses/${props.selectedExpense.id}`, {
    onSuccess: () => {
      emit("update:open", false);
    },
  });
};
</script>
