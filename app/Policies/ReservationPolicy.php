<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;

class ReservationPolicy
{
    /**
     * Staf (admin/resepsionis) boleh melihat semua reservasi;
     * Tamu hanya boleh melihat reservasi miliknya sendiri (anti-IDOR).
     */
    public function view(User $user, Reservation $reservation): bool
    {
        return $user->hasAnyRole(['admin', 'resepsionis'])
            || $user->id === $reservation->user_id;
    }

    /**
     * Hanya admin dan resepsionis yang boleh memproses status (check-in / check-out).
     */
    public function update(User $user, Reservation $reservation): bool
    {
        return $user->hasAnyRole(['admin', 'resepsionis']);
    }

    /**
     * Staf atau pemilik reservasi boleh membatalkan selama status masih pending/confirmed.
     */
    public function cancel(User $user, Reservation $reservation): bool
    {
        $isOwnerOrStaff = $user->hasAnyRole(['admin', 'resepsionis'])
            || $user->id === $reservation->user_id;

        return $isOwnerOrStaff && in_array($reservation->status, ['pending', 'confirmed'], true);
    }

    /**
     * Hanya admin yang berhak menghapus permanen riwayat reservasi.
     */
    public function delete(User $user, Reservation $reservation): bool
    {
        return $user->hasRole('admin');
    }
}