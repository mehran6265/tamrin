<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'cost',
        'education_title',
        'description',
        'requirements',
        'conditions',
        'is_available',
        'is_public',
        'driving_licence',
        'admin_id',
        'client_id',
        'department_id',
    ];


    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
    
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function address()
    {
        return $this->morphOne(Address::class, 'addressable');
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }
}
