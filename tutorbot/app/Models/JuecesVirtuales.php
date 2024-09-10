<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\EnvioSolucionProblema;
class JuecesVirtuales extends Model
{
    use HasFactory;

    public function envios(): HasMany
    {
        return $this->hasMany(EnvioSolucionProblema::class, 'id_juez');
    }
}
