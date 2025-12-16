@extends('layouts.app')

@section('title', 'Shipments')

@section('content')
<section class="max-w-11xl mx-auto mt-1 px-4 sm:px-6 lg:px-8">
  <div class="relative overflow-x-auto shadow-md sm:rounded-lg bg-white">
    <table class="w-full text-sm text-left text-gray-500">
      <thead class="text-xs text-gray-700 uppercase bg-gray-200">
             {{-- <tr>
              <th scope="col" class="px-6 py-3">
                ID
              </th>
              <th scope="col" class="px-6 py-3">
                NAME
              </th>
              <th scope="col" class="px-6 py-3">
                Email
              </th>
              <th scope="col" class="px-6 py-3">
                Phone
              </th>
              <th scope="col" class="px-6 py-3">
                Amount
              </th>
              <th scope="col" class="px-6 py-3">
                Pieces
              </th>
              <th scope="col" class="px-6 py-3">
                Weight
              </th>
              <th scope="col" class="px-6 py-3">
                ACCNO
              </th>
              <th scope="col" class="px-6 py-3">
                Ship Address
              </th>
              <th scope="col" class="px-6 py-3">
                Ship City
              </th>
              <th scope="col" class="px-6 py-3">
                REMARKS
              </th>
              <th scope="col" class="px-6 py-3">
                Products
              </th>
              <th scope="col" class="px-6 py-3">
                Status
              </th>
              <th scope="col" class="px-6 py-3">
                Actions
              </th>

            </tr>

      </thead>
      --}}
            <thead class="text-xs text-gray-700 uppercase bg-gray-200">
                <tr>
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
                    1 => 'bg-red-100 text-red-700',     // Pending
                    2 => 'bg-yellow-100 text-yellow-700', // Pickup Assign
                    3 => 'bg-green-100 text-green-700', // Delivered
                ];
                @endphp

                <td class="px-6 py-4">
                    <span class="px-2 py-1 text-xs rounded-full {{ $statusColors[$shipment['status']] ?? 'bg-gray-100 text-gray-700' }}">
                        {{ $shipment['statusName'] }}
                    </span>
                </td>

                <td class="px-6 py-4 flex space-x-3">
                    <a href="{{ route('parcel.details', $shipment['id']) }}"
                    class="text-blue-600 hover:underline">View</a>

                    <a href="{{ route('parcel.edit', $shipment['id']) }}"
                    class="text-green-600 hover:underline">Edit</a>
                </td>

            </tr>
            @endforeach

         {{-- <tr class="bg-white whitespace-nowrap border-b border-gray-200 hover:bg-gray-100">

              <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap ">
                #12345
              </th>
              <td class=" px-6 py-4">
                jONNY DOE
              </td>
              <td class="px-6 py-4">
                jonny@mail.com
              </td>
              <td class="px-6 py-4">
                0312-4567890
              </td>

              <td class="px-6 py-4">
                $2999
              </td>
              <td class="px-6 py-4">
                3
              </td>
              <td class="px-6 py-4">
                0.5kg
              </td>
              <td class="px-6 py-4">
                KHI-0000
              </td>
              <td class="px-6 py-4">
                123 Street, City
              </td>
              <td class="px-6 py-4">
                Karachi
              </td>
              <td class="px-6 py-4">
                Handle with care
              </td>
              <td class="px-6 py-4">
                Electronics, Clothes
              </td>
              <td class="px-6 py-4">
                In Transit
              </td>
              <td class="flex items-center px-6 py-4">
                <a href="#" class="font-medium text-blue-600  hover:underline">Edit</a>
                <a href="#" class="font-medium text-red-600 hover:underline ms-3">Remove</a>
              </td>

            </tr> --}}


      </tbody>
    </table>
  </div>
</section>
@endsection
