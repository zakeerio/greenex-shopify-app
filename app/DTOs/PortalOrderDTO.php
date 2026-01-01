<?php

namespace App\DTOs;

class PortalOrderDTO
{
    public function __construct(
        public array $orderData,
        public array $shopSettings
    ) {}

    public static function fromArray(array $data): self
    {
        return new self($data['order_data'], $data['shop_settings']);
    }

    public function toArray(): array
    {
        return [
            'merchant_order_id' => $this->orderData['id'] ?? null,
            'order_number' => $this->orderData['order_number'],
            'shop_id' => $this->shopSettings['id'],
            'shop_name' => $this->shopSettings['name'],
            'customer' => [
                'name' => $this->orderData['customer']['first_name'] . ' ' . $this->orderData['customer']['last_name'],
                'email' => $this->orderData['customer']['email'] ?? '',
                'phone' => $this->orderData['customer']['phone'] ?? '',
            ],
            'shipping_address' => $this->orderData['shipping_address'] ?? [],
            'line_items' => $this->orderData['line_items'] ?? [],
            'total_price' => $this->orderData['total_price'],
            'payment_type' => $this->orderData['financial_status'] === 'paid' ? 'prepaid' : 'cod',
        ];
    }
}
