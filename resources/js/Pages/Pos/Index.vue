<template>

    <Head title="POS" />
    <Banner />
    <div class="flex flex-col items-center justify-start min-h-screen py-8 space-y-4 bg-gray-100 md:px-36 px-16">
        <!-- Include the Header -->
        <Header />

        <div class="w-full md:w-5/6 w-full py-12 space-y-16">
            <div class="flex items-center justify-between space-x-4">
                <div class="flex w-full space-x-4">
                    <Link href="/">
                    <img src="/images/back-arrow.png" class="w-14 h-14" />
                    </Link>
                    <p class="pt-3 text-4xl font-bold tracking-wide text-black uppercase">
                        PoS
                    </p>
                </div>
                <div class="flex items-center justify-between w-full space-x-4">
                    <p class="text-3xl font-bold tracking-wide text-black">
                        Order ID : #{{ orderid  }}
                    </p>
                    <p class="text-3xl text-black cursor-pointer">
                        <i @click="refreshData" class="ri-restart-line"></i>
                    </p>
                </div>
            </div>
            <div class="flex md:flex-row flex-col w-full gap-4">
                <div class="flex flex-col md:w-1/2 w-full">
                    <div class="flex flex-col w-full">
                        <div class="p-16 space-y-8 bg-black shadow-lg rounded-3xl">
                            <p class="mb-4 text-5xl font-bold text-white">Customer Details</p>
                            <div class="mb-3">
                                <input v-model="customer.name" type="text" placeholder="Enter Customer Name"
                                    class="w-full px-4 py-4 text-black placeholder-black bg-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <div class="flex gap-2 mb-3 text-black">
                                <!-- <select
                  v-model="customer.countryCode"
                  class="w-[60px] px-2 py-2 bg-white placeholder-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  <option value="+94">+94</option>
                  <option value="+1">+1</option>
                  <option value="+44">+44</option>
                </select> -->
                                <input v-model="customer.contactNumber" type="text"
                                    placeholder="Enter Customer Contact Number"
                                    class="flex-grow px-4 py-4 text-black placeholder-black bg-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <div class="text-black">
                                <input v-model="customer.email" type="email" placeholder="Enter Customer Email"
                                    class="w-full px-4 py-4 text-black placeholder-black bg-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>

                            <div class="text-black">
                                <select required v-model="employee_id" id="employee_id"
                                    class="w-full px-4 py-4 text-black placeholder-black bg-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="" disabled selected>Select an Employee</option>
                                    <option v-for="employee in allemployee" :key="employee.id" :value="employee.id">
                                        {{ employee.name }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col items-center justify-center w-full md:pt-32 py-8 md:py-0 space-y-8">
                        <img src="/images/Fading wheel.gif" class="object-cover w-32 h-32 rounded-full" />
                        <p class="text-3xl text-black">
                            Bar Code Scanner is in Progress...
                        </p>
                    </div>
                </div>
                <div class="flex md:w-1/2 w-full p-8 border-4 border-black rounded-3xl">
                    <div class="flex flex-col items-start justify-center w-full md:px-12 px-4">
                        <div class="flex flex-col w-full gap-3">
                            <h2 class="md:text-3xl text-2xl font-bold text-black">Billing Details</h2>
                            <div class="flex items-center gap-2">
                              <!-- User Manual Button -->
                              <button @click="isSelectModalOpen = true"
                                  class="flex items-center px-4 py-2.5 text-base font-bold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition shadow-sm whitespace-nowrap">
                                  <i class="ri-book-open-line mr-1.5 text-lg"></i> User Manual
                              </button>
                              
                              <!-- Rental Items Button -->
                              <button @click="isSelectRentalItemModalOpen = true"
                                  class="flex items-center px-4 py-2.5 text-base font-bold text-white bg-purple-600 rounded-lg hover:bg-purple-700 transition shadow-sm whitespace-nowrap">
                                  <i class="ri-shopping-bag-3-line mr-1.5 text-lg"></i> Rental Items
                              </button>
                              
                              <!-- Return Rental Button -->
                              <button @click="isReturnRentalModalOpen = true"
                                  class="flex items-center px-4 py-2.5 text-base font-bold text-white bg-green-600 rounded-lg hover:bg-green-700 transition shadow-sm whitespace-nowrap">
                                  <i class="ri-arrow-go-back-line mr-1.5 text-lg"></i> Return Rental
                              </button>

                              <!-- Booked Items Button -->
                              <button @click="isBookedItemsModalOpen = true"
                                  class="flex items-center px-4 py-2.5 text-base font-bold text-white bg-sky-500 rounded-lg hover:bg-sky-600 transition shadow-sm whitespace-nowrap">
                                  <i class="ri-calendar-check-line mr-1.5 text-lg"></i> Booked Items
                              </button>
                            </div>
                        </div>

                        <div class="flex items-end justify-between w-full my-5 border-2 border-black rounded-2xl">
                            <div class="flex items-center justify-center w-3/4">
                                <label for="search" class="text-xl font-medium text-gray-800"></label>
                                <input v-model="form.barcode" id="search" type="text" placeholder="Enter BarCode Here!"
                                    class="w-full h-16 px-4 rounded-l-2xl focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <div class="flex items-end justify-end w-1/4">
                                <button @click="submitBarcode"
                                    class="px-12 py-4 text-2xl font-bold tracking-wider text-white uppercase bg-blue-600 rounded-r-xl">
                                    Enter
                                </button>
                            </div>
                        </div>

                        <!-- <div class="max-w-xs relative space-y-3">
              <label for="search" class="text-gray-900">
                Type the product name to search
              </label>

              <input
                v-model="form.barcode"
                id="search"
                type="text"
                placeholder="Enter BarCode Here!"
                class="w-full h-16 px-4 rounded-l-2xl focus:outline-none focus:ring-2 focus:ring-blue-500"
              />

              <ul
                v-if="searchResults.length"
                class="w-full rounded bg-white border border-gray-300 px-4 py-2 space-y-1 absolute z-10"
              >
                <li class="px-1 pt-1 pb-2 font-bold border-b border-gray-200">
                  Showing {{ searchResults.length }} results
                </li>
                <li
                  v-for="product in searchResults"
                  :key="product.id"
                  @click="selectProduct(product.name)"
                  class="cursor-pointer hover:bg-gray-100 p-1"
                >
                  {{ product.name }}
                </li>
              </ul>

              <p v-if="form.barcode" class="text-lg pt-2 absolute">
                You have selected:
                <span class="font-semibold">{{ form.barcode }}</span>
              </p>
            </div> -->

                        <div class="w-full text-center">
                            <p v-if="products.length === 0" class="text-2xl text-red-500">
                                No Products to show
                            </p>
                        </div>

                        <div class="flex items-center w-full py-4 border-b border-black" v-for="item in products"
                            :key="item.id">
                            <div class="flex w-1/6">
                                <img :src="item.image ? `/${item.image}` : '/images/placeholder.jpg'
                                    " alt="Supplier Image" class="object-cover w-16 h-16 border border-gray-500" />
                            </div>
                            <div class="flex flex-col justify-between w-5/6">
                                <p class="text-xl text-black">
                                    {{ item.name }}


                                </p>

                                <div
  v-if="Number(item.is_promotion) === 1"
  class="mt-2 rounded-lg border border-gray-200 p-3 bg-gray-50"
>
  <p class="text-md font-bold text-black mb-2">
    Pack items
  </p>

  <!-- Scrollable list -->
  <ul
    class="mt-1 list-disc pl-5 space-y-1 max-h-40 overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100"
  >
    <li
      v-for="pi in item.promotion_items ?? []"
      :key="pi.id"
      class="text-sm text-gray-700 bg-white rounded-md px-2 py-1 shadow-sm hover:bg-gray-50"
    >

      <span v-if="pi.product">  {{ pi.product.name }}</span>
      <span class="ml-2 text-lg text-dark">× {{ pi.quantity }}</span>
    </li>
  </ul>
</div>
                                <div class="flex items-center justify-between w-full">
                                    <div class="flex space-x-4">
                                        <p @click="incrementQuantity(item.id)"
                                            class="flex items-center justify-center w-8 h-8 text-white bg-black rounded cursor-pointer">
                                            <i class="ri-add-line"></i>
                                        </p>
                                        <!-- <p
                      class="bg-[#D9D9D9] border-2 border-black h-8 w-8 text-black flex justify-center items-center rounded"
                    >
                      {{ item.quantity }}
                    </p> -->
                                        <input type="number" v-model="item.quantity" min="0"
                                            class="bg-[#D9D9D9] border-2 border-black h-8 w-24 text-black flex justify-center items-center rounded text-center" />
                                        <p @click="decrementQuantity(item.id)"
                                            class="flex items-center justify-center w-8 h-8 text-white bg-black rounded cursor-pointer">
                                            <i class="ri-subtract-line"></i>
                                        </p>
                                    </div>
                                    <div class="flex items-center justify-center">
                                        <div>
                                            <p @click="applyDiscount(item.id)" v-if="
                                                item.discount &&
                                                item.discount > 0 &&
                                                item.apply_discount == false &&
                                                !appliedCoupon
                                            "
                                                class="cursor-pointer py-1 text-center px-4 bg-green-600 rounded-xl font-bold text-white tracking-wider">
                                                Apply {{ item.discount }}% off
                                            </p>

                                            <p v-if="
                                                item.discount &&
                                                item.discount > 0 &&
                                                item.apply_discount == true &&
                                                !appliedCoupon
                                            " @click="removeDiscount(item.id)"
                                                class="cursor-pointer py-1 text-center px-4 bg-red-600 rounded-xl font-bold text-white tracking-wider">
                                                Remove {{ item.discount }}% Off
                                            </p>
                                            <p class="text-2xl font-bold text-black text-right">
                                                {{ item.selling_price }}
                                                LKR
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-end w-1/6">
                                <p @click="removeProduct(item.id)"
                                    class="text-3xl text-black border-2 border-black rounded-full cursor-pointer">
                                    <i class="ri-close-line"></i>
                                </p>
                            </div>
                        </div>
                        <div class="w-full pt-6 space-y-2">
                            <div class="flex items-center justify-between w-full px-8">
                                <p class="text-xl">Sub Total</p>
                                <p class="text-xl">{{ subtotal }} LKR</p>
                            </div>
                            <div class="flex items-center justify-between w-full px-8 py-2 pb-4 border-b border-black">
                                <p class="text-xl">Discount</p>
                                <p class="text-xl">( {{ totalDiscount }} LKR )</p>
                            </div>

                            <div class="flex items-center justify-between w-full px-8 pt-4 pb-4 border-b border-black rounded-lg transition focus-within:bg-blue-50 focus-within:ring-2 focus-within:ring-blue-400">
                                <p class="text-xl text-black">Custom Discount</p>
                                <span class="flex items-center">
                                    <CurrencyInput ref="customDiscountRef" v-model="custom_discount" @blur="validateCustomDiscount"
                                        placeholder="Enter value" class=" rounded-md px-2 py-1 text-black text-md"
                                        @keydown.down.prevent="paymentCashRef?.focus()"
                                        @keydown.up.prevent="cashInputRef?.focus()" />
                                    <select v-model="custom_discount_type"
                                        class="ml-2 px-8 border-black rounded-md text-black py-1 text-md">
                                        <option value="percent">%</option>
                                        <option value="fixed">Rs</option>
                                    </select>
                                </span>
                            </div>

                            <!-- Rental-specific fields: Rent Now / Rent Later -->
                            <div v-if="hasRentalItems" class="w-full px-8 pt-4 pb-4 border-b border-black space-y-4">
                                <!-- Rent Now / Rent Later Toggle -->
                                <div v-if="!isBookedImport" class="flex items-center justify-center space-x-4">
                                    <button @click="rentalMode = 'rent_now'" :class="[
                                        'px-6 py-3 text-lg font-bold rounded-lg transition shadow-sm',
                                        rentalMode === 'rent_now'
                                            ? 'bg-green-600 text-white'
                                            : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                                    ]">
                                        <i class="ri-shopping-cart-line mr-2"></i> Rent Now
                                    </button>
                                    <button @click="rentalMode = 'rent_later'" :class="[
                                        'px-6 py-3 text-lg font-bold rounded-lg transition shadow-sm',
                                        rentalMode === 'rent_later'
                                            ? 'bg-sky-500 text-white'
                                            : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                                    ]">
                                        <i class="ri-calendar-todo-line mr-2"></i> Rent Later
                                    </button>
                                </div>

                                <!-- Booked Import Info -->
                                <div v-if="isBookedImport" class="bg-sky-50 border border-sky-300 rounded-lg p-3 text-center">
                                    <p class="text-lg font-bold text-sky-700"><i class="ri-bookmark-line mr-1"></i> Booked Item Import</p>
                                    <p class="text-sm text-sky-600">Booking ID: {{ bookedImportData.booking_order_id }} | Customer: {{ bookedImportData.customer_name }}</p>
                                </div>

                                <div class="flex items-center space-x-2">
                                    <span class="inline-block w-3 h-3 rounded-full" :class="rentalMode === 'rent_now' ? 'bg-green-600' : 'bg-sky-500'"></span>
                                    <p class="text-lg font-bold" :class="rentalMode === 'rent_now' ? 'text-green-700' : 'text-sky-700'">{{ rentalMode === 'rent_now' ? 'Rent Now - Period & Deposit' : 'Rent Later - Period & Advance' }}</p>
                                </div>
                                <div class="flex items-center justify-between space-x-4">
                                    <div class="flex flex-col flex-1">
                                        <label class="text-sm font-semibold text-gray-600 mb-1">From</label>
                                        <input type="date" v-model="rentalDateFrom"
                                            class="border border-gray-300 rounded-lg px-4 py-2 text-black focus:ring-2 focus:ring-purple-500 focus:border-purple-500" />
                                    </div>
                                    <div class="flex flex-col flex-1">
                                        <label class="text-sm font-semibold text-gray-600 mb-1">To</label>
                                        <input type="date" v-model="rentalDateTo"
                                            class="border border-gray-300 rounded-lg px-4 py-2 text-black focus:ring-2 focus:ring-purple-500 focus:border-purple-500" />
                                    </div>
                                </div>

                                <!-- Rent Now: Deposit -->
                                <template v-if="rentalMode === 'rent_now'">
                                    <div class="flex items-center justify-between">
                                        <p class="text-xl text-black">Deposit</p>
                                        <span class="flex items-center">
                                            <CurrencyInput v-model="depositAmount"
                                                placeholder="Enter deposit" class="rounded-md px-2 py-1 text-black text-md" />
                                            <span class="ml-2">LKR</span>
                                        </span>
                                    </div>
                                    <div class="bg-green-50 rounded-lg px-4 py-2 space-y-1">
                                        <div class="flex items-center justify-between">
                                            <p class="text-md text-gray-700">Item Value</p>
                                            <p class="text-md font-bold text-black">{{ subtotal }} LKR</p>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <p class="text-md text-gray-700">Deposit</p>
                                            <p class="text-md font-bold text-black">{{ Number(depositAmount || 0).toFixed(2) }} LKR</p>
                                        </div>
                                        <div class="flex items-center justify-between border-t border-green-300 pt-1">
                                            <p class="text-lg font-semibold text-green-700">Total to Pay</p>
                                            <p class="text-lg font-bold text-green-800">{{ rentNowTotal }} LKR</p>
                                        </div>
                                    </div>
                                </template>

                                <!-- Rent Later: Customer Name & Advance -->
                                <template v-if="rentalMode === 'rent_later' && !isBookedImport">
                                    <div class="flex items-center justify-between">
                                        <p class="text-xl text-black">Customer Name</p>
                                        <input v-model="rentLaterCustomerName" type="text" placeholder="Enter customer name"
                                            class="border border-gray-300 rounded-lg px-4 py-2 text-black focus:ring-2 focus:ring-sky-500 w-64" />
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <p class="text-xl text-black">Advance Amount</p>
                                        <span class="flex items-center">
                                            <CurrencyInput v-model="advanceAmount"
                                                placeholder="Enter advance" class="rounded-md px-2 py-1 text-black text-md" />
                                            <span class="ml-2">LKR</span>
                                        </span>
                                    </div>
                                    <div v-if="advanceAmount > 0" class="flex items-center justify-between bg-sky-50 rounded-lg px-4 py-2">
                                        <p class="text-lg font-semibold text-sky-700">Remaining After Advance</p>
                                        <p class="text-lg font-bold text-sky-800">{{ remainingAfterAdvance }} LKR</p>
                                    </div>
                                </template>

                                <!-- Booked Import: Show advance already paid & deposit -->
                                <template v-if="isBookedImport">
                                    <div class="flex items-center justify-between bg-orange-50 rounded-lg px-4 py-2">
                                        <p class="text-lg font-semibold text-orange-700">Advance Already Paid</p>
                                        <p class="text-lg font-bold text-orange-800">{{ Number(advanceAmount || 0).toFixed(2) }} LKR</p>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <p class="text-xl text-black">Deposit</p>
                                        <span class="flex items-center">
                                            <CurrencyInput v-model="depositAmount"
                                                placeholder="Enter deposit" class="rounded-md px-2 py-1 text-black text-md" />
                                            <span class="ml-2">LKR</span>
                                        </span>
                                    </div>
                                    <div class="bg-sky-50 rounded-lg px-4 py-2 space-y-1">
                                        <div class="flex items-center justify-between">
                                            <p class="text-md text-gray-700">Item Value</p>
                                            <p class="text-md font-bold text-black">{{ subtotal }} LKR</p>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <p class="text-md text-gray-700">Advance Paid</p>
                                            <p class="text-md font-bold text-green-600">- {{ Number(advanceAmount || 0).toFixed(2) }} LKR</p>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <p class="text-md text-gray-700">Deposit</p>
                                            <p class="text-md font-bold text-black">{{ Number(depositAmount || 0).toFixed(2) }} LKR</p>
                                        </div>
                                        <div class="flex items-center justify-between border-t border-sky-300 pt-1">
                                            <p class="text-lg font-semibold text-sky-700">Total to Pay</p>
                                            <p class="text-lg font-bold text-sky-800">{{ bookedImportTotal }} LKR</p>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <div class="flex items-center justify-between w-full px-8 pt-4 pb-4 border-b border-black rounded-lg transition focus-within:bg-blue-50 focus-within:ring-2 focus-within:ring-blue-400">
                                <p class="text-xl text-black">Cash</p>
                                <span>
                                    <CurrencyInput ref="cashInputRef" v-model="cash" :options="{ currency: 'EUR' }"
                                        @keydown.down.prevent="customDiscountRef?.focus()"
                                        @keydown.up.prevent="cashInputRef?.focus()" />
                                    <span class="ml-2">LKR</span>
                                </span>
                            </div>
                            <div class="flex items-center justify-between w-full px-8 pt-4">
                                <p class="text-3xl text-black">Total</p>
                                <p class="text-3xl text-black">{{ total }} LKR</p>
                            </div>


                            <div class="flex items-center justify-between w-full px-8 pt-4 pb-4 border-b border-black">
                                <p class="text-xl text-black">Balance</p>
                                <p>{{ balance }} LKR</p>
                            </div>
                        </div>

                        <div class="w-full my-5">
                            <div class="relative flex items-center">
                                <!-- Input Field -->
                                <label for="coupon" class="sr-only">Coupon Code</label>
                                <input id="coupon" v-model="couponForm.code" type="text" placeholder="Enter Coupon Code"
                                    class="w-full h-16 px-6 pr-40 text-lg text-gray-800 placeholder-gray-500 border-2 border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />

                                <template v-if="!appliedCoupon">
                                    <button type="button" @click="submitCoupon"
                                        class="absolute right-2 top-2 h-12 px-6 text-lg font-semibold text-white uppercase bg-blue-600 rounded-full hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        Apply Coupon
                                    </button>
                                </template>
                                <template v-else>
                                    <button type="button" @click="removeCoupon"
                                        class="absolute right-2 top-2 h-12 px-6 text-lg font-semibold text-white uppercase bg-red-600 rounded-full hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                        Remove Coupon
                                    </button>
                                </template>
                            </div>
                        </div>

                        <div class="flex flex-col w-full space-y-8">
                            <div class="flex items-center justify-center w-full pt-8 space-x-8">
                                <p class="text-xl text-black">Payment Method :</p>
                                <div ref="paymentCashRef" tabindex="0"
                                    @click="selectedPaymentMethod = 'cash'"
                                    @keydown.space.prevent="selectedPaymentMethod = 'cash'"
                                    @keydown.enter.prevent="selectedPaymentMethod = 'cash'"
                                    @keydown.right.prevent="paymentCardRef?.focus()"
                                    @keydown.down.prevent="confirmOrderRef?.focus()"
                                    @keydown.up.prevent="customDiscountRef?.focus()"
                                    :class="[
                                    'cursor-pointer w-[100px] border-2 border-black rounded-xl flex flex-col justify-center items-center text-center transition focus:outline-none focus:ring-4 focus:ring-offset-2 focus:ring-blue-500 focus:scale-105',
                                    selectedPaymentMethod === 'cash'
                                        ? 'bg-yellow-400 border-yellow-600 shadow-md font-bold'
                                        : 'text-black hover:bg-gray-100',
                                ]">
                                    <img src="/images/money-stack.png" alt="Cash" class="w-24" />
                                </div>
                                <div ref="paymentCardRef" tabindex="0"
                                    @click="selectedPaymentMethod = 'card'"
                                    @keydown.space.prevent="selectedPaymentMethod = 'card'"
                                    @keydown.enter.prevent="selectedPaymentMethod = 'card'"
                                    @keydown.left.prevent="paymentCashRef?.focus()"
                                    @keydown.down.prevent="confirmOrderRef?.focus()"
                                    @keydown.up.prevent="customDiscountRef?.focus()"
                                    :class="[
                                    'cursor-pointer w-[100px] border-2 border-black rounded-xl flex flex-col justify-center items-center text-center transition focus:outline-none focus:ring-4 focus:ring-offset-2 focus:ring-blue-500 focus:scale-105',
                                    selectedPaymentMethod === 'card'
                                        ? 'bg-yellow-400 border-yellow-600 shadow-md font-bold'
                                        : 'text-black hover:bg-gray-100',
                                ]">
                                    <img src="/images/bank-card.png" alt="Card" class="w-24" />
                                </div>
                            </div>

                            <div class="flex items-center justify-center w-full">
                                <button ref="confirmOrderRef" @click="() => { submitOrder(); }" @keydown.enter.prevent="submitOrder"
                                    @keydown.up.prevent="paymentCashRef?.focus()"
                                    type="button" :disabled="products.length === 0" :class="[
                                        'w-full py-4 text-2xl font-bold tracking-wider text-center text-white uppercase rounded-xl transition focus:outline-none focus:ring-4 focus:ring-offset-2 focus:ring-green-400',
                                        products.length === 0
                                            ? 'bg-gray-400 cursor-not-allowed'
                                            : 'bg-green-600 hover:bg-green-700 cursor-pointer',
                                    ]">
                                    <i class="pr-4 ri-add-circle-fill"></i> Confirm Order
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <PosSuccessModel :open="isSuccessModalOpen" @update:open="handleModalOpenUpdate" :products="products"
        :employee="employee" :cashier="loggedInUser" :customer="customer" :orderid="orderid" :cash="cash"
        :balance="balance" :subTotal="subtotal" :totalDiscount="totalDiscount" :total="total"
        :custom_discount_type="custom_discount_type"
        :custom_discount="custom_discount"
        :rentalDateFrom="rentalDateFrom"
        :rentalDateTo="rentalDateTo"
        :advanceAmount="advanceAmount"
        :depositAmount="depositAmount"
        :rentalMode="rentalMode"
        :isBookedImport="isBookedImport"
        :hasRentalItems="hasRentalItems" />
    <AlertModel v-model:open="isAlertModalOpen" :message="message" />

    <SelectProductModel v-model:open="isSelectModalOpen" :allcategories="allcategories" :colors="colors" :sizes="sizes"
        @selected-products="handleSelectedProducts" />

    <SelectRentalItemModel v-model:open="isSelectRentalItemModalOpen" :allcategories="allcategories" :colors="colors"
        @selected-rental-items="handleSelectedRentalItems" />

    <ReturnRentalModel v-model:open="isReturnRentalModalOpen" />

    <BookedItemsModel v-model:open="isBookedItemsModalOpen"
        @import-booked-item="handleImportBookedItem" />
    <Footer />
