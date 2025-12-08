<div class="bg-white">

    {{-- @php 
        if(empty($currentVisitorRecord))
        {
            $room = 'default';
            $queueId = null;
        }
        else
        {
            $room = 'room_' . base64_encode($currentVisitorRecord->queue_id);
            $queueId = base64_encode($currentVisitorRecord->queue_id);
        }
    @endphp --}}

    {{-- @livewire('virtual-meeting-admin', ['room' => $getroom, 'queueId' => $getqueueId]) --}}

    @if ($showVirtualMeeting)

    {{-- @livewire('virtual-meeting-admin', ['room' => $getroom, 'queueId' => $getqueueId], key($getqueueId)); --}}
    <div wire:init="videoToken"></div>
    @endif

    @assets
    <link href="{{asset('/css/app/call.css?v=3.1.0.0')}}" rel="stylesheet" data-navigate-track />
    @endassets
    @php $datetimeFormat = App\Models\AccountSetting::showDateTimeFormat(); @endphp
    <div class="p-4">


        <style>
            aside.fi-sidebar {
                display: none;
            }

            .fi-main {
                max-width: 100%;
                padding-left: 1rem;
                padding-right: 1rem;
            }

            .fi-main header,
            .fi-topbar {
                display: none
            }

            .theme-hold-bg {
                background-color: #96b100;
            }

            .center-btn {
                margin: 5px 0;
            }
        </style>
        <div class="flex justify-between top-header">
            <div class="columns-4 flex gap-3 items-center top-section">
                <div class="full-small flex justify-center main-logo">
                    {{-- @can('Dashboard')
                    <a href="{{ url('/') }}">
                    <img src="{{ url($logo) }}" alt="Logo">
                    </a>
                    @else
                    <img src="{{ url($logo) }}" alt="Logo">
                    @endcan --}}

                    <a href="{{ url('/') }}">
                        <img src="{{ url($logo) }}" alt="Logo">
                    </a>
                </div>
                @if ($siteDetail?->break == App\Models\SiteDetail::STATUS_YES)
                <button type="button" id="breakButton"
                    class="px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">
                    {{ __('text.Break') }}
                </button>
                @endif
                @if ($isEnableholdsms)
                <button type="button" id="holdButton" wire:click.prevent="modelHoldQueue"
                    class="px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">
                    {{ __('text.Hold Queues') }}
                </button>
                @endif
                @if ($isEnableholsuspension)
                <button type="button" id="suspensionButton" wire:click.prevent="modelOpenSuspension"
                    class="px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">
                    {{ __('text.Suspension') }}
                </button>
                @endif
                @if ($siteDetail?->ticket_generation_link == App\Models\SiteDetail::STATUS_YES)
                @can('Register Queue')
                <div class="flex gap-8 items-center">

                    <a href="{{ url($registerqueue?->booking_system == App\Models\AccountSetting::STATUS_ACTIVE ? 'main' : 'queue') }}"
                        target="_blank"
                        class="inline-flex items-center px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">

                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-4 h-4 mr-2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <span>{{ __('text.Register Queue') }}</span>
                    </a>

                </div>
                @endcan
                @endif

                @if ($siteDetail?->reset_cur_serving == App\Models\SiteDetail::STATUS_YES)
                @can('Reset Queue')
                {{-- <div>
                    <button type="button" id="resetQueue"
                        class="inline-flex items-centerpx-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">
                        {{ __('text.Reset Queue') }}
                </button>
            </div> --}}

            @endcan
            @endif

            @if ($siteDetail?->manual_ticket == App\Models\SiteDetail::STATUS_YES)
            @can('Generate Queue')

            {{-- <div>
                    <button type="button" id="generateQueue"
                        class="flex items-center px-4 py-2 text-white bg-green-600 hover:bg-green-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-300">

                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-5 h-5 mr-2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <span>{{ __('text.Generate Queue') }}</span>
            </button>

        </div> --}}
        @endcan
        @endif


        <button onclick="toggleFullScreen(document.body)"
            class="inline-flex items-center px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 hide-small"
            type="button">
            <span class="fi-btn-label">
                <svg width="20" height="20" x="0" y="0" viewBox="0 0 64 64" style="enable-background:new 0 0 512 512"
                    xml:space="preserve" class="">
                    <g>
                        <g xmlns="http://www.w3.org/2000/svg">
                            <g>
                                <g>
                                    <path
                                        d="m53.1 62.2h-42.2c-5.5 0-9.9-4.5-9.9-9.9v-41.4c0-5.4 4.5-9.9 9.9-9.9h42.1c5.5 0 9.9 4.5 9.9 9.9v41.3c.1 5.5-4.4 10-9.8 10zm-42.2-57.2c-3.2 0-5.9 2.7-5.9 5.9v41.3c0 3.3 2.7 5.9 5.9 5.9h42.1c3.3 0 5.9-2.7 5.9-5.9v-41.3c.1-3.2-2.6-5.9-5.8-5.9z"
                                        fill="#ffffff" data-original="#000000" class=""></path>
                                </g>
                            </g>
                        </g>
                        <g xmlns="http://www.w3.org/2000/svg">
                            <g>
                                <g>
                                    <path
                                        d="m51.6 34.9c-1.1 0-2-.9-2-2v-18.7h-18.3c-1.1 0-2-.9-2-2s.9-2 2-2h20.2c1.1 0 2 .9 2 2v20.8c.1 1-.8 1.9-1.9 1.9z"
                                        fill="#ffffff" data-original="#000000" class=""></path>
                                </g>
                            </g>
                        </g>
                        <g xmlns="http://www.w3.org/2000/svg">
                            <g>
                                <g>
                                    <path
                                        d="m32.7 53.8h-20.3c-1.1 0-2-.9-2-2v-20.7c0-1.1.9-2 2-2s2 .9 2 2v18.8h18.2c1.1 0 2 .9 2 2s-.8 1.9-1.9 1.9z"
                                        fill="#ffffff" data-original="#ffffff" class=""></path>
                                </g>
                            </g>
                        </g>
                    </g>
                </svg>
            </span>
        </button>
    </div>
    <div class="columns-5 flex gap-3 ml-lg-auto items-center full-small">
        @if(Auth::check() && !empty(Auth::user()->locations))
        <div class="min-w-10">@livewire('location-selector')</div>
        @endif

        <div class="flex-1">
            <select wire:model.live="selectedCounter"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                <option value=""> {{ __('text.Select Counter') }} </option>

                @foreach ($counters as $key => $counter)
                <option value="{{ $counter->id }}">
                    {{ $counter->name }}
                </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="columns-4 items-center flex px-2" wire:poll.1s>
        <div class="text-sm"> {{\Carbon\Carbon::now()->format($datetimeFormat)}}</div>
    </div>
    <div class="columns-4 items-center flex px-2">
        <div class="text-sm bg-gray-50 border border-gray-300 p-2.5 rounded-lg">{{ Auth::user()->name }} </div>
    </div>
    <div class="columns-4 items-center flex px-2">
        <form method="POST" action="{{ route('tenant.logout') }}">
            @csrf
            <button type="submit"
                class="px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">
                {{ __('text.Sign out') }}
            </button>

        </form>

    </div>
