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
            //$this->fecha_finalizado = Carbon::now();
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

    public function ProblemasSeleccionadas(): HasMany{
        return $this->hasMany(SeleccionProblemasCertamenes::class, 'id_res_certamen');
    }
}
