<?php

namespace Database\Seeders;

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Database\Seeder;

class HotelSeeder extends Seeder
{
    public function run(): void
    {
        $facilities = collect([
            ['name' => 'High-Speed Wi-Fi', 'icon' => 'wifi'],
            ['name' => 'King Size Bed', 'icon' => 'bed-double'],
            ['name' => 'Smart TV 55"', 'icon' => 'tv'],
            ['name' => 'Artisan Breakfast', 'icon' => 'coffee'],
            ['name' => 'Private Balcony', 'icon' => 'sun'],
            ['name' => 'Soaking Bathtub', 'icon' => 'bath'],
        ])->map(fn ($item) => Facility::firstOrCreate(['name' => $item['name']], $item));

        $standard = RoomType::firstOrCreate(
            ['slug' => 'superior-king'],
            [
                'name' => 'Superior King Room',
                'base_price' => 650000,
                'capacity' => 2,
                'description' => 'Kamar modern berdesain hangat dengan pencahayaan alami dan meja kerja ergonomis.',
                'image_url' => 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?auto=format&fit=crop&w=1200&q=80',
            ]
        );
        $standard->facilities()->sync($facilities->take(3)->pluck('id'));

        $deluxe = RoomType::firstOrCreate(
            ['slug' => 'deluxe-garden'],
            [
                'name' => 'Deluxe Garden View',
                'base_price' => 950000,
                'capacity' => 2,
                'description' => 'Kamar luas menghadap taman tropis dengan balkon pribadi dan sarapan untuk dua orang.',
                'image_url' => 'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=1200&q=80',
            ]
        );
        $deluxe->facilities()->sync($facilities->take(5)->pluck('id'));

        $suite = RoomType::firstOrCreate(
            ['slug' => 'executive-suite'],
            [
                'name' => 'Executive Svara Suite',
                'base_price' => 1650000,
                'capacity' => 4,
                'description' => 'Suite eksklusif dengan ruang tamu terpisah, bathtub marmer, dan panorama kota.',
                'image_url' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=1200&q=80',
            ]
        );
        $suite->facilities()->sync($facilities->pluck('id'));

        // Unit Kamar
        $roomsData = [
            ['room_type_id' => $standard->id, 'room_number' => '101', 'floor' => 1, 'status' => 'tersedia'],
            ['room_type_id' => $standard->id, 'room_number' => '102', 'floor' => 1, 'status' => 'terisi'],
            ['room_type_id' => $standard->id, 'room_number' => '103', 'floor' => 1, 'status' => 'tersedia'],
            ['room_type_id' => $deluxe->id,   'room_number' => '201', 'floor' => 2, 'status' => 'tersedia'],
            ['room_type_id' => $deluxe->id,   'room_number' => '202', 'floor' => 2, 'status' => 'maintenance', 'notes' => 'Perawatan AC berkala'],
            ['room_type_id' => $deluxe->id,   'room_number' => '203', 'floor' => 2, 'status' => 'tersedia'],
            ['room_type_id' => $suite->id,    'room_number' => '301', 'floor' => 3, 'status' => 'terisi'],
            ['room_type_id' => $suite->id,    'room_number' => '302', 'floor' => 3, 'status' => 'tersedia'],
        ];

        foreach ($roomsData as $r) {
            Room::firstOrCreate(['room_number' => $r['room_number']], $r);
        }

        // Data Reservasi Uji (Untuk Skenario Policy / IDOR Modul 6)
        $tamu1 = User::where('email', 'tamu@hotel.test')->first();
        $tamu2 = User::where('email', 'tamu2@hotel.test')->first();
        $room102 = Room::where('room_number', '102')->first();
        $room301 = Room::where('room_number', '301')->first();

        if ($tamu1 && $room102) {
            $rsv1 = new Reservation([
                'booking_code' => 'RSV-20261121-0001',
                'room_id' => $room102->id,
                'check_in_date' => now()->toDateString(),
                'check_out_date' => now()->addDays(2)->toDateString(),
                'total_nights' => 2,
                'total_price' => 1300000,
                'status' => 'checked_in',
                'special_requests' => 'Extra bantal 2 buah',
            ]);
            $rsv1->user_id = $tamu1->id;
            $rsv1->save();
        }

        if ($tamu2 && $room301) {
            $rsv2 = new Reservation([
                'booking_code' => 'RSV-20261121-0002',
                'room_id' => $room301->id,
                'check_in_date' => now()->toDateString(),
                'check_out_date' => now()->addDays(3)->toDateString(),
                'total_nights' => 3,
                'total_price' => 4950000,
                'status' => 'checked_in',
                'special_requests' => 'Late check-out jam 13.00',
            ]);
            $rsv2->user_id = $tamu2->id;
            $rsv2->save();
        }
    }
}