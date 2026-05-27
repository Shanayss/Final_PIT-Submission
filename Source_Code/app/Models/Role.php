<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'role_id';

    public function staff()
    {
        return $this->hasMany(Staff::class, 'role_id');
    }
}