</div>
<div class="flex justify-between pt-3 main-content">
    <div class="w-full pb-4" x-data="{ visible: @entangle('showVirtualMeeting') }" x-show="visible" x-cloak wire:ignore>
        <div class="content-section relative flex flex-col justify-center items-center mb-4 text-center bg-white border border-gray-200 rounded-lg shadow mb-2 p-2 dark:bg-gray-800 dark:border-gray-700 text-xl">

            <div id="video-container" class="grid gap-2 w-full h-[500px] overflow-y-auto p-2"></div>

            <!-- Controls -->
            <div class="mt-4 flex justify-center gap-2">
                <!-- Mic Toggle -->
                <button id="audio-btn"
                    onclick="toggleAudio()"
                    class="audio-control bg-gray-800 text-white p-2 rounded hover:bg-blue-600 transition"
                    title="Toggle Microphone">
                    <span id="audio-icon">
                        <!-- Mic On Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 1v10m0 0a4 4 0 004-4V5a4 4 0 00-8 0v2a4 4 0 004 4zm0 0v4m0 0H8m4 0h4" />
                        </svg>
                    </span>
                </button>

                <!-- Video Button -->
                <button id="video-btn"
                    onclick="toggleVideo()"
                    class="video-control bg-gray-800 text-white p-2 rounded hover:bg-green-600 transition"
                    title="Toggle Video">
                    <span id="video-icon">
                        <!-- Video On Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M4 6h8a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2z" />
                        </svg>
                    </span>
                </button>

                <!-- Leave Button -->
                <button onclick="leaveRoom()"
                    class="bg-red-600 text-white p-2 rounded hover:bg-red-700 transition"
                    title="Leave Meeting">
                    <!-- Leave Icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    @if($showVirtualMeeting == false)
    <div class="w-full pb-4">
        <div class="content-section relative flex flex-col justify-center items-center mb-4 text-center bg-white border border-gray-200 rounded-lg shadow mb-2 p-2 dark:bg-gray-800 dark:border-gray-700 text-xl"
            wire.poll.1s>
            @if (!empty($currentVisitorRecord))
            <div class="absolute left-0 start-0 top-0 flex flex-col space-y-2 p-4 mobile-row">
                @if($siteDetail?->show_call_history == App\Models\SiteDetail::STATUS_YES)
                <div class="flex items-center space-x-3 rtl:space-x-reverse border-2 border-slate-50 rounded-md p-1 cursor-pointer"
                    wire:loading.class="opacity-50" title="View History"
                    wire:click="historyQueue('{{$currentVisitorRecord->id}}','{{$currentVisitorRecord->queue_id}}')">
                    <svg class="fi-sidebar-item-icon h-6 w-6 text-primary-600 dark:text-primary-400"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                </div>
                @endif
                @if($siteDetail?->show_send_sms_button == App\Models\SiteDetail::STATUS_YES)
                <div class="flex items-center space-x-3 rtl:space-x-reverse border-2 border-slate-50 rounded-md p-1 cursor-pointer"
                    wire:loading.class="opacity-50" title="Send SMS"
                    wire:click="sendSMSModal('{{$currentVisitorRecord->id}}','{{$currentVisitorRecord->queue_id}}')">
                    <svg class="fi-sidebar-item-icon h-6 w-6 text-primary-600 dark:text-primary-400"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 0 1-.923 1.785A5.969 5.969 0 0 0 6 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337Z" />
                    </svg>
                </div>
                @endif
                @if($siteDetail?->notes_add_option == App\Models\SiteDetail::STATUS_YES)
                <div class="flex items-center space-x-3 rtl:space-x-reverse border-2 border-slate-50 rounded-md p-1 cursor-pointer"
                    title="Display Screen Message" wire:loading.class="opacity-50" wire:click="sendNote()">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="fi-sidebar-item-icon h-6 w-6 text-primary-600 dark:text-primary-400" fill="none"
                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                    </svg>
                </div>
                @endif
                @if($siteDetail?->activity_log == App\Models\SiteDetail::STATUS_YES)
                <div class="flex items-center space-x-3 rtl:space-x-reverse border-2 border-slate-50 rounded-md p-1 cursor-pointer"
                    title="Activity Log" wire:loading.class="opacity-50" wire:click="showActivityLog()">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="fi-sidebar-item-icon h-6 w-6 text-primary-600 dark:text-primary-400" fill="none"
                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                </div>
                @endif
            </div>

            <div class="flex flex-col space-y-2 font-bold leading-4">

                <span>{{ $siteDetail?->label_current_serving ?? __('text.Current Serving') }}</span>
                <span class="font-bold"> {{ $siteDetail?->label_queue_number ??__('text.Queue Number') }}</span>
                <span class="font-bold text-3xl">
                    @if($display_name)
                    {{ $currentVisitorRecord->name ?? '' }}
                    @else
                    {{ ($currentVisitorRecord->start_acronym ?? '') . '' . $currentVisitorRecord->token ?? '' }}
                    @endif
                    </span>
                <span class="{{ !$isServingTime ? 'hidden' : '' }}">
                    {{ $siteDetail?->label_serving_time ?? __('text.Serving Time') }} </span>
                <span id="serving-time-text" wire:ignore class="{{ !$isServingTime ? 'hidden' : '' }}">00:00:00</span>
                @if($categoriesShow)
                <span>

                    @php

                    $category = App\Models\Category::viewCategoryName($currentVisitorRecord->category_id);
                    $subcategory = App\Models\Category::viewCategoryName($currentVisitorRecord->sub_category_id);
                    $childcategory = App\Models\Category::viewCategoryName($currentVisitorRecord->child_category_id);


                    if(!empty($currentVisitorRecord->category_id))
                    {
                    if(isset($translations[$category][$language]) && !empty($translations[$category][$language]))
                    {
                    $category = $translations[$category][$language];
                    }
                    }
                    else
                    {
                    $category = '';
                    }

                    if(!empty($currentVisitorRecord->sub_category_id))
                    {
                    if(isset($translations[$subcategory][$language]) && !empty($translations[$subcategory][$language]))
                    {
                    $subcategory = '-> ' . $translations[$subcategory][$language];
                    }
                    else
                    {
                    $subcategory = '-> ' . $subcategory;
                    }
                    }
                    else
                    {
                    $subcategory = '';
                    }

                    if(!empty($currentVisitorRecord->child_category_id))
                    {
                    if(isset($translations[$childcategory][$language]) && !empty($translations[$childcategory][$language]))
                    {
                    $childcategory = '-> ' . $translations[$childcategory][$language];
                    }
                    else
                    {
                    $childcategory = '-> ' . $childcategory;
                    }
                    }
                    else
                    {
                    $childcategory = '';
                    }

                    @endphp

                    {{ $category }}
                    {{ $subcategory }}
                    {{ $childcategory }}
                </span>
                @endif
                 @if($display_name == 0)
                <span>{{ $currentVisitorRecord->name ?? '' }}</span>
                @endif
                @if (!empty($currentVisitorRecord->phone))
                <span class="flex text-center justify-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                    </svg>
                    {{ $currentVisitorRecord->phone }}
                </span>
                @endif
                <span> {{ $siteDetail?->label_counter ?? __('text.Counter') }}:
                    {{ !empty($currentVisitorRecord->counter) ? $currentVisitorRecord->counter->name : __('text.N/A') }}
                </span>
                @if (!empty($currentVisitorRecord->transfer_id))
                <span> {{__('text.Assigned to') }}
                    {{ App\Models\Category::viewCategoryName($currentVisitorRecord->transfer_id) }} </span>
                @endif
                @if (!empty($currentVisitorRecord->arrives_time))
                <span>{{ $siteDetail?->label_issue_date ?? __('text.Issue Date') }}:
                    {{ Carbon\Carbon::parse($currentVisitorRecord->arrives_time)->format($datetimeFormat) }}
                    @endif
                </span>

                @php
                $formFields = json_decode($currentVisitorRecord->json, true);
                @endphp

                @if(isset($formFields['type']) && $formFields['type'] == 'Virtual')
              
                <div class="flex justify-center">
                    <button type="button" class="w-16 py-2 px-2 text-xs font-medium text-white rounded bg-brand-500 shadow-theme-xs hover:bg-brand-600" wire:click="joinCall">
                        Join Call
                    </button>
                </div>
                @endif

            </div>
            @else
            <div>
                <span> {{ $siteDetail?->label_no_call ?? __('text.No Call') }} </span>
            </div>
            @endif
        </div>
        @if ($siteDetail?->total_served == App\Models\SiteDetail::STATUS_YES)
        <div
            class="flex flex-col items-center text-center bg-white border border-gray-200 rounded-lg shadow p-2 dark:bg-gray-800 text-xl font-bold">
            <div class="flex flex-col  text-center">
                <span> {{ $siteDetail?->label_total_served_token ?? __('text.Total Served Tokens') }}</span>
                <span>
                    @if($siteDetail?->total_call_count == App\Models\Queue::SERVED_MISSED)
                    {{ count( $tokenServed ) + count($missedCalls) }}
                    @else
                    {{ count( $tokenServed ) }}
                    @endif
                </span>
            </div>
        </div>
        @endif
    </div>
    @endif

    <div class="columns-3 flex flex-col space-y-2 px-4 button-section pb-4">
        @if(empty($nextStorageId))
        <button type="button" wire:click="nextCall()" wire:loading.attr="disabled" wire:target="nextCall"
            class="px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 center-btn">
            <span>{{ $siteDetail?->label_next ?? 'Next' }}</span>
        </button>

        @else
        <button type="button" wire:click="nextCall({{ $nextId }}, {{ $nextStorageId }})" wire:loading.attr="disabled"
            wire:target="nextCall"
            class="px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 center-btn">
            <span>{{ $siteDetail?->label_next ?? 'Next' }}</span>
        </button>

        @endif

        @if (!empty($currentVisitorRecord))

        @if($siteDetail?->is_transfer_option == App\Models\SiteDetail::STATUS_YES)
        {{-- <button type="button" @click="$dispatch('open-modal', {id: 'myModalTransfer'})"
                    class="px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 center-btn">
                    {{ $siteDetail?->label_transfer ?? 'Transfer' }}
        </button> --}}
        <button type="button" wire:click.prevent="openmyModalTransfer"
            class="px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 center-btn">
            {{ $siteDetail?->label_transfer ?? 'Transfer' }}
        </button>

        @endif


        @if($siteDetail?->staff_rating == App\Models\SiteDetail::STATUS_YES)
        <button type="button"
            class="{{ $isCloseBtn == true ? 'hidden' : '' }} px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 center-btn"
            onclick="ratingService()">
            {{ $siteDetail?->label_close ?? 'Close' }}
        </button>

        @else
        <button type="button" wire:click="closeCall"
            class="{{ $isCloseBtn == true ? 'hidden' : '' }} px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 center-btn">
            {{ $siteDetail?->label_close ?? 'Close' }}
        </button>

        @endif

        @if($showStartBtn == true)
        <button type="button" wire:click="startCall({{ $currentVisitorId }})"
            class="{{ $currentVisitorRecord->status }} {{ $isStartBtn == false ? 'hidden' : '' }} px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 center-btn">
            {{ $siteDetail?->label_start ?? 'Start' }}
        </button>
        @endif
        @if($siteDetail?->is_missed_call == App\Models\SiteDetail::STATUS_YES)
        <button type="button" wire:click="skipCall({{ $currentVisitorId }})"
            class="px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 center-btn">
            {{ $siteDetail?->label_skip ?? 'Missed' }}
        </button>
        @endif
        @if($siteDetail?->is_recall_button == App\Models\SiteDetail::STATUS_YES)
        <button type="button" wire:click="reCall({{ $currentVisitorId }})"
            class="px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 center-btn">
            {{ $siteDetail?->label_recall ?? 'Recall' }}
        </button>
        @endif
        @if($siteDetail?->is_move_back == App\Models\SiteDetail::STATUS_YES)
        <button type="button" wire:click="moveBack({{ $currentVisitorId }})"
            class="px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 center-btn">
            {{ $siteDetail?->label_move_back ?? 'Move Back' }}
        </button>
        @endif

        @if($siteDetail?->is_client_update == App\Models\SiteDetail::STATUS_YES)
        <button type="button" wire:click.prevent="modelEditCurrentVisitor"
            class="px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 center-btn">
            {{ __('text.Edit Visitor') }}
        </button>
        @endif

        @endif
    </div>
    <div class="visitor-section">
        <div class="flex flex-col gap-2 mb-6 border-2 h-full rounded">
            <div class="text-center p-2 rounded-md theme-bg text-white"> {{ $queuesCount }}
                {{ $siteDetail?->label_visitor_waiting ?? 'Visitors are waiting' }}
            </div>
            <div class="px-3">
                <input type="text"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                    placeholder="{{__('text.Search')}}" required wire:model.live.debounce.250ms="term" />
            </div>
            @persist('scrollbar')

            <div class="w-full h-200 overflow-auto px-3 visitor-calls" wire:scroll.debounce.100ms="loadMoreVisitor">
                @forelse($queues as $key=>$innerQueue)

                <div class="w-full max-w-sm bg-white border border-gray-200 cursor-pointer rounded-lg shadow mb-2 p-2 dark:bg-gray-800 dark:border-gray-700 text-small"
                    wire:key="{{$innerQueue->queue_id}}"
                    wire:click="menuOverlay({{$innerQueue->queue_id}},{{$innerQueue->id}})"
                    wire:loading.class="opacity-50">
                    @if($categoriesShow)
                    <div>

                        @php

                        $category = App\Models\Category::viewCategoryName($innerQueue->category_id);
                        $subcategory = App\Models\Category::viewCategoryName($innerQueue->sub_category_id);
                        $childcategory = App\Models\Category::viewCategoryName($innerQueue->child_category_id);


                        if(!empty($innerQueue->category_id))
                        {
                        if(isset($translations[$category][$language]) && !empty($translations[$category][$language]))
                        {
                        $category = $translations[$category][$language];
                        }
                        }
                        else
                        {
                        $category = '';
                        }

                        if(!empty($innerQueue->sub_category_id))
                        {
                        if(isset($translations[$subcategory][$language]) && !empty($translations[$subcategory][$language]))
                        {
                        $subcategory = '-- ' . $translations[$subcategory][$language];
                        }
                        else
                        {
                        $subcategory = '-- ' . $subcategory;
                        }
                        }
                        else
                        {
                        $subcategory = '';
                        }

                        if(!empty($innerQueue->child_category_id))
                        {
                        if(isset($translations[$childcategory][$language]) && !empty($translations[$childcategory][$language]))
                        {
                        $childcategory = '--' . $translations[$childcategory][$language];
                        }
                        else
                        {
                        $childcategory = '--' . $childcategory;
                        }
                        }
                        else
                        {
                        $childcategory = '';
                        }

                        @endphp


                        {{ $category }}

                        {{ $subcategory  }}
                        {{ $childcategory }}
                    </div>
                    @endif
                    <div class="font-bold">
                         @if($display_name)
                         {{ !empty($innerQueue->name) ? $innerQueue->name : ''}}
                         @else
                         {{ !empty($innerQueue->name) ? $innerQueue->name : ''}}
                        ({{!empty($innerQueue->start_acronym) ?$innerQueue->start_acronym :'' }}{{!empty($innerQueue->token) ?$innerQueue->token :'' }})
                         @endif
                       
                        @if ($innerQueue->is_arrived == App\Models\Queue::STATUS_NO)
                        @if (!empty($innerQueue->late_duration))
                        <span class="px-2 py-1 text-yellow-800 bg-yellow-200 text-sm font-semibold rounded-lg">
                            {{ $innerQueue->late_duration }} {{ __('text.min') }}
                        </span>

                        @endif
                        @else
                        @if ($innerQueue->is_arrived == App\Models\Queue::STATUS_YES)
                        <span class="px-2 py-1 text-yellow-800 bg-yellow-200 text-sm font-semibold rounded-lg">
                            {{ __('text.arrived') }}
                        </span>

                        @endif
                        @endif
                    </div>
                    @if (!empty($innerQueue->phone))
                    <div class="flex gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                        </svg>
                        {{ $innerQueue->phone }}
                    </div>
                    @endif
                    @if (!empty($innerQueue->transfer_id))
                    <div> <span> Assigned to
                            {{ App\Models\Category::viewCategoryName($innerQueue->transfer_id) }} </span>
                    </div>
                    @endif
                    <div>
                        @if (!empty($innerQueue->arrives_time))
                        {{ $siteDetail?->label_issue_date ?? "__('text.Issue Date')" }}:
                        {{ Carbon\Carbon::parse($innerQueue->arrives_time)->format($datetimeFormat) }}
                        @endif
                    </div>

                    {{-- Waiting Clock & Time Calculation --}}

                    @if(isset($siteDetail) && $siteDetail->is_waiting_time != 1)
                    <div class="flex items-center gap-2 mt-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-5 h-5 text-gray-500 animate-spin">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m4-2a8 8 0 1 1-8-8" />
                        </svg>
                        <span>
                            {{ __('text.waiting') }}:
                            <!-- {{ ($key + 1) * $siteDetail?->estimate_time }} mins -->
                            <!-- {{ number_format(abs(now()->diffInSeconds(Carbon\Carbon::parse($innerQueue->datetime))) / 60, 0) }} mins -->

                            @php
                            // or get from DB/session directly

                            $seconds = abs(
                            now($timezone)->diffInSeconds(
                            Carbon\Carbon::parse($innerQueue->datetime, $timezone)
                            )
                            );
                            $minutes = floor($seconds / 60);
                            @endphp

                            @if ($minutes >= 60)
                            {{ floor($minutes / 60) }}h {{ $minutes % 60 }}m
                            @else
                            {{ $minutes }} {{ __('text.mins') }}
                            @endif

                        </span>
                    </div>

                    @endif
                </div>



                @empty
                <h5> {{ $siteDetail?->label_no_call ?? 'No visitor' }} </h5>
                @endforelse

            </div>
            @endpersist

        </div>
    </div>
