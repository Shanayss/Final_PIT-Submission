<?php

namespace App\Http\Controllers\ClinicalStaff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    // Shows appointments assigned to the consultant/clinical staff
  public function index(Request $request)
{
    $search = $request->input('search');

    $appointments = DB::table('appointments')
        ->leftJoin('patients', 'appointments.patient_number', '=', 'patients.patient_number')
        ->leftJoin('staff', 'appointments.staff_number', '=', 'staff.staff_number')
        ->when($search, function ($query) use ($search) {
            $query->where(function ($inner) use ($search) {
                $inner->where('appointments.patient_number', 'like', "%{$search}%")
                    ->orWhere('appointments.status', 'like', "%{$search}%")
                    ->orWhere('appointments.examination_room', 'like', "%{$search}%")
                    ->orWhere('patients.first_name', 'like', "%{$search}%")
                    ->orWhere('patients.last_name', 'like', "%{$search}%")
                    ->orWhere('staff.first_name', 'like', "%{$search}%")
                    ->orWhere('staff.last_name', 'like', "%{$search}%");
            });
        })
        ->select(
            'appointments.appointment_id',
            'appointments.patient_number',
            'appointments.staff_number',
            'appointments.clinic_number',
            'appointments.appointment_date',
            'appointments.appointment_time',
            'appointments.examination_room',
            'appointments.status',

            // Patient name, fallback to patient number
            DB::raw("
                COALESCE(
                    NULLIF(TRIM(CONCAT(patients.first_name, ' ', patients.last_name)), ''),
                    appointments.patient_number
                ) as patient_display_name
            "),

            // Staff/Doctor name, fallback to staff number, then fallback text
            DB::raw("
                COALESCE(
                    NULLIF(TRIM(CONCAT(staff.first_name, ' ', staff.last_name)), ''),
                    appointments.staff_number,
                    'No assigned staff'
                ) as doctor_display_name
            ")
        )
        ->orderByDesc('appointments.appointment_id')
        ->get();

    $totalAppointments = DB::table('appointments')->count();
    $pendingCount = DB::table('appointments')->whereIn('status', ['Scheduled', 'Pending'])->count();
    $completedCount = DB::table('appointments')->where('status', 'Completed')->count();

    return view('ClinicalStaff.appointments', compact(
        'appointments',
        'totalAppointments',
        'pendingCount',
        'completedCount'
    ));
}

    // Updates appointment status from the appointment table
        public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:Scheduled,In Progress,Completed,Cancelled',
        ]);

        DB::table('appointments')
            ->where('appointment_id', $id)
            ->update([
                'status' => $request->status,
            ]);

        return redirect()
            ->route('clinical.appointments')
            ->with('success', 'Appointment status updated successfully.');
    }

    // Opens consultation form later
    public function consultation($id)
    {
        // Temporary page first, we will replace this with the real form next
        return 'Conduct consultation for appointment ID: ' . $id;
    }

    // Shows the create appointment form
    // Shows create appointment form
// Shows create appointment form
public function create()
{
    // Get patients from database
    $patients = DB::table('patients')
        ->orderBy('patient_number', 'asc')
        ->get();

    // Get doctors/consultants from database
    $doctors = DB::table('staff')
        ->whereIn('position', ['Doctor', 'Consultant'])
        ->orderBy('first_name', 'asc')
        ->get();

    // Since examination_rooms table does not exist, use fixed room list for now
    $rooms = collect([
        (object) ['room_number' => 'E001'],
        (object) ['room_number' => 'E002'],
        (object) ['room_number' => 'E003'],
        (object) ['room_number' => 'E004'],
        (object) ['room_number' => 'E005'],
        (object) ['room_number' => 'E006'],
        (object) ['room_number' => 'E007'],
        (object) ['room_number' => 'E008'],
        (object) ['room_number' => 'E009'],
        (object) ['room_number' => 'E010'],
        (object) ['room_number' => 'E011'],
        (object) ['room_number' => 'E012'],
        (object) ['room_number' => 'E013'],
        (object) ['room_number' => 'E014'],
    ]);

    return view('ClinicalStaff.appointments.create', compact('patients', 'doctors', 'rooms'));
}

