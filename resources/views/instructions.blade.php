@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">
                How to use GreenEx App
            </h1>
            <p class="mt-2 text-gray-600">
                Follow this guide to manage your shipments and fulfill Shopify orders efficiently.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            <!-- Step 1: Settings -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6">
                    <div class="flex items-center justify-center w-12 h-12 mb-4 rounded-full bg-green-100 text-green-600">
                        <i class="fa-solid fa-cogs text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">1. Authenticaton</h3>
                    <p class="text-gray-600 text-sm mb-4">
                        Before starting, go to the <strong>Settings</strong> page. Enter your GreenEx credentials (email &
                        password) or API Key to authenticate your store.
                    </p>
                    <div class="flex items-center text-sm text-gray-500">
                        <i class="fa-solid fa-circle-check text-green-500 mr-2"></i> Only required once
                    </div>
                </div>
            </div>

            <!-- Step 2: Sync Orders -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6">
                    <div class="flex items-center justify-center w-12 h-12 mb-4 rounded-full bg-blue-100 text-blue-600">
                        <i class="fa-solid fa-sync text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">2. Fetch Orders</h3>
                    <p class="text-gray-600 text-sm mb-4">
                        Navigate to the <strong>Orders</strong> page. The app automatically fetches unfulfilled orders from
                        Shopify. Use "Fetch" to refresh the list manually.
                    </p>
                    <div class="flex items-center text-sm text-gray-500">
                        <i class="fa-solid fa-wifi text-blue-500 mr-2"></i> Real-time sync
                    </div>
                </div>
            </div>

            <!-- Step 3: Process Orders -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6">
                    <div class="flex items-center justify-center w-12 h-12 mb-4 rounded-full bg-indigo-100 text-indigo-600">
                        <i class="fa-solid fa-box-open text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">3. Ship Orders</h3>
                    <p class="text-gray-600 text-sm mb-4">
                        Select the orders you want to ship and click <strong>"Process Selected"</strong>. This will:
                    <ul class="list-disc ml-5 mt-2 space-y-1">
                        <li>Send order data to GreenEx</li>
                        <li>Generate a Tracking ID</li>
                        <li>Mark order as Fulfilled in Shopify</li>
                    </ul>
                    </p>
                </div>
            </div>

            <!-- Step 4: Print Labels -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6">
                    <div class="flex items-center justify-center w-12 h-12 mb-4 rounded-full bg-purple-100 text-purple-600">
                        <i class="fa-solid fa-print text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">4. Print Labels</h3>
                    <p class="text-gray-600 text-sm mb-4">
                        Go to the <strong>Shipments</strong> page to view processed orders. Select orders and click
                        <strong>"Bulk Print"</strong> to generate shipping labels (PDF).
                    </p>
                </div>
            </div>

            <!-- Step 5: Tracking -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6">
                    <div class="flex items-center justify-center w-12 h-12 mb-4 rounded-full bg-orange-100 text-orange-600">
                        <i class="fa-solid fa-truck-fast text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">5. Tracking</h3>
                    <p class="text-gray-600 text-sm mb-4">
                        Tracking numbers are automatically added to the Shopify order. Customers receive the tracking link
                        in their email notification.
                    </p>
                    <div class="flex items-center text-sm text-gray-500">
                        <i class="fa-regular fa-envelope text-orange-500 mr-2"></i> Auto-notification
                    </div>
                </div>
            </div>

            <!-- Support -->
            <div
                class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 ring-2 ring-green-500 ring-opacity-50">
                <div class="p-6">
                    <div class="flex items-center justify-center w-12 h-12 mb-4 rounded-full bg-green text-white">
                        <i class="fa-solid fa-headset text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Need Help?</h3>
                    <p class="text-gray-600 text-sm mb-4">
                        If you encounter any issues or have questions, please contact our support team.
                    </p>
                    <div class="mt-4">
                        <a href="mailto:support@greenex.pk"
                            class="text-green-600 hover:text-green-800 font-medium text-sm flex items-center">
                            <i class="fa-solid fa-envelope mr-2"></i> support@greenex.pk
                        </a>
                        <a href="https://greenex.pk" target="_blank"
                            class="text-green-600 hover:text-green-800 font-medium text-sm flex items-center mt-2">
                            <i class="fa-solid fa-globe mr-2"></i> Visit Website
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
