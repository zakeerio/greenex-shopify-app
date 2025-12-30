<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;

class ShopifyWebhookController extends Controller
{
    // ✅ App Uninstall
    public function appUninstalled(Request $request)
    {
        $shop = $request->header('X-Shopify-Shop-Domain');

        User::where('name', $shop)->delete();

        return response()->json(['status' => 'App removed']);
    }

    // ✅ Order Create
    public function orderCreate(Request $request)
    {
        $order = $request->all();

        Order::updateOrCreate(
            ['shopify_order_id' => $order['id']],
            [
                'shop' => $request->header('X-Shopify-Shop-Domain'),
                'payload' => json_encode($order),
                'total_price' => $order['total_price']
            ]
        );

        return response()->json(['status' => 'Order stored']);
    }

    // ✅ Order Update
    public function orderUpdate(Request $request)
    {
        $order = $request->all();

        Order::where('shopify_order_id', $order['id'])
            ->update([
                'payload' => json_encode($order),
                'total_price' => $order['total_price']
            ]);

        return response()->json(['status' => 'Order updated']);
    }



    // ✅ Fulfillment Create
    public function fulfillmentCreate(Request $request)
    {
        \Log::info('Fulfillment Event:', $request->all());
        return response()->json(['status' => 'Fulfillment received']);
    }
}
