<script setup>
import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    roomTypes: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const authUser = computed(() => page.props.auth?.user || null);

const formatDateInput = (offsetDays = 0) => {
    const d = new Date();
    d.setDate(d.getDate() + offsetDays);
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const todayStr = formatDateInput(0);
const checkIn = ref(formatDateInput(0));
const checkOut = ref(formatDateInput(1));
const search = ref('');
const capacityFilter = ref('all');
const facilityFilter = ref('all');
const sortBy = ref('price_asc');

const selectedSuite = ref(null);
const selectedRoomId = ref('');

const fallbackImg = 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=1200&q=80';

const normalizedSuites = computed(() =>
    props.roomTypes.map((type) => {
        const availableRooms = Array.isArray(type.rooms) ? type.rooms : [];
        const availableCount = typeof type.rooms_count === 'number' ? type.rooms_count : availableRooms.length;
        return {
            id: type.id,
            name: type.name,
            description:
                type.description ||
                'Kamar berdesain hangat dengan pencahayaan alami, perlengkapan tidur premium, dan kenyamanan modern khas Svara Hotel.',
            base_price: Number(type.base_price || 0),
            capacity: Number(type.capacity || 2),
            cover_url: type.cover_url || fallbackImg,
            available_count: availableCount,
            available_rooms: availableRooms,
            facilities: Array.isArray(type.facilities) ? type.facilities.map((f) => f.name) : [],
        };
    })
);

const allFacilities = computed(() => {
    const set = new Set();
    normalizedSuites.value.forEach((s) => s.facilities.forEach((f) => set.add(f)));
    return Array.from(set).sort();
});

const heroImage = computed(() => {
    if (normalizedSuites.value.length === 0) {
        return 'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=1600&q=80';
    }
    const highest = [...normalizedSuites.value].sort((a, b) => b.base_price - a.base_price)[0];
    return highest.cover_url;
});

const totalAvailableUnits = computed(() =>
    normalizedSuites.value.reduce((acc, item) => acc + item.available_count, 0)
);

const totalNights = computed(() => {
    if (!checkIn.value || !checkOut.value) return 1;
    const start = new Date(checkIn.value);
    const end = new Date(checkOut.value);
    const diff = Math.round((end - start) / (1000 * 60 * 60 * 24));
    return diff > 0 ? diff : 1;
});

const filteredSuites = computed(() => {
    let list = [...normalizedSuites.value];

    if (search.value.trim() !== '') {
        const q = search.value.toLowerCase();
        list = list.filter(
            (s) =>
                s.name.toLowerCase().includes(q) ||
                s.description.toLowerCase().includes(q) ||
                s.facilities.some((f) => f.toLowerCase().includes(q))
        );
    }

    if (capacityFilter.value === '2') {
        list = list.filter((s) => s.capacity <= 2);
    } else if (capacityFilter.value === '3plus') {
        list = list.filter((s) => s.capacity >= 3);
    }

    if (facilityFilter.value !== 'all') {
        list = list.filter((s) => s.facilities.includes(facilityFilter.value));
    }

    if (sortBy.value === 'price_asc') {
        list.sort((a, b) => a.base_price - b.base_price);
    } else if (sortBy.value === 'price_desc') {
        list.sort((a, b) => b.base_price - a.base_price);
    } else if (sortBy.value === 'capacity_desc') {
        list.sort((a, b) => b.capacity - a.capacity);
    }

    return list;
});

const formatRupiah = (value) => 'Rp ' + Math.round(Number(value || 0)).toLocaleString('id-ID');

const handleImgError = (event) => {
    event.target.onerror = null;
    event.target.src = fallbackImg;
};

const openSuiteModal = (suite) => {
    selectedSuite.value = suite;
    selectedRoomId.value = suite.available_rooms.length > 0 ? String(suite.available_rooms[0].id) : '';
};

const closeSuiteModal = () => {
    selectedSuite.value = null;
};

const bookingUrl = (suite) => {
    const params = new URLSearchParams();
    const roomId =
        selectedSuite.value && selectedSuite.value.id === suite.id && selectedRoomId.value
            ? selectedRoomId.value
            : suite.available_rooms[0]?.id || '';

    if (roomId) params.set('room_id', roomId);
    if (checkIn.value) params.set('check_in_date', checkIn.value);
    if (checkOut.value) params.set('check_out_date', checkOut.value);

    const query = params.toString();
    return query ? `/reservations/create?${query}` : '/reservations/create';
};

const resetFilters = () => {
    search.value = '';
    capacityFilter.value = 'all';
    facilityFilter.value = 'all';
    sortBy.value = 'price_asc';
};
</script>

<template>
    <div @keydown.escape.window="closeSuiteModal()" class="min-h-screen flex flex-col bg-[#FAF9F6] text-stone-900">
        <!-- 1. TOP NAVIGATION BAR -->
        <header class="svara-navbar">
            <div class="svara-container svara-navbar-inner">
                <a href="/explore" class="flex items-center gap-3">
                    <div class="svara-logo-mark font-editorial">S</div>
                    <div>
                        <span class="font-editorial text-xl tracking-wide text-stone-900 leading-none block">Svara Hotel</span>
                        <span class="block text-[9px] uppercase tracking-[0.22em] text-stone-500 mt-1">Hospitality Suite</span>
                    </div>
                </a>

                <nav class="svara-nav-links">
                    <a href="#koleksi-suites" class="hover:text-stone-950 transition">Koleksi Suites</a>
                    <a href="#standar-layanan" class="hover:text-stone-950 transition">Standar Layanan</a>
                    <a href="#informasi-hotel" class="hover:text-stone-950 transition">Informasi Menginap</a>
                </nav>

                <div class="flex items-center gap-2.5">
                    <template v-if="authUser">
                        <span class="svara-user-pill">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                            {{ authUser.name }}
                        </span>

                        <a href="/dashboard" class="svara-dark-btn">
                            <span class="svara-dark-btn-icon">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <rect x="3" y="3" width="7" height="7" rx="1" />
                                    <rect x="14" y="3" width="7" height="7" rx="1" />
                                    <rect x="14" y="14" width="7" height="7" rx="1" />
                                    <rect x="3" y="14" width="7" height="7" rx="1" />
                                </svg>
                            </span>
                            <span>Dashboard</span>
                        </a>
                    </template>

                    <template v-else>
                        <a
                            href="/login"
                            class="px-3.5 py-2 rounded-lg text-xs font-semibold text-stone-700 hover:text-stone-950 hover:bg-stone-100 transition"
                        >
                            Masuk
                        </a>
                        <a href="/register" class="svara-dark-btn">
                            <span class="svara-dark-btn-icon">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                    <circle cx="12" cy="7" r="4" />
                                </svg>
                            </span>
                            <span>Daftar Tamu</span>
                        </a>
                    </template>
                </div>
            </div>
        </header>

        <!-- 2. HERO EDITORIAL -->
        <section class="svara-hero">
            <img :src="heroImage" alt="Svara Hotel & Suites" @error="handleImgError" class="svara-hero-bg" />
            <div class="svara-hero-overlay"></div>

            <div class="svara-container svara-hero-content">
                <div class="max-w-2xl">
                    <div class="svara-hero-badge">
                        <span>Boutique Sanctuary</span>
                        <span>&bull;</span>
                        <span class="tabular-nums">{{ totalAvailableUnits }} Unit Siap Huni</span>
                    </div>

                    <h1 class="font-editorial svara-hero-title">
                        Ketenangan ruang modern dalam kehangatan layanan Svara.
                    </h1>

                    <p class="svara-hero-desc">
                        Pilih kategori kamar sesuai kebutuhan perjalanan Anda. Cek ketersediaan unit secara real-time, bandingkan fasilitas, dan lakukan reservasi langsung tanpa perantara.
                    </p>

                    <div class="mt-7 flex flex-wrap items-center gap-3.5">
                        <a href="#koleksi-suites" class="svara-amber-btn">
                            <span class="svara-amber-btn-icon">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M2 20v-8a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v8" />
                                    <path d="M4 10V6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v4" />
                                    <path d="M2 16h20" />
                                </svg>
                            </span>
                            <span>Jelajahi Koleksi Suites</span>
                            <svg class="w-3.5 h-3.5 text-amber-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </a>

                        <div class="svara-hero-hours tabular-nums">
                            Check-In <strong class="text-white">14:00</strong> &bull; Check-Out <strong class="text-white">12:00</strong>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 3. INTERACTIVE STAY PLANNER & FILTER BAR -->
        <section id="koleksi-suites" class="svara-container svara-filter-wrapper">
            <div class="svara-filter-card">
                <div class="svara-filter-grid">
                    <div>
                        <label class="svara-field-label">Check-In</label>
                        <input type="date" v-model="checkIn" :min="todayStr" class="svara-input tabular-nums" />
                    </div>

                    <div>
                        <label class="svara-field-label">Check-Out</label>
                        <input type="date" v-model="checkOut" :min="checkIn" class="svara-input tabular-nums" />
                    </div>

                    <div>
                        <label class="svara-field-label">Kapasitas</label>
                        <select v-model="capacityFilter" class="svara-input">
                            <option value="all">Semua Tamu</option>
                            <option value="2">1 – 2 Tamu</option>
                            <option value="3plus">3+ Tamu</option>
                        </select>
                    </div>

                    <div>
                        <label class="svara-field-label">Urutkan</label>
                        <select v-model="sortBy" class="svara-input">
                            <option value="price_asc">Tarif Terendah</option>
                            <option value="price_desc">Tarif Tertinggi</option>
                            <option value="capacity_desc">Kapasitas Besar</option>
                        </select>
                    </div>

                    <div class="svara-search-col">
                        <label class="svara-field-label">Cari Kamar atau Fasilitas</label>
                        <div class="relative">
                            <svg class="w-4 h-4 text-stone-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                <circle cx="11" cy="11" r="8" />
                                <line x1="21" y1="21" x2="16.65" y2="16.65" />
                            </svg>
                            <input
                                type="text"
                                v-model="search"
                                placeholder="Suite, Garden, Balcony, King..."
                                class="svara-input svara-search-input"
                            />
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-stone-100 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar">
                        <button
                            type="button"
                            @click="facilityFilter = 'all'"
                            :class="facilityFilter === 'all' ? 'bg-[#181512] text-amber-50 border-[#181512]' : 'bg-stone-100 text-stone-600 border-stone-200 hover:bg-stone-200/70'"
                            class="px-3 py-1.5 rounded-lg border text-[11px] font-semibold whitespace-nowrap transition"
                        >
                            Semua Fasilitas
                        </button>

                        <button
                            v-for="facName in allFacilities"
                            :key="facName"
                            type="button"
                            @click="facilityFilter = facilityFilter === facName ? 'all' : facName"
                            :class="facilityFilter === facName ? 'bg-amber-800 text-white border-amber-800' : 'bg-stone-50 text-stone-600 border-stone-200 hover:bg-stone-100'"
                            class="px-3 py-1.5 rounded-lg border text-[11px] font-medium whitespace-nowrap transition"
                        >
                            {{ facName }}
                        </button>
                    </div>

                    <div class="flex items-center gap-3 text-xs text-stone-500 shrink-0 tabular-nums">
                        <span>Simulasi durasi: <strong class="text-stone-900">{{ totalNights }} malam</strong></span>
                        <button
                            v-if="search !== '' || capacityFilter !== 'all' || facilityFilter !== 'all'"
                            type="button"
                            @click="resetFilters"
                            class="text-amber-800 font-semibold hover:underline"
                        >
                            Reset Filter
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- 4. GRID KATALOG SUITES -->
        <main class="svara-container py-10 flex-1">
            <div class="flex flex-wrap items-baseline justify-between gap-2 mb-7">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-800">Akomodasi Pilihan</p>
                    <h2 class="font-editorial text-3xl sm:text-4xl text-stone-900 mt-0.5">Koleksi Kamar & Suites</h2>
                </div>
                <p class="text-xs text-stone-500 tabular-nums">Menampilkan {{ filteredSuites.length }} kategori kamar</p>
            </div>

            <div v-if="filteredSuites.length === 0" class="bg-white border border-stone-200 rounded-2xl p-12 text-center">
                <p class="text-base font-semibold text-stone-800">Tidak ada tipe kamar yang sesuai kriteria filter Anda.</p>
                <p class="text-xs text-stone-500 mt-1">Coba pilih kombinasi fasilitas lain atau atur ulang pencarian.</p>
                <button
                    type="button"
                    @click="resetFilters"
                    class="mt-4 px-4 py-2 rounded-xl bg-[#181512] text-amber-50 text-xs font-semibold hover:bg-stone-900 transition"
                >
                    Tampilkan Semua Kamar
                </button>
            </div>

            <div v-else class="svara-cards-grid">
                <article v-for="suite in filteredSuites" :key="suite.id" class="svara-suite-card group">
                    <div>
                        <div class="svara-card-media" @click="openSuiteModal(suite)">
                            <img
                                :src="suite.cover_url"
                                :alt="suite.name"
                                @error="handleImgError"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            />
                            <div class="svara-card-overlay"></div>

                            <div class="absolute top-3.5 left-3.5">
                                <span
                                    v-if="suite.available_count > 0"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-emerald-950/90 text-emerald-200 text-[10px] font-semibold uppercase tracking-wider border border-emerald-500/35 tabular-nums"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                    <span>Tersedia {{ suite.available_count }} Unit</span>
                                </span>
                                <span
                                    v-else
                                    class="inline-flex items-center px-2.5 py-1 rounded-md bg-rose-950/90 text-rose-200 text-[10px] font-semibold uppercase tracking-wider border border-rose-500/35"
                                >
                                    Penuh Sementara
                                </span>
                            </div>

                            <div class="absolute top-3.5 right-3.5">
                                <span class="px-2.5 py-1 rounded-md bg-white/95 text-stone-900 text-[11px] font-semibold tabular-nums shadow-2xs">
                                    Maks. {{ suite.capacity }} Tamu
                                </span>
                            </div>

                            <div class="absolute bottom-3.5 left-4 right-4 flex items-end justify-between gap-2 text-white">
                                <h3 class="font-editorial text-2xl text-amber-50 leading-tight">{{ suite.name }}</h3>
                                <div class="text-right shrink-0 tabular-nums">
                                    <p class="text-sm font-bold text-white">{{ formatRupiah(suite.base_price) }}</p>
                                    <p class="text-[10px] text-stone-300">/ malam</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-5 space-y-4">
                            <p class="text-xs text-stone-600 leading-relaxed line-clamp-2 min-h-[2.5rem]">
                                {{ suite.description }}
                            </p>

                            <div class="flex flex-wrap gap-1.5 min-h-[1.75rem]">
                                <span
                                    v-for="fac in suite.facilities.slice(0, 4)"
                                    :key="fac"
                                    class="px-2.5 py-1 rounded-md bg-stone-100 border border-stone-200/80 text-[11px] font-medium text-stone-700"
                                >
                                    {{ fac }}
                                </span>
                                <span
                                    v-if="suite.facilities.length > 4"
                                    class="px-2.5 py-1 rounded-md bg-amber-50 border border-amber-200 text-[11px] font-medium text-amber-900"
                                >
                                    +{{ suite.facilities.length - 4 }} lainnya
                                </span>
                            </div>

                            <div class="pt-3 border-t border-stone-100 flex items-center justify-between text-xs tabular-nums">
                                <span class="text-stone-500">Estimasi {{ totalNights }} malam</span>
                                <span class="font-bold text-stone-900">{{ formatRupiah(suite.base_price * totalNights) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="px-5 py-3.5 bg-stone-50/80 border-t border-stone-200 flex items-center justify-between gap-2.5">
                        <button
                            type="button"
                            @click="openSuiteModal(suite)"
                            class="px-3.5 py-2 rounded-xl border border-stone-300 bg-white text-xs font-semibold text-stone-700 hover:bg-stone-100 transition"
                        >
                            Rincian
                        </button>

                        <a
                            v-if="authUser"
                            :href="bookingUrl(suite)"
                            :class="suite.available_count === 0 ? 'pointer-events-none opacity-45' : ''"
                            class="svara-card-cta"
                        >
                            <span class="svara-dark-btn-icon">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <rect x="3" y="4" width="18" height="18" rx="2" />
                                    <path d="M16 2v4M8 2v4M3 10h18" />
                                </svg>
                            </span>
                            <span>{{ suite.available_count > 0 ? 'Pesan Suite' : 'Unit Penuh' }}</span>
                            <svg class="w-3.5 h-3.5 text-stone-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>

                        <a v-else href="/login" class="svara-card-cta">
                            <span class="svara-dark-btn-icon">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <rect x="3" y="4" width="18" height="18" rx="2" />
                                    <path d="M16 2v4M8 2v4M3 10h18" />
                                </svg>
                            </span>
                            <span>Masuk & Pesan</span>
                            <svg class="w-3.5 h-3.5 text-stone-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </article>
            </div>

            <!-- 5. STANDAR LAYANAN & INFORMASI MENGINAP -->
            <section id="standar-layanan" class="svara-standards-grid">
                <div class="bg-white border border-stone-200 rounded-2xl p-6">
                    <div class="w-9 h-9 rounded-xl bg-amber-50 border border-amber-200/80 text-amber-900 flex items-center justify-center mb-4">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 16 14" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-stone-900">Waktu Check-In & Check-Out</h3>
                    <p class="mt-1.5 text-xs text-stone-600 leading-relaxed">
                        Check-in dimulai pukul <strong>14:00 WIB</strong> dan check-out maksimal pukul <strong>12:00 WIB</strong>. Permintaan <em>early check-in</em> dapat dicantumkan pada catatan reservasi.
                    </p>
                </div>

                <div class="bg-white border border-stone-200 rounded-2xl p-6">
                    <div class="w-9 h-9 rounded-xl bg-amber-50 border border-amber-200/80 text-amber-900 flex items-center justify-center mb-4">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                            <polyline points="22 4 12 14.01 9 11.01" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-stone-900">Ketersediaan Unit Real-Time</h3>
                    <p class="mt-1.5 text-xs text-stone-600 leading-relaxed">
                        Seluruh status kamar terhubung langsung dengan sistem <em>front desk</em> Svara sehingga nomor kamar pilihan Anda langsung terkunci begitu reservasi dibuat.
                    </p>
                </div>

                <div id="informasi-hotel" class="bg-white border border-stone-200 rounded-2xl p-6">
                    <div class="w-9 h-9 rounded-xl bg-amber-50 border border-amber-200/80 text-amber-900 flex items-center justify-center mb-4">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                            <circle cx="12" cy="10" r="3" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-stone-900">Layanan Concierge 24 Jam</h3>
                    <p class="mt-1.5 text-xs text-stone-600 leading-relaxed">
                        Resepsionis kami siap membantu kebutuhan tambahan bantal, penjemputan bandara, hingga pengaturan jadwal menginap keluarga sepanjang hari.
                    </p>
                </div>
            </section>
        </main>

        <!-- 6. MODAL RINCIAN SUITE & SIMULASI RESERVASI -->
        <div v-if="selectedSuite" @click.self="closeSuiteModal" class="svara-modal-backdrop">
            <div class="svara-modal-box">
                <div class="overflow-y-auto">
                    <div class="relative h-64 bg-stone-200">
                        <img
                            :src="selectedSuite.cover_url"
                            :alt="selectedSuite.name"
                            @error="handleImgError"
                            class="w-full h-full object-cover"
                        />
                        <div class="svara-card-overlay"></div>

                        <button
                            type="button"
                            @click="closeSuiteModal"
                            title="Tutup"
                            class="absolute top-4 right-4 w-9 h-9 rounded-full bg-black/55 hover:bg-black/80 border border-white/25 text-white flex items-center justify-center transition"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <line x1="18" y1="6" x2="6" y2="18" />
                                <line x1="6" y1="6" x2="18" y2="18" />
                            </svg>
                        </button>

                        <div class="absolute bottom-4 left-6 right-6 flex flex-wrap items-end justify-between gap-4 text-white">
                            <div>
                                <span class="text-[10px] font-semibold uppercase tracking-[0.2em] text-amber-300">
                                    Maksimal {{ selectedSuite.capacity }} Tamu
                                </span>
                                <h3 class="font-editorial text-3xl sm:text-4xl text-amber-50 leading-tight mt-0.5">
                                    {{ selectedSuite.name }}
                                </h3>
                            </div>
                            <div class="text-right tabular-nums">
                                <p class="text-xl font-bold text-white">{{ formatRupiah(selectedSuite.base_price) }}</p>
                                <p class="text-[11px] text-stone-300">per malam (termasuk pajak)</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 sm:p-7 space-y-6">
                        <p class="text-xs sm:text-sm text-stone-600 leading-relaxed">{{ selectedSuite.description }}</p>

                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-stone-400 mb-2.5">Fasilitas Lengkap Suite</p>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                <div
                                    v-for="fac in selectedSuite.facilities"
                                    :key="'modal-' + fac"
                                    class="flex items-center gap-2 px-3 py-2 rounded-xl bg-stone-50 border border-stone-200/80 text-xs font-medium text-stone-800"
                                >
                                    <svg class="w-3.5 h-3.5 text-amber-800 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <polyline points="20 6 9 17 4 12" />
                                    </svg>
                                    <span class="truncate">{{ fac }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 sm:p-5 rounded-2xl bg-stone-50 border border-stone-200 space-y-4">
                            <div class="svara-modal-form-grid">
                                <div>
                                    <label class="svara-field-label">Check-In</label>
                                    <input type="date" v-model="checkIn" :min="todayStr" class="svara-input tabular-nums" />
                                </div>
                                <div>
                                    <label class="svara-field-label">Check-Out</label>
                                    <input type="date" v-model="checkOut" :min="checkIn" class="svara-input tabular-nums" />
                                </div>
                                <div>
                                    <label class="svara-field-label">Pilih Nomor Unit</label>
                                    <select
                                        v-model="selectedRoomId"
                                        :disabled="selectedSuite.available_rooms.length === 0"
                                        class="svara-input"
                                    >
                                        <option v-if="selectedSuite.available_rooms.length === 0" value="">Tidak ada unit tersedia</option>
                                        <option v-for="unit in selectedSuite.available_rooms" :key="unit.id" :value="String(unit.id)">
                                            Kamar {{ unit.room_number }} (Lantai {{ unit.floor }})
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="pt-3 border-t border-stone-200 flex flex-wrap items-center justify-between gap-4">
                                <div class="tabular-nums">
                                    <p class="text-[11px] text-stone-500">{{ formatRupiah(selectedSuite.base_price) }} &times; {{ totalNights }} malam</p>
                                    <p class="text-xl font-bold text-amber-900">{{ formatRupiah(selectedSuite.base_price * totalNights) }}</p>
                                </div>

                                <div class="flex items-center gap-2.5">
                                    <button
                                        type="button"
                                        @click="closeSuiteModal"
                                        class="px-4 py-2.5 rounded-xl border border-stone-300 bg-white text-xs font-semibold text-stone-700 hover:bg-stone-100 transition"
                                    >
                                        Tutup
                                    </button>

                                    <a
                                        v-if="authUser"
                                        :href="bookingUrl(selectedSuite)"
                                        :class="selectedSuite.available_count === 0 ? 'pointer-events-none opacity-45' : ''"
                                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#181512] text-amber-50 text-xs font-semibold hover:bg-stone-900 transition"
                                    >
                                        <span>Lanjutkan Reservasi</span>
                                        <span>&rarr;</span>
                                    </a>

                                    <a
                                        v-else
                                        href="/login"
                                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#181512] text-amber-50 text-xs font-semibold hover:bg-stone-900 transition"
                                    >
                                        <span>Masuk untuk Memesan</span>
                                        <span>&rarr;</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 7. FOOTER EDITORIAL -->
        <footer class="bg-[#181512] text-stone-400 border-t border-stone-800 mt-auto">
            <div class="svara-container py-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs">
                <div class="flex items-center gap-3">
                    <span class="font-editorial text-xl text-amber-50">Svara Hotel & Suites</span>
                    <span class="text-stone-600">&bull;</span>
                    <span>Hospitality & Reservation System</span>
                </div>
                <p class="text-[11px] text-stone-500">&copy; {{ new Date().getFullYear() }} Svara Hotel. Seluruh hak cipta dilindungi.</p>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.svara-container {
    width: 100%;
    max-width: 80rem;
    margin-left: auto;
    margin-right: auto;
    padding-left: 1.25rem;
    padding-right: 1.25rem;
}
@media (min-width: 1024px) {
    .svara-container {
        padding-left: 2rem;
        padding-right: 2rem;
    }
}

/* Navbar */
.svara-navbar {
    position: sticky;
    top: 0;
    z-index: 30;
    background-color: rgba(250, 249, 246, 0.95);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid #e7e5e4;
}
.svara-navbar-inner {
    height: 4.25rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}
.svara-logo-mark {
    width: 2.25rem;
    height: 2.25rem;
    flex-shrink: 0;
    border-radius: 0.5rem;
    background-color: #181512;
    border: 1px solid rgba(180, 83, 9, 0.45);
    color: #fcd34d;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}
.svara-nav-links {
    display: none;
}
@media (min-width: 768px) {
    .svara-nav-links {
        display: flex;
        align-items: center;
        gap: 2rem;
        font-size: 0.75rem;
        font-weight: 600;
        color: #57534e;
    }
}
.svara-user-pill {
    display: none;
}
@media (min-width: 640px) {
    .svara-user-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.375rem 0.75rem;
        border-radius: 0.5rem;
        background-color: #f5f5f4;
        border: 1px solid #e7e5e4;
        font-size: 11px;
        font-weight: 600;
        color: #44403c;
    }
}
.svara-dark-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.625rem;
    padding: 0.375rem 0.95rem 0.375rem 0.45rem;
    border-radius: 0.75rem;
    background-color: #181512;
    border: 1px solid #292524;
    color: #fffbeb;
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap;
    transition: all 0.2s ease;
}
.svara-dark-btn:hover {
    background-color: #1c1917;
    border-color: rgba(180, 83, 9, 0.6);
}
.svara-dark-btn-icon {
    width: 1.625rem;
    height: 1.625rem;
    flex-shrink: 0;
    border-radius: 0.45rem;
    background-color: rgba(245, 158, 11, 0.16);
    border: 1px solid rgba(251, 191, 36, 0.3);
    color: #fcd34d;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Hero Section */
.svara-hero {
    position: relative;
    background-color: #181512;
    color: #ffffff;
    overflow: hidden;
}
.svara-hero-bg {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: 0.4;
}
.svara-hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        90deg,
        #181512 0%,
        rgba(24, 21, 18, 0.93) 48%,
        rgba(24, 21, 18, 0.58) 100%
    );
}
.svara-hero-content {
    position: relative;
    z-index: 10;
    padding-top: 4rem;
    padding-bottom: 5.5rem;
}
@media (min-width: 1024px) {
    .svara-hero-content {
        padding-top: 5rem;
        padding-bottom: 6.5rem;
    }
}
.svara-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.375rem 0.875rem;
    border-radius: 9999px;
    background-color: rgba(69, 26, 3, 0.85);
    border: 1px solid rgba(245, 158, 11, 0.35);
    color: #fde68a;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.2em;
}
.svara-hero-title {
    color: #fffbeb;
    font-size: clamp(2.35rem, 4.4vw, 3.5rem);
    line-height: 1.08;
    margin-top: 1.25rem;
}
.svara-hero-desc {
    margin-top: 1rem;
    font-size: 0.9375rem;
    line-height: 1.65;
    color: #e7e5e4;
    max-width: 36rem;
}
.svara-amber-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.625rem;
    padding: 0.45rem 1rem 0.45rem 0.5rem;
    border-radius: 0.75rem;
    background-color: #92400e;
    border: 1px solid rgba(245, 158, 11, 0.35);
    color: #ffffff;
    font-size: 0.75rem;
    font-weight: 600;
    transition: background-color 0.2s ease;
}
.svara-amber-btn:hover {
    background-color: #78350f;
}
.svara-amber-btn-icon {
    width: 1.75rem;
    height: 1.75rem;
    border-radius: 0.5rem;
    background-color: rgba(0, 0, 0, 0.25);
    color: #fde68a;
    display: flex;
    align-items: center;
    justify-content: center;
}
.svara-hero-hours {
    padding: 0.625rem 1rem;
    border-radius: 0.75rem;
    background-color: rgba(12, 10, 9, 0.65);
    border: 1px solid rgba(255, 255, 255, 0.16);
    font-size: 0.75rem;
    color: #e7e5e4;
}

