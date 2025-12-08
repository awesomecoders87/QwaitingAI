<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BrightSign Audio Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        h1 { color: #333; }
        button { padding: 10px 20px; font-size: 16px; margin-top: 20px; cursor: pointer; }
        #log { margin-top: 20px; padding: 10px; background: #000; color: #fff; height: 200px; overflow-y: auto; font-size: 14px; }
    </style>
</head>
<body>
    <h1>BrightSign Audio Test</h1>

    <button id="playBtn">▶ Play Audio</button>

    <audio id="dingAudio" preload="auto">
        <!-- Fallback for browser -->
        <source src="https://bbjj.qwaiting.com/voice/Ding-noise/Ding-noise.mp3" type="audio/mpeg">
        Your browser does not support the audio element.
    </audio>

    <div id="log"></div>

    <script>
        const logContainer = document.getElementById('log');
        function log(msg, color = '#fff') {
            const div = document.createElement('div');
            div.textContent = msg;
            div.style.color = color;
            logContainer.appendChild(div);
            logContainer.scrollTop = logContainer.scrollHeight;
            console.log(msg);
        }

        // Path to audio (adjust for BrightSign local file)
        const brightSignFile = "/voice/Ding-noise/Ding-noise.mp3";

        let dingSound = null;

        function initDingSound() {
            try {
                if (typeof BS !== "undefined" && BS.sound) {
                    // BrightSign environment
                    dingSound = new BS.sound(brightSignFile);
                    log("[BrightSign] Sound object initialized: " + brightSignFile, 'lightgreen');
                } else {
                    log("[Browser] BS object not found. Using HTML5 audio fallback.", 'yellow');
                }
            } catch (err) {
                log("[Error] Initializing sound: " + err, 'red');
            }
        }

        function playDing() {
            try {
                if (dingSound) {
                    // BrightSign playback
                    dingSound.play();
                    log("[BrightSign] Playing sound via BS.sound", 'lightgreen');
                } else {
                    // HTML5 audio fallback
                    const audio = document.getElementById('dingAudio');
                    if (audio) {
                        audio.currentTime = 0;
                        audio.play().then(() => {
                            log("[Browser] Playing audio via HTML5 element", 'lightblue');
                        }).catch(err => {
                            log("[Error] HTML5 audio failed: " + err, 'red');
                        });
                    } else {
                        log("[Error] Audio element not found", 'red');
                    }
                }
            } catch (err) {
                log("[Error] playDing: " + err, 'red');
            }
        }

        document.getElementById('playBtn').addEventListener('click', () => {
            playDing();
        });

        // Initialize on page load
        window.addEventListener('DOMContentLoaded', () => {

            initDingSound();
        });

        // Optional: simulate BS object for testing in browser
        if (typeof BS === 'undefined') {
            window.BS = {
                sound: function(file) {
                    log("[Simulated BS.sound] Would play: " + file, 'orange');
                    const audio = new Audio(file);
                    audio.play();
                }
            };
        }
    </script>
</body>
</html>
