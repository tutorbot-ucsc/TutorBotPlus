<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Problemas;
class SolicitudRaLlm extends Model
{
    use HasFactory;

    public function problema(): BelongsTo
    {
        return $this->belongsTo(Problemas::class,'id_envio');
    }
}
