@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<section>
  <div class="grid xl:grid-cols-4 lg:grid-cols-3 md:grid-cols-2 gap-4 p-4">

    <!-- Card 1 -->
    <a href="#"
      class="grid grid-cols-2 p-4 items-center bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-gray-100">
      <div class="flex items-center justify-center h-full bg-green rounded-lg">
        <svg class="w-12 h-12 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
          stroke-width="2" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M5 4h1.5L9 16m0 0h8m-8 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm8 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm-8.5-3h9.25L19 7H7.312" />
        </svg>
      </div>
      <div class="flex flex-col justify-between p-4 h-full leading-normal">
        <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900">000000</h5>
        <p class="mb-3 font-normal text-gray-700">Total Orders</p>
      </div>
    </a>

    <!-- Card 2 -->
    <a href="#"
      class="grid grid-cols-2 p-4 items-center bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-gray-100">
      <div class="flex items-center justify-center h-full bg-green rounded-lg">
        <svg class="w-12 h-12 text-white" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
          <path fill-rule="evenodd"
            d="M4 4a2 2 0 0 0-2 2v9a1 1 0 0 0 1 1h.535a3.5 3.5 0 1 0 6.93 0h3.07a3.5 3.5 0 1 0 6.93 0H21a1 1 0 0 0 1-1v-4a.999.999 0 0 0-.106-.447l-2-4A1 1 0 0 0 19 6h-5a2 2 0 0 0-2-2H4Zm14.192 11.59.016.02a1.5 1.5 0 1 1-.016-.021Zm-10 0 .016.02a1.5 1.5 0 1 1-.016-.021Zm5.806-5.572v-2.02h4.396l1 2.02h-5.396Z"
            clip-rule="evenodd" />
        </svg>
      </div>
      <div class="flex flex-col justify-between p-4 h-full leading-normal">
        <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900">000000</h5>
        <p class="mb-3 font-normal text-gray-700">FullFill Orders</p>
      </div>
    </a>

    <!-- Card 3 -->
    <a href="#"
      class="grid grid-cols-2 p-4 items-center bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-gray-100">
      <div class="flex items-center justify-center h-full bg-green rounded-lg">
        <svg class="w-12 h-12 text-white" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
          <path fill-rule="evenodd"
            d="M4 4a2 2 0 0 0-2 2v9a1 1 0 0 0 1 1h.535a3.5 3.5 0 1 0 6.93 0h3.07a3.5 3.5 0 1 0 6.93 0H21a1 1 0 0 0 1-1v-4a.999.999 0 0 0-.106-.447l-2-4A1 1 0 0 0 19 6h-5a2 2 0 0 0-2-2H4Zm14.192 11.59.016.02a1.5 1.5 0 1 1-.016-.021Zm-10 0 .016.02a1.5 1.5 0 1 1-.016-.021Zm5.806-5.572v-2.02h4.396l1 2.02h-5.396Z"
            clip-rule="evenodd" />
        </svg>
      </div>
      <div class="flex flex-col justify-between p-4 h-full leading-normal">
        <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900">000000</h5>
        <p class="mb-3 font-normal text-gray-700">Canceled Orders</p>
      </div>
    </a>

    <!-- Card 4 -->
    <a href="#"
      class="grid grid-cols-2 p-4 items-center bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-gray-100">
      <div class="flex items-center justify-center h-full bg-green rounded-lg">
        <svg class="w-12 h-12 text-white" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
          <path fill-rule="evenodd"
            d="M9 15a6 6 0 1 1 12 0 6 6 0 0 1-12 0Zm3.845-1.855a2.4 2.4 0 0 1 1.2-1.226 1 1 0 0 1 1.992-.026c.426.15.809.408 1.111.749a1 1 0 1 1-1.496 1.327.682.682 0 0 0-.36-.213.997.997 0 0 1-.113-.032.4.4 0 0 0-.394.074.93.93 0 0 0 .455.254 2.914 2.914 0 0 1 1.504.9c.373.433.669 1.092.464 1.823a.996.996 0 0 1-.046.129c-.226.519-.627.94-1.132 1.192a1 1 0 0 1-1.956.093 2.68 2.68 0 0 1-1.227-.798 1 1 0 1 1 1.506-1.315.682.682 0 0 0 .363.216c.038.009.075.02.111.032a.4.4 0 0 0 .395-.074.93.93 0 0 0-.455-.254 2.91 2.91 0 0 1-1.503-.9c-.375-.433-.666-1.089-.466-1.817a.994.994 0 0 1 .047-.134Zm1.884.573.003.008c-.003-.005-.003-.008-.003-.008Zm.55 2.613s-.002-.002-.003-.007a.032.032 0 0 1 .003.007ZM4 14a1 1 0 0 1 1 1v4a1 1 0 1 1-2 0v-4a1 1 0 0 1 1-1Zm3-2a1 1 0 0 1 1 1v6a1 1 0 1 1-2 0v-6a1 1 0 0 1 1-1Zm6.5-8a1 1 0 0 1 1-1H18a1 1 0 0 1 1 1v3a1 1 0 1 1-2 0v-.796l-2.341 2.049a1 1 0 0 1-1.24.06l-2.894-2.066L6.614 9.29a1 1 0 1 1-1.228-1.578l4.5-3.5a1 1 0 0 1 1.195-.025l2.856 2.04L15.34 5h-.84a1 1 0 0 1-1-1Z"
            clip-rule="evenodd" />
        </svg>
      </div>
      <div class="flex flex-col justify-between p-4 h-full leading-normal">
        <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900">000000</h5>
        <p class="mb-3 font-normal text-gray-700">Total Revenue</p>
      </div>
    </a>

  </div>
</section>

@endsection
