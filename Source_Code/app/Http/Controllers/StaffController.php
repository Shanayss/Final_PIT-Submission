<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\StaffAllocation;
use App\Models\Qualification;
use App\Models\WorkExperience;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    public function staffManagement()
    {
        $staff = Staff::orderBy('staff_number')->get();

        return view('PersonnelOff.staff-management', compact('staff'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'staff_number' => ['required', 'string', 'max:10', 'unique:staff,staff_number'],
            'role_id' => ['required', 'integer', 'exists:roles,role_id'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:staff,email'],
            'password' => ['nullable', 'string', 'min:6'],
            'telephone' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date'],
            'sex' => ['nullable', Rule::in(['Male', 'Female'])],
            'nin' => ['nullable', 'string', 'max:255', 'unique:staff,nin'],
            'address' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'current_salary' => ['nullable', 'numeric', 'min:0'],
            'salary_scale' => ['nullable', 'string', 'max:255'],
            'hours_per_week' => ['nullable', 'integer', 'min:0'],
            'contract_type' => ['nullable', Rule::in(['Permanent', 'Temporary'])],
            'payment_type' => ['nullable', Rule::in(['Monthly', 'Weekly'])],
        ], [
            'staff_number.unique' => 'Staff Number already exists. Please use another staff number.',
            'email.unique' => 'Email already exists. Please use another email.',
            'nin.unique' => 'NIN already exists. Please use another NIN.',
        ]);

        $validated['password'] = Hash::make($validated['password'] ?? 'Password123');

        Staff::create($validated);

        return redirect()
            ->route('personnel.staff-management')
            ->with('success', 'Staff added successfully.');
    }

    public function update(Request $request, $staff_number)
    {
        $staff = Staff::where('staff_number', $staff_number)->firstOrFail();

        $validated = $request->validate([
            'role_id' => ['required', 'integer', 'exists:roles,role_id'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('staff', 'email')->ignore($staff_number, 'staff_number')],
            'password' => ['nullable', 'string', 'min:6'],
            'telephone' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date'],
            'sex' => ['nullable', Rule::in(['Male', 'Female'])],
            'nin' => ['nullable', 'string', 'max:255', Rule::unique('staff', 'nin')->ignore($staff_number, 'staff_number')],
            'address' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'current_salary' => ['nullable', 'numeric', 'min:0'],
            'salary_scale' => ['nullable', 'string', 'max:255'],
            'hours_per_week' => ['nullable', 'integer', 'min:0'],
            'contract_type' => ['nullable', Rule::in(['Permanent', 'Temporary'])],
            'payment_type' => ['nullable', Rule::in(['Monthly', 'Weekly'])],
        ], [
            'email.unique' => 'Email already exists. Please use another email.',
            'nin.unique' => 'NIN already exists. Please use another NIN.',
        ]);

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $staff->update($validated);

        return redirect()
            ->route('personnel.staff-management')
            ->with('success', 'Staff updated successfully.');
    }

    public function destroy($staff_number)
    {
        $staff = Staff::where('staff_number', $staff_number)->firstOrFail();

        $staff->delete();

        return redirect()
            ->back()
            ->with('success', 'Staff deleted successfully.');
    }

    public function personnelDashboard()
    {
        $totalStaff = Staff::count();

        $assignedToWards = DB::table('staff_allocations')
            ->whereNotNull('ward_number')
            ->distinct('staff_number')
            ->count('staff_number');

        $assignedToClinics = 0;

        $qualificationsRecorded = Qualification::count();

        $workExperiences = WorkExperience::count();

        $activeContracts = Staff::whereNotNull('contract_type')
            ->where('contract_type', '!=', '')
            ->count();

        $recentStaff = Staff::orderBy('staff_number', 'desc')
            ->take(5)
            ->get();

        return view('PersonnelOff.dashboard', compact(
            'totalStaff',
            'assignedToWards',
            'assignedToClinics',
            'qualificationsRecorded',
            'workExperiences',
            'activeContracts',
            'recentStaff'
        ));
    }

    public function qualifications()
    {
        $qualifications = DB::table('qualifications')
            ->join('staff', 'qualifications.staff_number', '=', 'staff.staff_number')
            ->select(
                'qualifications.*',
                'staff.first_name',
                'staff.last_name',
                'staff.position'
            )
            ->orderBy('staff.last_name')
            ->get();

        $staffs = DB::table('staff')
            ->select('staff_number', 'first_name', 'last_name')
            ->orderBy('last_name')
            ->get();

        return view('PersonnelOff.qualifications', compact(
            'qualifications',
            'staffs'
        ));
    }

    public function storeQualification(Request $request)
{
    DB::statement("
        CALL add_or_update_qualification(?, ?, ?, ?)
    ", [
        $request->staff_number,
        $request->qualification_type,
        $request->institution_name,
        $request->qualification_date,
    ]);

    return redirect()->back()->with('success', 'Qualification saved successfully.');
}

