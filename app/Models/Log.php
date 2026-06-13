<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;

    // 1. Matikan fitur updated_at otomatis dari Laravel
    public const UPDATED_AT = null;

    // 2. Daftarkan kolom yang diizinkan untuk diisi
    protected $fillable = [
        'user_id',
        'action',
        'ip_address',
        'details',
    ];
}
