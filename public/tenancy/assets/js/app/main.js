document.addEventListener('DOMContentLoaded', function () {

    // Select the third <li> element within the <ul> inside .featured-sidebar-group
    const publicLinks = document.querySelector('.links ul li');

    function copyToClipboard(id) {
        var copyText = document.getElementById(id);
        navigator.clipboard.writeText(copyText.href).then(() => {
            showToast('✅ Link copied!');
        }).catch(() => {
            showToast('❌ Failed to copy!');
        });
    }
    
    function showToast(message) {
        let toast = document.createElement("div");
        toast.textContent = message;
        toast.style.cssText = `
        position: fixed; bottom: 20px; left: 50%;
        transform: translateX(-50%);
        background: rgba(0,0,0,0.8); color: white;
        padding: 10px 20px; border-radius: 6px;
        font-size: 14px; z-index: 10000; opacity: 1;
        transition: opacity 0.5s ease-in-out;
    `;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = "0";
        }, 2000);
        setTimeout(() => {
            toast.remove();
        }, 2500);
    }

    function downloadQRCode(id) {
        var element = document.getElementById(id);
    
        if (!element) {
            alert("Element not found!");
            return;
        }
    
        var url = element.href || element.getAttribute("data-url"); // Get URL from href or data-url attribute
        if (!url) {
            alert("No valid URL found for QR code!");
            return;
        }
    
        var qrCodeUrl = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(url)}`;
    
        // Open QR code in a new tab
        window.open(qrCodeUrl, '_blank');
    }

    // Check if the third <li> element exists
    if (publicLinks) {
        // Add a click event listener to the third <li> element
        publicLinks.addEventListener('click', function (event) {
            event.preventDefault(); // Prevent default action, if any

            // Ensure Livewire is loaded before dispatching the event
            if (window.Livewire) {
                window.Livewire.dispatch('openPublicLinks');
            } else {
                console.error("Livewire is not loaded.");
            }
        });
    } 
    
    window.downloadQRCode = downloadQRCode;

    window.copyToClipboard = copyToClipboard;

});


// Get the current path
const currentPath = window.location.pathname;


// Retrieve the location message from localStorage
const checkLocation = localStorage.getItem('locationMessage');
// alert(checkLocation);

// Simulate session handling using sessionStorage
const hasLocation = checkLocation; // Use the value from localStorage
const redirected = sessionStorage.getItem('redirected');

if (hasLocation && !redirected) {
    // Check if the current URL is NOT /locations or /locations/create
    if (currentPath !== '/locations' && currentPath !== '/locations/create') {
        sessionStorage.setItem('redirected', 'true');
        console.log("test1");
        // Redirect to /locations
        window.location.href = '/locations/create';
    }
} else {
    sessionStorage.removeItem('redirected');
   
    // Uncomment the line below if redirected needs to be set again
    // sessionStorage.setItem('redirected', 'true');
}
