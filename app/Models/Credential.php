<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Credential extends Model
{
    use HasFactory;

    // Tambahkan 'user_id' di sini agar Laravel mengizinkannya diisi melalui ::create()
    protected $fillable = [
        'user_id',
        'access_key',
        'secret_key',
        'status',
    ];

    // Relasi kembali ke tabel Users (Opsional tapi direkomendasikan)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
