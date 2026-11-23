<?php

use App\Models\Facility;
use App\Models\RoomType;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->withoutVite();
    $this->seed(RolePermissionSeeder::class);
});

function facilityTestUser(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

test('hanya admin yang boleh membuka master fasilitas', function () {
    $this->actingAs(facilityTestUser('tamu'))->get(route('facilities.index'))->assertForbidden();
    $this->actingAs(facilityTestUser('resepsionis'))->get(route('facilities.index'))->assertForbidden();
    $this->actingAs(facilityTestUser('admin'))->get(route('facilities.index'))->assertOk();
});

test('admin dapat menambah fasilitas dan nama ganda ditolak', function () {
    $admin = facilityTestUser('admin');

    $this->actingAs($admin)
        ->post(route('facilities.store'), ['name' => 'Rain Shower'])
        ->assertRedirect(route('facilities.index'));

    $this->assertDatabaseHas('facilities', ['name' => 'Rain Shower']);

    $this->actingAs($admin)
        ->post(route('facilities.store'), ['name' => 'Rain Shower'])
        ->assertSessionHasErrors('name');
});

test('admin dapat mengubah nama fasilitas', function () {
    $facility = Facility::create(['name' => 'Wifi Lama']);

    $this->actingAs(facilityTestUser('admin'))
        ->put(route('facilities.update', $facility), ['name' => 'High-Speed Wi-Fi'])
        ->assertRedirect(route('facilities.index'));

    $this->assertDatabaseHas('facilities', ['id' => $facility->id, 'name' => 'High-Speed Wi-Fi']);
});

test('fasilitas yang masih dipakai tipe kamar tidak bisa dihapus', function () {
    $facility = Facility::create(['name' => 'King Size Bed']);
    $type = RoomType::unguarded(fn () => RoomType::create([
        'name' => 'Tipe Uji',
        'slug' => 'tipe-uji',
        'base_price' => 500000,
        'capacity' => 2,
    ]));
    $type->facilities()->attach($facility->id);

    $this->actingAs(facilityTestUser('admin'))
        ->delete(route('facilities.destroy', $facility))
        ->assertSessionHasErrors('delete');

    $this->assertDatabaseHas('facilities', ['id' => $facility->id]);
});

test('fasilitas yang tidak dipakai dapat dihapus', function () {
    $facility = Facility::create(['name' => 'Airport Shuttle']);

    $this->actingAs(facilityTestUser('admin'))
        ->delete(route('facilities.destroy', $facility))
        ->assertRedirect(route('facilities.index'));

    $this->assertDatabaseMissing('facilities', ['id' => $facility->id]);
});
