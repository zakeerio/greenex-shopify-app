<nav class="relative bg-white text-black after:pointer-events-none after:absolute after:inset-x-0 after:bottom-0 after:h-px after:bg-white/10 shadow-md">
    <div class="mx-auto max-w-11xl px-2 sm:px-6 lg:px-8">
        <div class="relative flex h-16 items-center justify-between">

            <!-- Mobile Menu Button -->
            <div class="absolute inset-y-0 left-0 flex items-center sm:hidden">
                <button data-collapse-toggle="mobile-menu" type="button"
                    class="relative inline-flex items-center justify-center rounded-md p-2 text-gray-200 hover:bg-white/10 hover:text-white focus:outline-2 focus:-outline-offset-1 focus:outline-blue-400"
                    aria-controls="mobile-menu" aria-expanded="false">
                    <span class="sr-only">Open main menu</span>
                    <svg class="block size-6" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                    <svg class="hidden size-6" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Logo + Links -->
            <div class="flex flex-1 items-center justify-center sm:items-stretch sm:justify-start">
                {{-- <div class="flex shrink-0 items-center">
                    <img src="{{ asset('images/logo.svg') }}" alt="Your Company" class="h-8 w-auto" />
                </div> --}}
                <div class="hidden sm:ml-6 sm:block">
                    <div class="flex space-x-4">
                        <a href="{{ route('dashboard') }}"
                           class="py-2 text-sm font-medium {{ (request()->routeIs('dashboard') || request()->routeIs('/')) ? 'text-green border-b-2 border-green' : 'text-black hover:text-green' }}">
                            Dashboard
                        </a>

                        <a href="{{ route('orders') }}"
                           class="px-3 py-2 text-sm font-medium {{ request()->routeIs('orders*') ? 'text-green border-b-2 border-green' : 'text-black hover:text-green' }}">
                            Orders
                        </a>

                        <a href="{{ route('shipments') }}"
                           class="px-3 py-2 text-sm font-medium {{ request()->routeIs('shipments*') ? 'text-green border-b-2 border-green' : 'text-black hover:text-green' }}">
                            Shipments
                        </a>

                        <a href="{{ route('settings') }}"
                           class="px-3 py-2 text-sm font-medium {{ request()->routeIs('settings*') ? 'text-green border-b-2 border-green' : 'text-black hover:text-green' }}">
                            Settings
                        </a>

                        <a href="#"
                           class="px-3 py-2 text-sm font-medium {{ request()->routeIs('instructions') ? 'text-green border-b-2 border-green' : 'text-black hover:text-green' }}">
                            Instructions
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right Section -->
            <div class="absolute inset-y-0 right-0 flex items-center pr-2 sm:static sm:inset-auto sm:ml-6 sm:pr-0">
                <div class="relative ml-3">
                    <button type="button" data-dropdown-toggle="profile-dropdown"
                        class="relative rounded-full p-1 text-black hover:text-green focus:outline-2 focus:outline-offset-2 focus:outline-blue-400">
                        <span class="sr-only">View notifications</span>
                        <svg class="size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                        </svg>
                    </button>

                    <div id="profile-dropdown"
                        class="hidden absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white min-h-[100px] py-1 outline -outline-offset-1 outline-white/10 shadow-lg transition-all">
                        <div class="w-full h-full mx-auto flex justify-center items-center p-4">
                            <p>Nothing</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div id="mobile-menu"
        class="hidden sm:hidden bg-white text-center backdrop-blur-md border-t border-white/10">
        <div class="space-y-1 px-2 pt-2 pb-3">
            <a href="{{ route('dashboard') }}"
               class="block px-3 py-2 text-base font-medium {{ request()->routeIs('dashboard') ? 'text-green' : 'text-black hover:text-green' }}">
                Dashboard
            </a>
            <a href="{{ route('orders') }}"
               class="block px-3 py-2 text-base font-medium {{ request()->routeIs('orders*') ? 'text-green' : 'text-black hover:text-green' }}">
                Orders
            </a>
            <a href="{{ route('shipments') }}"
               class="block px-3 py-2 text-base font-medium {{ request()->routeIs('shipments*') ? 'text-green' : 'text-black hover:text-green' }}">
                Shipments
            </a>
            <a href="{{ route('settings') }}"
               class="block px-3 py-2 text-base font-medium {{ request()->routeIs('settings*') ? 'text-green' : 'text-black hover:text-green' }}">
                Settings
            </a>
            <a href="#"
               class="block px-3 py-2 text-base font-medium {{ request()->routeIs('instructions') ? 'text-green' : 'text-black hover:text-green' }}">
                Instructions
            </a>
        </div>
    </div>
</nav>
