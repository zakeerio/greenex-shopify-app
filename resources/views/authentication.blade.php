@extends('layouts.app')

@section('title', 'Authenticate')

@section('content')
    {{-- CDN for Styling & Icons --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .animate-fade-in {
            animation: fadeIn 0.3s ease-out forwards;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(5px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .focus-ring:focus {
            box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.3);
        }
    </style>

    <div
        class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">

            <div class="text-center">
                <div
                    class="mx-auto h-12 w-12 bg-green-100 rounded-xl flex items-center justify-center text-green-600 text-xl shadow-sm">
                    <i class="fa fa-lock"></i>
                </div>
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900 tracking-tight">
                    Welcome Back
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Sign in to manage your account
                </p>
            </div>

            <div class="bg-white py-8 px-4 shadow-xl rounded-2xl sm:px-10 border border-gray-100 relative">

                <div class="mb-8">
                    <div class="grid grid-cols-2 bg-gray-100 p-1.5 rounded-xl">
                        <button type="button" onclick="switchTab('email')" id="email-tab"
                            class="py-2.5 text-sm font-bold rounded-lg shadow-sm bg-white text-gray-900 transition-all duration-200 focus:outline-none">
                            Email & Password
                        </button>
                        <button type="button" onclick="switchTab('apikey')" id="apikey-tab"
                            class="py-2.5 text-sm font-medium rounded-lg text-gray-500 hover:text-gray-700 transition-all duration-200 focus:outline-none">
                            API Key
                        </button>
                    </div>
                </div>

                <div id="myTabContent">

                    <div id="email-content" class="animate-fade-in block">
                        <form id="EmailAuthForm" class="space-y-6" action="{{ route('authenticateAndSave') }}"
                            method="POST">
                            @csrf
                            <input type="hidden" name="auth_method" value="email">
                            <div>
                                <label for="email" class="block text-sm font-bold text-gray-700">Email address</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fa fa-envelope text-gray-400"></i>
                                    </div>
                                    <input id="email" name="email" type="email" autocomplete="email" required
                                        class="focus-ring block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl focus:outline-none focus:border-green-500 sm:text-sm"
                                        placeholder="you@example.com">
                                </div>
                            </div>
                            <div>
                                <label for="password" class="block text-sm font-bold text-gray-700">Password</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fa fa-key text-gray-400"></i>
                                    </div>
                                    <input id="password" name="password" type="password" autocomplete="current-password"
                                        required
                                        class="focus-ring block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl focus:outline-none focus:border-green-500 sm:text-sm"
                                        placeholder="••••••••">
                                </div>
                            </div>
                            <button type="submit" id="EmailAuthBtn"
                                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-md text-sm font-bold text-white bg-green-600 hover:bg-green-700 focus:outline-none transition-all duration-200 transform active:scale-95">
                                Sign In
                            </button>
                        </form>
                    </div>

                    <div id="apikey-content" class="hidden animate-fade-in">
                        <form id="ApiKeyAuthForm" class="space-y-6" action="{{ route('authenticateAndSave') }}"
                            method="POST">
                            @csrf
                            <input type="hidden" name="auth_method" value="apikey">
                            <div>
                                <label for="apikey" class="block text-sm font-bold text-gray-700">API Key</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fa fa-code text-gray-400"></i>
                                    </div>
                                    <input id="apikey" name="apikey" type="text" required
                                        class="focus-ring block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl focus:outline-none focus:border-green-500 sm:text-sm font-mono"
                                        placeholder="Enter API Token">
                                </div>
                            </div>
                            <button type="submit" id="ApiKeyAuthBtn"
                                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-md text-sm font-bold text-white bg-green-600 hover:bg-green-700 focus:outline-none transition-all duration-200 transform active:scale-95">
                                Authenticate with Key
                            </button>
                        </form>
                    </div>

                </div>
            </div>
            <p class="text-center text-xs text-gray-400">
                &copy; {{ date('Y') }} <a href="https://greenex.pk" target="_blank">GreenEx</a>. All Rights Reserved.
            </p>
        </div>
    </div>

    {{-- Script moved OUTSIDE @push to ensure it loads --}}
    <script>
        // Make function Global
        window.switchTab = function(tab) {
            const emailTab = document.getElementById('email-tab');
            const apikeyTab = document.getElementById('apikey-tab');
            const emailContent = document.getElementById('email-content');
            const apikeyContent = document.getElementById('apikey-content');

            const activeClasses = ['bg-white', 'text-gray-900', 'shadow-sm', 'font-bold'];
            const inactiveClasses = ['text-gray-500', 'hover:text-gray-700', 'font-medium'];

            if (tab === 'email') {
                emailTab.classList.add(...activeClasses);
                emailTab.classList.remove('text-gray-500', 'hover:text-gray-700', 'font-medium');
                apikeyTab.classList.remove('bg-white', 'text-gray-900', 'shadow-sm', 'font-bold');
                apikeyTab.classList.add(...inactiveClasses);
                emailContent.classList.remove('hidden');
                apikeyContent.classList.add('hidden');
            } else {
                apikeyTab.classList.add(...activeClasses);
                apikeyTab.classList.remove('text-gray-500', 'hover:text-gray-700', 'font-medium');
                emailTab.classList.remove('bg-white', 'text-gray-900', 'shadow-sm', 'font-bold');
                emailTab.classList.add(...inactiveClasses);
                apikeyContent.classList.remove('hidden');
                emailContent.classList.add('hidden');
            }
        };

        document.addEventListener('DOMContentLoaded', function() {
            const emailForm = document.getElementById('EmailAuthForm');
            const apikeyForm = document.getElementById('ApiKeyAuthForm');

            async function handleAuth(e, form, btnId) {
                e.preventDefault();
                const btn = document.getElementById(btnId);
                const originalText = btn.innerHTML;

                btn.disabled = true;
                btn.innerHTML = '<i class="fa fa-circle-o-notch fa-spin"></i> Processing...';
                btn.classList.add('opacity-75', 'cursor-not-allowed');

                const formData = new FormData(form);
                const url = form.action;

                try {
                    // Fix: Ensure host param is attached correctly
                    const currentUrl = new URL(window.location.href);
                    const hostParam = currentUrl.searchParams.get('host');
                    const separator = url.includes('?') ? '&' : '?';
                    const fetchUrl = hostParam ? `${url}${separator}host=${hostParam}` : url;

                    const response = await fetch(fetchUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (response.ok && (data.status || data.success)) {
                        btn.classList.remove('bg-green-600', 'hover:bg-green-700');
                        btn.classList.add('bg-green-500');
                        btn.innerHTML = '<i class="fa fa-check"></i> Redirecting...';
                        setTimeout(() => {
                            window.location.href = "{{ route('home') }}";
                        }, 500);
                    } else {
                        alert(data.message || 'Authentication failed');
                        resetBtn(btn, originalText);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred. Check console for details.');
                    resetBtn(btn, originalText);
                }
            }

            function resetBtn(btn, text) {
                btn.disabled = false;
                btn.innerHTML = text;
                btn.classList.remove('opacity-75', 'cursor-not-allowed');
            }

            if (emailForm) emailForm.addEventListener('submit', (e) => handleAuth(e, emailForm, 'EmailAuthBtn'));
            if (apikeyForm) apikeyForm.addEventListener('submit', (e) => handleAuth(e, apikeyForm,
                'ApiKeyAuthBtn'));
        });
    </script>
@endsection
