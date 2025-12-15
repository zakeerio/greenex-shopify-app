<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShopSetting;
use Illuminate\Support\Facades\Hash;

class ShopSettingsController extends Controller
{

    /**
     * ✅ View Settings Page **
     */
    public function index(Request $request)
    {
        $shopId = auth()->user()->id ?? null;
        $settings = ShopSetting::where('user_id', $shopId)->first();

        return view('settings', compact('settings'));
    }


    /** ✅ Authenticate Account **/
     public function authenticate(Request $request)
    {   dd('REQUEST HIT', $request->all());
        $request->validate([
            'email'     => 'required|email',
            'api_token' => 'required'
        ]);

        $shop = auth()->user()->shop_domain ?? null;
        $user_id = auth()->user()->id ?? null;

        $setting = ShopSetting::updateOrCreate(
            ['shop_domain' => $shop,
             'user_id'     => $user_id],
            [
                'email'          => $request->email,
                'user_name'      => $request->name,
                'phone'          => $request->phone,
                'user_type'      => $request->user_type,
                'hub_id'         => $request->hub_id,
                'merchant_id'    => $request->merchant_id,
                'wallet_balance' => $request->wallet_balance ?? 0,
                'api_token'      => $request->api_token,
                'api_response'   => $request->api_response,
            ]
        );

        return response()->json([
            'status'  => true,
            'message' => 'Authenticated & Saved Successfully',
            'data'    => $setting
        ]);
    }

    /** ✅ Save Settings **/
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'api_token' => 'required|string',
            'portal_user_id' => 'required|integer', // Shopify user id
        ]);

        // $shop = $request->shop_domain;
        // dd('REQUEST HIT', $request->all(), auth()->user());
        $shop = auth()->user()->name ?? null;
        $userId = auth()->user()->id ?? null;

        $data = ShopSetting::updateOrCreate(
            [
                'shop_domain' => $shop,
                'user_id' => $userId
            ],
            [
                'email' => $request->email,
                'user_name' => $request->name ?? null,
                'phone' => $request->phone ?? null,
                'user_type' => $request->user_type ?? null,
                'hub_id' => $request->hub_id ?? null,
                'portal_user_id' => $request->portal_user_id ?? null,
                'merchant_id' => $request->merchant_id ?? null,
                'wallet_balance' => $request->wallet_balance ?? 0,
                'api_token' => $request->api_token ?? null,
                'api_response' => $request->api_response ?? null,
                'fulfillment_location' => $request->fulfillment_location ?? null,
                'fragile' => $request->fragile === 'Yes' ? true : false,
                'insurance' => $request->insurance === 'Yes' ? true : false,
                'account_type' => $request->account_type ?? 'live',
                'auto_push_cms' => $request->auto_push_cms === 'Yes' ? true : false,
                'price' => $request->price ?? 0,
            ]
        );

        return response()->json([
            'status' => true,
            'message' => 'Settings Saved Successfully',
            'data' => $data
        ]);
    }

    /** ✅ Update Settings **/
    public function updatesetting(Request $request)
    {
        // $shop = $request->shop_domain;
        // dd('REQUEST HIT', $request->all(), auth()->user());
        $shop = auth()->user()->name ?? null;
        $userId = auth()->user()->id ?? null;

        $data = ShopSetting::updateOrCreate(
            [
                'shop_domain' => $shop,
                'user_id' => $userId
            ],
            [
                'fulfillment_location' => $request->fulfillment_location ?? null,
                'fragile' => $request->fragile === 'Yes' ? true : false,
                'insurance' => $request->insurance === 'Yes' ? true : false,
                'account_type' => $request->account_type ?? 'live',
                'auto_push_cms' => $request->auto_push_cms === 'Yes' ? true : false,
                'price' => $request->price ?? 0,
            ]
        );

        return response()->json([
            'status' => true,
            'message' => 'Settings Saved Successfully',
            'data' => $data
        ]);
    }

}
