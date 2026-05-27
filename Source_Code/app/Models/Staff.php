<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Staff extends Authenticatable
{
    use Notifiable;

    protected $table = 'staff';

    protected $primaryKey = 'staff_number';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'staff_number',
        'role_id',
        'email',
        'password',
        'first_name',
        'last_name',
        'address',
        'telephone',
        'date_of_birth',
        'sex',
        'nin',
        'position',
        'current_salary',
        'salary_scale',
        'hours_per_week',
        'contract_type',
        'payment_type',
    ];

    protected $hidden = [
        'password',
    ];
}
