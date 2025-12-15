<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopSetting extends Model
{
    protected $fillable = [
        'user_id',               // Shopify user id
        'shop_domain',           // Unique per shop
        'email',
        'user_name',
        'phone',
        'user_type',
        'hub_id',
        'merchant_id',
        'wallet_balance',
        'api_token',
        'api_response',
        'fulfillment_location',
        'fragile',
        'insurance',
        'account_type',
        'auto_push_cms',
        'portal_user_id',
        'price'
    ];

    protected $casts = [
        'api_response'   => 'array',
        'fragile'        => 'boolean',
        'insurance'      => 'boolean',
        'auto_push_cms'  => 'boolean',
        'wallet_balance' => 'decimal:2',
        'price'          => 'decimal:2'
    ];

    // Relation to Shopify user
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
