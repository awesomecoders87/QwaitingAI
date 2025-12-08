<div class="px-6 py-4">
        @php  $datetimeFormat = App\Models\AccountSetting::showDateTimeFormat(); @endphp
    <!-- Header -->
    <div class="mb-4">
        <h1 class="text-xl font-semibold mb-4">Choose a Plan</h1>
        <div class="mt-2">
            <ul class="tabs-nav">
                <li>
                    <a href="javascript:void(0)" wire:click="setActiveTab('plans')"
                        class="px-4 py-2 rounded-md font-medium inline-block
                {{ $activeTab === 'plans' ? 'active-tab text-white' : 'bg-gray-100 text-black' }}">
                        Pricing & Plan
                    </a>
                </li>
                <li><a href="javascript:void(0)" wire:click="setActiveTab('current')"
                        class="px-4 py-2 rounded-md font-medium inline-block
                {{ $activeTab === 'current' ? 'active-tab text-white' : 'bg-gray-100 text-black' }}">
                        My Current Plan
                    </a></li>
                <li><a href="javascript:void(0)" wire:click="setActiveTab('invoice')"
                        class="px-4 py-2 rounded-md font-medium inline-block
                {{ $activeTab === 'invoice' ? 'active-tab text-white' : 'bg-gray-100 text-black' }}">
                       All Invoices
                    </a></li>
            </ul>
        </div>
    </div>
    @if ($activeTab === 'plans')

        <div x-data="{ monthly: true }">
            <!-- Toggle Switch -->
            <div class="mb-6 text-center">
                <div class="relative z-1 mx-auto inline-flex rounded-full bg-gray-200 p-1 dark:bg-gray-800">
                    <span :class="monthly ? 'translate-x-0' : 'translate-x-1/2'"
                        class="absolute top-1 -z-1 flex h-11 w-[120px] rounded-full bg-white shadow-theme-xs duration-200 ease-linear dark:bg-white/10 translate-x-0"></span>

                    <button @click="monthly = true" wire:click.prevent="changeType('monthly')"
                        :class="monthly ? 'text-gray-800 dark:text-white/90' : 'text-gray-500 hover:text-gray-700 dark:hover:text-white/70 dark:text-gray-400'"
                        class="flex h-11 w-[120px] items-center justify-center text-base font-medium text-gray-500 hover:text-gray-700 dark:hover:text-white/70 dark:text-gray-400">
                        Monthly
                    </button>
                    <button @click="monthly = false" wire:click.prevent="changeType('yearly')"
                        :class="!monthly ? 'text-gray-800 dark:text-white/90' : 'text-gray-500 hover:text-gray-700 dark:hover:text-white/80 dark:text-gray-400'"
                        class="flex h-11 w-[120px] items-center justify-center text-base font-medium text-gray-500 hover:text-gray-700 dark:hover:text-white/70 dark:text-gray-400">
                        Annually
                    </button>
                </div>
            </div>

            <!-- Plan Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6">
                @foreach ($packages as $package)
                    @php
                    $isSelected =false;
                    if(!empty($selectedPackageId)){

                        $isSelected = $selectedPackageId == $package->id;
                    }
                    $isCurrentActivePackage = $currentPackage && $currentPackage['package_id'] == $package->id && $currentPackage['status'] == 'active' && $currentPackage['unit'] == $billingCycle;
                    @endphp
                    <div
                        class="
                {{ $isSelected
                    ? 'relative rounded-2xl border p-7 flex flex-col transition duration-300 border-gray-800 bg-gray-800 text-white dark:border-white/10 dark:bg-white/10'
                    : 'relative rounded-2xl border p-7 flex flex-col transition duration-300 border-gray-200 bg-white text-gray-800 dark:border-gray-800 dark:bg-white/[0.03]' }}">
                        <span class= "{{ $isSelected
                    ? 'mb-3 block text-theme-xl font-semibold text-white'
                    : 'mb-3 block text-theme-xl font-semibold text-gray-800 dark:text-white/90' }}">{{ $package->name }}</span>

                          <div class="mb-1 flex items-center justify-between">
                        <div class="flex items-end">
                            <p class="text-2xl font-bold">
                                <h2 class="{{ $isSelected ? 'text-2xl font-bold text-white' : 'text-2xl font-bold text-gray-800 dark:text-white/90' }}"  x-show="monthly">{{ App\Models\Currency::where('currency_code',$package->currency)->value('currency_symbol'); }}{{ number_format($package->price, 0) }}</h2>
                                <h2 class="{{ $isSelected ? 'text-2xl font-bold text-white' : 'text-2xl font-bold text-gray-800 dark:text-white/90' }}" x-show="!monthly">{{ App\Models\Currency::where('currency_code',$package->currency)->value('currency_symbol'); }}{{ number_format($package->price_yearly, 0) }}</h2>
                            </p>
                            <span class="mb-1 inline-block text-sm dark:text-white/90" x-text="monthly ? '/month' : '/year'"></span>
                        </div>
                        </div>


                        <div class="{{ $isSelected ? 'my-6 h-px w-full bg-white/20' : 'my-6 h-px bg-gray-200 dark:bg-gray-700' }}"></div>

                        <div class="mb-8 space-y-3 flex-1">
                              @forelse($package->features as $feature)
                              <p class="{{ $isSelected ? 'flex items-start gap-3 text-sm text-white/80' : 'flex items-start gap-3 text-sm text-gray-500 dark:text-gray-400' }}">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M13.4017 4.35986L6.12166 11.6399L2.59833 8.11657" stroke="#12B76A" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                                     {{ $feature->panelFeature->feature ?? 'Unnamed Feature' }} {{ $feature->feature_value ?: '' }}
                                </p>
                               @empty
                                <p class="{{ $isSelected ? 'flex items-start gap-3 text-sm text-white/80' : 'text-title-md font-bold text-gray-800 dark:text-white/90' }}">No features listed</p>
                            @endforelse

      </div>


      @if ($isCurrentActivePackage)
    <button disabled
        class="flex w-full items-center justify-center rounded-lg bg-gray-400 p-3.5 text-sm font-medium text-white shadow-theme-xs cursor-not-allowed">
        Currently Active
    </button>
