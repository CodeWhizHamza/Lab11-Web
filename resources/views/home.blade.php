<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home-GeoCityLite</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <!-- Make sure you put this AFTER Leaflet's CSS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

</head>

<body>
    <div class="container mx-auto">
        <div class="flex justify-center">
            <div class="w-1/2">
                <h1 class="text-3xl font-bold text-center">GeoCityLite</h1>
                <p class="text-center">A simple web application to get the 5 closest cities based on the City Name or Latitude and Longitude given.</p>
                <form id="city-form">
                    @csrf
                    <div class="flex flex-col mt-4 justify-center">
                        <div class="flex justify-center">
                            <label for="city" class="w-1/4">City Name:</label>
                            <input type="text" name="city" id="city" class="w-3/4 px-2 py-1 border border-gray-400 rounded">
                        </div>
                        <hr class="my-4">
                        <button type="submit" id="ajaxSubmit" class="px-4 py-2 bg-blue-500 text-white rounded">Search</button>
                    </div>
                </form>

                <hr class="my-4">

                <!-- Cities Dropdown Selection -->
                <div class="flex justify-center">
                    <label for="city-select" class="w-1/4">Select City:</label>
                    <select name="city-select" id="city-select" class="w-3/4 px-2 py-1 border border-gray-400 rounded">
                        @foreach ($all_cities as $city)
                        <option value="{{ $city->city }}">{{ $city->city }}</option>
                        @endforeach
                    </select>
                </div>

                <hr class="my-4">

                <div id="map" style="height: 400px;">
                </div>


                <div class="mt-4" id="cities">
                    <h2 class="text-2xl font-bold text-center">5 Closest Cities</h2>
                    <table class="w-full mt-4">
                        <thead>
                            <tr>
                                <th class="border border-gray-400 px-4 py-2">City</th>
                                <th class="border border-gray-400 px-4 py-2">Country</th>
                                <th class="border border-gray-400 px-4 py-2">Distance (KM)</th>
                            </tr>
                        </thead>
                        <tbody id="table-body">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- scripts -->
        <script>
            var map = L.map('map').setView([30.37, 69.34], 6);

            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            var popup = L.popup();

            $(document).ready(function() {
                $('#ajaxSubmit').click(function(e) {
                    e.preventDefault();
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: 'POST',
                        url: '/get-cities',
                        data: {
                            city: $('#city').val(),
                        },
                        success: function(data) {
                            if (data.status === true) {
                                var city = data.city;

                                map.remove();
                                map = L.map('map').setView([city.latitude, city.longitude], 6);
                                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                                }).addTo(map);
                                map.on('click', onMapClick);

                                var cities = data.cities;
                                table = document.getElementById('table-body');
                                table.innerHTML = '';

                                for (var i = 0; i < cities.length; i++) {
                                    if (cities[i].city.city === city.city) {
                                        map.setView([cities[i].city.latitude, cities[i].city.longitude], 6);
                                        popup
                                            .setLatLng([cities[i].city.latitude, cities[i].city.longitude])
                                            .setContent(city.city + "  " + [cities[i].city.latitude, cities[i].city.longitude].toString())
                                            .openOn(map);
                                        continue;
                                    }

                                    let row = table.insertRow(-1);
                                    let cell1 = row.insertCell(0);
                                    let cell2 = row.insertCell(1);
                                    let cell3 = row.insertCell(2);
                                    cell1.innerHTML = cities[i].city.city;
                                    cell2.innerHTML = cities[i].city.country;
                                    cell3.innerHTML = cities[i].distance;

                                    var marker = L.marker([cities[i].city.latitude, cities[i].city.longitude]).addTo(map);

                                }
                            } else {
                                alert('No cities found!');
                            }
                        }
                    });
                });
            });

            function onMapClick(e) {
                popup
                    .setLatLng(e.latlng)
                    .setContent("You clicked the map at " + e.latlng.toString())
                    .openOn(map);

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: 'POST',
                    url: '/get-cities-from-map',
                    data: {
                        lat: e.latlng.lat,
                        lon: e.latlng.lng,
                    },
                    success: function(data) {
                        if (data.status === true) {
                            var city = data.city;


                            map.remove();
                            map = L.map('map').setView([city.latitude, city.longitude], 6);
                            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                            }).addTo(map);
                            map.on('click', onMapClick);

                            var cities = data.cities;
                            table = document.getElementById('table-body');
                            table.innerHTML = '';



                            for (var i = 0; i < cities.length; i++) {
                                if (cities[i].city.city === city.city) {
                                    map.setView([cities[i].city.latitude, cities[i].city.longitude], 6);
                                    popup
                                        .setLatLng([cities[i].city.latitude, cities[i].city.longitude])
                                        .setContent(city.city + "  " + [cities[i].city.latitude, cities[i].city.longitude].toString())
                                        .openOn(map);
                                    continue;
                                }

                                let row = table.insertRow(-1);
                                let cell1 = row.insertCell(0);
                                let cell2 = row.insertCell(1);
                                let cell3 = row.insertCell(2);
                                cell1.innerHTML = cities[i].city.city;
                                cell2.innerHTML = cities[i].city.country;
                                cell3.innerHTML = cities[i].distance;

                                var marker = L.marker([cities[i].city.latitude, cities[i].city.longitude]).addTo(map);

                            }
                        } else {
                            alert('No cities found!');
                        }
                    }
                });

            }

            map.on('click', onMapClick);

            var city_selected = '';

            var select_element = document.getElementById("city-select");
            var value = select_element.value;
            var text = select_element.options[select_element.selectedIndex].text;
            city_selected = text;

            select_element.addEventListener('change', function() {
                value = select_element.value;
                text = select_element.options[select_element.selectedIndex].text;
                city_selected = text;
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: 'POST',
                    url: '/get-cities-through-select',
                    data: {
                        city: city_selected,
                    },
                    success: function(data) {
                        if (data.status === true) {
                            var city = data.city;


                            map.remove();
                            map = L.map('map').setView([city.latitude, city.longitude], 6);
                            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                            }).addTo(map);
                            map.on('click', onMapClick);

                            var cities = data.cities;
                            table = document.getElementById('table-body');
                            table.innerHTML = '';



                            for (var i = 0; i < cities.length; i++) {
                                if (cities[i].city.city === city.city) {
                                    map.setView([cities[i].city.latitude, cities[i].city.longitude], 6);
                                    popup
                                        .setLatLng([cities[i].city.latitude, cities[i].city.longitude])
                                        .setContent(city.city + "  " + [cities[i].city.latitude, cities[i].city.longitude].toString())
                                        .openOn(map);
                                    continue;
                                }

                                let row = table.insertRow(-1);
                                let cell1 = row.insertCell(0);
                                let cell2 = row.insertCell(1);
                                let cell3 = row.insertCell(2);
                                cell1.innerHTML = cities[i].city.city;
                                cell2.innerHTML = cities[i].city.country;
                                cell3.innerHTML = cities[i].distance;

                                var marker = L.marker([cities[i].city.latitude, cities[i].city.longitude]).addTo(map);

                            }
                        } else {
                            alert('No cities found!');
                        }
                    }
                });
            });
        </script>

</body>

</html>