<div class="p-4">
    <h2 class="text-xl font-semibold mb-4">{{ __('setting.Locations') }} - Map</h2>

    <div class="mb-4 text-sm text-gray-600 dark:text-gray-300">
        {{ __('setting.Locations') }} map view shows all active locations with latitude & longitude.
    </div>

    <div id="locations-map" class="w-full h-[480px] rounded-lg border border-gray-200 shadow bg-white dark:border-gray-800 dark:bg-white/[0.03]"></div>

    <div class="mt-4">
        <h3 class="text-lg font-semibold mb-2">{{ __('setting.Locations') }}</h3>
        <ul class="space-y-1 text-sm text-gray-700 dark:text-gray-200 max-h-40 overflow-y-auto">
            @forelse($locations as $location)
                <li>
                    <span class="font-medium">{{ $location->location_name }}</span>
                    - {{ $location->address }}
                </li>
            @empty
                <li>{{ __('setting.No data found') }}</li>
            @endforelse
        </ul>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const locations = @json($locations);

            if (!locations.length) {
                return;
            }

            const first = locations[0];

            const map = new google.maps.Map(document.getElementById('locations-map'), {
                center: { lat: parseFloat(first.latitude), lng: parseFloat(first.longitude) },
                zoom: 5,
            });

            const bounds = new google.maps.LatLngBounds();

            locations.forEach(loc => {
                if (!loc.latitude || !loc.longitude) return;

                const position = { lat: parseFloat(loc.latitude), lng: parseFloat(loc.longitude) };
                const marker = new google.maps.Marker({
                    position,
                    map,
                    title: loc.location_name,
                });

                const infoWindow = new google.maps.InfoWindow({
                    content: `<div><strong>${loc.location_name}</strong><br>${loc.address ?? ''}<br>${loc.city ?? ''} ${loc.state ?? ''} ${loc.country ?? ''}</div>`
                });

                marker.addListener('click', () => {
                    infoWindow.open(map, marker);
                });

                bounds.extend(position);
            });

            if (locations.length > 1) {
                map.fitBounds(bounds);
            }
        });
    </script>

    @if(!empty($googleMapKey))
        <script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapKey }}"></script>
    @endif
</div>
