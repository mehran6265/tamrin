<?php

namespace App\Models;

use App\Traits\HasPermissionsTrait as TraitsHasPermissionsTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, TraitsHasPermissionsTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'phone',
        'user_type',
        'email',
        'password',
        'client_id',
        'is_activated',
        'is_blocked',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function client()
    {
        return $this->belongsTo(self::class, "client_id");
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function financial()
    {
        return $this->hasOne(Financial::class);
    }

    public function contact()
    {
        return $this->hasOne(Contact::class);
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function address()
    {
        return $this->morphOne(Address::class, 'addressable');
    }

    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class, "employee_id");
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
