@extends('layouts.erp')

@section('title', 'POS / Kasir')
@section('subtitle', 'Buat transaksi baru')

@push('scripts')
<script>
function pos() {
    return {
        customers: {!! json_encode($catalog['customers']) !!},
        frames: {!! json_encode($catalog['frames']) !!},
        lenses: {!! json_encode($catalog['lenses']) !!},
        promos: {!! json_encode($catalog['promos']) !!},
        rx: {!! json_encode($catalog['rx']) !!},

        customer_id: '{{ old('customer_id') }}',
        custQ: '', frameQ: '', lensQ: '',
        cart: [],
        promo_id: '{{ old('promo_id') }}',
        payment_method: '{{ old('payment_method', 'cash') }}',
        payment_status: '{{ old('payment_status', 'paid') }}',
        dp_amount: '{{ old('dp_amount') }}',
        status: '{{ old('status', 'ordered') }}',
        rx_mode: '{{ old('new_prescription') ? 'new' : 'existing' }}',
        prescription_id: '{{ old('prescription_id') }}',
        notes: '{{ old('notes') }}',
        quick: { open: false, name: '', phone: '', busy: false },
        busy: false,
        error: '',

        fmt(n) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n)); },

        filteredCustomers() {
            const q = this.custQ.toLowerCase();
            return this.customers.filter(c => !q || c.name.toLowerCase().includes(q) || c.phone.includes(q) || c.member_id.toLowerCase().includes(q));
        },
        filteredFrames() {
            const q = this.frameQ.toLowerCase();
            return this.frames.filter(f => !q || f.name.toLowerCase().includes(q) || f.brand.toLowerCase().includes(q) || f.sku.toLowerCase().includes(q));
        },
        filteredLenses() {
            const q = this.lensQ.toLowerCase();
            return this.lenses.filter(l => !q || l.name.toLowerCase().includes(q) || l.brand.toLowerCase().includes(q) || l.type.toLowerCase().includes(q));
        },
        custRx() {
            return this.rx.filter(r => r.customer_id == this.customer_id);
        },

        add(type, item) {
            const found = this.cart.find(c => c.type === type && c.id === item.id);
            if (found) {
                if (found.qty < item.stock) found.qty++;
                return;
            }
            if (item.stock < 1) { this.error = item.name + ' stok habis.'; return; }
            this.error = '';
            this.cart.push({ type, id: item.id, name: item.name, price: item.price, qty: 1, stock: item.stock });
        },
        remove(i) { this.cart.splice(i, 1); },
        inCart(type, id) { return this.cart.some(c => c.type === type && c.id === id); },
        get inCartCount() { return this.cart.length; },

        get subtotal() { return this.cart.reduce((s, c) => s + c.price * c.qty, 0); },
        get discount() {
            const p = this.promos.find(x => x.id == this.promo_id);
            if (!p || this.subtotal < p.min) return 0;
            const d = p.type === 'nominal' ? p.value : this.subtotal * p.value / 100;
            return Math.min(this.subtotal, Math.round(d));
        },
        get total() { return Math.max(0, this.subtotal - this.discount); },
        get payNow() { return this.payment_status === 'paid' ? this.total : (parseFloat(this.dp_amount) || 0); },
        get promoOk() {
            const p = this.promos.find(x => x.id == this.promo_id);
            return !p || this.subtotal >= p.min;
        },

        async saveCustomer() {
            if (!this.quick.name || !this.quick.phone) return;
            this.quick.busy = true;
            try {
                const res = await fetch('{{ route('pos.customers') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ name: this.quick.name, phone: this.quick.phone }),
                });
                const data = await res.json();
                if (!res.ok) {
                    this.error = data.message || (data.errors && Object.values(data.errors).flat()[0]) || 'Gagal menyimpan customer.';
                    return;
                }
                this.customers.push(data);
                this.customer_id = data.id;
                this.quick = { open: false, name: '', phone: '', busy: false };
                this.error = '';
            } catch (e) {
                this.error = 'Gagal terhubung ke server.';
            } finally {
                this.quick.busy = false;
            }
        },

        validate() {
            if (!this.customer_id) return 'Pilih customer terlebih dahulu.';
            if (!this.cart.length) return 'Keranjang masih kosong.';
            if (this.payment_status === 'down_payment' && !(parseFloat(this.dp_amount) > 0)) return 'Nominal DP wajib diisi.';
            if (this.rx_mode === 'existing' && !this.prescription_id && !this.custRx().length) return 'Pilih resep atau pilih input resep baru.';
            if (this.rx_mode === 'existing' && !this.prescription_id && this.custRx().length) return 'Pilih resep yang tersedia.';
            if (this.rx_mode === 'new' && !document.querySelector('[name="prescription[doctor_or_optician]"]')?.value) return 'Isi dokter / optometris pada resep baru.';
            return '';
        },
    };
}
</script>
@endpush

