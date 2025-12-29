@extends('layouts.app')

@section('title', 'Shipments')

@section('content')
    {{-- DataTables & jQuery --}}
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.tailwindcss.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- DataTables Tailwind CSS --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.tailwindcss.css">

    <style>
        /* Shopify-like DataTables Customization */
        .dataTables_wrapper .dataTables_length select {
            padding-right: 2.5rem;
            border-color: #e5e7eb;
            border-radius: 0.375rem;
        }

        .dataTables_wrapper .dataTables_filter input {
            border-color: #e5e7eb;
            border-radius: 0.375rem;
            padding: 0.5rem;
        }

        table.dataTable.dtr-inline.collapsed>tbody>tr>td.dtr-control:before {
            background-color: #008060;
        }

        /* Hide default tailwind datatables overrides that might conflict */
        div.dt-container div.dt-layout-row {
            margin: 1rem 0;
        }
    </style>

    <section class="max-w-11xl mx-auto mt-4 px-4 sm:px-6 lg:px-8">
        @csrf
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-xl font-bold text-gray-800">Shipments</h1>
            <button type="button" id="printBtn"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 shadow-sm transition-colors">
                Print Selected
            </button>
        </div>

        <div class="bg-white p-4 rounded-lg shadow border border-gray-200">
            <table id="shipmentsTable" class="w-full text-sm text-left text-gray-500 hover:text-gray-700">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 no-sort w-4">
                            <input type="checkbox" id="selectAll"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        </th>
                        <th class="px-6 py-3">ID</th>
                        <th class="px-6 py-3">Tracking ID</th>
                        <th class="px-6 py-3">Customer</th>
                        <th class="px-6 py-3">Phone</th>
                        <th class="px-6 py-3">COD</th>
                        <th class="px-6 py-3">Weight</th>
                        <th class="px-6 py-3">Invoice</th>
                        <th class="px-6 py-3">Address</th>
                        <th class="px-6 py-3">Type</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($shipments as $shipment)
                        <tr class="bg-white border-b hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <input type="checkbox" name="selected_shipments[]" value="{{ $shipment['id'] }}"
                                    class="shipment-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900">#{{ $shipment['id'] }}</td>

                            <td class="px-6 py-4 font-medium text-blue-600 hover:underline cursor-pointer">
                                {{ $shipment['tracking_id'] }}
                            </td>

                            <td class="px-6 py-4 text-gray-900">
                                {{ $shipment['customer_name'] }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ $shipment['customer_phone'] }}
                            </td>

                            <td class="px-6 py-4 font-semibold text-gray-900">
                                Rs {{ number_format($shipment['cod_amount']) }}
                            </td>

                            <td class="px-6 py-4">
                                {{ $shipment['weight'] }}
                            </td>

                            <td class="px-6 py-4">
                                {{ $shipment['invoice_no'] }}
                            </td>

                            <td class="px-6 py-4 max-w-xs truncate" title="{{ $shipment['customer_address'] }}">
                                {{ Str::limit($shipment['customer_address'], 30) }}
                            </td>

                            <td class="px-6 py-4">
                                {{ $shipment['deliveryType'] }}
                            </td>

                            @php
                                $statusColors = [
                                    1 => 'bg-red-100 text-red-800', // Pending
                                    2 => 'bg-yellow-100 text-yellow-800', // Pickup Assign
                                    3 => 'bg-green-100 text-green-800', // Delivered
                                ];
                            @endphp

                            <td class="px-6 py-4">
                                <span
                                    class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$shipment['status']] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $shipment['statusName'] }}
                                </span>
                            </td>

                            <td class="px-6 py-4 flex space-x-3">
                                {{-- <a href="{{ route('parcel.details', $shipment['id']) }}"
                                        class="text-blue-600 hover:underline">View</a>

                                    <a href="{{ route('parcel.edit', $shipment['id']) }}"
                                        class="text-green-600 hover:underline">Edit</a> --}}
                                ---

                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            var table = $('#shipmentsTable').DataTable({
                responsive: true,
                "columnDefs": [{
                    "targets": 0, // First column (checkboxes)
                    "orderable": true
                }],
                language: {
                    search: "",
                    searchPlaceholder: "Search shipments...",
                    lengthMenu: "Show _MENU_ entries"
                },
                "dom": '<"flex justify-between items-center mb-4"lf>rt<"flex justify-between items-center mt-4"ip>',
            });

            // Select All Logic (Works across all pages)
            $('#selectAll').on('click', function() {
                var rows = table.rows({
                    'search': 'applied'
                }).nodes();
                $('input[type="checkbox"]', rows).prop('checked', this.checked);
            });

            // Handle Print with DataTables
            $('#printBtn').on('click', function() {
                var selected = [];
                // Use table.$() to find checked inputs in all rows (including those not in DOM/pagination)
                table.$('input[type="checkbox"]:checked').each(function() {
                    selected.push($(this).val());
                });

                if (selected.length === 0) {
                    alert('Please select at least one shipment to print.');
                    return;
                }

                submitWithToken(selected);
            });

            // Print Functionality (unchanged logic, refactored for clarity)
            function submitWithToken(selectedIds) {
                // Try App Bridge first
                if (window.app && window['app-bridge'] && window['app-bridge'].utilities) {
                    var getSessionToken = window['app-bridge'].utilities.getSessionToken;
                    getSessionToken(window.app).then(function(token) {
                        createAndSubmitForm(token, selectedIds);
                    }).catch(function(err) {
                        console.error('Error fetching session token:', err);
                        createAndSubmitForm(null, selectedIds);
                    });
                } else {
                    console.warn('App Bridge not found, submitting with existing params');
                    createAndSubmitForm(null, selectedIds);
                }
            }

            function createAndSubmitForm(token, selectedIds) {
                var form = document.createElement('form');
                form.method = 'POST';
                var actionUrl = "{{ route('shipments.print') }}";
                var searchParams = new URLSearchParams(window.location.search);

                // Clean auth params
                ['hmac', 'signature', 'timestamp', 'id_token', 'token'].forEach(p => searchParams.delete(p));

                if (token) searchParams.set('token', token);
                actionUrl += '?' + searchParams.toString();

                form.action = actionUrl;
                form.target = '_blank';

                var csrfToken = "{{ csrf_token() }}";
                var csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken;
                form.appendChild(csrfInput);

                selectedIds.forEach(function(id) {
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
        });
    </script>
@endsection
