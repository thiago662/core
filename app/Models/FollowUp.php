<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FollowUp extends Model
{
    use HasFactory;

    use SoftDeletes;

    public $timestamps = true;

    protected $fillable = [
        'type',
        'message',
        'value',
        'lead_id',
        'created_at'
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }
}
