<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'name',
        'contact',
        'source',
        'message',
        'status',
        'user_id',
        'enterprise_id'
    ];

    public function followUp()
    {
        return $this->hasMany(FollowUp::class);
    }

    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
