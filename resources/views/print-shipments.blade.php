<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <!-- Add QR Code library -->
    <script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
    <style>
        body {
            margin: 0;
            padding: 0;
        }

        .main-table {
            border: 1px solid #000;
            width: 100%;
            border-collapse: collapse;
            /* margin-bottom: 5px; */
        }

        .label-section {
            width: 19cm;
            min-height: 12.85cm;
            /* height: 12.85cm; */
            margin-bottom: 5mm;
            page-break-after: always;
        }

        @media print {
            .label-size {
                display: none;
            }

            * {
                margin: 0 !important;
                padding: 0 !important;
                font-size: 14px !important;
            }

            td {
                padding: 4px !important;
            }

            .label-section {
                height: auto !important;
                /* page-break-inside: avoid !important; */
                page-break-after: always !important;
                /* Force new page for each label */
                page-break-before: avoid !important;
            }

            @page {
                size: 21cm 14.85cm;
                margin: 1cm !important;
            }

            body {
                zoom: 98%;
            }

            table {
                page-break-inside: avoid !important;
            }

            tr {
                page-break-inside: avoid !important;
            }

            .no-break {
                page-break-inside: avoid !important;
            }
        }
    </style>
</head>

<body>
    @foreach ($shipments as $index => $parcel)
    <div class="label-section">
        <div class="label-size" style="margin-bottom: 10px">
            <span style="color:red">Label size: 19cm X 12.85cm </span>
        </div>

        <table width="100%" border="0" cellpadding="0" cellspacing="0" class="main-table no-break">
            <tbody class="no-break">
                <!-- Top section -->
                <tr class="no-break">
                    <td colspan="3" width="100%" style="padding: 10px;">
                        <!-- Merchant info -->
                        <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0"
                            class="no-break">
                            <tbody>
                                <tr>
                                    <td
                                        style="width:200px; border-right: 1px solid; padding:10px; vertical-align: top;">
                                        {{-- <img alt="Logo" src="{{ settings()->logo_image ?? '' }}" class="logo"
                                        style="width:150px"> --}}
                                        <img src="{{ asset('images/logo.jpg') }}" alt="GreenEx Logo" style="width: 100px; max-width: 100px;">
                                    </td>
                                    <td style="padding-left: 15px; width:100%; vertical-align: top;">
                                        <h3 style="font-size: 18px; margin-bottom: 5px;">{{ __('Merchant:') }}
                                            {{ $parcel['merchant']['business_name'] ?? ($parcel['merchant_name'] ?? 'N/A') }}
                                        </h3>
                                        <div style="font-size: 14px;">
                                            {{ $parcel['merchant']['address'] ?? ($parcel['merchant_address'] ?? '') }}
                                        </div>
                                        <div style="font-size: 14px;">
                                            {{ $parcel['merchant']['user']['mobile'] ?? ($parcel['merchant_phone'] ?? '') }}
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Customer and shipping info -->
                        <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0"
                            style="margin-top: 15px;" class="no-break">
                            <tbody>
                                <tr>
                                    <td
                                        style="line-height:1.5; width:60%; border-right: 1px solid; border-top:#000000 1px solid; border-bottom:#000000 1px solid; padding:10px; vertical-align: top;">
                                        <h3 style="margin-bottom: 5px; font-size: 16px;">
                                            {{ __('Customer Information:') }}
                                        </h3>
                                        <div style="font-size: 14px;"><strong>Name:</strong>
                                            {{ $parcel['customer_name'] }}
                                        </div>
                                        <div style="font-size: 14px;"><strong>Phone:</strong>
                                            {{ $parcel['customer_phone'] }}
                                        </div>
                                        <div style="font-size: 14px;"><strong>Address:</strong>
                                            {{ $parcel['customer_address'] }}
                                        </div>
                                    </td>
                                    <td
                                        style="line-height:1.5; width:40%; border-top:#000000 1px solid; border-bottom:#000000 1px solid; padding:10px; vertical-align: top;">
                                        <div style="font-size: 14px;">
                                            <h3 style="margin-bottom: 5px; font-size: 16px;">Shipping Details:</h3>
                                            <div style="font-size: 14px;"><strong>Hub:</strong>
                                                {{ $parcel['hub']['name'] ?? ($parcel['hub_name'] ?? ($hubName ?? 'N/A')) }}
                                            </div>
                                            <div style="font-size: 14px;"><strong>Cash Collection:</strong>
                                                {{ $parcel['cash_collection'] ?? ($parcel['cod_amount'] ?? 0) }}
                                            </div>
                                            <div style="font-size: 14px;"><strong>Route:</strong> ISD</div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>

                <!-- Code section - COMPLETELY REWRITTEN -->
                <tr class="no-break">
                    <td class="no-break" style="text-align:center; padding:10px; border-top: none;">
                        <table width="100%" border="0" cellpadding="0" cellspacing="0" class="no-break">
                            <tr>
                                <td style="width:50%; text-align:left; vertical-align:middle; padding:10px;">
                                    <!-- Only track ID and new barcode image - no original barcode -->
                                    <div style="font-weight:bold; font-size: 16px; margin-bottom:10px;">
                                        {{ $parcel['tracking_id'] }}
                                    </div>
                                    <img id="barcode-img-{{ $index }}" style="width:250px; height:80px;">
                                </td>
                                <td style="width:50%; text-align:right; vertical-align:middle; padding:10px;">
                                    <!-- QR code will be inserted here -->
                                    <div id="qrcode-{{ $index }}"
                                        style="display: inline-block; margin:auto; width:150px; height:150px;">
                                    </div>
                                    <div style="margin-top:10px; font-size: 14px;">Scan to track</div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <script>
        (function() {
            var trackingId = "{{ $parcel['tracking_id'] }}";
            var index = "{{ $index }}";

            // Generate QR code
            try {
                var qr = qrcode(0, 'M');
                qr.addData(trackingId);
                qr.make();
                document.getElementById('qrcode-' + index).innerHTML = qr.createImgTag(5);
            } catch (e) {
                console.error('QR Gen Error', e);
            }

            // Create a barcode
            document.getElementById('barcode-img-' + index).src = "https://barcodeapi.org/api/code128/" + trackingId;
        })
        ();
    </script>
    @endforeach

    <script>
        // Auto print after a delay to ensure everything renders
        setTimeout(function() {
            window.print();
        }, 1500);
    </script>
</body>

</html>