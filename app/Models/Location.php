<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Location extends Model
{
    
    protected $connection = "mongodb";

    protected $collection = 'location';

    protected $fillable = [
        "locId",
        "country",
        "region",
        "city",
        "latitude",
        "longitude",
    ];

    protected $casts = [
        "locId" => "integer",
        "region" => "integer",
        "latitude" => "float",
        "longitude" => "float",
    ];
}
