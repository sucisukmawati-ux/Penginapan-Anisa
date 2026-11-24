@php
    $user = auth()->user();
    $isAdmin = $user?->isAdmin() ?? false;
    $isStaff = $user?->isStaff() ?? false;
    $isGuest = $user?->role === \App\Enums\UserRole::GUEST;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        @if ($user)
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo
                    :sidebar="true"
                    :href="($isAdmin || $isStaff) ? route('dashboard') : route('rooms.index')"
                    wire:navigate
                />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Menu Penginapan')" class="grid">
                    @if ($isAdmin || $isStaff)
                        <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                            {{ __('Dashboard') }}
                        </flux:sidebar.item>
                    @endif
                    @if ($isAdmin)
                        <flux:sidebar.item icon="building-office-2" :href="route('admin.rooms.index')" :current="request()->routeIs('admin.rooms.*')" wire:navigate>
                            {{ __('Kelola Kamar') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="sparkles" :href="route('admin.facilities.index')" :current="request()->routeIs('admin.facilities.*')" wire:navigate>
                            {{ __('Kelola Fasilitas') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="users" :href="route('admin.users.index')" :current="request()->routeIs('admin.users.*')" wire:navigate>
                            {{ __('Kelola Pengguna') }}
                        </flux:sidebar.item>
                    @endif
                    @if ($isAdmin || $isStaff)
                        <flux:sidebar.item icon="calendar-days" :href="route('admin.reservations')" :current="request()->routeIs('admin.reservations')" wire:navigate>
                            {{ __('Reservasi') }}
                        </flux:sidebar.item>
                    @endif
                    @if ($isGuest)
                        <flux:sidebar.item icon="home" :href="route('home')" :current="request()->routeIs('home')" wire:navigate>
                            {{ __('Beranda') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="building-office-2" :href="route('rooms.index')" :current="request()->routeIs('rooms.*')" wire:navigate>
                            {{ __('Cari Kamar') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="calendar-days" :href="route('reservations.index')" :current="request()->routeIs('reservations.*')" wire:navigate>
                            {{ __('Reservasi Saya') }}
                        </flux:sidebar.item>
                    @endif
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            @if ($user)
                <x-desktop-user-menu class="hidden lg:block" :name="$user->name" />
            @endif
        </flux:sidebar>

        @if ($user)
            <flux:header class="lg:hidden">
                <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

                <flux:spacer />

                <flux:dropdown position="top" align="end">
                    <flux:profile
                        :initials="$user->initials()"
                        icon-trailing="chevron-down"
                    />

                    <flux:menu>
                        <flux:menu.radio.group>
                            <div class="p-0 text-sm font-normal">
                                <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                    <flux:avatar
                                        :name="$user->name"
                                        :initials="$user->initials()"
                                    />

                                    <div class="grid flex-1 text-start text-sm leading-tight">
                                        <flux:heading class="truncate">{{ $user->name }}</flux:heading>
                                        <flux:text class="truncate">{{ $user->email }}</flux:text>
                                    </div>
                                </div>
                            </div>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <flux:menu.radio.group>
                            <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                                {{ __('Settings') }}
                            </flux:menu.item>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <flux:menu.item
                                as="button"
                                type="submit"
                                icon="arrow-right-start-on-rectangle"
                                class="w-full cursor-pointer"
                                data-test="logout-button"
                            >
                                {{ __('Log out') }}
                            </flux:menu.item>
                        </form>
                    </flux:menu>
                </flux:dropdown>
            </flux:header>
        @endif
        @else
            <header class="sticky top-0 z-30 border-b border-[#e2e7df] bg-white/95 shadow-sm backdrop-blur">
                <nav aria-label="Navigasi pengunjung" class="mx-auto flex min-h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-7">
                    <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3">
                        <img src="{{ asset('images/logo_anisa.jpeg') }}" alt="" class="size-10 rounded-full object-cover" />
                        <span class="truncate text-sm font-bold text-[#26332e] sm:text-base">Penginapan Anisa</span>
                    </a>
                    <div class="flex shrink-0 items-center gap-2 sm:gap-4">
                        <a href="{{ route('rooms.index') }}" class="hidden text-sm font-semibold text-[#46554d] transition hover:text-[#31584a] sm:inline-flex">Cari kamar</a>
                        <a href="{{ route('login') }}" class="inline-flex min-h-10 items-center justify-center rounded-full border border-[#d8ddd5] px-4 text-sm font-semibold text-[#31584a] transition hover:bg-[#f1f4ef]">Masuk</a>
                    </div>
                </nav>
            </header>
        @endif

        @if (! $user)
            <main>
        @endif
        {{ $slot }}
        @if (! $user)
            </main>
        @endif

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
