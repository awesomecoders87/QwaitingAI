document.addEventListener('livewire:init', () => {
  
    Livewire.on('desktop-notification', (response) => {
        showNotification('Queue Notification', response.token_notify);

        });
  

});


function showNotification(title, body) {
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
            createNotification(title, body);
        });
    } else if (Notification.permission === "granted") {
        createNotification(title, body);
    } 
}

// Function to create and show a notification
function createNotification(title, body) {
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
