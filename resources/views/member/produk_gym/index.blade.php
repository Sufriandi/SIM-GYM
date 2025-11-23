{{-- resources/views/member/produk_gym/index.blade.php --}}

@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Js; 
    use Illuminate\Support\Facades\Auth; 

    $adminNumber = app(\App\Http\Controllers\Member\ProdukGymController::class)->getAdminNumber();
    $search = $search ?? '';
    $kategori = $kategori ?? 'all';
    $kategoriOptions = $kategoriOptions ?? ['minuman', 'suplemen', 'lainnya'];

    // --- DATA MEMBER UNTUK WHATSAPP ---
    $member = Auth::user();
    $memberName = $member->name;
    $memberIdentifier = "{$memberName}";
    // ------------------------------------
@endphp

<x-layouts.member 
    :title="$pageTitle ?? 'Marketplace Produk'"
    :page-title="$pageTitle ?? 'Produk Gym'"
    page-subtitle="Pilih dan beli suplemen atau aksesoris gym favoritmu."
>
    
    {{-- JUDUL HALAMAN MENGGUNAKAN X-UI.SECTION-HEADER (TETAP) --}}
    <x-ui.section-header
        :title="$pageTitle ?? 'Produk Gym'"
        subtitle="Pilih dan beli suplemen atau aksesoris gym favoritmu."
    />

    {{-- PEMBATAS DI BAWAH SUBTITLE --}}
    <hr class="border-t border-brand-borderSoft mb-6">
    
    {{-- TAMPILKAN PESAN FLASH (TETAP) --}}
    @if (session('success'))
        <div class="bg-success-soft border border-success text-success-dark px-4 py-3 rounded relative mb-4">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="bg-danger-soft border border-danger text-danger px-4 py-3 rounded relative mb-4">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif
    
    {{-- BARIS PENCARIAN & FILTER (TETAP) --}}
    <div class="mb-6">
        <form method="GET" action="{{ route('member.produk_gym.index') }}" class="flex flex-col md:flex-row items-end gap-3">
            
            {{-- INPUT PENCARIAN --}}
            <div class="w-full md:w-3/5">
                <x-ui.label for="search">Pencarian Produk</x-ui.label>
                <div class="relative">
                    <input type="search" id="search" name="search" placeholder="Cari nama produk atau deskripsi..."
                            value="{{ $search }}"
                            class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-4 py-2 pl-10 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-gold-500 focus:border-transparent">
                    <i data-lucide="search" class="w-4 h-4 text-text-muted absolute left-3 top-1/2 transform -translate-y-1/2"></i>
                </div>
            </div>

            {{-- DROPDOWN FILTER KATEGORI --}}
            <div class="w-full md:w-1/5">
                <x-ui.label for="kategori">Filter Kategori</x-ui.label>
                <select id="kategori" name="kategori" class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent">
                    <option value="all" {{ $kategori === 'all' ? 'selected' : '' }}>Semua Kategori</option>
                    @foreach ($kategoriOptions as $option)
                        <option value="{{ $option }}" {{ $kategori === $option ? 'selected' : '' }}>
                            {{ ucwords($option) }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            {{-- TOMBOL SUBMIT/RESET --}}
            <div class="w-full md:w-1/5 flex gap-2 pt-1 md:pt-0">
                
                {{-- TOMBOL FILTER MERAH --}}
                <button type="submit" class="w-1/2 md:w-auto flex-grow bg-red-600 text-white font-bold py-2 px-4 rounded-xl hover:bg-red-700 transition">
                    Filter
                </button>
                
                @if ($search || $kategori != 'all')
                    <a href="{{ route('member.produk_gym.index') }}" class="w-1/2 md:w-auto flex-grow text-center bg-gray-200 text-brand-black font-bold py-2 px-4 rounded-xl hover:bg-gray-300 transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>
    {{-- AKHIR BARIS PENCARIAN & FILTER --}}
    
    @if($produks->isEmpty())
        <div class="p-8 bg-brand-card rounded-xl text-center text-text-muted">
            <i data-lucide="package-x" class="w-8 h-8 mx-auto mb-3"></i>
            <p>Maaf, tidak ditemukan produk yang sesuai dengan kriteria pencarian Anda.</p>
        </div>
    @else
        <h3 class="text-xl font-semibold text-text-main mb-4">Daftar Produk Tersedia</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach ($produks as $produk)
                @php
                    $imageUrl = $produk->foto ? Storage::url($produk->foto) : 'https://placehold.co/400x300/3A2D2A/F5E6D6?text=GYM+PRODUCT';
                @endphp
                <div class="bg-brand-card border border-brand-borderSoft rounded-xl overflow-hidden shadow-lg transform hover:scale-[1.02] transition-all duration-300 flex flex-col">
                    
                    {{-- FOTO PRODUK --}}
                    <div class="h-48 w-full overflow-hidden bg-brand-surface-50">
                        <img 
                            src="{{ $imageUrl }}" 
                            alt="{{ $produk->nama }}" 
                            class="w-full h-full object-cover"
                            onerror="this.onerror=null; this.src='https://placehold.co/400x300/3A2D2A/F5E6D6?text=GYM+PRODUCT';"
                        >
                    </div>

                    {{-- DETAIL PRODUK --}}
                    <div class="p-4 flex flex-col flex-grow">
                        <span class="text-xs font-medium text-gold-500 uppercase tracking-wider mb-1">{{ $produk->kategori }}</span>
                        <h3 class="text-lg font-bold text-text-main mb-2">{{ $produk->nama }}</h3>
                        
                        <p class="text-2xl font-extrabold text-success mb-3">
                            {{ 'Rp ' . number_format($produk->harga, 0, ',', '.') }}
                        </p>
                        
                        <p class="text-xs text-text-muted mb-4 flex-grow">
                            {{ Str::limit($produk->deskripsi, 60) }}
                        </p>
                        
                        {{-- STOK & TOMBOL BELI --}}
                        <div class="mt-auto">
                            <p class="text-sm font-semibold mb-3 {{ $produk->stok > 5 ? 'text-primary' : 'text-danger' }}">
                                Stok: {{ $produk->stok }} unit
                            </p>
                            
                            @if ($produk->stok > 0)
                                <button 
                                    type="button" 
                                    class="w-full bg-red-600 text-white font-bold py-2 rounded-lg hover:bg-red-700 transition-colors"
                                    onclick="if(window.bukaModalProduk) window.bukaModalProduk({{ Js::from($produk->nama) }}, {{ $produk->harga }}, {{ Js::from($imageUrl) }})"
                                >
                                    Beli Sekarang
                                </button>
                            @else
                                <button disabled class="w-full bg-gray-400 text-gray-700 font-bold py-2 rounded-lg">
                                    Stok Habis
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- PAGINATION --}}
        <div class="mt-8">
            {{ $produks->appends(['search' => $search, 'kategori' => $kategori])->links() }}
        </div>
    @endif
    
    {{-- MODAL KONFIRMASI WHATSAPP --}}
    <div 
        x-data="modalData()"
    >
        <div
            x-show="showConfirmModal"
            x-cloak
            x-transition
            class="fixed inset-0 z-50 flex items-center justify-center px-4 py-4 bg-black/60 backdrop-blur-sm" 
        >
            <div
                @click.away="showConfirmModal = false"
                class="relative w-full max-w-md rounded-2xl shadow-2xl bg-brand-card border border-brand-borderSoft overflow-hidden" 
            >
                {{-- Judul Modal --}}
                <div class="px-5 pt-5 pb-3 text-xl font-bold text-text-main border-b border-brand-borderSoft">
                    Konfirmasi Pemesanan
                </div>

                {{-- Area Gambar --}}
                <div class="h-48 w-full overflow-hidden bg-brand-surface-50 relative p-4 flex items-center justify-center border-b border-brand-borderSoft">
                    <img :src="productImage" alt="Produk Preview" class="max-h-full max-w-full object-contain rounded-lg">
                </div>

                {{-- Konten Konfirmasi --}}
                <div class="p-4 space-y-3">
                    <h3 class="text-xl font-bold text-text-main mb-2">
                        Pemesanan: <strong class="text-gold-500" x-text="productName"></strong>
                    </h3>

                    {{-- INPUT JUMLAH PEMBELIAN --}}
                    <div>
                        <label for="quantity" class="block text-sm font-medium text-text-main mb-2">Jumlah Unit Dipesan:</label>
                        <div class="flex items-center space-x-2">
                            <button 
                                @click="productQuantity = Math.max(1, productQuantity - 1)" 
                                :disabled="productQuantity <= 1"
                                class="p-1.5 border border-brand-borderSoft rounded-lg bg-brand-shell text-text-main hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                <i data-lucide="minus" class="w-4 h-4"></i>
                            </button>
                            <input 
                                type="number" 
                                id="quantity" 
                                x-model.number="productQuantity" 
                                min="1" 
                                class="w-16 text-center rounded-lg border border-brand-borderSoft bg-brand-shell text-base font-bold text-text-main py-2 focus:outline-none focus:ring-2 focus:ring-gold-500 [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none appearance-none"
                                required
                            >
                            <button 
                                @click="productQuantity += 1" 
                                class="p-1.5 border border-brand-borderSoft rounded-lg bg-brand-shell text-text-main hover:bg-gray-200">
                                <i data-lucide="plus" class="w-4 h-4"></i>
                            </button>
                        </div>
                        <p class="mt-2 text-xs text-text-muted">Total Harga Estimasi: <strong x-text="'Rp ' + (productPrice * productQuantity).toLocaleString('id-ID')"></strong></p>
                    </div>
                    {{-- AKHIR INPUT JUMLAH PEMBELIAN --}}
                    
                    <p class="text-base text-text-main">
                        Apakah Anda yakin ingin memesan <strong x-text="productQuantity"></strong> unit produk ini?
                    </p>
                    
                    <div class="text-sm text-text-muted bg-brand-shell p-3 rounded-lg border border-brand-borderSoft">
                        <p>
                            Anda akan diarahkan ke **WhatsApp Admin** untuk konfirmasi ketersediaan stok dan menyelesaikan pembayaran. Jumlah unit (<strong x-text="productQuantity"></strong>) akan tercantum di pesan.
                        </p>
                    </div>
                </div>
                
                {{-- Footer Tombol Aksi --}}
                <div class="px-5 py-3 flex justify-end gap-3 border-t border-brand-borderSoft bg-brand-shell">
                    <button type="button" @click="showConfirmModal = false" 
                            class="text-sm font-medium text-text-muted hover:text-text-main transition-colors py-2 px-3 rounded-lg">
                        Batal
                    </button>
                    <a :href="generateWhatsappLink()" target="_blank" @click="showConfirmModal = false"
                        class="bg-green-600 text-white font-bold py-2 px-4 rounded-lg text-sm hover:bg-green-700 transition-colors flex items-center gap-2 shadow-lg shadow-green-900/40">
                        <i data-lucide="message-square" class="w-4 h-4"></i>
                        Pesan via WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function modalData() {
            return {
                showConfirmModal: false,
                productName: '',
                productPrice: 0,
                productImage: '',
                productQuantity: 1,
                adminNumber: '{{ $adminNumber }}',
                memberIdentifier: {{ Js::from($memberIdentifier) }},
                
                init() {
                    // Fungsi global untuk membuka modal
                    window.bukaModalProduk = (name, price, image) => {
                        this.productName = name;
                        this.productPrice = price;
                        this.productImage = image;
                        this.productQuantity = 1;
                        this.showConfirmModal = true;
                    };
                },
                
                generateWhatsappLink() {
                    const message = encodeURIComponent(
                        `Pemesanan Produk Gym :\n\n` + 
                        `Identitas Pemesan: nama : ${this.memberIdentifier}\n\n` + 
                        `Produk Dipesan:\n` +
                        `Produk: ${this.productName}\n` +
                        `Harga: Rp ${this.productPrice.toLocaleString('id-ID')}\n` +
                        `Jumlah: ${this.productQuantity} unit\n\n` + 
                        `Mohon konfirmasi ketersediaan stok dan total pembayaran.`
                    );
                    return `https://wa.me/${this.adminNumber}?text=${message}`;
                }
            }
        }
    </script>
</x-layouts.member>