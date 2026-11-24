<?php

namespace Database\Seeders;

use App\Models\Facility;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Data master hotel: fasilitas, tipe kamar, dan unit kamar (Lantai 1–3).
 *
 * Status unit kamar sengaja dibuat selaras dengan DemoReservationSeeder:
 * kamar yang punya reservasi "checked_in" berstatus "terisi".
 */
class HotelSeeder extends Seeder
{
    public function run(): void
    {
        // ---------- Fasilitas ----------
        $facilityList = [
            ['name' => 'High-Speed Wi-Fi',   'icon' => 'wifi'],
            ['name' => 'King Size Bed',      'icon' => 'bed-double'],
            ['name' => 'Smart TV 55"',       'icon' => 'tv'],
            ['name' => 'Artisan Breakfast',  'icon' => 'coffee'],
            ['name' => 'Private Balcony',    'icon' => 'sun'],
            ['name' => 'Soaking Bathtub',    'icon' => 'bath'],
            ['name' => 'Air Conditioning',   'icon' => 'wind'],
            ['name' => 'Rain Shower',        'icon' => 'droplets'],
            ['name' => 'Mini Bar',           'icon' => 'wine'],
            ['name' => 'Workspace Desk',     'icon' => 'laptop'],
            // Sengaja belum dipasang ke tipe kamar mana pun (demo: fasilitas yang aman dihapus)
            ['name' => 'Airport Shuttle',    'icon' => 'car'],
        ];

        $facilities = [];
        foreach ($facilityList as $item) {
            $facilities[$item['name']] = Facility::updateOrCreate(['name' => $item['name']], ['icon' => $item['icon']]);
        }

        // ---------- Tipe kamar ----------
        // 'slug' dan 'image_url' belum masuk $fillable pada model RoomType, jadi dibuka sementara
        // dengan Model::unguarded() khusus di seeder (model tidak diubah).
        $types = [
            'superior-king' => [
                'name' => 'Superior King Room',
                'base_price' => 650000,
                'capacity' => 2,
                'description' => 'Kamar modern berdesain hangat dengan pencahayaan alami dan meja kerja ergonomis.',
                'image_url' => 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?auto=format&fit=crop&w=1200&q=80',
                'colors' => ['#d6c4a8', '#8a6a43'],
                'facilities' => ['High-Speed Wi-Fi', 'King Size Bed', 'Smart TV 55"', 'Air Conditioning', 'Workspace Desk'],
            ],
            'deluxe-garden' => [
                'name' => 'Deluxe Garden View',
                'base_price' => 950000,
                'capacity' => 2,
                'description' => 'Kamar luas menghadap taman tropis dengan balkon pribadi dan sarapan untuk dua orang.',
                'image_url' => 'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=1200&q=80',
                'colors' => ['#b9c4a3', '#5c6b3f'],
                'facilities' => ['High-Speed Wi-Fi', 'King Size Bed', 'Smart TV 55"', 'Air Conditioning', 'Artisan Breakfast', 'Private Balcony', 'Rain Shower'],
            ],
            'executive-suite' => [
                'name' => 'Executive Svara Suite',
                'base_price' => 1650000,
                'capacity' => 4,
                'description' => 'Suite eksklusif dengan ruang tamu terpisah, bathtub marmer, dan panorama kota.',
                'image_url' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=1200&q=80',
                'colors' => ['#c9a98a', '#4a3322'],
                'facilities' => ['High-Speed Wi-Fi', 'King Size Bed', 'Smart TV 55"', 'Air Conditioning', 'Artisan Breakfast', 'Private Balcony', 'Soaking Bathtub', 'Mini Bar', 'Rain Shower', 'Workspace Desk'],
            ],
        ];

        $roomTypes = [];
        foreach ($types as $slug => $data) {
            // Foto lokal (SVG) dibuat otomatis agar katalog tetap berfoto saat uji coba tanpa internet.
            // Tampil di browser setelah `php artisan storage:link`.
            $imagePath = $this->writePlaceholderImage($slug, $data['name'], $data['colors']);

            $roomType = RoomType::unguarded(fn () => RoomType::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $data['name'],
                    'base_price' => $data['base_price'],
                    'capacity' => $data['capacity'],
                    'description' => $data['description'],
                    'image' => $imagePath,
                    'image_url' => $data['image_url'],
                ]
            ));

            $roomType->facilities()->sync(
                collect($data['facilities'])->map(fn ($name) => $facilities[$name]->id)->all()
            );

            $roomTypes[$slug] = $roomType;
        }

        // ---------- Unit kamar (Lantai 1–3) ----------
        $rooms = [
            // Lantai 1 — Superior King
            ['101', 1, 'superior-king', 'tersedia'],
            ['102', 1, 'superior-king', 'terisi'],
            ['103', 1, 'superior-king', 'terisi'],
            ['104', 1, 'superior-king', 'tersedia'],
            // Lantai 2 — Deluxe Garden View
            ['201', 2, 'deluxe-garden', 'tersedia'],
            ['202', 2, 'deluxe-garden', 'tersedia'],
            ['203', 2, 'deluxe-garden', 'tersedia'],
            ['204', 2, 'deluxe-garden', 'terisi'],
            // Lantai 3 — Executive Svara Suite
            ['301', 3, 'executive-suite', 'terisi'],
            ['302', 3, 'executive-suite', 'tersedia'],
            ['303', 3, 'executive-suite', 'tersedia'],
            ['304', 3, 'executive-suite', 'maintenance', 'Perawatan AC berkala'],
        ];

        foreach ($rooms as $r) {
            Room::updateOrCreate(
                ['room_number' => $r[0]],
                [
                    'room_type_id' => $roomTypes[$r[2]]->id,
                    'floor' => $r[1],
                    'status' => $r[3],
                    'notes' => $r[4] ?? null,
                ]
            );
        }
    }

    /**
     * Buat gambar SVG sederhana bernuansa hangat sebagai foto tipe kamar bawaan.
     *
     * @param  array{0: string, 1: string}  $colors
     */
    private function writePlaceholderImage(string $slug, string $title, array $colors): string
    {
        $path = "room-types/demo-{$slug}.svg";
        $title = htmlspecialchars($title, ENT_XML1 | ENT_QUOTES, 'UTF-8');

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="750" viewBox="0 0 1200 750">
  <defs>
    <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="{$colors[0]}"/>
      <stop offset="1" stop-color="{$colors[1]}"/>
    </linearGradient>
  </defs>
  <rect width="1200" height="750" fill="url(#g)"/>
  <rect x="140" y="470" width="920" height="160" rx="18" fill="#ffffff" fill-opacity="0.18"/>
  <rect x="190" y="400" width="360" height="110" rx="14" fill="#ffffff" fill-opacity="0.28"/>
  <rect x="650" y="400" width="360" height="110" rx="14" fill="#ffffff" fill-opacity="0.28"/>
  <text x="600" y="250" text-anchor="middle" font-family="Georgia, serif" font-size="64" fill="#fffaf0">{$title}</text>
  <text x="600" y="305" text-anchor="middle" font-family="Arial, sans-serif" font-size="22" letter-spacing="8" fill="#fffaf0" fill-opacity="0.8">SVARA HOTEL &amp; SUITES</text>
</svg>
SVG;

        Storage::disk('public')->put($path, $svg);

        return $path;
    }
}
