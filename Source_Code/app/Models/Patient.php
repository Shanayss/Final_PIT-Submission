<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $table = 'patients';
    protected $primaryKey = 'patient_number';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'patient_number',
        'clinic_number',
        'first_name',
        'last_name',
        'address',
        'telephone',
        'date_of_birth',
        'sex',
        'marital_status',
        'date_registered',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'date_registered' => 'date',
    ];

    public function inPatients()
    {
        return $this->hasMany(InPatient::class, 'patient_number', 'patient_number');
    }

    public function nextOfKins()
    {
        return $this->hasMany(NextOfKin::class, 'patient_number', 'patient_number');
    }

    public function localDoctor()
    {
        return $this->belongsTo(LocalDoctor::class, 'clinic_number', 'clinic_number');
    }
}
