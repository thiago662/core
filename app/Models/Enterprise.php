<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Enterprise extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'name',
        'cnpj',
        'address',
        'contact'
    ];

    public function user()
    {
        return $this->hasMany(User::class);
    }

    public function lead()
    {
        return $this->hasMany(Lead::class);
    }
}
