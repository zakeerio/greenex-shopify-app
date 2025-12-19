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

    public function __construct()
    {
        $this->backendApiUrl = config('app.backend_api_url');
        $this->backendApiKey = config('app.backend_api_key');
        $this->version = config('shopify-app.api_version');
    }

    /**
     * Process and format raw Shopify orders
     */
    private function processOrders(array $orders): array
    {
        $user = Auth::user();
        $processedOrders = [];

        foreach ($orders as $order) {
            // Convert ResponseAccess to array if needed
            if ($order instanceof \Gnikyt\BasicShopifyAPI\ResponseAccess) {
                $order = $order->toArray();
            }

            // Helper function to convert nested ResponseAccess objects
            $convertToArray = function ($item) {
                if ($item instanceof \Gnikyt\BasicShopifyAPI\ResponseAccess) {
                    return $item->toArray();
                }
                return $item;
            };

            // Determine Payment Type (COD vs PREPAID)
            $gateways = $order['payment_gateway_names'] ?? [];
            $isCod = false;
            foreach ($gateways as $gateway) {
                if (stripos($gateway, 'cash') !== false || stripos($gateway, 'cod') !== false) {
                    $isCod = true;
                    break;
                }
            }
            $paymentType = $isCod ? 'COD' : 'PREPAID';

            // Calculate total pieces and weight
            $totalPieces = 0;
            $totalWeight = 0;

            foreach ($order['line_items'] ?? [] as $item) {
                $item = $convertToArray($item);
                $totalPieces += $item['quantity'] ?? 0;
                $totalWeight += ($item['grams'] ?? 0) * ($item['quantity'] ?? 0);
            }

            // Convert weight to KG if in grams
            if ($totalWeight > 0 && $totalWeight >= 10) {
                $totalWeight = $totalWeight / 1000; // grams to kg
            } elseif ($totalWeight <= 0) {
                $totalWeight = 0.5; // default
            }

            $totalWeight = max(0.1, $totalWeight);

            // Check if order already sent
            $alreadySent = null;
            if ($user) {
                $alreadySent = SentOrder::where('user_id', $user->id)
                    ->where('order_id', $order['id'])
                    ->first();
            }

            // Check fulfillment status
            $fulfillmentStatus = $order['fulfillment_status'] ?? null;
            $isAlreadyFulfilled = $fulfillmentStatus === 'fulfilled';

            // Check if order can be processed
            $canBeProcessed = !$alreadySent && !$isAlreadyFulfilled;

            $processedOrder = [
                'id' => $order['id'] ?? null,
                'order_number' => $order['order_number'] ?? null,
                'name' => $order['name'] ?? null,
                'contact_email' => $order['contact_email'] ?? null,
                'phone' => $order['phone'] ?? null,
                'created_at' => $order['created_at'] ?? null,
                'updated_at' => $order['updated_at'] ?? null,
                'processed_at' => $order['processed_at'] ?? null,
                'current_total_price' => $order['current_total_price'] ?? 0,
                'total_price' => $order['total_price'] ?? 0,
                'subtotal_price' => $order['subtotal_price'] ?? 0,
                'total_tax' => $order['total_tax'] ?? 0,
                'currency' => $order['currency'] ?? 'USD',
                'financial_status' => $order['financial_status'] ?? 'pending',
                'fulfillment_status' => $fulfillmentStatus,
                'cancelled_at' => $order['cancelled_at'] ?? null,
                'cancel_reason' => $order['cancel_reason'] ?? null,
                'note' => $order['note'] ?? null,
                'note_attributes' => array_map($convertToArray, $order['note_attributes'] ?? []),
                'tags' => $order['tags'] ?? '',
                'merchant_id' => $order['id'] ?? null,
                'payment_type' => $paymentType,

                // Calculated fields
                'pieces' => $totalPieces,
                'weight' => round($totalWeight, 2),

                // Status tracking
                'already_sent' => $alreadySent ? true : false,
                'already_fulfilled' => $isAlreadyFulfilled,
                'can_be_processed' => $canBeProcessed,
                'sent_order_data' => $alreadySent ? [
                    'tracking_id' => $alreadySent->tracking_id,
                    'portal_order_id' => $alreadySent->portal_order_id,
                    'status' => $alreadySent->status,
                    'sent_at' => $alreadySent->sent_at
                ] : null,

                // Line items
                'line_items' => array_map($convertToArray, $order['line_items'] ?? []),

                // Shipping address
                'shipping_address' => $convertToArray($order['shipping_address'] ?? []),

                // Billing address
                'billing_address' => $convertToArray($order['billing_address'] ?? []),

                // Customer info
                'customer' => $convertToArray($order['customer'] ?? []),

                // Shipping lines
                'shipping_lines' => array_map($convertToArray, $order['shipping_lines'] ?? []),

                // Discount codes
                'discount_codes' => array_map($convertToArray, $order['discount_codes'] ?? []),
            ];

            $processedOrders[] = $processedOrder;
        }

        return $processedOrders;
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

            Log::info('Fetching orders from Shopify', [
                'shop_id' => $shop->id,
                'shop_name' => $shop->name,
                'api_version' => $this->version
            ]);

            // Optional: Get filters from request
            $params = [
                'limit' => $request->get('limit', 250), // Max 250 per request
                'status' => $request->get('status', 'any'),
                'financial_status' => $request->get('financial_status'),
                'fulfillment_status' => $request->get('fulfillment_status'),
                'created_at_min' => $request->get('created_at_min'),
                'created_at_max' => $request->get('created_at_max'),
                'fields' => $request->get('fields', 'id,order_number,name,contact_email,created_at,current_total_price,total_price,currency,financial_status,fulfillment_status,cancelled_at,note,tags,line_items,shipping_address,customer,shipping_lines,discount_codes'), // Specify fields for faster response
            ];

            // Remove null/empty values
            $params = array_filter($params);

            // Check if we need to load all orders
            $loadAll = $request->get('load_all', false);
            $pageInfo = $request->get('page_info');

            if ($pageInfo) {
                $params['page_info'] = $pageInfo;
            }

            $allOrders = [];
            $hasNextPage = false;
            $nextPageInfo = null;
            $totalOrders = 0;

            do {
                // Fetch orders from Shopify
                $response = $shop->api()->rest('GET', "/admin/api/{$this->version}/orders.json", $params);

                // Get orders safely from ResponseAccess object
                $orders = [];

                // Check if body exists in response
                if (isset($response['body'])) {
                    $body = $response['body'];

                    // If body is a ResponseAccess object, convert it to array
                    if ($body instanceof \Gnikyt\BasicShopifyAPI\ResponseAccess) {
                        $body = $body->toArray();
                    }

                    // Check different possible structures
                    if (isset($body['container']['orders'])) {
                        $orders = $body['container']['orders'];
                    } elseif (isset($body['orders'])) {
                        $orders = $body['orders'];
                    }

                    // If orders is still a ResponseAccess object, convert to array
                    if ($orders instanceof \Gnikyt\BasicShopifyAPI\ResponseAccess) {
                        $orders = $orders->toArray();
                    }

                    // Check for pagination info
                    if (isset($body['container']['page_info'])) {
                        $nextPageInfo = $body['container']['page_info'];
                        $hasNextPage = true;
                    } elseif (isset($response['link'])) {
                        // Parse link header for pagination
                        $linkHeader = $response['link'];
                        if (str_contains($linkHeader, 'rel="next"')) {
                            $hasNextPage = true;
                            // Extract page_info from URL
                            if (preg_match('/page_info=([^&>]+)/', $linkHeader, $matches)) {
                                $nextPageInfo = $matches[1];
                            }
                        }
                    }
                }

                // Make sure $orders is actually an array
                $orders = is_array($orders) ? $orders : [];

                // Add to all orders array
                $allOrders = array_merge($allOrders, $orders);
                $totalOrders += count($orders);

                Log::info('Fetched page of orders:', [
                    'page_orders' => count($orders),
                    'total_so_far' => count($allOrders),
                    'has_next_page' => $hasNextPage,
                    'next_page_info' => $nextPageInfo ? substr($nextPageInfo, 0, 20) . '...' : null
                ]);

                // Prepare for next page if loadAll is true
                if ($loadAll && $hasNextPage && $nextPageInfo) {
                    $params['page_info'] = $nextPageInfo;
                    $hasNextPage = false; // Reset for loop

                    // Add small delay to avoid rate limiting
                    if (count($allOrders) > 100) {
                        usleep(100000); // 100ms delay after 100 orders
                    }
                } else {
                    break;
                }
            } while ($loadAll);

            Log::info('Total orders fetched from Shopify:', [
                'total_orders' => count($allOrders),
                'pages_fetched' => ceil(count($allOrders) / $params['limit']),
                'load_all' => $loadAll
            ]);

            // Process and format orders if needed
            $processedOrders = $this->processOrders($allOrders);

            Log::info('Orders fetched successfully', [
                'total_orders' => count($processedOrders),
                'processed_orders' => count($processedOrders)
            ]);

            // Prepare pagination data for response
            $pagination = [
                'has_next_page' => $hasNextPage,
                'next_page_info' => $nextPageInfo,
                'current_count' => count($processedOrders),
                'total_fetched' => $totalOrders,
                'limit' => $params['limit']
            ];

            // If it's an AJAX request, return JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'orders' => $processedOrders,
                    'total' => count($processedOrders),
                    'pagination' => $pagination,
                    'message' => 'Orders fetched successfully'
                ]);
            }

            // For regular request, return view
            return view('orders', [
                'orders' => $processedOrders,
                'totalOrders' => count($processedOrders),
                'pagination' => $pagination
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch orders from Shopify', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'API request failed',
                    'message' => $e->getMessage()
                ], 500);
            }

            return view('orders', [
                'orders' => [],
                'totalOrders' => 0,
                'pagination' => [],
                'error' => 'Failed to fetch orders: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Process selected orders and send to portal with Guzzle - UPDATED
     */
    public function processSelectedOrders(Request $request)
    {
        // Log incoming request
        Log::info('Process Selected Orders Request:', [
            'user_id' => Auth::id(),
            'selected_count' => count($request->selected_orders ?? []),
            'timestamp' => now(),
        ]);

        // Validate request
        $validator = Validator::make($request->all(), [
            'selected_orders' => 'required|array|min:1',
            'selected_orders.*.order_id' => 'required|string',
            'selected_orders.*.order_number' => 'required|string',
            'selected_orders.*.collect_payment' => 'required|in:yes,no',
        ]);

        if ($validator->fails()) {
            Log::error('Order selection validation failed:', $validator->errors()->toArray());
            return response()->json([
                'success' => false,
                'message' => 'Invalid order data',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        // Get shop settings
        $shopSetting = ShopSetting::where('user_id', $user->id)->first();
        if (!$shopSetting) {
            Log::error('Shop settings not found for user:', ['user_id' => $user->id]);
            return response()->json([
                'success' => false,
                'message' => 'Shop configuration not found. Please configure your shop settings first.'
            ], 404);
        }

        if (!$shopSetting->api_token) {
            Log::error('API token missing for shop:', ['shop_id' => $shopSetting->id]);
            return response()->json([
                'success' => false,
                'message' => 'API token not configured. Please set up your API token in shop settings.'
            ], 403);
        }

        Log::info('Shop settings loaded:', [
            'shop_id' => $shopSetting->id,
            'shop_name' => $shopSetting->shop_name,
            'api_token_exists' => !empty($shopSetting->api_token),
        ]);

        try {
            // Process each selected order
            $processedOrders = [];
            $failedOrders = [];

            foreach ($request->selected_orders as $index => $selectedOrder) {
                Log::info("Processing order {$index}:", [
                    'order_number' => $selectedOrder['order_number'],
                    'order_id' => $selectedOrder['order_id']
                ]);

                try {
                    // ========== CHECK 1: DUPLICATE IN DATABASE ==========
                    $alreadySent = SentOrder::where('user_id', $user->id)
                        ->where('order_id', $selectedOrder['order_id'])
                        ->first();

                    if ($alreadySent) {
                        Log::warning('❌ DUPLICATE: Order already sent', [
                            'order_id' => $selectedOrder['order_id'],
                            'order_number' => $selectedOrder['order_number'],
                            'sent_order_id' => $alreadySent->id,
                            'sent_at' => $alreadySent->sent_at
                        ]);

                        $failedOrders[] = [
                            'order_number' => $selectedOrder['order_number'],
                            'reason' => 'Order already sent to portal',
                            'details' => 'Sent on: ' . $alreadySent->sent_at,
                            'tracking_id' => $alreadySent->tracking_id,
                            'type' => 'duplicate'
                        ];
                        continue;
                    }

                    // ========== CHECK 2: SHOPIFY FULFILLMENT STATUS ==========
                    $shopifyOrder = $user->api()->rest(
                        'GET',
                        "/admin/api/{$this->version}/orders/{$selectedOrder['order_id']}.json"
                    );

                    $fulfillmentStatus = $shopifyOrder['body']['order']['fulfillment_status'] ?? null;

                    if ($fulfillmentStatus === 'fulfilled') {
                        Log::warning('❌ FULFILLED: Shopify order already fulfilled', [
                            'order_id' => $selectedOrder['order_id'],
                            'order_number' => $selectedOrder['order_number'],
                            'fulfillment_status' => $fulfillmentStatus
                        ]);

                        $failedOrders[] = [
                            'order_number' => $selectedOrder['order_number'],
                            'reason' => 'Order already fulfilled in Shopify',
                            'details' => 'Cannot send already fulfilled orders',
                            'type' => 'already_fulfilled'
                        ];
                        continue;
                    }

                    // ========== CHECK 3: ORDER CANCELLED ==========
                    $cancelledAt = $shopifyOrder['body']['order']['cancelled_at'] ?? null;
                    if ($cancelledAt) {
                        Log::warning('❌ CANCELLED: Order is cancelled', [
                            'order_id' => $selectedOrder['order_id'],
                            'order_number' => $selectedOrder['order_number']
                        ]);

                        $failedOrders[] = [
                            'order_number' => $selectedOrder['order_number'],
                            'reason' => 'Order is cancelled in Shopify',
                            'type' => 'cancelled'
                        ];
                        continue;
                    }

                    // ========== ALL CHECKS PASSED - PROCESS ORDER ==========
                    Log::info('✅ NEW: Order passed all checks, proceeding...', [
                        'order_number' => $selectedOrder['order_number'],
                        'order_id' => $selectedOrder['order_id']
                    ]);

                    // Transform for portal
                    $portalData = $this->transformOrderForPortal(
                        $selectedOrder,
                        $shopSetting
                    );

                    // Send to portal
                    $response = $this->sendToPortal($portalData, $shopSetting->api_token);

                    if ($response['success']) {
                        // Use database transaction for atomic operations
                        DB::beginTransaction();

                        try {
                            // ✅ SAVE TO DATABASE FIRST (tracking ID)
                            $sentOrder = SentOrder::create([
                                'user_id' => $user->id,
                                'shop_setting_id' => $shopSetting->id,
                                'order_id' => $selectedOrder['order_id'],
                                'order_number' => $selectedOrder['order_number'],
                                'portal_reference' => $response['reference'] ?? null,
                                'tracking_id' => $response['tracking_id'] ?? null,
                                'portal_order_id' => $response['portal_order_id'] ?? null,
                                'portal_response' => $response['portal_response'] ?? $response,
                                'collect_payment' => $selectedOrder['collect_payment'],
                                'status' => 'sent',
                                'sent_at' => now()
                            ]);

                            Log::info('✅ DATABASE: Sent order saved', [
                                'sent_order_id' => $sentOrder->id,
                                'tracking_id' => $sentOrder->tracking_id,
                                'portal_order_id' => $sentOrder->portal_order_id
                            ]);

                            // ✅ UPDATE SHOPIFY (with tracking ID)
                            $shopifyUpdated = $this->updateShopifyOrder(
                                $user,
                                $selectedOrder['order_id'],
                                $response
                            );

                            if ($shopifyUpdated) {
                                $sentOrder->update([
                                    'status' => 'confirmed',
                                    'shopify_updated' => true,
                                    'shopify_updated_at' => now()
                                ]);

                                Log::info('✅ SHOPIFY: Order updated successfully', [
                                    'order_id' => $selectedOrder['order_id'],
                                    'tracking_id' => $sentOrder->tracking_id
                                ]);
                            } else {
                                Log::warning('⚠️ SHOPIFY: Order update failed but portal sent', [
                                    'order_id' => $selectedOrder['order_id']
                                ]);
                            }

                            DB::commit();

                            $processedOrders[] = [
                                'order_number' => $selectedOrder['order_number'],
                                'tracking_id' => $sentOrder->tracking_id,
                                'portal_order_id' => $sentOrder->portal_order_id,
                                'sent_order_id' => $sentOrder->id,
                                'shopify_updated' => $shopifyUpdated,
                                'message' => 'Successfully processed'
                            ];
                        } catch (\Exception $dbException) {
                            DB::rollBack();
                            throw $dbException;
                        }
                    } else {
                        Log::error('❌ PORTAL: Failed to send to portal', [
                            'order_number' => $selectedOrder['order_number'],
                            'portal_response' => $response
                        ]);

                        $failedOrders[] = [
                            'order_number' => $selectedOrder['order_number'],
                            'reason' => $response['message'] ?? 'Portal processing failed',
                            'details' => $response,
                            'type' => 'portal_error'
                        ];
                    }
                } catch (\Exception $e) {
                    Log::error('❌ EXCEPTION: Error processing order', [
                        'order_number' => $selectedOrder['order_number'],
                        'error' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);

                    $failedOrders[] = [
                        'order_number' => $selectedOrder['order_number'],
                        'reason' => $e->getMessage(),
                        'type' => 'exception'
                    ];
                }
            }

            // Prepare response
            $responseData = [
                'success' => true,
                'message' => 'Orders processed',
                'processed' => $processedOrders,
                'failed' => $failedOrders,
                'summary' => [
                    'total_selected' => count($request->selected_orders),
                    'successful' => count($processedOrders),
                    'failed' => count($failedOrders),
                    'failed_types' => array_count_values(array_column($failedOrders, 'type'))
                ]
            ];

            Log::info('Order processing completed:', $responseData['summary']);

            return response()->json($responseData);
        } catch (\Exception $e) {
            Log::error('Order processing failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Processing failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Transform order data for portal format
     */
    private function transformOrderForPortal($orderData, $shopSetting)
    {
        $shippingAddress = $orderData['shipping_address'] ?? [];
        $shopName = $shopSetting->shop_name;

        if (empty($shopName)) {
            $shopName = Auth::user()->name ?? 'Unknown Shop';
            Log::warning('Shop name is empty, using fallback', [
                'shop_setting_id' => $shopSetting->id,
                'fallback_name'   => $shopName
            ]);
        }

        /* ---------------- PAYMENT LOGIC ---------------- */
        $totalPrice = (float) ($orderData['total_price'] ?? 0);
        $collectPayment = $orderData['collect_payment'] ?? 'no';
        $paymentType = strtoupper($orderData['payment_type'] ?? 'PREPAID');

        $cashCollection = 0;
        if ($paymentType === 'COD' && $collectPayment === 'yes') {
            $cashCollection = $totalPrice;
        }

        /* ---------------- WEIGHT LOGIC ---------------- */
        $weight = $orderData['weight'] ?? 0;

        if ($weight > 0 && $weight >= 10) {
            // assume grams → kg
            $weight = $weight / 1000;
        } elseif ($weight <= 0) {
            $weight = 0.5; // default
        }

        $weight = max(0.1, $weight);

        /* ---------------- PHONE FORMAT ---------------- */
        $customerPhone = $orderData['phone'] ?? '';
        if (!empty($customerPhone) && !str_starts_with($customerPhone, '+')) {
            $customerPhone = $this->formatPhoneNumber($customerPhone);
        }

        /* ---------------- PORTAL PAYLOAD ---------------- */
        $portalPayload = [
            'merchant_order_id' => $orderData['order_id']
                ?? $orderData['merchant_id']
                ?? null,

            'order_number' => $orderData['order_number'],
            'shop_id'      => $shopSetting->id,
            'shop_name'    => $shopName,

            /* ---------- CUSTOMER (NESTED) ---------- */
            'customer' => [
                'name'  => $orderData['name'] ?? 'N/A',
                'email' => $orderData['email'] ?? '',
                'phone' => $customerPhone,
            ],

            /* ---------- SHIPPING ADDRESS (NESTED) ---------- */
            'shipping_address' => [
                'address1' => $shippingAddress['address1'] ?? '',
                'address2' => $shippingAddress['address2'] ?? '',
                'city'     => $shippingAddress['city'] ?? '',
                'province' => $shippingAddress['province'] ?? '',
                'country'  => $shippingAddress['country'] ?? '',
                'zip'      => $shippingAddress['zip'] ?? '',
                'company'  => $shippingAddress['company'] ?? '',
            ],

            /* ---------- ORDER DETAILS (NESTED) ---------- */
            'order_details' => [
                'total_amount'   => $totalPrice,
                'currency'       => $orderData['currency'] ?? 'USD',
                'total_pieces'   => $orderData['pieces'] ?? 0,
                'total_weight'   => $weight,
                'payment_type'   => $paymentType === 'COD' ? 'cod' : 'prepaid',
                'collect_payment' => $collectPayment,
                'cash_collection' => $cashCollection,
                'order_date'     => now()->toISOString(),
                'note'           => $orderData['note'] ?? '',
                'discount_codes' => $orderData['discount_codes'] ?? [],
                'tax_amount'     => $orderData['total_tax'] ?? 0,
            ],

            /* ---------- LINE ITEMS (NESTED ARRAY) ---------- */
            'line_items' => array_map(function ($item) {
                return [
                    'product_id' => $item['product_id'] ?? null,
                    'variant_id' => $item['variant_id'] ?? null,
                    'title'      => $item['title'] ?? '',
                    'quantity'   => (int) ($item['quantity'] ?? 0),
                    'price'      => (float) ($item['price'] ?? 0),
                    'sku'        => $item['sku'] ?? '',
                    'grams'      => $item['grams'] ?? 0,
                    'vendor'     => $item['vendor'] ?? '',
                    'total_discount' => $item['total_discount'] ?? 0,
                ];
            }, $orderData['line_items'] ?? []),

            /* ---------- METADATA ---------- */
            'metadata' => [
                'source_name'           => 'shopify_app',
                'processed_by'          => Auth::user()->name ?? 'system',
                'processing_timestamp'  => now()->toISOString(),
                'shopify_order_id'      => $orderData['order_id'] ?? null,
            ],
        ];

        Log::info('Transformed portal payload', [
            'order_number'     => $portalPayload['order_number'],
            'shop_name'        => $shopName,
            'total_amount'     => $totalPrice,
            'cash_collection'  => $cashCollection,
            'weight'           => $weight,
            'customer_phone'   => $customerPhone,
            'items_count'      => count($portalPayload['line_items']),
        ]);

        return $portalPayload;
    }

    /**
     * Format phone number
     */
    private function formatPhoneNumber($phone)
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // If it starts with 0, replace with country code
        if (str_starts_with($phone, '0')) {
            $phone = '92' . substr($phone, 1);
        }

        // Add + if not present
        if (!str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }

        return $phone;
    }

    /**
     * Send order data to portal using Guzzle
     */
    private function sendToPortal($orderData, $apiToken): array
    {
        Log::info('Preparing to send order to portal:', [
            'portal_url' => $this->backendApiUrl . '/order/save',
            'order_number' => $orderData['order_number'] ?? 'N/A',
            'customer_name' => $orderData['customer']['name'] ?? 'N/A',
            'total_amount' => $orderData['order_details']['total_amount'] ?? 'N/A'
        ]);

        $client = new Client([
            'base_uri' => $this->backendApiUrl,
            'timeout' => 30.0,
            'verify' => config('app.env') === 'production',
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]
        ]);

        try {
            Log::debug('Sending portal request', [
                'endpoint' => '/order/save',
                'order_number' => $orderData['order_number'] ?? null,
            ]);

            $response = $client->post($this->backendApiUrl . '/order/save', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiToken,
                    'apiKey'        => $this->backendApiKey,
                    'Accept'        => 'application/json',
                    'Content-Type'  => 'application/json',
                ],
                'json' => $orderData,
                'http_errors' => false, // handle manually
            ]);

            $statusCode = $response->getStatusCode();
            $rawBody    = $response->getBody()->getContents();
            $body       = json_decode($rawBody, true);
            $databody = $body['data'];

            Log::info('Portal API Response', [
                'status_code' => $statusCode,
                'body' => $databody,
                'order_number' => $orderData['order_number'] ?? null,
                'json_error' => json_last_error_msg(),
                'has_tracking_id' => is_array($databody) && isset($databody['tracking_id']),
                'raw_preview' => substr($rawBody, 0, 500),
            ]);

            // ❌ Invalid JSON (HTML / text response)
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($databody)) {
                Log::error('Portal returned invalid JSON', [
                    'order_number' => $orderData['order_number'] ?? null,
                    'status_code' => $statusCode,
                    'json_error' => json_last_error_msg(),
                    'raw_snippet' => substr($rawBody, 0, 200),
                ]);

                return [
                    'success' => false,
                    'message' => 'Portal returned invalid response (not JSON).',
                    'status_code' => $statusCode,
                ];
            }

            // ✅ Success
            if ($statusCode >= 200 && $statusCode < 300) {
                return [
                    'success' => true,
                    'message' => 'Order sent successfully',
                    'reference' => $databody['tracking_id'] ?? $databody['id'] ?? null,
                    'tracking_id' => $databody['tracking_id'] ?? null,
                    'portal_order_id' => $databody['id'] ?? null,
                    'portal_response' => $databody,
                ];
            }

            // ❌ API-level error
            Log::error('Portal API returned error', [
                'order_number' => $orderData['order_number'] ?? null,
                'status_code' => $statusCode,
                'response' => $databody,
            ]);

            return [
                'success' => false,
                'message' => $body['error']
                    ?? $body['message']
                    ?? 'Portal returned an error',
                'status_code' => $statusCode,
                'portal_response' => $databody,
            ];
        } catch (RequestException $e) {

            Log::critical('Portal request failed', [
                'order_number' => $orderData['order_number'] ?? null,
                'error' => $e->getMessage(),
                'request' => $e->getRequest()
                    ? [
                        'method' => $e->getRequest()->getMethod(),
                        'uri' => (string) $e->getRequest()->getUri(),
                    ]
                    : null,
            ]);

            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Update Shopify order with tracking and fulfillment - FIXED VERSION
     */
    private function updateShopifyOrder($user, $shopifyOrderId, $portalResponse)
    {
        try {
            $trackingId = $portalResponse['tracking_id'] ?? null;
            $portalOrderId = $portalResponse['portal_order_id'] ?? null;

            Log::info('Updating Shopify order:', [
                'order_id' => $shopifyOrderId,
                'tracking_id' => $trackingId,
                'portal_order_id' => $portalOrderId
            ]);

            if (!$trackingId) {
                Log::warning('No tracking ID to update Shopify', [
                    'order_id' => $shopifyOrderId,
                    'portal_response' => $portalResponse
                ]);
                return false;
            }

            // 1. Get order details
            $orderRes = $user->api()->rest(
                'GET',
                "/admin/api/{$this->version}/orders/{$shopifyOrderId}.json"
            );

            $order = $orderRes['body']['order'] ?? [];

            if (empty($order)) {
                Log::error('Shopify order not found', ['order_id' => $shopifyOrderId]);
                return false;
            }

            // 2. Update order note with tracking info
            $existingNote = $order['note'] ?? '';
            $newNote = trim($existingNote . "\n\n--- Shipping Info ---\n" .
                "Tracking ID: {$trackingId}\n" .
                "Portal Order ID: {$portalOrderId}\n" .
                "Processed: " . now()->format('Y-m-d H:i:s'));

            $updateResponse = $user->api()->rest('PUT', "/admin/api/{$this->version}/orders/{$shopifyOrderId}.json", [
                'order' => [
                    'id' => $shopifyOrderId,
                    'note' => $newNote,
                    'note_attributes' => [
                        [
                            'name' => 'tracking_id',
                            'value' => $trackingId
                        ],
                        [
                            'name' => 'portal_order_id',
                            'value' => $portalOrderId ?? ''
                        ],
                        [
                            'name' => 'processed_by_portal',
                            'value' => 'yes'
                        ]
                    ]
                ]
            ]);

            // 3. Get fulfillment orders (New Shopify API method)
            $foRes = $user->api()->rest(
                'GET',
                "/admin/api/{$this->version}/orders/{$shopifyOrderId}/fulfillment_orders.json"
            );

            $fulfillmentOrders = $foRes['body']['fulfillment_orders'] ?? [];

            if (empty($fulfillmentOrders)) {
                Log::warning('No fulfillment orders found, trying legacy fulfillment', [
                    'order_id' => $shopifyOrderId
                ]);

                // Fallback to legacy fulfillment
                return $this->createLegacyFulfillment($user, $shopifyOrderId, $trackingId);
            }

            // 4. Create fulfillment using Fulfillment Orders API
            $fulfillmentOrder = $fulfillmentOrders[0];

            $fulfillmentPayload = [
                'fulfillment' => [
                    'message' => 'Shipped via GreenEx',
                    'notify_customer' => true,
                    'tracking_info' => [
                        'number' => $trackingId,
                        'company' => 'GreenEx',
                        'url' => $this->generateTrackingUrl($trackingId)
                    ],
                    'line_items_by_fulfillment_order' => [[
                        'fulfillment_order_id' => $fulfillmentOrder['id'],
                        'fulfillment_order_line_items' => array_map(function ($item) {
                            return [
                                'id' => $item['id'],
                                'quantity' => $item['quantity']
                            ];
                        }, $fulfillmentOrder['line_items'] ?? [])
                    ]]
                ]
            ];

            $fulfillmentResponse = $user->api()->rest(
                'POST',
                "/admin/api/{$this->version}/fulfillments.json",
                $fulfillmentPayload
            );

            Log::info('✅ SHOPIFY: Fulfillment created successfully', [
                'order_id' => $shopifyOrderId,
                'tracking_id' => $trackingId,
                'fulfillment_order_id' => $fulfillmentOrder['id'],
                'tracking_url' => $this->generateTrackingUrl($trackingId)
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('❌ SHOPIFY: Failed to update Shopify order:', [
                'order_id' => $shopifyOrderId,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return false;
        }
    }

    /**
     * Legacy fulfillment method for fallback
     */
    private function createLegacyFulfillment($user, $shopifyOrderId, $trackingId)
    {
        try {
            // Get default location
            $locationId = $this->getDefaultLocationId($user);

            if (!$locationId) {
                Log::error('No location ID found for legacy fulfillment');
                return false;
            }

            $fulfillmentData = [
                'fulfillment' => [
                    'location_id' => $locationId,
                    'tracking_number' => $trackingId,
                    'tracking_company' => 'GreenEx',
                    'tracking_url' => $this->generateTrackingUrl($trackingId),
                    'notify_customer' => true,
                    'status' => 'success'
                ]
            ];

            $response = $user->api()->rest(
                'POST',
                "/admin/api/{$this->version}/orders/{$shopifyOrderId}/fulfillments.json",
                $fulfillmentData
            );

            Log::info('Legacy fulfillment created', [
                'order_id' => $shopifyOrderId,
                'location_id' => $locationId,
                'tracking_id' => $trackingId
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Legacy fulfillment failed:', [
                'order_id' => $shopifyOrderId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get default location ID from Shopify
     */
    private function getDefaultLocationId($user)
    {
        try {
            $locations = $user->api()->rest('GET', "/admin/api/{$this->version}/locations.json");

            if (isset($locations['body']['locations']) && count($locations['body']['locations']) > 0) {
                $firstLocation = $locations['body']['locations'][0];
                return $firstLocation['id'];
            }

            return env('SHOPIFY_DEFAULT_LOCATION_ID', null);
        } catch (\Exception $e) {
            Log::warning('Could not get default location ID:', ['error' => $e->getMessage()]);
            return env('SHOPIFY_DEFAULT_LOCATION_ID', null);
        }
    }

    /**
     * Generate tracking URL based on tracking ID
     */
    private function generateTrackingUrl($trackingId)
    {
        if (str_starts_with($trackingId, 'GX')) {
            return "https://greenex.pk/tracking?tracking_id={$trackingId}";
        } elseif (str_starts_with($trackingId, 'LX')) {
            return "https://another-courier.com/tracking/{$trackingId}";
        }

        return "https://greenex.pk/tracking?tracking_id={$trackingId}";
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
