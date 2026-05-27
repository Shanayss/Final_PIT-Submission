<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\ClinicalStaff\AppointmentController as ClinicalAppointmentController;
use App\Http\Controllers\ClinicalStaff\ClinicalTreatmentController;
use App\Models\Staff;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\StaffLoginController;
use App\Http\Controllers\NursingStaff\NursingStaffController;
use App\Http\Controllers\PatientRegistrationController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    return view('welcome');
});


// WEB LOGIN ROUTE
Route::post('/staff-login', [StaffLoginController::class, 'webLogin'])
    ->name('staff.login');


// LOGOUT ROUTE
Route::post('/logout', function (Request $request) {
    session()->flush();
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/');
})->name('logout');


// ================= MEDICAL DIRECTOR ROUTES =================

Route::get('/MedDirector/dashboard', [\App\Http\Controllers\MedDirector\DashboardController::class, 'index'])
    ->name('meddirector.dashboard');

Route::get('/MedDirector/staff', [\App\Http\Controllers\MedDirector\StaffOverviewController::class, 'index'])
    ->name('meddirector.staff');

Route::get('/MedDirector/wards', [\App\Http\Controllers\MedDirector\WardBedsController::class, 'index'])
    ->name('meddirector.wards');

Route::get('/MedDirector/reports/patients', [\App\Http\Controllers\MedDirector\ReportsController::class, 'patients'])
    ->name('meddirector.reports.patients');

Route::get('/MedDirector/reports/outpatient', [\App\Http\Controllers\MedDirector\ReportsController::class, 'outpatient'])
    ->name('meddirector.reports.outpatient');

Route::get('/MedDirector/reports/supply', [\App\Http\Controllers\MedDirector\ReportsController::class, 'supply'])
    ->name('meddirector.reports.supply');

Route::resource('/MedDirector/suppliers', \App\Http\Controllers\MedDirector\SupplierController::class)->names([
    'index' => 'meddirector.suppliers.index',
    'store' => 'meddirector.suppliers.store',
    'update' => 'meddirector.suppliers.update',
    'destroy' => 'meddirector.suppliers.destroy',
])->only(['index', 'store', 'update', 'destroy']);




// ================= PERSONNEL OFFICER ROUTES =================

// dashboard
Route::get('/PersonnelOff/dashboard', [StaffController::class, 'personnelDashboard'])
    ->name('personnel.dashboard');

// staff management
Route::get('/PersonnelOff/staff-management', [StaffController::class, 'staffManagement'])
    ->name('personnel.staff-management');

Route::post('/PersonnelOff/staff', [StaffController::class, 'store'])
    ->name('staff.store');

Route::put('/PersonnelOff/staff/{staff_number}', [StaffController::class, 'update'])
    ->name('staff.update');

Route::delete('/PersonnelOff/staff/{staff_number}', [StaffController::class, 'destroy'])
    ->name('staff.destroy');

// qualifications
Route::get('/PersonnelOff/qualifications', [StaffController::class, 'qualifications'])
    ->name('personnel.qualifications');

Route::post('/PersonnelOff/qualifications', [StaffController::class, 'storeQualification'])
    ->name('qualifications.store');

Route::put('/PersonnelOff/qualifications/{qualification_id}', [StaffController::class, 'updateQualification'])
    ->name('qualifications.update');

Route::delete('/PersonnelOff/qualifications/{qualification_id}', [StaffController::class, 'destroyQualification'])
    ->name('qualifications.destroy');

// work experience
Route::get('/PersonnelOff/work-experiences', [StaffController::class, 'workExperiences'])
    ->name('personnel.work-experiences');

Route::post('/PersonnelOff/work-experiences', [StaffController::class, 'storeWorkExperience'])
    ->name('work-experiences.store');

Route::put('/PersonnelOff/work-experiences/{experience_id}', [StaffController::class, 'updateWorkExperience'])
    ->name('work-experiences.update');