</template>
<script setup>
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import Banner from "@/Components/Banner.vue";
import PosSuccessModel from "@/Components/custom/PosSuccessModel.vue";
import AlertModel from "@/Components/custom/AlertModel.vue";
import { useForm, router } from "@inertiajs/vue3";
import { ref, onMounted, computed, watch, nextTick } from "vue";
import { Head } from "@inertiajs/vue3";
import { Link } from "@inertiajs/vue3";
import axios from "axios";
import CurrencyInput from "@/Components/custom/CurrencyInput.vue";
import SelectProductModel from "@/Components/custom/SelectProductModel.vue";
import SelectRentalItemModel from "@/Components/custom/SelectRentalItemModel.vue";
import ReturnRentalModel from "@/Components/custom/ReturnRentalModel.vue";
import BookedItemsModel from "@/Components/custom/BookedItemsModel.vue";
import ProductAutoComplete from "@/Components/custom/ProductAutoComplete.vue";
import { generateOrderId } from "@/Utils/Other.js";

const product = ref(null);
const error = ref(null);
const products = ref([]);
const isSuccessModalOpen = ref(false);
const isAlertModalOpen = ref(false);
const message = ref("");
const appliedCoupon = ref(null);
const cash = ref(0);
const custom_discount = ref(0);
const isSelectModalOpen = ref(false);
const isSelectRentalItemModalOpen = ref(false);
const isReturnRentalModalOpen = ref(false);
const isBookedItemsModalOpen = ref(false);
const custom_discount_type = ref('fixed');
const rentalDateFrom = ref('');
const rentalDateTo = ref('');
const advanceAmount = ref(0);
const depositAmount = ref(0);
const rentalMode = ref('rent_now');
const rentLaterCustomerName = ref('');
const isBookedImport = ref(false);
const bookedImportData = ref({});
const orderid = computed(() => generateOrderId());