public function updateQualification(Request $request, $qualification_id)
{
    $request->validate([
        'staff_number' => ['required', 'exists:staff,staff_number'],
        'qualification_type' => ['required', 'string', 'max:100'],
        'qualification_date' => ['required', 'date'],
        'institution_name' => ['required', 'string', 'max:100'],
    ]);

    DB::table('qualifications')
        ->where('qualification_id', $qualification_id)
        ->update([
            'staff_number' => $request->staff_number,
            'qualification_type' => $request->qualification_type,
            'qualification_date' => $request->qualification_date,
            'institution_name' => $request->institution_name,
        ]);

    return redirect()->back()->with('success', 'Qualification updated successfully.');
}

public function destroyQualification($qualification_id)
{
    DB::table('qualifications')
        ->where('qualification_id', $qualification_id)
        ->delete();

    return redirect()->back()->with('success', 'Qualification deleted successfully.');
}

    private function qualificationRow($qualification_id)
    {
        return DB::table('qualifications')
            ->join('staff', 'qualifications.staff_number', '=', 'staff.staff_number')
            ->select(
                'qualifications.*',
                'staff.first_name',
                'staff.last_name',
                'staff.position'
            )
            ->where('qualifications.qualification_id', $qualification_id)
            ->first();
    }

    public function workExperiences()
    {
        $workExperiences = DB::table('work_experiences')
            ->join('staff', 'work_experiences.staff_number', '=', 'staff.staff_number')
            ->select(
                'work_experiences.*',
                'staff.first_name',
                'staff.last_name',
                'staff.position'
            )
            ->orderBy('staff.last_name')
            ->get();

        $staffs = DB::table('staff')
            ->select('staff_number', 'first_name', 'last_name')
            ->orderBy('last_name')
            ->get();

        return view('PersonnelOff.work-experiences', compact(
            'workExperiences',
            'staffs'
        ));
    }

    public function storeWorkExperience(Request $request)
    {
        DB::statement("
            CALL add_or_update_work_experience(?, ?, ?, ?, ?)
        ", [
            $request->staff_number,
            $request->name_of_organization,
            $request->position_held,
            $request->start_date,
            $request->finish_date,
        ]);

        return redirect()->back()->with('success', 'Work experience saved successfully.');
    }

    public function updateWorkExperience(Request $request, $experience_id)
    {
        $validated = $request->validate([
            'staff_number' => ['required', 'exists:staff,staff_number'],
            'name_of_organization' => ['required', 'string', 'max:100'],
            'position_held' => ['required', 'string', 'max:100'],
            'start_date' => ['required', 'date'],
            'finish_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        WorkExperience::findOrFail($experience_id)->update($validated);

        return redirect()->back()->with('success', 'Work experience updated successfully.');
    }

    public function destroyWorkExperience($experience_id)
    {
        WorkExperience::findOrFail($experience_id)->delete();

        return redirect()->back()->with('success', 'Work experience deleted successfully.');
    }

    public function staffAllocations()
    {
        $staffAllocations = DB::table('staff_allocations')
            ->join('staff', 'staff_allocations.staff_number', '=', 'staff.staff_number')
            ->leftJoin('wards', 'staff_allocations.ward_number', '=', 'wards.ward_number')
            ->select(
                'staff_allocations.*',
                'staff.first_name',
                'staff.last_name',
                'staff.position',
                'wards.ward_name'
            )
            ->orderBy('staff_allocations.ward_number')
            ->orderBy('staff_allocations.shift')
            ->get();

        $staffs = DB::table('staff')
            ->select('staff_number', 'first_name', 'last_name')
            ->orderBy('last_name')
            ->get();

        $wards = DB::table('wards')
            ->select('ward_number', 'ward_name')
            ->orderBy('ward_number')
            ->get();

        return view('PersonnelOff.staffallocations', compact(
            'staffAllocations',
            'staffs',
            'wards'
        ));
    }

   public function storeStaffAllocation(Request $request)
    {
        $request->validate([
            'staff_number' => ['required', 'exists:staff,staff_number'],
            'ward_number' => ['required', 'integer', 'exists:wards,ward_number'],
            'role_for_week' => ['required', 'string', 'max:50'],
            'shift' => ['required', Rule::in(['Early', 'Late', 'Night'])],
            'week_start_date' => ['required', 'date'],
        ]);

        DB::statement("
            CALL add_or_update_staff_allocation(?::varchar, ?::integer, ?::varchar, ?::varchar, ?::date)
        ", [
            $request->staff_number,
            $request->ward_number,
            $request->role_for_week,
            $request->shift,
            $request->week_start_date,
        ]);

        return redirect()->back()->with(
            'success',
            'Staff allocation saved successfully.'
        );
    }

    public function updateStaffAllocation(Request $request, $allocation_id)
    {
        $validated = $request->validate([
            'staff_number' => [
                'required',
                'exists:staff,staff_number',
                Rule::unique('staff_allocations', 'staff_number')->ignore($allocation_id, 'allocation_id'),
            ],
            'ward_number' => ['required', 'integer', 'exists:wards,ward_number'],
            'role_for_week' => ['required', 'string', 'max:50'],
            'shift' => ['required', Rule::in(['Early', 'Late', 'Night'])],
            'week_start_date' => ['required', 'date'],
        ]);

        StaffAllocation::findOrFail($allocation_id)->update($validated);

        return redirect()->back()->with('success', 'Staff allocation updated successfully.');
    }

    public function destroyStaffAllocation($allocation_id)
    {
        StaffAllocation::findOrFail($allocation_id)->delete();

        return redirect()->back()->with('success', 'Staff allocation deleted successfully.');
    }

    public function reports()
    {
        return view('PersonnelOff.reports');
    }

    public function reportStaffPerWard()
    {
        $data = DB::table('staff_allocations')
            ->join('wards', 'staff_allocations.ward_number', '=', 'wards.ward_number')
            ->select(
                'wards.ward_name',
                DB::raw('COUNT(staff_allocations.staff_number) as total_staff')
            )
            ->groupBy('wards.ward_name')
            ->orderBy('wards.ward_name')
            ->get();

        return view('PersonnelOff.reports-view', [
            'title' => 'Staff Per Ward Report',
            'headers' => ['Ward', 'Total Staff'],
            'rows' => $data
        ]);
    }

    public function reportQualificationSummary()
    {
        $data = DB::table('qualifications')
            ->select(
                'qualification_type',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('qualification_type')
            ->orderBy('qualification_type')
            ->get();

        return view('PersonnelOff.reports-view', [
            'title' => 'Qualification Summary Report',
            'headers' => ['Qualification', 'Total'],
            'rows' => $data
        ]);
    }

    public function reportWorkExperienceSummary()
    {
        $data = DB::table('work_experiences')
            ->select(
                'name_of_organization',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('name_of_organization')
            ->orderBy('name_of_organization')
            ->get();

        return view('PersonnelOff.reports-view', [
            'title' => 'Work Experience Summary',
            'headers' => ['Organization', 'Total Staff'],
            'rows' => $data
        ]);
    }

    public function reportAllocationSummary()
    {
        $data = DB::table('staff_allocations')
            ->select(
                'shift',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('shift')
            ->orderBy('shift')
            ->get();

        return view('PersonnelOff.reports-view', [
            'title' => 'Staff Allocation Summary',
            'headers' => ['Shift', 'Total Staff'],
            'rows' => $data
        ]);
    }

    public function reportContractSummary()
    {
        $data = DB::table('staff')
            ->select(
                'contract_type',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('contract_type')
            ->orderBy('contract_type')
            ->get();

        return view('PersonnelOff.reports-view', [
            'title' => 'Contract Summary Report',
            'headers' => ['Contract Type', 'Total Staff'],
            'rows' => $data
        ]);
    }
}
