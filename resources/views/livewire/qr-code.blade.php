<div class="p-4">
     <h2 class="text-xl font-bold mb-4">{{ __('setting.QR Code') }}</h2>
    <div class="bg-white shadow-md rounded-lg p-6 dark:bg-white/[0.03] dark:text-white">
               
        <form wire:submit="save" class="space-y-6">
            <div class="-mx-2.5 flex flex-wrap gap-y-5 md:grid-cols-2  max-w-3xl">
                <!-- QR Code Static/Dynamic URL -->
                <div class="w-full px-2.5 xl:w-1/2">
                    <label class="block text-sm font-medium text-gray-700 mb-2 dark:text-gray-400">
                        {{ __('setting.QR Code Static/Dynamic URL') }}
                    </label>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <input wire:model="qrcode_url_status" type="radio" id="static" name="qrcode_url_status" value="0" class="h-4 w-4 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                            <label for="static" class="ml-2 block text-sm text-gray-700 dark:text-white">
                                {{ __('setting.Static') }}
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 ml-6  dark:text-gray-400">{{ __("setting.The QR code won't change") }}</p>
                        
                        <div class="flex items-center">
                            <input wire:model="qrcode_url_status" type="radio" id="dynamic" name="qrcode_url_status" value="1" class="h-4 w-4 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                            <label for="dynamic" class="ml-2 block text-sm text-gray-700 dark:text-white">
                                {{ __('setting.Dynamic') }}
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 ml-6">{{ __('setting.The prior QR code will be automatically disabled and replaced with a dynamic one') }}</p>
                    </div>
                    @error('qrcode_url_status') <span class="text-error-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <!-- URL Validity String -->
                <div class="w-full px-2.5 xl:w-1/2" x-data="{ qrcode_url_status: @entangle('qrcode_url_status') }" x-show="qrcode_url_status == '1'">
                    <label for="url_validity_str" class="block text-sm font-medium text-gray-700 mb-2  dark:text-white">
                        {{ __('setting.URL Validity String') }}
                    </label>
                    <input wire:model="url_validity_str" type="text" id="url_validity_str" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                    @error('url_validity_str') <span class="text-error-500 text-xs">{{ $message }}</span> @enderror
                </div>
            
                
                <!-- Status -->
                <div class="w-full px-2.5 xl:w-1/2">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2 dark:text-white">
                        {{ __('setting.Status') }}
                    </label>
                    <select wire:model="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        <option value="1">{{ __('setting.Active') }}</option>
                        <option value="0">{{ __('setting.Inactive') }}</option>
                    </select>
                    @error('status') <span class="text-error-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <!-- Update Time -->
                <div class="w-full px-2.5 xl:w-1/2" x-data="{ status: @entangle('status') }" x-show="status == '1'">
                    <label for="qr_update_time" class="block text-sm font-medium text-gray-700 mb-2 dark:text-white">
                        {{ __('setting.Update Time') }}
                    </label>
                    <select wire:model="qr_update_time" id="qr_update_time" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        @foreach($updateTimeOptions as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                    @error('qr_update_time') <span class="text-error-500 text-xs">{{ $message }}</span> @enderror
                </div>
             
                <!-- URL -->
                <div class="w-full px-2.5 xl:w-1/2">
                    <label for="url" class="block text-sm font-medium text-gray-700 mb-2 dark:text-white">
                        {{ __('setting.URL') }}
                    </label>
                    <input wire:model="url" type="text" id="url" readonly class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm sm:text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                    @error('url') <span class="text-error-500 text-xs">{{ $message }}</span> @enderror
                @if (empty($url))
                    <p class="mt-1 text-xs text-gray-500 italic">
                        ⚠️ {{ __('setting.This URL will be generated after saving the settings.') }}
                    </p>
                @endif
                </div>
                
                <!-- Scan Valid Distance -->
                {{-- <div class="w-full px-2.5 xl:w-1/2">
                    <label for="scan_valid_distance" class="block text-sm font-medium text-gray-700 mb-2 dark:text-white">
                        {{ __('setting.Scan Valid Distance') }}
                    </label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <input wire:model="scan_valid_distance" type="number" id="scan_valid_distance" class="block w-full pr-12 rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">m</span>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">{{ __('setting.Enter Distance value in') }} <strong>{{ __('setting.Meter') }}</strong></p>
                    @error('scan_valid_distance') <span class="text-error-500 text-xs">{{ $message }}</span> @enderror
                </div> --}}
                
                <!-- ECC Level -->
                <div class="w-full px-2.5 xl:w-1/2">
                    <label for="level_ecc" class="block text-sm font-medium text-gray-700 mb-2 dark:text-white">
                        {{ __('setting.ECC') }}
                    </label>
                    <select wire:model="level_ecc" id="level_ecc" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        @foreach($levelEccOptions as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                    @error('level_ecc') <span class="text-error-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <!-- Size -->
                <div class="w-full px-2.5 xl:w-1/2">
                    <label for="size" class="block text-sm font-medium text-gray-700 mb-2 dark:text-white"> 
                        {{ __('setting.Size') }}
                    </label>
                    <select wire:model="size" id="size" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        @foreach($sizeOptions as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                    @error('size') <span class="text-error-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>
            
            <div class="flex space-x-4 pt-4 gap-2">
                <button type="submit" class="inline-flex justify-center px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">
                    {{ __('setting.Save') }}
                </button>
                
                <button type="button" wire:click="printQrCode" class="inline-flex justify-center px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">
                    {{ __('setting.Print') }}
                </button>
            </div>
        </form>
                </div>
        <!-- QR Code Preview -->
        @if (!$errors->any())
        <div class="bg-white p-4 rounded-lg shadow-lg max-w-md w-full text-center mt-6 dark:bg-white/[0.03] dark:text-white" id="qrCodeSection">
            @php
                $logo = App\Models\SiteDetail::viewImage(App\Models\SiteDetail::FIELD_BUSINESS_LOGO);
            @endphp

            <a href="{{ url('/') }}"> <img src="{{ url($logo) }}" class="mx-auto max-h-20" /> </a>
            <h2 class="text-lg font-bold text-gray-700 mb-6 dark:text-white" style="text-align:center"> {{ __('setting.SCAN TO ENTER') }}</h2>
            
            <div class="text-center mx-auto w-full max-w-md">
                <div class="bg-blue-500 py-2 px-4 rounded-full text-lg font-semibold mb-6" style="text-align:center">
                    {{ __('setting.Welcome') }}
                </div>
            </div>

            <div class="flex justify-center mb-6">
                {!! QrCode::size(25 * $size)->errorCorrection($level_ecc)->generate($customizeUrl) !!}
            </div>

            <div class="border-t border-gray-300 my-6"></div>

            <div class="flex justify-around text-gray-700 gap-5  dark:text-white">
                <div class="text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" version="1.1"
                        xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:svgjs="http://svgjs.com/svgjs"
                        width="30" height="30" x="0" y="0" viewBox="0 0 401.994 401.994"
                        style="enable-background:new 0 0 512 512" xml:space="preserve" class="m-auto fill-gray-700 dark:fill-gray-400">
                        <g>
                            <path
                                d="M0 401.991h182.724V219.265H0v182.726zm36.542-146.178h109.636v109.352H36.542V255.813z"></path>
                            <path
                                d="M73.089 292.355h36.544v36.549H73.089zM292.352 365.449h36.553v36.545h-36.553zM365.442 365.449h36.552v36.545h-36.552z"></path>
                            <path
                                d="M365.446 255.813h-36.542v-36.548H219.265v182.726h36.548V292.355h36.539v36.549h109.639V219.265h-36.545zM0 182.728h182.724V0H0v182.728zM36.542 36.542h109.636v109.636H36.542V36.542z"></path>
                            <path
                                d="M73.089 73.089h36.544v36.547H73.089zM219.265 0v182.728h182.729V0H219.265zm146.181 146.178H255.813V36.542h109.633v109.636z"></path>
                            <path d="M292.352 73.089h36.553v36.547h-36.553z"></path>
                        </g>
                    </svg>
                    <p class="font-semibold nowrap pt-3">1. {{ __('setting.SCAN') }}</p>
                    <p class="text-xs">{{ __('setting.The QR code to join the e-queue') }}</p>
                </div>
                <div class="text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" version="1.1"
                        xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:svgjs="http://svgjs.com/svgjs"
                        width="30" height="30" x="0" y="0" viewBox="0 0 48 48"
                        style="enable-background:new 0 0 512 512" xml:space="preserve" class="m-auto fill-gray-700 dark:fill-gray-400">
                        <g
                            transform="matrix(1.0599999999999998,0,0,1.0599999999999998,-1.4400000000000013,-1.4400000000000013)">
                            <path
                                d="M43 9H5a3 3 0 0 0-3 3v24a3 3 0 0 0 3 3h38a3 3 0 0 0 3-3V12a3 3 0 0 0-3-3zm1 27a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V12a1 1 0 0 1 1-1h38a1 1 0 0 1 1 1z"></path>
                            <path
                                d="M24 17h4a1 1 0 0 0 0-2h-4a1 1 0 0 0 0 2zM40 20H24a1 1 0 0 0 0 2h16a1 1 0 0 0 0-2zM40 25H24a1 1 0 0 0 0 2h16a1 1 0 0 0 0-2zM40 30H24a1 1 0 0 0 0 2h16a1 1 0 0 0 0-2zM14 22h-3a4 4 0 0 0-4 4v5a1 1 0 0 0 1 1h9a1 1 0 0 0 1-1v-5a4 4 0 0 0-4-4zm2 8H9v-4a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2zM12.5 21A3.5 3.5 0 1 0 9 17.5a3.5 3.5 0 0 0 3.5 3.5zm0-5a1.5 1.5 0 1 1-1.5 1.5 1.5 1.5 0 0 1 1.5-1.5z"></path>
                        </g>
                    </svg>
                    <p class="font-semibold nowrap pt-3">2. {{ __('setting.ENTER') }} </p>
                    <p class="text-xs">{{ __('text.your details') }}</p>
                </div>
                <div class="text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" version="1.1"
                        xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:svgjs="http://svgjs.com/svgjs"
                        width="30" height="30" x="0" y="0" viewBox="0 0 512 512"
                        style="enable-background:new 0 0 512 512" xml:space="preserve" class="m-auto fill-gray-700 dark:fill-gray-400">
                        <g transform="matrix(1,0,0,1,0,0)">
                            <path
                                d="M302.933 42.667h-51.2c-7.074 0-12.8 5.726-12.8 12.8s5.726 12.8 12.8 12.8h51.2c7.074 0 12.8-5.726 12.8-12.8s-5.726-12.8-12.8-12.8z"></path>
                            <path
                                d="M358.4 0H153.6c-28.228 0-51.2 22.972-51.2 51.2v409.6c0 28.228 22.972 51.2 51.2 51.2h204.8c28.228 0 51.2-22.972 51.2-51.2V51.2c0-28.228-22.972-51.2-51.2-51.2zM384 460.8c0 14.14-11.46 25.6-25.6 25.6H153.6c-14.14 0-25.6-11.46-25.6-25.6V51.2c0-14.14 11.46-25.6 25.6-25.6h204.8c14.14 0 25.6 11.46 25.6 25.6v409.6z"></path>
                            <circle cx="256" cy="443.733" r="25.6"></circle>
                            <circle cx="209.067" cy="55.467" r="12.8"></circle>
                        </g>
                    </svg>
                    <p class="font-semibold nowrap pt-3">3. {{ __('setting.RECEIVE') }} </p>
                    <p class="text-xs">{{ __('setting.an e-ticket and a link with updates on your') }}</p>
                </div>
                <div class="text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" version="1.1"
                        xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:svgjs="http://svgjs.com/svgjs"
                        width="30" height="30" x="0" y="0" viewBox="0 0 24 24"
                        style="enable-background:new 0 0 512 512" xml:space="preserve" class="m-auto fill-gray-700 dark:fill-gray-400">
                        <g transform="matrix(1.09,0,0,1.09,-1.0912048959732026,-1.0993500137329075)">
                            <path
                                d="m7.617 8.712 3.205-2.328c.36-.263.797-.398 1.243-.384a2.616 2.616 0 0 1 2.427 1.82c.186.583.356.977.51 1.182A4.992 4.992 0 0 0 19 11v2a6.987 6.987 0 0 1-5.402-2.547l-.697 3.955 2.061 1.73 2.223 6.108-1.88.684-2.04-5.604-3.39-2.845a2 2 0 0 1-.713-1.904l.509-2.885-.677.492-2.127 2.928-1.618-1.176L7.6 8.7zM13.5 5.5a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm-2.972 13.181-3.214 3.83-1.532-1.285 2.976-3.546.746-2.18 1.791 1.5z"></path>
                        </g>
                    </svg>
                    <p class="font-semibold nowrap pt-3">4. {{ __('setting.RETURN') }}</p>
                    <p class="text-xs">{{ __("setting.When it's your turn") }}</p>
                </div>
            </div>
        @endif
    </div>

<style>
    @media print {
        body * {
            visibility: hidden;
        }

        #qrCodeSection,
        #qrCodeSection * {
            visibility: visible;
        }

        #qrCodeSection {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
    }
</style>

<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('print-qr-code', () => {
            const printContents = document.getElementById('qrCodeSection').innerHTML;
            const originalContents = document.body.innerHTML;
            
            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
            
            // Reinitialize Livewire after printing
            Livewire.reinit();
        });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
   <script>
document.addEventListener('livewire:init', () => {
Livewire.on('saved', () => {
        
        Swal.fire({
            title: "Saved Successfully",
            text: 'Success',
            icon: "success",
            // allowOutsideClick: false,
        }).then((result) => {
            window.location.reload();
        });
    });
    });
   </script>
</div>