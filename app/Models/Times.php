<?php

namespace App\Models;

 use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Times extends Model
{
    
    protected $table = "times";
    protected $guarded = [];

        use HasFactory;

    protected $fillable = [
        'status',
        'type',
        'time_from',
        'time_to',
        'start_date',
        'end_date',
        'payrate',
        'education_title',
        'description',
        'requirements',
        'conditions',
        'client_id',
        'department_id',
        'employee_id',
        'extra_description',
        'invoice',
    ];

    public function client()
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

    public function cancellations()
    {
        return $this->hasMany(Cancellation::class);
    }

 
}