<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /** Status yang dihitung sebagai pendapatan terkonfirmasi. */
    private const REVENUE_STATUSES = ['checked_in', 'checked_out'];

    private const STATUSES = ['pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled'];

    private const STATUS_LABELS = [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'checked_in' => 'Checked-In',
        'checked_out' => 'Checked-Out',
        'cancelled' => 'Dibatalkan',
    ];

    public function index(Request $request)
    {
        [$start, $end, $status] = $this->resolveFilters($request);

        // Kartu KPI: hanya reservasi berstatus checked_in & checked_out dalam rentang tanggal
        $revenueQuery = $this->baseQuery($start, $end)->whereIn('status', self::REVENUE_STATUSES);
        $kpi = [
            'revenue' => (int) (clone $revenueQuery)->sum('total_price'),
            'nights' => (int) (clone $revenueQuery)->sum('total_nights'),
            'transactions' => (clone $revenueQuery)->count(),
        ];

        // Tabel rincian: mengikuti filter tanggal + status
        $detailQuery = $this->baseQuery($start, $end, $status)
            ->with(['user:id,name', 'room.roomType:id,name'])
            ->orderByDesc('check_in_date')
            ->orderByDesc('id');

        // Mode cetak menampilkan seluruh baris (tanpa paginasi)
        $printMode = $request->boolean('print');
        $transactions = $printMode
            ? $detailQuery->get()
            : $detailQuery->paginate(15)->withQueryString();

        return view('reports.index', [
            'start' => $start,
            'end' => $end,
            'status' => $status,
            'statusLabels' => self::STATUS_LABELS,
            'kpi' => $kpi,
            'transactions' => $transactions,
            'printMode' => $printMode,
        ]);
    }

    /**
     * Unduh rincian transaksi (sesuai filter) sebagai file CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        [$start, $end, $status] = $this->resolveFilters($request);

        $query = $this->baseQuery($start, $end, $status)
            ->with(['user:id,name', 'room.roomType:id,name'])
            ->orderByDesc('check_in_date')
            ->orderByDesc('id');

        $filename = 'laporan-svara-' . $start->toDateString() . '-sd-' . $end->toDateString() . '.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');

            // BOM UTF-8 agar Excel membaca karakter dengan benar
            fwrite($out, "\xEF\xBB\xBF");

            $this->putCsv($out, [
                'Kode Booking', 'Tamu', 'No. Kamar', 'Tipe Kamar',
                'Check-In', 'Check-Out', 'Malam', 'Total Harga (Rp)', 'Status',
            ]);

            foreach ($query->lazy(500) as $r) {
                $this->putCsv($out, [
                    $this->csvSafe($r->booking_code),
                    $this->csvSafe($r->user?->name ?? '-'),
                    $this->csvSafe($r->room?->room_number ?? '-'),
                    $this->csvSafe($r->room?->roomType?->name ?? '-'),
                    $r->check_in_date->format('Y-m-d'),
                    $r->check_out_date->format('Y-m-d'),
                    $r->total_nights,
                    $r->total_price,
                    self::STATUS_LABELS[$r->status] ?? $r->status,
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Validasi filter & isi nilai bawaan (30 hari terakhir sampai hari ini).
     *
     * @return array{0: Carbon, 1: Carbon, 2: ?string}
     */
    private function resolveFilters(Request $request): array
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['nullable', Rule::in(self::STATUSES)],
        ], [
            'end_date.after_or_equal' => 'Tanggal akhir tidak boleh lebih awal dari tanggal mulai.',
        ], [
            'start_date' => 'tanggal mulai',
            'end_date' => 'tanggal akhir',
            'status' => 'status reservasi',
        ]);

        $end = ! empty($validated['end_date'])
            ? Carbon::parse($validated['end_date'])->startOfDay()
            : today();

        $start = ! empty($validated['start_date'])
            ? Carbon::parse($validated['start_date'])->startOfDay()
            : $end->copy()->subDays(29);

        // Bila hanya tanggal akhir yang diisi dan lebih awal dari bawaan, rapikan agar tetap valid
        if ($start->gt($end)) {
            $start = $end->copy();
        }

        return [$start, $end, $validated['status'] ?? null];
    }

    /**
     * Rentang tanggal memakai tanggal check-in sebagai acuan transaksi.
     */
    private function baseQuery(Carbon $start, Carbon $end, ?string $status = null): Builder
    {
        return Reservation::query()
            ->whereDate('check_in_date', '>=', $start->toDateString())
            ->whereDate('check_in_date', '<=', $end->toDateString())
            ->when($status, fn (Builder $q) => $q->where('status', $status));
    }

    /**
     * Tulis satu baris CSV (argumen escape dikosongkan agar sesuai RFC 4180 di semua versi PHP).
     *
     * @param  resource  $handle
     */
    private function putCsv($handle, array $row): void
    {
        fputcsv($handle, $row, ',', '"', '');
    }

    /**
     * Cegah CSV injection: sel yang diawali = + - @ dianggap rumus oleh Excel.
     */
    private function csvSafe(string $value): string
    {
        return preg_match('/^[=+\-@\t\r]/', $value) ? "'" . $value : $value;
    }
}
