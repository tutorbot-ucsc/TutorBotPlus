<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\EnvioSolucionProblema;
class SolicitudRaLlm extends Model
{
    use HasFactory;

    public static function promptError($error_compilacion=null, $lenguaje, $resultado=null){
        if(isset($error_compilacion)){
            return "Eres un asistente de programación, el usuario te enviará un código escrito en ".$lenguaje." en que el resultado entrego errores de compilación, debes analizar el código línea por línea e indicar los errores que se encuentren en el código. No debes entregar la solución al error, solo indicar los errores que ha cometido el usuario. Para mayor contexto, el error de compilación que le generó el usuario de su código es el siguiente: \n ".$error_compilacion."Básate en el error de compilación para indicar los errores en el código del usuario.";
        }else{
            $queMejorar = "memoria";
            if(str_contains($resultado, 'time')){
                $queMejorar = "tiempo";
            }
            return "Eres un asistente de programación, el usuario te enviará un código escrito en ".$lenguaje." en que el resultado entrego el siguiente error '".$resultado."'. Debes analizar el código línea por línea y dar sugerencias de cómo podria mejorar el código en cuanto a ".$queMejorar;
        }
    }

    public static function promptErrorRespuestaErronea($entradas, $salidas_esperadas, $salidas_usuario, $lenguaje, $enunciado_resumido = null){
        $prompt = 'Eres un asistente de programación, el usuario te enviará un código escrito en '.$lenguaje." en que no entrega las salidas que se esperan, debes analizar el código línea por línea e indicar los errores que se encuentren en el código que impiden la entrega de la salida esperada. No entregues la solución al error, al problema ni tampoco dar sugerencias para mejorar el código, solo indicar los errores que ha cometido el usuario. Para mayor contexto, la entradas que debe recibir el código es el siguiente:\n".$entradas."\ny el código debe entregar las siguientes salidas:\n".$salidas_esperadas."\npero el código del usuario entrego las siguientes salidas: \n".$salidas_usuario."\n";
        if(isset($enunciado_resumido)){
            $prompt = $prompt."El problema que debe resolver el código es el siguiente \n:".$enunciado_resumido."\nBásate en el problema, entradas, salidas esperadas y las salidas generadas por el código para indicar los errores en el código del usuario.";
        }
        $prompt = $prompt."Básate en las entradas, salidas esperadas y las salidas generadas por el código para indicar los errores en el código del usuario.";
        return $prompt;
    }


    
    public function envio(): BelongsTo
    {
        return $this->belongsTo(EnvioSolucionProblema::class,'id_envio');
    }
}
