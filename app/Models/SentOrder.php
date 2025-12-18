<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SentOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shop_setting_id',
        'order_id',
        'order_number',
        'portal_reference',
        'tracking_id',
        'portal_order_id',
        'portal_response',
        'collect_payment',
        'status',
        'shopify_updated',
        'fulfillment_id',
        'sent_at',
        'processed_at',
        'portal_sync_at'
    ];

    protected $casts = [
        'portal_response' => 'array',
        'shopify_updated' => 'boolean',
        'sent_at' => 'datetime',
        'processed_at' => 'datetime',
        'portal_sync_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shopSetting()
    {
        return $this->belongsTo(ShopSetting::class);
    }
}
