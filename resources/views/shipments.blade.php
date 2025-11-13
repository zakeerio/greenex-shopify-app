@extends('layouts.app')

@section('title', 'Shipments')

@section('content')
<section class="max-w-11xl mx-auto mt-1 px-4 sm:px-6 lg:px-8">
  <div class="relative overflow-x-auto shadow-md sm:rounded-lg bg-white">
    <table class="w-full text-sm text-left text-gray-500">
      <thead class="text-xs text-gray-700 uppercase bg-gray-200">
             <tr>
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
      <tbody>
        {{-- @foreach ($shipments as $shipment)
        <tr class="bg-white border-b hover:bg-gray-100">
          <td class="px-6 py-4">#{{ $shipment->id }}</td>
          <td class="px-6 py-4">{{ $shipment->name }}</td>
          <td class="px-6 py-4">{{ $shipment->email }}</td>
          <td class="px-6 py-4">{{ $shipment->phone }}</td>
          <td class="px-6 py-4">${{ $shipment->amount }}</td>
          <td class="px-6 py-4">{{ $shipment->status }}</td>
          <td class="px-6 py-4 flex space-x-3">
            <a href="#" class="text-blue-600 hover:underline">Edit</a>
            <a href="#" class="text-red-600 hover:underline">Delete</a>
          </td>
        </tr>
        @endforeach --}}
         <tr class="bg-white whitespace-nowrap border-b border-gray-200 hover:bg-gray-100">

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

            </tr>


      </tbody>
    </table>
  </div>
</section>
@endsection
