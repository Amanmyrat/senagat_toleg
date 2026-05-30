<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Merchant extends Model
{
    protected $fillable = [
        'location_id',
        'username',
        'password',];
//    protected $casts = [
//        'password' => 'encrypted',
//    ];
    protected function password(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value
                ? Crypt::decryptString($value)
                : null,

            set: fn ($value) => $value
                ? Crypt::encryptString($value)
                : null,
        );
    }
}
