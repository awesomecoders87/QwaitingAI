<div>
<style>
    .public-link-model {
      z-index:999999 !important;
    }
</style>
    @if($showModal)
    <!-- 🔹 Modal Popup -->
    <div id="linksModal" class="modal public-link-model">
        <div class="modal-content">
            <h2>🚀 {{ __('text.Public Links Dashboard') }}</h2>

            <h3>📌 {{ __('text.Registration') }}</h3>
            <div class="link-card">
                <span><strong>{{ __('text.Web/Kiosk') }}:</strong></span>
                 @if($isBookingEnabled == 1)
                <a id="reg-web" target="_blank" href="{{ $queueGenerateUrl }}">{{ $queueGenerateUrl }}</a>
                @else
                 <a id="reg-web" target="_blank" href="{{ $queueUrl }}">{{ $queueUrl }}</a>
                @endif
                <div class="button-group">
                    <button class="copy-btn" onclick="copyToClipboard('reg-web')">{{ __('text.Copy') }}</button>
                    <button class="qr-btn" onclick="downloadQRCode('reg-web')">{{ __('text.Qr Code') }}</button>
                </div>
            </div>
            <div class="link-card">
                <span><strong>{{ __('text.Mobile') }}:</strong></span>
                <a id="reg-mobile" target="_blank" href="{{ $mobileQueueUrl }}">{{ $mobileQueueUrl }}</a>
                <div class="button-group">
                    <button class="copy-btn" onclick="copyToClipboard('reg-mobile')">{{ __('text.Copy') }}</button>
                    <button class="qr-btn" onclick="downloadQRCode('reg-mobile')">{{ __('text.Qr Code') }}</button>
                </div>
            </div>

            <h3>🕒 {{ __('text.Display') }}</h3>
            <div class="link-card">
                <span><strong>{{ __('text.Web') }}:</strong></span>
                <a id="display-web" target="_blank" href="{{ $displayUrl }}">{{ $displayUrl }}</a>
                <div class="button-group">
                    <button class="copy-btn" onclick="copyToClipboard('display-web')">{{ __('text.Copy') }}</button>
                </div>
            </div>

            @if($isBookingEnabled == 1)
            <h3>📅 {{ __('text.Bookings Only Registration') }}</h3>
            <div class="link-card">
                <span><strong>{{ __('text.Web') }}:</strong></span>
                <a id="bookings-web" target="_blank" href="{{ $bookingUrl }}">{{ $bookingUrl }}</a>
                <div class="button-group">
                    <button class="copy-btn" onclick="copyToClipboard('bookings-web')">{{ __('text.Copy') }}</button>
                    <button class="qr-btn" onclick="downloadQRCode('bookings-web')">{{ __('text.Qr Code') }}</button>
                </div>
            </div>
            @endif

            <button wire:click="closeModal" class="close-btn" onclick="">{{ __('text.Close') }}</button>
        </div>
    </div>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');


        .view-links-btn:hover {
            background: #4a0ea4;
            color: white;
            transform: scale(1.05);
        }

        .link-card:hover {
            transform: scale(1.03);
            background: rgba(255, 255, 255, 0.3);
            transition: 0.3s;
        }

        .copy-btn:hover,
        .qr-btn:hover {
            background: #4a0ea4;
            color: white;
            transform: scale(1.05);
        }

        .close-btn:hover {
            background: darkred;
            transform: scale(1.05);
        }


        .view-links-btn {
            background: white;
            color: #6a11cb;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s;
        }

        .view-links-btn:hover {
            background: #ddd;
        }

        /* Modal Styles */
        .modal {
            display: flex;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {

            background: #5B69EC;
            backdrop-filter: blur(10px);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.3);
            text-align: center;
            width: 90%;
            max-width: 600px;
            color: white;
        }

        .close-btn {
            background: red;
            color: white;
            border: none;
            padding: 8px 14px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            margin-top: 10px;
        }

        .link-card {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(255, 255, 255, 0.2);
            padding: 12px;
            margin: 10px 0;
            border-radius: 8px;
            transition: transform 0.3s ease;
        }

        .link-card a {
            text-decoration: none;
            color: white;
            font-weight: bold;
            flex-grow: 1;
            text-align: left;
            padding: 0 10px;
            overflow-wrap: break-word;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .button-group {
            display: flex;
            gap: 10px;
        }

        .copy-btn,
        .qr-btn {
            background: white;
            color: #6a11cb;
            border: none;
            padding: 8px 14px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s;
        }

        .copy-btn:hover,
        .qr-btn:hover {
            background: #ddd;
        }

        .link-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            background: rgba(255, 255, 255, 0.2);
            padding: 10px;
            border-radius: 8px;
            white-space: nowrap;
            /* Prevents text from wrapping */
            overflow: hidden;
            text-overflow: ellipsis;
            /* Adds "..." if text overflows */
        }

        .link-text {
            flex-grow: 1;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .button-container {
            display: flex;
            gap: 8px;
        }

        button {
            white-space: nowrap;
            flex-shrink: 0;
        }
    </style>

    @push('scripts')
    <script>
        function openPublicLinksModal() {
            document.getElementById("linksModal").style.display = "flex";
        }

        function closeModal() {
            document.getElementById("linksModal").style.display = "none";
        }

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
            z-index: 9999;
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
    </script>
    @endpush
    @endif
</div>