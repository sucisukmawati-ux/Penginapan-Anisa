<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased text-[#342d1e]">
        <div class="relative isolate flex min-h-svh flex-col items-center justify-center overflow-hidden p-5 sm:p-8">
            <div aria-hidden="true" class="absolute -left-24 -top-24 -z-10 size-80 rounded-full bg-[#f5df8b]/35 blur-3xl"></div>
            <div aria-hidden="true" class="absolute -bottom-32 -right-20 -z-10 size-96 rounded-full bg-[#f8eab9]/45 blur-3xl"></div>

            <div class="w-full max-w-md">
                <div class="rounded-3xl border border-[#f0e7ce] bg-white p-5 shadow-xl shadow-[#8b6a1e]/10 sm:p-8">
                    <a href="{{ route('home') }}" class="mb-6 flex flex-col items-center gap-2 font-medium" wire:navigate>
                        <span class="flex size-14 items-center justify-center overflow-hidden rounded-2xl border border-[#f0dfaa] bg-[#fff9e8] p-1 shadow-sm shadow-[#8b6a1e]/10">
                            <img src="{{ asset('images/logo_anisa.jpeg') }}" alt="Logo Penginapan Anisa" class="size-full rounded-xl object-cover" />
                        </span>
                        <span class="text-sm font-semibold tracking-wide text-[#76601e]">{{ config('app.name', 'Penginapan Anisa') }}</span>
                    </a>

                    <div class="flex flex-col gap-5">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
