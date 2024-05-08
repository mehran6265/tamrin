<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cancellation extends Model
{
    use HasFactory;

    protected $fillable = [
        'is_checked',
        'employee_id',
        'assignment_id',
        'text'
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, "employee_id");
    }

    public function assignment()
    {
        return $this->belongsTo(Assignment::class, "assignment_id");
    }
}
