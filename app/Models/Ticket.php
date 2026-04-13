<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'ticket_number', 'user_id', 'category_id', 'department_id', 
        'location_detail', 'subject', 'description', 'image_path', 
        'priority', 'status'
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function category() { return $this->belongsTo(Category::class); }
    public function department() { return $this->belongsTo(Department::class); }
    public function assignments() {
        return $this->hasMany(Assignment::class);
    }
}
