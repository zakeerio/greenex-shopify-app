<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ShipmentService
{
    protected $client;
    protected $backendApiUrl;
    protected $backendApiKey;

    public function __construct()
    {
        $this->backendApiUrl = rtrim(config('app.backend_api_url'), '/');
        $this->backendApiKey = config('app.backend_api_key');

        $this->client = new Client([
            'verify' => false, // local SSL issue fix
        ]);
    }

    public function request($method, $endpoint, $token, $data = [])
    {
        try {
            $options = [
                'headers' => [
                    'apiKey' => $this->backendApiKey,
                    'Accept' => 'application/json',
                    'Authorization' => "Bearer {$token}",
                ]
            ];

            if (in_array($method, ['POST', 'PUT'])) {
                $options['form_params'] = $data;
            }

            if ($method === 'GET' && !empty($data)) {
                $options['query'] = $data;
            }

            $url = str_starts_with($endpoint, 'http')
                ? $endpoint
                : $this->backendApiUrl . '/' . ltrim($endpoint, '/');

            $response = $this->client->request(
                $method,
                $url,
                $options
            );

            return json_decode($response->getBody()->getContents(), true);
        } catch (\Throwable $e) {
            Log::error('Shipment Service Request Error: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getShipments($token, $params = [])
    {
        return $this->request('GET', '/parcel/index', $token, $params);
    }
}
