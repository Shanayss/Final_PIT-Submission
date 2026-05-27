<?php

namespace App\Http\Controllers\MedDirector;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalPatients = DB::table('patients')->count();
        $totalStaff = DB::table('staff')->count();
        $totalBeds = DB::table('beds')->count();

        $occupiedBedsFromStatus = DB::table('beds')
            ->where('status', 'Occupied')
            ->count();
        $activePatientBeds = DB::table('in_patients')
            ->whereNull('date_actual_leave')
            ->whereNotNull('bed_number')
            ->distinct()
            ->count('bed_number');

        $occupiedBeds = max($occupiedBedsFromStatus, $activePatientBeds);
        $availableBeds = max(0, $totalBeds - $occupiedBeds);

        $wards = DB::table('wards')
            ->select('ward_number', 'ward_name', 'total_beds')
            ->orderBy('ward_number', 'asc')
            ->get();
        $bedsByWard = DB::table('beds')->get()->groupBy('ward_number');
        $activePatientsByWard = DB::table('in_patients')
            ->whereNull('date_actual_leave')
            ->whereNotNull('bed_number')
            ->get()
            ->groupBy('ward_number');

        $wardOccupancy = $wards->map(function ($ward) use ($bedsByWard, $activePatientsByWard) {
            $wardBeds = $bedsByWard->get($ward->ward_number, collect());
            $activeWardBeds = $activePatientsByWard
                ->get($ward->ward_number, collect())
                ->pluck('bed_number')
                ->filter()
                ->unique();
            $occupied = max(
                $wardBeds->filter(fn ($bed) => strtolower($bed->status ?? '') === 'occupied')->count(),
                $activeWardBeds->count()
            );
            $totalWardBeds = $wardBeds->count() ?: (int) $ward->total_beds;

            return [
                'ward_number' => $ward->ward_number,
                'ward_name' => $ward->ward_name,
                'occupied' => $occupied,
                'available' => max(0, $totalWardBeds - $occupied),
            ];
        });

        return view('Meddirector.dashboard', compact(
            'totalPatients',
            'totalStaff',
            'occupiedBeds',
            'availableBeds',
            'wardOccupancy'
        ));
    }

}