// const balance = ref(0);

const handleModalOpenUpdate = (newValue) => {
    isSuccessModalOpen.value = newValue;
    if (!newValue) {
        refreshData();
    }
};

const props = defineProps({
    loggedInUser: Object, // Using backend product name to avoid messing with selected products
    allcategories: Array,
    allemployee: Array,
    colors: Array,
    sizes: Array,
});

const discount = ref(0);

const customer = ref({
    name: "",
    countryCode: "",
    contactNumber: "",
    email: "",
});

const employee_id = ref("");

const selectedPaymentMethod = ref("cash");

const refreshData = () => {
    router.visit(route("pos.index"), {
        preserveScroll: false, // Reset scroll
        preserveState: false, // Reset component state
    });
};

const removeProduct = (id) => {
    products.value = products.value.filter((item) => item.id !== id);
};

const removeCoupon = () => {
    appliedCoupon.value = null; // Clear the applied coupon
    couponForm.code = ""; // Clear the coupon field
};

const incrementQuantity = (id) => {
    const product = products.value.find((item) => item.id === id);
    if (product) {
        product.quantity += 1;
    }
};

const decrementQuantity = (id) => {
    const product = products.value.find((item) => item.id === id);
    if (product && product.quantity > 1) {
        product.quantity -= 1;
    }
};

