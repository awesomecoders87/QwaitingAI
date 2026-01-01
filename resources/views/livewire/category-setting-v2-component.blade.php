@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@3.10.2/dist/fullcalendar.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/ui-lightness/jquery-ui.css">
<style>
    .fc-unthemed td.fc-today{background:transparent}
    .fc-daybox-border {border: 1px solid #d9d9d9 !important;box-shadow: inset rgb(0, 0, 0) 0 0 2px;-webkit-box-shadow: inset rgb(0, 0, 0) 0 0 2px;}
    .fc-row .fc-content-skeleton{z-index:1}
    .fc-row .fc-bg {z-index: 2;}
    .fc-editable-modal{padding-top:0}
    .fc-day {
        position: relative;
        overflow: visible;
    }
    .fc-day.fc-daybox-border {
        position: relative;
        z-index: 100;
    }
    
    .week-name-slots {
        width: 30%;
        float: left;
        padding-left: 30px;
        border-left: 1px solid #ddd;
        max-height: none;
        overflow-y: visible;
        box-sizing: border-box;
    }
    .week-name-slots ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .week-name-slots ul li {
        text-decoration: none;
        list-style: none;
        line-height: 2.8;
        margin: 0;
        padding: 8px 0;
        display: flex;
        align-items: center;
    }
    .week-name-slots ul li input[type="checkbox"] {
        margin-right: 12px;
        width: 18px;
        height: 18px;
        cursor: pointer;
        flex-shrink: 0;
        accent-color: #666;
    }
    .week-name-slots ul li input[type="checkbox"]:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        accent-color: #999;
    }
    .week-name-slots ul li.selected-day {
        background: transparent;
    }
    .week-name-slots ul li label {
        cursor: pointer;
        user-select: none;
        flex: 1;
        font-size: 14px;
        color: #333;
    }
    .week-name-slots ul li:hover {
        background: transparent;
    }
    .week-name-slots ul li.selected-day:hover {
        background: transparent;
    }
    td{background:transparent}  
    
    .popup-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .popup-overlay.hide {
        display: none;
    }
    .popup-wraper {
        background: white;
        border-radius: 8px;
        max-width: 680px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
    }
    .popup-head {
        padding: 20px;
        border-bottom: 1px solid #e5e7eb;
        font-weight: 600;
        font-size: 1.25rem;
    }
    .popup-body {
        padding: 20px;
    }
    .popup-footer {
        padding: 20px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
    .timing-slots {
        margin-top: 0;
        width: 70%;
        float: left;
        padding-right: 30px;
        box-sizing: border-box;
    }
    @media (max-width: 768px) {
        .timing-slots {
            width: 100%;
            float: none;
            padding-right: 0;
            margin-bottom: 20px;
        }
        .week-name-slots {
            width: 100%;
            float: none;
            padding-left: 0;
            border-left: none;
            border-top: 1px solid #ddd;
            padding-top: 20px;
            margin-top: 20px;
        }
    }
    .popup-form {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 15px;
        flex-wrap: nowrap;
        width: 100%;
    }
    .popup-form select {
        padding: 10px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
        min-width: 100px;
        flex: 0 1 auto;
        width: auto;
        max-width: 130px;
        background: #fff;
        cursor: pointer;
        color: #333;
        height: 40px;
        box-sizing: border-box;
    }
    .popup-form .separator {
        flex-shrink: 0;
        white-space: nowrap;
    }
    .popup-form select:focus {
        outline: none;
        border-color: #007cba;
    }
    .popup-form .separator {
        color: #666;
        font-weight: 500;
        padding: 0 5px;
    }
    .add-btn {
        cursor: pointer;
        padding: 0;
        background: transparent;
        color: rgba(77, 80, 85, 0.6);
        border: none;
        font-size: 28px;
        line-height: 1;
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .add-btn:hover {
        color: rgba(77, 80, 85, 0.8);
    }
    .delete-btn {
        cursor: pointer;
        padding: 0;
        background: transparent;
        color: rgba(77, 80, 85, 0.6);
        border: none;
        font-size: 20px;
        line-height: 1;
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
    }
    .delete-btn:hover {
        color: #ef4444;
    }
    .unavailable-text {
        color: #ef4444;
        font-weight: 500;
    }
    .btn {
        padding: 10px 20px;
        border-radius: 4px;
        border: none;
        cursor: pointer;
    }
    .btn-default {
        background: #e5e7eb;
        color: #374151;
    }
    .btn-primary {
        background: #3b82f6;
        color: white;
    }
    /* Calendar style from cakepp */
    .fc-unthemed td.fc-today{background:transparent}
    .fc-row .fc-content-skeleton{z-index:1}
    .fc-row .fc-bg {z-index: 2;}
    .fc-editable-modal {
        box-sizing: border-box;
        padding: 6px 0;
        background: #ffffff;
        border: 1px solid rgba(0, 0, 0, 0.2);
        border-radius: 6px;
        box-shadow: 0 1px 6px rgba(0, 0, 0, 0.2);
        -webkit-user-select: none;
        -ms-user-select: none;
        user-select: none;
        bottom: 0px;
        left: 0px;
        z-index: 1000;
        width: auto;
        min-width: 100%;
        white-space: nowrap;
        position: absolute;
        overflow: visible;
    }
    .fc-editable-modal.hide {
        display: none !important;
    }
    .fc-editable-modal .fc-edit-link{
        position: relative;
        display: block;
        box-sizing: border-box;
        width: 100%;
        padding: 8px 11px;
        color: var(--text-color, rgb(77, 80, 85));
        font-size: 12px;
        line-height: 20px;
        text-align: left;
        text-decoration:none
    }
    .fc-editable-modal .fc-edit-link:first-child{border-bottom:1px solid #ddd}
    .fc-editable-modal .fc-edit-link:hover{background: rgba(77, 80, 85, 0.1);text-decoration:none}
    .fc-editable-modal .fc-edit-link strong{margin-left:0px}
    .fc-daybox-border{border:1px solid #000 !important}
    .fc-editable-modal i.fa.fa-address-card-o,.fc-editable-modal i.fa.fa-calendar-o {
        margin-right: 8px;
        margin-top: 3px;
    }
    .popup-overlay {
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(255, 255, 255, .8);
        z-index: 9;
        overflow-y: auto;
        overflow-x: hidden;
        padding: 20px;
        box-sizing: border-box;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .popup-wraper{
        display: flex; 
        justify-content: center;
        align-items: center;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
        margin: 0;
    }
    .popup {
        background: #fff;
        z-index: 999;   
        max-width: 680px;
        width: 100%;
        min-width: 320px;
        border: 1px solid rgba(0, 0, 0, 0.2);
        border-radius: 6px;
        box-shadow: 0 1px 6px rgba(0, 0, 0, 0.2);
        box-sizing: border-box;
        margin: 0 auto;
    }
    .popup .popup-head {
        display: block;
        border-bottom: 1px solid #ddd;
        padding: 15px 24px;
        font-weight: 600;
        font-size: 18px;
        text-align: left;
        background: #fff;
    }
    .popup .popup-body {
        padding: 24px;
        display: block;
        width: 100%;
        box-sizing: border-box;
        overflow: visible;
        max-height: none;
    }
    .popup-body p {
        font-weight: 600;
        margin-bottom: 20px;
        font-size: 16px;
        color: #333;
    }
    .popup-body::after {
        content: "";
        display: table;
        clear: both;
    }
    .popup .popup-form{margin-bottom:15px}
    .popup-form select {
        border: 1px solid #ddd;
        padding: 10px;
        color: #333;
        font-size: 16px;
        width: 25%;
        border-radius:4px;
    }
    .popup-form span.add-btn {
        margin: 0;
        background: transparent;
        border: 0;
        font-size: 34px;
        padding: 0;
        line-height: 1;
        color: rgba(77, 80, 85, 0.6);
        cursor: pointer;
    }
    .popup-form .delete-btn {
        font-size: 24px;
        color: rgba(77, 80, 85, 0.6);
        margin-left: 15px;
    }
    .popup .popup-footer {
        border-top: 1px solid #ddd;
        padding: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        display: grid;
        grid-gap: 12px 24px;
        -ms-grid-rows: auto;
        -ms-grid-columns: 1fr 24px 1fr;
        grid-template: 'cancel confirm' / 1fr 1fr;
    }
    .popup-footer {
        padding: 15px 24px;
        border-top: 1px solid #ddd;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
    }
    .popup-footer .btn {
        margin: 0;
        box-sizing: border-box;
        min-height: 44px;
        padding: 10px 24px;
        font-size: 14px;
        line-height: 20px;
        vertical-align: middle;
        border: 1px solid;
        border-radius: 6px;
        transition: all 0.3s ease;
        cursor: pointer;
        font-weight: 500;
    }
    .btn.btn-default {
        background: #fff;
        border-color: #333;
        color: #333;
    }
    .btn.btn-default:hover {
        background: #f5f5f5;
    }
    .btn.apply-btn, .btn.btn-primary {
        background: rgb(0, 162, 255);
        border-color: rgb(0, 162, 255);
        color: #fff;
    }
    .popup-footer .btn:hover {
        opacity: 0.9;
    }
    .fc-scroller {
        overflow: visible !important;
    }
    .fc-body {
        position: relative;
    }
    .fc-day-grid-container {
        overflow: visible !important;
    }
    .fc-day-grid {
        overflow: visible !important;
    }
    .fc-event, .fc-event-dot {
        background-color: transparent;
        border: none;
        color: #4d5055 !important;
    }
    .fc-event-container span.fc-title{
        font-size: 13px;
        font-weight: normal;
    }
    .unavailable-text{color: #ccc !important;font-size: 15px;font-weight: normal;}
    .fc-editable-modal a{
        position:absolute;
        width:100%; 
        left:0;
        top:0;
        display:block;
        height:36px;
        z-index: 1001;
        cursor: pointer;
        text-decoration: none;
    }
    .fc-editable-modal a.date-edit{top:0;}
    .fc-editable-modal a.week-edit,.fc-editable-modal a.reset-menu{top:36px;}
    .fc-editable-modal .fc-edit-link{
        position: relative;
        cursor: pointer;
        z-index: 1002;
        pointer-events: auto;
        display: block;
    }
    .fc-editable-modal a span.fc-edit-link{
        pointer-events: auto;
    }
    #full-calendar {
        margin: 10px auto;
        background-color:#fff;
    }
    .calendar-datepicker {
        margin-bottom: 20px;
        width: 100%;
    }
    .calendar-datepicker .ui-datepicker {
        width: 100%;
        font-size: 14px;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 0;
    }
    .calendar-datepicker .ui-datepicker-header {
        background: #ff9800;
        border: none;
        border-radius: 4px 4px 0 0;
        color: #fff;
        padding: 10px;
    }
    .calendar-datepicker .ui-datepicker-header .ui-datepicker-title {
        color: #fff;
        font-weight: 600;
    }
    .calendar-datepicker .ui-datepicker-header .ui-datepicker-prev,
    .calendar-datepicker .ui-datepicker-header .ui-datepicker-next {
        color: #fff;
        cursor: pointer;
    }
    .calendar-datepicker .ui-datepicker-header .ui-datepicker-prev:hover,
    .calendar-datepicker .ui-datepicker-header .ui-datepicker-next:hover {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 3px;
    }
    .calendar-datepicker .ui-datepicker-header .ui-datepicker-prev span,
    .calendar-datepicker .ui-datepicker-header .ui-datepicker-next span {
        border-color: #fff;
    }
    .calendar-datepicker .ui-datepicker-calendar {
        width: 100%;
        margin: 0;
    }
    .calendar-datepicker .ui-datepicker-calendar th {
        padding: 8px;
        font-weight: 600;
        background: #f5f5f5;
        border-bottom: 1px solid #ddd;
    }
    .calendar-datepicker .ui-datepicker-calendar td {
        padding: 2px;
        border: none;
    }
    .calendar-datepicker .ui-datepicker-calendar td a {
        text-align: center;
        display: block;
        padding: 8px;
        text-decoration: none;
        border-radius: 3px;
        transition: all 0.2s;
    }
    .calendar-datepicker .ui-datepicker-calendar td a:hover {
        background: #e3f2fd;
    }
    .calendar-datepicker .ui-state-highlight {
        background: #2196F3 !important;
        color: #fff !important;
        border-color: #2196F3 !important;
        border-radius: 3px;
    }
    .calendar-datepicker .ui-state-highlight a {
        color: #fff !important;
        font-weight: 600;
    }
    .calendar-datepicker .ui-state-highlight:hover {
        background: #1976D2 !important;
    }
    .calendar-datepicker .ui-datepicker-calendar .ui-state-disabled {
        opacity: 0.3;
    }
    .calendar-datepicker .ui-datepicker-calendar .ui-state-disabled a {
        cursor: not-allowed;
    }
    #append_dates-input {
        display: none;
    }
</style>
@endpush

<div>
    <div class="border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        @if($mainpage)
        <div class="p-6">
            <h3 class="fi-section-header-heading text-2xl font-semibold leading-2 text-gray-950 dark:text-white mb-4">
                {{ $record->name ?? '' }} - {{ __('setting.Category Calendar') }}
            </h3>
            
            <div class="table-res">
                <div id="full-calendar"></div>
            </div>
        </div>
        @endif
    </div>

    <!-- Week Edit Popup -->
    <div class="popup-overlay week-edit-poup hide">
    <div class="popup-wraper">
        <div class="popup" style="max-width: 680px;">
            <div class="popup-head" id="week-popup-head">Friday availability</div>
            <div class="popup-body">
                <p>What hours are you available?</p>
                <form id="formId">
                    <div class="timing-slots"></div>
                    <div class="week-name-slots">
                        <ul>
                            <li>
                                <input type="checkbox" name="week_name[]" value="Sunday" id="week_sunday">
                                <label for="week_sunday">Sunday</label>
                            </li>
                            <li>
                                <input type="checkbox" name="week_name[]" value="Monday" id="week_monday">
                                <label for="week_monday">Monday</label>
                            </li>
                            <li>
                                <input type="checkbox" name="week_name[]" value="Tuesday" id="week_tuesday">
                                <label for="week_tuesday">Tuesday</label>
                            </li>
                            <li>
                                <input type="checkbox" name="week_name[]" value="Wednesday" id="week_wednesday">
                                <label for="week_wednesday">Wednesday</label>
                            </li>
                            <li>
                                <input type="checkbox" name="week_name[]" value="Thursday" id="week_thursday">
                                <label for="week_thursday">Thursday</label>
                            </li>
                            <li>
                                <input type="checkbox" name="week_name[]" value="Friday" id="week_friday">
                                <label for="week_friday">Friday</label>
                            </li>
                            <li>
                                <input type="checkbox" name="week_name[]" value="Saturday" id="week_saturday">
                                <label for="week_saturday">Saturday</label>
                            </li>
                        </ul>
                    </div>
                </form>
            </div>
            <div class="popup-footer"> 
                <button class="btn btn-default cancel-btn" data-popup-name="week-edit-poup">Cancel</button> 
                <button class="btn btn-primary apply-btn" data-popup-name="week-edit-poup">Apply</button>
            </div>
        </div>
    </div>
</div>

<!-- Date Edit Popup -->
<div class="popup-overlay date-edit-poup hide">
    <div class="popup-wraper">
        <div class="popup" style="max-width: 680px;">
            <div class="popup-head">Select the date(s) you want to assign specific hours</div>
            <div class="popup-body">
                <form id="dateFormId">
                    <div class="calendar-datepicker"></div>
                    <div id="append_dates-input"></div>
                    
                    <p style="margin-top: 20px; margin-bottom: 15px; font-weight: 600;">What hours are you available?</p>
                    
                    <div class="timing-slots"></div>
                </form>
            </div>
            <div class="popup-footer"> 
                <button class="btn btn-default cancel-btn" data-popup-name="date-edit-poup">Cancel</button> 
                <button class="btn btn-primary apply-btn" data-popup-name="date-edit-poup">Apply</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@3.10.2/dist/fullcalendar.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Wait for jQuery to be available
    if (typeof jQuery === 'undefined') {
        console.error('jQuery is not loaded');
        return;
    }
    
    jQuery(document).ready(function($) {
        // Check if calendar element exists
        if ($('#full-calendar').length === 0) {
            console.error('Calendar element not found');
            return;
        }
        
        var plus_button = 0; 
        var prev_timing = []; 
        var slots_unavailable = false;
        var categoryId = {{ $categoryId }};
        var level = {{ $level }};
        
        // Generate time slots (30 min intervals from 9 AM to 6 PM)
        var time_slots_array = [];
        @php
            $start = \Carbon\Carbon::createFromTime(9, 0);
            $end = \Carbon\Carbon::createFromTime(18, 0);
            $slots = [];
            while($start->lt($end)) {
                $slots[] = $start->format('h:iA');
                $start->addMinutes(30);
            }
            $slots[] = $end->format('h:iA'); // Add end time
        @endphp
        @foreach($slots as $slot)
            time_slots_array.push('{{ $slot }}');
        @endforeach
        
        var business_end_time = '06:00PM';
        
        // Get week_calendar and date_calendar from component
        var weekCalendar = @json($weekCalendar ?? []);
        var dateCalendar = @json($dateCalendar ?? []);
        
        // Get default business hours from category or use defaults
        var categoryStartTime = '{{ $record->start_time ?? "09:00:00" }}';
        var categoryEndTime = '{{ $record->end_time ?? "18:00:00" }}';
        var businessStartTime = categoryStartTime ? moment(categoryStartTime, 'HH:mm:ss').format('h:mmA') : '09:00AM';
        var businessEndTime = categoryEndTime ? moment(categoryEndTime, 'HH:mm:ss').format('h:mmA') : '06:00PM';
        
        // Initialize FullCalendar (v3 API)
        $('#full-calendar').fullCalendar({
        defaultView: 'month',
        defaultDate: moment(),
        editable: true,
        selectable: false,
        allDaySlot: false,
        handleWindowResize: true,
        showNonCurrentDates: false,
        fixedWeekCount: false,
        eventLimit: 2,
        eventLimitText: function(num) {
            return '+' + num + ' more';
        },
        selectHelper: true,
        droppable: true,
        overlap: false,
        eventDurationEditable: false,
        header: {
            left: 'title',
            center: '',
            right: 'prev,next'
        },
        viewRender: function(currentView) {
            var minDate = moment();
            // Past
            if (minDate >= currentView.start && minDate <= currentView.end) {
                $(".fc-prev-button").prop('disabled', true); 
                $(".fc-prev-button").addClass('fc-state-disabled'); 
            } else {
                $(".fc-prev-button").removeClass('fc-state-disabled'); 
                $(".fc-prev-button").prop('disabled', false); 
            }
            
            var $container = $(".fc-body").find(".fc-day");
            
            var html = '<div class="fc-editable-modal hide">' +
            '<div class="fc-row fc-week fc-widget-content">' +
                '<div class="fc-bg">' +
                        '<a href="javascript:void(0)" class="date-edit open-time-popup" data-attr="date-edit"  data-date=""><span class="fc-edit-link"><i class="fa fa-calendar-o"></i> Edit <strong> date(s)</strong></span></a>' +
                        '<a href="javascript:void(0)" class="week-edit open-time-popup week-menu" data-attr="week-edit" data-date=""><span class="fc-edit-link-week fc-edit-link week-menu"><i class="fa fa-address-card-o"></i> Edit <strong> all Saturdays</strong></span></a>' +
                        // '<a href="javascript:void(0)" class="week-reset reset-menu" data-attr="week-reset" data-date=""><span class="fc-edit-link-week fc-edit-link reset-menu"><i class="fa fa-refresh"></i> Reset <strong> to weekly hours</strong></span></a>' +
                '</div>' +				
            '</div>' +
        '</div>';
        
            $container.prepend(html);
        },
        events: function(start, end, timezone, callback) {
            var events = [];
            var currentDate = moment(start);
            var endDate = moment(end);
            
            // Loop through each day
            while (currentDate.isBefore(endDate)) {
                var dateStr = currentDate.format('YYYY-MM-DD');
                var dayName = currentDate.format('dddd');
                
                // Check for date-specific schedule (date_calendar)
                if (dateCalendar && dateCalendar[dateStr] !== undefined) {
                    var daySchedule = dateCalendar[dateStr];
                    if (daySchedule && daySchedule.length > 0) {
                        // Has date-specific schedule
                        for (var k = 0; k < daySchedule.length; k++) {
                            var slot = daySchedule[k];
                            if (slot.start && slot.end) {
                                events.push({
                                    title: slot.start + ' - ' + slot.end,
                                    start: dateStr,
                                    start_time: slot.start,
                                    end_time: slot.end,
                                    type: 'day schedule',
                                    capacity: slot.capacity || ''
                                });
                            }
                        }
                    } else {
                        // Empty array means unavailable - no event
                    }
                } else if (weekCalendar && weekCalendar[dayName] !== undefined) {
                    // Use weekly schedule (week_calendar)
                    var weekSchedule = weekCalendar[dayName];
                    if (weekSchedule && weekSchedule.length > 0) {
                        for (var j = 0; j < weekSchedule.length; j++) {
                            var weekSlot = weekSchedule[j];
                            if (weekSlot.start && weekSlot.end) {
                                events.push({
                                    title: weekSlot.start + ' - ' + weekSlot.end,
                                    start: dateStr,
                                    start_time: weekSlot.start,
                                    end_time: weekSlot.end,
                                    type: 'week schedule',
                                    capacity: weekSlot.capacity || ''
                                });
                            }
                        }
                    }
                } else {
                    // Use default business hours
                    events.push({
                        title: businessStartTime + ' - ' + businessEndTime,
                        start: dateStr,
                        start_time: businessStartTime,
                        end_time: businessEndTime,
                        type: 'business schedule',
                        capacity: ''
                    });
                }
                
                currentDate.add(1, 'day');
            }
            
            callback(events);
        },
        dayClick: function(date, allDay, jsEvent, view) {
            prev_timing = []; 
            var schedule_type = '';
            
            // Getting events on dayclick
            var eventsCount = 0;
            var date_format = date.format('YYYY-MM-DD');
            $('#full-calendar').fullCalendar('clientEvents', function(event) {
                var start = moment(event.start).format("YYYY-MM-DD");
                if(date_format == start){
                    if(event.capacity && event.capacity.length){
                        var capacity = '-' + event.capacity;
                    } else {
                        var capacity = '';
                    }
                    
                    if(event.start_time){
                        prev_timing[eventsCount] = event.start_time + '-' + event.end_time + capacity;
                        eventsCount++;
                    }
                    
                    if(event.type == 'day schedule'){
                        schedule_type = 'day schedule';
                    }
                }
            });
            
            // change date format & also get current date
            var d = new Date(date.format());
            var current_time = new Date(moment().format('YYYY-MM-DD'));
            
            // menu option dynamic - update only within the clicked day cell
            var weekday = ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];
            var dayName = weekday[d.getDay()];
            
            // Update the menu text and data attributes for this specific day cell
            var $dayCell = $(this);
            $dayCell.find('.fc-edit-link-week.fc-edit-link.week-menu strong').text(' all '+dayName+'s');
            $dayCell.find('.week-edit').attr('data-date', date.format());
            $dayCell.find('.date-edit').attr('data-date', date.format());
            $dayCell.find('.week-reset').attr('data-date', date.format());
            
            // hide all day menus first
            $('.fc-editable-modal').addClass('hide');
            $('.fc-editable-modal').removeClass('show');
            
            // change the day's border just for fun
            $(".fc-day").removeClass('fc-daybox-border');
            
            if(d >= current_time){
                //showing reset to weekly hour menu
                if(schedule_type == 'day schedule'){
                    $dayCell.find('.week-menu').addClass('hide');
                    $dayCell.find('.week-menu').removeClass('show');
                    $dayCell.find('.reset-menu').addClass('show');
                    $dayCell.find('.reset-menu').removeClass('hide');
                } else {
                    $dayCell.find('.week-menu').addClass('show');
                    $dayCell.find('.week-menu').removeClass('hide');
                    $dayCell.find('.reset-menu').addClass('hide');
                    $dayCell.find('.reset-menu').removeClass('show');
                }
                
                // show menu/navigation with highlight box	
                $dayCell.find('.fc-editable-modal').removeClass('hide');
                $dayCell.find('.fc-editable-modal').addClass('show');
                $dayCell.addClass('fc-daybox-border');
            }
        }
        });
        
        // Function to normalize time format from "09:00 AM" to "09:00AM"
        function normalizeTimeFormat(time) {
            if (!time) return '';
            // Remove all spaces and ensure proper format
            var normalized = time.toString().trim().replace(/\s+/g, '');
            // If it's in "09:00AM" format already, return as is
            // If it's in "09:00 AM" format, spaces are removed above
            return normalized;
        }
        
        function time_slots(popup_type, slots_unavailable, start_time='', end_time='', capacity=''){
            if(slots_unavailable==false){
                // Normalize times before comparison
                var normalizedStart = normalizeTimeFormat(start_time);
                var normalizedEnd = normalizeTimeFormat(end_time);
                
                var slots = '<select name="start[]">';	
                time_slots_array.forEach(function(slot) {
                    if(normalizedStart == slot) {
                        var start_sel = 'selected';
                    } else {
                        var start_sel = '';
                    }
                    slots += '<option value="' + slot + '" '+start_sel+'>' + slot + '</option>';		
                });
                slots += '</select>';
                
                slots += '<span class="separator"> -- </span>';
                
                slots += '<select name="end[]">';	
                time_slots_array.forEach(function(slot) {
                    if(normalizedEnd == slot) {
                        var end_sel = 'selected';
                    } else if(normalizedEnd=='' && slot==business_end_time){
                        var end_sel = 'selected';	
                    } else { 
                        var end_sel = ''; 
                    }	
                    slots += '<option value="' + slot + '" '+end_sel+'>' + slot + '</option>';		
                });
                slots += '</select>';
                
                slots += '<span class="separator"> -- </span>';
                
                slots += '<select name="capacity[]">';	
                slots += '<option value="" >NA</option>';
                for(var i=1; i<=50; i++){
                    if(capacity==i) {
                        var capcity_sel = 'selected';
                    } else { 
                        var capcity_sel = ''; 
                    }	
                    slots += '<option value="' + i + '" '+capcity_sel+'>' + i + '</option>';		
                }
                slots += '</select>';
                
                slots += '<a href="#" class="delete-btn delete-slot-btn" data-type="'+popup_type+'" ><i class="fa fa-trash"></i></a>';		
            } else {
                var slots='<div class="unavailable-text pull-left">Unavailable</div>';
            }
            
            plus_btn_html = '';
            if(plus_button==0){
                var plus_btn_html = '<span class="add-btn add-slot-btn" data-type="'+popup_type+'">+</span>';
            }
            
            var time_slots_html = '<div class="popup-form">'+slots+plus_btn_html+'</div>';
            
            if(slots_unavailable==false){
                plus_button = plus_button + 1;
            }
            
            return time_slots_html;
        }
        
        // Edit date Popup
        $(document).on('click','.open-time-popup',function(e){
            e.preventDefault();
            e.stopPropagation();
            
            var popup_type = $(this).attr('data-attr');
            var date = $(this).attr('data-date');
            
            if(!popup_type || !date){
                return;
            }
            
            var d = new Date(date);
            
            // hide day menu/navigations
            hide_navigation_popup();
            hide_day_border();
            
            if(popup_type=='week-edit'){
                // dynamic popup head title
                var weekday = ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];
                var dayName = weekday[d.getDay()];
                $('.'+popup_type+'-poup .popup-head').html(dayName+' availability');
                
                // Reset all checkboxes
                $(".week-name-slots input[name='week_name[]']:checkbox").prop('checked', false).removeAttr("disabled").removeAttr("readonly").removeAttr("readonly");
                
                // Pre-check the clicked day and make it readonly
                var $clickedDayCheckbox = $(".week-name-slots input[type=checkbox][value="+dayName+"]");
                $clickedDayCheckbox.prop("checked", true).attr('readonly', true).attr('disabled', 'disabled');
                $clickedDayCheckbox.closest('li').addClass('selected-day');
                
                // Show popup first
                $('.'+popup_type+'-poup').removeClass('hide');
                $('.'+popup_type+'-poup').addClass('show');
                $('body').removeClass('modal-close');
                $('body').addClass('modal-open');
                $('.'+popup_type+'-poup #formId, .'+popup_type+'-poup #weekFormId, .'+popup_type+'-poup #dateFormId').attr('data-date',date);
                
                // Load existing time slots for this weekday from weekCalendar (data is already available from the component)
                plus_button = 0;
                var existingSlots = weekCalendar && weekCalendar[dayName] ? weekCalendar[dayName] : [];
                
                // Clear and populate timing slots area
                $('.'+popup_type+'-poup .timing-slots').html('');
                
                if(existingSlots.length > 0){
                    for(var i = 0; i < existingSlots.length; i++){
                        var slot = existingSlots[i];
                        // Normalize time format: convert "09:00 AM" to "09:00AM" if needed
                        var startTime = normalizeTimeFormat(slot.start || '');
                        var endTime = normalizeTimeFormat(slot.end || '');
                        $('.'+popup_type+'-poup .timing-slots').append(time_slots(popup_type, false, startTime, endTime, slot.capacity || ''));
                    }
                } else {
                    // No existing slots, show one empty slot
                    $('.'+popup_type+'-poup .timing-slots').html(time_slots(popup_type, false));
                }
                
                // Add hidden fields after slots are loaded
                add_hidden_fields(popup_type);
                
                // Don't continue to the common popup show code below
                return;
            } else if(popup_type=='date-edit'){
                // Initialize date picker for date edit
                var newdate = moment(date, 'YYYY-MM-DD').format('MM/DD/YYYY');
                
                // Initialize dates array with clicked date
                dates = [];
                dates.push(newdate);
                
                // Call datepicker
                call_datepicker(newdate);
                append_dates();
                
                // Get updated dateCalendar from component - fetch fresh data first
                var dateStr = moment(date).format('YYYY-MM-DD');
                
                // Show popup first
                $('.'+popup_type+'-poup').removeClass('hide');
                $('.'+popup_type+'-poup').addClass('show');
                $('body').removeClass('modal-close');
                $('body').addClass('modal-open');
                $('.'+popup_type+'-poup #formId, .'+popup_type+'-poup #weekFormId, .'+popup_type+'-poup #dateFormId').attr('data-date',date);
                
                // Load existing slots from dateCalendar for the clicked date (data is already available from the component)
                plus_button = 0;
                var existingSlots = dateCalendar && dateCalendar[dateStr] ? dateCalendar[dateStr] : [];
                
                if(existingSlots.length > 0){
                    $('.'+popup_type+'-poup .timing-slots').html('');
                    for(var i = 0; i < existingSlots.length; i++){
                        var slot = existingSlots[i];
                        // Normalize time format: convert "09:00 AM" to "09:00AM" if needed
                        var startTime = normalizeTimeFormat(slot.start || '');
                        var endTime = normalizeTimeFormat(slot.end || '');
                        $('.'+popup_type+'-poup .timing-slots').append(time_slots(popup_type, false, startTime, endTime, slot.capacity || ''));
                    }
                } else {
                    // No existing slots, show one empty slot
                    $('.'+popup_type+'-poup .timing-slots').html(time_slots(popup_type, false));
                }
                
                // Add hidden fields after slots are loaded
                add_hidden_fields(popup_type);
                
                // Don't continue to the common popup show code below
                return;
            }
            
            // show popup
            $('.'+popup_type+'-poup').removeClass('hide');
            $('.'+popup_type+'-poup').addClass('show');
            $('body').removeClass('modal-close');
            $('body').addClass('modal-open');
            
            $('.'+popup_type+'-poup #formId, .'+popup_type+'-poup #weekFormId, .'+popup_type+'-poup #dateFormId').attr('data-date',date);
            
            // add hidden field like category && selected date
            add_hidden_fields(popup_type);
        });
        
        function hide_navigation_popup(){
            $('.fc-editable-modal').addClass('hide');
            $('.fc-editable-modal').removeClass('show');
        }
        
        function hide_day_border(){
            $(".fc-day").removeClass('fc-daybox-border');
        }
        
        function add_hidden_fields(popup_type){
            var selected_date = $('.'+popup_type+'-poup #formId, .'+popup_type+'-poup #weekFormId, .'+popup_type+'-poup #dateFormId').attr('data-date');
            var numItems = $('.'+popup_type+'-poup input[name=week_date]').length;
            if(numItems == 0){
                $('.'+popup_type+'-poup .timing-slots').append('<input name="week_date" value="'+selected_date+'" type="hidden"><input name="category" value="'+categoryId+'" type="hidden">');
            }
        }
        
        // Cancel Editing 
        $(document).on('click','.cancel-btn',function(){
            var popup_name = $(this).attr('data-popup-name');
            $('.'+popup_name).removeClass('show');
            $('.'+popup_name).addClass('hide');
            $('body').removeClass('modal-open');
            $('body').addClass('modal-close');
        });
        
        // Add slots
        $(document).on('click','.add-slot-btn',function(){
            slots_unavailable = false;
            var popup_type = $(this).attr('data-type');
            var numItems = $('.'+popup_type+'-poup .delete-slot-btn').length;
            if(numItems > 0){
                $('.'+popup_type+'-poup .timing-slots').append(time_slots(popup_type,slots_unavailable));
            } else {
                $('.'+popup_type+'-poup .timing-slots').html(time_slots(popup_type,slots_unavailable));
            }
            add_hidden_fields(popup_type);
        });
        
        // delete slots
        $(document).on('click','.delete-slot-btn',function(){
            var popup_type = $(this).attr('data-type');
            var numItems = $('.'+popup_type+'-poup .popup-form').length;
            $(this).parent().remove();
            if(numItems == 1){
                $('.'+popup_type+'-poup .timing-slots').html('<div class="popup-form"><div class="unavailable-text pull-left">Unavailable</div> <span class="add-btn add-slot-btn" data-type="'+popup_type+'">+</span></div>');
                add_hidden_fields(popup_type);
            }
            plus_button = plus_button - 1;
        });
        
        // Apply btn click event
        $(document).on('click','.apply-btn',function(){
            $(".week-name-slots input[name='week_name[]']:checkbox").removeAttr("disabled").removeAttr("readonly");
            var popup_name = $(this).attr('data-popup-name');
            var formElement = $('.'+popup_name+' #formId, .'+popup_name+' #weekFormId, .'+popup_name+' #dateFormId');
            var date = formElement.attr('data-date');
            var frmdata = formElement.serializeArray();
            
            // Convert serializeArray to object format for Livewire
            var dataObj = {};
            $.each(frmdata, function(i, field) {
                // Handle array fields (week_name[], start[], end[], capacity[], date_array[])
                if(field.name.indexOf('[]') !== -1){
                    var fieldName = field.name.replace('[]', '');
                    if(!dataObj[fieldName]){
                        dataObj[fieldName] = [];
                    }
                    dataObj[fieldName].push(field.value);
                } else {
                    dataObj[field.name] = field.value;
                }
            });
            
            // Add category and week_date/date_array
            dataObj['category'] = categoryId;
            
            if(popup_name == 'week-edit-poup'){
                dataObj['week_date'] = date;
            } else if(popup_name == 'date-edit-poup'){
                // For date edit, ensure the clicked date is included (avoid duplicates)
                if(!dataObj['date_array']){
                    dataObj['date_array'] = [];
                }
                var clickedDate = moment(date).format('YYYY-MM-DD');
                // Only add if not already in the array
                if(dataObj['date_array'].indexOf(clickedDate) === -1){
                    dataObj['date_array'].push(clickedDate);
                }
            }
            
            // Call Livewire method
            if(popup_name == 'week-edit-poup'){
                @this.call('saveWeekData', dataObj).then(function(result) {
                    // Close popup first
                    $('.'+popup_name).removeClass('show');
                    $('.'+popup_name).addClass('hide');
                    $('body').removeClass('modal-open');
                    
                    // Refetch events after data is updated (component will re-render with new data)
                    $('#full-calendar').fullCalendar('refetchEvents');
                }).catch(function(error) {
                    console.error('Error saving week data:', error);
                    // Close popup even on error
                    $('.'+popup_name).removeClass('show');
                    $('.'+popup_name).addClass('hide');
                    $('body').removeClass('modal-open');
                });
            } else if(popup_name == 'date-edit-poup'){
                @this.call('saveDatesData', dataObj).then(function(result) {
                    // Close popup first
                    $('.'+popup_name).removeClass('show');
                    $('.'+popup_name).addClass('hide');
                    $('body').removeClass('modal-open');
                    
                    // Refetch events after data is updated (component will re-render with new data)
                    $('#full-calendar').fullCalendar('refetchEvents');
                }).catch(function(error) {
                    console.error('Error saving date data:', error);
                    // Close popup even on error
                    $('.'+popup_name).removeClass('show');
                    $('.'+popup_name).addClass('hide');
                    $('body').removeClass('modal-open');
                });
            }
        });
        
        // // Reset to weekly hours
        // $(document).on('click','.week-reset',function(){
        //     var date = $(this).attr('data-date');
            
        //     // Call Livewire method
        //     @this.call('resetToWeeklyHours', date).then(function(result) {
        //         $('#full-calendar').fullCalendar('refetchEvents');
        //         $(".fc-editable-modal").removeClass("show");
        //         $(".fc-editable-modal").addClass("hide");
        //         $(".fc-day").removeClass('fc-daybox-border');
        //     });
        // });
        
        // Click outside to hide menu
        $(document).on("click", function(event){
            var $trigger = $(".fc-basic-view");
            if($trigger !== event.target && !$trigger.has(event.target).length){
                $(".fc-editable-modal").removeClass("show");
                $(".fc-editable-modal").addClass("hide");
                $(".fc-day").removeClass('fc-daybox-border');	
            }            
        });
        
        // Date picker functions for date-edit popup
        var dates = [];
        
        function call_datepicker(newdate){
            $(".calendar-datepicker").datepicker("destroy");
            
            $(".calendar-datepicker").datepicker({
                inline: true,
                changeMonth: true,
                changeYear: true,
                minDate: new Date(),
                dateFormat: 'mm/dd/yy',
                showOtherMonths: false,
                selectMultiple: true,
                onSelect: function (dateText, inst) {
                    addOrRemoveDate(dateText);
                    // Refresh the datepicker to update highlighting
                    $(".calendar-datepicker").datepicker('refresh');
                },
                beforeShowDay: function (date) {
                    var year = date.getFullYear();
                    var month = padNumber(date.getMonth() + 1);
                    var day = padNumber(date.getDate());
                    var dateString = month + "/" + day + "/" + year;
                    
                    var gotDate = jQuery.inArray(dateString, dates);
                    if (gotDate >= 0) {
                        return [true, "ui-state-highlight", ""];
                    }
                    return [true, "", ""];
                }
            });
            
            // Set the initial date
            try {
                $('.calendar-datepicker').datepicker('setDate', newdate);
            } catch(e) {
                // If setDate fails, try alternative format
                var dateParts = newdate.split('/');
                var dateObj = new Date(dateParts[2], dateParts[0] - 1, dateParts[1]);
                $('.calendar-datepicker').datepicker('setDate', dateObj);
            }
        }
        
        function padNumber(number) {
            var ret = new String(number);
            if (ret.length == 1) 
                ret = "0" + ret;
            return ret;
        }
        
        function addDate(date) {
            if (jQuery.inArray(date, dates) < 0) 
                dates.push(date);
            append_dates();
        }
        
        function removeDate(index) {
            dates.splice(index, 1);
            append_dates();
        }
        
        function addOrRemoveDate(date) {
            var index = jQuery.inArray(date, dates);
            if (index >= 0) 
                removeDate(index);
            else 
                addDate(date);
            append_dates();
        }
        
        function append_dates() {
            var printArr = '';
            dates.forEach(function (val) {
                printArr += '<input type="hidden" value="' + val + '" name="date_array[]" />';
            });
            $('#append_dates-input').html(printArr);
        }
        
        // Listen for 'saved' event from Livewire component
        Livewire.on('saved', (data) => {
            var message = 'Updated successfully!';
            
            // Handle different data formats
            if (data) {
                if (typeof data === 'string') {
                    message = data;
                } else if (data.message) {
                    message = data.message;
                } else if (Array.isArray(data) && data.length > 0) {
                    if (typeof data[0] === 'string') {
                        message = data[0];
                    } else if (data[0] && data[0].message) {
                        message = data[0].message;
                    }
                }
            }
            
            // Show success popup
            Swal.fire({
                title: 'Success!',
                text: message,
                icon: 'success',
                confirmButtonText: 'OK',
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then((result) => {
                if (result.isConfirmed) {
                    // Reload the page to show updated calendar
                    window.location.reload();
                }
            });
        });
        
        // Listen for Livewire updates to ensure calendar stays visible and functional
        document.addEventListener('livewire:updated', function () {
            // After Livewire updates, ensure calendar is still functional
            setTimeout(function() {
                var $calendar = $('#full-calendar');
                if ($calendar.length > 0 && typeof $calendar.fullCalendar === 'function') {
                    try {
                        // Refresh calendar events after Livewire update
                        $calendar.fullCalendar('refetchEvents');
                    } catch(e) {
                        console.error('Error refreshing calendar after Livewire update:', e);
                    }
                }
            }, 300);
        });
        
    }); // End jQuery ready
}); // End DOMContentLoaded
</script>
@endpush
