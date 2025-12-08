<div class="container">
    @push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.5.1/main.min.css"
        integrity="sha256-uq9PNlMzB+1h01Ij9cx7zeE2OR2pLAfRw3uUUOOPKdA=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
    :root {
        --fc-event-bg-color: var(--bs-primary) !important;
        --fc-event-border-color: var(--bs-primary) !important;
    }

    .fc-event-title a {
        color: white !important;
    }

    .fc-daygrid-event {
        border-radius: 50em;
        padding: 3px 10px;
    }

    .fc-daygrid-event.fc-event-start {
        margin-left: calc(var(--spacing) * 0) !important;
    }

    .fc-prev-button {
        color: white !important;
        background-color: transparent !important;
        border: none !important;
    }

    /* Style for the Next button */
    .fc-next-button {
        color: white !important;
        background-color: transparent !important;
        border: none !important;
    }
    </style>
    @endpush

    <div>
        <div id='calendar' class="bg-white dark:bg-gray-800 dark:text-gray-100 dark:border-gray-100"></div>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.9/flatpickr.min.js"
    integrity="sha512-+ruHlyki4CepPr07VklkX/KM5NXdD16K1xVwSva5VqOVbsotyCQVKEwdQ1tAeo3UkHCXfSMtKU/mZpKjYqkxZA=="
    crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.5.1/main.min.js"
    integrity="sha256-rPPF6R+AH/Gilj2aC00ZAuB2EKmnEjXlEWx5MkAp7bw=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.5.1/locales-all.min.js"
    integrity="sha256-/ZgxvDj3QtyBZNLbfJaHdwbHF8R6OW82+5MT5yBsH9g=" crossorigin="anonymous"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    if (calendarEl) {
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            events: @json($bookings),
            editable: false,
            selectable: false,
            validRange: {
                start: new Date().toISOString().split('T')[0] // Today's date in 'YYYY-MM-DD' format
            },
            customButtons: {
                bookAppointment: {
                    text: 'Book Appointment',
                    click: function() {
                        window.location.href = "{{ route('book-appointment') }}";
                    }
                }
            },
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'bookAppointment'
            },
            eventContent: function(arg) {
                // Format start and end times
                var startTime = arg.event.start.toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });
                var endTime = arg.event.end.toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });

                // Create custom HTML content
                var title = document.createElement('div');
                title.innerHTML = `${arg.event.title} <br/>(${startTime} - ${endTime})`;

                return {
                    domNodes: [title]
                };
            }
        });
        calendar.render();
    }
});
</script>
@endpush