// const orderId = computed(() => {
//   const timestamp = Date.now().toString(36).toUpperCase(); // Convert timestamp to a base-36 string
//   const randomString = Math.random().toString(36).substr(2, 5).toUpperCase(); // Generate a shorter random string
//   return `ORD-${timestamp}-${randomString}`; // Combine to create unique order ID
// });
const orderId = computed(() => {
    const characters =
        "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    return Array.from({ length: 6 }, () =>
        characters.charAt(Math.floor(Math.random() * characters.length))
    ).join("");
});

const submitOrder = async () => {
    console.log(products.value);

    // Validation for rental items
    if (hasRentalItems.value) {
        if (!rentalDateFrom.value || !rentalDateTo.value) {
            isAlertModalOpen.value = true;
            message.value = "Please select both 'From' and 'To' dates for the rental period.";
            return;
        }
        if (new Date(rentalDateTo.value) < new Date(rentalDateFrom.value)) {
            isAlertModalOpen.value = true;
            message.value = "'To' date cannot be before 'From' date.";
            return;
        }

        // Rent Later: save as booking instead of sale
        if (rentalMode.value === 'rent_later' && !isBookedImport.value) {
            if (!rentLaterCustomerName.value) {
                isAlertModalOpen.value = true;
                message.value = "Please enter the customer name for the booking.";
                return;
            }
            if (!advanceAmount.value || parseFloat(advanceAmount.value) <= 0) {
                isAlertModalOpen.value = true;
                message.value = "Please enter the advance amount for the booking.";
                return;
            }
            await submitBooking();
            return;
        }

        // Rent Now: validate deposit
        if (rentalMode.value === 'rent_now') {
            if (!depositAmount.value || parseFloat(depositAmount.value) <= 0) {
                isAlertModalOpen.value = true;
                message.value = "Please enter the deposit amount.";
                return;
            }
        }
    }

    if (balance.value < 0) {
        isAlertModalOpen.value = true;
        message.value = "Cash is not enough";
        return;
    }

    try {
        const response = await axios.post("/pos/submit", {
            customer: customer.value,
            products: products.value,
            employee_id: employee_id.value,
            paymentMethod: selectedPaymentMethod.value,
            userId: props.loggedInUser.id,
            orderid: orderid.value,
            cash: cash.value,
            custom_discount: custom_discount.value,
            rental_date_from: rentalDateFrom.value || null,
            rental_date_to: rentalDateTo.value || null,
            advance_amount: advanceAmount.value || 0,
            deposit: depositAmount.value || 0,
            rental_type: hasRentalItems.value ? (isBookedImport.value ? 'rent_later' : rentalMode.value) : null,
            booking_id: bookedImportData.value?.id || null,
        });
        isSuccessModalOpen.value = true;
        console.log(response.data);
    } catch (error) {
        if (error.response?.status === 423) {
            isAlertModalOpen.value = true;
            message.value = error.response.data.message;
        }
        console.error(
            "Error submitting order:",
            error.response?.data || error.message
        );
    }
};

