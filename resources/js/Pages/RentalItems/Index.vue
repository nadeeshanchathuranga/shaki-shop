<style lang="css">
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
  <Head title="Rental Items" />
  <Banner />
  <div
    class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gray-100 md:px-36 px-16"
  >
    <!-- Include the Header -->
    <Header />
    <div class="w-full md:w-5/6 py-12 space-y-16">
      <div class="flex items-center justify-between">
        <div class="flex items-center justify-center space-x-4">
          <Link href="/">
            <img src="/images/back-arrow.png" class="w-14 h-14" />
          </Link>
          <p class="text-4xl font-bold tracking-wide text-black uppercase">
            Rental Items
          </p>
        </div>

        <p
          @click="
            () => {
              if (HasRole(['Admin'])) {
                isCreateModalOpen = true;
              }
            }
          "
          :class="
            HasRole(['Admin'])
              ? 'md:px-12 py-4 px-4 md:text-2xl font-bold tracking-wider text-white uppercase bg-blue-600 rounded-xl cursor-pointer'
              : 'md:px-12 py-4 px-4 md:text-2xl font-bold tracking-wider text-white uppercase bg-blue-600 cursor-not-allowed rounded-xl'
          "
          :title="
            HasRole(['Admin'])
              ? ''
              : 'You do not have permission to add rental items'
          "
        >
          <i class="md:pr-4 ri-add-circle-fill"></i> Add Rental Item
        </p>
      </div>

      <div class="grid md:grid-cols-4 grid-cols-1 gap-8">
        <template v-if="rentalItems.data.length > 0">
          <div
            v-for="item in rentalItems.data"
            :key="item.id"
            class="space-y-4 text-white transition-transform duration-300 transform bg-black border-4 border-black shadow-lg hover:-translate-y-4"
          >
            <div>
              <img
                @click="openViewModal(item)"
                :src="
                  item.image
                    ? `/${item.image}`
                    : '/images/placeholder.jpg'
                "
                alt="Rental Item Image"
                class="object-cover w-full h-64 cursor-pointer hover:opacity-80 transition-opacity"
              />
            </div>
            <div class="px-2 py-4 space-y-4">
              <div
                class="flex items-start space-x-3 justify-between text-[11px] font-bold tracking-wide"
              >
                <p class="text-justify">{{ item.item_name || "N/A" }}</p>
                <p
                  class="px-3 text-white bg-green-700 py-2 rounded-full flex items-center"
                >
                  {{ item.rent_price || "N/A" }}
                </p>
              </div>

              <div class="flex justify-center space-x-2 items-start w-full">
                <div class="flex space-x-1 text-gray-400">
                  <p class="font-bold">Category:</p>
                  <p>{{ item.category?.name || "N/A" }}</p>
                </div>
                <div class="flex space-x-1 text-gray-400">
                  <p class="font-bold">Color:</p>
                  <p>{{ item.color?.name || "N/A" }}</p>
                </div>
              </div>

              <div class="flex justify-center space-x-4 items-start w-full text-gray-400">
                <div class="flex space-x-1">
                  <p class="font-bold">Supplier:</p>
                  <p>{{ item.supplier?.name || "N/A" }}</p>
                </div>
                <div class="flex space-x-1">
                  <p class="font-bold">Qty:</p>
                  <p>{{ item.rental_quantity }}</p>
                </div>
              </div>

              <div class="flex justify-between items-center text-xs text-gray-400">
                <div>
                  <p>Shop Comm: {{ item.commission_percentage_shop }}% ({{ item.commission_amount_shop }})</p>
                </div>
                <div>
                  <p>Supplier Comm: {{ item.commission_percentage_supplier }}% ({{ item.commission_amount_supplier }})</p>
                </div>
              </div>

              <div class="flex items-center justify-end">
                <div class="flex space-x-4">
                  <button
                    :disabled="!HasRole(['Admin'])"
                    @click="
                      () => {
                        if (HasRole(['Admin'])) {
                          openEditModal(item);
                        }
                      }
                    "
                    :class="{
                      'cursor-not-allowed opacity-50': !HasRole(['Admin']),
                      'cursor-pointer hover:bg-green-600 hover:text-white':
                        HasRole(['Admin']),
                    }"
                    class="flex items-center justify-center w-10 h-10 text-gray-800 transition duration-200 bg-gray-100 rounded-full"
                  >
                    <i class="ri-pencil-line"></i>
                  </button>

                  <button
                    :disabled="!HasRole(['Admin'])"
                    @click="
                      () => {
                        if (HasRole(['Admin'])) {
                          openDeleteModal(item);
                        }
                      }
                    "
                    :class="{
                      'cursor-not-allowed opacity-50': !HasRole(['Admin']),
                      'cursor-pointer hover:bg-red-600 hover:text-white':
                        HasRole(['Admin']),
                    }"
                    class="flex items-center justify-center w-10 h-10 text-gray-800 transition duration-200 bg-gray-100 rounded-full"
                  >
                    <i class="ri-delete-bin-line"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </template>
        <template v-else>
          <div class="col-span-4 text-center text-gray-500">
            <p class="text-center text-red-500 text-[17px]">
              No Rental Items Available
            </p>
          </div>
        </template>
      </div>

      <div class="flex space-x-2 pagination">
        <!-- Prev Button -->
        <span
          v-if="rentalItems.links[0]"
          @click.prevent="navigateTo(rentalItems.links[0].url)"
          :class="[
            'pagination-btn',
            { 'pagination-disabled': !rentalItems.links[0].url },
          ]"
        >
          Previous
        </span>

        <!-- Pagination Links -->
        <span
          v-for="link in rentalItems.links.slice(
            1,
            rentalItems.links.length - 1
          )"
          :key="link.label"
          @click.prevent="navigateTo(link.url)"
          :class="['pagination-btn', { 'pagination-active': link.active }]"
          v-html="link.label"
        ></span>

        <!-- Next Button -->
        <span
          v-if="rentalItems.links[rentalItems.links.length - 1]"
          @click.prevent="
            navigateTo(rentalItems.links[rentalItems.links.length - 1].url)
          "
          :class="[
            'pagination-btn',
            {
              'pagination-disabled':
                !rentalItems.links[rentalItems.links.length - 1].url,
            },
          ]"
        >
          Next
        </span>
      </div>
    </div>
  </div>

  <RentalItemCreateModel
    :categories="categories"
    :colors="colors"
    :suppliers="suppliers"
    v-model:open="isCreateModalOpen"
    @success="handleSuccess"
  />

  <RentalItemUpdateModel
    :categories="categories"
    :colors="colors"
    :suppliers="suppliers"
    :selectedItem="selectedItem"
    v-model:open="isEditModalOpen"
    @success="handleSuccess"
  />

  <RentalItemViewModel
    :selectedItem="selectedItemForView"
    v-model:open="isViewModalOpen"
  />

  <!-- Delete Confirmation Modal -->
  <TransitionRoot as="template" :show="isDeleteModalOpen">
    <Dialog class="relative z-10" @close="isDeleteModalOpen = false">
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
          <DialogPanel class="bg-white rounded-2xl shadow-xl w-96 p-8 text-center">
            <DialogTitle class="text-xl font-bold text-gray-800">
              Delete Rental Item
            </DialogTitle>
            <p class="mt-4 text-gray-600">
              Are you sure you want to delete this rental item? This action cannot be undone.
            </p>
            <div class="mt-6 flex justify-center space-x-4">
              <button
                @click="deleteRentalItem"
                class="px-6 py-2 text-white bg-red-600 rounded hover:bg-red-700"
              >
                Delete
              </button>
              <button
                @click="isDeleteModalOpen = false"
                class="px-6 py-2 text-gray-700 bg-gray-300 rounded hover:bg-gray-400"
              >
                Cancel
              </button>
            </div>
          </DialogPanel>
        </TransitionChild>
      </div>
    </Dialog>
  </TransitionRoot>

  <!-- Success Prompt Modal -->
  <TransitionRoot as="template" :show="isSuccessModalOpen">
    <Dialog class="relative z-10" @close="isSuccessModalOpen = false">
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
          <DialogPanel class="bg-white rounded-2xl shadow-xl w-96 p-8 text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100 mb-4">
              <i class="ri-check-line text-3xl text-green-600"></i>
            </div>
            <DialogTitle class="text-xl font-bold text-gray-800">
              Success
            </DialogTitle>
            <p class="mt-4 text-gray-600">
              {{ successMessage }}
            </p>
            <div class="mt-6 flex justify-center">
              <button
                @click="isSuccessModalOpen = false"
                class="px-8 py-2 text-white bg-green-600 rounded-lg hover:bg-green-700 transition font-bold"
              >
                OK
              </button>
            </div>
          </DialogPanel>
        </TransitionChild>
      </div>
    </Dialog>
  </TransitionRoot>

  <Footer />
