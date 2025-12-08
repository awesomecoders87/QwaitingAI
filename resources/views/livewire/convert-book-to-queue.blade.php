<div class="container mx-auto p-4">
    <div class="text-xl text-center">
        {{ $accountSetting->booking_convert_label ??__('text.Please scan or key in your identity number')}}
    </div>

    <div class="flex justify-center my-4 py-4">
        <form class="w-full max-w-md">
            <div class="space-y-12">
                <div class="pb-12">
                @if($step == 1)
                    <div class="col-span-full">
                    
                        <div class="mt-2">
                            <!-- <label for="label_booking_refID"
                                class="text-center block mb-2 text-sm font-medium text-gray-900 ">{{ $booking_placeholder }}</label> -->
                            <div
                                class="flex rounded-md shadow-sm ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md">
                                <input type="text"
                                    class="text-center block flex-1 border-slate-400 bg-white py-1.5 pl-1 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6 rounded-lg h-12 @error('booking_refID') is-invalid @enderror"
                                    placeholder="{{ $booking_placeholder }}"
                                    wire:model="booking_refID" minlength="2" maxlength="100" />
                            </div>
                            @error('booking_refID')
                                <div class="text-red-500">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    @endif
                    @if($queue_form && $step == 2)
                    <div>
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
                    </div>
                    @endif
                    <div class="col-span-full flex justify-center mt-3">
                        @if($queue_form && $step == 1)
                        <button  type="button" wire:click.prevent='convertToQueueForm'
                            class="flex justify-center bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-3 text-lg px-4 flex-1 rounded-lg">
                            <span> {{ __('text.Next') }} </span>
                            <span  wire:loading wire:target='convertToQueueForm' class="ml-2">
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
                        @else
                        <button
                      type="button" wire:click.prevent='convertToQueue'
                            class="flex justify-center bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-3 text-lg px-4 flex-1 rounded-lg">
                            <span> {{ __('text.enter') }} </span>
                            <span  wire:loading wire:target='convertToQueue' class="ml-2">
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
@endif
                    </div>
                   
                </div>
            </div>
        </form>
    </div>
    <div id="printQueue"></div>
    <div class="flex justify-center footer-section">

        <a href="{{ url('/main') }}"
            class="bg-white text-slate-950 hover:border-indigo-700 hover:bg-indigo-700 hover:text-white text-xl font-bold py-2 px-12 rounded-full border-2 border-gray-800"
            wire:loading.class="opacity-50">
            {{ __('text.home') }}
        </a>

    </div>
</div>
