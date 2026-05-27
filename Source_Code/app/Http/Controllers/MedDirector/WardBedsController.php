<?php

namespace App\Http\Controllers\MedDirector;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class WardBedsController extends Controller
{
    /**
     * Display the continuous layout patient and ward monitoring map dashboard canvas.
     */
    public function index()
    {
        // 1. Fetch all wards from your database sorted sequentially
        $wardsFromDb = DB::table('wards')->orderBy('ward_number', 'asc')->get();

        // 2. Fetch active patient admissions who haven't left yet
        $activePatients = DB::table('in_patients')
            ->whereNull('date_actual_leave')
            ->get()
            ->groupBy('ward_number');
        $bedsByWard = DB::table('beds')
            ->orderBy('bed_number', 'asc')
            ->get()
            ->groupBy('ward_number');

        // 3. Construct the dynamic nested configuration payload from actual beds rows.
        $wardsJsonConfig = $wardsFromDb->mapWithKeys(function ($ward) use ($activePatients, $bedsByWard) {
            $wardAdmissions = $activePatients->get($ward->ward_number, collect());
            $wardBeds = $bedsByWard->get($ward->ward_number, collect())->values();
            $totalBeds = $wardBeds->count() ?: (int) $ward->total_beds;
            
            $bedsList = [];
            for ($i = 0; $i < $totalBeds; $i++) {
                $bedRecord = $wardBeds->get($i);
                $bedNumber = $bedRecord->bed_number ?? ($i + 1);
                $relativeBedIndex = $i + 1;
                $sessionPatient = $wardAdmissions->firstWhere('bed_number', $bedNumber);
                $isOccupied = $sessionPatient || strtolower($bedRecord->status ?? 'Available') === 'occupied';

                if ($isOccupied) {
                    $bedsList[] = [
                        'id' => $bedNumber,
                        'relative_bed_index' => $relativeBedIndex,
                        'status' => 'occupied',
                        'label' => 'Occ',
                        'patient' => $sessionPatient ? 'Patient ' . $sessionPatient->patient_number : null,
                        'admitted' => $sessionPatient ? date('d-M-Y', strtotime($sessionPatient->date_admitted)) : null,
                        'leave' => $sessionPatient && $sessionPatient->date_expected_leave
                            ? date('d-M-Y', strtotime($sessionPatient->date_expected_leave))
                            : null
                    ];
                } else {
                    $bedsList[] = [
                        'id' => $bedNumber,
                        'relative_bed_index' => $relativeBedIndex,
                        'status' => 'vacant',
                        'label' => 'Free',
                        'patient' => null,
                        'admitted' => null,
                        'leave' => null
                    ];
                }
            }

            $result = [
                $ward->ward_number => [
                    'name' => $ward->ward_name,
                    'bedsCount' => $totalBeds,
                    'bedsList' => $bedsList
                ]
            ];

            return $result;
        })->toJson();

        // System telemetry overview analytics counters trackers
        $totalStaff = DB::table('staff')->count();
        $totalBeds = DB::table('beds')->count();
        $occupiedBedsFromBeds = DB::table('beds')->where('status', 'Occupied')->count();
        $activePatientBeds = DB::table('in_patients')
            ->whereNull('date_actual_leave')
            ->whereNotNull('bed_number')
            ->distinct()
            ->count('bed_number');
        $occupiedBeds = max($occupiedBedsFromBeds, $activePatientBeds);
        $availableBeds = max(0, $totalBeds - $occupiedBeds);
        $totalPatients = DB::table('patients')->count(); 

        // Compiling summary matrices layout table rows metrics directly matching live mutations
        $wardOccupancy = $wardsFromDb->map(function ($ward) use ($bedsByWard, $activePatients) {
            $wardBeds = $bedsByWard->get($ward->ward_number, collect());
            $activeWardBeds = $activePatients->get($ward->ward_number, collect())->pluck('bed_number')->filter()->unique();
            $occupied = max(
                $wardBeds->filter(fn ($bed) => strtolower($bed->status ?? '') === 'occupied')->count(),
                $activeWardBeds->count()
            );
            return [
                'ward_name' => $ward->ward_name,
                'ward_number' => $ward->ward_number,
                'occupied' => $occupied,
                'available' => max(0, ($wardBeds->count() ?: (int) $ward->total_beds) - $occupied),
            ];
        })->toArray();

        return view('meddirector.ward_beds', compact(
            'totalPatients',
            'totalStaff',
            'occupiedBeds',
            'availableBeds',
            'wardOccupancy',
            'wardsJsonConfig'
        ));
    }

}