</template>

<script setup>
import { ref } from "vue";
import { Head } from "@inertiajs/vue3";
import { Link, useForm, router } from "@inertiajs/vue3";
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import Banner from "@/Components/Banner.vue";
import RentalItemCreateModel from "@/Components/custom/RentalItemCreateModel.vue";
import RentalItemUpdateModel from "@/Components/custom/RentalItemUpdateModel.vue";
import RentalItemViewModel from "@/Components/custom/RentalItemViewModel.vue";
import { HasRole } from "@/Utils/Permissions";
import {
  Dialog,
  DialogPanel,
  DialogTitle,
  TransitionChild,
  TransitionRoot,
} from "@headlessui/vue";

const isCreateModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const isSuccessModalOpen = ref(false);
const isViewModalOpen = ref(false);
const successMessage = ref("");
const selectedItem = ref(null);
const selectedItemForView = ref(null);

const props = defineProps({
  rentalItems: Object,
  categories: Array,
  colors: Array,
  suppliers: Array,
});

const openEditModal = (item) => {
  selectedItem.value = item;
  isEditModalOpen.value = true;
};

const openViewModal = (item) => {
  selectedItemForView.value = item;
  isViewModalOpen.value = true;
};

const handleSuccess = (message) => {
  successMessage.value = message;
  isSuccessModalOpen.value = true;
};

const openDeleteModal = (item) => {
  selectedItem.value = item;
  isDeleteModalOpen.value = true;
};

const deleteRentalItem = () => {
  if (!selectedItem.value) return;
  const form = useForm({});
  form.delete(`/rental-items/${selectedItem.value.id}`, {
    onSuccess: () => {
      isDeleteModalOpen.value = false;
    },
    onError: (errors) => {
      console.error("Delete failed:", errors);
    },
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