// Submit booking for Rent Later
const submitBooking = async () => {
    try {
        const rentalItems = products.value.filter(p => p.is_rental);
        const items = rentalItems.map(p => ({
            rental_item_id: p.original_rental_id || parseInt(String(p.id).replace('rental_', '')),
            quantity: p.quantity,
            unit_price: p.selling_price,
        }));

        const response = await axios.post("/api/rental-bookings", {
            customer_name: rentLaterCustomerName.value,
            items: items,
            rental_date_from: rentalDateFrom.value,
            rental_date_to: rentalDateTo.value,
            advance_amount: advanceAmount.value,
        });

        // Print booking receipt
        printBookingReceipt(response.data);

        isAlertModalOpen.value = true;
        message.value = "Booking created successfully! Booking ID: " + response.data.booking_order_id;

        // Reset
        refreshData();
    } catch (error) {
        if (error.response?.status === 423) {
            isAlertModalOpen.value = true;
            message.value = error.response.data.message;
        } else {
            isAlertModalOpen.value = true;
            message.value = "Failed to create booking. Please try again.";
        }
        console.error("Error creating booking:", error.response?.data || error.message);
    }
};
// };

const subtotal = computed(() => {
    return products.value
        .reduce(
            (total, item) => total + parseFloat(item.selling_price) * item.quantity,
            0
        )
        .toFixed(2); // Ensures two decimal places
});

