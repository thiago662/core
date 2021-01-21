<?php

namespace App\Models;

use App\Models\Concerns\AppliesFilters;
use App\Models\Concerns\ConstructChart;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory;
    use SoftDeletes;
    use AppliesFilters;
    use ConstructChart;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'source',
        'interest',
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