/* Filter Bar */
.svara-filter-wrapper {
    position: relative;
    z-index: 20;
    margin-top: -2.25rem;
}
.svara-filter-card {
    background-color: #ffffff;
    border: 1px solid #e7e5e4;
    border-radius: 1rem;
    padding: 1.25rem 1.5rem;
    box-shadow: 0 14px 35px -12px rgba(24, 21, 18, 0.14);
}
.svara-filter-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.875rem;
    align-items: end;
}
@media (min-width: 640px) {
    .svara-filter-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .svara-search-col {
        grid-column: span 2 / span 2;
    }
}
@media (min-width: 1024px) {
    .svara-filter-grid {
        grid-template-columns: 155px 155px 155px 165px minmax(220px, 1fr);
    }
    .svara-search-col {
        grid-column: auto;
    }
}
.svara-field-label {
    display: block;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #78716c;
    margin-bottom: 0.375rem;
}
.svara-input {
    width: 100%;
    border-radius: 0.75rem;
    border: 1px solid #d6d3d1;
    background-color: #ffffff;
    padding: 0.525rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 500;
    color: #1c1917;
}
.svara-input:focus {
    outline: none;
    border-color: #92400e;
    box-shadow: 0 0 0 1px #92400e;
}
.svara-search-input {
    padding-left: 2.35rem;
}

