<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'time_from',
        'time_to',
        'start_date',
        'department_title',
        'cost',
        'education_title',
        'description',
        'requirements',
        'conditions',
        'client_id',
        'department_id',
        'employee_id',
        'extra_description',
    ];

    public function users()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}
