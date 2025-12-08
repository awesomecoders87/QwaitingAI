<div class="p-4">
    <style>
        .bg-black{
                background-color: #1018282e;
                z-index: 99999;
        }
    </style>
    <div class="max-w-6xl mx-auto">

        <!-- Tabs Header -->
        <div class="mb-8">
            <ul class="flex space-x-2 tabs-nav">
                <li>
                    <button wire:click="switchTab('payment')"
                        class="inline-block px-4 py-2 rounded-lg hover:text-gray-600 hover:bg-gray-100 bg-white
                        {{ $activeTab === 'payment' ? 'active-tab' : 'text-gray-800' }}">
                        {{ __('text.Payment Gateway Settings') }}
                    </button>
                </li>
                <li>
                    <button wire:click="switchTab('integration')" style="display:none;"
                        class="inline-block px-4 py-2 rounded-lg hover:text-gray-600 hover:bg-gray-100 bg-white tex-blue-600
                        {{ $activeTab === 'integration' ? 'active-tab' : 'text-gray-800' }}">
                        {{ __('text.Integration') }}
                    </button>
                </li>
                <li>
                    <button wire:click="switchTab('general')"
                        class="inline-block px-4 py-2 rounded-lg hover:text-gray-600 hover:bg-gray-100 bg-white tex-blue-600
                        {{ $activeTab === 'general' ? 'active-tab' : 'text-gray-800' }}">
                        {{ __('text.General Settings') }}
                    </button>
                </li>

              
            </ul>
        </div>

        <!-- Payment Gateway Tab -->
        @if ($activeTab === 'payment')
            <div>
                <h1 class="text-xl font-semibold mb-4">{{ __('text.Payment Gateway Settings') }}</h1> 

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Stripe -->
                    <div onclick="openModal('stripe')" class="cursor-pointer bg-white rounded p-6 shadow hover:shadow-xl transition-all transform hover:scale-105 duration-300  dark:bg-white/[0.03]">
                        <div class="flex items-center justify-between space-x-6">
                            <div class="flex items-center space-x-4">
                                <img src="{{ url('images/stripe.png') }}" class="w-16 h-16">
                                <div>
                                    <h2 class="text-xl font-semibold">{{ __('text.Stripe') }}</h2>
                                    <p class="text-gray-500 mt-2">{{ __('text.Easily enable Stripe to accept online payments.') }}</p>
                                </div>
                            </div>
                            <span class="font-semibold text-{{ $paymentStatus == 'Enabled' ? 'green' : 'red'}}-500">{{ $paymentStatus }}</span>
                        </div>
                    </div>

                    {{-- Uncomment and edit if PayPal support is added
                    <div onclick="openModal('paypal')" class="cursor-pointer bg-white rounded p-6 shadow hover:shadow-xl transition-all transform hover:scale-105 duration-300  dark:bg-white/[0.03]">
                        <div class="flex items-center justify-between space-x-6">
                            <div class="flex items-center space-x-4">
                                <img src="{{ asset('images/paypal.png') }}" class="w-16 h-16">
                                <div>
                                    <h2 class="text-xl font-semibold">{{ __('text.PayPal') }}</h2>
                                    <p class="text-gray-500 mt-2">{{ __('text.Connect your PayPal account to accept payments.') }}</p>
                                </div>
                            </div>
                            <span class="font-semibold text-red-500">{{ __('text.Disabled') }}</span>
                        </div>
                    </div>
                    --}}
                </div>

                <!-- Currency and Save -->
                 <h2 class="text-xl font-semibold mb-4 mt-4">{{ __('text.General Settings') }}</h2>
                <div class="p-6 bg-white rounded shadow space-y-6  dark:bg-white/[0.03] dark:text-white">
                    
                    <p class="text-gray-600 dark:text-gray-400">{{ __('text.Configure basic payment settings below.') }}</p>

                    <div class="grid items-center justify-between">
                        <label class="text-gray">{{ __('text.Select Currency') }}</label>
                        <select wire:model="currency" class="form-select block w-full text-gray-700 border-gray-300 rounded-lg p-2.5 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                          <option value="AED">United Arab Emirates Dirham (AED)</option>
                          <option value="AFN">Afghan Afghani (AFN)</option>
                          <option value="ALL">Albanian Lek (ALL)</option>
                          <option value="AMD">Armenian Dram (AMD)</option>
                          <option value="ANG">Netherlands Antillean Guilder (ANG)</option>
                          <option value="AOA">Angolan Kwanza (AOA)</option>
                          <option value="ARS">Argentine Peso (ARS)</option>
                          <option value="AUD">Australian Dollar (AUD)</option>
                          <option value="AWG">Aruban Florin (AWG)</option>
                          <option value="AZN">Azerbaijani Manat (AZN)</option>
                          <option value="BAM">Bosnia and Herzegovina Convertible Mark (BAM)</option>
                          <option value="BBD">Barbadian Dollar (BBD)</option>
                          <option value="BDT">Bangladeshi Taka (BDT)</option>
                          <option value="BGN">Bulgarian Lev (BGN)</option>
                          <option value="BHD">Bahraini Dinar (BHD)</option>
                          <option value="BIF">Burundian Franc (BIF)</option>
                          <option value="BMD">Bermudian Dollar (BMD)</option>
                          <option value="BND">Brunei Dollar (BND)</option>
                          <option value="BOB">Bolivian Boliviano (BOB)</option>
                          <option value="BRL">Brazilian Real (BRL)</option>
                          <option value="BSD">Bahamian Dollar (BSD)</option>
                          <option value="BTN">Bhutanese Ngultrum (BTN)</option>
                          <option value="BWP">Botswanan Pula (BWP)</option>
                          <option value="BYN">Belarusian Ruble (BYN)</option>
                          <option value="BZD">Belize Dollar (BZD)</option>
                          <option value="CAD">Canadian Dollar (CAD)</option>
                          <option value="CDF">Congolese Franc (CDF)</option>
                          <option value="CHF">Swiss Franc (CHF)</option>
                          <option value="CLP">Chilean Peso (CLP)</option>
                          <option value="CNY">Chinese Yuan (CNY)</option>
                          <option value="COP">Colombian Peso (COP)</option>
                          <option value="CRC">Costa Rican Colón (CRC)</option>
                          <option value="CUP">Cuban Peso (CUP)</option>
                          <option value="CVE">Cape Verdean Escudo (CVE)</option>
                          <option value="CZK">Czech Koruna (CZK)</option>
                          <option value="DJF">Djiboutian Franc (DJF)</option>
                          <option value="DKK">Danish Krone (DKK)</option>
                          <option value="DOP">Dominican Peso (DOP)</option>
                          <option value="DZD">Algerian Dinar (DZD)</option>
                          <option value="EGP">Egyptian Pound (EGP)</option>
                          <option value="ERN">Eritrean Nakfa (ERN)</option>
                          <option value="ETB">Ethiopian Birr (ETB)</option>
                          <option value="EUR">Euro (EUR)</option>
                          <option value="FJD">Fijian Dollar (FJD)</option>
                          <option value="FKP">Falkland Islands Pound (FKP)</option>
                          <option value="FOK">Faroese Króna (FOK)</option>
                          <option value="GBP">British Pound Sterling (GBP)</option>
                          <option value="GEL">Georgian Lari (GEL)</option>
                          <option value="GGP">Guernsey Pound (GGP)</option>
                          <option value="GHS">Ghanaian Cedi (GHS)</option>
                          <option value="GIP">Gibraltar Pound (GIP)</option>
                          <option value="GMD">Gambian Dalasi (GMD)</option>
                          <option value="GNF">Guinean Franc (GNF)</option>
                          <option value="GTQ">Guatemalan Quetzal (GTQ)</option>
                          <option value="GYD">Guyanese Dollar (GYD)</option>
                          <option value="HKD">Hong Kong Dollar (HKD)</option>
                          <option value="HNL">Honduran Lempira (HNL)</option>
                          <option value="HRK">Croatian Kuna (HRK)</option>
                          <option value="HTG">Haitian Gourde (HTG)</option>
                          <option value="HUF">Hungarian Forint (HUF)</option>
                          <option value="IDR">Indonesian Rupiah (IDR)</option>
                          <option value="ILS">Israeli New Shekel (ILS)</option>
                          <option value="IMP">Isle of Man Pound (IMP)</option>
                          <option value="INR">Indian Rupee (INR)</option>
                          <option value="IQD">Iraqi Dinar (IQD)</option>
                          <option value="IRR">Iranian Rial (IRR)</option>
                          <option value="ISK">Icelandic Króna (ISK)</option>
                          <option value="JEP">Jersey Pound (JEP)</option>
                          <option value="JMD">Jamaican Dollar (JMD)</option>
                          <option value="JOD">Jordanian Dinar (JOD)</option>
                          <option value="JPY">Japanese Yen (JPY)</option>
                          <option value="KES">Kenyan Shilling (KES)</option>
                          <option value="KGS">Kyrgyzstani Som (KGS)</option>
                          <option value="KHR">Cambodian Riel (KHR)</option>
                          <option value="KID">Kiribati Dollar (KID)</option>
                          <option value="KMF">Comorian Franc (KMF)</option>
                          <option value="KRW">South Korean Won (KRW)</option>
                          <option value="KWD">Kuwaiti Dinar (KWD)</option>
                          <option value="KYD">Cayman Islands Dollar (KYD)</option>
                          <option value="KZT">Kazakhstani Tenge (KZT)</option>
                          <option value="LAK">Lao Kip (LAK)</option>
                          <option value="LBP">Lebanese Pound (LBP)</option>
                          <option value="LKR">Sri Lankan Rupee (LKR)</option>
                          <option value="LRD">Liberian Dollar (LRD)</option>
                          <option value="LSL">Lesotho Loti (LSL)</option>
                          <option value="LYD">Libyan Dinar (LYD)</option>
                          <option value="MAD">Moroccan Dirham (MAD)</option>
                          <option value="MDL">Moldovan Leu (MDL)</option>
                          <option value="MGA">Malagasy Ariary (MGA)</option>
                          <option value="MKD">Macedonian Denar (MKD)</option>
                          <option value="MMK">Myanmar Kyat (MMK)</option>
                          <option value="MNT">Mongolian Tögrög (MNT)</option>
                          <option value="MOP">Macanese Pataca (MOP)</option>
                          <option value="MRU">Mauritanian Ouguiya (MRU)</option>
                          <option value="MUR">Mauritian Rupee (MUR)</option>
                          <option value="MVR">Maldivian Rufiyaa (MVR)</option>
                          <option value="MWK">Malawian Kwacha (MWK)</option>
                          <option value="MXN">Mexican Peso (MXN)</option>
                          <option value="MYR">Malaysian Ringgit (MYR)</option>
                          <option value="MZN">Mozambican Metical (MZN)</option>
                          <option value="NAD">Namibian Dollar (NAD)</option>
                          <option value="NGN">Nigerian Naira (NGN)</option>
                          <option value="NIO">Nicaraguan Córdoba (NIO)</option>
                          <option value="NOK">Norwegian Krone (NOK)</option>
                          <option value="NPR">Nepalese Rupee (NPR)</option>
                          <option value="NZD">New Zealand Dollar (NZD)</option>
                          <option value="OMR">Omani Rial (OMR)</option>
                          <option value="PAB">Panamanian Balboa (PAB)</option>
                          <option value="PEN">Peruvian Sol (PEN)</option>
                          <option value="PGK">Papua New Guinean Kina (PGK)</option>
                          <option value="PHP">Philippine Peso (PHP)</option>
                          <option value="PKR">Pakistani Rupee (PKR)</option>
                          <option value="PLN">Polish Złoty (PLN)</option>
                          <option value="PYG">Paraguayan Guaraní (PYG)</option>
                          <option value="QAR">Qatari Riyal (QAR)</option>
                          <option value="RON">Romanian Leu (RON)</option>
                          <option value="RSD">Serbian Dinar (RSD)</option>
                          <option value="RUB">Russian Ruble (RUB)</option>
                          <option value="RWF">Rwandan Franc (RWF)</option>
                          <option value="SAR">Saudi Riyal (SAR)</option>
                          <option value="SBD">Solomon Islands Dollar (SBD)</option>
                          <option value="SCR">Seychellois Rupee (SCR)</option>
                          <option value="SDG">Sudanese Pound (SDG)</option>
                          <option value="SEK">Swedish Krona (SEK)</option>
                          <option value="SGD">Singapore Dollar (SGD)</option>
                          <option value="SHP">Saint Helena Pound (SHP)</option>
                          <option value="SLL">Sierra Leonean Leone (SLL)</option>
                          <option value="SOS">Somali Shilling (SOS)</option>
                          <option value="SRD">Surinamese Dollar (SRD)</option>
                          <option value="SSP">South Sudanese Pound (SSP)</option>
                          <option value="STN">São Tomé and Príncipe Dobra (STN)</option>
                          <option value="SYP">Syrian Pound (SYP)</option>
                          <option value="SZL">Swazi Lilangeni (SZL)</option>
                          <option value="THB">Thai Baht (THB)</option>
                          <option value="TJS">Tajikistani Somoni (TJS)</option>
                          <option value="TMT">Turkmenistani Manat (TMT)</option>
                          <option value="TND">Tunisian Dinar (TND)</option>
                          <option value="TOP">Tongan Paʻanga (TOP)</option>
                          <option value="TRY">Turkish Lira (TRY)</option>
                          <option value="TTD">Trinidad and Tobago Dollar (TTD)</option>
                          <option value="TVD">Tuvaluan Dollar (TVD)</option>
                          <option value="TWD">New Taiwan Dollar (TWD)</option>
                          <option value="TZS">Tanzanian Shilling (TZS)</option>
                          <option value="UAH">Ukrainian Hryvnia (UAH)</option>
                          <option value="UGX">Ugandan Shilling (UGX)</option>
                          <option value="USD">United States Dollar (USD)</option>
                          <option value="UYU">Uruguayan Peso (UYU)</option>
                          <option value="UZS">Uzbekistani Soʻm (UZS)</option>
                          <option value="VES">Venezuelan Bolívar Soberano (VES)</option>
                          <option value="VND">Vietnamese Đồng (VND)</option>
                          <option value="VUV">Vanuatu Vatu (VUV)</option>
                          <option value="WST">Samoan Tālā (WST)</option>
                          <option value="XAF">Central African CFA Franc (XAF)</option>
                          <option value="XCD">East Caribbean Dollar (XCD)</option>
                          <option value="XDR">Special Drawing Rights (XDR)</option>
                          <option value="XOF">West African CFA Franc (XOF)</option>
                          <option value="XPF">CFP Franc (XPF)</option>
                          <option value="YER">Yemeni Rial (YER)</option>
                          <option value="ZAR">South African Rand (ZAR)</option>
                          <option value="ZMW">Zambian Kwacha (ZMW)</option>
                          <option value="ZWL">Zimbabwean Dollar (ZWL)</option>

                        </select>
                    </div>

                    <div class="flex justify-end">
                        <button wire:click="savePaymentSettings"
                            class="px-5 py-2 bg-blue-600 text-white font-semibold rounded-lg shadow hover:bg-blue-700 transition">
                            {{ __('text.Save') }}
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Integration Tab -->
        @if ($activeTab === 'integration')
            <div style="display:none;">
 <h1 class="text-xl font-semibold mb-4"> {{ __('text.Integration') }}</h1>
      <p class="text-lg text-gray-600 mb-4">{{ __('text.Here you can configure third-party integration settings like API keys, webhooks, and other integrations.') }}</p>

                 <div class="space-y-4">
        <div>
          <label class="block text-gray mb-2">{{ __('text.API Key') }}</label>
          <input type="text" wire:model='third_api_key' class="w-full border rounded-lg px-3 py-2" placeholder="Enter your API key" />
        </div>

        <div>
          <label class="block text-gray mb-2">{{ __('text.Webhook URL') }}</label>
          <input type="text" wire:model='third_webhook_url' class="w-full border rounded-lg px-3 py-2" placeholder="https://example.com/webhook" />
        </div>

        <div>
          <label class="block text-gray mb-2">{{ __('text.Enable Webhook') }}</label>
          <input type="checkbox"  wire:model='third_party_enable' class="form-checkbox h-5 w-5 text-blue-600" />
        </div>

        <div class="flex justify-end">
          <button wire:click="saveIntegrationSettings" class="px-6 py-2 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition-all duration-200">{{ __('text.Save') }}</button>
        </div>
      </div>
            </div>
        @endif


          <!-- Stripe Modal -->
  <div id="stripeModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg w-full max-w-md space-y-4">
      <h2 class="text-xl font-semibold">{{ __('text.Stripe Settings') }}</h2>

      <label class="flex items-center space-x-2">
        <input type="checkbox" id="enable_stripe" wire:model="stripeEnable"/>
        <span>{{ __('text.Enable Stripe') }}</span>
      </label>

      <div>
        <label class="block mb-1 font-medium">{{ __('text.Publishable Key') }}</label>
        <input type="text" wire:model="apiKey" class="w-full border rounded px-3 py-2" placeholder="pk_live_..." />
      </div>

      <div>
        <label class="block mb-1 font-medium">{{ __('text.Secret Key') }}</label>
        <input type="text" wire:model="apiSecret"  class="w-full border rounded px-3 py-2" placeholder="sk_live_..." />
      </div>

      <div class="flex justify-end space-x-2 pt-2">
        <button onclick="closeModal('stripe')" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">{{ __('text.Cancel') }}</button>
        <button wire:click="saveStripeSettings" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">{{ __('text.Save') }}</button>
      </div>
    </div>
  </div>

  <!-- PayPal Modal -->
  <div id="paypalModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg w-full max-w-md space-y-4">
      <h2 class="text-xl font-semibold">{{ __('text.PayPal Settings') }}</h2>

      <label class="flex items-center space-x-2">
        <input type="checkbox" id="enable_paypal" />
        <span>{{ __('text.Enable PayPal') }}</span>
      </label>

      <div>
        <label class="block mb-1 font-medium">{{ __('text.Client ID') }}</label>
        <input type="text" class="w-full border rounded px-3 py-2" placeholder="PayPal Client ID" />
      </div>

      <div>
        <label class="block mb-1 font-medium">{{ __('text.Secret') }}</label>
        <input type="text" class="w-full border rounded px-3 py-2" placeholder="PayPal Secret" />
      </div>

      <div>
        <label class="block mb-1 font-medium">{{ __('text.Mode') }}</label>
        <select class="w-full border rounded px-3 py-2">
          <option value="sandbox">{{ __('text.Sandbox') }}</option>
          <option value="live">{{ __('text.Live') }}</option>
        </select>
      </div>

      <div class="flex justify-end space-x-2 pt-2">
        <button onclick="closeModal('paypal')" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">{{ __('text.Cancel') }}</button>
        <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">{{ __('text.Save') }}</button>
      </div>
    </div>
  </div>

        <!-- General Setting Tab -->
        @if ($activeTab === 'general')
            <form wire:submit.prevent="saveGeneralSettings">
                <h2 class="text-xl font-semibold pb-3">{{ __('text.Payment Settings') }}</h2>
                <div  class="max-w-xl bg-white p-6 rounded-xl shadow-md space-y-6  dark:bg-white/[0.03] dark:text-white">      
                <div class="flex items-center justify-between">
                    <label for="enablePayment" class="text-gray-700 font-medium dark:text-white">
                        {{ __('text.Enable Payments') }}
                    </label>
                    <input type="checkbox" wire:model="enablePayment" id="enablePayment" class="toggle toggle-primary" />
                </div>
                <p class="text-sm text-gray-400">
                    {{ __('text.Allow users to make payments for services. Free services will skip payment.') }}
                </p>

                <div>
                    <label for="categoryLevel" class="block text-gray-700 font-medium mb-1 dark:text-white">
                        {{ __('text.Category Level for Pricing') }}
                    </label>
                    <select wire:model="categoryLevel"
                        id="categoryLevel"
                        class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        <option value="1">Level 1 - Main Category</option>
                        <option value="2">Level 2 - Subcategory</option>
                        <option value="3">Level 3 - Sub-subcategory</option>
                    </select>
                    <p class="text-sm text-gray-400 mt-1">{{ __('text.Choose the category level where you want to define service prices.') }}</p>
                </div>

                <div>
                    <label for="applicableTo" class="block text-gray-700 font-medium mb-1 dark:text-white">
                        {{ __('text.Apply Payment To') }}
                    </label>
                    <select wire:model="applicableTo"
                        id="applicableTo"
                        class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        <option value="appointment">{{ __('text.Appointment') }}</option>
                        <option value="walkin">{{ __('text.Walk-In') }}</option>
                        <option value="both">{{ __('text.Both') }}</option>
                    </select>
                    <p class="text-sm text-gray mt-1  dark:text-gray-400">Select where the payment should be enforced.</p>
                </div>

                <div class="text-right">
                    <button type="submit"
                        class="px-5 py-2 bg-blue-600 text-white font-semibold rounded-lg shadow hover:bg-blue-700 transition">
                        {{ __('text.Save') }}
                    </button>
                </div>
                    </div>
            </form>
        @endif

        @if (session()->has('message'))
            <div class="mt-4 text-green-600 font-medium">
                {{ session('message') }}
            </div>
        @endif

    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    window.addEventListener('swal:success', event => {
        Swal.fire({
            icon: 'success',
            title: event.detail.title,
            text: event.detail.text,
            confirmButtonColor: '#3085d6'
        });
    });
</script>
 <!-- Modal Script -->
  <script>
    function openModal(type) {
      document.getElementById(type + 'Modal').classList.remove('hidden');
    }

    function closeModal(type) {
      document.getElementById(type + 'Modal').classList.add('hidden');
    }

    // Toggle status for demonstration
    document.getElementById("enable_stripe").addEventListener("change", function() {
      let statusElement = document.getElementById("stripeStatus");
      if (this.checked) {
        statusElement.textContent = "Enabled";
        statusElement.classList.remove("text-red-500");
        statusElement.classList.add("text-green-500");
      } else {
        statusElement.textContent = "Disabled";
        statusElement.classList.remove("text-green-500");
        statusElement.classList.add("text-red-500");
      }
    });

    document.getElementById("enable_paypal").addEventListener("change", function() {
      let statusElement = document.getElementById("paypalStatus");
      if (this.checked) {
        statusElement.textContent = "Enabled";
        statusElement.classList.remove("text-red-500");
        statusElement.classList.add("text-green-500");
      } else {
        statusElement.textContent = "Disabled";
        statusElement.classList.remove("text-green-500");
        statusElement.classList.add("text-red-500");
      }
    });
  </script>
</div>
