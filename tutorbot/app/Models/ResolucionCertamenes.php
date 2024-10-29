<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResolucionCertamenes extends Model
{
    use HasFactory;

    public function finalizar_certamen(){
        if($this->finalizado == false){
            $this->finalizado = true;
            $certamen = $this->certamen;
            $_now = Carbon::now();
            if($_now->gte(Carbon::parse($certamen->fecha_inicio)) && $_now->lte(Carbon::parse($certamen->fecha_termino))){
                $this->fecha_finalizado = $_now;
            }else{
                $this->fecha_finalizado = $certamen->fecha_termino;
            }
            $envios = $this->envios()->whereNotNull('termino')->get();
            $envios = EnvioSolucionProblema::where('id_certamen', '=', $this->id)->whereNotNull('termino')->where('solucionado','=',true)->orderBy('solucionado', 'asc')->get();
            foreach($envios as $envio){
                $this->puntaje_obtenido+= $envio->puntaje;
                $this->problemas_resueltos +=1;
            }
            $this->save();
        }
    }
    public function usuario(): BelongsTo
    {
        return $this->BelongsTo(User::class, 'id_usuario');
    }

    public function certamen(): BelongsTo
    {
        return $this->BelongsTo(Certamenes::class, 'id_certamen');
    }

    public function envios(): HasMany{
        return $this->hasMany(EnvioSolucionProblema::class, 'id_certamen');
    }

    public function ProblemasSeleccionadas(): HasMany{
        return $this->hasMany(SeleccionProblemasCertamenes::class, 'id_res_certamen');
    }
}
