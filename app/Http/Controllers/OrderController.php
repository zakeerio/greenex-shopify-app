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
        //
        Log::debug('API Config from .env:', [
            'url' => $this->backendApiUrl,
            'key_set' => !empty($this->backendApiKey),
            'key_preview' => $this->backendApiKey ? substr($this->backendApiKey, 0, 8) . '...' : 'NOT SET',
        ]);
    }


    /**
     * Process and format raw Shopify orders
     */

    private function processOrders(array $orders): array
    {
        return array_map(function ($order) {
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

            return [
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
                'fulfillment_status' => $order['fulfillment_status'] ?? null,
                'cancelled_at' => $order['cancelled_at'] ?? null,
                'cancel_reason' => $order['cancel_reason'] ?? null,
                'note' => $order['note'] ?? null,
                'note_attributes' => array_map($convertToArray, $order['note_attributes'] ?? []),
                'tags' => $order['tags'] ?? '',
                'merchant_id' => $order['id'] ?? null, // Shopify order ID as merchant_id

                // Line items - handle possible ResponseAccess objects
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
        }, $orders);
    }

    /**
     * Show orders index page
     */
    /**
     * Show orders index page
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
                'limit' => $request->get('limit', 50),
                'status' => $request->get('status', 'any'),
                'financial_status' => $request->get('financial_status'),
                'fulfillment_status' => $request->get('fulfillment_status'),
                'created_at_min' => $request->get('created_at_min'),
                'created_at_max' => $request->get('created_at_max'),
            ];

            // Remove null/empty values
            $params = array_filter($params);

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
            }

            // Make sure $orders is actually an array
            $orders = is_array($orders) ? $orders : [];

            Log::info('Raw orders data:', [
                'orders_count' => count($orders),
                'orders_type' => gettype($orders),
                'sample_order_keys' => count($orders) > 0 ? array_keys($orders[0]) : 'No orders'
            ]);

            // Process and format orders if needed
            $processedOrders = $this->processOrders($orders);

            Log::info('Orders fetched successfully', [
                'total_orders' => count($orders),
                'processed_orders' => count($processedOrders)
            ]);

            // If it's an AJAX request, return JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'orders' => $processedOrders,
                    'total' => count($processedOrders),
                    'message' => 'Orders fetched successfully'
                ]);
            }

            // For regular request, return view
            return view('orders', [
                'orders' => $processedOrders,
                'totalOrders' => count($processedOrders)
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
                'error' => 'Failed to fetch orders: ' . $e->getMessage()
            ]);
        }
    }


    /**
     * Send orders to external API
     */
    public function sendOrders(Request $request)
    {
        // Log incoming request for debugging
        Log::info('Send Orders Request Received:', [
            'user_id' => Auth::id(),
            'orders_count' => count($request->orders ?? []),
            'ip' => $request->ip()
        ]);

        // Validate request
        $validator = Validator::make($request->all(), [
            'orders' => 'required|array|min:1',
            'orders.*.merchant_id' => 'required|string',
            'orders.*.total_price' => 'required|numeric|min:0',
            'orders.*.line_items' => 'required|array',
            'orders.*.shipping_address' => 'required|array',
            'orders.*.phone' => 'nullable|string',
            'orders.*.name' => 'required|string|max:255',
            'orders.*.email' => 'nullable|email',
            'orders.*.order_number' => 'required|string',
        ]);

        if ($validator->fails()) {
            Log::error('Order validation failed:', $validator->errors()->toArray());
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check authentication
        $user = Auth::user();
        if (!$user) {
            Log::warning('Unauthenticated order send attempt');
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        // Get shop settings with API token
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

        try {
            // Prepare data for external API
            $payload = [
                'orders' => $request->orders,
                'shop_id' => $shopSetting->id,
                'user_id' => $user->id,
                'timestamp' => now()->toISOString()
            ];

            Log::info('Sending orders to external API:', [
                'url' => config('services.backend_api.url') . '/order/save',
                'orders_count' => count($request->orders),
                'shop_id' => $shopSetting->id
            ]);

            // Send to external API using Laravel HTTP client
            $response = Http::withOptions([
                'verify' => config('app.env') === 'production' ? true : false,
                'timeout' => 60,
                'connect_timeout' => 30,
            ])->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'apiKey' => config('services.backend_api.key'),
                'Authorization' => 'Bearer ' . $shopSetting->api_token,
            ])->post(config('services.backend_api.url') . '/order/save', $payload);

            $responseBody = $response->json();
            $statusCode = $response->status();

            Log::info('External API response:', [
                'status' => $statusCode,
                'response' => $responseBody
            ]);

            if ($response->successful()) {
                // Log successful order submission
                Log::info('Orders sent successfully:', [
                    'order_numbers' => array_column($request->orders, 'order_number'),
                    'external_response' => $responseBody
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Orders sent successfully',
                    'data' => $responseBody,
                    'orders_sent' => count($request->orders)
                ]);
            } else {
                Log::error('External API error:', [
                    'status' => $statusCode,
                    'response' => $responseBody
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'External API error: ' . ($responseBody['message'] ?? 'Unknown error'),
                    'status_code' => $statusCode,
                    'response' => $responseBody
                ], $statusCode);
            }

        } catch (\Exception $e) {
            Log::error('Order sending failed:', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send orders: ' . $e->getMessage(),
                'error_type' => get_class($e)
            ], 500);
        }
    }



    /**
     * NEW: Process selected orders and send to portal with Guzzle - UPDATED
     */
    public function processSelectedOrders(Request $request)
    {
        // Log incoming request
        Log::info('Process Selected Orders Request:', [
            'user_id' => Auth::id(),
            'selected_count' => count($request->selected_orders ?? []),
            'timestamp' => now(),
            'sample_data' => $request->selected_orders[0] ?? 'No data'
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
            'api_token_length' => strlen($shopSetting->api_token ?? '')
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
                    // Transform order data for portal
                    $portalData = $this->transformOrderForPortal(
                        $selectedOrder,
                        $shopSetting
                    );
                        dd($portalData);
                    Log::debug('Portal data prepared:', [
                        'order_number' => $portalData['order_number'],
                        'shop_name' => $portalData['shop_name'],
                        'total_amount' => $portalData['order_details']['total_amount']
                    ]);

                    // Send to portal using Guzzle
                    $response = $this->sendToPortal($portalData, $shopSetting->api_token);

                    if ($response['success']) {
                        // Save to database as sent order
                        $sentOrder = SentOrder::create([
                            'user_id' => $user->id,
                            'shop_setting' => $shopSetting,
                            'order_id' => $selectedOrder['order_id'],
                            'order_number' => $selectedOrder['order_number'],
                            'portal_reference' => $response['reference'] ?? null,
                            'portal_response' => $response['portal_response'] ?? $response,
                            'collect_payment' => $selectedOrder['collect_payment'],
                            'status' => 'sent',
                            'sent_at' => now()
                        ]);

                        // Update Shopify with tracking and fulfillment
                        $shopifyUpdated = $this->updateShopifyOrder(
                            $user,
                            $selectedOrder['order_id'],
                            $response
                        );

                        if ($shopifyUpdated) {
                            $sentOrder->update(['status' => 'confirmed']);
                        }

                        $processedOrders[] = [
                            'order_number' => $selectedOrder['order_number'],
                            'tracking_id' => $response['portal_response']['tracking_id'] ?? null,
                            'portal_order_id' => $response['portal_response']['id'] ?? null,
                            'sent_order_id' => $sentOrder->id,
                            'shopify_updated' => $shopifyUpdated
                        ];

                        Log::info('Order processed successfully:', [
                            'order_number' => $selectedOrder['order_number'],
                            'sent_order_id' => $sentOrder->id,
                            'tracking_id' => $response['portal_response']['tracking_id'] ?? 'N/A'
                        ]);
                    } else {
                        $failedOrders[] = [
                            'order_number' => $selectedOrder['order_number'],
                            'reason' => $response['message'] ?? 'Portal processing failed',
                            'details' => $response
                        ];

                        Log::error('Order processing failed:', [
                            'order_number' => $selectedOrder['order_number'],
                            'response' => $response
                        ]);
                    }

                } catch (\Exception $e) {
                    Log::error('Error processing order:', [
                        'order_number' => $selectedOrder['order_number'],
                        'error' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);

                    $failedOrders[] = [
                        'order_number' => $selectedOrder['order_number'],
                        'reason' => $e->getMessage()
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
                    'failed' => count($failedOrders)
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
     * (OLD VERSION STRUCTURE + NEW CALCULATIONS & LOGGING)
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

        $totalPrice    = (float) ($orderData['total_price'] ?? 0);
        $collectPayment = $orderData['collect_payment'] ?? 'no';
        $paymentType    = strtoupper($orderData['payment_type'] ?? 'PREPAID');

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
                'currency'       => 'USD',
                'total_pieces'   => $orderData['pieces'] ?? 0,
                'total_weight'   => $weight,
                'payment_type'   => $paymentType === 'COD' ? 'cod' : 'prepaid',
                'collect_payment'=> $collectPayment,
                'cash_collection'=> $cashCollection,
                'order_date'     => now()->toISOString(),
                'note'           => $orderData['note'] ?? '',
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
                ];
            }, $orderData['line_items'] ?? []),

            /* ---------- METADATA ---------- */
            'metadata' => [
                'source_name'           => 'shopify_app',
                'processed_by'          => Auth::user()->name ?? 'system',
                'processing_timestamp'  => now()->toISOString(),
            ],
        ];

        /* ---------------- LOGGING ---------------- */

        Log::info('Transformed portal payload (OLD STRUCTURE)', [
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
     * Format shipping address
     */
    private function formatAddress($shippingAddress)
    {
        $parts = [];

        if (!empty($shippingAddress['address1'])) {
            $parts[] = $shippingAddress['address1'];
        }

        if (!empty($shippingAddress['address2'])) {
            $parts[] = $shippingAddress['address2'];
        }

        if (!empty($shippingAddress['city'])) {
            $parts[] = $shippingAddress['city'];
        }

        if (!empty($shippingAddress['province'])) {
            $parts[] = $shippingAddress['province'];
        }

        if (!empty($shippingAddress['zip'])) {
            $parts[] = $shippingAddress['zip'];
        }

        if (!empty($shippingAddress['country'])) {
            $parts[] = $shippingAddress['country'];
        }

        return implode(', ', $parts);
    }

    /**
 * Send order data to portal using Guzzle - FIXED
 */
    private function sendToPortal($orderData, $apiToken)
    {
        Log::info('Preparing to send order to portal:', [
            'portal_url' => $this->backendApiUrl . '/order/save',
            'order_number' => $orderData['order_number'] ?? 'N/A',
            'customer_name' => $orderData['customer_name'] ?? 'N/A',
            'total_amount' => $orderData['selling_price'] ?? 'N/A'
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
            Log::debug('Sending portal request:', [
                'endpoint' => '/order/save',
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiToken,
                    'apiKey' => $this->backendApiKey,
                ],
                'payload_summary' => [
                    'order_number' => $orderData['order_number'],
                    'customer_name' => $orderData['customer_name'],
                    'customer_phone' => $orderData['customer_phone'],
                    'selling_price' => $orderData['selling_price'],
                    'cash_collection' => $orderData['cash_collection'],
                    'weight' => $orderData['weight']
                ]
            ]);

            $response = $client->post('/order/save', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiToken,
                    'apiKey' => $this->backendApiKey,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json' => $orderData,
                'http_errors' => false
            ]);

            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody()->getContents(), true);

            Log::info('Portal API Response:', [
                'status_code' => $statusCode,
                'order_number' => $orderData['order_number'],
                'response_has_tracking_id' => isset($body['tracking_id']),
                'response_has_id' => isset($body['id']),
                'response_keys' => array_keys($body)
            ]);

            if ($statusCode >= 200 && $statusCode < 300) {
                return [
                    'success' => true,
                    'reference' => $body['tracking_id'] ?? $body['id'] ?? null,
                    'tracking_id' => $body['tracking_id'] ?? null,
                    'portal_order_id' => $body['id'] ?? null,
                    'message' => 'Order sent successfully',
                    'portal_response' => $body
                ];
            } else {
                Log::error('Portal API Error:', [
                    'status_code' => $statusCode,
                    'response' => $body,
                    'order_number' => $orderData['order_number']
                ]);

                return [
                    'success' => false,
                    'message' => $body['error'] ?? $body['message'] ?? 'Portal returned error',
                    'status_code' => $statusCode,
                    'portal_response' => $body
                ];
            }

        } catch (RequestException $e) {
            Log::error('Guzzle request failed:', [
                'error' => $e->getMessage(),
                'order_number' => $orderData['order_number'] ?? 'N/A',
                'request_info' => method_exists($e, 'getRequest') ? [
                    'method' => $e->getRequest()->getMethod(),
                    'uri' => (string) $e->getRequest()->getUri(),
                ] : null
            ]);

            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update Shopify order with tracking and fulfillment - FIXED
     */
    private function updateShopifyOrder($user, $shopifyOrderId, $portalResponse)
    {
        //    $portalResponse = [
        //         'status' => true,
        //         'message' => 'Order pushed successfully',
        //         'portal_order_id' => 456789,
        //         // 'tracking_id' => $portalResponse['tracking_id'],
        //         // 'tracking_url' => $this->generateTrackingUrl($portalResponse['tracking_id']),
        //         'tracking_id' => 'GX-1458758',
        //         'tracking_url' => $this->generateTrackingUrl('GX-1458758'),
        //         'portal_response' => [
        //             'id' => 456789,
        //             'order_status' => 'processing',
        //             'created_at' => now()->toDateTimeString()
        //         ]
        //     ];

        //     $user = Auth::user(); // logged-in Shopify user
        //     $shopifyOrderId = 6537998434521; // REAL Shopify order ID


        try {

            // Get tracking ID from the correct location in response
            $trackingId = $portalResponse['tracking_id'] ?? ($portalResponse['portal_response']['tracking_id'] ?? null);
            $portalOrderId = $portalResponse['portal_order_id'] ?? ($portalResponse['portal_response']['id'] ?? null);

            Log::info('Updating Shopify order:', [
                'order_id' => $shopifyOrderId,
                'tracking_id' => $trackingId,
                'portal_order_id' => $portalOrderId,
                'portal_response_keys' => array_keys($portalResponse)
            ]);

            if (!$trackingId) {
                Log::warning('No tracking ID to update Shopify', [
                    'order_id' => $shopifyOrderId,
                    'portal_response' => $portalResponse
                ]);
                return false;
            }

            // 1. Update order note with tracking info
            $existingOrder = $user->api()->rest('GET', "/admin/api/{$this->version}/orders/{$shopifyOrderId}.json");
            $existingNote = $existingOrder['body']['order']['note'] ?? '';

            $newNote = $existingNote . "\n\n--- Shipping Info ---\n" .
                    "Tracking ID: " . $trackingId . "\n" .
                    "Portal Order ID: " . ($portalOrderId ?? 'N/A') . "\n" .
                    "Processed: " . now()->format('Y-m-d H:i:s');

            // Update order with tracking info
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
                        ]
                    ]
                ]
            ]);

            // 2. Create fulfillment
            $locationId = $this->getDefaultLocationId($user);
            if (!$locationId) {
                Log::warning('No location ID found for fulfillment', ['order_id' => $shopifyOrderId]);
                return false;
            }

            $fulfillmentData = [
                'fulfillment' => [
                    'location_id' => $locationId,
                    'tracking_number' => $trackingId,
                    'tracking_company' => 'GreenEx',
                    'tracking_url' => $this->generateTrackingUrl($trackingId),
                    'notify_customer' => true
                ]
            ];

            $fulfillmentResponse = $user->api()->rest('POST',
                "/admin/api/{$this->version}/orders/{$shopifyOrderId}/fulfillments.json",
                $fulfillmentData
            );

            Log::info('Shopify order updated successfully:', [
                'order_id' => $shopifyOrderId,
                'tracking_id' => $trackingId,
                'fulfillment_status' => 'created'
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to update Shopify order:', [
                'order_id' => $shopifyOrderId,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
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
            $this->version = config('shopify-app.api_version');
            $locations = $user->api()->rest('GET', "/admin/api/{$this->version}/locations.json");

            if (isset($locations['body']['locations']) && count($locations['body']['locations']) > 0) {
                return $locations['body']['locations'][0]['id'];
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
        // Customize this based on your shipping provider
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