// Saves new appointment
// Saves new appointment
public function store(Request $request)
{
    $request->validate([
        'patient_number' => 'required|exists:patients,patient_number',
        'staff_number' => 'required|exists:staff,staff_number',
        'examination_room' => 'required',
        'appointment_date' => 'required|date',
        'appointment_time' => 'required',
        'status' => 'required|in:Scheduled,Completed,Cancelled',
    ]);

    $patient = DB::table('patients')->where('patient_number', $request->patient_number)->first();

    if ($this->appointmentSlotIsBooked($request->staff_number, $request->appointment_date, $request->appointment_time, $request->examination_room)) {
        return back()
            ->withInput()
            ->withErrors(['appointment_time' => 'The selected consultant or room is already booked at that date and time.']);
    }

    DB::table('appointments')->insert([
        'patient_number' => $request->patient_number,
        'clinic_number' => $patient->clinic_number,
        'staff_number' => $request->staff_number,
        'appointment_date' => $request->appointment_date,
        'appointment_time' => $request->appointment_time,
        'examination_room' => $request->examination_room,
        'status' => $request->status,
    ]);

    return redirect()
        ->route('clinical.appointments')
        ->with('success', 'Appointment scheduled successfully.');
}

// Opens edit appointment form
public function edit($appointment_id)
{
    $appointment = DB::table('appointments')
        ->leftJoin('patients', 'appointments.patient_number', '=', 'patients.patient_number')
        ->select(
            'appointments.*',
            'patients.telephone as patient_phone'
        )
        ->where('appointments.appointment_id', $appointment_id)
        ->first();

    if (!$appointment) {
        abort(404);
    }

    $patients = DB::table('patients')
        ->orderBy('patient_number', 'asc')
        ->get();

    $doctors = DB::table('staff')
        ->whereIn('position', ['Doctor', 'Consultant'])
        ->orderBy('first_name', 'asc')
        ->get();

    $rooms = DB::table('examination_rooms')
        ->orderBy('room_number', 'asc')
        ->get();

    return view('ClinicalStaff.appointments.edit', compact(
        'appointment',
        'patients',
        'doctors',
        'rooms'
    ));
}


// Updates appointment record
public function update(Request $request, $appointment_id)
{
    $request->validate([
        'patient_number' => 'required|exists:patients,patient_number',
        'staff_number' => 'required|exists:staff,staff_number',
        'examination_room' => 'required',
        'appointment_date' => 'required|date',
        'appointment_time' => 'required',
        'status' => 'required|in:Scheduled,Completed,Cancelled',
    ]);

    $patient = DB::table('patients')
        ->where('patient_number', $request->patient_number)
        ->first();

    if (!$patient) {
        return back()
            ->withInput()
            ->withErrors(['patient_number' => 'Selected patient does not exist.']);
    }

    if ($this->appointmentSlotIsBooked($request->staff_number, $request->appointment_date, $request->appointment_time, $request->examination_room, $appointment_id)) {
        return back()
            ->withInput()
            ->withErrors(['appointment_time' => 'The selected consultant or room is already booked at that date and time.']);
    }

        DB::table('appointments')
        ->where('appointment_id', $appointment_id)
        ->update([
            'patient_number' => $request->patient_number,
            'clinic_number' => $patient->clinic_number,
            'staff_number' => $request->staff_number,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'examination_room' => $request->examination_room,
            'status' => $request->status,
        ]);

    return redirect()
        ->route('clinical.appointments')
        ->with('success', 'Appointment updated successfully.');
}


// Deletes appointment record
public function destroy($appointment_id)
{
    DB::table('appointments')
        ->where('appointment_id', $appointment_id)
        ->delete();

    return redirect()
        ->route('clinical.appointments')
        ->with('success', 'Appointment deleted successfully.');
}





private function appointmentSlotIsBooked(string $staffNumber, string $date, string $time, string $room, ?int $ignoreAppointmentId = null): bool
{
    return DB::table('appointments')
        ->whereDate('appointment_date', $date)
        ->where('appointment_time', $time)
        ->when($ignoreAppointmentId, fn ($query) => $query->where('appointment_id', '!=', $ignoreAppointmentId))
        ->where(function ($query) use ($staffNumber, $room) {
            $query->where('staff_number', $staffNumber)
                ->orWhere('examination_room', $room);
        })
        ->exists();
}


}