@else
    <button wire:click="selectPlan({{ $package->id }})"
        class="{{ $isSelected ? 'flex w-full items-center justify-center rounded-lg bg-brand-500 p-3.5 text-sm font-medium text-white shadow-theme-xs transition-colors hover:bg-brand-600 dark:bg-brand-500 primary-btn' : 'flex w-full items-center justify-center rounded-lg bg-gray-800 p-3.5 text-sm font-medium text-white shadow-theme-xs transition-colors hover:bg-brand-500 dark:bg-brand-500 dark:hover:bg-brand-600 primary-btn' }}">
        {{ $isSelected ? 'Selected' : 'Select Plan' }}
    </button>
@endif

                        {{-- <button wire:click="selectPlan({{ $package->id }})"
                            class="{{ $isSelected ? "flex w-full items-center justify-center rounded-lg bg-brand-500 p-3.5 text-sm font-medium text-white shadow-theme-xs transition-colors hover:bg-brand-600" : "flex w-full items-center justify-center rounded-lg bg-gray-800 p-3.5 text-sm font-medium text-white shadow-theme-xs transition-colors hover:bg-brand-500 dark:bg-white/10" }}">
                            {{ $isSelected ? 'Selected' : 'Select Plan' }}
                        </button> --}}

                        @if ($loop->first)
                            {{-- <span
                                class="absolute top-0 right-0 bg-yellow-400 text-black text-xs px-2 py-1 rounded-bl">Most
                                Premium</span> --}}
                        @endif
                    </div>
                @endforeach
            </div>
        </div>


        <!-- Stripe Payment Section -->

        <div class="mt-10 grid grid-cols-1 md:grid-cols-2 gap-6" x-data x-ref="cardSection" id="card-section">

            <!-- Stripe Card Form -->
            <div class="bg-white rounded p-6 shadow dark:border-gray-800 dark:bg-white/[0.03]">
                <h3 class="text-lg font-semibold mb-4">Payment With Stripe</h3>
                <p class="text-sm text-gray-600 mb-4">
                    All your data is stored on our PCI compliant servers and systems, which will always remain encrypted
                    and 100% secure...
                </p>

                <div wire:ignore>
                    <form id="payment-form">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium">Card Holder Name</label>
                                <input type="text" id="card-holder-name" class="w-full border p-2 rounded dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
                                    placeholder="Cardholder Name">
                            </div>

                            <div>
                                <label class="block text-sm font-medium">Card Details</label>
                                <div id="card-element" class=" card-elementw-full border p-2 rounded dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"></div>

                                <div id="card-errors" class="text-sm text-red-600 mt-2 dark:text-white"></div>
                            </div>

                            <button type="button"
                            onclick="stripeSubmitHandler()"
                            id="pay-button"
                                class="bg-brand-500 hover:bg-brand-600 text-white text-center font-bolds py-3 text-lg px-4 flex-1 rounded-lg queue-footer-button primary-btn">
                                <span class="button-texts">Pay Now</span>
                                <svg id="pay-loader" class="ml-2 h-5 w-5 text-white animate-spin hidden"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    @endif
    @if ($activeTab === 'current')
        <!-- My Current Plan Content -->
        @if(count($allInvoices) > 0)
        <div class="bg-white p-6 rounded-lg shadow-md max-w-5xl mx-auto  dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
            <div class="flex justify-between items-start">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">{{ $allInvoices[0]?->package?->name ?? ''}}</h2>
              <p>Subscription Status:
            <span class="font-semibold text-indigo-600 capitalize">
                {{ $currentSubscription->stripe_status ?? 'N/A' }}
            </span>
        </p>
            <p class="mt-2 text-gray-600 dark:text-gray-200">
                Active since: <span class="font-semibold">{{ Carbon\Carbon::parse($allInvoices[0]->date)->format($datetimeFormat) }}</span><br>
                Next billing date: <span class="font-semibold">{{ Carbon\Carbon::parse($currentPlan->expired)->format($datetimeFormat) }}</span>
            </p>

            <h3 class="mt-6 mb-2 font-semibold text-gray-800 dark:text-white">Included Features</h3>
            <ul class="space-y-1 text-gray-700  dark:text-white">
                 @forelse($allInvoices[0]?->package->features as $feature)
                   <li class="flex items-center">
                    ✅ <span class="ml-2">  {{ $feature->panelFeature->feature ?? 'Unnamed Feature' }} {{ $feature->feature_value ?: '' }}</span>
                </li>
                 @empty
                   <li class="flex items-center">
                    No Feature
                </li>
                 @endforelse

            </ul>
        </div>

        <div class="text-left">
            <div class="bg-blue-100 text-blue-700 px-4 py-1 rounded-full font-semibold inline-block">
              {{ $allInvoices[0]?->package?->currency ?? ''}} {{ $allInvoices[0]?->price ?? ''}} /{{ $allInvoices[0]?->unit == 'monthly' ? 'month' : ($allInvoices[0]?->unit == 'year' ?'year' : 'Daily')}} ({{$allInvoices[0]?->unit}})
            </div>

            <div class="mt-8 p-4 bg-gray-100 rounded-md text-sm text-gray-800 w-64">
                <h4 class="font-semibold mb-2">Usage Summary</h4>
                <p>👤 Staff used: {{$countStaff ?? 'N/A'}} </p>
                <p>📅 Visitors this month: {{ $countVisitor ?? 'N/A'}}</p>
                {{--<p>🛠 Analytics reports downloaded: 5</p>
                <p>📨 Notifications sent: 42</p> --}}
            </div>
        </div>
    </div>

    <div class="mt-6 flex justify-between items-center">
        <button  class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
              <a href="javascript:void(0)" wire:click="setActiveTab('plans')">
                  Change Plan
              </a>
        </button>
        <button class="bg-gray-100 text-gray-800 px-6 py-2 rounded-lg hover:bg-gray-200 transition">
            <a href="{{ $latestInvoiceUrl }}" target="_blank">
                   View Invoice
                </a>
        </button>
        @if($currentSubscription && $currentSubscription->stripe_status === 'active')
        <button  wire:click="cancelSubscription"
    wire:loading.attr="disabled" class="bg-red-100 text-red-700 px-6 py-2 rounded-lg hover:bg-red-200 transition">
            Cancel Subscription
        </button>
        @endif
    </div>
