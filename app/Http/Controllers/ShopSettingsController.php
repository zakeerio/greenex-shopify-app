<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

// app/Http/Controllers/ShopSettingController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShopSetting;
use Illuminate\Support\Facades\Hash;

class ShopSettingsController extends Controller
{
    /** ✅ Authenticate Account **/
    public function authenticate(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'apikey' => 'required'
        ]);

        // ✅ Example API Test (replace with real API call)
        if ($request->apikey != 'demo-key') {
            return response()->json([
                'status' => false,
                'message' => 'Invalid API Key'
            ], 401);
        }

        return response()->json([
            'status' => true,
            'message' => 'Authenticated Successfully'
        ]);
    }

    /** ✅ Save Settings **/
    public function store(Request $request)
    {
        $shop = auth()->user()->shop_domain; // from Shopify session

        $data = ShopSetting::updateOrCreate(
            ['shop_domain' => $shop],
            [
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'apikey' => $request->apikey,
                'fulfillment_location' => $request->Fullfillment,
                'fragile' => $request->Firgile === 'Yes',
                'insurance' => $request->Insurance === 'Yes',
                'account_type' => $request->accounttype,
                'auto_push_cms' => $request->cms === 'Yes',
                'price' => $request->price,
            ]
        );

        return response()->json([
            'status' => true,
            'message' => 'Settings Saved Successfully',
            'data' => $data
        ]);
    }
}
