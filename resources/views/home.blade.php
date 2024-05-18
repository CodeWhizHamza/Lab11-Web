<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home-GeoCityLite</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            content: ["./*.html"],
            theme: {
                extend: {},
            },
            darkMode: "class",
        };
    </script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <!-- Make sure you put this AFTER Leaflet's CSS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <style>
        .dark .leaflet-layer,
        .dark .leaflet-control-zoom-in,
        .dark .leaflet-control-zoom-out,
        .dark .leaflet-control-attribution {
            filter: invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%);
        }
    </style>

</head>

<body style='min-width: 100vw; min-height: 100vh;' class="bg-gradient-to-tr from-[#2E3192] to-[#1bffff]">
    <div style='min-width: 100vw; min-height: 100vh;' class="container grid place-items-center">

        <div class='bg-white text-bg-slate-600 dark:bg-slate-600 dark:text-white mx-auto p-4 rounded-lg shadow-lg'
            style='max-height: fit-content;'>
            <h1 class="text-4xl font-bold text-center">GeoCityLite</h1>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                <div class="col-span-2">
                    <div class="flex flex-col justify-center mb-4">
                        <label for="city-select"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Choose a city</label>
                        <select id="city-select"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            @php
                                $all_cities = $all_cities->sortBy('city');
                            @endphp
                            @foreach ($all_cities as $city)
                                <option value="{{ $city->city }}">{{ $city->city }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="map" style="height: 60vh;"></div>
                </div>
                <div class="mt-4" id="cities">
                    <h2 class="text-2xl font-bold text-center mb-4">5 Closest Cities</h2>
                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                            <thead
                                class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">
                                        City
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Country
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Distance (KM)
                                    </th>
                                </tr>
                            </thead>
                            <tbody id='table-body'></tbody>
                        </table>
                    </div>
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
                                        map.setView([cities[i].city.latitude, cities[i].city
                                            .longitude
                                        ], 6);
                                        popup
                                            .setLatLng([cities[i].city.latitude, cities[i].city
                                                .longitude
                                            ])
                                            .setContent(city.city + "  " + [cities[i].city.latitude,
                                                cities[i].city.longitude
                                            ].toString())
                                            .openOn(map);
                                        continue;
                                    }
                                    let row = `
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                            ${cities[i].city.city}
                                        </th>
                                        <td class="px-6 py-4">
                                            ${cities[i].city.country}
                                        </td>
                                        <td class="px-6 py-4">
                                            ${cities[i].distance}
                                        </td>
                                    </tr>`;
                                    table.innerHTML += row;

                                    var marker = L.marker([cities[i].city.latitude, cities[i].city
                                        .longitude
                                    ]).addTo(map);

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
                                        .setContent(city.city + "  " + [cities[i].city.latitude, cities[i].city
                                            .longitude
                                        ].toString())
                                        .openOn(map);
                                    continue;
                                }

                                let row = `
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        ${cities[i].city.city}
                                    </th>
                                    <td class="px-6 py-4">
                                        ${cities[i].city.country}
                                    </td>
                                    <td class="px-6 py-4">
                                        ${cities[i].distance}
                                    </td>
                                </tr>`;
                                table.innerHTML += row;

                                var marker = L.marker([cities[i].city.latitude, cities[i].city.longitude]).addTo(
                                    map);

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
                                        .setContent(city.city + "  " + [cities[i].city.latitude, cities[i]
                                            .city.longitude
                                        ].toString())
                                        .openOn(map);
                                    continue;
                                }

                                let row = `
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        <th scope="row" class="px-2 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                            ${cities[i].city.city}
                                        </th>
                                        <td class="px-2 py-4">
                                            ${cities[i].city.country}
                                        </td>
                                        <td class="px-2 py-4">
                                            ${Math.round(cities[i].distance * 100) / 100}
                                        </td>
                                    </tr>`;
                                table.innerHTML += row;

                                var marker = L.marker([cities[i].city.latitude, cities[i].city.longitude])
                                    .addTo(map);

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
