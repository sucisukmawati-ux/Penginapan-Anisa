<x-layouts::app :title="__('Halaman tidak ditemukan')">
    <section class="mx-auto flex min-h-[65vh] w-full max-w-3xl flex-col items-center justify-center px-5 py-16 text-center">
        <p class="font-serif text-7xl font-semibold text-[#d9cdb8]">404</p>
        <h1 class="mt-4 font-serif text-3xl font-semibold text-[#26332e] sm:text-4xl">Kamar tidak ditemukan</h1>
        <p class="mt-3 max-w-lg text-sm leading-6 text-[#66736b]">
            Tautan yang dibuka mungkin sudah berubah atau kamar sudah dihapus dari daftar.
        </p>
        <div class="mt-7 flex flex-wrap justify-center gap-3">
            <a href="{{ route('rooms.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-full bg-[#31584a] px-5 text-sm font-semibold text-white transition hover:bg-[#26332e]">
                Lihat daftar kamar
            </a>
            <a href="{{ route('home') }}" class="inline-flex min-h-11 items-center justify-center rounded-full border border-[#d8ddd5] px-5 text-sm font-semibold text-[#46554d] transition hover:bg-[#f1f4ef]">
                Kembali ke beranda
            </a>
        </div>
    </section>
</x-layouts::app>