Route::delete('/PersonnelOff/work-experiences/{experience_id}', [StaffController::class, 'destroyWorkExperience'])
    ->name('work-experiences.destroy');

// staff allocations
Route::get('/PersonnelOff/staffallocations', [StaffController::class, 'staffAllocations'])
    ->name('personnel.staffallocations');

Route::post('/PersonnelOff/staffallocations', [StaffController::class, 'storeStaffAllocation'])
    ->name('staffallocations.store');

Route::put('/PersonnelOff/staffallocations/{allocation_id}', [StaffController::class, 'updateStaffAllocation'])
    ->name('staffallocations.update');

Route::delete('/PersonnelOff/staffallocations/{allocation_id}', [StaffController::class, 'destroyStaffAllocation'])
    ->name('staffallocations.destroy');

// reports
Route::get('/PersonnelOff/reports', [StaffController::class, 'reports'])
    ->name('personnel.reports');

Route::get('/PersonnelOff/reports/staff-per-ward', [StaffController::class, 'reportStaffPerWard'])
    ->name('reports.staffPerWard');

Route::get('/PersonnelOff/reports/qualification-summary', [StaffController::class, 'reportQualificationSummary'])
    ->name('reports.qualificationSummary');

Route::get('/PersonnelOff/reports/work-experience-summary', [StaffController::class, 'reportWorkExperienceSummary'])
    ->name('reports.workExperienceSummary');

Route::get('/PersonnelOff/reports/allocation-summary', [StaffController::class, 'reportAllocationSummary'])
    ->name('reports.allocationSummary');

Route::get('/PersonnelOff/reports/contract-summary', [StaffController::class, 'reportContractSummary'])
    ->name('reports.contractSummary');

// ================= CLINICAL STAFF ROUTES =================

Route::get('/ClinicalStaff/dashboard', function () {
    return view('ClinicalStaff.dashboard');
})->name('clinical.dashboard');

// ================= CLINICAL STAFF APPOINTMENTS =================

// Shows appointment dashboard/list
Route::get('/ClinicalStaff/appointments', [ClinicalAppointmentController::class, 'index'])
    ->name('clinical.appointments');

// Opens Add New Appointment form
Route::get('/ClinicalStaff/appointments/create', [ClinicalAppointmentController::class, 'create'])
    ->name('clinical.appointments.create');

// Saves new appointment
Route::post('/ClinicalStaff/appointments', [ClinicalAppointmentController::class, 'store'])
    ->name('clinical.appointments.store');

// Opens edit appointment form
Route::get('/ClinicalStaff/appointments/{appointment_id}/edit', [ClinicalAppointmentController::class, 'edit'])
    ->name('clinical.appointments.edit');

// Updates appointment record
Route::put('/ClinicalStaff/appointments/{appointment_id}', [ClinicalAppointmentController::class, 'update'])
    ->name('clinical.appointments.update');

// Deletes appointment record
Route::delete('/ClinicalStaff/appointments/{appointment_id}', [ClinicalAppointmentController::class, 'destroy'])
    ->name('clinical.appointments.destroy');


// ================= CLINICAL STAFF DIAGNOSES =================

Route::get('/ClinicalStaff/diagnoses', function () {
    return view('ClinicalStaff.diagnoses');
})->name('clinical.diagnoses');


// ================= CLINICAL STAFF TREATMENTS =================

Route::get('/ClinicalStaff/appointments/{appointment_id}/treatment', [ClinicalTreatmentController::class, 'create'])
    ->name('clinical.treatments.create');

// Treatment dashboard/list
Route::get('/ClinicalStaff/treatments', [ClinicalTreatmentController::class, 'index'])
    ->name('clinical.treatments');

// Open treatment form from selected appointment
Route::get('/ClinicalStaff/appointments/{appointment_id}/treatment', [ClinicalTreatmentController::class, 'create'])
    ->name('clinical.treatments.create');