@section('content')
    <form method="POST" action="{{ route('pos.store') }}" x-data="pos()" @submit="
        error = validate();
        if (error) { $event.preventDefault(); window.scrollTo({top:0, behavior:'smooth'}); }
    ">
        @csrf

        @if ($errors->any())
            <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                @foreach ($errors->all() as $e)<p>{{ $e }}</p>@endforeach
            </div>
        @endif

        <div x-show="error" x-cloak class="mb-5 rounded-xl border border-rose-300 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700" x-text="error"></div>

        <div class="grid gap-6 xl:grid-cols-3">
            <div class="space-y-6 xl:col-span-2">

                <!-- Step 1: Customer -->
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="flex items-center gap-2 text-sm font-bold text-slate-900">
                            <span class="grid h-6 w-6 place-items-center rounded-full bg-slate-900 text-[10px] font-bold text-white">1</span>
                            Pilih Customer
                        </h2>
                        <button type="button" @click="quick.open = !quick.open" class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700 hover:bg-emerald-100">
                            + Customer Baru
                        </button>
                    </div>

                    <div x-show="quick.open" x-cloak class="mb-4 grid gap-3 rounded-xl border border-emerald-200 bg-emerald-50/50 p-4 sm:grid-cols-[1fr_1fr_auto]">
                        <input x-model="quick.name" placeholder="Nama customer" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
                        <input x-model="quick.phone" placeholder="No. WhatsApp" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
                        <button type="button" @click="saveCustomer()" :disabled="quick.busy" class="rounded-xl bg-emerald-500 px-4 py-2.5 text-sm font-bold text-white disabled:opacity-50">
                            <span x-show="!quick.busy">Simpan</span><span x-show="quick.busy">...</span>
                        </button>
                    </div>

                    <input x-model="custQ" type="text" placeholder="Cari nama / no. HP / Member ID..." class="mb-3 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                    <select name="customer_id" x-model="customer_id" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
                        <option value="">— Pilih customer —</option>
                        <template x-for="c in filteredCustomers()" :key="c.id">
                            <option :value="c.id" x-text="c.name + ' (' + c.member_id + ')'" :selected="c.id == customer_id"></option>
                        </template>
                    </select>
                </section>

                <!-- Step 2 & 3: catalog -->
                <section class="grid gap-6 lg:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h2 class="mb-3 flex items-center gap-2 text-sm font-bold text-slate-900">
                            <span class="grid h-6 w-6 place-items-center rounded-full bg-slate-900 text-[10px] font-bold text-white">2</span> Pilih Frame
                        </h2>
                        <input x-model="frameQ" type="text" placeholder="Filter frame..." class="mb-3 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
                        <div class="max-h-72 space-y-2 overflow-y-auto pr-1">
                            <template x-for="f in filteredFrames()" :key="f.id">
                                <button type="button" @click="add('frame', f)"
                                        class="flex w-full items-center justify-between gap-3 rounded-xl border px-4 py-3 text-left transition"
                                        :class="inCart('frame', f.id) ? 'border-emerald-400 bg-emerald-50' : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50'">
                                    <span class="min-w-0">
                                        <span class="block truncate text-sm font-semibold text-slate-900" x-text="f.name"></span>
                                        <span class="block text-xs text-slate-500" x-text="f.brand + ' · stok ' + f.stock"></span>
                                    </span>
                                    <span class="shrink-0 text-sm font-bold text-slate-900" x-text="fmt(f.price)"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h2 class="mb-3 flex items-center gap-2 text-sm font-bold text-slate-900">
                            <span class="grid h-6 w-6 place-items-center rounded-full bg-slate-900 text-[10px] font-bold text-white">3</span> Pilih Lensa
                        </h2>
                        <input x-model="lensQ" type="text" placeholder="Filter brand / tipe..." class="mb-3 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
                        <div class="max-h-72 space-y-2 overflow-y-auto pr-1">
                            <template x-for="l in filteredLenses()" :key="l.id">
                                <button type="button" @click="add('lens', l)"
                                        class="flex w-full items-center justify-between gap-3 rounded-xl border px-4 py-3 text-left transition"
                                        :class="inCart('lens', l.id) ? 'border-emerald-400 bg-emerald-50' : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50'">
                                    <span class="min-w-0">
                                        <span class="block truncate text-sm font-semibold text-slate-900" x-text="l.name"></span>
                                        <span class="block text-xs text-slate-500" x-text="l.type + ' · ' + l.index + ' · stok ' + l.stock"></span>
                                    </span>
                                    <span class="shrink-0 text-sm font-bold text-slate-900" x-text="fmt(l.price)"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </section>

                <!-- Step 4: prescription -->
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="mb-4 flex items-center gap-2 text-sm font-bold text-slate-900">
                        <span class="grid h-6 w-6 place-items-center rounded-full bg-slate-900 text-[10px] font-bold text-white">4</span> Resep Kacamata
                    </h2>

                    <div class="flex flex-wrap gap-3 text-sm">
                        <label class="flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2">
                            <input type="radio" value="existing" x-model="rx_mode" name="rx_mode" class="text-emerald-500 focus:ring-emerald-500/30"> Pakai resep tersimpan
                        </label>
                        <label class="flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2">
                            <input type="radio" value="new" x-model="rx_mode" name="rx_mode" class="text-emerald-500 focus:ring-emerald-500/30"> Input resep baru
                        </label>
                    </div>

                    <div x-show="rx_mode === 'existing'" class="mt-4">
                        <template x-if="custRx().length">
                            <select name="prescription_id" x-model="prescription_id" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
                                <option value="">— Pilih resep —</option>
                                <template x-for="r in custRx()" :key="r.id">
                                    <option :value="r.id" x-text="r.doctor + ' · ' + r.date"></option>
                                </template>
                            </select>
                        </template>
                        <p x-show="!custRx().length" class="rounded-xl bg-slate-50 px-4 py-3 text-sm text-slate-500">
                            Customer ini belum punya resep — pilih "Input resep baru".
                        </p>
                    </div>

                    <div x-show="rx_mode === 'new'" x-cloak class="mt-4 space-y-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <div class="grid gap-4 sm:grid-cols-3">
                            <input type="hidden" name="new_prescription" value="1" :disabled="rx_mode !== 'new'">
                            <input name="prescription[doctor_or_optician]" placeholder="Dokter / Optometris *"
                                   class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
                            <input name="prescription[examination_date]" type="date" value="{{ now()->toDateString() }}"
                                   class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
                            <input name="prescription[prescription_type]" placeholder="Tipe resep"
                                   class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            @foreach (['od' => 'OD Kanan', 'os' => 'OS Kiri'] as $eye => $label)
                                <div>
                                    <p class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-500">{{ $label }}</p>
                                    <div class="grid grid-cols-3 gap-2">
                                        @foreach (['sph', 'cyl', 'axis', 'add', 'pd'] as $k)
                                            <input name="prescription[{{ $eye }}_{{ $k }}]" placeholder="{{ strtoupper($k) }}"
                                                   class="rounded-lg border border-slate-200 px-3 py-2 text-xs focus:border-emerald-500 focus:outline-none">
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <textarea name="prescription[notes]" rows="2" placeholder="Catatan resep" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none"></textarea>
                    </div>
                </section>
            </div>

            <!-- Summary -->
            <aside class="space-y-6 xl:sticky xl:top-24 xl:self-start">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="mb-4 flex items-center justify-between text-sm font-bold text-slate-900">
                        <span>Keranjang</span>
                        <span class="rounded-full bg-slate-900 px-2.5 py-0.5 text-[11px] text-white" x-text="cart.length + ' item'"></span>
                    </h2>

                    <div class="space-y-3">
                        <template x-if="!cart.length">
                            <p class="py-6 text-center text-sm text-slate-400">Belum ada item dipilih.</p>
                        </template>

                        <template x-for="(it, i) in cart" :key="it.type + it.id">
                            <div class="rounded-xl border border-slate-200 p-3">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-slate-900" x-text="it.name"></p>
                                        <p class="text-xs text-slate-500" x-text="fmt(it.price)"></p>
                                    </div>
                                    <button type="button" @click="remove(i)" class="text-slate-400 hover:text-rose-500">✕</button>
                                </div>
                                <div class="mt-2 flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="it.qty > 1 && it.qty--" class="h-7 w-7 rounded-lg border border-slate-200 text-slate-600">−</button>
                                        <span class="w-6 text-center text-sm font-bold" x-text="it.qty"></span>
                                        <button type="button" @click="it.qty < it.stock && it.qty++" class="h-7 w-7 rounded-lg border border-slate-200 text-slate-600">+</button>
                                    </div>
                                    <span class="text-sm font-bold text-slate-900" x-text="fmt(it.price * it.qty)"></span>
                                </div>
                            </div>
                        </template>
                    </div>

                    <input type="hidden" name="items_length" :value="cart.length">
                    <template x-for="(it, i) in cart" :key="'in-'+it.type+it.id">
                        <span class="hidden">
                            <input type="hidden" :name="`items[${i}][type]`" :value="it.type">
                            <input type="hidden" :name="`items[${i}][id]`" :value="it.id">
                            <input type="hidden" :name="`items[${i}][qty]`" :value="it.qty">
                        </span>
                    </template>

                    <!-- Step 5: promo -->
                    <div class="mt-5 border-t border-slate-100 pt-4">
                        <p class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-500">5 · Promo</p>
                        <select name="promo_id" x-model="promo_id" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
                            <option value="">Tanpa promo</option>
                            @foreach ($promotions as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                        <p x-show="promo_id && !promoOk" class="mt-2 text-xs font-semibold text-rose-600">Minimum pembelian promo belum tercapai.</p>
                    </div>

                    <!-- Totals -->
                    <div class="mt-4 space-y-2 border-t border-slate-100 pt-4 text-sm">
                        <div class="flex justify-between text-slate-500"><span>Subtotal</span><span x-text="fmt(subtotal)"></span></div>
                        <div class="flex justify-between text-slate-500"><span>Diskon</span><span class="text-rose-600" x-text="'-' + fmt(discount)"></span></div>
                        <div class="flex justify-between border-t border-slate-100 pt-2 text-base font-extrabold text-slate-900"><span>Total</span><span x-text="fmt(total)"></span></div>
                    </div>

                    <!-- Step 6: payment -->
                    <div class="mt-5 space-y-3 border-t border-slate-100 pt-4">
                        <div>
                            <p class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-500">6 · Metode Bayar</p>
                            <div class="grid grid-cols-4 gap-2">
                                @foreach (['cash' => 'Cash', 'transfer' => 'Transfer', 'qris' => 'QRIS', 'card' => 'Kartu'] as $value => $label)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="payment_method" value="{{ $value }}" x-model="payment_method" class="peer sr-only">
                                        <span class="block rounded-lg border border-slate-200 py-2 text-center text-xs font-semibold text-slate-600 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <p class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-500">Status Bayar</p>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="cursor-pointer">
                                    <input type="radio" name="payment_status" value="paid" x-model="payment_status" class="peer sr-only">
                                    <span class="block rounded-lg border border-slate-200 py-2 text-center text-xs font-semibold text-slate-600 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700">Lunas</span>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="payment_status" value="down_payment" x-model="payment_status" class="peer sr-only">
                                    <span class="block rounded-lg border border-slate-200 py-2 text-center text-xs font-semibold text-slate-600 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700">DP</span>
                                </label>
                            </div>
                            <div x-show="payment_status === 'down_payment'" x-cloak class="mt-3">
                                <input name="dp_amount" type="number" min="1" step="1" x-model="dp_amount" placeholder="Nominal DP"
                                       class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
                                <p class="mt-1 text-xs text-slate-500">Dibayar sekarang: <span class="font-semibold" x-text="fmt(payNow)"></span></p>
                            </div>
                        </div>

                        <div>
                            <p class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-500">Status Pesanan</p>
                            <select name="status" x-model="status" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
                                @foreach (['ordered' => 'Ordered (Dipesan)', 'processing' => 'Processing (Diproses)', 'ready' => 'Ready (Siap Diambil)', 'completed' => 'Completed (Selesai)'] as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <textarea name="notes" x-model="notes" rows="2" placeholder="Catatan transaksi (opsional)"
                                      class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none"></textarea>
                        </div>

                        <button type="submit" class="w-full rounded-xl bg-emerald-500 py-3.5 text-sm font-extrabold text-white transition hover:bg-emerald-600">
                            Simpan Transaksi
                        </button>
                    </div>
                </div>
            </aside>
        </div>
    </form>
@endsection
