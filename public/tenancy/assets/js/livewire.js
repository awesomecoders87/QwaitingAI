document.addEventListener('livewire:init', () => {
    console.log('livewire initiate2');

    Livewire.on('window-print', (response) => {

       // Debug: Check response content

        let html = `<!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <script src="https://cdn.tailwindcss.com"></script>
            </head>
            <body>${response}</body>
            </html>`;

        var iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        document.body.appendChild(iframe);

        var iframeDoc = iframe.contentWindow.document;
        iframeDoc.open();
        iframeDoc.write(html);
        iframeDoc.close();

        setTimeout(() => {
            iframe.contentWindow.focus(); // Ensure focus before print
            iframe.contentWindow.print();

            setTimeout(() => {
                document.body.removeChild(iframe);
            }, 1000);
        }, 500); // Small delay for rendering
    });

    Livewire.on('swal:saving-queue', (response) => {
        Livewire.dispatch('refresh-component');

        let e = response[0];
        Swal.fire({
            title: e.title,
            text: e.text,
            icon: e.icon,
        });
    });
    Livewire.on('queue:refresh', (response) => {
        Livewire.dispatch('refresh-component');

    });

  Livewire.on('desktop-notify', (response) => {

        let e = response;
        console.log(e);
        Livewire.dispatch('desktop-notification',{token_notify:e.token_notify})
  });

  Livewire.on('swal:saved-queue', (response) => {

        let e = response[0];
        let disable_print =e.disable_print;
        console.log(e);
        // Livewire.dispatch('desktop-notification',{token_notify:e.token_notify})

        Swal.fire({
            title: '',
            icon: e.icon ?? 'success',
            html: e.html,
            timer: e.timer ?? 1500,
            confirmButtonText: e.confirmButtonText,
            showConfirmButton: false,
            allowOutsideClick: false,
           allowEscapeKey: false,
        }).then((result) => {
            if (result.isConfirmed || result.dismiss === Swal.DismissReason.outside) {

                Swal.close();
            //     document.getElementById('printQueue').innerHTML = '';

            // Livewire.dispatch('window-print', e.html);
            }
        });
        if(disable_print != 1){
                 console.log('window print run');
            document.getElementById('printQueue').innerHTML = '';
            Livewire.dispatch('window-print', e.html);
        }


    });
    Livewire.on('swal:saved-queue-no-print', (response) => {

        let e = response[0];
        console.log(e);
        Livewire.dispatch('desktop-notification',{token_notify:e.token_notify})

        Swal.fire({
            title: '',
            icon: e.icon ?? 'success',
            html: e.html,
            timer: e.timer ?? 2000,
            confirmButtonText: e.confirmButtonText,
            showConfirmButton: false,
            allowOutsideClick: false,
           allowEscapeKey: false,
        }).then((result) => {
            if (result.isConfirmed || result.dismiss === Swal.DismissReason.outside) {

                Swal.close();

            }
        });



    });

    Livewire.on('reload', () => {
         window.location.reload();
    });

    Livewire.on('booked-print', (response) => {
        let htmlData = response[0].html;
        let html = `<!DOCTYPE html>
          <head>
              <meta charset="utf-8">
              <meta name="viewport" content="width=device-width, initial-scale=1.0">
              <script src="https://cdn.tailwindcss.com"></script>  </head>
              <body>${htmlData}</div> </body>
              </html>`

        var iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        document.body.appendChild(iframe);

        var iframeDoc = iframe.contentWindow.document;
        iframeDoc.open();
        iframeDoc.write(html);
        iframeDoc.close();

        iframe.onload = function () {
            iframe.contentWindow.print();

            setTimeout(() => {
                document.body.removeChild(iframe);
            }, 1000);
        };

    });

    const voiceMessages = {
        10: { lang: 'hi-IN', voice: "Google हिन्दी" }, // Hindi
        9: { lang: 'fr-FR', voice: "Google français" }, // French
        8: { lang: 'es-ES', voice: "Google español" }, // Spanish
        7: { lang: 'ar-SA', voice: "Google العربية" }, // Arabic
        6: { lang: 'ar-SA', voice: "Google العربية" }, // Arabic
        5: { lang: 'es-ES', voice: "Google español" }, // Spanish
        4: { lang: 'bn-BD', voice: "Google বাংলা" }, // Bengali
        3: { lang: 'ar-SA', voice: "Google العربية" }, // Arabic
        2: { lang: 'zh-CN', voice: "Google 粤語（香港" }, // Chinese
        1: { lang: 'en-US', voice: "Google US English" }, // English
        0: { lang: null, voice: null } // Ding Dong
    };


    function getVoiceMessage(key) {
        return voiceMessages[key] || { lang: 'en-US', voice: "Google US English" }; // Default to English if key not found
    }
    // Load voices
    window.speechSynthesis.onvoiceschanged = function() {
        window.speechSynthesis.getVoices();
    };




    // Livewire.on('announcement-display', (response) => {
    //     alert('sound');
    //     let speech = response[0].speech;
    //     let screenTune = response[0].screen_tune;
    //     let voice_lang = response[0].voice_lang;
    //     let audioElement = document.createElement('audio');
    //     audioElement.id = 'notificationSound';
    //     audioElement.src = '/voices/dingdong.mp3';
    //     audioElement.preload = 'auto';
    //     audioElement.style.display = 'none';
    //     document.body.appendChild(audioElement);

    //     if (screenTune == 0) {
    //         audioElement.play();
    //         audioElement.addEventListener('ended', function () {
    //             document.body.removeChild(audioElement);
    //         });
    //     } else {
    //         if (!speech) return;
    //         if ('speechSynthesis' in window) {
    //             var speechText = new SpeechSynthesisUtterance(speech);
    //             speechText.lang = voice_lang;
    //             speechText.rate = 0.8; // Slow down the speed
    //             console.log('Speech Rate:', speechText.rate);

    //             window.speechSynthesis.onvoiceschanged = () => {
    //                 var voices = window.speechSynthesis.getVoices();
    //                 console.log('Available Voices:', voices);
    //                 var voice = voices.find(voice => voice.lang === voice_lang);

    //                 if (voice) {
    //                     console.log('Selected Voice:', voice);
    //                     speechText.voice = voice;
    //                 } else {
    //                     console.warn('Selected language voice not found, using default.');
    //                 }

    //                 window.speechSynthesis.speak(speechText);
    //             };

    //             // Fallback if voices are already loaded
    //             var voices = window.speechSynthesis.getVoices();
    //             if (voices.length > 0) {
    //                 var voice = voices.find(voice => voice.lang === voice_lang);
    //                 if (voice) {
    //                     console.log('Selected Voice:', voice);
    //                     speechText.voice = voice;
    //                 }
    //                 window.speechSynthesis.speak(speechText);
    //             }
    //         } else {
    //             alert('Your browser does not support speech synthesis.');
    //         }
    //     }
    // });

    // Livewire.on('announcement-display', (response) => {

    //     let speech = response[0].speech;
    //     let screenTune = response[0].screen_tune;
    //     let voice_lang = response[0].voice_lang;
    //     let audioElement = document.createElement('audio');
    //     audioElement.id = 'notificationSound';
    //     audioElement.src = '/voices/dingdong.mp3';
    //     audioElement.preload = 'auto';
    //     audioElement.style.display = 'none';
    //     document.body.appendChild(audioElement);

    //     if (screenTune == 0) {
    //         audioElement.play().catch((err) => {
    //             console.error('Audio playback blocked:', err);
    //         });
    //         audioElement.addEventListener('ended', function () {
    //             document.body.removeChild(audioElement);
    //         });
    //     } else {
    //         if (!speech) return;
    //         if ('speechSynthesis' in window) {

    //                 Livewire.dispatch('display-sound');
    //             var speechText = new SpeechSynthesisUtterance(speech);
    //             speechText.lang = voice_lang;
    //             speechText.rate = 0.8; // Slow down the speed

    //             loadVoices().then((voices) => {
    //                 console.log('Selected Voice:', voices);
    //                 var voice = voices.find(voice => voice.lang === voice_lang);
    //                 if (voice) {
    //                     speechText.voice = voice;
    //                 } else {
    //                     console.warn('Selected language voice not found, using default.');
    //                 }

    //                 window.speechSynthesis.speak(speechText);

    //             });
    //         } else {
    //             alert('Your browser does not support speech synthesis.');
    //         }
    //     }
    // });

    // Force Chrome to load voices
    function loadVoices() {
        return new Promise((resolve) => {
            let voices = window.speechSynthesis.getVoices();
            if (voices.length) {
                resolve(voices);
            } else {
                window.speechSynthesis.onvoiceschanged = () => {
                    resolve(window.speechSynthesis.getVoices());
                };
            }
        });
    }


    Livewire.on('event-success-call', (response) => {
        if (response[0].message == 'Call started Successfully')
            localStorage.removeItem('visitStartTime');
        Swal.fire({
            icon: "success",
            title: response[0].message,
            showConfirmButton: false,
            timer: 1500
        });
    });

    /**
     * Mobile Queue
     */
    Livewire.on('swal:leave-waitlist', (resposne) => {

        Swal.fire({
            title: " Are you sure you want to leave the waitlist?",
            showCloseButton: true,
            showCancelButton: true,
            focusConfirm: false,
            confirmButtonText: 'Leave waitlist',
            cancelButtonText: 'Stay on waitlist',
            showCloseButton: false
        }).then((result) => {
            if (result.isConfirmed) {
                Livewire.dispatch('cancel-from-waitlist');
            }
        });
    });


    Livewire.on('swal:arrived-alert', (response) => {

        Swal.fire({
            title: "Are you sure?",
            icon: "info",
            showCloseButton: true,
            showCancelButton: true,
            focusConfirm: false,
            confirmButtonText: `YES, DO IT`,
            cancelButtonText: `NO, DO NOT`,
            showCloseButton: false
        }).then((result) => {
            if (result.isConfirmed) {
                Livewire.dispatch('is-arrived');
            }
        });
    });
    Livewire.on('swal:confirm-alert', (response) => {

        Swal.fire({
            title: "Are you sure?",
            icon: "info",
            showCloseButton: true,
            showCancelButton: true,
            focusConfirm: false,
            confirmButtonText: `YES, DO IT`,
            cancelButtonText: `NO, DO NOT`,
            showCloseButton: false
        }).then((result) => {
            if (result.isConfirmed) {
                Livewire.dispatch('late-save-waitlist');
            }
        });
    });


     Livewire.on('swal:limit-exceed', (data) => {
                let response =data[0];
            Swal.fire({
                title: response.title,
                text: response.text,
                icon: response.icon,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: true,
                confirmButtonText: 'Check Now',
                confirmButtonColor: '#2563eb', // blue confirm button
                showCancelButton: false
            }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.reload();
                        }
                    });
    });




    /**
     * Booking Appointment
     */
    const appointment_date = document.getElementById('appointment_date');
