<div class="container mx-auto">
<?php
$background ='#fffff';
$text_color ='#00000';
$category_background ='#fffff';
$buttons_background ='#fffff';

$background = $colorSetting?->page_layout ?? '';
$text_size = $this->siteData?->category_text_font_size ?? 'text-6xl';
$text_color = $colorSetting?->text_layout ?? '#00000';
$category_background = $colorSetting?->categories_background_layout ?? '#fffff';
$text_color_hover = $colorSetting?->hover_text_layout ?? '#00000';
$category_background_hover = $colorSetting?->hover_background_layout ?? '#fffff';
$buttons_background = $colorSetting?->buttons_layout ?? '#00000';
$buttons_background_hover = $colorSetting?->hover_buttons_layout ?? 'rgb(99 102 241 / var(--tw-bg-opacity))';

 ?>
<style>
    body,.queue-footer{
        background-color: <?= $background ?> !important    
    }
    a{
        color:#569FF7;
    }
    .text-color{
        color: <?= $text_color ?> !important;
        background-color: <?= $category_background ?> !important;
        border-color:  <?= $category_background ?>;     

    }
    .text-color:hover{
        color: <?= $text_color_hover ?> !important;
        background-color: <?= $category_background_hover ?> !important;
        border-color:  <?= $category_background ?>;  

    }
    .Qr-text-color{
        color: <?= $text_color ?> !important;
    }

    .queue-footer-button{
        color: <?= $text_color ?> !important;
        background-color: <?= $buttons_background ?> !important;
        border-color:  <?= $buttons_background ?>;    
    }
    .queue-footer-button:hover{
        color: <?= $text_color_hover ?> !important;
        background-color: <?=  $buttons_background_hover ?> !important;
        border-color:  <?= $buttons_background ?> !important;    
    }

