<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkExperience extends Model
{
    protected $table = 'work_experiences';
    protected $primaryKey = 'experience_id';
    public $timestamps = false;

    protected $fillable = [
        'staff_number',
        'position_held',
        'start_date',
        'finish_date',
        'name_of_organization',
    ];
}