//     Livewire.on('swal:saved', (response) => {
// alert('tset');
//         let e = response[0];
//         Swal.fire({
//             title: "Saved Successfully",
//             text: e.message,
//             icon: "success",
//             allowOutsideClick: false,
//         }).then((result) => {
//             window.location.reload();
//         });
//     });

    Livewire.on('swal:saving-booking', (response) => {

        let e = response[0];
        Swal.fire({
            title: e.title,
            text: e.text,
            icon: e.icon,
            allowOutsideClick: false,
        }).then((result) => {
            window.location.reload();
        });
    });

    Livewire.on('swal:saving-setting', (response) => {

        let e = response[0];
        Swal.fire({
            title: e.title,
            text: e.text,
            icon: e.icon,
            allowOutsideClick: false,
        }).then((result) => {
            window.location.reload();
        });
    });

    Livewire.on('swal:exist-booking', (response) => {

        let e = response[0];
        Swal.fire({
            title: e.title,
            text: e.text,
            icon: e.icon,
            allowOutsideClick: false,
        }).then((result) => {
            window.location.reload();
        });
    });
    Livewire.on('swal:ticket-generate', (response) => {
        let e = response[0];
        Swal.fire({
            title: e.title,
            text: e.text,
            icon: e.icon,
        });
    });



    /**
     * Full screen display screen
     *
     */

    let isFullscreen = false;

    Livewire.on('event-fullscreen', function (response) {
        isFullscreen = response[0].isFullscreen;

        if (isFullscreen) {
            document.documentElement.requestFullscreen().catch(err => {
                console.error(`Error attempting to enable fullscreen mode: ${err.message}`);
            });
        } else {
            document.exitFullscreen().catch(err => {
                console.error(`Error attempting to exit fullscreen mode: ${err.message}`);
            });
        }
    });

    // function fullScreenBtn(){
    //     const button = document.getElementById('toggleFullBtn');
    //     console.log('button',button);
    //     if (button) {
    //         button.style.display = document.fullscreenElement ? 'none' : 'block';
    //     }
    // }

    // document.addEventListener('fullscreenchange', function () {
    //     isFullscreen = !!document.fullscreenElement;
    //     fullScreenBtn();
    // });

    // Initial call to set button visibility based on initial fullscreen state
    // fullScreenBtn();

    window.handleSlotClick = function (value) {
        const slotElements = document.querySelectorAll('[data-slot]');

        slotElements.forEach(el => {
            if (el.getAttribute('data-slot') === value) {
                el.classList.remove('bg-gray-300');
                el.classList.add('bg-blue-300');
            } else {
                el.classList.remove('bg-blue-300');
                el.classList.add('bg-gray-300');
            }
        });

        // Call the Livewire method
        Livewire.dispatch('handleTimeClicked', { value });
    };

    Livewire.on('event-datepicker', (response) => {
        console.log('Event triggered:'+ JSON.stringify(response, null, 2)); // This should log when the event is triggered

        const datePicker = document.getElementById('datepicker-booking');
        let today = new Date();
        let defaultDate = today;

        if (response[0].closed_days && response[0].closed_days.includes(today.getDay()) ||
            response[0].disabled_dates.includes(today.toLocaleDateString('en-CA'))) {
            while (true) {
                defaultDate.setDate(defaultDate.getDate() + 1);
                if (!response[0].closed_days.includes(defaultDate.getDay()) &&
                    !response[0].disabled_dates.includes(defaultDate.toLocaleDateString('en-CA'))) {
                    break;
                }
            }
        }
        let showAdvanceDate =response[0].account.allow_req_before;

        let weekStart = response[0].account.week_start;

        // Map week_start to its corresponding number
        const weekStartMap = {
            "Monday": 1,
            "Tuesday": 2,
            "Wednesday": 3,
            "Thursday": 4,
            "Friday": 5,
            "Saturday": 6,
            "Sunday": 7
        };

        let weekStartNumber = weekStartMap[weekStart];

        flatpickr(datePicker, {
            inline: true,
            altInput: true,
            altFormat: "F j, Y",
            dateFormat: "Y-m-d",
            minDate: today,
            maxDate: new Date().fp_incr(showAdvanceDate),
            locale: {
                firstDayOfWeek: weekStartNumber
            },
            defaultDate: defaultDate,
            disable: [
                function (date) {
                    return response[0].closed_days.includes(date.getDay());
                },
                ...response[0].disabled_dates.map(dateString => new Date(dateString))
            ],
            onChange: function (selectedDate, dateStr, instance) {

                console.log(dateStr);
                Livewire.dispatch('update-appointment-time', { value: dateStr });
            }

        });
        Livewire.dispatch('update-appointment-time', { value: defaultDate.toLocaleDateString('en-CA') });

    });


});