</div>
@endif
    @endif
    @if ($activeTab === 'invoice')
        <!-- My Current Plan Content -->
       <div class="p-4 md:p-4 rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow text-left">
        @if (session()->has('error'))
            <div class="text-red-600 text-sm mb-2">{{ session('error') }}</div>
        @endif
    <h2 class="text-xl font-semibold mb-4 text-gray-800 dark:text-white">All Invoices</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-5 py-3 sm:px-6">Invoice ID</th>
                    <th class="px-5 py-3 sm:px-6">Date</th>
                    <th class="px-5 py-3 sm:px-6">Plan</th>
                    <th class="px-5 py-3 sm:px-6">Amount</th>
                   <th class="px-5 py-3 sm:px-6">Status</th>
                    <th class="px-5 py-3 sm:px-6">Download</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm">

                @if(isset($allInvoices))
                  @foreach ($allInvoices as $invoice)
                    <tr>
                        <td class="px-6 py-4 text-indigo-600 font-medium">{{ $invoice->inv_num ?? ''}}</td>
                        <td class="px-6 py-4">{{ Carbon\Carbon::parse($invoice->date)->format($datetimeFormat) }}</td>
                        <td class="px-6 py-4">{{ $invoice?->package?->name ?? ''}}</td>
                        <td class="px-6 py-4">{{ $invoice?->package?->currency ?? ''}} {{ $invoice?->price ?? ''}}</td>
                       <td class="px-6 py-4 text-sm font-semibold {{ $invoice->subscription?->stripe_status === 'active' ? 'text-green-600' : 'text-red-600' }}">
    {{ ucfirst($invoice->subscription->stripe_status ?? 'Unknown') }}
