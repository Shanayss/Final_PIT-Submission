<?php

namespace App\Http\Controllers\MedDirector;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class StaffOverviewController extends Controller
{
    /**
     * Display the dynamic staff overview dashboard panel.
     */
    public function index()
    {
        $wardsFromDb = DB::table('wards')->orderBy('ward_number', 'asc')->get();

        $activeOccupancy = DB::table('in_patients')
            ->whereNull('date_actual_leave')
            ->select('ward_number', DB::raw('count(*) as total_occupied'))
            ->groupBy('ward_number')
            ->get()
            ->pluck('total_occupied', 'ward_number');

        $allocationRows = DB::table('staff_allocations')
            ->select('ward_number', 'role_for_week', DB::raw('count(distinct staff_number) as total_staff'))
            ->groupBy('ward_number', 'role_for_week')
            ->get()
            ->groupBy('ward_number');

        $allocatedStaffNumbers = DB::table('staff_allocations')->distinct()->pluck('staff_number');
        $totalRegisteredStaff = DB::table('staff')->count();
        $totalStaffOnDuty = $allocatedStaffNumbers->count();
        $allocationWeekStart = DB::table('staff_allocations')->max('week_start_date');

        $nursesOnShift = DB::table('staff_allocations')
            ->where(function ($query) {
                $query->where('role_for_week', 'like', '%Nurse%');
            })
            ->distinct()
            ->count('staff_number');

        $doctorsOnDuty = DB::table('staff_allocations')
            ->where(function ($query) {
                $query->where('role_for_week', 'like', '%Doctor%')
                    ->orWhere('role_for_week', 'like', '%Consultant%');
            })
            ->distinct()
            ->count('staff_number');

        $supportOnDuty = DB::table('staff_allocations')
            ->where(function ($query) {
                $query->where('role_for_week', 'like', '%Auxiliary%')
                    ->orWhere('role_for_week', 'like', '%Support%');
            })
            ->distinct()
            ->count('staff_number');

        $themes = ['general', 'icu', 'pediatrics', 'surgical', 'neurology'];
        $icons = [
            'general'    => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16" /></svg>',
            'icu'        => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>',
            'pediatrics' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>',
            'surgical'   => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>',
            'neurology'  => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>'
        ];

        $wardStaffRecords = $wardsFromDb->map(function ($ward, $key) use ($activeOccupancy, $allocationRows, $themes, $icons) {
            $totalBeds = (int) $ward->total_beds;
            if ($totalBeds === 0) return null; 

            $occupied = $activeOccupancy->get($ward->ward_number, 0);
            $wardAllocations = $allocationRows->get($ward->ward_number, collect());
            $nurses = $wardAllocations
                ->filter(fn ($row) => str_contains(strtolower($row->role_for_week ?? ''), 'nurse'))
                ->sum('total_staff');
            $doctors = $wardAllocations
                ->filter(fn ($row) => str_contains(strtolower($row->role_for_week ?? ''), 'doctor')
                    || str_contains(strtolower($row->role_for_week ?? ''), 'consultant'))
                ->sum('total_staff');
            $support = $wardAllocations
                ->filter(fn ($row) => str_contains(strtolower($row->role_for_week ?? ''), 'auxiliary')
                    || str_contains(strtolower($row->role_for_week ?? ''), 'support'))
                ->sum('total_staff');
            $totalAllocated = $wardAllocations->sum('total_staff');
            $theme = $themes[$key % count($themes)];

            return [
                'ward_name' => $ward->ward_name,
                'color_theme' => $theme,
                'icon_svg' => $icons[$theme],
                'nurses' => $nurses,
                'doctors' => $doctors,
                'support' => $support,
                'total_allocated' => $totalAllocated,
                'occupied' => $occupied,
                'total_beds' => $totalBeds,
                'alert' => ($occupied >= $totalBeds) 
            ];
        })->filter()->toArray();

        $nursesPercentage = $totalStaffOnDuty > 0 ? round(($nursesOnShift / $totalStaffOnDuty) * 100) : 0;
        $doctorsPercentage = $totalStaffOnDuty > 0 ? round(($doctorsOnDuty / $totalStaffOnDuty) * 100) : 0;
        $supportPercentage = $totalStaffOnDuty > 0 ? round(($supportOnDuty / $totalStaffOnDuty) * 100) : 0;

        return view('Meddirector.staff', compact(
            'totalRegisteredStaff',
            'totalStaffOnDuty',
            'nursesOnShift',
            'doctorsOnDuty',
            'supportOnDuty',
            'nursesPercentage',
            'doctorsPercentage',
            'supportPercentage',
            'allocationWeekStart',
            'wardStaffRecords'
        ));
    }
}
