<div>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-semibold text-gray-900">{{ __('setting.Edit Screen Template Settings') }}</h1>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if (session()->has('success'))
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif

        <form wire:submit.prevent="save" class="bg-white shadow-md rounded-lg">
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Basic Settings -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('setting.Token Title (Display Screen)') }}*</label>
                    <input type="text" wire:model="token_title" required maxlength="50" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('token_title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('setting.Counter Title (Display Screen)') }}*</label>
                    <input type="text" wire:model="counter_title" required maxlength="50" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('counter_title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('setting.Waiting Queue Label (Display Screen)') }}*</label>
                    <input type="text" wire:model="waiting_queue" required maxlength="50" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('waiting_queue') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('setting.Missed Queue Label (Display Screen)') }}*</label>
                    <input type="text" wire:model="missed_queue" required maxlength="50" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('missed_queue') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('setting.Hold Queue Label (Display Screen)') }}*</label>
                    <input type="text" wire:model="hold_queue" required maxlength="50" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('hold_queue') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                 <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('setting.Disclaimer Label (Display Screen)') }}*</label>
                    <input type="text" wire:model="disclaimer_title" maxlength="50" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('disclaimer_title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('setting.Counter Font Size') }}*</label>
                    <select wire:model="font_size" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @foreach($fontSizes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('font_size') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('setting.Show Queue Number on Display Screen') }}*</label>
                    <select wire:model="show_queue_number" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @foreach($queueNumbers as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('show_queue_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <!-- Colors -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('setting.Font Color') }}*</label>
                    <div class="flex items-center mt-1">
                        <input type="color" wire:model="font_color" required class="h-10 w-14 rounded-l-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <input type="text" wire:model="font_color" required class="flex-1 h-10 rounded-r-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    @error('font_color') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('setting.Background Color') }}*</label>
                    <div class="flex items-center mt-1">
                        <input type="color" wire:model="background_color" required class="h-10 w-14 rounded-l-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <input type="text" wire:model="background_color" required class="flex-1 h-10 rounded-r-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    @error('background_color') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('setting.Current Serving Font Color') }}*</label>
                    <div class="flex items-center mt-1">
                        <input type="color" wire:model="current_serving_fontcolor" required class="h-10 w-14 rounded-l-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <input type="text" wire:model="current_serving_fontcolor" required class="flex-1 h-10 rounded-r-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    @error('current_serving_fontcolor') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('setting.Waiting Queue Background Color') }}*</label>
                    <div class="flex items-center mt-1">
                        <input type="color" wire:model="waiting_queue_bg" required class="h-10 w-14 rounded-l-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <input type="text" wire:model="waiting_queue_bg" required class="flex-1 h-10 rounded-r-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    @error('waiting_queue_bg') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('setting.Missed Queue Background Color') }}*</label>
                    <div class="flex items-center mt-1">
                        <input type="color" wire:model="missed_queue_bg" required class="h-10 w-14 rounded-l-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <input type="text" wire:model="missed_queue_bg" required class="flex-1 h-10 rounded-r-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    @error('missed_queue_bg') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('setting.Hold Queue Background Color') }}*</label>
                    <div class="flex items-center mt-1">
                        <input type="color" wire:model="hold_queue_bg" required class="h-10 w-14 rounded-l-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <input type="text" wire:model="hold_queue_bg" required class="flex-1 h-10 rounded-r-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    @error('hold_queue_bg') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('setting.display behavior of the current serving call') }}*</label>
                    <select wire:model="display_behavior" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="1">{{ __('setting.the current serving call in red color') }}</option>
                        <option value="2">{{ __('setting.the current serving call in red color with blinking effect for a few seconds before reverting to black') }}</option>

                    </select>
                    @error('display_behavior') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <!-- Toggle options -->
                <div>
                    <label class="inline-flex items-center">
                        <input type="checkbox" wire:model="is_header_show" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
                        <span class="ml-2">{{ __('setting.Display Screen Header') }}</span>
                    </label>
                </div>

                <div>
                    <label class="inline-flex items-center">
                        <input type="checkbox" wire:model="is_logo" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
                        <span class="ml-2">{{ __('setting.Display Logo') }}</span>
                    </label>
                </div>
				
				<div>
                    <label class="inline-flex items-center">
                        <input type="checkbox" wire:model="is_location" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
                        <span class="ml-2">Display Location Name</span>
                    </label>
                </div>
				
				 <div>
                    <label class="block text-sm font-medium text-gray-700">Location Font Color</label>
                    <div class="flex items-center mt-1">
                        <input type="color" wire:model="location_fontcolor"  class="h-10 w-14 rounded-l-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <input type="text" wire:model="location_fontcolor"  class="flex-1 h-10 rounded-r-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    @error('location_fontcolor') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Background Color</label>
                    <div class="flex items-center mt-1">
                        <input type="color" wire:model="location_bg" class="h-10 w-14 rounded-l-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <input type="text" wire:model="location_bg" class="flex-1 h-10 rounded-r-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    @error('location_bg') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="inline-flex items-center">
                        <input type="checkbox" wire:model="is_location" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
                        <span class="ml-2">Display Location Name</span>
                    </label>
                </div>
				
				 <div>
                    <label class="block text-sm font-medium text-gray-700"> {{ __('setting.Location Font Color') }}</label>
                    <div class="flex items-center mt-1">
                        <input type="color" wire:model="location_fontcolor"  class="h-10 w-14 rounded-l-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <input type="text" wire:model="location_fontcolor"  class="flex-1 h-10 rounded-r-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    @error('location_fontcolor') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700"> {{ __('setting.Background Color') }}</label>
                    <div class="flex items-center mt-1">
                        <input type="color" wire:model="location_bg" class="h-10 w-14 rounded-l-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <input type="text" wire:model="location_bg" class="flex-1 h-10 rounded-r-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    @error('location_bg') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="inline-flex items-center">
                        <input type="checkbox" wire:model="is_hold_queue" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
                        <span class="ml-2">{{ __('setting.Display Hold Queue') }}</span>
                    </label>
                </div>

                <div>
                    <label class="inline-flex items-center">
                        <input type="checkbox" wire:model="isdisclaimer" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
                        <span class="ml-2">{{ __('setting.Display Show Disclaimer') }}</span>
                    </label>
                </div>

                <div>
                   

				<label class="block text-sm font-medium text-gray-700 mb-2">
					{{ __('setting.Display By') }}
                    </label>

				<select wire:model="is_name_on_display_screen_show"
					class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">

					<option value="1">Token</option>
					<option value="2">Name</option>
					<option value="3">Both</option>

				</select>
                </div>


                  <div>
                    <label class="inline-flex items-center">
                        <input type="checkbox" wire:model="is_skip_call_show" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
                        <span class="ml-2">{{ __('setting.Show Skip Call') }}</span>
                    </label>
                </div>

                <div>
                    <label class="inline-flex items-center">
                        <input type="checkbox" wire:model="is_waiting_call_show" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
                        <span class="ml-2">{{ __('setting.Show Waiting Call') }}</span>
                    </label>
                </div>

                 <div>
                    <label class="inline-flex items-center">
                        <input type="checkbox" wire:model="is_skip_closed_call_from_display_screen" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
                        <span class="ml-2">{{ __('setting.Skip Closed Call From Display Screen') }}</span>
                    </label>
                </div>

            </div>

             <div class="p-3">
                <label class="block text-sm font-medium text-gray-700">{{ __('setting.Display Screen Disclaimer') }}</label>
                <textarea type="text" wire:model="display_screen_disclaimer" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
            </div>

            <!-- DateTime Section -->
            <div class="p-6 border-t border-gray-200">
                <h2 class="text-lg font-medium text-gray-900 mb-4">{{ __('setting.dateTime section') }}</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="inline-flex items-center">
                            <input type="checkbox" wire:model.live="is_datetime_show" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
                            <span class="ml-2">{{ __('setting.Display DateTime') }}</span>
                        </label>
                    </div>

                    @if($is_datetime_show)
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('setting.DateTime Font Color') }}</label>
                            <input type="color" wire:model="datetime_font_color" required class="h-10 w-14 rounded-l-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <input type="text" wire:model="datetime_font_color" required class="flex-1 h-10 rounded-r-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('datetime_font_color') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('setting.DateTime Background Color') }}</label>
                            <input type="color" wire:model="datetime_bg_color" required class="h-10 w-14 rounded-l-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <input type="text" wire:model="datetime_bg_color" required class="flex-1 h-10 rounded-r-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('datetime_bg_color') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('setting.DateTime Position') }}</label>
                            <div class="mt-2">
                                @foreach($positions as $value => $label)
                                    <label class="inline-flex items-center mr-4">
                                        <input type="radio" wire:model="datetime_position" value="{{ $value }}" class="border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
                                        <span class="ml-2">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('datetime_position') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    @endif
                </div>
            </div>
            <!-- Powered By Section -->
            <div class="p-6 border-t border-gray-200">
                <h2 class="text-lg font-medium text-gray-900 mb-4">{{ __('setting.powered by section') }}</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="inline-flex items-center">
                            <input type="checkbox" wire:model="is_powered_by" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
                            <span class="ml-2">{{ __('setting.Display Powered By') }}</span>
                        </label>
                    </div>

                    @if($is_powered_by)
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700">{{ __('setting.Powered Image') }}*</label>
                            <div class="mt-1 flex items-center">
                                @if($powered_image && !$newPoweredImage)
                                    <div class="mr-4">
                                        <img src="{{ url('storage/' . $powered_image) }}" class="h-20 w-auto object-contain">
                                    </div>
                                @endif

                                <input type="file" wire:model="newPoweredImage" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                @error('newPoweredImage') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror

                                @if($newPoweredImage)
                                    <div class="ml-4">
                                        <img src="{{ $newPoweredImage->temporaryUrl() }}" class="h-20 w-auto object-contain">
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Videos Section (conditional) -->
            @if($showVideoSection)
                <div class="p-6 border-t border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">{{ __('setting.videos section') }}</h2>

                    <div class="space-y-4">
                        @foreach($json as $index => $video)
                            <div class="flex items-center space-x-4">
                                <div class="flex-grow">
                                    <label class="block text-sm font-medium text-gray-700">{{ __('setting.youtube video id') }}</label>
                                    <input type="text" wire:model="json.{{ $index }}.yt_vid"  maxlength="50" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <button type="button" wire:click="removeVideo({{ $index }})" class="mt-6 inline-flex items-center px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-error-500 shadow-theme-xs hover:bg-error-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        @endforeach

                        <button type="button" wire:click="addVideo" class="inline-flex items-center px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">
                            {{ __('setting.add more video') }}
                        </button>
                    </div>
                </div>
            @endif

            <!-- Images Section (conditional) -->
            @if($showImageSection)
                <div class="p-6 border-t border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">{{ __('setting.Upload Image Section') }}</h2>

                    <div class="space-y-6">
                        <!-- Existing images -->
                        @if(count($json_data) > 0)
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                @foreach($json_data as $index => $image)
                                    <div class="relative">
                                        <img src="{{ url('storage/' . $image) }}" class="h-40 w-full object-cover rounded">
                                        <button type="button" wire:click="removeImage({{ $index }})" class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- Upload new images -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('setting.Add New Images') }}</label>
                            <input type="file" wire:model="newImages" multiple accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <p class="mt-1 text-sm text-gray-500">{{ __('setting.Upload between 2-5 images (max 3MB each)') }}</p>
                            @error('newImages.*') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Preview new images -->
                        @if($newImages)
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                @foreach($newImages as $index => $image)
                                    <img src="{{ $image->temporaryUrl() }}" class="h-40 w-full object-cover rounded">
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Form Actions -->
            <div class="px-6 py-4 bg-gray-50 text-right rounded-b-lg">
                <button type="submit" class="inline-flex items-center px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">
                    {{ __('setting.Save') }}
                </button>
            </div>
        </form>
    </div>
</div>
