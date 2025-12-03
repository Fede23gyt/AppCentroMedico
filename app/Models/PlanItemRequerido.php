<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanItemRequerido extends Model
{
    use HasFactory;

    protected $table = 'plan_items_requeridos';

    protected $fillable = [
        'plan_id',
        'item_codigo',
        'es_obligatorio',
        'grupo',
        'descripcion_grupo',
    ];

    protected $casts = [
        'es_obligatorio' => 'boolean',
    ];

    // Relación con Plan
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
