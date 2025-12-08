document.addEventListener('DOMContentLoaded', function() {
    function initAutocomplete() {
        var input = document.getElementById('autocomplete');
        if (!input) return;

        var autocomplete = new google.maps.places.Autocomplete(input);
        autocomplete.addListener('place_changed', function() {
            var place = autocomplete.getPlace();
            document.getElementById('latitude').value = place.geometry.location.lat();
            document.getElementById('longitude').value = place.geometry.location.lng();
        });
    }

    google.maps.event.addDomListener(window, 'load', initAutocomplete);
});