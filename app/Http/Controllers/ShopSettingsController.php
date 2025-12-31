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

        $shopId = Auth::id();
        $settings = ShopSetting::where('user_id', $shopId)->first();


        // If not authenticated (no settings/token), redirect to authenticate page
        if (!$settings || empty($settings->api_token)) {
            return redirect()->route('authentication');
        }

        $settings = $settings ?? [];

        return view('settings', compact('settings'));
    }

    /**
     * ✅ Show Authentication Form
     */
    public function showAuthenticationForm()
    {
        $shopId = Auth::id();
        $settings = ShopSetting::where('user_id', $shopId)->first();

        // If already authenticated, redirect to settings/dashboard
        if ($settings && !empty($settings->api_token)) {
            return redirect()->route('settings');
        }

        return view('authentication');
    }


    /** ✅ Authenticate Account **/
    public function authenticateAndSave(Request $request)
    {
        $authMethod = $request->input('auth_method', 'email'); // Default to email for backward partial compatibility if needed

        if ($authMethod === 'apikey') {
            $request->validate([
                'apikey' => 'required',
            ]);
        } else {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);
        }

        $userId = Auth::id();
        $authuserd = Auth::id();

        if (!$userId) {
            return back()->with('error', 'User not authenticated.');
        }

        $backendApiUrl = config('app.backend_api_url');
        $backendApiKey = config('app.backend_api_key');
        $backendUrl = config('app.backend_url');

        try {
            $client = new Client(['verify' => false]);
            $response = null;

            if ($authMethod === 'apikey') {
                $token = $request->apikey;

                $response = $client->request('GET', $backendUrl . '/user', [
                    'headers' => [
                        'apiKey' => $backendApiKey,
                        'Accept' => 'application/json',
                        'Authorization' => "Bearer $token",
                    ]
                ]);

                $checkResponse = json_decode($response->getBody()->getContents(), true);

                if (!isset($checkResponse)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Invalid API Key or API response structure'
                    ], 422);
                    //throw new \Exception("Invalid API Key or API response structure");
                }

                $data = $checkResponse;

                $user = [
                    'hub' => ['name' => 'default'], // Not in response, use default
                    'email' => $data['email'] ?? null,
                    'name' => $data['name'] ?? 'API User',
                    'phone' => $data['mobile'] ?? null,
                    'user_type' => $data['user_type'] ?? null,
                    'hub_id' => $data['hub_id'] ?? null,
                    'id' => $data['id'] ?? null, // portal_user_id
                    'merchant' => [], // Merchant details not in this response?
                ];

                // dd($user);
            } else {
                // Email/Pass Flow (Original)
                $response = $client->post($backendApiUrl . '/signin', [
                    'headers' => [
                        'Accept' => 'application/json',
                        'apiKey' => $backendApiKey,
                    ],
                    'form_params' => [
                        'email' => $request->email,
                        'password' => $request->password,
                        'api_key' => '123',
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
                $checkResponse = $apiResponse['data']; // consistent variable for api_response column
            }


            $shopDomain = Auth::user()->name ?? 'default';
            $userId = Auth::id();

            if (!$userId) {
                return response()->json(['status' => false, 'message' => 'User not authenticated'], 401);
            }

            /** 🔹 STEP 2: Save / Update Shop Settings */
            $shopSetting = ShopSetting::updateOrCreate(
                [
                    'user_id' => $userId
                ],
                [
                    'shop_domain'           => $authuser['name'] ?? 'default',
                    'email'                 => $request->email ?? ($user['email'] ?? null),
                    'user_name'             => $user['name'] ?? null,
                    'phone'                 => $user['phone'] ?? null,
                    'user_type'             => $user['user_type'] ?? null,
                    'hub_id'                => $user['hub_id'] ?? null,
                    'portal_user_id'        => $user['id'] ?? ($user['portal_user_id'] ?? null),
                    'merchant_id'           => $user['merchant']['id'] ?? null,
                    'wallet_balance'        => $user['merchant']['wallet_balance'] ?? 0,
                    'api_token'             => $token,
                    'api_response'          => $checkResponse ?? [],

                ]
            );

            return response()->json([
                'status' => true,
                'message' => 'Account authenticated & settings saved',
                'data' => $shopSetting
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    /** ✅ Save/Update Settings **/
    public function store(Request $request)
    {
        $userId = Auth::id();
        if (!$userId) return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);

        $shop = Auth::user()->name ?? 'unknown';

        $data = ShopSetting::updateOrCreate(
            ['user_id' => $userId],
            [
                'shop_domain' => $shop,
                // Only update fields passed in request to avoid overwriting with nulls if validation allows
                'fulfillment_location' => $request->fulfillment_location,
                'fragile' => $request->fragile === 'Yes',
                'insurance' => $request->insurance === 'Yes',
                'account_type' => $request->account_type ?? 'live',
                'auto_push_cms' => $request->auto_push_cms === 'Yes',
                'price' => $request->price ?? 0,

                // Allow updating email/token if passed, though usually done via authenticate
                'email' => $request->email ?? ShopSetting::where('user_id', $userId)->value('email'),
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
        return $this->store($request);
    }
}