// Save treatment form
Route::post('/ClinicalStaff/appointments/{appointment_id}/treatment', [ClinicalTreatmentController::class, 'store'])
    ->name('clinical.treatments.store');

//delete route for delete button in the treatment dashboard for deleting record
Route::delete('/ClinicalStaff/treatments/{treatment_id}', [ClinicalTreatmentController::class, 'destroy'])
    ->name('clinical.treatments.destroy');

//for the view button in the treatment dashboard
Route::get('/ClinicalStaff/treatments/{treatment_id}', [ClinicalTreatmentController::class, 'show'])
    ->name('clinical.treatments.show');
    

// ================= NURSING STAFF ROUTES =================

Route::get('/NursingStaff/dashboard', [NursingStaffController::class, 'dashboard'])
    ->name('nurse.dashboard');

Route::get('/NursingStaff/patients', [NursingStaffController::class, 'patients'])
    ->name('nurse.patients');

Route::get('/NursingStaff/register-patient', [NursingStaffController::class, 'registerPatients'])
    ->name('nurse.register-patient');

Route::post('/NursingStaff/register-patient', [NursingStaffController::class, 'storeRegisteredPatient'])
    ->name('nurse.register-patient.store');

Route::put('/NursingStaff/register-patient/{patientNumber}', [NursingStaffController::class, 'updateRegisteredPatient'])
    ->name('nurse.register-patient.update');

Route::delete('/NursingStaff/register-patient/{patientNumber}', [NursingStaffController::class, 'destroyRegisteredPatient'])
    ->name('nurse.register-patient.destroy');

Route::get('/NursingStaff/wards', [NursingStaffController::class, 'wardBeds'])
    ->name('nurse.wards');

Route::get('/NursingStaff/assign-beds', [NursingStaffController::class, 'assignBeds'])
    ->name('nurse.assign-beds');

Route::post('/NursingStaff/assign-beds', [NursingStaffController::class, 'storeBedAssignment'])
    ->name('nurse.assign-beds.store');

Route::get('/NursingStaff/admit-patients', [NursingStaffController::class, 'admitPatients'])
    ->name('nurse.admit-patients');

Route::post('/NursingStaff/admit-patients', [NursingStaffController::class, 'storeAdmission'])
    ->name('nurse.admit-patients.store');

Route::get('/NursingStaff/discharge-patients', [NursingStaffController::class, 'dischargePatients'])
    ->name('nurse.discharge-patients');

Route::post('/NursingStaff/discharge-patients', [NursingStaffController::class, 'storeDischarge'])
    ->name('nurse.discharge-patients.store');

Route::get('/NursingStaff/ward-occupancy', [NursingStaffController::class, 'wardOccupancy'])
    ->name('nurse.ward-occupancy');

Route::get('/NursingStaff/medication/record', [NursingStaffController::class, 'recordMedication'])
    ->name('nurse.medication.record');

Route::post('/NursingStaff/medication/record', [NursingStaffController::class, 'storeMedicationRecord'])
    ->name('nurse.medication.record.store');

Route::get('/NursingStaff/medication/prescriptions', [NursingStaffController::class, 'viewPrescriptions'])
    ->name('nurse.medication.prescriptions');

Route::get('/NursingStaff/medication/schedules', [NursingStaffController::class, 'medicationSchedules'])
    ->name('nurse.medication.schedules');

Route::get('/NursingStaff/patient-care', [NursingStaffController::class, 'patients'])
    ->name('nurse.patient-care');

Route::get('/NursingStaff/patient-care/update-condition', [NursingStaffController::class, 'updateCondition'])
    ->name('nurse.patient-care.update-condition');

Route::post('/NursingStaff/patient-care/update-condition', [NursingStaffController::class, 'storeCondition'])
    ->name('nurse.patient-care.update-condition.store');

Route::get('/NursingStaff/patient-care/assigned', [NursingStaffController::class, 'assignedPatients'])
    ->name('nurse.patient-care.assigned');

