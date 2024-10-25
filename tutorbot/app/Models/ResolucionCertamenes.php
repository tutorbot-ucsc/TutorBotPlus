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
            $envios = $this->envios()->whereNotNull('termino')->get();
            $problemas_seleccionado = $this->ProblemasSeleccionadas()->get();
            foreach($problemas_seleccionado as $item){
                $penalizacion = 0;
                $envios = EnvioSolucionProblema::where('id_certamen', '=', $this->id)->whereNotNull('termino')->orderBy('solucionado', 'asc')->get();
                foreach($envios as $envio){
                    if($envio->solucionado == true){
                        $this->puntaje_obtenido+= $envio->puntaje - $penalizacion;
                        $this->problemas_resueltos +=1;
                        break;
                    }else{
                        $penalizacion += $certamen->penalizacion_error;
                    }
                }
            }
            $this->fecha_finalizado = Carbon::now();
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
