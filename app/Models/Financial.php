<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Financial extends Model
{
    use HasFactory;

    protected $fillable =[
        'bank_name',
        'account_detail',
        'account_number',
        'iban_number',
        'tax_number',
        'terms_of_payment',
        'iban_holder',
        'user_id',
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
