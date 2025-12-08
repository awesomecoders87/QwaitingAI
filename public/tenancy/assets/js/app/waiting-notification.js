document.addEventListener('livewire:init', () => {

    Livewire.on('waiting-notification', (event) => {
        showNotificationOfwaiting('Waiting Notification', event[0].token_notify);
        console.log(event);

        });

});


function showNotificationOfwaiting(title, body) {
    // Check if the browser supports notifications
    if (!("Notification" in window)) {
        alert("This browser does not support desktop notifications.");
        return;
    }

    // Request permission if not already granted
    if (Notification.permission === "default") {
        Notification.requestPermission().then(function(permission) {
            if (permission !== "granted") {
                // alert("Notification permission denied.");
                return;
            }
            createNotificationOfWaiting(title, body);
        });
    } else if (Notification.permission === "granted") {
        createNotificationOfWaiting(title, body);
    }
}

// Function to create and show a notification
function createNotificationOfWaiting(title, body) {
    const options = {
        body: body,
        icon: "https://via.placeholder.com/150"
    };

    try {
        const notification = new Notification(title, options);

        // Action when the notification is clicked
        // notification.onclick = function() {
        //     window.open("https://www.example.com");
        // };
    } catch (error) {
        console.error("Notification error:", error);
    }
}