</td>
                        <td class="px-6 py-4">
                             <button wire:click="downloadInvoice('{{ $invoice->id }}')" wire:loading.attr="disabled" class="text-blue-600 hover:underline">Download</button>
                        </td>
                </tr>
                  @endforeach
                @endif

            </tbody>
        </table>
    </div>
</div>

    @endif
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        let stripe, card;
        let mountTimeout = null;

        function mountStripeCard() {
            const cardElementContainer = document.getElementById("card-element");
            if (!cardElementContainer || cardElementContainer.children.length > 0) return;

            stripe = Stripe("{{ config('services.stripe.key') }}");
            const elements = stripe.elements();
            card = elements.create("card");
            card.mount("#card-element");
        }

        function stripeSubmitHandler() {
            const cardHolderName = document.getElementById("card-holder-name");
            const errorDisplay = document.getElementById("card-errors");
             // Show loader
    document.getElementById('pay-loader').classList.remove('hidden');
    document.querySelector('.button-texts').classList.add('hidden');

            errorDisplay.textContent = "";

            stripe.createToken(card, {
                name: cardHolderName.value,
            }).then(({
                token,
                error
            }) => {
                if (error) {
                    errorDisplay.textContent = error.message;
                      errorDisplay.textContent = error.message;
            // Hide loader again on error
            document.getElementById('pay-loader').classList.add('hidden');
            document.querySelector('.button-texts').classList.remove('hidden');
                } else {
                    Livewire.dispatch("stripeTokenReceived", {
                        stripeToken: token.id,
                        cardName: cardHolderName.value,
                    });
                }
            });
        }

        document.addEventListener("DOMContentLoaded", () => {
            mountStripeCard();

            Livewire.hook("message.processed", (message, component) => {
                mountStripeCard(); // Re-attach card on tab switch
            });
            Livewire.on("card-element-append", () => {
                if (mountTimeout) {
                    clearTimeout(mountTimeout);
                }
                mountTimeout = setTimeout(() => mountStripeCard(), 500); // delay mount
            });

            window.stripeSubmitHandler = stripeSubmitHandler;
        });
    </script>

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('saved', (response) => {
                Swal.fire({
                    title: "Saved Successfully",
                    text: response.message,
                    icon: "success",
                    allowOutsideClick: false,
                }).then(() => {
                    window.location.reload();
                });
            });
            Livewire.on('cancel', (response) => {
                Swal.fire({
                    title: "Cancelled Successfully",
                    text: response.message,
                    icon: "success",
                    allowOutsideClick: false,
                }).then(() => {
                    window.location.reload();
                });
            });
          Livewire.on('error', () => {
            Swal.fire({
                title: "Something Went Wrong",
                text: "Please try again or contact support.",
                icon: "error",
                confirmButtonText: "Reload",
                allowOutsideClick: false,
            }).then(() => {
                window.location.reload();
            });
        });

         Livewire.on('error-plan', (data) => {

                Swal.fire({
                    title: "Something Went Wrong",
                    text: data.message, // 🔁 Dynamic error text
                    icon: "error",
                    confirmButtonText: "Reload",
                    allowOutsideClick: false,
                }).then(() => {
                    window.location.reload();
                });
            });


            Livewire.on('triggerDownload', (url) => {
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', '');
                link.setAttribute('target', '_blank'); // optional: open in new tab
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
        });

        Livewire.on('scrollToCard', () => {
            const cardSection = document.querySelector('#card-section');
            if (cardSection) {
                cardSection.scrollIntoView({ behavior: 'smooth' });
            }
        });

         Livewire.on('show-error', message => {
            console.log(message);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message.message,
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK'
            });
        });

        Livewire.on('saved', message => {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: message[0],
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK'
            });
        });
        });


    </script>

    {{-- <script>
    let stripe;
    let card;

    document.addEventListener("livewire:init", () => {
        const cardContainer = document.getElementById('card-element');
        if (!cardContainer) return console.error('Card element missing');

        stripe = Stripe("{{ config('services.stripe.key') }}");
        const elements = stripe.elements();
        card = elements.create('card');
        card.mount('#card-element');

        const payBtn = document.getElementById('pay-btn');
        const loader = document.getElementById('pay-loader');
        const cardHolderName = document.getElementById("card-holder-name");
        const errorDisplay = document.getElementById("card-errors");
        const countryInput = document.getElementById("billing-country"); // ✅ Add this

        if (payBtn) {
            payBtn.addEventListener('click', async () => {
                loader.classList.remove('hidden');
                payBtn.disabled = true;

                const { paymentMethod, error } = await stripe.createPaymentMethod('card', card);
                if (error) {
                    errorDisplay.textContent = error.message;
                } else {
                    Livewire.dispatch('stripeTokenReceived', {
                        cardName: cardHolderName.value,
                        paymentMethodId: paymentMethod.id,
                         line1: document.getElementById("billing-line1").value,
                            postal: document.getElementById("billing-postal").value,
                            city: document.getElementById("billing-city").value,
                            state: document.getElementById("billing-state").value,
                            country: document.getElementById("billing-country").value
                    });
                }
            });
        }

        Livewire.on('confirmPayment', async ({ clientSecret }) => {
            const result = await stripe.confirmCardPayment(clientSecret);
            if (result.error) {
                document.getElementById("card-errors").textContent = result.error.message;
            } else if (result.paymentIntent.status === 'succeeded') {
                Livewire.dispatch('paymentSucceeded');
            }
        });

        window.addEventListener('payment-success', function () {
            Swal.fire({
                title: 'Payment Successful!',
                text: 'Thank you for subscribing.',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = "{{ route('tenant.dashboard') }}";
            });
        });
    });
</script> --}}
</div>