Route::get('/NursingStaff/patient-care/assigned/{patientNumber}', [NursingStaffController::class, 'patientDetails'])
    ->name('nurse.patient-care.assigned.show');

Route::get('/NursingStaff/patient-care/care-notes', [NursingStaffController::class, 'careNotes'])
    ->name('nurse.patient-care.care-notes');

Route::post('/NursingStaff/patient-care/care-notes', [NursingStaffController::class, 'storeCareNote'])
    ->name('nurse.patient-care.care-notes.store');

Route::get('/NursingStaff/supplies', fn () => redirect()->route('nurse.supplies.request'))
    ->name('nurse.supplies');

Route::get('/NursingStaff/supplies/create-requisition', [NursingStaffController::class, 'createRequisition'])
    ->name('nurse.supplies.create');

Route::post('/NursingStaff/supplies/create-requisition', [NursingStaffController::class, 'storeRequisition'])
    ->name('nurse.supplies.store');

Route::get('/NursingStaff/supplies/request', [NursingStaffController::class, 'requestSupplies'])
    ->name('nurse.supplies.request');

Route::get('/NursingStaff/supplies/confirm-deliveries', [NursingStaffController::class, 'confirmDeliveries'])
    ->name('nurse.supplies.confirm');

Route::post('/NursingStaff/supplies/confirm-deliveries', [NursingStaffController::class, 'storeDeliveryConfirmation'])
    ->name('nurse.supplies.confirm.store');

Route::get('/NursingStaff/reports', [NursingStaffController::class, 'reports'])
    ->name('nurse.reports');

Route::get('/NursingStaff/scheduling', [NursingStaffController::class, 'scheduling'])
    ->name('nurse.scheduling');

// ================= PATIENT REGISTRATION ROUTES =================

Route::get('/register-patient', [PatientRegistrationController::class, 'index'])
    ->name('patient.register');

Route::post('/register-patient', [PatientRegistrationController::class, 'store'])
    ->name('patient.register.store');

Route::get('/register-patient/recent', [PatientRegistrationController::class, 'getRecentPatients'])
    ->name('patient.register.recent');

Route::get('/register-patient/{id}', [PatientRegistrationController::class, 'getPatientDetails'])
    ->name('patient.register.details');

// ================= API ENDPOINTS =================

Route::get('/api/local-doctors', function () {
    $doctors = DB::table('local_doctors')->orderBy('full_name')->get();
    return response()->json(['doctors' => $doctors]);
})->name('api.local-doctors');


// ================= CASHIER ROUTES =================

Route::get('/Cashier/dashboard', function () {
    return view('Cashier.dashboard');
})->name('cashier.dashboard');

Route::get('/Cashier/billing', function () {
    return view('Cashier.billing');
})->name('cashier.billing');

Route::get('/Cashier/payments', function () {
    return view('Cashier.payments');
})->name('cashier.payments');

Route::get('/Cashier/reports', function () {
    return view('Cashier.reports');
})->name('cashier.reports');


// ================= PROFILE ROUTES =================

Route::get('/profile', [ProfileController::class, 'edit'])
    ->name('profile.edit');

Route::patch('/profile', [ProfileController::class, 'update'])
    ->name('profile.update');

Route::delete('/profile', [ProfileController::class, 'destroy'])
    ->name('profile.destroy');


// ================= DEFAULT DASHBOARD =================

Route::get('/dashboard', function () {
    $role = session('role');

    return match ((int) $role) {
        1 => redirect()->route('meddirector.dashboard'),
        2 => redirect()->route('personnel.dashboard'),
        3 => redirect()->route('clinical.dashboard'),
        4 => redirect()->route('nurse.dashboard'),
        5 => redirect()->route('cashier.dashboard'),
        default => redirect('/'),
    };
})->name('dashboard');


require __DIR__.'/auth.php';
