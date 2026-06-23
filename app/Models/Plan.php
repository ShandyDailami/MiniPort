<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'plan_name',
        'price',
        'storage_limit_mb',
        // Tambahkan 4 baris ini:
        'description',
        'max_buckets',
        'max_file_size_mb',
        'allow_presigned_links',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'storage_limit_mb' => 'integer',
            // Opsional: tambahkan casting boolean agar aman
            'allow_presigned_links' => 'boolean',
        ];
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}