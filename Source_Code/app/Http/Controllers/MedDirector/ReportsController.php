<?php

namespace App\Http\Controllers\MedDirector;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function patients()
    {
        $wardsFromDb = DB::table('wards')->orderBy('ward_number', 'asc')->get();
        $activePatients     = DB::table('in_patients')->whereNull('date_actual_leave')->get()->groupBy('ward_number');
        $dischargesSummary  = DB::table('in_patients')->whereNotNull('date_actual_leave')->get()->groupBy('ward_number');
        $admissionsSummary  = DB::table('in_patients')->get()->groupBy('ward_number');
        $bedsByWard         = DB::table('beds')->get()->groupBy('ward_number');

        $themesList = ['general', 'icu', 'pediatrics', 'surgical', 'neurology', 'maternity'];
        $iconsMap = [
            'general'    => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16" /></svg>',
            'icu'        => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>',
            'pediatrics' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>',
            'surgical'   => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 002-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>',
            'neurology'  => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>',
            'maternity'  => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>',
        ];

        $wardReportsData = $wardsFromDb->map(function ($ward, $key) use (
            $activePatients,
            $dischargesSummary,
            $admissionsSummary,
            $bedsByWard,
            $themesList,
            $iconsMap
        ) {
            $wardBeds   = $bedsByWard->get($ward->ward_number, collect());
            $totalBeds  = $wardBeds->count() ?: (int) $ward->total_beds;

            if ($totalBeds === 0) {
                return null;
            }

            $activeWardBedCount = $activePatients
                ->get($ward->ward_number, collect())
                ->pluck('bed_number')
                ->filter()
                ->unique()
                ->count();

            $occupiedByBedStatus = $wardBeds
                ->filter(fn($bed) => strtolower($bed->status ?? '') === 'occupied')
                ->count();

            $occupiedCount  = max($activeWardBedCount, $occupiedByBedStatus);
            $dischargesCount = $dischargesSummary->get($ward->ward_number, collect())->count();
            $admissionsCount = $admissionsSummary->get($ward->ward_number, collect())->count();
            $chosenTheme    = $themesList[$key % count($themesList)];

            return [
                'ward_name'  => $ward->ward_name,
                'theme'      => $chosenTheme,
                'icon_svg'   => $iconsMap[$chosenTheme],
                'total_beds' => $totalBeds,
                'occupied'   => $occupiedCount,
                'available'  => max(0, $totalBeds - $occupiedCount),
                'discharges' => $dischargesCount,
                'admissions' => $admissionsCount,
            ];
        })->filter()->toArray();

        $reportSummary = [
            'total_wards' => count($wardReportsData),
            'total_beds'  => collect($wardReportsData)->sum('total_beds'),
            'occupied'    => collect($wardReportsData)->sum('occupied'),
            'available'   => collect($wardReportsData)->sum('available'),
            'admissions'  => collect($wardReportsData)->sum('admissions'),
            'discharges'  => collect($wardReportsData)->sum('discharges'),
        ];

        return view('Meddirector.reports.patients', compact('wardReportsData', 'reportSummary'));
    }

    public function outpatient()
    {
        $clinicsFromDb = DB::table('local_doctors')->orderBy('clinic_number', 'asc')->get();
        $appointments  = DB::table('appointments')
            ->leftJoin('patients', 'appointments.patient_number', '=', 'patients.patient_number')
            ->select('appointments.*', 'patients.first_name', 'patients.last_name')
            ->get();

        $themesList = ['cardio', 'neuro', 'ortho'];
        $iconsMap = [
            'cardio' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>',
            'neuro'  => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>',
            'ortho'  => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>',
        ];

        $clinicReportsData = $clinicsFromDb->map(function ($clinic, $key) use ($appointments, $themesList, $iconsMap) {
            $clinicAppointments = $appointments->where('clinic_number', $clinic->clinic_number);

            $scheduledAppointments = $clinicAppointments->filter(function ($app) {
                $status = strtolower($app->status ?? '');
                return in_array($status, ['pending', 'scheduled']);
            });

            $completedAppointments = $clinicAppointments->filter(
                fn($app) => strtolower($app->status ?? '') === 'completed'
            );

            $chosenTheme = $themesList[$key % count($themesList)];

            $completedVisits = $completedAppointments->map(function ($appointment) {
                return [
                    'initials'     => strtoupper(
                        substr($appointment->first_name ?? '', 0, 1) .
                        substr($appointment->last_name  ?? '', 0, 1)
                    ),
                    'patient_name' => trim(($appointment->first_name ?? 'Unknown') . ' ' . ($appointment->last_name ?? 'Patient')),
                    'status'       => $appointment->status,
                    'room'         => $appointment->examination_room ?? 'Unassigned',
                    'target_date'  => date('j M Y', strtotime($appointment->appointment_date)),
                ];
            })->values()->toArray();

            $scheduledVisits = $scheduledAppointments->map(function ($appointment) {
                return [
                    'initials'     => strtoupper(
                        substr($appointment->first_name ?? '', 0, 1) .
                        substr($appointment->last_name  ?? '', 0, 1)
                    ),
                    'patient_name' => trim(($appointment->first_name ?? 'Unknown') . ' ' . ($appointment->last_name ?? 'Patient')),
                    'status'       => $appointment->status,
                    'room'         => $appointment->examination_room ?? 'Unassigned',
                    'target_date'  => date('j M Y', strtotime($appointment->appointment_date)),
                ];
            })->values()->toArray();

            $followups = $scheduledAppointments->map(function ($appointment) {
                return [
                    'initials'     => strtoupper(
                        substr($appointment->first_name ?? '', 0, 1) .
                        substr($appointment->last_name  ?? '', 0, 1)
                    ),
                    'patient_name' => trim(($appointment->first_name ?? 'Unknown') . ' ' . ($appointment->last_name ?? 'Patient')),
                    'status'       => $appointment->status,
                    'reason'       => 'Routine Check-up',
                    'target_date'  => date('j M Y', strtotime($appointment->appointment_date)),
                ];
            })->values()->toArray();

            $referrals = [];
            if (!empty($completedVisits)) {
                $randomCompletedVisit = $completedVisits[array_rand($completedVisits)];
                $referrals[] = [
                    'initials'     => $randomCompletedVisit['initials'],
                    'patient_name' => $randomCompletedVisit['patient_name'],
                    'priority'     => 'Medium',
                    'destination'  => 'General Surgery',
                ];
            }

            return [
                'clinic_name'      => 'Clinic ' . $clinic->clinic_number,
                'doctor_name'      => $clinic->full_name ?? trim(($clinic->first_name ?? '') . ' ' . ($clinic->last_name ?? '')),
                'room_number'      => $clinicAppointments->pluck('examination_room')->filter()->first() ?? 'Unassigned',
                'hours'            => '09:00–16:00',
                'theme'            => $chosenTheme,
                'icon_svg'         => $iconsMap[$chosenTheme],
                'seen'             => $completedAppointments->count(),
                'pending'          => $scheduledAppointments->count(),
                'dna'              => $clinicAppointments->filter(fn($app) => strtolower($app->status ?? '') === 'no show')->count(),
                'new'              => $clinicAppointments->unique('patient_number')->count(),
                'completed_count'  => count($completedVisits),
                'scheduled_count'  => count($scheduledVisits),
                'referrals_count'  => count($referrals),
                'followups_count'  => count($followups),
                'completed_visits' => $completedVisits,
                'scheduled_visits' => $scheduledVisits,
                'referrals'        => $referrals,
                'followups'        => $followups,
            ];
        })->toArray();

        $outpatientSummary = [
            'clinics'      => $clinicsFromDb->count(),
            'appointments' => $appointments->count(),
            'completed'    => $appointments->filter(fn($app) => strtolower($app->status ?? '') === 'completed')->count(),
            'scheduled'    => $appointments->filter(fn($app) => in_array(strtolower($app->status ?? ''), ['pending', 'scheduled']))->count(),
            'dna'          => $appointments->filter(fn($app) => strtolower($app->status ?? '') === 'no show')->count(),
            'patients'     => $appointments->unique('patient_number')->count(),
        ];

        return view('Meddirector.reports.outpatient', compact('clinicReportsData', 'outpatientSummary'));
    }

    public function supply()
    {
        $wardsFromDb = DB::table('wards')
            ->where('total_beds', '>', 0)
            ->orderBy('ward_number', 'asc')
            ->get();

        $themesList = ['general', 'icu', 'surgical', 'pediatrics', 'neurology', 'maternity'];
        $iconsMap = [
            'general'    => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>',
            'icu'        => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>',
            'pediatrics' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>',
            'surgical'   => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 002-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>',
            'neurology'  => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>',
            'maternity'  => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>',
        ];

        $supplyUsageData = $wardsFromDb->map(function ($ward, $key) use ($themesList, $iconsMap) {
            $chosenTheme     = $themesList[$key % count($themesList)];
            $isSecondRowItem = in_array($chosenTheme, ['icu', 'neurology']);

            $occupantsCount = DB::table('in_patients')
                ->where('ward_number', $ward->ward_number)
                ->whereNull('date_actual_leave')
                ->count();

            return [
                'ward_name'   => $ward->ward_name,
                'theme'       => $chosenTheme,
                'icon_svg'    => $iconsMap[$chosenTheme],
                'gloves'      => $occupantsCount * 14,
                'label_two'   => $isSecondRowItem ? 'Syringes' : 'Dressings',
                'count_two'   => $occupantsCount * 8,
                'iv_bags'     => $occupantsCount * 4,
                'medications' => $occupantsCount * 22,
            ];
        })->toArray();

        $lowStockAlertsCollection = [];

        $dbItems = DB::table('items')
            ->whereColumn('quantity_of_stock', '<=', 'reorder_level')
            ->orderBy('quantity_of_stock', 'asc')
            ->get();

        foreach ($dbItems as $item) {
            $lowStockAlertsCollection[] = $this->stockAlertRow(
                $item->item_name,
                'Medical Supplies',
                (int) $item->quantity_of_stock
            );
        }

        $dbDrugs = DB::table('drugs')
            ->whereColumn('quantity_of_stock', '<=', 'reorder_level')
            ->orderBy('quantity_of_stock', 'asc')
            ->get();

        foreach ($dbDrugs as $drug) {
            $lowStockAlertsCollection[] = $this->stockAlertRow(
                $drug->drug_name,
                'Drug Inventory',
                (int) $drug->quantity_of_stock
            );
        }

        return view('Meddirector.reports.supply', compact('supplyUsageData', 'lowStockAlertsCollection'));
    }

    private function stockAlertRow(string $name, string $category, int $quantity): array
    {
        $isOut = $quantity === 0;

        return [
            'item_name'        => $name,
            'category_label'   => $category,
            'last_restock_date' => 'Not recorded',
            'qty_left'         => $quantity,
            'unit_label'       => 'units',
            'ward_allocations' => 'All Wards',
            'status_class'     => $isOut ? 'outofstock' : 'lowstock',
            'status_label'     => $isOut ? 'Out of stock' : 'Low stock',
        ];
    }
}
