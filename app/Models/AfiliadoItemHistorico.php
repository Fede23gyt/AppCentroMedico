<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AfiliadoItemHistorico extends Model
{
    use HasFactory;

    protected $table = 'afiliado_items_historico';

    protected $fillable = [
        'afiliado_id',
        'item_codigo',
        'fecha_lectura',
        'certificado',
    ];

    protected $casts = [
        'fecha_lectura' => 'datetime',
    ];

    // Relación con Afiliado
    public function afiliado(): BelongsTo
    {
        return $this->belongsTo(Afiliado::class);
    }
}
