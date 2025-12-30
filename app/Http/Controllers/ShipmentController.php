<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Models\ShopSetting;
use Illuminate\Support\Facades\Auth;

class ShipmentController extends Controller
{
    protected $client;
    protected $backendApiUrl;
    protected $backendApiKey;
    protected $token;

    public function __construct()
    {
        $this->backendApiUrl = config('app.backend_api_url');
        $this->backendApiKey = config('app.backend_api_key');

        $this->client = new Client([
            'verify' => false, // local SSL issue fix
        ]);

        $userId = Auth::id();
        $setting = ShopSetting::where('user_id', $userId)->first();

        $this->token = $setting?->api_token;
    }

    /* ===============================
       📦 LIST SHIPMENTS
    =============================== */
    public function index()
    {
        // $response = $this->client->request( 'GET', $this->backendApiUrl . '/parcel/index' );

        // dd($backendApiUrl . '/dashboard');

        $response = $this->client->request('GET', $this->backendApiUrl . '/parcel/index', [
            'headers' => [
                'apiKey' => $this->backendApiKey,
                'Accept' => 'application/json',
                'Authorization' => "Bearer $this->token",
            ]
        ]);

        $json = json_decode($response->getBody()->getContents(), true);
        $shipments = $json['data']['parcels'] ?? $json['data'] ?? [];

        // $shipments = $json['data'] ?? [];
        // dd($shipments);

        // return view('shipments', compact('shipments'));
        return \Inertia\Inertia::render('Shipments/Index', compact('shipments'));
    }

    // /* ===============================
    //    🖨️ BULK PRINT
    // =============================== */
    // public function bulkPrint(Request $request)
    // {
    //     $selectedIds = $request->input('selected_shipments', []);

    //     if (empty($selectedIds)) {
    //         return redirect()->back()->with('error', 'No shipments selected.');
    //     }

    //     $response = $this->client->request('GET', $this->backendApiUrl . '/parcel/index', [
    //         'headers' => [
    //             'apiKey' => $this->backendApiKey,
    //             'Accept' => 'application/json',
    //             'Authorization' => "Bearer $this->token",
    //         ]
    //     ]);

    //     $json = json_decode($response->getBody()->getContents(), true);
    //     $allShipments = $json['data']['parcels'] ?? $json['data'] ?? [];

    //     $shipments = array_filter($allShipments, function ($shipment) use ($selectedIds) {
    //         return in_array($shipment['id'], $selectedIds);
    //     });

    //     return view('print-shipments', compact('shipments'));
    // }

    public function bulkPrint(Request $request)
    {
        // \Illuminate\Support\Facades\Log::info('BulkPrint Entry', ['method' => $request->method(), 'url' => $request->fullUrl()]);

        // ✅ If someone hits URL directly or refreshes (GET request)
        if ($request->isMethod('get')) {
            return redirect()->route('shipments');
        }

        // \Illuminate\Support\Facades\Log::info('BulkPrint Hit', ['method' => $request->method(), 'all' => $request->all()]);
        // dd($request->all());

        $selectedIds = $request->input('selected_shipments', []);

        if (empty($selectedIds)) {
            return redirect()->back()->with('error', 'No shipments selected.');
        }

        $response = $this->client->request('POST', $this->backendApiUrl . '/parcel/printLabelParcels', [
            'headers' => [
                'apiKey' => $this->backendApiKey,
                'Accept' => 'application/json',
                'Authorization' => "Bearer $this->token",
            ],
            'form_params' => [
                'parcel_ids' => $selectedIds
            ]
        ]);
        // dd();
        $json = json_decode($response->getBody()->getContents(), true);

        $shipments = $json['data']['parcels'] ?? $json['data'] ?? [];

        return view('print-shipments', compact('shipments'));
    }


    /* ===============================
       ➕ CREATE FORM
    =============================== */
    public function create()
    {
        return view('shipments.create');
    }

    /* ===============================
       💾 STORE SHIPMENT
    =============================== */
    public function store(Request $request)
    {
        $response = $this->request('POST', '/parcel/store', $request->all());

        return response()->json($response);
    }

    /* ===============================
       🔍 DETAILS
    =============================== */
    public function details($id)
    {
        $response = $this->request('GET', $this->backendApiUrl . "/parcel/details/{$id}");

        $shipment = $response['data'] ?? null;

        return view('shipments.details', compact('shipment'));
    }

    /* ===============================
       ✏️ EDIT
    =============================== */
    public function edit($id)
    {
        $response = $this->request('GET', $this->backendApiUrl . "/parcel/details/{$id}");

        $shipment = $response['data'] ?? null;

        return view('shipments.edit', compact('shipment'));
    }

    /* ===============================
       🔄 UPDATE
    =============================== */
    public function update(Request $request, $id)
    {
        $response = $this->request('PUT', "/parcel/update/{$id}", $request->all());

        return response()->json($response);
    }

    /* ===============================
       📜 LOGS
    =============================== */
    public function logs($id)
    {
        $response = $this->request('GET', "/parcel/logs/{$id}");

        $logs = $response['data'] ?? [];

        return view('shipments.logs', compact('logs'));
    }

    /* ===============================
       🔎 FILTER
    =============================== */
    public function filter(Request $request)
    {
        $response = $this->request('GET', '/parcel/filter', $request->query());

        $shipments = $response['data'] ?? [];

        return view('shipments.index', compact('shipments'));
    }

    /* ===============================
       🔄 UPDATE STATUS
    =============================== */
    public function updateStatus($id, $statusId)
    {
        $response = $this->request(
            'PUT',
            "/parcel/{$id}/status/{$statusId}"
        );

        return redirect()->back()->with('success', 'Status Updated');
    }

    /* ===============================
       ❌ DELETE
    =============================== */
    public function destroy($id)
    {
        $response = $this->request('DELETE', "/parcel/delete/{$id}");

        return response()->json($response);
    }

    /* ===============================
       🧠 COMMON GUZZLE METHOD
    =============================== */
    private function request($method, $endpoint, $data = [])
    {
        try {
            $options = [
                'headers' => [
                    'apiKey' => $this->backendApiKey,
                    'Accept' => 'application/json',
                    'Authorization' => "Bearer {$this->token}",
                ]
            ];

            if (in_array($method, ['POST', 'PUT'])) {
                $options['form_params'] = $data;
            }

            if ($method === 'GET' && !empty($data)) {
                $options['query'] = $data;
            }

            $response = $this->client->request(
                $method,
                $this->backendApiUrl . $endpoint,
                $options
            );

            return json_decode($response->getBody()->getContents(), true);
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
