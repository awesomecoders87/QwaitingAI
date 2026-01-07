<div>
    <!-- Inline styles to ensure SVG icons have size even if Tailwind build is missing classes -->
    <style>
        .icon-size-lg {
            width: 80px;
            height: 80px;
        }

        .icon-size-md {
            width: 40px;
            height: 40px;
        }

        .icon-size-sm {
            width: 20px;
            height: 20px;
        }
    </style>

    <div class="w-full animate-fade-in-up">

        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2 tracking-tight">
                {{ __('text.Book an Appointment') }}
            </h1>
            <p class="text-lg text-gray-600 dark:text-gray-300">
                {{ __('text.Please select how you would like to meet with us') }}
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 w-full">

            <!-- Virtual Option -->
            <a href="{{ route('book-appointment', ['booking_type' => 'virtual']) }}"
                class="group relative flex flex-col items-center justify-center p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 hover:border-blue-500 hover:shadow-md transition-all duration-300 transform hover:-translate-y-1 overflow-hidden"
                style="text-decoration: none;">

                <div
                    class="absolute inset-0 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-800 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                </div>

                <div
                    class="relative z-10 w-20 h-20 icon-size-lg mb-4 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="w-10 h-10 icon-size-md text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                </div>

                <h2 class="relative z-10 text-xl font-bold text-blue-600 dark:text-blue-400 mb-2 transition-colors">
                    {{ __('text.Virtual Appointment') }}
                </h2>
                <p
                    class="relative z-10 text-center text-sm text-gray-500 dark:text-gray-400 group-hover:text-gray-700 dark:group-hover:text-gray-300 transition-colors">
                    {{ __('text.Connect with us remotely via video call') }}
                </p>

                <div
                    class="relative z-10 mt-4 inline-flex items-center text-gray-900 dark:text-white font-semibold group-hover:translate-x-1 transition-transform">
                    <span>{{ __('text.Select') }}</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 icon-size-sm ml-2" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
            </a>

            <!-- Walking / In-Person Option -->
            <a href="{{ route('book-appointment', ['booking_type' => 'walking']) }}"
                class="group relative flex flex-col items-center justify-center p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 hover:border-emerald-500 hover:shadow-md transition-all duration-300 transform hover:-translate-y-1 overflow-hidden"
                style="text-decoration: none;">

                <div
                    class="absolute inset-0 bg-gradient-to-br from-emerald-50 to-green-50 dark:from-gray-700 dark:to-gray-800 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                </div>

                <div
                    class="relative z-10 w-20 h-20 icon-size-lg mb-4 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="w-10 h-10 icon-size-md text-emerald-600 dark:text-emerald-400" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>

                <h2 class="relative z-10 text-xl font-bold text-black-900 dark:text-emerald-400 mb-2 transition-colors">
                    {{ __('text.Walking Appointment') }}
                </h2>
                <p
                    class="relative z-10 text-center text-sm text-gray-500 dark:text-gray-400 group-hover:text-gray-700 dark:group-hover:text-gray-300 transition-colors">
                    {{ __('text.Visit us in person at our location') }}
                </p>

                <div
                    class="relative z-10 mt-4 inline-flex items-center text-gray-900 dark:text-white font-semibold group-hover:translate-x-1 transition-transform">
                    <span>{{ __('text.Select') }}</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 icon-size-sm ml-2" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
            </a>

        </div>

        <div class="mt-8 text-center">
            <a href="{{ url()->previous() }}"
                class="text-sm text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                ← {{ __('text.Go Back') }}
            </a>
        </div>

    </div>
</div>