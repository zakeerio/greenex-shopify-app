@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

  <form class="p-4 md:p-5 bg-gray-200 mt-10 rounded-lg shadow">
    <div class="grid gap-4 mb-4 lg:grid-cols-3 md:grid-cols-2">
      <div>
        <label for="name" class="block mb-2 text-sm font-medium text-gray-900">UserName</label>
        <input type="text" id="name" name="name"
          class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5"
          placeholder="UserName" required>
      </div>

      <div>
        <label for="password" class="block mb-2 text-sm font-medium text-gray-900">Password</label>
        <input type="password" id="password" name="password"
          class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5"
          placeholder="Password" required>
      </div>

      <div>
        <label for="Fullfillment" class="block mb-2 text-sm font-medium text-gray-900">Fullfillment Location</label>
        <select id="Fullfillment"
          class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
          <option selected>Fullfillment Location</option>
          <option value="Dadu">Dadu</option>
          <option value="Karachi">Karachi</option>
        </select>
      </div>

      <div>
        <label for="Firgile" class="block mb-2 text-sm font-medium text-gray-900">Firgile</label>
        <select id="Firgile"
          class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
          <option selected>Firgile</option>
          <option value="Yes">Yes</option>
          <option value="No">No</option>
        </select>
      </div>

      <div>
        <label for="apikey" class="block mb-2 text-sm font-medium text-gray-900">API Key</label>
        <input type="text" id="apikey" name="apikey"
          class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5"
          placeholder="API Key" required>
      </div>

      <div>
        <label for="Insurance" class="block mb-2 text-sm font-medium text-gray-900">Insurance</label>
        <select id="Insurance"
          class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
          <option selected>Insurance</option>
          <option value="Yes">Yes</option>
          <option value="No">No</option>
        </select>
      </div>

      <div>
        <label for="accounttype" class="block mb-2 text-sm font-medium text-gray-900">Account Type</label>
        <select id="accounttype"
          class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
          <option selected>Account Type</option>
          <option value="live">Live</option>
          <option value="offline">Offline</option>
        </select>
      </div>

      <div>
        <label for="cms" class="block mb-2 text-sm font-medium text-gray-900">Order Push Automatically on CMS</label>
        <select id="cms"
          class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
          <option selected>Account Type</option>
          <option value="Yes">Yes</option>
          <option value="No">No</option>
        </select>
      </div>

      <div>
        <label for="price" class="block mb-2 text-sm font-medium text-gray-900">Price</label>
        <input type="number" id="price" name="price"
          class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5"
          placeholder="$2999" required>
      </div>
    </div>

    <div class="flex flex-col md:flex-row md:justify-between md:items-center space-y-4 md:space-y-0">
      <button type="button"
        class="text-white bg-green hover:bg-green-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5">
        Authenticate Account
      </button>
      <button type="submit"
        class="text-white bg-green hover:bg-green-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5">
        Save Account
      </button>
    </div>
  </form>

</section>
@endsection
