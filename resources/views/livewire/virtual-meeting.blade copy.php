<div class="p-6 bg-white shadow rounded">

        <h2 class="text-2xl font-bold mb-4 text-gray-800">Meeting</h2>

    <!-- Video Grid -->
    <div id="video-container" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 min-h-[300px]">
        <!-- Remote videos will be injected here -->
    </div>

     <!-- Controls -->
        <div class="mt-4 flex justify-center gap-2">
            <!-- Mic Toggle -->
            <button id="audio-btn"
        onclick="toggleAudio()"
        class="audio-control bg-gray-800 text-white p-2 rounded hover:bg-blue-600 transition"
        title="Toggle Microphone">
        <span id="audio-icon">
            <!-- Mic On Icon -->
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 1v10m0 0a4 4 0 004-4V5a4 4 0 00-8 0v2a4 4 0 004 4zm0 0v4m0 0H8m4 0h4" />
            </svg>
        </span>
    </button>

    <!-- Video Button -->
    <button id="video-btn"
        onclick="toggleVideo()"
        class="video-control bg-gray-800 text-white p-2 rounded hover:bg-green-600 transition"
        title="Toggle Video">
        <span id="video-icon">
            <!-- Video On Icon -->
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M4 6h8a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2z" />
            </svg>
        </span>
    </button>

    <!-- Leave Button -->
    <button onclick="leaveRoom()"
        class="bg-red-600 text-white p-2 rounded hover:bg-red-700 transition"
        title="Leave Meeting">
        <!-- Leave Icon -->
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1" />
        </svg>
    </button>
        </div>

    @push('scripts')
    <script src="https://sdk.twilio.com/js/video/releases/2.31.0/twilio-video.min.js"></script>

    <script>
        const token = @json($token);
        const roomName = @json($room);

        let room;
        let localAudioTrack;
        let localVideoTrack;
        let isAudioEnabled = true;
        let isVideoEnabled = true;

        document.addEventListener("DOMContentLoaded", (event) => {
            runVirtualMeeting()
        });

        function runVirtualMeeting() {

            Twilio.Video.createLocalTracks({
                audio: true,
                video: {
                    width: 640
                }
            }).then(localTracks => {
                localAudioTrack = localTracks.find(t => t.kind === 'audio');
                localVideoTrack = localTracks.find(t => t.kind === 'video');

                return Twilio.Video.connect(token, {
                    name: roomName,
                    tracks: localTracks
                });
            }).then(_room => {
                room = _room;
                const container = document.getElementById('video-container');

               if (!document.getElementById('local-participant')) {
                   const localDiv = document.createElement('div');
                    localDiv.id = 'local-participant';
                    localDiv.className = `
                        fixed bottom-4 right-4 z-50
                        bg-black rounded-lg border-2 border-white
                        overflow-hidden shadow-lg
                        w-28 h-20 sm:w-36 sm:h-24
                    `.trim();

                    localDiv.innerHTML = `
                        <p class="absolute bottom-1 left-1 text-white text-xs 
                        bg-black bg-opacity-70 px-1 py-0.5 rounded z-20">You</p>
                    `;

                    const videoElement = localVideoTrack.attach();
                    videoElement.style.width = '100%';
                    videoElement.style.height = '100%';
                    videoElement.style.objectFit = 'cover';

                    localDiv.appendChild(videoElement);
                    container.appendChild(localDiv);
                }

                // Render remote participants only
                room.participants.forEach(participant => {
                    if (participant.identity !== room.localParticipant.identity) {
                        handleParticipant(participant);
                    }
                });

                room.on('participantConnected', participant => {
                    if (participant.identity !== room.localParticipant.identity) {
                        handleParticipant(participant);
                    }
                });

                room.on('participantDisconnected', participant => {
                    document.getElementById(participant.sid)?.remove();
                });

            }).catch(error => {
                console.error('Connection failed', error);
                alert('Error: ' + error.message);
            });
        }

        function handleParticipant(participant) {
            const container = document.getElementById('video-container');
            const div = document.createElement('div');
            div.id = participant.sid;
            div.className = "bg-black rounded relative overflow-hidden";
            div.innerHTML = `<p class="absolute bottom-2 left-2 text-white text-sm bg-black bg-opacity-50 px-2 py-1 rounded">${participant.identity}</p>`;
            container.appendChild(div);

            // Only attach already subscribed tracks once
            participant.tracks.forEach(publication => {
                if (publication.isSubscribed && publication.track) {
                    div.appendChild(publication.track.attach());
                }

                // Listen for future subscriptions
                publication.on('subscribed', track => {
                    div.appendChild(track.attach());
                });
            });

            // You can safely remove this duplicate listener (already handled above):
            // participant.on('trackSubscribed', track => {
            //     div.appendChild(track.attach());
            // });

            participant.on('trackUnsubscribed', track => {
                track.detach().forEach(el => el.remove());
            });
        }

       function toggleAudio() {
            if (!localAudioTrack) return;
            isAudioEnabled = !isAudioEnabled;
            localAudioTrack.enable(isAudioEnabled);
            const audioIcon = document.getElementById('audio-icon');
            const audioBtn = document.getElementById('audio-btn');
            
            audioIcon.innerHTML = isAudioEnabled
                ? `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 1v10m0 0a4 4 0 004-4V5a4 4 0 00-8 0v2a4 4 0 004 4zm0 0v4m0 0H8m4 0h4" />
                </svg>`
                : `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 9l6 6m0-6l-6 6M12 1a3 3 0 00-3 3v4a3 3 0 006 0V4a3 3 0 00-3-3z" />
                </svg>`;
            
            // Change button color based on state
            audioBtn.className = isAudioEnabled 
                ? "audio-control bg-gray-800 text-white p-2 rounded hover:bg-blue-600 transition"
                : "audio-control bg-red-600 text-white p-2 rounded hover:bg-red-700 transition";
        }
        
        function toggleVideo() {
            if (!localVideoTrack) return;
            isVideoEnabled = !isVideoEnabled;
            localVideoTrack.enable(isVideoEnabled);
            const videoIcon = document.getElementById('video-icon');
            const videoBtn = document.getElementById('video-btn');
            
            videoIcon.innerHTML = isVideoEnabled
                ? `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M4 6h8a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2z" />
                </svg>`
                : `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18 21l-1.414-1.414M5.636 5.636L4 4l1.414 1.414" />
                </svg>`;
            
            // Change button color based on state
            videoBtn.className = isVideoEnabled 
                ? "video-control bg-gray-800 text-white p-2 rounded hover:bg-green-600 transition"
                : "video-control bg-red-600 text-white p-2 rounded hover:bg-red-700 transition";
        }

    function leaveRoom() { 
    if (room) {
        room.disconnect();

        // Remove local participant div
        const localDiv = document.getElementById('local-participant');
        if (localDiv) {
            localDiv.remove();
        }

        // Optional: Remove remote participants too (clean up everything)
        const container = document.getElementById('video-container');
        if (container) {
            container.innerHTML = ''; // clears all video frames if needed
        }

        window.Livewire.dispatch('leave-meeting');

        window.location.href = "{{ $ticketLink }}";
    } 
}


window.addEventListener('beforeunload', function () {
    leaveRoom();
});
    </script>

    <script>

        var pusher = new Pusher("{{ $pusherKey }}", {
        cluster: "{{ $pusherCluster }}",
        encrypted: true
        });

        var queueVirtual = pusher.subscribe("queue-virtual.{{ tenant('id') }}.{{$queueStorage->locations_id}}.{{$queueStorage->id}}");

        queueVirtual.bind('queue-virtual', function(data) {
            window.location.href = "{{ $ticketLink }}";
        });

    </script>
    
   @endpush

</div>