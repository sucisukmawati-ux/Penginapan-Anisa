<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Penginapan Anisa</title>
        <meta name="description" content="Penginapan Anisa — tempat menginap yang nyaman, tenang, dan siap menemani liburan Anda.">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            html.scroll-reveal-enabled [data-scroll-reveal] {
                opacity: 0;
                transform: translate3d(0, 36px, 0) scale(0.985);
                transition:
                    opacity 750ms cubic-bezier(0.22, 1, 0.36, 1),
                    transform 750ms cubic-bezier(0.22, 1, 0.36, 1);
                transition-delay: var(--scroll-reveal-delay, 0ms);
                will-change: opacity, transform;
            }

            html.scroll-reveal-enabled [data-scroll-reveal="image"] {
                transform: translate3d(0, 28px, 0) scale(0.96);
            }

            html.scroll-reveal-enabled [data-scroll-reveal="card"] {
                transform: translate3d(0, 30px, 0) scale(0.98);
            }

            html.scroll-reveal-enabled [data-scroll-reveal].is-visible {
                opacity: 1;
                transform: translate3d(0, 0, 0) scale(1);
                will-change: auto;
            }

            @media (prefers-reduced-motion: reduce) {
                html.scroll-reveal-enabled [data-scroll-reveal] {
                    opacity: 1;
                    transform: none;
                    transition: none;
                    will-change: auto;
                }
            }

            .testimonial-rating-options {
                display: flex;
                flex-direction: row-reverse;
                justify-content: flex-end;
                gap: 0.25rem;
            }

            .testimonial-rating-star {
                color: #c8c8bc;
                cursor: pointer;
                line-height: 1;
                transition: color 150ms ease, transform 150ms ease;
            }

            .testimonial-rating-star:hover,
            .testimonial-rating-star:hover ~ .testimonial-rating-star,
            .testimonial-rating-input:checked ~ .testimonial-rating-star {
                color: #c28a55;
            }

            .testimonial-rating-input:focus-visible + .testimonial-rating-star {
                outline: 2px solid #26332e;
                outline-offset: 4px;
            }

            .testimonial-rating-input:checked + .testimonial-rating-star {
                transform: scale(1.08);
            }

            .testimonial-star-filled {
                color: #c28a55;
            }

            .testimonial-scroller {
                -ms-overflow-style: none;
                scrollbar-width: none;
            }

            .testimonial-scroller::-webkit-scrollbar {
                display: none;
            }
        </style>
    </head>
    <body class="font-sans bg-[#faf8f2] text-[#26332e] antialiased" style="--font-sans: 'Plus Jakarta Sans', sans-serif; --font-serif: 'Playfair Display', serif;">
        <div class="min-h-screen">
            <main class="size-full">
                <section class="relative isolate">
                    <nav aria-label="Navigasi utama" class="absolute inset-x-4 top-4 z-20 mx-auto flex min-h-20 max-w-7xl flex-wrap items-center justify-between rounded-full border border-white/15 bg-[#26332e]/65 px-5 text-white shadow-lg shadow-black/10 backdrop-blur-md sm:inset-x-8 sm:top-7 sm:px-8">
                        <a href="{{ route('home') }}" class="flex items-center gap-3.5" aria-label="Penginapan Anisa - Beranda">
                            <img src="{{ asset('images/logo_anisa.jpeg') }}" alt="Logo Penginapan Anisa" class="h-10 w-10 rounded-full object-cover" />
                            <span class="flex flex-col gap-0.5">
                                <span class="text-lg font-normal tracking-[0.02em] text-white">Penginapan Anisa</span>
                                <span class="text-[8px] font-semibold uppercase tracking-[0.2em] text-white/75">Istirahat dengan hangat</span>
                            </span>
                        </a>

                        <div class="order-3 flex w-full items-center justify-between gap-5 overflow-x-auto border-t border-white/30 py-3 text-[10px] font-normal uppercase tracking-[0.12em] md:order-none md:w-auto md:justify-center md:gap-8 md:overflow-visible md:border-0 md:py-0 md:text-[11px]">
                            <a href="#tentang" class="relative shrink-0 py-1 transition-colors hover:text-[#d7b99a] after:absolute after:bottom-0 after:left-0 after:h-px after:w-0 after:bg-[#a76f54] after:transition-all after:duration-300 hover:after:w-full">Tentang</a>
                            <a href="#kamar" class="relative shrink-0 py-1 transition-colors hover:text-[#d7b99a] after:absolute after:bottom-0 after:left-0 after:h-px after:w-0 after:bg-[#a76f54] after:transition-all after:duration-300 hover:after:w-full">Kamar</a>
                            <a href="#fitur" class="relative shrink-0 py-1 transition-colors hover:text-[#d7b99a] after:absolute after:bottom-0 after:left-0 after:h-px after:w-0 after:bg-[#a76f54] after:transition-all after:duration-300 hover:after:w-full">Fasilitas</a>
                            <a href="#testimoni" class="relative shrink-0 py-1 transition-colors hover:text-[#d7b99a] after:absolute after:bottom-0 after:left-0 after:h-px after:w-0 after:bg-[#a76f54] after:transition-all after:duration-300 hover:after:w-full">Testimoni</a>
                        </div>

                        <div class="flex items-center gap-3 sm:gap-5">
                            @auth
                                <form method="POST" action="{{ route('logout') }}" class="hidden sm:inline-flex">
                                    @csrf
                                    <button type="submit" class="min-h-11 items-center px-1 text-xs font-medium tracking-wide transition-colors hover:text-[#d7b99a] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#26332e]">
                                        Logout
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('login') }}" class="hidden min-h-11 items-center px-1 text-xs font-medium tracking-wide transition-colors hover:text-[#d7b99a] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#26332e] sm:inline-flex">Login</a>
                            @endauth
                            <a href="{{ route('rooms.index') }}" class="inline-flex min-h-11 items-center rounded-full bg-[#26332e] px-5 text-[10px] font-semibold uppercase tracking-[0.14em] text-white transition-colors hover:bg-[#3d5148] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#26332e] sm:min-h-12 sm:px-6">
                                Booking
                            </a>
                        </div>
                    </nav>
                    <div class="relative isolate overflow-hidden">
                        <img
                            src="{{ asset('images/hiro2.jpg') }}"
                            alt="Tampak depan Penginapan Anisa"
                            class="absolute inset-0 z-0 h-full w-full object-cover"
                        />
                        <div class="absolute inset-0 z-0 bg-gradient-to-r from-[#172b24]/85 via-[#172b24]/55 to-[#172b24]/15"></div>
                        <div class="relative z-10 mx-auto flex min-h-[500px] max-w-7xl flex-col justify-center px-6 pb-20 pt-32 text-white sm:min-h-[570px] sm:px-10 lg:min-h-[620px] lg:px-12">
                            <span class="mb-5 inline-flex w-fit items-center gap-2 rounded-full border border-white/25 bg-white/10 px-4 py-2 text-xs font-semibold backdrop-blur">
                                <span class="size-2 rounded-full bg-[#f0c943]"></span>
                                Kamar kipas angin &amp; AC
                            </span>
                            <h1 class="max-w-3xl font-serif text-4xl font-medium leading-[1.08] tracking-tight sm:text-4xl lg:text-7xl">
                                Istirahat nyaman,<br class="hidden sm:block"> booking lebih mudah.
                            </h1>
                            <div class="mt-5 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center sm:gap-6">
                                <p class="max-w-lg text-sm leading-6 text-white/90 sm:text-base">
                                    Pilih kamar yang sesuai, tentukan waktu istirahat, lalu lanjutkan reservasi secara online.
                                </p>
                                <a href="#cari-kamar" class="inline-flex min-h-12 w-fit items-center justify-center rounded-full bg-[#f0c943] px-6 text-sm font-bold text-[#342d1e] shadow-lg transition hover:bg-[#f7d96c] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white">
                                    Cari kamar <span class="ml-2" aria-hidden="true">↓</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div id="cari-kamar" class="relative z-20 mx-auto -mt-20 max-w-7xl scroll-mt-8 px-4 sm:-mt-24 sm:px-8">
                        <form action="{{ route('rooms.index') }}" method="GET" class="rounded-2xl bg-white p-4 shadow-2xl shadow-[#172b24]/15 sm:p-5">
                            <div class="mb-3 flex flex-wrap items-end justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#66736b]">Mulai rencanakan istirahat Anda</p>
                                    <h2 class="mt-1 text-left text-lg font-bold text-[#26332e] sm:text-xl">Cari kamar</h2>
                                </div>
                                <span class="rounded-full bg-[#f1f4ef] px-3 py-1.5 text-xs font-semibold text-[#31584a]">Penginapan Anisa</span>
                            </div>
                            <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-[1.05fr_1.2fr_1fr_0.7fr_0.7fr_0.7fr_auto] lg:items-end">
                                <label class="block">
                                    <span class="mb-1.5 block text-xs font-semibold text-[#59665e]">Tipe kamar</span>
                                    <select name="room_type" class="min-h-11 w-full rounded-xl border border-[#dfe5dd] bg-white px-3 text-sm text-[#26332e] outline-none focus:border-[#31584a] focus:ring-2 focus:ring-[#31584a]/15">
                                        <option value="">Semua tipe kamar</option>
                                        <option value="fan">Kamar kipas angin</option>
                                        <option value="ac">Kamar AC</option>
                                    </select>
                                </label>
                                <label class="block">
                                    <span class="mb-1.5 block text-xs font-semibold text-[#59665e]">Tanggal &amp; waktu mulai</span>
                                    <input type="datetime-local" name="start_at" min="{{ now()->format('Y-m-d\TH:i') }}" value="{{ now()->addHour()->format('Y-m-d\TH:i') }}" required class="min-h-11 w-full rounded-xl border border-[#dfe5dd] bg-white px-3 text-sm text-[#26332e] outline-none focus:border-[#31584a] focus:ring-2 focus:ring-[#31584a]/15">
                                </label>
                                <label class="block">
                                    <span class="mb-1.5 block text-xs font-semibold text-[#59665e]">Durasi sewa</span>
                                    <select name="duration_type" data-duration-select class="min-h-11 w-full rounded-xl border border-[#dfe5dd] bg-white px-3 text-sm text-[#26332e] outline-none focus:border-[#31584a] focus:ring-2 focus:ring-[#31584a]/15">
                                        <option value="three_hours">3 jam</option>
                                        <option value="half_day">Setengah hari (12 jam)</option>
                                        <option value="overnight">Menginap / malam</option>
                                    </select>
                                </label>
                                <div data-nights-field hidden>
                                    <label class="block">
                                        <span class="mb-1.5 block text-xs font-semibold text-[#59665e]">Jumlah malam</span>
                                        <input type="number" name="nights" value="1" min="1" max="30" disabled class="min-h-11 w-full rounded-xl border border-[#dfe5dd] bg-white px-3 text-sm text-[#26332e] outline-none focus:border-[#31584a] focus:ring-2 focus:ring-[#31584a]/15">
                                    </label>
                                </div>
                                <label class="block">
                                    <span class="mb-1.5 block text-xs font-semibold text-[#59665e]">Jumlah tamu</span>
                                    <input type="number" name="guest_count" value="{{ request('guest_count') ?? 2 }}" min="1" max="10" class="min-h-11 w-full rounded-xl border border-[#dfe5dd] bg-white px-3 text-sm text-[#26332e] outline-none focus:border-[#31584a] focus:ring-2 focus:ring-[#31584a]/15">
                                </label>
                                <label class="block">
                                    <span class="mb-1.5 block text-xs font-semibold text-[#59665e]">Jumlah kamar</span>
                                    <input type="number" name="room_count" value="{{ request('room_count') ?? 1 }}" min="1" max="10" class="min-h-11 w-full rounded-xl border border-[#dfe5dd] bg-white px-3 text-sm text-[#26332e] outline-none focus:border-[#31584a] focus:ring-2 focus:ring-[#31584a]/15">
                                </label>
                                <button type="submit" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-[#31584a] px-5 text-sm font-bold text-white transition hover:bg-[#26332e] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#31584a]">
                                    <span aria-hidden="true">⌕</span> Cari
                                </button>
                            </div>
                            <p class="mt-2 text-[11px] leading-5 text-[#788276]">Pilih tipe dan jadwal lebih dulu. Durasi serta tanggal dapat disesuaikan kembali saat reservasi.</p>
                        </form>
                    </div>
                </section>
                <section id="tentang" class="mx-auto bg-[#faf8f2] max-w-7xl px-5 py-10 sm:px-8 sm:py-14">
                    <div class="grid gap-8 md:grid-cols-[1.05fr_0.95fr] md:items-center md:gap-12">
                        <div class="relative">
                            <img
                                src="{{ asset('images/kamar2.jpeg') }}"
                                alt="Kamar tidur dengan desain sederhana dan nyaman"
                                class="h-[260px] w-full rounded-2xl object-cover shadow-lg shadow-[#26332e]/10 sm:h-[360px]"
                            />
                            <span class="absolute bottom-4 left-4 rounded-full bg-[#f1ede4] px-4 py-2.5 text-[9px] font-semibold uppercase tracking-[0.16em] text-[#26332e] shadow-sm">Tempat untuk beristirahat</span>
                        </div>
                        <div class="py-2">
                            <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-[#66736b]">Tentang Penginapan Anisa</p>
                            <h2 class="mt-4 max-w-lg font-serif text-3xl text-[#31584a] font-medium leading-tight tracking-tight sm:text-4xl">
                                Sederhana dalam gaya, istimewa dalam rasa.
                            </h2>
                            <p class="mt-5 max-w-lg text-sm leading-7 text-[#31584a]">
                                Kami percaya istirahat yang baik dimulai dari tempat yang nyaman. Nikmati suasana tenang, kamar yang terawat, dan keramahan yang membuat Anda ingin kembali.
                            </p>
                            <a href="#fitur" class="mt-7 inline-flex items-center border-b border-[#77857a] pb-2 text-[10px] font-semibold uppercase tracking-[0.15em] transition hover:border-[#31584a] hover:text-[#31584a]">
                                Kenali tempat kami
                            </a>
                            <div class="mt-8 grid grid-cols-3 border-t border-[#e2e7df] pt-5">
                                <div>
                                    <p class="font-serif text-2xl sm:text-3xl">{{ $roomOptions->sum('available_count') }}</p>
                                    <p class="mt-1 text-[9px] uppercase tracking-[0.12em] text-[#66736b]">Kamar tersedia</p>
                                </div>
                                <div class="border-l border-[#e2e7df] pl-4 sm:pl-7">
                                    <p class="font-serif text-2xl sm:text-3xl">{{ $roomOptions->count() }}</p>
                                    <p class="mt-1 text-[9px] uppercase tracking-[0.12em] text-[#66736b]">Tipe kamar</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="bg-[#f1f4ef] py-10 sm:py-14">
                    <div class="mx-auto grid max-w-7xl gap-8 px-5 sm:px-8 md:grid-cols-2 md:items-center md:gap-10">
                        <div class="order-2 md:order-1">
                            <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-[#66736b]">Waktu untuk diri sendiri</p>
                            <h2 class="mt-4 max-w-md font-serif text-3xl text-[#31584a] font-medium leading-tight tracking-tight sm:text-4xl">
                                Lepaskan lelah. Nikmati momen.
                            </h2>
                            <p class="mt-5 max-w-md text-sm leading-7 text-[#66736b]">
                                Tersedia kamar kipas angin dan AC, dengan pilihan tarif untuk 3 jam, setengah hari, atau menginap.
                            </p>
                            <a href="{{ route('rooms.index') }}" class="mt-7 inline-flex min-h-12 items-center rounded-full bg-[#26332e] px-6 text-[10px] font-semibold uppercase tracking-[0.14em] text-white transition hover:bg-[#3d5148]">
                                Lihat pilihan kamar
                            </a>
                        </div>
                        <img
                            src="{{ asset('images/halaman2.jpg') }}"
                            alt="Area duduk dengan suasana hangat"
                            class="order-1 h-[260px] w-full rounded-2xl object-cover shadow-lg shadow-[#26332e]/10 sm:h-[350px] md:order-2"
                        />
                    </div>
                </section>

                <section id="kamar" class="mx-auto max-w-7xl px-5 py-12 sm:px-8 sm:py-16">
                    <div class="mb-7 flex flex-col gap-4 sm:mb-8 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-[#66736b]">Pilihan kamar</p>
                            <h2 class="mt-3 font-serif text-3xl font-medium tracking-tight sm:text-4xl">Temukan ruang Anda.</h2>
                        </div>
                        <a href="{{ route('rooms.index') }}" class="inline-flex w-fit items-center border-b border-[#77857a] pb-2 text-[10px] font-semibold uppercase tracking-[0.14em] transition hover:border-black">
                            Semua kamar
                        </a>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2 md:gap-7">
                        @foreach ($roomOptions as $option)
                            <article class="group">
                                <div class="relative overflow-hidden rounded-2xl bg-[#e2ddd1] shadow-md shadow-[#26332e]/10">
                                    <img src="{{ $option['uploaded_image'] ?? asset('images/'.$option['image']) }}" alt="{{ $option['label'] }}" class="h-[260px] w-full object-cover content-center transition duration-500 group-hover:scale-[1.03] sm:h-[360px]" />
                                    <span class="absolute left-4 top-4 rounded-full bg-[#faf8f2]/95 px-4 py-2 text-[9px] font-semibold uppercase tracking-[0.14em] text-[#26332e]">{{ $option['available_count'] }} kamar tersedia</span>
                                </div>
                                <div class="flex flex-col gap-4 pt-5 sm:flex-row sm:items-end sm:justify-between">
                                    <div>
                                        <h3 class="font-serif text-xl font-medium">{{ $option['label'] }}</h3>
                                        <p class="mt-2 max-w-sm text-xs leading-6 text-[#66736b]">{{ $option['description'] }}</p>
                                        <p class="mt-3 text-xs font-medium text-[#566158]">Mulai Rp {{ number_format($option['rates']['overnight'], 0, ',', '.') }} / malam</p>
                                    </div>
                                    <a href="{{ route('rooms.index') }}" class="inline-flex min-h-11 w-fit shrink-0 items-center justify-center rounded-full border border-[#26332e] px-5 text-[9px] font-semibold uppercase tracking-[0.12em] transition hover:bg-[#26332e] hover:text-white">
                                        Pilih kamar
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>

                <section id="fitur" class="border-y border-[#e6e9e2] bg-[#f1f4ef] py-10 sm:py-14">
                    <div class="mx-auto max-w-7xl px-5 sm:px-8">
                        <div class="max-w-2xl">
                            <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-[#66736b]">Pilihan untuk setiap kebutuhan</p>
                            <h2 class="mt-3 font-serif text-3xl font-medium leading-tight tracking-tight sm:text-4xl">Atur waktu istirahat sesuai rencana.</h2>
                        </div>
                        <div class="mt-7 grid gap-4 sm:grid-cols-3">
                            <article class="rounded-2xl border border-[#e2e7df] bg-white/70 p-5">
                                <span class="text-xs uppercase tracking-[0.18em] text-[#66736b]">01 / Durasi</span>
                                <h3 class="mt-4 font-serif text-xl">Sewa fleksibel</h3>
                                <p class="mt-3 text-xs leading-6 text-[#66736b]">Pilih tarif 3 jam, setengah hari, atau menginap sesuai kebutuhan.</p>
                            </article>
                            <article class="rounded-2xl border border-[#e2e7df] bg-white/70 p-5">
                                <span class="text-xs uppercase tracking-[0.18em] text-[#66736b]">02 / Tipe kamar</span>
                                <h3 class="mt-4 font-serif text-xl">Kipas angin atau AC</h3>
                                <p class="mt-3 text-xs leading-6 text-[#66736b]">Bandingkan pilihan kamar dan tarif sebelum membuat reservasi.</p>
                            </article>
                            <article class="rounded-2xl border border-[#e2e7df] bg-white/70 p-5">
                                <span class="text-xs uppercase tracking-[0.18em] text-[#66736b]">03 / Reservasi</span>
                                <h3 class="mt-4 font-serif text-xl">Pesan secara online</h3>
                                <p class="mt-3 text-xs leading-6 text-[#66736b]">Lihat kamar yang tersedia, pilih kamar, lalu lanjutkan proses booking.</p>
                            </article>
                        </div>
                    </div>
                </section>

                <section id="booking" class="mx-auto max-w-7xl px-5 py-12 sm:px-8 sm:py-16">
                    <div class="flex flex-col gap-5 border-b border-[#e2e7df] pb-8 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-[#66736b]">Informasi booking</p>
                            <h2 class="mt-3 font-serif text-3xl font-medium tracking-tight sm:text-4xl">Tarif menginap.</h2>
                        </div>
                        <p class="max-w-md text-xs leading-6 text-[#66736b]">Tarif berdasarkan tipe kamar dan durasi sewa. Ketersediaan kamar dapat berubah.</p>
                    </div>
                    <div class="grid gap-5 pt-7 sm:grid-cols-2 sm:gap-6">
                        @foreach ($roomOptions as $option)
                            <article class="rounded-2xl border border-[#e2e7df] border-t-2 border-t-[#31584a] bg-white p-5 shadow-sm shadow-[#26332e]/5 sm:p-6">
                                <div class="flex items-baseline justify-between gap-3">
                                    <h3 class="font-serif text-xl">{{ $option['short_label'] }}</h3>
                                    <span class="text-[10px] uppercase tracking-[0.12em] text-[#66736b]">{{ $option['available_count'] }} kamar tersedia</span>
                                </div>
                                <dl class="mt-5 space-y-3 text-xs">
                                    <div class="flex justify-between border-b border-[#e6e9e2] pb-3">
                                        <dt class="text-[#66736b]">3 jam</dt>
                                        <dd class="font-semibold">Rp {{ number_format($option['rates']['three_hours'], 0, ',', '.') }}</dd>
                                    </div>
                                    <div class="flex justify-between border-b border-[#e6e9e2] pb-3">
                                        <dt class="text-[#66736b]">Setengah hari</dt>
                                        <dd class="font-semibold">Rp {{ number_format($option['rates']['half_day'], 0, ',', '.') }}</dd>
                                    </div>
                                    <div class="flex justify-between border-b border-[#e6e9e2] pb-3">
                                        <dt class="text-[#66736b]">Menginap / malam</dt>
                                        <dd class="font-semibold">Rp {{ number_format($option['rates']['overnight'], 0, ',', '.') }}</dd>
                                    </div>
                                </dl>
                            </article>
                        @endforeach
                    </div>
                    <a href="{{ route('rooms.index') }}" class="mt-9 inline-flex min-h-12 items-center justify-center rounded-full bg-[#26332e] px-6 text-[10px] font-semibold uppercase tracking-[0.14em] text-white transition hover:bg-[#3d5148]">
                        Cek ketersediaan kamar
                    </a>
                </section>

                <section id="testimoni" class="bg-[#eef1eb] py-8 sm:py-12">
                    <div class="mx-auto max-w-7xl px-5 sm:px-8">
                        <div class="rounded-2xl bg-[#faf8f2] p-4 shadow-sm shadow-[#26332e]/5 sm:p-6">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                                <div>
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-[#66736b]">Ulasan pengunjung</p>
                                    <h2 class="mt-2 font-serif text-2xl font-medium leading-tight tracking-tight sm:text-3xl">Pengalaman para tamu.</h2>
                                </div>
                                <p class="max-w-sm text-xs leading-5 text-[#66736b]">Pengalaman singkat dari tamu yang pernah menginap di Penginapan Anisa.</p>
                            </div>

                            @php
                                $ratedTestimonials = $testimonials->whereNotNull('rating');
                                $averageRating = $ratedTestimonials->isNotEmpty() ? $ratedTestimonials->avg('rating') : null;
                            @endphp
                            @if ($testimonials->isNotEmpty())
                                <div class="mt-5 flex flex-wrap items-center gap-x-4 gap-y-2 rounded-xl border border-[#e2e7df] bg-white px-4 py-3">
                                    @if ($averageRating !== null)
                                        <span class="text-3xl font-bold leading-none text-[#26332e]">{{ number_format($averageRating, 1, ',', '.') }}</span>
                                        <div>
                                            <p class="text-sm leading-none text-[#c28a55]" role="img" aria-label="Rata-rata {{ number_format($averageRating, 1, ',', '.') }} dari 5 bintang">
                                                @for ($star = 1; $star <= 5; $star++)
                                                    <span aria-hidden="true">{{ $star <= round($averageRating) ? '★' : '☆' }}</span>
                                                @endfor
                                            </p>
                                            <p class="mt-1 text-[11px] text-[#66736b]">Rata-rata dari {{ $ratedTestimonials->count() }} penilaian</p>
                                        </div>
                                    @else
                                        <p class="text-sm font-semibold text-[#26332e]">Belum ada rating</p>
                                    @endif
                                    <span class="border-l border-[#e2e7df] pl-4 text-xs text-[#66736b]">{{ $testimonials->count() }} ulasan tamu</span>
                                </div>
                            @endif

                            <details data-testimonial-form class="group mt-5 border-t border-[#e2e7df] pt-4" @if ($errors->any() || session()->has('success')) open @endif>
                                <summary class="flex min-h-10 w-fit cursor-pointer list-none items-center justify-center gap-2 rounded-lg bg-[#31584a] px-4 text-xs font-semibold text-white transition hover:bg-[#26332e] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#26332e] [&::-webkit-details-marker]:hidden">
                                    Tulis ulasan
                                    <span aria-hidden="true" class="text-base leading-none transition-transform group-open:rotate-45">+</span>
                                </summary>
                                <div class="mt-4 max-w-4xl rounded-xl border border-[#e2e7df] bg-white p-4 shadow-sm sm:p-5">
                                    @if (session('success'))
                                        <div role="status" class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-800">
                                            {{ session('success') }}
                                        </div>
                                    @endif

                                    <form action="{{ route('testimonials.store') }}" method="POST" class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_17rem]">
                                        @csrf
                                        <div>
                                            <label for="testimonial-name" class="mb-1.5 block text-xs font-semibold text-[#46554d]">Nama</label>
                                            <input
                                                id="testimonial-name"
                                                name="name"
                                                type="text"
                                                value="{{ old('name') }}"
                                                maxlength="100"
                                                required
                                                autocomplete="name"
                                                placeholder="Nama Anda"
                                                class="min-h-10 w-full rounded-lg border border-[#d8ddd5] bg-white px-3 py-2 text-sm text-[#26332e] outline-none transition placeholder:text-[#92988f] focus:border-[#31584a] focus:ring-2 focus:ring-[#31584a]/15"
                                            />
                                            @error('name')
                                                <p class="mt-2 text-xs text-red-700">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div>
                                            <fieldset aria-describedby="testimonial-rating-hint">
                                                <legend class="mb-1.5 text-xs font-semibold text-[#46554d]">Rating</legend>
                                                <p id="testimonial-rating-hint" class="mb-1.5 text-[11px] text-[#66736b]">Pilih bintang untuk pengalaman Anda.</p>
                                                <div class="testimonial-rating-options w-fit rounded-lg border border-[#e2e7df] bg-[#f8f9f6] px-2 py-0.5">
                                                    @for ($rating = 5; $rating >= 1; $rating--)
                                                        <input
                                                            id="testimonial-rating-{{ $rating }}"
                                                            class="testimonial-rating-input sr-only"
                                                            type="radio"
                                                            name="rating"
                                                            value="{{ $rating }}"
                                                            required
                                                            @checked((int) old('rating') === $rating)
                                                        />
                                                        <label
                                                            for="testimonial-rating-{{ $rating }}"
                                                            aria-label="{{ $rating }} bintang"
                                                            class="testimonial-rating-star inline-flex size-7 items-center justify-center rounded-md text-xl"
                                                        >★</label>
                                                    @endfor
                                                </div>
                                            </fieldset>
                                            @error('rating')
                                                <p class="mt-2 text-xs text-red-700">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div class="sm:col-span-2">
                                            <div class="mb-1.5 flex items-center justify-between gap-3">
                                                <label for="testimonial-content" class="block text-xs font-semibold text-[#46554d]">Ulasan Anda</label>
                                                <span id="testimonial-content-count" class="text-[10px] text-[#66736b]">{{ mb_strlen(old('content', '')) }} / 1000 karakter</span>
                                            </div>
                                            <textarea
                                                id="testimonial-content"
                                                name="content"
                                                rows="2"
                                                minlength="10"
                                                maxlength="1000"
                                                required
                                                placeholder="Bagaimana pengalaman Anda selama menginap?"
                                                aria-describedby="testimonial-content-count"
                                                class="min-h-20 w-full resize-y rounded-lg border border-[#d8ddd5] bg-white px-3 py-2 text-sm leading-5 text-[#26332e] outline-none transition placeholder:text-[#92988f] focus:border-[#31584a] focus:ring-2 focus:ring-[#31584a]/15"
                                            >{{ old('content') }}</textarea>
                                            <p class="mt-1 text-[11px] text-[#66736b]">Ulasan Anda membantu tamu lain memilih kamar.</p>
                                            @error('content')
                                                <p class="mt-2 text-xs text-red-700">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <button type="submit" class="inline-flex min-h-10 w-fit items-center justify-center rounded-lg bg-[#31584a] px-4 text-sm font-semibold text-white transition hover:bg-[#26332e] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#26332e] sm:col-span-2 sm:justify-self-end">
                                            Kirim ulasan
                                        </button>
                                    </form>
                                </div>
                            </details>

                            @if ($testimonials->count() > 1)
                                <div class="mt-5 flex justify-end">
                                    <button
                                        type="button"
                                        data-testimonial-toggle
                                        aria-pressed="false"
                                        aria-label="Jeda geser ulasan otomatis"
                                        class="inline-flex min-h-9 items-center rounded-lg border border-[#d8ddd5] bg-white px-3 text-xs font-medium text-[#5d6b62] transition hover:border-[#31584a] hover:text-[#31584a] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#26332e]"
                                        hidden
                                    >Jeda otomatis</button>
                                </div>
                            @endif
                            <div class="testimonial-scroller mt-5 overflow-x-auto pb-2 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#31584a]" data-testimonial-scroller role="region" aria-label="Daftar testimoni tamu" tabindex="0">
                                @if ($testimonials->isNotEmpty())
                                    <div class="flex w-max" data-testimonial-track>
                                        <div class="flex w-max shrink-0 gap-3 pr-3" data-testimonial-group>
                                            @foreach ($testimonials as $testimonial)
                                                <blockquote class="flex w-[min(85vw,22rem)] shrink-0 flex-col rounded-xl border border-[#e2e7df] bg-white p-4 text-sm leading-5 text-[#4a554f] shadow-sm shadow-[#26332e]/5">
                                                    <div class="flex items-center justify-between gap-3">
                                                        <div class="flex min-w-0 items-center gap-2.5">
                                                            <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-[#f1f4ef] text-sm font-bold uppercase text-[#31584a]" aria-hidden="true">{{ mb_strtoupper(mb_substr((string) (data_get($testimonial, 'name') ?: 'T'), 0, 1)) }}</span>
                                                            <div class="min-w-0">
                                                                <p class="truncate text-xs font-semibold text-[#26332e]">{{ data_get($testimonial, 'name') }}</p>
                                                                @if (data_get($testimonial, 'created_at'))
                                                                    <time class="text-[10px] text-[#8a918b]" datetime="{{ data_get($testimonial, 'created_at')->toDateString() }}">{{ data_get($testimonial, 'created_at')->format('d/m/Y') }}</time>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        @if (data_get($testimonial, 'rating'))
                                                            <span class="shrink-0 rounded-md bg-[#f1f4ef] px-2 py-1 text-[10px] font-semibold text-[#31584a]" aria-label="{{ data_get($testimonial, 'rating') }} dari 5 bintang">
                                                                {{ data_get($testimonial, 'rating') }} <span aria-hidden="true" class="text-[#c28a55]">★</span>
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <p class="mt-3 line-clamp-4 text-xs leading-5">“{{ data_get($testimonial, 'content') }}”</p>
                                                </blockquote>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <p class="w-full rounded-2xl border border-dashed border-[#c8c8bc] bg-white/70 p-5 text-sm leading-6 text-[#66736b]">Belum ada testimoni tamu. Jadilah yang pertama berbagi pengalaman menginap Anda.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </section>
            </main>

            <footer class="bg-[#26332e] text-white">
                <div class="grid min-h-[420px] lg:grid-cols-2">
                    <div class="flex flex-col justify-between px-5 py-8 sm:px-8 sm:py-10 lg:px-12 lg:py-12">
                        <div>
                            <h2 class="max-w-md font-sans text-2xl font-semibold leading-[1.18] tracking-tight sm:text-3xl">
                                Ada yang ingin ditanyakan?
                            </h2>
                            <p class="mt-3 max-w-md text-[11px] leading-5 text-white/50">
                                Hubungi kami atau lihat informasi penginapan sebelum membuat reservasi.
                            </p>
                        </div>

                        <div class="mt-10 grid grid-cols-2 gap-8 text-[11px] sm:grid-cols-3 sm:gap-12">
                            <div>
                                <h3 class="mb-4 text-[13px] font-medium text-white/90">Jelajahi</h3>
                                <div class="space-y-2 text-[10px] text-white/45">
                                    <a href="#tentang" class="block transition hover:text-white">Tentang kami</a>
                                    <a href="#kamar" class="block transition hover:text-white">Kamar &amp; Tarif</a>
                                    <a href="#fitur" class="block transition hover:text-white">Fasilitas</a>
                                    <a href="#testimoni" class="block transition hover:text-white">Testimoni</a>
                                </div>
                            </div>
                            <div>
                                <h3 class="mb-4 text-[13px] font-medium text-white/90">Reservasi</h3>
                                <div class="space-y-2 text-[10px] text-white/45">
                                    <a href="#booking" class="block transition hover:text-white">Informasi tarif</a>
                                    <a href="{{ route('rooms.index') }}" class="block transition hover:text-white">Pilih kamar</a>
                                    <a href="https://wa.me/6285849388597" target="_blank" rel="noopener noreferrer" class="block transition hover:text-white">Hubungi via WhatsApp</a>
                                </div>
                            </div>
                            <div class="hidden sm:block">
                                <h3 class="mb-4 text-[13px] font-medium text-white/90">Media sosial</h3>
                                <div class="space-y-2 text-[10px] text-white/45">
                                    <a href="https://www.instagram.com/penginapan_anisa/" target="_blank" rel="noopener noreferrer" class="block transition hover:text-white">Instagram</a>
                                    <a href="https://www.tiktok.com/@penginapan_anisa" target="_blank" rel="noopener noreferrer" class="block transition hover:text-white">Tiktok</a>
                                    <a href="https://wa.me/6285849388597" target="_blank" rel="noopener noreferrer" class="block transition hover:text-white">WhatsApp</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex min-h-[420px] flex-col bg-[#26332e]">
                        <div class="flex h-[180px] justify-end overflow-hidden sm:h-[220px]">
                            <img
                                src="{{ asset('images/halaman1.jpeg') }}"
                                alt="Suasana kamar dan ruang istirahat Penginapan Anisa"
                                class="h-full w-[72%] object-cover"
                            />
                        </div>
                        <div class="flex flex-1 items-end px-5 pb-8 sm:px-8 lg:px-12 lg:pb-10">
                            <div class="w-full">
                                <p class="font-sans text-3xl font-bold leading-none tracking-tight sm:text-4xl lg:text-5xl">Penginapan Anisa</p>
                                <div class="mt-10 flex items-center justify-between border-t border-white/10 pt-5 text-[9px] uppercase tracking-[0.14em] text-white/40">
                                    <span>© {{ date('Y') }} Penginapan Anisa</span>
                                    <a href="{{ route('home') }}" class="font-bold tracking-[0.18em] text-white/70">ANISA</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
        <script>
            const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const revealElements = document.querySelectorAll(
                'main > section:not(:first-child), main > section:not(:first-child):not(#testimoni) article, main > section:not(:first-child):not(#testimoni) img, footer > div > div:first-child > div'
            );

            if (!reducedMotion && 'IntersectionObserver' in window) {
                const revealObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('is-visible');
                            observer.unobserve(entry.target);
                        }
                    });
                }, {
                    threshold: 0.12,
                    rootMargin: '0px 0px -40px 0px',
                });

                revealElements.forEach((element, index) => {
                    const kind = element.matches('img') ? 'image' : element.matches('article') ? 'card' : 'section';
                    element.dataset.scrollReveal = kind;
                    element.style.setProperty('--scroll-reveal-delay', `${(index % 5) * 90}ms`);
                    revealObserver.observe(element);
                });

                document.documentElement.classList.add('scroll-reveal-enabled');
            }

            const reviewText = document.querySelector('#testimonial-content');
            const characterCount = document.querySelector('#testimonial-content-count');

            if (reviewText && characterCount) {
                reviewText.addEventListener('input', () => {
                    characterCount.textContent = `${reviewText.value.length} / 1000 karakter`;
                });
            }

            const reviewSlider = document.querySelector('[data-testimonial-scroller]');
            const reviewTrack = document.querySelector('[data-testimonial-track]');
            const reviewGroup = document.querySelector('[data-testimonial-group]');
            const pauseButton = document.querySelector('[data-testimonial-toggle]');

            if (reviewSlider && reviewTrack && reviewGroup && pauseButton && !reducedMotion) {
                const reviews = Array.from(reviewGroup.querySelectorAll('blockquote'));

                if (reviews.length > 1) {
                    const duplicateGroup = reviewGroup.cloneNode(true);
                    duplicateGroup.setAttribute('aria-hidden', 'true');
                    reviewTrack.append(duplicateGroup);

                    let isPaused = false;
                    let isPointerDown = false;
                    let previousFrameTime = 0;
                    let hasOverflow = false;
                    let scrollPosition = 0;

                    const updatePauseButton = () => {
                        hasOverflow = reviewGroup.scrollWidth > reviewSlider.clientWidth + 1;
                        pauseButton.hidden = !hasOverflow;
                        scrollPosition = reviewSlider.scrollLeft;

                        if (!hasOverflow) {
                            reviewSlider.scrollLeft = 0;
                            scrollPosition = 0;
                        }
                    };

                    const animateReviews = (timestamp) => {
                        if (previousFrameTime && !isPaused && !isPointerDown && !document.hidden && hasOverflow) {
                            const elapsed = Math.min(timestamp - previousFrameTime, 50);
                            scrollPosition += elapsed * 0.018;

                            if (scrollPosition >= reviewGroup.scrollWidth) {
                                scrollPosition -= reviewGroup.scrollWidth;
                            }

                            reviewSlider.scrollLeft = scrollPosition;
                        }

                        previousFrameTime = timestamp;
                        window.requestAnimationFrame(animateReviews);
                    };

                    pauseButton.hidden = false;
                    updatePauseButton();
                    window.addEventListener('resize', updatePauseButton, { passive: true });

                    pauseButton.addEventListener('click', () => {
                        isPaused = !isPaused;
                        pauseButton.setAttribute('aria-pressed', String(isPaused));
                        pauseButton.textContent = isPaused ? 'Lanjutkan otomatis' : 'Jeda otomatis';
                        pauseButton.setAttribute('aria-label', isPaused ? 'Lanjutkan geser ulasan otomatis' : 'Jeda geser ulasan otomatis');
                    });

                    reviewSlider.addEventListener('pointerdown', () => {
                        isPointerDown = true;
                        scrollPosition = reviewSlider.scrollLeft;
                    });
                    window.addEventListener('pointerup', () => {
                        isPointerDown = false;
                        scrollPosition = reviewSlider.scrollLeft;
                    });
                    window.addEventListener('pointercancel', () => {
                        isPointerDown = false;
                        scrollPosition = reviewSlider.scrollLeft;
                    });
                    reviewSlider.addEventListener('wheel', () => {
                        scrollPosition = reviewSlider.scrollLeft;
                    }, { passive: true });

                    window.requestAnimationFrame(updatePauseButton);
                    window.addEventListener('load', updatePauseButton, { once: true });
                    window.requestAnimationFrame(animateReviews);
                }
            }

        </script>
    </body>
</html>
