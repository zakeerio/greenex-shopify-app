@extends('layouts.app')

@section('title', 'Orders')

@section('content')
<section class="max-w-11xl mx-auto mt-1 px-4 sm:px-6 lg:px-8">
  <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
    <table class="w-full text-sm text-left rtl:text-right text-gray-200">
    <thead class="text-xs whitespace-nowrap text-gray-700 uppercase bg-gray-200">
        <tr>
            <th scope="col" class="p-4">
                <div class="flex items-center">
                    <input id="checkbox-all-search" type="checkbox" class="w-4 h-4 text-blue-600 bg-white border-gray-300 rounded-sm focus:ring-blue-500">
                </div>
            </th>
            <th class="px-6 py-3">ID</th>
            <th class="px-6 py-3">Name</th>
            <th class="px-6 py-3">Email</th>
            <th class="px-6 py-3">Phone</th>
            <th class="px-6 py-3">Collect Payment</th>
            <th class="px-6 py-3">Amount</th>
            <th class="px-6 py-3">Pieces</th>
            <th class="px-6 py-3">Weight</th>
            <th class="px-6 py-3">Payment Type</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($orders as $order)
            @php
                $lineItems = $order['line_items'] ?? [];
                $pieces = array_sum(array_column($lineItems, 'quantity'));
                $weight = array_sum(array_column($lineItems, 'grams')) / 1000; // grams → KG
                $paymentType = $order['financial_status'] == 'paid' ? 'Prepaid' : 'COD';
                $shipping = $order['shipping_address'] ?? [];
            @endphp

            <tr class="bg-white border-b whitespace-nowrap text-black border-gray-400">
                <td class="w-4 p-4">
                    <input type="checkbox" class="order-checkbox w-4 h-4 text-black bg-white border-gray-300 rounded-sm" data-order-id="{{ $order['id'] }}">
                </td>

                <th class="px-6 py-4 font-medium text-gray-900">#{{ $order['order_number'] }}</th>

                <td class="px-6 py-4">{{ $shipping['name'] ?? 'N/A' }}</td>
                <td class="px-6 py-4">{{ $order['contact_email'] ?? 'N/A' }}</td>
                <td class="px-6 py-4">{{ $shipping['phone'] ?? $order['phone'] ?? 'N/A' }}</td>

                <td class="px-6 py-4">
                    <select class="border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5">
                        <option value="yes" {{ $paymentType == 'COD' ? 'selected' : '' }}>Yes</option>
                        <option value="no" {{ $paymentType != 'COD' ? 'selected' : '' }}>No</option>
                    </select>
                </td>

                <td class="px-6 py-4 price-input">{{ number_format($order['current_total_price'], 2) }}</td>
                <td class="px-6 py-4">{{ $pieces }}</td>
                <td class="px-6 py-4">{{ $weight }} kg</td>
                <td class="px-6 py-4">{{ strtoupper($paymentType) }}</td>

                {{-- Hidden inputs for sending to backend --}}
                <input type="hidden" class="merchant-id" value="{{ $order['merchant_id'] ?? '' }}">
                <input type="hidden" class="line-items" value="{{ json_encode($order['line_items'] ?? []) }}">
                <input type="hidden" class="shipping-address" value="{{ json_encode($shipping) }}">
                <input type="hidden" class="name" value="{{ $shipping['name'] ?? '' }}">
                <input type="hidden" class="note" value="{{ $order['note'] ?? '' }}">
            </tr>
        @empty
            <tr>
                <td colspan="10" class="text-center py-4 text-gray-700">No orders found.</td>
            </tr>
        @endforelse
    </tbody>
</table>

{{-- Button to send selected orders to backend --}}
<button id="sendOrders" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded">Send Selected Orders</button>

  </div>
</section>
@endsection