</div>
@if($modelmyModalTransfer)
<div x-data="{ open: opem }" x-show="open" @open-modal.window="if ($event.detail.id === 'myModalTransfer') open = true"
    @close-modal.window="if ($event.detail.id === 'myModalTransfer') open = false" @keydown.escape.window="open = false"
    class="fixed left-0 top-0 z-99999 flex h-screen w-full flex-col overflow-y-auto overflow-x-hidden p-6 dark:bg-gray-900 lg:p-10">
    <div class="modal-close-btn fixed inset-0 h-full w-full bg-gray-800/50"></div>

    <!-- Modal Content -->
    <div @click.outside="open = false"
        class="relative m-auto w-full max-w-[600px] rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-10">

        <button wire:click.prevent="modelclose" type="button"
            class="absolute right-3 top-3 z-999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-100 text-gray-400 transition-colors hover:bg-gray-200 hover:text-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white sm:right-6 sm:top-6 sm:h-11 sm:w-11">
            <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z"
                    fill=""></path>
            </svg>
        </button>

        <!-- Modal Content -->
        <div>

            <!-- Modal Header -->
            <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                {{ __('text.Transfer Call') }}
            </div>

            <!-- Modal Body -->
            <div class="w-full mt-4 max-h-96 overflow-y-auto">
                <ul class="text-left text-gray-500 dark:text-gray-400 grid grid-cols-1">
                    @foreach ($categories as $key => $value)
                    <li class="border-b border-gray-100 rounded-md p-2 m-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700"
                        wire:loading.class="opacity-50" wire:click="transferCall({{ $key }})">
                        {{ $value }}
                    </li>
                    @php
                    $secondChilds = App\Models\Category::getPluckNames($key);
                    @endphp
                    @if (!empty($secondChilds))
                    @foreach ($secondChilds as $keyChild => $schid)
                    <li class="border-b border-slate-50 rounded-md p-2 m-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700"
                        wire:loading.class="opacity-50" wire:click="transferCall({{ $keyChild }})">
                        >> {{ $schid }}
                    </li>
                    @php
                    $secondSubChilds = App\Models\Category::getPluckNames($keyChild);
                    @endphp
                    @if (!empty($secondSubChilds))
                    @foreach ($secondSubChilds as $keySubChild => $subchid)
                    <li class="border-b border-slate-50 rounded-md p-2 m-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700"
                        wire:loading.class="opacity-50" wire:click="transferCall({{ $keySubChild }})">
                        >> >> {{ $subchid }}
                    </li>
                    @endforeach
                    @endif
                    @endforeach
                    @endif
                    @endforeach
                </ul>
            </div>

            <!-- Modal Footer -->

            <div class="mt-8 flex w-full items-center justify-end gap-3">
                <button wire:click.prevent="modelclose" type="button" type="button"
                    class="flex justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                    {{ __('text.Close') }}
                </button>

            </div>

        </div>
    </div>
</div>
@endif
@if($modelmenuOverlayRandom)
<div x-data="{ open: open }" x-show="open"
    @open-modal.window="if ($event.detail.id === 'menuOverlayRandom') open = true"
    @close-modal.window="if ($event.detail.id === 'menuOverlayRandom') open = false"
    @keydown.escape.window="open = false"
    class="fixed inset-0 flex items-center justify-center p-5 overflow-y-auto modal z-99999">

    <div class="modal-close-btn fixed inset-0 h-full w-full bg-gray-800/50"></div>

    <!-- Modal Content -->
    <div @click.outside="open = false"
        class="relative w-full max-w-[600px] rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-10">

        <button wire:click.prevent="modelclose" type="button"
            class="absolute right-3 top-3 z-999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-100 text-gray-400 transition-colors hover:bg-gray-200 hover:text-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white sm:right-6 sm:top-6 sm:h-11 sm:w-11">
            <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z"
                    fill=""></path>
            </svg>
        </button>

        <!-- Modal Header -->
        <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
            {{ __('text.Choose Your Next Step') }}
        </div>

        <!-- Modal Body -->
        <div class="w-full mt-4">
            <ul class="text-left text-gray-500 dark:text-gray-400 grid grid-cols-2 gap-2">
                @if(empty($randomQueueStorageID))
                <li class="flex items-center justify-center text-center border-2 border-gray-100 rounded-md p-2 m-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700"
                    wire:loading.class="opacity-50" wire:click="nextCall({{ $randomQueueID }})">
                    {{ __('text.Call') }}
                </li>
                @else
                <li class="flex items-center justify-center text-center border-2 border-gray-100 rounded-md p-2 m-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700"
                    wire:loading.class="opacity-50"
                    wire:click="nextCall({{ $randomQueueID }}, {{ $randomQueueStorageID }})">
                    {{ __('text.Call') }}
                </li>
                @endif
                @if($siteDetail?->is_hold == App\Models\SiteDetail::STATUS_YES)
                <li class="flex items-center justify-center text-center border-2 border-gray-100 rounded-md p-2 m-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700"
                    wire:loading.class="opacity-50" wire:click="holdCall({{ $randomQueueStorageID }})">
                    {{ __('text.Hold') }}
                </li>
                @endif
                @if($siteDetail?->is_cancelled_queue == App\Models\SiteDetail::STATUS_YES)

                <li class="flex items-center justify-center text-center border-2 border-gray-100 rounded-md p-2 m-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700"
                    wire:loading.class="opacity-50" wire:click="cancelCall({{ $randomQueueStorageID }})">
                    {{ __('text.Cancel') }}
                </li>
                @endif
                @if($siteDetail?->is_transfer_option == App\Models\SiteDetail::STATUS_YES)
                <li class="flex items-center justify-center text-center border-2 border-gray-100 rounded-md p-2 m-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700"
                    wire:loading.class="opacity-50" wire:click="openTransferModal({{ $randomQueueID }})">
                    {{ $siteDetail?->label_transfer ?? __('text.Transfer') }}
                </li>
                @endif

                @if($siteDetail?->show_send_sms_button == App\Models\SiteDetail::STATUS_YES)
                <li class="flex items-center justify-center text-center border-2 border-gray-100 rounded-md p-2 m-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700"
                    wire:loading.class="opacity-50" wire:click="sendSMSModal({{ $randomQueueStorageID }})">
                    {{ __('text.Send SMS') }}
                </li>
                @endif

                @if($siteDetail?->is_client_update == App\Models\SiteDetail::STATUS_YES)

                @endif

                @if($siteDetail?->show_call_history == App\Models\SiteDetail::STATUS_YES)
                <li class="flex items-center justify-center text-center border-2 border-slate-50 rounded-md p-2 m-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700"
                    wire:loading.class="opacity-50"
                    wire:click="historyQueue('{{ $randomQueueStorageID }}', '{{ $randomQueueID }}')">
                    {{ __('text.History') }}
                </li>
                @endif
            </ul>
        </div>

        <!-- Modal Footer -->
        <div class="flex items-center justify-end w-full gap-3 mt-8">
            <button wire:click.prevent="modelclose" type="button" type="button"
                class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 sm:w-auto">
                {{ __('text.Close') }}
            </button>
        </div>
    </div>