// Check if the element with id "appointment_date" exists
var appointmentDateElement = document.getElementById("appointment_date");

// If the element exists, attach the event listener
if (appointmentDateElement) {
    appointmentDateElement.addEventListener("changeDate", function (e) {
        Livewire.dispatch('changeDate', {
            selectedDate: e.detail.datepicker.inputField.value
        });
    });
}
// Check if the element with id "appointment_date" exists
var dynamicDatePickers = document.getElementsByClassName("dynamicDatePicker");

// If the element exists, attach the event listener
if (dynamicDatePickers.length > 0) {
    Array.from(dynamicDatePickers).forEach(function (element) {
        element.addEventListener("changeDate", function (e) {
            Livewire.dispatch('changeDate', {
                selectedDate: e.detail.datepicker.inputField.value
            });
        });
    });
}



document.addEventListener("DOMContentLoaded", function () {
  const closeAllDropdowns = () => {
    document.querySelectorAll(".dropdown-menu.fixed-dropdown").forEach(menu => {
      menu.classList.remove("fixed-dropdown");
      menu.style.left = "";
      menu.style.top = "";
      menu.style.visibility = "hidden";
    });
  };

  document.addEventListener("click", function (e) {
    const btn = e.target.closest(".action-btn");

    if (!btn) {
      // Clicked outside -> close all
      closeAllDropdowns();
      return;
    }

    e.stopPropagation();
    const menu = btn.nextElementSibling;

    if (menu.classList.contains("fixed-dropdown")) {
      closeAllDropdowns();
      return;
    }

    closeAllDropdowns();

    // Positioning
    const rect = btn.getBoundingClientRect();
    const menuWidth = 160;
    const menuHeight = menu.scrollHeight || 100;

    let left = rect.left;
    let top = rect.bottom;

    if (left + menuWidth > window.innerWidth) {
      left = window.innerWidth - menuWidth - 10;
    }

    if (top + menuHeight > window.innerHeight) {
      top = rect.top - menuHeight;
    }

    menu.classList.add("fixed-dropdown");
    menu.style.left = left + "px";
    menu.style.top = top + "px";
    menu.style.visibility = "visible";
  });
});

