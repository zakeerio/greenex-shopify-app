<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopifyStore extends Model
{
    protected $fillable = ['user_id', 'shop_domain', 'access_token', 'api_version'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
