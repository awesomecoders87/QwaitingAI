<div>
   <script>
     document.addEventListener('livewire:init', () => {
    Livewire.on('swal:checkin-failed-limit-exceed', (data) => {
                    const payload = Array.isArray(data) ? data[0] : data;
                    Swal.fire({
                        title: payload.title || 'Daily limit reached',
                        text: payload.text || 'You have reached today\'s ticket limit.',
                        icon: 'info',
                        iconColor: '#2563eb', // blue icon
                        background: '#f8fafc', // soft background
                        showCloseButton: true,
                        allowOutsideClick: false,
                        allowEscapeKey: true,
                        showConfirmButton: true,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#2563eb', // blue confirm button
                        customClass: {
                            popup: 'rounded-lg border border-indigo-100 shadow-md',
                            title: 'text-indigo-800 font-semibold',
                            content: 'text-slate-700',
                            confirmButton: 'px-6 py-2 rounded-md'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // optional: any follow-up action
                        }
                    });
                });

                Livewire.on('error', (data) => {
                    const payload = Array.isArray(data) ? data[0] : data;
                    Swal.fire({
                        title: 'Error',
                        text: payload.message,
                        icon: 'error',
                        showConfirmButton: true,
                        confirmButtonText: 'OK',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // optional: any follow-up action
                        }
                    });
                });

            });
   </script>
</div>
