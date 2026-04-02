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
          <DialogPanel class="bg-white text-black border-4 border-blue-600 rounded-[20px] shadow-xl w-5/6 lg:w-3/6 p-6">
            <div class="flex flex-col items-start justify-start w-full h-full px-2 pt-4">
              <div class="flex justify-center w-full h-full py-4 space-x-8 items-start-center">
                <!-- Left Side: Image -->
                <div class="w-1/2">
                  <img
                    :src="selectedItem.image ? `/${selectedItem.image}` : '/images/placeholder.jpg'"
                    alt="Rental Item Image"
                    class="object-cover h-full rounded-2xl"
                  />
                </div>

                <!-- Right Side: Rental Item Details -->
                <div class="flex flex-col justify-between w-1/2 h-full">
                  <!-- Item Name -->
                  <div class="flex items-center justify-between">
                    <p class="text-3xl font-bold text-black w-full break-words">
                      {{ selectedItem.item_name }}
                    </p>
                  </div>

                  <!-- Category -->
                  <p class="pb-6 mt-2 text-[#00000099] text-xl font-normal italic">
                    {{ selectedItem.category?.name ?? "No Category" }}
                  </p>

                  <!-- Supplier -->
                  <p class="pb-6 text-2xl font-bold text-black">
                    <span class="text-[#00000099] font-normal">Supplier: </span>
                    {{ selectedItem.supplier?.name || "N/A" }}
                  </p>

                  <!-- Color -->
                  <div class="flex items-center justify-between w-full text-2xl">
                    <div class="flex flex-col w-full">
                      <p class="text-justify text-[#00000099] text-2xl flex items-center pb-6">
                        Color:
                        <span class="font-bold text-black">
                          {{ selectedItem?.color?.name ?? "N/A" }}
                        </span>
                      </p>
                    </div>
                  </div>

                  <!-- Rent Price -->
                  <div class="flex items-center justify-between w-full pb-6 text-2xl">
                    <div class="flex flex-col w-full">
                      <p class="text-[#00000099]">Rent Price:</p>
                      <p class="font-bold text-black">
                        {{ selectedItem?.rent_price ?? "N/A" }} LKR
                      </p>
                    </div>
                  </div>

                  <!-- Quantity & Commission -->
                  <div class="flex items-center justify-between w-full pb-6 text-2xl">
                    <div class="flex flex-col w-full">
                      <p class="text-[#00000099]">Quantity:</p>
                      <p class="font-bold text-black">
                        {{ selectedItem?.rental_quantity ?? "N/A" }}
                      </p>
                    </div>
                    <div class="flex flex-col w-full">
                      <p class="text-[#00000099]">Shop Commission:</p>
                      <p class="font-bold text-black">
                        {{ selectedItem?.commission_percentage_shop }}{{ selectedItem?.commission_type_shop === 'fixed' ? ' Rs' : '%' }} ({{ selectedItem?.commission_amount_shop }} LKR)
                      </p>
                    </div>
                  </div>

                  <!-- Supplier Commission -->
                  <p class="pb-8 text-2xl font-bold text-black">
                    <span class="text-[#00000099] font-normal">Supplier Commission: </span>
                    {{ selectedItem?.commission_percentage_supplier }}{{ selectedItem?.commission_type_supplier === 'fixed' ? ' Rs' : '%' }} ({{ selectedItem?.commission_amount_supplier }} LKR)
                  </p>

                  <!-- Created Date -->
                  <p class="pb-8 text-2xl font-bold text-black">
                    <span class="text-[#00000099] font-normal">Created On: </span>
                    {{ formattedDate }}
                  </p>

                  <!-- Barcode Printing Section -->
                  <div class="mt-2" v-if="HasRole(['Admin', 'Manager'])">
                    <label class="block mb-1 text-xl text-gray-700">Number of Barcodes:</label>
                    <input
                      type="number"
                      v-model="barcodeCount"
                      min="1"
                      class="w-full px-4 py-2 text-xl border rounded focus:outline-none focus:ring focus:ring-blue-300"
                    />
                    <button
                      class="w-full px-4 py-3 mt-4 text-2xl font-semibold tracking-widest text-white bg-blue-600 rounded-xl hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2"
                      @click="generateAndPrintBarcodes"
                    >
                      Print Bar Codes
                    </button>
                  </div>
                </div>
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
import { ref, computed } from "vue";
import dayjs from "dayjs";
import advancedFormat from "dayjs/plugin/advancedFormat";
import { HasRole } from "@/Utils/Permissions";

