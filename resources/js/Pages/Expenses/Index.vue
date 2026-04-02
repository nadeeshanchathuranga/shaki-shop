<style>
.dataTables_wrapper .dataTables_paginate {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 20px;
}

#ExpenseTable_filter {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  margin-bottom: 16px;
}

#ExpenseTable_filter label {
  font-size: 17px;
  color: #000000;
  display: flex;
  align-items: center;
}

#ExpenseTable_filter input[type="search"] {
  font-weight: 400;
  padding: 9px 15px;
  font-size: 14px;
  color: #000000cc;
  border: 1px solid rgb(209 213 219);
  border-radius: 5px;
  background: #fff;
  outline: none;
  transition: all 0.5s ease;
}

#ExpenseTable_filter input[type="search"]:focus {
  outline: none;
  border: 1px solid #4b5563;
  box-shadow: none;
}

#ExpenseTable_filter {
  float: left;
}

.dataTables_wrapper {
  margin-bottom: 10px;
}
</style>

<template>
  <Head title="Expenses" />
  <Banner />
  <div
    class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gray-100 md:px-36 px-16"
  >
    <Header />
    <div class="w-full md:w-5/6 py-12 space-y-24">
      <div class="flex items-center justify-between">
        <div></div>
        <p class="text-3xl italic font-bold text-black">
          <span class="px-4 py-1 mr-3 text-white bg-black rounded-xl">
            {{ allexpenses.length }}
          </span>
          <span class="text-xl">/ Total Expenses</span>
        </p>
      </div>

      <div class="flex w-full">
        <div class="flex items-center w-full h-16 space-x-4 rounded-2xl">
          <Link href="/">
            <img src="/images/back-arrow.png" class="w-14 h-14" />
          </Link>
          <p class="text-4xl font-bold tracking-wide text-black uppercase">
            Expenses
          </p>
        </div>
        <div class="flex justify-end w-full">
          <p
            @click="() => { if (HasRole(['Admin', 'Manager'])) { isCreateModalOpen = true; } }"
            :class="HasRole(['Admin', 'Manager'])
              ? 'md:px-12 py-4 px-4 md:text-2xl font-bold tracking-wider text-white uppercase bg-blue-600 rounded-xl cursor-pointer'
              : 'md:px-12 py-4 px-4 md:text-2xl font-bold tracking-wider text-white uppercase bg-blue-600 cursor-not-allowed rounded-xl'"
            :title="HasRole(['Admin', 'Manager']) ? '' : 'You do not have permission to add expenses'"
          >
            <i class="md:pr-4 ri-add-circle-fill"></i> Add Expense
          </p>
        </div>
      </div>

      <template v-if="allexpenses && allexpenses.length > 0">
        <div class="overflow-x-auto">
          <table
            id="ExpenseTable"
            class="w-full text-gray-700 bg-white border border-gray-300 rounded-lg shadow-md table-auto"
          >
            <thead>
              <tr
                class="bg-gradient-to-r from-blue-600 via-blue-500 to-blue-600 text-[16px] text-white border-b border-blue-700 text-left"
              >
                <th class="p-4 font-semibold tracking-wide uppercase">#</th>
                <th class="p-4 font-semibold tracking-wide uppercase">Date</th>
                <th class="p-4 font-semibold tracking-wide uppercase">Amount</th>
                <th class="p-4 font-semibold tracking-wide uppercase">Reason</th>
                <th class="p-4 font-semibold tracking-wide uppercase">Added By</th>
                <th class="p-4 font-semibold tracking-wide uppercase">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(expense, index) in allexpenses"
                :key="expense.id"
                class="hover:bg-gray-200"
              >
                <td class="px-6 py-3">{{ index + 1 }}</td>
                <td class="px-6 py-3">{{ expense.date }}</td>
                <td class="px-6 py-3">{{ Number(expense.amount).toLocaleString() }}</td>
                <td class="px-6 py-3">{{ expense.description }}</td>
                <td class="px-6 py-3">{{ expense.user?.name ?? '-' }}</td>
                <td class="px-6 py-3">
                  <button
                    :class="HasRole(['Admin', 'Manager'])
                      ? 'px-4 py-2 bg-green-500 text-white rounded-lg'
                      : 'px-4 py-2 bg-green-400 text-white rounded-lg cursor-not-allowed'"
                    :disabled="!HasRole(['Admin', 'Manager'])"
                    @click="() => { if (HasRole(['Admin', 'Manager'])) openEditModal(expense); }"
                  >
                    Edit
                  </button>
                  <button
                    :class="HasRole(['Admin', 'Manager'])
                      ? 'px-4 py-2 bg-red-500 text-white rounded-lg ml-2'
                      : 'px-4 py-2 bg-red-400 text-white rounded-lg cursor-not-allowed ml-2'"
                    :disabled="!HasRole(['Admin', 'Manager'])"
                    @click="() => { if (HasRole(['Admin', 'Manager'])) openDeleteModal(expense); }"
                  >
                    Delete
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </template>
      <template v-else>
        <div class="col-span-4 text-center">
          <p class="text-center text-red-500 text-[17px]">No Expenses Available</p>
        </div>
      </template>
    </div>
  </div>

  <ExpenseCreateModal v-model:open="isCreateModalOpen" />

  <ExpenseUpdateModal
    v-model:open="isEditModalOpen"
    :selected-expense="selectedExpense"
  />

  <ExpenseDeleteModal
    v-model:open="isDeleteModalOpen"
    :selected-expense="selectedExpense"
  />

  <Footer />
</template>

<script setup>
import { ref } from "vue";
import { Link, Head } from "@inertiajs/vue3";
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import Banner from "@/Components/Banner.vue";
import ExpenseCreateModal from "@/Components/custom/ExpenseCreateModal.vue";
import ExpenseUpdateModal from "@/Components/custom/ExpenseUpdateModal.vue";
import ExpenseDeleteModal from "@/Components/custom/ExpenseDeleteModal.vue";
import { HasRole } from "@/Utils/Permissions";

defineProps({
  allexpenses: Array,
  totalExpenses: Number,
});

const isCreateModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedExpense = ref(null);

const openEditModal = (expense) => {
  selectedExpense.value = expense;
  isEditModalOpen.value = true;
};

const openDeleteModal = (expense) => {
  selectedExpense.value = expense;
  isDeleteModalOpen.value = true;
};

$(document).ready(function () {
  $("#ExpenseTable").DataTable({
    dom: "Bfrtip",
    pageLength: 10,
    buttons: [],
    initComplete: function () {
      let searchInput = $("div.dataTables_filter input");
      searchInput.attr("placeholder", "Search ...");
    },
    language: {
      search: "",
    },
  });
});
</script>
