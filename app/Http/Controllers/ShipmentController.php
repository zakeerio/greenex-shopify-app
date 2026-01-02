<?php

namespace App\Http\Controllers;

use App\Models\ShopSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\ShipmentService;

class ShipmentController extends Controller
{
    protected $shipmentService;
    protected $token;

    public function __construct(ShipmentService $shipmentService)
    {
        $this->shipmentService = $shipmentService;
    }

    private function getToken()
    {
        $userId = Auth::id();
        $setting = ShopSetting::where('user_id', $userId)->first();
        return $setting?->api_token;
    }

    /* ===============================
       📦 LIST SHIPMENTS
    =============================== */
    public function index()
    {
        $response = $this->shipmentService->getShipments($this->getToken());
        $shipments = $response['data']['parcels'] ?? $response['data'] ?? [];

        return view('shipments', compact('shipments'));
    }

    /* ===============================
       🖨️ BULK PRINT
    =============================== */
    public function bulkPrint(Request $request)
    {
        if ($request->isMethod('get')) {
            return redirect()->route('shipments');
        }

        $selectedIds = $request->input('selected_shipments', []);

        if (empty($selectedIds)) {
            return redirect()->back()->with('error', 'No shipments selected.');
        }

        $response = $this->shipmentService->getShipments($this->getToken());
        $allShipments = $response['data']['parcels'] ?? $response['data'] ?? [];

        $shipments = array_filter($allShipments, function ($shipment) use ($selectedIds) {
            return in_array($shipment['id'], $selectedIds);
        });

        // Fetch Hub Name from Shop Settings
        $userId = Auth::id();
        $shopSetting = ShopSetting::where('user_id', $userId)->first();
        // api_response is the user object directly. Fallback to 'address' if 'hub.name' is missing.
        $hubName = $shopSetting->api_response['hub']['name'] ?? ($shopSetting->api_response['address'] ?? 'N/A');

        return view('print-shipments', compact('shipments', 'hubName'));
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
        $response = $this->shipmentService->request('POST', '/parcel/store', $this->getToken(), $request->all());
        return response()->json($response);
    }
    /* ===============================
       🔍 DETAILS
    =============================== */
    public function details($id)
    {
        $response = $this->shipmentService->request('GET', "/parcel/details/{$id}", $this->getToken());
        $shipment = $response['data'] ?? null;
        return view('shipments.details', compact('shipment'));
    }

    /* ===============================
       ✏️ EDIT
    =============================== */
    public function edit($id)
    {
        $response = $this->shipmentService->request('GET', "/parcel/details/{$id}", $this->getToken());
        $shipment = $response['data'] ?? null;
        return view('shipments.edit', compact('shipment'));
    }

    /* ===============================
       🔄 UPDATE
    =============================== */
    public function update(Request $request, $id)
    {
        $response = $this->shipmentService->request('PUT', "/parcel/update/{$id}", $this->getToken(), $request->all());
        return response()->json($response);
    }

    /* ===============================
       📜 LOGS
    =============================== */
    public function logs($id)
    {
        $response = $this->shipmentService->request('GET', "/parcel/logs/{$id}", $this->getToken());
        $logs = $response['data'] ?? [];
        return view('shipments.logs', compact('logs'));
    }

    /* ===============================
       🔎 FILTER
    =============================== */
    public function filter(Request $request)
    {
        $response = $this->shipmentService->request('GET', '/parcel/filter', $this->getToken(), $request->query());
        $shipments = $response['data'] ?? [];
        return view('shipments.index', compact('shipments'));
    }

    /* ===============================
       🔄 UPDATE STATUS
    =============================== */
    public function updateStatus($id, $statusId)
    {
        $this->shipmentService->request('PUT', "/parcel/{$id}/status/{$statusId}", $this->getToken());
        return redirect()->back()->with('success', 'Status Updated');
    }

    /* ===============================
       ❌ DELETE
    =============================== */
    public function destroy($id)
    {
        $response = $this->shipmentService->request('DELETE', "/parcel/delete/{$id}", $this->getToken());
        return response()->json($response);
    }
}