const totalDiscount = computed(() => {
    const productDiscount = products.value.reduce((total, item) => {
        // Check if item has a discount
        if (item.discount && item.discount > 0 && item.apply_discount == true) {
            const discountAmount =
                (parseFloat(item.selling_price) - parseFloat(item.discounted_price)) *
                item.quantity;
            return total + discountAmount;
        }
        return total; // If no discount, return total as-is
    }, 0); // Ensures two decimal places

    const couponDiscount = appliedCoupon.value
        ? Number(appliedCoupon.value.discount)
        : 0;

    return (productDiscount + couponDiscount).toFixed(2);
});

const validateCustomDiscount = () => {
    if (!custom_discount.value || isNaN(custom_discount.value)) {
        custom_discount.value = 0; // Set default to 0 if the field is empty or invalid
    }
};

const hasRentalItems = computed(() => {
    return products.value.some((item) => item.is_rental === true);
});

const rentNowTotal = computed(() => {
    const sub = parseFloat(subtotal.value) || 0;
    const dep = parseFloat(depositAmount.value) || 0;
    return (sub + dep).toFixed(2);
});

const bookedImportTotal = computed(() => {
    const sub = parseFloat(subtotal.value) || 0;
    const adv = parseFloat(advanceAmount.value) || 0;
    const dep = parseFloat(depositAmount.value) || 0;
    return (sub - adv + dep).toFixed(2);
});

const total = computed(() => {
    const subtotalValue = parseFloat(subtotal.value) || 0;
    const discountValue = parseFloat(totalDiscount.value) || 0;
    const customDiscount = parseFloat(custom_discount.value) || 0;

    let customValue = 0;

    if (custom_discount_type.value === 'percent') {
        customValue = (subtotalValue * customDiscount) / 100;
    } else if (custom_discount_type.value === 'fixed') {
        customValue = customDiscount;
    }

    let totalBeforeAdvance = subtotalValue - discountValue - customValue;

    if (hasRentalItems.value) {
        const advance = parseFloat(advanceAmount.value) || 0;
        const deposit = parseFloat(depositAmount.value) || 0;

        if (rentalMode.value === 'rent_now') {
            // Rent Now: total = item value + deposit
            return (totalBeforeAdvance + deposit).toFixed(2);
        } else if (isBookedImport.value) {
            // Booked import: total = item value - advance + deposit
            return (totalBeforeAdvance - advance + deposit).toFixed(2);
        } else if (rentalMode.value === 'rent_later') {
            // Rent Later: just show advance (what customer pays now)
            return advance.toFixed(2);
        }
    }

    return totalBeforeAdvance.toFixed(2);
});

const remainingAfterAdvance = computed(() => {
    const subtotalValue = parseFloat(subtotal.value) || 0;
    const discountValue = parseFloat(totalDiscount.value) || 0;
    const customDiscount = parseFloat(custom_discount.value) || 0;

    let customValue = 0;
    if (custom_discount_type.value === 'percent') {
        customValue = (subtotalValue * customDiscount) / 100;
    } else if (custom_discount_type.value === 'fixed') {
        customValue = customDiscount;
    }

    const totalBeforeAdvance = subtotalValue - discountValue - customValue;
    const advance = parseFloat(advanceAmount.value) || 0;
    return (totalBeforeAdvance - advance).toFixed(2);
});

const balance = computed(() => {
    if (cash.value == null || cash.value === 0) {
        return 0; // If cash.value is null or 0, return 0
    }
    return (parseFloat(cash.value) - parseFloat(total.value)).toFixed(2);
});
// Check for product or handle errors
const form = useForm({
    employee_id: "",
    barcode: "", // Form field for barcode
});

const couponForm = useForm({
    code: "",
});

// Temporary barcode storage during scanning
let barcode = "";
let timeout; // Timeout to detect the end of the scan

const submitCoupon = async () => {
    try {
        const response = await axios.post(route("pos.getCoupon"), {
            code: couponForm.code, // Send the coupon field
        });

        const { coupon: fetchedCoupon, error: fetchedError } = response.data;

        if (fetchedCoupon) {
            appliedCoupon.value = fetchedCoupon;
            products.value.forEach((product) => {
                product.apply_discount = false;
            });
        } else {
            isAlertModalOpen.value = true;
            message.value = fetchedError;
            error.value = fetchedError;
        }
    } catch (err) {
        // console.error(error);
        if (err.response.status === 422) {
            isAlertModalOpen.value = true;
            message.value = err.response.data.message;
        }
    }
};

