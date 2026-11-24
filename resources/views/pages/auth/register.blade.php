<x-layouts::auth :title="__('Register')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Daftar akun tamu')" :description="__('Buat akun untuk memesan kamar dan melihat reservasi Anda.')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-6">
            @csrf
            <flux:input
                name="name"
                :label="__('Nama lengkap')"
                :value="old('name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                :placeholder="__('Nama lengkap')"
            />

            <flux:input
                name="email"
                :label="__('Alamat email')"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="email@example.com"
            />

            <flux:input
                name="phone"
                :label="__('Nomor WhatsApp (opsional)')"
                :value="old('phone')"
                type="tel"
                autocomplete="tel"
                placeholder="08xxxxxxxxxx"
            />

            <flux:input
                name="password"
                :label="__('Kata sandi')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Minimal 8 karakter')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <flux:input
                name="password_confirmation"
                :label="__('Ulangi kata sandi')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Ulangi kata sandi')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full" data-test="register-user-button">
                    {{ __('Daftar') }}
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Sudah punya akun?') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('Masuk') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
