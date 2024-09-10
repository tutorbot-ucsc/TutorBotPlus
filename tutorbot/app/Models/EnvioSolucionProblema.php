<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\JuecesVirtuales;
use App\Models\EvaluacionSolucion;
use App\Models\Problemas;
use App\Models\SolicitudRaLlm;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EnvioSolucionProblema extends Model
{
    use HasFactory;

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function juez_virtual(): BelongsTo
    {
        return $this->belongsTo(JuecesVirtuales::class, 'id_juez');
    }

    public function problema(): BelongsTo
    {
        return $this->belongsTo(Problemas::class, 'id_problema');
    }

    public function evaluaciones(): BelongsTo
    {
        return $this->belongsTo(EvaluacionSolucion::class, 'id_envio');
    }

    public function retroalimentacion(): HasMany
    {
        return $this->hasMany(SolicitudRaLlm::class,'id_envio');
    }
}
