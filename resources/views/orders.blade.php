@extends('layouts.app')

@section('title', 'Orders')

@section('content')
<section class="max-w-7xl mx-auto mt-10 px-4 sm:px-6 lg:px-8">
  <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
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
        <tr class="bg-white border-b whitespace-nowrap text-black border-gray-400">
          <td class="w-4 p-4">
            <input type="checkbox" class="w-4 h-4 text-black bg-white border-gray-300 rounded-sm focus:ring-blue-500">
          </td>
          <th class="px-6 py-4 font-medium text-gray-900">#12345</th>
          <td class="px-6 py-4">Jonny Doe</td>
          <td class="px-6 py-4">jonny@mail.com</td>
          <td class="px-6 py-4">0312-4567890</td>
          <td class="px-6 py-4">
            <select
              class="border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
              <option value="yes" selected>Yes</option>
              <option value="no">No</option>
            </select>
          </td>
          <td class="px-6 py-4">$2999</td>
          <td class="px-6 py-4">3</td>
          <td class="px-6 py-4">0.5kg</td>
          <td class="px-6 py-4">COD</td>
        </tr>
      </tbody>
    </table>
  </div>
</section>
@endsection
