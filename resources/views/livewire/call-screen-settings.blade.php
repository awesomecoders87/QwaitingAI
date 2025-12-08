<div class="p-4">
    <style>
        .tooltip-box{
          color: #fff !important;
        }
    </style>
    <h2 class="text-xl font-semibold mb-4">{{ __('setting.Call Screen Settings') }}</h2>
    <div class="p-6 bg-white dark:bg-gray-900 shadow-md rounded-lg">
        <form wire:submit.prevent="save">
            <div class="grid grid-cols-2 gap-4">
                @foreach ([
        'show_visitor_cat' => ['label' => __('setting.Show Services Name on Visitor List'), 'tooltip' => __('setting.When enabled, staff can view which services the visitor has selected directly on the call screen')],
        'fixed_visitor_list_queue' => ['label' => __('setting.Show Fixed Queue Count on Visitor List'), 'tooltip' => __('setting.When enabled, the visitor list will always show a fixed number of queues, regardless of how many are active')],
        'missed_queue_history_popup' => [
            'label' => __('setting.Missed Queue History Popup'),
            'tooltip' => __('setting.When enabled, clicking on a missed queue will first open a history popup along with the Take Call button. Staff can review the history and call the ticket again.
        When disabled, clicking on a missed queue will directly call the ticket without showing the popup'),
        ],
        'is_move_back' => ['label' => __('setting.Enable Move Back button'), 'tooltip' => __('setting.When enabled, a Move Back button will appear on the call screen. Staff can use it to send the current visitor/ticket back to the waiting queue')],
        'is_missed_call' => ['label' => __('setting.Enable Skip Button and Missed Queue List'), 'tooltip' => __('setting.When enabled, staff will see a Skip button on the call screen to skip the current visitor without cancelling. The skipped ticket will move into the Missed Queue list, which staff can view and manage later')],
        'is_recall_button' => ['label' => __('setting.Enable Recall Button'), 'tooltip' => __('setting.When enabled, a Recall button will be available on the call screen. Staff can use it to call a visitor again')],
        'is_hold' => ['label' => __('setting.Enable Hold Button and Hold Queue List'), 'tooltip' => __('setting.When enabled, staff will see a Hold button on the call screen. They can use it to place a visitor on hold. All held visitors will appear in the Hold Queue list, so staff can manage or resume them later')],
        'show_department_missed_queue' => ['label' => __('setting.Show missed queues for assigned services only'), 'tooltip' => __('setting.When this option is enabled, the system will display only the missed queues related to the services that are specifically assigned to staff. Queues from other services will be excluded')],
        // 'counter_assigned_queue' => ['label' => __('setting.Can check assigned counter missed queues only?'), 'tooltip' => __('setting.Restrict missed queues to assigned counters')],
        'show_send_sms_button' => ['label' => __('setting.Enable Send SMS Button'), 'tooltip' => __('setting.When enabled, staff will see a Send SMS button on the call screen. They can use it to send text messages directly to the visitor')],
        'show_call_history' => ['label' => __('setting.Enable Visitor History Button'), 'tooltip' => __('setting.When enabled, staff can open the History option on the call screen to view all actions performed for that visitor, such as whether they were called, missed, or put on hold')],
        'counter_option' => ['label' => __('setting.Enable Counter Change Option for Staff'), 'tooltip' => __('setting.when staff are created, the admin selects one primary counter and may also assign multiple counters. If this switch is enabled, staff can view both the selected counter and the additional assigned counters, and switch between them as needed. If disabled, staff will only see the admin-selected (primary) counter and cannot change')],
        // 'staff_rating' => ['label' => __('setting.Need Staff Rating'), 'tooltip' => __('setting.Enable staff rating feature')],
        'is_waiting_time' => ['label' => __('setting.Hide Waiting Time on Call Screen'), 'tooltip' => __('setting.When enabled, the waiting time of each visitor will not be displayed on the waiting queues')],
        'is_cancelled_queue' => ['label' => __('setting.Enable Cancel Button'), 'tooltip' => __('setting.When enabled, a Cancel button will appear in the action popup (opened when staff clicks a visitor from the list). Staff can use this button to cancel the ticket/queue, and the ticket will be moved to the Cancelled status')],
        // 'ticket_generation_link' => ['label' => __('setting.Ticket Generation Link'), 'tooltip' => __('setting.Show link for ticket generation')],
        'served_queue' => ['label' => __('setting.Display Served Queue on Call Screen'), 'tooltip' => __('setting.When enabled, staff can view the list of visitors who have already been served directly on the call screen. If disabled, served queues will not be displayed. This helps staff keep track of completed visits and maintain better visibility of their call history')],
        'total_served' => ['label' => __('setting.Enable Total Served Queue Count'), 'tooltip' => __('setting.When enabled, the call screen will display a section showing the total number of visitors served by the staff')],
        'is_transfer_option' => ['label' => __('setting.Enable transfer button'), 'tooltip' => __('setting.When enabled, a Transfer button will appear in the call screen action popup. Staff can use it to transfer the current ticket/queue to another staff member or service. If disabled, the transfer option will not be available')],
        'is_client_update' => ['label' => __('setting.Enable Edit Visitor Button'), 'tooltip' => __('setting.When this option is enabled, an Edit Visitor button will appear on the call screen. Staff can use it to update visitor details if changes are required. If disabled, the edit option will be hidden, and staff won’t be able to modify visitor information')],
        'is_sound_notification' => ['label' => __('setting.Enable Sound Notification'), 'tooltip' => __('setting.This option allows staff to receive audio notifications on the call screen. When enabled, a sound will play for specific actions/events, such as clicking Next to call the next visitor or closing a ticket. If disabled, no sound will be played for these events')],
        'break' => ['label' => __('setting.Enable Break Button'), 'tooltip' => __('setting.This option shows a Break button on the call screen. Staff can click it to indicate that they are taking a break')],
        // 'activity_log' => ['label' => __('setting.Activity Log'), 'tooltip' => __('setting.Enable logging of activities')],
        'is_suspension_button' => ['label' => __('setting.Enable Suspension Button'), 'tooltip' => __('setting.When enabled, staff will see a Suspension button on the call screen. They can use it to cancel all active queues at once and send SMS/Email notifications to visitors with a cancellation reason')],
        'display_name' => ['label' => __('setting.Enable Name Instead of Token Number'), 'tooltip' => __('setting.When enabled, visitor names will appear instead of ticket numbers in all places on the call screen')],
        'notes_add_option' => ['label' => __('setting.Enable Add Note Button'), 'tooltip' => __('setting.This setting enables the Add Note button on the call screen. Staff can click it to open a text field and enter notes about the visitor, ticket, or interaction. These notes will be saved for reference in reports or history')],
        'counter_transfer' => ['label' => __('setting.Enable Counter Transfer Option'), 'tooltip' => __('setting.Show/hide the counters for transfering the calls')],
        'login_counters_only' => ['label' => __('setting.Enable Login Counters Only'), 'tooltip' => __('setting.Display Only Login Counters')],
        'category_transfer' => ['label' => __('setting.Enable Service Transfer Option'), 'tooltip' => __('setting.Show/hide the service for transfering the calls')],
        'show_transfer_token' => ['label' => __('setting.Enable Transfer token List'), 'tooltip' => __('setting.Show/hide token of the transfering the calls')],
        // 'mode_transfer_option' => ['label' => __('setting.Walk-IN/Appointment Transfer Option'), 'tooltip' => __('setting.Allow to tranfer walk-in to appointment and vica-versa')],
 'enable_waiting_popup' => ['label' => __('setting.Enable Waiting Popup'), 'tooltip' => __('setting.This setting enables the waiting time popup for visitors on the screen')],
    ] as $field => $data)
                     <label class="flex items-center space-x-2 relative">
                        <input type="checkbox" wire:model.defer="data.{{ $field }}" class="rounded-md"
                            {{ $this->siteDetails?->$field == '1' ? 'checked' : '' }}>
                        <span>{{ $data['label'] }}</span>

                        <!-- Info Icon -->
                        
                        <div class="relative group inline-block">
                          <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="9" />
                                <line x1="12" y1="8" x2="12" y2="8" />
                                <line x1="12" y1="12" x2="12" y2="16" />
                            </svg>
                        </button>
                          <span
                            class="absolute left-1/2 -translate-x-1/2 z-50 mb-2 -translate-x-1/2 hidden group-hover:block 
                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                   w-56">
                            {{ $data['tooltip'] }}
                          </span>
                        </div>
                        
                    </label>
                @endforeach
            </div>

            <div class="mt-6 grid grid-cols-2 gap-4">
                 <div>
                    <label
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('setting.show the waiting time popup (mins)') }}</label>
                    <input type="number" wire:model.defer="data.popup_waiting_time"
                        class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 px-3 py-2 focus:ring focus:ring-indigo-200">
                </div>
                <div>
                    <label
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('setting.Fixed Queue Size') }}</label>
                    <input type="text" wire:model.defer="data.fixed_queue_size"
                        class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 px-3 py-2 focus:ring focus:ring-indigo-200">
                </div>

                @foreach ([
                    'label_next' => __('setting.Next Button Label'),
                    'label_recall' => __('setting.Recall Button Label'),
                    'label_start' => __('setting.Start Button Label'),
                    'label_close' => __('setting.Close Button Label'),
                    'label_skip' => __('setting.Missed Button Label'),
                    'label_move_back' => __('setting.Move Back Button Label'),
                    'label_transfer' => __('setting.Transfer Button Label'),
                    'label_generate_queue' => __('setting.Generate Queue Button Label'),
                    'label_counter' => __('setting.Counter Label'),
                    'label_no_call' => __('setting.No Call Label'),
                    'label_total_served_token' => __('setting.Total Served Tokens Label'),
                    'label_cancelled_queue_no' => __('setting.Cancelled Queue No. Label'),
                    'label_missed_queue' => __('setting.Missed Queue Label'),
                    'label_hold_queue' => __('setting.Hold Queue Label'),
                    'label_visitor_waiting' => __('setting.Visitors are Waiting Label'),
                    'label_current_serving' => __('setting.Current Serving Label'),
                    'label_queue_number' => __('setting.Queue Number Label'),
                    'label_serving_time' => __('setting.Serving Time Label'),
                    'label_issue_date' => __('setting.Issue Date Label'),
                    'label_transfer_token' => __('setting.transfer token label'),
    ] as $field => $label)
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</label>
                        <input type="text" wire:model.defer="data.{{ $field }}"
                            class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 px-3 py-2 focus:ring focus:ring-indigo-200">
                    </div>
                @endforeach
            </div>

            <div class="mt-6 grid grid-cols-2 gap-4">
                {{-- <div>
                    <label
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('setting.Show Total Call Count') }}</label>
                    <select wire:model.defer="data.total_call_count"
                        class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 px-3 py-2 focus:ring focus:ring-indigo-200">
                        <option value="Served Calls">{{ __('setting.Served Calls') }}</option>
                        <option value="Served + Missed Calls">{{ __('setting.Served Calls') }} +
                            {{ __('setting.Missed Calls') }}</option>
                    </select>
                </div> --}}
                <div>
                    <label
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('setting.Show Buttons Start and Close') }}</label>
                    <select wire:model.defer="data.hide_button"
                        class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 px-3 py-2 focus:ring focus:ring-indigo-200">
                        @foreach ($hidebuttons as $key => $hidebutton)
                            <option value="{{ $key }}">{{ $hidebutton }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('setting.Enable Visitor Priority') }}</label>
                    <select wire:model.defer="data.queue_priority"
                        class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 px-3 py-2 focus:ring focus:ring-indigo-200">
                        <option value="Default">{{ __('setting.Default') }}</option>
                        <option value="Sort">{{ __('setting.Sort') }}</option>
                    </select>
                </div>
                {{-- <div>
                    <label
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('setting.Add Note Option') }}</label>
                    <select wire:model.defer="data.notes_add_option"
                        class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 px-3 py-2 focus:ring focus:ring-indigo-200">
                        <option value="0">{{ __('setting.no') }}</option>
                        <option value="1">{{ __('setting.yes') }}</option>
                    </select>
                </div> --}}
            </div>

            <div class="mt-6 grid grid-cols-2 gap-4">
                {{-- <div>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" wire:model.defer="data.email_reminder_status" class="rounded-md" {{ $this->siteDetails?->email_reminder_status == "1" ? "checked" : "" }}>
                        <span>{{ __('setting.Email Reminder') }}</span>
                    </label>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('setting.Time') }}</label>
                    <input type="number" wire:model.defer="data.email_reminder_time" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 px-3 py-2 focus:ring focus:ring-indigo-200">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('setting.Period') }}</label>
                    <select wire:model.defer="data.period" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 px-3 py-2 focus:ring focus:ring-indigo-200">
                        <option value="minutes" {{ $this->siteDetails?->email_reminder_type == 'minutes' ? 'selected' : '' }}>{{ __('setting.Minutes') }}</option>
                        <option value="hours" {{ $this->siteDetails?->email_reminder_type == 'hours' ? 'selected' : '' }}>{{ __('setting.Hours') }}</option>
                        <option value="days" {{ $this->siteDetails?->email_reminder_type == 'days' ? 'selected' : '' }}>{{ __('setting.Days') }}</option>
                    </select>
                </div> --}}

                @if ($hold_queue_feature)
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('setting.Default Hold Message') }}</label>
                        <textarea wire:model.defer="data.hold_message"
                            class="w-full dark:bg-dark-900 h-30 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700  dark:bg-gray-800 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"></textarea>
                    </div>
                @endif
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" wire:loading.attr="disabled" wire:target="save"
                    class="px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 flex items-center">
                    <span wire:loading wire:target="save"
                        class="animate-spin border-t-2 border-white border-solid rounded-full h-5 w-5 mr-2"></span>
                    {{ __('setting.Save') }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        Livewire.on('call-screen-settings-created', count => {
            Swal.fire({
                title: "Success!",
                text: "Call Screen Settings Created!",
                icon: "success"
            });
        });
        Livewire.on('call-screen-settings-updated', count => {
            Swal.fire({
                title: "Success!",
                text: "Call Screen Settings Updated!",
                icon: "success"
            });
        });
    });

    function toggleTooltip(el) {
        // Close any other open tooltips
        document.querySelectorAll('.tooltip-box').forEach(t => t.classList.add('hidden'));

        // Toggle current tooltip
        const tooltip = el.parentElement.querySelector('.tooltip-box');
        tooltip.classList.toggle('hidden');
    }

    // Close tooltip when mouse leaves the whole label
    document.querySelectorAll('label').forEach(label => {
        label.addEventListener('mouseleave', function() {
            const tooltip = label.querySelector('.tooltip-box');
            if (tooltip) tooltip.classList.add('hidden');
        });
    });
</script>
