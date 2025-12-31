@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<section class="max-w-11xl mx-auto mt-1 px-4 sm:px-6 lg:px-8">

    <form id="SettingForm" action="{{ route('authenticateAndSave') }}" class="p-4 md:p-5 bg-gray-200 rounded-lg shadow"
        method="POST">
        <div class="grid gap-4 mb-4 lg:grid-cols-3 md:grid-cols-2">

            @csrf

            <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">

            <div>
                <label for="email" class="block mb-2 text-sm font-medium text-gray-900">Email</label>
                <input type="text" id="email" name="email"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5"
                    placeholder="Email address" required {{ $settings ? 'readonly' : '' }}
                    value="{{ old('email', $settings->email ?? '') }}">
            </div>

            <div>
                <label for="password" class="block mb-2 text-sm font-medium text-gray-900">Password</label>
                <input type="password" id="password" name="password" {{ $settings ? 'readonly' : '' }}
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5"
                    placeholder="Password">
            </div>

            <div>
                <label for="fullfilment" class="block mb-2 text-sm font-medium text-gray-900">Fullfillment
                    Location</label>
                <select id="fullfilment" name="fullfilment"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
                    <option value="">Fullfillment Location</option>
                    @foreach (['islamabad', 'lahore', 'faislabad', 'rawalpindi', 'karachi'] as $city)
                    <option value="{{ $city }}"
                        {{ ($settings->fulfillment_location ?? '') === $city ? 'selected' : '' }}>
                        {{ ucfirst($city) }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="Fragile" class="block mb-2 text-sm font-medium text-gray-900">Fragile</label>
                <select id="Fragile" name="fragile"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
                    <option value="">Fragile</option>
                    <option value="Yes" {{ $settings->fragile ?? false ? 'selected' : '' }}>Yes</option>
                    <option value="No" {{ empty($settings->fragile) ? 'selected' : '' }}>No</option>
                </select>
            </div>

            @php
            use Illuminate\Support\Str;

            $apiMasked = $settings->api_token ?? '';
            if ($apiMasked) {
            // Mask everything except last 4 characters
            $apiMasked = Str::mask($apiMasked, '*', 0, strlen($apiMasked) - 4);
            }
            @endphp

            <div>
                <label for="apikey" class="block mb-2 text-sm font-medium text-gray-900">API Key</label>
                <input type="text" id="apikey" name="apikey" {{ $settings ? 'readonly' : '' }}
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5"
                    placeholder="API Key" value="{{ old('apikey', $apiMasked ?? '') }}">
            </div>

            <div>
                <label for="Insurance" class="block mb-2 text-sm font-medium text-gray-900">Insurance</label>
                <select id="Insurance" name="insurance"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
                    <option value="">Insurance</option>
                    <option value="Yes" {{ $settings->insurance ?? false ? 'selected' : '' }}>Yes</option>
                    <option value="No" {{ empty($settings->insurance) ? 'selected' : '' }}>No</option>
                </select>
            </div>

            <div>
                <label for="accounttype" class="block mb-2 text-sm font-medium text-gray-900">Account Type</label>
                <select id="accounttype" name="account_type"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
                    <option value="">Account Type</option>
                    <option value="live" {{ ($settings->account_type ?? '') === 'live' ? 'selected' : '' }}>Live
                    </option>
                    <option value="offline" {{ ($settings->account_type ?? '') === 'offline' ? 'selected' : '' }}>
                        Offline</option>
                </select>
            </div>

            <div>
                <label for="auto_push_orders" class="block mb-2 text-sm font-medium text-gray-900">Order Push
                    Automatically on CMS</label>
                <select id="auto_push_orders" name="auto_push_orders"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
                    <option value="">Order Push Automatically</option>
                    <option value="Yes" {{ $settings->auto_push_cms ?? false ? 'selected' : '' }}>Yes</option>
                    <option value="No" {{ empty($settings->auto_push_cms) ? 'selected' : '' }}>No</option>
                </select>
            </div>

            <div>
                <label for="price" class="block mb-2 text-sm font-medium text-gray-900">Price</label>
                <input type="number" id="price" name="price"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5"
                    placeholder="$2999" value="{{ old('price', $settings->price ?? '') }}">
            </div>

        </div>

        <div class="flex flex-col md:flex-row md:justify-between md:items-center space-y-4 md:space-y-0">

            {{-- <button id="AuthenticateAccount" type="button"
                    class="text-white bg-green hover:bg-green-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5">
                    Authenticate Account
                </button> --}}

            <button type="button" id="SaveAccountSettings"
                class="text-white bg-green hover:bg-green-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5">
                Save Account
            </button>
        </div>
    </form>

</section>
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('SettingForm');
        const authBtn = document.getElementById('AuthenticateAccount'); // May be null
        const saveBtn = document.getElementById('SaveAccountSettings');

        async function submitForm(url) {
            const formData = new FormData(form);

            // Show loading state
            if (authBtn) {
                authBtn.disabled = true;
                var originalAuthText = authBtn.innerHTML;
            }

            if (saveBtn) {
                saveBtn.disabled = true;
                var originalSaveText = saveBtn.innerHTML;
            }

            if (url.includes('authenticate') && authBtn) {
                authBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing...';
            } else if (saveBtn) {
                saveBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';
            }

            // Get Session Token
            let token = "";
            if (window.app) {
                try {
                    const utils = window["app-bridge"].utilities;
                    token = await utils.getSessionToken(window.app);
                } catch (e) {
                    console.error("Error retrieving session token:", e);
                }
            }

            try {
                const response = await fetch(url + `?host={{ request('host') }}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${token}`
                    },
                    body: formData
                });

                const data = await response.json();

                if (response.ok && (data.status || data.success)) {
                    // Success
                    alert(data.message || 'Saved successfully');
                    window.location.reload();
                } else {
                    alert(data.message || data.error || 'Error saving settings');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please check console for details.');
            } finally {
                // Reset buttons
                if (authBtn) {
                    authBtn.disabled = false;
                    authBtn.innerHTML = originalAuthText;
                }
                if (saveBtn) {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = originalSaveText;
                }
            }
        }

        if (authBtn) {
            authBtn.addEventListener('click', function(e) {
                e.preventDefault();
                submitForm("{{ route('authenticateAndSave') }}");
            });
        }

        if (saveBtn) {
            saveBtn.addEventListener('click', function(e) {
                e.preventDefault();
                // Use updatesetting route for general updates
                submitForm("{{ route('updatesetting') }}");
            });
        }
    });
</script>
@endpush
@endsection