dayjs.extend(advancedFormat);

const emit = defineEmits(["update:open"]);

const { selectedItem } = defineProps({
  open: {
    type: Boolean,
    required: true,
  },
  selectedItem: {
    type: Object,
    default: null,
  },
});

const barcodeCount = ref(1);

const formattedDate = computed(() =>
  selectedItem && selectedItem.created_at
    ? dayjs(selectedItem.created_at).format("Do MMMM YYYY")
    : ""
);

function generateAndPrintBarcodes() {
  let barcode = selectedItem?.barcode;
  const count = parseInt(barcodeCount.value, 10);

  // Auto-generate barcode if it doesn't exist
  if (!barcode || barcode.trim() === '') {
    barcode = `RI-${selectedItem?.id}`;
  }
  
  if (!count || count < 1) {
    alert('Please enter a valid number of barcodes.');
    return;
  }

  // Exact 30mm × 18mm sticker size - 1 per row guaranteed
  const MM_TO_PX = 3.78;
  const LABEL_W_MM = 30;
  const LABEL_H_MM = 18;
  const BARCODE_H_MM = 7;

  // Build labels HTML
  const labelsHtml = Array.from({ length: count }).map((_, idx) => `
    <div class="barcode-label">
      <div class="product-name">${selectedItem?.item_name || 'N/A'}</div>

      <div class="barcode-svg"><svg id="barcode${idx + 1}"></svg></div>
      <div class="bottom-info">${(selectedItem?.rent_price ?? 'N/A')} LKR</div>
    </div>
  `).join('');

  const htmlContent = `
    <html>
    <head>
      <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
      <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        html, body {
          background: white;
          margin: 0;
          padding: 0;
        }
        body {
          font-family: "Poppins", sans-serif;
          -webkit-print-color-adjust: exact;
          print-color-adjust: exact;
        }

        .barcode-container {
          width: 36mm;
          margin: 0 auto;
          padding: 3mm;
          display: flex;
          flex-wrap: wrap;
          justify-content: center;
          align-content: flex-start;
          gap: 0;
        }

        .barcode-label {
          width: ${LABEL_W_MM}mm;
          height: ${LABEL_H_MM}mm;
          min-width: ${LABEL_W_MM}mm;
          max-width: ${LABEL_W_MM}mm;
          min-height: ${LABEL_H_MM}mm;
          max-height: ${LABEL_H_MM}mm;
          background: white;
          padding: 0.8mm;
          display: flex;
          flex-direction: column;
          align-items: center;
          justify-content: space-between;
          overflow: hidden;
          page-break-inside: avoid;
          flex-shrink: 0;
          flex-grow: 0;
        }

        .product-name {
          font-size: 8px;
          font-weight: 600;
          line-height: 1.1;
          width: 100%;
          text-align: center;
          white-space: nowrap;
          overflow: hidden;
          text-overflow: ellipsis;
        }

        .barcode-svg {
          width: 100%;
          height: ${BARCODE_H_MM}mm;
          display: flex;
          align-items: center;
          justify-content: center;
          flex-shrink: 0;
        }
        .barcode-svg svg {
          width: 95%;
          height: 100%;
        }

        .bottom-info {
          font-size: 11px;
          font-weight: 700;
          line-height: 1;
          width: 100%;
          text-align: center;
        }

        @media print {
          @page {
            margin: 3mm;
            size: auto;
          }
          html, body {
            padding: 0;
            margin: 0;
          }
          .barcode-container {
            margin: 0 auto;
            padding: 2mm;
          }
        }
      </style>
    </head>
    <body>
      <div class="barcode-container">
        ${labelsHtml}
      </div>
    </body>
    </html>
  `;

  const printWindow = window.open('', '_blank');
  printWindow.document.write(htmlContent);
  printWindow.document.close();

  printWindow.onload = () => {
    for (let i = 1; i <= count; i++) {
      const svg = printWindow.document.getElementById(`barcode${i}`);
      if (!svg) continue;
      JsBarcode(svg, barcode, {
        format: 'CODE128',
        lineColor: '#000',
        width: 1.3,
        height: Math.round(BARCODE_H_MM * MM_TO_PX),
        displayValue: false,
        margin: 0,
      });
    }

    setTimeout(() => {
      printWindow.focus();
      printWindow.print();
      printWindow.close();
    }, 500);
  };
}
</script>
