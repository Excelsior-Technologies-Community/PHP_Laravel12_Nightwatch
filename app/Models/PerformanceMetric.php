<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformanceMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'method',
        'path',
        'route_name',
        'status_code',
        'duration_ms',
        'memory_mb',
        'category',
    ];

    protected function casts(): array
    {
        return [
            'status_code' => 'integer',
            'duration_ms' => 'float',
            'memory_mb' => 'float',
        ];
    }
}