<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'user_information',
        'payment_target',
        'bank_id',
        'amount',
        'order_id',
        'pay_id',
        'status',
        'error_code',
        'error_message',
        'return_url',
        'client_ip',
        'bank_key'
    ];

    protected $casts = [
        'status' => 'string',
        'user_information' => 'array',
        'payment_target'   => 'array',
        'error_code' => 'integer',
    ];

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
