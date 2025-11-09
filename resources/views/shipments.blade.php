@extends('layouts.app')

@section('title', 'Shipments')

@section('content')
<section class="max-w-7xl mx-auto mt-20 px-4 sm:px-6 lg:px-8">
  <div class="relative overflow-x-auto shadow-md sm:rounded-lg bg-white">
    <table class="w-full text-sm text-left text-gray-500">
      <thead class="text-xs text-gray-700 uppercase bg-gray-200">
        <tr>
          <th class="px-6 py-3">ID</th>
          <th class="px-6 py-3">Name</th>
          <th class="px-6 py-3">Email</th>
          <th class="px-6 py-3">Phone</th>
          <th class="px-6 py-3">Amount</th>
          <th class="px-6 py-3">Status</th>
          <th class="px-6 py-3">Actions</th>
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
      </tbody>
    </table>
  </div>
</section>
@endsection
