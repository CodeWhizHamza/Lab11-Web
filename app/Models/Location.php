<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Location extends Model
{
    
    protected $connection = "mongodb";

    protected $collection = 'newcities';

    protected $fillable = [
        "locId",
        "country",
        "region",
        "city",
        "latitude",
        "longitude",
        "loc"
    ];
    protected $casts = [
        'longitude' => 'float',
        'latitude' => 'float',
    ];

}
