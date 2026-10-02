@extends('layouts.erp')

@section('title', 'Buat Transaksi')
@section('subtitle', 'Satu transaksi, satu member, beberapa pemakai kacamata')

@push('scripts')
<script>
function pos() {
    const RX_FIELDS = ['doctor_or_optician', 'examination_date', 'prescription_type',
        'od_sph', 'od_cyl', 'od_axis', 'od_add', 'od_pd',
        'os_sph', 'os_cyl', 'os_axis', 'os_add', 'os_pd',
        'pd_total', 'fitting_height', 'notes'];

    return {
        customers: {!! json_encode($catalog['customers']) !!},
        items: {!! json_encode($catalog['items']) !!},
        promos: {!! json_encode($catalog['promos']) !!},
        rx: {!! json_encode($catalog['rx']) !!},
        today: '{{ now()->toDateString() }}',

        customer_id: '{{ old('customer_id') }}',
        custQ: '',
        users: [],
        seq: 0,
        promo_id: '{{ old('promo_id') }}',
        payment_method: '{{ old('payment_method', 'cash') }}',
        payment_status: '{{ old('payment_status', 'paid') }}',
        dp_amount: '{{ old('dp_amount') }}',
        status: '{{ old('status', 'ordered') }}',
        notes: '{{ old('notes') }}',
        quick: { open: false, name: '', phone: '', busy: false },
        busy: false,
        error: '',

        init() {
            const oldUsers = {!! json_encode(old('users') ?: []) !!};

            if (Array.isArray(oldUsers) && oldUsers.length) {
                this.users = oldUsers.map(u => this.makeUser(u.name || '', u));
            } else {
                this.users = [this.makeUser()];
            }
        },

        fmt(n) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n)); },

        rxDefaults() {
            const rx = { prescription_type: '', notes: '', source: 'in_store' };
            RX_FIELDS.forEach(f => rx[f] = '');
            rx.examination_date = this.today;
            return rx;
        },

        makeUser(name, snapshot) {
            const snap = snapshot || {};
            const rx = Object.assign(this.rxDefaults(), snap.prescription || {});
            if (!rx.examination_date) rx.examination_date = this.today;

            const lines = (Array.isArray(snap.items) ? snap.items : [])
                .map(old => this.catalogItem(old.type, parseInt(old.id, 10)))
                .filter(Boolean)
                .map(item => ({
                    type: item.type, id: item.id, name: item.name, category: item.category,
                    price: item.price, stock: item.stock,
                    qty: Math.max(1, parseInt(snap.items.find(o => String(o.id) === String(item.id) && o.type === item.type)?.qty, 10) || 1),
                    _k: ++this.seq,
                }));

            return {
                _k: ++this.seq,
                name: name || '',
                q: '', cat: '',
                items: lines,
                rx_mode: snap.prescription_id ? 'existing' : 'new',
                prescription_id: snap.prescription_id || '',
                rx: rx,
                ro1: snap.ro1 || '',
                ro2: snap.ro2 || '',
            };
        },

        catalogItem(type, id) { return this.items.find(i => i.type === type && i.id === id); },

        addUser() {
            this.users.push(this.makeUser());
            this.error = '';
        },
        removeUser(index) {
            if (this.users.length < 2) return;
            this.users.splice(index, 1);
        },
        syncCustomerName() {
            if (this.users.length === 1 && !this.users[0].name) {
                const c = this.customers.find(c => String(c.id) === String(this.customer_id));
                if (c) this.users[0].name = c.name;
            }
        },

        get categories() { return [...new Set(this.items.map(i => i.category))]; },

        filteredCustomers() {
            const q = this.custQ.toLowerCase();
            return this.customers.filter(c => !q || c.name.toLowerCase().includes(q) || c.phone.includes(q) || c.member_id.toLowerCase().includes(q));
        },

        stockLeft(item) {
            let used = 0;
            this.users.forEach(u => u.items.forEach(li => {
                if (li.type === item.type && li.id === item.id) used += li.qty;
            }));
            return item.stock - used;
        },
        stockFor(line) { return this.stockLeft(line) + line.qty; },
        inUserCart(user, item) { return user.items.some(li => li.type === item.type && li.id === item.id); },

        pickerItems(user) {
            const q = (user.q || '').toLowerCase();
            return this.items.filter(item => {
                if (user.cat && item.category !== user.cat) return false;
                if (q && ![item.name, item.brand, item.sku, item.category].some(v => (v || '').toLowerCase().includes(q))) return false;
                return this.inUserCart(user, item) || this.stockLeft(item) > 0;
            });
        },

        addItem(index, item) {
            const user = this.users[index];
            const found = user.items.find(li => li.type === item.type && li.id === item.id);
            if (found) { this.bumpQty(index, user.items.indexOf(found), 1); return; }

            if (this.stockLeft(item) < 1) { this.error = item.name + ' stok habis.'; return; }
            this.error = '';
            user.items.push({
                type: item.type, id: item.id, name: item.name, category: item.category,
                price: item.price, stock: item.stock, qty: 1, _k: ++this.seq,
            });
            if (this.hasLens(user) && !user.prescription_id) user.rx_mode = 'new';
        },
        removeItem(index, lineIndex) { this.users[index].items.splice(lineIndex, 1); },
        bumpQty(index, lineIndex, delta) {
            const line = this.users[index].items[lineIndex];
            const next = line.qty + delta;
            if (next < 1) return;
            if (next > this.stockFor(line)) { this.error = 'Stok ' + line.name + ' tersisa ' + this.stockFor(line) + '.'; return; }
            this.error = '';
            line.qty = next;
        },

        hasLens(user) { return user.items.some(li => li.type === 'lens'); },
        hasItem(user, type) { return user.items.some(li => li.type === type); },
        userSubtotal(user) { return user.items.reduce((s, li) => s + li.price * li.qty, 0); },
        custRx(user) { return this.rx.filter(r => String(r.customer_id) === String(this.customer_id)); },
        needsRx(user) { return this.hasLens(user); },

        get itemCount() { return this.users.reduce((n, u) => n + u.items.length, 0); },
        get subtotal() { return this.users.reduce((s, u) => s + this.userSubtotal(u), 0); },
        get discount() {
            const p = this.promos.find(x => String(x.id) === String(this.promo_id));
            if (!p || this.subtotal < p.min) return 0;
            const d = p.type === 'nominal' ? p.value : this.subtotal * p.value / 100;
            return Math.min(this.subtotal, Math.round(d));
        },
        get total() { return Math.max(0, this.subtotal - this.discount); },
        get payNow() { return this.payment_status === 'paid' ? this.total : (parseFloat(this.dp_amount) || 0); },
        get promoOk() {
            const p = this.promos.find(x => String(x.id) === String(this.promo_id));
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
            if (!this.customer_id) return 'Pilih customer (member) terlebih dahulu.';
            if (!this.users.length) return 'Tambahkan minimal satu pemakai.';
            for (let i = 0; i < this.users.length; i++) {
                const u = this.users[i];
                const label = u.name || ('Pemakai ' + (i + 1));
                if (!u.name.trim()) return 'Nama pemakai ke-' + (i + 1) + ' wajib diisi.';
                if (!u.items.length) return label + ' belum memilih item.';
                if (this.needsRx(u)) {
                    if (u.rx_mode === 'existing' && !u.prescription_id) return 'Pilih resep tersimpan untuk ' + label + '.';
                    if (u.rx_mode === 'new' && !u.rx.doctor_or_optician) return 'Isi dokter / optometris pada resep ' + label + '.';
                }
            }
            if (this.payment_status === 'down_payment' && !(parseFloat(this.dp_amount) > 0)) return 'Nominal DP wajib diisi.';
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

                <!-- Step 1: Customer / Member -->
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="flex items-center gap-2 text-sm font-bold text-slate-900">
                            <span class="grid h-6 w-6 place-items-center rounded-full bg-slate-900 text-[10px] font-bold text-white">1</span>
                            Pilih Customer / Member ID
                        </h2>
                        <button type="button" @click="quick.open = !quick.open" class="rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-bold text-red-700 hover:bg-red-100">
                            + Customer Baru
                        </button>
                    </div>

                    <div x-show="quick.open" x-cloak class="mb-4 grid gap-3 rounded-xl border border-red-200 bg-red-50/50 p-4 sm:grid-cols-[1fr_1fr_auto]">
                        <input x-model="quick.name" placeholder="Nama customer" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-red-500 focus:outline-none">
                        <input x-model="quick.phone" placeholder="No. WhatsApp" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-red-500 focus:outline-none">
                        <button type="button" @click="saveCustomer()" :disabled="quick.busy" class="rounded-xl bg-red-600 px-4 py-2.5 text-sm font-bold text-white disabled:opacity-50">
                            <span x-show="!quick.busy">Simpan</span><span x-show="quick.busy">...</span>
                        </button>
                    </div>

                    <input x-model="custQ" type="text" placeholder="Cari nama / no. HP / Member ID..." class="mb-3 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/20">
                    <select name="customer_id" x-model="customer_id" @change="syncCustomerName()" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-red-500 focus:outline-none">
                        <option value="">— Pilih customer —</option>
                        <template x-for="c in filteredCustomers()" :key="c.id">
                            <option :value="c.id" x-text="c.name + ' (' + c.member_id + ')'" :selected="String(c.id) === String(customer_id)"></option>
                        </template>
                    </select>
                    <p class="mt-2 text-xs text-slate-500">Satu transaksi bisa memuat beberapa pemakai (mis. Ayah, Anak 1, Anak 2) dengan satu member ID yang sama.</p>
                </section>

                <!-- Step 2: Pemakai + item + resep -->
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                        <h2 class="flex items-center gap-2 text-sm font-bold text-slate-900">
                            <span class="grid h-6 w-6 place-items-center rounded-full bg-slate-900 text-[10px] font-bold text-white">2</span>
                            Nama Pemakai (User) &amp; Itemnya
                        </h2>
                        <button type="button" @click="addUser()" class="rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-bold text-red-700 hover:bg-red-100">
                            + Tambah Pemakai
                        </button>
                    </div>

                    <div class="space-y-4">
                        <template x-for="(u, ui) in users" :key="u._k">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50/50 p-4">
                                <input type="hidden" :name="`users[${ui}][name]`" :value="u.name">

                                <!-- item pemakai ikut terkirim saat form disubmit -->
                                <template x-for="(li, liIdx) in u.items" :key="'field-' + li._k">
                                    <span>
                                        <input type="hidden" :name="`users[${ui}][items][${liIdx}][type]`" :value="li.type">
                                        <input type="hidden" :name="`users[${ui}][items][${liIdx}][id]`" :value="li.id">
                                        <input type="hidden" :name="`users[${ui}][items][${liIdx}][qty]`" :value="li.qty">
                                    </span>
                                </template>

                                <div class="mb-3 flex items-center gap-2">
                                    <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-red-600 text-[11px] font-bold text-white" x-text="ui + 1"></span>
                                    <input x-model="u.name" type="text" placeholder="Nama pemakai (mis. Adi, Anak 1)"
                                           class="flex-1 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold focus:border-red-500 focus:outline-none">
                                    <button type="button" x-show="users.length > 1" @click="removeUser(ui)"
                                            class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50">Hapus</button>
                                </div>

                                <!-- petugas: RO1 periksa mata, RO2 potong lensa -->
                                <div class="mb-3 grid gap-2 sm:grid-cols-2">
                                    <label class="block">
                                        <span class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-slate-500">RO1 · Periksa Mata</span>
                                        <input type="text" :name="`users[${ui}][ro1]`" x-model="u.ro1" maxlength="120" placeholder="Nama petugas refraksi"
                                               class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs focus:border-red-500 focus:outline-none">
                                    </label>
                                    <label class="block">
                                        <span class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-slate-500">RO2 · Potong Lensa</span>
                                        <input type="text" :name="`users[${ui}][ro2]`" x-model="u.ro2" maxlength="120" placeholder="Nama petugas lensa"
                                               class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs focus:border-red-500 focus:outline-none">
                                    </label>
                                </div>

                                <!-- pemilihan item -->
                                <p class="mb-2 text-[11px] font-bold uppercase tracking-wider text-slate-500">Pilih item untuk pemakai ini</p>
                                <div class="mb-2 flex flex-wrap gap-1.5">
                                    <button type="button" @click="u.cat = ''"
                                            class="rounded-full border px-3 py-1 text-[11px] font-bold"
                                            :class="u.cat === '' ? 'border-red-600 bg-red-600 text-white' : 'border-slate-200 bg-white text-slate-500'">Semua</button>
                                    <template x-for="cat in categories" :key="cat">
                                        <button type="button" @click="u.cat = cat"
                                                class="rounded-full border px-3 py-1 text-[11px] font-bold"
                                                :class="u.cat === cat ? 'border-red-600 bg-red-600 text-white' : 'border-slate-200 bg-white text-slate-500'"
                                                x-text="cat"></button>
                                    </template>
                                </div>
                                <input x-model="u.q" type="text" placeholder="Cari frame, lensa, softlens, case..."
                                       class="mb-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:border-red-500 focus:outline-none">

                                <div class="grid max-h-60 gap-2 overflow-y-auto pr-1 sm:grid-cols-2">
                                    <template x-for="it in pickerItems(u)" :key="it.type + '-' + it.id">
                                        <button type="button" @click="addItem(ui, it)"
                                                class="flex items-center justify-between gap-2 rounded-xl border px-3 py-2.5 text-left transition"
                                                :class="inUserCart(u, it) ? 'border-red-400 bg-red-50' : 'border-slate-200 bg-white hover:border-slate-300'">
                                            <span class="min-w-0">
                                                <span class="block truncate text-xs font-semibold text-slate-900" x-text="it.name"></span>
                                                <span class="block truncate text-[11px] text-slate-500" x-text="it.category + ' · sisa ' + stockLeft(it)"></span>
                                            </span>
                                            <span class="shrink-0 text-xs font-bold text-slate-900" x-text="fmt(it.price)"></span>
                                        </button>
                                    </template>
                                    <p x-show="!pickerItems(u).length" class="col-span-full rounded-xl bg-white px-3 py-4 text-center text-xs text-slate-400">Tidak ada item cocok / stok habis.</p>
                                </div>

                                <!-- item terpilih -->
                                <div class="mt-3">
                                    <div class="mb-2 flex items-center justify-between" x-show="u.items.length">
                                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Item terpilih</p>
                                        <button type="button" @click="u.items = []" class="text-[11px] font-semibold text-rose-600 hover:underline">Kosongkan semua</button>
                                    </div>

                                    <div class="space-y-2">
                                        <template x-for="(li, li2) in u.items" :key="li._k">
                                            <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2">
                                                <span class="min-w-0">
                                                    <span class="block truncate text-xs font-semibold text-slate-900" x-text="li.name"></span>
                                                    <span class="block text-[11px] uppercase tracking-wide text-red-600" x-text="li.category"></span>
                                                </span>
                                                <span class="flex shrink-0 items-center gap-1.5">
                                                    <button type="button" @click="bumpQty(ui, li2, -1)" :disabled="li.qty <= 1"
                                                            title="Kurangi jumlah" class="h-6 w-6 rounded-lg border border-slate-200 text-xs text-slate-600 disabled:cursor-not-allowed disabled:opacity-40">−</button>
                                                    <span class="w-5 text-center text-xs font-bold" x-text="li.qty"></span>
                                                    <button type="button" @click="bumpQty(ui, li2, 1)" title="Tambah jumlah" class="h-6 w-6 rounded-lg border border-slate-200 text-xs text-slate-600">+</button>
                                                    <span class="w-20 text-right text-xs font-bold text-slate-900" x-text="fmt(li.price * li.qty)"></span>
                                                    <button type="button" @click="removeItem(ui, li2)" title="Hapus item dari keranjang"
                                                            class="rounded-lg border border-rose-200 bg-rose-50 px-2 py-1 text-[11px] font-bold text-rose-600 hover:bg-rose-100">Hapus</button>
                                                </span>
                                            </div>
                                        </template>
                                    </div>

                                    <p x-show="!u.items.length" class="rounded-xl bg-white px-3 py-3 text-center text-xs text-slate-400">Belum ada item dipilih.</p>
                                </div>

                                <!-- resep muncul ketika lensa dipilih -->
                                <template x-if="hasLens(u)">
                                    <div class="mt-3 rounded-xl border border-red-100 bg-white p-4">
                                    <p class="mb-2 text-[11px] font-bold uppercase tracking-wider text-red-600">
                                        Resep · <span x-text="u.name || ('Pemakai ' + (ui + 1))"></span>
                                    </p>

                                    <div class="flex flex-wrap gap-3 text-xs">
                                        <label class="flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2">
                                            <input type="radio" value="new" x-model="u.rx_mode" :name="`rx_mode_${ui}`" class="text-red-500 focus:ring-red-500/30"> Input resep baru
                                        </label>
                                        <label class="flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2">
                                            <input type="radio" value="existing" x-model="u.rx_mode" :name="`rx_mode_${ui}`" class="text-red-500 focus:ring-red-500/30"> Pakai resep tersimpan
                                        </label>
                                    </div>

                                    <template x-if="u.rx_mode === 'new'">
                                        <div class="mt-3">
                                            <p class="mb-1.5 text-[11px] font-bold uppercase tracking-wider text-slate-500">Sumber resep</p>
                                            <div class="flex flex-wrap gap-3 text-xs">
                                                <label class="flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2">
                                                    <input type="radio" value="in_store" x-model="u.rx.source" :name="`users[${ui}][prescription][source]`" class="text-red-500 focus:ring-red-500/30"> In Store (periksa di toko)
                                                </label>
                                                <label class="flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2">
                                                    <input type="radio" value="bring_own" x-model="u.rx.source" :name="`users[${ui}][prescription][source]`" class="text-red-500 focus:ring-red-500/30"> Bawa resep sendiri
                                                </label>
                                            </div>
                                        </div>
                                    </template>

                                    <template x-if="u.rx_mode === 'existing'">
                                        <div class="mt-3">
                                            <select x-show="custRx(u).length" x-model="u.prescription_id"
                                                    class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-red-500 focus:outline-none">
                                                <option value="">— Pilih resep —</option>
                                                <template x-for="r in custRx(u)" :key="r.id">
                                                    <option :value="r.id" x-text="r.doctor + ' · ' + r.date" :selected="String(r.id) === String(u.prescription_id)"></option>
                                                </template>
                                            </select>
                                            <p x-show="!custRx(u).length" class="rounded-xl bg-slate-50 px-3 py-3 text-xs text-slate-500">
                                                Customer ini belum punya resep — pilih "Input resep baru".
                                            </p>
                                        </div>
                                    </template>

                                    <template x-if="u.rx_mode === 'new'">
                                        <div class="mt-3 space-y-3 rounded-xl border border-slate-100 bg-slate-50 p-3">
                                        <div class="grid gap-3 sm:grid-cols-3">
                                            <input :name="`users[${ui}][prescription][doctor_or_optician]`" x-model="u.rx.doctor_or_optician" placeholder="Dokter / Optometris *"
                                                   class="rounded-xl border border-slate-200 px-3 py-2 text-xs focus:border-red-500 focus:outline-none">
                                            <input :name="`users[${ui}][prescription][examination_date]`" x-model="u.rx.examination_date" type="date"
                                                   class="rounded-xl border border-slate-200 px-3 py-2 text-xs focus:border-red-500 focus:outline-none">
                                            <input :name="`users[${ui}][prescription][prescription_type]`" x-model="u.rx.prescription_type" placeholder="Tipe resep"
                                                   class="rounded-xl border border-slate-200 px-3 py-2 text-xs focus:border-red-500 focus:outline-none">
                                        </div>
                                        <div class="grid gap-3 sm:grid-cols-2">
                                            <template x-for="eye in ['od', 'os']" :key="eye">
                                                <div>
                                                    <p class="mb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-500" x-text="eye === 'od' ? 'OD Kanan' : 'OS Kiri'"></p>
                                                    <div class="grid grid-cols-3 gap-1.5">
                                                        <template x-for="part in ['sph', 'cyl', 'axis', 'add', 'pd']" :key="eye + part">
                                                            <input :name="`users[${ui}][prescription][${eye}_${part}]`" x-model="u.rx[eye + '_' + part]"
                                                                   :placeholder="part.toUpperCase()"
                                                                   class="rounded-lg border border-slate-200 px-2 py-1.5 text-[11px] focus:border-red-500 focus:outline-none">
                                                        </template>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                        <div class="grid gap-3 sm:grid-cols-3">
                                            <input :name="`users[${ui}][prescription][pd_total]`" x-model="u.rx.pd_total" placeholder="PD Total"
                                                   class="rounded-lg border border-slate-200 px-3 py-2 text-xs focus:border-red-500 focus:outline-none">
                                            <input :name="`users[${ui}][prescription][fitting_height]`" x-model="u.rx.fitting_height" placeholder="Fitting Height"
                                                   class="rounded-lg border border-slate-200 px-3 py-2 text-xs focus:border-red-500 focus:outline-none">
                                            <input :name="`users[${ui}][prescription][notes]`" x-model="u.rx.notes" placeholder="Catatan resep"
                                                   class="rounded-lg border border-slate-200 px-3 py-2 text-xs focus:border-red-500 focus:outline-none">
                                        </div>
                                            </div>
                                        </template>

                                        <template x-if="u.rx_mode === 'existing'">
                                            <input type="hidden" :name="`users[${ui}][prescription_id]`" :value="u.prescription_id">
                                        </template>
                                        <template x-if="u.rx_mode === 'new'">
                                            <input type="hidden" :name="`users[${ui}][new_prescription]`" value="1">
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </section>
            </div>

            <!-- Summary -->
            <aside class="space-y-6 xl:sticky xl:top-24 xl:self-start">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="mb-4 flex items-center justify-between text-sm font-bold text-slate-900">
                        <span>Keranjang</span>
                        <span class="rounded-full bg-slate-900 px-2.5 py-0.5 text-[11px] text-white" x-text="users.length + ' pemakai · ' + itemCount + ' item'"></span>
                    </h2>

                    <div class="space-y-4">
                        <template x-for="(u, ui) in users" :key="'sum-' + u._k">
                            <div class="rounded-xl border border-slate-100 p-3">
                                <p class="mb-2 flex items-center justify-between gap-2 text-xs font-bold uppercase tracking-wider text-slate-500">
                                    <span x-text="u.name || ('Pemakai ' + (ui + 1))"></span>
                                    <span class="flex items-center gap-2">
                                        <button type="button" x-show="u.items.length" @click="u.items = []"
                                                class="text-[10px] font-semibold normal-case text-rose-600 hover:underline">Kosongkan</button>
                                        <span class="text-slate-400 normal-case" x-text="fmt(userSubtotal(u))"></span>
                                    </span>
                                </p>

                                <template x-for="(li, li3) in u.items" :key="'sum-item-' + li._k">
                                    <div class="mb-2 flex items-start justify-between gap-2 rounded-lg bg-slate-50 px-2.5 py-2">
                                        <div class="min-w-0">
                                            <p class="truncate text-xs font-semibold text-slate-900" x-text="li.name"></p>
                                            <p class="text-[11px] text-slate-500">
                                                <span x-text="li.qty + ' × ' + fmt(li.price)"></span>
                                                <span class="text-red-600" x-show="li.type === 'lens'"> · resep: <span x-text="u.rx_mode === 'existing' ? 'tersimpan' : (u.rx.source === 'bring_own' ? 'bawa resep sendiri' : 'in store')"></span></span>
                                            </p>
                                        </div>
                                        <span class="flex shrink-0 items-center gap-1.5">
                                            <span class="text-xs font-bold text-slate-900" x-text="fmt(li.price * li.qty)"></span>
                                            <button type="button" @click="removeItem(ui, li3)" title="Hapus item dari keranjang"
                                                    class="grid h-5 w-5 place-items-center rounded border border-slate-200 bg-white text-[11px] leading-none text-slate-400 hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600">✕</button>
                                        </span>
                                    </div>
                                </template>

                                <p x-show="!u.items.length" class="text-[11px] text-slate-400">Belum ada item.</p>
                            </div>
                        </template>
                    </div>

                    <!-- Step 3: promo -->
                    <div class="mt-5 border-t border-slate-100 pt-4">
                        <p class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-500">3 · Promo</p>
                        <select name="promo_id" x-model="promo_id" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-red-500 focus:outline-none">
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

                    <!-- Step 4: payment -->
                    <div class="mt-5 space-y-3 border-t border-slate-100 pt-4">
                        <div>
                            <p class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-500">4 · Metode Bayar</p>
                            <div class="grid grid-cols-4 gap-2">
                                @foreach (['cash' => 'Cash', 'transfer' => 'Transfer', 'qris' => 'QRIS', 'card' => 'Kartu'] as $value => $label)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="payment_method" value="{{ $value }}" x-model="payment_method" class="peer sr-only">
                                        <span class="block rounded-lg border border-slate-200 py-2 text-center text-xs font-semibold text-slate-600 peer-checked:border-red-500 peer-checked:bg-red-50 peer-checked:text-red-700">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <p class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-500">Status Bayar</p>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="cursor-pointer">
                                    <input type="radio" name="payment_status" value="paid" x-model="payment_status" class="peer sr-only">
                                    <span class="block rounded-lg border border-slate-200 py-2 text-center text-xs font-semibold text-slate-600 peer-checked:border-red-500 peer-checked:bg-red-50 peer-checked:text-red-700">Lunas</span>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="payment_status" value="down_payment" x-model="payment_status" class="peer sr-only">
                                    <span class="block rounded-lg border border-slate-200 py-2 text-center text-xs font-semibold text-slate-600 peer-checked:border-red-500 peer-checked:bg-red-50 peer-checked:text-red-700">DP</span>
                                </label>
                            </div>
                            <div x-show="payment_status === 'down_payment'" x-cloak class="mt-3">
                                <input name="dp_amount" type="number" min="1" step="1" x-model="dp_amount" placeholder="Nominal DP"
                                       class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-red-500 focus:outline-none">
                                <p class="mt-1 text-xs text-slate-500">Dibayar sekarang: <span class="font-semibold" x-text="fmt(payNow)"></span></p>
                            </div>
                        </div>

                        <div>
                            <p class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-500">Status Pesanan</p>
                            <select name="status" x-model="status" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-red-500 focus:outline-none">
                                @foreach (['ordered' => 'Ordered (Dipesan)', 'processing' => 'Processing (Diproses)', 'ready' => 'Ready (Siap Diambil)', 'completed' => 'Completed (Selesai)'] as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <textarea name="notes" x-model="notes" rows="2" placeholder="Catatan transaksi (opsional)"
                                      class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-red-500 focus:outline-none"></textarea>
                        </div>

                        <button type="submit" class="w-full rounded-xl bg-red-600 py-3.5 text-sm font-extrabold text-white transition hover:bg-red-700">
                            Simpan Transaksi
                        </button>
                    </div>
                </div>
            </aside>
        </div>
    </form>
@endsection