</div>
@endif
@if($modelSendsms)
<div x-data="{ open: open }" x-show="open" @open-modal.window="if ($event.detail.id === 'sendSMSModal') open = true"
    @close-modal.window="if ($event.detail.id === 'sendSMSModal') open = false" @keydown.escape.window="open = false"
    class="fixed inset-0 flex items-center justify-center p-5 overflow-y-auto modal z-99999">

    <div class="modal-close-btn fixed inset-0 h-full w-full bg-gray-800/50 "></div>

    <div @click.outside="open = false"
        class="relative w-full max-w-[600px] rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-10">

        <!-- Modal Content -->
        <div>

            <!-- Modal Header -->
            <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                {{ __('text.Send SMS') }}
            </div>

            <!-- Modal Body -->
            <div class="w-full mt-4">
                <form wire:submit.prevent="sendSMS">
                    <textarea wire:model="sms" rows="5"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        required></textarea>

                    <div class="flex items-center justify-end w-full gap-3 mt-8">
                        <!-- Submit Button -->
                        <button type="submit"
                            class="flex justify-center rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                            {{ __('text.Ok') }}
                        </button>

                        <!-- Cancel Button -->
                        <button type="button" @click="$dispatch('close-modal', {id: 'sendSMSModal'})"
                            class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 sm:w-auto">
                            {{ __('text.Cancel') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@if($modelholdsms)
<div x-data="{ open: open }" x-show="open" @open-modal.window="if ($event.detail.id === 'holdsms') open = true"
    @close-modal.window="if ($event.detail.id === 'holdsms') open = false" @keydown.escape.window="open = false"
    class="fixed inset-0 flex items-center justify-center p-5 overflow-y-auto modal z-99999">

    <div class="modal-close-btn fixed inset-0 h-full w-full bg-gray-800/50 "></div>

    <div @click.outside="open = false"
        class="relative w-full max-w-[600px] rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-10">

        <!-- Modal Content -->
        <div>

            <!-- Modal Header -->
            <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                {{ __('text.Send SMS') }}
            </div>

            <!-- Modal Body -->
            <div class="w-full mt-4">
                <form wire:submit.prevent="holdSendSMS">
                    <textarea wire:model="holdsms" rows="5"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        required></textarea>

                    <div class="flex items-center justify-end w-full gap-3 mt-8">
                        <!-- Submit Button -->
                        <button type="submit"
                            class="flex justify-center rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                            {{ __('text.Ok') }}
                        </button>

                        <!-- Cancel Button -->
                        <button type="button" @click="$dispatch('close-modal', {id: 'holdsms'})"
                            class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 sm:w-auto">
                            {{ __('text.Cancel') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

@if($modelSuspension)
<div x-data="{ open: open }" x-show="open" @open-modal.window="if ($event.detail.id === 'holdSuspension') open = true"
    @close-modal.window="if ($event.detail.id === 'holdSuspension') open = false" @keydown.escape.window="open = false"
    class="fixed inset-0 flex items-center justify-center p-5 overflow-y-auto modal z-99999">

    <div class="modal-close-btn fixed inset-0 h-full w-full bg-gray-800/50 "></div>

    <div @click.outside="open = false"
        class="relative w-full max-w-[600px] rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-10">

        <!-- Modal Content -->
        <div>

            <!-- Modal Header -->
            <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                {{ __('text.Suspension Queue and Bookings') }}
            </div>

            <!-- Modal Body -->
            <div class="w-full mt-4">
                <form wire:submit.prevent="suspensionSendData">
                    <select wire:model="actionType"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 mb-3">
                        <option value=""> {{ __('text.Select Type') }} </option>
                        <option value="appointment">{{ __('text.Appointment') }}</option>
                        <option value="queue">{{ __('text.Queue') }}</option>
                        <option value="appointment_and_queue">{{ __('text.Appointment and Queue') }}</option>

                    </select>
                    <select wire:model="notificationType"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 mb-3">
                        <option value=""> {{ __('text.Select Notifcation') }} </option>
                        <option value="email">{{ __('text.Email') }}</option>
                        <option value="sms">{{ __('text.SMS') }}</option>
                        <option value="sms_and_email">{{ __('text.Email and SMS') }}</option>
                    </select>
                    <textarea wire:model="suspensionReason" rows="5" placeholder="Enter the Reason"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        required></textarea>

                    <div class="flex items-center justify-end w-full gap-3 mt-8">
                        <!-- Submit Button -->
                        <button type="submit"
                            class="flex justify-center rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                            {{ __('text.Ok') }}
                        </button>

                        <!-- Cancel Button -->
                        <button type="button" @click="$dispatch('close-modal', {id: 'holdSuspension'})"
                            class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 sm:w-auto">
                            {{ __('text.Cancel') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

@if($modelCallHistory)
<div x-data="{ open: open }" x-show="open" @open-modal.window="if ($event.detail.id === 'unholdCall') open = true"
    @close-modal.window="if ($event.detail.id === 'unholdCall') open = false" @keydown.escape.window="open = false"
    class="fixed left-0 top-0 z-99999 flex h-screen w-full flex-col overflow-y-auto overflow-x-hidden p-6 dark:bg-gray-900 lg:p-10">
    <div class="modal-close-btn fixed inset-0 h-full w-full bg-gray-800/50"></div>

    <!-- Modal Content -->
    <div @click.outside="open = false"
        class="relative m-auto w-full max-w-[800px] rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-10">
        <button wire:click.prevent="modelclose" type="button"
            class="absolute right-3 top-3 z-999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-100 text-gray-400 transition-colors hover:bg-gray-200 hover:text-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white sm:right-6 sm:top-6 sm:h-11 sm:w-11">
            <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z"
                    fill=""></path>
            </svg>
        </button>

        <!-- Modal Content -->
        <div>

            <!-- Modal Header -->
            <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                {{ __('text.Call History') }}
            </div>

            <!-- Modal Body -->
            <div class="w-full flex flex-wrap mt-4">
                <!-- Queue Details -->
                <div class="flex-1 p-4 border-e border-gray-600">
                    <ul class="space-y-4 text-left text-gray-500 dark:text-gray-400">
                        <li class="flex justify-between py-2">
                            <span class="text-gray-600">{{ __('text.Token') }}</span>
                            <span
                                class="font-semibold">{{$holdCurrentQueue?->start_acronym.''.$holdCurrentQueue?->token}}</span>
                        </li>

                        @if(!empty($holdCurrentQueue->category_id))
                        <li class="flex justify-between py-2">
                            <span class="text-gray-600">{{__('text.Level')}} 1:</span>
                            <span
                                class="font-semibold text-gray-900">{{ App\Models\Category::viewCategoryName($holdCurrentQueue->category_id) }}</span>
                        </li>
                        @endif
                        @if(!empty($holdCurrentQueue->sub_category_id))
                        <li class="flex justify-between py-2">
                            <span class="text-gray-600">{{__('text.Level')}} 2:</span>
                            <span
                                class="font-semibold text-gray-900">{{ App\Models\Category::viewCategoryName($holdCurrentQueue->sub_category_id) }}</span>
                        </li>
                        @endif
                        @if(!empty($holdCurrentQueue->child_category_id))
                        <li class="flex justify-between py-2">
                            <span class="text-gray-600">{{__('text.Level')}} 3:</span>
                            <span
                                class="font-semibold text-gray-900">{{ App\Models\Category::viewCategoryName($holdCurrentQueue->child_category_id) }}</span>
                        </li>
                        @endif

                        @forelse($userDetails as $key => $userD)
                        <li class="flex justify-between py-2">
                            <span class="text-gray-600">{{ App\Models\FormField::viewLabel($team_id, $key) }}</span>
                            <span class="font-semibold text-gray-900">{{ $userD }}</span>
                        </li>
                        @empty
                        {{__('text.No user details')}}
                        @endforelse

                        @if(!empty($holdCurrentQueue))
                        <li class="flex text-center">
                            <button type="button"
                                wire:click="nextCall({{$holdCurrentQueue?->queue_id}},{{$holdCurrentQueue?->id}})"
                                class="px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">
                                {{ __('text.Take Call') }}
                            </button>
                        </li>
                        @endif
                    </ul>
                </div>

                <!-- Activity Logs -->
                <div class="flex-1 p-4 overflow-y-auto" style="height: 350px">
                    @if($siteDetail?->activity_log == App\Models\SiteDetail::STATUS_YES)
                    @forelse($activityLogs as $index => $log)
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="h-6 w-6 rounded-full flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </div>
                        </div>
                        <div class="ps-4 flex-1">
                            <div class="flex justify-between items-center space-y-2">
                                <p class="font-medium text-gray-600">{{ $log->text }} {{ __('text.by') }} {{ $log->createdBy?->name }}</p>
                                <span
                                    class="text-gray-600 text-sm">{{ Carbon\Carbon::parse($log->created_at)->format($datetimeFormat) }}</span>
                            </div>
                        </div>
                    </div>
                    @empty
                    {{ __('text.No logs found') }}
                    @endforelse
                    @endif
                </div>
            </div>

            <!-- Modal Footer -->

            <div class="mt-8 flex w-full items-center justify-end gap-3">
                <button wire:click.prevent="modelclose" type="button" type="button"
                    class="flex justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                    {{ __('text.Cancel') }}
                </button>

            </div>

        </div>
    </div>
</div>
@endif

@if($modelEstimateNotes)
<div x-data="{ open: open }" x-show="open" @open-modal.window="if ($event.detail.id === 'estimateNote') open = true"
    @close-modal.window="if ($event.detail.id === 'estimateNote') open = false" @keydown.escape.window="open = false"
    class="fixed inset-0 flex items-center justify-center p-5 overflow-y-auto modal z-99999">

    <div class="modal-close-btn fixed inset-0 h-full w-full bg-gray-800/50 "></div>

    <div @click.outside="open = false"
        class="relative w-full max-w-[600px] rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-10">

        <!-- Modal Content -->
        <div>

            <!-- Modal Header -->
            <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                {{ __('text.Estimate Notes') }}
            </div>

            <!-- Modal Body -->
            <div class="w-full mt-4">
                <form wire:submit.prevent="submitEstimateNote">
                    <textarea wire:model="notice_sms" rows="5"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                </textarea>

                    <div class="my-4 flex space-x-2">
                        <!-- Submit Button -->
                        <button type="submit"
                            class="flex justify-center w-full px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 sm:w-auto">
                            {{ __('text.Ok') }}
                        </button>

                        <!-- Cancel Button -->
                        <button type="button" wire:click.prevent="modelclose" type="button"
                            class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 sm:w-auto">
                            {{ __('text.Cancel') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@if($modelHistoryQueue)
<div x-data="{ open:open }" x-show="open" @open-modal.window="if ($event.detail.id === 'historyQueue') open = true"
    @close-modal.window="if ($event.detail.id === 'historyQueue') open = false" @keydown.escape.window="open = false"
    class="fixed left-0 top-0 z-99999 flex h-screen w-full flex-col overflow-y-auto overflow-x-hidden p-6 dark:bg-gray-900 lg:p-10">
    <div class="modal-close-btn fixed inset-0 h-full w-full bg-gray-800/50"></div>

    <!-- Modal Content -->
    <div @click.outside="open = false"
        class="relative m-auto w-full max-w-[900px] rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-10">

        <button wire:click.prevent="modelclose" type="button"
            class="absolute right-3 top-3 z-999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-100 text-gray-400 transition-colors hover:bg-gray-200 hover:text-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white sm:right-6 sm:top-6 sm:h-11 sm:w-11">
            <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z"
                    fill=""></path>
            </svg>
        </button>

        <!-- Modal Content -->
        <div>

            <!-- Modal Header -->
            <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                {{ __('text.Call History') }}
            </div>

            <!-- Modal Body -->
            <div class="w-full flex flex-wrap mt-4">
                <!-- Queue Details -->
                <div class="flex-1 p-4 border-e border-gray-600">
                    <ul class="space-y-2 text-left text-gray-500 dark:text-gray-400">
                        <li class="flex justify-between py-2 border-b">
                            <span class="text-gray-600">{{ __('text.Token') }}</span>
                            <span
                                class="font-semibold text-gray-900">{{$randomCurrentQueue?->start_acronym.''.$randomCurrentQueue?->token}}</span>
                        </li>

                        @if(!empty($randomCurrentQueue->category_id))
                        <li class="flex justify-between py-2 border-b">
                            <span class="text-gray-600">{{ __('text.Level') }} 1:</span>
                            <span
                                class="font-semibold text-gray-900">{{ App\Models\Category::viewCategoryName($randomCurrentQueue->category_id) }}</span>
                        </li>
                        @endif
                        @if(!empty($randomCurrentQueue->sub_category_id))
                        <li class="flex justify-between py-2 border-b">
                            <span class="text-gray-600">{{ __('text.Level') }} 2:</span>
                            <span
                                class="font-semibold text-gray-900">{{ App\Models\Category::viewCategoryName($randomCurrentQueue->sub_category_id) }}</span>
                        </li>
                        @endif
                        @if(!empty($randomCurrentQueue->child_category_id))
                        <li class="flex justify-between py-2 border-b">
                            <span class="text-gray-600">{{ __('text.Level') }} 3:</span>
                            <span
                                class="font-semibold text-gray-900">{{ App\Models\Category::viewCategoryName($randomCurrentQueue->child_category_id) }}</span>
                        </li>
                        @endif

                        @forelse($userDetails as $key => $userD)
                        <li class="flex justify-between py-2 border-b">
                            <span class="text-gray-600">{{ App\Models\FormField::viewLabel($team_id, $key) }}</span>
                            <span class="font-semibold text-gray-900">{{ $userD }}</span>
                        </li>
                        @empty
                        {{ __('text.No user details') }}
                        @endforelse
                    </ul>
                </div>

                <!-- Activity Logs -->
                <div class="flex-1 p-4 overflow-y-auto" style="max-height: 350px">
                    @if($siteDetail?->activity_log == App\Models\SiteDetail::STATUS_YES)
                    @forelse($activityLogs as $index => $log)
                    <div class="flex items-start border-b py-1">
                        <div class="flex-shrink-0">
                            <div class="h-6 w-6 rounded-full flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </div>
                        </div>
                        <div class="ps-4 flex-1">
                            <div class="flex justify-between items-center space-y-2">
                                <p class="font-medium text-gray-600">{{ $log->text }} {{ __('text.by') }} {{ $log->createdBy?->name }}</p>
                                <span
                                    class="text-gray-600 text-sm">{{ Carbon\Carbon::parse($log->created_at)->format($datetimeFormat) }}</span>
                            </div>
                        </div>
                    </div>
                    @empty
                    {{ __('text.No logs found') }}
                    @endforelse
                    @endif
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="mt-8 flex w-full items-center justify-end gap-3">
                <button type="button" wire:click.prevent="modelclose" type="button"
                    class="flex justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                    {{ __('text.Cancel') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endif
@if($modelhistoryTakeCall)
<div x-data="{ open: open }" x-show="open" @open-modal.window="if ($event.detail.id === 'historyTakeCall') open = true"
    @close-modal.window="if ($event.detail.id === 'historyTakeCall') open = false" @keydown.escape.window="open = false"
    class="fixed left-0 top-0 z-99999 flex h-screen w-full flex-col overflow-y-auto overflow-x-hidden p-6 dark:bg-gray-900 lg:p-10">
    <div class="modal-close-btn fixed inset-0 h-full w-full bg-gray-800/50"></div>

    <!-- Modal Content -->
    <div @click.outside="open = false"
        class="relative m-auto w-full max-w-[900px] rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-10">
        <button wire:click.prevent="modelclose" type="button"
            class="absolute right-3 top-3 z-999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-100 text-gray-400 transition-colors hover:bg-gray-200 hover:text-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white sm:right-6 sm:top-6 sm:h-11 sm:w-11">
            <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z"
                    fill=""></path>
            </svg>
        </button>

        <div>

            <!-- Modal Header -->
            <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                {{ __('text.Call History') }}
            </div>

            <!-- Modal Body -->
            <div class="w-full flex flex-wrap mt-4">

                <!-- Left Section -->
                <div class="flex-1 p-4 border-e border-gray-600">
                    <ul class="space-y-4 text-left text-gray-500 dark:text-gray-400">
                        <li class="flex justify-between py-2">
                            <span class="text-gray-600">{{ __('text.Token') }}</span>
                            <span
                                class="font-semibold text-gray-900">{{ $randomCurrentQueue?->start_acronym.''.$randomCurrentQueue?->token }}</span>
                        </li>

                        @if(!empty($randomCurrentQueue->category_id))
                        <li class="flex justify-between py-2">
                            <span class="text-gray-600">{{ __('text.Level') }} 1:</span>
                            <span
                                class="font-semibold text-gray-900">{{ App\Models\Category::viewCategoryName($randomCurrentQueue->category_id) }}</span>
                        </li>
                        @endif

                        @if(!empty($randomCurrentQueue->sub_category_id))
                        <li class="flex justify-between py-2">
                            <span class="text-gray-600">{{ __('text.Level') }} 2:</span>
                            <span
                                class="font-semibold text-gray-900">{{ App\Models\Category::viewCategoryName($randomCurrentQueue->sub_category_id) }}</span>
                        </li>
                        @endif

                        @if(!empty($randomCurrentQueue->child_category_id))
                        <li class="flex justify-between py-2">
                            <span class="text-gray-600">{{ __('text.Level') }} 3:</span>
                            <span
                                class="font-semibold text-gray-900">{{ App\Models\Category::viewCategoryName($randomCurrentQueue->child_category_id) }}</span>
                        </li>
                        @endif

                        @forelse($userDetails as $key => $userD)
                        <li class="flex justify-between py-2">
                            <span class="text-gray-600">{{ App\Models\FormField::viewLabel($team_id, $key) }}</span>
                            <span class="font-semibold text-gray-900">{{ $userD }}</span>
                        </li>
                        @empty
                        <li class="py-2">{{ __('text.No user details') }}</li>
                        @endforelse

                        @if(!empty($randomCurrentQueue))
                        <li class="flex text-center">
                            <button type="button"
                                wire:click="nextCall({{ $randomCurrentQueue?->queue_id }}, {{ $randomCurrentQueue?->id }})"
                                class="px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">
                                {{ __('text.Take Call') }}
                            </button>
                        </li>
                        @endif
                    </ul>
                </div>

                <!-- Right Section (Activity Logs) -->
                <div class="flex-1 p-4 overflow-y-auto" style="max-height: 350px;">
                    @if($siteDetail?->activity_log == App\Models\SiteDetail::STATUS_YES)
                    @forelse($activityLogs as $index => $log)
                    <div class="flex items-start border-b py-2">
                        <div class="flex-shrink-0">
                            <div class="h-6 w-6 rounded-full flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </div>
                        </div>
                        <div class="ps-4 flex-1">
                            <div class="flex justify-between items-center">
                                <p class="font-medium text-gray-600">
                                    {{ $log->text }} {{ __('text.by') }} {{ $log->createdBy?->name }}
                                </p>
                                <span class="text-gray-600 text-sm">
                                    {{ Carbon\Carbon::parse($log->created_at)->format($datetimeFormat) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="py-2">{{ __('text.No logs found') }}</p>
                    @endforelse
                    @endif
                </div>

            </div>


            <!-- Modal Footer -->
            <div class="mt-8 flex w-full items-center justify-end gap-3">
                <button wire:click.prevent="modelclose" type="button" type="button"
                    class="flex justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                    {{ __('text.Close') }}
                </button>

            </div>
        </div>
    </div>
</div>
@endif
@if($modelsActivityLog)
<div x-data="{ open: true }" x-show="open" @open-modal.window="if ($event.detail.id === 'showActivityLog') open = true"
    @close-modal.window="if ($event.detail.id === 'showActivityLog') open = false" @keydown.escape.window="open = false"
    class="fixed left-0 top-0 z-99999 flex h-screen w-full flex-col overflow-y-auto overflow-x-hidden p-6 dark:bg-gray-900 lg:p-10">
    <div class="modal-close-btn fixed inset-0 h-full w-full bg-gray-800/50"></div>

    <!-- Modal Content -->
    <div @click.outside="open = false"
        class="relative m-auto w-full max-w-[600px] rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-10">
        <button wire:click.prevent="modelclose" type="button"
            class="absolute right-3 top-3 z-999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-100 text-gray-400 transition-colors hover:bg-gray-200 hover:text-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white sm:right-6 sm:top-6 sm:h-11 sm:w-11">
            <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z"
                    fill=""></path>
            </svg>
        </button>

        <!-- Modal Content -->

        <div>

            <!-- Modal Header -->
            <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                {{ __('text.Activity Log') }}
            </div>

            <!-- Modal Body -->
            <div class="w-full mt-4">
                <div class="space-y-4">
                    @forelse($activityLogs as $index => $log)
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="h-8 w-8 rounded-full flex items-center justify-center bg-gray-200 dark:bg-gray-700">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor" class="w-6 h-6 text-gray-700 dark:text-gray-300">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </div>
                        </div>
                        <div class="ps-4 flex-1">
                            <div class="flex justify-between items-center">
                                <p class="font-medium text-gray-900 dark:text-gray-100">{{ $log->text }}</p>
                                <span
                                    class="text-gray-500 text-sm">{{ Carbon\Carbon::parse($log->created_at)->format($datetimeFormat) }}</span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-gray-500 dark:text-gray-300">{{ __('text.No logs found') }}</p>
                    @endforelse
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-end mt-4">
                <button type="button" wire:click.prevent="modelclose" type="button"
                    class="flex justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                    {{ __('text.Close') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endif

@if($modelslideCurrentVisitor)

<div x-data="{ open: true }" x-show="open"
    @open-modal.window="if ($event.detail.id == 'slideCurrentVisitor') open = true"
    @close-modal.window="if ($event.detail.id == 'slideCurrentVisitor') open = false"
    @keydown.escape.window="open = false"
    class="fixed inset-0 flex items-center justify-center p-5 overflow-y-auto modal z-99999">

    <div class="modal-close-btn fixed inset-0 h-full w-full bg-gray-800/50 "></div>

    <div @click.outside="open = false"
        class="relative w-full max-w-[600px] rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-10">

        <button wire:click.prevent="modelclose" type="button"
            class="absolute right-3 top-3 z-999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-100 text-gray-400 transition-colors hover:bg-gray-200 hover:text-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white sm:right-6 sm:top-6 sm:h-11 sm:w-11">
            <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z"
                    fill=""></path>
            </svg>
        </button>

        <!-- Slide-over Content -->
        <div>

            <!-- Modal Header -->
            <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                {{ __('text.Edit Visitor') }}
            </div>

            <!-- Modal Body -->

            <div class="w-full mt-4">
                <form wire:submit.prevent="currentVisitorEdit">
                    <div class="space-y-4">
                        @foreach ($dynamicForm as $form)
                        @php
                        $fieldId = strtolower($form['title']).'_'.$form['id'];
                        $fieldModel = "dynamicProperties.$fieldId";
                        @endphp
                        <!-- Visitor Details -->
                        @if (in_array($form['type'], [App\Models\FormField::TEXT_FIELD,
                        App\Models\FormField::URL_FIELD]))
                        <div>
                            <label for="{{ $fieldId }}"
                                class="block text-sm font-medium text-gray-900">{{ $language !== 'en' ? (isset($translations[$form['label']][$language]) && !empty($translations[$form['label']][$language]) ? $translations[$form['label']][$language]  : $form['label']) : $form['label'] }}</label>
                            <input type="{{ $form['type'] == App\Models\FormField::TEXT_FIELD ? 'text' : 'url' }}"
                                id="{{ $fieldId }}" placeholder="{{ $language !== 'en' ? ($translations[$form['label'] . '_placeholders'][$language] ?? $form['placeholder']) : $form['placeholder'] }}"
                                wire:model="{{ $fieldModel }}" minlength="{{ $form['minimum_number_allowed'] }}"
                                maxlength="{{ $form['maximum_number_allowed'] }}"
                                class="mt-1 block w-full p-2 border rounded-md focus:ring-blue-500 focus:border-blue-500">
                            @error($fieldModel) <div class="text-red-500">{{ $message }}</div> @enderror
                        </div>
                        @elseif ($form['type'] == App\Models\FormField::PHONE_FIELD)
                        <div>
                            <label for="{{ $fieldId }}"
                                class="block text-sm font-medium text-gray-900"> {{ $language !== 'en' ? (isset($translations[$form['label']][$language]) && !empty($translations[$form['label']][$language]) ? $translations[$form['label']][$language]  : $form['label']) : $form['label'] }}</label>
                            <input type="number" id="{{ $fieldId }}" placeholder="{{ $language !== 'en' ? ($translations[$form['label'] . '_placeholders'][$language] ?? $form['placeholder']) : $form['placeholder'] }}"
                                wire:model="{{ $fieldModel }}" minlength="{{ $form['minimum_number_allowed'] }}"
                                maxlength="{{ $form['maximum_number_allowed'] }}"
                                class="mt-1 block w-full p-2 border rounded-md focus:ring-blue-500 focus:border-blue-500">
                            @error($fieldModel) <div class="text-red-500">{{ $message }}</div> @enderror
                        </div>
                        @elseif ($form['type'] == App\Models\FormField::DATE_FIELD)
                        <div>
                            <label for="{{ $fieldId }}"
                                class="block text-sm font-medium text-gray-900">{{ $language !== 'en' ? (isset($translations[$form['label']][$language]) && !empty($translations[$form['label']][$language]) ? $translations[$form['label']][$language]  : $form['label']) : $form['label'] }}</label>
                            <input type="date" id="{{ $fieldId }}" onclick="this.showPicker()" wire:model="{{ $fieldModel }}"
                                placeholder="{{ $language !== 'en' ? ($translations[$form['label'] . '_placeholders'][$language] ?? $form['placeholder']) : $form['placeholder'] }}"
                                class="mt-1 block w-full p-2 border rounded-md focus:ring-blue-500 focus:border-blue-500">
                            @error($fieldModel) <div class="text-red-500">{{ $message }}</div> @enderror
                        </div>

                        @elseif ($form['type'] == App\Models\FormField::SELECT_FIELD)
                        <div>
                            <label for="{{ $fieldId }}"
                                class="block text-sm font-medium text-gray-900">{{ $language !== 'en' ? (isset($translations[$form['label']][$language]) && !empty($translations[$form['label']][$language]) ? $translations[$form['label']][$language]  : $form['label']) : $form['label'] }}</label>
                            <select id="{{ $fieldId }}" wire:model="{{ $fieldModel }}"
                                class="mt-1 block w-full p-2 border rounded-md focus:ring-blue-500 focus:border-blue-500">
                                <option value="">{{ __('text.Select an option') }}</option>
                                @foreach ($form['options'] as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                            @error($fieldModel) <div class="text-red-500">{{ $message }}</div> @enderror
                        </div>

                        @elseif($form['type'] == App\Models\FormField::NUMBER_FIELD)
                        <div class="col-span-full">
                            <div class="mt-2">
                                <label for="{{ $fieldId }}"
                                    class="block mb-2 text-sm font-medium text-gray-900 ">{{ session('app_locale') !== 'en' ? ($translations[$form['label']][session('app_locale')] ?? $form['label']) : $form['label'] }}</label>
                                <div
                                    class="flex rounded-md shadow-sm ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md">
                                    <input type="number"
                                        id="{{ $fieldId }}"
                                        class="text-center block flex-1 border-slate-400 bg-transparent py-1.5 pl-1 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6  h-12 rounded-lg @error('dynamicProperties.' . $form['title'] . '_' . $form['id']) is-invalid @enderror"
                                        placeholder="{{ session('app_locale') !== 'en' ? ($translations[$form['label'] . '_placeholders'][session('app_locale')] ?? $form['placeholder']) : $form['placeholder'] }}"

                                        wire:model="{{ $fieldModel }}">
                                </div>
                                @error($fieldModel) <div class="text-red-500">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        @elseif($form['type'] == App\Models\FormField::TEXTAREA_FIELD)
                        <div class="col-span-full">
                            <div class="mt-2">
                                <label for="{{ $fieldId }}"
                                    class="block mb-2 text-sm font-medium text-gray-900 ">{{ session('app_locale') !== 'en' ? ($translations[$form['label']][session('app_locale')] ?? $form['label']) : $form['label'] }}</label>
                                <div
                                    class="flex rounded-md shadow-sm ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md ">
                                    <textarea id="{{ $fieldId }}" rows="4"
                                        class="block p-2.5 w-full text-blue-600 bg-gray-100 border-slate-400 rounded focus:ring-blue-500 dark:focus:ring-blue-600 focus:ring-2 h-12 @error('dynamicProperties.' . $form['title'] . '_' . $form['id']) is-invalid @enderror"
                                        placeholder="{{ session('app_locale') !== 'en' ? ($translations[$form['label'] . '_placeholders'][session('app_locale')] ?? $form['placeholder']) : $form['placeholder'] }}" wire:model="{{ $fieldModel }}"
                                        minlength="{{ $form['minimum_number_allowed'] }}" maxlength="{{ $form['maximum_number_allowed'] }}"> </textarea>
                                </div>
                                @error($fieldModel) <div class="text-red-500">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        @elseif($form['type'] == App\Models\FormField::CHECKBOX_FIELD)
                        <div class="col-span-full">
                            <div class="flex items-center mb-4">
                                <input id="{{ $fieldId }}" type="checkbox"
                                    value=""
                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 @error('dynamicProperties.' . $form['title'] . '_' . $form['id']) is-invalid @enderror"
                                    wire:model="{{ $fieldModel }}">
                                <label for="{{ $form['title'] . '_' . $form['id'] }}"
                                    class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-750">{{ $form['title'] }}</label>
                            </div>
                            @error($fieldModel) <div class="text-red-500">{{ $message }}</div> @enderror
                        </div>
                        @endif
                        @endforeach

                        <!-- end Visitor Levels -->

                        <!--Service Details -->
                        <div>
                            <label class="block text-sm font-medium text-gray-900">{{ $level1 }}</label>
                            <select wire:model.live="selectedCategoryId"
                                class="block w-full p-2 border rounded-md focus:ring-blue-500 focus:border-blue-500">
                                <option disabled>{{ __('text.Select an option') }}</option>
                                @foreach ($firstCategories as $keyCat => $nameCate)
                                @php
                                if($language != 'en')
                                {
                                if(isset($translations[$nameCate->name][$language]) && !empty($translations[$nameCate->name][$language]))
                                {
                                $category = $translations[$nameCate->name][$language];
                                }
                                else
                                {
                                $category = $nameCate->name;
                                }
                                }
                                else
                                {
                                $category = $nameCate->name;
                                }
                                @endphp
                                <option value="{{ $nameCate->id }}">{{ $category }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-900">{{ $level2 }}</label>
                            <select wire:model.live="secondChildId" wire:key="{{ $selectedCategoryId }}"
                                class="block w-full p-2 border rounded-md focus:ring-blue-500 focus:border-blue-500">
                                <option value="">{{ __('text.Select an option') }}</option>
                                @foreach ($secondCategories as $keySCat => $nameSCate)
                                @php
                                if($language != 'en')
                                {
                                if(isset($translations[$nameSCate][$language]) && !empty($translations[$nameSCate][$language]))
                                {
                                $seccategory = $translations[$nameSCate][$language];
                                }
                                else
                                {
                                $seccategory = $nameSCate;
                                }
                                }
                                else
                                {
                                $seccategory = $nameSCate;
                                }
                                @endphp
                                <option value="{{ $keySCat }}">{{ $seccategory }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-900">{{ $level3 }}</label>
                            <select wire:model.live="thirdChildId" wire:key="{{ $secondChildId }}"
                                class="block w-full p-2 border rounded-md focus:ring-blue-500 focus:border-blue-500">
                                <option value="">{{ __('text.Select an option') }}</option>
                                @foreach ($thirdCategories as $keySCat => $nameSCate)
                                @php
                                if($language != 'en')
                                {
                                if(isset($translations[$nameSCate][$language]) && !empty($translations[$nameSCate][$language]))
                                {
                                $thirdcategory = $translations[$nameSCate][$language];
                                }
                                else
                                {
                                $thirdcategory = $nameSCate;
                                }
                                }
                                else
                                {
                                $thirdcategory = $nameSCate;
                                }
                                @endphp
                                <option value="{{ $keySCat }}">{{ $thirdcategory }}</option>
                                @endforeach
                            </select>
                        </div>


                        @forelse($staticVisitorDetails as $key => $visitor)
                        <div class="flex justify-between py-2">
                            <span
                                class="text-gray-600">{{ App\Models\FormField::viewLabel($team_id, $key,$location) }}</span>
                            <span class="font-semibold">{{ $visitor }}</span>
                        </div>
                        @empty
                        @endforelse

                    </div>

                    <!-- Modal Footer -->
                    <div class="flex justify-end mt-4 space-x-2">
                        <button type="submit"
                            class="flex justify-center rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                            {{ __('text.Save') }}
                        </button>
                        <button type="button" wire:click.prevent="modelclose" type="button"
                            class="flex justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                            {{ __('text.Close') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

{{--
<div x-data="{ open: @entangle('showModal') }" x-show="open" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg w-[800px] h-[600px] p-4 relative">
        @if ($popupUrl)
            <iframe src="{{ $popupUrl }}" class="w-full h-full border-none"></iframe>
@endif
<button @click="open = false" class="absolute top-2 right-2 text-xl">&times;</button>
</div>
</div>
--}}

<div id="call-footer">
    @if($siteDetail?->served_queue == App\Models\SiteDetail::STATUS_YES)
    <div
        class="serving-section flex items-center text-center bg-black text-white p-2 dark:bg-gray-800 text-xl font-bold">
        <span>
            {{ $siteDetail?->label_total_served_token ?? __('text.Total Served Tokens') }}: </span>
        <span class="flex items-center gap-2">
            <span style="margin-left:10px">
       
                @forelse($tokenServed as $index => $tokenServe)

                @foreach($tokenServe['queue_storages'] as $storageKey => $queueS)
                <a href="javascript:void(0)" onclick="revertCallfn('{{$index}}','{{$queueS['queue_id']}}')"
                    class="opacity-50">
                    @if($display_name)
                    {{ $tokenServe['name'] != '' ? $tokenServe['name'] : $tokenServe['token'] }}
                    @else
                    {{ $tokenServe['token'] }}
                    @endif
                </a>
                @endforeach
                @if (!$loop->last) , @endif

                @empty
                <span style="margin-left:10px;">{{ __('text.N/A') }}</span>
                @endforelse


            </span>
    </div>
    @endif
    @if($siteDetail?->is_missed_call == App\Models\SiteDetail::STATUS_YES)
    <div class="missed-section flex items-center text-center bg-custom-600 text-white p-2 dark:bg-gray-800  theme-bg">
        <span>
            {{ $siteDetail?->label_missed_queue ?? __('text.Missed Queue') }}: </span>
        <span>
            @forelse($missedCalls as $index => $missedCall)
            @php
            $linkAction = $siteDetail?->missed_queue_history_popup == App\Models\SiteDetail::STATUS_YES
            ? 'historyTakeCall'
            : 'nextCall';
            @endphp


            @foreach($missedCall['queue_storages'] as $storageIndex => $queueStorage)
            <a href="javascript:void(0)" wire:click="{{ $linkAction }}({{ $index }}, {{ $queueStorage['queue_id']  }})"
                wire:loading.class="opacity-50" style="margin-left:10px;">
              @php
                $missedName = (!empty($missedCall['start_acronym']) ? $missedCall['start_acronym'] : '') . $missedCall['token'];
            @endphp

            @if($display_name)
                {{ !empty($queueStorage['name']) ? $queueStorage['name'] : $missedName }}
            @else
                {{ $missedName }}
            @endif
            </a>
            @endforeach
            @if (!$loop->last) , @endif

            @empty
            <span style="margin-left:10px;">{{ __('text.N/A') }}</span>
            @endforelse
        </span>
    </div>
    @endif
    @if($siteDetail?->is_hold == App\Models\SiteDetail::STATUS_YES)
    <div
        class="missed-section flex items-center text-center bg-green-600 text-white p-2 dark:bg-gray-800  text-2 theme-hold-bg">
        <span>
            {{ $siteDetail?->label_hold_queue ?? __('text.Hold Queue') }}: </span>
        <span>
         
            @forelse($holdCalls as $index => $holdCall)
            <a href="javascript:void(0)" wire:click="unholdCallModal({{ $holdCall['id'] }})"
                wire:loading.class="opacity-50" style="margin-left:10px;">
              @if($display_name)
                    {{ !empty($holdCall['name']) 
                        ? $holdCall['name'] 
                        : (!empty($holdCall['start_acronym']) 
                            ? $holdCall['start_acronym'] 
                            : 'W' . $holdCall['token']) }}
                @else
                    {{ !empty($holdCall['start_acronym']) 
                        ? $holdCall['start_acronym'] 
                        : 'W' }}{{ $holdCall['token'] }}
                @endif
            </a>

            @if (!$loop->last) , @endif
            @empty
            <span style="margin-left:10px;">{{ __('text.N/A') }}</span>
            @endforelse
        </span>
    </div>
    @endif


</div>
<audio id="audio">
    <source src="voice/Ding-noise/Ding-noise.mp3" type="audio/mpeg" />
    <source src="voice/Ding-noise/Ding-noise.ogv" type="audio/ogg" />
</audio>


</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{asset('/js/app/call.js?v=3.1.0.0')}}"></script>

<script>
    // var pusher = new Pusher("{{ env('PUSHER_APP_KEY') }}", {
    //     cluster: "{{ env('PUSHER_APP_CLUSTER') }}",
    //     encrypted: true
    // });

    var pusher = new Pusher("{{ $pusherKey }}", {
        cluster: "{{ $pusherCluster }}",
        encrypted: true
    });

    var queueCall = pusher.subscribe("queue-call.{{ $team_id }}");

    queueCall.bind('queue-call', function(data) {

        Livewire.dispatch('create-queue', {
            event: data
        });

        @if($siteDetail?->is_sound_notification == App\Models\SiteDetail::STATUS_YES)
            document.getElementById("audio")?.play();
        @endif
    });

    var queueProgress = pusher.subscribe("queue-progress.{{ $team_id }}.{{$location}}.{{auth()->user()->id}}");

    queueProgress.bind('queue-progress', function(data) {
        Livewire.dispatch('next-queue', {
            event: data
        });
    });

    var breakReason = pusher.subscribe("break-reason.{{ auth()->user()->id }}");

    breakReason.bind('break-reason', function(data) {
        Livewire.dispatch('break-request', {
            event: data
        });
    });
</script>

<script>
    document.addEventListener('livewire:init', () => {
        // Livewire.on('refreshComponent', () => {
        //     Livewire.refresh(); // Refresh the component only, not the whole page
        // });

        document.getElementById('resetQueue')?.addEventListener('click', function() {
            Swal.fire({
                title: "{{ __('message.Are you sure') }}?",
                icon: "warning",
                text: "{{ __('message.You won\'t be able to revert this') }}!",
                showCancelButton: true,
                confirmButtonText: "{{ __('message.YES, RESET IT') }}!",
                cancelButtonText: "{{ __('message.No, CANCEL') }}!",
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: "{{ __('message.Reset Queue') }}",
                        text: "{{ __('message.Please Wait') }}...",
                        padding: 20,
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        willOpen: function() {
                            Swal.showLoading();
                        }
                    }).then(() => {

                    }).catch(Swal.noop);

                    Livewire.dispatch('reset-current-queue');
                }
            });
        });

        document.getElementById('generateQueue')?.addEventListener('click', function() {
            let queueNumber, parentSelect;
            Swal.fire({
                title: "{{ __('message.Generate Queue') }}",
                html: `
               <form><div style="text-align: left; margin-bottom: 1rem;">
                   <input type="text" id="queue_number" class="swal2-input" wire:model="queue_number" placeholder="{{ __('message.Enter Queue Number'
        ) }}*" maxlength="10" style="width: 100%;">
               </div>
               <div style="text-align: left; margin-top: 1rem;">
                   <select id="parent_select" class="swal2-input" style="width: 100%;" wire:model="parent">
                       <option value="">{{ __('text.Select Category') }}</option>
                       @foreach ($categories as $key=> $cat)
                          <option value="{{ $key }}">{{ $cat }}</option>
                      @endforeach
                   </select>      
              </div>
              </form>
           `,
                showCancelButton: true,
                confirmButtonText: "{{ __('message.OK') }}",
                cancelButtonText: "{{ __('message.Cancel') }}",
                preConfirm: () => {
                    queueNumber = Swal.getPopup().querySelector('#queue_number').value;
                    parentSelect = Swal.getPopup().querySelector('#parent_select')
                        .value;
                    if (!queueNumber || !parentSelect) {
                        Swal.showValidationMessage(`{{ __('message.VAL001.message') }}`);
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    updateLoader();
                    Livewire.dispatch('generate-queue-created', {
                        queueNumber,
                        parentSelect
                    });
                }
            });
        });

        function updateLoader() {
            Swal.fire({
                title: "{{ __('message.Updating') }}",
                text: "{{ __('message.Please Wait') }}...",
                timer: 15000,
                padding: 20,
                showConfirmButton: false,
                allowOutsideClick: false,
                willOpen: function() {
                    Swal.showLoading();
                }
            }).then(() => {

            }).catch(Swal.noop);
        }

        document.getElementById('breakButton')?.addEventListener('click', function() {
            let breakType, breakComment;
            Swal.fire({
                title: "{{ __('message.Break') }}",
                html: `
               <div style="text-align: left; margin-bottom: 1rem;">
                   <label for="breakType" style="display: block; margin-bottom: .5rem;">{{ __('message.Type of Break') }}</label>
                   <select id="breakType" class="swal2-input" style="width: 100%;" wire:model="break_reason">
                       <option value="">{{ __('message.Choose Any Reason') }}</option>
                       @foreach (App\Models\BreakReason::getReasons() as $key =>$type)
                               <option value="{{ $key }}">{{ $type }}</option>
                      @endforeach
                   </select>
               </div>
               <div style="text-align: left; margin-top: 1rem;">
                   <label for="breakComment" style="display: block; margin-bottom: .5rem;">{{ __('text.Comment') }}</label>
                   <input type="text" id="breakComment" class="swal2-input" wire:model="break_comment" style="width: 100%;">
               </div>
           `,
                showCancelButton: true,
                confirmButtonText: "{{ __('message.OK') }}",
                cancelButtonText: "{{ __('message.Cancel') }}",
                preConfirm: () => {
                    breakType = Swal.getPopup().querySelector('#breakType').value;

                    breakComment = Swal.getPopup().querySelector('#breakComment')
                        .value;
                    if (!breakType || !breakComment) {
                        Swal.showValidationMessage(`{{ __('message.VAL002.message') }}`);
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    console.log(result);
                    updateLoader();
                    Livewire.dispatch('break-created', {
                        breakType,
                        breakComment
                    });
                }
            });

        });


        Livewire.on('event-continue-break', (response) => {
            console.log('Response:', response);
            const breakTime = response[0].breakTime;
            Swal.fire({
                title: "{{ __('message.Unlock Screen') }}",
                text: `{{ __('message.Click on the continue button to unlock this screen! Break time is for') }} ${breakTime} {{ __('message.minutes.') }}`,
                icon: "warning",
                confirmButtonText: "{{ __('message.CONTINUE') }}",
                allowOutsideClick: false,
                //position: 'top',
                customClass: {
                    container: 'swal2-top-center'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    updateLoader();
                    Livewire.dispatch('break-end');
                    Swal.close();

                }
            });
        });
        Livewire.on('event-continue-break-close', () => {
            alert('close');
            Livewire.dispatch('break-end');
            Swal.close();
        });
        Livewire.on('event-success-call', (response) => {
            if (response[0].message == 'Call started Successfully')
                localStorage.removeItem(`${labelVisitorStorage}`);
            Swal.fire({
                icon: "success",
                title: response[0].message,
                showConfirmButton: false,
                timer: 5000
            });

            // Reload after 5 seconds (5000ms)
            //setTimeout(() => {
            //  location.reload();
            //}, 3000);
        });
        Livewire.on('event-success-suspended', (response) => {
            if (response[0].message == 'Suspension processed successfully with notifications sent')
                localStorage.removeItem(`${labelVisitorStorage}`);
            Swal.fire({
                icon: "success",
                title: response[0].message,
                showConfirmButton: false,
                timer: 5000
            });

            // Reload after 5 seconds (5000ms)
            setTimeout(() => {
                location.reload();
            }, 1000);
        });

        Livewire.on('event-error-call', (response) => {
            Swal.fire({
                icon: "error",
                title: response[0].message,
                showConfirmButton: false,
                timer: 5000
            });
        });

        Livewire.on('event-error', (data) => {
            console.log(data);
            let response = data[0];
            Swal.fire({
                icon: "error",
                title: `${response.message}`,
                showConfirmButton: false,
                timer: 5000
                // html: `
                //     <strong>${response.label_description}:</strong> ${response.description}<br><br>
                //     <strong>${response.label_resolution}:</strong> ${response.resolution}
                // `,
            });
        });

        Livewire.on('break-request-reject', (response) => {
            Swal.fire({
                icon: "error",
                title: response[0].message,
                showConfirmButton: false,
                timer: 5000
            });
        });

        let labelVisitorStorage = 'visitStartTime_{{$location}}';

        function formatTime(seconds) {
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            const remainingSeconds = Math.floor(seconds % 60);
            labelVisitorStorage = `visitStartTime_{{$location}}`;

            return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(remainingSeconds).padStart(2, '0')}`;
        }

        function updateServingTime() {
            const startTime = localStorage.getItem(`${labelVisitorStorage}`) || Date.now();
            const currentTime = Date.now();
            const servingSeconds = Math.floor((currentTime - startTime) / 1000);
            const servingTimeElement = document.getElementById('serving-time-text');

            if (servingTimeElement) {
                servingTimeElement.textContent = formatTime(servingSeconds);
            }

            localStorage.setItem(`${labelVisitorStorage }`, startTime);
        }


        Livewire.on('event-serving-time', (response) => {
            // Retry logic to ensure the element is found
            const intervalId = setInterval(() => {
                const servingTimeElement = document.getElementById('serving-time-text');
                if (servingTimeElement) {
                    clearInterval(intervalId);
                    updateServingTime();
                    setInterval(updateServingTime, 1000);
                }
            }, 100);

            // Timeout after a reasonable time to prevent infinite looping
            setTimeout(() => {
                clearInterval(intervalId);
            }, 5000); // 5 seconds timeout, adjust as necessary
        });

        //  Livewire.on('desktop-notification', (response) => {
        // let e = response[0];
        // showNotification('Queue Notification', `The token No .${e.queueacronym}.${e.queueToken}`);
        // });

        Livewire.on('refresh-page', () => {
            window.location.reload(); // Full browser reload
        });

        //     Livewire.on('virtual', () => {
        //         // alert(123456);
        //         document.getElementById('virtualMeeting').style.display = "block";

        // });


    });
    const datetimeFormat = @json($datetimeFormat);
    const sessionTimezone = @json(session('timezone_set', 'UTC'));

    function getIntlOptionsFromPHPFormat(phpFormat) {
        switch (phpFormat) {
            case 'Y-m-d H:i:s':
                return {
                    year: 'numeric',
                        month: '2-digit',
                        day: '2-digit',
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                        hour12: false,
                        timeZone: sessionTimezone
                };
            case 'd M Y, h:i A':
                return {
                    day: '2-digit',
                        month: 'short',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true,
                        timeZone: sessionTimezone
                };
            case 'D, M d, Y H:i':
                return {
                    weekday: 'short',
                        month: 'short',
                        day: '2-digit',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: false,
                        timeZone: sessionTimezone
                };
                // Add more mappings as needed
            default:
                // Fallback
                return {
                    year: 'numeric',
                        month: '2-digit',
                        day: '2-digit',
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                        hour12: false,
                        timeZone: sessionTimezone
                };
        }
    }

    // function getFormattedDate() {
    //     const options = getIntlOptionsFromPHPFormat(datetimeFormat);
    //     return new Intl.DateTimeFormat('en-US', options).format(new Date());
    // }

    // function updateClock() {
    // document.getElementById("showClock").innerHTML = getFormattedDate();
    // }

    // setInterval(updateClock, 1000);

    function revertCallfn(queueID, storageID) {
        Swal.fire({
            title: " {{ __('message.Are you sure') }}?",
            icon: "warning",
            text: "{{ __('message.You want to revert this') }}!",
            showCancelButton: true,
            confirmButtonText: "{{ __('message.YES, REVERT IT') }}!",
            cancelButtonText: "{{ __('message.No, CANCEL') }}!",
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: "{{ __('message.Revert Queue') }}",
                    text: "{{ __('text.Please Wait') }}...",
                    padding: 20,
                    showConfirmButton: false,
                    allowOutsideClick: false,

                    willOpen: function() {
                        Swal.showLoading();
                    }
                }).then(() => {
                    // Do something when the loading dialog closes
                }).catch(Swal.noop);

                Livewire.dispatch('revert-served-queue', {
                    queueID,
                    storageID
                });
            } else {
                Swal.fire({
                    title: "{{ __('message.Cancelled') }}",
                    text: "{{ __('message.Your data is safe') }}:)",
                    icon: "error",
                    timer: 15000,
                    padding: 20,
                });
            }
        });
    }

    function ratingService() {
        Swal.fire({
            title: "{{ __('message.Please rate our service') }}",
            html: `
                     <div style="display: flex; justify-content: space-around; align-items: center;">
                           <div onclick="rateService('Excellent')" style="cursor: pointer;">
                              <span style="font-size: 2em;">😀</span>
                              <div>{{ __('message.Excellent') }}</div>
                           </div>
                           <div onclick="rateService('Good')" style="cursor: pointer;">
                              <span style="font-size: 2em;">😊</span>
                              <div>{{ __('message.Good') }}</div>
                           </div>
                           <div onclick="rateService('Neutral')" style="cursor: pointer;">
                              <span style="font-size: 2em;">😐</span>
                              <div>{{ __('message.Neutral') }}</div>
                           </div>
                           <div onclick="rateService('Poor')" style="cursor: pointer;">
                              <span style="font-size: 2em;">🙁</span>
                              <div>{{ __('message.Poor') }}</div>
                           </div>
                     </div>
                  `,
            showConfirmButton: false,
            allowOutsideClick: false,
        });
    }

    function rateService(rating) {

        Livewire.dispatch('rating-service', {
            rating
        });
        Swal.fire({
            text: "{{ __('text.Please Wait') }}",
            timer: 15000,
            padding: 20,
            showConfirmButton: false,
            allowOutsideClick: false,
            willOpen: function() {
                Swal.showLoading();
            }
        }).then(() => {

        }).catch(Swal.noop);
    }
    $(document).ready(function() {
        var headerTop = $('.top-header').height();
        var mainContent = $('.main-content').height();
        var callFooter = $('#call-footer').height();
        $(".main-content").css("height", $(window).height() - headerTop - callFooter - 10),
            $(window).resize(function() {
                $(".main-content").css("height", $(window).height() - headerTop - callFooter - 10)
            });
    });

    /*Fullscreen function*/
    function toggleFullScreen(elem) {
        if ((document.fullScreenElement !== undefined && document.fullScreenElement === null) || (document
                .msFullscreenElement !== undefined && document.msFullscreenElement === null) || (document.mozFullScreen !==
                undefined && !document.mozFullScreen) || (document.webkitIsFullScreen !== undefined && !document
                .webkitIsFullScreen)) {
            if (elem.requestFullScreen) {
                elem.requestFullScreen();
            } else if (elem.mozRequestFullScreen) {
                elem.mozRequestFullScreen();
            } else if (elem.webkitRequestFullScreen) {
                elem.webkitRequestFullScreen(Element.ALLOW_KEYBOARD_INPUT);
            } else if (elem.msRequestFullscreen) {
                elem.msRequestFullscreen();
            }
        } else {
            if (document.cancelFullScreen) {
                document.cancelFullScreen();
            } else if (document.mozCancelFullScreen) {
                document.mozCancelFullScreen();
            } else if (document.webkitCancelFullScreen) {
                document.webkitCancelFullScreen();
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            }
        }
    }
</script>
<script src="https://sdk.twilio.com/js/video/releases/2.31.0/twilio-video.min.js"></script>
<script>

    let room;
    let localAudioTrack;
    let localVideoTrack;
    let isAudioEnabled = true;
    let isVideoEnabled = true;

    Livewire.on('join-call', ({
        token,
        room
    }) => {
        setTimeout(() => {
            runVirtualMeeting(token, room); // Pass as arguments
        }, 2000);
    });

    Livewire.on('close-virtual-call', () => {
        leaveRoom();
    });

    function runVirtualMeeting(token, roomName) {

        Twilio.Video.createLocalTracks({
            audio: true,
            video: {
                width: 320,
                height: 240
            }
        }).then(localTracks => {
            localAudioTrack = localTracks.find(t => t.kind === 'audio');
            localVideoTrack = localTracks.find(t => t.kind === 'video');
            return Twilio.Video.connect(token, {
                name: roomName,
                tracks: localTracks
            });
        }).then(_room => {
            room = _room;
            const container = document.getElementById('video-container');
            console.log(roomName);

            console.log(container);
            // Create local video (small, positioned in corner)
            if (!document.getElementById('local-participant')) {
                const localDiv = document.createElement('div');
                localDiv.id = 'local-participant';
                localDiv.className = `
                        absolute bottom-2 right-2 z-10
                        bg-black rounded-lg border-2 border-white
                        overflow-hidden shadow-lg
                        w-32 h-24 sm:w-40 sm:h-28
                    `.trim();

                localDiv.innerHTML = `
                        <p class="absolute bottom-1 left-1 text-white text-xs 
                        bg-black bg-opacity-70 px-1 py-0.5 rounded z-20">You</p>
                    `;

                // Attach local video track
                const videoElement = localVideoTrack.attach();
                videoElement.style.width = '100%';
                videoElement.style.height = '100%';
                videoElement.style.objectFit = 'cover';
                localDiv.appendChild(videoElement);

                container.appendChild(localDiv);
            }

            // Render remote participants (full size in grid)
            room.participants.forEach(participant => {
                if (participant.identity !== room.localParticipant.identity) {
                    handleParticipant(participant);
                }
            });

            room.on('participantConnected', participant => {
                if (participant.identity !== room.localParticipant.identity) {
                    handleParticipant(participant);
                }
            });

            room.on('participantDisconnected', participant => {
                document.getElementById(participant.sid)?.remove();
                updateGridLayout();
            });

        }).catch(error => {
            alert('Error: ' + error.message);
            window.Livewire.dispatch('leave-meeting');
        });
    }

    function updateGridLayout() {
        const container = document.getElementById('video-container');
        const participantCount = container.querySelectorAll('div[id^="PA"]')?.length || 0; // Remote participant divs

        if (participantCount <= 1) {
            container.className = "grid grid-cols-1 w-full h-full gap-2 p-2";
        } else if (participantCount == 2) {
            container.className = "grid grid-cols-2 w-full h-full gap-2 p-2";
        } else if (participantCount <= 4) {
            container.className = "grid grid-cols-2 md:grid-cols-2 w-full h-full gap-2 p-2";
        } else {
            container.className = "grid grid-cols-2 md:grid-cols-3 w-full h-full gap-2 p-2";
        }
    }

    function handleParticipant(participant) {
        console.log(participant);
        const container = document.getElementById('video-container');
        const div = document.createElement('div');
        div.id = participant.sid;
        // div.className = "bg-black rounded-lg relative overflow-hidden min-h-[200px] w-full";
        div.className = "bg-black rounded-lg relative overflow-hidden w-full h-[240px]";
        div.innerHTML = `
                <p class="absolute bottom-2 left-2 text-white text-sm 
                bg-black bg-opacity-70 px-2 py-1 rounded z-10">
                ${participant.identity}
                </p>
            `;

        container.appendChild(div);
        updateGridLayout();

        // Attach already subscribed tracks
        participant.tracks.forEach(publication => {
            if (publication.isSubscribed && publication.track) {
                const trackElement = publication.track.attach();
                if (publication.track.kind === 'video') {
                    trackElement.style.width = '100%';
                    trackElement.style.height = '100%';
                    trackElement.style.objectFit = 'cover';
                }
                div.appendChild(trackElement);
            }

            // Listen for future subscriptions
            publication.on('subscribed', track => {
                const trackElement = track.attach();
                if (track.kind === 'video') {
                    trackElement.style.width = '100%';
                    trackElement.style.height = '100%';
                    trackElement.style.objectFit = 'cover';
                }
                div.appendChild(trackElement);
            });
        });

        participant.on('trackUnsubscribed', track => {
            track.detach().forEach(el => el.remove());
        });
    }

    function toggleAudio() {
        if (!localAudioTrack) return;
        isAudioEnabled = !isAudioEnabled;
        localAudioTrack.enable(isAudioEnabled);
        const audioIcon = document.getElementById('audio-icon');
        const audioBtn = document.getElementById('audio-btn');

        audioIcon.innerHTML = isAudioEnabled ?
            `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 1v10m0 0a4 4 0 004-4V5a4 4 0 00-8 0v2a4 4 0 004 4zm0 0v4m0 0H8m4 0h4" />
                </svg>` :
            `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 9l6 6m0-6l-6 6M12 1a3 3 0 00-3 3v4a3 3 0 006 0V4a3 3 0 00-3-3z" />
                </svg>`;

        // Change button color based on state
        audioBtn.className = isAudioEnabled ?
            "audio-control bg-gray-800 text-white p-2 rounded hover:bg-blue-600 transition" :
            "audio-control bg-red-600 text-white p-2 rounded hover:bg-red-700 transition";
    }

    function toggleVideo() {
        if (!localVideoTrack) return;
        isVideoEnabled = !isVideoEnabled;
        localVideoTrack.enable(isVideoEnabled);
        const videoIcon = document.getElementById('video-icon');
        const videoBtn = document.getElementById('video-btn');

        videoIcon.innerHTML = isVideoEnabled ?
            `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M4 6h8a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2z" />
                </svg>` :
            `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18 21l-1.414-1.414M5.636 5.636L4 4l1.414 1.414" />
                </svg>`;

        // Change button color based on state
        videoBtn.className = isVideoEnabled ?
            "video-control bg-gray-800 text-white p-2 rounded hover:bg-green-600 transition" :
            "video-control bg-red-600 text-white p-2 rounded hover:bg-red-700 transition";
    }

    function leaveRoom() {
        if (room) {

             room.localParticipant.tracks.forEach(publication => {
            const track = publication.track;
            if (track) {
                track.stop(); // Stop camera/mic
            }
        });
        
            room.disconnect();

            // Remove local participant div
            const localDiv = document.getElementById('local-participant');
            if (localDiv) {
                localDiv.remove();
            }

            // Optional: Remove remote participants too (clean up everything)
            const container = document.getElementById('video-container');
            if (container) {
                container.innerHTML = ''; // clears all video frames if needed
            }

            window.Livewire.dispatch('leave-meeting');
        }
    }

    window.addEventListener('beforeunload', function() {
        leaveRoom();
    });
</script>
@endpush
</div>