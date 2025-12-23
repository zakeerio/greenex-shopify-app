@extends('layouts.app')

@section('title', 'Shipments')

@section('content')
    <section class="max-w-11xl mx-auto mt-1 px-4 sm:px-6 lg:px-8">
        {{-- <form action="{{ route('shipments.print') }}" method="POST" target="_blank"> --}}
        @csrf
        <div class="flex justify-end mb-4">
            <button type="button" id="printBtn" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Print
                Selected</button>
        </div>
        <div class="relative overflow-x-auto shadow-md sm:rounded-lg bg-white">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-200">
                    <tr>
                        <th class="px-6 py-3">
                            <input type="checkbox" id="selectAll">
                        </th>
                        <th class="px-6 py-3">ID</th>
                        <th class="px-6 py-3">Tracking ID</th>
                        <th class="px-6 py-3">Customer</th>
                        <th class="px-6 py-3">Phone</th>
                        <th class="px-6 py-3">COD Amount</th>
                        <th class="px-6 py-3">Weight</th>
                        <th class="px-6 py-3">Invoice</th>
                        <th class="px-6 py-3">Ship Address</th>
                        <th class="px-6 py-3">Delivery Type</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($shipments as $shipment)
                        {{-- @dd($shipment) --}}
                        <tr class="bg-white border-b hover:bg-gray-100">

                            <td class="px-6 py-4">
                                <input type="checkbox" name="selected_shipments[]" value="{{ $shipment['id'] }}"
                                    class="shipment-checkbox">
                            </td>
                            <td class="px-6 py-4">#{{ $shipment['id'] }}</td>

                            <td class="px-6 py-4 font-medium text-blue-600">
                                {{ $shipment['tracking_id'] }}
                            </td>

                            <td class="px-6 py-4">
                                {{ $shipment['customer_name'] }}
                            </td>

                            <td class="px-6 py-4">
                                {{ $shipment['customer_phone'] }}
                            </td>

                            <td class="px-6 py-4 font-semibold">
                                Rs {{ number_format($shipment['cod_amount']) }}
                            </td>

                            <td class="px-6 py-4">
                                {{ $shipment['weight'] }}
                            </td>

                            <td class="px-6 py-4">
                                {{ $shipment['invoice_no'] }}
                            </td>

                            <td class="px-6 py-4 max-w-xs truncate">
                                {{ $shipment['customer_address'] }}
                            </td>

                            <td class="px-6 py-4">
                                {{ $shipment['deliveryType'] }}
                            </td>

                            @php
                                $statusColors = [
                                    1 => 'bg-red-100 text-red-700', // Pending
                                    2 => 'bg-yellow-100 text-yellow-700', // Pickup Assign
                                    3 => 'bg-green-100 text-green-700', // Delivered
                                ];
                            @endphp

                            <td class="px-6 py-4">
                                <span
                                    class="px-2 py-1 text-xs rounded-full {{ $statusColors[$shipment['status']] ?? 'bg-gray-100 text-gray-700' }}">
                                    {{ $shipment['statusName'] }}
                                </span>
                            </td>

                            <td class="px-6 py-4 flex space-x-3">
                                {{-- <a href="{{ route('parcel.details', $shipment['id']) }}"
                                        class="text-blue-600 hover:underline">View</a>

                                    <a href="{{ route('parcel.edit', $shipment['id']) }}"
                                        class="text-green-600 hover:underline">Edit</a> --}}
                                <span class="text-gray-400">Disabled</span>
                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{-- </form> --}}
    </section>

    <script>
        document.getElementById('selectAll').addEventListener('change', function() {
            var checkboxes = document.querySelectorAll('.shipment-checkbox');
            for (var checkbox of checkboxes) {
                checkbox.checked = this.checked;
            }
        });

        document.getElementById('printBtn').addEventListener('click', function() {
            var selected = [];
            document.querySelectorAll('.shipment-checkbox:checked').forEach(function(checkbox) {
                selected.push(checkbox.value);
            });

            if (selected.length === 0) {
                alert('Please select at least one shipment to print.');
                return;
            }

            function submitWithToken(token) {
                var form = document.createElement('form');
                form.method = 'POST';

                var actionUrl = "{{ route('shipments.print') }}";
                var searchParams = new URLSearchParams(window.location.search);

                // Remove legacy auth params to prevent hmac verification mismatch
                searchParams.delete('hmac');
                searchParams.delete('signature');
                searchParams.delete('timestamp');
                // The new token replaces id_token/token
                searchParams.delete('id_token');
                searchParams.delete('token');

                if (token) {
                    searchParams.set('token', token);
                }

                actionUrl += '?' + searchParams.toString();

                form.action = actionUrl;
                form.target = '_blank';

                var csrfToken = document.querySelector('input[name="_token"]').value;
                var csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken;
                form.appendChild(csrfInput);

                selected.forEach(function(id) {
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'selected_shipments[]';
                    input.value = id;
                    form.appendChild(input);
                });

                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
            }

            // Try to get fresh token via App Bridge
            if (window.app && window['app-bridge'] && window['app-bridge'].utilities) {
                var getSessionToken = window['app-bridge'].utilities.getSessionToken;
                getSessionToken(window.app).then(function(token) {
                    submitWithToken(token);
                }).catch(function(err) {
                    console.error('Error fetching session token:', err);
                    submitWithToken(null);
                });
            } else {
                console.warn('App Bridge not found, submitting with existing params');
                submitWithToken(null);
            }
        });
    </script>
@endsection
