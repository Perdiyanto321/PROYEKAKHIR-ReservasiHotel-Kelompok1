<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Jika tamu login, tampilkan ringkasan reservasi pribadinya
        if ($user->hasRole('tamu')) {
            $myReservations = Reservation::with('room.roomType')
                ->where('user_id', $user->id)
                ->latest()
                ->take(5)
                ->get();

            return view('dashboard', [
                'isGuest' => true,
                'myReservations' => $myReservations,
            ]);
        }

        // KPI Operasional untuk Admin & Resepsionis
        $totalRooms = Room::count();
        $availableRooms = Room::tersedia()->count();
        $occupiedRooms = Room::where('status', 'terisi')->count();
        $occupancyRate = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100) : 0;
        $todayCheckIns = Reservation::whereDate('check_in_date', today())
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->count();
        $totalRevenue = Reservation::whereIn('status', ['confirmed', 'checked_in', 'checked_out'])
            ->sum('total_price');

        $recentReservations = Reservation::with(['user', 'room.roomType'])
            ->latest()
            ->take(6)
            ->get();

        return view('dashboard', [
            'isGuest' => false,
            'totalRooms' => $totalRooms,
            'availableRooms' => $availableRooms,
            'occupiedRooms' => $occupiedRooms,
            'occupancyRate' => $occupancyRate,
            'todayCheckIns' => $todayCheckIns,
            'totalRevenue' => $totalRevenue,
            'recentReservations' => $recentReservations,
        ]);
    }
}