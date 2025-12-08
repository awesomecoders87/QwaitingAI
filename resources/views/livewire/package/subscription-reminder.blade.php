<div>
@if ($showPopup)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-lg p-6 shadow-lg w-full max-w-md text-center">
            <h2 class="text-xl font-semibold mb-4">Subscription Expired</h2>
            <p class="mb-4">Your subscription has expired. Please renew to continue using the platform.</p>
            <button wire:click="redirectToSubscription"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                Buy Subscription
            </button>
        </div>
    </div>
    @endif
</div>