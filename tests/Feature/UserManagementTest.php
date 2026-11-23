<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->withoutVite();
    $this->seed(RolePermissionSeeder::class);
});

function userMgmtTestUser(string $role, array $attrs = []): User
{
    $user = User::factory()->create($attrs);
    $user->assignRole($role);

    return $user;
}

test('hanya admin yang boleh membuka manajemen pengguna', function () {
    $this->actingAs(userMgmtTestUser('tamu'))->get(route('users.index'))->assertForbidden();
    $this->actingAs(userMgmtTestUser('resepsionis'))->get(route('users.index'))->assertForbidden();
    $this->actingAs(userMgmtTestUser('admin'))->get(route('users.index'))->assertOk();
});

test('pencarian menyaring pengguna berdasarkan nama atau email', function () {
    userMgmtTestUser('tamu', ['name' => 'Zulkifli Uji', 'email' => 'zulkifli@contoh.test']);
    userMgmtTestUser('tamu', ['name' => 'Budi Lain', 'email' => 'budi@contoh.test']);

    $this->actingAs(userMgmtTestUser('admin'))
        ->get(route('users.index', ['cari' => 'zulkifli']))
        ->assertOk()
        ->assertSee('Zulkifli Uji')
        ->assertDontSee('Budi Lain');
});

test('admin dapat membuat akun resepsionis baru', function () {
    $this->actingAs(userMgmtTestUser('admin'))
        ->post(route('users.store'), [
            'name' => 'Staf Baru',
            'email' => 'staf.baru@contoh.test',
            'phone' => '081200000000',
            'role' => 'resepsionis',
            'password' => 'Rahasia-12345',
            'password_confirmation' => 'Rahasia-12345',
        ])
        ->assertRedirect(route('users.index'));

    $created = User::where('email', 'staf.baru@contoh.test')->first();
    expect($created)->not->toBeNull();
    expect($created->hasRole('resepsionis'))->toBeTrue();
});

test('akun baru tidak boleh dibuat dengan role tamu lewat form staf', function () {
    $this->actingAs(userMgmtTestUser('admin'))
        ->post(route('users.store'), [
            'name' => 'Bukan Staf',
            'email' => 'bukan.staf@contoh.test',
            'role' => 'tamu',
            'password' => 'Rahasia-12345',
            'password_confirmation' => 'Rahasia-12345',
        ])
        ->assertSessionHasErrors('role');

    $this->assertDatabaseMissing('users', ['email' => 'bukan.staf@contoh.test']);
});

test('admin dapat mengubah role pengguna lain', function () {
    $target = userMgmtTestUser('tamu');

    $this->actingAs(userMgmtTestUser('admin'))
        ->patch(route('users.role', $target), ['role' => 'resepsionis'])
        ->assertSessionHasNoErrors();

    expect($target->fresh()->hasRole('resepsionis'))->toBeTrue();
    expect($target->fresh()->hasRole('tamu'))->toBeFalse();
});

test('admin tidak dapat mengubah role akunnya sendiri', function () {
    $admin = userMgmtTestUser('admin');

    $this->actingAs($admin)
        ->patch(route('users.role', $admin), ['role' => 'tamu'])
        ->assertSessionHasErrors('role');

    expect($admin->fresh()->hasRole('admin'))->toBeTrue();
});

test('resepsionis tidak dapat mengubah role pengguna', function () {
    $target = userMgmtTestUser('tamu');

    $this->actingAs(userMgmtTestUser('resepsionis'))
        ->patch(route('users.role', $target), ['role' => 'admin'])
        ->assertForbidden();

    expect($target->fresh()->hasRole('tamu'))->toBeTrue();
});