document.addEventListener("DOMContentLoaded", function () {
    // Create the alert element
    const alertBox = document.createElement("div");
    alertBox.id = "internet-alert";
    alertBox.style.position = "fixed";
    alertBox.style.bottom = "20px";
    alertBox.style.right = "20px";
    alertBox.style.padding = "12px 18px";
    alertBox.style.borderRadius = "6px";
    alertBox.style.color = "#fff";
    alertBox.style.fontFamily = "sans-serif";
    alertBox.style.fontSize = "14px";
    alertBox.style.boxShadow = "0 4px 10px rgba(0,0,0,0.2)";
    alertBox.style.display = "none";
    alertBox.style.zIndex = "99999";
    document.body.appendChild(alertBox);

    function showAlert(message, bgColor) {
        alertBox.textContent = message;
        alertBox.style.backgroundColor = bgColor;
        alertBox.style.display = "block";
    }

    function hideAlert() {
        alertBox.style.display = "none";
    }

    function updateConnectionStatus() {
        if (navigator.onLine) {
            showAlert("✅ Internet connection restored", "#28a745");
            setTimeout(hideAlert, 3000); // hide after 3 seconds
        } else {
            showAlert("⚠️ You are offline. Check your internet connection.", "#dc3545");
        }

    }

    // Check on page load
    updateConnectionStatus();

    // Listen for connection changes
    window.addEventListener("online", updateConnectionStatus);
    window.addEventListener("offline", updateConnectionStatus);

    // Extra safety: recheck connection every few seconds
    setInterval(() => {
        if (!navigator.onLine) {
            showAlert("⚠️ You are offline. Check your internet connection.", "#dc3545");
        }
    }, 5000); // recheck every 5s
});


//when error come page refresh
document.addEventListener('livewire:init', () => {
    Livewire.hook('request', ({ fail, respond, payload, succeed, resolve, reject, options }) => {
        fail(async ({ status, preventDefault, retry }) => {
            if (status === 419) {
                preventDefault();

                // Fetch a new CSRF token
                try {
                    let response = await fetch('/sanctum/csrf-cookie', {
                        credentials: 'same-origin'
                    });

                    if (response.ok) {
                        // Get the new token from the meta tag
                        let token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                        // Update the token in the Livewire payload and in the window
                        window.Laravel.csrfToken = token;

                        // Retry the original request
                        retry();
                    } else {
                       location.reload(true);
                    }
                } catch (e) {
                    location.reload(true);
                }
            }

            if (status === 408) {
                  location.reload(true);
            }

            if (status === 401) {
                preventDefault();
                window.location.href = '/';
            }
        });
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const logoutBtn = document.getElementById("logoutBtn");
    if (logoutBtn) {
        logoutBtn.addEventListener("click", function () {
            Swal.fire({
                title: "Are you sure?",
                text: "You will be logged out of your account.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, log me out"
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById("logoutForm").submit();
                }
            });
        });
    }
});
