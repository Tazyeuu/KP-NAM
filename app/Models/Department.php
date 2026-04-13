<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['name', 'latitude', 'longitude'];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
