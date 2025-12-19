<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\ShopSetting;
use App\Http\Controllers\Controller;
use GuzzleHttp\Client;

use Osiset\ShopifyApp\Objects\Values\ShopDomain;

class ShopifyController extends Controller
{
    protected $backendApiUrl;
    protected $backendApiKey;
    protected $version;
    //
    // public function fetchOrders(Request $request)
    // {
    //     $version = config('shopify-app.api_version'); // or stored version

    //     $shop = Auth::user();

    //     if (!$shop) {
    //         return response()->json([
    //             'error' => 'Shop not authenticated',
    //         ], 401);
    //     }

    //     try {

    //         // $response = $shop->api()->rest('GET', '/orders.json');
    //         // return $response['body']['orders'];

    //         $response = $shop->api()->rest('GET', "/admin/api/{$version}/orders.json", ['limit' => 100]);
    //         // dd($response['body']['container']['orders']);

    //         // if (!isset($response['body']['container']['orders'])) {
    //         //     return response()->json(['error' => 'Failed to fetch orders 1234 '], 500);
    //         // } else {
    //             // dd($response['body']['container']['orders']);
    //         // }

    //         $orders = ($response['body']['container']['orders']) ? $response['body']['container']['orders'] : [];
    //         // dd($orders);
    //         return view('orders', ['orders' => $orders]);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => 'API request failed', 'message' => $e->getMessage()], 500);
    //     }
    // }

    // sendOrders
    //     public function sendOrders(Request $request)
    // {
    //     $request->validate([
    //         'orders' => 'required|array|min:1',
    //     ]);

    //     $user = Auth::user();

    //     if (!$user) {
    //         return response()->json([
    //             'error' => 'Shop not authenticated'
    //         ], 401);
    //     }

    //     $shopSetting = ShopSetting::where('user_id', $user->id)->first();

    //     if (!$shopSetting || !$shopSetting->api_token) {
    //         return response()->json([
    //             'error' => 'API token not found'
    //         ], 403);
    //     }

    //     try {

    //         $client = new Client([
    //             'verify' => false, // ⚠️ only local
    //             'timeout' => 30,
    //         ]);

    //         $response = $client->post(
    //             env('BACKEND_API_URL') . '/order/save',
    //             [
    //                 'headers' => [
    //                     'Accept'        => 'application/json',
    //                     'apiKey'        => env('BACKEND_API_KEY'),
    //                     'Authorization' => 'Bearer ' . $shopSetting->api_token,
    //                 ],
    //                 'json' => [
    //                     'orders' => $request->orders
    //                 ]
    //             ]
    //         );

    //         $result = json_decode($response->getBody()->getContents(), true);

    //         return response()->json([
    //             'success' => true,
    //             'data'    => $result
    //         ]);

    //     } catch (\Throwable $e) {

    //         return response()->json([
    //             'success' => false,
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function __construct()
    {
        $this->backendApiUrl = config('app.backend_api_url');
        $this->backendApiKey = config('app.backend_api_key');
        $this->version = config('shopify-app.api_version');
    }
    public function dashboard(Request $request)
    {


        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $user_id = Auth::user()->id;
        $user = ShopSetting::where('user_id', $user_id)->first();

        try {

            // Hardcoded data for testing

            // $data = [
            //     "t_parcel" => 35,
            //     "t_delivered" => 2,
            //     "t_return" => 1,
            //     "t_sale" => 7900.00,
            //     "t_delivery_fee" => 7900.00,
            //     "t_balance_proc" => 0.00,
            //     "t_balance_paid" => 0.00,
            //     "t_request" => 0,
            //     "merchant" => [
            //         "id" => 4,
            //         "user_id" => 8,
            //         "business_name" => "izzz",
            //         "merchant_unique_id" => "131448",
            //         "current_balance" => 0.00,
            //         "opening_balance" => 0.00,
            //         "wallet_balance" => 15000.00,
            //         "vat" => 0.00,
            //         "cod_charges" => [
            //             "inside_city" => 100,
            //             "sub_city" => 100,
            //             "outside_city" => 100
            //         ],
            //         "nid_id" => 34,
            //         "trade_license" => 35,
            //         "payment_period" => 3,
            //         "status" => 1,
            //         "address" => "gulbarg",
            //         "wallet_use_activation" => 0,
            //         "return_charges" => 100.00,
            //         "reference_name" => "syed salman",
            //         "reference_phone" => "03438640000",
            //         "created_at" => "2025-07-09T04:14:28.000000Z",
            //         "updated_at" => "2025-09-20T17:34:50.000000Z"
            //     ],
            //     "t_fraud" => 0,
            //     "t_shop" => 1,
            //     "t_parcel_bank" => 1,
            //     "t_cash_collection" => 3219055683.00,
            //     "t_selling_price" => 17350.00,
            //     "t_liquid_fragile" => 0.00,
            //     "t_vat_amount" => 0.00,
            //     "t_delivery_charge" => 150.00,
            //     "t_cod_amount" => 3219086966.00,
            //     "t_packaging" => 0.00,
            //     "t_delivery_amount" => 3219087116.00,
            //     "t_current_payable" => 800.00,
            //     "dates" => [
            //         "2025-12-05",
            //         "2025-12-06",
            //         "2025-12-07",
            //         "2025-12-08",
            //         "2025-12-09",
            //         "2025-12-10",
            //         "2025-12-11",
            //         "2025-12-12"
            //     ],
            //     "totals" => [0,0,0,0,0,0,0,0],
            //     "pendings" => [0,0,0,0,0,0,0,0],
            //     "delivers" => [0,0,0,0,0,0,0,0],
            //     "par_delivers" => [0,0,0,0,0,0,0,0],
            //     "returns" => [0,0,0,0,0,0,0,0]
            // ];

            $client = new Client([
                'verify' => false, // only for local SSL issues
            ]);


            $token = $user->api_token;

            $response = $client->request('GET', $this->backendApiUrl . '/dashboard', [
                'headers' => [
                    'apiKey' => $this->backendApiKey,
                    'Accept' => 'application/json',
                    'Authorization' => "Bearer $token",
                ]
            ]);

            $json = json_decode($response->getBody()->getContents(), true);
            $data = $json['data'] ?? [];

            return view('dashboard', compact('data'));
        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
