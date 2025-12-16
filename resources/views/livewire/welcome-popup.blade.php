<div>
    <div 
        x-data="{ 
            show: @js($show),
            init() {
                this.$watch('$wire.show', value => {
                    this.show = value;
                });
            }
        }"
        x-show="show"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0"
        style="display: @if($show) block @else none @endif; z-index: 9999999;"
    >
        <!-- Backdrop - Non-clickable (covers entire page including sidebar/menu) -->
        <div 
            x-show="show"
            class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity"
            style="z-index: 9999998;"
        ></div>

        <!-- Modal Content -->
        <div 
            x-show="show"
            class="relative mx-auto max-w-3xl transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow-xl transition-all sm:my-8"
            style="z-index: 9999999;"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            @click.stop
        >
            <!-- Green Gradient Header -->
            <div class="bg-gradient-to-r from-green-500 to-green-600 h-2"></div>

            <!-- Close Button -->
            <button 
                wire:click="close"
                class="absolute right-4 top-4 z-10 rounded-full bg-white dark:bg-gray-700 p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-400 transition shadow-md"
                aria-label="Close Welcome Popup"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <!-- Content -->
            <div class="p-8">
                <!-- Logo and Welcome Header -->
                <div class="text-center mb-8">
                    <!-- Logo -->
                    <div class="flex items-center justify-center gap-2 mb-4">
                        <div class="w-12 h-12 bg-purple-600 rounded-full flex items-center justify-center">
                            <span class="text-white text-2xl font-bold">W</span>
                        </div>
                        <span class="text-2xl font-semibold text-gray-800 dark:text-white">Waiting</span>
                    </div>
                    
                    <!-- Welcome Title -->
                    <h1 class="text-4xl font-bold text-gray-800 dark:text-white mb-3">
                        Welcome {{ auth()->user()->name ?? 'Admin' }}
                    </h1>
                    
                    <!-- Description -->
                    <p class="text-gray-600 dark:text-gray-300 text-lg">
                        With Qwaiting, you can enhance the experience for your on-site visitors. Let's get started with the essentials!
                    </p>
                </div>

                <!-- Key Features Section -->
                <div class="mb-8">
                    <div class="space-y-4">
                        <!-- Feature 1: Greeting Visitors -->
                        <div class="flex items-start gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-yellow-400 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-1">Greeting Visitors</h3>
                                <p class="text-gray-600 dark:text-gray-300 text-sm">Welcome and manage your visitors efficiently with our queue management system.</p>
                            </div>
                        </div>

                        <!-- Feature 2: Delivering Excellent Service -->
                        <div class="flex items-start gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-yellow-400 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-1">Delivering Excellent Service</h3>
                                <p class="text-gray-600 dark:text-gray-300 text-sm">Streamline your service delivery and ensure customer satisfaction.</p>
                            </div>
                        </div>

                        <!-- Feature 3: Insights for Management -->
                        <div class="flex items-start gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-yellow-400 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-1">Insights for Management</h3>
                                <p class="text-gray-600 dark:text-gray-300 text-sm">Get valuable analytics and insights to improve your operations.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Video Tutorial Section -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Video Tutorial</h2>
                    <div class="relative bg-black rounded-lg aspect-video overflow-hidden">
                        <div class="absolute inset-0 flex items-center justify-center">
                            <button class="w-20 h-20 bg-red-600 rounded-full flex items-center justify-center hover:bg-red-700 transition shadow-lg">
                                <svg class="w-10 h-10 text-white ml-1" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M8 5v14l11-7z"/>
                                </svg>
                            </button>
                        </div>
                        <!-- Video Thumbnail Background (you can replace with actual thumbnail) -->
                        <div class="absolute inset-0 bg-gradient-to-br from-gray-800 to-gray-900 opacity-50"></div>
                    </div>
                    <p class="text-center text-gray-700 dark:text-gray-300 mt-3 font-medium">How to Setup Qwaiting Account?</p>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center pt-6 border-t border-gray-200 dark:border-gray-700">
                    <button 
                        wire:click="redirectToDashboard"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-8 rounded-lg transition duration-200 shadow-md">
                        Explore Dashboard
                    </button>
                    <button 
                        wire:click="close"
                        class="text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 underline">
                        Skip for now
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let welcomePopupComponent = null;

            // Find WelcomePopup component
            function findWelcomePopupComponent() {
                if (welcomePopupComponent) {
                    return welcomePopupComponent;
                }
                
                const allComponents = document.querySelectorAll('[wire\\:id]');
                for (let el of allComponents) {
                    const componentId = el.getAttribute('wire:id');
                    const component = Livewire.find(componentId);
                    if (component && component.__instance?.constructor?.name === 'WelcomePopup') {
                        welcomePopupComponent = component;
                        return component;
                    }
                }
                return null;
            }

            // Note: Popup will only show once after login
            // Navigation detection removed - popup shows only on initial page load if not dismissed

            // Also handle regular page loads - mount() handles this
        });
    </script>
    @endpush
</div>