// Automatically submit the barcode to the backend
const submitBarcode = async () => {
    try {
        // Send POST request to the backend
        const response = await axios.post(route("pos.getProduct"), {
            barcode: form.barcode, // Send the barcode field
        });

        // Extract the response data
        const { product: fetchedProduct, type: fetchedType, error: fetchedError } = response.data;

        if (fetchedProduct) {
            if (fetchedType === "rental") {
                const posFormattedItem = {
                    id: "rental_" + fetchedProduct.id,
                    original_rental_id: fetchedProduct.id,
                    name: fetchedProduct.item_name + " (Rental)",
                    selling_price: fetchedProduct.rent_price,
                    image: fetchedProduct.image,
                    is_rental: true,
                    stock_quantity: fetchedProduct.rental_quantity,
                };

                if (posFormattedItem.stock_quantity < 1) {
                    isAlertModalOpen.value = true;
                    message.value = "Rental item is out of stock";
                    return;
                }

                const existingRentalItem = products.value.find(
                    (item) => item.id === posFormattedItem.id
                );

                if (existingRentalItem) {
                    if (existingRentalItem.quantity < posFormattedItem.stock_quantity) {
                        existingRentalItem.quantity += 1;
                    } else {
                        isAlertModalOpen.value = true;
                        message.value = `Cannot exceed available rental quantity (${posFormattedItem.stock_quantity}) for this item.`;
                    }
                } else {
                    products.value.push({
                        ...posFormattedItem,
                        quantity: 1,
                        apply_discount: false,
                    });
                }
            } else {
                if (fetchedProduct.stock_quantity < 1) {
                    isAlertModalOpen.value = true;
                    message.value = "Product is out of stock";
                    return;
                }

                // Check if the product already exists in the products array
                const existingProduct = products.value.find(
                    (item) => item.id === fetchedProduct.id
                );

                if (existingProduct) {
                    // If it exists, increment the quantity
                    existingProduct.quantity += 1;
                } else {
                    // If it doesn't exist, add it to the products array with quantity 1
                    products.value.push({
                        ...fetchedProduct,
                        quantity: 1,
                        apply_discount: false, // Add the new attribute
                    });
                }
            }

            product.value = fetchedProduct; // Update product state for individual display
            error.value = null; // Clear any previous errors
            console.log("Item fetched successfully and added to cart:", fetchedProduct);
        } else {
            isAlertModalOpen.value = true;
            message.value = fetchedError;
            error.value = fetchedError; // Set the error message
            console.error("Error:", fetchedError);
        }
    } catch (err) {
        if (err.response.status === 422) {
            isAlertModalOpen.value = true;
            message.value = err.response.data.message;
        }

        console.error("An error occurred:", err.response?.data || err.message);
        error.value = "An unexpected error occurred. Please try again.";
    }
};

// Handle input from the barcode scanner
let lastKeyTime = 0;
const handleScannerInput = (event) => {
    // If user is typing directly in the barcode input, let it work normally
    if (event.target.id === 'search') return;

    const now = Date.now();
    const timeDiff = now - lastKeyTime;
    lastKeyTime = now;

    // Detect scanner: rapid keypresses (< 50ms apart) or we are mid-scan
    const isScan = timeDiff < 50 || barcode.length > 0;
    if (isScan) {
        // Prevent characters from being typed into whichever input is focused (e.g. cash)
        event.preventDefault();
    }

    clearTimeout(timeout); // Clear the timeout for each keypress
    if (event.key === "Enter") {
        // Barcode scanning completed — blur any focused element so no stray
        // characters from the next scan land in a visible input field.
        if (document.activeElement && document.activeElement !== document.body) {
            document.activeElement.blur();
        }
        form.barcode = barcode; // Set the scanned barcode into the form
        submitBarcode().finally(() => {
            // Refocus cash input after the product lookup finishes so the
            // cashier can enter the cash amount with the keyboard.
            nextTick(() => cashInputRef.value?.focus());
        });
        barcode = ""; // Reset the barcode for the next scan
    } else {
        // Append the pressed key to the barcode
        barcode += event.key;
    }

    // Timeout to reset the barcode if scanning is interrupted
    timeout = setTimeout(() => {
        barcode = "";
    }, 100); // Adjust timeout based on scanner speed
};

// Attach the keypress event listener when the component is mounted
const cashInputRef = ref(null);
const customDiscountRef = ref(null);
const paymentCashRef = ref(null);
const paymentCardRef = ref(null);
const confirmOrderRef = ref(null);

onMounted(() => {
    document.addEventListener("keypress", handleScannerInput);
    console.log(props.products);
});

const applyDiscount = (id) => {
    products.value.forEach((product) => {
        if (product.id === id) {
            product.apply_discount = true;
        }
    });
};

const removeDiscount = (id) => {
    products.value.forEach((product) => {
        if (product.id === id) {
            product.apply_discount = false;
        }
    });
};

const handleSelectedProducts = (selectedProducts) => {
    selectedProducts.forEach((fetchedProduct) => {
        const existingProduct = products.value.find(
            (item) => item.id === fetchedProduct.id
        );

        if (existingProduct) {
            // If the product exists, increment its quantity
            existingProduct.quantity += 1;
        } else {
            // If the product doesn't exist, add it with a default quantity
            products.value.push({
                ...fetchedProduct,
                quantity: 1,
                apply_discount: false, // Default additional attribute
            });
        }
    });
};

const handleSelectedRentalItems = (selectedRentalItems) => {
    // Reset booked import state when adding new rental items
    isBookedImport.value = false;
    bookedImportData.value = {};

    selectedRentalItems.forEach((fetchedRentalItem) => {
        // Map rental item attributes to match what POS expects for a product
        const posFormattedItem = {
            id: 'rental_' + fetchedRentalItem.id, // Prefix ID to avoid collision with normal products
            original_rental_id: fetchedRentalItem.id,
            name: fetchedRentalItem.item_name + ' (Rental)',
            selling_price: fetchedRentalItem.rent_price,
            image: fetchedRentalItem.image,
            is_rental: true, // Flag to identify it during checkout
            stock_quantity: fetchedRentalItem.rental_quantity // For POS validation limits
        };

        const existingProduct = products.value.find(
            (item) => item.id === posFormattedItem.id
        );

        if (existingProduct) {
            // Check if we hit the rental quantity limit
            if (existingProduct.quantity < posFormattedItem.stock_quantity) {
                existingProduct.quantity += 1;
            } else {
                 isAlertModalOpen.value = true;
                 message.value = `Cannot exceed available rental quantity (${posFormattedItem.stock_quantity}) for this item.`;
            }
        } else {
            products.value.push({
                ...posFormattedItem,
                quantity: 1,
                apply_discount: false,
            });
        }
    });
};

// Handle import of booked items to POS
const handleImportBookedItem = (selectedBookings) => {
    // Clear existing products first
    products.value = [];

    selectedBookings.forEach((booking) => {
        const posFormattedItem = {
            id: 'rental_' + booking.rental_item_id,
            original_rental_id: booking.rental_item_id,
            name: (booking.rental_item?.item_name || 'Rental Item') + ' (Booked)',
            selling_price: booking.unit_price,
            image: booking.rental_item?.image,
            is_rental: true,
            stock_quantity: booking.quantity,
        };

        products.value.push({
            ...posFormattedItem,
            quantity: booking.quantity,
            apply_discount: false,
        });
    });

    // Set booked import state
    const firstBooking = selectedBookings[0];
    isBookedImport.value = true;
    rentalMode.value = 'rent_later';
    bookedImportData.value = firstBooking;
    rentalDateFrom.value = firstBooking.rental_date_from;
    rentalDateTo.value = firstBooking.rental_date_to;
    advanceAmount.value = parseFloat(firstBooking.advance_amount) || 0;
    depositAmount.value = 0;

    // Set customer name from booking
    customer.value.name = firstBooking.customer_name;
};

