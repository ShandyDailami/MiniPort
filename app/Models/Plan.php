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
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'storage_limit_mb' => 'integer',
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