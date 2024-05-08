<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'employee_id'
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
