<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bucket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bucket_name',
        'region',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}