@extends('layouts.app')
@section('title', 'Shipments')
@section('content')

{{-- DataTables & jQuery --}}
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.tailwindcss.min.js"></script>
<script src="https://cdn.tailwindcss.com"></script>

{{-- DataTables CSS --}}
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.tailwindcss.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

<style>
    /* 1. Shopify-like Input Styles */
    .dataTables_wrapper .dataTables_length select {
        padding-right: 2rem;
        border-color: #e5e7eb;
        border-radius: 0.375rem;
        min-width: 80px;
    }

    .dataTables_wrapper .dataTables_filter input {
        border-color: #e5e7eb;
        border-radius: 0.375rem;
        padding: 0.5rem;
        margin-left: 0.5rem;
    }

    /* 2. Table Layout Fixes */
    table.dataTable {
        width: 100% !important;
        margin-top: 1rem;
        border-collapse: collapse !important;
    }

    /* 3. Child Row Plus Icon Styling */
    table.dataTable.dtr-inline.collapsed>tbody>tr>td.dtr-control {
        position: relative;
        padding-left: 40px !important;
    }

    table.dataTable.dtr-inline.collapsed>tbody>tr>td.dtr-control:before {
        background-color: #008060;
        top: 50%;
        left: 10px;
        transform: translateY(-50%);
        height: 16px;
        width: 16px;
        line-height: 16px;
        border-radius: 50%;
        display: block;
        position: absolute;
        color: white;
        text-align: center;
        content: '+';
        font-family: 'Courier New', Courier, monospace;
        box-shadow: none;
        border: none;
    }

    /* Minus Icon when Open */
    table.dataTable.dtr-inline.collapsed>tbody>tr.parent>td.dtr-control:before {
        background-color: #d82c0d;
        content: '-';
    }

    /* 4. Child Row Content Styling (Jo + click karne pe text ata h) */
    table.dataTable>tbody>tr.child ul.dtr-details {
        width: 100%;
    }

    table.dataTable>tbody>tr.child span.dtr-title {
        font-weight: 600;
        min-width: 120px;
        display: inline-block;
        color: #374151;
        /* Gray-700 */
    }
</style>

<section class="w-full max-w-[95%] mx-auto mt-4 px-4 sm:px-6 lg:px-8">
    @csrf

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <h1 class="text-xl font-bold text-gray-800">Shipments</h1>
        <button type="button" id="printBtn"
            class="w-full sm:w-auto bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 shadow-sm transition-colors">
            Print Selected
        </button>
    </div>

    <div class="bg-white p-4 rounded-lg shadow border border-gray-200">
        <table id="shipmentsTable" class="w-full text-sm text-left text-gray-500 hover:text-gray-700 nowrap display" width="100%">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 no-sort w-4">
                        <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    </th>
                    <th class="px-6 py-3">ID</th>
                    <th class="px-6 py-3">Tracking ID</th>
                    <th class="px-6 py-3">Customer</th>
                    <th class="px-6 py-3">Phone</th>
                    <th class="px-6 py-3">COD</th>
                    <th class="px-6 py-3">Weight</th>
                    <th class="px-6 py-3">Invoice</th>

                    {{-- CHANGE: Added class 'none' to force Address into child row --}}
                    <th class="px-6 py-3 none">Address</th>

                    {{-- CHANGE: Added class 'none' to force Type into child row --}}
                    <th class="px-6 py-3 none">Type</th>

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

                    <td class="px-6 py-4">
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

                    {{-- Ye ab + icon k andar dikhega --}}
                    <td class="px-6 py-4">
                        {{ $shipment['customer_address'] }}
                    </td>

                    {{-- Ye bhi ab + icon k andar dikhega --}}
                    <td class="px-6 py-4">
                        {{ $shipment['deliveryType'] }}
                    </td>

                    @php
                    $statusColors = [
                    1 => 'bg-red-100 text-red-800',
                    2 => 'bg-yellow-100 text-yellow-800',
                    3 => 'bg-green-100 text-green-800',
                    ];
                    @endphp

                    <td class="px-6 py-4">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$shipment['status']] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ $shipment['statusName'] }}
                        </span>
                    </td>

                    <td class="px-6 py-4 flex space-x-3">
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
        var table = $('#shipmentsTable').DataTable({
            responsive: true,
            autoWidth: false,
            "columnDefs": [{
                    "targets": 0, // Checkbox column
                    "orderable": false,
                    "className": 'dtr-control'
                },
                {
                    "targets": 2, // Tracking ID Priority High
                    "responsivePriority": 1
                },
                {
                    "targets": -1, // Actions Priority High
                    "orderable": false,
                    "responsivePriority": 2
                }
                // Note: Address or Type k liye JS likhne ki zarurat nahi, 
                // HTML class 'none' apna kaam karegi.
            ],
            language: {
                search: "",
                searchPlaceholder: "Search...",
                lengthMenu: "Show _MENU_"
            },
            "dom": '<"flex flex-col sm:flex-row justify-between items-center mb-4 gap-4"lf>rt<"flex flex-col sm:flex-row justify-between items-center mt-4 gap-4"ip>',
        });

        // Select All Logic
        $('#selectAll').on('click', function(e) {
            e.stopPropagation();
            var rows = table.rows({
                'search': 'applied'
            }).nodes();
            $('input[type="checkbox"]', rows).prop('checked', this.checked);
        });

        // Individual Checkbox Click Logic
        $('#shipmentsTable tbody').on('click', 'input[type="checkbox"]', function(e) {
            e.stopPropagation();
        });

        // Print Logic
        $('#printBtn').on('click', function() {
            var selected = [];
            table.$('input[type="checkbox"]:checked').each(function() {
                selected.push($(this).val());
            });

            if (selected.length === 0) {
                alert('Please select at least one shipment to print.');
                return;
            }
            submitWithToken(selected);
        });

        // Form Submit
        function submitWithToken(selectedIds) {
            if (window.app && window['app-bridge'] && window['app-bridge'].utilities) {
                var getSessionToken = window['app-bridge'].utilities.getSessionToken;
                getSessionToken(window.app).then(function(token) {
                    createAndSubmitForm(token, selectedIds);
                }).catch(function(err) {
                    createAndSubmitForm(null, selectedIds);
                });
            } else {
                createAndSubmitForm(null, selectedIds);
            }
        }

        function createAndSubmitForm(token, selectedIds) {
            var form = document.createElement('form');
            form.method = 'POST';
            var actionUrl = "{{ route('shipments.print') }}";
            var searchParams = new URLSearchParams(window.location.search);
            ['hmac', 'signature', 'timestamp', 'id_token', 'token'].forEach(p => searchParams.delete(p));
            if (token) searchParams.set('token', token);
            actionUrl += '?' + searchParams.toString();
            form.action = actionUrl;
            form.target = '_blank';
            var csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = "{{ csrf_token() }}";
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