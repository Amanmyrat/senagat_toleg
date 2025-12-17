<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BalanceOrder extends Model
{
    use HasFactory;

    protected $table = 'balance_orders';

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'bank_id',
        'amount',
        'phone',
        'order_id',
        'pay_id',
        'status',
        'error_code',
        'error_message',
        'return_url',
        'client_ip',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'status' => 'string',
        'user_id' => 'integer',
        'bank_id' => 'integer',
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
