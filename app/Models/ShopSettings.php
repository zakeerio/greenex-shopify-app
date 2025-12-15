<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopSetting extends Model
{
    protected $fillable = [
        'shop_domain',
        'email',
        'password',
        'apikey',
        'api_secret',
        'fulfillment_location',
        'fragile',
        'insurance',
        'account_type',
        'auto_push_cms',
        'price'
    ];
}