.flatpickr-day.flatpickr-disabled, .flatpickr-day.flatpickr-disabled:hover {
    cursor: not-allowed;
    color: rgb(57 57 57 / 48%);
    background: #bfbfbf;
}</style>

    <div class="{{ $locationStep == false ? 'hidden' : '' }} ">
        @if (empty($location) && !empty($allLocations))
            <div class="flex justify-center my-4">
                <select wire:model.change="location" class="border border-gray-300 rounded p-2 m-3 w-2/5">
                    <option value="" disabled>{{ __('text.select location') }}</option>
                    @foreach ($allLocations as $locationId => $locationName)
                        <option value="{{ $locationId }}" class="text-color">{{ $locationName }}</option>
                    @endforeach
                </select>
            </div>
        @endif
    </div>
    <div class="overflow-x-hidden main-container">

       
        <div class="cate_items md:grid {{ $category_row == 'class-cat-1-row' ? 'grid-cols-1' : 'grid-cols-2' }} gap-4 {{ $firstStep == false ? 'hidden' : '' }} ">
            @foreach ($firstCategories as $keyCat => $nameCate)
                <div class="{{ $borderWidth }}  border-solid border-slate-950 rounded-lg m-4 cursor-pointer p-4 cate_item hover:bg-indigo-500 hover:border-indigo-500 hover:text-white text-color"
                    wire:loading.class="opacity-50" wire:click="showFirstChild({{ $nameCate->id }})">

                    <div class="flex items-center justify-center">
                        @if ($siteDetails && $siteDetails->show_cat_icon == App\Models\SiteDetail::STATUS_YES && !empty($nameCate->img))
                            <img src="{{ asset('storage/' . $nameCate->img) }}" class="w-8 md:w-10 lg:w-12 mr-4" />
                        @endif <span
                            class="{{ $fontSize }} {{ $fontFamily }}">{{ $nameCate->name }}</span>
                    </div>


                </div>
            @endforeach
        </div>

        @if (!empty($firstChildren))
            <div class="md:grid {{ $category_row == 'class-cat-1-row' ? 'grid-cols-1' : 'grid-cols-2' }} gap-4 {{ $secondStep == false ? 'hidden' : '' }} ">
                @foreach ($firstChildren as $key => $name)
                    <div class="{{ $borderWidth }}   border-solid border-slate-950 rounded-lg m-4 cursor-pointer p-4 cate_item hover:bg-indigo-500 hover:border-indigo-500 hover:text-white leading-6 text-color"
                        wire:loading.class="opacity-50" wire:click="showSecondChild({{ $key }})"> <span
                            class="{{ $fontSize }} {{ $fontFamily }}">{{ $name }}</span></div>
                @endforeach
            </div>
        @endif

        @if (!empty($secondChildren))
            <div class="md:grid {{ $category_row == 'class-cat-1-row' ? 'grid-cols-1' : 'grid-cols-2' }} gap-4 {{ $thirdStep == false ? 'hidden' : '' }} ">
                @foreach ($secondChildren as $index => $nameC)
                    <div class="{{ $borderWidth }}  border-solid border-slate-950 rounded-lg m-4 cursor-pointer p-4  cate_item hover:bg-indigo-500 hover:border-indigo-500 hover:text-white leading-6 text-color"
                        wire:loading.class="opacity-50" wire:click="showQueueForm({{ $index }})"><span
                            class="{{ $fontSize }} {{ $fontFamily }}"> {{ $nameC }}</span></div>
                @endforeach
            </div>
        @endif
        {{-- h-screen  --}}
        <div class="flex justify-center items-center   {{ $fourthStep == false ? 'hidden' : '' }} ">

            <form wire:submit.prevent="saveAppointmentForm" class="w-full max-w-md">
                <div class="space-y-12">
                    <div class="pb-12">

                        <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8">
                            <div wire:ignore>
                                <input type="text" id="datepicker-booking" placeholder="Select Appointment Date.."
                                    wire:model="appointment_date">
                            </div>

                            @if (!empty($this->availableSlots['start_at']))
                                <div class="col-span-full">
                                    <div class="mt-2">
                                        <div
                                            class="flex gap-2 flex-wrap items-center justify-between @error('start_time') is-invalid @enderror">
                                            @foreach ($this->availableSlots['start_at'] as $value)
                                                <span data-slot="{{ $value }}"
                                                    class="{{ $start_time && $start_time == $value ? 'bg-blue-300' : 'bg-gray-300' }} p-2 rounded-md cursor-pointer"
                                                    onclick="handleSlotClick('{{ $value }}')">
                                                    {{ $value }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                            @endif
                            @error('start_time')
                                <div class="text-red-500">{{ $message }}</div>
                            @enderror
                            @foreach ($dynamicForm as $form)
                                @if (App\Models\CategoryFormField::checkFieldCategory($form['id'], $allCategories))
                                    @if ($form['type'] == App\Models\FormField::TEXT_FIELD || $form['type'] == App\Models\FormField::URL_FIELD)
                                        <div class="col-span-full">
                                            <div class="mt-2">
                                                <label for="{{ $form['label'] }}"
                                                    class="text-center block mb-2 text-sm font-medium text-gray-900 ">{{ $form['label'] }}</label>
                                                <div
                                                    class="flex rounded-md shadow-sm ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md">
                                                    <input
                                                        type="{{ $form['type'] == App\Models\FormField::TEXT_FIELD ? 'text' : 'url' }}"
                                                        id="{{ $form['title'] . '_' . $form['id'] }}"
                                                        class="text-center block flex-1 border-slate-400 bg-transparent py-1.5 pl-1 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6 rounded-lg h-12 @error('dynamicProperties.' . $form['title'] . '_' . $form['id']) is-invalid @enderror"
                                                        placeholder="{{ $form['placeholder'] }}"
                                                        wire:model="dynamicProperties.{{ $form['title'] . '_' . $form['id'] }}"
                                                        minlength="{{ $form['minimum_number_allowed'] }}"
                                                        maxlength="{{ $form['maximum_number_allowed'] }}">
                                                </div>
                                                @error('dynamicProperties.' . $form['title'] . '_' . $form['id'])
                                                    <div class="text-red-500">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    @elseif($form['type'] == App\Models\FormField::DATE_FIELD)
                                        <div class="col-span-full">
                                            <div class="mt-2">
                                                <label for="{{ $form['label'] }}"
                                                    class="text-center block mb-2 text-sm font-medium text-gray-900 ">{{ $form['label'] }}</label>
                                                <div
                                                    class="flex rounded-md shadow-sm ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md">
                                                    <input id="{{ $form['title'] . '_' . $form['id'] }}"
                                                        wire:model="dynamicProperties.{{ $form['title'] . '_' . $form['id'] }}"
                                                        datepicker-format="yyyy-mm-dd" type="text"
                                                        datepicker-autohide placeholder="{{ $form['placeholder'] }}"
                                                        class="dynamicDatePicker text-center block flex-1 border-slate-400 bg-transparent py-1.5 pl-1 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6 rounded-lg h-12 @error('dynamicProperties.' . $form['title'] . '_' . $form['id']) is-invalid @enderror">
                                                </div>
                                                @error('dynamicProperties.' . $form['title'] . '_' . $form['id'])
                                                    <div class="text-red-500">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    @elseif($form['type'] == App\Models\FormField::SELECT_FIELD)
                                        <div class="col-span-full">
                                            <div class="mt-2">
                                                <label for="{{ $form['label'] }}"
                                                    class="{{ $fontSize }} {{ $fontFamily }} text-center block mb-2 font-medium text-gray-900 ">{{ $form['label'] }}</label>
                                                <div
                                                    class="flex rounded-md shadow-sm ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md">
                                                    <select id="{{ $form['title'] . '_' . $form['id'] }}"
                                                        wire:model="dynamicProperties.{{ $form['title'] . '_' . $form['id'] }}"
                                                        class="text-center block flex-1 border-slate-400 bg-transparent py-1.5 pl-1 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6 rounded-lg h-12 @error('dynamicProperties.' . $form['title'] . '_' . $form['id']) is-invalid @enderror">
                                                        <option value=""> Select an option</option>
                                                        @foreach ($form['options'] as $option)
                                                            <option value="{{ $option }}">
                                                                {{ $option }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                @error('dynamicProperties.' . $form['title'] . '_' . $form['id'])
                                                    <div class="text-red-500">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    @elseif($form['type'] == App\Models\FormField::NUMBER_FIELD)
                                        <div class="col-span-full">
                                            <div class="mt-2">
                                                <label for="{{ $form['label'] }}"
                                                    class="text-center block mb-2 text-sm font-medium text-gray-900 ">{{ $form['label'] }}</label>
                                                <div
                                                    class="flex rounded-md shadow-sm ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md">
                                                    <input type="number" id="{{ $form['title'] . '_' . $form['id'] }}"
                                                        class="text-center block flex-1 border-slate-400 bg-transparent py-1.5 pl-1 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6 rounded-lg h-12 @error('dynamicProperties.' . $form['title'] . '_' . $form['id']) is-invalid @enderror"
                                                        placeholder="{{ $form['placeholder'] }}"
                                                        max="{{ $form['maximum_number_allowed'] }}"
                                                        min="{{ $form['minimum_number_allowed'] }}"
                                                        wire:model="dynamicProperties.{{ $form['title'] . '_' . $form['id'] }}">
                                                </div>
                                                @error('dynamicProperties.' . $form['title'] . '_' . $form['id'])
                                                    <div class="text-red-500">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    @elseif($form['type'] == App\Models\FormField::PHONE_FIELD)
                                        <div class="col-span-full">
                                            <div class="mt-2">
                                                <label for="{{ $form['label'] }}"
                                                    class="text-center block mb-2 text-sm font-medium text-gray-900 ">{{ $form['label'] }}</label>
                                                <div
                                                    class="flex rounded-md shadow-sm ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md">
                                                    <input type="text" id="{{ $form['title'] . '_' . $form['id'] }}"
                                                        class="text-center block flex-1 border-slate-400 bg-transparent py-1.5 pl-1 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6 rounded-lg h-12 @error('dynamicProperties.' . $form['title'] . '_' . $form['id']) is-invalid @enderror"
                                                        placeholder="{{ $form['placeholder'] }}"
                                                        max="{{ $form['maximum_number_allowed'] }}"
                                                        min="{{ $form['minimum_number_allowed'] }}"
                                                        wire:model="dynamicProperties.{{ $form['title'] . '_' . $form['id'] }}">
                                                </div>
                                                @error('dynamicProperties.' . $form['title'] . '_' . $form['id'])
                                                    <div class="text-red-500">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    @elseif($form['type'] == App\Models\FormField::TEXTAREA_FIELD)
                                        <div class="col-span-full">
                                            <div class="mt-2">
                                                <label for="{{ $form['label'] }}"
                                                    class="{{ $fontSize }} {{ $fontFamily }} text-center block mb-2 font-medium text-gray-900 ">{{ $form['label'] }}</label>
                                                <div
                                                    class="flex rounded-md shadow-sm ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md">
                                                    <textarea id="{{ $form['title'] . '_' . $form['id'] }}" rows="4"
                                                        class="block p-2.5 w-full text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600focus:ring-2 @error('dynamicProperties.' . $form['title'] . '_' . $form['id']) is-invalid @enderror"
                                                        placeholder="{{ $form['placeholder'] }}" wire:model="dynamicProperties.{{ $form['title'] . '_' . $form['id'] }}"
                                                        minlength="{{ $form['minimum_number_allowed'] }}" maxlength="{{ $form['maximum_number_allowed'] }}"> </textarea>
                                                </div>
                                                @error('dynamicProperties.' . $form['title'] . '_' . $form['id'])
                                                    <div class="text-red-500">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    @elseif($form['type'] == App\Models\FormField::POLICY_FIELD)
                                        <div class="col-span-full">
                                            <div class="mt-2">
                                              
                                                @if ($form['policy'] == 'Text')
                                                    <div class="flex items-center mb-4">
                                                        <input type="checkbox" value=""
                                                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 @error('dynamicProperties.' . $form['title'] . '_' . $form['id']) is-invalid @enderror"
                                                            id="{{ $form['title'] . '_' . $form['id'] }}"
                                                            wire:model="dynamicProperties.{{ $form['title'] . '_' . $form['id'] }}"
                                                            minlength="{{ $form['minimum_number_allowed'] }}"
                                                            maxlength="{{ $form['maximum_number_allowed'] }}"
                                                            @if ($form['mandatory'] == 1) required @endif>

                                                        <label for="{{ $form['title'] . '_' . $form['id'] }}"
                                                            class="{{ $fontSize }} {{ $fontFamily }} ms-2  font-medium text-gray-900 dark:text-gray-750">{!! html_entity_decode($form['policy_content']) !!}</label>
                                                    </div>
                                                @else
                                                $subdomain = request()->getHost();
                                                <label for="{{ $form['title'] . '_' . $form['id'] }}"
                                                        class="ms-2 {{ $fontSize }} {{ $fontFamily }} font-medium font_bold text-gray-900 dark:text-gray-750">
                                                        <a href="{!! $form['policy_url'] !!}"> {!! $form['policy_url'] !!}
                                                        </a></label>
                                                @endif

                                                @error('dynamicProperties.' . $form['title'] . '_' . $form['id'])
                                                    <div class="text-red-500">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    @elseif($form['type'] == App\Models\FormField::CHECKBOX_FIELD)
                                        <div class="flex items-center mb-4">
                                            <input id="{{ $form['title'] . '_' . $form['id'] }}" type="checkbox"
                                                value=""
                                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 @error('dynamicProperties.' . $form['title'] . '_' . $form['id']) is-invalid @enderror"
                                                wire:model="dynamicProperties.{{ $form['title'] . '_' . $form['id'] }}">
                                            <label for="{{ $form['title'] . '_' . $form['id'] }}"
                                                class="ms-2 {{ $fontSize }} {{ $fontFamily }} font-medium text-gray-900 dark:text-gray-750">{{ $form['title'] }}</label>
                                        </div>

                                        @error('dynamicProperties.' . $form['title'] . '_' . $form['id'])
                                            <div class="text-red-500">{{ $message }}</div>
                                        @enderror
                                    @endif
                                @endif
                            @endforeach
                            <div class="col-span-full flex justify-center mt-3">
                                <button
                                    class="flex justify-center bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-3 text-lg px-4 flex-1 rounded-lg queue-footer-button">
                                    <span class="{{ $fontSize }} {{ $fontFamily }}"> {{ __('text.submit') }} </span>
                                    <span wire:loading wire:target='saveAppointmentForm' class="ml-2">
                                        <svg aria-hidden="true"
                                            class="inline w-6 h-6 text-gray-200 animate-spin dark:text-gray-600 fill-gray-600 dark:fill-gray-300"
                                            viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                                                fill="currentColor" />
                                            <path
                                                d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                                                fill="currentFill" />
                                        </svg>
                                    </span>
                                </button>

                            </div>

                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div id="printQueue"></div>
        <div class="flex justify-center footer-section">


            <button type="button" wire:click="goBackFn({{ $totalLevelCount }})"
                class="bg-white text-slate-950 hover:border-indigo-700 hover:bg-indigo-700 hover:text-white text-xl font-bold py-2 px-12 rounded-full border-2 border-gray-800 {{ $totalLevelCount <= 1 ? 'hidden' : '' }} queue-footer-button"
                wire:loading.class="opacity-50">
                {{ __('text.back') }}
            </button>

        </div>
    </div>
</div>
