<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShopSetting;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Client;
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

        $settings = $settings ?? [];

        return view('settings', compact('settings'));
    }


    /** ✅ Authenticate Account **/
    public function authenticateAndSave(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'apikey' => 'required',
            'fullfilment' => 'nullable',
            'fragile' => 'nullable',
            'insurance' => 'nullable',
            'account_type' => 'nullable',
            'auto_push_orders' => 'nullable',
            'price' => 'nullable|numeric',
        ]);

        $backendApiUrl = config('app.backend_api_url');
        $backendApiKey = config('app.backend_api_key');

        try {
            $client = new Client([
                'verify' => false // local SSL only
            ]);

            /** 🔹 STEP 1: External API SIGNIN */
            $response = $client->post($backendApiUrl . '/signin', [
                'headers' => [
                    'Accept' => 'application/json',
                    'apiKey' => $backendApiKey,
                ],
                'form_params' => [
                    'email' => $request->email,
                    'password' => $request->password,
                    'api_key' => $request->apikey,
                ]
            ]);

            $apiResponse = json_decode($response->getBody()->getContents(), true);

            if (!isset($apiResponse['data']['token'])) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid API response'
                ], 422);
            }

            $token = $apiResponse['data']['token'];
            $user  = $apiResponse['data']['user'];

            /** 🔹 STEP 2: Save / Update Shop Settings */
            $shopSetting = ShopSetting::updateOrCreate(
                [
                    'user_id' => $request->user_id
                ],
                [
                    'shop_domain'           => $user['hub']['name'] ?? 'default',
                    'email'                 => $request->email,
                    'user_name'             => $user['name'] ?? null,
                    'phone'                 => $user['phone'] ?? null,
                    'user_type'             => $user['user_type'] ?? null,
                    'hub_id'                => $user['hub_id'] ?? null,
                    'merchant_id'           => $user['merchant']['id'] ?? null,
                    'wallet_balance'        => $user['merchant']['wallet_balance'] ?? 0,
                    'api_token'             => $token,
                    'api_response'          => $apiResponse,
                    'fulfillment_location'  => $request->fullfilment,
                    'fragile'               => $request->fragile === 'Yes',
                    'insurance'             => $request->insurance === 'Yes',
                    'account_type'          => $request->account_type ?? 'live',
                    'auto_push_cms'         => $request->auto_push_orders === 'Yes',
                    'price'                 => $request->price ?? 0,
                ]
            );

            return back()->with('success', 'Account authenticated & settings saved');
            exit;
            // return response()->json([
            //     'status' => true,
            //     'message' => 'Account authenticated & settings saved',
            //     'data' => $shopSetting
            // ]);

        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
            // return response()->json([
            //     'status' => false,
            //     'error' => $e->getMessage()
            // ], 500);
        }
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

        $shop = $request->shop_domain ?? null;
        $userId = $request->user_id ?? null;

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