// Print booking receipt for Rent Later
const printBookingReceipt = (data) => {
    const itemRows = data.bookings.map(b => `
        <tr>
            <td>${b.rental_item?.item_name || 'Rental Item'}</td>
            <td style="text-align:center;">${b.quantity}</td>
            <td>${Number(b.total_price).toFixed(2)}</td>
        </tr>
    `).join('');

    const totalItemValue = data.bookings.reduce((s, b) => s + Number(b.total_price), 0);

    const receiptHTML = `
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Booking Receipt</title>
        <style>
            @media print { body { margin:0; padding:0 5mm 0 0; -webkit-print-color-adjust:exact; } }
            body { background:#fff; font-size:12px; font-family:'Arial',sans-serif; margin:0; padding:10px 5mm 10mm 7mm; color:#000; }
            .header { text-align:center; margin-bottom:16px; }
            .header h1 { font-size:20px; font-weight:bold; margin:0; }
            .header p { font-size:12px; margin:4px 0; }
            .section { margin-bottom:16px; padding-top:8px; border-top:1px solid #000; }
            .info-row { display:flex; justify-content:space-between; font-size:14px; margin-top:8px; }
            .info-row p { margin:0; font-weight:bold; }
            .info-row small { font-weight:normal; }
            table { width:100%; font-size:12px; border-collapse:collapse; margin-top:8px; }
            table th,table td { padding:6px 8px; }
            table th { text-align:left; }
            table td { text-align:right; }
            table td:first-child { text-align:left; }
            .totals { border-top:1px solid #000; padding-top:8px; font-size:12px; }
            .totals div { display:flex; justify-content:space-between; margin-bottom:8px; }
            .footer { text-align:center; font-size:10px; margin-top:16px; }
            .footer p { margin:6px 0; }
        </style>
    </head>
    <body>
        <div class="receipt-container">
            <div class="header">
                <img src="/images/billlogo.png" style="width:230px;height:100px;" />
                <p>No 51/1/1,Mahabage road, Ragama</p>
                <p>0756865900</p>
                <p style="font-weight:bold;font-size:14px;margin-top:8px;border:1px solid #000;display:inline-block;padding:2px 12px;">RENTAL BOOKING RECEIPT</p>
            </div>
            <div class="section">
                <div class="info-row">
                    <div><p>Date:</p><small>${new Date().toLocaleDateString()}</small></div>
                    <div><p>Booking ID:</p><small>${data.booking_order_id}</small></div>
                </div>
                <div class="info-row">
                    <div><p>Customer:</p><small>${data.customer_name}</small></div>
                </div>
            </div>
            <div class="section">
                <table>
                    <colgroup><col style="width:60%;"><col style="width:15%;"><col style="width:25%;"></colgroup>
                    <thead><tr><th>Items</th><th style="text-align:center;">Qty</th><th style="text-align:right;">Price</th></tr></thead>
                    <tbody style="font-size:11px;">${itemRows}</tbody>
                </table>
            </div>
            <div class="totals">
                <div><span>Item Total</span><span>${totalItemValue.toFixed(2)} LKR</span></div>
                <div><span>Rental Period</span><span>${data.rental_date_from} to ${data.rental_date_to}</span></div>
                <div style="font-weight:bold;font-size:14px;"><span>Advance Paid</span><span>${Number(data.advance_amount).toFixed(2)} LKR</span></div>
                <div><span>Remaining Balance</span><span>${(totalItemValue - Number(data.advance_amount)).toFixed(2)} LKR</span></div>
            </div>
            <div style="font-size:9px;font-style:italic;border-top:1px dashed #000;padding-top:6px;margin-top:8px;line-height:1.6;display:flex;flex-direction:column;text-align:left;">
                <span style="margin:1px 0;display:block;">★ ගිවිසගත් දිනයට පෙර භාරගත් අයිතමය භාර නොදී සිටීමෙන්, අදාළ දිනය ඉක්මවා ඇති එක් දිනක් සඳහා රු. 200 ක මුදලක් අමතරව අය කෙරේ.</span>
                <span style="margin:1px 0;display:block;">★ භාරගත් අයිතමයට යම් හානියක් සිදුවී ඇත්නම්, ඊට අදාළ අලාභය තැන්පතු මුදලින් අය කරගනු ලැබේ.</span>
                <span style="margin:1px 0;display:block;">★ අත්තිකාරම් මුදලක් ගෙවා භාරගත් අයිතමය ගනුදෙනුව හදිසියේ අවසන් කර භාරදෙන්නේ නම්, කිසිම හේතුවක් මත අත්තිකාරම් මුදල ආපසු ගෙවනු නොලැබේ.</span>
            </div>
            <div class="footer">
                <p>THANK YOU!</p>
                <p style="font-weight:bold;">Powered by JAAN Network Ltd.</p>
                <p>${new Date().toLocaleTimeString()}</p>
            </div>
        </div>
    </body>
    </html>
    `;

    const printWindow = window.open('', '_blank');
    if (!printWindow) { alert('Failed to open print window.'); return; }
    printWindow.document.open();
    printWindow.document.write(receiptHTML);
    printWindow.document.close();
    printWindow.onload = () => { 
        printWindow.focus(); 
        printWindow.print(); 
        // Delay closing to allow print to complete
        setTimeout(() => { printWindow.close(); }, 1000);
    };
};

// const searchTerm = ref(form.barcode);

// // Computed property for filtered product results
// const searchResults = computed(() => {
//   if (searchTerm.value === "") {
//     return [];
//   }

//   let matches = 0;

//   return props.products.filter((product) => {
//     if (
//       product.name.toLowerCase().includes(searchTerm.value.toLowerCase()) &&
//       matches < 10
//     ) {
//       matches++;
//       return product;
//     }
//   });
// });

// // Watch for changes in the form barcode field and update the search term
// watch(
//   () => form.barcode,
//   (newValue) => {
//     searchTerm.value = newValue;
//   }
// );

// // Method to select a product (or barcode)
// const selectProduct = (productName) => {
//   form.barcode = productName; // Set the selected product name to the barcode field
//   searchTerm.value = ""; // Clear the search term after selection
// };
</script>
