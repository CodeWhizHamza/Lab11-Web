<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CityController;
use App\Models\Location;

Route::get('/', function () {
    return view('home', ['all_cities' => Location::limit(1000)->get()]);
})->name('home');

Route::post('/get-cities', [CityController::class, 'get_cities'])->name('get-cities');

Route::post('/get-cities-from-map', [CityController::class, 'get_cities_from_map'])->name('get-cities-from-map');

Route::post('/get-cities-through-select', [CityController::class, 'get_cities_through_select'])->name('get-cities-from-map');