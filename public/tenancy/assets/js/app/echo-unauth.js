

// import Echo from './../../../node_modules/laravel-echo/dist/echo.js';

// import Datepicker from './../../../node_modules/flowbite-datepicker/js/Datepicker.js';



// Initialize the datepicker
const appointment_date = document.getElementById('appointment_date');
if (appointment_date) {
  new Datepicker(appointment_date, {
    todayHighlight: true,
    minDate: new Date(), // Set the minimum date to today's date
    maxDate: new Date(Date.now() + 14 * 24 * 60 * 60 * 1000), // set maximum date to two weeks from today
    format:'yyyy-mm-dd'
  
  });
}

// Check if elements with class "dynamicDatePicker" exist
var dynamicDatePickers = document.getElementsByClassName("dynamicDatePicker");

// If the elements exist, initialize the datepicker for each one
if (dynamicDatePickers.length > 0) {
    for (var i = 0; i < dynamicDatePickers.length; i++) {
        new Datepicker(dynamicDatePickers[i], {
            todayHighlight: true,
            maxDate: new Date(), // Set maximum date to two weeks from today
            format: 'yyyy-mm-dd'
        });
    }
}