@extends('layouts.app')

@section('title', 'Sent Orders')

@section('content')
    <section class="max-w-11xl mx-auto mt-1 px-4 sm:px-6 lg:px-8">
        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
            <table class="w-full text-sm text-left rtl:text-right text-gray-200">
                <thead class="text-xs whitespace-nowrap text-gray-700 uppercase bg-gray-200">
                    <tr>
                        <th class="px-6 py-3">Order Number</th>
                        <th class="px-6 py-3">Tracking ID</th>
                        <th class="px-6 py-3">Sent At</th>
                        <th class="px-6 py-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sentOrders as $order)
                        <tr class="bg-white border-b whitespace-nowrap text-black border-gray-400">
                            <td class="px-6 py-4">{{ $order->order_number }}</td>
                            <td class="px-6 py-4">{{ $order->tracking_id }}</td>
                            <td class="px-6 py-4">{{ $order->sent_at ? $order->sent_at->format('Y-m-d H:i') : 'N/A' }}</td>
                            <td class="px-6 py-4">{{ $order->status }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-gray-700">No sent orders found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-4">
                {{ $sentOrders->links() }}
            </div>
        </div>
    </section>
@endsection
