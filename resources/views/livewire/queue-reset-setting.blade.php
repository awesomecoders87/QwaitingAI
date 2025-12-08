<div class="p-6 dark:bg-gray-900 min-h-screen">
    <div class="w-full mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-2">{{ __('Queue Reset Settings') }}</h1>
            <p class="text-gray-600 dark:text-gray-300">{{ __('Configure queue token and reset settings for your location') }}</p>
        </div>

        @if (session()->has('message'))
            <div class="mb-6 bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-800 text-green-700 dark:text-green-200 px-4 py-3 rounded relative transition-colors duration-200" role="alert">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span class="block sm:inline">{{ session('message') }}</span>
                </div>
            </div>
        @endif
        
        @if (session()->has('error'))
            <div class="mb-6 bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-800 text-red-700 dark:text-red-200 px-4 py-3 rounded relative transition-colors duration-200" role="alert">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <form wire:submit.prevent="save" class="space-y-6">
            <!-- Queue Token Settings Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 transition-colors duration-200">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    {{ __('Queue Token Settings') }}
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('Queue Token Setting') }}
                            <span class="text-gray-400 ml-1" data-tooltip="Control when queue tokens are automatically reset">
                                <i class="fas fa-info-circle"></i>
                            </span>
                        </label>
                        <select wire:model.live="queueToken" id="queue_token" 
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            <option value="default">{{ __('Default') }}</option>
                            <option value="custom">{{ __('Custom Timing') }}</option>
                            <option value="never">{{ __('Never Reset') }}</option>
                        </select>
                        @error('queueToken') 
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="time_input" class="space-y-2 {{ $queueToken !== 'custom' ? 'opacity-50' : '' }}" style="display: {{ $queueToken === 'custom' ? 'block' : 'none' }};">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('Daily Reset Time') }}
                            <span class="text-gray-400 ml-1" data-tooltip="Set the time when tokens should be reset daily">
                                <i class="fas fa-info-circle"></i>
                            </span>
                        </label>
                        <input type="time" 
                               wire:model="queueTokenEndTime" 
                               id="timepicker" 
                               class="mt-1 block w-full pl-3 pr-10 py-2 text-base border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                               @if($queueToken !== 'custom') disabled @endif
                               onclick="this.showPicker()">
                        @error('queueTokenEndTime') 
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700 flex justify-end space-x-3">
                    <button type="button" 
                            wire:click="resetToken" 
                            id="resetTokenBtn"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 {{ $queueToken !== 'never' ? 'hidden' : '' }}">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                        </svg>
                        {{ __('Reset Tokens Now') }}
                    </button>
                    
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        {{ __('Save Changes') }}
                    </button>
                </div>
            </div>

            <!-- Information Card -->
            <div class="bg-blue-50 dark:bg-blue-900/30 rounded-lg p-4 border border-blue-200 dark:border-blue-800/50">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">{{ __('About Queue Token Settings') }}</h3>
                        <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                            <p class="mb-2">{{ __('• Default: Tokens reset at the end of each business day') }}</p>
                            <p class="mb-2">{{ __('• Custom Timing: Set a specific time for daily token resets') }}</p>
                            <p>{{ __('• Never: Tokens will never reset automatically (use with caution)') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <script>
    document.addEventListener('livewire:init', () => {


         Livewire.on('queueSettingUpdated', () => {
       
        Swal.fire({
                title: "Success!",
                text: "Updated successfully!",
                icon: "success",
                confirmButtonText: "OK",
                
            });
    });
    
    });
    
    // Handle token reset response
    Livewire.on('tokenReset', (data) => {
        // Show success message with dark mode support
        const isDarkMode = document.documentElement.classList.contains('dark');
        const alertDiv = document.createElement('div');
        alertDiv.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded shadow-lg transition-all duration-300 transform translate-x-0 opacity-100 ${
            isDarkMode 
                ? 'bg-green-900 border border-green-700 text-green-100' 
                : 'bg-green-100 border border-green-400 text-green-700'
        }`;
        alertDiv.role = 'alert';
        alertDiv.innerHTML = `
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span>Tokens reset successfully!</span>
            </div>
        `;
        document.body.appendChild(alertDiv);
        
        // Remove alert after 3 seconds
        setTimeout(() => {
            alertDiv.remove();
        }, 3000);
    });

    
        // Show/hide time input based on queue token selection
        Livewire.on('queueTokenUpdated', (value) => {
            const timeInput = document.getElementById('time_input');
            const resetBtn = document.getElementById('resetTokenBtn');
            
            if (value === 'custom') {
                timeInput.style.display = 'block';
                timeInput.classList.remove('opacity-50');
                document.getElementById('timepicker').disabled = false;
                resetBtn.classList.add('hidden');
            } else {
                if (value === 'never') {
                    resetBtn.classList.remove('hidden');
                } else {
                    resetBtn.classList.add('hidden');
                }
                timeInput.style.display = 'none';
            }
        });

   
</script>
</div>


