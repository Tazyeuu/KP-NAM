<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketLog extends Model
{
    protected $fillable = ['ticket_id', 'changed_by', 'status_to', 'note'];
}
