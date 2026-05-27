<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffAllocation extends Model
{
    protected $table = 'staff_allocations';
    protected $primaryKey = 'allocation_id';
    public $timestamps = false;

    protected $fillable = [
        'staff_number',
        'ward_number',
        'role_for_week',
        'shift',
        'week_start_date',
    ];
}
