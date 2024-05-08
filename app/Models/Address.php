<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable =[
        'address',
        'address_extra',
        'state',
        'city',
        'postcode',
        'country',
        'address_extra',
        'country_code',
        'latitude',
        'longitude',
    ];

    public function addressable()
    {
        return $this->morphTo();
    }
}
