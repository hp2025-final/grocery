<div x-data="{ show: false, payment: null }" x-show="show" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg relative p-6">
        <button @click="show = false" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
        <template x-if="payment">
            <div>
                <h3 class="text-lg font-bold mb-2">Vendor Payment Details</h3>
                <div class="mb-2"><span class="font-semibold">Payment #:</span> <span x-text="payment.payment_number"></span></div>
                <div class="mb-2"><span class="font-semibold">Date:</span> <span x-text="payment.payment_date"></span></div>
                <div class="mb-2"><span class="font-semibold">Vendor:</span> <span x-text="payment.vendor?.name"></span></div>
                <div class="mb-2"><span class="font-semibold">Amount:</span> <span x-text="payment.amount_paid"></span></div>
                <div class="mb-2"><span class="font-semibold">Bank:</span> <span x-text="payment.payment_account?.bank?.name ?? payment.payment_account?.name"></span></div>
                <div class="mb-2"><span class="font-semibold">Reference:</span> <span x-text="payment.reference"></span></div>
                <div class="mb-2"><span class="font-semibold">Notes:</span> <span x-text="payment.notes"></span></div>
                <!-- Add more fields as needed -->
            </div>
        </template>
    </div>
</div>
