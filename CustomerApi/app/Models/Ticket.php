<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'user_id',
        'agent_id',
    ];

    protected $casts = [
        'status' => 'string',
        'priority' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function responses()
    {
        return $this->hasMany(Response::class);
    }
}
