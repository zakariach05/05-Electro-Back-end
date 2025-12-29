<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_address',
        'customer_city',
        'total_amount',
        'status',
        'payment_method',
        'acompte_paid',
        'assigned_agent',
        'payment_status',
        'order_notes',
        'refunded_amount',
        'secure_token',
        'email_sent'
    ];

    protected $casts = [
        'acompte_paid' => 'boolean',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function subOrders()
    {
        return $this->hasMany(SubOrder::class);
    }
}
