<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $fillable = ['ticket_id', 'teknisi_id', 'note', 'assigned_at', 'started_at', 'completed_at'];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function teknisi()
    {
        return $this->belongsTo(User::class, 'teknisi_id');
    }
}
