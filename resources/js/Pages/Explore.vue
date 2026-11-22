<script setup>
defineProps({
    roomTypes: Array,
});
</script>

<template>
    <div class="min-h-screen max-w-6xl mx-auto px-6 py-12">
        <p class="text-xs uppercase tracking-[0.2em] text-amber-800 font-semibold">Svara Hotel & Suites</p>
        <h1 class="text-4xl mt-1" style="font-family: 'Instrument Serif', serif;">Katalog Kamar & Reservasi</h1>
        <p class="text-sm text-stone-600 mt-2">Halaman SPA Inertia + Vue 3 siap dikembangkan oleh tim.</p>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
            <div v-for="type in roomTypes" :key="type.id" class="bg-white border border-stone-200 rounded-xl overflow-hidden">
                <img :src="type.image_url" :alt="type.name" class="w-full h-48 object-cover">
                <div class="p-5">
                    <h2 class="font-semibold text-lg">{{ type.name }}</h2>
                    <p class="text-xs text-stone-500 mt-1">Kapasitas {{ type.capacity }} Tamu</p>
                    <p class="text-amber-800 font-bold mt-3 tabular-nums">Rp {{ Number(type.base_price).toLocaleString('id-ID') }} / malam</p>
                </div>
            </div>
        </div>
    </div>
</template>