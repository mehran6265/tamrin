<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'first_name',
        'last_name',
        'phone',
        'mobile',
        'education_title',
        'kvk_number',
        'btw_number',
        'payrate',
        'role',
        'date_of_birth',
        'gender',
        'url',
        'user_id',
        'profile_url',
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
