jQuery(document).ready(function () {
    // ==============================================
    // 🧱 Basic Setup
    // ==============================================
    jQuery("body, html").css("height", "100vh");

    // ==============================================
    // 🔊 Unlock Audio on First User Gesture (BrightSign)
    // ==============================================
  let unlocked = false;

async function unlockAudioOnce() {
    if (unlocked) return;
    const audio = document.getElementById("audio");
    if (!audio) {
        console.warn("[DING] No audio element found.");
        // alert("⚠️ Audio element not found on this screen.");
        return;
    }
    try {
        audio.load();
        await audio.play();
        audio.pause();
        unlocked = true;
        console.log("[DING] audio unlocked");
        // alert("✅ Audio unlocked successfully!");
    } catch (e) {
        console.log("[DING] unlock failed, will retry on next gesture:", e);
        // alert("⚠️ Audio unlock failed. Please tap the screen again.");
    }
}
if(unlocked == false){

    unlockAudioOnce();
}
    // ==============================================
    // 🖥️ Fullscreen Toggle Logic
    // ==============================================
    function toggleFullScreen() {
        if (
            !document.fullscreenElement &&
            !document.mozFullScreenElement &&
            !document.webkitFullscreenElement &&
            !document.msFullscreenElement
        ) {
            let elem = document.documentElement;
            if (elem.requestFullscreen) {
                elem.requestFullscreen();
            } else if (elem.msRequestFullscreen) {
                elem.msRequestFullscreen();
            } else if (elem.mozRequestFullScreen) {
                elem.mozRequestFullScreen();
            } else if (elem.webkitRequestFullscreen) {
                elem.webkitRequestFullscreen();
            }
            localStorage.setItem("fullscreen", "true");
            setTimeout(resizeScreen, 500);
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            } else if (document.mozCancelFullScreen) {
                document.mozCancelFullScreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            }
            localStorage.setItem("fullscreen", "false");
            setTimeout(resizeScreen, 500);
        }
    }

    // ==============================================
    // 🎛️ Bind Fullscreen Button + Unlock Audio
    // ==============================================
    const fsBtn = document.getElementById("toggleFullBtn");
    if (fsBtn) {
        fsBtn.addEventListener("click", function () {
            unlockAudioOnce();   // 🔓 Unlock audio
            toggleFullScreen();  // 🖥️ Toggle fullscreen
        });

        fsBtn.addEventListener("touchstart", function () {
            unlockAudioOnce();
            toggleFullScreen();
        });
    } else {
        console.warn("[DING] toggleFullBtn button not found in DOM.");
    }

    // Also allow key press to unlock audio (failsafe)
    document.addEventListener("keydown", unlockAudioOnce);

    // Re-enter fullscreen if it was active before reload
    if (localStorage.getItem("fullscreen") === "true") {
        toggleFullScreen();
    }

    // ==============================================
    // 🦉 Owl Carousel Initialization
    // ==============================================
    const owl = $("#owl-slider-display");
    if (owl.length) {
        owl.owlCarousel({
            items: 1,
            loop: true,
            margin: 0,
            dots: false,
            nav: false,
            autoplay: true,
            autoplayTimeout: 4000,
        });
    }

    // ==============================================
    // 📏 Resize Screen Handler
    // ==============================================
    function resizeScreen() {
        const winHeight = $(window).height();
        $("#main").css("height", winHeight);

        const headerHeight = $(".main-header-display").outerHeight() || 0;
        const footerHeight = $("#footer").outerHeight() || 0;

        $(".main-header-display").css("height", headerHeight);
        $("#footer").css("height", footerHeight);
        $(".table-display-inside").css("height", winHeight - headerHeight);
        $("#main-display").css(
            "height",
            winHeight - headerHeight - footerHeight - 40 + "px"
        );
    }

    // Initial + Responsive Resize
    resizeScreen();
    $(window).resize(resizeScreen);
});
