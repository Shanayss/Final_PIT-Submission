<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Qualification extends Model
{
    protected $table = 'qualifications';
    protected $primaryKey = 'qualification_id';
    public $timestamps = false;

    protected $fillable = [
        'staff_number',
        'qualification_type',
        'qualification_date',
        'institution_name',
    ];
}
