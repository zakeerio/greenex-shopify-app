<?php

namespace App\Http\Controllers;

use App\Models\ShopSetting;
use App\Models\SentOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    protected $backendApiUrl;
    protected $backendApiKey;
    protected $version;

    public function __construct(
        protected \App\Services\ShopifyOrderService $shopifyService
    ) {
        $this->backendApiUrl = config('app.backend_api_url');
        $this->backendApiKey = config('app.backend_api_key');
        $this->version = config('shopify-app.api_version');
    }

    /**
     * Show orders index page with pagination support
     */
    public function index(Request $request)
    {
        try {
            $shop = Auth::user();

            if (!$shop) {
                return response()->json([
                    'success' => false,
                    'error' => 'Shop not authenticated',
                ], 401);
            }

            // Get filters from request
            $params = [
                'limit' => $request->get('limit', 50),
                'status' => $request->get('status', 'any'),
                'financial_status' => $request->get('financial_status'),
                'fulfillment_status' => $request->get('fulfillment_status'),
                'created_at_min' => $request->get('created_at_min'),
                'created_at_max' => $request->get('created_at_max'),
                'fields' => $request->get('fields', 'id,order_number,name,contact_email,phone,created_at,current_total_price,total_price,currency,financial_status,fulfillment_status,cancelled_at,note,tags,line_items,shipping_address,customer,shipping_lines,discount_codes'),
                'load_all' => $request->get('load_all', false),
                'page_info' => $request->get('page_info')
            ];

            $processedOrders = $this->shopifyService->fetchOrders($shop, $params);

            // Pagination (Simplified for now, Service returns flat list but could return pagination metadata)
            // Assuming Service handles everything and returns simplified array for now.
            // If pagination validation is needed, we should update Service to return DTO with pagination.
            // For now, let's pass empty pagination or handle what we have.

            $pagination = [
                'has_next_page' => false, // Service needs to return this to support pagination controls
                'next_page_info' => null,
                'current_count' => count($processedOrders),
                'total_fetched' => count($processedOrders),
                'limit' => $params['limit']
            ];

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'orders' => $processedOrders,
                    'total' => count($processedOrders),
                    'pagination' => $pagination,
                    'message' => 'Orders fetched successfully'
                ]);
            }

            return view('orders', [
                'orders' => $processedOrders,
                'totalOrders' => count($processedOrders),
                'pagination' => $pagination
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch orders', ['error' => $e->getMessage()]);

            $errorMessage = $e->getCode() == 401 ? 'Shopify Authentication Failed.' : 'Failed to fetch orders: ' . $e->getMessage();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMessage], 500);
            }

            return view('orders', [
                'orders' => [],
                'totalOrders' => 0,
                'pagination' => [],
                'error' => $errorMessage
            ]);
        }
    }

    /**
     * Process selected orders and send to portal with Guzzle - UPDATED
     */
    /**
     * Process selected orders and send to portal
     */
    public function processSelectedOrders(Request $request, \App\Actions\ProcessOrderAction $processOrderAction)
    {
        Log::info('Process Selected Orders Request:', [
            'user_id' => Auth::id(),
            'selected_count' => count($request->selected_orders ?? []),
        ]);

        $validator = Validator::make($request->all(), [
            'selected_orders' => 'required|array|min:1',
            'selected_orders.*.order_id' => 'required',
            'selected_orders.*.order_number' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $user = Auth::user();
            $result = $processOrderAction->execute($user, $request->selected_orders);

            return response()->json([
                'success' => true,
                'message' => 'Orders processed',
                'processed' => $result['processed'],
                'failed' => $result['failed'],
                'summary' => [
                    'successful' => count($result['processed']),
                    'failed' => count($result['failed']),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Order Processing Fatal Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }


    /**
     * View sent orders history
     */
    public function sentOrders(Request $request)
    {
        $user = Auth::user();
        $sentOrders = SentOrder::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('orders.sent', ['sentOrders' => $sentOrders]);
    }
}
