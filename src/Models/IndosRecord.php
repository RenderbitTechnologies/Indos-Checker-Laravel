<?php

namespace RenderbitTechnologies\IndosCheckerLaravel\Models;

use Illuminate\Database\Eloquent\Model;

class IndosRecord extends Model
{
    protected $fillable = [
        'indos_number',
        'is_valid',
        'verified_at',
        'raw_response',
    ];

    protected $casts = [
        'is_valid' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function getTable(): string
    {
        return config('indos-checker-laravel.table', 'indos_checker_laravel_table');
    }
}
