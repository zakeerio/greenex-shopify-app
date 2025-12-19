@extends('layouts.app')

@section('title', 'Orders')

@section('content')
    <section class="max-w-11xl mx-auto mt-1 px-4 sm:px-6 lg:px-8">
        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
            <form method="POST" id="ordersForm" action="{{ route('orders.process-selected') }}">
                @csrf

                <table class="w-full text-sm text-left rtl:text-right text-gray-200">
                    <thead class="text-xs whitespace-nowrap text-gray-700 uppercase bg-gray-200">
                        <tr>
                            <th scope="col" class="p-4">
                                <div class="flex items-center">
                                    <input id="checkbox-all-search" type="checkbox"
                                        class="w-4 h-4 text-blue-600 bg-white border-gray-300 rounded-sm focus:ring-blue-500">
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
                                $paymentType =
                                    $order['payment_type'] ??
                                    ($order['financial_status'] == 'paid' ? 'PREPAID' : 'COD');
                                $shipping = $order['shipping_address'] ?? [];
                                $orderData = [
                                    'id' => $order['id'],
                                    'total_price' => $order['current_total_price'],
                                    'line_items' => $lineItems,
                                    'shipping_address' => $shipping,
                                    'phone' => $shipping['phone'] ?? ($order['phone'] ?? ''),
                                    'name' => $shipping['name'] ?? '',
                                    'note' => $order['note'] ?? '',
                                    'email' => $order['contact_email'] ?? '',
                                    'order_number' => $order['order_number'] ?? '',
                                    'payment_type' => $paymentType,
                                    'pieces' => $pieces,
                                    'weight' => $weight,
                                    'can_be_processed' => $order['can_be_processed'] ?? false,
                                    'already_sent' => $order['already_sent'] ?? false,
                                    'already_fulfilled' => $order['already_fulfilled'] ?? false,
                                    'currency' => $order['currency'] ?? 'USD',
                                    'total_tax' => $order['total_tax'] ?? 0,
                                    'discount_codes' => $order['discount_codes'] ?? [],
                                    'merchant_id' => $order['merchant_id'] ?? $order['id'],
                                ];
                            @endphp

                            <tr class="bg-white border-b whitespace-nowrap text-black border-gray-400 order-row"
                                data-order-id="{{ $order['id'] }}">
                                <td class="w-4 p-4">
                                    <input type="checkbox" name="ordercheckbox[]" value="{{ $order['id'] }}"
                                        class="order-checkbox w-4 h-4 text-black bg-white border-gray-300 rounded-sm"
                                        data-order-data="{{ json_encode($orderData) }}">
                                </td>

                                <th class="px-6 py-4 font-medium text-gray-900">#{{ $order['order_number'] }}</th>

                                <td class="px-6 py-4">{{ $shipping['name'] ?? 'N/A' }}</td>
                                <td class="px-6 py-4">{{ $order['contact_email'] ?? 'N/A' }}</td>
                                <td class="px-6 py-4">{{ $shipping['phone'] ?? ($order['phone'] ?? 'N/A') }}</td>

                                <td class="px-6 py-4">
                                    <select
                                        class="collect-payment border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5"
                                        data-order-id="{{ $order['id'] }}">
                                        <option value="yes" {{ $paymentType == 'COD' ? 'selected' : '' }}>Yes</option>
                                        <option value="no" {{ $paymentType != 'COD' ? 'selected' : '' }}>No</option>
                                    </select>
                                </td>

                                <td class="px-6 py-4 price" data-price="{{ $order['current_total_price'] }}">
                                    {{ number_format($order['current_total_price'], 2) }}
                                </td>
                                <td class="px-6 py-4">{{ $pieces }}</td>
                                <td class="px-6 py-4">{{ number_format($weight, 2) }} kg</td>
                                <td class="px-6 py-4 payment-type">{{ strtoupper($paymentType) }}</td>


                                {{-- Hidden inputs for sending to backend --}}
                                <input type="hidden" class="order_id" name="order_id[]" value="{{ $order['id'] }}">
                                <input type="hidden" class="phone" name="phone"
                                    value="{{ $shipping['phone'] ?? ($order['phone'] ?? '') }}">
                                <input type="hidden" class="line_items" name="line_items[]"
                                    value="{{ json_encode($order['line_items'] ?? []) }}">
                                <input type="hidden" class="shipping_address" name="shipping_address[]"
                                    value="{{ json_encode($shipping) }}">
                                <input type="hidden" class="name" name="name[]" value="{{ $shipping['name'] ?? '' }}">
                                <input type="hidden" class="note" name="note[]" value="{{ $order['note'] ?? '' }}">
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4 text-gray-700">No orders found.</td>
                            </tr>
                        @endforelse


                    </tbody>
                </table>
                <!-- Buttons section -->
                <div class="p-4 border-t border-gray-200">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <!-- Left side: Action buttons -->
                        <div class="flex flex-wrap items-center gap-3">
                            <!-- Send Orders (Current Method) -->
                            <button id="sendOrders"
                                class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium text-sm transition-colors">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                    </svg>
                                    Send Orders (Current)
                                </span>
                            </button>

                            <!-- Process with Guzzle (New Method) -->
                            <button id="processSelected" type="button"
                                class="px-5 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:ring-4 focus:ring-green-300 font-medium text-sm transition-colors">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Process Orders (Guzzle)
                                </span>
                            </button>

                            <!-- Refresh Orders -->
                            <button onclick="loadOrders()"
                                class="px-5 py-2.5 bg-gray-600 text-white rounded-lg hover:bg-gray-700 focus:ring-4 focus:ring-gray-300 font-medium text-sm transition-colors">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    Refresh Orders
                                </span>
                            </button>
                        </div>

                        <!-- Right side: Selected count -->
                        <div class="flex items-center">
                            <span id="selectedCount"
                                class="text-sm font-medium text-gray-700 bg-gray-100 px-3 py-1.5 rounded-lg">
                                0 orders selected
                            </span>
                        </div>
                    </div>
                </div>
            </form>


        </div>

    </section>


@endsection