/* Suite Cards Grid */
.svara-cards-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.75rem;
}
@media (min-width: 768px) {
    .svara-cards-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}
@media (min-width: 1024px) {
    .svara-cards-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}
.svara-suite-card {
    background-color: #ffffff;
    border: 1px solid #e7e5e4;
    border-radius: 1rem;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: all 0.25s ease;
}
.svara-suite-card:hover {
    border-color: #d6d3d1;
    box-shadow: 0 14px 35px -12px rgba(24, 21, 18, 0.12);
}
.svara-card-media {
    position: relative;
    height: 14rem;
    background-color: #e7e5e4;
    overflow: hidden;
    cursor: pointer;
}
.svara-card-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        to top,
        rgba(12, 10, 9, 0.9) 0%,
        rgba(12, 10, 9, 0.28) 55%,
        rgba(12, 10, 9, 0.18) 100%
    );
}
.svara-card-cta {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    padding: 0.375rem 0.875rem 0.375rem 0.45rem;
    border-radius: 0.75rem;
    background-color: #181512;
    border: 1px solid #292524;
    color: #fffbeb;
    font-size: 0.75rem;
    font-weight: 600;
    transition: all 0.2s ease;
}
.svara-card-cta:hover {
    background-color: #1c1917;
    border-color: rgba(180, 83, 9, 0.6);
}

/* Standards Grid */
.svara-standards-grid {
    margin-top: 4rem;
    padding-top: 3rem;
    border-top: 1px solid #e7e5e4;
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
}
@media (min-width: 768px) {
    .svara-standards-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

/* Modal */
.svara-modal-backdrop {
    position: fixed;
    inset: 0;
    z-index: 50;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    background-color: rgba(12, 10, 9, 0.75);
    backdrop-filter: blur(4px);
}
.svara-modal-box {
    background-color: #ffffff;
    border: 1px solid #e7e5e4;
    border-radius: 1rem;
    width: 100%;
    max-width: 48rem;
    max-height: 90vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.45);
}
.svara-modal-form-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.75rem;
}
@media (min-width: 640px) {
    .svara-modal-form-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}
</style>