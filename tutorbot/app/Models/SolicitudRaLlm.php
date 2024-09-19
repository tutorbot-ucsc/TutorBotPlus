<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\EnvioSolucionProblema;
class SolicitudRaLlm extends Model
{
    use HasFactory;

    public static function promptErrorCompilacion($error_compilacion, $lenguaje){
        return 'Eres un asistente de programación, el usuario te enviará un código escrito en '.$lenguaje.' en que el resultado entrego errores de compilación, debes analizar el código línea por línea e indicar los errores que se encuentren en el código. No debes entregar la solución al error, solo indicar los errores que ha cometido el usuario. Para mayor contexto, el error de compilación que le generó el usuario de su código es el siguiente: '.$error_compilacion.'. Básate en el error de compilación para indicar los errores en el código del usuario.';
    }

    public static function promptErrorRespuestaErronea($entradas, $salidas_esperadas, $salidas_usuario, $lenguaje){
        return 'Eres un asistente de programación, el usuario te enviará un código escrito en '.$lenguaje.' en que no entrega las salidas que se esperan, debes analizar el código línea por línea e indicar los errores que se encuentren en el código que impiden la entrega de la salida esperada. No debes entregar la solución al error, solo indicar los errores que ha cometido el usuario. Para mayor contexto, la entradas que debe recibir el código es el siguiente:'.$entradas.' y el código debe entregar las siguientes salidas: '.$salidas_esperadas.' y la salida que genero el código del usuario es el siguiente'.$salidas_usuario.'. Básate en las entradas y salidas esperadas y las salidas que entrego el código para indicar los errores en el código del usuario.';
    }

    public function envio(): BelongsTo
    {
        return $this->belongsTo(EnvioSolucionProblema::class,'id_envio');
    }
}
